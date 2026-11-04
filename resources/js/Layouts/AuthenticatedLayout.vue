<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router, useForm } from '@inertiajs/vue3';
import Popup from '@/Components/Popup.vue';
import TextInput from '@/Components/TextInput.vue';
import Button from '@/Components/Button.vue';

const form = useForm({
    amount: '',
});

const page = usePage();
const user = computed(() => page.props.auth.user);
const isSidebarToggled = ref(true);
const isPopupOpen = ref(false);

const handlePopup = async (name) => {
    await axios.get(route('checkRegisterStatus')).then(response => {
            if(response.data.success){
                if(name == 'OCR' && ['closed', ''].includes(response.data.status)){
                    isPopupOpen.value = !isPopupOpen.value;
                    form.amount = '';
                }
                else {
                    router.visit(route('saleForm2'));
                }
            }
        }).catch(error => {
            console.error('Error while checking status:', error);
            alert('Failed to check status. Please try again.');
        });
        
};

const closePopup = (name) => {
    if(name == 'OCR'){
        isPopupOpen.value = !isPopupOpen.value;
        form.amount = '';
    }  
};

const toggleSidebar = () => {
    isSidebarToggled.value = !isSidebarToggled.value;
};

const sidebarItems = computed(() => [
    {
        title: 'Dashboard',
        route: 'dashboard',
        icon: 'fas fa-fw fa-tachometer-alt',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.dashboard_view,
        isBlade: false,
        active: route().current('dashboard')
    },
    {
        title: 'Inventory',
        route: 'inventoryCheck',
        icon: 'fas fa-shopping-bag',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.product_create,
        isBlade: false,
        active: route().current('inventoryCheck')
    },
    {
        title: 'Sale Invoice',
        route: 'saleForm3',
        icon: 'fas fa-university',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.sale_invoice_create,
        isBlade: false,
        active: route().current('saleForm3')
    },
    // {
    //     title: 'New Sale Invoice',
    //     route: 'saleForm2',
    //     icon: 'fas fa-university',
    //     permission: user.value.type === 'Admin' || user.value.permission?.pages?.sale_invoice_create,
    //     isBlade: false,
    //     active: route().current('saleForm2'),
    //     small: true
    // }, 
    {
        title: 'Purchase Invoice',
        route: 'purchaseForm2',
        icon: 'fas fa-tags',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.purchase_invoice_create,
        isBlade: false,
        active: route().current('purchaseForm2'),
    },
    {
        title: 'View Sale',
        route: 'viewSInvoice',
        icon: 'fas fa-th-list',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.sale_invoice_view,
        isBlade: false,
        active: route().current('viewSInvoice'),
        small: true
    },
    {
        title: 'View Purchase',
        route: 'viewPInvoice',
        icon: 'fas fa-th-list',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.purchase_invoice_view,
        isBlade: false,
        active: route().current('viewPInvoice'),
        small: true
    },
    {
        title: 'View Return',
        route: 'returnCheck',
        icon: 'fas fa-th-list',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.invoice_return_view,
        isBlade: false,
        active: route().current('returnCheck'),
        small: true
    },
    {
        title: 'Accounts',
        route: 'viewAccount',
        icon: 'fas fa-user-check',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.account_view,
        isBlade: false,
        active: route().current('viewAccount')
    },
    {
        title: 'Ledger',
        route: 'viewLedgerForm',
        icon: 'fas fa-file',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.ledger_create,
        isBlade: false,
        active: route().current('viewLedgerForm')
    },
    {
        title: 'Transaction',
        route: 'transCheck',
        icon: 'fas fa-wallet',
        permission: user.value.type === 'Admin' || (user.value.permission?.pages?.trans_cashin_create && user.value.permission?.pages?.trans_cashout_create),
        isBlade: false,
        active: route().current('transCheck')
    },
    {
        title: 'StockForm',
        route: 'stockForm',
        icon: 'fas fa-wrench',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.stock_form_view,
        isBlade: false,
        active: route().current('stockForm')
    },
    {
        title: 'Delivery Orders',
        route: 'deliveryOrder.view',
        icon: 'fas fa-truck',
        permission: user.value.type === 'Admin' || user.value.permission?.pages?.delivery_order_update,
        isBlade: false,
        active: route().current('deliveryOrder.view')
    }
]);

