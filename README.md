# EduProfile - School Profiling and Student Status Monitoring System

EduProfile is a multi-tenant Laravel platform with two applications:

- Central app: tenant onboarding, approvals, plan administration, support tracking
- Tenant app: school-level student management, document workflow, status monitoring, and RBAC

## Repository Structure

- Central/Central: central administration application
- Tenant/Tenant: tenant application

## Prerequisites

- PHP 8.2+
- Composer 2+
- Node.js 20+
- MySQL 8+

## Central App Setup

1. Open terminal in Central/Central
2. Install dependencies:
   - composer install
   - npm install
3. Configure environment:
   - copy .env.example to .env
   - set DB credentials and mail settings
4. Generate application key:
   - php artisan key:generate
5. Run migrations and seeders:
   - php artisan migrate --seed
6. Build assets:
   - npm run build
7. Start app:
   - php artisan serve --host=127.0.0.1 --port=8000

## Tenant App Setup

1. Open terminal in Tenant/Tenant
2. Install dependencies:
   - composer install
   - npm install
3. Configure environment:
   - copy .env.example to .env
   - set tenant DB credentials and central DB credentials
4. Generate application key:
   - php artisan key:generate
5. Run migrations and seeders:
   - php artisan migrate --seed
6. Build assets:
   - npm run build
7. Start app:
   - php artisan serve --host=127.0.0.1 --port=8001

## Release Preparation Checklist

- Verify production env values in both .env files
- Run php artisan config:cache and php artisan route:cache in deployment
- Run automated tests before tagging release
- Ensure no development debug logging remains enabled
- Build production assets in both apps

## Recent Module Additions

- Tenant request approval queue with pending, approved, rejected lifecycle
- Tenant disable flow with reason and disabled timestamp
- Modular RBAC foundation (roles, permissions, user-role mapping)
- Modular plan tables (plans, plan_features, tenant_plan)
- Versioning schema preparation (version, release_notes)
- Support ticket CRUD structure (support_tickets)
