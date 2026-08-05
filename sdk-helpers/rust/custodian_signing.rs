//! Request signing for Custodian-authenticated endpoints (query,
//! custodian_users/bulk, project_users/bulk, validate).
//!
//! These endpoints require two headers instead of a bearer token:
//!   x-client-id: your Custodian client_id
//!   x-signature: sign_custodian_payload(<request body JSON string>, <your unique_identifier>)
//!
//! Your client_id and unique_identifier are issued to you out-of-band when
//! your Custodian integration is provisioned - they are not discoverable
//! from the API.
//!
//! IMPORTANT: the signature must be computed over the EXACT string you send
//! as the request body, and that string must match what the server will
//! produce when it re-encodes the same data with PHP's
//! json_encode(..., JSON_UNESCAPED_SLASHES):
//!   - forward slashes ("/") must NOT be escaped
//!   - non-ASCII characters MUST be escaped as backslash-u hex escapes
//!     (serde_json does NOT do this by default - see
//!     escape_non_ascii_for_php_compat below)
//!   - object key order must match the order you originally built the payload in
//!
//! The safest pattern is: build your payload struct, serialize it once, sign
//! that exact string, and send that exact string as your request body.
//!
//! Requires the `hmac`, `sha2`, and `base64` crates.

use base64::{engine::general_purpose::STANDARD, Engine as _};
use hmac::{Hmac, Mac};
use sha2::Sha256;
use std::collections::HashMap;

type HmacSha256 = Hmac<Sha256>;

pub fn sign_custodian_payload(payload: &str, secret: &str) -> String {
    let mut mac =
        HmacSha256::new_from_slice(secret.as_bytes()).expect("HMAC can take key of any size");
    mac.update(payload.as_bytes());
    STANDARD.encode(mac.finalize().into_bytes())
}

pub fn build_custodian_headers(
    payload: &str,
    client_id: &str,
    secret: &str,
) -> HashMap<&'static str, String> {
    let mut headers = HashMap::new();
    headers.insert("x-client-id", client_id.to_string());
    headers.insert("x-signature", sign_custodian_payload(payload, secret));
    headers
}

/// serde_json does not escape non-ASCII characters the way PHP's json_encode
/// does by default. If your payload may contain non-ASCII text (names,
/// addresses, etc.), run the serialized JSON through this before signing
/// AND before sending, so both match PHP's re-encoded form exactly.
pub fn escape_non_ascii_for_php_compat(json: &str) -> String {
    let mut out = String::with_capacity(json.len());
    for c in json.chars() {
        if (c as u32) > 127 {
            out.push_str(&format!("\\u{:04x}", c as u32));
        } else {
            out.push(c);
        }
    }
    out
}
