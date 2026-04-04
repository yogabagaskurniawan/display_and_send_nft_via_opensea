<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <title>{{ $title ?? config('app.name') }}</title>

        <script src="https://cdn.tailwindcss.com"></script>
        {{-- @vite(['resources/css/app.css', 'resources/js/app.js']) --}}
        <meta name="csrf-token" content="{{ csrf_token() }}">
        @livewireStyles
    </head>
    <body>
        {{ $slot }}

        @livewireScripts
    </body>
</html>
