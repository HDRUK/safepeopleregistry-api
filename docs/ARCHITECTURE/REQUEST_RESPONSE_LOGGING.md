# Request/Response Logging

`App\Http\Middleware\LogRequestResponse` is **global** middleware (`app/Http/Kernel.php`): it runs on every HTTP request and writes one `LogRequestResponse` info entry holding the method, full URL, request headers, request body, user id, response status, response headers and a truncated response body.

Because it sees every request, it must never write credentials or other sensitive data to the logs.

## What is masked

Values are replaced with `***` for any body key or header name (case-insensitive) in `$sensitiveFields`:

| Field | Why |
|---|---|
| `password`, `token`, `access_token` | Credentials in request bodies |
| `authorization` | Keycloak Bearer tokens |
| `cookie` | Request session cookies. The `web` session cookie is a super-admin credential for Horizon. |
| `set-cookie` | Response cookies, which issue those same sessions |

Response headers are masked as well as request headers.

## What is not logged at all

Requests are skipped completely (no log entry) when the path matches:

- `{horizon.path}` and `{horizon.path}/*` (default `horizon`): Horizon API responses contain queued job payloads, which may include personal data.
- `auth/horizon` and `auth/horizon/*`: the Keycloak callback URL carries the OAuth `code` and `state`, which would end up in the logged `url` and `body`.

See [HORIZON_DASHBOARD_AUTH.md](HORIZON_DASHBOARD_AUTH.md).

## Why not globally mask `code` / `state`?

They are common, legitimate field names across the API (e.g. country codes, address state). Masking them everywhere would make the logs much less useful. They are only sensitive in the OAuth callback, so that route is skipped instead.

## Adding new sensitive data

- A new credential-bearing field or header: add it to `$sensitiveFields`.
- A new route whose URL, body or response is sensitive throughout: add it to `shouldSkip()`.
- Add a regression test to `tests/Feature/LogRequestResponseTest.php` either way.

## Compliance note

These logs may still hold personal data from ordinary API bodies (names, emails and so on). Keep log retention and access in line with GDPR obligations.
