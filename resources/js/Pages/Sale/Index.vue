<script setup>
import { Head, useForm, router, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, nextTick } from "vue";
import TextInput from '@/Components/TextInput.vue';
import Popup from '@/Components/Popup.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Button from '@/Components/Button.vue';
import TextArea from '@/Components/TextArea.vue';
import Modal2 from '@/Components/Modal2.vue';
import SelectWithSearch from '@/Components/SelectWithSearch.vue';
import axios from "axios";
import InputLabel from '@/Components/InputLabel.vue';
import ProductSearch from '@/Components/ProductSearch.vue';
import TabNavigation from '@/Components/TabNavigation.vue';

const props = defineProps({
    accounts: {
        type: Array,
        required: true,
    },
    products: {
        type: Array,
        required: true,
    },
    categories: {
        type: Array,
        required: true,
    },
    brands: {
        type: Array,
        default: () => [],
    },
    flash: {
        type: Object,
        required: true,
    },
});

// Form state
const form = useForm({
    sale_day: new Date().toLocaleDateString('en-CA'),
    cust_id: '1',
    cat_id: '',
    brand_id: '',
    order_type: 'Dine_In',
    table_no: '1',
    gross: 0,
    discount: 0,
    order_tax: 0,
    shipping: 0,
    received: '',
    remaining: 0,
    return: 0,
    inv_status: 'Original',
    hid: null,
    pros: [],
});

const customerForm = useForm({
    acc_name: '',
    acc_type: 'Customer',
    acc_city: '',
    acc_address: '',
    acc_contact_per: '',
    acc_mobile: '',
    acc_email: '',
    acc_tel1: '',
    acc_tel2: '',
});

const nonForm = useForm({
    saleReturnInv: '',
});

const registerForm = useForm({
    closing_note: '',
});

const broadcastChannel = new BroadcastChannel('customer_display_channel');

// UI state
const isSaveAndPrint = ref(false);
const saleReturnInv = ref(null);
const isPopupOpen = ref(false);
const isCloseRegisterPopupOpen = ref(false);
const isRegisterPopupOpen = ref(false);
const popupRef = ref(null);
const saleTitle = ref("Sale Invoice");
const showShortcuts = ref(false);
const isFullscreen = ref(false);
const selectedLocation = ref("Main Store");
const allAccounts = ref([...props.accounts]);
const activeTab = ref('final');
const invoice_status = ref(null);

// Modals
const showDiscountModal = ref(false);
const showTaxModal = ref(false);
const showShippingModal = ref(false);
const tempDiscount = ref(0);
const tempTax = ref(0);
const tempShipping = ref(0);

// Computed properties
const hasItems = computed(() => form.pros.length > 0);
const canSubmit = computed(() => hasItems.value && form.remaining === 0);
const formattedGross = computed(() => Number(form.gross).toFixed(2));
const formattedRemaining = computed(() => Number(form.remaining).toFixed(2));
const formattedReturn = computed(() => Number(form.return).toFixed(2));
const totalItems = computed(() => form.pros.reduce((sum, item) => sum + item.qty, 0));
const subtotal = computed(() => form.pros.reduce((sum, item) => sum + item.item_total, 0));
const finalTotal = computed(() => {
    const sub = subtotal.value;
    const disc = Number(form.discount) || 0;
    const tax = Number(form.order_tax) || 0;
    const ship = Number(form.shipping) || 0;
    return sub - disc + tax + ship;
});

// Tab configuration
const tabs = [
    { id: 'final', label: 'Final', icon: 'fas fa-check' },
    { id: 'quotation', label: 'Quotation', icon: 'fas fa-quote-left' },
    { id: 'draft', label: 'Draft', icon: 'fas fa-file' },
    { id: 'suspend', label: 'Suspend', icon: 'fas fa-pause' }
];

// Format customer options for SelectWithSearch
const customerOptions = computed(() => {
    return allAccounts.value.map(account => ({
        value: account.id,
        label: account.name
    }));
});

const categoryOptions = computed(() => {
    return props.categories.map(category => ({
        value: category.id,
        label: category.name
    }));
});

const brandOptions = computed(() => {
    return props.brands.map(brand => ({
        value: brand.id,
        label: brand.name
    }));
});

const displayedProducts = computed(() => {
    let result = [];
    
    props.products.forEach(product => {
        // If product has variations, show ONLY variations
        if (product.variations && product.variations.length > 0) {
            product.variations.forEach(variation => {
                const productName = product.name || '';
                const variationName = product.variation_name || ''; 
                const variationValue = variation.var_name || ''; 
                // const barcodeFormat = product.barcode +"-"+variation.var_sku || ''; 
                 
                result.push({
                    unique_key: `var-${variation.id}`,
                    id: variation.id, // This might not represent the product ID expected by backend but the specific variation ID/barcode matters for adding row
                    barcode: variation.var_sku || '',
                    // barcodeDisplay: barcodeFormat, 
                    name: `${productName} (${variationValue})`,
                    nameFormat: `${productName} (${variationName}:${variationValue}-(${variation.var_sku})) `,
                    price: Number(variation.var_retail_price || 0),
                    image: product.image_uri1, 
                    qty: variation.var_qty || 0,
                    unit: product.weight_unit
                });
            });
        } 
        else {
            // Standard Product (including ones being combos themselves)
            result.push({
                unique_key: `prod-${product.id}`,
                id: product.id,
                barcode: product.barcode,
                // barcodeDisplay: product.barcode || '',
                name: product.name,
                nameFormat: product.name +" ("+product.barcode+")",
                price: Number(product.retail || 0),
                image: product.image_uri1,
                qty: product.qty,
                unit: product.weight_unit
            });
        }
    });

    return result;
});

const accountTypes = [
    { value: 'Asset', label: 'Asset' },
    { value: 'Capital', label: 'Capital' },
    { value: 'Customer', label: 'Customer' },
    { value: 'Expense', label: 'Expense' },
    { value: 'Liability', label: 'Liability' },
    { value: 'Vendor', label: 'Vendor' },
];

// Product management
const addNewRow = (id, name, price, index, item_qty = 1) => {
    const existingIndex = form.pros.findIndex(item => item.id === id);
    
    if (existingIndex !== -1) {
        const currentQty = Number(form.pros[existingIndex].qty);
        const newQty = index === 'pre' ? Number(item_qty) : currentQty + 1;
        
        form.pros[existingIndex] = {
            id,
            name,
            price: Number(price),
            qty: newQty,
            item_total: newQty * Number(price),
        };
    } else {
        const qty = index === 'pre' ? Number(item_qty) : 1;
        form.pros.push({
            id,
            name,
            price: Number(price),
            qty,
            item_total: qty * Number(price),
        });
    }
    
    calculateTotals();
};

const removeRow = (index) => {
    form.pros.splice(index, 1);
    calculateTotals();
    
    if (form.pros.length === 0) {
        resetSaleState();
    }
};

const clearRow = () => {
    form.pros = [];
    resetSaleState();
    clearFields();
    calculateTotals(); // Broadcast update
};

const resetSaleState = () => {
    nonForm.saleReturnInv = "";
    // saleTitle.value = "Sale Invoice";
};

const clearFields = () => {
    form.gross = 0;
    form.discount = 0;
    form.order_tax = 0;
    form.shipping = 0;
    form.received = '';
    form.remaining = 0;
    form.return = 0;
    form.hid = null;
    invoice_status.value = null;
};

// Calculations
const calculateTotals = () => {
    const sub = form.pros.reduce((sum, item) => sum + item.item_total, 0);
    const disc = Number(form.discount) || 0;
    const tax = Number(form.order_tax) || 0;
    const ship = Number(form.shipping) || 0;
    
    form.gross = sub - disc + tax + ship;
    
    const received = Number(form.received) || 0;
    const difference = form.gross - received;
    
    form.remaining = difference > 0 ? difference : 0;
    form.return = received > 0 ? Math.abs(Math.min(difference, 0)) : 0;
    
    if (form.gross === 0) {
        form.hid = null;
    }
    
    // Broadcast to Customer Display
    if (broadcastChannel) {
        broadcastChannel.postMessage({
            type: 'UPDATE_CART',
            data: {
                items: JSON.parse(JSON.stringify(form.pros)),
                totals: {
                    gross: form.gross,
                    discount: form.discount,
                    tax: form.order_tax,
                    shipping: form.shipping,
                    received: form.received,
                    remaining: form.remaining,
                    change: form.return,
                    subtotal: sub.toFixed(2),
                    finalTotal: (sub - disc + tax + ship).toFixed(2),
                    totalItems: form.pros.reduce((sum, item) => sum + item.qty, 0)
                }
            }
        });
    }
};

// Discount, Tax, Shipping Modals
const openDiscountModal = () => {
    tempDiscount.value = form.discount;
    showDiscountModal.value = true;
};

