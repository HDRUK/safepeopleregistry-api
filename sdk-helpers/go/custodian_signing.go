// Package safepeopleregistrysdk - request signing for Custodian-authenticated endpoints
// (query, custodian_users/bulk, project_users/bulk, validate).
//
// These endpoints require two headers instead of a bearer token:
//
//	x-client-id: your Custodian client_id
//	x-signature: SignCustodianPayload(<request body JSON string>, <your unique_identifier>)
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
//   - non-ASCII characters MUST be escaped as backslash-u hex escapes
//     (Go's encoding/json does NOT do this by default - see
//     EscapeNonASCIIForPHPCompat below)
//   - object key order must match the order you originally built the payload in
//
// The safest pattern is: build your payload struct/map, marshal it once,
// sign that exact string, and send that exact string as your request body.
package safepeopleregistrysdk

import (
	"crypto/hmac"
	"crypto/sha256"
	"encoding/base64"
	"fmt"
	"strings"
)

func SignCustodianPayload(payload string, secret string) string {
	mac := hmac.New(sha256.New, []byte(secret))
	mac.Write([]byte(payload))
	return base64.StdEncoding.EncodeToString(mac.Sum(nil))
}

func BuildCustodianHeaders(payload string, clientID string, secret string) map[string]string {
	return map[string]string{
		"x-client-id": clientID,
		"x-signature": SignCustodianPayload(payload, secret),
	}
}

// EscapeNonASCIIForPHPCompat escapes non-ASCII runes as backslash-u hex
// escapes, matching PHP's json_encode default behaviour. Run this over your
// marshalled JSON before signing AND before sending if your payload may
// contain non-ASCII text (names, addresses, etc.).
func EscapeNonASCIIForPHPCompat(jsonStr string) string {
	var b strings.Builder
	for _, r := range jsonStr {
		if r > 127 {
			b.WriteString(fmt.Sprintf("\\u%04x", r))
		} else {
			b.WriteRune(r)
		}
	}
	return b.String()
}