const reportItems = computed(() => [
    { title: 'Cashin Report', param: 'Cashin' },
    { title: 'Cashout Report', param: 'Cashout' },
    { title: 'Receivable Report', param: 'Receivable' },
    { title: 'Payable Report', param: 'Payable' },
    { title: 'Stock Report', param: 'Stock' },
    { title: 'Stock Tracking Report', param: 'StockTracking' },
    { title: 'Product Wise Sale Report', param: 'ProductWiseSale' },
    { title: 'Brand Wise Sale Report', param: 'BrandWiseSale' },
    { title: 'Day End Report', param: 'DayEnd' },
    { title: 'Day End Detail Report', param: 'DayEndDetail' },
    { title: 'Profit & Loss Report', param: 'ProfitLoss' },
]);

const hasReportPermission = computed(() => {
    return user.value.type === 'Admin' || user.value.permission?.pages?.report_create;
});

const hasSettingsPermission = computed(() => {
    return user.value.type === 'Admin' || user.value.permission?.pages?.settings_create;
});

const hasPOSPermission = computed(() => {
    return user.value.type === 'Admin' || user.value.permission?.pages?.sale_invoice_create;
});

const logout = () => {
    router.post(route('logout'));
};

// Form submission
const submitOpenRegister = async () => {
    try {
        await axios.post(route('insertRegister'), form).then(response => {
            if(response.data.success){
                console.log('Saved successfully');
                form.amount = '';
                handlePopup('OCR');
                router.visit(route('saleForm2'));
            }
        }).catch(error => {
            console.error('Error while saving:', error);
            alert('Failed to save. Please try again.');
        });
    } catch (error) {
        console.error('Error while saving:', error);
        alert('Failed to save. Please try again.');
    }
};
</script>

<template>
    <!-- Page Wrapper -->
    <div id="wrapper" :class="{ 'sidebar-toggled': isSidebarToggled }">

        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" 
            :class="{ 'toggled': isSidebarToggled }" 
            id="accordionSidebar">

            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
                <div class="sidebar-brand-icon">
                    <p class="mb-0 font-weight-bold" style="font-size: 14px;">POS System</p>
                </div>
                <!-- <div class="sidebar-brand-text mx-3">Admin Panel</div> -->
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Sidebar Items -->
            <template v-for="(item, index) in sidebarItems" :key="index">
                <li v-if="item.permission" class="nav-item" :class="{ 'active': item.active }">
                    <component 
                        :is="item.isBlade ? 'a' : Link" 
                        :href="route(item.route)" 
                        class="nav-link"
                        :class="{ 'py-2': item.small }"
                    >
                        <i :class="item.icon"></i>
                        <span :class="{ 'small': item.small }">{{ item.title }}</span>
                    </component>
                </li>
            </template>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle" @click="toggleSidebar"></button>
            </div>

        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column" style="overflow: visible;">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3" @click="toggleSidebar">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        <li v-if="hasPOSPermission" class="nav-item dropdown no-arrow mx-1 cursor-pointer" @click="handlePopup('OCR')">
                            <div class="nav-link">
                                <i class="fas fa-cash-register fa-fw text-gray-600"></i>
                                <span class="d-none d-lg-inline text-gray-600 small ml-1">POS</span>
                            </div>
                        </li>

                        <!-- Nav Item - Reports -->
                        <li v-if="hasReportPermission" class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-chart-bar fa-fw text-gray-600"></i>
                                <span class="d-none d-lg-inline text-gray-600 small ml-1">Reports</span>
                            </a>
                            <!-- Dropdown - Reports -->
                            <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="alertsDropdown">
                                <h6 class="dropdown-header bg-primary border-0">
                                    Reports Center
                                </h6>
                                <div class="dropdown-scroll">
                                    <Link v-for="(report, index) in reportItems" :key="index" 
                                          :href="route('report', report.param)" 
                                          class="dropdown-item d-flex align-items-center">
                                        <div class="mr-3">
                                            <div class="icon-circle bg-primary-light">
                                                <i class="fas fa-file-alt text-primary"></i>
                                            </div>
                                        </div>
                                        <div>
                                            <span class="font-weight-bold text-gray-800">{{ report.title }}</span>
                                        </div>
                                    </Link>
                                </div>
                            </div>
                        </li>

                        <div class="topbar-divider d-none d-sm-block"></div>

                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small font-weight-bold">{{ user.name }}</span>
                                <div class="img-profile rounded-circle bg-gray-200 d-flex align-items-center justify-content-center text-gray-600">
                                    <i class="fas fa-user"></i>
                                </div>
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                                aria-labelledby="userDropdown">
                                <template v-if="user.type === 'Admin'">
                                    <Link :href="route('profile.edit')" class="dropdown-item">
                                        <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Profile
                                    </Link>
                                    <!-- <Link :href="route('viewCompany')" class="dropdown-item">
                                        <i class="fas fa-building fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Company
                                    </Link> -->
                                    <!-- Settings Submenu -->
                                    <div class="dropdown-item dropdown-submenu" v-if="hasSettingsPermission">
                                        <div class="d-flex align-items-center justify-content-between w-100">
                                            <span>
                                                <i class="fas fa-cog fa-sm fa-fw mr-2 text-gray-400"></i>
                                                Settings
                                            </span>
                                            <i class="fas fa-chevron-right fa-sm text-gray-400"></i>
                                        </div>
                                        <div class="dropdown-menu shadow">
                                            <Link :href="route('business.settings.index')" class="dropdown-item">
                                                Business
                                            </Link>
                                            <Link :href="route('tax.index')" class="dropdown-item">
                                                Tax
                                            </Link>
                                            <Link :href="route('locations.index')" class="dropdown-item">
                                                Locations
                                            </Link>
                                            <Link :href="route('viewProfile')" class="dropdown-item">
                                                Add User
                                            </Link>
                                        </div>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                </template>
                                <button @click="logout" class="dropdown-item w-100 text-left">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </button>
                            </div>
                        </li>

                    </ul>

                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <slot />
                </div>

            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white mt-auto no-print shadow-sm-top">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto text-gray-600">
                        <span>Copyright &copy; Arbaz {{ new Date().getFullYear() }}</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->

        </div>
        <!-- End of Content Wrapper -->

    </div>
    <!-- End of Page Wrapper -->

    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <!-- Open Cash Register Popup -->
    <Popup v-if="isPopupOpen" title="Open Cash Register">
        <TextInput 
            type="number" 
            placeholder="Enter Amount" 
            v-model="form.amount" 
            class="w-full px-3 py-2 text-base mb-4"
            @keyup.enter="submitOpenRegister" 
        />
        
        <div class="flex gap-3 justify-end">
            <Button @click="closePopup('OCR')" 
                    class="px-6 py-2.5 border-none rounded-md font-semibold cursor-pointer transition-all duration-200 bg-gray-200 text-gray-700 hover:bg-gray-300">
                Cancel
            </Button>
            <Button @click="submitOpenRegister" 
                    class="px-6 py-2.5 border-none rounded-md font-semibold cursor-pointer transition-all duration-200 bg-indigo-600 text-white hover:bg-indigo-700">
                Open Register
            </Button>
        </div>
    </Popup>
