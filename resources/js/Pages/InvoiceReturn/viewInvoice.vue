<script setup>
import { Head, router, Link } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Toast from '@/Components/Toast.vue';

const props = defineProps({
    from: String,
    url: String,
    url2: String,
    invoices: Object,
    filters: Object,
});

const startDate = ref(props.filters.start_date || '');
const endDate = ref(props.filters.end_date || '');
const search = ref(props.filters.search || '');

const filter = () => {
    router.get(
        route(route().current()),
        { start_date: startDate.value, end_date: endDate.value, search: search.value },
        { preserveState: true, replace: true }
    );
};

const resetForm = () => {
    startDate.value = '';
    endDate.value = '';
    search.value = '';
    filter();
};

</script>

<template>

    <Head title="Invoices" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">View {{ from }} Return</h1>
            
        </div>

                <div class="row pt-4">
            <div class="col-md-12">
                <div class="card overflow-auto">
                    <div class="card-body">
                        <span class="mb-1">Filters</span>
                        <div class="flex space-x-2 mb-4">
                            <input type="text" v-model="search" class="border p-2 rounded" placeholder="Search Invoice #">
                            <input type="date" v-model="startDate" class="border p-2 rounded" placeholder="Start Date">
                            <label class="mt-2">To</label>
                            <input type="date" v-model="endDate" class="border p-2 rounded" placeholder="End Date">
                            <button @click="filter" class="btn btn-primary text-white px-4 py-2 rounded">Filter</button>
                            <button @click="resetForm" class="btn btn-secondary text-white px-4 py-2 rounded">Reset</button>
                        </div>
                        
                        
                    </div>
                </div>
            </div>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card overflow-auto">
                    <div class="card-body">
                        <span class="text-xs mb-1">*showing current year records</span>
                        <table class="table table-striped">
                            <thead class="text-sm text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th>Day</th>
                                    <th>Invoice_No</th>      
                                    <th>Reference</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="invoice in invoices.data" :key="invoice.id">
                                    <td>{{ new Date(invoice.day).toLocaleDateString('en-GB') }}</td>
                                    <td>{{ invoice.id }}</td>
                                    <td>{{ invoice.ref }}</td>
                                    <td>{{ (invoice.return_status == "Returned" ? "Returned" : (invoice.return_status == "Credit_Note_Issued" ? "Credit Note Issued" : (invoice.return_status == "Debit_Note_Issued"? "Debit Note Issued" : "" ) ) )    }}</td>
                                    <td>
                                        <a class="btn btn-primary btn-sm" target="_blank" :href="`/${url}/${invoice.id}`">Print</a>
                                        <Link v-if="invoice.return_status == 'Original'" class="btn btn-danger btn-sm ml-2" :href="from == 'Sale' ? route('saleReturn', invoice.id) : route('purchaseReturn', invoice.id)">Return</Link>
                                    </td>
                                </tr>
                                <tr v-if="invoices.data.length === 0">
                                    <td colspan="5" class="text-center">No records found.</td>
                                </tr>
                            </tbody>
                        </table>
                        
                        <div class="d-flex justify-content-center mt-4" v-if="invoices.links">
                            <Pagination :pagination="invoices" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <Toast v-if="$page.props.flash.message" :msg="$page.props.flash.message" />

    </AuthenticatedLayout>
</template>
