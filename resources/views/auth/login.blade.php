<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    @vite(['resources/css/app.css'])
</head>
<body class="w-full min-h-screen flex items-center justify-center bg-gray-100 text-gray-900 dark:bg-[#09090B] dark:text-gray-200">
    
    <div class="w-full max-w-md bg-white p-6 rounded-lg shadow-lg border border-gray-300 dark:bg-[#18181B] dark:border-gray-900">
        <!-- Session Status -->
        @if(session('status'))
            <div class="mb-4 text-green-600 dark:text-green-400">
                {{ session('status') }}
            </div>
        @endif

        <!-- Error Alert -->
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mt-3 dark:bg-red-600 dark:text-white" role="alert">
                <span class="block sm:inline">{{ session('error') }}</span>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    class="block w-full mt-1 p-2 bg-white border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-[#202024] dark:border-gray-600 dark:text-white">
                @error('email')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password -->
            <div class="mt-4">
                <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    class="block w-full mt-1 p-2 bg-white border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 dark:bg-[#202024] dark:border-gray-600 dark:text-white">
                @error('password')
                    <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
                @enderror
            </div>

            <!-- Remember Me -->
            <div class="block mt-4">
                <label for="remember_me" class="inline-flex items-center">
                    <input id="remember_me" type="checkbox" name="remember"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:border-gray-600 dark:bg-[#202024]">
                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Remember me</span>
                </label>
            </div>

            <div class="flex items-center justify-between mt-4">
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-sm text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-100">
                        Forgot your password?
                    </a>
                @endif

                <button type="submit" class="ml-3 px-4 py-2 bg-[#f59e0b] text-white rounded-md hover:bg-[#fbbf24] focus:outline-none focus:ring-2 focus:ring-[#d97706]">
                    Log in
                </button>
            </div>
        </form>

        <!-- Divider -->
        <div class="flex items-center my-6">
            <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
            <span class="mx-4 text-gray-500 dark:text-gray-400">or</span>
            <div class="flex-grow border-t border-gray-300 dark:border-gray-700"></div>
        </div>

        <!-- Google Sign-In -->
        <div class="flex items-center justify-center">
            <a href="{{ route('auth.google') }}" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                Sign in with Google
            </a>
        </div>
    </div>

</body>
</html>
