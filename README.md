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

### Public

- `POST /api/register`: public. Validation in `RegisterRequest`.
- `POST /api/login`: public with `throttle:login`. Validation in `LoginRequest`.
- `GET /api/users`: public read-only.
- `GET /api/users/{user}`: public read-only.
- `GET /api/collections`: public read-only.
- `GET /api/collections/{collection}`: public read-only.
- `GET /api/categories`: public read-only.
- `GET /api/categories/{category}`: public read-only.
- `GET /api/items`: public read-only.
- `GET /api/items/{item}`: public read-only.
- `GET /api/criteria`: public read-only.
- `GET /api/criteria/{criterion}`: public read-only.
- `GET /api/item-criteria`: public read-only.
- `GET /api/items/{item}/criteria`: public read-only.

### Authenticated

- `POST /api/logout`: any authenticated user.
- `GET /api/user`: any authenticated user.
- `POST /api/users`: authenticated, then `UserPolicy@create`. Effectively `admin` only.
- `PUT|PATCH /api/users/{user}`: authenticated, then `UserPolicy@update`. Admin can update anyone; normal user only self.
- `DELETE /api/users/{user}`: authenticated, then `UserPolicy@delete`. Admin only, and cannot delete self.
- `POST /api/collections`: authenticated, then `CollectionPolicy@create`.
- `PUT|PATCH /api/collections/{collection}`: authenticated, then `CollectionPolicy@update`. Owner only.
- `DELETE /api/collections/{collection}`: authenticated, then `CollectionPolicy@delete`. Owner only.
- `POST /api/items`: authenticated. Collection ownership is enforced by how the item is attached to the current user collection.
- `PUT|PATCH /api/items/{item}`: authenticated, then `ItemPolicy@update`. Owner only.
- `DELETE /api/items/{item}`: authenticated, then `ItemPolicy@delete`. Owner only.
- `POST /api/item-criteria`: authenticated, then `ItemPolicy@score` through `StoreItemCriteriaRequest`. Item owner only.
- `PUT /api/items/{item}/criteria/{criterion}`: authenticated, then `ItemPolicy@score`. Item owner only.
- `DELETE /api/items/{item}/criteria/{criterion}`: authenticated, then `ItemPolicy@score`. Item owner only.

### Authenticated + role

- `POST|PUT|PATCH|DELETE /api/categories/...`: authenticated plus `user_type:admin,editor`.
- `POST|PUT|PATCH|DELETE /api/criteria/...`: authenticated plus `user_type:admin,editor`.

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
