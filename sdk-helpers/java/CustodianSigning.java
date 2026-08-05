package uk.ac.hdruk.safepeopleregistryapi;

/*
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
 *   - non-ASCII characters MUST be escaped as backslash-u hex escapes (most
 *     Java JSON libraries, e.g. Jackson/Gson, do not do this by default -
 *     configure your serializer to escape non-ASCII, or restrict payloads to ASCII)
 *   - object key order must match the order you originally built the payload in
 *
 * The safest pattern is: build your payload object, serialize it once, sign
 * that exact string, and send that exact string as your request body.
 */

import java.nio.charset.StandardCharsets;
import java.util.Base64;
import java.util.HashMap;
import java.util.Map;
import javax.crypto.Mac;
import javax.crypto.spec.SecretKeySpec;

public final class CustodianSigning {

    private CustodianSigning() {
    }

    public static String signCustodianPayload(String payload, String secret) {
        try {
            Mac mac = Mac.getInstance("HmacSHA256");
            mac.init(new SecretKeySpec(secret.getBytes(StandardCharsets.UTF_8), "HmacSHA256"));
            byte[] digest = mac.doFinal(payload.getBytes(StandardCharsets.UTF_8));
            return Base64.getEncoder().encodeToString(digest);
        } catch (Exception e) {
            throw new RuntimeException("Failed to sign custodian payload", e);
        }
    }

    public static Map<String, String> buildCustodianHeaders(String payload, String clientId, String secret) {
        Map<String, String> headers = new HashMap<>();
        headers.put("x-client-id", clientId);
        headers.put("x-signature", signCustodianPayload(payload, secret));
        return headers;
    }
}
