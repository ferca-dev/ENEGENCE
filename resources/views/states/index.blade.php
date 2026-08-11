@extends('layouts.app')

@section('title', 'Estados de México | ' . e(config('app.name')))
@section('meta_description', 'Listado de entidades federativas de México obtenido desde INEGI')
@section('main_class', 'container-fluid flex-grow-1 px-0')

@section('content')
    @if ($states->isEmpty())
        <output class="alert alert-info shadow-sm d-block">
            No hay estados disponibles. Ejecuta
            <code>php artisan inegi:sync-states</code> o
            <code>make vercel-sync-states</code>
            para cargar la información.
        </output>
    @else
        <div class="container-fluid py-5">
            <noscript>
                <div class="alert alert-warning">
                    La tabla completa está disponible, pero la búsqueda, el orden y la paginación requieren JavaScript.
                </div>
            </noscript>

            <section class="card mx-auto shadow" aria-labelledby="states-table-title">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="states-table" class="table table-hover align-middle text-nowrap w-100">
                            <caption class="visually-hidden">Clave, nombre, abreviatura, población y viviendas habitadas de los estados de México</caption>
                            <thead class="table-light">
                                <tr>
                                    <th scope="col" class="text-center">Clave</th>
                                    <th scope="col">Estado</th>
                                    <th scope="col" class="text-end">Población total</th>
                                    <th scope="col" class="text-end">Mujeres</th>
                                    <th scope="col" class="text-end">Hombres</th>
                                    <th scope="col" class="text-end">Viviendas habitadas</th>
                                    <th scope="col" class="text-center">Acciones</th>
                                </tr>
                                <tr class="column-filters" data-dt-order="disable">
                                    <th scope="col" class="text-center"><input class="form-control form-control-sm column-filter text-center" type="search" size="1" placeholder="Filtrar…" aria-label="Buscar por clave"></th>
                                    <th scope="col"><input class="form-control form-control-sm column-filter" type="search" size="1" placeholder="Filtrar…" aria-label="Buscar por estado"></th>
                                    <th scope="col"><input class="form-control form-control-sm column-filter" type="search" size="1" placeholder="Filtrar…" aria-label="Buscar por población total"></th>
                                    <th scope="col"><input class="form-control form-control-sm column-filter" type="search" size="1" placeholder="Filtrar…" aria-label="Buscar por población de mujeres"></th>
                                    <th scope="col"><input class="form-control form-control-sm column-filter" type="search" size="1" placeholder="Filtrar…" aria-label="Buscar por población de hombres"></th>
                                    <th scope="col"><input class="form-control form-control-sm column-filter" type="search" size="1" placeholder="Filtrar…" aria-label="Buscar por viviendas habitadas"></th>
                                    <th scope="col"><span class="visually-hidden">Acciones sin filtro</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($states as $state)
                                <tr>
                                    <th scope="row" class="text-center"><span class="badge text-bg-light border font-monospace">{{ $state->code }}</span></th>
                                    <td>
                                        <span class="fw-semibold">{{ $state->name }}</span>
                                        <span>({{ $state->abbreviation ?? '—' }})</span>
                                    </td>
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
                                    <td class="text-center">
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary"
                                            data-state-details
                                            data-state-name="{{ $state->name }}"
                                            data-details-url="{{ route('states.municipalities', $state) }}"
                                            aria-expanded="false"
                                            aria-label="Mostrar municipios de {{ $state->name }}">
                                            Mostrar municipios
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </div>
    @endif
@endsection
