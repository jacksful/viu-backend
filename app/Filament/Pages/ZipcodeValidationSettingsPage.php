<?php

namespace App\Filament\Pages;

use App\Support\ZipcodeValidationSettings;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Validator;

class ZipcodeValidationSettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationLabel = 'Zipcode Validation';

    protected static ?string $title = 'Zipcode Validation Settings';

    protected static ?int $navigationSort = 5;

    protected static string|\UnitEnum|null $navigationGroup = 'Settings';

    protected static ?string $slug = 'zipcode-validation-settings';

    protected string $view = 'filament.pages.zipcode-validation-settings';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'api_base_url' => ZipcodeValidationSettings::apiBaseUrl(),
        ]);
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Third-Party ZIP Validation API')
                    ->description('Used when checking whether a submitted ZIP code is valid before availability lookup.')
                    ->schema([
                        TextInput::make('api_base_url')
                            ->label('API base URL')
                            ->helperText('The ZIP code is appended to this URL. Example: https://api.zippopotam.us/us/90210')
                            ->url()
                            ->maxLength(500)
                            ->placeholder(ZipcodeValidationSettings::DEFAULT_API_BASE_URL)
                            ->required(),
                    ]),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Validator::make($data, [
            'api_base_url' => ['required', 'url', 'max:500'],
        ])->validate();

        ZipcodeValidationSettings::setApiBaseUrl($data['api_base_url']);

        Notification::make()
            ->title('Zipcode validation settings saved')
            ->success()
            ->send();

        $this->form->fill([
            'api_base_url' => ZipcodeValidationSettings::apiBaseUrl(),
        ]);
    }
}
