<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import SignupStep from './SignupStep.vue';
import BusinessStep from './BusinessStep.vue';
import DesignationStep from './DesignationStep.vue';

const currentStep = ref(1);
const totalSteps = 3;

// Combined form data
const form = useForm({
    // Step 1: Signup
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    
    // Step 2: Business Details
    business_name: '',
    business_type: '',
    business_address: '',
    business_city: '',
    business_state: '',
    business_zip: '',
    business_country: '',
    business_phone: '',
    business_email: '',
    tax_id: '',
    registration_number: '',
    
    // Step 3: Designation & Preferences
    designation: '',
    department: '',
    currency: 'USD',
    timezone: 'UTC',
    fiscal_year_start: '01',
    date_format: 'MM/DD/YYYY',
    time_format: '12',
});

const progress = computed(() => {
    return (currentStep.value / totalSteps) * 100;
});

const canGoNext = computed(() => {
    if (currentStep.value === 1) {
        return form.name && form.email && form.password && form.password_confirmation && form.password === form.password_confirmation;
    } else if (currentStep.value === 2) {
        return form.business_name && form.business_type && form.business_phone && form.business_email;
    } else if (currentStep.value === 3) {
        return form.designation && form.currency && form.timezone;
    }
    return false;
});

const nextStep = () => {
    if (currentStep.value < totalSteps && canGoNext.value) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

const submit = () => {
    if (canGoNext.value) {
        form.post(route('onboard.store'), {
            onError: (errors) => {
                // This logs the 'error' key sent from your controller
                console.log(errors.error); 
            },
            onFinish: () => {
                // Logic for when the request completes (regardless of success/fail)
            },
        });
    }
};

const steps = [
    { number: 1, title: 'Account', description: 'Create your account' },
    { number: 2, title: 'Business', description: 'Business information' },
    { number: 3, title: 'Preferences', description: 'Setup preferences' },
];
</script>

<template>
    <div class="onboard-wrapper">
        <Head title="Onboarding" />
        
        <!-- Left Side - Form -->
        <div class="left-panel">
            <div class="form-container">
                <!-- Logo/Brand -->
                <div class="brand">
                    <h1 class="brand-title">POS System</h1>
                </div>

                <!-- Progress Indicator -->
                <div class="progress-section">
                    <div class="progress-bar">
                        <div class="progress-fill" :style="{ width: progress + '%' }"></div>
                    </div>
                    <p class="progress-text">Step {{ currentStep }} of {{ totalSteps }}</p>
                </div>

                <!-- Scrollable Section (Content + Nav + Footer) -->
                <div class="scrollable-section">
                    <!-- Step Content -->
                    <div class="content-area">
                        <transition name="slide-fade" mode="out-in">
                            <SignupStep 
                                v-if="currentStep === 1" 
                                :form="form"
                            />
                            <BusinessStep 
                                v-else-if="currentStep === 2" 
                                :form="form"
                            />
                            <DesignationStep 
                                v-else-if="currentStep === 3" 
                                :form="form"
                            />
                        </transition>
                    </div>

                    <!-- Navigation -->
                    <div class="nav-section">
                        <button 
                            v-if="currentStep > 1"
                            @click="prevStep"
                            class="btn btn-secondary"
                            type="button"
                        >
                            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                            </svg>
                            Previous
                        </button>
                        
                        <button 
                            v-if="currentStep < totalSteps"
                            @click="nextStep"
                            :disabled="!canGoNext"
                            class="btn btn-primary"
                            type="button"
                        >
                            Next
                            <svg class="btn-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>

                        <button 
                            v-else
                            @click="submit"
                            :disabled="!canGoNext || form.processing"
                            class="btn btn-primary"
                            type="button"
                        >
                            <span v-if="!form.processing">Complete Setup</span>
                            <span v-else>Processing...</span>
                        </button>
                    </div>

                    <!-- Login Link -->
                    <div class="footer-link">
                        <p>
                            Already have an account?
                            <Link :href="route('login')" class="link">Login here</Link>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Image/Illustration -->
        <div class="right-panel">
            <div class="image-content">
                <div class="step-info-card">
                    <div class="step-number">{{ currentStep }}</div>
                    <h2 class="step-heading">{{ steps[currentStep - 1].title }}</h2>
                    <p class="step-desc">{{ steps[currentStep - 1].description }}</p>
                </div>
                
                <!-- Decorative Elements -->
                <div class="decorative-circles">
                    <div class="circle circle-1"></div>
                    <div class="circle circle-2"></div>
                    <div class="circle circle-3"></div>
                </div>

                <!-- Step Indicators -->
                <div class="step-dots">
                    <div 
                        v-for="step in steps" 
                        :key="step.number"
                        class="dot"
                        :class="{ 
                            'active': currentStep === step.number,
                            'completed': currentStep > step.number 
                        }"
                    ></div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Main wrapper - split screen */
.onboard-wrapper {
    display: flex;
    height: 100vh;
    width: 100vw;
    overflow: hidden;
}

/* Left Panel - Form (40%) */
.left-panel {
    width: 40%;
    height: 100vh;
    background: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1rem;
    overflow: hidden;
}

