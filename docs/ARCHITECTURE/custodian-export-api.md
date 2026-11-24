# Custodian export API

A Custodian's own server can pull everything the Registry holds that is relevant to it: its projects, the users on them, their profiles, and each user's validation status. It is machine-to-machine only. There is no front end and no logged-in person; requests are signed with the Custodian's API key.

A Custodian only ever receives its own data. It is identified by its signed credentials, and no endpoint takes a custodian id, so there's no parameter that could name another Custodian.

## Where things are defined

| What | Source of truth |
|---|---|
| Endpoints and middleware | The `v1/custodian/export` route group in `routes/api.php` |
| Parameters and full response schemas | Swagger, **Custodian Export** tag (generated from `CustodianExportController`) |
| Accepted query parameters | `App\Http\Requests\Custodians\ExportCustodianData` |
| Exported fields | `app/Http/Resources/CustodianExport/` (allow-listed) |
| Signature checks and timestamp window | `App\Http\Middleware\VerifySignedCustodianRequest`, `speedi.system.custodian_signature_tolerance` |
| Canonical string | `App\Http\Traits\HmacSigning::buildCanonicalRequest()` |
| Rate limit | `App\Http\Middleware\ThrottleVerifiedCustodian`, `speedi.system.custodian_export_rate_limit` |
| Behaviour, including what must never be exported | `tests/Feature/CustodianExportTest.php`, `VerifySignedCustodianRequestTest.php`, `ThrottleVerifiedCustodianTest.php` |

## Credentials

A Custodian signs requests with two values stored on its `Custodian` record and shown on the Home tab of its profile in the portal:

- **Client id** (`client_id`): identifies the Custodian and is sent on every request. It isn't a secret.
- **API key** (`unique_identifier`): the shared secret used to sign requests. Never send it, and keep it out of source control and logs. It is only returned to the Custodian's own admins and members (`CustodianController@show`). The same key signs the Registry's outbound webhooks to the Custodian.

## Signing a request

Send `x-client-id`, `x-timestamp` (current Unix seconds) and `x-signature`.

`x-signature` is `base64( HMAC-SHA256( canonical string, API key ) )`, using the raw binary digest, not hex. The canonical string is four lines joined by `\n`, with no newline after the last:

```
{x-timestamp}
{HTTP method, upper case}
{path, starting /api/...}
{query string}
```

Query string rules:

- Sort the parameters by key.
- Encode them per RFC 3986 (`%20` for a space, not `+`), joined with `&`.
- With no parameters, the last line is empty, so the string ends with `\n`.
- The URL you request may list parameters in any order. Only the signed string must be sorted.

A signature is only valid for that exact method, path and query, and only near the Registry's current time. Sign every request, including each page, separately.

### Worked example

API key `EXAMPLEsecretDoNotUse0123456789abcdefghij` (an example only), timestamp `1764000000`, requesting `GET /api/v1/custodian/export/users?per_page=50&page=2`:

```
canonical   = "1764000000\nGET\n/api/v1/custodian/export/users\npage=2&per_page=50"
x-signature = mXwq666p4b7fzjCmaPxzxP5tP/+7YHFw7/IRr6WQrO4=
```

The same request to `/api/v1/custodian/export/projects` with no query:

```
canonical   = "1764000000\nGET\n/api/v1/custodian/export/projects\n"
x-signature = QwgV/EzTbchOK1QaGWsb399TL9PyJBbeKeqaGUDnmTg=
```

Use these to check an implementation before calling the API. `test_a_signature_built_from_the_documented_canonical_string_is_accepted` in `VerifySignedCustodianRequestTest` keeps the server to this format.

### Shell example

```bash
CLIENT_ID='your-client-id'
API_KEY='your-api-key'
BASE_URL='https://registry.example.org'
REQUEST_PATH='/api/v1/custodian/export/users'
QUERY='page=2&per_page=50'   # sorted by key

TIMESTAMP=$(date +%s)
SIGNATURE=$(printf '%s\nGET\n%s\n%s' "$TIMESTAMP" "$REQUEST_PATH" "$QUERY" \
  | openssl dgst -sha256 -hmac "$API_KEY" -binary | base64)

curl -s "$BASE_URL$REQUEST_PATH?$QUERY" \
  -H "x-client-id: $CLIENT_ID" \
  -H "x-timestamp: $TIMESTAMP" \
  -H "x-signature: $SIGNATURE"
```