const applyDiscount = () => {
    form.discount = Number(tempDiscount.value) || 0;
    calculateTotals();
    showDiscountModal.value = false;
};

const openTaxModal = () => {
    tempTax.value = form.order_tax;
    showTaxModal.value = true;
};

const applyTax = () => {
    form.order_tax = Number(tempTax.value) || 0;
    calculateTotals();
    showTaxModal.value = false;
};

const openShippingModal = () => {
    tempShipping.value = form.shipping;
    showShippingModal.value = true;
};

const applyShipping = () => {
    form.shipping = Number(tempShipping.value) || 0;
    calculateTotals();
    showShippingModal.value = false;
};

// Form submission
const submit = async (from) => {
    if (!hasItems.value) {
        alert('Please add at least one item to the sale.');
        return;
    }

    try {
        form.inv_status = from;
        form.received = ((from == 'card') || (from == 'cash') ? form.gross: 0);
        
        const route_name = nonForm.saleReturnInv ? 'saveSaleReturn' : 'saveInvoice2';
        // alert(route_name);
        // alert(form.hid);
        await axios.post(route(route_name), form).then(response => {
            // if (isSaveAndPrint.value && !saleReturnInv.value) {
            //     showPrintWindow(response.data.id);
            //     isSaveAndPrint.value = false;
            // }
            showPrintWindow(response.data.id);
            console.log('Sale saved successfully');
            clearRow();
        }).catch(error => {
            console.error('Error while saving sale:', error);
            alert('Failed to save sale. Please try again.');
        });
        
        // saleTitle.value = "Sale Invoice";
    } catch (error) {
        console.error('Error while saving sale:', error);
        alert('Failed to save sale. Please try again.');
    }
};

// Category and Brand filter
const getProduct = (id, from) => {
    let finalCatId = 'all';
    let finalBrandId = 'all';
    
    if(from == 'cat'){
        finalCatId = id || 'all';
    }
    else if(from == 'brand'){
        finalBrandId = id || 'all';
    }

    router.get('saleForm2', { cat_id: finalCatId, brand_id: finalBrandId }, { 
        preserveState: true, 
        replace: true 
    });
};

// Print functionality
let printTimeout;
const showPrintWindow = (id) => {
    if (printTimeout) clearTimeout(printTimeout);
    
    // Check if we were in fullscreen before printing
    const wasFullscreen = !!document.fullscreenElement;
    
    printTimeout = setTimeout(() => {
        if (id) {
            // Create a hidden iframe for direct printing (faster, no new window)
            // The print page has onload="window.print()" so it will auto-print
            const iframe = document.createElement('iframe');
            iframe.style.position = 'fixed';
            iframe.style.right = '0';
            iframe.style.bottom = '0';
            iframe.style.width = '0';
            iframe.style.height = '0';
            iframe.style.border = 'none';
            iframe.src = `/salePrint/${id}`;
            
            document.body.appendChild(iframe);
            
            // Handle after print event (browser support varies)
            iframe.contentWindow.addEventListener('afterprint', () => {
                // If we were fullscreen and lost it, try to restore (might fail without user gesture)
                if (wasFullscreen && !document.fullscreenElement) {
                    try {
                        document.documentElement.requestFullscreen().catch(() => {
                            // If auto-restore fails (likely), update our state to reflect reality
                            isFullscreen.value = false;
                        });
                    } catch (e) {
                         isFullscreen.value = false;
                    }
                }
                
                // Cleanup
                setTimeout(() => {
                    if (document.body.contains(iframe)) {
                         document.body.removeChild(iframe);
                    }
                }, 500);
            });

            // Wait for content to load - it will auto-print via onload="window.print()"
            iframe.onload = function() {
                try {
                    // The page will print itself, just clean up after a delay
                    setTimeout(() => {
                        // We don't focus() here anymore to avoid stealing focus/breaking fullscreen
                        // iframe.contentWindow.print(); // already handled by page onload
                        
                        // Fallback cleanup if afterprint doesn't fire
                        setTimeout(() => {
                            if (document.body.contains(iframe)) {
                                document.body.removeChild(iframe);
                            }
                        }, 5000); // Long timeout for user interaction with print dialog
                    }, 100);
                } catch (error) {
                    console.error('Print error:', error);
                    if (document.body.contains(iframe)) {
                        document.body.removeChild(iframe);
                    }
                }
            };
            
            // Fallback: remove iframe if it fails to load
            iframe.onerror = function() {
                console.error('Failed to load print content');
                if (document.body.contains(iframe)) {
                    document.body.removeChild(iframe);
                }
            };
        }
    }, 500);
};

const showSeparatePrintWindow = (id) => {
    window.open('/salePrint/' + id, 'popUpWindow', 'height=1000,width=1000,left=50,top=50,resizable=yes,scrollbars=yes,toolbar=yes,menubar=no,location=no,directories=no, status=yes');
};

const openCustomerDisplayWindow = () => {
    window.open(
                `/customerDisplay`, 
                'customerDisplayWindow', 
                'height=1200,width=1200,left=100,top=100,resizable=yes,scrollbars=yes'
            );
};

// Quantity modal
const quantityModalData = ref({
    id: '',
    name: '',
    price: 0,
    qty: 1,
});

const openQuantityModal = (id, name, price) => {
    quantityModalData.value = { id, name, price, qty: 1 };
    $('#modal_qty').modal('show');
};

const incrementQuantity = () => {
    quantityModalData.value.qty++;
};

const decrementQuantity = () => {
    if (quantityModalData.value.qty > 1) {
        quantityModalData.value.qty--;
    }
};

const updateQuantity = () => {
    const { id, name, price, qty } = quantityModalData.value;
    const existingIndex = form.pros.findIndex(item => item.id === id);
    
    if (existingIndex !== -1) {
        form.pros[existingIndex] = {
            id,
            name,
            price: Number(price),
            qty: Number(qty),
            item_total: Number(qty) * Number(price),
        };
        calculateTotals();
    }
    
    quantityModalData.value = { id: '', name: '', price: 0, qty: 1 };
    $('#modal_qty').modal('hide');
};

// Pre-sales (paused sales)
const openModalPausedSale = async () => {
    try {
        const response = await axios.get("saleForm4");
        const tableBody = document.getElementById("pre_sale_items");
        
        clearRow();
        tableBody.innerHTML = '';
        
        if (response.data && response.data.data !== '404' && response.data.length > 0) {
            response.data.forEach(item => {
                const row = tableBody.insertRow();
                row.innerHTML = `
                    <td>${item.table_no}</td>
                    <td>${new Date(item.day).toLocaleDateString("en-GB")}</td>
                    <td>${item.finalVal}</td>
                    <td>
                        <a href="#" onclick="loadSales(${item.id}, 'pre')" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i>
                        </a>
                    </td>
                    <td>${item.id}</td>
                `;
            });
        } else {
            tableBody.innerHTML = '<tr><td colspan="5" class="text-center">No paused sales found</td></tr>';
        }
        
        $('#modal_pasued_sale').modal('show');
    } catch (error) {
        console.error('Error fetching paused sales:', error);
        alert("Error fetching paused sales");
    }
};

// Close Register Logic
const closeRegisterData = ref({
    start_time: '12/01/2026 08:00:00',
    end_time: new Date().toLocaleString(),
    total_sales: 5000.00,
    total_refund: 200.00,
    total_payment: 4800.00,
    credit_sales: 500.00,
    total_expense: 150.00,
    total_cash: 2500.00,
    total_card_slips: 1500.00,
    total_cheques: 800.00,
    payment_methods: [
        { method: 'Cash', sell: 2500.00, expense: 150.00 },
        { method: 'Card', sell: 1500.00, expense: 0.00 },
        { method: 'Cheque', sell: 800.00, expense: 0.00 },
        { method: 'Other', sell: 200.00, expense: 0.00 },
    ],
    product_details: [
        { name: 'Burger', qty: 10, total: 500.00 },
        { name: 'Pizza', qty: 5, total: 250.00 },
        { name: 'Coke', qty: 2, total: 1000.00 },
    ],
    brand_details: [
        { name: 'Brand X', qty: 12, total: 1200.00 },
        { name: 'Brand Y', qty: 5, total: 550.00 },
    ],
    cash_denominations: {
        '5000': 0, '1000': 0, '500': 0, '100': 0, '50': 0, '20': 0, '10': 0
    },
    closing_note: '',
    user: {
        name: 'Admin User',
        email: 'admin@pos.com',
        location: 'Main Store'
    }
});

