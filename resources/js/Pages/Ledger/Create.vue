<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted, ref, computed } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectWithSearch from '@/Components/SelectWithSearch.vue';


const props = defineProps({
    cust_accounts: {
        type: Array,
    },
    vend_accounts: {
        type: Array,
    },
});

const allCustomers = ref([...props.cust_accounts]);
const allVendors = ref([...props.vend_accounts]);

const form = useForm({
    from: new Date().toISOString().substr(0, 10),
    to: new Date().toISOString().substr(0, 10),
    source: 'Customer',
    acc_type: '0',
});

// Format customer options for SelectWithSearch
const customerOptions = computed(() => {
    return allCustomers.value.map(account => ({
        value: account.id,
        label: account.name
    }));
});

// Format vendor options for SelectWithSearch
const vendorOptions = computed(() => {
    return allVendors.value.map(account => ({
        value: account.id,
        label: account.name
    }));
});

onMounted(() => {
    // Update Vue data when the user selects an option
    $("#acc_type").on("change", function () {
        form.acc_type = $(this).val(); 
    });
});

const submit = () => {
    form.get(route('Ledger'), {});
};

let show_cust = true;
let show_vend = false;
const change = (e) => {
    if(e == 'c'){
        show_cust = true;
        show_vend = false;
    }
    else {
        show_cust = false;
        show_vend = true;
    }
};

const isFormValid = computed(() => {
    return form.from && 
           form.to &&
           form.acc_type &&
           form.acc_type != 0;
});

// datatable and select2 initialization
$(document).ready(function() {
    
    $('.js-example-basic-single').select2({
        placeholder: "Select an option",
        allowClear: true
    });

    // Ensure focus on search input when dropdown opens
    $('.js-example-basic-single').on('select2:open', function () {
        setTimeout(() => {
            document.querySelector('.select2-search__field').focus();
        }, 10);
    });

});

</script>

<template>

    <Head title="Ledger" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Generate Ledger</h1>
        </div>

        <div class="row pt-4">
            <div class="col-md-7">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-6">
                                    <InputLabel for="from" value="From" required />

                                    <TextInput id="from" type="date" class="mt-1 block w-full" v-model="form.from"
                                         autofocus required placeholder="Enter From" />

                                    <InputError class="mt-2" :message="form.errors.from" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="to" value="To" required />

                                    <TextInput id="to" type="date" class="mt-1 block w-full" v-model="form.to"
                                        required placeholder="Enter To" />

                                    <InputError class="mt-2" :message="form.errors.to" />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <InputLabel for="source" value="Select Type" />
                                    Customer
                                    <input type="radio" id="source" v-model="form.source" value="Customer" @click="change('c')" checked>
                                    Vendor
                                    <input type="radio" id="source" v-model="form.source" value="Vendor" @click="change('v')">
                                    <InputError class="mt-2" :message="form.errors.source" />
                                </div>

                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <InputLabel for="acc_type" value="Select Type" required />
                                    <!-- <select 
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 
                                        :ring-indigo-500 rounded-md shadow-sm js-example-basic-single" 
                                        id="acc_type" 
                                        v-model="form.acc_type" 
                                        required>
                                        <option disabled value="0">-- Select --</option>
                                        <option v-if="show_cust" v-for="account in cust_accounts" :value="account.id">
                                            {{ account.name }}
                                        </option>
                                        <option v-if="show_vend" v-for="account in vend_accounts" :value="account.id">
                                            {{ account.name }}
                                        </option>

                                    </select> -->
                                    <SelectWithSearch
                                        v-if="show_cust"
                                        v-model="form.acc_type"
                                        :options="customerOptions"
                                        placeholder="-- Select Customer --"
                                        search-placeholder="Search customers..."
                                        class="flex-1 text-xs"
                                    />

                                    <SelectWithSearch
                                        v-if="show_vend"
                                        v-model="form.acc_type"
                                        :options="vendorOptions"
                                        placeholder="-- Select Vendor --"
                                        search-placeholder="Search vendors..."
                                        class="flex-1 text-xs"
                                    />

                                    <InputError class="mt-2" :message="form.errors.acc_type" />
                                </div>

                            </div>
                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton class="btn btn-primary" :disabled="form.processing || !isFormValid" :class="{'opacity-50 cursor-not-allowed': form.processing || !isFormValid}">
                                    Submit
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
