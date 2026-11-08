<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#f5f4ed">
        <title>@yield('title', 'Ruang Kilat — Photobooth')</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body>
        <main class="app-shell">
            @include('partials.photobooth-header')
            @yield('content')
            @include('partials.photobooth-footer')
        </main>
    </body>
</html>
