<script setup>
import { ref, computed } from 'vue';
import { Link, usePage, router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import Modal2 from '@/Components/Modal2.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Select2 from '@/Components/Select2.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

const form = useForm({
    acc_name: '',
    acc_type: '',
    acc_city: '',
    acc_address: '',
    acc_contact_per: '',
    acc_mobile: '',
    acc_email: '',
    acc_tel1: '',
    acc_tel2: '',
});

const accountTypes = [
    { id: 'Asset', name: 'Asset' },
    { id: 'Capital', name: 'Capital' },
    { id: 'Customer', name: 'Customer' },
    { id: 'Expense', name: 'Expense' },
    { id: 'Liability', name: 'Liability' },
    { id: 'Vendor', name: 'Vendor' },
];

const triggerCount = ref(0);

const submit = () => {
    triggerCount.value++;
    // try {
    //     const response = await axios.get(route('insertAccountApi'));
        
    //     // Axios stores the JSON body in 'data'
    //     apiResult.value = response.data; 
        
    //     console.log('Success:', apiResult.value);
        
    //     // UI logic
    //     $('#modal_customer').modal('hide');
    // } catch (error) {
    //     console.error('Error fetching data:', error);
    // }
};

const page = usePage();
const user = computed(() => page.props.auth.user);
const isSidebarToggled = ref(true);

const toggleSidebar = () => {
    isSidebarToggled.value = !isSidebarToggled.value;
};

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

const logout = () => {
    router.post(route('logout'));
};

</script>

<template>
    <!-- Page Wrapper -->
    <div id="wrapper" :class="{ 'sidebar-toggled': isSidebarToggled }">

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">

            <!-- Main Content -->
            <div id="content">

                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow-sm">

                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3" @click="toggleSidebar">
                        <i class="fa fa-bars"></i>
                    </button>
                    <ul class="navbar-nav">
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" @click="submit" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-user-plus fa-fw text-gray-600"></i>
                                <span class="d-none d-lg-inline text-gray-600 small ml-1">Add Customer</span>
                            </a>
                        </li>
                    </ul>
                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">

                        

                        <!-- Nav Item - Reports -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-chart-bar fa-fw text-gray-600"></i>
                                <span class="d-none d-lg-inline text-gray-600 small ml-1">Reports</span>
                            </a>
                            <!-- Dropdown - Reports -->
                            <div v-if="hasReportPermission" class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
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
                                    <Link :href="route('viewCompany')" class="dropdown-item">
                                        <i class="fas fa-building fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Company
                                    </Link>
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
                <div class="container-fluid h-[calc(100vh-100px)] mb-20">
                    <slot :signal="triggerCount" />
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
    padding: 0.75rem 0;
    flex-shrink: 0;
    min-height: auto;
}

.shadow-sm-top {
    box-shadow: 0 -0.125rem 0.25rem 0 rgba(58, 59, 69, 0.075) !important;
}

/* Prevent wrapper from forcing full height */
#wrapper {
    min-height: 0 !important;
    height: auto !important;
}

/* Transition for sidebar toggle */
#wrapper #content-wrapper {
    transition: margin-left 0.3s;
    min-height: 0 !important;
    height: auto !important;
    flex: 1 0 auto !important;
}

/* Prevent excessive white space in main content */
#content {
    min-height: 0 !important;
    height: auto !important;
    flex: 0 0 auto !important;
}

.container-fluid {
    min-height: 0 !important;
    padding-bottom: 1rem;
}

.sidebar {
    transition: width 0.3s, margin 0.3s;
}

.img-profile {
    height: 2rem;
    width: 2rem;
}
</style>