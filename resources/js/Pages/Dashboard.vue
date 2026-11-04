<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    stats: {
        type: Object,
        required: true,
    },
    charts: {
        type: Object,
        required: true,
    },
    recentInvoices: {
        type: Array,
        required: true,
    },
    recentPurchaseInvoices: {
        type: Array,
        required: true,
    },
    lowStockItems: {
        type: Array,
        required: true,
    },
});

// Format currency
const formatCurrency = (amount) => {
    return new Intl.NumberFormat('en-US', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(amount || 0);
};

// Format date
const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-GB');
};

// Get growth indicator
const getGrowthClass = (growth) => {
    return growth >= 0 ? 'text-green-600' : 'text-red-600';
};

const getGrowthIcon = (growth) => {
    return growth >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
};

// Quick actions
const quickActions = [
    { name: 'New Sale', icon: 'fa-cash-register', route: 'saleForm2', color: 'bg-blue-500', hoverColor: 'hover:bg-blue-600' },
    { name: 'Add Product', icon: 'fa-box', route: 'addProduct', color: 'bg-green-500', hoverColor: 'hover:bg-green-600' },
    { name: 'Add Customer', icon: 'fa-user-plus', route: 'createAccount', color: 'bg-purple-500', hoverColor: 'hover:bg-purple-600' },
    { name: 'Manufacturing', icon: 'fa-industry', route: 'manufacturingView', color: 'bg-orange-500', hoverColor: 'hover:bg-orange-600' },
    { name: 'View Stock', icon: 'fa-warehouse', route: 'viewStock', color: 'bg-teal-500', hoverColor: 'hover:bg-teal-600' },
    { name: 'New Purchase', icon: 'fa-chart-bar', route: 'purchaseForm2', color: 'bg-indigo-500', hoverColor: 'hover:bg-indigo-600' },
];

// Chart data for recent sales
const salesChartData = computed(() => {
    return props.charts.recentSales.map(item => ({
        date: formatDate(item.date),
        amount: parseFloat(item.total),
    }));
});

// Get max value for chart scaling
const maxSalesValue = computed(() => {
    const values = salesChartData.value.map(item => item.amount);
    return Math.max(...values, 1);
});
</script>

<style scoped>
.stat-card {
    transition: all 0.3s ease;
}

.stat-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

.action-card {
    transition: all 0.2s ease;
}

.action-card:hover {
    transform: translateY(-2px);
}

.action-card:active {
    transform: translateY(0);
}

.chart-bar {
    transition: all 0.3s ease;
}

.chart-bar:hover {
    opacity: 0.8;
}

