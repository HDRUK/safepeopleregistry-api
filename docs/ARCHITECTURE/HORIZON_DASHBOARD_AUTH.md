# Horizon dashboard authentication

How a person opens the Horizon dashboard in a browser, and why it works this way.

## The problem

The API authenticates with the `api` guard (Keycloak, `Authorization: Bearer`
header). A browser opening `/horizon` from the address bar can't send that
header, so it was always treated as unauthenticated and sent to the portal.

Accepting the token in the URL (`/horizon?token=...`) was removed in the May
2026 security audit (#723): tokens in URLs end up in logs, browser history
and `Referer` headers. Don't bring that back.

## The flow

```
Browser ── GET /horizon ──▶ Authenticate middleware (auth:web)
                              │ no web session
                              ▼
            302 /auth/horizon/login ──▶ 302 Keycloak sign-in (authorization code)
                                                   │
            GET /auth/horizon/callback ◀───────────┘
              │ Socialite swaps the code for a token and checks `state` (CSRF)
              │ finds User by keycloak_id (none → 403)
              │ Auth::guard('web')->login($user) and the session ID is regenerated
              ▼
            302 /horizon ──▶ Horizon::auth → viewHorizon gate → dashboard
```

| Piece | Where |
| ----- | ----- |
| Horizon route middleware `['web', 'auth:web']` | `config/horizon.php` |
| Horizon path matching and redirect to login | `app/Http/Middleware/Authenticate.php` (`handleHorizonAuth`) |
| Login, callback and logout | `app/Http/Controllers/HorizonAuthController.php`, `routes/web.php` |
| Who may view (`viewHorizon` gate, `Horizon::auth`) | `app/Providers/HorizonServiceProvider.php` |

The login routes live under `/auth/horizon/*` rather than `/horizon/*`
because Horizon registers a catch-all `horizon/{view?}` route that would
shadow them.

`Horizon::auth` is set explicitly so it checks the `web` guard user. That
replaces Horizon's default, which lets everyone in when `APP_ENV=local`.
Local developers sign in like everyone else.

Logout: `GET /auth/horizon/logout`.

## Deployment requirements

- **Keycloak client:** the client in `KEYCLOAK_CLIENT_ID` must have the
  standard (authorization code) flow enabled. Each environment's
  `https://<api-host>/auth/horizon/callback` must be in its valid redirect URIs.
- **Ingress:** `/horizon*` and `/auth/horizon/*` must be routed to the API pod
  (not only `/api/*`).
- **Sessions:** with more than one API pod, `SESSION_DRIVER` must be shared
  (`redis` or `database`), not `file`. With `file`, the OAuth `state` and the
  login session end up on whichever pod served the request, which causes
  login loops or "invalid state" errors.
- **Cookies:** set `SESSION_SECURE_COOKIE=true` in deployed environments.
  `same_site` is `lax`, which is needed for the Keycloak redirect back to the
  callback to carry the session cookie.

## Who can view it

**Super-admins only (`user_group = ADMINS`, Keycloak group `\Admins`), in
every environment.** This is enforced twice:

1. **At login:** the callback refuses (403) any Keycloak identity that has no
   registry user or that fails the `viewHorizon` gate, so other users never
   get a session.
2. **On every request:** `Horizon::auth` checks `viewHorizon` against the
   session user, so a user demoted after signing in loses access on their
   next request.

Having a Keycloak account is not enough. Researchers, organisation users and
custodians are all refused.

Why: the dashboard shows job payloads, which may contain personal data (e.g.
notification/email jobs), so access is limited to the people who operate the
platform (GDPR data minimisation).

To change who can view it, edit the `viewHorizon` gate only. The login
callback and `Horizon::auth` both use it.

## Logging

The `web` session cookie is a super-admin credential and the callback URL
carries the OAuth `code`/`state`, so the global `LogRequestResponse`
middleware skips `horizon*` and `auth/horizon/*` entirely and masks
`cookie`/`set-cookie` headers everywhere. See
[REQUEST_RESPONSE_LOGGING.md](REQUEST_RESPONSE_LOGGING.md).

## Tests

`tests/Feature/HorizonBrowserAuthTest.php` covers the session regression,
the redirect to login, the callback (admin, non-admin and unknown Keycloak
users) and the admin-only gate in every environment. `tests/Feature/ClaudeSecurityReview_Sonnet_4-6_1.php`
still asserts that tokens in the query string are rejected.
