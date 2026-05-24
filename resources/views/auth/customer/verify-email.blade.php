<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email - Customer Portal</title>
 
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <div class="flex justify-center">
                    <div class="w-16 h-16 rounded-lg flex items-center justify-center">
                        <img src="{{ asset('image/logo-viu.png') }}" alt="Viu Logo" class="img-fluid"  onerror="this.style.display='none'">
                    </div>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    Verify Your Email Address
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    Customer Portal - Real Estate Predictive Analytics
                </p>
            </div>

            <div class="bg-white rounded-lg shadow p-6">
                @if (session('status'))
                    <div class="mb-4 p-4 rounded-md {{ session('error') ? 'bg-red-50 text-red-800' : 'bg-green-50 text-green-800' }}">
                        <p class="text-sm font-medium">{{ session('status') }}</p>
                    </div>
                @endif

                <div class="text-center">
                    <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 mb-4">
                        <i class="fas fa-envelope text-blue-600 text-xl"></i>
                    </div>
                    
                    <p class="text-sm text-gray-600 mb-4">
                        Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?
                    </p>
                    
                    <p class="text-sm text-gray-600 mb-6">
                        If you didn't receive the email, we'll gladly send you another.
                    </p>

                    @if (Auth::check())
                        <form method="POST" action="{{ route('verification.send') }}" class="mb-4">
                            @csrf
                            <button type="submit" 
                                class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Resend Verification Email
                            </button>
                        </form>
                    @endif

                    <div class="mt-6">
                        <form method="POST" action="{{ route('user.logout') }}">
                            @csrf
                            <button type="submit" 
                                class="text-sm text-gray-600 hover:text-gray-900">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</body>
</html>