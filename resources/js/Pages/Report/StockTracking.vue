<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportLayout from '@/Layouts/ReportLayout.vue';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    data: {
        type: Object,
    },
    username: {
        type: String,
    },
    company: {
        type: String,
    },
    from: {
        type: String,
    },
    to: {
        type: String,
    },
});

const printDiv = (divName) => {
    window.print();
}

</script>

<template>

    <Head title="Stock Tracking" />
    <AuthenticatedLayout>
        <ReportLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Stock Tracking Report</h1>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card overflow-auto">
                    <div class="card-body">
                        <div id="printableArea">
                            <table width="100%">
                                <tr>
                                    <th width="37%">
                                        <div class="flex flex-col text-sm">
                                            <span>Generated On: {{ new Date().toLocaleDateString('en-GB') }}</span>
                                            <span>User Name: {{ username }}</span>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="font-semibold text-blue-500 text-center">
                                            <h1>{{ company[0] }}</h1>
                                            <h1>Stock Tracking Report</h1>
                                        </div>
                                    </th>
                                    <th>
                                        <div class="flex flex-col text-sm text-end">
                                            <span>From: {{ from }}</span>
                                            <span>To: {{ to }}</span>
                                        </div>
                                    </th>
                                </tr>
                                <tr class="no-print">
                                    <td>
                                        <br>
                                        <div class="d-flex gap-3">
                                            <i class="fas fa-print" @click="printDiv()"
                                            accesskey="p" style="cursor: pointer;" title="Print"></i>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sr#</th>
                                        <th>Date</th>
                                        <th>User</th>
                                        <th>Product</th>
                                        <th>Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(account, index) in data.data" :key="index">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ new Date(account.day).toLocaleDateString('en-GB') }}</td>
                                        <td>{{ account.user_name }}</td>
                                        <td>{{ account.product_name }}</td>
                                        <td>{{ account.qty }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7">
                                            <div class="d-flex flex-column justify-content-center" v-if="data.links">
                                                <Pagination :pagination="data" />
                                                <h6 class="text-center copyrights" style="font-size: 9px; display: none;"><span style="font-family: sans;">Developed By Arbaz Ehsan;</span> 03137747660</h6>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </ReportLayout>
    </AuthenticatedLayout>
</template>
