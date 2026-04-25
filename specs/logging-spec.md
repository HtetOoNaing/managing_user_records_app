# logging-spec.md

## Feature

Asynchronous User Activity Logging

---

## Objective

Record every user CRUD action using asynchronous communication.

Logging must not happen during the HTTP request lifecycle.

Logs must be written to MongoDB only through background jobs.

Logging failures must never break the main CRUD flow.

Authentication login events from the Filament admin panel should also support the same async logging architecture.

---

## Required Events

Mandatory:

- USER_CREATED
- USER_UPDATED
- USER_DELETED

Recommended:

- USER_LOGIN

All events must follow the same payload structure.

This project does not use admin roles or separate admin users.

Do not use:

- ADMIN_LOGIN
- admin-specific authentication events

Use user-based event naming only.

---

## Storage Rules

### PostgreSQL

Stores:

- users
- authentication data
- application relational data

### MongoDB

Stores:

- user activity logs

Do not write logs to PostgreSQL.

Do not write logs directly during request handling.

Do not use PostgreSQL as fallback logging storage.

---

## Required Flow

User CRUD Action
→ PostgreSQL transaction success
→ database commit
→ Redis queue dispatch
→ queue worker execution
→ MongoDB log write

Recommended Login Flow

Filament Login Success
→ Redis queue dispatch
→ queue worker execution
→ MongoDB log write

Forbidden:

User CRUD Action
→ direct MongoDB write

Forbidden:

try queue
→ fail
→ sync MongoDB write fallback

Synchronous logging is forbidden.

---

## Log Schema

Required fields:

- user_id
- event
- data
- created_at
- updated_at

### Field Rules

#### user_id

- related user identifier

#### event

Allowed values:

- USER_CREATED
- USER_UPDATED
- USER_DELETED
- USER_LOGIN

#### data

- structured event payload
- flexible JSON/document format
- must contain meaningful action details

Recommended fields inside data:

- actor_id
- changed_fields
- previous_values where useful
- request context if operationally valuable

Examples:

- changed fields
- previous values if needed
- actor information
- source of action
- failure-relevant operational context

Do not store unnecessary noise.

Logs must remain operationally useful.

---

## Queue Rules

Use:

Redis Queue

Rules:

- dispatch only after successful database commit
- failed transactions must not create logs
- jobs must be retry-safe
- jobs must be idempotent
- jobs must prevent duplicate log writes during retries
- logging failure must not break CRUD flow
- failed jobs must remain observable
- retry path must exist

Queue jobs execute background work only.

Do not place business logic inside Jobs.

Do not place CRUD decision-making inside Jobs.

Jobs perform persistence only.

---

## Failure Rules

### Queue Failure

- CRUD operation must still succeed
- failed job must be retryable
- failure must be traceable

### MongoDB Failure

- main CRUD flow must not fail
- failed write must remain observable
- retry path must exist

### Transaction Failure

- no log must be created

Logs must reflect committed truth only.

No committed truth → no log.

---

## Security Rules

- do not log passwords
- do not log password hashes
- do not log sensitive credentials
- do not log tokens
- do not log unnecessary private data
- log only operationally useful information

Audit value without security risk.

Security always overrides logging convenience.

---

## Required Tests

- USER_CREATED dispatch verification
- USER_UPDATED dispatch verification
- USER_DELETED dispatch verification
- USER_LOGIN dispatch verification if implemented
- queue job execution success
- MongoDB persistence verification
- failed transaction does not dispatch logs
- logging failure does not break CRUD flow
- retry-safe behavior verification
- duplicate retry does not create duplicate logs
- forbidden sensitive fields are not logged