// Close Register
const openCloseRegisterModal = async () => {
    try {
        await axios.get("getRegisterDetails")
        .then(response => {
            // Update only the fields returned by backend, preserving local state like cash_denominations
            const backendData = response.data;
            closeRegisterData.value = {
                ...closeRegisterData.value,
                ...backendData,
                // Ensure payment methods array matches structure if needed, currently it does.
            };
            $('#modal_close_register').modal('show');
        })
        .catch(err => {
            console.log(err)
        });
    } catch (error) {
        console.error('Error fetching close register data:', error);
        alert("Error fetching data");
    }
};

// Register Information
const openRegisterInformationModal = async () => {
    try {
        const response = await axios.get("getRegisterDetails");
        
        if (response.data) {
            // Update only the fields returned by backend, preserving local state like cash_denominations
            const backendData = response.data;
            closeRegisterData.value = {
                ...closeRegisterData.value,
                ...backendData,
                // Ensure payment methods array matches structure if needed, currently it does.
            };
        }
        
        $('#modal_register_information').modal('show');
    } catch (error) {
        console.error('Error fetching close register data:', error);
        alert("Error fetching data");
    }
};

// Add Expense 
const openAddExpenseModal = () => {
    customerForm.cust_id = 'Expense';
    $('#modal_add_expense').modal('show');
};

// Add Customer 
const openAddCustomerModal = () => {
    customerForm.cust_id = 'Customer';
    $('#modal_add_expense').modal('show');
};

const recentInvoiceData = ref({
    invoices: []
});

const filteredInvoices = computed(() => {
    if (!recentInvoiceData.value.invoices) return [];
    
    // Mapping tabs to statuses
    const statusMap = {
        'final': ['Original', 'cash', 'card', 'credit'],
        'quotation': ['quotation'],
        'draft': ['draft'],
        'suspend': ['suspend']
    };
    
    const allowedStatuses = statusMap[activeTab.value] || [];
    
    return recentInvoiceData.value.invoices.filter(inv => allowedStatuses.includes(inv.inv_status));
});

// View Recent Invoices
const openRecentModal = async () => {
    try {
        const response = await axios.get("getRecentInvoiceDetails");
        
        if (response.data) {
            // Update only the fields returned by backend, preserving local state like cash_denominations
            const backendData = response.data;
            recentInvoiceData.value = {
                ...recentInvoiceData.value,
                ...backendData,
            };
            $('#modal_recent').modal('show');
        }
        
        
    } catch (error) {
        console.error('Error fetching close register data:', error);
        alert("Error fetching data");
    }
};

// Load saved sale
const loadSales = async (id, from) => {
    try {
        const response = await axios.get(route('saleForm5', [id, from]));
        
        response.data.forEach(item => {
            const price = Number(item.price || 0);
            let name = item.name +' ('+ item.barcode +')' ;

            // if (item.var_sku) {
            //     const productName = item.name || '';
            //     const variationName = item.variation_name || '';
            //     const variationValue = item.var_name || '';
            //     const barcode = item.barcode || item.var_sku;
                
            //     name = `${productName}-${variationName}:${variationValue} (${barcode})`;
            // }
            addNewRow(item.barcode, name, price, 'pre', item.qty);
        });
        
        form.hid = id;
        invoice_status.value = from;
        
        // if (from !== "return") {
        //     $('#modal_pasued_sale').modal('hide');
        //     saleTitle.value = "Edit Sale Invoice";
        // }
        $('#modal_recent').modal('hide');

    } catch (error) {
        console.error('Error loading sale:', error);
        alert("No Record Found!");
        // saleTitle.value = "Sale Invoice";
    }
};
// Expose to window if needed
window.loadSales = loadSales;

// Sale return
const handlePopup = (name) => {
    if(name == 'SR'){
        isPopupOpen.value = !isPopupOpen.value;
    }
    else if(name == 'CR'){
        isCloseRegisterPopupOpen.value = !isCloseRegisterPopupOpen.value;
    }
    else if(name == 'RI'){
        isRegisterPopupOpen.value = !isRegisterPopupOpen.value;
    }
    
};

const openModalSaleReturn = async () => {
    if (!nonForm.saleReturnInv) {
        alert('Please enter an invoice number');
        return;
    }
    
    try {
        await loadSales(nonForm.saleReturnInv, "return");
        form.hid = nonForm.saleReturnInv;
        handlePopup('SR');
        invoice_status.value = "Sale Return";
        // clearRow();
    } catch (error) {
        console.error('Error loading sale return:', error);
    }
};

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

// Keyboard shortcuts
const handleKeyboardShortcut = (event) => {
    if (event.ctrlKey || event.altKey) {
        switch(event.key.toLowerCase()) {
            case 'n':
                event.preventDefault();
                clearRow();
                break;
            case 's':
                event.preventDefault();
                submit();
                break;
            case 'p':
                event.preventDefault();
                isSaveAndPrint.value = true;
                submit();
                break;
            case 'h':
                event.preventDefault();
                openModalPausedSale();
                break;
        }
    }
};

const calculateCashTotal = computed(() => {
    let total = 0;
    for (const [denom, count] of Object.entries(closeRegisterData.value.cash_denominations)) {
        total += Number(denom) * Number(count);
    }
    return total;
});

// Initialize on mount
onMounted(() => {
    getProduct('all');
    window.addEventListener('keydown', handleKeyboardShortcut);
    // Update end time periodically for mock
    setInterval(() => {
        closeRegisterData.value.end_time = new Date().toLocaleString();
    }, 60000);
});

onUnmounted(() => {
    if (printTimeout) clearTimeout(printTimeout);
    window.removeEventListener('keydown', handleKeyboardShortcut);
    if (broadcastChannel) broadcastChannel.close();
});

// Add product to the table
const addProduct = (product) => {
    const price = Number(product.retail || product.price || 0);
    let name = product.name +' ('+ product.barcode +')' ;

    if (product._isSelectedVariation) {
        const parent = product._parent;
        const productName = parent.name || '';
        const variationName = parent.variation_name || '';
        const variationValue = product.var_name || '';
        const barcode = product.barcode || '';
        
        name = `${productName} (${variationName}:${variationValue}-(${barcode})) `;
    }
    
    addNewRow(product.barcode, name, price);
};

const submitCustomer = () => {
    customerForm.processing = true;
    axios.post(route('insertAccountApi'), customerForm.data())
        .then(response => {
            const newCustomer = response.data;
            if (newCustomer && newCustomer.id) {
                // Add to local list if not already present
                if (!allAccounts.value.find(c => c.id == newCustomer.id)) {
                    allAccounts.value.push(newCustomer);
                }
                // Automatically select the newly created customer
                nextTick(() => {
                   form.cust_id = newCustomer.id;
                });
                
                // Close modal and reset form
                $('#modal_add_expense').modal('hide');
                customerForm.reset();
            }
        })
        .catch(error => {
            console.error("Error saving customer:", error);
            // You could handle validation errors here if the API returns them
        })
        .finally(() => {
            customerForm.processing = false;
        });
};

const submitRegister = () => {
    registerForm.processing = true;
    axios.post(route('updateRegister'), registerForm.data())
        .then(response => {
            if (response.data.success) {
                $('#modal_close_register').modal('hide');
                router.visit(route('home'));
            }
        })
        .catch(error => {
            alert('Error saving register');
            console.error("Error saving register:", error);
            // You could handle validation errors here if the API returns them
        })
        .finally(() => {
            registerForm.processing = false;
        });
};

const deleteInvoice = async (id) => {
    await axios.delete(route('deleteInvoiceApi', id))
    .then(response => {
        if (response.data.success) {
            alert(response.data.message);
            $("#modal_recent").modal("hide");
        }
    })
    .catch(error => {
        console.error("Error deleting invoice:", error);
        alert('Error deleting invoice');
    });
    

};

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

</script>

<style scoped>
/* Shortcuts tooltip */
.shortcuts-tooltip {
    position: absolute;
    top: 100%;
    left: 0;
    margin-top: 0.5rem;
    background: white;
    color: #333;
    padding: 1rem;
    border-radius: 0.5rem;
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    min-width: 250px;
    z-index: 1000;
}

/* Scrollbar Styling */
::-webkit-scrollbar {
    width: 8px;
    height: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
}

::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #555;
}

