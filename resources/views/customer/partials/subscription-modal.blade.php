<!-- Subscription Management Modal -->
<div x-data="{
    subscriptionModalOpen: false,
    subscriptionLoading: false,
    subscriptionData: {
        memberSince: '',
        subscriptionStart: '',
        subscriptionEnd: '',
        nextBillingDate: '',
        zipcodeCount: 0,
        totalMonthly: '0.00',
        zipcodes: [],
        billingHistory: []
    },
    async loadSubscriptionData() {
        this.subscriptionLoading = true;
        try {
            const response = await fetch('{{ route('user.subscription.data') }}', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            const data = await response.json();
            if (response.ok) {
                this.subscriptionData = data;
            }
        } catch (error) {
            console.error('Error loading subscription data:', error);
        } finally {
            this.subscriptionLoading = false;
        }
    }
}" 
     x-show="subscriptionModalOpen" 
     x-cloak
     @keydown.escape.window="subscriptionModalOpen = false"
     @open-subscription-modal.window="subscriptionModalOpen = true; loadSubscriptionData()"
     @close-subscription-modal.window="subscriptionModalOpen = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" 
         x-show="subscriptionModalOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="subscriptionModalOpen = false" type="button"></div>
    
    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-[90vh] overflow-y-auto"
             x-show="subscriptionModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.away="subscriptionModalOpen = false"
             x-cloak>
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 sticky top-0 bg-white z-10">
                <div>
                    <h3 class="text-2xl font-bold text-gray-900">Subscription Management</h3>
                    <p class="text-sm text-gray-500 mt-1">Manage your ZIP code subscriptions and billing</p>
                </div>
                <button @click="subscriptionModalOpen = false" class="text-gray-400 hover:text-gray-600" type="button">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <div class="px-6 py-6">
                <!-- Loading State -->
                <div x-show="subscriptionLoading" class="text-center py-12">
                    <i class="fas fa-spinner fa-spin text-4xl text-gray-400 mb-4"></i>
                    <p class="text-gray-600">Loading subscription information...</p>
                </div>

                <!-- Content -->
                <div x-show="!subscriptionLoading" x-cloak>
                    <!-- Subscription Summary Card -->
                    <div class="bg-blue-50 rounded-lg p-6 mb-6 border border-blue-100">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="space-y-3">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Member Since</span>
                                    <span class="text-sm font-medium text-gray-900" x-text="subscriptionData.memberSince"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Subscription Start</span>
                                    <span class="text-sm font-medium text-gray-900" x-text="subscriptionData.subscriptionStart"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Subscription End</span>
                                    <span class="text-sm font-medium text-gray-900" x-text="subscriptionData.subscriptionEnd"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Next Billing Date</span>
                                    <span class="text-sm font-medium text-gray-900" x-text="subscriptionData.nextBillingDate"></span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm text-gray-600">Assigned ZIP Codes</span>
                                    <span class="text-sm font-medium text-gray-900" x-text="subscriptionData.zipcodeCount + ' codes'"></span>
                                </div>
                            </div>
                            <div class="flex items-center justify-end">
                                <div class="text-right">
                                    <p class="text-sm text-gray-600 mb-1">Total Monthly Subscription</p>
                                    <p class="text-3xl font-bold text-gray-900" x-text="'$' + subscriptionData.totalMonthly"></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription ZIP Codes Section -->
                    <div class="mb-6">
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Subscription ZIP Codes</h4>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                            <template x-for="zipcode in subscriptionData.zipcodes" :key="zipcode.id">
                                <div class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-start space-x-3">
                                        <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                            <i class="fas fa-map-marker-alt text-blue-600"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-semibold text-gray-900" x-text="'ZIP ' + zipcode.code"></p>
                                            <p class="text-xs text-gray-600 mt-1" x-text="zipcode.city + ', ' + zipcode.state"></p>
                                            <p class="text-sm font-medium text-gray-900 mt-2" x-text="zipcode.price ? ('$' + zipcode.price + (zipcode.price_label || '/month')) : '—'"></p>
                                            <p class="text-xs text-gray-500 mt-2" x-show="zipcode.subscription_start" x-text="'Start: ' + zipcode.subscription_start"></p>
                                            <p class="text-xs text-gray-500" x-show="zipcode.subscription_end" x-text="'End: ' + zipcode.subscription_end"></p>
                                        </div>
                                    </div>
                                </div>
                            </template>
                            <template x-if="subscriptionData.zipcodes.length === 0">
                                <div class="col-span-full text-center py-8 text-gray-500">
                                    <i class="fas fa-map-marker-alt text-4xl mb-2 text-gray-300"></i>
                                    <p>No ZIP codes subscribed</p>
                                </div>
                            </template>
                        </div>
                        <p class="text-sm text-gray-500 italic">
                            Contact your administrator to add or remove ZIP codes from your subscription.
                        </p>
                    </div>

                    <!-- Billing History Section -->
                    <div>
                        <h4 class="text-lg font-semibold text-gray-900 mb-4">Billing History</h4>
                        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Invoice Date</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <template x-for="invoice in subscriptionData.billingHistory" :key="invoice.id">
                                            <tr>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900" x-text="invoice.date"></td>
                                                <td class="px-6 py-4 text-sm text-gray-600" x-text="invoice.description"></td>
                                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900" x-text="'$' + invoice.amount"></td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    <span class="px-3 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800" x-text="invoice.status"></span>
                                                </td>
                                            </tr>
                                        </template>
                                        <template x-if="subscriptionData.billingHistory.length === 0">
                                            <tr>
                                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                                    No billing history available
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <p class="text-sm text-gray-500 italic mt-4">
                            Invoices are sent by your administrator. Contact them for billing questions.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Modal Footer -->
            <div class="flex items-center justify-end px-6 py-4 border-t border-gray-200 sticky bottom-0 bg-white">
                <button type="button" 
                        @click="subscriptionModalOpen = false"
                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    [x-cloak] { display: none !important; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // This will be handled by Alpine.js in the parent component
});
</script>

