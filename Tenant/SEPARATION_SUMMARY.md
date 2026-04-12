# Application Separation Summary

## What Was Changed

### Central App (EduProfile)
**Removed:**
- ❌ `app/Http/Controllers/Admin/` - Tenant-specific admin features
- ❌ `app/Models/Student.php` - Tenant-specific model
- ❌ Routes for `/faculty/*`, `/student/*`, `/admin/*`
- ❌ Middleware: `EnsureUserIsAdmin`, `EnsureTenantIsActive` aliases
- ❌ Student-related migrations from being used centrally

**Kept:**
- ✅ `app/Http/Controllers/TenantSignupController.php` - Tenant creation
- ✅ `app/Http/Controllers/Developer/TenantController.php` - Tenant management
- ✅ `app/Models/School.php` - Tenant (school) records
- ✅ `app/Models/User.php` - For developers/admins
- ✅ `app/Http/Middleware/EnsureCentralDomain.php` - Blocks tenant domains
- ✅ `app/Http/Middleware/EnsureUserIsDeveloper.php` - Developer access control
- ✅ Routes: `/tenant-signup`, `/developer/*`, `/login`, `/profile`

**Model Changes:**
- `User` model: Only developers and admins (no students/faculty)
- `School` model: Tracks tenant databases and domains

**Database:**
- Default: `eduprofile_central`
- Stores: School records, Developer users, Billing data

---

### Tenant App (EduProfile-Tenant)
**Removed:**
- ❌ `app/Http/Controllers/Developer/` - Central-only feature
- ❌ `app/Http/Controllers/TenantSignupController.php` - Central-only
- ❌ `app/Http/Middleware/EnsureCentralDomain.php` - Not needed
- ❌ `app/Http/Middleware/EnsureUserIsDeveloper.php` - Not needed
- ❌ Central-only models references

**Added:**
- ✅ `app/Http/Middleware/ResolveTenant.php` - NEW: Tenant detection and DB switching
- ✅ All student/faculty/admin features kept

**Kept:**
- ✅ `app/Http/Controllers/Admin/*` - School admin features
- ✅ `app/Models/Student.php` - Student records
- ✅ `app/Models/User.php` - Tenant users (students/faculty)
- ✅ `app/Http/Middleware/EnsureTenantIsActive.php` - Subscription check
- ✅ `app/Http/Middleware/EnsureUserIsAdmin.php` - Admin access control
- ✅ Routes: `/student/*`, `/faculty/*`, `/admin/*`, `/login`, `/profile`

**Middleware Flow (Tenant App):**
```
Request → ResolveTenant (detect & switch DB) → EnsureTenantIsActive (subscription check) → Routes
```

**Database:**
- Tenant database: Per-school isolated database (switched at runtime)
- Central database: Read-only access for tenant lookup (via 'central' connection)

---

## Configuration Changes

### Central App (.env)
```env
APP_NAME="EduProfile Central"
DB_DATABASE=eduprofile_central
```

### Tenant App (.env)
```env
APP_NAME="EduProfile Tenant"
DB_DATABASE=eduprofile_tenant_placeholder  # Placeholder (overridden by ResolveTenant)
CENTRAL_DB_DATABASE=eduprofile_central      # Read-only central DB connection
CENTRAL_DB_HOST=127.0.0.1
CENTRAL_DB_USERNAME=root
CENTRAL_DB_PASSWORD=
```

---

## Key Technical Details

### File-by-File Changes

| File | Central | Tenant |
|------|---------|--------|
| `routes/web.php` | Signup + Developer only | Student/Faculty/Admin only |
| `bootstrap/app.php` | Removed tenant middleware aliases | Added ResolveTenant, removed central aliases |
| `config/app.php` | Name = "EduProfile Central" | Name = "EduProfile Tenant" |
| `config/database.php` | Standard MySQL config | Added 'central' connection for tenant lookup |
| `app/Models/User.php` | Central developers/admins | Tenant users (students/faculty/admins) |
| `app/Models/School.php` | Tenant registry with DB/domain | Removed (or minimal local copy) |
| `app/Models/Student.php` | ❌ Removed | ✅ Kept |

