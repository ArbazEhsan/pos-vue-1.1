<script setup>
import { computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
});

const passwordStrength = computed(() => {
    const password = props.form.password;
    if (!password) return { strength: 0, label: '', color: '' };
    
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^a-zA-Z\d]/.test(password)) strength++;
    
    if (strength <= 2) return { strength, label: 'Weak', color: '#ef4444' };
    if (strength <= 3) return { strength, label: 'Fair', color: '#f59e0b' };
    if (strength <= 4) return { strength, label: 'Good', color: '#3b82f6' };
    return { strength, label: 'Strong', color: '#10b981' };
});

const passwordsMatch = computed(() => {
    return props.form.password && props.form.password_confirmation && 
           props.form.password === props.form.password_confirmation;
});
</script>

<template>
    <div class="step-container">
        <div class="step-header">
            <div class="step-icon">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
            </div>
            <h2 class="step-title">Create Your Account</h2>
            <!-- <p class="step-description">Enter your personal information to get started</p> -->
        </div>

        <div class="form-grid">
            <!-- Full Name -->
            <div class="">
                <InputLabel for="name" value="Full Name" required />
                <TextInput
                    id="name"
                    type="text"
                    class="block w-full text-sm"
                    v-model="form.name"
                    required
                    autofocus
                    placeholder="John Doe"
                />
                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <!-- Email -->
            <div class="">
                <InputLabel for="email" value="Email Address" required />
                <TextInput
                    id="email"
                    type="email"
                    class="block w-full text-sm"
                    v-model="form.email"
                    required
                    autocomplete="username"
                    placeholder="john.doe@example.com"
                />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <!-- Password -->
            <div class="form-group">
                <InputLabel for="password" value="Password" required />
                <TextInput
                    id="password"
                    type="password"
                    class="block w-full text-sm"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-2" :message="form.errors.password" />
                
                <!-- Password Strength Indicator -->
                <div v-if="form.password" class="password-strength mt-2">
                    <div class="strength-bar">
                        <div 
                            class="strength-fill" 
                            :style="{ 
                                width: (passwordStrength.strength * 20) + '%',
                                backgroundColor: passwordStrength.color 
                            }"
                        ></div>
                    </div>
                    <span class="strength-label" :style="{ color: passwordStrength.color }">
                        {{ passwordStrength.label }}
                    </span>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="">
                <InputLabel for="password_confirmation" value="Confirm Password" required />
                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="block w-full text-sm"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                    placeholder="••••••••"
                />
                <InputError class="mt-2" :message="form.errors.password_confirmation" />
                
                <!-- Password Match Indicator -->
                <div v-if="form.password_confirmation" class="match-indicator mt-2">
                    <span v-if="passwordsMatch" class="match-success">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Passwords match
                    </span>
                    <span v-else class="match-error">
                        <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Passwords don't match
                    </span>
                </div>
            </div>
        </div>

        <!-- Password Requirements -->
        <!-- <div class="info-box">
            <div class="info-header">
                <svg class="info-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span>Password Requirements</span>
            </div>
            <ul class="info-list">
                <li>At least 8 characters long</li>
                <li>Contains uppercase and lowercase letters</li>
                <li>Includes at least one number</li>
                <li>Has at least one special character</li>
            </ul>
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

.password-strength {
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.strength-bar {
    flex: 1;
    height: 4px;
    background-color: #e5e7eb;
    border-radius: 9999px;
    overflow: hidden;
}

.strength-fill {
    height: 100%;
    transition: all 0.3s ease;
    border-radius: 9999px;
}

.strength-label {
    font-size: 0.75rem;
    font-weight: 600;
    min-width: 60px;
}

.match-indicator {
    font-size: 0.875rem;
    font-weight: 500;
}

.match-success {
    color: #10b981;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.match-error {
    color: #ef4444;
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.match-indicator .icon {
    width: 16px;
    height: 16px;
}

.info-box {
    background: linear-gradient(135deg, #eff6ff 0%, #f0f9ff 100%);
    border: 1px solid #bfdbfe;
    border-radius: 8px;
    padding: 1rem;
}

.info-header {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #1e40af;
    margin-bottom: 0.5rem;
}

.info-icon {
    width: 20px;
    height: 20px;
}

.info-list {
    list-style: none;
    padding-left: 1.75rem;
    color: #1e40af;
    font-size: 0.875rem;
}

.info-list li {
    position: relative;
    margin-bottom: 0.25rem;
}

.info-list li::before {
    content: "•";
    position: absolute;
    left: -1rem;
    color: #3b82f6;
}

@media (max-width: 640px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
}
</style>
