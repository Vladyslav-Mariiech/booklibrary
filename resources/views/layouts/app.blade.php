<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div id="app">
    <nav class="navigation">
        <div class="navigation-container">
            <a href="/" class="navigation-logo">BookLibrary</a>
            <div class="navigation-content">
                <!-- Left Side Of Navbar -->
                <ul class="navigation-left">
                    <li><a href="{{ route('books.index') }}">Books</a></li>
                </ul>
                <!-- Right Side Of Navbar -->
                <ul class="navigation-right">
                </ul>
            </div>
        </div>
    </nav>
    <main class="main-content">
        @yield('content')
    </main>
</div>
</body>
</html>
