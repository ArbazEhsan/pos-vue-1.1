<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import ReportLayout from '@/Layouts/ReportLayout.vue';
import Select2 from '@/Components/Select2.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, computed } from 'vue';

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
    products: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    from_: props.filters.from_ || props.from || '',
    to: props.filters.to || props.to || '',
    product_id: props.filters.product_id || '',
});

const submit = () => {
    form.get(route('getProductWiseSaleReport'));
};

const printDiv = () => {
    window.print();
}

const selectedProductName = computed(() => {
    if (form.product_id) {
        const product = props.products.find(p => p.id == form.product_id);
        return product ? product.name : '';
    }
    return '';
});

const totalQty = computed(() => {
    if (props.data && props.data.data) {
        return props.data.data.reduce((sum, item) => sum + Number(item.qty), 0);
    }
    return 0;
});

const totalAmount = computed(() => {
    if (props.data && props.data.data) {
        return props.data.data.reduce((sum, item) => sum + Number(item.final), 0);
    }
    return 0;
});

const getProductName = (item) => {
    if (item.variation) {
        const prodName = item.variation.product?.name || '';
        const varType = item.variation.product?.variation_name;
        const varValue = item.variation.var_name || '';
        if (varType) {
            return `${prodName} (${varType}:${varValue})`;
        }
        return `${prodName} (${varValue})`;
    }
    return item.product?.name || '';
};
</script>

<template>

    <Head title="Product Wise Sale Report" />
    <AuthenticatedLayout>
        <ReportLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Product Wise Sale Report</h1>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit" class="form-inline mb-3 align-items-end no-print">
                            <div class="form-group mr-2">
                                <!-- <label for="from" class="mr-2">From:</label> -->
                                <InputLabel for="from_" value="From:" />
                                <!-- <input type="date" v-model="form.from_" class="form-control" required> -->
                                <TextInput id="from_" type="date" class="mt-1 block w-full"
                                        v-model="form.from_" required autofocus placeholder="Enter From Date" />

                                    <InputError class="mt-2" :message="form.errors.from_" />
                            </div>
                            <div class="form-group mr-2 no-print">
                                <!-- <label for="to" class="mr-2">To:</label>
                                <input type="date" v-model="form.to" class="form-control" required> -->
                                <InputLabel for="to" value="To:" />

                                    <TextInput id="to" type="date" class="mt-1 block w-full"
                                        v-model="form.to" required placeholder="Enter To Date" />

                                    <InputError class="mt-2" :message="form.errors.to" />
                            </div>
                            <div class="form-group mr-2 no-print" style="min-width: 200px;">
                                <!-- <label class="mr-2">Product:</label> -->
                                 <InputLabel for="product" value="Product:" />
                                <Select2 v-model="form.product_id" :options="products" />
                            </div>
                            <button type="submit" class="btn btn-primary" :disabled="form.processing">Generate</button>
                        </form>
                    <hr class="mb-3">
                        <div id="printableArea" v-if="data && data.data">
                            <table width="100%">
                                <thead>
                                    <tr>
                                        <td width="25%">Print Date: {{ new Date().toLocaleDateString('en-GB') }}</td>
                                        <td v-if="company && company.length > 0" class="text-center">{{ company[0] }}</td>
                                        <td style="text-align: end;">From: {{ from }}</td>
                                    </tr>
                                    <tr>
                                        <td><span>User Name: {{ username }}</span></td>
                                        <td class="text-center">
                                            <span class="">Product Wise Sale Report</span>
                                            <span v-if="selectedProductName"> - {{ selectedProductName }}</span>
                                        </td>
                                        <td style="text-align: end;">To: {{ to }}</td>
                                    </tr>
                                    <tr class="no-print">
                                        <td>
                                            <br>
                                            <i class="fas fa-print" @click="printDiv()"
                                                accesskey="p" style="cursor: pointer;"></i>
                                        </td>
                                    </tr>
                                </thead>
                            </table>
                            <table class="table mt-3">
                                <thead>
                                    <tr>
                                        <th>Sr#</th>
                                        <th>Date</th>
                                        <th>Sale#</th>
                                        <th>Product Name</th>
                                        <th>Barcode #</th>
                                        <th>Qty</th>
                                        <th>Price</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(item, index) in data.data" :key="item.id">
                                        <td>{{ index + 1 + (data.current_page - 1) * data.per_page }}</td>
                                        <td>{{ new Date(item.day).toLocaleDateString('en-GB') }}</td>
                                        <td>{{ item.sale_no }}</td>
                                        <td>{{ getProductName(item) }}</td>
                                        <td>{{ item.barcode }}</td>
                                        <td>{{ item.qty }}</td>
                                        <td>{{ item.price }}</td>
                                        <td>{{ item.final }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold bg-light">
                                        <td colspan="5" class="text-right">Total:</td>
                                        <td>{{ new Intl.NumberFormat('en-US').format(totalQty) }}</td>
                                        <td>-</td>
                                        <td>{{ new Intl.NumberFormat('en-US').format(totalAmount.toFixed(2)) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="8">
                                            <div class="d-flex flex-column justify-content-center" v-if="data.links">
                                                <Pagination :pagination="data" />
                                                <h6 class="text-center copyrights" style="font-size: 9px; display: none;"><span style="font-family: sans;">Developed By Arbaz Ehsan;</span> 03137747660</h6>
                                            </div>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                         <div v-else class="text-center mt-4">
                            <p>No data available. Please select a date range and click Generate.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </ReportLayout>
    </AuthenticatedLayout>
</template>
