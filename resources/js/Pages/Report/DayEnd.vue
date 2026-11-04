<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportLayout from '@/Layouts/ReportLayout.vue';


defineProps({
    data: {
        type: Array,
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

let sum = 0;
const compute = (e) => {
    sum += e;
}

const printDiv = (divName) => {
    window.print();
}

</script>

<template>

    <Head title="Day End" />
    <AuthenticatedLayout>
        <ReportLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between no-print">
            <h1 class="h3 mb-0 text-gray-800">Day End Report</h1>
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
                                            <h1>Day End Report</h1>
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
                                        <th>Total Sale</th>
                                        <th>Total Cashin</th>
                                        <th>Total Credit</th>
                                        <th>Total Cash In Hand</th>
                                        <th>Total Cash Out</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="account, index in data">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ new Intl.NumberFormat('en-US').format(account.sale) }}</td>
                                        <td>{{ new Intl.NumberFormat('en-US').format(account.cashin) }}</td>
                                        <td>{{ new Intl.NumberFormat('en-US').format(account.credit) }}</td>
                                        <td>{{ new Intl.NumberFormat('en-US').format(account.cashin) }}</td>
                                        <td>{{ new Intl.NumberFormat('en-US').format(account.cashout) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7">
                                            <div class="d-flex flex-column justify-content-center" v-if="data.links">
                                                <!-- <Pagination :pagination="data" /> -->
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
