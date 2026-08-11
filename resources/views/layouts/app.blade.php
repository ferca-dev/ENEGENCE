<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="@yield('meta_description', 'Catálogos geográficos y demográficos de México')">
    <title>@yield('title', config('app.name'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>

<body class="d-flex min-vh-100 flex-column bg-body-tertiary text-body">
    <nav class="navbar sticky-top bg-dark shadow-sm site-navbar" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand site-brand" href="{{ route('states.index') }}">
                <span class="site-brand__title">ENEGENCE</span>
                <span class="site-brand__subtitle">Prueba técnica - Fernando Cárdenas</span>
            </a>
            @yield('navbar_actions')
        </div>
    </nav>

    <main class="@yield('main_class', 'container-fluid flex-grow-1')">
        @yield('content')
    </main>
    @stack('scripts')
</body>

</html>
