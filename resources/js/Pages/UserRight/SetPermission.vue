<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    permission: {
        type: Object,
    },
    user: {
        type: Object,
    },
});

const permissionStructure = [
    // { name: 'Category', create: 'category_create', view: 'category_view', update: 'category_update', delete: null },
    { name: 'Dashboard', create: null, view: 'dashboard_view', update: null, delete: null },
    { name: 'Product', create: 'product_create', view: 'product_view', update: 'product_update', delete: null },
    { name: 'Manufacturing', create: 'manufactring_create', view: 'manufactring_view', update: 'manufactring_update', delete: null },
    { name: 'Print Label', create: 'print_label_create', view: null, update: null, delete: null },
    { name: 'Count Sheet', create: null, view: null, update: 'count_sheet_update', delete: null },
    { name: 'Bulk Price', create: null, view: null, update: 'bulk_price_update', delete: null },
    { name: 'Stock Form', create: null, view: 'stock_form_view', update: null, delete: null },
    { name: 'Sale Invoice', create: 'sale_invoice_create', view: 'sale_invoice_view', update: 'sale_invoice_update', delete: null },
    { name: 'Purchase Invoice', create: 'purchase_invoice_create', view: 'purchase_invoice_view', update: 'purchase_invoice_update', delete: null },
    { name: 'Invoice Return', create: null, view: 'invoice_return_view', update: null, delete: null },
    { name: 'Account', create: 'account_create', view: 'account_view', update: 'account_update', delete: null },
    { name: 'Cashin', create: 'trans_cashin_create', view: 'trans_cashin_view', update: null, delete: 'trans_cashin_delete' },
    { name: 'Cashout', create: 'trans_cashout_create', view: 'trans_cashout_view', update: null, delete: 'trans_cashout_delete' },
    { name: 'Ledger', create: 'ledger_create', view: 'ledger_view', update: null, delete: null },
    { name: 'Delivery Order', create: null, view: null, update: 'delivery_order_update', delete: null },
    { name: 'Report', create: 'report_create', view: null, update: null, delete: null },
    { name: 'Settings', create: 'settings_create', view: null, update: null, delete: null },
];

const form = useForm({
    id: props.user.id,
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    acc_type: props.user.type,
    permissions: props.permission?.pages || {},
});

const submit = () => {
    form.post(route('setPermission'), {
        onSuccess: () => {
            // Optional: Handle success actions
        }
    });
};

</script>

<template>

    <Head title="User" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Set Permission</h1>
        </div>

        <div class="row pt-4 mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form @submit.prevent="submit">

                            <div class="align-items-center">
                                <h1 class="">ID: {{ user.id }}</h1>
                                <h1 class="">Name: {{ user.name }}</h1>
                                <h1 class="">Email: {{ user.email }}</h1>
                            </div>

                            <div class="row pt-4">
                                <div class="col-md-12">
                                    <table class="table">
                                        <thead class="text-sm text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                            <tr>
                                                <th>Sr#</th>
                                                <th>Name</th>
                                                <th class="text-center">Create</th>
                                                <th class="text-center">View</th>
                                                <th class="text-center">Update</th>
                                                <th class="text-center">Delete</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(group, index) in permissionStructure" :key="index">
                                                <td>{{ index + 1 }}</td>
                                                <td>{{ group.name }}</td>
                                                <td class="text-center">
                                                    <label v-if="group.create" class="flex items-center justify-center cursor-pointer">
                                                        <input type="checkbox" v-model="form.permissions[group.create]" class="form-checkbox h-5 w-5 text-blue-600" />
                                                    </label>
                                                </td>
                                                <td class="text-center">
                                                    <label v-if="group.view" class="flex items-center justify-center cursor-pointer">
                                                        <input type="checkbox" v-model="form.permissions[group.view]" class="form-checkbox h-5 w-5 text-blue-600" />
                                                    </label>
                                                </td>
                                                <td class="text-center">
                                                    <label v-if="group.update" class="flex items-center justify-center cursor-pointer">
                                                        <input type="checkbox" v-model="form.permissions[group.update]" class="form-checkbox h-5 w-5 text-blue-600" />
                                                    </label>
                                                </td>
                                                <td class="text-center">
                                                    <label v-if="group.delete" class="flex items-center justify-center cursor-pointer">
                                                        <input type="checkbox" v-model="form.permissions[group.delete]" class="form-checkbox h-5 w-5 text-blue-600" />
                                                    </label>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton class="btn btn-primary">
                                    Update Permissions
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </AuthenticatedLayout>
</template>
