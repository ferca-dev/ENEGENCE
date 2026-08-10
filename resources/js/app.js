import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';

const navbar = document.querySelector('.site-navbar');

if (navbar) {
    let scrollFrame;
    const root = document.documentElement;

    const syncNavbarHeight = () => {
        root.style.setProperty('--site-navbar-height', `${navbar.offsetHeight}px`);
    };

    const updateNavbar = () => {
        navbar.classList.toggle('is-compact', window.scrollY > 24);
        scrollFrame = undefined;
    };

    const handleScroll = () => {
        if (scrollFrame === undefined) {
            scrollFrame = window.requestAnimationFrame(updateNavbar);
        }
    };

    syncNavbarHeight();

    if ('ResizeObserver' in window) {
        const navbarObserver = new ResizeObserver(syncNavbarHeight);
        navbarObserver.observe(navbar);
    } else {
        window.addEventListener('resize', syncNavbarHeight);
        navbar.addEventListener('transitionend', syncNavbarHeight);
    }

    updateNavbar();
    window.addEventListener('scroll', handleScroll, { passive: true });
}

const table = document.querySelector('#states-table');

if (table) {
    const statesPerPage = 30;
    const totalStates = table.tBodies[0]?.rows.length ?? 0;
    const lengthMenu = [];

    for (let length = statesPerPage; length < totalStates; length += statesPerPage) {
        lengthMenu.push(length);
    }

    lengthMenu.push(totalStates);

    const dataTable = new DataTable(table, {
        responsive: true,
        pageLength: Math.min(statesPerPage, totalStates),
        lengthMenu,
        order: [[0, 'asc']],
        layout: {
            topStart: 'pageLength',
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging',
        },
        language: {
            aria: {
                paginate: {
                    first: 'Primera página',
                    last: 'Última página',
                    next: 'Página siguiente',
                    previous: 'Página anterior',
                },
            },
            emptyTable: 'No hay estados disponibles',
            info: 'Mostrando _START_ a _END_ de _TOTAL_ estados',
            infoEmpty: 'No hay estados para mostrar',
            infoFiltered: '(filtrados de _MAX_ estados totales)',
            lengthMenu: 'Mostrar _MENU_ estados',
            zeroRecords: 'No se encontraron estados',
            paginate: {
                first: '<i class="bi bi-chevron-bar-left" aria-hidden="true"></i><span class="visually-hidden">Primera página</span>',
                last: '<i class="bi bi-chevron-bar-right" aria-hidden="true"></i><span class="visually-hidden">Última página</span>',
                next: '<i class="bi bi-chevron-right" aria-hidden="true"></i><span class="visually-hidden">Página siguiente</span>',
                previous: '<i class="bi bi-chevron-left" aria-hidden="true"></i><span class="visually-hidden">Página anterior</span>',
            },
        },
    });

    const tableWrapper = document.querySelector('#states-table_wrapper');

    tableWrapper?.querySelector('.dt-length')
        ?.closest('.row')
        ?.classList.add('states-table-toolbar');

    tableWrapper?.querySelector('.dt-info')
        ?.closest('.row')
        ?.classList.add('states-table-footer');

    table.querySelectorAll('.column-filter').forEach((input, columnIndex) => {
        input.addEventListener('click', (event) => event.stopPropagation());
        input.addEventListener('input', () => {
            const column = dataTable.column(columnIndex);

            if (column.search() !== input.value) {
                column.search(input.value).draw();
            }
        });
    });
}
