# Release Checklist

## v1.0.0

### Product and contract

- Confirm `/api/v1` is the only base path communicated to new clients
- Decide when legacy `/api/...` compatibility routes will be removed
- Review [openapi.yaml](/Users/fevereiro/Documents/GitHub/ECOAL_26_team_4_API/openapi.yaml) against the intended public contract
- Confirm `/docs` exposure policy for the target environment

### Environment

- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Set `APP_URL` to the real public base URL
- Set `APP_DOCS_ENABLED=false` unless docs should be public
- Configure real mail credentials
- Configure `SANCTUM_STATEFUL_DOMAINS`
- Decide and set `SANCTUM_TOKEN_EXPIRATION` if token expiry is required
- Confirm log retention and log destination

### Database

- Choose the production database engine
- Run migrations in the target environment
- Confirm backup strategy
- Confirm rollback strategy

### Security

- Review CORS configuration for the real frontend origins
- Confirm rate limits are acceptable for production traffic
- Decide token lifetime and revocation policy
- Confirm docs are not unintentionally public

### Quality gates

- Run `php artisan test`
- Run a smoke test against `/api/v1/login`, `/api/v1/user`, and one protected write route
- Validate `/docs` and `/docs/openapi.yaml` behavior in the target environment
- Review audit log output for login, logout, and user changes

### Release wrap-up

- Tag the release as `v1.0.0`
- Publish or attach `CHANGELOG.md`
- Share base URL, auth flow, and OpenAPI spec location with consumers
