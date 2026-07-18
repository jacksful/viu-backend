<?php

namespace App\Filament\Pages;

use App\Cms\Enums\PageRobots;
use App\Models\TrackingSocialSetting;
use App\Support\TrackingSocialSettings;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TrackingSocialSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    protected static ?string $navigationLabel = 'Tracking & SEO';

    protected static ?string $title = 'Tracking & SEO Settings';

    protected static ?int $navigationSort = 4;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $slug = 'tracking-social-settings';

    protected string $view = 'filament.pages.tracking-social-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = TrackingSocialSetting::singleton();

        $this->form->fill($settings->only($settings->getFillable()));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Google Settings')
                    ->description('Google Analytics, Tag Manager, and Search Console verification.')
                    ->schema([
                        Toggle::make('google_analytics_enabled')
                            ->label('Enable Google Analytics')
                            ->live(),

                        TextInput::make('google_analytics_measurement_id')
                            ->label('Google Analytics Measurement ID')
                            ->placeholder('G-XXXXXXXXXX')
                            ->maxLength(50)
                            ->visible(fn (callable $get): bool => (bool) $get('google_analytics_enabled')),

                        Toggle::make('google_tag_manager_enabled')
                            ->label('Enable Google Tag Manager')
                            ->live(),

                        TextInput::make('google_tag_manager_id')
                            ->label('Google Tag Manager ID')
                            ->placeholder('GTM-XXXXXXX')
                            ->maxLength(50)
                            ->visible(fn (callable $get): bool => (bool) $get('google_tag_manager_enabled')),

                        TextInput::make('google_search_console_verification')
                            ->label('Google Search Console Verification Meta Tag Content')
                            ->helperText('Paste only the content value, not the full meta tag.')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Facebook Settings')
                    ->schema([
                        Toggle::make('facebook_pixel_enabled')
                            ->label('Enable Facebook Pixel')
                            ->live(),

                        TextInput::make('facebook_pixel_id')
                            ->label('Facebook Pixel ID')
                            ->maxLength(50)
                            ->visible(fn (callable $get): bool => (bool) $get('facebook_pixel_enabled')),

                        TextInput::make('facebook_domain_verification')
                            ->label('Facebook Domain Verification Meta Tag Content')
                            ->helperText('Paste only the content value, not the full meta tag.')
                            ->maxLength(255),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Other Social Media Tracking')
                    ->description('Enable each platform only when you have a valid pixel or tag ID.')
                    ->schema([
                        Toggle::make('tiktok_pixel_enabled')
                            ->label('Enable TikTok Pixel')
                            ->live(),

                        TextInput::make('tiktok_pixel_id')
                            ->label('TikTok Pixel ID')
                            ->maxLength(50)
                            ->visible(fn (callable $get): bool => (bool) $get('tiktok_pixel_enabled')),

                        Toggle::make('linkedin_insight_enabled')
                            ->label('Enable LinkedIn Insight Tag')
                            ->live(),

                        TextInput::make('linkedin_insight_tag_id')
                            ->label('LinkedIn Insight Tag ID')
                            ->maxLength(50)
                            ->visible(fn (callable $get): bool => (bool) $get('linkedin_insight_enabled')),

                        Toggle::make('pinterest_tag_enabled')
                            ->label('Enable Pinterest Tag')
                            ->live(),

                        TextInput::make('pinterest_tag_id')
                            ->label('Pinterest Tag ID')
                            ->maxLength(50)
                            ->visible(fn (callable $get): bool => (bool) $get('pinterest_tag_enabled')),

                        Toggle::make('twitter_pixel_enabled')
                            ->label('Enable Twitter/X Pixel')
                            ->live(),

                        TextInput::make('twitter_pixel_id')
                            ->label('Twitter/X Pixel ID')
                            ->maxLength(50)
                            ->visible(fn (callable $get): bool => (bool) $get('twitter_pixel_enabled')),

                        Toggle::make('snapchat_pixel_enabled')
                            ->label('Enable Snapchat Pixel')
                            ->live(),

                        TextInput::make('snapchat_pixel_id')
                            ->label('Snapchat Pixel ID')
                            ->maxLength(50)
                            ->visible(fn (callable $get): bool => (bool) $get('snapchat_pixel_enabled')),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Social Profile Links')
                    ->description('Used in the site footer and other public areas.')
                    ->schema([
                        TextInput::make('facebook_url')
                            ->label('Facebook URL')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('instagram_url')
                            ->label('Instagram URL')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('linkedin_url')
                            ->label('LinkedIn URL')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('twitter_url')
                            ->label('Twitter/X URL')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('youtube_url')
                            ->label('YouTube URL')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('tiktok_url')
                            ->label('TikTok URL')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('pinterest_url')
                            ->label('Pinterest URL')
                            ->url()
                            ->maxLength(255),

                        TextInput::make('whatsapp_number')
                            ->label('WhatsApp Number')
                            ->helperText('Include country code, e.g. +15551234567')
                            ->tel()
                            ->maxLength(30),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('SEO and Meta Settings')
                    ->description('Site-wide defaults when a page does not define its own SEO data.')
                    ->schema([
                        TextInput::make('default_meta_title')
                            ->label('Default meta title')
                            ->maxLength(255),

                        Textarea::make('default_meta_description')
                            ->label('Default meta description')
                            ->rows(3)
                            ->maxLength(500),

                        Textarea::make('default_meta_keywords')
                            ->label('Default meta keywords')
                            ->rows(2)
                            ->maxLength(500),

                        FileUpload::make('default_og_image_path')
                            ->label('Default OG image')
                            ->image()
                            ->disk('public')
                            ->directory('cms/seo')
                            ->visibility('public'),

                        Select::make('default_robots')
                            ->label('Default robots meta value')
                            ->options(collect(PageRobots::cases())->mapWithKeys(
                                fn (PageRobots $robots) => [$robots->value => $robots->label()]
                            ))
                            ->required()
                            ->default(PageRobots::IndexFollow->value),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->normalizePayload($this->form->getState());

        $validator = Validator::make($data, $this->validationRules($data));

        if ($validator->fails()) {
            Notification::make()
                ->title('Could not save settings')
                ->body($validator->errors()->first())
                ->danger()
                ->send();

            $validator->validate();
        }

        $settings = TrackingSocialSetting::singleton();

        $settings->update(
            collect($data)->only($settings->getFillable())->all()
        );

        TrackingSocialSettings::clearCache();

        Notification::make()
            ->title('Tracking & social settings saved')
            ->success()
            ->send();

        $this->form->fill($settings->fresh()->only($settings->getFillable()));
    }

    /**
     * @return array<string, mixed>
     */
    protected function validationRules(array $data = []): array
    {
        return [
            'google_analytics_measurement_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf(fn () => (bool) ($data['google_analytics_enabled'] ?? false)),
                Rule::when(
                    filled($data['google_analytics_measurement_id'] ?? null),
                    ['regex:/^(G-[A-Z0-9]+|UA-\d+-\d+)$/i']
                ),
            ],
            'google_tag_manager_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf(fn () => (bool) ($data['google_tag_manager_enabled'] ?? false)),
                Rule::when(
                    filled($data['google_tag_manager_id'] ?? null),
                    ['regex:/^GTM-[A-Z0-9]+$/i']
                ),
            ],
            'google_search_console_verification' => ['nullable', 'string', 'max:255'],
            'google_analytics_enabled' => ['boolean'],
            'google_tag_manager_enabled' => ['boolean'],
            'facebook_pixel_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf(fn () => (bool) ($data['facebook_pixel_enabled'] ?? false)),
                Rule::when(
                    filled($data['facebook_pixel_id'] ?? null),
                    ['regex:/^\d+$/']
                ),
            ],
            'facebook_domain_verification' => ['nullable', 'string', 'max:255'],
            'facebook_pixel_enabled' => ['boolean'],
            'tiktok_pixel_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf(fn () => (bool) ($data['tiktok_pixel_enabled'] ?? false)),
            ],
            'linkedin_insight_tag_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf(fn () => (bool) ($data['linkedin_insight_enabled'] ?? false)),
            ],
            'pinterest_tag_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf(fn () => (bool) ($data['pinterest_tag_enabled'] ?? false)),
            ],
            'twitter_pixel_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf(fn () => (bool) ($data['twitter_pixel_enabled'] ?? false)),
            ],
            'snapchat_pixel_id' => [
                'nullable',
                'string',
                'max:50',
                Rule::requiredIf(fn () => (bool) ($data['snapchat_pixel_enabled'] ?? false)),
            ],
            'tiktok_pixel_enabled' => ['boolean'],
            'linkedin_insight_enabled' => ['boolean'],
            'pinterest_tag_enabled' => ['boolean'],
            'twitter_pixel_enabled' => ['boolean'],
            'snapchat_pixel_enabled' => ['boolean'],
            'facebook_url' => ['nullable', 'url', 'max:255'],
            'instagram_url' => ['nullable', 'url', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:255'],
            'twitter_url' => ['nullable', 'url', 'max:255'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'tiktok_url' => ['nullable', 'url', 'max:255'],
            'pinterest_url' => ['nullable', 'url', 'max:255'],
            'whatsapp_number' => ['nullable', 'string', 'max:30', 'regex:/^\+?[0-9\s\-()]+$/'],
            'default_meta_title' => ['nullable', 'string', 'max:255'],
            'default_meta_description' => ['nullable', 'string', 'max:500'],
            'default_meta_keywords' => ['nullable', 'string', 'max:500'],
            'default_og_image_path' => ['nullable', 'string', 'max:255'],
            'default_robots' => ['required', 'string', Rule::in(array_column(PageRobots::cases(), 'value'))],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function normalizePayload(array $data): array
    {
        foreach ($data as $key => $value) {
            if ($value === '') {
                $data[$key] = null;
            }
        }

        $booleanFields = [
            'google_analytics_enabled',
            'google_tag_manager_enabled',
            'facebook_pixel_enabled',
            'tiktok_pixel_enabled',
            'linkedin_insight_enabled',
            'pinterest_tag_enabled',
            'twitter_pixel_enabled',
            'snapchat_pixel_enabled',
        ];

        foreach ($booleanFields as $field) {
            $data[$field] = (bool) ($data[$field] ?? false);
        }

        if (! $data['google_analytics_enabled']) {
            $data['google_analytics_measurement_id'] = null;
        }

        if (! $data['google_tag_manager_enabled']) {
            $data['google_tag_manager_id'] = null;
        }

        if (! $data['facebook_pixel_enabled']) {
            $data['facebook_pixel_id'] = null;
        }

        if (! $data['tiktok_pixel_enabled']) {
            $data['tiktok_pixel_id'] = null;
        }

        if (! $data['linkedin_insight_enabled']) {
            $data['linkedin_insight_tag_id'] = null;
        }

        if (! $data['pinterest_tag_enabled']) {
            $data['pinterest_tag_id'] = null;
        }

        if (! $data['twitter_pixel_enabled']) {
            $data['twitter_pixel_id'] = null;
        }

        if (! $data['snapchat_pixel_enabled']) {
            $data['snapchat_pixel_id'] = null;
        }

        if (isset($data['default_og_image_path']) && is_array($data['default_og_image_path'])) {
            $data['default_og_image_path'] = $data['default_og_image_path'][0] ?? null;
        }

        return $data;
    }
}
