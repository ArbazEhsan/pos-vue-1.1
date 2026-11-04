<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Table from '@/Components/Table.vue';

const props = defineProps({
    permission: {
        type: Object,
    },
    user: {
        type: Object,
    },
});

const columns = [
    { key: 'sr', label: 'Sr#' },
    { key: 'date', label: 'Date' },
    { key: 'user', label: 'User' },
    { key: 'permission', label: 'Permission' },
];

const rows = [
    { sr: '1', date: '1/1/2025', user: 'Rafique', permission: 'Yes' },
    { sr: '2', date: '1/1/2025', user: 'Rafique', permission: 'Yes' },
    { sr: '3', date: '1/1/2025', user: 'Rafique', permission: 'Yes' },
    { sr: '4', date: '1/1/2025', user: 'Rafique', permission: 'Yes' },
    { sr: '5', date: '1/1/2025', user: 'Rafique', permission: 'Yes' },
    { sr: '6', date: '1/1/2025', user: 'Rafique', permission: 'Yes' },
    { sr: '7', date: '1/1/2025', user: 'Rafique', permission: 'Yes' },
];

</script>

<template>

    <Head title="User" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Testing Table</h1>
        </div>

        <div class="row pt-5">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit(2)">

                            <div class="d-sm-flex align-items-center justify-content-between">
                                <h1 class="h3 mb-0 mt-6 text-gray-800">Set Permission</h1>
                            </div>

                            <div class="row pt-4">
                                <div class="col-md-12">
                                    <Table :rows="rows" :columns="columns" />
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
