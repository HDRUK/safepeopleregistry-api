/**
 * Request signing for Custodian-authenticated endpoints (query,
 * custodian_users/bulk, project_users/bulk, validate).
 *
 * These endpoints require two headers instead of a bearer token:
 *   x-client-id: your Custodian client_id
 *   x-signature: signCustodianPayload(<request body JSON string>, <your unique_identifier>)
 *
 * Your client_id and unique_identifier are issued to you out-of-band when
 * your Custodian integration is provisioned - they are not discoverable
 * from the API.
 *
 * IMPORTANT: the signature must be computed over the EXACT string you send
 * as the request body, and that string must match what the server will
 * produce when it re-encodes the same data with PHP's
 * json_encode(..., JSON_UNESCAPED_SLASHES):
 *   - forward slashes ("/") must NOT be escaped
 *   - non-ASCII characters MUST be escaped as unicode escapes (JSON.stringify
 *     does NOT do this by default - see escapeNonAsciiForPhpCompat below)
 *   - object key order must match the order you originally built the payload in
 *
 * The safest pattern is: build your payload object, serialize it once with
 * JSON.stringify, sign that exact string, and send that exact string as your
 * request body (set the `body` directly, don't let your HTTP client
 * re-serialize the object independently).
 *
 * Requires Node's built-in `crypto` module (or the Web Crypto API if
 * targeting a browser - see webcrypto variant below).
 */

import { createHmac } from 'crypto';

export function signCustodianPayload(payload: string, secret: string): string {
  return createHmac('sha256', secret).update(payload, 'utf8').digest('base64');
}

export function buildCustodianHeaders(
  payload: string,
  clientId: string,
  secret: string,
): Record<string, string> {
  return {
    'x-client-id': clientId,
    'x-signature': signCustodianPayload(payload, secret),
  };
}

const NON_ASCII_PATTERN = new RegExp('[' + String.fromCharCode(128) + '-' + String.fromCharCode(65535) + ']', 'g');

/**
 * JSON.stringify does not escape non-ASCII characters the way PHP's
 * json_encode does by default. If your payload may contain non-ASCII text
 * (names, addresses, etc.), run the stringified JSON through this before
 * signing AND before sending, so both match PHP's re-encoded form exactly.
 */
export function escapeNonAsciiForPhpCompat(json: string): string {
  return json.replace(NON_ASCII_PATTERN, (c) => {
    const hex = c.charCodeAt(0).toString(16);
    return '\\u' + '0000'.slice(hex.length) + hex;
  });
}

/**
 * Browser-safe variant using the Web Crypto API (async, no Node `crypto`).
 */
export async function signCustodianPayloadWebCrypto(payload: string, secret: string): Promise<string> {
  const key = await crypto.subtle.importKey(
    'raw',
    new TextEncoder().encode(secret),
    { name: 'HMAC', hash: 'SHA-256' },
    false,
    ['sign'],
  );
  const digest = await crypto.subtle.sign('HMAC', key, new TextEncoder().encode(payload));
  return btoa(String.fromCharCode(...new Uint8Array(digest)));
}
