<!-- Profile Edit Modal -->
<div x-show="profileModalOpen" 
     x-cloak
     @keydown.escape.window="profileModalOpen = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity" 
         x-show="profileModalOpen"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="profileModalOpen = false" type="button"></div>
    
    <!-- Modal -->
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
             x-show="profileModalOpen"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             @click.away="profileModalOpen = false"
             x-cloak>
            
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <h3 class="text-xl font-semibold text-gray-900">Edit Profile</h3>
                    <button @click="profileModalOpen = false" class="text-gray-400 hover:text-gray-600" type="button">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            
            <!-- Modal Body -->
            <form id="profileEditForm" class="px-6 py-4" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                
                <!-- Success Message -->
                <div id="profileSuccessMessage" class="hidden mb-4 p-4 bg-green-50 border border-green-200 rounded-md">
                    <div class="flex items-center">
                        <i class="fas fa-check-circle text-green-600 mr-2"></i>
                        <p class="text-sm text-green-800">Profile updated successfully!</p>
                    </div>
                </div>
                
                <!-- Error Message -->
                <div id="profileErrorMessage" class="hidden mb-4 p-4 bg-red-50 border border-red-200 rounded-md">
                    <div class="flex items-center">
                        <i class="fas fa-exclamation-circle text-red-600 mr-2"></i>
                        <p class="text-sm text-red-800" id="profileErrorText"></p>
                    </div>
                </div>
                
                <!-- Profile Photo Section -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Profile Photo</h4>
                    <div class="flex items-center space-x-6">
                        <!-- Current/Preview Photo -->
                        <div class="flex-shrink-0">
                            <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center overflow-hidden border-2 border-gray-300">
                                <img id="profilePhotoPreview" 
                                     src="{{ Auth::user()->profile_photo_url }}"
                                     alt="Profile Photo"
                                     class="w-full h-full object-cover {{ Auth::user()->profile_photo_url ? '' : 'hidden' }}">
                                <i id="profilePhotoIcon" class="fas fa-user text-gray-400 text-4xl {{ Auth::user()->profile_photo_url ? 'hidden' : '' }}"></i>
                            </div>
                        </div>
                        
                        <!-- Upload Controls -->
                        <div class="flex-1">
                            <label for="profile_photo" class="block text-sm font-medium text-gray-700 mb-2">
                                Change Photo
                            </label>
                            <input type="file" 
                                   id="profile_photo" 
                                   name="profile_photo"
                                   accept="image/jpeg,image/jpg,image/png,image/gif"
                                   class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer">
                            <p class="mt-1 text-xs text-gray-500">JPG, PNG or GIF. Max size: 2MB</p>
                            <p class="mt-1 text-xs text-red-600 hidden" id="profile_photo_error"></p>
                            
                            <!-- Remove Photo Button (only show if photo exists) -->
                            @if(Auth::user()->profile_photo_url)
                            <button type="button" 
                                    id="removePhotoBtn"
                                    class="mt-2 text-sm text-red-600 hover:text-red-800">
                                <i class="fas fa-trash-alt mr-1"></i>Remove Photo
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                
                <!-- Personal Information -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Personal Information</h4>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label for="first_name" class="block text-sm font-medium text-gray-700 mb-1">First Name</label>
                            <input type="text" 
                                   id="first_name" 
                                   name="first_name" 
                                   value="{{ Auth::user()->first_name }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <p class="mt-1 text-xs text-red-600 hidden" id="first_name_error"></p>
                        </div>
                        <div>
                            <label for="last_name" class="block text-sm font-medium text-gray-700 mb-1">Last Name</label>
                            <input type="text" 
                                   id="last_name" 
                                   name="last_name" 
                                   value="{{ Auth::user()->last_name }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <p class="mt-1 text-xs text-red-600 hidden" id="last_name_error"></p>
                        </div>
                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                            <input type="email" 
                                   id="email" 
                                   name="email" 
                                   value="{{ Auth::user()->email }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <p class="mt-1 text-xs text-red-600 hidden" id="email_error"></p>
                        </div>
                        <div>
                            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                            <input type="tel" 
                                   id="phone" 
                                   name="phone" 
                                   value="{{ Auth::user()->phone }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <p class="mt-1 text-xs text-red-600 hidden" id="phone_error"></p>
                        </div>
                    </div>
                </div>
                
                <!-- Address Information -->
                <div class="mb-6">
                    <h4 class="text-lg font-medium text-gray-900 mb-4">Address Information</h4>
                    <div class="space-y-4">
                        <div>
                            <label for="address" class="block text-sm font-medium text-gray-700 mb-1">Address</label>
                            <input type="text" 
                                   id="address" 
                                   name="address" 
                                   value="{{ Auth::user()->address }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                            <p class="mt-1 text-xs text-red-600 hidden" id="address_error"></p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                <input type="text" 
                                       id="city" 
                                       name="city" 
                                       value="{{ Auth::user()->city }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-red-600 hidden" id="city_error"></p>
                            </div>
                            <div>
                                <label for="state" class="block text-sm font-medium text-gray-700 mb-1">State</label>
                                <input type="text" 
                                       id="state" 
                                       name="state" 
                                       value="{{ Auth::user()->state }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-red-600 hidden" id="state_error"></p>
                            </div>
                            <div>
                                <label for="zip" class="block text-sm font-medium text-gray-700 mb-1">ZIP Code</label>
                                <input type="text" 
                                       id="zip" 
                                       name="zip" 
                                       value="{{ Auth::user()->zip }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-red-600 hidden" id="zip_error"></p>
                            </div>
                            <div>
                                <label for="country" class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                                <input type="text" 
                                       id="country" 
                                       name="country" 
                                       value="{{ Auth::user()->country }}"
                                       class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                <p class="mt-1 text-xs text-red-600 hidden" id="country_error"></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Modal Footer -->
                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <button type="button" 
                            @click="profileModalOpen = false"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        Cancel
                    </button>
                    <button type="submit" 
                            id="profileSubmitBtn"
                            class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span id="profileSubmitText">Save Changes</span>
                        <span id="profileSubmitSpinner" class="hidden">
                            <i class="fas fa-spinner fa-spin mr-2"></i>Saving...
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('profileEditForm');
    const submitBtn = document.getElementById('profileSubmitBtn');
    const submitText = document.getElementById('profileSubmitText');
    const submitSpinner = document.getElementById('profileSubmitSpinner');
    const successMessage = document.getElementById('profileSuccessMessage');
    const errorMessage = document.getElementById('profileErrorMessage');
    const errorText = document.getElementById('profileErrorText');
    const profilePhotoInput = document.getElementById('profile_photo');
    const profilePhotoPreview = document.getElementById('profilePhotoPreview');
    const profilePhotoIcon = document.getElementById('profilePhotoIcon');
    const removePhotoBtn = document.getElementById('removePhotoBtn');
    
    // Handle profile photo preview
    if (profilePhotoInput) {
        profilePhotoInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file type
                const validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
                if (!validTypes.includes(file.type)) {
                    const errorElement = document.getElementById('profile_photo_error');
                    if (errorElement) {
                        errorElement.textContent = 'Please select a valid image file (JPG, PNG, or GIF).';
                        errorElement.classList.remove('hidden');
                    }
                    e.target.value = '';
                    return;
                }
                
                // Validate file size (2MB = 2 * 1024 * 1024 bytes)
                if (file.size > 2 * 1024 * 1024) {
                    const errorElement = document.getElementById('profile_photo_error');
                    if (errorElement) {
                        errorElement.textContent = 'Image size must be less than 2MB.';
                        errorElement.classList.remove('hidden');
                    }
                    e.target.value = '';
                    return;
                }
                
                // Clear any previous errors
                const errorElement = document.getElementById('profile_photo_error');
                if (errorElement) {
                    errorElement.classList.add('hidden');
                }
                
                // Create preview
                const reader = new FileReader();
                reader.onload = function(e) {
                    profilePhotoPreview.src = e.target.result;
                    profilePhotoPreview.classList.remove('hidden');
                    profilePhotoIcon.classList.add('hidden');
                };
                reader.readAsDataURL(file);
            }
        });
    }
    
    // Handle remove photo button
    if (removePhotoBtn) {
        removePhotoBtn.addEventListener('click', function() {
            // Create a hidden input to indicate photo removal
            let removePhotoInput = document.getElementById('remove_profile_photo');
            if (!removePhotoInput) {
                removePhotoInput = document.createElement('input');
                removePhotoInput.type = 'hidden';
                removePhotoInput.id = 'remove_profile_photo';
                removePhotoInput.name = 'remove_profile_photo';
                removePhotoInput.value = '1';
                form.appendChild(removePhotoInput);
            }
            
            // Clear preview
            profilePhotoPreview.src = '';
            profilePhotoPreview.classList.add('hidden');
            profilePhotoIcon.classList.remove('hidden');
            
            // Clear file input
            if (profilePhotoInput) {
                profilePhotoInput.value = '';
            }
            
            // Hide remove button
            removePhotoBtn.style.display = 'none';
        });
    }
    
    form.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Hide messages
        successMessage.classList.add('hidden');
        errorMessage.classList.add('hidden');
        
        // Clear previous errors
        document.querySelectorAll('[id$="_error"]').forEach(el => {
            el.classList.add('hidden');
            el.textContent = '';
        });
        
        // Disable submit button
        submitBtn.disabled = true;
        submitText.classList.add('hidden');
        submitSpinner.classList.remove('hidden');
        
        const formData = new FormData(form);
        
        try {
            const response = await fetch('{{ route("user.profile.update") }}', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            });
            
            const data = await response.json();
            
            if (response.ok) {
                // Success
                successMessage.classList.remove('hidden');
                
                // Update user info in dropdown if needed
                if (data.user) {
                    // Update profile photo preview if URL is provided
                    if (data.user.profile_photo_url) {
                        profilePhotoPreview.src = data.user.profile_photo_url;
                        profilePhotoPreview.classList.remove('hidden');
                        profilePhotoIcon.classList.add('hidden');
                        if (removePhotoBtn) removePhotoBtn.style.display = 'block';
                    } else {
                        profilePhotoPreview.src = '';
                        profilePhotoPreview.classList.add('hidden');
                        profilePhotoIcon.classList.remove('hidden');
                        if (removePhotoBtn) removePhotoBtn.style.display = 'none';
                    }
                    
                    // Reload page after 1.5 seconds to show updated info
                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);
                } else {
                    // Just close modal after 1.5 seconds
                    setTimeout(() => {
                        // Close modal by dispatching event or using Alpine
                        window.dispatchEvent(new CustomEvent('close-profile-modal'));
                        successMessage.classList.add('hidden');
                    }, 1500);
                }
            } else {
                // Validation errors
                if (data.errors) {
                    Object.keys(data.errors).forEach(field => {
                        const errorElement = document.getElementById(field + '_error');
                        if (errorElement) {
                            errorElement.textContent = data.errors[field][0];
                            errorElement.classList.remove('hidden');
                        }
                    });
                } else {
                    errorText.textContent = data.message || 'An error occurred while updating your profile.';
                    errorMessage.classList.remove('hidden');
                }
            }
        } catch (error) {
            errorText.textContent = 'An error occurred while updating your profile. Please try again.';
            errorMessage.classList.remove('hidden');
        } finally {
            // Re-enable submit button
            submitBtn.disabled = false;
            submitText.classList.remove('hidden');
            submitSpinner.classList.add('hidden');
        }
    });
});
</script>

