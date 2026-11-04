<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import TextInput from '@/Components/TextInput.vue';
import TextArea from '@/Components/TextArea.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Select from '@/Components/Select.vue';

const props = defineProps({
    user: {
        type: Object,
    },
});

const form = useForm({
    id: null,
    first_name: '',
    last_name: '',
    email: '',
    acc_type: '',
    password: '',
    password_confirmation: '',
    dob: '',
    gender: '',
    marital_status: '',
    mobile_no: '',
    permanent_address: '',
    current_address: '',
    account_holder_name: '',
    account_no: '',
    bank_name: '',
    branch_name: '',
    bic: '',
    basic_salary: '',
    salary_frequency: '',
});

onMounted(() => {

    if(props.user.user_detail){    
        form.id = props.user.id;
        form.first_name = props.user.user_detail.first_name;
        form.last_name = props.user.user_detail.last_name;
        form.email = props.user.email;
        form.acc_type = props.user.user_detail.account_type;
        form.password = props.user.user_detail.password;
        form.password_confirmation = props.user.user_detail.password;
        form.dob = props.user.user_detail.date_of_birth;
        form.gender = props.user.user_detail.gender;
        form.marital_status = props.user.user_detail.marital_status;
        form.mobile_no = props.user.user_detail.mobile;
        form.permanent_address = props.user.user_detail.permanent_address;
        form.current_address = props.user.user_detail.current_address;
        form.account_holder_name = props.user.user_detail.bank_account_holder_name;
        form.account_no = props.user.user_detail.bank_account_no;
        form.bank_name = props.user.user_detail.bank_name;
        form.branch_name = props.user.user_detail.bank_branch_name;
        form.bic = props.user.user_detail.bank_identifier_code;
        form.basic_salary = props.user.user_detail.basic_salary;
        form.salary_frequency = props.user.user_detail.salary_frequency;
    }

    else{
        form.id = props.user.id;
        form.email = props.user.email; 
    }
});

const submit = () => {
    form.post(route('updateUser'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
    });
};

const accountOptions = ref([
    { id: 'Admin', name: 'Admin' },
    { id: 'Cashier', name: 'Cashier' },
    { id: 'Manager', name: 'Manager' },
    { id: 'Rider', name: 'Rider' },
    { id: 'Salesman', name: 'Salesman' },
    { id: 'Waiter', name: 'Waiter' },
    { id: 'Chef', name: 'Chef' },
]);

const genderOptions = ref([
    { id: 'Male', name: 'Male' },
    { id: 'Female', name: 'Female' },
    { id: 'Others', name: 'Others' },
]);

const maritalOptions = ref([
    { id: 'Single', name: 'Single' },
    { id: 'Married', name: 'Married' },
    { id: 'Divorced', name: 'Divorced' },
]);

const salaryFrequencyOptions = ref([
    { id: 'Monthly', name: 'Monthly' },
    { id: 'Weekly', name: 'Weekly' },
    { id: 'Daily', name: 'Daily' },
]);

const isFormValid = computed(() => {
    return !!(
        form.first_name && 
        form.last_name && 
        form.email &&
        form.acc_type &&
        form.password &&
        form.password_confirmation
    );
});

</script>

