<script setup>
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ImageUpload from '@/Components/ImageUpload.vue';
import TextArea from '@/Components/TextArea.vue';
import Tooltip from '@/Components/Tooltip.vue';
import CheckboxWithBox from '@/Components/CheckboxWithBox.vue';
import Select2 from '@/Components/Select2.vue';
import TextInputWithIcon from '@/Components/TextInputWithIcon.vue';

const props = defineProps({
    settings: Object,
});

const activeTab = ref('business');

const form = useForm({
    // Business Tab
    business_name: props.settings?.business_name || '',
    start_date: props.settings?.start_date || '',
    default_profit_percent: props.settings?.default_profit_percent || 0,
    currency: props.settings?.currency || 'USD',
    currency_symbol_placement: props.settings?.currency_symbol_placement || 'before',
    time_zone: props.settings?.time_zone || 'UTC',
    financial_year_start_month: props.settings?.financial_year_start_month || 1,
    stock_accounting_method: props.settings?.stock_accounting_method || 'fifo',
    transaction_edit_days: props.settings?.transaction_edit_days || 30,
    date_format: props.settings?.date_format || 'Y-m-d',
    time_format: props.settings?.time_format || '24',
    currency_precision: props.settings?.currency_precision || 2,
    quantity_precision: props.settings?.quantity_precision || 2,
    
    logo: props.settings?.logo_path 
        ? (props.settings.logo_path.startsWith('http') || props.settings.logo_path.startsWith('/') 
            ? props.settings.logo_path 
            : `/storage/${props.settings.logo_path.replace(/^storage\//, '')}`) 
        : null,

    address: props.settings?.address || '',
    phone: props.settings?.phone || '',
    email: props.settings?.email || '',
    website: props.settings?.website || '',
    invoice_footer_text: props.settings?.invoice_footer_text || '',

    // Tax Tab
    tax_identification_number: props.settings?.tax_identification_number || '',
    tax_1_name: props.settings?.tax_1_name || '',
    tax_1_no: props.settings?.tax_1_no || '',
    tax_2_name: props.settings?.tax_2_name || '',
    tax_2_no: props.settings?.tax_2_no || '',
    inline_tax_sale_purchase: Boolean(props.settings?.inline_tax_sale_purchase),

    // Products Tab
    sku_prefix: props.settings?.sku_prefix || '',
    enable_product_expiry: Boolean(props.settings?.enable_product_expiry), 
    product_expiry_type: props.settings?.product_expiry_type || 'add_item_expiry', // Renamed from ambiguous usage
    on_product_expiry: props.settings?.on_product_expiry || 'keep_selling', // Renamed from ambiguous usage
    enable_category: Boolean(props.settings?.enable_category),
    default_unit: props.settings?.default_unit || '',
    enable_brands: Boolean(props.settings?.enable_brands),
    enable_sub_category: Boolean(props.settings?.enable_sub_category),
    enable_price_tax_info: Boolean(props.settings?.enable_price_tax_info),
    enable_sub_units: Boolean(props.settings?.enable_sub_units),
    enable_racks: Boolean(props.settings?.enable_racks),
    enable_row: Boolean(props.settings?.enable_row),
    enable_position: Boolean(props.settings?.enable_position),
    enable_warranty: Boolean(props.settings?.enable_warranty),
    enable_product_image: Boolean(props.settings?.enable_product_image),

    // Contact Tab
    default_credit_limit: props.settings?.default_credit_limit || 0,

    // Sale Tab
    default_sale_discount: props.settings?.default_sale_discount || 0,
    default_sale_tax: props.settings?.default_sale_tax || '',
    sales_item_addition_method: props.settings?.sales_item_addition_method || 'Add Item In New Row',
    amount_rounding_method: props.settings?.amount_rounding_method || 'None',
    sales_price_is_minimum_selling_price: Boolean(props.settings?.sales_price_is_minimum_selling_price),
    allow_overselling: Boolean(props.settings?.allow_overselling),
    enable_sales_order: Boolean(props.settings?.enable_sales_order),
    is_pay_term_required: Boolean(props.settings?.is_pay_term_required),
    commission_agent: props.settings?.commission_agent || 'Disable',
    commission_calculation_type: props.settings?.commission_calculation_type || 'Invoice Value',
    is_commission_agent_required: Boolean(props.settings?.is_commission_agent_required),
    public_key: props.settings?.public_key || '',
    secret_key: props.settings?.secret_key || '',
    is_payment_link_enabled: Boolean(props.settings?.is_payment_link_enabled),

    // POS Tab
    express_checkout: props.settings?.express_checkout || '',
    pay_and_checkout: props.settings?.pay_and_checkout || '',
    draft: props.settings?.draft || '',
    cancelVal: props.settings?.cancelVal || '',
    go_to_product_quantity: props.settings?.go_to_product_quantity || '',
    weighing_scale: props.settings?.weighing_scale || '',
    edit_discount: props.settings?.edit_discount || '',
    edit_order_tax: props.settings?.edit_order_tax || '',
    add_payment_row: props.settings?.add_payment_row || '',
    finalize_payment: props.settings?.finalize_payment || '',
    add_new_product: props.settings?.add_new_product || '',
    
    disable_multiple_pay: Boolean(props.settings?.disable_multiple_pay),
    disable_draft: Boolean(props.settings?.disable_draft),
    disable_express_checkout: Boolean(props.settings?.disable_express_checkout),
    disable_product_suggestion: Boolean(props.settings?.disable_product_suggestion),
    disable_recent_transactions: Boolean(props.settings?.disable_recent_transactions),
    disable_discount: Boolean(props.settings?.disable_discount),
    disable_order_tax: Boolean(props.settings?.disable_order_tax),
    subtotal_editable: Boolean(props.settings?.subtotal_editable),
    disable_suspend_sale: Boolean(props.settings?.disable_suspend_sale),
    enable_transaction_date: Boolean(props.settings?.enable_transaction_date),
    enable_service_staff: Boolean(props.settings?.enable_service_staff),
    is_service_staff_required: Boolean(props.settings?.is_service_staff_required),
    disable_credit_sale_button: Boolean(props.settings?.disable_credit_sale_button),
    enable_weighing_scale: Boolean(props.settings?.enable_weighing_scale),
    show_invoice_scheme: Boolean(props.settings?.show_invoice_scheme),
    show_invoice_layout_dropdown: Boolean(props.settings?.show_invoice_layout_dropdown),
    print_invoice_on_suspend: Boolean(props.settings?.print_invoice_on_suspend),
    show_pricing_on_product_suggestion_tooltip: Boolean(props.settings?.show_pricing_on_product_suggestion_tooltip),
    
    // Weighing Scale
    weighing_scale_prefix: props.settings?.weighing_scale_prefix || '', 
    product_sku_length: props.settings?.product_sku_length || '4',
    quantity_integer_part_length: props.settings?.quantity_integer_part_length || '2',
    quantity_fractional_part_length: props.settings?.quantity_fractional_part_length || '2',

    // Purchases Tab
    enable_editing_product_price_from_purchase_screen: Boolean(props.settings?.enable_editing_product_price_from_purchase_screen),
    enable_purchase_status: Boolean(props.settings?.enable_purchase_status),
    enable_lot_number: Boolean(props.settings?.enable_lot_number),
    enable_purchase_order: Boolean(props.settings?.enable_purchase_order),
    enable_purchase_requisition: Boolean(props.settings?.enable_purchase_requisition),

    // Payment Tab
    cash_denominations: props.settings?.cash_denominations || '',
    enable_cash_denomination_on: props.settings?.enable_cash_denomination_on || 'POS Screen',
    enable_cash_denomination_for_payment_methods: props.settings?.enable_cash_denomination_for_payment_methods || '',
    strict_check: Boolean(props.settings?.strict_check),

    // Prefixes Tab
    prefix_purchase: props.settings?.prefix_purchase || '',
    prefix_purchase_return: props.settings?.prefix_purchase_return || '',
    prefix_purchase_requisition: props.settings?.prefix_purchase_requisition || '',
    prefix_purchase_order: props.settings?.prefix_purchase_order || '',
    prefix_stock_transfer: props.settings?.prefix_stock_transfer || '',
    prefix_stock_adjustment: props.settings?.prefix_stock_adjustment || '',
    prefix_sell_return: props.settings?.prefix_sell_return || '',
    prefix_expenses: props.settings?.prefix_expenses || '',
    prefix_contacts: props.settings?.prefix_contacts || '',
    prefix_purchase_payment: props.settings?.prefix_purchase_payment || '',
    prefix_sell_payment: props.settings?.prefix_sell_payment || '',
    prefix_expense_payment: props.settings?.prefix_expense_payment || '',
    prefix_business_location: props.settings?.prefix_business_location || '',
    prefix_username: props.settings?.prefix_username || '',
    prefix_subscription_no: props.settings?.prefix_subscription_no || '',
    prefix_draft: props.settings?.prefix_draft || '',
    prefix_sales_order: props.settings?.prefix_sales_order || '',

    // Email Settings
    mail_driver: props.settings?.mail_driver || 'smtp',
    mail_host: props.settings?.mail_host || '',
    mail_port: props.settings?.mail_port || '',
    mail_username: props.settings?.mail_username || '',
    mail_password: props.settings?.mail_password || '',
    mail_encryption: props.settings?.mail_encryption || 'tls',
    mail_from_address: props.settings?.mail_from_address || '',
    mail_from_name: props.settings?.mail_from_name || '',
});

