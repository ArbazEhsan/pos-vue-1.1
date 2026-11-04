<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Toast from '@/Components/Toast.vue';


defineProps({
    data: {
        type: Object,
    },
});


const computedtext = (e) => {
    let txt = "Unactive";
    if (e == 1) {
        txt = "Active";
    }
    return txt;
}
</script>

<template>

    <Head title="User" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Users</h1>
            <Link :href="route('viewUserForm')" class="btn btn-success">New</Link>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card overflow-auto">
                    <div class="card-body">
                        <table class="table">
                            <thead class="text-sm text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th>Sr#</th>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Password</th>
                                    <th>Type</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="product, index in data">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ product.id }}</td>
                                    <td>{{ product.name }}</td>
                                    <td>{{ product.email }}</td>
                                    <td>{{ product.user_detail?.password }}</td>
                                    <td>{{ product.type }}</td>
                                    <td>
                                        <div class="btn-group">
                                            <Link class="text-blue-600" 
                                                :href="route('editUser', product.id)">
                                                <i class="fas fa-edit" title="Edit"></i>
                                            </Link>
                                            <Link class="text-blue-600 hover:text-blue-700 ml-3"
                                                :href="route('userRights', product.id)">
                                                <i class="fas fa-user" title="Set Permissions"></i>
                                            </Link>
                                            <Link class="text-red-600 hover:text-red-700 ml-3"
                                                :href="route('deleteUser', product.id)">
                                                <i class="fas fa-trash" title="Delete"></i>
                                            </Link>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <Toast v-if="$page.props.flash.message" :msg="$page.props.flash.message" />

    </AuthenticatedLayout>
</template>
