<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Toast from '@/Components/Toast.vue';
import TextInput from '@/Components/TextInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const props = defineProps({
    data: {
        type: Array,
    },
});

// Local reactive copy of products for editing
const products = ref([]);

// Snapshot of original values to detect changes
let originals = [];

onMounted(() => {
    const list = Array.isArray(props.data)
        ? props.data
        : (props.data?.data ?? []);

    products.value = list.map(p => ({
        id:        p.id,
        barcode:   p.barcode,
        name:      p.name,
        purchase:  p.purchase  ?? '',
        whole_sale: p.whole_sale ?? '',
        retail:    p.retail    ?? '',
    }));

    // Keep a deep snapshot for dirty-checking
    originals = products.value.map(p => ({ ...p }));
});

const form = useForm({
    product_id: [],
    product_pp: [],
    product_wp: [],
    product_rp: [],
});

const update = () => {
    // Reset form arrays
    form.product_id  = [];
    form.product_pp  = [];
    form.product_wp  = [];
    form.product_rp  = [];

    // Only include rows where at least one price has changed
    products.value.forEach((p, i) => {
        const orig = originals[i];
        const ppChanged  = String(p.purchase)   !== String(orig.purchase);
        const wpChanged  = String(p.whole_sale) !== String(orig.whole_sale);
        const rpChanged  = String(p.retail)     !== String(orig.retail);

        if (ppChanged || wpChanged || rpChanged) {
            form.product_id.push(p.id);
            form.product_pp.push(p.purchase);
            form.product_wp.push(p.whole_sale);
            form.product_rp.push(p.retail);
        }
    });

    if (form.product_id.length === 0) {
        alert('No changes detected.');
        return;
    }

    form.post(route('updateBulk'), {
        onSuccess: () => {
            // Update snapshot so subsequent saves only send new changes
            originals = products.value.map(p => ({ ...p }));
        },
    });
};
</script>

<template>

    <Head title="Bulk Price" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Bulk Price Editing</h1>
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
                                        <th>P.P</th>
                                        <th>W.S</th>
                                        <th>R.P</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(product, index) in products" :key="product.id">
                                        <td>{{ index + 1 }}</td>
                                        <td>{{ product.barcode }}</td>
                                        <td>{{ product.name }}</td>
                                        <td>
                                            <TextInput
                                                type="number"
                                                class="mt-1 block w-full"
                                                v-model="product.purchase"
                                                placeholder="Purchase Price"
                                            />
                                        </td>
                                        <td>
                                            <TextInput
                                                type="number"
                                                class="mt-1 block w-full"
                                                v-model="product.whole_sale"
                                                placeholder="Wholesale Price"
                                            />
                                        </td>
                                        <td>
                                            <TextInput
                                                type="number"
                                                class="mt-1 block w-full"
                                                v-model="product.retail"
                                                placeholder="Retail Price"
                                            />
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="flex items-center justify-start mt-4 gap-3">
                                <PrimaryButton :disabled="form.processing">
                                    {{ form.processing ? 'Saving...' : 'Update Changed Rows' }}
                                </PrimaryButton>
                                <span v-if="form.processing" class="text-sm text-gray-500">Saving changes...</span>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <Toast v-if="$page.props.flash.message" :msg="$page.props.flash.message" />

    </AuthenticatedLayout>
</template>
