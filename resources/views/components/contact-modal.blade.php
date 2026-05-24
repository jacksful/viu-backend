<!-- Contact Modal -->
<div class="modal fade" id="contactModal" tabindex="-1" aria-labelledby="contactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="contactModalLabel">Contact Us</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="contactForm" method="POST" action="{{ route('contacts.store') }}">
                @csrf
                <div class="modal-body">
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div id="contactFormErrors" class="alert alert-danger d-none">
                        <ul class="mb-0" id="contactErrorList"></ul>
                    </div>

                    <div class="mb-3">
                        <label for="contact_name" class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" 
                               id="contact_name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="contact_email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" 
                               id="contact_email" name="email" value="{{ old('email') }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="contact_phone" class="form-label">Phone</label>
                        <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                               id="contact_phone" name="phone" value="{{ old('phone') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="contact_subject" class="form-label">Subject</label>
                        <input type="text" class="form-control @error('subject') is-invalid @enderror" 
                               id="contact_subject" name="subject" value="{{ old('subject') }}">
                        @error('subject')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="contact_message" class="form-label">Message <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('message') is-invalid @enderror" 
                                  id="contact_message" name="message" rows="5" required>{{ old('message') }}</textarea>
                        @error('message')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary" id="contactSubmitBtn">
                        <span class="spinner-border spinner-border-sm d-none" id="contactSpinner" role="status" aria-hidden="true"></span>
                        Send Message
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const contactForm = document.getElementById('contactForm');
        const contactModal = new bootstrap.Modal(document.getElementById('contactModal'));
        const contactSubmitBtn = document.getElementById('contactSubmitBtn');
        const contactSpinner = document.getElementById('contactSpinner');
        const contactFormErrors = document.getElementById('contactFormErrors');
        const contactErrorList = document.getElementById('contactErrorList');

        // Handle form submission
        if (contactForm) {
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();

                // Reset error display
                contactFormErrors.classList.add('d-none');
                contactErrorList.innerHTML = '';
                
                // Show loading state
                contactSubmitBtn.disabled = true;
                contactSpinner.classList.remove('d-none');

                // Get form data
                const formData = new FormData(contactForm);

                // Submit via AJAX
                fetch(contactForm.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || 
                                       document.querySelector('input[name="_token"]')?.value
                    }
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => Promise.reject(err));
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Show success message
                        const successAlert = document.createElement('div');
                        successAlert.className = 'alert alert-success';
                        successAlert.textContent = data.message;
                        contactForm.querySelector('.modal-body').insertBefore(
                            successAlert, 
                            contactForm.querySelector('.modal-body').firstChild
                        );

                        // Reset form
                        contactForm.reset();

                        // Close modal after 2 seconds
                        setTimeout(() => {
                            contactModal.hide();
                            successAlert.remove();
                        }, 2000);
                    }
                })
                .catch(error => {
                    // Handle validation errors
                    if (error.errors) {
                        contactErrorList.innerHTML = '';
                        Object.keys(error.errors).forEach(key => {
                            const messages = Array.isArray(error.errors[key]) 
                                ? error.errors[key] 
                                : [error.errors[key]];
                            messages.forEach(message => {
                                const li = document.createElement('li');
                                li.textContent = message;
                                contactErrorList.appendChild(li);
                            });
                        });
                        contactFormErrors.classList.remove('d-none');
                    } else if (error.message) {
                        contactErrorList.innerHTML = '<li>' + error.message + '</li>';
                        contactFormErrors.classList.remove('d-none');
                    } else {
                        contactErrorList.innerHTML = '<li>Something went wrong. Please try again.</li>';
                        contactFormErrors.classList.remove('d-none');
                    }
                })
                .finally(() => {
                    // Reset loading state
                    contactSubmitBtn.disabled = false;
                    contactSpinner.classList.add('d-none');
                });
            });
        }

        // Clear form when modal is closed
        const contactModalElement = document.getElementById('contactModal');
        if (contactModalElement) {
            contactModalElement.addEventListener('hidden.bs.modal', function() {
                contactForm.reset();
                contactFormErrors.classList.add('d-none');
                contactErrorList.innerHTML = '';
                // Remove any success messages
                const successAlerts = contactForm.querySelectorAll('.alert-success');
                successAlerts.forEach(alert => alert.remove());
            });
        }
    });
</script>
@endpush

