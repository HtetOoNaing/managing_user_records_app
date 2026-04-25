# auth-spec.md

## Feature

Filament Admin Panel Authentication

---

## Objective

Allow users to log in to the Filament admin panel using email and password.

Only authenticated users can access protected admin routes.

Any created user in the default Laravel users table can log in to the admin panel.

This project does not use separate admin accounts, admin roles, or admin-specific tables.

Authentication must follow Filament 5 and Laravel 13 conventions only.

---

## Requirements

- login using email and password
- secure password hashing
- Filament authentication system only
- session-based authentication
- authentication middleware on protected routes
- logout functionality
- unauthorized users must be redirected or denied access
- protected admin routes must not be publicly accessible

Do not create:

- custom authentication system
- separate admin_users table
- managed_users table
- role system
- permission system
- is_admin field

Use:

- default Laravel users table
- Filament login page
- Laravel session authentication

---

## Login Input

Required fields:

- email
- password

Validation rules:

### email

- required
- valid email format
- must exist for login attempt

### password

- required
- string

Authentication failure must not expose whether email or password is incorrect.

---

## Success Flow

1. user opens Filament login page
2. user submits email and password
3. validate input
4. verify credentials
5. create authenticated session
6. redirect to Filament dashboard
7. access protected admin routes allowed

---

## Failure Flow

### Invalid Credentials

- return authentication failure
- do not expose which field is incorrect

### Validation Failure

- return validation errors
- do not attempt authentication

### Unauthorized Access

- protected routes must reject access
- redirect to login page or return unauthorized response

### Session Expiration

- expired sessions must require re-authentication

---

## Security Rules

- passwords must always be hashed
- never store plain text passwords
- password must never be exposed in forms or responses
- CSRF protection must remain enabled
- login attempts must not expose sensitive details
- protected routes must use authentication middleware
- mass assignment protection must remain enabled
- framework security protections must not be bypassed

---

## Recommended Event Logging

Log successful login as:

USER_LOGIN

Logging must follow async logging rules.

Rules:

- logging must be asynchronous only
- logging must use Redis queue
- MongoDB writes must happen only inside Jobs
- logging failure must never break login flow

Never log synchronously.

---

## Required Tests

- successful login
- invalid credentials
- validation failure
- unauthorized route access blocked
- authenticated route access allowed
- logout success
- session expiration behavior
- password hashing validation
- USER_LOGIN queue dispatch verification if implemented