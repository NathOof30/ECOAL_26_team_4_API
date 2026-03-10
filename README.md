# ECOAL API

## Quick start

### Requirements

- PHP `^8.2`
- Composer
- SQLite
- Node.js + npm

### Install

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
```

If you want the frontend assets available too:

```bash
npm install
npm run build
```

### Run locally

API only:

```bash
php artisan serve
```

Full local stack from the Composer script:

```bash
composer run dev
```

Production-oriented settings to review before deploy:

- `APP_ENV=production`
- `APP_DEBUG=false`
- `APP_URL`
- `APP_DOCS_ENABLED=false`
- mailer credentials
- `SANCTUM_STATEFUL_DOMAINS`
- `SANCTUM_TOKEN_EXPIRATION` if token lifetime should be enforced

### Documentation access

- `/docs` serves Swagger UI
- `/docs/openapi.yaml` serves the raw OpenAPI file
- docs are enabled by default in `local` and `testing`
- to expose docs in another environment, set `APP_DOCS_ENABLED=true`

### API versioning

- the canonical base path is `/api/v1`
- legacy unversioned `/api/...` routes are still available temporarily for backward compatibility
- new clients should integrate only against `/api/v1`

### Run tests

```bash
php artisan test
```

### Default API flow

1. `POST /api/v1/register` or `POST /api/v1/login`
2. Copy the returned bearer token
3. Send `Authorization: Bearer <token>` on protected routes
4. Use `GET /api/v1/user` to confirm the authenticated profile

Example login request:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password"}'
```

Example authenticated request:

```bash
curl http://127.0.0.1:8000/api/v1/user \
  -H "Accept: application/json" \
  -H "Authorization: Bearer YOUR_TOKEN"
```

## Authorization model

This API now uses three layers:

- `auth:sanctum` in [routes/api.php](/Users/fevereiro/Documents/GitHub/ECOAL_26_team_4_API/routes/api.php) to require authentication.
- `user_type` middleware in [bootstrap/app.php](/Users/fevereiro/Documents/GitHub/ECOAL_26_team_4_API/bootstrap/app.php) for simple role barriers such as `admin` or `admin,editor`.
- `Policies` plus `FormRequest::authorize()` for resource-based authorization such as "own profile", "own collection", or "own item".

## Where authorization lives

- Route middleware: fixed role checks that do not depend on a specific record.
- Policy: ownership and permission checks that depend on the target model.
- FormRequest: validation plus an authorization entry point that calls the policy before the controller runs.

## Route map

| Route | Access | Authorization source |
| --- | --- | --- |
| `POST /api/v1/register` | Public | `RegisterRequest` |
| `POST /api/v1/login` | Public | `throttle:login` + `LoginRequest` |
| `POST /api/v1/forgot-password` | Public | `ForgotPasswordRequest` |
| `POST /api/v1/reset-password` | Public | `ResetPasswordRequest` |
| `GET /api/v1/users` | Public | None |
| `GET /api/v1/users/{user}` | Public | None |
| `GET /api/v1/collections` | Public | None |
| `GET /api/v1/collections/{collection}` | Public | None |
| `GET /api/v1/categories` | Public | None |
| `GET /api/v1/categories/{category}` | Public | None |
| `GET /api/v1/items` | Public | None |
| `GET /api/v1/items/{item}` | Public | None |
| `GET /api/v1/criteria` | Public | None |
| `GET /api/v1/criteria/{criterion}` | Public | None |
| `GET /api/v1/item-criteria` | Public | None |
| `GET /api/v1/items/{item}/criteria` | Public | None |
| `POST /api/v1/logout` | Authenticated | `auth:sanctum` |
| `GET /api/v1/user` | Authenticated | `auth:sanctum` |
| `POST /api/v1/users` | Authenticated admin | `auth:sanctum` + `UserPolicy@create` |
| `PUT|PATCH /api/v1/users/{user}` | Authenticated | `auth:sanctum` + `UserPolicy@update` |
| `DELETE /api/v1/users/{user}` | Authenticated admin | `auth:sanctum` + `UserPolicy@delete` |
| `POST /api/v1/collections` | Authenticated | `auth:sanctum` + `CollectionPolicy@create` |
| `PUT|PATCH /api/v1/collections/{collection}` | Authenticated owner | `auth:sanctum` + `CollectionPolicy@update` |
| `DELETE /api/v1/collections/{collection}` | Authenticated owner | `auth:sanctum` + `CollectionPolicy@delete` |
| `POST /api/v1/items` | Authenticated | `auth:sanctum` + current user collection binding |
| `PUT|PATCH /api/v1/items/{item}` | Authenticated owner | `auth:sanctum` + `ItemPolicy@update` |
| `DELETE /api/v1/items/{item}` | Authenticated owner | `auth:sanctum` + `ItemPolicy@delete` |
| `POST /api/v1/item-criteria` | Authenticated owner | `auth:sanctum` + `StoreItemCriteriaRequest` + `ItemPolicy@score` |
| `PUT /api/v1/items/{item}/criteria/{criterion}` | Authenticated owner | `auth:sanctum` + `ItemPolicy@score` |
| `DELETE /api/v1/items/{item}/criteria/{criterion}` | Authenticated owner | `auth:sanctum` + `ItemPolicy@score` |
| `POST|PUT|PATCH|DELETE /api/v1/categories/...` | Authenticated admin/editor | `auth:sanctum` + `user_type:admin,editor` |
| `POST|PUT|PATCH|DELETE /api/v1/criteria/...` | Authenticated admin/editor | `auth:sanctum` + `user_type:admin,editor` |

