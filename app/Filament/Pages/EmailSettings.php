<?php

namespace App\Filament\Pages;

use App\Models\EmailSetting;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;

class EmailSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Email Settings';

    protected static ?string $title = 'Email Settings';

    protected static ?int $navigationSort = 2;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected string $view = 'filament.pages.email-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = EmailSetting::singleton();

        $this->form->fill([
            'mail_mailer' => $settings->mail_mailer,
            'mail_host' => $settings->mail_host,
            'mail_port' => $settings->mail_port,
            'mail_username' => $settings->mail_username,
            'mail_password' => null,
            'mail_encryption' => $settings->mail_encryption,
            'mail_from_address' => $settings->mail_from_address,
            'mail_from_name' => $settings->mail_from_name,
            'admin_notification_enabled' => $settings->admin_notification_enabled,
            'admin_notification_address' => $settings->admin_notification_address,
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Mail Transport')
                    ->description('Configure how the application sends email.')
                    ->schema([
                        Select::make('mail_mailer')
                            ->label('Mailer')
                            ->options([
                                'smtp' => 'SMTP',
                                'log' => 'Log (development)',
                                'sendmail' => 'Sendmail',
                            ])
                            ->required()
                            ->live(),

                        TextInput::make('mail_host')
                            ->label('SMTP host')
                            ->maxLength(255)
                            ->required(fn (callable $get): bool => $get('mail_mailer') === 'smtp')
                            ->visible(fn (callable $get): bool => $get('mail_mailer') === 'smtp'),

                        TextInput::make('mail_port')
                            ->label('SMTP port')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(65535)
                            ->required(fn (callable $get): bool => $get('mail_mailer') === 'smtp')
                            ->visible(fn (callable $get): bool => $get('mail_mailer') === 'smtp'),

                        Select::make('mail_encryption')
                            ->label('Encryption')
                            ->options([
                                '' => 'None',
                                'tls' => 'TLS',
                                'ssl' => 'SSL',
                            ])
                            ->visible(fn (callable $get): bool => $get('mail_mailer') === 'smtp'),

                        TextInput::make('mail_username')
                            ->label('SMTP username')
                            ->maxLength(255)
                            ->visible(fn (callable $get): bool => $get('mail_mailer') === 'smtp'),

                        TextInput::make('mail_password')
                            ->label('SMTP password')
                            ->password()
                            ->revealable()
                            ->placeholder('Leave blank to keep current password')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->visible(fn (callable $get): bool => $get('mail_mailer') === 'smtp'),

                        TextInput::make('mail_from_address')
                            ->label('From address')
                            ->email()
                            ->maxLength(255)
                            ->required(),

                        TextInput::make('mail_from_name')
                            ->label('From name')
                            ->maxLength(255)
                            ->required(),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Interested People Notifications')
                    ->description('Configure admin email alerts when a new interested person submits the form.')
                    ->schema([
                        Toggle::make('admin_notification_enabled')
                            ->label('Notify admin on new submission')
                            ->default(true)
                            ->live(),

                        TextInput::make('admin_notification_address')
                            ->label('Admin notification email')
                            ->email()
                            ->maxLength(255)
                            ->required(fn (callable $get): bool => (bool) $get('admin_notification_enabled'))
                            ->visible(fn (callable $get): bool => (bool) $get('admin_notification_enabled')),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        $settings = EmailSetting::singleton();

        $payload = [
            'mail_mailer' => $data['mail_mailer'],
            'mail_host' => $data['mail_host'] ?? null,
            'mail_port' => isset($data['mail_port']) ? (int) $data['mail_port'] : null,
            'mail_username' => $data['mail_username'] ?? null,
            'mail_encryption' => filled($data['mail_encryption'] ?? null) ? $data['mail_encryption'] : null,
            'mail_from_address' => $data['mail_from_address'],
            'mail_from_name' => $data['mail_from_name'],
            'admin_notification_enabled' => (bool) $data['admin_notification_enabled'],
            'admin_notification_address' => $data['admin_notification_address'] ?? null,
        ];

        if (! empty($data['mail_password'])) {
            $payload['mail_password'] = $data['mail_password'];
        }

        $settings->update($payload);
        EmailSetting::applyMailConfig();

        Notification::make()
            ->title('Email settings saved')
            ->success()
            ->send();

        $this->form->fill([
            ...$data,
            'mail_password' => null,
        ]);
    }
}
