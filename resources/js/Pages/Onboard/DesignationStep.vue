<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import Select from '@/Components/Select.vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
});

const designations = [
    { id: '', name: 'Select Your Role' },
    { id: 'Admin', name: 'Owner' },
    { id: 'manager', name: 'Manager' },
    { id: 'supervisor', name: 'Supervisor' },
    { id: 'cashier', name: 'Cashier' },
    { id: 'accountant', name: 'Accountant' },
    { id: 'sales_manager', name: 'Sales Manager' },
    { id: 'inventory_manager', name: 'Inventory Manager' },
    { id: 'admin', name: 'Administrator' },
    { id: 'other', name: 'Other' },
];

const departments = [
    { value: '', label: 'Select Department' },
    { value: 'management', label: 'Management' },
    { value: 'sales', label: 'Sales' },
    { value: 'inventory', label: 'Inventory' },
    { value: 'accounting', label: 'Accounting' },
    { value: 'customer_service', label: 'Customer Service' },
    { value: 'operations', label: 'Operations' },
    { value: 'it', label: 'IT / Technology' },
    { value: 'other', label: 'Other' },
];

const currencies = [
    { value: 'USD', label: 'USD - US Dollar ($)', symbol: '$' },
    { value: 'EUR', label: 'EUR - Euro (€)', symbol: '€' },
    { value: 'GBP', label: 'GBP - British Pound (£)', symbol: '£' },
    { value: 'INR', label: 'INR - Indian Rupee (₹)', symbol: '₹' },
    { value: 'PKR', label: 'PKR - Pakistani Rupee (₨)', symbol: '₨' },
    { value: 'BDT', label: 'BDT - Bangladeshi Taka (৳)', symbol: '৳' },
    { value: 'AED', label: 'AED - UAE Dirham (د.إ)', symbol: 'د.إ' },
    { value: 'SAR', label: 'SAR - Saudi Riyal (﷼)', symbol: '﷼' },
    { value: 'CAD', label: 'CAD - Canadian Dollar ($)', symbol: '$' },
    { value: 'AUD', label: 'AUD - Australian Dollar ($)', symbol: '$' },
];

const timezones = [
    { value: 'UTC', label: 'UTC - Coordinated Universal Time' },
    { value: 'America/New_York', label: 'Eastern Time (US & Canada)' },
    { value: 'America/Chicago', label: 'Central Time (US & Canada)' },
    { value: 'America/Denver', label: 'Mountain Time (US & Canada)' },
    { value: 'America/Los_Angeles', label: 'Pacific Time (US & Canada)' },
    { value: 'Europe/London', label: 'London (GMT)' },
    { value: 'Europe/Paris', label: 'Paris, Berlin, Rome' },
    { value: 'Asia/Dubai', label: 'Dubai, Abu Dhabi' },
    { value: 'Asia/Karachi', label: 'Karachi, Islamabad' },
    { value: 'Asia/Kolkata', label: 'Mumbai, Kolkata, New Delhi' },
    { value: 'Asia/Dhaka', label: 'Dhaka' },
    { value: 'Asia/Singapore', label: 'Singapore, Hong Kong' },
    { value: 'Australia/Sydney', label: 'Sydney, Melbourne' },
];

const months = [
    { value: '01', label: 'January' },
    { value: '02', label: 'February' },
    { value: '03', label: 'March' },
    { value: '04', label: 'April' },
    { value: '05', label: 'May' },
    { value: '06', label: 'June' },
    { value: '07', label: 'July' },
    { value: '08', label: 'August' },
    { value: '09', label: 'September' },
    { value: '10', label: 'October' },
    { value: '11', label: 'November' },
    { value: '12', label: 'December' },
];

const dateFormats = [
    { value: 'MM/DD/YYYY', label: 'MM/DD/YYYY (12/31/2024)' },
    { value: 'DD/MM/YYYY', label: 'DD/MM/YYYY (31/12/2024)' },
    { value: 'YYYY-MM-DD', label: 'YYYY-MM-DD (2024-12-31)' },
    { value: 'DD-MMM-YYYY', label: 'DD-MMM-YYYY (31-Dec-2024)' },
];

const timeFormats = [
    { value: '12', label: '12-hour (3:30 PM)' },
    { value: '24', label: '24-hour (15:30)' },
];
</script>

