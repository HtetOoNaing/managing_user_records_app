# user-crud-spec.md

## Feature

User Management CRUD

---

## Objective

Provide admin panel functionality to manage users.

Implementation uses:

* Laravel 13
* Filament 5 Resource-based admin panel

Required operations:

* create user
* view user list
* update user
* delete user

User list must be displayed in data table format.

Any created user can log in to the admin panel.

All CRUD actions must trigger asynchronous logging.

---

## User Schema

Required fields:

* id
* name
* email
* password
* created_at
* updated_at

Rules:

* email must be unique
* password must be securely hashed
* timestamps must be managed automatically

Not required:

* is_admin

This assessment does not require role separation.

Any created user can access the admin panel.

---

## Create User

### Input

* name
* email
* password

### Validation Rules

#### name

* required
* string
* reasonable max length

#### email

* required
* valid email format
* unique

#### password

* required
* string
* secure minimum length

### Success Flow

1. validate input
2. create user
3. hash password
4. persist to PostgreSQL
5. commit transaction
6. dispatch USER_CREATED log job
7. return success response

### Failure Flow

* validation failure returns errors
* duplicate email rejected
* failed transaction must not dispatch logs

---

## View User List

### Requirements

* protected Filament admin route
* data table format
* searchable
* sortable
* pagination enabled

### Output

Display:

* id
* name
* email
* created_at
* updated_at

Never expose:

* password

Filament table should follow admin usability best practices.

---

## Update User

### Input

* name
* email
* password (optional)

### Validation Rules

#### name

* required
* string

#### email

* required
* valid email format
* unique except current user

#### password

* optional
* if provided, must be securely hashed

### Additional Rules

* password field must never be prefilled
* blank password must not overwrite existing password
* password should update only when explicitly provided

### Success Flow

1. validate input
2. update user
3. hash password if changed
4. persist changes
5. commit transaction
6. dispatch USER_UPDATED log job
7. return success response

### Failure Flow

* validation failure returns errors
* duplicate email rejected
* failed transaction must not dispatch logs

---

## Delete User

### Requirements

* protected admin route
* safe deletion required

### Success Flow

1. locate user
2. delete user
3. commit transaction
4. dispatch USER_DELETED log job
5. return success response

### Failure Flow

* missing user returns failure
* failed transaction must not dispatch logs

Recommended:

Prevent accidental self-deletion if needed for system safety.

---

## Filament Resource Rules

Use:

* Filament Resource
* Filament Forms
* Filament Tables

Prefer:

* Resource-based CRUD
* framework-native conventions

Avoid:

* unnecessary custom controllers
* duplicated CRUD architecture
* custom admin implementation without reason

Use Filament best practices only.

Do not force non-Filament architecture.

---

## Security Rules

* all admin routes protected by authentication middleware
* password must never be exposed
* password must never be stored in plain text
* mass assignment protection required
* validation must be production-safe
* SQL injection and XSS risks must be prevented
* sensitive fields must not appear in logs

Because Filament handles forms internally:

FormRequest classes are not mandatory for this feature.

Production-safe validation is mandatory.

---

## Required Tests

* create user success
* create user validation failure
* duplicate email rejection
* update user success
* update user validation failure
* password update hashing verification
* password optional on edit verification
* password never prefilled verification
* delete user success
* delete missing user failure
* protected route access verification
* password never exposed
* CRUD queue dispatch verification
* MongoDB logging verification