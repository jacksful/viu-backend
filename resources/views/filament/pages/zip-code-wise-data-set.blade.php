<x-filament-panels::page>
    @php
        $zipcodeGroups = $this->zipcodeGroups;
        $allStatuses = $this->allStatuses;
        $allMonths = $this->allMonths;
        $allYears = $this->allYears;
    @endphp
    <style>
        .fi-section-actions-ctn {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            grid-gap: 10px;
        }
        .fi-section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .flex{
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .fi-section-content-ctn{
            border-top: none;
        }
        .fi-section-content{
            padding: 0 20px 10px;
        }
    </style>
    <x-filament::section>
        {{-- Header Section --}}
        <x-slot name="heading">
            Dataset Management
        </x-slot>

        <x-slot name="description">
            Upload and manage ZIP-based datasets.
        </x-slot>

        <x-slot name="headerActions">
            <x-filament::button
                icon="heroicon-o-arrow-down-tray"
                color="gray"
                wire:click="downloadAll"
            >
                Download All
            </x-filament::button>
            <x-filament::button
                icon="heroicon-o-plus"
                tag="a"
                href="{{ route('filament.admin.resources.datasets.index') }}"
            >
                Upload Dataset
            </x-filament::button>
        </x-slot>

        {{-- Search and Filters Section --}}
        <div class="fi-section-actions-ctn rounded-lg border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/50">
            <div class="flex flex-wrap items-center gap-4" style="display: flex; align-items: center; margin-bottom: 10px; grid-gap: 10px;">
                <div class="flex-1 min-w-[300px]">
                    <x-filament::input.wrapper>
                        <x-filament::input
                            type="search"
                            wire:model.live.debounce.300ms="search"
                            placeholder="Search ZIP, city, or month..."
                        />
                        <x-slot name="suffix">
                            <x-filament::icon
                                icon="heroicon-m-magnifying-glass"
                                class="h-5 w-5 text-gray-400"
                            />
                        </x-slot>
                    </x-filament::input.wrapper>
                </div>
                
                <x-filament::input.wrapper class="w-48">
                    <x-filament::input.select wire:model.live="statusFilter">
                        <option value="">All Status</option>
                        @foreach($allStatuses as $status)
                            <option value="{{ $status }}">{{ ucfirst($status) }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>

                <x-filament::input.wrapper class="w-48">
                    <x-filament::input.select wire:model.live="monthFilter">
                        <option value="">All Months</option>
                        @foreach($allMonths as $monthNum => $monthName)
                            <option value="{{ $monthNum }}">{{ $monthName }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>

                <x-filament::input.wrapper class="w-48">
                    <x-filament::input.select wire:model.live="yearFilter">
                        <option value="">All Years</option>
                        @foreach($allYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </x-filament::input.select>
                </x-filament::input.wrapper>

                <x-filament::input.wrapper class="w-32">
                    <x-filament::input.select wire:model.live="perPage">
                        <option value="5">5 per page</option>
                        <option value="10">10 per page</option>
                        <option value="20">20 per page</option>
                        <option value="50">50 per page</option>
                    </x-filament::input.select>
                </x-filament::input.wrapper>
            </div>
        </div>

        {{-- Dataset Groups by ZIP Code --}}
        <div class="mt-6 space-y-4">
            @forelse($zipcodeGroups as $group)
                @php
                    $zipcode = $group['zipcode'];
                    $datasets = $group['datasets'];
                    $count = $group['count'];
                @endphp

                @if($datasets->count() > 0)
                    <x-filament::section style="margin-bottom: 20px;">
                        <x-slot name="heading">
                            <div class="flex items-center gap-3">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-database w-5 h-5 text-blue-600" aria-hidden="true"><ellipse cx="12" cy="5" rx="9" ry="3"></ellipse><path d="M3 5V19A9 3 0 0 0 21 19V5"></path><path d="M3 12A9 3 0 0 0 21 12"></path></svg>
                                <span>ZIP {{ $zipcode->code }} - {{ $zipcode->city }}, {{ $zipcode->state }}</span>
                                <x-filament::badge color="gray" size="sm">
                                    {{ $datasets->count() }} {{ Str::plural('dataset', $datasets->count()) }}
                                </x-filament::badge>
                            </div>
                        </x-slot>

                        {{-- Dataset Entries --}}
                        <div class="space-y-3">
                            @foreach($datasets as $dataset)
                                <div class="fi-section rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-900" style="margin: 10px 0;">
                                    <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between" style="justify-content: space-between; padding: 10px;">
                                        <div class="flex-1" style="width: 70%;">
                                            <div class="mb-3 flex items-center gap-3">
                                                <h3 class="text-lg font-semibold text-gray-950 dark:text-white">
                                                    {{ $allMonths[$dataset['month']] ?? $dataset['month'] }} {{ $dataset['year'] }}
                                                </h3>
                                                <x-filament::badge
                                                    :color="$dataset['status'] === 'published' ? 'success' : 'warning'"
                                                    icon="heroicon-o-check-circle"
                                                >
                                                    {{ ucfirst($dataset['status']) }}
                                                </x-filament::badge>
                                            </div>
                                            
                                            <div class="grid grid-cols-2 gap-4 text-sm text-gray-600 dark:text-gray-400 md:grid-cols-4" style="display: flex ; align-items: center; justify-content: space-between; font-size: 14px; margin-top: 10px;">
                                                <div>
                                                    <span class="font-medium text-gray-950 dark:text-white">Rows:</span> {{ number_format($dataset['rows']) }}
                                                </div>
                                                <div>
                                                    <span class="font-medium text-gray-950 dark:text-white">Version:</span> {{ $dataset['version'] }}
                                                </div>
                                                <div>
                                                    <span class="font-medium text-gray-950 dark:text-white">Uploaded:</span> {{ $dataset['uploaded_at']->format('m/d/Y') }}
                                                </div>
                                                <div>
                                                    <span class="font-medium text-gray-950 dark:text-white">By:</span> Admin User
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Action Buttons --}}
                                        <div class="flex flex-shrink-0 items-center gap-2">
                                            <x-filament::button
                                                size="sm"
                                                color="gray"
                                                icon="heroicon-o-eye"
                                                wire:click="viewDataset({{ $dataset['id'] }})"
                                                wire:loading.attr="disabled"
                                            >
                                                View
                                            </x-filament::button>
                                            <x-filament::button
                                                size="sm"
                                                color="gray"
                                                icon="heroicon-o-arrow-down-tray"
                                                wire:click="downloadDataset({{ $dataset['id'] }})"
                                            >
                                                Download
                                            </x-filament::button>
                                            <x-filament::button
                                                size="sm"
                                                color="danger"
                                                icon="heroicon-o-trash"
                                                wire:click="deleteDataset({{ $dataset['id'] }})"
                                                wire:confirm="Are you sure you want to delete this dataset?"
                                                tooltip="Delete"
                                            >
                                            </x-filament::button>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-filament::section>
                @endif
            @empty
                <x-filament::empty-state
                    icon="heroicon-o-document-duplicate"
                    heading="No datasets found"
                    description="Get started by uploading a new dataset."
                />
            @endforelse
        </div>

        {{-- Pagination --}}
        @if($zipcodeGroups->hasPages())
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-gray-700 dark:text-gray-300">
                    Showing {{ $zipcodeGroups->firstItem() }} to {{ $zipcodeGroups->lastItem() }} of {{ $zipcodeGroups->total() }} results
                </div>
                <div class="flex items-center gap-2">
                    {{-- Previous Button --}}
                    <x-filament::button
                        size="sm"
                        color="gray"
                        icon="heroicon-o-chevron-left"
                        wire:click="previousPage"
                        :disabled="$zipcodeGroups->onFirstPage()"
                    >
                        
                    </x-filament::button>

                    {{-- Page Numbers --}}
                    @foreach($zipcodeGroups->getUrlRange(1, $zipcodeGroups->lastPage()) as $page => $url)
                        @if($page == $zipcodeGroups->currentPage())
                            <x-filament::button
                                size="sm"
                                color="primary"
                            >
                                {{ $page }}
                            </x-filament::button>
                        @else
                            <x-filament::button
                                size="sm"
                                color="gray"
                                wire:click="goToPage({{ $page }})"
                            >
                                {{ $page }}
                            </x-filament::button>
                        @endif
                    @endforeach

                    {{-- Next Button --}}
                    <x-filament::button
                        size="sm"
                        color="gray"
                        icon="heroicon-o-chevron-right"
                        icon-position="after"
                        wire:click="nextPage"
                        :disabled="!$zipcodeGroups->hasMorePages()"
                    >
                        
                    </x-filament::button>
                </div>
            </div>
        @endif
    </x-filament::section>

    {{-- Dataset View Modal --}}
    <x-filament::modal 
        id="view-dataset-modal" 
        wire:model="isDatasetModalOpen"
        width="7xl"
    >
        @php
            $uploadedZipcode = $this->viewingDataset;
        @endphp

        @if($uploadedZipcode)
            @php
                $zipcode = $uploadedZipcode->zipcode;
                $monthName = $allMonths[$uploadedZipcode->month] ?? $uploadedZipcode->month ?? '';
            @endphp

            <x-slot name="heading">
                Dataset Details - ZIP {{ $zipcode->code }} - {{ $monthName }} {{ $uploadedZipcode->year }}
            </x-slot>

            <x-slot name="description">
                {{ $zipcode->city }}, {{ $zipcode->state }}
            </x-slot>

            <div class="space-y-4">
                {{-- Dataset Summary --}}
                <x-filament::section>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Total Records</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ number_format($uploadedZipcode->datasets()->count()) }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Status</div>
                            <div class="mt-1">
                                <x-filament::badge
                                    :color="$uploadedZipcode->status === 'published' ? 'success' : 'warning'"
                                >
                                    {{ ucfirst($uploadedZipcode->status) }}
                                </x-filament::badge>
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Uploaded</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                                {{ $uploadedZipcode->created_at->format('m/d/Y') }}
                            </div>
                        </div>
                        <div>
                            <div class="text-xs font-medium text-gray-500 dark:text-gray-400">Version</div>
                            <div class="text-lg font-semibold text-gray-900 dark:text-white">v1</div>
                        </div>
                    </div>
                </x-filament::section>

                {{-- Dataset Records Table --}}
                <x-filament::section>
                    <x-slot name="heading">
                        Dataset Records
                    </x-slot>

                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Property ID
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Address
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Tax Value
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Bedrooms
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Baths
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Sq Ft
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($datasetRecords as $record)
                                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 dark:text-white">
                                            {{ $record->propertyid ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white">
                                            {{ $record->address ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            {{ $record->restype ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            @if($record->tax_value)
                                                ${{ number_format($record->tax_value) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            {{ $record->bedrooms ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            {{ $record->fullbaths ?? '-' }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                            @if($record->sfla)
                                                {{ number_format($record->sfla) }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                            No records found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </x-filament::section>
            </div>

            <x-slot name="footer">
                <x-filament::button
                    color="gray"
                    wire:click="closeDatasetModal"
                >
                    Close
                </x-filament::button>
            </x-slot>
        @endif
    </x-filament::modal>
</x-filament-panels::page>


