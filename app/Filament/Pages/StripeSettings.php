<?php

namespace App\Filament\Pages;

use App\Models\StripeSetting;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;

class StripeSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationLabel = 'Payments & Stripe';

    protected static ?string $title = 'Payments & Stripe Settings';

    protected static ?int $navigationSort = 3;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected string $view = 'filament.pages.stripe-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = StripeSetting::singleton();

        $this->form->fill([
            'enabled' => $settings->enabled,
            'test_mode' => $settings->test_mode,
            'publishable_key' => $settings->publishable_key,
            'secret_key' => null,
            'webhook_secret' => null,
            'currency' => $settings->currency,
            'success_url' => $settings->success_url,
            'cancel_url' => $settings->cancel_url,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Stripe Payments')
                    ->description('Configure Stripe subscription checkout for ZIP territory purchases.')
                    ->schema([
                        Toggle::make('enabled')
                            ->label('Enable Stripe checkout')
                            ->default(false)
                            ->live(),

                        Toggle::make('test_mode')
                            ->label('Test mode')
                            ->default(true)
                            ->helperText('Use Stripe test keys when enabled.'),

                        TextInput::make('publishable_key')
                            ->label('Publishable key')
                            ->maxLength(255)
                            ->required(fn (callable $get): bool => (bool) $get('enabled')),

                        TextInput::make('secret_key')
                            ->label('Secret key')
                            ->password()
                            ->revealable()
                            ->placeholder('Leave blank to keep current secret key')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (callable $get): bool => (bool) $get('enabled') && ! StripeSetting::singleton()->secret_key),

                        TextInput::make('webhook_secret')
                            ->label('Webhook signing secret')
                            ->password()
                            ->revealable()
                            ->placeholder('Leave blank to keep current webhook secret')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->helperText('Create a webhook endpoint in Stripe pointing to the URL below.'),

                        TextInput::make('currency')
                            ->label('Currency')
                            ->maxLength(3)
                            ->default('usd')
                            ->required(),

                        TextInput::make('success_url')
                            ->label('Success redirect URL')
                            ->url()
                            ->placeholder(route('stripe.checkout.success'))
                            ->helperText('Optional. Defaults to the built-in checkout success page.'),

                        TextInput::make('cancel_url')
                            ->label('Cancel redirect URL')
                            ->url()
                            ->placeholder(url('/?checkout=cancelled'))
                            ->helperText('Optional. Defaults to the homepage with a cancelled checkout query flag.'),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Webhook Endpoint')
                    ->schema([
                        Placeholder::make('webhook_url')
                            ->label('Webhook URL')
                            ->content(route('stripe.webhook'))
                            ->copyable(),

                        Placeholder::make('webhook_events')
                            ->label('Recommended events')
                            ->content('checkout.session.completed, customer.subscription.updated, customer.subscription.deleted, invoice.payment_succeeded, invoice.payment_failed'),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = StripeSetting::singleton();

        $payload = [
            'enabled' => (bool) $data['enabled'],
            'test_mode' => (bool) $data['test_mode'],
            'publishable_key' => $data['publishable_key'] ?? null,
            'currency' => strtolower($data['currency'] ?? 'usd'),
            'success_url' => $data['success_url'] ?? null,
            'cancel_url' => $data['cancel_url'] ?? null,
        ];

        if (! empty($data['secret_key'])) {
            $payload['secret_key'] = $data['secret_key'];
        }

        if (! empty($data['webhook_secret'])) {
            $payload['webhook_secret'] = $data['webhook_secret'];
        }

        $settings->update($payload);
        StripeSetting::applyConfig();

        Notification::make()
            ->title('Stripe settings saved')
            ->success()
            ->send();

        $this->form->fill([
            ...$data,
            'secret_key' => null,
            'webhook_secret' => null,
        ]);
    }
}
