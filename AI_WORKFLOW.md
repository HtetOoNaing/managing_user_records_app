# AI_WORKFLOW.md

## Standard AI Development Workflow

---

## Step 1 — Write Specification First

Before implementation:

* define business requirement
* define acceptance criteria
* define edge cases
* define failure scenarios
* define testing expectations
* define architecture constraints

All feature work must begin inside:

/specs/

Required files:

* auth-spec.md
* user-crud-spec.md
* logging-spec.md
* queue-spec.md
* testing-spec.md

No implementation begins without specification.

---

## Step 2 — Select Approved Prompt

Use prompts from:

PROMPTS.md

Every prompt must include:

* business objective
* architecture constraints
* production requirements
* testing expectations
* failure handling expectations

Avoid:

* vague prompts
* incomplete prompts
* contextless code generation
* random copy-paste prompts

---

## Step 3 — Generate First Candidate

AI may generate:

* architecture proposals
* service layer structure
* controller skeletons
* queue job candidates
* validation candidates
* test candidates
* refactoring suggestions

Generated output is a candidate only.

Never treat generated code as final implementation.

---

## Step 4 — Run Architecture Review

Review generated output for:

* business logic leaks
* controller bloat
* transaction risks
* async logging violations
* security concerns
* maintainability issues
* testing gaps
* production-readiness gaps

Generation without review is not allowed.

---

## Step 5 — Refactor to Production Grade

Refactor based on review findings.

Focus on:

* transaction safety
* queue reliability
* naming clarity
* service boundaries
* security improvements
* failure handling
* testability
* maintainability

Do not ship first-generated code.

---

## Step 6 — Generate Tests

Generate tests only after architecture is validated.

Required testing includes:

* happy path
* failure path
* validation failures
* queue dispatch verification
* queue job execution
* MongoDB persistence verification
* authorization checks
* retry safety where applicable

No critical feature is complete without tests.

---

## Step 7 — Human Verification

Verify manually:

* business correctness
* edge cases
* hidden failure paths
* transaction integrity
* queue safety
* security assumptions
* assessment requirement alignment

Final responsibility belongs to the engineer.

---

## Step 8 — Final Validation

Before submission:

* all requirements satisfied
* async logging works correctly
* tests pass
* Docker setup runs successfully
* reviewer setup is simple
* code is production-safe
* no undocumented shortcuts exist

---

## Rule

AI generates candidates.

Engineer approves production.
