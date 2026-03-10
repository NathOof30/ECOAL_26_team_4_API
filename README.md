# ECOAL API - Lighter Collection Backend

REST API for a multi-user lighter collection platform built with Laravel and SQLite.

The project follows ECOAL constraints:
- One collection per user.
- Items classified with one required category and one optional second category.
- Items evaluated using shared criteria for comparison.
- Sanctum authentication with protected write operations.
- Ownership and role-based access control.

## Domain Model

- `users`: account data, role (`admin`, `editor`, `user`), avatar URL.
- `collections`: one collection per user (`user_id` is unique).
- `category`: classification values (mechanism, period values).
- `criteria`: comparison dimensions (Durability, Price, Rarity, Autonomy).
- `items`: lighter objects, linked to collection and categories, with visibility status.
- `item_criteria`: pivot table storing per-item criterion score (`0`, `1`, `2`).

## Security Rules

- Authentication uses Laravel Sanctum (`auth:sanctum`).
- Public routes are read-only for core resources.
- Write permissions:
	- Users can manage only their own account.
	- Admin users can create users and manage roles/activation status.
	- Admin users only can create/update/delete categories and criteria.
	- Users can create/update/delete only items and collections they own.
	- Users can score only items in their own collection.
- Registration cannot escalate privileges (`user_type` is forced to `user`).

## Upload Support

Two upload endpoints are available:
- `POST /api/user/avatar`: authenticated user avatar upload.
- `POST /api/items/{item}/image`: item image upload for item owner.

Validation rules:
- MIME: `jpg`, `jpeg`, `png`, `webp`
- Avatar max size: `2 MB`
- Item image max size: `5 MB`

Uploaded files are stored on the `public` disk and returned as `/storage/...` URLs.

## Item Publication Rule

An item cannot be created as public.

To publish an item (`status=true`), all criteria must have a score for that item. This guarantees consistent comparison across the collection.

## API Overview

Base URL:

```txt
http://127.0.0.1:8000/api
```

Authentication:
- `POST /register`
- `POST /login`
- `POST /logout` (auth)
- `GET /user` (auth)

Public read routes:
- `GET /users`, `GET /users/{id}`
- `GET /collections`, `GET /collections/{id}`
- `GET /categories`, `GET /categories/{id}`
- `GET /items`, `GET /items/{id}`
- `GET /criteria`, `GET /criteria/{id}`
- `GET /item-criteria`
- `GET /items/{item}/criteria`

Protected write routes:
- `POST /collections`, `PUT/PATCH /collections/{id}`, `DELETE /collections/{id}`
- `POST /items`, `PUT/PATCH /items/{id}`, `DELETE /items/{id}`
- `POST /item-criteria`, `PUT /items/{item}/criteria/{criterion}`, `DELETE /items/{item}/criteria/{criterion}`
- `POST /user/avatar`
- `POST /items/{item}/image`
- `PUT/PATCH /users/{id}`, `DELETE /users/{id}`

Admin-only routes:
- `POST /users`
- `POST/PUT/PATCH/DELETE /categories...`
- `POST/PUT/PATCH/DELETE /criteria...`

## Local Setup

1. Install dependencies:

```bash
composer install
```

2. Configure environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Configure SQLite database in `.env`:

```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

4. Run migrations and seeders:

```bash
php artisan migrate --seed
```

5. Create storage symlink for uploaded files:

```bash
php artisan storage:link
```

6. Start local server:

```bash
php artisan serve
```

## Tests

Run feature tests:

```bash
php artisan test --testsuite=Feature
```

Current feature tests include:
- CRUD and authentication checks.
- Ownership and authorization checks.
- Role hardening and privilege escalation protection.
- Avatar and item image upload validation.
- Item publication rule based on criteria completeness.
