<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Pagination from '@/Components/Pagination.vue';
import Search from '@/Components/Search.vue';
import Toast from '@/Components/Toast.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';


const props = defineProps({
    data: {
        type: Array,
    },
});

const form = useForm({
    product_qty: [],
    product_id: [],
});

const message = ref(false);

const inputs = ref({});

const update = () => {
    form.product_qty = [];
    form.product_id = [];

    for (const id in inputs.value) {
        const qty = inputs.value[id];
        if (qty !== '' && qty !== null && qty !== undefined) {
            form.product_id.push(id);
            form.product_qty.push(qty);
        }
    }
    
    form.post(route('updateProductQty'), {
        onSuccess: () => {
            message.value = true;
            inputs.value = {};
            setTimeout(() => message.value = false, 3000);
        },
        onError: () => {
            alert("Went Wrong");
        }
    });
};


// datatable and select2 initialization
$(document).ready(function() {
    
    $('.js-example-basic-single').select2({
        placeholder: "Select an option",
        allowClear: true
    });

    $(".datatable").DataTable({
        dom: "<'flex items-center justify-between' <'flex items-center space-x-4' l> f> r t i p",
        pageLength: 10,
    });
});

</script>

<template>

    <Head title="Countsheet" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Countsheet</h1>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card overflow-auto">
                    <div class="card-body">
                        <form @submit.prevent="update">
                            <table class="table datatable">
                                <thead class="text-sm text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th>Sr#</th>
                                        <th>Barcode</th>
                                        <th>Name</th>
                                        <th>Purchase Price</th>
                                        <th>Remaining Qty</th>
                                        <th>New Qty</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="product, index in data" :key="index">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ product.barcode }}</td>
                                        <td>{{ product.name }}</td>
                                        <td>{{ product.purchase }} </td>
                                        <td>{{ product.qty }} </td>
                                        <td>
                                            <TextInput type="number" class="mt-1 block w-full"
                                                v-model="inputs[product.id]" :id="`qty${index+1}`" placeholder="Enter Quantity" />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="flex items-center justify-start mt-4">
                                <PrimaryButton class="btn btn-primary">
                                    Update
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <Toast v-if="message" :msg="'Updated Successfully'" />

    </AuthenticatedLayout>
</template>
