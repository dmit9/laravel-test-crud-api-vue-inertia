<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title inertia>{{ config('app.name', 'LaraTestApp') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        @if(app()->environment('production'))
            <link rel="stylesheet" href="{{ asset('build/assets/app-qkH8EkzO.css') }}">
            <script type="module" src="{{ asset('build/assets/app-Ce5p81Q7.js') }}" defer></script>
        @endif
        <!-- Scripts -->

        @vite(['resources/js/app.js', "resources/js/Pages/{$page['component']}.vue"])
        @routes
        @inertiaHead

    </head>
    <body class="font-sans antialiased">
        @inertia
    </body>
</html>
