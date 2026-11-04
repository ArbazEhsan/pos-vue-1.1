<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({
    categories: {
        type: Array,
    },
});

const form = useForm({
    pro_name: '',
    pro_short_code: '',
    pro_category: '0',
    pro_pur_price: '',
    pro_w_price: '',
    pro_r_price: '',
    pro_qty: '',
});

const submit = () => {
    form.post(route('insertStock'), {});
};

</script>

<template>

    <Head title="Stock" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Add Stock</h1>
        </div>

        <div class="row pt-5">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-12">
                                    <InputLabel for="pro_name" value="Name" />

                                    <TextInput id="pro_name" type="text" class="mt-1 block w-full"
                                        v-model="form.pro_name" required autofocus placeholder="Enter Name" />

                                    <InputError class="mt-2" :message="form.errors.pro_name" />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="pro_short_code" value="Short Code" />

                                    <TextInput id="pro_short_code" type="number" class="mt-1 block w-full"
                                        v-model="form.pro_short_code" required placeholder="Enter Short Code" />

                                    <InputError class="mt-2" :message="form.errors.pro_short_code" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="pro_category" value="Select Category" />

                                    <select 
                                        class="mt-1 block w-full border-gray-300 focus:border-indigo-500 
                                        :ring-indigo-500 rounded-md shadow-sm" 
                                        id="pro_category" 
                                        v-model="form.pro_category" 
                                        required>
                                        <option disabled value="0">-- Select --</option>
                                        <option v-for="category in categories" :value="category.id">
                                            {{ category.name }}
                                        </option>
                                    </select>

                                    <InputError class="mt-2" :message="form.errors.pro_category" />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="pro_pur_price" value="Purchase Price" />

                                    <TextInput id="pro_pur_price" type="number" class="mt-1 block w-full"
                                        v-model="form.pro_pur_price" required placeholder="Enter Purchase Price" />

                                    <InputError class="mt-2" :message="form.errors.pro_pur_price" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="pro_w_price" value="Wholesale Price" />

                                    <TextInput id="pro_w_price" type="number" class="mt-1 block w-full"
                                        v-model="form.pro_w_price" required placeholder="Enter Wholesale Price" />

                                    <InputError class="mt-2" :message="form.errors.pro_w_price" />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="pro_r_price" value="Retail Price" />

                                    <TextInput id="pro_r_price" type="number" class="mt-1 block w-full"
                                        v-model="form.pro_r_price" required placeholder="Enter Retail Price" />

                                    <InputError class="mt-2" :message="form.errors.pro_r_price" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="pro_qty" value="Min Qty" />

                                    <TextInput id="pro_qty" type="number" class="mt-1 block w-full"
                                        v-model="form.pro_qty" required placeholder="Enter Min Qty" />

                                    <InputError class="mt-2" :message="form.errors.pro_qty" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton class="btn btn-primary">
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
