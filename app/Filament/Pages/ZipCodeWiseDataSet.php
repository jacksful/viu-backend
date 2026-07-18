<?php

namespace App\Filament\Pages;

use App\Models\UploadedZipcode;
use App\Models\Zipcode;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection as SupportCollection;
use Illuminate\Support\Facades\Auth;

class ZipCodeWiseDataSet extends Page
{
    protected string $view = 'filament.pages.zip-code-wise-data-set';
    protected static ?string $navigationLabel = 'Zip Code Wise Data Set';
    protected static ?int $navigationSort = 3;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    public ?string $search = '';
    public ?string $statusFilter = '';
    public ?string $monthFilter = '';
    public ?string $yearFilter = '';
    public int $perPage = 5;
    public int $page = 1; // Add page property for Livewire

    public bool $isDatasetModalOpen = false;
    public ?int $viewingDatasetId = null;
    public $datasetRecords = [];

    public static function getNavigationGroup(): ?string
    {
        return 'Market';
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

    public function getZipcodeGroupsProperty(): LengthAwarePaginator
    {
        $query = UploadedZipcode::with(['zipcode', 'datasets'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($this->statusFilter) {
            $query->where('status', $this->statusFilter);
        }
        if ($this->monthFilter) {
            $query->where('month', $this->monthFilter);
        }
        if ($this->yearFilter) {
            $query->where('year', $this->yearFilter);
        }
        if ($this->search) {
            $query->whereHas('zipcode', function ($q) {
                $q->where('code', 'like', "%{$this->search}%")
                    ->orWhere('city', 'like', "%{$this->search}%");
            });
        }

        $uploadedZipcodes = $query->get();

        // Group by zipcode
        $groupedData = $uploadedZipcodes->groupBy('zipcode_id');

        $zipcodeGroups = [];
        foreach ($groupedData as $zipcodeId => $items) {
            $zipcode = $items->first()->zipcode;
            if (!$zipcode) continue;

            $datasets = $items->map(function ($uploadedZipcode) {
                return [
                    'id' => $uploadedZipcode->id,
                    'month' => $uploadedZipcode->month,
                    'year' => $uploadedZipcode->year,
                    'status' => $uploadedZipcode->status,
                    'rows' => $uploadedZipcode->datasets()->count(),
                    'version' => 'v1',
                    'uploaded_at' => $uploadedZipcode->created_at,
                    'csv_file' => $uploadedZipcode->csv_file,
                ];
            });

            $zipcodeGroups[] = [
                'zipcode' => $zipcode,
                'datasets' => $datasets,
                'count' => $datasets->count(),
            ];
        }

        // Convert to collection and paginate
        $collection = new SupportCollection($zipcodeGroups);
        $currentPage = $this->page; // Use Livewire property instead of request
        $items = $collection->slice(($currentPage - 1) * $this->perPage, $this->perPage)->values();

        return new LengthAwarePaginator(
            $items,
            $collection->count(),
            $this->perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
    }

    public function getAllMonthsProperty(): array
    {
        return [
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
    }

    public function getAllStatusesProperty(): array
    {
        return ['draft', 'published'];
    }

    public function getAllYearsProperty(): array
    {
        return UploadedZipcode::distinct()->pluck('year')->sort()->values()->toArray();
    }

    public function goToPage($page)
    {
        $this->page = $page;
    }

    public function nextPage()
    {
        if ($this->page < $this->zipcodeGroups->lastPage()) {
            $this->page++;
        }
    }

    public function previousPage()
    {
        if ($this->page > 1) {
            $this->page--;
        }
    }

    public function updatedSearch()
    {
        $this->page = 1;
    }

    public function updatedStatusFilter()
    {
        $this->page = 1;
    }

    public function updatedMonthFilter()
    {
        $this->page = 1;
    }

    public function updatedYearFilter()
    {
        $this->page = 1;
    }

    public function updatedPerPage()
    {
        $this->page = 1;
    }

    protected function resetPage(): void
    {
        $this->dispatch('$refresh');
    }

    public function viewDataset($id)
    {
        $this->viewingDatasetId = $id;
        $this->loadDatasetRecords();
        $this->isDatasetModalOpen = true;

        // Dispatch browser event to open modal
        $this->dispatch('open-modal', id: 'view-dataset-modal');
    }

    public function loadDatasetRecords()
    {
        if ($this->viewingDatasetId) {
            $uploadedZipcode = UploadedZipcode::with(['zipcode', 'datasets'])->find($this->viewingDatasetId);

            if ($uploadedZipcode) {
                $this->datasetRecords = $uploadedZipcode->datasets()->paginate(50)->items();
            }
        }
    }

    public function closeDatasetModal()
    {
        $this->isDatasetModalOpen = false;
        $this->viewingDatasetId = null;
        $this->datasetRecords = [];
    }

    public function getViewingDatasetProperty()
    {
        if (!$this->viewingDatasetId) {
            return null;
        }

        return UploadedZipcode::with(['zipcode', 'datasets'])->find($this->viewingDatasetId);
    }

    public function downloadDataset($id)
    {
        try {
            $uploadedZipcode = UploadedZipcode::findOrFail($id);
            $filePath = $uploadedZipcode->csv_file;

            if ($filePath && Storage::disk('local')->exists($filePath)) {
                $file = Storage::disk('local')->get($filePath);
                $fileName = basename($filePath);

                return Response::streamDownload(function () use ($file) {
                    echo $file;
                }, $fileName, [
                    'Content-Type' => 'text/csv',
                ]);
            }

            \Filament\Notifications\Notification::make()
                ->title('File not found')
                ->danger()
                ->send();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Failed to download file')
                ->danger()
                ->send();
        }
    }

    public function deleteDataset($id)
    {
        try {
            $uploadedZipcode = UploadedZipcode::findOrFail($id);
            $uploadedZipcode->delete();

            \Filament\Notifications\Notification::make()
                ->title('Dataset deleted successfully')
                ->success()
                ->send();
        } catch (\Exception $e) {
            \Filament\Notifications\Notification::make()
                ->title('Failed to delete dataset')
                ->danger()
                ->send();
        }
    }

    public function downloadAll()
    {
        \Filament\Notifications\Notification::make()
            ->title('Download all feature coming soon')
            ->info()
            ->send();
    }
}
