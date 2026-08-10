<!doctype html>
<html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="description" content="Municipios de {{ $state->name }} obtenidos desde INEGI">
        <title>Municipios de {{ $state->name }} | {{ config('app.name') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-vh-100 bg-body-tertiary text-body">
        <nav class="navbar sticky-top bg-dark shadow-sm site-navbar" data-bs-theme="dark">
            <div class="container-fluid">
                <a class="navbar-brand site-brand" href="{{ route('states.index') }}">
                    <span class="site-brand__title">ENEGENCE</span>
                    <span class="site-brand__subtitle">Prueba técnica - Fernando Cárdenas</span>
                </a>
                <a class="btn btn-sm btn-outline-light d-inline-flex align-items-center gap-2" href="{{ route('states.index') }}">
                    <i class="bi bi-arrow-left" aria-hidden="true"></i>
                    <span>Volver a estados</span>
                </a>
            </div>
        </nav>

        <main class="container-fluid py-5">
            <header class="mb-4">
                <p class="text-uppercase text-primary fw-semibold small mb-2">Estado {{ $state->code }}</p>
                <h1 class="display-6 fw-bold mb-2">Municipios de {{ $state->name }}</h1>
                <p class="text-secondary mb-0">
                    Información consultada directamente en el catálogo geográfico de INEGI.
                </p>
            </header>

            @if ($loadError)
                <div class="alert alert-danger shadow-sm" role="alert">
                    <h2 class="h5 alert-heading">No fue posible consultar los municipios</h2>
                    <p class="mb-3">INEGI no respondió con información válida. Intenta nuevamente en unos minutos.</p>
                    <a class="alert-link" href="{{ route('states.municipalities', $state) }}">Volver a intentar</a>
                </div>
            @else
                <section class="card overflow-hidden border shadow-sm" aria-labelledby="municipalities-table-title">
                    <div class="card-body p-4">
                        <div class="d-flex flex-column flex-sm-row justify-content-between gap-3 border-bottom pb-3 mb-4">
                            <div>
                                <h2 class="h5 mb-1" id="municipalities-table-title">Municipios</h2>
                                <p class="text-secondary small mb-0">Listado completo ordenado por clave municipal.</p>
                            </div>
                            <span class="badge rounded-pill text-bg-primary align-self-start px-3 py-2">
                                {{ count($municipalities) }} municipios
                            </span>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <caption class="visually-hidden">
                                    Clave, nombre y población total de los municipios de {{ $state->name }}
                                </caption>
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Clave</th>
                                        <th scope="col">Municipio</th>
                                        <th scope="col" class="text-end">Población total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($municipalities as $municipality)
                                        <tr>
                                            <th scope="row"><span class="badge text-bg-light border font-monospace">{{ $municipality['code'] }}</span></th>
                                            <td>{{ $municipality['name'] }}</td>
                                            <td class="text-end">
                                                @if ($municipality['total_population'] === null)
                                                    <span class="text-secondary">No disponible</span>
                                                @else
                                                    <span class="fw-semibold font-monospace">{{ number_format($municipality['total_population']) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            @endif
        </main>
    </body>
</html>
