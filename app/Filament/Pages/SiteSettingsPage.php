<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Support\SiteSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Validator;

class SiteSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationLabel = 'General';

    protected static ?string $title = 'General Settings';

    protected static ?int $navigationSort = 1;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $slug = 'site-settings';

    protected string $view = 'filament.pages.site-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = SiteSetting::singleton();

        $this->form->fill($settings->only($settings->getFillable()));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Branding Settings')
                    ->description('Global site identity, logos, and favicon.')
                    ->schema([
                        TextInput::make('site_name')
                            ->label('Site name')
                            ->maxLength(255)
                            ->placeholder('VIU Real Estate Solutions'),

                        TextInput::make('site_tagline')
                            ->label('Site tagline')
                            ->maxLength(255)
                            ->placeholder('Own the market before they sell'),

                        FileUpload::make('logo_light_path')
                            ->label('Logo light')
                            ->helperText('Used on dark backgrounds such as the site header and footer.')
                            ->image()
                            ->disk('public')
                            ->directory('cms/site')
                            ->visibility('public'),

                        FileUpload::make('logo_dark_path')
                            ->label('Logo dark')
                            ->helperText('Used on light backgrounds.')
                            ->image()
                            ->disk('public')
                            ->directory('cms/site')
                            ->visibility('public'),

                        FileUpload::make('favicon_path')
                            ->label('Favicon')
                            ->image()
                            ->disk('public')
                            ->directory('cms/site')
                            ->visibility('public'),

                        FileUpload::make('admin_panel_logo_path')
                            ->label('Admin panel logo')
                            ->image()
                            ->disk('public')
                            ->directory('cms/site')
                            ->visibility('public'),

                        FileUpload::make('footer_logo_path')
                            ->label('Footer logo')
                            ->image()
                            ->disk('public')
                            ->directory('cms/site')
                            ->visibility('public'),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Contact Settings')
                    ->description('Public contact details used across the website.')
                    ->schema([
                        TextInput::make('contact_email')
                            ->label('Contact email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('support_email')
                            ->label('Support email')
                            ->email()
                            ->maxLength(255),

                        TextInput::make('phone_number')
                            ->label('Phone number')
                            ->tel()
                            ->maxLength(30),

                        TextInput::make('whatsapp_number')
                            ->label('WhatsApp number')
                            ->helperText('Include country code, e.g. +15551234567')
                            ->tel()
                            ->maxLength(30),

                        Textarea::make('address')
                            ->label('Address')
                            ->rows(3)
                            ->maxLength(500),

                        TextInput::make('google_map_embed_url')
                            ->label('Google Map embed URL')
                            ->helperText('Paste the Google Maps embed URL (the src value from the iframe).')
                            ->url()
                            ->maxLength(2000),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Validator::make($data, $this->validationRules())->validate();

        $settings = SiteSetting::singleton();
        $settings->update($this->normalizePayload($data));

        SiteSettings::clearCache();

        Notification::make()
            ->title('Site settings saved')
            ->success()
            ->send();

        $this->form->fill($settings->fresh()->only($settings->getFillable()));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationRules(): array
    {
        return [
            'site_name' => ['nullable', 'string', 'max:255'],
            'site_tagline' => ['nullable', 'string', 'max:255'],
            'logo_light_path' => ['nullable', 'string', 'max:255'],
            'logo_dark_path' => ['nullable', 'string', 'max:255'],
            'favicon_path' => ['nullable', 'string', 'max:255'],
            'admin_panel_logo_path' => ['nullable', 'string', 'max:255'],
            'footer_logo_path' => ['nullable', 'string', 'max:255'],
            'contact_email' => ['nullable', 'email', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s\-().]+$/'],
            'whatsapp_number' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s\-().]+$/'],
            'address' => ['nullable', 'string', 'max:500'],
            'google_map_embed_url' => ['nullable', 'url', 'max:2000'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizePayload(array $data): array
    {
        $fileFields = [
            'logo_light_path',
            'logo_dark_path',
            'favicon_path',
            'admin_panel_logo_path',
            'footer_logo_path',
        ];

        foreach ($fileFields as $field) {
            if (isset($data[$field]) && is_array($data[$field])) {
                $data[$field] = $data[$field][0] ?? null;
            }
        }

        return $data;
    }
}
