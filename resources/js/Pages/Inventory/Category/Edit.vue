<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    categories: {
        type: Object,
    },
});

const form = useForm({
    cat_id: props.categories[0].id,
    cat_name: props.categories[0].name,
});

const submit = () => {
    form.post(route('updateCategory'), {});
};

</script>

<template>

    <Head title="Category" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Edit Category</h1>
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
                                <input type="text" id="cat_id" name="cat_id" v-model="form.cat_id" hidden/>

                                <InputError class="mt-2" :message="form.errors.cat_name" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton class="ms-4" :class="{ 'opacity-25': form.processing }"
                                    :disabled="form.processing">
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
