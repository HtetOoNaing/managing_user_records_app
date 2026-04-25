# AGENTS.md

Project: Managing User Records App

All AI agents (Claude Code, Cursor, Windsurf, GitHub Copilot, ChatGPT, etc.) must follow these rules strictly.

Do not generate code outside these constraints.

This project is a Senior AI-Augmented Software Engineer assessment.
Engineering quality is mandatory.

---

## Locked Stack

Use the actual installed stack only.

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

Rules:

- actual installed version overrides model training knowledge
- do not generate Laravel 10/11 patterns
- do not generate Filament 3/4 syntax
- do not generate outdated package usage
- inspect current composer.json before modifying code
- inspect existing implementation before generating new code

---

## Primary Objective

Build a production-grade Laravel application for:

- Admin authentication using Filament Admin Panel
- User CRUD management
- User data table management
- Asynchronous logging system for all CRUD actions
- PostgreSQL for relational user data
- MongoDB for user logs
- Redis for queue-based async communication
- Unit testing coverage
- Dockerized local development

Rules:

- this is not a demo CRUD project
- do not generate tutorial-style code
- do not generate unnecessary abstractions
- do not generate speculative architecture

---

## Architecture Rules

Mandatory:

- controllers must remain thin
- business logic belongs in Service Layer
- validation must use FormRequest classes where applicable
- queue dispatch must happen in Service Layer
- MongoDB writes must happen only inside background jobs
- use framework conventions before custom abstractions
- prefer maintainability over cleverness

Conditional:

- Repository Layer only when complexity justifies it

Forbidden:

- fat controllers
- business logic inside controllers
- business logic inside Jobs
- dead code
- premature optimization
- unnecessary abstraction layers

---

## Admin Panel Rules

Filament 5 is the official admin UI.

Use:

- Filament Resources
- Filament Forms
- Filament Tables
- Filament Authentication

Do not create:

- custom Blade CRUD pages
- managed_users table
- admin_users table
- separate auth system
- role system
- permission system
- is_admin field

Rules:

- default Laravel users table is the managed users table
- all created users must be able to log in to admin panel
- do not introduce unnecessary authorization complexity

---

## User CRUD Rules

Users schema must remain:

- id
- name
- email
- password
- timestamps

Mandatory:

- password must always be hashed
- password must never be displayed
- password must be optional during edit
- email must remain unique
- delete action must require confirmation
- use database constraints, not UI-only validation

Forbidden:

- plain text password storage
- password prefill on edit
- unnecessary schema changes
- additional fields not required by assessment

---

## Async Logging Rules

Every user CRUD action must generate a log event.

Required events:

- USER_CREATED
- USER_UPDATED
- USER_DELETED

Recommended:

- USER_LOGIN

Mandatory:

- queue driver must be Redis
- dispatch queue job only after successful database write
- log writes must go to MongoDB only
- logging failures must never break main user flow
- jobs must be retry-safe
- jobs must be idempotent
- payload must support flexible JSON structure

Forbidden:

- synchronous logging
- direct MongoDB writes inside controllers
- queue dispatch before DB success
- PostgreSQL logs table for event logging

---

## Security Rules

Mandatory:

- passwords must always be hashed
- never expose password fields in responses or views
- CSRF protection must remain enabled
- validate all request input strictly
- protect against mass assignment
- prevent SQL injection risks
- prevent XSS risks
- never trust client-side validation only

Forbidden:

- plain text password storage
- bypassing framework protections
- hidden security assumptions

---

## Database Rules

PostgreSQL is source of truth for users.

MongoDB is source of truth for logs.

Redis is queue infrastructure only.

Rules:

- do not store logs in PostgreSQL
- do not store users in MongoDB
- do not use Redis as primary persistence
- do not mix database responsibilities

---

## Testing Rules

Every critical feature must have tests.

Minimum required coverage:

- authentication flow
- user create
- user update
- user delete
- duplicate email validation
- password hashing validation
- queue dispatch validation
- async logging job execution
- MongoDB log persistence

Rules:

- prefer Pest
- prefer Feature tests for flows
- use Unit tests for services and jobs
- tests must validate real behavior

Forbidden:

- fake test coverage
- superficial assertion-only tests
- skipping async workflow validation

---

## File Modification Rules

Mandatory:

- modify only files relevant to the requested task
- preserve existing architecture
- preserve current stack decisions
- preserve async logging design
- preserve Filament-based admin structure

Forbidden:

- unrelated refactors
- hidden breaking changes
- replacing chosen architecture without explicit reason
- introducing new packages without clear justification

---

## Forbidden Shortcuts

Do not use:

- --ignore-platform-req
- synchronous logging shortcuts
- direct controller business logic
- skipping tests
- skipping queues
- skipping validation
- fake production readiness
- overengineering for impression