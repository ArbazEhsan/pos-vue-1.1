# User Flow Documentation

## Complete User Journey

### Flow Diagram

```
┌─────────────────────────────────────────────────────────────┐
│                      User Arrives                            │
└─────────────────────┬───────────────────────────────────────┘
                      │
                      ▼
            ┌─────────────────────┐
            │   Has Account?      │
            └─────────┬───────────┘
                      │
         ┌────────────┴────────────┐
         │                         │
         ▼ YES                     ▼ NO
┌─────────────────┐       ┌─────────────────┐
│  Login Page     │       │ Onboarding Page │
│  /login         │       │  /onboard       │
└────────┬────────┘       └────────┬────────┘
         │                         │
         │                         ▼
         │              ┌──────────────────────┐
         │              │  Step 1: Signup      │
         │              │  - Full Name         │
         │              │  - Email             │
         │              │  - Password          │
         │              │  - Confirm Password  │
         │              └──────────┬───────────┘
         │                         │
         │                         ▼
         │              ┌──────────────────────┐
         │              │  Step 2: Business    │
         │              │  - Business Name     │
         │              │  - Business Type     │
         │              │  - Address           │
         │              │  - Contact Info      │
         │              │  - Tax ID            │
         │              └──────────┬───────────┘
         │                         │
         │                         ▼
         │              ┌──────────────────────┐
         │              │  Step 3: Preferences │
         │              │  - Designation       │
         │              │  - Department        │
         │              │  - Currency          │
         │              │  - Timezone          │
         │              │  - Date/Time Format  │
         │              │  - Fiscal Year       │
         │              └──────────┬───────────┘
         │                         │
         │                         ▼
         │              ┌──────────────────────┐
         │              │  Complete Setup      │
         │              │  (Submit Form)       │
         │              └──────────┬───────────┘
         │                         │
         │                         ▼
         │              ┌──────────────────────┐
         │              │  Redirect to Login   │
         │              │  with Success Msg    │
         │              └──────────┬───────────┘
         │                         │
         └─────────────────────────┘
                      │
                      ▼
         ┌────────────────────────┐
         │  User Logs In          │
         │  (Email + Password)    │
         └────────────┬───────────┘
                      │
                      ▼
         ┌────────────────────────┐
         │  Dashboard / Home      │
         │  (Authenticated)       │
         └────────────────────────┘
```

## Routes Configuration

### Guest Routes (Unauthenticated Users)
```php
// Login page - existing users
GET /login -> Login.vue

// Onboarding flow - new users
GET /onboard -> Onboard/Index.vue
POST /onboard -> OnboardController@store

// Traditional register (optional, can be disabled)
GET /register -> Register.vue
POST /register -> RegisteredUserController@store
```

### Authenticated Routes
```php
// After successful login
GET / -> Home
GET /dashboard -> Dashboard
// ... other authenticated routes
```

## User Scenarios

### Scenario 1: New User Registration
1. User visits the application
2. Sees login page with "Don't have an account? Sign up here" link
3. Clicks "Sign up here" → Redirected to `/onboard`
4. Completes Step 1 (Account Creation)
5. Completes Step 2 (Business Details)
6. Completes Step 3 (Preferences)
7. Clicks "Complete Setup"
8. Account is created in database
9. Redirected to `/login` with success message
10. User logs in with credentials
11. Redirected to dashboard/home

### Scenario 2: Existing User Login
1. User visits the application
2. Sees login page
3. Enters email and password
4. Clicks "Log in"
5. Authenticated and redirected to dashboard/home

### Scenario 3: User on Onboarding Page (Already Has Account)
1. User accidentally lands on `/onboard`
2. Sees "Already have an account? Login here" link at bottom
3. Clicks link → Redirected to `/login`
4. Proceeds with normal login

## Middleware Protection

### Guest Middleware
- Applied to: `/onboard`, `/login`, `/register`
- Purpose: Prevents authenticated users from accessing these pages
- Behavior: If user is already logged in, redirects to home/dashboard

### Auth Middleware
- Applied to: `/`, `/dashboard`, and all other app routes
- Purpose: Protects authenticated routes
- Behavior: If user is not logged in, redirects to login page

## Success Messages

### After Onboarding
```
"Account created successfully! Please login to continue."
```
Displayed on the login page after successful registration.

### After Login
```
"Welcome! You have been logged in successfully."
```
Or redirects directly to dashboard without message.

## Navigation Links

### Login Page
- **"Forgot your password?"** → `/password/reset`
- **"Don't have an account? Sign up here"** → `/onboard`

### Onboarding Page
- **"Already have an account? Login here"** → `/login`

## Database Flow

### On Onboarding Completion
1. Create user record in `users` table
2. Hash and store password
3. (Optional) Create business settings record
4. (Optional) Create user preferences record
5. Commit transaction
6. Redirect to login

### On Login
1. Validate credentials
2. Create session
3. Redirect to intended page or dashboard

## Security Features

### Password Requirements
- Minimum 8 characters
- Mix of uppercase and lowercase
- At least one number
- At least one special character

### Validation
- Email uniqueness check
- Password confirmation match
- Required field validation
- Email format validation

### CSRF Protection
- All POST requests include CSRF token
- Laravel automatically validates tokens

## Error Handling

### Onboarding Errors
- Database transaction rollback on failure
- User-friendly error messages
- Form data preserved on validation errors
- Specific field error messages

### Login Errors
- Invalid credentials message
- Account locked/disabled handling
- Rate limiting for security

## Customization Options

### To Disable Traditional Register Route
Comment out or remove the register routes in `routes/auth.php`:
```php
// Route::get('/register', [RegisteredUserController::class, 'create']);
// Route::post('/register', [RegisteredUserController::class, 'store']);
```

### To Make Onboarding the Default Registration
Update the register link in navigation to point to onboarding:
```vue
<Link :href="route('onboard.index')">Sign Up</Link>
```

### To Add Email Verification
1. Add email verification step after onboarding
2. Send verification email
3. Require verification before first login

## Testing the Flow

### Test New User Registration
1. Navigate to `http://localhost/login`
2. Click "Sign up here"
3. Fill out all three steps
4. Submit the form
5. Verify redirect to login
6. Log in with created credentials
7. Verify access to dashboard

### Test Existing User Login
1. Navigate to `http://localhost/login`
2. Enter existing credentials
3. Click "Log in"
4. Verify access to dashboard

### Test Navigation
1. From login → onboard → login (using links)
2. From onboard → login (using "Already have account" link)
3. Verify all redirects work correctly

## Future Enhancements

- [ ] Email verification after registration
- [ ] Social login integration (Google, Facebook)
- [ ] Two-factor authentication setup during onboarding
- [ ] Business logo upload in Step 2
- [ ] Multi-language support
- [ ] Save progress and resume later
- [ ] Skip optional steps
- [ ] Welcome email after registration
- [ ] Onboarding progress tracking
- [ ] Admin approval workflow
