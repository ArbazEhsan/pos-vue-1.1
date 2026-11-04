<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Search from '@/Components/Search.vue';
import Toast from '@/Components/Toast.vue';


const props = defineProps({

    ledger: {
        type: Object,
    },
    cust: {
        type: Array,
    },
    to: {
        type: String,
    },
    from: {
        type: String,
    },
    company: {
        type: Array,
    },
    opb: {
        type: Object,
    },
    source: {
        type: String,
    },
    remarks101: {
        type: Array,
    },

});

let debit = 0;
let credit = 0;
let balance = 0;
let fromTxt = '';
let fromTxt2 = '';
const compute = (dr, cr) => {

    let btext = '';

    if (dr > 0 && cr == '0' || !cr) {
        balance += Number(dr);
        btext = 'DR';
    }
    else if (cr > 0 && dr == '0' || !dr) {
        balance -= Number(cr);
        btext = 'CR';
    }

    debit += Number(dr);
    credit += Number(cr);

    // if (props.source == 'Customer') {
    //     fromTxt2 = ' (Rcvble)';
    //     fromTxt = ' (Payable)';
    // }
    // else {
    //     fromTxt2 = ' (Payable)';
    //     fromTxt = ' (Rcvble)';
    // }

    return Math.abs(balance) + " " + btext;

}

const printDiv = () => {
    window.print();
}

</script>

<template>

    <Head title="Ledger" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between no-print">
            <h1 class="h3 mb-0 text-gray-800">Generated Ledger</h1>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card overflow-auto">
                    <div class="card-body">
                        <div id="printableArea">
                            <!-- <div class="row">
                            <div class="col-md-12"> -->
                                <table class="" width="100%">
                                    <!-- <tr>
                                        <td width="28%">Print Date: {{ new Date().toLocaleDateString('en-GB') }}
                                        </td>
                                        <td style="text-align: center;font-size: 18px;font-weight: bold;"><i>{{
                                            company[0].name }}</i>

                                        </td>
                                        <td style="float: right;">From: <span id="to">{{ from }}</span></td>
                                    </tr> -->
                                    <!-- <tr>
                                        <td><span id="uname">{{ cust.name }} | {{ cust.address }}</span></td>
                                        <td style="text-align: center;">Account Statement</td>
                                        <td style="float: right;">To: {{ to }}</td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <br>
                                            <i class="fas fa-print" @click="printDiv('printableArea')" accesskey="p"></i>
                                        </td>
                                    </tr> -->
                                    <tr>
                                        <th width="37%">
                                            <div class="flex flex-col text-sm">
                                                <span class="font-semibold text-blue-500">Account Information</span>
                                                <span>Generated On: {{ new Date().toLocaleDateString('en-GB') }}</span>
                                                <span>Account #: {{ cust.id }}</span>
                                                <span>Account Title: {{ cust.name }}</span>
                                                <span>Address: {{ cust.address }}</span>
                                            </div>
                                        </th>
                                        <th><h1 class="font-semibold text-blue-500 text-center">Account Statement</h1></th>
                                        <th>
                                            <div class="flex flex-col text-sm text-end">
                                                <span class="font-semibold text-blue-500">Statement Information</span>
                                                <span>Company ID: {{ company.id }}</span>
                                                <span>Company Title: {{ company.business_name }}</span>
                                                <span>Statement Period: {{ from }} - {{ to }}</span>
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
                            <!-- End of header table -->
                        
                            <!-- Ledger Data Table -->
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>V-No</th>
                                        <th>Remarks</th>
                                        <th>Debit</th>
                                        <th>Credit</th>
                                        <th>Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td colspan="6" class="opening-balance text-center"><b>Previous Balance</b></td>
                                        <td><b>
                                                {{ new Intl.NumberFormat('en-US').format(Math.abs(opb['bal']).toFixed(2)) + ' (' + opb['text'] + ')' }}
                                            </b>
                                        </td>
                                    </tr>
                                    <tr v-for="data, index in ledger.data">
                                        <td>{{ new Date(data.day).toLocaleDateString('en-GB') }}</td>
                                        <td>{{ data.type }}</td>
                                        <td>{{ data.transaction.invoice_id }}</td>
                                        <td width="20%">{{ remarks101[index] }}</td>
                                        <td>{{ data.dr }}</td>
                                        <td>{{ data.cr }}</td>
                                        <td width="20%">{{ compute(data.dr, data.cr) }}</td>
                                    </tr>
                                    <tr class="foot-value">
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td class="total"><b>Total</b></td>
                                        <td class="dt-value tdalign">
                                            <b>
                                                {{ debit }}
                                            </b>
                                        </td>
                                        <td><b>
                                                {{ credit }}
                                            </b></td>
                                        <td v-if="balance >= 0">
                                            {{ balance + fromTxt2 }}
                                        </td>
                                        <td v-else>
                                            ({{ Math.abs(balance) + fromTxt }})
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7">
                                            <div class="d-flex justify-content-center" v-if="ledger.links">
                                                <div class="d-flex flex-column align-items-center">
                                                    <Pagination :pagination="ledger" />
                                                    <h6 class="copyrights" style="font-size: 9px; display: none;"><span style="font-family: sans;">Developed By Arbaz Ehsan;</span> 03137747660</h6>
                                                </div>
                                            </div>
                                            
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                            <!-- Pagination -->
                            <!-- <div class="d-flex justify-content-center mt-4 " v-if="ledger.links">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="mb-2">Page {{ ledger.current_page }} of {{ ledger.last_page }}</span>
                                    <nav aria-label="Page navigation no-print">
                                        <ul class="pagination">
                                            <li v-for="(link, k) in ledger.links" :key="k" class="page-item" :class="{ 'active': link.active, 'disabled': !link.url }">
                                                <a class="page-link" :href="link.url" v-html="link.label"></a>
                                            </li>
                                        </ul>
                                    </nav>
                                </div>
                            </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>

<style>
@media print {
    .no-print {
        display: none !important;
    }
    .copyrights {
        display: block !important;
    } 

    /* Hide everything initially */
    body * {
        visibility: hidden;
    }

    /* Make the printable area visible */
    #printableArea, #printableArea * {
        visibility: visible;
    }
}
</style>
