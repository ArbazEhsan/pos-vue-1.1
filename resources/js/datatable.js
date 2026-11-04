// datatable.js

/**
 * Initializes the product data table.
 * @param {object} columnsConfig - The columns configuration object.
 * @param {object} url - The url object.
 */
export function initializeDataTable(columnsConfig, route) {

    $(".datatable").DataTable({
        processing: true,
        serverSide: false,
        destroy: true,
        ajax: {
            url: route,
            type: "GET",
            dataSrc: "" // Because you're returning a plain array 
        },
        columns: columnsConfig,
        dom: "<'flex items-center justify-between' <'flex items-center space-x-4' lB> f> r t i p",
        buttons: [
            // ... (excel, pdf, print buttons configuration as you provided) ...
            {
                extend: "excelHtml5",
                text: '<i class="fas fa-file-excel"></i>',
                className: "bg-gray-200 text-xs px-3 py-1 rounded mb-2",
                exportOptions: {
                    columns: function (idx, data, node) {
                        var colTitle = $(node).text().trim();
                        return colTitle !== "Action";
                    },
                },
            },
            {
                extend: "pdfHtml5",
                text: '<i class="fas fa-file-pdf"></i>',
                className: "bg-gray-200 text-xs px-3 py-1 rounded",
                exportOptions: {
                    columns: function (idx, data, node) {
                        var colTitle = $(node).text().trim();
                        return colTitle !== "Action";
                    },
                },
                customize: function (doc) {
                    doc.styles.tableHeader = {
                        fillColor: "#4E73DF",
                        color: "white",
                        alignment: "center",
                        bold: true,
                    };
                    // Use the selector that matches the table in your Vue component
                    var table = $(".datatable").DataTable();
                    var colCount = table.columns().header().length;
                    doc.content[1].table.widths = Array(colCount).fill("*");

                },
            },
            {
                extend: "print",
                text: '<i class="fas fa-print"></i>',
                className: "bg-gray-200 text-xs px-3 py-1 rounded",
                exportOptions: {
                    columns: function (idx, data, node) {
                        var colTitle = $(node).text().trim();
                        return colTitle !== "Action";
                    },
                },
            },
        ],
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        pageLength: 10,
    });
}
