<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UploadedZipcodeResource\Pages;
use App\Models\UploadedZipcode;
use Filament\Actions;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Components as SchemaComponents;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class UploadedZipcodeResource extends Resource
{
    protected static ?string $model = UploadedZipcode::class;

    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $navigationLabel = 'Uploaded ZIP Codes';

    protected static ?int $navigationSort = 1;

    public static function getNavigationIcon(): ?string
    {
        return 'heroicon-o-folder-arrow-down';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Market';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->schema([
                SchemaComponents\Section::make('Upload Information')
                    ->schema([
                        Components\Select::make('zipcode_id')
                            ->label('ZIP Code')
                            ->relationship('zipcode', 'code')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->placeholder('Select ZIP code'),

                        Components\Select::make('month')
                            ->label('Month')
                            ->required()
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
                            ])
                            ->default(now()->month)
                            ->placeholder('Select month'),

                        Components\Select::make('year')
                            ->label('Year')
                            ->required()
                            ->options(function () {
                                $years = [];
                                $currentYear = now()->year;
                                for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
                                    $years[$i] = $i;
                                }
                                return $years;
                            })
                            ->default(now()->year)
                            ->placeholder('Select year'),

                        Components\FileUpload::make('csv_file')
                            ->label('CSV File')
                            ->acceptedFileTypes([
                                'text/csv',
                                'text/comma-separated-values',
                                'application/csv',
                                'application/vnd.ms-excel',
                                'text/plain',
                                '.csv',
                            ])
                            ->disk('local')
                            ->directory('datasets/csv')
                            ->visibility('private')
                            ->maxSize(10240)
                            ->helperText('CSV files only (max 10MB)')
                            ->downloadable()
                            ->previewable()
                            ->deletable(),

                        Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'published' => 'Published',
                            ])
                            ->default('draft')
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
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

        return $table
            ->columns([
                Tables\Columns\TextColumn::make('zipcode.code')
                    ->label('ZIP Code')
                    ->searchable()
                    ->sortable()
                    ->formatStateUsing(fn($record) => $record->zipcode ? "ZIP {$record->zipcode->code}" : '-'),

                Tables\Columns\TextColumn::make('location')
                    ->label('Location')
                    ->getStateUsing(function ($record) {
                        $zipcode = $record->zipcode;
                        $city = $zipcode->city ?? '';
                        $state = $zipcode->state ?? '';
                        return $city && $state ? "{$city}, {$state}" : '-';
                    })
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        return $query->whereHas('zipcode', function ($q) use ($search) {
                            $q->where('city', 'like', "%{$search}%")
                                ->orWhere('state', 'like', "%{$search}%");
                        });
                    }),

                Tables\Columns\TextColumn::make('month_year')
                    ->label('Month/Year')
                    ->getStateUsing(function ($record) use ($monthNames) {
                        $monthName = $monthNames[$record->month] ?? $record->month;
                        return "{$monthName} {$record->year}";
                    })
                    ->searchable()
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->orderBy('year', $direction)
                            ->orderBy('month', $direction);
                    }),

                Tables\Columns\TextColumn::make('datasets_count')
                    ->label('Datasets Count')
                    ->counts('datasets')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success' => 'published',
                        'warning' => 'draft',
                    ])
                    ->formatStateUsing(fn(string $state): string => ucfirst($state))
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime('m/d/Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                    ]),

                Tables\Filters\SelectFilter::make('month')
                    ->label('Month')
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

                Tables\Filters\SelectFilter::make('year')
                    ->label('Year')
                    ->options(function () {
                        $years = [];
                        $currentYear = now()->year;
                        for ($i = $currentYear - 5; $i <= $currentYear + 5; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    }),

                Tables\Filters\SelectFilter::make('zipcode_id')
                    ->label('ZIP Code')
                    ->relationship('zipcode', 'code')
                    ->searchable()
                    ->preload(),
            ])
            ->actions([
                Actions\ActionGroup::make([
                    Actions\Action::make('view_datasets')
                    ->label('View Datasets')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(
                        fn($record) =>
                        "Datasets for ZIP {$record->zipcode->code} - {$monthNames[$record->month]} {$record->year}"
                    )
                    ->modalWidth('7xl')
                    ->modalContent(function ($record) {
                        return view('filament.resources.uploaded-zipcode-resource.datasets-table', [
                            'datasets' => $record->datasets()->paginate(50),
                            'uploadedZipcode' => $record,
                        ]);
                    })
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),

                Actions\Action::make('download')
                    ->label('Download CSV')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->action(function ($record) {
                        $filePath = $record->csv_file;
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
                    ->visible(fn($record) => !empty($record->csv_file)),

                Actions\EditAction::make()
                    ->modalHeading('Edit Uploaded ZIP Code')
                    ->modalWidth('5xl'),

                Actions\DeleteAction::make()
                    ->requiresConfirmation()
                    ->modalHeading('Delete uploaded zipcode')
                    ->modalDescription('Are you sure you want to delete this uploaded zipcode? This action cannot be undone.'),
                ]),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->modalHeading('Delete selected uploads')
                        ->modalDescription('Are you sure you want to delete the selected uploaded zipcodes? This action cannot be undone.'),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->groups([
                Tables\Grouping\Group::make('zipcode.code')
                    ->label('ZIP Code')
                    ->collapsible(),
                Tables\Grouping\Group::make('month')
                    ->label('Month')
                    ->collapsible()
                    ->getTitleFromRecordUsing(function ($record) use ($monthNames) {
                        return $monthNames[$record->month] ?? "Month {$record->month}";
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    // public static function getPages(): array
    // {
    //     return [
    //         'index' => Pages\ManageUploadedZipcodes::route(''),
    //     ];
    // }
}
