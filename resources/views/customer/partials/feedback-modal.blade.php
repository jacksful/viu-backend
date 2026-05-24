<!-- Feedback Modal -->
<div x-data="{
    feedbackModalOpen: false,
    subject: '',
    message: '',
    errors: {},
    successMessage: '',
    errorMessage: '',
    submitting: false,
    async submitFeedback() {
        // Reset messages
        this.successMessage = '';
        this.errorMessage = '';
        this.errors = {};
        
        // Basic validation
        if (!this.subject.trim()) {
            this.errors.subject = 'Please fill in this field';
            return;
        }
        
        if (!this.message.trim()) {
            this.errors.message = 'Please fill in this field';
            return;
        }
        
        this.submitting = true;
        
        const formData = new FormData();
        formData.append('subject', this.subject);
        formData.append('message', this.message);
        formData.append('_token', '{{ csrf_token() }}');
        
        try {
            const response = await fetch('{{ route("user.feedback.store") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (response.ok) {
                this.successMessage = data.message || 'Thank you for your feedback!';
                this.subject = '';
                this.message = '';
                
                // Close modal after 2 seconds
                setTimeout(() => {
                    this.feedbackModalOpen = false;
                    this.successMessage = '';
                }, 2000);
            } else {
                // Validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        this.errors[field] = data.errors[field][0];
                    });
                } else {
                    this.errorMessage = data.message || 'An error occurred while sending your feedback. Please try again.';
                }
            }
        } catch (error) {
            this.errorMessage = 'An error occurred while sending your feedback. Please try again.';
        } finally {
            this.submitting = false;
        }
    }
}" 
     x-show="feedbackModalOpen" 
     x-cloak
     @keydown.escape.window="feedbackModalOpen = false"
     @open-feedback-modal.window="feedbackModalOpen = true"
     @close-feedback-modal.window="feedbackModalOpen = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" 
         x-show="feedbackModalOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="feedbackModalOpen = false" type="button"></div>
    
    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
             x-show="feedbackModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.away="feedbackModalOpen = false"
             x-cloak>
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Send Feedback</h3>
                <button @click="feedbackModalOpen = false" class="text-gray-400 hover:text-gray-600" type="button">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <form id="feedbackForm" 
                  @submit.prevent="submitFeedback()"
                  class="px-6 py-4">
                @csrf
                
                <!-- Success Message -->
                <div x-show="successMessage" 
                     x-cloak
                     class="mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        <p class="text-sm text-green-800" x-text="successMessage"></p>
                    </div>
                </div>
                
                <!-- Error Message -->
                <div x-show="errorMessage" 
                     x-cloak
                     class="mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                        <p class="text-sm text-red-800" x-text="errorMessage"></p>
                    </div>
                </div>
                
                <!-- Subject Field -->
                <div class="mb-4">
                    <label for="subject" class="block text-sm font-medium text-gray-700 mb-1">
                        Subject <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="subject" 
                           name="subject" 
                           x-model="subject"
                           required
                           placeholder="Brief description of your feedback"
                           class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                           :class="errors.subject ? 'border-red-500' : 'border-gray-300'">
                    <p x-show="errors.subject" 
                       x-cloak
                       x-text="errors.subject"
                       class="mt-1 text-xs text-red-600"></p>
                </div>
                
                <!-- Message Field -->
                <div class="mb-4">
                    <label for="message" class="block text-sm font-medium text-gray-700 mb-1">
                        Message
                    </label>
                    <textarea id="message" 
                              name="message" 
                              x-model="message"
                              rows="6"
                              required
                              placeholder="Provide details about your feedback, suggestions, or issues..."
                              class="w-full px-3 py-2 border rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                              :class="errors.message ? 'border-red-500' : 'border-gray-300'"></textarea>
                    <p x-show="errors.message" 
                       x-cloak
                       x-text="errors.message"
                       class="mt-1 text-xs text-red-600"></p>
                </div>
                
                <!-- Informational Text -->
                <div class="mb-4">
                    <p class="text-sm text-gray-600">
                        Your feedback will be sent directly to the admin team. We typically respond within 24-48 hours.
                    </p>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" 
                            @click="feedbackModalOpen = false"
                            :disabled="submitting"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed">
                        Cancel
                    </button>
                    <button type="submit" 
                            :disabled="submitting"
                            class="px-4 py-2 text-sm font-medium text-white bg-gray-900 border border-transparent rounded-md hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                        <i class="fas fa-paper-plane mr-2"></i>
                        <span x-show="!submitting">Send Feedback</span>
                        <span x-show="submitting" x-cloak>
                            <i class="fas fa-spinner fa-spin mr-2"></i>Sending...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
[x-cloak] { display: none !important; }
</style>

