<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Toast from '@/Components/Toast.vue';
import Modal2 from '@/Components/Modal2.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import BaseDataTable from '@/Components/baseDataTable.vue';
import Checkbox from '@/Components/Checkbox.vue';
import axios from 'axios';


const props = defineProps({
    tax: Array,
    taxGroup: Array,
});

// Tax Form
const taxForm = useForm({
    id: 0,
    name: '',
    rate: '',
    is_group_only: false,
});

// Tax Group Form
const taxGroupForm = useForm({
    id: 0,
    name: '',
    tax_ids: [],
});

const availableTaxes = ref([]);
const taxTableKey = ref(0);
const taxGroupTableKey = ref(1);

// Tax Columns
const taxColumns = [
    { data: 'id', title: 'ID', width: '60px' },
    { data: 'name', title: 'Name' },
    { data: 'rate', title: 'Rate (%)', render: (data) => `${parseFloat(data).toFixed(2)}%` },
    { data: 'is_group_only', title: 'Group Only', render: (data) => data ? '<span class="badge bg-info text-white">Yes</span>' : '<span class="badge bg-secondary text-white">No</span>' },
    { data: "status", title: "Status", name: 'product-status', slots: { default: 'column-product-status' } },
    { data: null, title: 'Actions', orderable: false, render: (data, type, row) => {
            return `
                <button class="btn btn-sm btn-primary edit-tax" data-id="${row.id}">
                    <i class="fa fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-${row.status ? 'warning' : 'success'} toggle-tax-status" data-id="${row.id}">
                    <i class="fa fa-${row.status ? 'ban' : 'check'}"></i>
                </button>
                <button class="btn btn-sm btn-danger delete-tax" data-id="${row.id}">
                    <i class="fa fa-trash"></i>
                </button>
            `;
        }
    }
];

// Tax Group Columns
const taxGroupColumns = [
    { data: 'id', title: 'ID', width: '60px' },
    { data: 'name', title: 'Name' },
    { data: 'tax_details', title: 'Taxes', render: (data) => {
            if (!data || data.length === 0) return '-';
            return data.map(t => `${t.name}`).join(', ');
        }
    },
    { data: 'total_rate', title: 'Total Rate (%)', render: (data) => `${parseFloat(data || 0).toFixed(2)}%` },
    { data: "status", title: "Status", name: 'product-status', slots: { default: 'column-product-status' } },
    { data: null, title: 'Actions', orderable: false, render: (data, type, row) => {
            return `
                <button class="btn btn-sm btn-primary edit-tax-group" data-id="${row.id}">
                    <i class="fa fa-edit"></i>
                </button>
                <button class="btn btn-sm btn-${row.status ? 'warning' : 'success'} toggle-tax-group-status" data-id="${row.id}">
                    <i class="fa fa-${row.status ? 'ban' : 'check'}"></i>
                </button>
                <button class="btn btn-sm btn-danger delete-tax-group" data-id="${row.id}">
                    <i class="fa fa-trash"></i>
                </button>
            `;
        }
    }
];

// Submit Tax
const submitTax = () => {
    const route = taxForm.id ? 'tax.update' : 'tax.store';
    taxForm.post(window.route(route), {
        onSuccess: () => {
            $('#modal_tax').modal('hide');
            taxForm.reset();
            taxTableKey.value++;
        },
    });
};

// Submit Tax Group
const submitTaxGroup = () => {
    const route = taxGroupForm.id ? 'taxGroup.update' : 'taxGroup.store';
    taxGroupForm.post(window.route(route), {
        onSuccess: () => {
            $('#modal_tax_group').modal('hide');
            taxGroupForm.reset();
            taxGroupTableKey.value++;
        },
    });
};

// Add/Edit Tax
const addEditTax = async (id = 0) => {
    if (id === 0) {
        taxForm.reset();
        $('#modal_tax').modal('show');
    } else {
        try {
            const response = props.tax.find(t => t.id === id);
            taxForm.id = response.id;
            taxForm.name = response.name;
            taxForm.rate = response.rate;
            taxForm.is_group_only = response.is_group_only;
            $('#modal_tax').modal('show');
        } catch (error) {
            alert('Error loading tax: ' + (error.response?.data?.message || error.message));
        }
    }
};

