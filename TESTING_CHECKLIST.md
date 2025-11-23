# Testing Checklist - AI Video Generator

## Pre-Testing Setup

- [ ] Database imported successfully (`mysql -u root -p ai_video_generator < database.sql`)
- [ ] Environment variables configured (`.env` file created)
- [ ] File permissions set correctly (`chmod -R 755 storage/`)
- [ ] Web server running (Apache/Nginx)
- [ ] PHP version 7.0+ verified (`php -v`)

## Unit Tests

### Database & Models
- [ ] Database connection established
- [ ] Tenant model CRUD operations
- [ ] User model CRUD operations
- [ ] Project model CRUD operations
- [ ] Scene model operations
- [ ] Asset model operations
- [ ] Render job model operations
- [ ] Video model operations
- [ ] Subscription model operations
- [ ] Plan model operations
- [ ] API key model operations

### Run Command
```bash
php tests/BasicTests.php
```

Expected: All 12 tests pass

## Authentication Tests

### Registration
- [ ] User can register new account
- [ ] Email validation works
- [ ] Password validation (min 6 chars)
- [ ] Confirm password matching
- [ ] Company name required
- [ ] Duplicate email rejected
- [ ] Tenant created with user
- [ ] Free plan assigned automatically

### Login
- [ ] User can login with valid credentials
- [ ] Invalid email rejected
- [ ] Invalid password rejected
- [ ] Session created on successful login
- [ ] CSRF token generated
- [ ] Redirect to dashboard after login

### Logout
- [ ] User can logout
- [ ] Session destroyed
- [ ] Redirect to login page

### Password Security
- [ ] Passwords hashed with `password_hash()`
- [ ] Passwords not visible in database
- [ ] Password verification works

## CRUD Operations Tests

### Projects
- [ ] Create new project
- [ ] View project list (with pagination)
- [ ] Filter projects by type
- [ ] Filter projects by status
- [ ] View project details
- [ ] Edit project
- [ ] Delete project
- [ ] Duplicate project

### Scenes
- [ ] Add scene to project
- [ ] View all scenes for project
- [ ] Edit scene details
- [ ] Delete scene
- [ ] Scene ordering/positioning
- [ ] Total duration calculation

### Assets
- [ ] Upload image file
- [ ] Upload video file (simulated)
- [ ] Upload audio file (simulated)
- [ ] File type validation
- [ ] File size validation
- [ ] View asset library
- [ ] Search assets by name/tags
- [ ] Filter assets by type
- [ ] Delete asset
- [ ] Storage usage tracking

### Templates
- [ ] Create custom template
- [ ] View public templates
- [ ] View private templates
- [ ] Filter templates by category
- [ ] Apply template to project
- [ ] Template usage count increments
- [ ] Delete template (own templates only)

### Render Jobs
- [ ] Create new render job
- [ ] View render job list
- [ ] Filter by status
- [ ] View job details
- [ ] Progress tracking
- [ ] Retry failed job
- [ ] Background processing

### Videos
- [ ] View completed videos
- [ ] Video details page
- [ ] Download video
- [ ] Duplicate video as new project
- [ ] View count increments
- [ ] Download count increments
- [ ] Public token generation

## Tenant Isolation Tests

### Data Isolation
- [ ] Tenant A cannot view Tenant B's projects
- [ ] Tenant A cannot edit Tenant B's projects
- [ ] Tenant A cannot view Tenant B's assets
- [ ] Tenant A cannot view Tenant B's users
- [ ] Tenant A cannot view Tenant B's videos
- [ ] Tenant A cannot access Tenant B's render jobs

### Test Procedure
1. Login as Tenant 2 user (john@example.com)
2. Note IDs of projects, assets, etc.
3. Logout
4. Login as Tenant 3 user (mike@digitalagency.com)
5. Try to access Tenant 2 resources by direct URL
6. Verify access denied or 404 error

## Subscription & Quota Tests

### Plan Limits
- [ ] Free plan limits enforced (3 projects max)
- [ ] Starter plan limits enforced (10 projects max)
- [ ] Professional plan limits enforced
- [ ] Agency plan unlimited projects works
- [ ] Monthly render limit enforced
- [ ] Storage limit enforced
- [ ] Error message when limit exceeded

