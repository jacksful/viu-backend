<?php

namespace App\Filament\Pages;

use App\Models\CloudflareSetting;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;

class CloudflareSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Cloudflare Security';

    protected static ?string $title = 'Cloudflare Turnstile Settings';

    protected static ?int $navigationSort = 6;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $slug = 'cloudflare-settings';

    protected string $view = 'filament.pages.cloudflare-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = CloudflareSetting::singleton();

        $this->form->fill([
            'enabled' => $settings->enabled,
            'site_key' => $settings->site_key,
            'secret_key' => null,
            'admin_login_enabled' => $settings->admin_login_enabled,
            'customer_login_enabled' => $settings->customer_login_enabled,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Cloudflare Turnstile')
                    ->description('Protect admin and customer login pages with Cloudflare Turnstile CAPTCHA.')
                    ->schema([
                        SchemaComponents\Grid::make(3)
                            ->schema([
                                Toggle::make('enabled')
                                    ->label('Enable Turnstile')
                                    ->default(false)
                                    ->live(),

                                Toggle::make('admin_login_enabled')
                                    ->label('Protect admin login')
                                    ->default(true)
                                    ->disabled(fn (callable $get): bool => ! (bool) $get('enabled')),

                                Toggle::make('customer_login_enabled')
                                    ->label('Protect customer login')
                                    ->default(true)
                                    ->disabled(fn (callable $get): bool => ! (bool) $get('enabled')),
                            ])
                            ->columnSpanFull(),

                        TextInput::make('site_key')
                            ->label('Site key')
                            ->maxLength(255)
                            ->columnSpanFull()
                            ->required(fn (callable $get): bool => (bool) $get('enabled')),

                        TextInput::make('secret_key')
                            ->label('Secret key')
                            ->password()
                            ->revealable()
                            ->columnSpanFull()
                            ->placeholder('Leave blank to keep current secret key')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->required(fn (callable $get): bool => (bool) $get('enabled') && ! CloudflareSetting::singleton()->secret_key),
                    ]),

                SchemaComponents\Section::make('Setup Guide')
                    ->schema([
                        Placeholder::make('dashboard_link')
                            ->label('Cloudflare dashboard')
                            ->columnSpanFull()
                            ->content('Create a Turnstile widget at dash.cloudflare.com → Turnstile, then paste the site key and secret key above.'),

                        Placeholder::make('protected_pages')
                            ->label('Protected pages')
                            ->columnSpanFull()
                            ->content('Admin login: '.url('/admin/login')."\nCustomer login: ".route('user.login')),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = CloudflareSetting::singleton();

        $payload = [
            'enabled' => (bool) $data['enabled'],
            'site_key' => $data['site_key'] ?? null,
            'admin_login_enabled' => (bool) ($data['admin_login_enabled'] ?? false),
            'customer_login_enabled' => (bool) ($data['customer_login_enabled'] ?? false),
        ];

        if (! empty($data['secret_key'])) {
            $payload['secret_key'] = $data['secret_key'];
        }

        $settings->update($payload);
        CloudflareSetting::applyConfig();

        Notification::make()
            ->title('Cloudflare settings saved')
            ->success()
            ->send();

        $this->form->fill([
            ...$data,
            'secret_key' => null,
        ]);
    }
}