const submit = () => {
    form.post(route('business.settings.update'), {
        preserveScroll: true,
        onSuccess: () => {
            // Success handler
        },
    });
};

const sendTestEmail = () => {
    router.post(route('business.settings.test-email'), {
        mail_driver: form.mail_driver,
        mail_host: form.mail_host,
        mail_port: form.mail_port,
        mail_username: form.mail_username,
        mail_password: form.mail_password,
        mail_encryption: form.mail_encryption,
        mail_from_address: form.mail_from_address,
        mail_from_name: form.mail_from_name,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success
        },
    });
};

const tabs = [
    { id: 'business', label: 'Business', icon: 'fas fa-briefcase' },
    { id: 'tax', label: 'Tax', icon: 'fas fa-percentage' },
    { id: 'products', label: 'Products', icon: 'fas fa-box-open' },
    { id: 'contact', label: 'Contact', icon: 'fas fa-address-book' },
    { id: 'sale', label: 'Sale', icon: 'fas fa-shopping-cart' },
    { id: 'pos', label: 'POS', icon: 'fas fa-cash-register' },
    // { id: 'displayScreen', label: 'Display Screen', icon: 'fas fa-desktop' },
    { id: 'purchases', label: 'Purchases', icon: 'fas fa-shopping-bag' },
    { id: 'payment', label: 'Payment', icon: 'fas fa-money-bill-wave' },
    // { id: 'dashboard', label: 'Dashboard', icon: 'fas fa-tachometer-alt' },
    // { id: 'system', label: 'System', icon: 'fas fa-cogs' },
    { id: 'prefixes', label: 'Prefixes', icon: 'fas fa-font' },
    { id: 'emailSettings', label: 'Email Settings', icon: 'fas fa-envelope' },
    // { id: 'smsSettings', label: 'SMS Settings', icon: 'fas fa-sms' },
    // { id: 'rewardPointSettings', label: 'Reward Point Settings', icon: 'fas fa-star' },
    // { id: 'customLabels', label: 'Custom Labels', icon: 'fas fa-tags' },
];

</script>

