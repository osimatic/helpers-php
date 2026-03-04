<?php

namespace Osimatic\Security;

/**
 * Verifies HMAC-SHA256 signatures on incoming HTTP requests to prevent tampering and replay attacks.
 *
 * The signing scheme uses a canonical string composed of:
 *   - A Unix timestamp (X-Timestamp header)
 *   - A one-time nonce (X-Nonce header)
 *   - Alphabetically sorted key=value pairs for each signed field
 *
 * The list of signed fields is defined by the caller, allowing this class to be used
 * for any type of request regardless of the domain.
 *
 * @link https://en.wikipedia.org/wiki/HMAC HMAC
 * @link https://en.wikipedia.org/wiki/Replay_attack Replay attack
 * @link https://en.wikipedia.org/wiki/Cryptographic_nonce Cryptographic nonce
 */
class RequestSignatureVerifier
{
    // ========================================
    // Constants
    // ========================================

    /**
     * Maximum allowed difference in seconds between the request timestamp and the server time.
     * Requests outside this window are rejected as expired.
     */
    public const int DEFAULT_TIMESTAMP_TOLERANCE_SECONDS = 300; // ±5 minutes

    // ========================================
    // Verification Methods
    // ========================================

    /**
     * Verifies the HMAC-SHA256 signature of an incoming request.
     *
     * Performs three checks in order:
     *   1. Timestamp freshness — rejects requests older than $toleranceSeconds
     *   2. Nonce uniqueness — rejects replayed requests
     *   3. Signature integrity — rejects tampered payloads
     *
     * The caller is responsible for providing the exact list of fields to sign,
     * sorted alphabetically, matching the signing logic on the client side.
     *
     * @param array  $postData         Request POST body
     * @param array  $headers          HTTP headers containing X-Timestamp, X-Nonce, and X-Signature
     * @param string $secret           Shared secret known to both the server and the client
     * @param array  $signedFields     Alphabetically sorted list of field names to include in the signature
     * @param int    $toleranceSeconds Maximum age of a valid request in seconds (default: 300)
     *
     * @throws \RuntimeException If any header is missing (code 400), the timestamp is expired (code 400),
     *                           the nonce was already used (code 400), or the signature is invalid (code 403)
     */
    public static function verify(
        array $postData,
        array $headers,
        string $secret,
        array $signedFields,
        int $toleranceSeconds = self::DEFAULT_TIMESTAMP_TOLERANCE_SECONDS
    ): void {
        $timestamp = $headers['X-Timestamp'] ?? null;
        $nonce     = $headers['X-Nonce']     ?? null;
        $signature = $headers['X-Signature'] ?? null;

        if (!$timestamp || !$nonce || !$signature) {
            throw new \RuntimeException('Missing signature headers', 400);
        }

        // 1. Reject requests with an expired timestamp.
        if (abs(time() - (int) $timestamp) > $toleranceSeconds) {
            throw new \RuntimeException('Request timestamp expired', 400);
        }

        // 2. Reject replayed requests via nonce deduplication.
        if (!self::isNonceUnique($nonce)) {
            throw new \RuntimeException('Nonce already used', 400);
        }

        // 3. Rebuild the canonical string and compare signatures.
        $canonical = self::buildCanonical($timestamp, $nonce, $signedFields, $postData);
        $expected  = base64_encode(hash_hmac('sha256', $canonical, $secret, true));

        if (!hash_equals($expected, $signature)) {
            throw new \RuntimeException('Invalid signature', 403);
        }

        // Nonce is valid — record it to block future replays.
        self::markNonceUsed($nonce);
    }

    // ========================================
    // Helper Methods
    // ========================================

    /**
     * Builds the canonical string that both the server and the client must sign independently.
     *
     * Format:
     * <pre>
     *   {timestamp}\n
     *   {nonce}\n
     *   field1=value1&field2=value2   (keys in the order provided, no trailing '&')
     * </pre>
     *
     * Missing fields are included with an empty string value to ensure
     * a deterministic canonical form on both sides.
     *
     * @param string $timestamp    Unix timestamp string from the X-Timestamp header
     * @param string $nonce        One-time random value from the X-Nonce header
     * @param array  $signedFields Ordered list of field names to include
     * @param array  $postData     Request POST body used to resolve field values
     * @return string The canonical string ready to be signed
     */
    private static function buildCanonical(
        string $timestamp,
        string $nonce,
        array $signedFields,
        array $postData
    ): string {
        $parts = [];
        foreach ($signedFields as $key) {
            $parts[] = $key . '=' . (isset($postData[$key]) ? (string) $postData[$key] : '');
        }

        return $timestamp . "\n" . $nonce . "\n" . implode('&', $parts);
    }

    /**
     * Checks whether the given nonce has not been used before.
     *
     * Must be implemented using a store that supports TTL-based expiry to avoid unbounded growth.
     *
     * Example with Redis (recommended — automatic TTL):
     * <code>
     *   return $redis->set("hmac_nonce:{$nonce}", 1, 'EX', 300, 'NX') !== false;
     * </code>
     *
     * Example with a database table:
     * <code>
     *   return DB::table('used_nonces')->where('nonce', $nonce)->doesntExist();
     * </code>
     *
     * @param string $nonce The nonce value from the X-Nonce header
     * @return bool True if the nonce has never been seen, false if it was already used
     */
    private static function isNonceUnique(string $nonce): bool
    {
        // TODO: implement using Redis or a database table
        return true;
    }

    /**
     * Records a nonce as used to prevent future replay attacks.
     *
     * The stored entry must expire after the configured tolerance window to avoid unbounded growth.
     *
     * Example with Redis:
     * <code>
     *   $redis->set("hmac_nonce:{$nonce}", 1, 'EX', 300);
     * </code>
     *
     * Example with a database table:
     * <code>
     *   DB::table('used_nonces')->insert(['nonce' => $nonce, 'used_at' => now()]);
     * </code>
     * Entries older than the tolerance window should be purged via a scheduled job.
     *
     * @param string $nonce The nonce value to record
     */
    private static function markNonceUsed(string $nonce): void
    {
        // TODO: implement using Redis or a database table
    }
}