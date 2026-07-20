<?php

namespace App\Filament\Pages;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use SensitiveParameter;

class EditProfile extends BaseEditProfile
{
    protected static ?string $title = 'Edit Profile';

    public static function isSimple(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                SchemaComponents\Section::make('Personal Information')
                    ->description('Update your personal details.')
                    ->schema([
                        Components\TextInput::make('first_name')
                            ->label('First Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter first name'),

                        Components\TextInput::make('last_name')
                            ->label('Last Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Enter last name'),

                        $this->getEmailFormComponent(),

                        Components\TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(255)
                            ->placeholder('Enter phone number'),
                    ])
                    ->columns(2),

                SchemaComponents\Section::make('Address')
                    ->schema([
                        Components\TextInput::make('address')
                            ->label('Address')
                            ->maxLength(255)
                            ->placeholder('Enter street address')
                            ->columnSpanFull(),

                        Components\TextInput::make('city')
                            ->label('City')
                            ->maxLength(255)
                            ->placeholder('Enter city'),

                        Components\TextInput::make('state')
                            ->label('State')
                            ->maxLength(255)
                            ->placeholder('Enter state'),

                        Components\TextInput::make('zip')
                            ->label('ZIP Code')
                            ->maxLength(255)
                            ->placeholder('Enter ZIP code'),

                        Components\TextInput::make('country')
                            ->label('Country')
                            ->maxLength(255)
                            ->default('USA')
                            ->placeholder('Enter country'),
                    ])
                    ->columns(2)
                    ->collapsible(),

                SchemaComponents\Section::make('Profile Photo')
                    ->schema([
                        Components\FileUpload::make('profile_photo_path')
                            ->label('Photo')
                            ->image()
                            ->disk('public')
                            ->directory('profile-photos')
                            ->visibility('public')
                            ->imageEditor()
                            ->maxSize(2048)
                            ->helperText('JPEG, PNG, or GIF. Max 2 MB.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(#[SensitiveParameter] array $data): array
    {
        $data['name'] = trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, #[SensitiveParameter] array $data): Model
    {
        if (
            array_key_exists('profile_photo_path', $data)
            && $record->profile_photo_path
            && $record->profile_photo_path !== $data['profile_photo_path']
            && Storage::disk('public')->exists($record->profile_photo_path)
        ) {
            Storage::disk('public')->delete($record->profile_photo_path);
        }

        return parent::handleRecordUpdate($record, $data);
    }

    public function getTitle(): string
    {
        return 'Edit Profile';
    }

    public function getHeading(): string
    {
        return 'Edit Profile';
    }

    public function getMultiFactorAuthenticationContentComponent(): ?Component
    {
        return null;
    }
}
