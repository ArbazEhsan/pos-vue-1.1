<script setup>
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import Toast from '@/Components/Toast.vue';
import Modal2 from '@/Components/Modal2.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import BaseDataTable from '@/Components/baseDataTable.vue';
import Select from '@/Components/Select.vue';
import Select2 from '@/Components/Select2.vue';
import TextArea from '@/Components/TextArea.vue';
import Button from '@/Components/Button.vue';
import Pagination from '@/Components/Pagination.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    deliveryOrders: {
        type: Object,
    },
    riders: {
        type: Array,
    }
});

const page = usePage()
const permissions = page.props.auth.user.permission?.pages;
const user = page.props.auth.user.type;

const selectedOrder = ref(null);

const form = useForm({
    id: null,
    status: '',
    rider_id: '',
    notes: '',
    start_date: '',
    end_date: '',
    search: ''
});

onMounted(() => {
    console.log(props.riders);
});

const updateStatus = () => {
    form.id = selectedOrder.value.id;
    form.post(route('deliveryOrder.updateStatus'), {
        preserveScroll: true,
        onSuccess: () => {
            alert('Status updated successfully');
        },
        onError: () => {
            console.log(form.errors);
            alert('Status updated failed');
        }
    });
};

const getStatusColor = (status) => {
    switch (status) {
        case 'Preparing': return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'Transit': return 'bg-amber-100 text-amber-800 border-amber-200';
        case 'Delivered': return 'bg-emerald-100 text-emerald-800 border-emerald-200';
        case 'Canceled': return 'bg-rose-100 text-rose-800 border-rose-200';
        case 'Return': return 'bg-slate-100 text-slate-800 border-slate-200';
        default: return 'bg-indigo-100 text-indigo-800 border-indigo-200';
    }
};

const stats = computed(() => {
    const total = props.deliveryOrders.data.length;
    const preparing = props.deliveryOrders.data.filter(o => (o.delivery_order?.status === 'Preparing')).length;
    const transit = props.deliveryOrders.data.filter(o => o.delivery_order?.status === 'Transit').length;
    const delivered = props.deliveryOrders.data.filter(o => o.delivery_order?.status === 'Delivered').length;
    return { total, preparing, transit, delivered };
});

const viewOrder = (id) => {
    if (props.deliveryOrders.data) {
        const order = props.deliveryOrders.data.find(p => p.id == id);
        if (order) {
            const totalSold = order.sales_sum_qty || 0;
            const stockValue = (parseFloat(order.qty) * parseFloat(order.purchase)).toFixed(2);
            
            order.total_unit_sold = totalSold;
            order.current_stock_value = stockValue;

            form.id = order.id;
            form.status = order.delivery_order?.status || '';
            form.rider_id = order.delivery_order?.rider_id || '';
            form.notes = order.delivery_order?.notes || '';

            selectedOrder.value = order;
            $('#modal_order_detail').modal('show');
        }
    }
};

const prepOptions = ref([
    { id: 'Posted', name: 'Posted' },
    { id: 'Preparing', name: 'Preparing' },
    { id: 'Transit', name: 'Transit' },
    { id: 'Delivered', name: 'Delivered' },
    { id: 'Rejected', name: 'Rejected' },
    { id: 'Cancelled', name: 'Cancelled' },
    { id: 'Call Not Received', name: 'Call Not Received' },
    { id: 'Number Switch Off', name: 'Number Switch Off' },
    { id: 'Wrong Number', name: 'Wrong Number' },
    { id: 'Waiting For Rider', name: 'Waiting For Rider' },
    { id: 'Refunded', name: 'Refunded' },
    { id: 'Order Changed', name: 'Order Changed' },
    { id: 'Product Not Available', name: 'Product Not Available' },
]);

const formatDateTime = (dateString) => {
    if (!dateString) return '';

    const utcDateString = dateString.includes('Z') ? dateString : `${dateString.replace(' ', 'T')}Z`;
    
    const date = new Date(utcDateString);

    return date.toLocaleString('en-GB', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: 'numeric',
        minute: '2-digit',
        hour12: true
    });
};

const formatDuration = (totalMinutes) => {
    if (!totalMinutes || totalMinutes <= 0) return '0m';

    const days = Math.floor(totalMinutes / (24 * 60));
    const remainingMinutesAfterDays = totalMinutes % (24 * 60);
    const hours = Math.floor(remainingMinutesAfterDays / 60);
    const minutes = remainingMinutesAfterDays % 60;

    let parts = [];
    
    if (days > 0) parts.push(`${days}d`);
    if (hours > 0) parts.push(`${hours}h`);
    if (minutes > 0 || parts.length === 0) parts.push(`${minutes}m`);

    // Join with a space: "1d 2h 30m"
    return parts.join(' ');
};

