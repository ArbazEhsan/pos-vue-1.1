<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DataTableNew from '@/Components/DataTableNew.vue';

const columns = [
    { key: 'id', label: 'ID' },
    { key: 'name', label: 'Name' },
    { key: 'purchase', label: 'Purchase', type: 'number' },
    { key: 'whole_sale', label: 'Whole Sale', type: 'number' },
    { key: 'retail', label: 'Retail', type: 'number' },
    { key: 'type', label: 'Type' },
    { key: 'qty', label: 'Qty' },
]

</script>

<template>

    <Head title="User" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <!-- <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Testing Table</h1>
        </div> -->

        <div class="row pt-2">
            <div class="col-md-12">
                <DataTableNew :apiUrl="'/getProductDetails'" :columns="columns" :deletable="false" :viewable="false"
                    :title="'Products'" />
            </div>
        </div>

    </AuthenticatedLayout>
</template>
