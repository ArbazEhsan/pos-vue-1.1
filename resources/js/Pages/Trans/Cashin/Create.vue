<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectWithSearch from '@/Components/SelectWithSearch.vue';


const props = defineProps({
    accounts: {
        type: Array,
    },
});

const allAccounts = ref([...props.accounts]);

const form = useForm({
    cashin_day: new Date().toLocaleDateString('en-CA'),
    cashin_cust_id: '',
    cashin_amount: '',
    cashin_bill_no: '',
    cashin_remarks: '',

});

// Format account options for SelectWithSearch
const accountOptions = computed(() => {
    return allAccounts.value.map(account => ({
        value: account.id,
        label: account.name
    }));
});

const isFormValid = computed(() => {
    return form.cashin_day && 
           form.cashin_cust_id &&
           form.cashin_cust_id != '0' &&
           form.cashin_amount &&
           form.cashin_amount > 0;
});

const submit = () => {
    if (!form.cashin_cust_id || form.cashin_cust_id === '0') {
        form.setError('cashin_cust_id', 'Please select an account.');
        return;
    }
    form.post(route('insertTransIn'), {});
};


</script>

<template>

    <Head title="Cashin" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Add Cashin</h1>
        </div>

        <div class="row pt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="cashin_day">
                                        Date <span class="text-danger">*</span>
                                    </InputLabel>
                                    <TextInput id="cashin_day" type="date" class="mt-1 block w-full"
                                        v-model="form.cashin_day" autofocus placeholder="Enter Date" required/>

                                    <InputError class="mt-2" :message="form.errors.cashin_day" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="cashin_cust_id" value="Account Name" required />

                                    <!-- <Select2 
                                        id="cashin_cust_id"
                                        v-model="form.cashin_cust_id" 
                                        :options="accounts" 
                                        class="mt-1 block w-full"
                                        @change="form.clearErrors('cashin_cust_id')"
                                    /> -->
                                    <SelectWithSearch
                                        v-model="form.cashin_cust_id"
                                        :options="accountOptions"
                                        placeholder="-- Select Account --"
                                        search-placeholder="Search Account..."
                                        class="flex-1 text-xs"
                                    />

                                    <InputError class="mt-2" :message="form.errors.cashin_cust_id" />
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="cashin_amount">
                                        Amount <span class="text-danger">*</span>
                                    </InputLabel>

                                    <TextInput id="cashin_amount" type="number" class="mt-1 block w-full"
                                        v-model="form.cashin_amount" placeholder="Enter Amount" required/>

                                    <InputError class="mt-2" :message="form.errors.cashin_amount" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="cashin_remarks" value="Remarks" />

                                    <TextInput id="cashin_remarks" type="text" class="mt-1 block w-full"
                                        v-model="form.cashin_remarks" placeholder="Enter Remarks" />

                                    <InputError class="mt-2" :message="form.errors.cashin_remarks" />
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="cashin_bill_no" value="Bill No" />

                                    <TextInput id="cashin_bill_no" type="number" class="mt-1 block w-full"
                                        v-model="form.cashin_bill_no" placeholder="Enter Bill No" />

                                    <InputError class="mt-2" :message="form.errors.cashin_bill_no" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton class="btn btn-primary" :disabled="form.processing || !isFormValid" :class="{'opacity-50 cursor-not-allowed': form.processing || !isFormValid}">
                                    {{ form.processing ? 'Saving...' : 'Save' }}
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
