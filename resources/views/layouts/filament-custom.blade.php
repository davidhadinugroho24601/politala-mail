<!DOCTYPE html>
<html lang="en" dir="ltr" class="fi min-h-screen dark">
<head>
    <meta charset="utf-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', 'Laravel')</title>

    <!-- Styles -->
    <style>
        [x-cloak=''],
        [x-cloak='x-cloak'],
        [x-cloak='1'] {
            display: none !important;
        }

        @media (max-width: 1023px) {
            [x-cloak='-lg'] {
                display: none !important;
            }
        }

        @media (min-width: 1024px) {
            [x-cloak='lg'] {
                display: none !important;
            }
        }
    </style>

    @vite([
        'resources/filament-custom-style/app.css',
        'resources/filament-custom-style/css.css',
        'resources/filament-custom-style/app.js',
        'resources/filament-custom-style/async-alpine.js',
        'resources/filament-custom-style/echo.js',
        'resources/filament-custom-style/forms.css',
        'resources/filament-custom-style/livewire.js',
        'resources/filament-custom-style/notifications.js',
        'resources/filament-custom-style/support.js',
        'resources/filament-custom-style/support.css'
    ])

    <style>
        :root {
            --font-family: 'Inter';
            --sidebar-width: 20rem;
            --collapsed-sidebar-width: 4.5rem;
            --default-theme-mode: system;
        }
    </style>

    <script>
        const theme = localStorage.getItem('theme') ?? 'system';
        if (
            theme === 'dark' ||
            (theme === 'system' && window.matchMedia('(prefers-color-scheme: dark)').matches)
        ) {
            document.documentElement.classList.add('dark');
        }
    </script>

    @livewireStyles
</head>
<body class="fi-body fi-panel-admin min-h-screen bg-gray-50 font-normal text-gray-950 antialiased dark:bg-gray-950 dark:text-white">
    @yield('content')

    <!-- Scripts -->
    <script>
        window.filamentData = [];
    </script>

    <script src="{{ asset('js/notifications.js') }}"></script>
    <script src="{{ asset('js/async-alpine.js') }}"></script>
    <script src="{{ asset('js/support.js') }}"></script>
    <script src="{{ asset('js/echo.js') }}"></script>
    <script src="{{ asset('js/app.js') }}"></script>

    @livewireScripts
</body>
</html>
