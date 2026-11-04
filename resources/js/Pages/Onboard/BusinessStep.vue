<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import Select from '@/Components/Select.vue';
import TextArea from '@/Components/TextArea.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
});

const businessTypes = [
    { id: '', name: 'Select Business Type' },
    { id: 'retail', name: 'Retail Store' },
    { id: 'restaurant', name: 'Restaurant / Cafe' },
    { id: 'grocery', name: 'Grocery Store' },
    { id: 'pharmacy', name: 'Pharmacy' },
    { id: 'automotive', name: 'Automotive' },
    { id: 'electronics', name: 'Electronics Store' },
    { id: 'clothing', name: 'Clothing / Fashion' },
    { id: 'hardware', name: 'Hardware Store' },
    { id: 'bookstore', name: 'Bookstore' },
    { id: 'salon', name: 'Salon / Spa' },
    { id: 'other', name: 'Other' },
];

const countries = [
    { id: '', name: 'Select Country' },
    { id: 'US', name: 'United States' },
    { id: 'CA', name: 'Canada' },
    { id: 'GB', name: 'United Kingdom' },
    { id: 'AU', name: 'Australia' },
    { id: 'IN', name: 'India' },
    { id: 'PK', name: 'Pakistan' },
    { id: 'BD', name: 'Bangladesh' },
    { id: 'AE', name: 'United Arab Emirates' },
    { id: 'SA', name: 'Saudi Arabia' },
    { id: 'other', name: 'Other' },
];
</script>

<template>
    <div class="step-container">
        <div class="step-header">
            <div class="step-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
            </div>
            <h2 class="step-title">Business Information</h2>
            <!-- <p class="step-description">Tell us about your business</p> -->
        </div>

        <div class="form-grid">
            <!-- Business Name -->
            <div class="">
                <InputLabel for="business_name" value="Business Name" required />
                <TextInput
                    id="business_name"
                    type="text"
                    class="block w-full text-sm"
                    v-model="form.business_name"
                    required
                    placeholder="ABC Store"
                />
                <InputError class="mt-2" :message="form.errors.business_name" />
            </div>

            <!-- Business Type -->
            <div class="">
                <InputLabel for="business_type" value="Business Type" required />
                <Select
                    id="business_type"
                    v-model="form.business_type"
                    :options="businessTypes"
                    class="block w-full text-sm"
                    required
                />
                <InputError class="mt-2" :message="form.errors.business_type" />
            </div>

            <!-- Registration Number -->
            <!-- <div class="">
                <InputLabel for="registration_number" value="Registration Number" />
                <TextInput
                    id="registration_number"
                    type="text"
                    class="block w-full text-sm"
                    v-model="form.registration_number"
                    placeholder="REG123456"
                />
                <InputError class="mt-2" :message="form.errors.registration_number" />
            </div> -->

            <!-- Business Address -->
            <!-- <div class="form-group full-width">
                <InputLabel for="business_address" value="Business Address" />
                <TextArea
                    id="business_address"
                    class="block w-full text-sm"
                    v-model="form.business_address"
                    rows="2"
                    placeholder="123 Main Street"
                />
                <InputError class="mt-2" :message="form.errors.business_address" />
            </div> -->

            <!-- City -->
            <!-- <div class="">
                <InputLabel for="business_city" value="City" />
                <TextInput
                    id="business_city"
                    type="text"
                    class="block w-full text-sm"
                    v-model="form.business_city"
                    placeholder="New York"
                />
                <InputError class="mt-2" :message="form.errors.business_city" />
            </div> -->

            <!-- State/Province -->
            <!-- <div class="">
                <InputLabel for="business_state" value="State / Province" />
                <TextInput
                    id="business_state"
                    type="text"
                    class="block w-full text-sm"
                    v-model="form.business_state"
                    placeholder="NY"
                />
                <InputError class="mt-2" :message="form.errors.business_state" />
            </div> -->

            <!-- ZIP Code -->
            <!-- <div class="">
                <InputLabel for="business_zip" value="ZIP / Postal Code" />
                <TextInput
                    id="business_zip"
                    type="text"
                    class="block w-full text-sm"
                    v-model="form.business_zip"
                    placeholder="10001"
                />
                <InputError class="mt-2" :message="form.errors.business_zip" />
            </div> -->

            <!-- Country -->
            <!-- <div class="">
                <InputLabel for="business_country" value="Country" />
                <Select
                    id="business_country"
                    v-model="form.business_country"
                    :options="countries"
                    class="block w-full text-sm"
                />
                <InputError class="mt-2" :message="form.errors.business_country" />
            </div> -->

            <!-- Business Phone -->
            <div class="">
                <InputLabel for="business_phone" value="Business Phone" required />
                <TextInput
                    id="business_phone"
                    type="tel"
                    class="block w-full text-sm"
                    v-model="form.business_phone"
                    required
                    placeholder="+1 (555) 123-4567"
                />
                <InputError class="mt-2" :message="form.errors.business_phone" />
            </div>

            <!-- Business Email -->
            <div class="">
                <InputLabel for="business_email" value="Business Email" />
                <TextInput
                    id="business_email"
                    type="email"
                    class="block w-full text-sm"
                    v-model="form.business_email"
                    placeholder="contact@business.com"
                />
                <InputError class="mt-2" :message="form.errors.business_email" />
            </div>

            <!-- Tax ID -->
            <!-- <div class="">
                <InputLabel for="tax_id" value="Tax ID / VAT Number" />
                <TextInput
                    id="tax_id"
                    type="text"
                    class="block w-full text-sm"
                    v-model="form.tax_id"
                    placeholder="XX-XXXXXXX"
                />
                <InputError class="mt-2" :message="form.errors.tax_id" />
                <p class="mt-1 text-sm text-gray-500">Enter your tax identification number if applicable</p>
            </div> -->
        </div>

        <!-- Info Box -->
        <!-- <div class="info-box">
            <div class="info-header">
                <svg class="info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                </svg>
                <span>Your Information is Secure</span>
            </div>
            <p class="info-text">
                All business information is encrypted and stored securely. This information will be used for invoices, receipts, and business reports.
            </p>
        </div> -->
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

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 1.5rem;
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

.info-box {
    background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
    border: 1px solid #bbf7d0;
    border-radius: 8px;
    padding: 1rem;
}

.info-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #166534;
    margin-bottom: 0.5rem;
}

.info-icon {
    width: 20px;
    height: 20px;
}

.info-text {
    padding-left: 1.75rem;
    color: #166534;
    font-size: 0.875rem;
    line-height: 1.5;
}

@media (max-width: 640px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