<template>
    <Head title="Business Settings" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Business Settings</h1>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="card shadow rounded-lg overflow-visible">
                    <div class="card-body p-0 overflow-visible">
                        <div class="flex flex-col md:flex-row">
                            <!-- Sidebar Tabs -->
                            <div class="md:w-64 bg-gray-50 border-r border-gray-200 rounded-l-lg">
                                <ul class="flex flex-col p-4 space-y-2">
                                    <li v-for="tab in tabs" :key="tab.id">
                                        <button 
                                            @click="activeTab = tab.id"
                                            :class="[
                                                'w-full text-left px-4 py-3 rounded-lg font-medium transition-colors duration-200 flex items-center space-x-3',
                                                activeTab === tab.id 
                                                    ? 'bg-white text-indigo-600 shadow-sm border border-gray-200' 
                                                    : 'text-gray-600 hover:bg-gray-100'
                                            ]"
                                        >
                                            <i :class="tab.icon" class="w-5 text-center"></i>
                                            <span>{{ tab.label }}</span>
                                        </button>
                                    </li>
                                </ul>
                            </div>

                            <!-- Content Area -->
                            <div class="flex-1 p-6 md:p-8">
                                <form @submit.prevent="submit" class="space-y-6">
                                    
                                    <!-- Business Tab -->
                                    <div v-show="activeTab === 'business'" class="space-y-8 animate-fade-in">
                                        
                                        <!-- General Info -->
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">General Information</h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                                <div class="md:col-span-2 flex flex-col items-center sm:flex-row gap-6">
                                                    <div>
                                                        <InputLabel value="Business Logo" class="mb-2" />
                                                        <ImageUpload v-model="form.logo" />
                                                        <InputError :message="form.errors.logo" class="mt-2" />
                                                    </div>
                                                    <div class="flex-1 w-full">
                                                        <InputLabel for="business_name" value="Business Name*" />
                                                        <TextInput id="business_name" v-model="form.business_name" type="text" class="mt-1 block w-full" placeholder="Enter business name" required/>
                                                        <InputError :message="form.errors.business_name" class="mt-2" />
                                                    </div>
                                                </div>

                                                <div>
                                                    <InputLabel for="start_date" value="Start Date" />
                                                    <TextInput id="start_date" v-model="form.start_date" type="date" class="mt-1 block w-full" />
                                                    <InputError :message="form.errors.start_date" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="default_profit_percent">
                                                        Default Profit (%)*
                                                        <Tooltip>
                                                            Default profit margin of a product.<br/>
                                                            <span class="text-gray-300">Used to calculate selling price based on purchase price.<br/>You can modify this for individual products.</span>
                                                        </Tooltip>
                                                    </InputLabel>
                                                    <TextInput id="default_profit_percent" v-model="form.default_profit_percent" type="number" step="0.01" class="mt-1 block w-full" required/>
                                                    <InputError :message="form.errors.default_profit_percent" class="mt-2" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Financial Settings -->
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Financial Settings</h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                                <div>
                                                    <InputLabel for="currency" value="Currency Code" />
                                                    <TextInput id="currency" v-model="form.currency" type="text" class="mt-1 block w-full" placeholder="USD" />
                                                    <InputError :message="form.errors.currency" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="currency_symbol_placement" value="Symbol Placement" />
                                                    <select id="currency_symbol_placement" v-model="form.currency_symbol_placement" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option value="before">Before Amount ($10)</option>
                                                        <option value="after">After Amount (10$)</option>
                                                    </select>
                                                    <InputError :message="form.errors.currency_symbol_placement" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="financial_year_start_month">
                                                        Fiscal Year Start
                                                        <Tooltip text="Starting month of the Financial Year for your business" />
                                                    </InputLabel>
                                                    <select id="financial_year_start_month" v-model="form.financial_year_start_month" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option v-for="i in 12" :key="i" :value="i">{{ new Date(0, i-1).toLocaleString('default', { month: 'long' }) }}</option>
                                                    </select>
                                                    <InputError :message="form.errors.financial_year_start_month" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="stock_accounting_method">
                                                        Stock Accounting Method*
                                                        <Tooltip text="Method used for stock accounting" />
                                                    </InputLabel>
                                                    <select id="stock_accounting_method" v-model="form.stock_accounting_method" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option value="fifo">FIFO (First In First Out)</option>
                                                        <option value="lifo">LIFO (Last In First Out)</option>
                                                    </select>
                                                    <InputError :message="form.errors.stock_accounting_method" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="currency_precision">
                                                        Currency Precision*
                                                        <Tooltip>
                                                            Number of digits after decimal point for currency value.<br/>
                                                            Example: 2 for 0.00, 3 for 0.000
                                                        </Tooltip>
                                                    </InputLabel>
                                                    <TextInput id="currency_precision" v-model="form.currency_precision" type="number" min="0" max="4" class="mt-1 block w-full" />
                                                    <InputError :message="form.errors.currency_precision" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="quantity_precision">
                                                        Quantity Precision*
                                                        <Tooltip>
                                                            Number of digits after decimal point for quantity value.<br/>
                                                            Example: 2 for 0.00, 3 for 0.000
                                                        </Tooltip>
                                                    </InputLabel>
                                                    <TextInput id="quantity_precision" v-model="form.quantity_precision" type="number" min="0" max="4" class="mt-1 block w-full" />
                                                    <InputError :message="form.errors.quantity_precision" class="mt-2" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- System Settings -->
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">System Settings</h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                                <div>
                                                    <InputLabel for="time_zone" value="Time Zone" />
                                                    <select id="time_zone" v-model="form.time_zone" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option value="UTC">UTC</option>
                                                        <option value="Asia/Karachi">Asia/Karachi</option>
                                                        <option value="America/New_York">America/New_York</option>
                                                        <option value="Europe/London">Europe/London</option>
                                                    </select>
                                                    <InputError :message="form.errors.time_zone" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="transaction_edit_days">
                                                        Transaction Edit Days*
                                                        <Tooltip text="Number of days from Transaction Date during which a transaction can be edited" />
                                                    </InputLabel>
                                                    <TextInput id="transaction_edit_days" v-model="form.transaction_edit_days" type="number" min="0" class="mt-1 block w-full" />
                                                    <InputError :message="form.errors.transaction_edit_days" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="date_format" value="Date Format*" />
                                                    <select id="date_format" v-model="form.date_format" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option value="d-m-Y">dd-mm-yyyy</option>
                                                        <option value="m-d-Y">mm-dd-yyyy</option>
                                                        <option value="Y-m-d">yyyy-mm-dd</option>
                                                        <option value="d/m/Y">dd/mm/yyyy</option>
                                                        <option value="m/d/Y">mm/dd/yyyy</option>
                                                        <option value="Y/m/d">yyyy/mm/dd</option>
                                                    </select>
                                                    <InputError :message="form.errors.date_format" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="time_format" value="Time Format" />
                                                    <select id="time_format" v-model="form.time_format" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option value="12">12 Hour</option>
                                                        <option value="24">24 Hour</option>
                                                    </select>
                                                    <InputError :message="form.errors.time_format" class="mt-2" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Contact Info -->
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Contact Information</h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                                <div>
                                                    <InputLabel for="email" value="Email" />
                                                    <TextInput id="email" v-model="form.email" type="email" class="mt-1 block w-full" />
                                                    <InputError :message="form.errors.email" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="phone" value="Phone" />
                                                    <TextInput id="phone" v-model="form.phone" type="text" class="mt-1 block w-full" />
                                                    <InputError :message="form.errors.phone" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="website" value="Website" />
                                                    <TextInput id="website" v-model="form.website" type="text" class="mt-1 block w-full" />
                                                    <InputError :message="form.errors.website" class="mt-2" />
                                                </div>

                                                <div class="md:col-span-2">
                                                    <InputLabel for="address" value="Address" />
                                                    <TextArea id="address" v-model="form.address" rows="3" class="mt-1 block w-full" />
                                                    <InputError :message="form.errors.address" class="mt-2" />
                                                </div>

                                                <div class="md:col-span-2">
                                                    <InputLabel for="invoice_footer_text" value="Invoice Footer Text" />
                                                    <TextArea id="invoice_footer_text" v-model="form.invoice_footer_text" rows="2" class="mt-1 block w-full" />
                                                    <InputError :message="form.errors.invoice_footer_text" class="mt-2" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tax Tab -->
                                    <div v-show="activeTab === 'tax'" class="space-y-8 animate-fade-in">
                                        <div class="bg-blue-50 border-l-4 border-blue-400 p-4">
                                            <div class="flex">
                                                <div class="flex-shrink-0">
                                                    <i class="fas fa-info-circle text-blue-400"></i>
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm text-blue-700">
                                                        Registered tax number for your business.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                                            <!-- Tax 1 -->
                                            <div class="border rounded-lg p-6 bg-white shadow-sm">
                                                <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                                                    <span class="bg-indigo-100 text-indigo-700 rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">1</span>
                                                    Tax 1 Configuration
                                                </h4>
                                                <div class="space-y-4">
                                                    <div>
                                                        <InputLabel for="tax_1_name" value="Tax Name" />
                                                        <TextInput id="tax_1_name" v-model="form.tax_1_name" type="text" class="mt-1 block w-full" placeholder="e.g. VAT" />
                                                    </div>
                                                    <div>
                                                        <InputLabel for="tax_1_no" value="Tax Number / ID" />
                                                        <TextInput id="tax_1_no" v-model="form.tax_1_no" type="text" class="mt-1 block w-full" placeholder="e.g. VAT-12345" />
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tax 2 -->
                                            <div class="border rounded-lg p-6 bg-white shadow-sm">
                                                <h4 class="font-semibold text-gray-900 mb-4 flex items-center">
                                                    <span class="bg-purple-100 text-purple-700 rounded-full w-6 h-6 flex items-center justify-center text-xs mr-2">2</span>
                                                    Tax 2 Configuration
                                                </h4>
                                                <div class="space-y-4">
                                                    <div>
                                                        <InputLabel for="tax_2_name" value="Tax Name" />
                                                        <TextInput id="tax_2_name" v-model="form.tax_2_name" type="text" class="mt-1 block w-full" placeholder="e.g. GST" />
                                                    </div>
                                                    <div>
                                                        <InputLabel for="tax_2_no" value="Tax Number / ID" />
                                                        <TextInput id="tax_2_no" v-model="form.tax_2_no" type="text" class="mt-1 block w-full" placeholder="e.g. GST-67890" />
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="md:col-span-2">
                                                <InputLabel for="tax_identification_number" value="General Tax Identification Number (TIN)" />
                                                <TextInput id="tax_identification_number" v-model="form.tax_identification_number" type="text" class="mt-1 block w-full" />
                                                <InputError :message="form.errors.tax_identification_number" class="mt-2" />
                                            </div>
                                            <div class="md:col-span-2">
                                                <CheckboxWithBox id="inline_tax_sale_purchase" v-model="form.inline_tax_sale_purchase" label="Enable Inline Tax in Purchase and Sell" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Products Tab -->
                                    <div v-show="activeTab === 'products'" class="space-y-8 animate-fade-in">
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                            <div class="md:col-span-2">
                                                <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Product Defaults</h3>
                                            </div>

                                            <div>
                                                <InputLabel for="sku_prefix" value="SKU Prefix" />
                                                <div class="relative mt-1">
                                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                        <span class="text-gray-500 sm:text-sm"><i class="fas fa-barcode"></i></span>
                                                    </div>
                                                    <TextInput id="sku_prefix" v-model="form.sku_prefix" type="text" class="block w-full pl-10" placeholder="e.g. SKU-" />
                                                </div>
                                                <!-- <TextInputWithIcon icon="fas fa-barcode" v-model="form.sku_prefix" type="text" class="mt-1 block w-full pl-10" placeholder="e.g. SKU-" /> -->
                                                <p class="mt-1 text-xs text-gray-500">Prefix for auto-generated product SKUs.</p>
                                                <InputError :message="form.errors.sku_prefix" class="mt-2" />
                                            </div>

                                            <div>
                                                <InputLabel for="default_unit" value="Default Unit" />
                                                <!-- <TextInput id="default_unit" v-model="form.default_unit" type="text" class="mt-1 block w-full" placeholder="e.g. Piece" /> -->
                                                <select id="default_unit" v-model="form.default_unit" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                    <option disabled selected>-- Select --</option>
                                                    <option value="pieces">Pieces (Pc(s))</option>
                                                    <option value="packets">Packets (packs)</option>
                                                    <option value="grams">Grams (g)</option>
                                                    <option value="kilograms">Kilograms (kg)</option>
                                                </select>
                                                <InputError :message="form.errors.default_unit" class="mt-2" />
                                            </div>
                                            <div>
                                                <InputLabel for="product_expiry_type" value="Product Expiry Type" />
                                                <select id="product_expiry_type" v-model="form.product_expiry_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                    <option value="add_item_expiry">Add item expiry</option>
                                                    <option value="add_manufacturing_date_expiry_period">Add Manufacturing date & expiry period</option>
                                                </select>
                                            </div>
                                            <div>
                                                <InputLabel for="on_product_expiry" value="On Product Expiry" />
                                                <select id="on_product_expiry" v-model="form.on_product_expiry" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                    <option value="keep_selling">Keep Selling</option>
                                                    <option value="stop_selling">Stop Selling n Days Before</option>
                                                </select>
                                            </div>

                                            <div class="md:col-span-2 pt-4 border-t">
                                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Features</h3>
                                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                                    <CheckboxWithBox id="enable_brands" v-model="form.enable_brands" label="Enable Brands" />
                                                    <CheckboxWithBox id="enable_categories" v-model="form.enable_category" label="Enable Categories" />
                                                    <CheckboxWithBox id="enable_sub_category" v-model="form.enable_sub_category" label="Enable Sub-Categories" />
                                                    <CheckboxWithBox id="enable_price_tax_info" v-model="form.enable_price_tax_info" label="Enable Price & Tax info" />
                                                    <CheckboxWithBox id="enable_sub_units" v-model="form.enable_sub_units" label="Enable Sub Units" description="Selected Unit will show sub units." />
                                                    <CheckboxWithBox id="enable_racks" v-model="form.enable_racks" label="Enable Racks" description="Different Locations of Rack details" />
                                                    <CheckboxWithBox id="enable_row" v-model="form.enable_row" label="Enable Row" />
                                                    <CheckboxWithBox id="enable_position" v-model="form.enable_position" label="Enable Position" />
                                                    <CheckboxWithBox id="enable_warranty" v-model="form.enable_warranty" label="Enable Warranty" />
                                                    <CheckboxWithBox id="enable_product_image" v-model="form.enable_product_image" label="Is product image required?" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contact Tab -->
                                    <div v-show="activeTab === 'contact'" class="space-y-8 animate-fade-in">
                                        
                                        <!-- General Info -->
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Contact Information</h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                                <div>
                                                    <InputLabel for="default_credit_limit" value="Default Credit Limit" />
                                                    <TextInput id="default_credit_limit" v-model="form.default_credit_limit" type="number" class="mt-1 block w-full" placeholder="Defualt Credit Limit"/>
                                                    <InputError :message="form.errors.default_credit_limit" class="mt-2" />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Sale Tab -->
                                    <div v-show="activeTab === 'sale'" class="space-y-8 animate-fade-in">
                                        
                                        <!-- General Info -->
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">General Information</h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                                <div>
                                                    <InputLabel for="default_sale_discount" value="Default Sale Discount*" />
                                                    <TextInput id="default_sale_discount" v-model="form.default_sale_discount" type="number" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.default_sale_discount" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="default_sale_tax" value="Default Sale Tax" />
                                                    <Select2 id="default_sale_tax" v-model="form.default_sale_tax" :options="taxes" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.default_sale_tax" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="sales_item_addition_method" value="Sales Item Addition Method" />
                                                    <select id="sales_item_addition_method" v-model="form.sales_item_addition_method" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option>Add Item In New Row</option>
                                                        <option>Increase Item Qty If It Already Exist</option>
                                                    </select>
                                                    <InputError :message="form.errors.sales_item_addition_method" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="amount_rounding_method">
                                                        Amount Rounding Method
                                                        <Tooltip>Example: <br><b>Round to nearest 
                                                                whole number:</b> 2.23 => 2, 2.50 => 3, 2.73 => 3 <br><b>Round to nearest 
                                                                decimal (multiple of 0.05):</b> 2.11 => 2.10, 2.12 => 2.10, 2.13 => 2.15
                                                        </Tooltip>
                                                    </InputLabel>
                                                    <select id="amount_rounding_method" v-model="form.amount_rounding_method" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option>None</option>
                                                        <option>Round To Nearest Whole Number</option>
                                                        <option>Round To Nearest Decimal (multiple of 0.05)</option>
                                                        <option>Round To Nearest Decimal (multiple of 0.1)</option>
                                                        <option>Round To Nearest Decimal (multiple of 0.5)</option>
                                                    </select>
                                                    <InputError :message="form.errors.amount_rounding_method" class="mt-2" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="sales_price_is_minimum_selling_price" v-model="form.sales_price_is_minimum_selling_price" label="Sales Price is Minimum Selling Price" TooltipText="If this is enabled, on the POS or Sales screen default selling price will be the minimum selling price for the product. You cannot set price below the default selling price." />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="allow_overselling" v-model="form.allow_overselling" label="Allow Overselling" TooltipText="Check this field to allow a product to sell more than the available quantity. Oversold quantity will be adjusted automatically from future stock."/>
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="enable_sales_order" v-model="form.enable_sales_order" label="Enable Sales Order" TooltipText="The sales order, sometimes abbreviated as SO, is an order issued by a business or sole trader to a customer. A sales order may be for products and/or services."/>
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="is_pay_term_required" v-model="form.is_pay_term_required" label="Is pay term required?" />
                                                </div>
                                                
                                            </div>
                                        </div>

                                        <!-- Commission Agent -->
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Commission Agent</h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                                <div>
                                                    <InputLabel for="commission_agent" value="Sales Commission Agent" />
                                                    <select id="commission_agent" v-model="form.commission_agent" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option>Disable</option>
                                                        <option>Logged In User</option>
                                                        <option>Select From User's List</option>
                                                        <option>Select From Commission Agent's List</option>
                                                    </select>
                                                    <InputError :message="form.errors.commission_agent" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="commission_calculation_type" value="Commission Calculation Type" />
                                                    <select id="commission_calculation_type" v-model="form.commission_calculation_type" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option>Invoice Value</option>
                                                        <option>Payment Received</option>
                                                    </select>
                                                    <InputError :message="form.errors.commission_calculation_type" class="mt-2" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="is_commission_agent_required" v-model="form.is_commission_agent_required" label="Is Commission Agent Required?" />
                                                </div>

                                            </div>
                                        </div>

                                        <!-- Payment Link -->
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Payment Link <Tooltip>By Enabling Users Can Pay Invoice Using Payment Link</Tooltip></h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                                
                                                <div>
                                                    <InputLabel for="public_key" value="Public Key" />
                                                    <TextInput id="public_key" v-model="form.public_key" type="text" class="mt-1 block w-full" />
                                                    <InputError :message="form.errors.public_key" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="secret_key" value="Secret Key" />
                                                    <TextInput id="secret_key" v-model="form.secret_key" type="text" class="mt-1 block w-full" />
                                                    <InputError :message="form.errors.secret_key" class="mt-2" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="is_payment_link_enabled" v-model="form.is_payment_link_enabled" label="Enable Payment Link" />
                                                </div>

                                            </div>
                                        </div>

                                    </div>

                                    <!-- POS Tab -->
                                    <div v-show="activeTab === 'pos'" class="space-y-8 animate-fade-in">
                                        
                                        <!-- General Info -->
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">POS Settings</h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-1">
                                                <div>
                                                    <InputLabel for="keyboard_shortcuts" value="Add Keyboard Shortcuts" />
                                                    <span class="text-xs">Shortcut should be the names of the keys separated by '+'; Example: ctrl+shift+b, ctrl+h</span>
                                                </div>

                                                <div>
                                                    <InputLabel for="available_key_names" value="Available Key Names Are" />
                                                    <span class="text-xs">shift, ctrl, alt, backspace, tab, enter, return, capslock, esc, escape, space, pageup, pagedown, end, home,
                                                        left, up, right, down, ins, del, and plus
                                                    </span>
                                                </div>

                                            </div>

                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 mt-3">
                                                <div>
                                                    <table class="w-full text-sm border border-gray-200">
                                                        <thead class="bg-gray-50">
                                                            <tr class="border-b">
                                                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Operations</th>
                                                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Keyboard Shortcut</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr class="border-b">
                                                                <td class="px-3 py-2 font-medium text-gray-900">Express Checkout</td>
                                                                <td class="px-3 py-2 font-medium text-gray-900">
                                                                    <TextInput id="express_checkout" v-model="form.express_checkout" type="text" class="mt-1 block text-xs"/>
                                                                </td>
                                                            </tr>
                                                            <tr class="border-b">
                                                                <td class="px-3 py-2 font-medium text-gray-900">Pay & Checkout</td>
                                                                <td class="px-3 py-2 font-medium text-gray-900">
                                                                    <TextInput id="pay_and_checkout" v-model="form.pay_and_checkout" type="text" class="mt-1 block text-xs"/>
                                                                </td>
                                                            </tr>
                                                            <tr class="border-b">
                                                                <td class="px-3 py-2 font-medium text-gray-900">Draft</td>
                                                                <td class="px-3 py-2 font-medium text-gray-900">
                                                                    <TextInput id="draft" v-model="form.draft" type="text" class="mt-1 block text-xs"/>
                                                                </td>
                                                            </tr>
                                                            <tr class="border-b">
                                                                <td class="px-3 py-2 font-medium text-gray-900">Cancel</td>
                                                                <td class="px-3 py-2 font-medium text-gray-900">
                                                                    <TextInput id="cancelVal" v-model="form.cancelVal" type="text" class="mt-1 block text-xs"/>
                                                                </td>
                                                            </tr>
                                                            <tr class="border-b">
                                                                <td class="px-3 py-2 font-medium text-gray-900">Go to Product Quantity</td>
                                                                <td class="px-3 py-2 font-medium text-gray-900">
                                                                    <TextInput id="go_to_product_quantity" v-model="form.go_to_product_quantity" type="text" class="mt-1 block text-xs"/>
                                                                </td>
                                                            </tr>
                                                            <tr class="border-b">
                                                                <td class="px-3 py-2 font-medium text-gray-900">Weighing Scale</td>
                                                                <td class="px-3 py-2 font-medium text-gray-900">
                                                                    <TextInput id="weighing_scale" v-model="form.weighing_scale" type="text" class="mt-1 block text-xs"/>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                                <div>
                                                    <table class="w-full text-sm border border-gray-200">
                                                        <thead class="bg-gray-50">
                                                            <tr class="border-b">
                                                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Operations</th>
                                                                <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600">Keyboard Shortcut</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <tr class="border-b">
                                                                <td class="px-3 py-2 font-medium text-gray-900">Edit Discount</td>
                                                                <td class="px-3 py-2 font-medium text-gray-900">
                                                                    <TextInput id="edit_discount" v-model="form.edit_discount" type="text" class="mt-1 block text-xs"/>
                                                                </td>
                                                            </tr>
                                                            <tr class="border-b">
                                                                <td class="px-3 py-2 font-medium text-gray-900">Edit Order Tax</td>
                                                                <td class="px-3 py-2 font-medium text-gray-900">
                                                                    <TextInput id="edit_order_tax" v-model="form.edit_order_tax" type="text" class="mt-1 block text-xs"/>
                                                                </td>
                                                            </tr>
                                                            <tr class="border-b">
                                                                <td class="px-3 py-2 font-medium text-gray-900">Add Payment Row</td>
                                                                <td class="px-3 py-2 font-medium text-gray-900">
                                                                    <TextInput id="add_payment_row" v-model="form.add_payment_row" type="text" class="mt-1 block text-xs"/>
                                                                </td>
                                                            </tr>
                                                            <tr class="border-b">
                                                                <td class="px-3 py-2 font-medium text-gray-900">Finalize Payment</td>
                                                                <td class="px-3 py-2 font-medium text-gray-900">
                                                                    <TextInput id="finalize_payment" v-model="form.finalize_payment" type="text" class="mt-1 block text-xs"/>
                                                                </td>
                                                            </tr>
                                                            <tr class="border-b">
                                                                <td class="px-3 py-2 font-medium text-gray-900">Add new product</td>
                                                                <td class="px-3 py-2 font-medium text-gray-900">
                                                                    <TextInput id="add_new_product" v-model="form.add_new_product" type="text" class="mt-1 block text-xs"/>
                                                                </td>
                                                            </tr>
                                                        </tbody>
                                                    </table>
                                                </div>

                                            </div>
                                            
                                        </div>

                                        <!-- POS Settings -->
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">POS Settings</h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                                <div>
                                                    <CheckboxWithBox id="disable_multiple_pay" v-model="form.disable_multiple_pay" label="Disable Multiple Pay" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="disable_draft" v-model="form.disable_draft" label="Disable Draft" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="disable_express_checkout" v-model="form.disable_express_checkout" label="Disable Express Checkout" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="disable_product_suggestion" v-model="form.disable_product_suggestion" label="Don't show product suggestion" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="disable_recent_transactions" v-model="form.disable_recent_transactions" label="Don't show recent transactions" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="disable_discount" v-model="form.disable_discount" label="Disable Discount" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="disable_order_tax" v-model="form.disable_order_tax" label="Disable order tax" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="subtotal_editable" v-model="form.subtotal_editable" label="Subtotal Editable" TooltipText="Check this to make Subtotal field editable for each product in POS screen"/>
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="disable_suspend_sale" v-model="form.disable_suspend_sale" label="Disable Suspend Sale" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="enable_transaction_date" v-model="form.enable_transaction_date" label="Enable transaction date on POS screen" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="enable_service_staff" v-model="form.enable_service_staff" label="Enable service staff in product line" TooltipText="If enabled different service staffs can be assigned for different products for an order/sale"/>
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="is_service_staff_required" v-model="form.is_service_staff_required" label="Is service staff required" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="disable_credit_sale_button" v-model="form.disable_credit_sale_button" label="Disable credit sale button" TooltipText="If enabled credit sale button will be shown in place of Card button on pos screen"/>
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="enable_weighing_scale" v-model="form.enable_weighing_scale" label="Enable Weighing Scale" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="show_invoice_scheme" v-model="form.show_invoice_scheme" label="Show invoice scheme" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="show_invoice_layout_dropdown" v-model="form.show_invoice_layout_dropdown" label="Show invoice layout dropdown" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="print_invoice_on_suspend" v-model="form.print_invoice_on_suspend" label="Print invoice on suspend" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="show_pricing_on_product_suggestion_tooltip" v-model="form.show_pricing_on_product_suggestion_tooltip" label="Show pricing on product suggestion tooltip" />
                                                </div>

                                            </div>                                            
                                        </div>

                                        <!-- Weighing Scale barcode Setting -->
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Weighing Scale barcode Setting</h3>
                                            <span class="text-gray-600">Configure barcode as per your weighing scale.</span>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-4 mt-3">
                                                
                                                <div>
                                                    <InputLabel for="weighing_scale_prefix" value="Prefix" />
                                                    <TextInput id="weighing_scale_prefix" v-model="form.weighing_scale_prefix" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.weighing_scale_prefix" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="product_sku_length" value="Product sku length" />
                                                    <select id="product_sku_length" v-model="form.product_sku_length" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                        <option>5</option>
                                                        <option>6</option>
                                                        <option>7</option>
                                                        <option>8</option>
                                                        <option>9</option>
                                                    </select>    
                                                </div>

                                                <div>
                                                    <InputLabel for="quantity_integer_part_length" value="Quantity integer part length" />
                                                    <select id="quantity_integer_part_length" v-model="form.quantity_integer_part_length" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                        <option>5</option>
                                                    </select>    
                                                </div>

                                                <div>
                                                    <InputLabel for="quantity_fractional_part_length" value="Quantity fractional part length" />
                                                    <select id="quantity_fractional_part_length" v-model="form.quantity_fractional_part_length" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option>1</option>
                                                        <option>2</option>
                                                        <option>3</option>
                                                        <option>4</option>
                                                    </select>    
                                                </div>
                                                

                                            </div>                                            
                                        </div>
                                    </div>

                                    <!-- Purchase Tab -->
                                    <div v-show="activeTab === 'purchases'" class="space-y-8 animate-fade-in">
                                        <!-- Purchase Settings -->
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Purchase Settings</h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                                                <div>
                                                    <CheckboxWithBox id="enable_editing_product_price_from_purchase_screen" v-model="form.enable_editing_product_price_from_purchase_screen" label="Enable editing product price from purchase screen" TooltipText="If enabled product purchase price and selling price will be updated after a purchase is added or updated"/>
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="enable_purchase_status" v-model="form.enable_purchase_status" label="Enable Purchase Status" TooltipText="On disable all purchases will be marked as <i>Item Received</i>"/>
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="enable_lot_number" v-model="form.enable_lot_number" label="Enable Lot number" TooltipText="This will enable you to enter Lot number for each purchase line in purchase screen"/>
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="enable_purchase_order" v-model="form.enable_purchase_order" label="Enable purchase order" TooltipText="A purchase requisition is a document that an employee creates to request a purchase of goods or services."/>
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="enable_purchase_requisition" v-model="form.enable_purchase_requisition" label="Enable Purchase Requisition" TooltipText="A purchase requisition is a document that an employee creates to request a purchase of goods or services."/>
                                                </div>

                                            </div>                                            
                                        </div>

                                       
                                    </div> 

                                    <!-- Payment Tab -->
                                    <div v-show="activeTab === 'payment'" class="space-y-8 animate-fade-in">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Payment Settings</h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                                <div class="md:col-span-2 flex flex-col items-center sm:flex-row gap-6">
                                                    <div class="flex-1 w-full">
                                                        <InputLabel for="cash_denominations" value="Cash Denominations" />
                                                        <TextInput id="cash_denominations" v-model="form.cash_denominations" type="text" class="mt-1 block w-full" placeholder="Enter cash denominations"/>
                                                        <p class="mt-1 text-xs text-gray-500">Comma separated values Example: 100,200,500,2000</p>
                                                        <InputError :message="form.errors.cash_denominations" class="mt-2" />
                                                    </div>
                                                </div>

                                                <div>
                                                    <InputLabel for="enable_cash_denomination_on" value="Enable cash denomination on" />
                                                    <select id="enable_cash_denomination_on" v-model="form.enable_cash_denomination_on" class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                                        <option>POS Screen</option>
                                                        <option>All Screen</option>
                                                    </select>    
                                                </div>

                                                <div>
                                                    <InputLabel for="enable_cash_denomination_for_payment_methods" value="Enable cash denomination for payment methods" />
                                                    <TextInput id="enable_cash_denomination_for_payment_methods" v-model="form.enable_cash_denomination_for_payment_methods" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.enable_cash_denomination_for_payment_methods" class="mt-2" />
                                                </div>

                                                <div>
                                                    <CheckboxWithBox id="strict_check" v-model="form.strict_check" label="Strict check" TooltipText="If enabled payment amount must be equal to sum of cash denominations"/>
                                                </div>

                                            </div>
                                        </div>
                                        
                                    </div>

                                    <!-- Prefixes Tab -->
                                    <div v-show="activeTab === 'prefixes'" class="space-y-8 animate-fade-in">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Prefix Settings</h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                                                
                                                <div>
                                                    <InputLabel for="prefix_purchase" value="Purchase" />
                                                    <TextInput id="prefix_purchase" v-model="form.prefix_purchase" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_purchase" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_purchase_return" value="Purchase Return" />
                                                    <TextInput id="prefix_purchase_return" v-model="form.prefix_purchase_return" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_purchase_return" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_purchase_requisition" value="Purchase Requisition" />
                                                    <TextInput id="prefix_purchase_requisition" v-model="form.prefix_purchase_requisition" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_purchase_requisition" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_purchase_order" value="Purchase Order" />
                                                    <TextInput id="prefix_purchase_order" v-model="form.prefix_purchase_order" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_purchase_order" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_stock_transfer" value="Stock Transfer" />
                                                    <TextInput id="prefix_stock_transfer" v-model="form.prefix_stock_transfer" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_stock_transfer" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_stock_adjustment" value="Stock Adjustment" />
                                                    <TextInput id="prefix_stock_adjustment" v-model="form.prefix_stock_adjustment" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_stock_adjustment" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_sell_return" value="Sell Return" />
                                                    <TextInput id="prefix_sell_return" v-model="form.prefix_sell_return" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_sell_return" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_expenses" value="Expenses" />
                                                    <TextInput id="prefix_expenses" v-model="form.prefix_expenses" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_expenses" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_contacts" value="Contacts" />
                                                    <TextInput id="prefix_contacts" v-model="form.prefix_contacts" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_contacts" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_purchase_payment" value="Purchase Payment" />
                                                    <TextInput id="prefix_purchase_payment" v-model="form.prefix_purchase_payment" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_purchase_payment" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_sell_payment" value="Sell Payment" />
                                                    <TextInput id="prefix_sell_payment" v-model="form.prefix_sell_payment" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_sell_payment" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_expense_payment" value="Expense Payment" />
                                                    <TextInput id="prefix_expense_payment" v-model="form.prefix_expense_payment" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_expense_payment" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_business_location" value="Business Location" />
                                                    <TextInput id="prefix_business_location" v-model="form.prefix_business_location" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_business_location" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_username" value="Username" />
                                                    <TextInput id="prefix_username" v-model="form.prefix_username" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_username" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_subscription_no" value="Subscription No." />
                                                    <TextInput id="prefix_subscription_no" v-model="form.prefix_subscription_no" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_subscription_no" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_draft" value="Draft" />
                                                    <TextInput id="prefix_draft" v-model="form.prefix_draft" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_draft" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="prefix_sales_order" value="Sales Order" />
                                                    <TextInput id="prefix_sales_order" v-model="form.prefix_sales_order" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.prefix_sales_order" class="mt-2" />
                                                </div>

                                                

                                            </div>
                                        </div>
                                        
                                    </div>

                                    <!-- Email Tab -->
                                    <div v-show="activeTab === 'emailSettings'" class="space-y-8 animate-fade-in">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-6">Email Settings</h3>
                                            <div class="grid grid-cols-1 gap-6 md:grid-cols-4">
                                                
                                                <div>
                                                    <InputLabel for="mail_driver" value="Mail Driver" />
                                                    <TextInput id="mail_driver" v-model="form.mail_driver" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.mail_driver" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="mail_host" value="Host" />
                                                    <TextInput id="mail_host" v-model="form.mail_host" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.mail_host" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="mail_port" value="Port" />
                                                    <TextInput id="mail_port" v-model="form.mail_port" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.mail_port" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="mail_username" value="Username" />
                                                    <TextInput id="mail_username" v-model="form.mail_username" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.mail_username" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="mail_password" value="Password" />
                                                    <TextInput id="mail_password" v-model="form.mail_password" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.mail_password" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="mail_encryption" value="Encryption" />
                                                    <TextInput id="mail_encryption" v-model="form.mail_encryption" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.mail_encryption" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="mail_from_address" value="From Address" />
                                                    <TextInput id="mail_from_address" v-model="form.mail_from_address" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.mail_from_address" class="mt-2" />
                                                </div>

                                                <div>
                                                    <InputLabel for="mail_from_name" value="From Name" />
                                                    <TextInput id="mail_from_name" v-model="form.mail_from_name" type="text" class="mt-1 block w-full"/>
                                                    <InputError :message="form.errors.mail_from_name" class="mt-2" />
                                                </div>

                                                <div>
                                                    <PrimaryButton type="button" @click.prevent="sendTestEmail">
                                                        <i class="fas fa-envelope mr-2"></i> Send Test Email
                                                    </PrimaryButton>
                                                </div>

                                            </div>
                                        </div>
                                        
                                    </div>

                                    <!-- Submit Button -->
                                    <div class="flex items-center justify-end pt-6 border-t border-gray-200 mt-6">
                                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                            <i class="fas fa-save mr-2"></i> Save Changes
                                        </PrimaryButton>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
.animate-fade-in {
    animation: fadeIn 0.3s ease-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(5px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