// Add/Edit Tax Group
const addEditTaxGroup = async (id = 0) => {
    if (id === 0) {
        taxGroupForm.reset();
        await loadActiveTaxes();
        $('#modal_tax_group').modal('show');
    } else {
        try {
            await loadActiveTaxes();
            const response = props.taxGroup.find(t => t.id === id);
            taxGroupForm.id = response.id;
            taxGroupForm.name = response.name;
            taxGroupForm.tax_ids = response.tax_ids || [];
            $('#modal_tax_group').modal('show');
        } catch (error) {
            alert('Error loading tax group: ' + (error.response?.data?.message || error.message));
        }
    }
};

// Load Active Taxes
const loadActiveTaxes = async () => {
    try {
        availableTaxes.value = props.tax.filter(t => !t.is_group_only);
    } catch (error) {
        console.error('Error loading taxes:', error);
    }
};

// Delete Tax
const deleteTax = async (id) => {
    if (confirm('Are you sure you want to delete this tax?')) {
        try {
            await axios.get(window.route('tax.delete', { id }));
            taxTableKey.value++;
        } catch (error) {
            alert('Error: ' + (error.response?.data?.message || error.message));
        }
    }
};

// Delete Tax Group
const deleteTaxGroup = async (id) => {
    if (confirm('Are you sure you want to delete this tax group?')) {
        try {
            await axios.get(window.route('taxGroup.delete', { id }));
            taxGroupTableKey.value++;
        } catch (error) {
            alert('Error: ' + (error.response?.data?.message || error.message));
        }
    }
};

// Toggle Tax Status
const toggleTaxStatus = async (id) => {
    try {
        await axios.get(window.route('tax.toggleStatus', { id }));
        taxTableKey.value++;
    } catch (error) {
        alert('Error: ' + (error.response?.data?.message || error.message));
    }
};

// Toggle Tax Group Status
const toggleTaxGroupStatus = async (id) => {
    try {
        await axios.get(window.route('taxGroup.toggleStatus', { id }));
        taxGroupTableKey.value++;
    } catch (error) {
        alert('Error: ' + (error.response?.data?.message || error.message));
    }
};

// Setup event listeners
onMounted(() => {
    // Tax table events
    $(document).on('click', '.edit-tax', function() {
        addEditTax($(this).data('id'));
    });
    
    $(document).on('click', '.delete-tax', function() {
        deleteTax($(this).data('id'));
    });
    
    $(document).on('click', '.toggle-tax-status', function() {
        toggleTaxStatus($(this).data('id'));
    });
    
    // Tax group table events
    $(document).on('click', '.edit-tax-group', function() {
        addEditTaxGroup($(this).data('id'));
    });
    
    $(document).on('click', '.delete-tax-group', function() {
        deleteTaxGroup($(this).data('id'));
    });
    
    $(document).on('click', '.toggle-tax-group-status', function() {
        toggleTaxGroupStatus($(this).data('id'));
    });
});
</script>

