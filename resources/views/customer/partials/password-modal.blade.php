<!-- Change Password Modal -->
<div x-data="{ passwordModalOpen: false }"
     x-show="passwordModalOpen"
     x-cloak
     @keydown.escape.window="passwordModalOpen = false"
     @open-password-modal.window="passwordModalOpen = true"
     @close-password-modal.window="passwordModalOpen = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"
         x-show="passwordModalOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="passwordModalOpen = false"
         type="button"></div>

    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-md w-full"
             x-show="passwordModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.away="passwordModalOpen = false"
             x-cloak>

            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Change Password</h3>
                <button @click="passwordModalOpen = false" class="text-gray-400 hover:text-gray-600" type="button">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form id="passwordChangeForm" class="px-6 py-4">
                @csrf
                @method('PUT')

                <!-- Success Message -->
                <div id="passwordSuccessMessage" class="hidden mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        <p class="text-sm text-green-800">Password updated successfully!</p>
                    </div>
                </div>

                <!-- Error Message -->
                <div id="passwordErrorMessage" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                        <p class="text-sm text-red-800" id="passwordErrorText"></p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Current Password</label>
                        <input type="password"
                               id="current_password"
                               name="current_password"
                               autocomplete="current-password"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <p class="mt-1 text-xs text-red-600 hidden" id="current_password_error"></p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password"
                               id="password"
                               name="password"
                               autocomplete="new-password"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <p class="mt-1 text-xs text-red-600 hidden" id="password_error"></p>
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password"
                               id="password_confirmation"
                               name="password_confirmation"
                               autocomplete="new-password"
                               required
                               class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                        <p class="mt-1 text-xs text-red-600 hidden" id="password_confirmation_error"></p>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-3 pt-6 mt-2 border-t border-gray-200">
                    <button type="button"
                            @click="passwordModalOpen = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button type="submit"
                            id="passwordSubmitBtn"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span id="passwordSubmitText">Update Password</span>
                        <span id="passwordSubmitSpinner" class="hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Updating...
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('passwordChangeForm');
    if (!form) return;

    const submitBtn = document.getElementById('passwordSubmitBtn');
    const submitText = document.getElementById('passwordSubmitText');
    const submitSpinner = document.getElementById('passwordSubmitSpinner');
    const successMessage = document.getElementById('passwordSuccessMessage');
    const errorMessage = document.getElementById('passwordErrorMessage');
    const errorText = document.getElementById('passwordErrorText');

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        successMessage.classList.add('hidden');
        errorMessage.classList.add('hidden');

        document.querySelectorAll('[id$="_error"]').forEach(el => {
            if (el.id.startsWith('password') || el.id === 'current_password_error') {
                el.classList.add('hidden');
                el.textContent = '';
            }
        });

        submitBtn.disabled = true;
        submitText.classList.add('hidden');
        submitSpinner.classList.remove('hidden');

        const formData = new FormData(form);

        try {
            const response = await fetch('{{ route("user.password.update") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });

            const data = await response.json();

            if (response.ok) {
                successMessage.classList.remove('hidden');
                form.reset();

                setTimeout(() => {
                    window.dispatchEvent(new CustomEvent('close-password-modal'));
                    successMessage.classList.add('hidden');
                }, 1500);
            } else {
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorElement = document.getElementById(field + '_error');
                        if (errorElement) {
                            errorElement.textContent = data.errors[field][0];
                            errorElement.classList.remove('hidden');
                        }
                    });
                } else {
                    errorText.textContent = data.message || 'An error occurred while updating your password.';
                    errorMessage.classList.remove('hidden');
                }
            }
        } catch (error) {
            errorText.textContent = 'An error occurred while updating your password. Please try again.';
            errorMessage.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitText.classList.remove('hidden');
            submitSpinner.classList.add('hidden');
        }
    });
});
</script>