.gradient-bg-1 {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.gradient-bg-2 {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
}

.gradient-bg-3 {
    background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
}

.gradient-bg-4 {
    background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
}

.gradient-bg-5 {
    background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
}

.gradient-bg-6 {
    background: linear-gradient(135deg, #30cfd0 0%, #330867 100%);
}
</style>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    <i class="fas fa-tachometer-alt mr-2"></i>
                    Dashboard
                </h2>
                <span class="text-sm text-gray-600">
                    <i class="far fa-calendar mr-1"></i>
                    {{ new Date().toLocaleDateString('en-GB', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' }) }}
                </span>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Quick Actions -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">
                        <i class="fas fa-bolt mr-2 text-yellow-500"></i>
                        Quick Actions
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                        <Link 
                            v-for="action in quickActions" 
                            :key="action.name"
                            :href="route(action.route)"
                            class="action-card p-4 rounded-lg shadow-md text-white text-center"
                            :class="[action.color, action.hoverColor]"
                        >
                            <i :class="`fas ${action.icon} text-3xl mb-2`"></i>
                            <div class="text-sm font-medium">{{ action.name }}</div>
                        </Link>
                    </div>
                </div>

                <!-- Sales Statistics -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">
                        <i class="fas fa-chart-line mr-2 text-blue-500"></i>
                        Sales Overview
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Today's Sales -->
                        <div class="stat-card bg-white rounded-lg shadow-md p-6 gradient-bg-1 text-white">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-medium opacity-90">Today's Sales</div>
                                <i class="fas fa-calendar-day text-2xl opacity-75"></i>
                            </div>
                            <div class="text-3xl font-bold">{{ formatCurrency(stats.sales.today) }}</div>
                        </div>

                        <!-- This Month -->
                        <div class="stat-card bg-white rounded-lg shadow-md p-6 gradient-bg-2 text-white">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-medium opacity-90">This Month</div>
                                <i class="fas fa-calendar-alt text-2xl opacity-75"></i>
                            </div>
                            <div class="text-3xl font-bold mb-1">{{ formatCurrency(stats.sales.month) }}</div>
                            <div class="text-xs flex items-center" :class="getGrowthClass(stats.sales.growth)">
                                <i :class="`fas ${getGrowthIcon(stats.sales.growth)} mr-1`"></i>
                                {{ Math.abs(stats.sales.growth) }}% vs last month
                            </div>
                        </div>

                        <!-- Total Sales -->
                        <div class="stat-card bg-white rounded-lg shadow-md p-6 gradient-bg-3 text-white">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-medium opacity-90">Total Sales</div>
                                <i class="fas fa-chart-line text-2xl opacity-75"></i>
                            </div>
                            <div class="text-3xl font-bold">{{ formatCurrency(stats.sales.total) }}</div>
                        </div>

                        <!-- Cash Flow -->
                        <div class="stat-card bg-white rounded-lg shadow-md p-6 gradient-bg-4 text-white">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-medium opacity-90">Net Cash Flow</div>
                                <i class="fas fa-money-bill-wave text-2xl opacity-75"></i>
                            </div>
                            <div class="text-3xl font-bold">
                                {{ formatCurrency(stats.cashFlow.receivables - stats.cashFlow.payables) }}
                            </div>
                            <div class="text-xs mt-1 opacity-90">
                                Receivables: {{ formatCurrency(stats.cashFlow.receivables) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Statistics -->
                <div class="mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-3">
                        <i class="fas fa-shopping-cart mr-2 text-orange-500"></i>
                        Purchase Overview
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <!-- Today's Purchases -->
                        <div class="stat-card bg-white rounded-lg shadow-md p-6 gradient-bg-5 text-white">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-medium opacity-90">Today's Purchases</div>
                                <i class="fas fa-shopping-bag text-2xl opacity-75"></i>
                            </div>
                            <div class="text-3xl font-bold">{{ formatCurrency(stats.purchases.today) }}</div>
                        </div>

                        <!-- This Month Purchases -->
                        <div class="stat-card bg-white rounded-lg shadow-md p-6 gradient-bg-6 text-white">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-medium opacity-90">This Month</div>
                                <i class="fas fa-calendar-check text-2xl opacity-75"></i>
                            </div>
                            <div class="text-3xl font-bold mb-1">{{ formatCurrency(stats.purchases.month) }}</div>
                            <div class="text-xs flex items-center" :class="getGrowthClass(stats.purchases.growth)">
                                <i :class="`fas ${getGrowthIcon(stats.purchases.growth)} mr-1`"></i>
                                {{ Math.abs(stats.purchases.growth) }}% vs last month
                            </div>
                        </div>

                        <!-- Total Purchases -->
                        <div class="stat-card bg-white rounded-lg shadow-md p-6 gradient-bg-1 text-white">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-medium opacity-90">Total Purchases</div>
                                <i class="fas fa-box-open text-2xl opacity-75"></i>
                            </div>
                            <div class="text-3xl font-bold">{{ formatCurrency(stats.purchases.total) }}</div>
                        </div>

                        <!-- Profit Margin -->
                        <div class="stat-card bg-white rounded-lg shadow-md p-6 gradient-bg-2 text-white">
                            <div class="flex items-center justify-between mb-2">
                                <div class="text-sm font-medium opacity-90">Gross Profit</div>
                                <i class="fas fa-percentage text-2xl opacity-75"></i>
                            </div>
                            <div class="text-3xl font-bold">
                                {{ formatCurrency(stats.sales.total - stats.purchases.total) }}
                            </div>
                            <div class="text-xs mt-1 opacity-90">
                                Sales - Purchases
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inventory & Business Stats -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
                    <!-- Products -->
                    <div class="stat-card bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-gray-700">
                                <i class="fas fa-boxes mr-2 text-green-500"></i>
                                Inventory
                            </h4>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Products</span>
                                <span class="text-lg font-bold text-gray-900">{{ stats.products.total }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Low Stock</span>
                                <span class="text-lg font-bold text-orange-600">{{ stats.products.lowStock }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Out of Stock</span>
                                <span class="text-lg font-bold text-red-600">{{ stats.products.outOfStock }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Customers -->
                    <div class="stat-card bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-gray-700">
                                <i class="fas fa-users mr-2 text-blue-500"></i>
                                Accounts
                            </h4>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Customers</span>
                                <span class="text-lg font-bold text-gray-900">{{ stats.customers.total }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Vendors</span>
                                <span class="text-lg font-bold text-gray-900">{{ stats.customers.vendors }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Accounts</span>
                                <span class="text-lg font-bold text-blue-600">{{ stats.customers.total + stats.customers.vendors }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Manufacturing -->
                    <div class="stat-card bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-gray-700">
                                <i class="fas fa-industry mr-2 text-purple-500"></i>
                                Manufacturing
                            </h4>
                        </div>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Products</span>
                                <span class="text-lg font-bold text-gray-900">{{ stats.manufacturing.total }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">This Month</span>
                                <span class="text-lg font-bold text-purple-600">{{ stats.manufacturing.thisMonth }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <Link :href="route('viewFinishedProducts')" class="text-sm text-purple-600 hover:text-purple-800">
                                    View All <i class="fas fa-arrow-right ml-1"></i>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts and Tables Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Sales Chart -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h4 class="font-semibold text-gray-700 mb-4">
                            <i class="fas fa-chart-bar mr-2 text-indigo-500"></i>
                            Last 7 Days Sales
                        </h4>
                        <div class="space-y-2">
                            <div 
                                v-for="(item, index) in salesChartData" 
                                :key="index"
                                class="flex items-center gap-3"
                            >
                                <div class="text-xs text-gray-600 w-20">{{ item.date }}</div>
                                <div class="flex-1 bg-gray-100 rounded-full h-8 relative overflow-hidden">
                                    <div 
                                        class="chart-bar h-full bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-end pr-3"
                                        :style="{ width: `${(item.amount / maxSalesValue) * 100}%` }"
                                    >
                                        <span class="text-xs font-semibold text-white">{{ formatCurrency(item.amount) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="salesChartData.length === 0" class="text-center py-8 text-gray-500">
                                <i class="fas fa-chart-bar text-4xl mb-2 opacity-30"></i>
                                <p>No sales data available</p>
                            </div>
                        </div>
                    </div>

                    <!-- Top Products -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h4 class="font-semibold text-gray-700 mb-4">
                            <i class="fas fa-star mr-2 text-yellow-500"></i>
                            Top Selling Products (This Month)
                        </h4>
                        <div class="space-y-3">
                            <div 
                                v-for="(product, index) in charts.topProducts" 
                                :key="index"
                                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-yellow-400 to-orange-500 flex items-center justify-center text-white font-bold text-sm">
                                        {{ index + 1 }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ product.name }}</div>
                                        <div class="text-xs text-gray-500">Qty: {{ product.total_qty }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-gray-900">{{ formatCurrency(product.total_sales) }}</div>
                                </div>
                            </div>
                            <div v-if="charts.topProducts.length === 0" class="text-center py-8 text-gray-500">
                                <i class="fas fa-box-open text-4xl mb-2 opacity-30"></i>
                                <p>No product data available</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Purchase Chart -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h4 class="font-semibold text-gray-700 mb-4">
                            <i class="fas fa-chart-area mr-2 text-orange-500"></i>
                            Last 7 Days Purchases
                        </h4>
                        <div class="space-y-2">
                            <div 
                                v-for="(item, index) in charts.recentPurchases" 
                                :key="index"
                                class="flex items-center gap-3"
                            >
                                <div class="text-xs text-gray-600 w-20">{{ formatDate(item.date) }}</div>
                                <div class="flex-1 bg-gray-100 rounded-full h-8 relative overflow-hidden">
                                    <div 
                                        class="chart-bar h-full bg-gradient-to-r from-orange-500 to-red-600 rounded-full flex items-center justify-end pr-3"
                                        :style="{ width: `${(item.total / Math.max(...charts.recentPurchases.map(i => i.total), 1)) * 100}%` }"
                                    >
                                        <span class="text-xs font-semibold text-white">{{ formatCurrency(item.total) }}</span>
                                    </div>
                                </div>
                            </div>
                            <div v-if="charts.recentPurchases.length === 0" class="text-center py-8 text-gray-500">
                                <i class="fas fa-shopping-cart text-4xl mb-2 opacity-30"></i>
                                <p>No purchase data available</p>
                            </div>
                        </div>
                    </div>

                    <!-- Top Purchased Products -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h4 class="font-semibold text-gray-700 mb-4">
                            <i class="fas fa-trophy mr-2 text-orange-500"></i>
                            Top Purchased Products (This Month)
                        </h4>
                        <div class="space-y-3">
                            <div 
                                v-for="(product, index) in charts.topPurchasedProducts" 
                                :key="index"
                                class="flex items-center justify-between p-3 bg-orange-50 rounded-lg hover:bg-orange-100 transition"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-red-500 flex items-center justify-center text-white font-bold text-sm">
                                        {{ index + 1 }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ product.name }}</div>
                                        <div class="text-xs text-gray-500">Qty: {{ product.total_qty }}</div>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <div class="font-semibold text-gray-900">{{ formatCurrency(product.total_purchases) }}</div>
                                </div>
                            </div>
                            <div v-if="charts.topPurchasedProducts.length === 0" class="text-center py-8 text-gray-500">
                                <i class="fas fa-box-open text-4xl mb-2 opacity-30"></i>
                                <p>No purchase data available</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activity and Alerts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Invoices -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-gray-700">
                                <i class="fas fa-receipt mr-2 text-green-500"></i>
                                Recent Invoices
                            </h4>
                            <Link :href="route('viewSInvoice')" class="text-sm text-blue-600 hover:text-blue-800">
                                View All <i class="fas fa-arrow-right ml-1"></i>
                            </Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Invoice #</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Customer</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Date</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="invoice in recentInvoices" :key="invoice.id" class="hover:bg-gray-50">
                                        <td class="px-3 py-2 font-medium text-gray-900">#{{ invoice.id }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ invoice.customer_name }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ formatDate(invoice.day) }}</td>
                                        <td class="px-3 py-2 text-right font-semibold text-gray-900">{{ formatCurrency(invoice.finalVal) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="recentInvoices.length === 0" class="text-center py-8 text-gray-500">
                                <i class="fas fa-receipt text-4xl mb-2 opacity-30"></i>
                                <p>No recent invoices</p>
                            </div>
                        </div>
                    </div>

                    <!-- Low Stock Alert -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-gray-700">
                                <i class="fas fa-exclamation-triangle mr-2 text-red-500"></i>
                                Low Stock Alert
                            </h4>
                            <Link :href="route('stockForm')" class="text-sm text-blue-600 hover:text-blue-800">
                                View All <i class="fas fa-arrow-right ml-1"></i>
                            </Link>
                        </div>
                        <div class="space-y-2">
                            <div 
                                v-for="item in lowStockItems" 
                                :key="item.id"
                                class="flex items-center justify-between p-3 bg-red-50 border border-red-100 rounded-lg"
                            >
                                <div>
                                    <div class="font-medium text-gray-900">{{ item.name }}</div>
                                    <div class="text-xs text-gray-500">{{ item.barcode }}</div>
                                </div>
                                <div class="text-right">
                                    <div class="text-sm font-semibold text-red-600">{{ item.qty }} / {{ item.min_qty }}</div>
                                    <div class="text-xs text-gray-500">Current / Min</div>
                                </div>
                            </div>
                            <div v-if="lowStockItems.length === 0" class="text-center py-8 text-gray-500">
                                <i class="fas fa-check-circle text-4xl mb-2 opacity-30 text-green-500"></i>
                                <p>All products are well stocked!</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Invoices and Additional Info -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mt-6">
                    <!-- Recent Purchase Invoices -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-gray-700">
                                <i class="fas fa-file-invoice mr-2 text-orange-500"></i>
                                Recent Purchase Invoices
                            </h4>
                            <Link :href="route('viewPInvoice')" class="text-sm text-blue-600 hover:text-blue-800">
                                View All <i class="fas fa-arrow-right ml-1"></i>
                            </Link>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-orange-50">
                                    <tr>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Invoice #</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Vendor</th>
                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Date</th>
                                        <th class="px-3 py-2 text-right text-xs font-semibold text-gray-600">Amount</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    <tr v-for="invoice in recentPurchaseInvoices" :key="invoice.id" class="hover:bg-orange-50">
                                        <td class="px-3 py-2 font-medium text-gray-900">#{{ invoice.id }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ invoice.vendor_name }}</td>
                                        <td class="px-3 py-2 text-gray-600">{{ formatDate(invoice.day) }}</td>
                                        <td class="px-3 py-2 text-right font-semibold text-gray-900">{{ formatCurrency(invoice.finalVal) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div v-if="recentPurchaseInvoices.length === 0" class="text-center py-8 text-gray-500">
                                <i class="fas fa-file-invoice text-4xl mb-2 opacity-30"></i>
                                <p>No recent purchase invoices</p>
                            </div>
                        </div>
                    </div>

                    <!-- Placeholder for future widget -->
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h4 class="font-semibold text-gray-700">
                                <i class="fas fa-chart-pie mr-2 text-indigo-500"></i>
                                Business Summary
                            </h4>
                        </div>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">Total Revenue</span>
                                <span class="text-lg font-bold text-blue-600">{{ formatCurrency(stats.sales.total) }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-orange-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">Total Expenses</span>
                                <span class="text-lg font-bold text-orange-600">{{ formatCurrency(stats.purchases.total) }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">Gross Profit</span>
                                <span class="text-lg font-bold text-green-600">{{ formatCurrency(stats.sales.total - stats.purchases.total) }}</span>
                            </div>
                            <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
                                <span class="text-sm font-medium text-gray-700">Profit Margin</span>
                                <span class="text-lg font-bold text-purple-600">
                                    {{ stats.sales.total > 0 ? ((stats.sales.total - stats.purchases.total) / stats.sales.total * 100).toFixed(1) : 0 }}%
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>
