import Collapse from 'bootstrap/js/dist/collapse';
import DataTable from 'datatables.net-bs5';

const navbar = document.querySelector('.site-navbar');

if (navbar) {
    let scrollFrame;

    const updateNavbar = () => {
        navbar.classList.toggle('is-compact', window.scrollY > 24);
        scrollFrame = undefined;
    };

    const handleScroll = () => {
        if (scrollFrame === undefined) {
            scrollFrame = window.requestAnimationFrame(updateNavbar);
        }
    };

    updateNavbar();
    window.addEventListener('scroll', handleScroll, { passive: true });
}

const table = document.querySelector('#states-table');

if (table) {
    const dataTableLanguage = (items, infoSuffix = '') => ({
        aria: {
            paginate: {
                first: 'Primera página',
                last: 'Última página',
                next: 'Página siguiente',
                previous: 'Página anterior',
            },
        },
        emptyTable: `No hay ${items} disponibles`,
        info: `Mostrando _START_ a _END_ de _TOTAL_ ${items}${infoSuffix}`,
        infoEmpty: `No hay ${items} para mostrar${infoSuffix}`,
        infoFiltered: `(filtrados de _MAX_ ${items} totales)`,
        lengthMenu: `Mostrar _MENU_ ${items}${infoSuffix}`,
        zeroRecords: `No se encontraron ${items}`,
        paginate: {
            first: '<i class="bi bi-chevron-bar-left" aria-hidden="true"></i><span class="visually-hidden">Primera página</span>',
            last: '<i class="bi bi-chevron-bar-right" aria-hidden="true"></i><span class="visually-hidden">Última página</span>',
            next: '<i class="bi bi-chevron-right" aria-hidden="true"></i><span class="visually-hidden">Página siguiente</span>',
            previous: '<i class="bi bi-chevron-left" aria-hidden="true"></i><span class="visually-hidden">Página anterior</span>',
        },
    });

    const addColumnFilters = (dataTableElement, dataTable) => {
        dataTableElement.querySelectorAll('.column-filter').forEach((input) => {
            const columnIndex = input.closest('th')?.cellIndex;

            if (columnIndex === undefined) {
                return;
            }

            input.addEventListener('click', (event) => event.stopPropagation());
            input.addEventListener('input', () => {
                const column = dataTable.column(columnIndex);

                if (column.search() !== input.value) {
                    column.search(input.value).draw();
                }
            });
        });
    };

    const addBootstrapLayout = (dataTableElement) => {
        const wrapper = dataTableElement.closest('.dt-container');

        wrapper?.querySelector('.dt-length')
            ?.closest('.row')
            ?.classList.add('data-table-toolbar');

        wrapper?.querySelector('.dt-info')
            ?.closest('.row')
            ?.classList.add('data-table-footer');
    };

    const initializeDataTable = (dataTableElement, options) => {
        const dataTable = new DataTable(dataTableElement, {
            pageLength: options.pageLength,
            lengthMenu: options.lengthMenu,
            order: [[0, 'asc']],
            columnDefs: options.columnDefs ?? [],
            layout: {
                topStart: 'pageLength',
                topEnd: null,
                bottomStart: 'info',
                bottomEnd: 'paging',
            },
            language: dataTableLanguage(options.items, options.infoSuffix),
        });

        addBootstrapLayout(dataTableElement);
        addColumnFilters(dataTableElement, dataTable);

        return dataTable;
    };

    const dataTable = initializeDataTable(table, {
        pageLength: 16,
        lengthMenu: [16, 32],
        items: 'estados',
        columnDefs: [
            {
                targets: -1,
                orderable: false,
                searchable: false,
            },
        ],
    });

    const detailsByRow = new WeakMap();
    const numberFormatter = new Intl.NumberFormat('es-MX');
    const escapeHtml = (value) => String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');

    const setActionState = (action, expanded) => {
        const stateName = action.dataset.stateName;
        const actionText = expanded ? 'Ocultar municipios' : 'Mostrar municipios';

        action.setAttribute('aria-expanded', String(expanded));
        action.setAttribute('aria-label', `${actionText} de ${stateName}`);
        action.textContent = actionText;
        action.classList.toggle('btn-primary', expanded);
        action.classList.toggle('btn-outline-primary', !expanded);
    };

    const renderLoading = (panel) => {
        panel.innerHTML = `
            <div class="d-flex align-items-center gap-2 p-4" role="status">
                <span class="spinner-border spinner-border-sm" aria-hidden="true"></span>
                <span>Cargando municipios…</span>
            </div>
        `;
    };

    const renderMunicipalities = (panel, action, municipalities) => {
        const stateName = escapeHtml(action.dataset.stateName);
        const rows = municipalities.map((municipality) => {
            const population = municipality.total_population === null
                ? '<span class="text-secondary">No disponible</span>'
                : `<span class="font-monospace">${numberFormatter.format(municipality.total_population)}</span>`;

            return `
                <tr>
                    <th scope="row" class="text-center"><span class="badge text-bg-light border font-monospace">${escapeHtml(municipality.code)}</span></th>
                    <td>${escapeHtml(municipality.name)}</td>
                    <td class="text-end">${population}</td>
                </tr>
            `;
        }).join('');

        panel.innerHTML = `
            <div class="bg-body-tertiary p-4">
                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle text-nowrap w-100" aria-label="Municipios de ${stateName}">
                        <thead class="table-light">
                            <tr>
                                <th scope="col" class="text-center">Clave</th>
                                <th scope="col">Municipio</th>
                                <th scope="col" class="text-end">Población total</th>
                            </tr>
                            <tr class="column-filters" data-dt-order="disable">
                                <th scope="col" class="text-center"><input class="form-control form-control-sm column-filter text-center" type="search" placeholder="Filtrar…" aria-label="Buscar por clave municipal"></th>
                                <th scope="col"><input class="form-control form-control-sm column-filter" type="search" placeholder="Filtrar…" aria-label="Buscar por municipio"></th>
                                <th scope="col"><input class="form-control form-control-sm column-filter" type="search" placeholder="Filtrar…" aria-label="Buscar por población municipal"></th>
                            </tr>
                        </thead>
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>
        `;

        const municipalitiesTable = panel.querySelector('table');

        initializeDataTable(municipalitiesTable, {
            pageLength: 5,
            lengthMenu: [5, 10, 15, 20],
            items: 'municipios',
            infoSuffix: ` de ${stateName}`,
        });
    };

    const loadMunicipalities = async (panel, action) => {
        renderLoading(panel);

        try {
            const response = await fetch(action.dataset.detailsUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (!response.ok) {
                throw new Error(`Municipality request failed with status ${response.status}`);
            }

            const payload = await response.json();

            if (!Array.isArray(payload.municipalities)) {
                throw new Error('Municipality response has an invalid format');
            }

            renderMunicipalities(panel, action, payload.municipalities);
        } catch (error) {
            console.error(error);
            panel.innerHTML = `
                <div class="alert alert-danger m-3 d-flex align-items-center justify-content-between gap-3" role="alert">
                    <span>No fue posible cargar los municipios.</span>
                    <button class="btn btn-sm btn-outline-danger" type="button" data-retry-municipalities>Reintentar</button>
                </div>
            `;
        }
    };

    table.addEventListener('click', (event) => {
        const retry = event.target.closest('[data-retry-municipalities]');

        if (retry) {
            const panel = retry.closest('.collapse');
            const rowNode = panel?.parentElement?.parentElement?.previousElementSibling;
            const action = rowNode?.querySelector('[data-state-details]');

            if (panel && action) {
                loadMunicipalities(panel, action);
            }

            return;
        }

        const action = event.target.closest('[data-state-details]');

        if (!action) {
            return;
        }

        const rowNode = action.closest('tr');
        const row = dataTable.row(rowNode);
        let details = detailsByRow.get(rowNode);

        if (action.getAttribute('aria-expanded') === 'true') {
            details?.collapse.hide();
            return;
        }

        if (!details) {
            const panel = document.createElement('div');
            panel.className = 'collapse';

            const collapse = new Collapse(panel, { toggle: false });
            details = { collapse, panel, row };
            detailsByRow.set(rowNode, details);

            panel.addEventListener('hidden.bs.collapse', () => {
                details.row.child.hide();
                setActionState(action, false);
            });

            loadMunicipalities(panel, action);
        }

        details.row.child(details.panel, 'p-0').show();
        setActionState(action, true);
        details.collapse.show();
    });
}
