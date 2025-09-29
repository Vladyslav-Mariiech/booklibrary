<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
<div id="app">
    <nav class="navigation">
        <div class="navigation-container">
            <a href="{{ route('crud.index') }}" class="navigation-logo">BookLibrary</a>
            <div class="navigation-content">
                <!-- Left Side Of Navbar -->
                <ul class="navigation-left">
                    <li><a href="{{ route('crud.books.index') }}">Books</a></li>
                    <li><a href="{{ route('crud.authors.index') }}">Authors</a></li>
                </ul>
                <!-- Right Side Of Navbar -->
                <ul class="navigation-right">
                    @guest
                        <li><a href="{{ route('login') }}">Login</a></li>
                    @else
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" id="navbarDropdown">
                                {{ Auth::user()->name }}
                                <span class="arrow">▼</span>
                            </a>
                            <div class="dropdown-menu" id="dropdownMenu">
                                <a href="{{ route('logout') }}"
                                   onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                    Logout
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </li>
                    @endguest
                </ul>
            </div>
        </div>
    </nav>
    <main class="main-content">
        @yield('content')
    </main>
</div>

@if(request()->routeIs('crud.books.*'))
    @vite(['resources/js/crud/books.js'])
@elseif(request()->routeIs('crud.authors.*'))
    @vite(['resources/js/crud/authors.js'])
@endif
</body>
</html>
