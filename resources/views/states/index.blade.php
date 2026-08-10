<!doctype html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Listado de entidades federativas de México obtenido desde INEGI">
    <title>Estados de México | {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="min-vh-100 bg-body-tertiary text-body">
    <nav class="navbar sticky-top bg-dark shadow-sm site-navbar" data-bs-theme="dark">
        <div class="container-fluid">
            <a class="navbar-brand site-brand" href="{{ route('states.index') }}">
                <span class="site-brand__title">ENEGENCE</span>
                <span class="site-brand__subtitle">Prueba técnica - Fernando Cárdenas</span>
            </a>
        </div>
    </nav>

    <main class="container-fluid py-5">
        @if ($states->isEmpty())
        <output class="alert alert-info shadow-sm d-block">
            No hay estados disponibles. Ejecuta
            <code>php artisan inegi:sync-states</code> o
            <code>make vercel-sync-states</code>
            para cargar la información.
        </output>
        @else
        <section class="" aria-labelledby="states-table-title">
                <noscript>
                    <div class="alert alert-warning">
                        La tabla completa está disponible, pero la búsqueda, el orden y la paginación requieren JavaScript.
                    </div>
                </noscript>

                <table id="states-table" class="table table-hover align-middle text-nowrap w-100">
                    <caption class="visually-hidden">Clave, nombre, abreviatura, población y viviendas habitadas de los estados de México</caption>
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="text-center">Clave</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Abreviatura</th>
                            <th scope="col" class="text-end">Población total</th>
                            <th scope="col" class="text-end">Mujeres</th>
                            <th scope="col" class="text-end">Hombres</th>
                            <th scope="col" class="text-end">Viviendas habitadas</th>
                        </tr>
                        <tr class="column-filters" data-dt-order="disable">
                            <th scope="col" class="text-center"><input class="form-control form-control-sm column-filter text-center" type="search" size="1" placeholder="Filtrar…" aria-label="Buscar por clave"></th>
                            <th scope="col"><input class="form-control form-control-sm column-filter" type="search" size="1" placeholder="Filtrar…" aria-label="Buscar por estado"></th>
                            <th scope="col"><input class="form-control form-control-sm column-filter" type="search" size="1" placeholder="Filtrar…" aria-label="Buscar por abreviatura"></th>
                            <th scope="col"><input class="form-control form-control-sm column-filter" type="search" size="1" placeholder="Filtrar…" aria-label="Buscar por población total"></th>
                            <th scope="col"><input class="form-control form-control-sm column-filter" type="search" size="1" placeholder="Filtrar…" aria-label="Buscar por población de mujeres"></th>
                            <th scope="col"><input class="form-control form-control-sm column-filter" type="search" size="1" placeholder="Filtrar…" aria-label="Buscar por población de hombres"></th>
                            <th scope="col"><input class="form-control form-control-sm column-filter" type="search" size="1" placeholder="Filtrar…" aria-label="Buscar por viviendas habitadas"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($states as $state)
                        <tr>
                            <th scope="row" class="text-center"><span class="badge text-bg-light border font-monospace">{{ $state->code }}</span></th>
                            <td>
                                <a
                                    class="d-inline-flex align-items-center gap-2 fw-semibold text-decoration-none"
                                    href="{{ route('states.municipalities', $state) }}">
                                    {{ $state->name }}
                                </a>
                            </td>
                            <td>{{ $state->abbreviation ?? '—' }}</td>
                            <td class="text-end" data-order="{{ $state->total_population }}">
                                <span class="fw-semibold font-monospace">{{ number_format($state->total_population) }}</span>
                            </td>
                            <td class="text-end" data-order="{{ $state->female_population ?? -1 }}">
                                <span class="font-monospace">{{ $state->female_population === null ? '—' : number_format($state->female_population) }}</span>
                            </td>
                            <td class="text-end" data-order="{{ $state->male_population ?? -1 }}">
                                <span class="font-monospace">{{ $state->male_population === null ? '—' : number_format($state->male_population) }}</span>
                            </td>
                            <td class="text-end" data-order="{{ $state->inhabited_dwellings ?? -1 }}">
                                <span class="font-monospace">{{ $state->inhabited_dwellings === null ? '—' : number_format($state->inhabited_dwellings) }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
        </section>
        @endif
    </main>
</body>

</html>
