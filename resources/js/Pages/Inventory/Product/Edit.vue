<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { onMounted, ref, onUnmounted, computed, watch } from "vue";
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextArea from '@/Components/TextArea.vue';
import TextInputWithDataList from '@/Components/TextInputWithDataList.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import ImageUpload from '@/Components/ImageUpload.vue';
import CategorySelector from '@/Components/CategorySelector.vue';
import CheckboxWithBox from '@/Components/CheckboxWithBox.vue';
import Tooltip from '@/Components/Tooltip.vue';
import Select from '@/Components/Select.vue';
import Switch from '@/Components/Switch.vue';
import RichTextEditor from '@/Components/RichTextEditor.vue';
import ProductSearch from '@/Components/ProductSearch.vue';

const props = defineProps({
    categories: {
        type: Object,
    },
    brands: {
        type: Object,
    },
    productToDuplicate: {
        type: Object,
        default: null,
    },
    taxes: {
        type: Object,
        default: null,
    },
    product: {
        type: Object,
        default: null,
    },
    search_products: {
        type: Object,
        default: null,
    },
    businessSettings: {
        type: Object,
        default: null,
    },
});

const productsObject = computed(() => {
    if (Array.isArray(props.search_products)) return props.search_products;
    if (typeof props.search_products === 'object' && props.search_products !== null) return Object.values(props.search_products);
    return [];
});

const showVariations = ref(false);
const showCombo = ref(false);
const mockCategories = ref(props.categories);
const activeSection = ref("basic-info");

const form = useForm({
    pro_id: '',
    pro_name: '',
    pro_short_code: '',
    pro_category: '0',
    pro_brands: '',
    pro_sellable: false,
    pro_consumable: false,
    pro_warrenty_period: '',
    pro_warrenty_type: '',
    pro_expiry_date: '',
    pro_pur_price: '',
    pro_w_price: '',
    pro_r_price: '',
    pro_qty: '',
    pro_min_qty: '',
    // Tax fields
    applicable_tax: '0',
    selling_price_tax_type: 'exclusive',
    pro_pur_price_inc: '', // Calculated field for frontend
    pro_description: '',
    pro_image1: '',
    pro_image2: '',
    pro_image3: '',
    pro_image4: '',
    pro_image5: '',
    variation_image: '',
    variation: [],
    variation_name: '', // e.g., Color, Size
    include_image: false,
    // Shipping fields
    weight: '',
    weight_unit: 'kg',
    height: '',
    width: '',
    length: '',
    // Combo fields
    combo_products: [],
});

const addNewRow = () => {
    form.variation.push({
        var_name: '',
        var_image: null,
        var_sku: '',
        var_purchase_price: '', // Exc Tax
        var_purchase_price_inc: '', // Inc Tax (Calculated)
        var_wholesale_price: '',
        var_price: '',
        var_qty: '',
    });
};

const removeRow = (index) => {
    form.variation.splice(index, 1);
}

const showVariationSection = () => {
    if (showVariations.value && form.variation.length === 0) {
        addNewRow();
    } else if (!showVariations.value) {
        form.variation = [];
    }
};

const showComboSection = () => {
    if (showCombo.value) {
        showVariations.value = false;
        form.variation = [];
    } else {
        form.combo_products = [];
    }
};

const addComboProduct = (product) => {
    let purchasePrice = 0;
    // Determine purchase price based on whether it's a variation or simple product
    if (product._isSelectedVariation) {
        purchasePrice = parseFloat(product.var_purchase_price) || 0;
    } else {
        purchasePrice = parseFloat(product.purchase) || 0;
    }

    const key = product._isSelectedVariation ? `v-${product.id}` : `p-${product.id}`;

    const existingIndex = form.combo_products.findIndex(p => p._key === key);

    if (existingIndex !== -1) {
        form.combo_products[existingIndex].qty += 1;
    } else {
        form.combo_products.push({
            _key: key,
            product_id: product._isSelectedVariation ? (product._parent ? product._parent.id : product.product_id) : product.id,
            variation_id: product._isSelectedVariation ? product.id : null,
            name: product._isSelectedVariation
                ? (product._parent.name + ' - ' + product._parent.variation_name + ' - ' + product.var_name + '(' + product.var_sku + ')')
                : (product.name + '(' + product.barcode + ')'),
            purchase_price: purchasePrice,
            qty: 1,
        });
    }

    calculateComboTotals();
};

const removeComboProduct = (index) => {
    form.combo_products.splice(index, 1);
    calculateComboTotals();
};

const calculateComboTotals = () => {
    const totalPurchase = form.combo_products.reduce((sum, item) => {
        return sum + ((parseFloat(item.qty) || 0) * (parseFloat(item.purchase_price) || 0));
    }, 0);
    form.pro_pur_price = totalPurchase.toFixed(2);
};

// Watcher to ensure exclusivity
watch(showVariations, (val) => {
    if (val) {
        showCombo.value = false;
        form.combo_products = [];
    }
});

