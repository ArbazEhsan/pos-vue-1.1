<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import ReportLayout from '@/Layouts/ReportLayout.vue';

defineProps({
    data: {
        type: [Array, Object],
    },
    data2: {
        type: [Array, Object],
    },
    data3: {
        type: [Array, Object],
    },
    data4: {
        type: [Array, Object],
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
    const printContents = document.getElementById(divName).innerHTML;
    const originalContents = document.body.innerHTML;
    document.body.innerHTML = printContents;
    window.print();
    document.body.innerHTML = originalContents;
}

</script>

<template>

    <Head title="Day End Detail" />
    <AuthenticatedLayout>
        <ReportLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Day End Detail Report</h1>
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
                                            <h1>Day End Detail Report</h1>
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
                            <div class="row">
                                <div class="col-md-12">
                                    <h1
                                        style="width: 100%; background-color: #E0E0E0;color:black;padding: 10px;font-size: 20px;margin-bottom: 5px;">
                                        Today Sale
                                    </h1>
                                </div>
                            </div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sr#</th>
                                        <th>Date</th>
                                        <th>Sale#</th>
                                        <th>Barcode</th>
                                        <th>Customer</th>
                                        <th>Naration</th>
                                        <th>Total</th>
                                        <th>Paid</th>
                                        <th>Remaining</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="account, index in (data.data ? data.data : data)">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ new Date(account.day).toLocaleDateString('en-GB') }}</td>
                                        <td>{{ account.sale_no }}</td>
                                        <td>{{ account.barcode }}</td>
                                        <td>{{ account.name }}</td>
                                        <td>{{ account.ref }}</td>
                                        <td>{{ account.finalVal }}</td>
                                        <td>{{ account.received }}</td>
                                        <td>{{ account.remaining }}</td>
                                    </tr>
                                </tbody>

                            </table>
                            <div class="d-flex justify-content-center" v-if="data.links">
                                <Pagination :pagination="data" />
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h1
                                        style="width: 100%; background-color: #E0E0E0;color:black;padding: 10px;font-size: 20px;margin-bottom: 5px;">
                                        Today Purchase
                                    </h1>
                                </div>
                            </div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sr#</th>
                                        <th>Date</th>
                                        <th>Purchase#</th>
                                        <th>Barcode</th>
                                        <th>Vender</th>
                                        <th>Naration</th>
                                        <th>Total</th>
                                        <th>Paid</th>
                                        <th>Remaining</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="account, index in (data2.data ? data2.data : data2)">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ new Date(account.day).toLocaleDateString('en-GB') }}</td>
                                        <td>{{ account.pur_no }}</td>
                                        <td>{{ account.barcode }}</td>
                                        <td>{{ account.name }}</td>
                                        <td>{{ account.ref }}</td>
                                        <td>{{ account.finalVal }}</td>
                                        <td>{{ account.received }}</td>
                                        <td>{{ account.remaining }}</td>
                                    </tr>
                                </tbody>

                            </table>
                            <div class="d-flex justify-content-center" v-if="data2.links">
                                <Pagination :pagination="data2" />
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h1
                                        style="width: 100%; background-color: #E0E0E0;color:black;padding: 10px;font-size: 20px;margin-bottom: 5px;">
                                        Today Cashin
                                    </h1>
                                </div>
                            </div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sr#</th>
                                        <th>Date</th>
                                        <th>Invoice#</th>
                                        <th>Name</th>
                                        <th>Remarks</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="account, index in (data3.data ? data3.data : data3)">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ new Date(account.day).toLocaleDateString('en-GB') }}</td>
                                        <td>{{ account.id }}</td>
                                        <td>{{ account.name }}</td>
                                        <td>{{ account.remarks }}</td>
                                        <td>{{ account.amount }}</td>
                                    </tr>
                                </tbody>

                            </table>
                            <div class="d-flex justify-content-center" v-if="data3.links">
                                <Pagination :pagination="data3" />
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <h1
                                        style="width: 100%; background-color: #E0E0E0;color:black;padding: 10px;font-size: 20px;margin-bottom: 5px;">
                                        Today Cashout
                                    </h1>
                                </div>
                            </div>
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Sr#</th>
                                        <th>Date</th>
                                        <th>Invoice#</th>
                                        <th>Name</th>
                                        <th>Remarks</th>
                                        <th>Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="account, index in (data4.data ? data4.data : data4)">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ new Date(account.day).toLocaleDateString('en-GB') }}</td>
                                        <td>{{ account.id }}</td>
                                        <td>{{ account.name }}</td>
                                        <td>{{ account.remarks }}</td>
                                        <td>{{ account.amount }}</td>
                                    </tr>
                                </tbody>

                            </table>
                            <div class="d-flex justify-content-center" v-if="data4.links">
                                <Pagination :pagination="data4" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </ReportLayout>
    </AuthenticatedLayout>
</template>
<!-- <tfoot>
    <tr>
        <td colspan="7">
            <br>
            <center>
                <h6 style="font-size: 9px;"><span style="font-family: sans;">Developed
                        By Arbaz Ehsan;</span> 03137747660; arbazehsan988@gmail.com</h6>
            </center>
        </td>
    </tr>
</tfoot> -->