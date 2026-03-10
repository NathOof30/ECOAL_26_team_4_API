# ECOAL API

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
| `POST /api/register` | Public | `RegisterRequest` |
| `POST /api/login` | Public | `throttle:login` + `LoginRequest` |
| `GET /api/users` | Public | None |
| `GET /api/users/{user}` | Public | None |
| `GET /api/collections` | Public | None |
| `GET /api/collections/{collection}` | Public | None |
| `GET /api/categories` | Public | None |
| `GET /api/categories/{category}` | Public | None |
| `GET /api/items` | Public | None |
| `GET /api/items/{item}` | Public | None |
| `GET /api/criteria` | Public | None |
| `GET /api/criteria/{criterion}` | Public | None |
| `GET /api/item-criteria` | Public | None |
| `GET /api/items/{item}/criteria` | Public | None |
| `POST /api/logout` | Authenticated | `auth:sanctum` |
| `GET /api/user` | Authenticated | `auth:sanctum` |
| `POST /api/users` | Authenticated admin | `auth:sanctum` + `UserPolicy@create` |
| `PUT|PATCH /api/users/{user}` | Authenticated | `auth:sanctum` + `UserPolicy@update` |
| `DELETE /api/users/{user}` | Authenticated admin | `auth:sanctum` + `UserPolicy@delete` |
| `POST /api/collections` | Authenticated | `auth:sanctum` + `CollectionPolicy@create` |
| `PUT|PATCH /api/collections/{collection}` | Authenticated owner | `auth:sanctum` + `CollectionPolicy@update` |
| `DELETE /api/collections/{collection}` | Authenticated owner | `auth:sanctum` + `CollectionPolicy@delete` |
| `POST /api/items` | Authenticated | `auth:sanctum` + current user collection binding |
| `PUT|PATCH /api/items/{item}` | Authenticated owner | `auth:sanctum` + `ItemPolicy@update` |
| `DELETE /api/items/{item}` | Authenticated owner | `auth:sanctum` + `ItemPolicy@delete` |
| `POST /api/item-criteria` | Authenticated owner | `auth:sanctum` + `StoreItemCriteriaRequest` + `ItemPolicy@score` |
| `PUT /api/items/{item}/criteria/{criterion}` | Authenticated owner | `auth:sanctum` + `ItemPolicy@score` |
| `DELETE /api/items/{item}/criteria/{criterion}` | Authenticated owner | `auth:sanctum` + `ItemPolicy@score` |
| `POST|PUT|PATCH|DELETE /api/categories/...` | Authenticated admin/editor | `auth:sanctum` + `user_type:admin,editor` |
| `POST|PUT|PATCH|DELETE /api/criteria/...` | Authenticated admin/editor | `auth:sanctum` + `user_type:admin,editor` |

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
    "first": "http://127.0.0.1:8000/api/collections?page=1",
    "last": "http://127.0.0.1:8000/api/collections?page=3",
    "prev": null,
    "next": "http://127.0.0.1:8000/api/collections?page=2"
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

- `GET /api/users?name=joao&sort=name&direction=asc&per_page=10`
- `GET /api/collections?user_id=1&sort=title&direction=desc`
- `GET /api/items?collection_id=2&category1_id=1&status=true`
- `GET /api/item-criteria?id_item=5&sort=id_criteria&direction=asc`
