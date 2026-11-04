<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from "vue";
import Button from '@/Components/Button.vue';

// State
const isFullscreen = ref(false);
const cart = ref([]);
const totals = ref({
    gross: 0,
    discount: 0,
    tax: 0,
    shipping: 0,
    received: 0,
    remaining: 0,
    change: 0,
    subtotal: 0,
    finalTotal: 0,
    totalItems: 0
});

const channel = ref(null);

// Format currency
const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'PKR', // Assuming LKR based on context or generic currency symbol
        minimumFractionDigits: 2
    }).format(value || 0);
};

// Listen for updates
onMounted(() => {
    channel.value = new BroadcastChannel('customer_display_channel');
    channel.value.onmessage = (event) => {
        if (event.data && event.data.type === 'UPDATE_CART') {
            cart.value = event.data.data.items || [];
            totals.value = event.data.data.totals || {};
        }
    };
});

onUnmounted(() => {
    if (channel.value) {
        channel.value.close();
    }
});

const hasItems = computed(() => cart.value.length > 0);

// Fullscreen toggle
const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
        isFullscreen.value = true;
    } else {
        if (document.exitFullscreen) {
            document.exitFullscreen();
            isFullscreen.value = false;
        }
    }
};

</script>

<template>
    <Head title="Customer Display" />
    
    <div class="min-h-screen bg-gray-50 flex flex-col font-sans">
        <!-- Header -->
        <header class="bg-white shadow-sm border-b border-gray-200 py-4 px-6 flex justify-between items-center z-10">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-indigo-600 rounded-lg flex items-center justify-center text-white font-bold text-xl shadow-md">
                    <i class="fas fa-store"></i>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800 tracking-tight">Store Name</h1>
                    <p class="text-xs text-gray-500">Customer Display</p>
                </div>
                <div>
                    <Button @click="toggleFullscreen" 
                            class="bg-white/20 border border-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm cursor-pointer transition-all duration-200 flex items-center gap-2 hover:bg-gray-50 hover:-translate-y-0.5 hover:shadow-md" 
                            title="Fullscreen">
                        <i :class="isFullscreen ? 'fas fa-compress' : 'fas fa-expand'"></i>
                    </Button>
                </div>
            </div>
            <div class="text-right">
                <p class="text-sm text-gray-500">{{ new Date().toLocaleDateString('en-GB') }}</p>
                <p class="text-xs text-gray-400">Welcome</p>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 flex overflow-hidden">
            <!-- Left Panel: Cart Items -->
            <div class="flex-1 overflow-y-auto p-6 transition-all duration-300">
                <div v-if="hasItems" class="space-y-4">
                    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                        <table class="w-full text-left">
                            <thead class="bg-gray-50 text-xs uppercase text-gray-500 font-semibold sticky top-0">
                                <tr>
                                    <th class="px-6 py-4">Item Description</th>
                                    <th class="px-6 py-4 text-center">Qty</th>
                                    <th class="px-6 py-4 text-right">Price</th>
                                    <th class="px-6 py-4 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in cart" :key="item.id" class="hover:bg-indigo-50/30 transition-colors duration-150">
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center text-gray-400">
                                                <i class="fas fa-box"></i>
                                            </div>
                                            <div>
                                                <p class="font-medium text-gray-900">{{ item.name }}</p>
                                                <!-- Optional: Show barcode or variant info if available -->
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center justify-center px-2.5 py-0.5 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                            {{ item.qty }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-right text-gray-600">
                                        {{ Number(item.price).toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-gray-900">
                                        {{ item.item_total.toFixed(2) }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="h-full flex flex-col items-center justify-center text-center p-12">
                     <div class="w-32 h-32 bg-indigo-50 rounded-full flex items-center justify-center mb-6 animate-pulse">
                        <i class="fas fa-shopping-basket text-4xl text-indigo-300"></i>
                    </div>
                    <h2 class="text-3xl font-bold text-gray-800 mb-2">Welcome!</h2>
                    <p class="text-gray-500 text-lg max-w-md">Ready to serve you. Please wait while the cashier adds items to your order.</p>
                </div>
            </div>

            <!-- Right Panel: Summary -->
            <aside class="w-96 bg-white shadow-xl flex flex-col z-20 border-l border-gray-200">
                <div class="flex-1 p-8 flex flex-col justify-start space-y-6 overflow-y-auto">
                    
                    <h3 class="text-lg font-bold text-gray-800 border-b border-gray-100 pb-2">Order Summary</h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center text-gray-600">
                            <span>Subtotal</span>
                            <span class="font-medium">{{ formatCurrency(totals.subtotal) }}</span>
                        </div>
                         <div class="flex justify-between items-center text-gray-600" v-if="totals.discount > 0">
                            <span>Discount</span>
                            <span class="text-red-500 font-medium">- {{ formatCurrency(totals.discount) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600" v-if="totals.tax > 0">
                            <span>Tax</span>
                            <span class="font-medium">+ {{ formatCurrency(totals.tax) }}</span>
                        </div>
                        <div class="flex justify-between items-center text-gray-600" v-if="totals.shipping > 0">
                            <span>Shipping</span>
                            <span class="font-medium">+ {{ formatCurrency(totals.shipping) }}</span>
                        </div>
                    </div>

                    <div class="my-6 border-t-2 border-dashed border-gray-200"></div>

                    <div class="flex justify-between items-end">
                        <span class="text-xl font-bold text-gray-800">Total</span>
                        <span class="text-4xl font-extrabold text-indigo-600 leading-none">
                            {{ formatCurrency(totals.finalTotal) }}
                        </span>
                    </div>

                     <!-- Payment Details (Only show if payment received) -->
                    <div v-if="totals.received > 0" class="mt-8 bg-green-50 rounded-xl p-4 border border-green-100 space-y-2 animate-fade-in">
                         <div class="flex justify-between items-center text-green-800">
                            <span class="font-medium">Paid Amount</span>
                            <span class="font-bold">{{ formatCurrency(totals.received) }}</span>
                        </div>
                        <div v-if="totals.change > 0" class="flex justify-between items-center text-green-700 text-lg border-t border-green-200 pt-2 mt-2">
                             <span class="font-bold">Change Due</span>
                            <span class="font-bold">{{ formatCurrency(totals.change) }}</span>
                        </div>
                         <div v-if="totals.remaining > 0" class="flex justify-between items-center text-red-600 text-lg border-t border-red-200 pt-2 mt-2">
                             <span class="font-bold">Remaining</span>
                            <span class="font-bold">{{ formatCurrency(totals.remaining) }}</span>
                        </div>
                    </div>

                </div>

                <!-- Footer Ad / Message -->
                <div class="bg-gray-900 text-white p-6 text-center">
                    <p class="text-sm text-gray-400 mb-1">Thank you for shopping with us!</p>
                    <p class="font-semibold text-gray-200">Have a great day</p>
                </div>
            </aside>
        </main>
    </div>
</template>

<style scoped>
@import url('https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css');

.animate-fade-in {
    animation: fadeIn 0.5s ease-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Custom Scrollbar */
::-webkit-scrollbar {
    width: 6px;
}
::-webkit-scrollbar-track {
    background: #f1f1f1; 
}
::-webkit-scrollbar-thumb {
    background: #c7c7c7; 
    border-radius: 3px;
}
::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8; 
}
</style>
