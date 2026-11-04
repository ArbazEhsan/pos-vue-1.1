<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SelectWithSearch from '@/Components/SelectWithSearch.vue';

const form = useForm({
    acc_name: '',
    acc_type: '',
    acc_city: '',
    acc_address: '',
    acc_contact_per: '',
    acc_mobile: '',
    acc_email: '',
    acc_tel1: '',
    acc_tel2: '',
});

const accountTypes = [
    { value: 'Asset', label: 'Asset' },
    { value: 'Capital', label: 'Capital' },
    { value: 'Customer', label: 'Customer' },
    { value: 'Expense', label: 'Expense' },
    { value: 'Liability', label: 'Liability' },
    { value: 'Vendor', label: 'Vendor' },
];

const isFormValid = computed(() => {
    return form.acc_name && 
           form.acc_type &&
           form.acc_type != 0;
});

const submit = () => {
    form.post(route('insertAccount'), {});
};

</script>

<template>

    <Head title="Account" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Add Account</h1>
        </div>

        <div class="row pt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-12">
                                    <InputLabel for="acc_name" value="Name" required />
                                    <TextInput id="acc_name" type="text" class="mt-1 block w-full"
                                        v-model="form.acc_name" required autofocus placeholder="Enter Name" />
                                    <InputError class="mt-2" :message="form.errors.acc_name" />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="acc_type" value="Select Type" required />

                                    <SelectWithSearch
                                        v-model="form.acc_type"
                                        :options="accountTypes"
                                        :multiple="false"
                                        placeholder="Select Type"
                                        search-placeholder="Search here..."
                                        class="flex-1 text-xs border-none"
                                    />

                                    <InputError class="mt-2" :message="form.errors.acc_type" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="acc_city" value="City" />

                                    <TextInput id="acc_city" type="text" class="mt-1 block w-full"
                                        v-model="form.acc_city" placeholder="Enter City" />

                                    <InputError class="mt-2" :message="form.errors.acc_city" />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="acc_address" value="Address" />

                                    <TextInput id="acc_address" type="text" class="mt-1 block w-full"
                                        v-model="form.acc_address" placeholder="Enter Address" />

                                    <InputError class="mt-2" :message="form.errors.acc_address" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="acc_contact_per" value="Contact Person" />

                                    <TextInput id="acc_contact_per" type="number" class="mt-1 block w-full"
                                        v-model="form.acc_contact_per" placeholder="Enter Contact Person" />

                                    <InputError class="mt-2" :message="form.errors.acc_contact_per" />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="acc_mobile" value="Mobile#" />

                                    <TextInput id="acc_mobile" type="number" class="mt-1 block w-full"
                                        v-model="form.acc_mobile" placeholder="Enter Mobile#" />

                                    <InputError class="mt-2" :message="form.errors.acc_mobile" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="acc_email" value="Email" />

                                    <TextInput id="acc_email" type="email" class="mt-1 block w-full"
                                        v-model="form.acc_email" placeholder="Enter Email" />

                                    <InputError class="mt-2" :message="form.errors.acc_email" />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="acc_tel1" value="Telephone No 1" />

                                    <TextInput id="acc_tel1" type="number" class="mt-1 block w-full"
                                        v-model="form.acc_tel1" placeholder="Enter Telephone No 1" />

                                    <InputError class="mt-2" :message="form.errors.acc_tel1" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="acc_tel2" value="Telephone No 2" />

                                    <TextInput id="acc_tel2" type="number" class="mt-1 block w-full"
                                        v-model="form.acc_tel2" placeholder="Enter Telephone No 2" />

                                    <InputError class="mt-2" :message="form.errors.acc_tel2" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton class="btn btn-primary" :disabled="form.processing || !isFormValid" :class="{'opacity-50 cursor-not-allowed': form.processing || !isFormValid}">
                                    Save
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