</template>

<style scoped>
/* Custom Scrollbar for Reports Dropdown */
.dropdown-scroll {
    max-height: 300px;
    overflow-y: auto;
}

/* Enhanced Sidebar Styling */
.bg-gradient-primary {
    background-color: #4e73df;
    background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
    background-size: cover;
}

.sidebar-brand-text {
    font-weight: 800;
    letter-spacing: 0.05rem;
}

/* Icon Circle Styling */
.icon-circle {
    height: 2.5rem;
    width: 2.5rem;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-primary-light {
    background-color: rgba(78, 115, 223, 0.1) !important;
}

.text-primary {
    color: #4e73df !important;
}

/* Topbar Styling */
.topbar {
    height: 4.375rem;
    z-index: 1000; /* Ensure topbar stays on top */
}

/* Footer Styling */
.sticky-footer {
    padding: 2rem 0;
    flex-shrink: 0;
}

.shadow-sm-top {
    box-shadow: 0 -0.125rem 0.25rem 0 rgba(58, 59, 69, 0.075) !important;
}

/* Transition for sidebar toggle */
#wrapper #content-wrapper {
    transition: margin-left 0.3s;
}

.sidebar {
    transition: width 0.3s, margin 0.3s;
}

.img-profile {
    height: 2rem;
    width: 2rem;
}

/* Submenu Support */
.dropdown-submenu {
    position: relative;
    cursor: pointer;
}

.dropdown-submenu .dropdown-menu {
    top: 0;
    right: 100%;
    left: auto;
    margin-top: -1px;
    margin-right: 0.1rem;
    display: none;
}

.dropdown-submenu:hover .dropdown-menu {
    display: block;
}
</style>