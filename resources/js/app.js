import DataTable from 'datatables.net-bs5';
import 'datatables.net-responsive-bs5';

const table = document.querySelector('#states-table');

if (table) {
    const statesPerPage = 15;
    const totalStates = table.tBodies[0]?.rows.length ?? 0;
    const lengthMenu = [];

    for (let length = statesPerPage; length < totalStates; length += statesPerPage) {
        lengthMenu.push(length);
    }

    lengthMenu.push(totalStates);

    new DataTable(table, {
        responsive: true,
        pageLength: Math.min(statesPerPage, totalStates),
        lengthMenu,
        order: [[0, 'asc']],
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
            search: 'Buscar:',
            zeroRecords: 'No se encontraron estados',
            paginate: {
                first: '<i class="bi bi-chevron-bar-left" aria-hidden="true"></i><span class="visually-hidden">Primera página</span>',
                last: '<i class="bi bi-chevron-bar-right" aria-hidden="true"></i><span class="visually-hidden">Última página</span>',
                next: '<i class="bi bi-chevron-right" aria-hidden="true"></i><span class="visually-hidden">Página siguiente</span>',
                previous: '<i class="bi bi-chevron-left" aria-hidden="true"></i><span class="visually-hidden">Página anterior</span>',
            },
        },
    });
}
