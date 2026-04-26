# Dashboard Enhancement Spec

## Business Objective

Replace the default Filament dashboard with meaningful widgets that provide immediate operational visibility when an admin logs in. Current state shows only generic Filament boilerplate (AccountWidget, FilamentInfoWidget) with no application-specific metrics.

---

## Target State

Three custom widgets replace the default dashboard:

1. **StatsOverviewWidget** — real-time application metrics
2. **RecentActivityWidget** — last 10 user activity log entries
3. **QuickActionsWidget** — navigation shortcuts to key admin areas

---

## Widget Specifications

### 1. StatsOverviewWidget

**Data sources:**
- Total Users → `users` table (PostgreSQL)
- New Users Today → `users` where `created_at >= today's start`
- Activities Today → `user_activity_logs` where `created_at >= today's start` (MongoDB)

**Display:**
- Three stat cards in a row
- Each card shows label + count + description + icon

**Architecture constraints:**
- Read-only queries; no writes
- MongoDB query must use Carbon/DateTime range (not raw string)
- Must not break if MongoDB is unavailable (graceful count of 0)

---

### 2. RecentActivityWidget

**Data source:** `user_activity_logs` (MongoDB), ordered by `created_at DESC`, limited to 10 records

**Columns:**
| Column | Source | Format |
|--------|--------|--------|
| Event | `event` | Badge with matching color |
| User | `user_id` | Name resolved from PostgreSQL; fallback chain to log data |
| Time | `created_at` | Relative ("5 minutes ago") with absolute tooltip |

**Behavior:**
- Row is clickable — links to full activity log detail page
- Pagination locked to 10 per page
- No create/edit/delete actions
- Event badge colors match `UserActivityLogResource` (consistent UX)
- User name resolution uses same fallback chain as `UserActivityLogResource`:
  1. Live PostgreSQL lookup
  2. `data.user_name`
  3. `data.attributes.name`
  4. `data.current_values.name`
  5. `data.previous_values.name`
  6. `"Deleted User #id"`

**Architecture constraints:**
- Extends `Filament\Widgets\TableWidget`
- Full-width column span

---

### 3. QuickActionsWidget

**Links:**
- "Create New User" → `/admin/users/create`
- "View All Users" → `/admin/users`
- "View Activity Logs" → `/admin/user-activity-logs`

**Architecture constraints:**
- Extends `Filament\Widgets\Widget` with custom Blade view
- Navigation links only — no Livewire actions
- Full-width column span

---

## Widget Sort Order

| Order | Widget |
|-------|--------|
| 1 | StatsOverviewWidget |
| 2 | RecentActivityWidget |
| 3 | QuickActionsWidget |

AccountWidget is removed from explicit panel registration (replaced by the above).
FilamentInfoWidget is removed from explicit panel registration (not relevant to this application).

---

## Acceptance Criteria

- [ ] Dashboard loads without errors for authenticated user
- [ ] StatsOverviewWidget shows accurate total user count
- [ ] StatsOverviewWidget shows accurate today's new user count
- [ ] StatsOverviewWidget shows accurate today's activity count
- [ ] RecentActivityWidget shows last 10 logs in descending order
- [ ] RecentActivityWidget resolves user names (not raw IDs)
- [ ] RecentActivityWidget event badges have correct colors
- [ ] RecentActivityWidget rows link to activity log detail pages
- [ ] QuickActionsWidget renders all 3 navigation buttons
- [ ] All buttons navigate to correct admin routes
- [ ] All widgets are responsive (full-width on mobile)
- [ ] No regression in existing user CRUD or activity log functionality

---

## Edge Cases

- No users exist → Stats show 0, Recent Activity shows empty state
- MongoDB unavailable → StatsOverviewWidget catches exception, shows 0 for activity count
- User in log has been deleted → Name resolved from log payload; shows "Deleted User #id" as last resort
- All activity events today → correct count regardless of event type

---

## Testing Expectations

- Feature test: Dashboard accessible by authenticated user (HTTP 200)
- Feature test: Stats widget renders with correct counts
- Feature test: Recent activity widget renders with log data
- Feature test: Quick actions widget renders all navigation buttons
- Existing tests must continue to pass (no regression)

---

## Architecture Constraints

- Widgets live in `app/Filament/Widgets/`
- Blade view for QuickActionsWidget in `resources/views/filament/widgets/`
- No new database tables, migrations, or models
- No service layer needed (read-only display logic belongs in widgets)
- Follow existing Filament 5 patterns used in `UserActivityLogResource`
