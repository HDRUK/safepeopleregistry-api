"""
Request signing for Custodian-authenticated endpoints (query,
custodian_users/bulk, project_users/bulk, validate).

These endpoints require two headers instead of a bearer token:
  x-client-id: your Custodian client_id
  x-signature: sign_custodian_payload(<request body JSON string>, <your unique_identifier>)

Your client_id and unique_identifier are issued to you out-of-band when your
Custodian integration is provisioned - they are not discoverable from the API.

IMPORTANT: the signature must be computed over the EXACT string you send as
the request body, and that string must match what the server will produce
when it re-encodes the same data with PHP's json_encode(..., JSON_UNESCAPED_SLASHES):
  - forward slashes ("/") must NOT be escaped
  - non-ASCII characters MUST be escaped as \\uXXXX (PHP's json_encode default -
    Python's json.dumps default of ensure_ascii=True already does this)
  - object key order must match the order you originally built the payload in

The safest pattern is: build your payload dict, serialize it once with
json.dumps(payload, separators=(",", ":")), sign that string, and send that
exact string as your request body (don't let requests/httpx re-serialize the
dict independently - pass the string directly as `data=`, not `json=`).
"""

import base64
import hashlib
import hmac


def sign_custodian_payload(payload: str, secret: str) -> str:
    """Compute the x-signature header value for a Custodian-authenticated request.

    Args:
        payload: the exact JSON string you will send as the request body.
        secret: your Custodian's unique_identifier.

    Returns:
        Base64-encoded HMAC-SHA256 signature to send as the x-signature header.
    """
    digest = hmac.new(secret.encode("utf-8"), payload.encode("utf-8"), hashlib.sha256).digest()
    return base64.b64encode(digest).decode("ascii")


def build_custodian_headers(payload: str, client_id: str, secret: str) -> dict:
    """Convenience helper returning both required headers as a dict."""
    return {
        "x-client-id": client_id,
        "x-signature": sign_custodian_payload(payload, secret),
    }