// Tax Calculation Logic
const selectedTaxRate = computed(() => {
    if (!form.applicable_tax || form.applicable_tax === '0') return 0;
    const tax = props.taxes?.find(t => t.id == form.applicable_tax);
    return tax ? parseFloat(tax.rate) : 0;
});

// Helper to round to 2 decimals
const round = (num) => Math.round((parseFloat(num) || 0) * 100) / 100;

// Watchers for Main Product
watch(() => form.pro_pur_price, (val) => {
    // When Exc changes, update Inc
    if (document.activeElement?.id === 'pro_pur_price') {
        const rate = selectedTaxRate.value;
        const inc = parseFloat(val) * (1 + rate / 100);
        form.pro_pur_price_inc = isNaN(inc) ? '' : inc.toFixed(2);
    }
});

watch(() => form.pro_pur_price_inc, (val) => {
    // When Inc changes, update Exc
    if (document.activeElement?.id === 'pro_pur_price_inc') {
        const rate = selectedTaxRate.value;
        const exc = parseFloat(val) / (1 + rate / 100);
        form.pro_pur_price = isNaN(exc) ? '' : exc.toFixed(2);
    }
});

watch(() => form.applicable_tax, () => {
    // Recalculate Inc based on Exc when tax changes
    const rate = selectedTaxRate.value;

    // Update Main Product
    if (form.pro_pur_price) {
        const inc = parseFloat(form.pro_pur_price) * (1 + rate / 100);
        form.pro_pur_price_inc = inc.toFixed(2);
    }

    // Update Variations
    form.variation.forEach(v => {
        if (v.var_purchase_price) {
            const vInc = parseFloat(v.var_purchase_price) * (1 + rate / 100);
            v.var_purchase_price_inc = vInc.toFixed(2);
        }
    });

    // Handle Retail Price Tax Type Adjustment if needed?
    // User requested "selling price tax type with retail price".
    // Usually changing tax rate doesn't change Input Price, but changes semantic meaning.
});

// Variation Tax Handlers
const updateVariationTax = (index, type) => {
    const rate = selectedTaxRate.value;
    const variation = form.variation[index];

    if (type === 'exc') {
        const inc = parseFloat(variation.var_purchase_price) * (1 + rate / 100);
        variation.var_purchase_price_inc = isNaN(inc) ? '' : inc.toFixed(2);
    } else {
        const exc = parseFloat(variation.var_purchase_price_inc) / (1 + rate / 100);
        variation.var_purchase_price = isNaN(exc) ? '' : exc.toFixed(2);
    }
};

const submit = () => {
    // Strip frontend-only _key field from combo_products before submitting
    const originalComboProducts = form.combo_products;
    form.combo_products = form.combo_products.map(({ _key, ...rest }) => rest);

    form.post(route('updateProduct'), {
        onSuccess: () => {
            // Redirect is handled by controller, but we can reset if needed or just wait
        },
        onError: (e) => {
            // Restore _key on error so the UI still works
            form.combo_products = originalComboProducts;
            console.log(e);
        },
    });
};

// Calculate shipping fee based on weight and dimensions
const shippingFee = computed(() => {
    const weight = parseFloat(form.weight) || 0;
    const height = parseFloat(form.height) || 0;
    const width = parseFloat(form.width) || 0;
    const length = parseFloat(form.length) || 0;

    if (weight === 0 && height === 0 && width === 0 && length === 0) {
        return 0;
    }

    // Convert weight to kg if in grams
    const weightInKg = form.weight_unit === 'g' ? weight / 1000 : weight;

    // Calculate volumetric weight (cm³ to kg)
    const volumetricWeight = (height * width * length) / 5000;

    // Use the greater of actual weight or volumetric weight
    const chargeableWeight = Math.max(weightInKg, volumetricWeight);

    // Base rate: $5 for first kg, $2 for each additional kg
    const baseFee = 5;
    const additionalFee = Math.max(0, Math.ceil(chargeableWeight) - 1) * 2;

    return baseFee + additionalFee;
});

const scrollToSection = (id) => {
    activeSection.value = id;
    const element = document.getElementById(id);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
};