<template>

    <Head title="User" />
    <AuthenticatedLayout>
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between">
            <h1 class="h3 mb-0 text-gray-800">Edit User</h1>
        </div>
        <form @submit.prevent="submit">
            <!-- Personal Information -->
            <div class="row pt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h1>Personal Information</h1>
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <InputLabel for="first_name" value="First Name" required/>
                                    <TextInput id="first_name" type="text" class="mt-1 w-full" v-model="form.first_name" placeholder="Enter First Name" autofocus required/>
                                </div>
                                <div class="col-md-3">
                                    <InputLabel for="last_name" value="Last Name" required/>
                                    <TextInput id="last_name" type="text" class="mt-1 w-full" v-model="form.last_name" placeholder="Enter Last Name" required/>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <InputLabel for="email" value="Email" required/>
                                    <TextInput id="email" type="email" class="mt-1 w-full" v-model="form.email" placeholder="Enter Email" required/>
                                </div>
                                <div class="col-md-3">
                                    <InputLabel for="acc_type" value="Account Type" required/>
                                    <Select 
                                        class="mt-1 w-full" 
                                        id="acc_type" 
                                        v-model="form.acc_type"
                                        :options="accountOptions"
                                    />
                                </div>
                                
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <InputLabel for="password" value="Password" required/>
                                    <TextInput id="password" type="text" class="mt-1 w-full" v-model="form.password" autocomplete="new-password"  placeholder="Enter Password" required/>
                                </div>
                                <div class="col-md-3">
                                    <InputLabel for="password_confirmation" value="Confirm Password" required/>
                                    <TextInput id="password_confirmation" type="text" class="mt-1 w-full" v-model="form.password_confirmation" autocomplete="new-password"  placeholder="Enter Confirm Password" required/>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div> 
            
            <!-- More Informations -->
            <div class="row pt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h1>More Informations</h1>
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <InputLabel for="dob" value="Date of Birth"/>
                                    <TextInput id="dob" type="date" class="mt-1 w-full" v-model="form.dob" placeholder="Enter Date of Birth"/>
                                </div>
                                <div class="col-md-3">
                                    <InputLabel for="gender" value="Gender"/>
                                    <Select 
                                        class="mt-1 w-full" 
                                        id="gender" 
                                        v-model="form.gender"
                                        :options="genderOptions"
                                    />
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <InputLabel for="marital_status" value="Marital Status"/>
                                    <Select 
                                        class="mt-1 w-full" 
                                        id="marital_status" 
                                        v-model="form.marital_status"
                                        :options="maritalOptions"
                                    />
                                </div>
                                <div class="col-md-3">
                                    <InputLabel for="mobile_no" value="Mobile No."/>
                                    <TextInput id="mobile_no" type="text" class="mt-1 w-full"
                                        v-model="form.mobile_no" placeholder="Enter Mobile No."/>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <InputLabel for="permanent_address" value="Permanent Address"/>
                                    <TextArea id="permanent_address" class="mt-1 w-full"
                                        v-model="form.permanent_address" placeholder="Enter Permanent Address"/>
                                </div>
                                <div class="col-md-3">
                                    <InputLabel for="current_address" value="Current Address"/>
                                    <TextArea id="current_address" class="mt-1 w-full"
                                        v-model="form.current_address" placeholder="Enter Current Address"/>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>
            </div>  
            
            <!-- Bank Informations -->
            <div class="row pt-3">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-body">
                            <h1>Bank Informations</h1>
                            <div class="row mt-4">
                                <div class="col-md-3">
                                    <InputLabel for="account_holder_name" value="Account Holder's Name"/>
                                    <TextInput id="account_holder_name" type="text" class="mt-1 w-full" v-model="form.account_holder_name"
                                        placeholder="Enter Account Holder's Name"/>
                                </div>
                                <div class="col-md-3">
                                    <InputLabel for="account_no" value="Account No."/>
                                    <TextInput id="account_no" type="text" class="mt-1 w-full" v-model="form.account_no"
                                        placeholder="Enter Account No."/>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <InputLabel for="bank_name" value="Bank Name"/>
                                    <TextInput id="bank_name" type="text" class="mt-1 w-full" v-model="form.bank_name"
                                        placeholder="Enter Bank Name"/>
                                </div>
                                <div class="col-md-3">
                                    <InputLabel for="branch_name" value="Branch Name"/>
                                    <TextInput id="branch_name" type="text" class="mt-1 w-full" v-model="form.branch_name"
                                        placeholder="Enter Branch Name"/>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <InputLabel for="bic" value="Bank Identifier Code"/>
                                    <TextInput id="bic" type="text" class="mt-1 w-full"
                                        v-model="form.bic" placeholder="Enter Bank Identifier Code"/>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-3">
                                    <InputLabel for="basic_salary" value="Basic salary"/>
                                    <TextInput id="basic_salary" type="text" class="mt-1 w-full" v-model="form.basic_salary"
                                        placeholder="Enter Basic salary"/>
                                </div>
                                <div class="col-md-3">
                                    <InputLabel for="salary_frequency" value="Salary Frequency"/>
                                    <Select 
                                        class="mt-1 w-full" 
                                        id="salary_frequency" 
                                        v-model="form.salary_frequency"
                                        :options="salaryFrequencyOptions"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end mt-4 mb-4">
                <PrimaryButton class="btn btn-primary px-5 py-2" :disabled="form.processing || !isFormValid" :class="{'opacity-50 cursor-not-allowed': form.processing || !isFormValid}">
                    Save
                </PrimaryButton>
            </div> 
        </form>
    </AuthenticatedLayout>
</template>