<template>
    <div class="step-container">
        <div class="step-header">
            <div class="step-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            </div>
            <h2 class="step-title">Preferences & Settings</h2>
            <!-- <p class="step-description">Customize your POS system settings</p> -->
        </div>

        <div class="">
            <!-- Role & Department Section -->
            <div class="">
                <!-- <h3 class="section-title">
                    <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Role & Department
                </h3> -->
                
                <div class="form-grid">
                    <div class="form-group">
                        <InputLabel for="designation" value="Your Designation" required />
                        <Select
                            id="designation"
                            v-model="form.designation"
                            class="mt-1 block w-full input-enhanced border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                            :options="designations"
                        />
                        <InputError class="mt-2" :message="form.errors.designation" />
                    </div>

                    <!-- <div class="form-group">
                        <InputLabel for="department" value="Department" />
                        <select
                            id="department"
                            v-model="form.department"
                            class="mt-1 block w-full input-enhanced border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option v-for="dept in departments" :key="dept.value" :value="dept.value">
                                {{ dept.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.department" />
                    </div> -->
                </div>
            </div>

            <!-- Regional Settings Section -->
            <!-- <div class="section">
                <h3 class="section-title">
                    <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Regional Settings
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <InputLabel for="currency" value="Currency *" />
                        <select
                            id="currency"
                            v-model="form.currency"
                            class="mt-1 block w-full input-enhanced border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option v-for="currency in currencies" :key="currency.value" :value="currency.value">
                                {{ currency.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.currency" />
                    </div>

                    <div class="form-group">
                        <InputLabel for="timezone" value="Timezone *" />
                        <select
                            id="timezone"
                            v-model="form.timezone"
                            class="mt-1 block w-full input-enhanced border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                            required
                        >
                            <option v-for="tz in timezones" :key="tz.value" :value="tz.value">
                                {{ tz.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.timezone" />
                    </div>
                </div>
            </div> -->

            <!-- Display Preferences Section -->
            <!-- <div class="section">
                <h3 class="section-title">
                    <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
                    </svg>
                    Display Preferences
                </h3>
                
                <div class="form-grid">
                    <div class="form-group">
                        <InputLabel for="date_format" value="Date Format" />
                        <select
                            id="date_format"
                            v-model="form.date_format"
                            class="mt-1 block w-full input-enhanced border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option v-for="format in dateFormats" :key="format.value" :value="format.value">
                                {{ format.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.date_format" />
                    </div>

                    <div class="form-group">
                        <InputLabel for="time_format" value="Time Format" />
                        <select
                            id="time_format"
                            v-model="form.time_format"
                            class="mt-1 block w-full input-enhanced border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option v-for="format in timeFormats" :key="format.value" :value="format.value">
                                {{ format.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.time_format" />
                    </div>
                </div>
            </div> -->

            <!-- Fiscal Year Section -->
            <!-- <div class="section">
                <h3 class="section-title">
                    <svg class="section-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Fiscal Year
                </h3>
                
                <div class="form-grid">
                    <div class="form-group full-width">
                        <InputLabel for="fiscal_year_start" value="Fiscal Year Start Month" />
                        <select
                            id="fiscal_year_start"
                            v-model="form.fiscal_year_start"
                            class="mt-1 block w-full input-enhanced border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                        >
                            <option v-for="month in months" :key="month.value" :value="month.value">
                                {{ month.label }}
                            </option>
                        </select>
                        <InputError class="mt-2" :message="form.errors.fiscal_year_start" />
                        <p class="mt-1 text-sm text-gray-500">Select the month when your fiscal year begins</p>
                    </div>
                </div>
            </div> -->
        </div>

        <!-- Success Message -->
        <div class="success-box ">
            <div class="success-content ">
                <svg class="success-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="success-title text-xs">Almost Done!</h4>
                    <p class="success-text text-xs">Click "Complete Setup" to finish your onboarding and start using your POS system.</p>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.step-container {
    animation: fadeIn 0.3s ease;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.step-header {
    text-align: center;
    margin-bottom: 2rem;
}

.step-icon {
    width: 64px;
    height: 64px;
    margin: 0 auto 1rem;
    background: linear-gradient(135deg, #667eea 0%, #5749a7 100%);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
}

.step-icon svg {
    width: 32px;
    height: 32px;
}

.step-title {
    font-size: 1.5rem;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 0.5rem;
}

.step-description {
    font-size: 0.875rem;
    color: #6b7280;
}

.form-sections {
    display: flex;
    flex-direction: column;
    gap: 2rem;
    margin-bottom: 1.5rem;
}

.section {
    background: #f9fafb;
    border-radius: 8px;
    padding: 1.5rem;
    border: 1px solid #e5e7eb;
}

.section-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #1f2937;
    margin-bottom: 1rem;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.section-icon {
    width: 24px;
    height: 24px;
    color: #667eea;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.input-enhanced {
    transition: all 0.2s ease;
}

.input-enhanced:focus {
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
}

.success-box {
    background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
    border: 2px solid #86efac;
    border-radius: 12px;
    padding: 1rem;
}

.success-content {
    display: flex;
    gap: 1rem;
    align-items: flex-start;
}

.success-icon {
    width: 32px;
    height: 32px;
    color: #16a34a;
    flex-shrink: 0;
}

.success-title {
    font-size: 1.125rem;
    font-weight: 600;
    color: #166534;
    margin-bottom: 0.25rem;
}

.success-text {
    font-size: 0.875rem;
    color: #166534;
    line-height: 1.5;
}

@media (max-width: 640px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .section {
        padding: 1rem;
    }
}
</style>
