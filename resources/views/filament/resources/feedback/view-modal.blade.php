<div class="space-y-6">
    <!-- User Information -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">User Information</h3>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <p class="text-xs text-gray-500 mb-1">Name</p>
                <p class="text-sm font-medium text-gray-900">{{ $feedback->user->name ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Email</p>
                <p class="text-sm font-medium text-gray-900">{{ $feedback->user->email ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Phone</p>
                <p class="text-sm font-medium text-gray-900">{{ $feedback->user->phone ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Submitted At</p>
                <p class="text-sm font-medium text-gray-900">{{ $feedback->created_at->format('M j, Y g:i A') }}</p>
            </div>
        </div>
    </div>

    <!-- Feedback Details -->
    <div>
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Feedback Details</h3>
        <div class="space-y-4">
            <div>
                <p class="text-xs text-gray-500 mb-1">Subject</p>
                <p class="text-sm font-medium text-gray-900">{{ $feedback->subject }}</p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Message</p>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-900 whitespace-pre-wrap">{{ $feedback->message }}</p>
                </div>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Status</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                    @if($feedback->status === 'pending') bg-yellow-100 text-yellow-800
                    @elseif($feedback->status === 'reviewed') bg-blue-100 text-blue-800
                    @else bg-green-100 text-green-800
                    @endif">
                    {{ ucfirst($feedback->status) }}
                </span>
            </div>
        </div>
    </div>
</div>

