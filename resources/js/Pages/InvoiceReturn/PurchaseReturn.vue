<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import TextInputForTable from '@/Components/TextInputForTable.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Select2 from '@/Components/Select2.vue';
import Modal2 from '@/Components/Modal2.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { onMounted, watch, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    products: {
        type: Array,
        default: () => [],
    },
    customers: {
        type: Array,
        default: () => [],
    },
    invoice_data: {
        type: Array,
        default: () => [],
    },
    invoice_id: {
        type: [String, Number],
        default: null,
    },
});

const form = useForm({
    hid: '', // Hidden ID for editing
    sale_day: new Date().toISOString().substr(0, 10),
    bilty_no: 0,
    reference: '',
    cust_id: '',
    table_no: 0,
    gross: 0,
    discount: 0,
    profit: 0,
    final_value: 0,
    received: 0,
    remaining: 0,
    return: 0,
    productArray: [],
    credit_note_issued: '', // Used for remarks
});

// Computed properties for calculations
const calculatedGross = computed(() => {
    return form.productArray.reduce((sum, item) => {
        return sum + (Number(item.qty) * Number(item.price));
    }, 0);
});

const calculatedFinal = computed(() => {
    const gross = calculatedGross.value;
    const discount = Number(form.discount) || 0;
    const profit = Number(form.profit) || 0;
    return gross - discount + profit;
});

const calculatedRemaining = computed(() => {
    return calculatedFinal.value - (Number(form.received) || 0);
});

// Watchers to update form values
watch(calculatedGross, (val) => form.gross = val);
watch(calculatedFinal, (val) => form.final_value = val);
watch(calculatedRemaining, (val) => form.remaining = val);

onMounted(() => {
    if (props.invoice_data && Object.keys(props.invoice_data).length > 0) {
        const invoice = props.invoice_data;
        
        form.hid = invoice.id; 
        form.sale_day = new Date().toISOString().substr(0, 10);
        form.bilty_no = invoice.bilty_no;
        form.reference = invoice.ref;
        form.cust_id = invoice.cust_id;
        form.table_no = invoice.table_no;
        // Reset financial fields for the return transaction
        form.discount = 0;
        form.profit = 0;
        form.received = 0; 

        // Populate products
        if (invoice.purchases && Array.isArray(invoice.purchases)) {
            form.productArray = invoice.purchases.map(item => ({
                id: item.barcode, 
                name: item.product ? item.product.name : 'Unknown Product',
                qty: item.qty,
                original_qty: item.qty, // Track original quantity
                price: item.price,
                total: item.final,
                is_invoice_item: true // Flag to identify items from original invoice
            }));
        }
    } else {
        // Initialize with one empty row if no invoice data
        addProductRow();
    }
});

function addProductRow() {
    form.productArray.push({
        id: '',
        name: '',
        qty: 1,
        original_qty: 0,
        price: 0,
        total: 0,
        is_invoice_item: false
    });
}

function removeProduct(index) {
    form.productArray.splice(index, 1);
    if (form.productArray.length === 0) {
        addProductRow();
    }
}

function onProductSelect(product, index) {
    if (!product) return;
    
    let selectedProduct = null;
    if (typeof product === 'object') {
        selectedProduct = product;
    } else {
        selectedProduct = props.products.find(p => p.barcode == product);
    }

    if (selectedProduct) {
        const item = form.productArray[index];
        item.id = selectedProduct.barcode || selectedProduct.id;
        item.name = selectedProduct.name;
        // Only update price if it's not an invoice item or if we want to reset it
        if (!item.is_invoice_item) {
             item.price = selectedProduct.retail || 0;
        }
       
        updateRowTotal(index);
    }
}

function updateRowTotal(index) {
    const item = form.productArray[index];
    
    // Validation: Cannot return more than original quantity
    if (item.is_invoice_item && item.original_qty > 0) {
        if (item.qty > item.original_qty) {
            item.qty = item.original_qty;
            // Optional: Show a toast or alert
            // alert(`Cannot return more than sold quantity (${item.original_qty})`);
        }
    }
    
    if (item.qty < 1) item.qty = 1;

    item.total = Number(item.qty) * Number(item.price);
}

