<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { onMounted, defineProps } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Toast from '@/Components/Toast.vue';
import Modal2 from '@/Components/Modal2.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import CategorySelector from '@/Components/CategorySelector.vue';
import axios from 'axios';

const props = defineProps({
    categories: Object,
});

const form = useForm({
    cat_id: 0,
    cat_name: '',
    parent_id: null,
});

const mockCategories = [
    {
        id: 1,
        name: 'Beauty & Personal Care',
        children: [
            {
                id: 11,
                name: 'Personal Care',
                children: [
                    { id: 111, name: 'Hair Care' },
                    { id: 112, name: 'Skin Care' },
                    { id: 113, name: 'Oral Care' }
                ]
            },
            {
                id: 12,
                name: 'Cosmetics',
                children: [
                    { id: 121, name: 'Makeup' },
                    { id: 122, name: 'Nail Care' }
                ]
            },
             { id: 13, name: 'Fragrances' }
        ]
    },
    {
        id: 2,
        name: 'Womenswear & Underwear',
        children: [
            { id: 21, name: 'Clothing' },
            { id: 22, name: 'Shoes' },
            { id: 23, name: 'Bags' }
        ]
    },
    {
        id: 3,
        name: 'Menswear & Underwear',
        children: [
             { id: 31, name: 'Clothing' },
             { id: 32, name: 'Shoes' },
        ]
    },
    {
        id: 4,
        name: 'Home Supplies',
        children: [
            { id: 41, name: 'Home Organizers' },
            { id: 42, name: 'Laundry Supplies' }
        ]
    },
     {
        id: 5,
        name: 'Electronics',
        children: [
            { id: 51, name: 'Phones & Accessories' },
            { id: 52, name: 'Computers & Office Equipment' },
             { id: 53, name: 'Household Appliances' }
        ]
    },
    {
        id: 6,
        name: 'Health & Wellness',
        children: [
             { id: 61, name: 'Dietary Supplements' }
        ]
    },
    {
        id: 7,
        name: 'Kids\' Fashion',
        children: [] // Subcategories exist but not listed for brevity
    },
     {
        id: 8,
        name: 'Baby & Maternity',
        children: [
             { id: 81, name: 'Baby Care & Health' },
             { id: 82, name: 'Baby Clothing & Shoes' }
        ]
    },
    {
        id: 9,
        name: 'Sports & Outdoors',
        children: []
    },
    {
        id: 10,
        name: 'Food & Beverages',
        children: [
            {
                id: 101,
                name: 'Shelf-Stable Food & Beverage',
                children: [
                    { id: 1011, name: 'Pantry Food' }, // Canned goods, pasta, rice, certain spreads
                    { id: 1012, name: 'Snacks, Chips, Salsas & Dips' }, // Includes packaged sweets/chocolates
                    { id: 1013, name: 'Baking Supplies' },
                    { id: 1014, name: 'Beverages (Shelf-Stable)' },
                    { id: 1015, name: 'Cereals & Breakfast Foods' }
                ]
            },
            // Note: Fresh items like raw chicken or fresh bakery items are generally prohibited.
        ]
    },
    {
        id: 11,
        name: 'Pet Supplies',
        children: []
    },
    {
        id: 12,
        name: 'Toys & Hobbies',
        children: []
    }
];


const page = usePage()
const permissions = page.props.auth.user.permission;
const user = page.props.auth.user;

const submit = () => {
    form.post(route('insertCategory'), {
        forceFormData: true, 
        onSuccess: (page) => {
            if (page.props.flash.message) {
                $('#modal_category').modal('hide');
                $(".datatable").DataTable().ajax.reload();
                form.reset();
            }
        },
        onError: (errors) => {
            console.error("Error:", errors);
        },
    });
}

const addEdit = async (id) => {
    axios.get(route('addEditCategory', { id: id }))
        .then(response => {
            // console.log("Success:", response.data);
            form.cat_id = response.data?.id || 0;
            form.cat_name = response.data?.name || '';
            form.parent_id = response.data?.parent_id || null;
            $('#modal_category').modal('toggle');
        })
        .catch(error => {
            // console.error("Error:", error);
            alert("Error: " + (error.response?.data?.message || error.message));
        });
};

