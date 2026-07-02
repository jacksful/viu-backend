<!-- Header -->
<header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center">
                <div class="w-10 h-10  rounded-lg flex items-center justify-center mr-3">
                    <img src="{{ asset('image/logo-viu.png') }}" alt="Viu Logo" class="img-fluid"  onerror="this.style.display='none'">
                </div>
                <div>
                    <h1 class="text-lg font-semibold text-gray-900">Client Portal</h1>
                    <p class="text-xs text-gray-500">Real Estate Predictive Analytics</p>
                </div>
            </div>
            <div class="flex items-center space-x-4">
                <button @click="$dispatch('open-feedback-modal')" class="text-gray-600 hover:text-gray-900 relative">
                    <i class="far fa-comment text-xl"></i>
                </button>
                <!-- Notification Dropdown -->
                <div class="relative" x-data="{ 
                    open: false, 
                    notifications: [], 
                    unreadCount: {{ Auth::user()->unreadNotificationsCount() }},
                    loading: false,
                    async init() {
                        await this.fetchNotifications();
                    },
                    async fetchNotifications() {
                        this.loading = true;
                        try {
                            const response = await fetch('{{ route('user.notifications.index') }}');
                            const data = await response.json();
                            this.notifications = data.notifications || [];
                            this.unreadCount = data.unread_count || 0;
                        } catch (error) {
                            console.error('Error fetching notifications:', error);
                        } finally {
                            this.loading = false;
                        }
                    },
                    async markAsRead(notificationId) {
                        try {
                            const url = '{{ url('/user/notifications') }}/' + notificationId + '/read';
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                },
                            });
                            const data = await response.json();
                            if (data.success) {
                                this.unreadCount = data.unread_count || 0;
                                const notification = this.notifications.find(n => n.id === notificationId);
                                if (notification) {
                                    notification.is_read = true;
                                }
                            }
                        } catch (error) {
                            console.error('Error marking notification as read:', error);
                        }
                    },
                    async markAllAsRead() {
                        try {
                            const response = await fetch('{{ route('user.notifications.read-all') }}', {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                },
                            });
                            const data = await response.json();
                            if (data.success) {
                                this.unreadCount = 0;
                                this.notifications.forEach(n => n.is_read = true);
                            }
                        } catch (error) {
                            console.error('Error marking all as read:', error);
                        }
                    },
                    getNotificationIcon(type) {
                        const icons = {
                            'dataset_published': 'fas fa-database',
                            'data_update': 'fas fa-exclamation-circle',
                            'subscription_renewal': 'fas fa-calendar',
                            'platform_update': 'fas fa-bullhorn',
                            'zipcode_assigned': 'fas fa-map-marker-alt',
                            'subscription_activated': 'fas fa-check-circle',
                        };
                        return icons[type] || 'fas fa-bell';
                    },
                    getNotificationIconColor(type) {
                        const colors = {
                            'dataset_published': 'text-blue-600',
                            'data_update': 'text-green-600',
                            'subscription_renewal': 'text-orange-600',
                            'platform_update': 'text-purple-600',
                            'zipcode_assigned': 'text-blue-600',
                            'subscription_activated': 'text-green-600',
                        };
                        return colors[type] || 'text-gray-600';
                    },
                    formatDate(dateString) {
                        const date = new Date(dateString);
                        return date.toLocaleDateString('en-US', { year: 'numeric', month: '2-digit', day: '2-digit' });
                    }
                }" @click.away="open = false">
                    <button @click="open = !open; if(open) fetchNotifications()" class="text-gray-600 hover:text-gray-900 relative">
                        <i class="far fa-bell text-xl"></i>
                        <span x-show="unreadCount > 0" class="absolute top-0 right-0 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <!-- Notification Dropdown Panel -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-96 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                         style="display: none;">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                            <div class="flex items-center space-x-2">
                                <i class="far fa-bell text-gray-600"></i>
                                <h3 class="text-lg font-semibold text-gray-900">Notifications</h3>
                            </div>
                            <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- Notifications List -->
                        <div class="max-h-96 overflow-y-auto">
                            <template x-if="loading">
                                <div class="px-6 py-8 text-center text-gray-500">
                                    <i class="fas fa-spinner fa-spin text-xl mb-2"></i>
                                    <p>Loading notifications...</p>
                                </div>
                            </template>
                            
                            <template x-if="!loading && notifications.length === 0">
                                <div class="px-6 py-8 text-center text-gray-500">
                                    <i class="far fa-bell text-3xl mb-2"></i>
                                    <p>No notifications</p>
                                </div>
                            </template>
                            
                            <template x-for="notification in notifications" :key="notification.id">
                                <div @click="markAsRead(notification.id)" 
                                     class="px-6 py-4 border-b border-gray-100 hover:bg-gray-50 cursor-pointer transition-colors">
                                    <div class="flex items-start space-x-4">
                                        <!-- Icon -->
                                        <div class="flex-shrink-0">
                                            <div class="w-10 h-10 rounded-full flex items-center justify-center" 
                                                 :class="{
                                                     'bg-blue-100': ['dataset_published', 'zipcode_assigned'].includes(notification.type),
                                                     'bg-green-100': ['data_update', 'subscription_activated'].includes(notification.type),
                                                     'bg-orange-100': notification.type === 'subscription_renewal',
                                                     'bg-purple-100': notification.type === 'platform_update',
                                                     'bg-gray-100': !['dataset_published', 'data_update', 'subscription_renewal', 'platform_update', 'zipcode_assigned', 'subscription_activated'].includes(notification.type)
                                                 }">
                                                <i :class="getNotificationIcon(notification.type) + ' ' + getNotificationIconColor(notification.type)"></i>
                                            </div>
                                        </div>
                                        
                                        <!-- Content -->
                                        <div class="flex-1 min-w-0">
                                            <div class="flex items-start justify-between">
                                                <div class="flex-1">
                                                    <h4 class="text-sm font-semibold text-gray-900" x-text="notification.title"></h4>
                                                    <p class="text-sm text-gray-600 mt-1" x-text="notification.description"></p>
                                                    <p class="text-xs text-gray-500 mt-2" x-text="formatDate(notification.created_at)"></p>
                                                </div>
                                                <!-- Unread Indicator -->
                                                <div x-show="!notification.is_read" class="flex-shrink-0 ml-2">
                                                    <span class="w-2 h-2 bg-blue-500 rounded-full block"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                        
                        <!-- Footer -->
                        <div class="px-6 py-4 border-t border-gray-200" x-show="notifications.length > 0 && unreadCount > 0">
                            <button @click="markAllAsRead()" class="text-sm text-blue-600 hover:text-blue-700 font-medium">
                                Mark all as read
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Profile Dropdown -->
                <div class="relative" x-data="{ open: false, profileModalOpen: false }">
                    <button @click="open = !open" class="flex items-center space-x-2 focus:outline-none">
                        <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center overflow-hidden">
                            @if(Auth::user()->profile_photo_url)
                                <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-user text-gray-600 text-sm"></i>
                            @endif
                        </div>
                    </button>
                    
                    <!-- Dropdown Menu -->
                    <div x-show="open" 
                         @click.away="open = false"
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-lg border border-gray-200 z-50"
                         style="display: none;">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                            <h3 class="text-lg font-semibold text-gray-900">Profile</h3>
                            <button @click="open = false" class="text-gray-400 hover:text-gray-600">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        
                        <!-- User Info -->
                        <div class="px-6 py-4 border-b border-gray-200">
                            <div class="flex items-start space-x-4">
                                <div class="w-16 h-16 bg-gray-300 rounded-full flex items-center justify-center overflow-hidden flex-shrink-0">
                                    @if(Auth::user()->profile_photo_url)
                                        <img src="{{ Auth::user()->profile_photo_url }}" alt="{{ Auth::user()->name }}" class="w-full h-full object-cover">
                                    @else
                                        <i class="fas fa-user text-gray-600 text-2xl"></i>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="text-lg font-semibold text-gray-900 truncate">{{ Auth::user()->name }}</h4>
                                    <p class="text-sm text-gray-500 truncate">{{ Auth::user()->email }}</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Account Details -->
                        <div class="px-6 py-4 border-b border-gray-200 space-y-3">
                            @php
                                $user = Auth::user();
                                $activeSubscriptions = \App\Models\UserZipcodeSubscription::where('user_id', $user->id)
                                    ->where('status', 'active')
                                    ->get();
                                
                                $assignedZipcodes = collect();
                                foreach ($activeSubscriptions as $subscription) {
                                    $zipcodes = \App\Models\Zipcode::whereIn('id', $subscription->zipcode_ids ?? [])->get();
                                    $assignedZipcodes = $assignedZipcodes->merge($zipcodes);
                                }
                                $assignedZipcodes = $assignedZipcodes->unique('id')->values();
                                
                                $plan = $activeSubscriptions->isNotEmpty() ? 'Professional' : 'Free';
                                $memberSince = $user->created_at->format('F Y');
                            @endphp
                            
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Plan</span>
                                <span class="px-3 py-1 bg-blue-100 text-blue-800 text-sm font-medium rounded-full">{{ $plan }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Status</span>
                                <span class="text-sm font-medium text-green-600">{{ ucfirst($user->status ?? 'Active') }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Member Since</span>
                                <span class="text-sm text-gray-900">{{ $memberSince }}</span>
                            </div>
                            <div>
                                <span class="text-sm text-gray-600 block mb-2">Assigned ZIP Codes:</span>
                                <div class="flex flex-wrap gap-2">
                                    @forelse($assignedZipcodes as $zipcode)
                                        <span class="px-3 py-1 bg-gray-100 text-gray-700 text-sm font-medium rounded-full">
                                            {{ $zipcode->code }}
                                        </span>
                                    @empty
                                        <span class="text-sm text-gray-400">No ZIP codes assigned</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        
                        <!-- Navigation Links -->
                        <div class="px-6 py-2 border-b border-gray-200">
                            <button @click="open = false; $dispatch('open-profile-modal')" class="w-full flex items-center space-x-3 px-2 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors">
                                <i class="fas fa-user w-5 text-gray-400"></i>
                                <span>Edit Profile</span>
                            </button>
                            <button @click="open = false; $dispatch('open-subscription-modal')" class="w-full flex items-center space-x-3 px-2 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors">
                                <i class="fas fa-credit-card w-5 text-gray-400"></i>
                                <span>Subscription</span>
                            </button>
                            <button @click="open = false; $dispatch('open-password-modal')" class="w-full flex items-center space-x-3 px-2 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors">
                                <i class="fas fa-lock w-5 text-gray-400"></i>
                                <span>Change Password</span>
                            </button>
                            <a href="{{ route('user.settings') }}" class="flex items-center space-x-3 px-2 py-2 text-sm text-gray-700 hover:bg-gray-50 rounded-md transition-colors">
                                <i class="fas fa-cog w-5 text-gray-400"></i>
                                <span>Settings</span>
                            </a>
                        </div>
                        
                        <!-- Logout -->
                        <div class="px-6 py-4">
                            <form action="{{ route('user.logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="flex items-center space-x-3 w-full text-sm text-red-600 hover:text-red-700 transition-colors">
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Profile Edit Modal (included after header in pages) -->

