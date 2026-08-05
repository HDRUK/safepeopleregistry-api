// Request signing for Custodian-authenticated endpoints (query,
// custodian_users/bulk, project_users/bulk, validate).
//
// These endpoints require two headers instead of a bearer token:
//   x-client-id: your Custodian client_id
//   x-signature: SignCustodianPayload(<request body JSON string>, <your unique_identifier>)
//
// Your client_id and unique_identifier are issued to you out-of-band when
// your Custodian integration is provisioned - they are not discoverable
// from the API.
//
// IMPORTANT: the signature must be computed over the EXACT string you send
// as the request body, and that string must match what the server will
// produce when it re-encodes the same data with PHP's
// json_encode(..., JSON_UNESCAPED_SLASHES):
//   - forward slashes ("/") must NOT be escaped
//   - non-ASCII characters MUST be escaped as \uXXXX (System.Text.Json escapes
//     these by default with JavaScriptEncoder.Default, so the standard
//     serializer settings already match)
//   - object key order must match the order you originally built the payload in
//
// The safest pattern is: build your payload object, serialize it once, sign
// that exact string, and send that exact string as your request body.

using System;
using System.Collections.Generic;
using System.Security.Cryptography;
using System.Text;

namespace SafePeopleRegistryApiSdk.Custodian
{
    public static class CustodianSigning
    {
        public static string SignCustodianPayload(string payload, string secret)
        {
            using var hmac = new HMACSHA256(Encoding.UTF8.GetBytes(secret));
            var hash = hmac.ComputeHash(Encoding.UTF8.GetBytes(payload));
            return Convert.ToBase64String(hash);
        }

        public static Dictionary<string, string> BuildCustodianHeaders(string payload, string clientId, string secret)
        {
            return new Dictionary<string, string>
            {
                ["x-client-id"] = clientId,
                ["x-signature"] = SignCustodianPayload(payload, secret),
            };
        }
    }
}
