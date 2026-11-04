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
    brands: {
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
    brand_id: props.filters.brand_id || '',
});

const submit = () => {
    form.get(route('getBrandWiseSaleReport'));
};

const printDiv = () => {
    window.print();
}

const selectedBrandName = computed(() => {
    if (form.brand_id) {
        const brand = props.brands.find(b => b.id == form.brand_id);
        return brand ? brand.name : '';
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

const getBrandName = (item) => {
    if (item.variation) {
        return item.variation.product?.brand?.name || '';
    }
    return item.product?.brand?.name || '';
};
</script>

<template>

    <Head title="Brand Wise Sale Report" />
    <AuthenticatedLayout>
        <ReportLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Brand Wise Sale Report</h1>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit" class="form-inline mb-3 align-items-end no-print">
                            <div class="form-group mr-2">
                                <InputLabel for="from_" value="From:" />
                                <TextInput id="from_" type="date" class="mt-1 block w-full"
                                        v-model="form.from_" required autofocus placeholder="Enter From Date" />

                                    <InputError class="mt-2" :message="form.errors.from_" />
                            </div>
                            <div class="form-group mr-2 no-print">
                                <InputLabel for="to" value="To:" />

                                    <TextInput id="to" type="date" class="mt-1 block w-full"
                                        v-model="form.to" required placeholder="Enter To Date" />

                                    <InputError class="mt-2" :message="form.errors.to" />
                            </div>
                            <div class="form-group mr-2 no-print" style="min-width: 200px;">
                                 <InputLabel for="brand" value="Brand:" />
                                <Select2 v-model="form.brand_id" :options="brands" />
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
                                            <span class="">Brand Wise Sale Report</span>
                                            <span v-if="selectedBrandName"> - {{ selectedBrandName }}</span>
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
                                        <th>Brand Name</th>
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
                                        <td>{{ getBrandName(item) }}</td>
                                        <td>{{ getProductName(item) }}</td>
                                        <td>{{ item.barcode }}</td>
                                        <td>{{ item.qty }}</td>
                                        <td>{{ item.price }}</td>
                                        <td>{{ item.final }}</td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr class="font-weight-bold bg-light">
                                        <td colspan="6" class="text-right">Total:</td>
                                        <td>{{ new Intl.NumberFormat('en-US').format(totalQty) }}</td>
                                        <td>-</td>
                                        <td>{{ new Intl.NumberFormat('en-US').format(totalAmount.toFixed(2)) }}</td>
                                    </tr>
                                    <tr>
                                        <td colspan="9">
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
