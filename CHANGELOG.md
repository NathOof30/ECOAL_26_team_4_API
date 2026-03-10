# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2026-03-10

### Added

- versioned API entrypoint under `/api/v1`
- compatibility layer for existing unversioned `/api/...` routes
- Sanctum-based authentication with register, login, logout, and authenticated profile routes
- password reset flow with `forgot-password` and `reset-password`
- role barriers for `admin`, `editor`, and `user`
- ownership policies for users, collections, items, and item scoring
- audit logging for authentication and user management events
- Swagger UI at `/docs`
- local OpenAPI spec at `/docs/openapi.yaml`
- release-oriented homepage replacing the default Laravel landing page
- feature tests for CRUD, ownership, docs access, password reset, throttling, and versioned auth routes

### Changed

- canonical API base path is now `/api/v1`
- README updated with setup, auth flow, docs access, versioning, and deploy-oriented notes
- OpenAPI spec expanded with examples for major resources and auth flows
- sensitive public auth routes now have explicit throttling
- docs are disabled by default outside `local` and `testing`, unless `APP_DOCS_ENABLED=true`
- Sanctum token expiration is now configurable through environment variables

### Fixed

- controller authorization support via Laravel authorization trait
- user factory defaults aligned with runtime authorization expectations
- duplicate item score handling now returns `409` consistently
- deleting a missing item score now returns `404`
- password reset notifications no longer depend on a missing web route

### Notes

- SQLite remains the default local/test database
- mail delivery, production infrastructure, and deployment pipeline still require environment-specific setup before public launch