## Main files

- Routes: [routes/api.php](/Users/fevereiro/Documents/GitHub/ECOAL_26_team_4_API/routes/api.php)
- Middleware alias: [bootstrap/app.php](/Users/fevereiro/Documents/GitHub/ECOAL_26_team_4_API/bootstrap/app.php)
- Policies registration: [app/Providers/AppServiceProvider.php](/Users/fevereiro/Documents/GitHub/ECOAL_26_team_4_API/app/Providers/AppServiceProvider.php)
- Policies: [app/Policies/UserPolicy.php](/Users/fevereiro/Documents/GitHub/ECOAL_26_team_4_API/app/Policies/UserPolicy.php), [app/Policies/CollectionPolicy.php](/Users/fevereiro/Documents/GitHub/ECOAL_26_team_4_API/app/Policies/CollectionPolicy.php), [app/Policies/ItemPolicy.php](/Users/fevereiro/Documents/GitHub/ECOAL_26_team_4_API/app/Policies/ItemPolicy.php), [app/Policies/CategoryPolicy.php](/Users/fevereiro/Documents/GitHub/ECOAL_26_team_4_API/app/Policies/CategoryPolicy.php), [app/Policies/CriteriaPolicy.php](/Users/fevereiro/Documents/GitHub/ECOAL_26_team_4_API/app/Policies/CriteriaPolicy.php)
- Requests: [app/Http/Requests](/Users/fevereiro/Documents/GitHub/ECOAL_26_team_4_API/app/Http/Requests)

## Practical rule

- If the rule is "must be logged in", use `auth:sanctum`.
- If the rule is "must have one of these roles", use `user_type`.
- If the rule is "depends on this record", use a policy.

## Response format

### Success

Single resource:

```json
{
  "data": {
    "id": 1,
    "title": "My First Collection"
  }
}
```

Paginated list:

```json
{
  "data": [
    {
      "id": 1,
      "title": "Alpha"
    }
  ],
  "links": {
    "first": "http://127.0.0.1:8000/api/v1/collections?page=1",
    "last": "http://127.0.0.1:8000/api/v1/collections?page=3",
    "prev": null,
    "next": "http://127.0.0.1:8000/api/v1/collections?page=2"
  },
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 3
  }
}
```

Validation error:

```json
{
  "message": "The given data was invalid.",
  "status": 422,
  "errors": {
    "email": [
      "The email field is required."
    ]
  }
}
```

## Query parameters

- `per_page`: number of records per page, max `100`
- `sort`: allowed sort field depends on the endpoint
- `direction`: `asc` or `desc`

Examples:

- `GET /api/v1/users?name=joao&sort=name&direction=asc&per_page=10`
- `GET /api/v1/collections?user_id=1&sort=title&direction=desc`
- `GET /api/v1/items?collection_id=2&category1_id=1&status=true`
- `GET /api/v1/item-criteria?id_item=5&sort=id_criteria&direction=asc`

## Password reset flow

1. `POST /api/v1/forgot-password` with the user email
2. Read the reset token from the email payload
3. `POST /api/v1/reset-password` with `email`, `token`, `password`, and `password_confirmation`

Forgot password example:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/forgot-password \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com"}'
```

Reset password example:

```bash
curl -X POST http://127.0.0.1:8000/api/v1/reset-password \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","token":"RESET_TOKEN","password":"NewStrongPass1!","password_confirmation":"NewStrongPass1!"}'
```

In local development, the token is sent by the configured mailer. For an API-only client, use the token returned in the reset email content.

## OpenAPI

A starter OpenAPI spec for the authentication endpoints is available in [openapi.yaml](/Users/fevereiro/Documents/GitHub/ECOAL_26_team_4_API/openapi.yaml).

## Audit logs

Security-relevant events are written to `storage/logs/audit.log`.

Current events:

- `auth.login_failed`
- `auth.login_inactive_user`
- `auth.login_success`
- `auth.logout`
- `users.created`
- `users.deactivated`
- `users.role_changed`
- `users.active_status_changed`
- `users.deleted`
