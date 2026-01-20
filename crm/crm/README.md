
# Laravel CRM Foundation (Accounts & Contacts)

This is a **drop-in foundation** for a Laravel app that implements the core data model and API endpoints for **Accounts (Companies)** and **Contacts**, including groups/holdings, delegations, catalog categories, activities, attachments, RGPD consent fields, e-invoicing/billing fields for Odoo, and ownership (user/team).

> Tested with Laravel 11 / PHP 8.2+. Copy these files into a fresh Laravel project and run the migrations.

## Quick start

1. Create a new Laravel project:
   ```bash
   composer create-project laravel/laravel crm
   cd crm
   ```

2. Copy the contents of this zip into your project root (it contains `app/`, `database/`, `routes/`, `config/`).

3. Install dependencies you may want (optional):
   - spatie/laravel-permission (for RBAC), laravel/sanctum (API tokens), laravel/telescope (debug).
   ```bash
   composer require laravel/sanctum
   php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
   php artisan migrate
   ```

4. Run migrations for the CRM foundation:
   ```bash
   php artisan migrate
   ```

5. (Optional) Seed lookup data:
   ```bash
   php artisan db:seed --class=CrmSeed
   ```

6. Serve & test API endpoints (see below):
   ```bash
   php artisan serve
   # API base: /api/v1
   ```

## API Endpoints (initial)

- `GET    /api/v1/accounts`
- `POST   /api/v1/accounts`
- `GET    /api/v1/accounts/{id}`
- `PUT    /api/v1/accounts/{id}`
- `DELETE /api/v1/accounts/{id}`

- `GET    /api/v1/contacts`
- `POST   /api/v1/contacts`
- `GET    /api/v1/contacts/{id}`
- `PUT    /api/v1/contacts/{id}`
- `DELETE /api/v1/contacts/{id}`

> Controllers validate required fields, enforce unique emails, and basic ownership checks (stub). Extend Policies for fine-grained access.

## Notes

- **Ownership**: `owner_user_id` and `owner_team_id` on both Accounts & Contacts. Contacts inherit Account owner by default (in observer), with optional override.
- **Email unique** on Contacts. Additional emails and phones are supported via child tables.
- **E-invoicing**: required billing fields are on Accounts to feed Odoo (VAT, fiscal address, payment terms, IBAN/BIC, DIR3, channel, PEPPOL ID, flags and state dates).
- **Catalog**: generic categories that can link to Accounts and/or Contacts. Use for "Plan de Igualdad", "RSE", etc.
- **Groups/Holding**: `account_groups` + membership to attach many accounts to a group; also parent-child on accounts for matrix/filial.
- **Delegations**: `account_delegations` with their own address/phone and optional contact links.
- **Activities**: polymorphic (account/contact) with channels, status, outcomes.
- **Attachments**: polymorphic with disk/path for Flysystem storage; run `php artisan storage:link`.

## Next steps

- Add FormRequest validation rules as your business needs evolve.
- Implement Policies and middleware for team-based visibility.
- Implement dedupe services on form intake + observers (stubs included).
- Add search/filter endpoints (or use Laravel Scout/Meilisearch/Elasticsearch) and saved views.
- Add Mail & VoIP integrations as needed.
