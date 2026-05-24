@extends('layouts.app')

@section('title', config('app.name', 'VIU') . ' - Real Estate Intelligence Platform')

@section('body_class', 'home-page')

@section('content')
    @include('components.hero', ['hero' => $hero])
    @include('components.stats-bar')
    @include('components.feature-be-first', ['section' => $strategicWindow])
    @include('components.feature-one-zip', ['section' => $territoryZip])
    @include('components.recognition-section', ['section' => $recognition])
    <x-pricing-section :zipcodes="$zipcodes" :section="$pricing" />
    @include('components.faq-section', ['section' => $qa])
    @include('components.cta-banner')
@endsection

@push('scripts')
<script>
    let selectedZipcodes = [];
    const zipcodesData = @json($zipcodes);

    function checkAvailability() {
        const zipcodeInput = document.getElementById('zipcode_search');
        const zipcode = zipcodeInput.value.trim();
        
        if (!zipcode) {
            alert('Please enter a ZIP code');
            return;
        }

        // Show loading state
        const button = document.getElementById('hero-zip-search-btn');
        if (!button) return;
        const originalButtonText = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Checking...';

        // Make AJAX call to backend
        fetch('{{ route("leads.check-availability") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ zipcode: zipcode })
        })
        .then(response => response.json())
        .then(data => {
            button.disabled = false;
            button.innerHTML = originalButtonText;

            if (data.available) {
                // Show success message
                showMessage(data.message, 'success');
                
                // Add zipcode to zipcodesData if not already present
                const existingZipcode = zipcodesData.find(z => z.id === data.zipcode.id);
                if (!existingZipcode) {
                    zipcodesData.push(data.zipcode);
                }
                
                // Auto-select the zipcode if not already selected
                if (!selectedZipcodes.includes(data.zipcode.id)) {
                    selectedZipcodes.push(data.zipcode.id);
                    updateSelectedZipcodesTags();
                    updateSelectedZipcodes();
                    updateTotal();
                }
                
                // Clear the input field
                zipcodeInput.value = '';

                const pricingModalEl = document.getElementById('pricingLeadModal');
                if (pricingModalEl && typeof bootstrap !== 'undefined') {
                    bootstrap.Modal.getOrCreateInstance(pricingModalEl).show();
                }
            } else {
                // Show error message
                showMessage(data.message, 'error');
            }
        })
        .catch(error => {
            button.disabled = false;
            button.innerHTML = originalButtonText;
            console.error('Error:', error);
            showMessage('An error occurred while checking availability. Please try again.', 'error');
        });
    }

    function showMessage(message, type) {
        // Remove existing messages
        const existingMessages = document.querySelectorAll('.availability-message');
        existingMessages.forEach(msg => msg.remove());

        // Create message element
        const messageDiv = document.createElement('div');
        messageDiv.className = `alert alert-${type === 'success' ? 'success' : 'danger'} availability-message`;
        messageDiv.textContent = message;
        
        // Insert after the search input container
        const searchContainer = document.getElementById('availability-messages-shower');
        searchContainer.appendChild(messageDiv);

        // Auto-remove after 5 seconds
        setTimeout(() => {
            messageDiv.remove();
        }, 5000);
    }

    function toggleZipcode(zipcodeId) {
        // Remove zipcode from selection (used for removing selected zipcodes)
        const index = selectedZipcodes.indexOf(zipcodeId);
        if (index > -1) {
            selectedZipcodes.splice(index, 1);
            updateSelectedZipcodesTags();
            updateSelectedZipcodes();
            updateTotal();
        }
    }

    function updateSelectedZipcodesTags() {
        const tagsContainer = document.getElementById('selected-zipcodes-tags');
        if (!tagsContainer) return;
        
        if (selectedZipcodes.length === 0) {
            tagsContainer.style.display = 'none';
            tagsContainer.innerHTML = '';
            return;
        }
        
        tagsContainer.style.display = 'flex';
        tagsContainer.innerHTML = '';
        
        selectedZipcodes.forEach(zipcodeId => {
            const zipcode = zipcodesData.find(z => z.id === zipcodeId);
            if (zipcode) {
                const tag = document.createElement('div');
                tag.className = 'badge px-3 py-2 me-2 mb-1 d-flex align-items-center gap-2 zip-tag';
                
                const removeBtn = document.createElement('button');
                removeBtn.type = 'button';
                removeBtn.className = 'btn-close btn-close-white ms-1';
                removeBtn.style.fontSize = '0.7rem';
                removeBtn.style.opacity = '0.8';
                removeBtn.setAttribute('aria-label', `Remove ${zipcode.code}`);
                removeBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    e.stopPropagation();
                    toggleZipcode(zipcodeId);
                });
                
                tag.innerHTML = `
                    <i class="bi bi-check-circle-fill"></i>
                    <span>${zipcode.code}</span>
                `;
                // tag.appendChild(removeBtn);
                tagsContainer.appendChild(tag);
            }
        });
    }

    function updateSelectedZipcodes() {
        const container = document.getElementById('selected-zipcodes');
        const contactForm = document.getElementById('contact-form');
        const submitBtn = document.getElementById('submit-btn');
        
        if (selectedZipcodes.length === 0) {
            container.innerHTML = '<p class="text-muted small">No ZIP codes selected yet. Use the hero search to add ZIP codes.</p>';
            contactForm.style.display = 'none';
            submitBtn.disabled = true;
            return;
        }
        
        container.innerHTML = '';
        contactForm.style.display = 'block';
        submitBtn.disabled = false;
        
        selectedZipcodes.forEach((zipcodeId, index) => {
            const zipcode = zipcodesData.find(z => z.id === zipcodeId);
            if (zipcode) {
                const price = parseFloat(zipcode.monthly_price || 349);
                const leadsCount = zipcode.leads_count || 0;
                const location = zipcode.city && zipcode.state 
                    ? `${zipcode.city}, ${zipcode.state}` 
                    : (zipcode.city || zipcode.state || 'Location not available');
                
                const div = document.createElement('div');
                div.className = 'd-flex align-items-start justify-content-between py-3' + (index < selectedZipcodes.length - 1 ? ' border-bottom' : '');
                div.innerHTML = `
                    <div class="flex-grow-1">
                        <div class="fs-5 fw-bold text-dark mb-1">$${price.toLocaleString()} /mo</div>
                        <div class="small text-muted">
                            ${leadsCount.toLocaleString()} Leads, ${location}
                        </div>
                    </div>
                    <button type="button" onclick="toggleZipcode(${zipcodeId})" class="btn btn-link text-muted p-0 ms-3">
                        <svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M11.534 1.04175C12.1172 1.04182 12.6446 1.38932 12.8743 1.92554L13.7459 3.95841H17.5C17.8452 3.95841 18.125 4.23824 18.125 4.58341C18.125 4.92859 17.8452 5.20841 17.5 5.20841H16.8384L16.1353 16.8051C16.0619 18.0146 15.0595 18.9584 13.8477 18.9584H6.15234C4.94053 18.9584 3.93806 18.0146 3.86475 16.8051L3.16162 5.20841H2.5C2.15482 5.20841 1.875 4.92859 1.875 4.58341C1.875 4.23824 2.15482 3.95841 2.5 3.95841H6.25407L7.12565 1.92554C7.35544 1.38941 7.88269 1.04182 8.46598 1.04175H11.534ZM5.1123 16.7294C5.14563 17.2792 5.60153 17.7084 6.15234 17.7084H13.8477C14.3985 17.7084 14.8544 17.2792 14.8877 16.7294L15.5859 5.20841H4.41406L5.1123 16.7294ZM8.46598 2.29175C8.38276 2.29182 8.30758 2.34142 8.27474 2.41789L7.61475 3.95841H12.3853L11.7253 2.41789C11.6925 2.34149 11.6173 2.29182 11.534 2.29175H8.46598Z" fill="#5C5C5C"/>
                        </svg>

                    </button>
                `;
                container.appendChild(div);
            }
        });
    }

    function updateTotal() {
        let total = 0;
        selectedZipcodes.forEach(zipcodeId => {
            const zipcode = zipcodesData.find(z => z.id === zipcodeId);
            if (zipcode) {
                total += parseFloat(zipcode.monthly_price || 349);
            }
        });
        
        // Apply volume discount if multiple zipcodes selected
        let finalTotal = total;

        // if (selectedZipcodes.length > 1) {
        //     // Example: 10% discount for 2+ zipcodes
        //     finalTotal = total * 0.9;
        // }
        
        document.getElementById('total-price').textContent = finalTotal > 0 ? `$${Math.round(finalTotal).toLocaleString()} TOTAL` : '$0';
    }

    const leadFormEl = document.getElementById('leadForm');
    if (leadFormEl) {
        leadFormEl.addEventListener('submit', function(e) {
            if (selectedZipcodes.length === 0) {
                e.preventDefault();
                alert('Please select at least one ZIP code');
                return false;
            }

            const existingInputs = this.querySelectorAll('input[name="zipcodes[]"]');
            existingInputs.forEach(input => input.remove());

            selectedZipcodes.forEach(zipcodeId => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'zipcodes[]';
                input.value = zipcodeId;
                this.appendChild(input);
            });
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        @if (session('success') || $errors->any())
        const pricingModalEl = document.getElementById('pricingLeadModal');
        if (pricingModalEl && typeof bootstrap !== 'undefined') {
            bootstrap.Modal.getOrCreateInstance(pricingModalEl).show();
        }
        @endif
    });
</script>
@endpush