<template>
    <Head title="Tax Management" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa fa-percent"></i> Business Locations
            </h1>
        </div>

        <!-- Taxes Section -->
        <div class="row">
            <div class="col-md-12 mb-4">
                <div class="card shadow-sm">
                    <div class="bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold text-primary">
                            <i class="fa fa-list"></i> Taxes
                        </h5>
                        <PrimaryButton @click="addEditTax(0)" class="btn btn-primary">
                            <i class="fa fa-plus mr-1"></i> New Tax
                        </PrimaryButton>
                    </div>
                    <div class="card-body">
                        <BaseDataTable
                            :key="taxTableKey"
                            :columns="taxColumns"
                            :ajaxUrl="route('tax.getTaxes')"
                            :theadClass="'text-xs'"
                            class="text-sm"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Tax Groups Section -->
        <div class="row">
            <div class="col-md-12">
                <div class="card shadow-sm">
                    <div class="bg-white py-3 px-4 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 font-weight-bold text-success">
                            <i class="fa fa-layer-group"></i> Tax Groups
                        </h5>
                        <PrimaryButton @click="addEditTaxGroup(0)" class="btn btn-success">
                            <i class="fa fa-plus mr-1"></i> New Tax Group
                        </PrimaryButton>
                    </div>
                    <div class="card-body">
                        <BaseDataTable
                            :key="taxGroupTableKey"
                            :columns="taxGroupColumns"
                            :ajaxUrl="route('taxGroup.getTaxGroups')"
                            :theadClass="'text-xs'"
                            class="text-sm"
                        />
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notifications -->
        <Toast v-if="$page.props.flash.message" :msg="$page.props.flash.message" />
        <Toast v-if="$page.props.flash.error" :msg="$page.props.flash.error" />

        <!-- Tax Modal -->
        <Modal2 id="modal_tax" :title="taxForm.id ? 'Edit Tax' : 'Add Tax'">
            <form @submit.prevent="submitTax">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <InputLabel for="tax_name" value="Tax Name" />
                        <TextInput
                            id="tax_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="taxForm.name"
                            required
                            placeholder="e.g., VAT, GST, Sales Tax"
                        />
                        <InputError class="mt-2" :message="taxForm.errors.name" />
                    </div>

                    <div class="col-md-12 mb-3">
                        <InputLabel for="tax_rate" value="Tax Rate (%)" />
                        <TextInput
                            id="tax_rate"
                            type="number"
                            step="0.01"
                            min="0"
                            max="100"
                            class="mt-1 block w-full"
                            v-model="taxForm.rate"
                            required
                            placeholder="e.g., 5.00"
                        />
                        <InputError class="mt-2" :message="taxForm.errors.rate" />
                    </div>

                    <div class="col-md-12 mb-3">
                        <div class="flex items-center">
                            <Checkbox
                                id="is_group_only"
                                v-model:checked="taxForm.is_group_only"
                            />
                            <InputLabel for="is_group_only" class="ml-2 mt-2 text-sm text-gray-700">
                                For Tax Group Only
                                <span class="text-xs text-gray-500">(This tax can only be used in tax groups)</span>
                            </InputLabel>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <PrimaryButton class="btn btn-primary" :disabled="taxForm.processing">
                            <i class="fa fa-save mr-1"></i> Save Tax
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </Modal2>

        <!-- Tax Group Modal -->
        <Modal2 id="modal_tax_group" :title="taxGroupForm.id ? 'Edit Tax Group' : 'Add Tax Group'">
            <form @submit.prevent="submitTaxGroup">
                <div class="row">
                    <div class="col-md-12 mb-3">
                        <InputLabel for="tax_group_name" value="Tax Group Name" />
                        <TextInput
                            id="tax_group_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="taxGroupForm.name"
                            required
                            placeholder="e.g., Standard Tax, Combined Tax"
                        />
                        <InputError class="mt-2" :message="taxGroupForm.errors.name" />
                    </div>

                    <div class="col-md-12 mb-3">
                        <InputLabel for="tax_group_taxes" value="Select Taxes" />
                        <div class="mt-2 border rounded p-3 bg-gray-50" style="max-height: 300px; overflow-y: auto;">
                            <div v-if="availableTaxes.length === 0" class="text-center text-gray-500 py-3">
                                No active taxes available. Please create taxes first.
                            </div>
                            <div v-else>
                                <div
                                    v-for="tax in availableTaxes"
                                    :key="tax.id"
                                    class="flex items-center mb-2 p-2 hover:bg-gray-100 rounded"
                                >
                                    <Checkbox
                                        :id="`tax_${tax.id}`"
                                        :value="tax.id"
                                        v-model:checked="taxGroupForm.tax_ids"
                                        :modelValue="taxGroupForm.tax_ids"
                                        @update:modelValue="(val) => {
                                            if (val.includes(tax.id)) {
                                                if (!taxGroupForm.tax_ids.includes(tax.id)) {
                                                    taxGroupForm.tax_ids.push(tax.id);
                                                }
                                            } else {
                                                taxGroupForm.tax_ids = taxGroupForm.tax_ids.filter(id => id !== tax.id);
                                            }
                                        }"
                                    />
                                    <InputLabel :for="`tax_${tax.id}`" class="ml-2 mt-2 text-sm cursor-pointer flex-1">
                                        <span class="font-medium">{{ tax.name }}</span>
                                        <span class="text-gray-600 ml-2">({{ parseFloat(tax.rate).toFixed(2) }}%)</span>
                                    </InputLabel>
                                </div>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="taxGroupForm.errors.tax_ids" />
                        <p class="text-xs text-gray-500 mt-1">
                            Select multiple taxes to combine them into a group
                        </p>
                    </div>

                    <div class="col-md-12">
                        <PrimaryButton class="btn btn-success" :disabled="taxGroupForm.processing">
                            <i class="fa fa-save mr-1"></i> Save Tax Group
                        </PrimaryButton>
                    </div>
                </div>
            </form>
        </Modal2>

    </AuthenticatedLayout>
</template>