const submit = () => {
    const pros = form.productArray
        .filter(item => item.id && item.qty > 0)
        .map(item => ({
            id: item.id,
            qty: item.qty,
            price: item.price,
            item_total: item.qty * item.price
        }));

    // form.transform((data) => ({
    //     ...data,
    //     pros: pros,
    //     gross: calculatedGross.value,
    //     final_value: calculatedFinal.value,
    //     remaining: calculatedRemaining.value
    // })).post(route('savePurchaseReturn'), {
    //     onSuccess: (event) => {
    //         // alert(event);
    //     }
    // });

    const payload = {
        ...form.data(), // Assuming 'form' is an Inertia useForm helper instance
        pros: pros,
        gross: calculatedGross.value,
        final_value: calculatedFinal.value,
        remaining: calculatedRemaining.value
    };

    axios.post(route('savePurchaseReturn'), payload)
        .then(response => {
            console.log("Invoice ID received:", response.data);
            
            const invoiceId = response.data.invoice_id;

            window.open('/purPrint/' + invoiceId, 'popUpWindow', 'height=400,width=1000,left=50,top=50,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');
            window.history.back();
        })
        .catch(error => {
            console.error("Error saving sale return:", error.response.data);
        });
};

const isEligible = computed(() => {
    if (!props.invoice_data || !props.invoice_data.created_at) return true;
    const now = new Date();
    const cutoffTime = new Date(now.getTime() - (24 * 60 * 60 * 1000));
    const invoiceTime = new Date(props.invoice_data.created_at);
    return invoiceTime >= cutoffTime;
});

const isFormValid = computed(() => {
    return form.sale_day && 
           form.cust_id && 
           form.credit_note_issued && 
           form.productArray.length > 0 && 
           form.productArray.some(item => item.id && item.qty > 0);
});

const openProductsModal = () => {
    $('#modal_products').modal('show');
};

$(document).ready(function() {
    $(".datatable").DataTable({
        processing: true,
        serverSide: false,
        destroy: true,
        lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]],
        pageLength: 10,

        initComplete: function () {
            // Get the DataTables API instance for this table
            var api = this.api();
            var wrapper = $(api.table().container());

            // --- 1. Style and Center the 'Showing Entries' Info Text ---
            wrapper.find('.dataTables_info')
                // text-center doesn't work well here without adjusting the width/display
                // Use text-sm for small text and ensure it takes full width below the table
                .addClass('text-sm text-center w-full');

            // --- 2. Style and Center the Pagination Controls ---
            wrapper.find('.dataTables_paginate')
                // Use flexbox utilities to center the pagination buttons horizontally
                .addClass('flex justify-center text-sm mt-3 w-full');

            // Optional: Also make the 'Show entries' and 'Search' areas small
            wrapper.find('.dataTables_length, .dataTables_filter')
                .addClass('text-sm');

            // Optional: Apply specific styles to the input elements themselves for consistent size
            wrapper.find('.dataTables_filter input, .dataTables_length select')
                 .addClass('text-sm px-2 py-1 border border-gray-300 rounded shadow-sm');
        }
    });
});

</script>

