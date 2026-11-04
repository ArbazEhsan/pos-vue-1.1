<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    comp_name: '',
    comp_p_1: '',
    comp_p_2: '',
});

const submit = () => {
    form.post(route('insertCompany'), {});
};

</script>

<template>

    <Head title="Company" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Add Company</h1>
        </div>

        <div class="row pt-4">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div class="row">
                                <div class="col-md-12">
                                    <InputLabel for="comp_name" value="Name" />

                                    <TextInput id="comp_name" type="text" class="mt-1 block w-full"
                                        v-model="form.comp_name" required autofocus placeholder="Enter Name" />

                                    <InputError class="mt-2" :message="form.errors.comp_name" />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="comp_p_1" value="Phone #1" />

                                    <TextInput id="comp_p_1" type="text" class="mt-1 block w-full"
                                        v-model="form.comp_p_1" required placeholder="Enter Phone #1" />

                                    <InputError class="mt-2" :message="form.errors.comp_p_1" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="comp_p_2" value="Phone #2" />

                                    <TextInput id="comp_p_2" type="text" class="mt-1 block w-full"
                                        v-model="form.comp_p_2" required placeholder="Enter Phone #2" />

                                    <InputError class="mt-2" :message="form.errors.comp_p_2" />
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
