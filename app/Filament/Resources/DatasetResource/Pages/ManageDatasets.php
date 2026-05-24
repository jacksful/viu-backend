<?php

namespace App\Filament\Resources\DatasetResource\Pages;

use App\Filament\Resources\DatasetResource;
use App\Models\Dataset;
use App\Models\UploadedZipcode;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Grid;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ManageDatasets extends ManageRecords
{
    protected static string $resource = DatasetResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->modalHeading('Upload Dataset')
                ->modalSubheading('Select ZIP code and upload CSV file')
                ->modalWidth('3xl')
                ->modalSubmitActionLabel('Validate & Preview')
                ->modalCancelActionLabel('Cancel')
                ->form([
                    Grid::make(2)
                        ->schema([
                            \Filament\Forms\Components\Select::make('zipcode_id')
                                ->label('ZIP Code')
                                ->options(function () {
                                    return \App\Models\Zipcode::query()
                                        ->get()
                                        ->mapWithKeys(fn($zipcode) => [$zipcode->id => "ZIP {$zipcode->code} - {$zipcode->city}, {$zipcode->state}"])
                                        ->toArray();
                                })
                                ->searchable()
                                ->preload()
                                ->required()
                                ->reactive()
                                ->afterStateUpdated(function ($state, callable $set) {
                                    if ($state) {
                                        $zipcode = \App\Models\Zipcode::find($state);
                                        if ($zipcode) {
                                            $set('city', $zipcode->city);
                                            $set('state', $zipcode->state);
                                        }
                                    }
                                })
                                ->placeholder('Select ZIP code'),

                            \Filament\Forms\Components\TextInput::make('city')
                                ->label('City')
                                ->disabled()
                                ->dehydrated(false),

                            \Filament\Forms\Components\TextInput::make('state')
                                ->label('State')
                                ->disabled()
                                ->dehydrated(false),

                            \Filament\Forms\Components\Select::make('month')
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

                            \Filament\Forms\Components\Select::make('year')
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

                            \Filament\Forms\Components\FileUpload::make('csv_file')
                                ->label('CSV File')
                                ->required()
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
                                ->maxSize(102400)
                                ->helperText('CSV files only (max 100MB)'),

                            \Filament\Forms\Components\Select::make('status')
                                ->label('Status')
                                ->options([
                                    'draft' => 'Draft',
                                    'published' => 'Published',
                                ])
                                ->default('draft')
                                ->required(),
                        ]),
                ])

                ->using(function (array $data): Dataset {
                    try {
                        Log::info('CSV Upload - Form data received', ['data_keys' => array_keys($data)]);

                        $uploadedZipcode = UploadedZipcode::where([
                            'zipcode_id' => $data['zipcode_id'] ?? null,
                            'month' => $data['month'] ?? null,
                            'year' => $data['year'] ?? null
                        ])->first();

                        if ($uploadedZipcode) {
                            throw new \Exception('Uploaded ZIP code already exists for the selected month and year');
                        }

                        // Process CSV file and create records for each row
                        if (isset($data['csv_file']) && !empty($data['csv_file'])) {
                            // Handle array or string file path
                            $csvFilePath = is_array($data['csv_file'])
                                ? (is_array($data['csv_file'][0] ?? null)
                                    ? ($data['csv_file'][0]['path'] ?? $data['csv_file'][0] ?? null)
                                    : ($data['csv_file'][0] ?? null))
                                : $data['csv_file'];

                            Log::info('CSV Upload - File path extracted', ['path' => $csvFilePath]);



                            if ($csvFilePath) {
                                return $this->importCsvData($csvFilePath, $data);
                            } else {
                                throw new \Exception('CSV file path is empty or invalid');
                            }
                        }

                        throw new \Exception('No CSV file provided');
                    } catch (\Exception $e) {
                        Log::error('CSV Import Error: ' . $e->getMessage(), [
                            'trace' => $e->getTraceAsString(),
                            'data' => $data,
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ]);
                        throw new \Exception('Failed to import CSV: ' . $e->getMessage());
                    }
                }),
        ];
    }

    protected function importCsvData(string $csvFilePath, array $formData): Dataset
    {
        try {
            Log::info('CSV Import - Starting', ['file_path' => $csvFilePath]);

            // Try to get the file path - handle both relative and absolute paths
            $fullPath = null;

            // Remove any query parameters or fragments
            $csvFilePath = preg_replace('/\?.*$/', '', $csvFilePath);
            $csvFilePath = preg_replace('/#.*$/', '', $csvFilePath);

            // Check if it's already a full path
            if (file_exists($csvFilePath)) {
                $fullPath = $csvFilePath;
                Log::info('CSV Import - Found as full path', ['path' => $fullPath]);
            }
            // Try storage path (relative path from storage root)
            elseif (Storage::disk('local')->exists($csvFilePath)) {
                $fullPath = Storage::disk('local')->path($csvFilePath);
                Log::info('CSV Import - Found in local disk', ['path' => $fullPath]);
            }
            // Try public disk
            elseif (Storage::disk('public')->exists($csvFilePath)) {
                $fullPath = Storage::disk('public')->path($csvFilePath);
                Log::info('CSV Import - Found in public disk', ['path' => $fullPath]);
            }
            // Try with datasets/csv prefix
            elseif (Storage::disk('local')->exists('datasets/csv/' . basename($csvFilePath))) {
                $fullPath = Storage::disk('local')->path('datasets/csv/' . basename($csvFilePath));
                Log::info('CSV Import - Found with prefix', ['path' => $fullPath]);
            }
            // Try direct file path variations
            else {
                $possiblePaths = [
                    storage_path('app/private/' . $csvFilePath),
                    storage_path('app/private/datasets/csv/' . basename($csvFilePath)),
                    storage_path('app/public/' . $csvFilePath),
                    storage_path('app/public/datasets/csv/' . basename($csvFilePath)),
                    // Try Livewire temporary upload path
                    storage_path('app/livewire-tmp/' . basename($csvFilePath)),
                ];

                foreach ($possiblePaths as $path) {
                    if (file_exists($path)) {
                        $fullPath = $path;
                        Log::info('CSV Import - Found in possible paths', ['path' => $fullPath]);
                        break;
                    }
                }
            }

            if (!$fullPath || !file_exists($fullPath)) {
                Log::error('CSV Import - File not found', [
                    'original_path' => $csvFilePath,
                    'checked_paths' => $possiblePaths ?? []
                ]);
                throw new \Exception('CSV file not found. Path: ' . $csvFilePath . '. Please ensure the file was uploaded successfully.');
            }

            $handle = fopen($fullPath, 'r');
            if ($handle === false) {
                throw new \Exception('Could not open CSV file at: ' . $fullPath);
            }

            // Read header row
            $headers = fgetcsv($handle);
            if ($headers === false || empty($headers)) {
                fclose($handle);
                throw new \Exception('CSV file is empty or invalid. Please check the file format.');
            }

            // Normalize headers (trim, lowercase, replace spaces with underscores)
            $headers = array_map(function ($header) {
                return strtolower(trim(str_replace(' ', '_', $header)));
            }, $headers);

            // Map CSV columns to database fields
            $fieldMapping = [
                'propertyid' => 'propertyid',
                'restype' => 'restype',
                'tax_value' => 'tax_value',
                'address' => 'address',
                'times_sold' => 'times_sold',
                'day_since_sold' => 'day_since_sold',
                'last_date_sold' => 'last_date_sold',
                'township' => 'township',
                'style' => 'style',
                'yearbuilt' => 'yearbuilt',
                'extwallfinish_desc' => 'extwallfinish_desc',
                'rooftype_desc' => 'rooftype_desc',
                'roofmaterial_desc' => 'roofmaterial_desc',
                'basement_desc' => 'basement_desc',
                'hctype' => 'hctype',
                'hcfueltype_desc' => 'hcfueltype_desc',
                'hcsystemtype_desc' => 'hcsystemtype_desc',
                'bedrooms' => 'bedrooms',
                'fullbaths' => 'fullbaths',
                'sfla' => 'sfla',
                'phycondition' => 'phycondition',
                'utility' => 'utility',
                'propdesirability' => 'propdesirability',
                'locdesirability' => 'locdesirability',
                'status' => 'status',
                'predicted_status' => 'predicted_status',
                'correct_status' => 'correct_status',
                'status_probability' => 'status_probability',
            ];

            // Find column indices
            $columnIndices = [];
            foreach ($fieldMapping as $dbField => $csvField) {
                $index = array_search($csvField, $headers);
                if ($index !== false) {
                    $columnIndices[$dbField] = $index;
                }
            }

            // Create or find the UploadedZipcode record
            $uploadedZipcode = UploadedZipcode::where([
                'zipcode_id' => $formData['zipcode_id'] ?? null,
                'month' => $formData['month'] ?? null,
                'year' => $formData['year'] ?? null
            ])->first();

            if (!$uploadedZipcode) {
                $uploadedZipcode = UploadedZipcode::create([
                    'zipcode_id' => $formData['zipcode_id'] ?? null,
                    'month' => $formData['month'] ?? null,
                    'year' => $formData['year'] ?? null,
                    'csv_file' => $csvFilePath,
                    'status' => $formData['status'] ?? 'draft',
                ]);
            }
            // If uploaded zipcode already exists, continue with the import using the existing record
            // No need to return early - just use the existing $uploadedZipcode


            // Get fillable fields once to avoid repeated calls
            $fillableFields = (new Dataset)->getFillable();

            // Initialize all fillable fields with null for consistent batch inserts
            $emptyRecord = array_fill_keys($fillableFields, null);
            $emptyRecord['uploaded_zipcode_id'] = $uploadedZipcode->id;

            $records = [];

            // Read data rows
            while (($row = fgetcsv($handle)) !== false) {
                // Skip empty rows
                if (empty(array_filter($row))) {
                    continue;
                }

                // Start with empty record template
                $recordData = $emptyRecord;

                // Map CSV columns to record data
                foreach ($columnIndices as $dbField => $columnIndex) {
                    // Only process fields that are fillable
                    if (!in_array($dbField, $fillableFields)) {
                        continue;
                    }

                    if (isset($row[$columnIndex]) && $row[$columnIndex] !== '' && trim($row[$columnIndex]) !== '?') {
                        $value = trim($row[$columnIndex]);
                        // Skip question marks and empty values
                        if ($value !== '?' && $value !== '') {
                            $recordData[$dbField] = $value;
                        }
                    }
                }

                // Ensure we only have fillable fields (should already be the case, but double-check)
                $recordData = array_intersect_key($recordData, array_flip($fillableFields));

                // Ensure uploaded_zipcode_id is set
                $recordData['uploaded_zipcode_id'] = $uploadedZipcode->id;

                $records[] = $recordData;

                // Insert in batches of 500 for better performance
                if (count($records) >= 500) {
                    Dataset::insert($records);
                    $records = [];
                }
            }

            // Insert remaining records
            if (!empty($records)) {
                Dataset::insert($records);
            }

            fclose($handle);

            // Return the first created record (or create a placeholder)
            $firstRecord = Dataset::where('uploaded_zipcode_id', $uploadedZipcode->id)->first();

            if (!$firstRecord) {
                // Create a summary record if no records were created
                $firstRecord = Dataset::create([
                    'uploaded_zipcode_id' => $uploadedZipcode->id,
                ]);
            }

            // Send notifications to subscribed users after dataset import is complete
            try {
                $uploadedZipcode->notifySubscribedUsers();
            } catch (\Exception $e) {
                // Log error but don't fail the import
                Log::warning('Failed to send dataset notifications', [
                    'error' => $e->getMessage(),
                    'uploaded_zipcode_id' => $uploadedZipcode->id,
                ]);
            }

            return $firstRecord;
        } catch (\Exception $e) {
            if (isset($handle) && is_resource($handle)) {
                fclose($handle);
            }
            Log::error('CSV Import Failed', [
                'error' => $e->getMessage(),
                'file_path' => $csvFilePath ?? 'unknown',
                'form_data' => $formData
            ]);
            throw $e;
        }
    }

    protected function parseDate(string $dateString): ?\DateTime
    {
        $formats = [
            'Y-m-d',
            'm/d/Y',
            'd/m/Y',
            'Y/m/d',
            'm-d-Y',
            'd-m-Y',
        ];

        foreach ($formats as $format) {
            try {
                $date = \DateTime::createFromFormat($format, $dateString);
                if ($date !== false) {
                    return $date;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try strtotime as fallback
        $timestamp = strtotime($dateString);
        if ($timestamp !== false) {
            $date = new \DateTime();
            $date->setTimestamp($timestamp);
            return $date;
        }

        return null;
    }
}