const handleScroll = () => {
    const sections = ['basic-info', 'product-details', 'sales-info', 'shipping'];
    for (const id of sections) {
        const element = document.getElementById(id);
        if (element) {
            const rect = element.getBoundingClientRect();
            if (rect.top >= 0 && rect.top <= 300) {
                activeSection.value = id;
                break;
            }
        }
    }
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);

    // Populate form with existing product data (Edit Mode)
    if (props.product) {
        form.pro_id = props.product.id;
        form.pro_name = props.product.name || '';
        form.pro_short_code = props.product.barcode || '';
        form.pro_category = props.product.category_id || '0';
        form.pro_brands = props.product.brand?.name || '';

        // Convert 1/0 to boolean for sellable and consumable
        form.pro_sellable = props.product.sellable === 1 || props.product.sellable === true;
        form.pro_consumable = props.product.consumable === 1 || props.product.consumable === true;

        form.pro_warrenty_period = props.product.warranty_period || '';
        form.pro_warrenty_type = props.product.warranty_type || '';
        form.pro_expiry_date = props.product.expiry_date || '';
        form.pro_pur_price = props.product.purchase || '';
        form.pro_w_price = props.product.whole_sale || '';
        form.pro_r_price = props.product.retail || '';
        form.pro_qty = props.product.qty || '';
        form.pro_min_qty = props.product.min_qty || '';
        form.pro_description = props.product.description || '';
        form.weight = props.product.weight || '';
        form.weight_unit = props.product.weight_unit || 'kg';
        form.height = props.product.height || '';
        form.width = props.product.width || '';
        form.length = props.product.length || '';
        form.variation_name = props.product.variation_name || '';

        // Convert 1/0 to boolean for include_image
        form.include_image = props.product.include_image === 1 || props.product.include_image === true;

        // Pre-populate images if they exist
        if (props.product.image_uri1) form.pro_image1 = '/storage/' + props.product.image_uri1;
        if (props.product.image_uri2) form.pro_image2 = '/storage/' + props.product.image_uri2;
        if (props.product.image_uri3) form.pro_image3 = '/storage/' + props.product.image_uri3;
        if (props.product.image_uri4) form.pro_image4 = '/storage/' + props.product.image_uri4;
        if (props.product.image_uri5) form.pro_image5 = '/storage/' + props.product.image_uri5;

        // Tax Info
        // Note: tax_id is not yet in product model, but if it were:
        // form.applicable_tax = props.product.tax_id || '0'; 
        // For now, default to '0' or whatever logic fits. 
        // User requested "combo not working in edit page", so prioritizing that.

        // Load variations if they exist
        if (props.product.variations && props.product.variations.length > 0) {
            showVariations.value = true;
            form.variation = props.product.variations.map(v => ({
                var_name: v.var_name || '',
                var_image: v.var_image ? '/storage/' + v.var_image : null,
                var_sku: v.var_sku.split('-')[1] || '',
                var_purchase_price: v.var_purchase_price || '',
                var_purchase_price_inc: v.var_purchase_price || '',
                var_wholesale_price: v.var_wholesale_price || '',
                var_price: v.var_retail_price || '',
                var_qty: v.var_qty || '',
            }));
        }

        // Load Combo Logic
        if (props.product.type === 'combo' && props.product.combos) {
            showCombo.value = true;
            form.combo_products = props.product.combos.map(c => {
                // Construct the key and display name.
                const isVar = !!c.combo_variation_id;
                const key = isVar ? `v-${c.combo_variation_id}` : `p-${c.combo_product_id}`;
                const name = isVar
                    ? (c.combo_variation?.product?.name + ' - ' + c.combo_variation?.product?.variation_name + ' - ' + c.combo_variation?.var_name + '(' + c.combo_variation?.var_sku + ')')
                    : c.combo_product?.name + '(' + c.combo_product?.barcode + ')';

                return {
                    _key: key,
                    product_id: c.combo_product_id,
                    variation_id: c.combo_variation_id,
                    name: name || 'Unknown Product',
                    purchase_price: c.price,
                    qty: parseFloat(c.quantity),
                };
            });
            calculateComboTotals();
        }
    }
    // Populate form if duplicating a product (Fallback or Alternative)
    else if (props.productToDuplicate) {
        const product = props.productToDuplicate;

        // Basic Information
        form.pro_name = product.name + ' (Copy)' || '';
        form.pro_short_code = product.barcode || '';
        form.pro_category = product.category_id || '0';
        form.pro_brands = product.brand?.name || '';

        // Product Details
        form.pro_sellable = (product.sellable = 1 ? true : false) || false;
        form.pro_consumable = (product.consumable = 1 ? true : false) || false;
        form.pro_description = product.description || '';
        form.pro_warrenty_period = product.warranty_period || '';
        form.pro_warrenty_type = product.warranty_type || '';
        form.pro_expiry_date = product.expiry_date || '';

        // Sales Information
        form.pro_pur_price = product.purchase || '';
        form.pro_w_price = product.whole_sale || '';
        form.pro_r_price = product.retail || '';
        form.pro_qty = product.qty || '';
        form.pro_min_qty = product.min_qty || '';

        // Shipping
        form.weight = product.weight || '';
        form.weight_unit = product.weight_unit || 'kg';
        form.height = product.height || '';
        form.width = product.width || '';
        form.length = product.length || '';

        // Variations
        if (product.variations && product.variations.length > 0) {
            showVariations.value = true;
            form.variation_name = product.variation_name || '';
            form.include_image = product.include_image || false;
            form.variation = product.variations.map(v => ({
                var_name: v.var_name || '',
                var_image: null, // Don't copy images
                var_sku: v.var_sku || '',
                var_purchase_price: v.var_purchase_price || '',
                var_wholesale_price: v.var_wholesale_price || '',
                var_price: v.var_retail_price || v.var_price || '',
                var_qty: v.var_qty || '',
            }));
        }

        // Note: Images are not copied when duplicating
    }
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});

const warrantyOptions = ref([
    { id: 'none', name: 'No Warranty' },
    { id: 'limited_mfg', name: 'Limited Manufacturer Warranty' },
    { id: 'full_mfg', name: 'Full Manufacturer Warranty' },
    { id: 'seller', name: 'Seller Warranty' },
    { id: 'lifetime_mfg', name: 'Manufacturer\'s Lifetime Warranty' }
]);

