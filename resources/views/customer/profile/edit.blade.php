<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Client Portal</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-50" x-data="{ profileModalOpen: false }" @open-profile-modal.window="profileModalOpen = true" @close-profile-modal.window="profileModalOpen = false">
    <div class="min-h-screen">
        @include('customer.partials.header')
        @include('customer.partials.profile-modal')
        @include('customer.partials.subscription-modal')
        @include('customer.partials.feedback-modal')
        
        <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <div class="bg-white rounded-lg shadow p-6">
                <h1 class="text-2xl font-bold text-gray-900 mb-6">Edit Profile</h1>
                <p class="text-gray-600">Profile editing functionality coming soon...</p>
            </div>
        </main>
    </div>
</body>
</html>