.form-container {
    width: 100%;
    max-width: 480px;
    height: 100%;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

/* Brand */
.brand {
    flex-shrink: 0;
    margin-bottom: 2rem;
}

.brand-title {
    font-size: 1.75rem;
    font-weight: 700;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Progress Section */
.progress-section {
    flex-shrink: 0;
    margin-bottom: 1.5rem;
}

.progress-bar {
    width: 100%;
    height: 4px;
    background-color: #e5e7eb;
    border-radius: 9999px;
    overflow: hidden;
    margin-bottom: 0.75rem;
}

.progress-fill {
    height: 100%;
    background: linear-gradient(90deg, #667eea 0%, #5749a7 100%);
    border-radius: 9999px;
    transition: width 0.3s ease;
}

.progress-text {
    font-size: 0.875rem;
    color: #6b7280;
    font-weight: 500;
}

/* Scrollable Section - Everything after progress bar */
.scrollable-section {
    flex: 1;
    overflow-y: auto;
    overflow-x: hidden;
    padding-right: 0.5rem;
    display: flex;
    flex-direction: column;
    
    /* Custom scrollbar */
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 #f1f5f9;
}

.scrollable-section::-webkit-scrollbar {
    width: 4px;
}

.scrollable-section::-webkit-scrollbar-track {
    background: transparent;
}

.scrollable-section::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 10px;
}

.scrollable-section::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Content Area - No scroll (part of scrollable section) */
.content-area {
    flex: 1;
    margin-bottom: 1.5rem;
}

/* Navigation Section */
.nav-section {
    flex-shrink: 0;
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.btn {
    flex: 1;
    padding: 0.875rem 1.5rem;
    border-radius: 10px;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    outline: none;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-primary {
    background: linear-gradient(135deg, #667eea 0%, #5749a7 100%);
    color: white;
    box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
}

.btn-primary:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(102, 126, 234, 0.4);
}

.btn-secondary {
    background: #f3f4f6;
    color: #4b5563;
}

.btn-secondary:hover {
    background: #e5e7eb;
}

.btn-icon {
    width: 18px;
    height: 18px;
}

/* Footer Link */
.footer-link {
    flex-shrink: 0;
    text-align: center;
    font-size: 0.875rem;
    color: #6b7280;
}

.link {
    color: #667eea;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.2s;
}

.link:hover {
    color: #764ba2;
}

/* Right Panel - Image/Illustration (60%) */
.right-panel {
    width: 60%;
    height: 100vh;
    background: linear-gradient(135deg, #667eea 0%, #5749a7 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.image-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: white;
    padding: 3rem;
}

/* Step Info Card */
.step-info-card {
    background: rgba(255, 255, 255, 0.15);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    padding: 3rem;
    border: 1px solid rgba(255, 255, 255, 0.2);
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.2);
    margin-bottom: 3rem;
}

.step-number {
    width: 80px;
    height: 80px;
    margin: 0 auto 1.5rem;
    background: rgba(255, 255, 255, 0.25);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    font-weight: 700;
    color: white;
    border: 3px solid rgba(255, 255, 255, 0.3);
}

.step-heading {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 1rem;
    color: white;
}

.step-desc {
    font-size: 1.25rem;
    color: rgba(255, 255, 255, 0.9);
    line-height: 1.6;
}

/* Decorative Circles */
.decorative-circles {
    position: absolute;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    z-index: 1;
    pointer-events: none;
}

.circle {
    position: absolute;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.1);
    animation: float 20s infinite ease-in-out;
}

.circle-1 {
    width: 400px;
    height: 400px;
    top: -100px;
    right: -100px;
    animation-delay: 0s;
}

.circle-2 {
    width: 300px;
    height: 300px;
    bottom: -50px;
    left: -50px;
    animation-delay: 5s;
}

.circle-3 {
    width: 200px;
    height: 200px;
    top: 50%;
    left: 10%;
    animation-delay: 10s;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-30px) rotate(180deg);
    }
}

/* Step Dots */
.step-dots {
    display: flex;
    gap: 1rem;
    justify-content: center;
    position: relative;
    z-index: 2;
}

.dot {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.3);
    transition: all 0.3s ease;
}

.dot.active {
    background: white;
    transform: scale(1.3);
    box-shadow: 0 0 20px rgba(255, 255, 255, 0.6);
}

.dot.completed {
    background: rgba(255, 255, 255, 0.7);
}

/* Transitions */
.slide-fade-enter-active {
    transition: all 0.3s ease;
}

.slide-fade-leave-active {
    transition: all 0.2s ease;
}

.slide-fade-enter-from {
    transform: translateX(20px);
    opacity: 0;
}

.slide-fade-leave-to {
    transform: translateX(-20px);
    opacity: 0;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .left-panel {
        width: 45%;
    }
    
    .right-panel {
        width: 55%;
    }
    
    .step-heading {
        font-size: 2rem;
    }
    
    .step-desc {
        font-size: 1.1rem;
    }
}

@media (max-width: 768px) {
    .onboard-wrapper {
        flex-direction: column;
    }
    
    .left-panel {
        width: 100%;
        height: 100vh;
        padding: 1.5rem;
    }
    
    .right-panel {
        display: none;
    }
    
    .form-container {
        max-width: 100%;
    }
}

/* Ensure no body scroll */
:global(body) {
    overflow: hidden;
}
</style>
