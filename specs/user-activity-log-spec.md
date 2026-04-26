# User Activity Log Resource Specification

## Business Objective
Create a read-only Filament resource to view and analyze user activity logs stored in MongoDB. This provides audit trail visibility for all user CRUD operations.

## Current State
- Logs are written to MongoDB via async queue (`WriteUserActivityLog` job)
- `UserActivityLog` model exists with MongoDB connection
- No UI to view these logs
- Logs contain: event type, user_id, actor_id, timestamps, changed data

## Target State
- New Filament Resource: `UserActivityLogResource`
- Menu item: "User Activities" in admin sidebar
- Read-only resource (no create/edit/delete)
- Features:
  - Data table with columns: Event, User, Actor, Timestamp, Details
  - Filters: By event type, date range
  - Search: By user name, event type
  - View action: Detailed log entry with formatted JSON

## Data Structure

### MongoDB Schema (`user_activity_logs` collection)
```json
{
  "_id": ObjectId,
  "idempotency_key": "string (SHA1)",
  "user_id": integer,
  "event": "USER_CREATED|USER_UPDATED|USER_DELETED|USER_LOGIN",
  "data": {
    "actor_id": integer,
    "attributes": {},
    "changed_fields": [],
    "previous_values": {},
    "current_values": {}
  },
  "created_at": ISODate,
  "updated_at": ISODate
}
```

### Cross-Database Join
- `user_id` → PostgreSQL `users` table (get user name)
- `data.actor_id` → PostgreSQL `users` table (get actor name)

## Acceptance Criteria

### Functional Requirements
1. "User Activities" appears in admin sidebar navigation
2. Lists all logs from MongoDB in descending time order (newest first)
3. Shows human-readable user names (resolved from PostgreSQL)
4. Event badges with color coding:
   - USER_CREATED: success (green)
   - USER_UPDATED: warning (amber)
   - USER_DELETED: danger (red)
   - USER_LOGIN: info (blue)
5. Filters work: event type, date range
6. Search works: user name, event type
7. View page shows formatted JSON data
8. No CRUD actions (read-only resource)

### UI/UX Requirements
1. Table columns:
   - ID (shortened MongoDB ObjectId)
   - Event (badge with color)
   - User (name from users table)
   - Actor (name from users table - "who did it")
   - Timestamp (human-readable datetime)
2. Responsive design
3. Consistent with existing amber theme
4. View page has back button to list

### Technical Requirements
1. Read-only resource (no forms, no actions)
2. Use existing `UserActivityLog` model
3. Eloquent queries for MongoDB
4. In-memory joins for user names (or separate queries)
5. Sort by `created_at` descending
6. Pagination enabled

### Performance Requirements
1. Index on `created_at` for sorting
2. Consider caching user lookups
3. Lazy loading for user names

## Edge Cases
1. **Deleted user**: Show user_id if user no longer exists
2. **Missing actor_id**: Show "System" or "Unknown"
3. **Empty data**: Show "No additional data"
4. **Large JSON data**: Truncate or format nicely
5. **No logs**: Show empty state message

## Testing Expectations

### Feature Tests
```php
// tests/Feature/UserActivityLogResourceTest.php
test_user_activity_log_resource_is_accessible()
test_user_activity_logs_list_shows_logs()
test_user_activity_logs_shows_user_names()
test_user_activity_logs_shows_event_badges()
test_user_activity_logs_has_filters()
test_user_activity_logs_has_search()
test_user_activity_logs_is_read_only()
test_user_activity_log_view_page_works()
```

### Manual Testing Checklist
- [ ] "User Activities" in sidebar
- [ ] List shows logs with correct data
- [ ] Event badges have correct colors
- [ ] User names display correctly
- [ ] Filters filter correctly
- [ ] Search returns results
- [ ] No create/edit/delete buttons
- [ ] View page shows details
- [ ] Back button works

## Architecture Constraints
- Use existing `UserActivityLog` model
- Read-only resource (no mutations)
- Cross-database queries (MongoDB + PostgreSQL)
- Follow Filament 5 conventions
- No additional packages

## Implementation Notes

### Event Badge Colors
```php
'USER_CREATED' => Color::Emerald,
'USER_UPDATED' => Color::Amber,
'USER_DELETED' => Color::Rose,
'USER_LOGIN'   => Color::Blue,
```

### User Name Resolution
Option 1: Join in memory
```php
$userIds = $logs->pluck('user_id')->unique();
$users = User::whereIn('id', $userIds)->pluck('name', 'id');
```

Option 2: Accessor on model
```php
public function getUserNameAttribute(): string
{
    return User::find($this->user_id)?->name ?? "User #{$this->user_id}";
}
```

### Table Configuration
```php
->columns([
    TextColumn::make('id')->formatStateUsing(fn ($state) => substr($state, -8)),
    BadgeColumn::make('event')
        ->colors([
            'USER_CREATED' => Color::Emerald,
            'USER_UPDATED' => Color::Amber,
            'USER_DELETED' => Color::Rose,
        ]),
    TextColumn::make('user_name'),
    TextColumn::make('actor_name'),
    TextColumn::make('created_at')->dateTime(),
])
```

## Files to Create
```
laravel/app/Filament/Resources/UserActivityLogResource.php
laravel/app/Filament/Resources/UserActivityLogResource/Pages/ListUserActivityLogs.php
laravel/app/Filament/Resources/UserActivityLogResource/Pages/ViewUserActivityLog.php
laravel/tests/Feature/UserActivityLogResourceTest.php
```

## Definition of Done
- [ ] Spec document approved
- [ ] Resource implemented per spec
- [ ] Feature tests passing
- [ ] Manual testing completed
- [ ] Sidebar navigation working
- [ ] Read-only verified (no mutations)
- [ ] User names displaying
- [ ] Event badges colored
- [ ] Handoff document written

---

*Spec Version: 1.0*
*Created: 2026-04-26*
*Status: Ready for Implementation*
