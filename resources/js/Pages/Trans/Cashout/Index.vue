<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Search from '@/Components/Search.vue';
import Toast from '@/Components/Toast.vue';
import BaseDataTable from '@/Components/baseDataTable.vue';


defineProps({
    transactions: {
        type: Object,
    },
});

// datatable and select2 initialization
$(document).ready(function() {
    
    $('.js-example-basic-single').select2({
        placeholder: "Select an option",
        allowClear: true
    });

    $(".datatable").DataTable({
        dom: "<'flex items-center justify-between' <'flex items-center space-x-4' lB> f> r t i p",
        buttons: [
            {
                extend: "excelHtml5",
                text: '<i class="fas fa-file-excel"></i> Excel',
                className: "bg-green-500 text-black text-xs px-3 py-1 rounded",
                exportOptions: {
                    columns: function (idx, data, node) {
                        // Get the column title
                        var colTitle = $(node).text().trim();
                        return colTitle !== "Action"; // Exclude "Action" column
                    },
                },
            },
            {
                extend: "pdfHtml5",
                text: '<i class="fas fa-file-pdf"></i> PDF',
                className: "bg-red-500 text-black text-xs px-3 py-1 rounded",
                exportOptions: {
                    columns: function (idx, data, node) {
                        // Get the column title
                        var colTitle = $(node).text().trim();
                        return colTitle !== "Action"; // Exclude "Action" column
                    },
                },
                customize: function (doc) {
                    var table = $(".datatable").DataTable();
                    var colCount = table.columns().header().length;

                    // Equal width for all columns
                    var widths = [];
                    for (var i = 0; i < colCount; i++) {
                        widths.push("*");
                    }
                    doc.content[1].table.widths = widths;

                    // **1️⃣ Add Border to Table**
                    doc.content[1].layout = {
                        hLineWidth: function () { return 1; }, // Horizontal line
                        vLineWidth: function () { return 0; }, // Vertical line
                        hLineColor: function () { return "#000000"; }, // Black border
                        vLineColor: function () { return "#000000"; } // Black border
                    };

                    // **2️⃣ Make Header Blue with White Text**
                    doc.styles.tableHeader = {
                        fillColor: "#4E73DF", // Bootstrap Blue
                        color: "white", // White text
                        alignment: "center",
                        bold: true
                    };

                    // **3️⃣ Ensure Table Text is Centered**
                    doc.styles.tableBodyEven = {
                        alignment: "center"
                    };
                    doc.styles.tableBodyOdd = {
                        alignment: "center"
                    };
                },
            },
            {
                extend: "print",
                text: '<i class="fas fa-print"></i> Print',
                className: "bg-yellow-500 text-black text-xs px-3 py-1 rounded",
                exportOptions: {
                    columns: function (idx, data, node) {
                        // Get the column title
                        var colTitle = $(node).text().trim();
                        return colTitle !== "Action"; // Exclude "Action" column
                    },
                },
            },
        ],
        lengthMenu: [10, 25, 50, 100],
        pageLength: 10,
    });
});


</script>

<template>

    <Head title="Cashout" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Cashout</h1>
            <Link :href="$page.props.auth.user.type == 'Admin' || $page.props.auth.user.permission?.pages?.trans_cashout_create ? route('createTransOut') : '#'" class="btn btn-success">New</Link>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card overflow-auto">
                    <div class="card-body">
                    
                         <table class="table datatable">
                            <thead class="text-sm text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th>Sr#</th>
                                    <th>Bill#</th>
                                    <th>Inv #</th>
                                    <th>Customer Name</th>
                                    <th>Remarks</th>
                                    <th>Date</th>
                                    <th>Amt</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="transaction, index in transactions.data">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ transaction.bill_no }}</td>
                                    <td>{{ transaction.id }}</td>
                                    <td>{{ transaction.account.name }}</td>
                                    <td>{{ transaction.remarks }}</td>
                                    <td>{{ new Date(transaction.day).toLocaleDateString('en-GB') }}</td>
                                    <td>{{ transaction.amount }}</td>
                                    <td>
                                        <div v-if="$page.props.auth.user.type == 'Admin' || $page.props.auth.user.permission?.pages?.trans_cashout_delete">
                                            <Link class="text-red-600 hover:text-red-700" :href="route('deleteTransOut', transaction.id)">
                                                <i class="fas fa-trash"></i>
                                            </Link>
                                        </div>
                                        <div v-else>
                                            Restricted
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <Toast v-if="$page.props.flash.message" :msg="$page.props.flash.message" />

    </AuthenticatedLayout>
</template>