<template>
    <Head title="Purchase Return"/>
    <AuthenticatedLayout>
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">
                    Purchase Return
                </h1>
                <div class="text-sm text-gray-500">
                    Invoice #: {{ props.invoice_id || 'New' }}
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <form @submit.prevent="submit">
                        <!-- Top Form Fields -->
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-6">
                            <div>
                                <InputLabel for="sale_day" value="Date *" />
                                <TextInput id="sale_day" type="date" class="mt-1 block w-full" v-model="form.sale_day" required />
                                <InputError class="mt-2" :message="form.errors.sale_day" />
                            </div>
                            <div>
                                <InputLabel for="bilty_no" value="Bilty No" />
                                <TextInput id="bilty_no" type="number" class="mt-1 block w-full" v-model="form.bilty_no" placeholder="Bilty #" />
                            </div>
                            <div>
                                <InputLabel for="reference" value="Reference" />
                                <TextInput id="reference" type="text" class="mt-1 block w-full" v-model="form.reference" placeholder="Ref #" />
                            </div>
                            <div class="md:col-span-3">
                                <InputLabel for="vendor" value="Vendor *" />
                                <Select2 v-model="form.cust_id" :options="props.customers" class="mt-1 block w-full" />
                                <InputError class="mt-2" :message="form.errors.cust_id" />
                            </div>
                        </div>

                        <!-- Product Table -->
                        <div class="overflow-x-auto mb-6">
                            <table class="min-w-full divide-y divide-gray-200 border">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" width="5%">Sr#</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" width="5%">Barcode</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" width="15%"><i class="fas fa-search text-blue-500 cursor-pointer" @click="openProductsModal" accesskey="p"></i> Product Name</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" width="10%">Qty</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" width="10%">Price</th>
                                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" width="10%">Total</th>
                                        <th scope="col" class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" width="5%">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="(item, index) in form.productArray" :key="index">
                                        <td class="px-4 py-2 text-sm text-gray-500">{{ index + 1 }}</td>
                                        <td class="px-4 py-2">
                                            <TextInputForTable type="text" v-model="item.id" placeholder="Barcode" @blur="onProductSelect(item.id, index)" :readonly="item.is_invoice_item" :class="['w-full', {'bg-gray-100': item.is_invoice_item}]"/>
                                        </td>
                                        <td class="px-4 py-2">
                                            <TextInputForTable type="text" v-model="item.name" placeholder="Product Name" readonly class="bg-gray-50 w-full" />
                                        </td>
                                        <td class="px-4 py-2">
                                            <div class="relative">
                                                <TextInputForTable type="number" class="w-full" v-model="item.qty" @input="updateRowTotal(index)" min="1" :max="item.is_invoice_item ? item.original_qty : null" />
                                                <span v-if="item.is_invoice_item" class="absolute -top-2 -right-2 bg-blue-100 text-blue-800 text-[10px] px-1 rounded-full">
                                                    Max: {{ item.original_qty }}
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-2">
                                            <TextInputForTable type="number" v-model="item.price" @input="updateRowTotal(index)" :readonly="item.is_invoice_item" :class="['w-full',{'bg-gray-100': item.is_invoice_item}]"/>
                                        </td>
                                        <td class="px-4 py-2">
                                            <TextInputForTable type="number" v-model="item.total" readonly class="bg-gray-50 font-medium text-gray-900 w-full" />
                                        </td>
                                        <td class="px-4 py-2 text-center">
                                            <button type="button" @click="removeProduct(index)" class="text-red-500 hover:text-red-700 transition-colors">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot v-if="!props.invoice_data">
                                    <tr>
                                        <td colspan="7" class="px-4 py-2">
                                            <PrimaryButton type="button" @click="addProductRow" size="sm">
                                                <i class="fas fa-plus mr-1"></i> Add Item
                                            </PrimaryButton>
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <!-- Totals Section -->
                        <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                <div>
                                    <InputLabel for="gross" value="Gross Total" />
                                    <TextInput id="gross" type="number" class="mt-1 block w-full bg-gray-100 font-bold" :value="calculatedGross" readonly />
                                </div>
                                <div>
                                    <InputLabel for="discount" value="Discount" />
                                    <TextInput id="discount" type="number" class="mt-1 block w-full" v-model="form.discount" min="0" />
                                </div>
                                <div>
                                    <InputLabel for="profit" value="Profit / Adj" />
                                    <TextInput id="profit" type="number" class="mt-1 block w-full bg-gray-100" v-model="form.profit" readonly />
                                </div>
                                <div>
                                    <InputLabel for="final_value" value="Net Payable" />
                                    <TextInput id="final_value" type="number" class="mt-1 block w-full bg-indigo-50 text-indigo-700 font-bold border-indigo-200" :value="calculatedFinal" readonly />
                                </div>
                                <div>
                                    <InputLabel for="received" value="Paid Amount" />
                                    <TextInput id="received" type="number" class="mt-1 block w-full" v-model="form.received" min="0" />
                                </div>
                                <div>
                                    <InputLabel for="remaining" value="Balance" />
                                    <TextInput id="remaining" type="number" class="mt-1 block w-full bg-gray-100" :value="calculatedRemaining" readonly />
                                </div>
                            </div>
                        </div>

                        <!-- Footer / Remarks -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                            <div>
                                <InputLabel for="credit_note_issued" value="Reason for Return / Remarks *" />
                                <TextArea id="credit_note_issued" class="mt-1 block w-full h-24" v-model="form.credit_note_issued" placeholder="Enter details about why this return is being processed..." />
                            </div>
                            <div class="flex items-end justify-end">
                                <PrimaryButton class="w-full md:w-auto justify-center py-3 text-lg" :disabled="form.processing || !isFormValid" :class="{'opacity-50 cursor-not-allowed': form.processing || !isFormValid}">
                                    <i class="fas fa-save mr-2"></i> Process Return
                                </PrimaryButton>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Products Modal -->
        <Modal2 id="modal_products" title="Products Details">
            <div class="overflow-x-auto">
            <table class="w-full text-sm datatable">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Barcode</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600 w-30">Name</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Purchase</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Wholesale</th>
                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Retail</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="(item, index) in products" :key="index" class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-600">{{ item.barcode }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ item.name }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ item.purchase }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ item.whole_sale }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ item.retail }}</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </Modal2>
    </AuthenticatedLayout>
</template>