### New Middleware (Tenant App)
**ResolveTenant.php** - Executes before authentication:
1. Gets request domain (e.g., `myschool.example.com`)
2. Queries Central Database for School with matching `tenant_domain`
3. Updates DB config to use that tenant's database
4. Stores tenant info in request for later use

---

## Running Both Apps Locally

```bash
# Terminal 1: Central App
cd d:\WEBSYSTEM\Tenant\EduProfile
php artisan serve --port=8000

# Terminal 2: Tenant App  
cd d:\WEBSYSTEM\Tenant\EduProfile-Tenant
php artisan serve --port=8001
```

**Local Hosts File** (`C:\Windows\System32\drivers\etc\hosts`)
```
127.0.0.1   app.local
127.0.0.1   tenant1.local
127.0.0.1   tenant2.local
```

**Local Testing:**
- Central: `http://app.local:8000`
- Tenant 1: `http://tenant1.local:8001`
- Tenant 2: `http://tenant2.local:8001`

---

## Migration Execution

### Initial Setup

**Central App:**
```bash
php artisan migrate
# Creates: schools, users, sessions, migrations tables
```

**Tenant App:**
- Run migrations for each tenant's database separately
- Or: Use a command to migrate all tenant databases

### Tenant Creation Flow

1. User visits Central App's `/tenant-signup`
2. Controller:
   - Creates School record in central database
   - Creates tenant database with unique name
   - Runs Tenant App migrations in that database
   - Creates admin user in tenant database
3. User can now log into `tenantdomain.example.com`
4. Tenant App's ResolveTenant middleware handles domain → database mapping

---

## Directory Structure After Separation

```
d:\WEBSYSTEM\Tenant\
├── EduProfile/                          # Central App
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── Developer/TenantController.php  ✅
│   │   │   ├── TenantSignupController.php      ✅
│   │   │   └── ProfileController.php            ✅
│   │   ├── Models/
│   │   │   ├── School.php                      ✅
│   │   │   └── User.php                        ✅
│   │   └── Http/Middleware/
│   │       ├── EnsureCentralDomain.php         ✅
│   │       └── EnsureUserIsDeveloper.php       ✅
│   ├── routes/web.php                   ✅ (Central only routes)
│   ├── config/database.php              ✅ (Central DB only)
│   └── .env                             ✅ (Central config)
│
├── EduProfile-Tenant/                   # Tenant App (NEW)
│   ├── app/
│   │   ├── Http/Controllers/
│   │   │   ├── Admin/                           ✅
│   │   │   └── ProfileController.php            ✅
│   │   ├── Models/
│   │   │   ├── Student.php                      ✅
│   │   │   └── User.php                         ✅
│   │   └── Http/Middleware/
│   │       ├── ResolveTenant.php                ✅ (NEW)
│   │       ├── EnsureTenantIsActive.php         ✅
│   │       └── EnsureUserIsAdmin.php            ✅
│   ├── routes/web.php                   ✅ (Tenant only routes)
│   ├── config/database.php              ✅ (Tenant + Central connections)
│   └── .env                             ✅ (Tenant config)
│
└── APP_SEPARATION_GUIDE.md              ✅ (This file)
```

---

## Next Steps

1. **Database Setup:**
   - Create `eduprofile_central` database
   - Run Central App migrations

2. **Configure Tenant Signup:**
   - Verify `TenantSignupController` works
   - Test database provisioning

3. **Test Local Tenant Creation:**
   - Sign up a new tenant
   - Verify database created and migrations run
   - Test tenant login at new domain

4. **Deploy to Production:**
   - Deploy Central App to main domain
   - Deploy Tenant App to wildcard domain or separate server
   - Configure DNS and web server routing

5. **Optional Enhancements:**
   - Add API endpoints for tenant management
   - Add webhook for billing integration
   - Add domain validation (e.g., prevent subdomain conflicts)

---

## Security Notes

✅ **Done Right:**
- Tenant databases are isolated
- Central domain middleware blocks tenant domains
- Each tenant only accesses their own database

⚠️ **To Consider:**
- Validate domain names on signup
- Add rate limiting to tenant signup
- Log all tenant creation events
- Monitor for unauthorized database access
- Regular backups of central and tenant databases
