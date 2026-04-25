# PROMPTS.md

## Prompt Standards

Every prompt must follow the real project implementation.

Current stack:

- PHP 8.4
- Laravel 13.x
- Filament 5.x
- PostgreSQL 17
- MongoDB 8
- Redis 8
- Docker Compose
- Redis Queue Driver
- Pest (preferred for testing)

Before prompting AI:

- check AGENTS.md
- check AI_WORKFLOW.md
- check relevant spec files
- check current implementation
- check composer.json if package behavior matters

Do not let AI assume versions from training data.

Actual installed versions override model assumptions.

---

## Every Prompt Must Include

- business objective
- architecture constraints
- production requirements
- testing expectations
- failure handling expectations
- version-specific constraints where relevant

Avoid:

- vague prompts
- broad prompts
- contextless code generation
- random copy-paste prompts
- “build the whole system”
- speculative architecture generation

Do not request implementation before specification exists.

---

## Mandatory Constraints for This Project

Always remind AI:

- use Filament 5 only
- use Laravel 13 patterns only
- do not generate Filament 3/4 syntax
- do not generate Laravel 10/11 legacy patterns
- do not create custom auth system
- do not create admin_users table
- do not create managed_users table
- do not add is_admin field
- all users can log in to Filament admin panel
- use default Laravel users table
- async logging must use Redis queue
- MongoDB writes must happen only inside Jobs
- no synchronous logging
- no direct MongoDB writes inside controllers
- no unnecessary repository pattern

These constraints prevent AI drift.

---

## Prompt — Architecture Review

Review this Laravel implementation for senior-level production readiness.

Check for:

- thin controllers
- proper Service Layer boundaries
- FormRequest validation usage
- Filament 5 best practices
- async logging correctness
- Redis queue usage
- MongoDB write isolation inside Jobs
- transaction safety
- queue dispatch after DB commit
- retry-safe job design
- idempotent logging jobs
- password security
- validation quality
- testability
- maintainability

Reject:

- synchronous logging
- fat controllers
- direct MongoDB writes inside request lifecycle
- business logic inside Jobs
- custom auth system
- separate admin tables
- unnecessary architecture added for complexity

Return:

- issues found
- risks
- production concerns
- exact refactoring recommendations

---

## Prompt — Filament Resource Review

Review this Filament 5 Resource for production readiness.

Validate:

- password hashing is correct
- password is optional on edit
- password is never prefilled
- password is never exposed
- email uniqueness is safe
- delete requires confirmation
- validation rules are correct
- forms follow Filament 5 best practices
- tables are production-safe
- no legacy Filament syntax exists

Reject:

- plain text password handling
- insecure edit forms
- unnecessary custom pages
- custom Blade CRUD replacement

Return:

- issues found
- security risks
- exact corrections required

---

## Prompt — Service Layer Generation

Generate Laravel Service Layer implementation for this feature.

Requirements:

- thin controllers
- FormRequest validation already exists where applicable
- business logic inside Service Layer only
- database transaction safety where needed
- queue dispatch after successful commit only
- no MongoDB writes inside request lifecycle
- production-safe error handling
- testable design
- clear naming
- Laravel 13 compatible only

Do not generate:

- controller-heavy implementation
- synchronous logging
- unnecessary repository pattern
- logic inside Jobs

Return:

- Service class
- method responsibilities
- failure handling notes

---

## Prompt — Queue Job Generation

Generate Laravel queue job for asynchronous user activity logging.

Requirements:

- Redis queue driver
- MongoDB writes only inside Job
- retry-safe implementation
- idempotent behavior
- logging failure must not break CRUD flow
- event payload includes:

  - user_id
  - event
  - data
  - timestamps

Do not generate:

- business logic inside Job
- synchronous fallback logging
- controller-triggered MongoDB writes
- PostgreSQL log table fallback

Return:

- Job class
- payload structure
- retry handling strategy

---

## Prompt — Testing Generation

Generate production-grade tests for this feature.

Required coverage:

- happy path
- validation failure path
- duplicate email validation
- password hashing verification
- authorization checks
- queue dispatch verification
- queue Job execution
- MongoDB persistence verification
- failure path testing
- retry-safe behavior where applicable

Use:

- Pest
- Laravel testing best practices
- Laravel 13 compatible patterns

Do not generate:

- weak happy-path-only tests
- unverified async behavior
- fake coverage without assertions

Return:

- test cases
- edge cases
- assertions required

---

## Prompt — Final Review

Review the full assessment before submission.

Validate:

- assessment requirements completed
- Filament admin authentication works
- user CRUD works correctly
- all users can log in to admin panel
- async logging works correctly
- PostgreSQL usage is correct
- MongoDB usage is correct
- Redis queue works correctly
- unit testing coverage is sufficient
- Docker setup is reproducible
- reviewer onboarding is simple
- architecture reflects senior engineering standards

Return:

- missing areas
- weak areas
- production risks
- final improvement recommendations