const computedtext = (status) => (status == 1 ? "Active" : "Unactive");

onMounted(() => {
    window.props = props;
    $(".datatable").DataTable({
        processing: true,
        serverSide: false,
        destroy: true,
        ajax: {
            url: "/category",
            type: "GET",
            dataSrc: "" // Because you're returning a plain array 
        },
        columns: [
            {
                data: null,
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                },
                orderable: false,
            },
            { data: "id" },
            { data: "name" },
            { data: "parent_id" },
            {
                data: "status",
                render: function (data) {
                    return `<span class="bg-green-500 py-1 px-1 rounded text-white text-xs ${data === 0 ? 'bg-red-500' : ''}">
                                ${data === 1 ? "Active" : "Unactive"}
                            </span>`;
                },
                orderable: false,
            },
            {
                data: "id",
                render: function (data, type, row) {
                    if(user.type == 'Admin' || permissions?.category_update)
                    {
                        return `
                            <button data-href="/editCategory/${data}" data-id="${data}" class="mr-2 edit-btn"><i class="fas fa-edit"></i></button>
                            <a href="/statusCategory/${row.status}/${data}" class="ml-2"><i class="fas fa-exchange-alt"></i></a>
                        `;
                    }
                    else
                    {
                        return `Restricted`;
                    } 
                },
                orderable: false,
            },
        ],
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
                    doc.styles.tableHeader = {
                        fillColor: "#4E73DF", // Bootstrap Blue
                        color: "white",
                        alignment: "center",
                        bold: true,
                    };
                    var table = $(".datatable").DataTable();
                    var colCount = table.columns().header().length;

                    // **Ensure Table Fills the Page Width**
                    doc.content[1].table.widths = Array(colCount).fill("*"); // Distribute columns equally

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
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        pageLength: 10,
    });

    $(document).off('click', '.edit-btn').on('click', '.edit-btn', function (event) {
        event.preventDefault();
        const id = $(this).data('id');
        addEdit(id);
    });
});
</script>

<template>

    <Head title="Category" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-between">
            <h1 class="h3 mb-0 text-gray-800"><i class="fa fa-th-large fa-1"></i> Category {{ categories }}</h1>
            <!-- <Link :href="route('createCategory')" class="btn btn-success">New</Link> -->
            <PrimaryButton data-href="route('createCategory')" @click="$page.props.auth.user.type == 'Admin' || $page.props.auth.user.permission?.pages?.category_create ? addEdit(0) : ''" class="btn btn-primary">New</PrimaryButton>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card overflow-auto">
                    <div class="card-body">
                        <table class="table datatable">
                            <thead class="text-sm text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th>Sr#</th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Parent</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <Toast v-if="$page.props.flash.message" :msg="$page.props.flash.message" />

        <!-- add Edit Form -->
        <Modal2 id="modal_category" title="Category">
            <div class="row">
                <div class="col-md-12">
                    <form @submit.prevent="submit">
                        <div class="row">
                            <div class="col-md-12">
                                <InputLabel for="cat_parent" value="Parent Category" />
                                <CategorySelector
                                    v-model="form.parent_id"
                                    :categories="mockCategories"
                                    placeholder="Select Parent Category"
                                    class="mt-1"
                                />
                            </div>
                            <!--<div class="col-md-12 mt-3">
                                <InputLabel for="cat_name">
                                    Name <span class="text-red-600">*</span>
                                </InputLabel>
                                <TextInput id="cat_name" type="text" class="mt-1 block w-full" v-model="form.cat_name"
                                    required autofocus placeholder="Enter Name" />
                                <TextInput id="cat_id" type="text" class="mt-1 block w-full" v-model="form.cat_id" hidden/>
                                <InputError class="mt-2" :message="form.errors.cat_name" />
                            </div> -->
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-12">
                                <PrimaryButton class="btn btn-primary">
                                    Save
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </Modal2>

    </AuthenticatedLayout>
</template>
