# EduProfile Support and Updates Workflow

This guide covers the practical workflow for support and version updates.

## 6. Support and Updates Module Setup

Tenant app module implemented with:

- Support tickets
- Update logs
- Sidebar visibility for all authenticated users
- Admin-only update publishing and ticket status updates

### Tables

1. support_tickets
- id
- school_id (tenant scope)
- user_id
- subject
- message
- priority
- status
- created_at
- updated_at

2. app_updates
- id
- version
- title
- description
- release_date
- release_document_path (optional)
- is_active
- created_at
- updated_at

## 7. How GitHub and the App Connect

Two options are available:

1. Manual (recommended for student projects)
- Create GitHub release
- Update APP_VERSION in .env
- Add a row in app_updates

2. Automated (advanced)
- Use GitHub API to fetch latest release metadata
- Map it into app_updates or direct UI output

Manual is recommended because it is easier to defend and maintain.

## 8. Suggested Team Workflow

For each release cycle:

1. Create feature branch
2. Develop and test
3. Merge to main/develop
4. Tag version when milestone is done
5. Create GitHub release
6. Update APP_VERSION in .env
7. Insert app_updates row with release notes
8. Confirm sidebar and Support & Updates page show latest version and notes

## 9. Example Real Workflow

Example commands:

```bash
git checkout main
git pull origin main
git merge feature/support-updates
git add .
git commit -m "Prepare release v0.3.0"
git push origin main
git tag v0.3.0
git push origin v0.3.0
```

Then create GitHub release `v0.3.0` and include notes such as:
- Added support tickets
- Added update tracking
- Added sidebar version display

Then update Laravel config values:

```env
APP_VERSION=0.3.0
```

And add one `app_updates` row:

- version: 0.3.0
- title: Support and Updates Module
- description: Added support tickets, update logs, and sidebar version display.
