<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted, watch, computed, ref, nextTick, onUnmounted } from 'vue';
import axios from 'axios';
import InvoiceLayout from '@/Layouts/InvoiceLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import TextInputForTable from '@/Components/TextInputForTable.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Button from '@/Components/Button.vue';
import Select2 from '@/Components/Select2.vue';
import SelectWithSearch from '@/Components/SelectWithSearch.vue';
import Modal2 from '@/Components/Modal2.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Checkbox from '@/Components/Checkbox.vue';
import ProductSearch from '@/Components/ProductSearch.vue';

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
    business_type: {
        type: String,
        default: null,
    },
});

const isRestaurant = computed(() => props.business_type === 'restaurant');

const form = useForm({
    hid: '', // Hidden ID for editing
    sale_day: new Date().toISOString().substr(0, 10),
    bilty_no: 0,
    reference: '',
    cust_id: '',
    order_type: 'Dine_In',
    table_no: 1,
    gross: 0,
    discount: 0,
    profit: 0,
    final_value: 0,
    received: 0,
    remaining: 0,
    return: 0,
    productArray: [],
});

const customerForm = useForm({
    acc_name: '',
    acc_type: 'Customer',
    acc_city: '',
    acc_address: '',
    acc_contact_per: '',
    acc_mobile: '',
    acc_email: '',
    acc_tel1: '',
    acc_tel2: '',
});

const customerInvoices = ref([]);
const customerBalance = ref(0);
const currentRowIndex = ref(0); // Track current row for modal navigation
const focusedRowIndex = ref(null); // Track which row triggered the modal
const signal = ref(0);
const isSearchBarModeOn = ref(false);
const allCustomers = ref([...props.customers]);

const accountTypes = [
    { id: 'Asset', name: 'Asset' },
    { id: 'Capital', name: 'Capital' },
    { id: 'Customer', name: 'Customer' },
    { id: 'Expense', name: 'Expense' },
    { id: 'Liability', name: 'Liability' },
    { id: 'Vendor', name: 'Vendor' },
];


const fetchCustomerDetails = async (id) => {
    if (!id) return;
    try {
        const response = await axios.get(route('getCustomerDetails', id));
        customerInvoices.value = response.data.invoices;
        customerBalance.value = response.data.balance;
    } catch (error) {
        console.error("Error fetching customer details:", error);
    }
};

watch(() => form.cust_id, (newId) => {
    if (newId) {
        fetchCustomerDetails(newId);
    } else {
        customerInvoices.value = [];
        customerBalance.value = 0;
    }
});

