@props(['icon', 'title', 'description'])

<div class="bg-white p-4 rounded-4">
    <div class="bg-primary bg-opacity-10 rounded p-3 d-inline-flex align-items-center justify-content-center mb-4" style="width: 3rem; height: 3rem;">
        <img src="{{ asset($icon) }}" alt="{{ $title }} Icon" class="img-fluid" style="width: 1.5rem; height: 1.5rem;" onerror="this.style.display='none'">
    </div>
    <h3 class="h5 fw-semibold mb-2">{{ $title }}</h3>
    <p class="text-muted mb-0">{{ $description }}</p>
</div>