### Usage Tracking
- [ ] Project creation increments counter
- [ ] Render job increments counter
- [ ] Render minutes tracked correctly
- [ ] Storage usage updated on upload
- [ ] Storage usage updated on delete
- [ ] Monthly usage resets

### Subscription Management
- [ ] View current subscription
- [ ] View usage vs limits
- [ ] Upgrade plan
- [ ] Downgrade plan
- [ ] View invoices
- [ ] View payment history

### Test Procedure
1. Create projects until limit reached
2. Verify error message displayed
3. Verify "Upgrade Plan" link shown
4. Upgrade to higher plan
5. Verify limit increased
6. Create additional project successfully

## Role-Based Access Control Tests

### Platform Admin
- [ ] Access admin dashboard
- [ ] View all tenants
- [ ] View all plans
- [ ] Create new plan
- [ ] Platform-wide statistics visible

### Tenant Admin
- [ ] Access tenant settings
- [ ] Update tenant profile
- [ ] Manage brand settings
- [ ] Add team members
- [ ] Edit team member roles
- [ ] Delete team members
- [ ] View subscription
- [ ] Change subscription plan
- [ ] Generate API keys
- [ ] Revoke API keys

### Editor
- [ ] Create projects
- [ ] Edit own projects
- [ ] Upload assets
- [ ] Create render jobs
- [ ] Download videos
- [ ] Cannot access tenant settings
- [ ] Cannot manage users
- [ ] Cannot manage subscription

### Viewer
- [ ] View projects (read-only)
- [ ] View assets (read-only)
- [ ] View videos (read-only)
- [ ] Download videos
- [ ] Cannot create projects
- [ ] Cannot upload assets
- [ ] Cannot start renders

## Public API Tests

### Authentication
- [ ] Valid API key accepted
- [ ] Invalid API key rejected (401)
- [ ] Missing API key rejected (401)
- [ ] Expired API key rejected (401)
- [ ] Revoked API key rejected (401)

### Create Render Job Endpoint
**POST /api/v1/render**

Test Cases:
- [ ] Valid text_to_video request succeeds (201)
- [ ] Valid image_to_video request succeeds
- [ ] Valid video_to_video request succeeds
- [ ] Valid images_to_video request succeeds
- [ ] Missing project_id returns error (400)
- [ ] Invalid project_id returns error (404)
- [ ] Project from different tenant denied (404)
- [ ] Missing required parameters returns error (400)
- [ ] Quota exceeded returns error (429)
- [ ] Response includes job_id
- [ ] Response includes correct status ("queued")

### Get Render Job Endpoint
**GET /api/v1/render/{job_id}**

Test Cases:
- [ ] Queued job returns correct status
- [ ] Processing job returns progress
- [ ] Completed job returns video details
- [ ] Failed job returns error message
- [ ] Job from different tenant denied (404)
- [ ] Invalid job_id returns error (404)

### API Request Examples

```bash
# Create render job
curl -X POST http://localhost/api/v1/render \
  -H "X-API-KEY: avgen_live_your_key_here" \
  -H "Content-Type: application/json" \
  -d '{
    "project_id": 1,
    "type": "text_to_video",
    "parameters": {
      "script": "Test video",
      "style": "cinematic",
      "duration": 30,
      "resolution": "1080p"
    }
  }'

# Get job status
curl http://localhost/api/v1/render/1 \
  -H "X-API-KEY: avgen_live_your_key_here"
```

## Security Tests

### CSRF Protection
- [ ] POST without CSRF token rejected
- [ ] POST with invalid CSRF token rejected
- [ ] POST with valid CSRF token accepted
- [ ] CSRF token in all forms

### XSS Prevention
- [ ] HTML in project name escaped
- [ ] Script tags in description escaped
- [ ] All user input properly escaped
- [ ] No unescaped output in views

### SQL Injection Prevention
- [ ] Single quote in search doesn't break
- [ ] SQL keywords in input don't execute
- [ ] All queries use prepared statements
- [ ] No raw SQL with user input

### File Upload Security
- [ ] PHP file upload rejected
- [ ] .exe file upload rejected
- [ ] File size limit enforced
- [ ] File type validation works
- [ ] Path traversal attempt blocked
- [ ] Unique filenames generated

