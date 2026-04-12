# EduProfile App Separation Guide

This project has been split into two separate Laravel applications:

## Central App (EduProfile)
**Purpose:** Manages tenant creation, subscription, and developer features

**Location:** `d:\WEBSYSTEM\Tenant\EduProfile`

**Database:** `eduprofile_central`

**Key Features:**
- Tenant signup and management
- Developer dashboard for managing tenants
- School (tenant) records management
- Subscription and billing management

**Routes:**
- `/tenant-signup` - Tenant signup form (guest only)
- `/developer/*` - Developer dashboard (Central only)
- `/login` - Authentication
- `/profile` - User profile management

**Environment Variables:**
```env
APP_NAME="EduProfile Central"
DB_DATABASE=eduprofile_central
DB_HOST=127.0.0.1
```

**Installation:**
```bash
cd EduProfile
composer install
php artisan key:generate
php artisan migrate --database=mysql
npm install && npm run build
```

---

## Tenant App (EduProfile-Tenant)
**Purpose:** Manages tenant-specific operations (students, faculty, courses)

**Location:** `d:\WEBSYSTEM\Tenant\EduProfile-Tenant`

**Database:** Dynamic per tenant (from School.tenant_database)

**Key Features:**
- Student management
- Faculty dashboards
- School admin panels
- Per-tenant data isolation

**Routes:**
- `/student/*` - Student dashboard (authenticated)
- `/faculty/*` - Faculty dashboard (authenticated)
- `/admin/*` - Admin dashboard (admin role only)
- `/login` - Authentication
- `/profile` - User profile management

**Middleware:**
- `ResolveTenant` - Resolves tenant domain and switches database
- `EnsureTenantIsActive` - Validates tenant subscription status
- `admin` - Role-based access control

**Environment Variables:**
```env
APP_NAME="EduProfile Tenant"
# Tenant database (placeholder - switched at runtime)
DB_DATABASE=eduprofile_tenant_placeholder
DB_HOST=127.0.0.1
# Central database (for tenant lookup)
CENTRAL_DB_DATABASE=eduprofile_central
CENTRAL_DB_HOST=127.0.0.1
```

**Installation:**
```bash
cd EduProfile-Tenant
composer install
php artisan key:generate
npm install && npm run build
```

**Note:** Migrations in Tenant App are applied to each tenant's database dynamically.

---

## Database Architecture

### Central Database (eduprofile_central)
```
schools (tenants)
├── id
├── name
├── tenant_database (unique DB name for this tenant)
├── tenant_domain (unique domain for this tenant)
├── plan_type
├── plan_due_at
├── is_enabled
└── ...

users (developers/admin users)
├── id
├── school_id (nullable - NULL for developers)
├── email
├── role (developer, etc.)
└── ...
```

### Tenant Database (tenant_*, e.g., tenant_myschool_xyz123)
```
users (student/faculty in this tenant)
├── id
├── school_id (points to this tenant's school)
├── name
├── email
├── role (student, faculty, admin)
└── ...

students
├── id
├── user_id
├── school_id
└── ... (school-specific fields)
```

---

## Tenant Resolution Flow

1. User accesses `student.example.com`
2. Tenant App's `ResolveTenant` middleware:
   - Queries Central Database for domain
   - Finds School record with matching `tenant_domain`
   - Retrieves `tenant_database` name
   - Dynamically sets DB connection to that database
   - Stores tenant info in request
3. Subsequent queries use tenant's isolated database

---

## Setup on Server

### Development
```bash
# Terminal 1: Central App
cd EduProfile
php artisan serve --port=8000

# Terminal 2: Tenant App
cd EduProfile-Tenant
php artisan serve --port=8001
```

### Production
- **Central App**: `app.example.com` (main domain)
- **Tenant App**: `*.example.com` or `example.com/tenant/{id}` routing

Add DNS wildcards or router rules to route `*.example.com` to Tenant App server.

---

## Key Migrations

### Central App Migrations
- `*_create_users_table` - Central users (developers)
- `*_create_schools_table` - Tenant records
- `*_add_tenant_fields_to_schools_table` - Domain and DB tracking

### Tenant App Migrations
- `*_create_users_table` - Per-tenant users (students/faculty)
- `*_create_students_table` - Student records
- Other tenant-specific tables

---

## Authentication

### Central App
- Users authenticate against `users` table in central database
- Roles: `admin`, `developer`
- School-specific users have `school_id = NULL`

### Tenant App
- Users authenticate against `users` table in tenant's database
- Roles: `student`, `faculty`, `admin`
- All users must have `school_id` matching their tenant

---

## Migration Strategy When Deploying

1. Set up Central App first with central database
2. Create tenant signup functionality
3. Deploy Tenant App code on separate server/domain
4. When tenant signs up:
   - Central App creates database
   - Tenant App can immediately serve that domain
5. Environment variables are loaded from each app's `.env`

---

## Important Notes

- **Do NOT** run `php artisan migrate` in Tenant App to central database
- Central App only stores users with `school_id = NULL` or for tenant management
- Tenant App never stores data in central database
- Each tenant gets a completely isolated database for security and data privacy
- Plan type migrations: School table has billing information in central database
