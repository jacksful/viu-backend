<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Client Portal</title>
   
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50" x-data="{ profileModalOpen: false }" @open-profile-modal.window="profileModalOpen = true" @close-profile-modal.window="profileModalOpen = false">
    @include('customer.partials.header')
    @include('customer.partials.profile-modal')
    @include('customer.partials.password-modal')
    @include('customer.partials.subscription-modal')
    @include('customer.partials.feedback-modal')

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Key Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Properties</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($totalProperties) }}</p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-home text-blue-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Average Accuracy</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($averageAccuracy, 1) }}%</p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-bullseye text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Predictive Score</p>
                        <p class="text-2xl font-bold text-gray-900 mt-1">{{ number_format($predictiveScore, 1) }}%</p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                        <i class="fas fa-chart-line text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Filters</h2>
            <form method="GET" action="{{ route('user.dashboard') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4 align-bottom items-end">
                {{-- <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ZIP Code</label>
                    <select name="zipcode" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All ZIP Codes</option>
                        @foreach($availableZipcodes as $zipcode)
                            <option value="{{ $zipcode['code'] }}" {{ request('zipcode') == $zipcode['code'] ? 'selected' : '' }}>
                                {{ $zipcode['label'] }}
                            </option>
                        @endforeach
                    </select>
                </div> --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Month</label>
                    <select name="month_year" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">All Months</option>
                        @foreach($availableMonths as $month)
                            <option value="{{ $month['month'] }}-{{ $month['year'] }}" {{ request('month_year') == $month['month'].'-'.$month['year'] ? 'selected' : '' }}>
                                {{ $month['label'] }}
                            </option>
                        @endforeach
                    </select>
                    @if(request('month_year'))
                        <input type="hidden" name="month" value="{{ explode('-', request('month_year'))[0] }}">
                        <input type="hidden" name="year" value="{{ explode('-', request('month_year'))[1] }}">
                    @endif
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Predicted Status</label>
                    <select name="predicted_status" class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="all" {{ request('predicted_status') == 'all' || !request('predicted_status') ? 'selected' : '' }}>All Statuses</option>
                        <option value="Sold" {{ request('predicted_status') == 'Sold' ? 'selected' : '' }}>Sold</option>
                        <option value="Pending" {{ request('predicted_status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Active" {{ request('predicted_status') == 'Active' ? 'selected' : '' }}>Active</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                    <div class="relative">
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Property ID, Address..." 
                            class="w-full border border-gray-300 rounded-md px-3 py-2 pl-10 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <i class="fas fa-search absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                    </div>
                </div>
                <div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm font-medium">
                        Apply Filters
                    </button>
                    <a href="{{ route('user.dashboard') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-sm font-medium">
                        Clear
                    </a>
                </div>
            </form>
        </div>

        <!-- Property Dataset -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Property Dataset</h2>
                    <p class="text-sm text-gray-600 mt-1">
                        Showing {{ $datasets->firstItem() ?? 0 }}-{{ $datasets->lastItem() ?? 0 }} of {{ $datasets->total() }} properties • 
                        Click any row to view full details
                    </p>
                </div>
                <div class="flex items-center space-x-4">
                    <select name="per_page" onchange="window.location.href='{{ route('user.dashboard') }}?per_page='+this.value+'&{{ http_build_query(request()->except('per_page')) }}'" 
                        class="border border-gray-300 rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25 per page</option>
                        <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50 per page</option>
                        <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100 per page</option>
                    </select>
                    <a href="{{ route('user.export', request()->all()) }}" 
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 text-sm font-medium flex items-center space-x-2">
                        <i class="fas fa-download"></i>
                        <span>Export CSV</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                Property ID <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                Address <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                Type <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                Tax Value <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                Beds <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                Baths <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                SFLA <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                Predicted <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                Probability <i class="fas fa-sort ml-1"></i>
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer hover:bg-gray-100">
                                Accuracy <i class="fas fa-sort ml-1"></i>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($datasets as $dataset)
                            <tr class="hover:bg-gray-50 cursor-pointer">
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $dataset->propertyid ?? '-' }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900">
                                    {{ $dataset->address ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ $dataset->restype ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    @if($dataset->tax_value)
                                        ${{ number_format($dataset->tax_value) }}
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ $dataset->bedrooms ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ $dataset->fullbaths ?? '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    {{ $dataset->sfla ? number_format($dataset->sfla) : '-' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($dataset->predicted_status)
                                        @php
                                            $statusColors = [
                                                'Sold' => 'bg-green-100 text-green-800',
                                                'Pending' => 'bg-yellow-100 text-yellow-800',
                                                'Active' => 'bg-blue-100 text-blue-800',
                                            ];
                                            $color = $statusColors[$dataset->predicted_status] ?? 'bg-gray-100 text-gray-800';
                                        @endphp
                                        <span class="px-2 py-1 text-xs font-medium rounded-full {{ $color }}">
                                            {{ $dataset->predicted_status }}
                                        </span>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-600">
                                    @if($dataset->status_probability)
                                        {{ number_format($dataset->status_probability * 100, 1) }}%
                                    @else
                                        -
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    @if($dataset->correct_status === 'Yes')
                                        <i class="fas fa-check text-green-600"></i>
                                    @elseif($dataset->correct_status === 'No')
                                        <i class="fas fa-times text-red-600"></i>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                                    No properties found matching your criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($datasets->hasPages())
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $datasets->links() }}
                </div>
            @endif
        </div>
    </main>

    <script>
        // Handle month_year select change
        document.querySelector('select[name="month_year"]')?.addEventListener('change', function() {
            if (this.value) {
                const [month, year] = this.value.split('-');
                const form = this.closest('form');
                let monthInput = form.querySelector('input[name="month"]');
                let yearInput = form.querySelector('input[name="year"]');
                
                if (!monthInput) {
                    monthInput = document.createElement('input');
                    monthInput.type = 'hidden';
                    monthInput.name = 'month';
                    form.appendChild(monthInput);
                }
                if (!yearInput) {
                    yearInput = document.createElement('input');
                    yearInput.type = 'hidden';
                    yearInput.name = 'year';
                    form.appendChild(yearInput);
                }
                
                monthInput.value = month;
                yearInput.value = year;
            }
        });
    </script>
</body>
</html>