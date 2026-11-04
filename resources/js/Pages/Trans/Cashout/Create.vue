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
        default: () => [],
    },
});

const allAccounts = ref([...props.accounts]);

const form = useForm({
    cashout_day: new Date().toLocaleDateString('en-CA'),
    cashout_cust_id: '',
    cashout_amount: '',
    cashout_bill_no: '',
    cashout_remarks: '',
});

// Format account options for SelectWithSearch
const accountOptions = computed(() => {
    return allAccounts.value.map(account => ({
        value: account.id,
        label: account.name
    }));
});

const isFormValid = computed(() => {
    return form.cashout_day && 
           form.cashout_cust_id &&
           form.cashout_cust_id != '0' &&
           form.cashout_amount &&
           form.cashout_amount > 0;
});

const submit = () => {
    if (!form.cashout_cust_id || form.cashout_cust_id === '0') {
        form.setError('cashout_cust_id', 'Please select an account.');
        return;
    }
    form.post(route('insertTransOut'), {});
};
</script>

<template>

    <Head title="Cashout" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Add Cashout</h1>
        </div>

        <div class="row pt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="cashout_day">
                                        Date <span class="text-danger">*</span>
                                    </InputLabel>

                                    <TextInput id="cashout_day" type="date" class="mt-1 block w-full"
                                        v-model="form.cashout_day" autofocus placeholder="Enter Date" required/>

                                    <InputError class="mt-2" :message="form.errors.cashout_day" />
                                </div>
                                <div class="col-md-6">

                                    <InputLabel for="cashout_cust_id" value="Account Name" required />

                                    <!-- <Select2 
                                        id="cashout_cust_id"
                                        v-model="form.cashout_cust_id" 
                                        :options="accounts" 
                                        class="mt-1 block w-full"
                                        @change="form.clearErrors('cashout_cust_id')"
                                    /> -->
                                    <SelectWithSearch
                                        v-model="form.cashout_cust_id"
                                        :options="accountOptions"
                                        placeholder="-- Select Account --"
                                        search-placeholder="Search Account..."
                                        class="flex-1 text-xs"
                                    />

                                    <InputError class="mt-2" :message="form.errors.cashout_cust_id" />
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="cashout_amount">
                                        Amount <span class="text-danger">*</span>
                                    </InputLabel>

                                    <TextInput id="cashout_amount" type="number" class="mt-1 block w-full"
                                        v-model="form.cashout_amount" placeholder="Enter Amount" required/>

                                    <InputError class="mt-2" :message="form.errors.cashout_amount" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="cashout_remarks" value="Remarks" />

                                    <TextInput id="cashout_remarks" type="text" class="mt-1 block w-full"
                                        v-model="form.cashout_remarks" placeholder="Enter Remarks" />

                                    <InputError class="mt-2" :message="form.errors.cashout_remarks" />
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="cashout_bill_no" value="Bill No" />

                                    <TextInput id="cashout_bill_no" type="number" class="mt-1 block w-full"
                                        v-model="form.cashout_bill_no" placeholder="Enter Bill No" />

                                    <InputError class="mt-2" :message="form.errors.cashout_bill_no" />
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
