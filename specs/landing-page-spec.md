# Landing Page Specification

## Business Objective
Replace the default Laravel welcome page with a branded landing page that provides clear navigation to the admin panel.

## Current State
- Root URL `/` displays Laravel's default welcome view
- Links to `route('login')` and `route('register')` are broken (Filament uses different auth routes)
- No clear path to access the admin panel at `/admin`

## Target State
- Clean, branded landing page for "Assessment" application
- Prominent "Admin Login" button linking to `/admin/login`
- Brief description of application purpose
- Professional styling consistent with admin panel amber theme
- Fully mobile responsive

## Acceptance Criteria

### Functional Requirements
1. Root page (`/`) displays without broken links or errors
2. "Admin Login" button is prominently displayed above the fold
3. Clicking "Admin Login" redirects to `/admin/login`
4. Page displays correct app name: "Assessment"
5. Page includes brief description: "User Management System"

### UI/UX Requirements
1. Uses Tailwind CSS styling with amber color accents
2. Dark mode support (consistent with Laravel default)
3. Mobile responsive (works on 320px to 1920px widths)
4. No layout shifts on load
5. Call-to-action button uses amber/amber-600 colors

### Technical Requirements
1. Single Blade file modification: `resources/views/welcome.blade.php`
2. No new controllers or routes needed
3. No external dependencies beyond existing Tailwind
4. Maintains existing `<title>` dynamic config

## Edge Cases
1. **Already authenticated user**: Button should still show (user can navigate to admin)
2. **Filament not installed**: Graceful degradation (link still works but may 404)
3. **JavaScript disabled**: Page works without JS

## Testing Expectations

### Feature Tests
```php
// tests/Feature/LandingPageTest.php
- test_landing_page_loads_successfully()
- test_landing_page_shows_admin_login_button()
- test_admin_login_button_redirects_to_admin_login()
- test_landing_page_shows_app_name()
- test_landing_page_is_mobile_responsive()
```

### Manual Testing Checklist
- [ ] Page loads at `http://localhost:8000/`
- [ ] No 404 errors for login/register links
- [ ] Admin Login button visible and clickable
- [ ] Redirects to `/admin/login` correctly
- [ ] Looks good on mobile (320px width)
- [ ] Looks good on desktop (1920px width)

## Architecture Constraints
- No new PHP files
- No route changes
- Single Blade template modification only
- Preserve existing head/meta structure

## Implementation Notes

### Color Scheme
- Background: `#FDFDFC` (light) / `#0a0a0a` (dark)
- Text: `#1b1b18` (light) / `#EDEDEC` (dark)
- Primary button: Amber/amber-600
- Border: `#19140035` (light) / `#3E3E3A` (dark)

### Content Structure
```
[Header with Logo/Brand]
[Hero Section]
  - App Name: "Assessment"
  - Tagline: "User Management System"
  - Description: Brief text about admin capabilities
[CTA Section]
  - "Admin Login" button (amber, prominent)
[Footer]
  - Minimal copyright/version info
```

## Failure Handling
- If user is already logged in, still show landing page (don't auto-redirect)
- Broken assets should not prevent button functionality
- CDN failures should not prevent page rendering

## Definition of Done
- [ ] Spec document approved
- [ ] Landing page implemented per spec
- [ ] Feature tests passing
- [ ] Manual testing completed
- [ ] Mobile responsive verified
- [ ] No broken links
- [ ] Admin login button functional
- [ ] Handoff document written