## Reading the users export

The fields are in Swagger and `ExportUserResource`. Two things aren't obvious from the schema:

- **Projects are scoped to the caller.** A user's `projects` only contains the calling Custodian's projects, even if the user is on other Custodians' projects too.
- **Each project carries the affiliation that links the user to it.** `projects[].affiliation` is the full affiliation, in the same shape as an entry in the user's `affiliations` history, and its `id` matches one there. It is `null` when no affiliation was recorded for the link. `affiliations` is the user's complete history, past and present.

Use `digi_ident` to match users between syncs.

## What is not exported

Fields are allow-listed, so nothing is exported unless a resource in `app/Http/Resources/CustodianExport/` names it. Contact details, identity documents and certificate numbers are deliberately left out (GDPR data minimisation). `test_the_export_contains_no_contact_or_identity_document_details` searches the raw response for those values and fails if any appear.

## Errors and limits

- **400:** a query parameter outside the rules in `ExportCustodianData`.
- **401:** missing headers get their own message. An unknown client id, an out-of-window timestamp, a disabled Custodian or a mismatched signature all return the same generic response, so it doesn't reveal whether a client id exists or which check failed. This depends on `verify.custodian.signed.request` being listed before `check.custodian.access` on the export routes, because that middleware's messages do distinguish them. `test_an_unknown_client_id_gets_the_same_response_as_a_bad_signature` fails if the order changes.
- **429:** the per-Custodian rate limit was exceeded. Wait for the number of seconds in the `Retry-After` header. Only correctly signed requests count towards the limit, so anyone who knows a client id can't use it up.

## Rotating the API key

Admins and non-read-only members of a Custodian can replace its API key from the portal API (`CustodianController@rotateCredentials`). The response contains the new key.

- The old key stops working immediately, for this API and for every other signed machine-to-machine endpoint.
- Outbound webhooks are signed with the new key from then on, so update webhook verification at the same time.
- The client id doesn't change.
- Every member of the Custodian gets an in-app notification, and the rotation is recorded in the activity log (who and when, never the key).

`CustodianCredentialRotationTest` covers each of these.

## For Registry engineers

### Middleware order

Laravel sorts middleware using its priority list, so the order listed in `routes/api.php` isn't necessarily the order they run in. Laravel's own `ThrottleRequests` is on that list and gets moved ahead of the custom route middleware. Used for the per-Custodian limit, it would count requests before their signature had been checked, and anyone could use up a Custodian's quota. `ThrottleVerifiedCustodian` isn't on the list, so it runs after `VerifySignedCustodianRequest`. `ThrottleVerifiedCustodianTest` fails if that ever stops being true.

To see the real order for a route:

```bash
php artisan tinker --execute='app(\Illuminate\Contracts\Http\Kernel::class); $r = app("router"); $route = collect($r->getRoutes()->getRoutes())->first(fn ($x) => $x->uri() === "api/v1/custodian/export/users"); foreach ($r->gatherRouteMiddleware($route) as $m) { echo (is_string($m) ? $m : get_class($m)) . PHP_EOL; }'
```

### Scoping

`CustodianExportController` takes the Custodian only from the request attribute set by `VerifySignedCustodianRequest`, and constrains every query and eager load by its id. Never accept a custodian id from the path or query on these routes.

### Logging and audit

Export and rotation requests are excluded from request/response logging; see [REQUEST_RESPONSE_LOGGING.md](REQUEST_RESPONSE_LOGGING.md). Each export request instead writes a `CustodianExport` audit entry (`CustodianExportController::audit()`).

### Adding a field

1. Add it to the resource, and to the eager load in `CustodianExportController` if it needs a relation or column.
2. Update the OpenAPI annotation.
3. If it could be personal data, decide whether Custodians need it. Anything that must stay out belongs in the "not exported" test.

## Known limitations

- **Users who left or were declined** are still exported, with their full profile, while their validation record exists. `validation_status` shows which.
- **Is a membership current?** A user's project entry has no project dates or project status. Combine `validation_status` with the same project in the projects export.
- **Read-only members can see the API key**, and the key also allows writes through the `/bulk` endpoints. This is an accepted risk.
- **Data protection:** this is a bulk transfer of personal data to Custodians. The lawful basis, retention and privacy notice need confirming with IG/DPO.
