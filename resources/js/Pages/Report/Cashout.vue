<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportLayout from '@/Layouts/ReportLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Search from '@/Components/Search.vue';
import Toast from '@/Components/Toast.vue';
import { formToJSON } from 'axios';
import { computed } from 'vue';


const props = defineProps({
    data: {
        type: Object,
    },
    username: {
        type: String,
    },
    company: {
        type: Array,
    },
    from: {
        type: String,
    },
    to: {
        type: String,
    },
});

const totalAmount = computed(() => {
    return props.data.data.reduce((sum, item) => sum + Number(item.amount), 0);
});

const printDiv = (divName) => {
    // const printContents = document.getElementById(divName).innerHTML;
    // const originalContents = document.body.innerHTML;
    // document.body.innerHTML = printContents;
    window.print();
    // document.body.innerHTML = originalContents;
}

</script>

<template>

    <Head title="Cashout" />
    <AuthenticatedLayout>
        <ReportLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Cashout Report</h1>
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
                                            <h1>Cashout Report</h1>
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
                                        <th>Invoice #</th>
                                        <th>Customer</th>
                                        <th>Naration</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(tran, index) in data.data" :key="tran.id">
                                        <td>{{ (data.current_page - 1) * data.per_page + index + 1 }}</td>
                                        <td>{{ new Date(tran.created_at).toLocaleString('en-GB', { hour12: true }) }}</td>
                                        <td>{{ tran.id }}</td>
                                        <td>{{ tran.account ? tran.account.name : 'N/A' }}</td>
                                        <td>{{ tran.remarks }}</td>
                                        <td>{{ tran.amount }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="4"></td>
                                        <td class="pull-right"><b>Total</b></td>
                                        <td><b>{{ totalAmount }}</b></td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="7">
                                        <div class="d-flex flex-column justify-content-center" v-if="data.links">
                                            <Pagination :pagination="data" />
                                            <h6 class="text-center copyrights" style="font-size: 9px; display: none;"><span style="font-family: sans;">Developed By Arbaz Ehsan;</span> 03137747660; arbazehsan988@gmail.com</h6>
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