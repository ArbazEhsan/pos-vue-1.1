<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';


const props = defineProps({
    accounts: {
        type: Array,
    },
});


const form = useForm({
    acc_id: props.accounts[0].id,
    acc_name: props.accounts[0].name,
    acc_type: props.accounts[0].type,
    acc_city: props.accounts[0].city,
    acc_address: props.accounts[0].address,
    acc_contact_per: props.accounts[0].contact_person,
    acc_mobile: props.accounts[0].mobile,
    acc_email: props.accounts[0].email,
    acc_tel1: props.accounts[0].tel_1,
    acc_tel2: props.accounts[0].tel_2,
});

const submit = () => {
    form.post(route('updateAccount'), {});
};

</script>

<template>

    <Head title="Account" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Edit Account</h1>
        </div>

        <div class="row pt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-12">
                                    <InputLabel for="acc_name" value="Name" />

                                    <TextInput id="acc_name" type="text" class="mt-1 block w-full"
                                        v-model="form.acc_name" required autofocus placeholder="Enter Name" />

                                    <InputError class="mt-2" :message="form.errors.acc_name" />
                                    <input type="text" id="pro_id" name="pro_id" v-model="form.pro_id" hidden/>

                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="acc_type" value="Select Type" />

                                    <select 
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 
                                        :ring-indigo-500 rounded-md shadow-sm" 
                                        id="acc_type" 
                                        v-model="form.acc_type" 
                                        required>
                                        <option disabled selected>-- Select Type --</option>
                                        <option>Asset</option>
                                        <option>Capital</option>
                                        <option>Customer</option>
                                        <option>Expense</option>
                                        <option>Liability</option>
                                        <option>Vendor</option>
                                    </select>

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
                                <PrimaryButton class="btn btn-primary">
                                    Update
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