// Format customer options for SelectWithSearch
const customerOptions = computed(() => {
    return allCustomers.value.map(account => ({
        value: account.id,
        label: account.name
    }));
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

const displayedProducts = computed(() => {
    let result = [];
    
    props.products.forEach(product => {
        // If product has variations, show ONLY variations
        if (product.variations && product.variations.length > 0) {
            product.variations.forEach(variation => {
                const productName = product.name || '';
                const variationName = product.variation_name || ''; 
                const variationValue = variation.var_name || ''; 
                // const barcodeFormat = product.barcode +"-"+variation.var_sku || ''; 
                 
                result.push({
                    unique_key: `var-${variation.id}`,
                    id: variation.id, // This might not represent the product ID expected by backend but the specific variation ID/barcode matters for adding row
                    barcode: variation.var_sku || '',
                    // barcodeDisplay: barcodeFormat, 
                    // name: `${productName} (${variationValue})`,
                    name: `${productName} (${variationName}:${variationValue})`,
                    nameFormat: `${productName} (${variationName}:${variationValue})`,
                    purchase: Number(variation.var_purchase_price || 0),
                    wholesale: Number(variation.var_whole_sale_price || 0),
                    retail: Number(variation.var_retail_price || 0),
                    image: product.image_uri1, 
                    qty: variation.var_qty || 0,
                    unit: product.weight_unit
                });
            });
        } 
        else {
            // Standard Product (including ones being combos themselves)
            result.push({
                unique_key: `prod-${product.id}`,
                id: product.id,
                barcode: product.barcode,
                // barcodeDisplay: product.barcode || '',
                name: product.name,
                nameFormat: product.name,
                purchase: Number(product.purchase || 0),
                wholesale: Number(product.whole_sale || 0),
                retail: Number(product.retail || 0),
                image: product.image_uri1,
                qty: product.qty,
                unit: product.weight_unit
            });
        }
    });

    return result;
});

const hasItems = computed(() => form.productArray.some(item => item.id));

// Watchers to update form values
watch(calculatedGross, (val) => form.gross = val);
watch(calculatedFinal, (val) => form.final_value = val);
watch(calculatedRemaining, (val) => form.remaining = val);
// Watch for changes and save to localStorage
watch(isSearchBarModeOn, (newValue) => { localStorage.setItem('checkboxStatus', newValue)});

onMounted(() => {
    if (props.invoice_data && Object.keys(props.invoice_data).length > 0) {
        const invoice = props.invoice_data;
        
        form.hid = invoice.id; 
        form.sale_day = new Date().toISOString().substr(0, 10);
        form.bilty_no = invoice.bilty_no;
        form.reference = invoice.ref;
        form.cust_id = invoice.cust_id;
        form.order_type = invoice.order_type;
        form.table_no = invoice.table_no;
        // Reset financial fields for the return transaction
        form.discount = 0;
        form.profit = 0;
        form.received = 0; 

        // Populate products
        if (invoice.sales && Array.isArray(invoice.sales)) {
            form.productArray = invoice.sales.map(item => ({
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
        form.cust_id = '1';
        addProductRow();
    }
    
    if (form.cust_id) {
        fetchCustomerDetails(form.cust_id);
    }

    // Keyboard shortcut listener
    window.addEventListener('keydown', handleKeydown);
    saveCheckBoxState();
});

onUnmounted(() => {
    window.removeEventListener('keydown', handleKeydown);
});

const saveCheckBoxState = () => {
    const savedValue = localStorage.getItem('checkboxStatus');
    if (savedValue !== null) {
        // localStorage stores everything as strings
        isSearchBarModeOn.value = savedValue === 'true';
    }  
};

const handleKeydown = (e) => {
    // F2 to open modal
    if (e.key === 'F2') {
        e.preventDefault();
        openProductsModal();
    }
};

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

function onProductSelect(product, index = null) {
    if (!product) return;
    
    let selectedProduct = null;
    if (typeof product === 'object') {
        selectedProduct = product;
    } else {
        selectedProduct = displayedProducts.value.find(p => p.barcode == product);

        if (!selectedProduct) {
            selectedProduct = props.products.find(p => p.barcode == product);
        }
    }

    if (!selectedProduct) return;

    const targetBarcode = selectedProduct.barcode || selectedProduct.id;

    // Check if this product/barcode already exists in another row in productArray
    const existingIndex = form.productArray.findIndex((p, i) => {
        if (index !== null && index !== undefined && i === index) {
            return false; // Skip the row currently being edited
        }
        return p.id && String(p.id) === String(targetBarcode);
    });

    if (existingIndex !== -1) {
        // Increment quantity of existing item
        form.productArray[existingIndex].qty = Number(form.productArray[existingIndex].qty || 0) + 1;
        updateRowTotal(existingIndex);

        // If the barcode was entered in another row, clear or reset that row
        if (index !== null && index !== undefined && index !== existingIndex) {
            if (index < form.productArray.length - 1) {
                form.productArray.splice(index, 1);
            } else {
                form.productArray[index] = {
                    id: '',
                    name: '',
                    qty: 1,
                    original_qty: 0,
                    price: 0,
                    total: 0,
                    is_invoice_item: false
                };
            }
        }

        // Ensure there is always an empty row at the bottom
        nextTick(() => {
            const lastItem = form.productArray[form.productArray.length - 1];
            if (!lastItem || lastItem.id) {
                addProductRow();
            }

            nextTick(() => {
                const qtyInput = document.getElementById('qty-' + existingIndex);
                if (qtyInput) {
                    qtyInput.focus();
                    focusedRowIndex.value = existingIndex;
                }
            });
        });
        return;
    }

    // If index is not provided (e.g. from Search Bar), find the first empty row or create new
    if (index === null || index === undefined) {
         index = form.productArray.findIndex(p => !p.id);
         if (index === -1) {
             addProductRow();
             index = form.productArray.length - 1;
         }
    }

    const item = form.productArray[index];
    item.id = targetBarcode;
    if (selectedProduct._isSelectedVariation) {
        item.name = selectedProduct._parent.name + ' (' + selectedProduct._parent.variation_name + ':' + selectedProduct.name + '(' + selectedProduct.var_sku + '))';
    } else if (selectedProduct.unique_key) {
        item.name = selectedProduct.name;
    } else if (selectedProduct.barcode) {
        item.name = selectedProduct.name + '(' + selectedProduct.barcode + ')';
    } else {
        item.name = selectedProduct.name;
    }

    // Only update price if it's not an invoice item or if we want to reset it
    if (!item.is_invoice_item) {
         item.price = selectedProduct.retail || 0;
    }
    
    updateRowTotal(index);

    // Auto-advance logic: Add new row if needed and focus it
    nextTick(() => {
         // 1. Ensure empty row exists at bottom
         const lastItem = form.productArray[form.productArray.length - 1];
         if (lastItem.id) {
             addProductRow();
         }
         
         // 2. Focus the next empty row
         nextTick(() => {
            let nextEmptyIndex = form.productArray.findIndex(p => !p.id);
            nextEmptyIndex = nextEmptyIndex - 1;
            if (nextEmptyIndex !== -1) {
                 const nextInput = document.getElementById('qty-' + nextEmptyIndex);
                 if (nextInput) {
                     nextInput.focus();
                     focusedRowIndex.value = nextEmptyIndex; 
                 }
            }
         });
    });
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

    const payload = {
        ...form.data(), // Assuming 'form' is an Inertia useForm helper instance
        pros: pros,
        gross: calculatedGross.value,
        final_value: calculatedFinal.value,
        remaining: calculatedRemaining.value
    };

    axios.post(route('saveSaleInvoice'), payload)
        .then(response => {
            console.log("Invoice ID received:", response.data);
            
            const invoiceId = response.data.invoice_id;

            window.open('/salePrint/' + invoiceId, 'popUpWindow', 'height=400,width=1000,left=50,top=50,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');
            form.reset();
            form.cust_id = '1';
            props.invoice_id = invoiceId + 1;

            addProductRow();
            fetchCustomerDetails(form.cust_id);
        })
        .catch(error => {
            console.error("Error saving invoice:", error.response.data);
        });
};

const isFormValid = computed(() => {
    return form.sale_day && 
           form.cust_id && 
           form.order_type &&
           form.productArray.length > 0 && 
           form.productArray.some(item => item.id && item.qty > 0);
});

const openProductsModal = () => {
    $('#modal_products').modal('show');
    currentRowIndex.value = -1; // Reset index
    
    // Focus on search box after modal opens
    setTimeout(() => {
        const searchInput = document.querySelector('#modal_products input[type="search"]');
        if (searchInput) {
            searchInput.focus();
        }
    }, 500);
};

// Handle row selection in modal
const handleModalKeydown = (e) => {
    console.log(e.key);
    // We only care about Tab and Enter
    if (e.key !== 'Tab' && e.key !== 'Enter') return;

    const table = $('#modal_products .datatable').DataTable();
    // Convert API result to standard array of DOM nodes
    const rows = table.rows({ search: 'applied' }).nodes().toArray();
    const rowCount = rows.length;

    if (rowCount === 0) return;

    if (e.key === 'Tab') {
        e.preventDefault();
        
        // Remove highlight from previous row if valid
        if (currentRowIndex.value >= 0 && currentRowIndex.value < rowCount) {
             $(rows[currentRowIndex.value]).removeClass('highlight-row');
        }
        
        if (e.shiftKey) {
            // Move backward
            currentRowIndex.value = (currentRowIndex.value - 1 + rowCount) % rowCount;
        } else {
            // Move forward
            currentRowIndex.value = (currentRowIndex.value + 1) % rowCount;
        }
        
        // Highlight new row
        const newRow = rows[currentRowIndex.value];
        $(newRow).addClass('highlight-row');
        newRow.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
        
    } else if (e.key === 'Enter') {
        e.preventDefault();
        
        // If no row is selected via Tab yet, select the first one
        if (currentRowIndex.value === -1 && rowCount > 0) {
            currentRowIndex.value = 0;
        }

        const selectedRow = rows[currentRowIndex.value];
        if (selectedRow) {
            // Get data from the row
            // We can extract directly from DOM to be safe, or use data()
            const barcode = $(selectedRow).find('td:eq(0)').text().trim(); // Assuming Barcode is 1st column
            
            if (barcode) {
                // Determine target index: use focused row if available, otherwise append
                let targetIndex = focusedRowIndex.value;
                
                // If no specific row focused (or invalid), find first empty or create new
                if (targetIndex === null || targetIndex === undefined || targetIndex < 0 || targetIndex >= form.productArray.length) {
                    targetIndex = form.productArray.findIndex(p => !p.id);
                    if (targetIndex === -1) {
                         addProductRow();
                         targetIndex = form.productArray.length - 1;
                    }
                }
                
                onProductSelect(barcode, targetIndex);
                $('#modal_products').modal('hide');
            }
        }
    }
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
                 
            // Add keydown listener to the search input for navigation
            wrapper.find('.dataTables_filter input').on('keydown', handleModalKeydown);
        }
    });
});

watch(signal, (newVal) => {
    if (newVal > 0) {
        $('#modal_customer').modal('show');  
    }
});

const submitCustomer = () => {
    customerForm.processing = true;
    axios.post(route('insertAccountApi'), customerForm.data())
        .then(response => {
            const newCustomer = response.data;
            if (newCustomer && newCustomer.id) {
                // Add to local list if not already present
                if (!allCustomers.value.find(c => c.id == newCustomer.id)) {
                    allCustomers.value.push(newCustomer);
                }
                // Automatically select the newly created customer
                nextTick(() => {
                   form.cust_id = newCustomer.id;
                });
                
                // Close modal and reset form
                $('#modal_customer').modal('hide');
                customerForm.reset();
            }
        })
        .catch(error => {
            console.error("Error saving customer:", error);
            // You could handle validation errors here if the API returns them
        })
        .finally(() => {
            customerForm.processing = false;
        });
};
</script>

<template>
    <Head title="Sale Invoice"/>
    <InvoiceLayout v-slot="slotProps">
        <div class="max-w-8xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-4">
            <div class="lg:col-span-3">
                <!-- Header -->
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-2xl font-bold text-gray-800">
                        Sale Invoice 
                        <div class="flex items-center">
                            <Checkbox v-model="isSearchBarModeOn" :checked="isSearchBarModeOn" />
                            <span class="text-sm text-gray-500 ml-2">Search Bar Mode</span>
                        </div>
                    </h1>
                    <!-- <div class="text-sm text-gray-500">
                        Invoice #: {{ props.invoice_id || 'New' }}
                    </div> -->
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 bg-white border-b border-gray-200">
                        <form @submit.prevent="submit">
                            <!-- Top Form Fields -->
                            <div class="grid grid-cols-1 md:grid-cols-7 gap-4 mb-6">
                                <div>
                                    <InputLabel for="sale_day" value="Date" required />
                                    <TextInput id="sale_day" type="date" class="mt-1 block w-full text-xs" v-model="form.sale_day" required autofocus="true"/>
                                    <InputError class="mt-2" :message="form.errors.sale_day" />
                                </div>
                                <div>
                                    <InputLabel for="bilty_no" value="Bilty No" />
                                    <TextInput id="bilty_no" type="number" class="mt-1 block w-full text-xs" v-model="form.bilty_no" placeholder="Bilty #" />
                                </div>
                                <div>
                                    <InputLabel for="reference" value="Reference" />
                                    <TextInput id="reference" type="text" class="mt-1 block w-full text-xs" v-model="form.reference" placeholder="Ref #" />
                                </div>
                                <div v-if="isRestaurant">
                                    <InputLabel for="orderType" value="Order Type" />
                                    <select id="orderType" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm text-xs" v-model="form.order_type">
                                        <option value="Dine_In">Dine In</option>
                                        <option value="Take_Away">Take Away</option>
                                        <option value="Delivery">Delivery</option>
                                    </select>
                                </div>
                                <div v-if="isRestaurant">
                                    <InputLabel for="table_no" value="Table #" />
                                    <TextInput id="table_no" type="number" class="mt-1 block w-full text-xs" v-model="form.table_no" min="1" />
                                </div>
                                <div class="md:col-span-2">
                                    <InputLabel for="customer" value="Customer" required />
                                    <SelectWithSearch
                                        v-model="form.cust_id"
                                        :options="customerOptions"
                                        placeholder="-- Select Customer --"
                                        search-placeholder="Search customers..."
                                        class="flex-1 text-xs"
                                    />
                                    <!-- <Select2 v-model="form.cust_id" :options="allCustomers" class="mt-1 block w-full text-xs" /> -->
                                    <InputError class="mt-2" :message="form.errors.cust_id" />
                                </div>
                            </div>

                            <!-- Product Table -->
                            <div class="overflow-x-auto mb-6" v-if="!isSearchBarModeOn">
                                <table class="min-w-full divide-y divide-gray-200 border">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" width="1%">Sr#</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" width="5%">Barcode</th>
                                            <th scope="col" class="px-3 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" width="18%"><i class="fas fa-search text-blue-500 cursor-pointer" @click="openProductsModal" accesskey="p"></i> Product Name</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" width="6%">Qty</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" width="6%">Price</th>
                                            <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider" width="6%">Total</th>
                                            <th scope="col" class="px-1 py-1 text-center text-xs font-medium text-gray-500 uppercase tracking-wider" width="1%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="(item, index) in form.productArray" :key="index">
                                            <td class="px-2 py-1 text-sm text-gray-500">{{ index + 1 }}</td>
                                            <td class="px-3 py-3">
                                                <TextInputForTable 
                                                    :id="'barcode-' + index"
                                                    type="text" 
                                                    v-model="item.id" 
                                                    placeholder="Barcode" 
                                                    @blur="onProductSelect(item.id, index)" 
                                                    @keydown.enter.prevent="onProductSelect(item.id, index)"
                                                    @focus="focusedRowIndex = index"
                                                    :readonly="item.is_invoice_item" 
                                                    :class="['w-full text-xs font-bold', {'bg-gray-100': item.is_invoice_item}]"
                                                />
                                            </td>
                                            <td class="px-4 py-2">
                                                <TextInputForTable type="text" v-model="item.name" placeholder="Product Name" readonly class="bg-gray-50 w-full text-xs font-bold" />
                                            </td>
                                            <td class="px-3 py-2">
                                                <div class="relative">
                                                    <TextInputForTable :id="'qty-' + index" type="number" class="w-full text-xs" v-model="item.qty" @input="updateRowTotal(index)" step="0.01" min="0" :max="item.is_invoice_item ? item.original_qty : null" />
                                                    <span v-if="item.is_invoice_item" class="absolute -top-2 -right-2 bg-blue-100 text-blue-800 text-[10px] px-1 rounded-full">
                                                        Max: {{ item.original_qty }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-3 py-2">
                                                <TextInputForTable type="number" v-model="item.price" @input="updateRowTotal(index)" :readonly="item.is_invoice_item" :class="['w-full text-xs',{'bg-gray-100': item.is_invoice_item}]"/>
                                            </td>
                                            <td class="px-3 py-2">
                                                <TextInputForTable type="number" v-model="item.total" readonly class="bg-gray-50 font-medium text-gray-900 w-full text-xs" />
                                            </td>
                                            <td class="px-2 py-1 text-center">
                                                <!-- <button type="button"  class="text-red-500 hover:text-red-700 transition-colors"> -->
                                                    <i class="fas fa-trash text-red-500 cursor-pointer transition-all duration-200 hover:text-red-600 hover:scale-125"
                                                    @click="removeProduct(index)"></i>
                                                <!-- </button> -->
                                            </td>
                                        </tr>
                                    </tbody>
                                    <!-- <tfoot>
                                        <tr>
                                            <td colspan="7" class="px-4 py-2">
                                                <PrimaryButton type="button" @click="addProductRow" size="sm">
                                                    <i class="fas fa-plus mr-1"></i> Add Item
                                                </PrimaryButton>
                                            </td>
                                        </tr>
                                    </tfoot> -->
                                </table>
                            </div>

                            <!-- Search bar Product Table -->
                            <div class="mb-6" v-if="isSearchBarModeOn">
                                <ProductSearch 
                                    :products="products"
                                    @select="onProductSelect"
                                />
                                <table class="w-full border-collapse mt-4">
                                    <thead class="sticky top-0 bg-gray-50 z-10">
                                        <tr>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-b-2 border-gray-200">Product</th>
                                            <th class="px-4 py-2 text-center text-xs font-semibold text-gray-600 uppercase tracking-wide border-b-2 border-gray-200 w-24">Quantity</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-b-2 border-gray-200 w-25">Price</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-b-2 border-gray-200 w-28">Subtotal</th>
                                            <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-b-2 border-gray-200 w-16"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template v-for="(item, index) in form.productArray" :key="index">
                                            <tr v-if="item.id" class="border-b border-gray-100 transition-colors duration-150 hover:bg-gray-50">
                                                <td class="px-4 py-2 text-sm text-gray-700">{{ item.name }}</td>
                                                <td class="px-4 py-2">
                                                    <div class="relative">
                                                        <TextInput type="number" 
                                                            class="w-full px-2 py-1 text-center text-sm"
                                                            v-model="item.qty"
                                                            @input="updateRowTotal(index)"
                                                            step="0.01" min="0" :max="item.is_invoice_item ? item.original_qty : null" />
                                                        <span v-if="item.is_invoice_item" class="absolute -top-2 -right-2 bg-blue-100 text-blue-800 text-[10px] px-1 rounded-full">
                                                            Max: {{ item.original_qty }}
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-700">
                                                    <TextInput type="number" 
                                                        v-model="item.price" 
                                                        @input="updateRowTotal(index)" 
                                                        :readonly="item.is_invoice_item" 
                                                        class="w-full px-2 py-1 text-sm"
                                                        :class="{'bg-gray-100': item.is_invoice_item}"/>
                                                </td>
                                                <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ item.total }}</td>
                                                <td class="px-4 py-2 text-center">
                                                    <i class="fas fa-trash text-red-500 cursor-pointer transition-all duration-200 hover:text-red-600 hover:scale-125" 
                                                    @click="removeProduct(index)"></i>
                                                </td>
                                            </tr>
                                        </template>
                                        <tr v-if="!hasItems">
                                            <td colspan="5" class="text-center py-12 text-gray-400">
                                                <i class="fas fa-shopping-cart text-5xl mb-4 opacity-30"></i>
                                                <p>No items added yet</p>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totals Section -->
                            <div class="bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6">
                                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                                    <div>
                                        <InputLabel for="gross" value="Gross Total" />
                                        <TextInput id="gross" type="number" class="mt-1 block w-full bg-gray-100 font-bold text-xs" v-model="calculatedGross" readonly/>
                                    </div>
                                    <div>
                                        <InputLabel for="discount" value="Discount" />
                                        <TextInput id="discount" type="number" class="mt-1 block w-full text-xs" v-model="form.discount" min="0" />
                                    </div>
                                    <div>
                                        <InputLabel for="profit" value="Profit / Adj" />
                                        <TextInput id="profit" type="number" class="mt-1 block w-full text-xs" v-model="form.profit" min="0" />
                                    </div>
                                    <div>
                                        <InputLabel for="final_value" value="Net Payable" />
                                        <TextInput id="final_value" type="number" class="mt-1 block w-full bg-indigo-50 text-indigo-700 font-bold border-indigo-200 text-xs" v-model="calculatedFinal" readonly />
                                    </div>
                                    <div>
                                        <InputLabel for="received" value="Paid Amount" />
                                        <TextInput id="received" type="number" class="mt-1 block w-full text-xs" v-model="form.received" min="0" />
                                    </div>
                                    <div>
                                        <InputLabel for="remaining" value="Balance" />
                                        <TextInput id="remaining" type="number" class="mt-1 block w-full bg-gray-100 text-xs" v-model="calculatedRemaining" readonly />
                                    </div>
                                </div>
                            </div>

                            <!-- Footer / Remarks -->
                            <div class="flex justify-end gap-6 mb-6">
                                <div class="flex items-end justify-end gap-3">
                                    <PrimaryButton class="w-full md:w-auto justify-center py-3 text-lg" :disabled="form.processing || !isFormValid" :class="{'opacity-50 cursor-not-allowed': form.processing || !isFormValid}">
                                        <i class="fas fa-save mr-2"></i> Process Invoice
                                    </PrimaryButton>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        <div class="lg:col-span-1 space-y-6 mb-3">
            <!-- Balance Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-2">Customer Balance</h3>
                <div class="text-3xl font-bold" :class="customerBalance >= 0 ? 'text-green-600' : 'text-red-600'">
                    {{ Number(Math.abs(customerBalance)).toLocaleString() }}
                </div>
                <div class="text-sm text-gray-500">
                    {{ customerBalance >= 0 ? 'Receivable' : 'Payable' }}
                </div>
            </div>

            <!-- Last 7 Invoices Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4 border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Recent Invoices (Last 7)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-2 py-2 text-left font-medium text-gray-500">Inv #</th>
                                <th class="px-2 py-2 text-left font-medium text-gray-500">Date</th>
                                <th class="px-2 py-2 text-right font-medium text-gray-500">Amount</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <tr v-for="inv in customerInvoices" :key="inv.id">
                                <td class="px-2 py-2"><a target="_blank" :href="`/salePrint/${inv.id}`">{{ inv.id }}</a></td>
                                <td class="px-2 py-2">{{ new Date(inv.day).toLocaleDateString('en-GB') }}</td>
                                <td class="px-2 py-2 text-right">{{ Number(inv.finalVal).toLocaleString() }}</td>
                            </tr>
                            <tr v-if="customerInvoices.length === 0">
                                <td colspan="3" class="px-2 py-4 text-center text-gray-500">No recent invoices</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

        <!-- Products Modal -->
        <Modal2 id="modal_products" :Class="'sm:max-w-6xl'" title="Products Details">
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
                    <tr v-for="(item, index) in displayedProducts" :key="item.unique_key" class="hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-600">{{ item.barcode }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ item.nameFormat }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ item.purchase }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ item.wholesale }}</td>
                        <td class="px-3 py-2 text-gray-600">{{ item.retail }}</td>
                    </tr>
                </tbody>
            </table>
            </div>
        </Modal2>

        <!-- Add Customer Modal -->
        <Modal2 id="modal_customer" :Class="'sm:max-w-3xl'" title="Add Customer">
            <div class="row">
                <div class="col-md-12">
                    <div class="">
                        <div class="">
                            <form @submit.prevent="submitCustomer">
                                <div class="row">
                                    <div class="col-md-12">
                                        <InputLabel for="acc_name" value="Name" required />
                                        <TextInput id="acc_name" type="text" class="mt-1 block w-full"
                                            v-model="customerForm.acc_name" placeholder="Enter Name" required autofocus />
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <InputLabel for="acc_type" value="Type" required />
                                        <Select2 v-model="customerForm.acc_type" :options="accountTypes" />
                                    </div>
                                    <div class="col-md-6">
                                        <InputLabel for="acc_city" value="City" />
                                        <TextInput id="acc_city" type="text" class="mt-1 block w-full"
                                            v-model="customerForm.acc_city" placeholder="Enter City" />
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <InputLabel for="acc_address" value="Address" />
                                        <TextInput id="acc_address" type="text" class="mt-1 block w-full"
                                            v-model="customerForm.acc_address" placeholder="Enter Address" />
                                    </div>
                                    <div class="col-md-6">
                                        <InputLabel for="acc_contact_per" value="Contact Person" />
                                        <TextInput id="acc_contact_per" type="number" class="mt-1 block w-full"
                                            v-model="customerForm.acc_contact_per" placeholder="Enter Contact Person" />
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <InputLabel for="acc_mobile" value="Mobile#" />
                                        <TextInput id="acc_mobile" type="number" class="mt-1 block w-full"
                                            v-model="customerForm.acc_mobile" placeholder="Enter Mobile#" />
                                    </div>
                                    <div class="col-md-6">
                                        <InputLabel for="acc_email" value="Email" />
                                        <TextInput id="acc_email" type="email" class="mt-1 block w-full"
                                            v-model="customerForm.acc_email" placeholder="Enter Email" />
                                    </div>
                                </div>
                                <div class="row mt-2">
                                    <div class="col-md-6">
                                        <InputLabel for="acc_tel1" value="Telephone No 1" />
                                        <TextInput id="acc_tel1" type="number" class="mt-1 block w-full"
                                            v-model="customerForm.acc_tel1" placeholder="Enter Telephone No 1" />
                                    </div>
                                    <div class="col-md-6">
                                        <InputLabel for="acc_tel2" value="Telephone No 2" />
                                        <TextInput id="acc_tel2" type="number" class="mt-1 block w-full"
                                            v-model="customerForm.acc_tel2" placeholder="Enter Telephone No 2" />
                                    </div>
                                </div>

                                <div class="flex items-center justify-end mt-4">
                                    <PrimaryButton class="btn btn-primary" :disabled="customerForm.processing">
                                        Save
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </Modal2>
        
        <span id="signal" class="hidden">{{ signal = slotProps.signal }}</span>
    </InvoiceLayout>
</template>

<style scoped>
/* Force the background color for the highlighted row */
:deep(.highlight-row) {
    background-color: #bfdbfe !important; /* blue-200 */
}
:deep(.highlight-row td) {
    background-color: #bfdbfe !important; /* Ensure cells also get the color */
}
</style>
