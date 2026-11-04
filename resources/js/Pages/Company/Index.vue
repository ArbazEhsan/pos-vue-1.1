<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Toast from '@/Components/Toast.vue';


defineProps({
    companies: {
        type: Array,
    },
});

</script>

<template>

    <Head title="Company" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Company</h1>
        </div>

        <div class="row pt-4">
            <div class="col-md-12">
                <div class="card overflow-auto">
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>
                                        <Link :href="route('addCompany')" class="btn btn-success" >New</Link>
                                    </th>
                                </tr>
                            </thead>
                        </table>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Sr#</th>
                                    <th>Name</th>
                                    <th>Phone_1</th>
                                    <th>Phone_2</th>
                                    <th>Operation</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="company, index in companies">
                                    <td>{{ index + 1 }}</td>
                                    <td>{{ company.name }}</td>
                                    <td>{{ company.phone1 }}</td>
                                    <td>{{ company.phone2 }}</td>
                                    <td>
                                        <Link class="text-red-600 hover:text-red-700" :href="route('deleteCompany', company.id)">
                                            <i class="fas fa-trash"></i>
                                        </Link>
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
