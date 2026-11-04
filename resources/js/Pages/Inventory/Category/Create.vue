<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    cat_name: '',
});

const submit = () => {
    form.post(route('insertCategory'), {});
};

</script>

<template>

    <Head title="Category" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Create Category</h1>
        </div>

        <div class="row pt-5">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="cat_name" value="Name" />

                                <TextInput id="cat_name" type="text" class="mt-1 block w-full" v-model="form.cat_name"
                                    required autofocus placeholder="Enter Name" />

                                <InputError class="mt-2" :message="form.errors.cat_name" />
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