const tax_types = ref([
    { id: 'inclusive', name: 'Inclusive' },
    { id: 'exclusive', name: 'Exclusive' }
]);

const units = ref([
    { id: 'kg', name: 'kg' },
    { id: 'g', name: 'g' },
    { id: 'pc', name: 'Pieces (pc)' },
    { id: 'pkt', name: 'Packets (pkt)' },
    { id: 'dz', name: 'Dozen (dz)' },
]);

const isFormValid = computed(() => {
    return !!(
        form.pro_name &&
        form.pro_category &&
        form.pro_brands &&
        form.selling_price_tax_type &&
        form.pro_min_qty &&
        form.weight &&
        form.weight_unit &&
        form.height &&
        form.width &&
        form.length &&
        (form.pro_short_code || form.variation?.var_sku)
    );
});
</script>

<template>

    <Head title="Product" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">
                <i class="fa fa-shopping-basket"></i>
                {{ product ? 'Edit Product' : (productToDuplicate ? 'Duplicate Product' : 'Add Product') }}
            </h1>
        </div>

        <form @submit.prevent="submit">
            <div class="row">
                <!-- Sidebar Navigation -->
                <div class="col-md-3 d-none d-md-block">
                    <div class="sticky-sidebar">
                        <div class="bg-white rounded-lg shadow-sm p-4 mb-4 border border-gray-100">
                            <div class="flex items-start mb-4">
                                <i class="fas fa-bullhorn text-gray-800 mt-1 mr-2"></i>
                                <div>
                                    <h5 class="font-bold text-gray-800 text-sm mb-1">Suggestions</h5>
                                    <p class="text-xs text-gray-500 leading-relaxed">
                                        Check how to improve your product information.
                                        <a href="#" class="text-blue-500 hover:underline">Learn more</a>
                                    </p>
                                </div>
                            </div>


                            <ul class="space-y-1">
                                <li>
                                    <a href="javascript:void(0)" @click="scrollToSection('basic-info')"
                                        class="block px-4 py-3 text-sm font-medium rounded-md transition-colors duration-200 text-decoration-none"
                                        :class="activeSection === 'basic-info' ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                                        Basic Information
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" @click="scrollToSection('product-details')"
                                        class="block px-4 py-3 text-sm font-medium rounded-md transition-colors duration-200 text-decoration-none"
                                        :class="activeSection === 'product-details' ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                                        Product Details
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" @click="scrollToSection('sales-info')"
                                        class="block px-4 py-3 text-sm font-medium rounded-md transition-colors duration-200 text-decoration-none"
                                        :class="activeSection === 'sales-info' ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                                        Sales Information
                                    </a>
                                </li>
                                <li>
                                    <a href="javascript:void(0)" @click="scrollToSection('shipping')"
                                        class="block px-4 py-3 text-sm font-medium rounded-md transition-colors duration-200 text-decoration-none"
                                        :class="activeSection === 'shipping' ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900'">
                                        Shipping
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Main Content -->
                <div class="col-md-9">

                    <!-- Basic Information Section -->
                    <div id="basic-info" class="card shadow-sm border-0 mb-4 scroll-mt-20">
                        <div class="card-header bg-white py-3 border-b-0">
                            <h5 class="mb-0 text-gray-800 font-bold">Basic Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <InputLabel for="pro_name" value="Name" required />
                                    <TextInput id="pro_name" type="text" class="mt-1 block w-full"
                                        v-model="form.pro_name" placeholder="Enter Name" required autofocus />
                                </div>

                                <div class="col-md-12 mb-4">
                                    <InputLabel for="pro_category" value="Category" required />
                                    <CategorySelector v-model="form.pro_category" :categories="mockCategories"
                                        placeholder="Select Category" class="mt-1" />
                                </div>

                                <div class="col-md-12 mb-4">
                                    <InputLabel for="Brands" value="Brand" required />
                                    <TextInputWithDataList id="pro_brands" type="text" class="mt-1 block w-full"
                                        v-model="form.pro_brands" :options="brands" listId="pro_brands"
                                        placeholder="Enter Brand" required />
                                </div>

                                <div class="col-md-12 mb-4">
                                    <InputLabel for="pro_short_code" value="SKU" required />
                                    <TextInput id="pro_short_code" type="text" class="w-full"
                                        v-model="form.pro_short_code" placeholder="Enter SKU" required />
                                </div>

                                <!-- Product Attributes -->
                                <div class="col-md-12">
                                    <div class="border rounded-lg p-4 bg-gray-50/50">
                                        <div class="mb-3">
                                            <h3 class="text-sm font-medium text-gray-700">Product Attributes</h3>
                                            <p class="text-xs text-gray-500">Enter product attributes if any</p>
                                        </div>
                                        <div class="flex flex-wrap gap-4">
                                            <div>
                                                <InputLabel for="pro_warrenty_period" value="Warrenty Period" />
                                                <TextInput id="pro_warrenty_period" type="text"
                                                    class="mt-1 block text-sm" v-model="form.pro_warrenty_period"
                                                    placeholder="Enter Warrenty Period" />
                                            </div>
                                            <div>
                                                <InputLabel for="pro_expiry_date" value="Expiry Date" />
                                                <TextInput id="pro_expiry_date" type="date" class="mt-1 block text-sm"
                                                    v-model="form.pro_expiry_date" placeholder="Enter Expiry Date" />
                                            </div>
                                            <div>
                                                <InputLabel for="pro_warrenty_type" value="Warrenty Type" />
                                                <Select id="pro_warrenty_type" v-model="form.pro_warrenty_type"
                                                    :options="warrantyOptions" class="text-xs" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Product Images -->
                                <div class="col-md-12 mt-3">
                                    <div class="border rounded-lg p-4 bg-gray-50/50">
                                        <div class="mb-3">
                                            <h3 class="text-sm font-medium text-gray-700">Product Images</h3>
                                            <p class="text-xs text-gray-500">Upload 1:1 format images. First image will
                                                be the main cover.</p>
                                        </div>
                                        <div class="flex flex-wrap gap-4">
                                            <div>
                                                <ImageUpload v-model="form.pro_image1" :isCover="true"
                                                    placeholderLabel="Add Cover" />
                                            </div>
                                            <div>
                                                <ImageUpload v-model="form.pro_image2" placeholderLabel="Image 2" />
                                            </div>
                                            <div>
                                                <ImageUpload v-model="form.pro_image3" placeholderLabel="Image 3" />
                                            </div>
                                            <div>
                                                <ImageUpload v-model="form.pro_image4" placeholderLabel="Image 4" />
                                            </div>
                                            <div>
                                                <ImageUpload v-model="form.pro_image5" placeholderLabel="Image 5" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Product Details Section -->
                    <div id="product-details" class="card shadow-sm border-0 mb-4 scroll-mt-20">
                        <div class="card-header bg-white py-3 border-b-0">
                            <h5 class="mb-0 text-gray-800 font-bold">Product Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12 mb-4">
                                    <InputLabel for="pro_description" value="Description" />
                                    <div class="mt-1">
                                        <!-- <RichTextEditor 
                                            v-model="form.pro_description" 
                                            placeholder="Type text or paste a link"
                                        /> -->
                                        <TextArea id="pro_description" v-model="form.pro_description"
                                            placeholder="Type text or paste a link" />
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <CheckboxWithBox id="sellable" v-model="form.pro_sellable" label="Sellable Item"
                                        TooltipText="Product that can be sold" />
                                </div>
                                <div class="col-md-6">
                                    <CheckboxWithBox id="consumable" v-model="form.pro_consumable"
                                        label="Consumable Item"
                                        TooltipText="Product used as raw material to make other products" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sales Information Section -->
                    <div id="sales-info" class="card shadow-sm border-0 mb-4 scroll-mt-20">
                        <div class="card-header bg-white py-3 border-b-0">
                            <h5 class="mb-0 text-gray-800 font-bold">Sales Information</h5>
                        </div>
                        <div class="card-body">
                            <div class="row mb-3">
                                <div class="col-md-3">
                                    <InputLabel for="applicable_tax" value="Applicable Tax" />
                                    <Select id="applicable_tax" class="mt-1 w-full max-w-md text-xs"
                                        v-model="form.applicable_tax" :options="taxes" />
                                </div>
                                <div class="col-md-3">
                                    <InputLabel for="selling_price_tax_type" value="Selling Price Tax Type" required />
                                    <Select id="selling_price_tax_type" class="mt-1 w-full max-w-md text-xs"
                                        v-model="form.selling_price_tax_type" :options="tax_types" required />
                                </div>
                            </div>
                            <div class="row">
                                <!-- Add Variations Switch -->
                                <div class="col-md-12 mb-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h6 class="text-sm font-semibold text-gray-800 mb-1">Enable Variations</h6>
                                            <p class="text-xs text-gray-500">Add product variations like size, color,
                                                etc.</p>
                                        </div>
                                        <Switch id="variation-toggle" v-model="showVariations"
                                            @change="showVariationSection" />
                                    </div>
                                </div>

                                <!-- Add Combo Switch -->
                                <div class="col-md-12 mb-4">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h6 class="text-sm font-semibold text-gray-800 mb-1">Enable Combo Bundle
                                            </h6>
                                            <p class="text-xs text-gray-500">Create a bundle from existing products.</p>
                                        </div>
                                        <Switch id="combo-toggle" v-model="showCombo" @change="showComboSection" />
                                    </div>
                                </div>

                                <!-- Combo Configuration (when enabled) -->
                                <div v-if="showCombo" class="col-md-12 mb-4">
                                    <div class="border rounded-lg p-4 bg-gray-50/50">
                                        <h6 class="text-sm font-semibold text-gray-700 mb-4">Combo Configuration</h6>

                                        <div class="mb-4">
                                            <InputLabel value="Search Products to Add" class="mb-2" />
                                            <ProductSearch :products="productsObject" @select="addComboProduct"
                                                placeholder="Search products to add to bundle..." />
                                        </div>

                                        <!-- Combo Table -->
                                        <div v-if="form.combo_products.length > 0"
                                            class="overflow-x-auto rounded-lg border border-gray-200 bg-white mb-4">
                                            <table class="w-full text-sm">
                                                <thead class="bg-gray-50">
                                                    <tr class="border-b">
                                                        <th class="px-4 py-2 text-left font-medium text-gray-600">
                                                            Product Name</th>
                                                        <th class="px-4 py-2 text-center font-medium text-gray-600"
                                                            style="width: 120px;">Quantity</th>
                                                        <th class="px-4 py-2 text-right font-medium text-gray-600">
                                                            Purchase Price</th>
                                                        <th class="px-4 py-2 text-right font-medium text-gray-600">Total
                                                            Amount</th>
                                                        <th class="px-4 py-2 text-center" style="width: 50px;"></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <tr v-for="(item, index) in form.combo_products" :key="index"
                                                        class="border-b last:border-0 hover:bg-gray-50">
                                                        <td class="px-4 py-2">
                                                            <div class="font-medium text-gray-900">{{ item.name }}</div>
                                                        </td>
                                                        <td class="px-4 py-2">
                                                            <TextInput type="number" min="1"
                                                                class="w-full text-center text-xs" v-model="item.qty"
                                                                @input="calculateComboTotals" />
                                                        </td>
                                                        <td class="px-4 py-2 text-right">
                                                            {{ item.purchase_price }}
                                                        </td>
                                                        <td class="px-4 py-2 text-right font-medium">
                                                            {{ ((parseFloat(item.qty) || 0) *
                                                                (parseFloat(item.purchase_price) || 0)).toFixed(2) }}
                                                        </td>
                                                        <td class="px-4 py-2 text-center">
                                                            <button type="button"
                                                                class="text-red-500 hover:text-red-700 transition"
                                                                @click="removeComboProduct(index)">
                                                                <i class="fa fa-trash"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                                <tfoot class="bg-gray-50 font-semibold">
                                                    <tr>
                                                        <td colspan="3" class="px-4 py-2 text-right">Total Purchase
                                                            Cost:</td>
                                                        <td class="px-4 py-2 text-right text-gray-900">
                                                            {{form.combo_products.reduce((acc, item) => acc +
                                                                ((parseFloat(item.qty) || 0) *
                                                                    (parseFloat(item.purchase_price) || 0)), 0).toFixed(2)}}
                                                        </td>
                                                        <td></td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 mb-3">
                                                <InputLabel for="combo_retail_price" value="Bundle Retail Price"
                                                    required />
                                                <TextInput id="combo_retail_price" type="number" step="0.01"
                                                    class="mt-1 block w-full" v-model="form.pro_r_price"
                                                    placeholder="Bundle retail price" required />
                                            </div>
                                            <!-- <div class="col-md-4 mb-3">
                                                <InputLabel for="combo_sku" value="Bundle SKU/Barcode" required />
                                                <TextInput 
                                                    id="combo_sku"
                                                    type="text" 
                                                    class="mt-1 block w-full"
                                                    v-model="form.pro_short_code"
                                                    placeholder="Bundle SKU/barcode"
                                                    required
                                                />
                                            </div> -->
                                            <div class="col-md-4 mb-3">
                                                <InputLabel for="combo_qty" value="Bundle Qty" required />
                                                <TextInput id="combo_qty" type="number" class="mt-1 block w-full"
                                                    v-model="form.pro_qty" placeholder="Bundle quantity" required />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Variation Input (when enabled) -->
                                <div v-if="showVariations" class="col-md-12 mb-4">
                                    <div class="border rounded-lg p-4 bg-gray-50">
                                        <h6 class="text-sm font-semibold text-gray-700 mb-4">Variation Configuration
                                        </h6>

                                        <!-- Variation Name Field -->
                                        <div class="mb-4">
                                            <InputLabel for="variation_name" value="Variation Name" required />
                                            <TextInput id="variation_name" type="text" class="mt-1 w-full max-w-md"
                                                v-model="form.variation_name" placeholder="e.g., Color, Size, Material"
                                                required />
                                            <p class="text-xs text-gray-500 mt-1">This will be used as the column header
                                                in the price table</p>
                                        </div>

                                        <!-- Include Image Checkbox -->
                                        <div class="mb-4">
                                            <label class="flex items-center space-x-2 cursor-pointer">
                                                <input type="checkbox"
                                                    class="form-checkbox h-4 w-4 text-blue-600 rounded border-gray-300 transition focus:ring-blue-600"
                                                    v-model="form.include_image" />
                                                <span class="text-sm font-medium text-gray-700">Include Image for each
                                                    variation</span>
                                            </label>
                                        </div>

                                        <!-- Variation Options -->
                                        <div class="space-y-3">
                                            <div v-for="(variation, index) in form.variation" :key="index"
                                                class="flex gap-3 items-start p-3 bg-white rounded-lg border border-gray-200">
                                                <!-- Image Upload (if include_image is checked) -->
                                                <div v-if="form.include_image" class="flex-shrink-0">
                                                    <ImageUpload v-model="variation.var_image" :isCover="false"
                                                        placeholderLabel="Image" />
                                                </div>

                                                <!-- Option Name -->
                                                <div class="flex-1">
                                                    <InputLabel :for="'var_name_' + index" class="mb-1"
                                                        value="Option Name" required />
                                                    <TextInput :id="'var_name_' + index" type="text" class="w-full"
                                                        v-model="variation.var_name"
                                                        placeholder="e.g., Black, White, Small, Large" required />
                                                </div>

                                                <!-- Delete Button -->
                                                <button type="button"
                                                    class="flex-shrink-0 mt-6 px-3 py-2 text-red-500 hover:text-red-700 hover:bg-red-50 rounded-md transition"
                                                    @click="removeRow(index)" title="Remove option">
                                                    <i class="fa fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Add Option Button -->
                                        <button type="button"
                                            class="mt-3 px-4 py-2 text-sm text-blue-600 hover:bg-blue-50 border border-blue-300 rounded-md transition"
                                            @click="addNewRow">
                                            <i class="fa fa-plus mr-1"></i> Add {{ form.variation_name }} Option
                                        </button>
                                    </div>
                                </div>

                                <!-- Price & Stock Table -->
                                <div class="col-md-12">
                                    <h6 class="mb-0 text-gray-800 mb-3">Price & Stock</h6>
                                    <div class="border rounded-lg overflow-hidden">
                                        <div class="overflow-x-auto">
                                            <table class="w-full text-sm">
                                                <thead class="bg-gray-50">
                                                    <tr class="border-b">
                                                        <th v-if="showVariations && form.variation.length > 0"
                                                            class="sticky left-0 bg-gray-50 px-3 py-2 text-left text-xs font-semibold text-gray-600 border-r z-10"
                                                            style="min-width: 150px;">
                                                            {{ form.variation_name }}
                                                        </th>
                                                        <th v-if="showVariations && form.variation.length > 0"
                                                            class="px-3 py-2 text-left text-xs font-semibold text-gray-600"
                                                            style="min-width: 120px;">
                                                            <span class="text-red-600">*</span> SKU
                                                            <Tooltip
                                                                text="SKU is a unique identifier for the product." />
                                                        </th>
                                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600"
                                                            style="min-width: 120px;" colspan="2">
                                                            Purchase Price <span class="text-red-600">*</span>
                                                            <br>
                                                            <div class="flex justify-between w-full">
                                                                <span class="text-xs text-gray-500">Exc. Tax</span>
                                                                <span class="text-xs text-gray-500">Inc. Tax</span>
                                                            </div>
                                                        </th>
                                                        <!-- <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600" style="min-width: 120px;">
                                                            Wholesale Price <span class="text-red-600">*</span>
                                                        </th> -->
                                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600"
                                                            style="min-width: 120px;">
                                                            Retail Price <span class="text-red-600">*</span>
                                                            <br>
                                                            <div class="flex justify-between w-full">
                                                                <span class="text-xs text-gray-500">{{
                                                                    form.selling_price_tax_type === 'inclusive' ?
                                                                        'Inc.Tax' : 'Exc.Tax' }}</span>
                                                            </div>
                                                        </th>
                                                        <th class="px-3 py-2 text-left text-xs font-semibold text-gray-600"
                                                            style="min-width: 100px;">
                                                            Quantity <span class="text-red-600">*</span>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <!-- Without Variations and Not Combo - Single Row -->
                                                    <tr v-if="(!showVariations && !showCombo) || form.variation.length === 0 && !showCombo"
                                                        class="border-b hover:bg-gray-50">
                                                        <!-- <td class="px-3 py-2">
                                                            <TextInput 
                                                                id="pro_short_code" 
                                                                type="number" 
                                                                class="w-full text-xs" 
                                                                v-model="form.pro_short_code"
                                                                placeholder="Enter SKU" 
                                                                required 
                                                            />
                                                        </td> -->
                                                        <td class="px-3 py-2">
                                                            <TextInput id="pro_pur_price" type="number" step="0.01"
                                                                class="w-full text-xs" v-model="form.pro_pur_price"
                                                                placeholder="0.00" required />
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <TextInput id="pro_pur_price_inc" type="number" step="0.01"
                                                                class="w-full text-xs" v-model="form.pro_pur_price_inc"
                                                                placeholder="0.00" required />
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <div class="relative">
                                                                <TextInput type="number" step="0.01"
                                                                    class="w-full text-xs" v-model="form.pro_r_price"
                                                                    placeholder="0.00" required />
                                                                <!-- <span class="text-[10px] text-gray-500 absolute -bottom-4 right-0">
                                                                    {{ form.selling_price_tax_type === 'inclusive' ? 'Inc. Tax' : 'Exc. Tax' }}
                                                                </span> -->
                                                            </div>
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <TextInput type="number" class="w-full text-xs"
                                                                v-model="form.pro_qty" placeholder="0" required />
                                                        </td>
                                                    </tr>

                                                    <!-- With Variations - Multiple Rows -->
                                                    <tr v-else v-for="(variation, index) in form.variation" :key="index"
                                                        class="border-b hover:bg-gray-50">
                                                        <td
                                                            class="sticky left-0 bg-white px-3 py-2 font-medium text-gray-900 border-r">
                                                            {{ variation.var_name || `Variation ${index + 1}` }}
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <TextInput type="text" class="w-full text-xs"
                                                                v-model="variation.var_sku" placeholder="SKU"
                                                                required />
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <TextInput type="number" step="0.01" class="w-full text-xs"
                                                                v-model="variation.var_purchase_price"
                                                                @input="updateVariationTax(index, 'exc')"
                                                                placeholder="0.00" required />
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <TextInput type="number" step="0.01" class="w-full text-xs"
                                                                v-model="variation.var_purchase_price_inc"
                                                                @input="updateVariationTax(index, 'inc')"
                                                                placeholder="0.00" required />
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <TextInput type="number" step="0.01" class="w-full text-xs"
                                                                v-model="variation.var_price" placeholder="0.00"
                                                                required />
                                                        </td>
                                                        <td class="px-3 py-2">
                                                            <TextInput type="number" class="w-full text-xs"
                                                                v-model="variation.var_qty" placeholder="0" required />
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-12 mt-3">
                                    <InputLabel for="pro_min_qty">
                                        Minimum Alert Qty <span class="text-red-600">*</span>
                                        <Tooltip
                                            text="Minimum Qty Alert that triggers an alert when the quantity level falls below this threshold." />
                                    </InputLabel>
                                    <TextInput id="pro_min_qty" type="number" class="mt-1 block w-full"
                                        v-model="form.pro_min_qty" placeholder="Enter Quantity" required />
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- Shipping Section -->
                    <div id="shipping" class="card shadow-sm border-0 mb-5 scroll-mt-20">
                        <div class="card-header bg-white py-3 border-b-0">
                            <h5 class="mb-0 text-gray-800 font-bold">Shipping</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Weight with Package -->
                                <div class="col-md-6 mb-4">
                                    <InputLabel for="weight" value="Weight With Package" required />
                                    <div class="flex gap-2 mt-1">
                                        <TextInput id="weight" type="number" step="0.01" class="flex-1"
                                            v-model="form.weight" placeholder="0.00" required />
                                        <Select v-model="form.weight_unit"
                                            class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                            style="width: 100px;" :options="units" />
                                    </div>
                                </div>

                                <!-- Product Dimensions -->
                                <div class="col-md-12 mb-4">
                                    <InputLabel for="dimensions" value="Product Dimensions (cm)" required />
                                    <div class="flex gap-2 mt-1">
                                        <div class="flex-1">
                                            <TextInput id="height" type="number" step="0.01" class="w-full"
                                                v-model="form.height" placeholder="Height" required />
                                        </div>
                                        <span class="flex items-center text-gray-400">×</span>
                                        <div class="flex-1">
                                            <TextInput id="width" type="number" step="0.01" class="w-full"
                                                v-model="form.width" placeholder="Width" required />
                                        </div>
                                        <span class="flex items-center text-gray-400">×</span>
                                        <div class="flex-1">
                                            <TextInput id="length" type="number" step="0.01" class="w-full"
                                                v-model="form.length" placeholder="Length" required />
                                        </div>
                                    </div>
                                </div>

                                <!-- Estimated Shipping Fee -->
                                <div class="col-md-12">
                                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                                        <div class="flex items-start">
                                            <i class="fas fa-info-circle text-blue-500 mt-1 mr-3"></i>
                                            <div>
                                                <h6 class="font-semibold text-gray-800 mb-1">
                                                    Estimated Shipping Fee:
                                                    <span class="text-blue-600">{{ businessSettings.currency + ' ' +
                                                        shippingFee.toFixed(2) }}</span>
                                                </h6>
                                                <p class="text-xs text-gray-600">
                                                    The shipping fee is calculated based on product weight and
                                                    dimensions.
                                                    Actual fees may vary depending on the shipping carrier and
                                                    destination.
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Sticky Footer / Submit (Optional but let's keep it near bottom or sticky) -->
                    <div class="fixed bottom-0 right-0 left-0 bg-white border-t p-4 z-50 text-right shadow-lg d-none">
                        <!-- Alternative if sticky footer desired, otherwise standard flow -->
                    </div>
                    <div class="text-right mb-5 pb-5">
                        <PrimaryButton class="btn btn-primary px-5 py-2" :disabled="form.processing || !isFormValid"
                            :class="{ 'opacity-50 cursor-not-allowed': form.processing || !isFormValid }">
                            Save Product
                        </PrimaryButton>
                    </div>

                </div>
            </div>
        </form>
    </AuthenticatedLayout>
</template>

<style scoped>
.sticky-sidebar {
    position: -webkit-sticky;
    position: sticky;
    top: 100px;
    /* Adjust based on your header height */
    z-index: 10;
    height: fit-content;
}

.scroll-mt-20 {
    scroll-margin-top: 20px;
}
</style>
