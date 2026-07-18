<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DatasetResource\Pages;
use App\Models\Dataset;
use App\Models\Zipcode;
use App\Models\UploadedZipcode;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class DatasetResource extends Resource
{
  protected static ?string $model = Dataset::class;

  protected static ?string $navigationLabel = 'Datasets';

  protected static ?int $navigationSort = 2;

  public static function getNavigationIcon(): ?string
  {
    return 'heroicon-o-document-duplicate';
  }

  public static function getNavigationGroup(): ?string
  {
    return 'Market';
  }

  public static function form(Schema $schema): Schema
  {
    return $schema
      ->schema([
        SchemaComponents\Section::make('Dataset Information')
          ->schema([
            Components\Select::make('uploaded_zipcode_id')
              ->label('Uploaded ZIP Code')
              ->relationship('uploadedZipcode', 'id', fn($query) => $query->with('zipcode'))
              ->getOptionLabelFromRecordUsing(function ($record) {
                $zipcode = $record->zipcode;
                $monthNames = [
                  1 => 'January',
                  2 => 'February',
                  3 => 'March',
                  4 => 'April',
                  5 => 'May',
                  6 => 'June',
                  7 => 'July',
                  8 => 'August',
                  9 => 'September',
                  10 => 'October',
                  11 => 'November',
                  12 => 'December',
                ];
                $monthName = $monthNames[$record->month] ?? $record->month;

                if ($zipcode) {
                  return "ZIP {$zipcode->code} - {$zipcode->city}, {$zipcode->state} ({$monthName} {$record->year})";
                }
                return "ID: {$record->id} ({$monthName} {$record->year})";
              })
              ->searchable()
              ->preload()
              ->required()
              ->placeholder('Select uploaded ZIP code'),
          ]),
      ]);
  }

  public static function table(Table $table): Table
  {
    return $table
      ->columns([
        Tables\Columns\TextColumn::make('uploadedZipcode.zipcode.code')
          ->label('ZIP Code')
          ->searchable()
          ->sortable()
          ->formatStateUsing(fn($record) => $record->uploadedZipcode?->zipcode ? "ZIP {$record->uploadedZipcode->zipcode->code}" : '-'),

        Tables\Columns\TextColumn::make('location')
          ->label('Location')
          ->getStateUsing(function ($record) {
            $zipcode = $record->uploadedZipcode?->zipcode;
            $city = $zipcode->city ?? '';
            $state = $zipcode->state ?? '';
            return $city && $state ? "{$city}, {$state}" : '-';
          })
          ->searchable(query: function (Builder $query, string $search): Builder {
            return $query->whereHas('uploadedZipcode.zipcode', function ($q) use ($search) {
              $q->where('city', 'like', "%{$search}%")
                ->orWhere('state', 'like', "%{$search}%");
            });
          }),

        Tables\Columns\TextColumn::make('month_year')
          ->label('Month/Year')
          ->getStateUsing(function ($record) {
            $uploadedZipcode = $record->uploadedZipcode;
            if (!$uploadedZipcode) return '-';

            $months = [
              1 => 'January',
              2 => 'February',
              3 => 'March',
              4 => 'April',
              5 => 'May',
              6 => 'June',
              7 => 'July',
              8 => 'August',
              9 => 'September',
              10 => 'October',
              11 => 'November',
              12 => 'December',
            ];
            $month = $months[$uploadedZipcode->month] ?? '';
            return "{$month} {$uploadedZipcode->year}";
          })
          ->searchable()
          ->sortable(query: function (Builder $query, string $direction): Builder {
            return $query->join('uploaded_zipcodes', 'datasets.uploaded_zipcode_id', '=', 'uploaded_zipcodes.id')
              ->orderBy('uploaded_zipcodes.year', $direction)
              ->orderBy('uploaded_zipcodes.month', $direction)
              ->select('datasets.*');
          }),

        Tables\Columns\BadgeColumn::make('uploadedZipcode.status')
          ->label('Status')
          ->colors([
            'success' => 'published',
            'warning' => 'draft',
          ])
          ->formatStateUsing(fn(?string $state): string => $state ? ucfirst($state) : '-')
          ->sortable(),

        Tables\Columns\TextColumn::make('created_at')
          ->label('Created')
          ->dateTime('m/d/Y')
          ->sortable(),
      ])
      ->filters([
        Tables\Filters\SelectFilter::make('uploadedZipcode.status')
          ->label('Status')
          ->relationship('uploadedZipcode', 'status')
          ->options([
            'draft' => 'Draft',
            'published' => 'Published',
          ]),

        Tables\Filters\SelectFilter::make('uploadedZipcode.month')
          ->label('Month')
          ->relationship('uploadedZipcode', 'month')
          ->options([
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December',
          ]),

        Tables\Filters\SelectFilter::make('uploadedZipcode.year')
          ->label('Year')
          ->relationship('uploadedZipcode', 'year')
          ->options(function () {
            $years = [];
            $currentYear = now()->year;
            for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
              $years[$i] = $i;
            }
            return $years;
          }),

        Tables\Filters\SelectFilter::make('uploadedZipcode.zipcode_id')
          ->label('ZIP Code')
          ->relationship('uploadedZipcode.zipcode', 'code')
          ->searchable()
          ->preload(),
      ])
      ->actions([
        Actions\ActionGroup::make([
          Actions\ViewAction::make()
          ->icon('heroicon-o-eye'),
        Actions\Action::make('download')
          ->label('Download')
          ->icon('heroicon-o-arrow-down-tray')
          ->action(function ($record) {
            $filePath = $record->uploadedZipcode?->csv_file;
            if ($filePath && Storage::disk('local')->exists($filePath)) {
              $file = Storage::disk('local')->get($filePath);
              $fileName = basename($filePath);

              return Response::streamDownload(function () use ($file) {
                echo $file;
              }, $fileName, [
                'Content-Type' => 'text/csv',
              ]);
            }
          })
          ->visible(fn($record) => !empty($record->uploadedZipcode?->csv_file)),
        Actions\EditAction::make()
          ->modalHeading('Edit Dataset')
          ->modalWidth('5xl'),
        Actions\DeleteAction::make()
          ->requiresConfirmation()
          ->modalHeading('Delete dataset')
          ->modalDescription('Are you sure you want to delete this dataset? This action cannot be undone.'),
        ]),
      ])
      ->bulkActions([
        Actions\BulkActionGroup::make([
          Actions\DeleteBulkAction::make()
            ->requiresConfirmation()
            ->modalHeading('Delete selected datasets')
            ->modalDescription('Are you sure you want to delete the selected datasets? This action cannot be undone.'),
        ]),
      ])
      ->defaultSort('created_at', 'desc')
      ->groups([
        Tables\Grouping\Group::make('uploadedZipcode.zipcode.code')
          ->label('ZIP Code')
          ->collapsible(),
      ]);
  }

  public static function getRelations(): array
  {
    return [
      //
    ];
  }

  public static function getPages(): array
  {
    return [
      'index' => Pages\ManageDatasets::route(''),
    ];
  }

  public static function canAccess(): bool
  {
    return match (Auth::user()->role) {
      'admin' => true,
      'super_admin' => true,
      'customer' => false,
      default => false,
    };
  }
}
