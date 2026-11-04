<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Search from '@/Components/Search.vue';
import Toast from '@/Components/Toast.vue';


defineProps({
    accounts: {
        type: Object,
    },
});


const computedtext = (e) => {
    let txt = "Unactive";
    if (e == 1) {
        txt = "Active";
    }
    return txt;
}

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

    <Head title="Accounts" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Accounts</h1>
            <Link :href="$page.props.auth.user.type == 'Admin' || $page.props.auth.user.permission?.pages?.account_create ? route('createAccount') : '#'" class="btn btn-success">New</Link>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card overflow-auto">
                    <div class="card-body">
                        <table class="table datatable">
                            <thead class="text-sm text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th>Type</th>
                                    <th>A/C#</th>
                                    <th>A/C Title</th>
                                    <th>City</th>
                                    <th>Address</th>
                                    <th>Contact.Per</th>
                                    <th>Mobile</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="account, index in accounts">
                                    <td>{{ account.type }}</td>
                                    <td>{{ account.id }}</td>
                                    <td>{{ account.name }}</td>
                                    <td>{{ account.city }}</td>
                                    <td>{{ account.address }}</td>
                                    <td>{{ account.contact_person }}</td>
                                    <td>{{ account.mobile }}</td>
                                    <td>
                                        <span class="bg-green-500 py-1 px-1 rounded text-white text-xs"
                                            :class="{ 'bg-red-500': account.status === 0 }">
                                            {{ computedtext(account.status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div v-if="$page.props.auth.user.type == 'Admin' || $page.props.auth.user.permission?.pages?.account_update">
                                            <Link :href="route('editAccount',account.id)">
                                                <i class="fas fa-edit"></i>
                                            </Link>
                                            <Link :href="route('statusAccount', [account.status,account.id])">
                                                <i class="fas fa-exchange-alt ml-3"></i>
                                            </Link>
                                        </div>
                                        <div v-else>Restricted</div>
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