### Session Security
- [ ] Session expires after inactivity
- [ ] Multiple logins handled correctly
- [ ] Session hijacking prevented
- [ ] Logout destroys session

## Performance Tests

### Page Load Times
- [ ] Dashboard loads < 2 seconds
- [ ] Project list loads < 2 seconds
- [ ] Asset library loads < 3 seconds
- [ ] Video list loads < 2 seconds

### Database Queries
- [ ] No N+1 query problems
- [ ] Pagination queries optimized
- [ ] JOIN queries use indexes
- [ ] COUNT queries efficient

### File Operations
- [ ] Large file upload handles correctly
- [ ] Multiple concurrent uploads work
- [ ] File deletion completes quickly

## User Interface Tests

### Navigation
- [ ] All navigation links work
- [ ] Breadcrumbs accurate
- [ ] Active menu item highlighted
- [ ] Dropdown menus function

### Forms
- [ ] All forms submit correctly
- [ ] Validation messages displayed
- [ ] Required fields enforced
- [ ] Form data persists on error

### Alerts & Notifications
- [ ] Success messages displayed
- [ ] Error messages displayed
- [ ] Alerts auto-dismiss (5 seconds)
- [ ] Flash messages work correctly

### Responsive Design
- [ ] Mobile view functional
- [ ] Tablet view functional
- [ ] Desktop view functional
- [ ] Forms usable on mobile

## Integration Tests

### Complete Video Creation Flow
1. [ ] Register new account
2. [ ] Login successfully
3. [ ] Create new project
4. [ ] Upload image asset
5. [ ] Add scene to project
6. [ ] Associate asset with scene
7. [ ] Create render job
8. [ ] Process render job (run CLI script)
9. [ ] View completed video
10. [ ] Download video

### Team Collaboration Flow
1. [ ] Tenant admin creates new user
2. [ ] New user receives credentials
3. [ ] New user logs in
4. [ ] New user creates project
5. [ ] Tenant admin views project
6. [ ] Tenant admin edits project
7. [ ] Both users see changes

### Subscription Upgrade Flow
1. [ ] View current plan (Free)
2. [ ] Create 3 projects (reach limit)
3. [ ] Attempt 4th project (blocked)
4. [ ] View upgrade plans
5. [ ] Upgrade to Starter plan
6. [ ] Create 4th project (succeeds)
7. [ ] View updated usage stats

## Browser Compatibility Tests

- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

## Background Jobs Tests

### Render Processing
- [ ] Queued jobs detected
- [ ] Jobs transition to processing
- [ ] Progress updates correctly
- [ ] Jobs complete successfully
- [ ] Video records created
- [ ] Notifications sent
- [ ] Failed jobs retry works

### Run Command
```bash
php app/console/process-renders.php
```

## Regression Tests

After any code changes, verify:
- [ ] Existing projects still load
- [ ] Login still works
- [ ] File upload still works
- [ ] API endpoints still work
- [ ] No new errors in logs

## Load Testing (Optional)

### Concurrent Users
- [ ] 10 concurrent users
- [ ] 50 concurrent users
- [ ] 100 concurrent users
- [ ] Database handles load
- [ ] No deadlocks occur

### Tools
- Apache Bench (ab)
- JMeter
- Locust

## Accessibility Tests

- [ ] Alt text on images
- [ ] Form labels present
- [ ] Keyboard navigation works
- [ ] Screen reader compatible
- [ ] WCAG 2.1 Level A compliance

## Documentation Tests

- [ ] README.md accurate
- [ ] Installation steps work
- [ ] API documentation correct
- [ ] Code comments helpful
- [ ] Example requests work

## Post-Testing

### Cleanup
- [ ] Test data removed
- [ ] Test accounts deleted
- [ ] Storage cleared
- [ ] Logs reviewed

### Issues Found
Document any issues found during testing:

1. Issue description:
2. Steps to reproduce:
3. Expected behavior:
4. Actual behavior:
5. Severity: (Critical/High/Medium/Low)

## Sign-Off

**Tested By:** _______________
**Date:** _______________
**All Tests Passed:** [ ] Yes [ ] No
**Notes:** _______________
