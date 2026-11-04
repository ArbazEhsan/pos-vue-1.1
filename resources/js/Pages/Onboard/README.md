# Onboarding Flow Documentation

## Overview
A professional multi-step onboarding flow for the POS system that guides new users through account creation, business setup, and preference configuration.

## Features

### Step 1: Account Creation
- Full name input
- Email address with validation
- Password with strength indicator
- Password confirmation with match validation
- Real-time validation feedback

### Step 2: Business Details
- Business name and type selection
- Complete address information (street, city, state, zip, country)
- Business contact details (phone and email)
- Tax ID and registration number
- Security assurance message

### Step 3: Designation & Preferences
- User role/designation selection
- Department assignment
- Regional settings:
  - Currency selection (USD, EUR, GBP, INR, PKR, etc.)
  - Timezone configuration
- Display preferences:
  - Date format (MM/DD/YYYY, DD/MM/YYYY, etc.)
  - Time format (12-hour or 24-hour)
- Fiscal year start month

## Components Created

### Main Components
1. **Index.vue** - Main onboarding container with step navigation
2. **SignupStep.vue** - User account creation step
3. **BusinessStep.vue** - Business information step
4. **DesignationStep.vue** - Preferences and settings step

### Controller
- **OnboardController.php** - Handles form submission and data processing

## Design Features

### Visual Design
- Modern gradient color scheme (purple/indigo theme)
- Smooth animations and transitions
- Progress bar showing completion percentage
- Step indicators with checkmarks for completed steps
- Responsive design for mobile and desktop
- Professional form layouts with proper spacing

### User Experience
- Clear step-by-step progression
- Real-time validation feedback
- Password strength indicator
- Disabled "Next" button until required fields are filled
- Previous/Next navigation
- Visual feedback for completed steps

## Integration Steps

### 1. Add Routes
Add these routes to your `routes/web.php`:

```php
use App\Http\Controllers\OnboardController;

// Onboarding routes (outside auth middleware if for new users)
Route::get('/onboard', [OnboardController::class, 'index'])->name('onboard.index');
Route::post('/onboard', [OnboardController::class, 'store'])->name('onboard.store');
```

### 2. Database Setup (Optional)
If you want to store business and preference data separately, create migrations:

```bash
php artisan make:migration create_business_settings_table
php artisan make:migration create_user_preferences_table
```

Example migration for business_settings:
```php
Schema::create('business_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('name');
    $table->string('type');
    $table->text('address')->nullable();
    $table->string('city')->nullable();
    $table->string('state')->nullable();
    $table->string('zip')->nullable();
    $table->string('country')->nullable();
    $table->string('phone');
    $table->string('email');
    $table->string('tax_id')->nullable();
    $table->string('registration_number')->nullable();
    $table->timestamps();
});
```

Example migration for user_preferences:
```php
Schema::create('user_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->string('designation');
    $table->string('department')->nullable();
    $table->string('currency', 10)->default('USD');
    $table->string('timezone')->default('UTC');
    $table->string('fiscal_year_start', 2)->default('01');
    $table->string('date_format')->default('MM/DD/YYYY');
    $table->string('time_format', 10)->default('12');
    $table->timestamps();
});
```

### 3. Create Models (Optional)
```bash
php artisan make:model BusinessSetting
php artisan make:model UserPreference
```

### 4. Update Controller
Update the `OnboardController.php` to use your models if you created them:

```php
BusinessSetting::create($businessData);
UserPreference::create($preferencesData);
```

### 5. Access the Onboarding
Navigate to: `http://your-domain/onboard`

## Customization

### Colors
The gradient colors can be customized in each component's `<style>` section:
- Primary gradient: `#667eea` to `#764ba2`
- Success gradient: `#10b981` to `#059669`

### Fields
To add or remove fields:
1. Update the form object in `Index.vue`
2. Add/remove inputs in the respective step component
3. Update validation in `OnboardController.php`

### Business Types
Edit the `businessTypes` array in `BusinessStep.vue` to add more options.

### Currencies/Timezones
Edit the respective arrays in `DesignationStep.vue`.

## Validation Rules

### Password Requirements
- Minimum 8 characters
- Contains uppercase and lowercase letters
- Includes at least one number
- Has at least one special character

### Required Fields
- **Step 1:** Name, Email, Password, Password Confirmation
- **Step 2:** Business Name, Business Type, Business Phone, Business Email
- **Step 3:** Designation, Currency, Timezone

## Browser Compatibility
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## Mobile Responsive
The onboarding flow is fully responsive and works on:
- Desktop (1024px+)
- Tablet (768px - 1023px)
- Mobile (320px - 767px)

## Future Enhancements
- Email verification step
- Phone number verification
- Business logo upload
- Multi-language support
- Social login integration
- Skip option for optional steps
- Save progress and resume later
