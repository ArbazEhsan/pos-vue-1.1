<script setup>
import { Head } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportLayout from '@/Layouts/ReportLayout.vue';
import { ref, onMounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps({
    data: {
        type: Array,
        default: () => [],
    },
    data2: {
        type: Array,
        default: () => [],
    },
    data3: {
        type: Array,
        default: () => [],
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
    total_sale_qty: {
        type: Number,
        default: 0,
    },
    total_sale_final: {
        type: Number,
        default: 0,
    },
    total_pur_qty: {
        type: Number,
        default: 0,
    },
    total_pur_final: {
        type: Number,
        default: 0,
    },
    gross_profit: {
        type: Number,
        default: 0,
    },
    operating_expense: {
        type: Number,
        default: 0,
    },
    operating_income: {
        type: Number,
        default: 0,
    },
    should_fetch_data: {
        type: Boolean,
        default: false,
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const loading = ref(false);
const reportData = ref({
    data: props.data,
    data2: props.data2,
    data3: props.data3,
    total_sale_qty: props.total_sale_qty,
    total_sale_final: props.total_sale_final,
    total_pur_qty: props.total_pur_qty,
    total_pur_final: props.total_pur_final,
    gross_profit: props.gross_profit,
    operating_expense: props.operating_expense,
    operating_income: props.operating_income,
});

const fetchData = async () => {
    loading.value = true;
    try {
        // 1. Fetch Sales
        const salesResponse = await axios.get('/getpalReport', {
            params: {
                ...props.filters,
                fetch_data: 1,
                step: 'sales'
            }
        });
        const salesData = salesResponse.data;
        reportData.value.data = salesData.data;
        reportData.value.total_sale_qty = salesData.total_sale_qty;
        reportData.value.total_sale_final = salesData.total_sale_final;

        // 2. Fetch Purchases
        const purchasesResponse = await axios.get('/getpalReport', {
            params: {
                ...props.filters,
                fetch_data: 1,
                step: 'purchases'
            }
        });
        const purchasesData = purchasesResponse.data;
        reportData.value.data2 = purchasesData.data2;
        reportData.value.total_pur_qty = purchasesData.total_pur_qty;
        reportData.value.total_pur_final = purchasesData.total_pur_final;

        // 3. Fetch Expenses
        const expensesResponse = await axios.get('/getpalReport', {
            params: {
                ...props.filters,
                fetch_data: 1,
                step: 'expenses'
            }
        });
        const expensesData = expensesResponse.data;
        reportData.value.data3 = expensesData.data3;
        reportData.value.operating_expense = expensesData.operating_expense;

        // 4. Calculate Totals
        reportData.value.gross_profit = reportData.value.total_sale_final - reportData.value.total_pur_final;
        reportData.value.operating_income = reportData.value.gross_profit - reportData.value.operating_expense;

    } catch (error) {
        console.error("Error fetching report data:", error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    if (props.should_fetch_data) {
        fetchData();
    }
});

watch(() => props.filters, () => {
    if (props.should_fetch_data) {
        fetchData();
    }
}, { deep: true });

let sum = 0;
const compute = (e) => {
    sum += e;
}

const printDiv = (divName) => {
    window.print();
}

const formatCurrency = (value) => {
    if (value === null || value === undefined) return '0';
    let val = Number(value);
    if (isNaN(val)) return value;
    
    // Format with commas
    let formatted = val.toLocaleString('en-PK', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    
    // Handle negative values with parentheses for accounting style
    if (val < 0) {
        return `(${Math.abs(val).toLocaleString('en-PK', { minimumFractionDigits: 0, maximumFractionDigits: 2 })})`;
    }
    return formatted;
}

</script>

<style>
.headd {
    font-weight: bold;
    text-align: center;
}

.headd2 {
    font-weight: bold;
}
.loader-container {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 200px;
}
.spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #3498db;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
}
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>

<template>

    <Head title="Profit & Loss" />
    <AuthenticatedLayout>
        <ReportLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Profit & Loss Report</h1>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card overflow-auto">
                    <div class="card-body">
                        <div v-if="loading" class="loader-container">
                            <div class="spinner"></div>
                        </div>
                        <div v-else id="printableArea">
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
                                            <h1>Profit & Loss Report</h1>
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
                                        <th>Particulars</th>
                                        <th>Qty</th>
                                        <th>Amount (Rs.)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="font-weight:bold;">Sales</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <template v-for="category in reportData.data" :key="category.cat_name + '_sale_group'">
                                        <tr class="bg-gray-100 font-bold">
                                            <td colspan="3">{{ category.cat_name }}</td>
                                        </tr>
                                        <tr v-for="product in category.products" :key="product.pro_name + '_sale'">
                                            <td class="pl-4">{{ product.pro_name }}</td>
                                            <td>{{ product.qty }}</td>
                                            <td>{{ formatCurrency(product.amount) }}</td>
                                        </tr>
                                        <tr class="font-bold border-t">
                                            <td class="text-right">Total {{ category.cat_name }}</td>
                                            <td>{{ category.total_qty }}</td>
                                            <td>{{ formatCurrency(category.total_amount) }}</td>
                                        </tr>
                                    </template>
                                    <tr>
                                        <td class="headd">Total Sales</td>
                                        <td class="headd2">{{ reportData.total_sale_qty }}</td>
                                        <td class="headd2">{{ formatCurrency(reportData.total_sale_final) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="headd2">Purchases</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <template v-for="category in reportData.data2" :key="category.cat_name + '_pur_group'">
                                        <tr class="bg-gray-100 font-bold">
                                            <td colspan="3">{{ category.cat_name }}</td>
                                        </tr>
                                        <tr v-for="product in category.products" :key="product.pro_name + '_pur'">
                                            <td class="pl-4">{{ product.pro_name }}</td>
                                            <td>{{ product.qty }}</td>
                                            <td>{{ formatCurrency(product.amount) }}</td>
                                        </tr>
                                        <tr class="font-bold border-t">
                                            <td class="text-right">Total {{ category.cat_name }}</td>
                                            <td>{{ category.total_qty }}</td>
                                            <td>{{ formatCurrency(category.total_amount) }}</td>
                                        </tr>
                                    </template>
                                    <tr>
                                        <td class="headd">Total Purchase</td>
                                        <td class="headd2">{{ reportData.total_pur_qty }}</td>
                                        <td class="headd2">{{ formatCurrency(reportData.total_pur_final) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="headd">Gross Profit</td>
                                        <td></td>
                                        <td class="headd2">{{ formatCurrency(reportData.gross_profit) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="headd2">Operating Expenses</td>
                                        <td></td>
                                        <td></td>
                                    </tr>
                                    <tr v-for="account in reportData.data3" :key="account.pro_name + '_exp'">
                                        <td>{{ account.pro_name }}</td>
                                        <td></td>
                                        <td>{{ formatCurrency(account.tran_sum_amnt) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="headd">Total Operating Expenses</td>
                                        <td></td>
                                        <td class="headd2">{{ formatCurrency(reportData.operating_expense) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="headd">Operating Income</td>
                                        <td></td>
                                        <td class="headd2">{{ formatCurrency(reportData.operating_income) }}</td>
                                    </tr>
                                    <tr style="font-weight:bold;background:yellow">
                                        <td>Net Income</td>
                                        <td></td>
                                        <td class="headd2">{{ formatCurrency(reportData.operating_income) }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="7">
                                            <div class="d-flex flex-column justify-content-center" v-if="reportData.data.links">
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
