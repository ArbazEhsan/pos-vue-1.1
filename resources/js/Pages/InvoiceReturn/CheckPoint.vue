<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);

const hasPermission = computed(() => {
    return user.value.type === 'Admin' || user.value.permission?.pages?.invoice_return_view;
});
</script>

<template>
    <Head title="Invoice Return" />
    <AuthenticatedLayout>
        <div class="container-fluid py-4">
            <!-- Page Heading -->
            <div class="d-flex align-items-center justify-content-between mb-5">
                <div>
                    <h1 class="h3 mb-1 text-gray-800 fw-bold">Invoice Return</h1>
                    <p class="text-muted mb-0">Manage your sales and purchase returns efficiently.</p>
                </div>
            </div>

            <div class="row g-4">
                <!-- View Sale Return Card -->
                <div v-if="hasPermission" class="col-xl-3 col-md-6">
                    <Link :href="route('viewSaleReturn')" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm hover-card">
                            <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                                <div class="icon-circle bg-primary-light text-primary mb-3">
                                    <i class="fas fa-undo fa-2x"></i>
                                </div>
                                <h5 class="card-title font-weight-bold text-gray-800 mb-2">Sale Return</h5>
                                <p class="card-text text-muted small">View and manage returns from customers.</p>
                                <div class="mt-auto pt-3 text-primary font-weight-bold small">
                                    Access Module <i class="fas fa-arrow-right ml-1"></i>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>

                <!-- View Purchase Return Card -->
                <div v-if="hasPermission" class="col-xl-3 col-md-6">
                    <Link :href="route('viewPurchaseReturn')" class="text-decoration-none">
                        <div class="card h-100 border-0 shadow-sm hover-card">
                            <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                                <div class="icon-circle bg-success-light text-success mb-3">
                                    <i class="fas fa-history fa-2x"></i>
                                </div>
                                <h5 class="card-title font-weight-bold text-gray-800 mb-2">Purchase Return</h5>
                                <p class="card-text text-muted small">View and manage returns to vendors.</p>
                                <div class="mt-auto pt-3 text-success font-weight-bold small">
                                    Access Module <i class="fas fa-arrow-right ml-1"></i>
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.hover-card {
    transition: all 0.3s ease;
    cursor: pointer;
    background: #fff;
    border-radius: 1rem;
}

.hover-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
}

.icon-circle {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.bg-primary-light {
    background-color: rgba(78, 115, 223, 0.1);
}

.bg-success-light {
    background-color: rgba(28, 200, 138, 0.1);
}

.hover-card:hover .icon-circle {
    transform: scale(1.1);
}

.text-gray-800 {
    color: #5a5c69 !important;
}

.fw-bold {
    font-weight: 700 !important;
}
</style>
