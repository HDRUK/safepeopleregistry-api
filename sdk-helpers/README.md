# Custodian request signing helpers

Every generated SDK ships with a small `custodian_signing` module (one per
language) that implements the HMAC signing scheme required by the four
Custodian-authenticated endpoints:

- `POST /query`
- `POST /validate`
- `POST /custodian_users/bulk`
- `POST /project_users/bulk`

These endpoints are authenticated with two headers instead of a bearer
token:

| Header | Value |
|---|---|
| `x-client-id` | Your Custodian `client_id` |
| `x-signature` | `base64(HMAC-SHA256(<request body JSON>, <your unique_identifier>))` |

Your `client_id` and `unique_identifier` are issued out-of-band when your
Custodian integration is provisioned - they cannot be discovered from the
API itself.

## Why openapi-generator can't do this for you

OpenAPI's `securitySchemes` only model `apiKey`, `http` (basic/bearer),
`oauth2`, and `openIdConnect`. There is no scheme type for "sign the request
body with a secret you hold" - so no generator, in any language, can
compute this automatically. The generated SDK exposes `x-client-id` and
`x-signature` as plain string parameters on the relevant methods; these
helpers are what you use to compute the `x-signature` value before calling
them.

## The one gotcha that will bite you

The server verifies your signature against `json_encode($request->all(),
JSON_UNESCAPED_SLASHES)` - i.e. PHP's own re-encoding of your decoded
payload, not the literal bytes you sent. To match that:

- **Forward slashes must not be escaped.** Most JSON libraries already do
  this by default.
- **Non-ASCII characters must be escaped as `\uXXXX`.** PHP's `json_encode`
  does this by default; most other languages' JSON libraries do *not*
  (JavaScript's `JSON.stringify`, Go's `encoding/json`, Rust's `serde_json`,
  Java's Jackson/Gson all leave non-ASCII characters as-is). Each helper
  below includes an `escapeNonAsciiForPhpCompat`-style function - run your
  serialized JSON through it if your payload might contain non-ASCII text
  (names, addresses, etc.) before both signing and sending.
- **Key order matters.** Sign the exact string you're about to send, and
  send that exact string as your body - don't let your HTTP client
  re-serialize the object independently after you've signed a different
  serialization of it.

If your signature verification keeps failing, this is almost always why.

## Usage (pattern is the same across languages)

```
payload = build_and_serialize(body)      # JSON string, PHP-encoding-compatible
signature = sign_custodian_payload(payload, secret)
headers = { "x-client-id": client_id, "x-signature": signature }
# send `payload` as the raw body, with `headers` set
```

## Per-language notes

- **Python** (`custodian_signing.py`): stdlib only (`hmac`, `hashlib`,
  `base64`). `json.dumps` already escapes non-ASCII by default
  (`ensure_ascii=True`), so no extra step needed there.
- **TypeScript** (`custodianSigning.ts`): uses Node's `crypto` module; a
  Web Crypto (`signCustodianPayloadWebCrypto`) variant is included for
  browser targets.
- **C#** (`CustodianSigning.cs`): `System.Security.Cryptography.HMACSHA256`.
  `System.Text.Json` already escapes non-ASCII by default.
- **Java** (`CustodianSigning.java`): `javax.crypto.Mac`. Most JSON
  libraries (Jackson, Gson) do not escape non-ASCII by default - configure
  your serializer or restrict payloads to ASCII.
- **Go** (`custodian_signing.go`): stdlib only (`crypto/hmac`,
  `crypto/sha256`). Requires no extra dependency.
- **Rust** (`custodian_signing.rs`): requires the `hmac`, `sha2`, and
  `base64` crates - `scripts/generate-sdks.sh` adds these to the generated
  `Cargo.toml` automatically.