.product-icon {
    width: 50px;
    height: 40px;
    background: linear-gradient(135deg, #667eea20 0%, #764ba220 100%);
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #667eea;
}
</style>

<template>
    <Head title="Sale" />
    
    <!-- Main Container -->
    <div class="flex flex-col h-screen overflow-hidden bg-gray-100 p-2 gap-4">
        <!-- Top Toolbar -->
        <div class="bg-white px-2 py-2 flex items-center gap-2 flex-wrap shadow-md rounded-xl z-50">
            <div class="flex items-center gap-2 text-sm font-medium px-3 py-2 bg-gray-50 rounded-md">
                <i class="fas fa-map-marker-alt text-gray-700"></i>
                <span class="text-gray-700">{{ selectedLocation }}</span>
            </div>
            
            <div class="relative flex items-center gap-2 text-sm font-medium px-3 py-2 bg-gray-50 rounded-md" 
                 @mouseenter="showShortcuts = true" 
                 @mouseleave="showShortcuts = false">
                <i class="far fa-calendar-alt text-gray-700"></i>
                <span class="text-gray-700">{{ new Date().toLocaleDateString('en-GB') }}</span>
                <i class="fas fa-keyboard text-gray-700"></i>
                
                <div v-if="showShortcuts" class="shortcuts-tooltip">
                    <h4 class="text-sm font-semibold mb-3 text-indigo-600">Keyboard Shortcuts</h4>
                    <div class="flex justify-between py-1.5 text-xs">
                        <span>New Sale</span>
                        <span class="bg-gray-100 px-2 py-0.5 rounded font-mono font-semibold">Ctrl+N</span>
                    </div>
                    <div class="flex justify-between py-1.5 text-xs">
                        <span>Save</span>
                        <span class="bg-gray-100 px-2 py-0.5 rounded font-mono font-semibold">Ctrl+S</span>
                    </div>
                    <div class="flex justify-between py-1.5 text-xs">
                        <span>Print</span>
                        <span class="bg-gray-100 px-2 py-0.5 rounded font-mono font-semibold">Ctrl+P</span>
                    </div>
                    <div class="flex justify-between py-1.5 text-xs">
                        <span>Suspended Sales</span>
                        <span class="bg-gray-100 px-2 py-0.5 rounded font-mono font-semibold">Ctrl+H</span>
                    </div>
                </div>
            </div>
            
            <Link :href="route('home')" 
                  class="bg-white/20 border border-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm cursor-pointer transition-all duration-200 flex items-center gap-2 hover:bg-gray-50 hover:-translate-y-0.5 hover:shadow-md" 
                  title="Go Back">
                <i class="fas fa-arrow-left"></i>
            </Link>
            
            <Button @click="openCloseRegisterModal"
                    class="bg-white/20 border border-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm cursor-pointer transition-all duration-200 flex items-center gap-2 hover:bg-gray-50 hover:-translate-y-0.5 hover:shadow-md" 
                    title="Close Register">
                <i class="fas fa-cash-register"></i>
            </Button>
            
            <Button @click="openRegisterInformationModal"
                    class="bg-white/20 border border-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm cursor-pointer transition-all duration-200 flex items-center gap-2 hover:bg-gray-50 hover:-translate-y-0.5 hover:shadow-md" 
                    title="Register Details">
                <i class="fas fa-info-circle"></i>
            </Button>
            
            <!-- <Button class="bg-white/20 border border-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm cursor-pointer transition-all duration-200 flex items-center gap-2 hover:bg-gray-50 hover:-translate-y-0.5 hover:shadow-md" 
                    title="Calculator">
                <i class="fas fa-calculator"></i>
            </Button> -->
            
            <Button @click="handlePopup('SR')" 
                    class="bg-white/20 border border-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm cursor-pointer transition-all duration-200 flex items-center gap-2 hover:bg-gray-50 hover:-translate-y-0.5 hover:shadow-md" 
                    title="Sale Return">
                <i class="fas fa-undo"></i>
            </Button>
            
            <Button @click="toggleFullscreen" 
                    class="bg-white/20 border border-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm cursor-pointer transition-all duration-200 flex items-center gap-2 hover:bg-gray-50 hover:-translate-y-0.5 hover:shadow-md" 
                    title="Fullscreen">
                <i :class="isFullscreen ? 'fas fa-compress' : 'fas fa-expand'"></i>
            </Button>
            
            <!-- <Button @click="openModalPausedSale" 
                    class="bg-white/20 border border-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm cursor-pointer transition-all duration-200 flex items-center gap-2 hover:bg-gray-50 hover:-translate-y-0.5 hover:shadow-md" 
                    title="View Suspended Sales">
                <i class="fas fa-pause-circle"></i>
            </Button> -->
            
            <Button @click="openCustomerDisplayWindow"
                class="bg-white/20 border border-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm cursor-pointer transition-all duration-200 flex items-center gap-2 hover:bg-gray-50 hover:-translate-y-0.5 hover:shadow-md" 
                title="Customer Display">
                <i class="fas fa-desktop"></i>
            </Button>
            
            <!-- <Button @click="openAddExpenseModal"
                    class="bg-white/20 border border-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm cursor-pointer transition-all duration-200 flex items-center gap-2 hover:bg-gray-50 hover:-translate-y-0.5 hover:shadow-md" 
                    title="Add Expense">
                <i class="fas fa-money-bill-wave"></i>
                <span>Add Expense</span>
            </Button> -->

            <div v-if="invoice_status" class="bg-blue-700 border border-gray-200 text-white font-bold px-4 py-2 rounded-md text-sm flex items-center gap-2">
                <span>Invoice: {{ invoice_status }}</span>
            </div>
        </div>
        
        <!-- Main Content -->
        <div class="flex-1 flex overflow-hidden gap-4">
            <!-- Left Section - Selected Items -->
            <div class="bg-white gap-2 shadow-md rounded-xl flex-[0_0_55%] flex flex-col">
                <div class="py-3 px-4">
                    <span v-if="form.hid" class="text-sm">Invoice No: {{ form.hid }}</span>
                    <div class="flex gap-3 items-center">
                        <div class="flex-[0_0_50%] flex gap-2">
                            <Button @click="openAddCustomerModal"
                                    class="bg-white/20 border border-gray-200 text-gray-700 px-2 py-1 rounded-md text-sm cursor-pointer transition-all duration-200 flex items-center gap-2 hover:bg-gray-50 hover:-translate-y-0.5 hover:shadow-md" 
                                    title="Add New Customer">
                                <i class="fas fa-plus"></i>
                            </Button>
                            <SelectWithSearch
                                v-model="form.cust_id"
                                :options="customerOptions"
                                placeholder="-- Select Customer --"
                                search-placeholder="Search customers..."
                                class="flex-1 text-sm"
                            />
                        </div>
                        
                        <div class="flex-1">
                            <ProductSearch
                                :products="products"
                                @select="addProduct"
                                autofocus
                            />
                        </div>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-0">
                    <table class="w-full border-collapse">
                        <thead class="sticky top-0 bg-gray-50 z-10">
                            <tr>
                                <th class="px-4 py-1 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-b-2 border-gray-200">Product</th>
                                <th class="px-4 py-1 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-b-2 border-gray-200 w-24">Quantity</th>
                                <th class="px-4 py-1 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-b-2 border-gray-200 w-28">Price</th>
                                <th class="px-4 py-1 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-b-2 border-gray-200 w-28">Subtotal</th>
                                <th class="px-4 py-1 text-left text-xs font-semibold text-gray-600 uppercase tracking-wide border-b-2 border-gray-200 w-16"></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(pro, index) in form.pros" 
                                :key="index"
                                class="border-b border-gray-100 transition-colors duration-150 hover:bg-gray-50">
                                <td class="px-1 py-1 text-sm text-gray-700">{{ pro.name }}</td>
                                <td class="px-1 py-1">
                                    <TextInput type="number" 
                                           class="w-16 px-2 py-1 text-center"
                                           v-model="pro.qty"
                                           @input="() => { pro.item_total = pro.qty * pro.price; calculateTotals(); }"
                                           min="1" />
                                </td>
                                <td class="px-1 py-1 text-sm text-gray-700">{{ pro.price.toFixed(2) }}</td>
                                <td class="px-1 py-1 text-sm text-gray-700">{{ pro.item_total.toFixed(2) }}</td>
                                <td class="px-1 py-1">
                                    <i class="fas fa-trash text-red-500 cursor-pointer transition-all duration-200 hover:text-red-600 hover:scale-125" 
                                       @click="removeRow(index)"></i>
                                </td>
                            </tr>
                            <tr v-if="!hasItems">
                                <td colspan="5" class="text-center py-12 text-gray-400">
                                    <i class="fas fa-shopping-cart text-5xl mb-4 opacity-30"></i>
                                    <p>No items added yet</p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="bg-gray-50 border-t-2 border-gray-200 grid grid-cols-3 gap-0">
                    <!-- First Row: Items and Total -->
                    <div class="px-2 col-span-3 grid grid-cols-2 gap-4 border-b border-gray-200 pb-0 mb-1">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium text-sm">Items:</span>
                            <span class="font-bold text-lg text-indigo-600">{{ totalItems }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-600 font-medium text-sm">Total:</span>
                            <span class="font-bold text-lg text-indigo-600">{{ finalTotal.toFixed(2) }}</span>
                        </div>
                    </div>
                    
                    <!-- Second Row: Discount, Tax, Shipping -->
                    <div class="px-2 flex justify-between items-center p-0 py-0 text-sm">
                        <span class="text-gray-600 font-medium">Discount:</span>
                        <span class="font-semibold text-gray-900 flex items-center gap-2">
                            {{ form.discount.toFixed(2) }}
                            <i class="fas fa-edit text-indigo-600 cursor-pointer text-sm transition-all duration-200 hover:text-indigo-700 hover:scale-125" 
                               @click="openDiscountModal"></i>
                        </span>
                    </div>
                    <div class="px-2 flex justify-between items-center p-0 py-0 text-sm">
                        <span class="text-gray-600 font-medium">Order Tax:</span>
                        <span class="font-semibold text-gray-900 flex items-center gap-2">
                            {{ form.order_tax.toFixed(2) }}
                            <i class="fas fa-edit text-indigo-600 cursor-pointer text-sm transition-all duration-200 hover:text-indigo-700 hover:scale-125" 
                               @click="openTaxModal"></i>
                        </span>
                    </div>
                    <div class="px-2 flex justify-between items-center p-0 py-0 text-sm">
                        <span class="text-gray-600 font-medium">Shipping:</span>
                        <span class="font-semibold text-gray-900 flex items-center gap-2">
                            {{ form.shipping.toFixed(2) }}
                            <i class="fas fa-edit text-indigo-600 cursor-pointer text-sm transition-all duration-200 hover:text-indigo-700 hover:scale-125" 
                               @click="openShippingModal"></i>
                        </span>
                    </div>
                </div>
            </div> 

            <!-- Right Section - Products -->
            <div class="bg-white p-1 gap-2 shadow-md rounded-xl flex-[0_0_45%] flex flex-col">
                <div class="p-2">
                    <div class="row">
                        <div class="col-md-6">
                            <SelectWithSearch
                                v-model="form.cat_id"
                                @change="getProduct($event, 'cat')"
                                :options="categoryOptions"
                                placeholder="-- Select Category --"
                                search-placeholder="Search categories..."
                                class="w-full text-sm"
                            />
                        </div>
                        <div class="col-md-5">
                            <SelectWithSearch
                                v-model="form.brand_id"
                                @change="getProduct($event, 'brand')"
                                :options="brandOptions"
                                placeholder="-- Select Brand --"
                                search-placeholder="Search brands..."
                                class="w-full text-sm"
                            />
                        </div>
                    </div>
                </div>
                
                <div class="flex-1 overflow-y-auto px-3 bg-white">
                    <div class="grid grid-cols-4 gap-3">
                        <div v-for="item in displayedProducts" 
                             :key="item.unique_key"
                             class="bg-white border border-gray-200 rounded-lg p-2.5 cursor-pointer transition-all duration-200 text-center min-h-[70px] flex flex-col justify-center items-center hover:-translate-y-1 hover:shadow-lg hover:shadow-indigo-100 hover:border-indigo-500"
                             @click="addNewRow(item.barcode, item.nameFormat, item.price)">
                            <img
                                v-if="item.image"
                                :src="`/storage/${item.image}`"
                                alt="Product Image" 
                                class="w-11 h-11 object-cover rounded shadow-sm border border-gray-200"
                            />
                            <div v-else class="flex justify-center product-icon"><i class="fas fa-cube fa-xl"></i></div>
                            <div class="text-xs font-semibold text-gray-900 mb-1.5 leading-tight overflow-hidden text-ellipsis line-clamp-2">
                                {{ item.name }}
                            </div>
                            <div class="text-xs text-gray-600">
                                {{ '(' + item.barcode + ')' }}
                                <br>
                                {{ item.qty + ' ' + ('Pcs') + ' in stock' }}
                            </div>
                        </div>
                        
                        <div v-if="displayedProducts.length === 0" class="col-span-4 text-center py-12 text-gray-400">
                            <i class="fas fa-box-open text-5xl mb-3 opacity-30"></i>
                            <p>No products found</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer Actions -->
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-2 py-2 flex items-center gap-2 overflow-x-auto shadow-md rounded-xl z-50">
            <Button
                @click="submit('draft')" 
                :disabled="!hasItems"
                class="px-3 py-2 rounded text-xs font-semibold cursor-pointer transition-all duration-200 flex items-center gap-1.5 whitespace-nowrap bg-gray-600 text-white hover:-translate-y-0.5 hover:shadow-lg hover:bg-gray-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-file-alt text-xs"></i>
                <span>Draft</span>
            </Button>
            
            <Button 
                @click="submit('quotation')" 
                :disabled="!hasItems"
                class="px-3 py-2 border-none rounded text-xs font-semibold cursor-pointer transition-all duration-200 flex items-center gap-1.5 whitespace-nowrap bg-purple-600 text-white hover:-translate-y-0.5 hover:shadow-lg hover:bg-purple-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-file-invoice text-xs"></i>
                <span>Quotation</span>
            </Button>
            
            <Button 
                @click="submit('suspend')" 
                :disabled="!hasItems"
                class="px-3 py-2 border-none rounded text-xs font-semibold cursor-pointer transition-all duration-200 flex items-center gap-1.5 whitespace-nowrap bg-amber-500 text-white hover:-translate-y-0.5 hover:shadow-lg hover:bg-amber-600 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-pause text-xs"></i>
                <span>Suspend</span>
            </Button>
            
            <Button 
                @click="submit('credit')" 
                :disabled="!hasItems"
                class="px-3 py-2 border-none rounded text-xs font-semibold cursor-pointer transition-all duration-200 flex items-center gap-1.5 whitespace-nowrap bg-blue-600 text-white hover:-translate-y-0.5 hover:shadow-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-credit-card text-xs"></i>
                <span>Credit Sale</span>
            </Button>
            
            <Button 
                @click="submit('card')" 
                :disabled="!hasItems"
                class="px-3 py-2 border-none rounded text-xs font-semibold cursor-pointer transition-all duration-200 flex items-center gap-1.5 whitespace-nowrap bg-cyan-600 text-white hover:-translate-y-0.5 hover:shadow-lg hover:bg-cyan-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-credit-card text-xs"></i>
                <span>Card</span>
            </Button>
            
            <!-- <Button class="px-3 py-2 border-none rounded text-xs font-semibold cursor-pointer transition-all duration-200 flex items-center gap-1.5 whitespace-nowrap bg-purple-600 text-white hover:-translate-y-0.5 hover:shadow-lg hover:bg-purple-700">
                <i class="fas fa-money-check-alt text-xs"></i>
                <span>Multiple Pay</span>
            </Button> -->
            
            <Button 
                @click="submit('cash')" 
                :disabled="!hasItems"
                class="px-3 py-2 border-none rounded text-xs font-semibold cursor-pointer transition-all duration-200 flex items-center gap-1.5 whitespace-nowrap bg-emerald-600 text-white hover:-translate-y-0.5 hover:shadow-lg hover:bg-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed">
                <i class="fas fa-money-bill-wave text-xs"></i>
                <span>Cash</span>
            </Button>
            
            <Button @click="clearRow"
                    class="px-3 py-2 border-none rounded text-xs font-semibold cursor-pointer transition-all duration-200 flex items-center gap-1.5 whitespace-nowrap bg-red-500 text-white hover:-translate-y-0.5 hover:shadow-lg hover:bg-red-600">
                <i class="fas fa-times text-xs"></i>
                <span>Cancel</span>
            </Button>
            
            <div class="ml-auto bg-white/10 px-4 py-2 rounded-lg backdrop-blur-sm border border-white/20">
                <div class="text-[10px] text-white/80 uppercase tracking-wider">Total Payable</div>
                <div class="text-lg font-bold text-white">{{ finalTotal.toFixed(2) }}</div>
            </div>
            
            <Button @click="openRecentModal"
                class="px-3 py-2 border-none rounded text-xs font-semibold cursor-pointer transition-all duration-200 flex items-center gap-1.5 whitespace-nowrap bg-indigo-600 text-white hover:-translate-y-0.5 hover:shadow-lg hover:bg-indigo-700">
                <i class="fas fa-history text-xs"></i>
                <span>Recent</span>
            </Button>
        </div>
    </div>
    
    <!-- Discount Modal -->
    <Popup v-if="showDiscountModal" title="Enter Discount">
        <TextInput 
            type="number" 
            v-model="tempDiscount" 
            class="w-full px-3 py-2 text-base mb-4"
            placeholder="Enter discount amount"
            step="0.01"
            @keyup.enter="applyDiscount"
        />
        <div class="flex gap-3 justify-end">
            <Button @click="showDiscountModal = false" 
                    class="px-5 py-2.5 border-none rounded-md font-semibold cursor-pointer transition-all duration-200 bg-gray-200 text-gray-700 hover:bg-gray-300">
                Cancel
            </Button>
            <Button @click="applyDiscount" 
                    class="px-5 py-2.5 border-none rounded-md font-semibold cursor-pointer transition-all duration-200 bg-indigo-600 text-white hover:bg-indigo-700">
                Apply
            </Button>
        </div>
    </Popup>
    
    <!-- Tax Modal -->
    <Popup v-if="showTaxModal" title="Enter Order Tax">
        <TextInput 
            type="number" 
            v-model="tempTax" 
            class="w-full px-3 py-2 text-base mb-4"
            placeholder="Enter tax amount"
            step="0.01"
            @keyup.enter="applyTax" 
        />
        <div class="flex gap-3 justify-end">
            <Button @click="showTaxModal = false" 
                    class="px-5 py-2.5 border-none rounded-md font-semibold cursor-pointer transition-all duration-200 bg-gray-200 text-gray-700 hover:bg-gray-300">
                Cancel
            </Button>
            <Button @click="applyTax" 
                    class="px-5 py-2.5 border-none rounded-md font-semibold cursor-pointer transition-all duration-200 bg-indigo-600 text-white hover:bg-indigo-700">
                Apply
            </Button>
        </div>
    </Popup>
    
    <!-- Shipping Modal -->
    <Popup v-if="showShippingModal" title="Enter Shipping Cost">
        <TextInput 
            type="number" 
            v-model="tempShipping" 
            class="w-full px-3 py-2 text-base mb-4"
            placeholder="Enter shipping cost"
            step="0.01"
            @keyup.enter="applyShipping" 
        />
        <div class="flex gap-3 justify-end">
            <Button @click="showShippingModal = false" 
                    class="px-5 py-2.5 border-none rounded-md font-semibold cursor-pointer transition-all duration-200 bg-gray-200 text-gray-700 hover:bg-gray-300">
                Cancel
            </Button>
            <Button @click="applyShipping" 
                    class="px-5 py-2.5 border-none rounded-md font-semibold cursor-pointer transition-all duration-200 bg-indigo-600 text-white hover:bg-indigo-700">
                Apply
            </Button>
        </div>
    </Popup>
    
    <!-- Quantity Modal -->
    <Modal2 id="modal_qty" title="Update Quantity">
        <div class="p-4">
            <h4 class="text-lg font-semibold mb-4">{{ quantityModalData.name }}</h4>
            
            <div class="flex items-center justify-center gap-3 mb-4">
                <button class="btn btn-outline-secondary px-4 py-2"
                        type="button"
                        @click="decrementQuantity()">
                    <i class="fas fa-minus"></i>
                </button>
                
                <input type="number" 
                       v-model="quantityModalData.qty"
                       class="form-control text-center text-xl font-bold w-24"
                       min="1">
                
                <button class="btn btn-outline-secondary px-4 py-2"
                        type="button"
                        @click="incrementQuantity()">
                    <i class="fas fa-plus"></i>
                </button>
            </div>
            
            <PrimaryButton @click.prevent="updateQuantity" 
                           class="btn btn-primary w-full">
                <i class="fas fa-check mr-2"></i>
                Update Quantity
            </PrimaryButton>
        </div>
    </Modal2>
    
    <!-- Paused Sales Modal -->
    <Modal2 id="modal_pasued_sale" title="Paused Sales">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="thead-light">
                    <tr>
                        <th>Table #</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Action</th>
                        <th>Invoice #</th>
                    </tr>
                </thead>
                <tbody id="pre_sale_items"></tbody>
            </table>
        </div>
    </Modal2>

    <!-- Sale Return Popup -->
    <Popup v-if="isPopupOpen" ref="popupRef" title="Sale Return">
        <TextInput 
            type="text" 
            placeholder="Enter Invoice No." 
            v-model="nonForm.saleReturnInv" 
            class="w-full px-3 py-2 text-base mb-4"
            @keyup.enter="openModalSaleReturn()" 
        />
        
        <div class="flex gap-3 justify-end">
            <Button @click="handlePopup('SR')" 
                    class="px-5 py-2.5 border-none rounded-md font-semibold cursor-pointer transition-all duration-200 bg-gray-200 text-gray-700 hover:bg-gray-300">
                Cancel
            </Button>
            <Button @click="openModalSaleReturn()" 
                    class="px-5 py-2.5 border-none rounded-md font-semibold cursor-pointer transition-all duration-200 bg-indigo-600 text-white hover:bg-indigo-700">
                <i class="fas fa-check mr-2"></i>
                Load Invoice
            </Button>
        </div>
    </Popup>

    <!-- Close Register Modal -->
    <Modal2 id="modal_close_register" :Class="'sm:max-w-6xl'" title="Close Register">
        <div class="p-6">
            <!-- Header Info -->
            <div class="flex justify-between items-center mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                    <div class="text-gray-500">Register From:</div>
                    <div class="font-semibold text-gray-800">{{ formatDateTime(closeRegisterData.start_time) }}</div>
                    <div class="text-gray-500">Register To:</div>
                    <div class="font-semibold text-gray-800">{{ formatDateTime(closeRegisterData.end_time) }}</div>
                </div>
                <div class="text-right text-sm">
                    <div class="font-bold text-lg text-indigo-700">{{ closeRegisterData.user.location }}</div>
                    <div class="text-gray-600">{{ closeRegisterData.user.name }} ({{ closeRegisterData.user.email }})</div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <div class="text-blue-500 text-xs uppercase tracking-wide font-semibold mb-1">Total Sales</div>
                    <div class="text-2xl font-bold text-blue-700">{{ closeRegisterData.total_sales.toFixed(2) }}</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                    <div class="text-red-500 text-xs uppercase tracking-wide font-semibold mb-1">Total Refund</div>
                    <div class="text-2xl font-bold text-red-700">{{ closeRegisterData.total_refund.toFixed(2) }}</div>
                </div>
                <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-100">
                    <div class="text-emerald-500 text-xs uppercase tracking-wide font-semibold mb-1">Net Payment</div>
                    <div class="text-2xl font-bold text-emerald-700">{{ closeRegisterData.total_payment.toFixed(2) }}</div>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg border border-orange-100">
                    <div class="text-orange-500 text-xs uppercase tracking-wide font-semibold mb-1">Credit Sales</div>
                    <div class="text-2xl font-bold text-orange-700">{{ closeRegisterData.credit_sales.toFixed(2) }}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="text-gray-500 text-xs uppercase tracking-wide font-semibold mb-1">Total Expense</div>
                    <div class="text-2xl font-bold text-gray-700">{{ closeRegisterData.total_expense.toFixed(2) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Payment Methods -->
                <div>
                    <h5 class="text-sm font-bold text-gray-700 mb-3 border-b pb-2">Payment Methods Breakdown</h5>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-500 text-left bg-gray-50">
                                <th class="p-2 rounded-l">Method</th>
                                <th class="p-2">Sell</th>
                                <th class="p-2 rounded-r">Expense</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="pm in closeRegisterData.payment_methods" :key="pm.method" class="border-b border-gray-100">
                                <td class="p-2 font-medium">{{ pm.method }}</td>
                                <td class="p-2">{{ pm.sell.toFixed(2) }}</td>
                                <td class="p-2 text-gray-600">{{ pm.expense.toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded border border-gray-200">
                             <div class="text-xs text-gray-500">Total Cash</div>
                             <div class="font-bold text-gray-800">{{ closeRegisterData.total_cash.toFixed(2) }}</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded border border-gray-200">
                             <div class="text-xs text-gray-500">Total Card Slips</div>
                             <div class="font-bold text-gray-800">{{ closeRegisterData.total_card_slips.toFixed(2) }}</div>
                        </div>
                         <div class="bg-gray-50 p-3 rounded border border-gray-200">
                             <div class="text-xs text-gray-500">Total Cheques</div>
                             <div class="font-bold text-gray-800">{{ closeRegisterData.total_cheques.toFixed(2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Cash Denominations -->
                <div>
                    <h5 class="text-sm font-bold text-gray-700 mb-3 border-b pb-2">Cash Denominations</h5>
                    <div class="max-h-60 overflow-y-auto pr-2">
                        <div v-for="(count, denom) in closeRegisterData.cash_denominations" :key="denom" class="flex items-center mb-2">
                            <span class="w-16 font-mono font-semibold">{{ denom }}</span>
                            <span class="mx-2 text-gray-400">x</span>
                            <TextInput type="number" 
                                v-model="closeRegisterData.cash_denominations[denom]" 
                                class="flex-1 py-1 px-2 text-right" 
                                placeholder="0" />
                            <span class="w-24 text-right font-mono text-gray-600">= {{ (denom * count).toFixed(2) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-200">
                        <span class="font-bold text-gray-700">Total Counted:</span>
                        <span class="font-bold text-xl text-emerald-600">{{ calculateCashTotal.toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Product & Brand Details -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h5 class="text-sm font-bold text-gray-700 mb-2 border-b pb-2">Details of Product Sold</h5>
                    <div class="max-h-40 overflow-y-auto border rounded">
                        <table class="w-full text-xs">
                             <thead class="bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="p-2 text-left">Product</th>
                                    <th class="p-2 text-right">Qty</th>
                                    <th class="p-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in closeRegisterData.product_details" :key="item.name">
                                    <td class="p-2">{{ item.name }}</td>
                                    <td class="p-2 text-right">{{ item.qty }}</td>
                                    <td class="p-2 text-right">{{ item.total }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="p-2 font-bold text-right">Total:</td>
                                    <td class="p-2 font-bold text-right">{{ closeRegisterData.product_details.reduce((total, item) => Number(total) + Number(item.qty), 0) }}</td>
                                    <td class="p-2 font-bold text-right">{{ closeRegisterData.product_details.reduce((total, item) => Number(total) + Number(item.total), 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div>
                    <h5 class="text-sm font-bold text-gray-700 mb-2 border-b pb-2">Sold by Brand</h5>
                     <div class="max-h-40 overflow-y-auto border rounded">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="p-2 text-left">Brand</th>
                                    <th class="p-2 text-right">Qty</th>
                                    <th class="p-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in closeRegisterData.brand_details" :key="item.name">
                                    <td class="p-2">{{ item.name }}</td>
                                    <td class="p-2 text-right">{{ item.qty }}</td>
                                    <td class="p-2 text-right">{{ item.total }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="p-2 font-bold text-right">Total:</td>
                                    <td class="p-2 font-bold text-right">{{ closeRegisterData.brand_details.reduce((total, item) => Number(total) + Number(item.qty), 0) }}</td>
                                    <td class="p-2 font-bold text-right">{{ closeRegisterData.brand_details.reduce((total, item) => Number(total) + Number(item.total), 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Closing Note -->
            <div class="mb-6">
                <InputLabel for="closing_note" value="Closing Note" />
                <TextArea 
                    id="closing_note"
                    v-model="closeRegisterData.closing_note" 
                    class="w-full" 
                    placeholder="Enter any notes..." />
            </div>

            <!-- Footer Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <Button data-dismiss="modal" aria-label="Close" 
                        class="px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Cancel
                </Button>
                <Button @click="submitRegister" 
                        class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Close Register
                </Button>
            </div>
        </div>
    </Modal2>

    <!-- Register Information Modal -->
    <Modal2 id="modal_register_information" :Class="'sm:max-w-6xl'" title="Register Information">
        <div class="p-6">
            <!-- Header Info -->
            <div class="flex justify-between items-center mb-6 bg-gray-50 p-4 rounded-lg border border-gray-200">
                <div class="grid grid-cols-2 gap-x-8 gap-y-2 text-sm">
                    <div class="text-gray-500">Register From:</div>
                    <div class="font-semibold text-gray-800">{{ formatDateTime(closeRegisterData.start_time) }}</div>
                    <div class="text-gray-500">Register To:</div>
                    <div class="font-semibold text-gray-800">{{ formatDateTime(closeRegisterData.end_time) }}</div>
                </div>
                <div class="text-right text-sm">
                    <div class="font-bold text-lg text-indigo-700">{{ closeRegisterData.user.location }}</div>
                    <div class="text-gray-600">{{ closeRegisterData.user.name }} ({{ closeRegisterData.user.email }})</div>
                </div>
            </div>

            <!-- Financial Summary -->
            <div class="grid grid-cols-3 gap-4 mb-6">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-100">
                    <div class="text-blue-500 text-xs uppercase tracking-wide font-semibold mb-1">Total Sales</div>
                    <div class="text-2xl font-bold text-blue-700">{{ closeRegisterData.total_sales.toFixed(2) }}</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg border border-red-100">
                    <div class="text-red-500 text-xs uppercase tracking-wide font-semibold mb-1">Total Refund</div>
                    <div class="text-2xl font-bold text-red-700">{{ closeRegisterData.total_refund.toFixed(2) }}</div>
                </div>
                <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-100">
                    <div class="text-emerald-500 text-xs uppercase tracking-wide font-semibold mb-1">Net Payment</div>
                    <div class="text-2xl font-bold text-emerald-700">{{ closeRegisterData.total_payment.toFixed(2) }}</div>
                </div>
                <div class="bg-orange-50 p-4 rounded-lg border border-orange-100">
                    <div class="text-orange-500 text-xs uppercase tracking-wide font-semibold mb-1">Credit Sales</div>
                    <div class="text-2xl font-bold text-orange-700">{{ closeRegisterData.credit_sales.toFixed(2) }}</div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg border border-gray-200">
                    <div class="text-gray-500 text-xs uppercase tracking-wide font-semibold mb-1">Total Expense</div>
                    <div class="text-2xl font-bold text-gray-700">{{ closeRegisterData.total_expense.toFixed(2) }}</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-6 mb-6">
                <!-- Payment Methods -->
                <div>
                    <h5 class="text-sm font-bold text-gray-700 mb-3 border-b pb-2">Payment Methods Breakdown</h5>
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="text-xs text-gray-500 text-left bg-gray-50">
                                <th class="p-2 rounded-l">Method</th>
                                <th class="p-2">Sell</th>
                                <th class="p-2 rounded-r">Expense</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="pm in closeRegisterData.payment_methods" :key="pm.method" class="border-b border-gray-100">
                                <td class="p-2 font-medium">{{ pm.method }}</td>
                                <td class="p-2">{{ pm.sell.toFixed(2) }}</td>
                                <td class="p-2 text-gray-600">{{ pm.expense.toFixed(2) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4 grid grid-cols-2 gap-4">
                        <div class="bg-gray-50 p-3 rounded border border-gray-200">
                             <div class="text-xs text-gray-500">Total Cash</div>
                             <div class="font-bold text-gray-800">{{ closeRegisterData.total_cash.toFixed(2) }}</div>
                        </div>
                        <div class="bg-gray-50 p-3 rounded border border-gray-200">
                             <div class="text-xs text-gray-500">Total Card Slips</div>
                             <div class="font-bold text-gray-800">{{ closeRegisterData.total_card_slips.toFixed(2) }}</div>
                        </div>
                         <div class="bg-gray-50 p-3 rounded border border-gray-200">
                             <div class="text-xs text-gray-500">Total Cheques</div>
                             <div class="font-bold text-gray-800">{{ closeRegisterData.total_cheques.toFixed(2) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Cash Denominations -->
                <div>
                    <h5 class="text-sm font-bold text-gray-700 mb-3 border-b pb-2">Cash Denominations</h5>
                    <div class="max-h-60 overflow-y-auto pr-2">
                        <div v-for="(count, denom) in closeRegisterData.cash_denominations" :key="denom" class="flex items-center mb-2">
                            <span class="w-16 font-mono font-semibold">{{ denom }}</span>
                            <span class="mx-2 text-gray-400">x</span>
                            <TextInput type="number" 
                                v-model="closeRegisterData.cash_denominations[denom]" 
                                class="flex-1 py-1 px-2 text-right" 
                                placeholder="0" />
                            <span class="w-24 text-right font-mono text-gray-600">= {{ (denom * count).toFixed(2) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center mt-3 pt-3 border-t border-gray-200">
                        <span class="font-bold text-gray-700">Total Counted:</span>
                        <span class="font-bold text-xl text-emerald-600">{{ calculateCashTotal.toFixed(2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Product & Brand Details -->
            <div class="grid grid-cols-2 gap-6 mb-6">
                <div>
                    <h5 class="text-sm font-bold text-gray-700 mb-2 border-b pb-2">Details of Product Sold</h5>
                    <div class="max-h-40 overflow-y-auto border rounded">
                        <table class="w-full text-xs">
                             <thead class="bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="p-2 text-left">Product</th>
                                    <th class="p-2 text-right">Qty</th>
                                    <th class="p-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in closeRegisterData.product_details" :key="item.name">
                                    <td class="p-2">{{ item.name }}</td>
                                    <td class="p-2 text-right">{{ item.qty }}</td>
                                    <td class="p-2 text-right">{{ item.total }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="p-2 font-bold text-right">Total:</td>
                                    <td class="p-2 font-bold text-right">{{ closeRegisterData.product_details.reduce((total, item) => Number(total) + Number(item.qty), 0) }}</td>
                                    <td class="p-2 font-bold text-right">{{ closeRegisterData.product_details.reduce((total, item) => Number(total) + Number(item.total), 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
                <div>
                    <h5 class="text-sm font-bold text-gray-700 mb-2 border-b pb-2">Sold by Brand</h5>
                     <div class="max-h-40 overflow-y-auto border rounded">
                        <table class="w-full text-xs">
                            <thead class="bg-gray-50 text-gray-500">
                                <tr>
                                    <th class="p-2 text-left">Brand</th>
                                    <th class="p-2 text-right">Qty</th>
                                    <th class="p-2 text-right">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <tr v-for="item in closeRegisterData.brand_details" :key="item.name">
                                    <td class="p-2">{{ item.name }}</td>
                                    <td class="p-2 text-right">{{ item.qty }}</td>
                                    <td class="p-2 text-right">{{ item.total }}</td>
                                </tr>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td class="p-2 font-bold text-right">Total:</td>
                                    <td class="p-2 font-bold text-right">{{ closeRegisterData.brand_details.reduce((total, item) => Number(total) + Number(item.qty), 0) }}</td>
                                    <td class="p-2 font-bold text-right">{{ closeRegisterData.brand_details.reduce((total, item) => Number(total) + Number(item.total), 0) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
                <!-- <Button class="px-6 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Print Mini
                </Button>
                <Button class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-primary hover:bg-primary focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    Print Details
                </Button> -->
            </div>
        </div>
    </Modal2>

    <!-- Add Expense Modal -->
    <Modal2 id="modal_add_expense" :Class="'sm:max-w-3xl'" title="Add Account">
        <div class="row">
            <div class="col-md-12">
                <div class="">
                    <div class="">
                        <form @submit.prevent="submitCustomer">
                            <div class="row">
                                <div class="col-md-12">
                                    <InputLabel for="acc_name" value="Name" required />
                                    <TextInput id="acc_name" type="text" class="mt-1 block w-full"
                                        v-model="customerForm.acc_name" placeholder="Enter Name" required autofocus />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="acc_type" value="Type" required />
                                    <!-- <Select2 v-model="customerForm.acc_type" :options="accountTypes" /> -->
                                    <SelectWithSearch
                                        v-model="customerForm.cust_id"
                                        :options="accountTypes"
                                        placeholder="-- Select Type --"
                                        search-placeholder="Search type..."
                                        class="flex-1 text-sm"
                                    />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="acc_city" value="City" />
                                    <TextInput id="acc_city" type="text" class="mt-1 block w-full"
                                        v-model="customerForm.acc_city" placeholder="Enter City" />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="acc_address" value="Address" />
                                    <TextInput id="acc_address" type="text" class="mt-1 block w-full"
                                        v-model="customerForm.acc_address" placeholder="Enter Address" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="acc_contact_per" value="Contact Person" />
                                    <TextInput id="acc_contact_per" type="number" class="mt-1 block w-full"
                                        v-model="customerForm.acc_contact_per" placeholder="Enter Contact Person" />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="acc_mobile" value="Mobile#" />
                                    <TextInput id="acc_mobile" type="number" class="mt-1 block w-full"
                                        v-model="customerForm.acc_mobile" placeholder="Enter Mobile#" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="acc_email" value="Email" />
                                    <TextInput id="acc_email" type="email" class="mt-1 block w-full"
                                        v-model="customerForm.acc_email" placeholder="Enter Email" />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <InputLabel for="acc_tel1" value="Telephone No 1" />
                                    <TextInput id="acc_tel1" type="number" class="mt-1 block w-full"
                                        v-model="customerForm.acc_tel1" placeholder="Enter Telephone No 1" />
                                </div>
                                <div class="col-md-6">
                                    <InputLabel for="acc_tel2" value="Telephone No 2" />
                                    <TextInput id="acc_tel2" type="number" class="mt-1 block w-full"
                                        v-model="customerForm.acc_tel2" placeholder="Enter Telephone No 2" />
                                </div>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton class="btn btn-primary" :disabled="customerForm.processing">
                                    Save
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </Modal2>

    <!-- View Recent Modal -->
    <Modal2 id="modal_recent" :Class="'sm:max-w-6xl'" title="Recent Invoices">
        <div class="row">
            <div class="col-md-12">
                <!-- Tab Navigation and Content -->
                <TabNavigation v-model="activeTab" :tabs="tabs">
                    <!-- Final Tab Content -->
                    <template #final>
                        <table class="table table-hover">
                            <tbody>
                                <tr v-for="(inv, index) in filteredInvoices" :key="inv.id">
                                    <td>{{ index + 1 }}. {{ inv.id }}</td>
                                    <td>{{ inv.account ? inv.account.name : 'Walk-In Customer' }}</td>
                                    <td>{{ Number(inv.finalVal).toFixed(2) }}</td>
                                    <td>
                                        <i class="fa fa-edit mr-2 text-blue-500 cursor-pointer transition-all duration-200 hover:text-blue-600 hover:scale-125" title="Edit" @click="loadSales(inv.id, 'Final')"></i>
                                        <i class="fa fa-print mr-2 text-green-500 cursor-pointer transition-all duration-200 hover:text-green-600 hover:scale-125" title="Print" @click="showSeparatePrintWindow(inv.id)"></i>
                                    </td>
                                </tr>
                                <tr v-if="filteredInvoices.length === 0">
                                    <td colspan="5" class="text-center">No invoices found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </template>

                    <!-- Quotation Tab Content -->
                    <template #quotation>
                        <table class="table table-hover">
                            <tbody>
                                <tr v-for="(inv, index) in filteredInvoices" :key="inv.id">
                                    <td>{{ index + 1 }}. {{ inv.id }}</td>
                                    <td>{{ inv.account ? inv.account.name : 'Walk-In Customer' }}</td>
                                    <td>{{ Number(inv.finalVal).toFixed(2) }}</td>
                                    <td>
                                        <i class="fa fa-edit mr-2 text-blue-500 cursor-pointer transition-all duration-200 hover:text-blue-600 hover:scale-125" title="Edit" @click="loadSales(inv.id, 'Quotation')"></i>
                                        <i class="fa fa-print mr-2 text-green-500 cursor-pointer transition-all duration-200 hover:text-green-600 hover:scale-125" title="Print" @click="showSeparatePrintWindow(inv.id)"></i>
                                        <i class="fa fa-trash mr-2 text-red-500 cursor-pointer transition-all duration-200 hover:text-red-600 hover:scale-125" title="Delete" @click="deleteInvoice(inv.id)"></i>
                                    </td>
                                </tr>
                                <tr v-if="filteredInvoices.length === 0">
                                    <td colspan="4" class="text-center">No quotations found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </template>

                    <!-- Draft Tab Content -->
                    <template #draft>
                        <table class="table table-hover">
                            <tbody>
                                <tr v-for="(inv, index) in filteredInvoices" :key="inv.id">
                                    <td>{{ index + 1 }}. {{ inv.id }}</td>
                                    <td>{{ inv.account ? inv.account.name : 'Walk-In Customer' }}</td>
                                    <td>{{ Number(inv.finalVal).toFixed(2) }}</td>
                                    <td>
                                        <i class="fa fa-edit mr-2 text-blue-500 cursor-pointer transition-all duration-200 hover:text-blue-600 hover:scale-125" title="Edit" @click="loadSales(inv.id, 'Draft')"></i>
                                        <i class="fa fa-print mr-2 text-green-500 cursor-pointer transition-all duration-200 hover:text-green-600 hover:scale-125" title="Print" @click="showSeparatePrintWindow(inv.id)"></i>
                                        <i class="fa fa-trash mr-2 text-red-500 cursor-pointer transition-all duration-200 hover:text-red-600 hover:scale-125" title="Delete" @click="deleteInvoice(inv.id)"></i>
                                    </td>
                                </tr>
                                <tr v-if="filteredInvoices.length === 0">
                                    <td colspan="4" class="text-center">No drafts found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </template>

                    <!-- Suspend Tab Content -->
                    <template #suspend>
                        <table class="table table-hover">
                            <tbody>
                                <tr v-for="(inv, index) in filteredInvoices" :key="inv.id">
                                    <td>{{ index + 1 }}. {{ inv.id }}</td>
                                    <td>{{ inv.account ? inv.account.name : 'Walk-In Customer' }}</td>
                                    <td>{{ Number(inv.finalVal).toFixed(2) }}</td>
                                    <td>
                                        <i class="fa fa-edit mr-2 text-blue-500 cursor-pointer transition-all duration-200 hover:text-blue-600 hover:scale-125" title="Edit" @click="loadSales(inv.id, 'Suspend')"></i>
                                        <i class="fa fa-print mr-2 text-green-500 cursor-pointer transition-all duration-200 hover:text-green-600 hover:scale-125" title="Print" @click="showSeparatePrintWindow(inv.id)"></i>
                                        <i class="fa fa-trash mr-2 text-red-500 cursor-pointer transition-all duration-200 hover:text-red-600 hover:scale-125" title="Delete" @click="deleteInvoice(inv.id)"></i>
                                    </td>
                                </tr>
                                <tr v-if="filteredInvoices.length === 0">
                                    <td colspan="5" class="text-center">No invoices found.</td>
                                </tr>
                            </tbody>
                        </table>
                    </template>
                </TabNavigation>
            </div>
        </div>
    </Modal2>

</template>