const applyFilter = () => {
    form.get(route('deliveryOrder.view'), {
        preserveScroll: true,
        preserveState: true,
        replace: true,
        onSuccess: () => {
            // alert('Filter applied successfully');
        },
        onError: () => {
            console.log(form.errors);
            // alert('Filter applied failed');
        }
    });
};

const resetFilter = () => {
    form.start_date = '';
    form.end_date = '';
    form.search = '';
    applyFilter();
};

</script>

<template>
    <Head title="Delivery Management" />

    <AuthenticatedLayout>
        <div class="py-6 px-4 sm:px-6 lg:px-8">
            <!-- Header section -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
                <div>
                    <h1 class="text-3xl font-extrabold text-gray-900 tracking-tight">Delivery Command Center</h1>
                    <p class="mt-1 text-sm text-gray-500">Track and manage all outgoing delivery orders.</p>
                </div>
            </div>

            <div class="row pt-0">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="p-4">
                                <h5 class="font-semibold text-gray-700 mb-4">
                                    <i class="fas fa-chart-bar mr-2 text-gray-500"></i>
                                    Delivery Report
                                </h5>
                                <!-- Filters -->
                                <div class="row mb-4">
                                    <div class="col-md-3">
                                        <InputLabel for="start_date" value="Start Date"/>
                                        <TextInput id="start_date" type="date" v-model="form.start_date" class="w-full text-sm" />
                                    </div>
                                    <div class="col-md-3">
                                        <InputLabel for="end_date" value="End Date"/>
                                        <TextInput id="end_date" type="date" v-model="form.end_date" class="w-full text-sm" />
                                    </div>
                                    <div class="col-md-2">
                                        <InputLabel for="search" value="Search"/>
                                        <TextInput id="search" type="text" v-model="form.search" class="w-full text-sm" placeholder="Search by order ID" />
                                    </div>
                                    <div class="col-md-2 d-flex">
                                        <PrimaryButton class="mt-8" @click="applyFilter()">Filter</PrimaryButton>
                                        <Button class="btn btn-secondary mt-8 ml-2 text-sm" @click="resetFilter()">Reset</Button>
                                    </div>
                                </div>

                                <!-- Summary Cards -->
                                <div class="row mb-4">
                                    <div class="col-xl-3 col-md-6 mb-4">
                                        <div class="card border-left-primary shadow h-100 py-2">
                                            <div class="card-body">
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col mr-2">
                                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Orders</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ stats.total }}</div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-md-6 mb-4">
                                        <div class="card border-left-info shadow h-100 py-2">
                                            <div class="card-body">
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col mr-2">
                                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Preparing</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ stats.preparing }}</div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <i class="fas fa-utensils fa-2x text-gray-300"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-md-6 mb-4">
                                        <div class="card border-left-warning shadow h-100 py-2">
                                            <div class="card-body">
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col mr-2">
                                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total In Transit</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ stats.transit }}</div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-xl-3 col-md-6 mb-4">
                                        <div class="card border-left-success shadow h-100 py-2">
                                            <div class="card-body">
                                                <div class="row no-gutters align-items-center">
                                                    <div class="col mr-2">
                                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Delivered Today</div>
                                                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ stats.delivered }}</div>
                                                    </div>
                                                    <div class="col-auto">
                                                        <i class="fas fa-truck fa-2x text-gray-300"></i>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Report Table -->
                                <div class="row">
                                    <div class="col-12">
                                            <div class="card shadow mb-4">
                                            <div class="card-header py-3">
                                                <h6 class="m-0 font-weight-bold text-primary">Detailed Report</h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered">
                                                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                                            <tr>
                                                                <th>Order#</th>
                                                                <th>Customer</th>      
                                                                <th>Date</th>
                                                                <th>Status</th>
                                                                <th>Rider</th>
                                                                <th>Preparation</th>
                                                                <th>Transit</th>
                                                                <th>Delivered</th>
                                                                <th>Total</th>
                                                                <th>Action</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody class="text-sm">
                                                            <tr v-for="order in deliveryOrders.data" :key="order.id">
                                                                <td>{{ order.id }}</td>
                                                                <td>
                                                                    {{ order.account.name }}
                                                                    <br>
                                                                    {{ order.account.contact_person }}
                                                                    <br>
                                                                    {{ order.account.address }}
                                                                </td>
                                                                <td>{{ formatDateTime(order.created_at) }}</td>
                                                                <td>{{ order.delivery_order?.status ?? 'None' }}</td>
                                                                <td>
                                                                    {{ order.delivery_order?.rider.name ?? 'Unassigned' }}
                                                                    <br>
                                                                    {{ order.delivery_order?.rider.contact_person }}
                                                                </td>
                                                                <td>
                                                                    {{ order.delivery_order?.preparing_at ? formatDateTime(order.delivery_order?.preparing_at) : '--' }}
                                                                    <br>
                                                                    Due: {{ order.delivery_order?.preparing_due_at ? formatDuration(order.delivery_order?.preparing_due_at) : '--' }}
                                                                </td>
                                                                <td>{{ order.delivery_order?.transit_at ? formatDateTime(order.delivery_order?.transit_at) : '--' }}</td>
                                                                <td>
                                                                    {{ order.delivery_order?.delivered_at ? formatDateTime(order.delivery_order?.delivered_at) : '--' }}
                                                                    <br>
                                                                    Due: {{ order.delivery_order?.delivery_due_at ? formatDuration(order.delivery_order?.delivery_due_at) : '--' }}
                                                                </td>
                                                                <td>{{ order.finalVal }}</td>
                                                                <td>
                                                                    <Button class="btn btn-primary btn-sm" @click="viewOrder(order.id)">View</Button>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                        
                                                    <div class="d-flex justify-content-center mt-4" v-if="deliveryOrders.links">
                                                        <Pagination :pagination="deliveryOrders" v-if="deliveryOrders.data.length > 0"/>
                                                    </div>
                                                    <div v-if="deliveryOrders.data.length === 0" class="text-center py-4 text-gray-500">
                                                        No records found for the selected period.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Detail & Management Modal -->
        <Modal2 modal_id="modal_order_detail" title="Order Management" Class="modal-xl">
            <div v-if="selectedOrder" class="p-2">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Left Column: Order Details -->
                    <div class="lg:col-span-1 space-y-6">
                        <div class="bg-gray-50 rounded-xl p-5 border border-gray-100">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h4 class="text-lg font-bold text-gray-900">Order #{{ selectedOrder.id }}</h4>
                                    <p class="text-sm text-gray-500">Placed on {{ formatDateTime(selectedOrder.created_at) }}</p>
                                </div>
                                <span :class="getStatusColor(selectedOrder.delivery_order?.status || 'Preparing')" class="px-4 py-1.5 rounded-full text-sm font-bold border shadow-sm">
                                    {{ selectedOrder.delivery_order?.status || 'None' }}
                                </span>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mt-6 text-sm">
                                <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                    <span class="block text-gray-500 mb-1">Customer Details</span>
                                    <p class="font-bold text-gray-900">{{ selectedOrder.account?.name || 'Walk-in' }}</p>
                                    <p class="text-gray-600">{{ selectedOrder.account?.contact_person || 'No contact' }}</p>
                                    <p class="text-gray-600 mt-2">{{ selectedOrder.account?.address || 'No address provided' }}</p>
                                </div>
                                <div class="bg-white p-3 rounded-lg shadow-sm border border-gray-100">
                                    <span class="block text-gray-500 mb-1">Payment Information</span>
                                    <p class="text-gray-900">Total Amount: <span class="font-bold">{{ selectedOrder.finalVal }}</span></p>
                                    <p class="text-gray-600">Remaining: {{ selectedOrder.remaining }}</p>
                                    <!-- <p class="mt-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800" v-if="selectedOrder.remaining <= 0">
                                        <i class="fas fa-check-circle mr-1"></i> Paid
                                    </p>
                                    <p class="mt-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-800" v-else>
                                        <i class="fas fa-exclamation-circle mr-1"></i> Unpaid
                                    </p> -->
                                </div>
                            </div>

                            <div class="mt-8">
                                <h5 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-4">Order Items</h5>
                                <div class="bg-white rounded-lg overflow-hidden border border-gray-100 shadow-sm">
                                    <table class="min-w-full divide-y divide-gray-100 text-sm">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-4 py-2 text-left text-gray-500">Item (SKU)</th>
                                                <th class="px-4 py-2 text-center text-gray-500">Qty</th>
                                                <th class="px-4 py-2 text-right text-gray-500">Price</th>
                                                <th class="px-4 py-2 text-right text-gray-500">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-50">
                                            <tr v-for="item in selectedOrder.sales" :key="item.id">
                                                <td class="px-4 py-2 text-gray-900">{{ item.product.name + '(' + item.barcode + ')' }}</td>
                                                <td class="px-4 py-2 text-center text-gray-600">{{ item.qty }}</td>
                                                <td class="px-4 py-2 text-right text-gray-600">{{ item.price }}</td>
                                                <td class="px-4 py-2 text-right font-semibold text-gray-900">{{ item.final }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Progress Timeline -->
                        <div class="bg-white rounded-xl p-5 border border-gray-100 shadow-sm">
                            <h4 class="text-md font-bold text-gray-900 mb-6">Delivery Journey</h4>
                            <div class="relative">
                                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                                <div class="space-y-8 relative">
                                    <!-- Preparing -->
                                    <div class="flex items-start">
                                        <div :class="selectedOrder.delivery_order?.preparing_at ? 'bg-blue-500 shadow-blue-200 shadow-lg' : 'bg-gray-200'" class="z-10 w-9 h-9 rounded-full flex items-center justify-center text-white transition-all duration-300">
                                            <i class="fas fa-utensils text-xs"></i>
                                        </div>
                                        <div class="ml-4 pt-1">
                                            <p class="text-sm font-bold text-gray-900">Order Preparing</p>
                                            <p class="text-xs text-gray-500">{{ selectedOrder.delivery_order?.preparing_at ? formatDateTime(selectedOrder.delivery_order?.preparing_at) : 'None' }}</p>
                                        </div>
                                    </div>
                                    <!-- Transit -->
                                    <div class="flex items-start">
                                        <div :class="selectedOrder.delivery_order?.transit_at ? 'bg-amber-500 shadow-amber-200 shadow-lg' : 'bg-gray-200'" class="z-10 w-9 h-9 rounded-full flex items-center justify-center text-white transition-all duration-300">
                                            <i class="fas fa-motorcycle text-xs"></i>
                                        </div>
                                        <div class="ml-4 pt-1">
                                            <p class="text-sm font-bold text-gray-900">In Transit</p>
                                            <p class="text-xs text-gray-500">{{ selectedOrder.delivery_order?.transit_at ? formatDateTime(selectedOrder.delivery_order?.transit_at) : 'None' }}</p>
                                        </div>
                                    </div>
                                    <!-- Delivered -->
                                    <div class="flex items-start">
                                        <div :class="selectedOrder.delivery_order?.delivered_at ? 'bg-emerald-500 shadow-emerald-200 shadow-lg' : 'bg-gray-200'" class="z-10 w-9 h-9 rounded-full flex items-center justify-center text-white transition-all duration-300">
                                            <i class="fas fa-home text-xs"></i>
                                        </div>
                                        <div class="ml-4 pt-1">
                                            <p class="text-sm font-bold text-gray-900">Delivered</p>
                                            <p class="text-xs text-gray-500">{{ selectedOrder.delivery_order?.delivered_at ? formatDateTime(selectedOrder.delivery_order?.delivered_at) : 'None' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Controls -->
                    <div class="space-y-6">
                        <!-- Rider Assignment -->
                        <div class="bg-indigo-50 rounded-xl p-5 border border-indigo-100">
                            <h4 class="text-sm font-bold text-indigo-900 uppercase tracking-wider mb-4">Assign Rider</h4>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-semibold text-indigo-700 mb-1">Available Delivery Personnel</label>
                                    <Select2 
                                        v-model="form.rider_id" 
                                        :options="riders"
                                        class="w-full"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-indigo-700 mb-1">Execution Steps</label>
                                    <Select
                                        v-model="form.status"
                                        :options="prepOptions"
                                    />
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-indigo-700 mb-1">Delivery Notes</label>
                                    <TextArea 
                                        id="notes"
                                        v-model="form.notes" 
                                        placeholder="Special instructions for rider..."
                                    />
                                </div>
                                <PrimaryButton @click="updateStatus" :disabled="form.processing" class="w-full justify-center py-2.5 shadow-sm mt-4">
                                    <i class="fas fa-user-check mr-2"></i> Update Status
                                </PrimaryButton>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </Modal2>

        <Toast v-if="$page.props.flash.success" :msg="$page.props.flash.success" />
        <Toast v-if="$page.props.flash.error" :msg="$page.props.flash.error" type="error" />

    </AuthenticatedLayout>
</template>

<style scoped>
/* Glassmorphism utility if needed */
.glass {
    background: rgba(255, 255, 255, 0.7);
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
}
</style>
