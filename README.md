# Managing User Records App

A production-grade Laravel 13 application for user management with asynchronous activity logging.

## Assessment Overview

This project demonstrates a Senior AI-Augmented Software Engineer assessment implementation covering:

- **Admin Authentication**: Filament 5 admin panel with secure login
- **User CRUD Management**: Full create, read, update, delete via Filament Resources
- **Asynchronous Logging**: Redis queue-based activity logging to MongoDB
- **Multi-Database Architecture**: PostgreSQL for users, MongoDB for logs
- **Comprehensive Testing**: Pest + PHPUnit with 49 tests and 119 assertions

## Technology Stack

| Component | Version | Purpose |
|-----------|---------|---------|
| PHP | 8.4 | Runtime |
| Laravel | 13.x | Framework |
| Filament | 5.x | Admin Panel |
| PostgreSQL | 17 | User Data Storage |
| MongoDB | 8 | Activity Log Storage |
| Redis | 8 | Queue Infrastructure |
| Pest | 4.x | Testing Framework |

## Architecture Highlights

### Service Layer Pattern
- Thin Filament Resource pages delegate to `UserService`
- Business logic centralized in `app/Services/UserService.php`
- Validation via Laravel Validator with FormRequest-equivalent rules
- Transaction-safe database operations with `DB::transaction()`

### Asynchronous Logging
- Queue dispatch via `DB::afterCommit()` ensures logs only fire after successful DB writes
- Idempotent job design with `updateOrCreate()` and idempotency keys
- Retry-safe with exponential backoff (5s, 15s, 30s)
- Sensitive data sanitization before persistence

### Security Measures
- Passwords hashed with Laravel's `hashed` cast (BCRYPT)
- Passwords never exposed in API responses or views
- CSRF protection enabled by default
- Mass assignment protection via `#[Fillable]` attributes
- Database constraints for unique emails

## Quick Start

### Prerequisites
- Docker & Docker Compose
- Git

### Setup

```bash
# Clone the repository
git clone <repository-url>
cd managing_user_records_app

# Start Docker services
docker compose up -d

# Install dependencies
docker compose exec app composer install

# Setup environment
docker compose exec app cp .env.example .env
docker compose exec app php artisan key:generate

# Run migrations
docker compose exec app php artisan migrate

# Seed the database with test data
docker compose exec app php artisan db:seed

# Access the application
open http://localhost:8000/admin
```

### Default Credentials
The database seeder creates an admin user automatically:

| Field | Value |
|-------|-------|
| Email | `admin@example.com` |
| Password | `password` |

Log in at: `http://localhost:8000/admin/login`

The seeder also creates 5 additional test users to populate the data table.

## Development Commands

```bash
# Run all tests
docker compose exec app php artisan test

# Run tests with coverage
docker compose exec app php artisan test --coverage

# Run specific test suites
docker compose exec app php artisan test --testsuite=Feature
docker compose exec app php artisan test --testsuite=Unit

# Access application shell
docker compose exec app bash

# View queue worker logs
docker compose logs -f queue

# View application logs
docker compose exec app php artisan pail
```

## Testing

The project includes comprehensive test coverage:

### Feature Tests (26 tests)
- `FilamentAuthenticationTest` - Login, logout, auth middleware
- `UserCrudManagementTest` - CRUD operations, validation, password handling
- `UserActivityLoggingTest` - Async logging, idempotency, transaction rollback

### Unit Tests (23 tests - Pest)
- `UserServiceTest` - Service layer business logic
- `WriteUserActivityLogTest` - Queue job behavior

Run tests:
```bash
docker compose exec app php artisan test
```

## Architecture Decisions

### Why Service Layer?
Controllers (Filament Pages) remain thin, delegating to `UserService`. This provides:
- Reusable business logic
- Testable units without HTTP layer
- Clear separation of concerns
- Transaction safety

### Why Async Logging?
User activity logging happens outside the HTTP request lifecycle via Redis queue:
- Non-blocking user operations
- Retry capability for failed logs
- MongoDB dedicated for log storage
- No impact on main database performance

### Why Pest for Unit Tests?
Modern, expressive syntax while maintaining PHPUnit compatibility:
- Better readability with `describe()` and `it()`
- Dataset support for parameterized tests
- Built-in parallel execution support

## Project Structure

```
managing_user_records_app/
├── AGENTS.md                 # AI agent constraints & rules
├── AI_WORKFLOW.md            # Development workflow
├── IMPLEMENTATION_PLAN.md    # Phase-by-phase plan
├── PROMPTS.md                # Approved AI prompts
├── docker-compose.yml        # Docker orchestration
├── specs/                    # Feature specifications
│   ├── auth-spec.md
│   ├── user-crud-spec.md
│   ├── logging-spec.md
│   ├── queue-spec.md
│   └── testing-spec.md
├── handoff/                  # Phase handoff documents
│   ├── phase-1-handoff.md
│   ├── phase-2-handoff.md
│   ├── phase-3-handoff.md
│   ├── phase-4-handoff.md
│   ├── phase-5-handoff.md
│   └── phase-6-handoff.md
└── laravel/
    ├── app/
    │   ├── Filament/Resources/Users/   # Filament admin UI
    │   ├── Jobs/WriteUserActivityLog.php # Queue job
    │   ├── Models/
    │   │   ├── User.php                 # PostgreSQL user model
    │   │   └── UserActivityLog.php      # MongoDB log model
    │   └── Services/UserService.php     # Business logic
    ├── tests/
    │   ├── Feature/                     # Integration tests
    │   └── Unit/                        # Unit tests (Pest)
    └── docker-compose.yml               # Local development
```

## Database Configuration

### PostgreSQL (Users)
- Host: `postgres`
- Database: `managing_user_records`
- Port: `5432`
- Tables: `users`, `cache`, `jobs`

### MongoDB (Logs)
- URI: `mongodb://mongodb:27017`
- Database: `managing_user_logs`
- Collection: `user_activity_logs`

### Redis (Queue)
- Host: `redis`
- Port: `6379`
- Connection: `redis`

## Verification Checklist

Before submission, verify:

- [ ] All tests pass: `docker compose exec app php artisan test`
- [ ] Docker builds cleanly: `docker compose up -d --build`
- [ ] Admin login works at `http://localhost:8000/admin`
- [ ] User CRUD operations work
- [ ] Activity logs appear in MongoDB
- [ ] Queue worker processes jobs

## Troubleshooting

### Tests Failing
```bash
# Ensure fresh database
docker compose exec app php artisan migrate:fresh

# Run specific test with verbose output
docker compose exec app php artisan test --filter=test_name -v
```

### Queue Not Processing
```bash
# Restart queue worker
docker compose restart queue

# Check queue status
docker compose exec app php artisan queue:status
```

### MongoDB Connection Issues
```bash
# Verify MongoDB is running
docker compose ps mongodb

# Test connection
docker compose exec app php artisan tinker --execute="DB::connection('mongodb')->getClient()->listDatabases();"
```

## License

This project is for assessment purposes.

## Credits

Built with [Laravel](https://laravel.com) and [Filament](https://filamentphp.com).
