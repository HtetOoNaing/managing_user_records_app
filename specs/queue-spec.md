# queue-spec.md

## Feature

Queue Processing for Asynchronous Logging

---

## Objective

Process user activity logs asynchronously using Redis queue workers.

Queue processing must ensure:

- CRUD operations remain fast
- logging happens outside the HTTP request lifecycle
- failed jobs are retryable
- retry execution is safe
- logging failures do not break the main application flow

Queue is used for asynchronous logging in this assessment.

Primary use cases:

- USER_CREATED
- USER_UPDATED
- USER_DELETED

Recommended:

- USER_LOGIN from Filament admin panel authentication

Queue must support the logging architecture only.

Do not place unrelated business workflows inside the queue system.

---

## Queue Driver

Use:

Redis

Do not use:

- database queue
- sync logging fallback
- direct MongoDB writes
- PostgreSQL fallback logging

Redis queue is required for production-safe async processing.

Laravel queue driver must be configured as:

QUEUE_CONNECTION=redis

---

## Queue Flow

Required CRUD Flow

User CRUD Action
→ PostgreSQL transaction success
→ database commit
→ dispatch queue job
→ queue worker processes job
→ MongoDB log write

Recommended Login Flow

Filament Login Success
→ dispatch queue job
→ queue worker processes job
→ MongoDB log write

Forbidden:

User CRUD Action
→ direct MongoDB write

Forbidden:

try queue
→ fail
→ synchronous MongoDB fallback write

Queue dispatch must happen only after successful commit.

Failed transactions must never create logs.

---

## Job Responsibility

Queue jobs must handle:

- MongoDB log persistence
- retry-safe execution
- idempotent behavior
- failure isolation

Queue jobs must not handle:

- business logic
- user CRUD operations
- request validation
- authentication logic
- transaction ownership

Jobs execute background work only.

Jobs must persist already-decided events.

Jobs must not decide business outcomes.

---

## Retry Rules

Jobs must be:

- retry-safe
- idempotent

Rules:

- retries must not create invalid duplicate logs
- repeated execution must remain safe
- duplicate writes must be prevented during retries
- failure state must remain observable

Retry behavior must not corrupt audit history.

Queue retries must preserve data correctness.

---

## Failure Handling

### Queue Dispatch Failure

- must be traceable
- must not create false success assumptions
- must not silently fail

### Worker Failure

- CRUD operation must remain successful
- failed jobs must be retryable
- failure must be visible for debugging

### MongoDB Write Failure

- main application flow must not fail
- retry path must exist
- failure must remain observable

### Transaction Failure

- queue dispatch must not occur
- no log must be created

Logging failure must never block user CRUD.

Committed truth only must be logged.

---

## Worker Rules

Required:

- dedicated queue worker execution
- reliable worker startup
- queue worker service inside Docker Compose
- local Docker support for worker process

Recommended:

- supervisor-style worker management for production readiness
- worker health visibility

Queue worker must be included in containerized setup.

Current assessment implementation uses:

separate queue container

This must be preserved.

---

## Monitoring Rules

Minimum observability:

- failed jobs must be inspectable
- retry attempts must be traceable
- queue processing failures must be debuggable
- failure state must be visible to engineers

Silent queue failure is not acceptable.

Logging reliability requires operational visibility.

---

## Security Rules

- do not log passwords
- do not log password hashes
- do not log tokens
- do not log sensitive credentials
- do not expose queue payloads with private data
- log only operationally useful information

Security overrides convenience.

Queue payloads must remain safe for retries and debugging.

---

## Required Tests

- queue dispatch verification
- job execution verification
- MongoDB persistence verification
- failed transaction prevents dispatch
- retry-safe execution verification
- duplicate retry does not create duplicate logs
- failure path testing
- worker failure does not break CRUD flow
- USER_LOGIN queue dispatch verification if implemented