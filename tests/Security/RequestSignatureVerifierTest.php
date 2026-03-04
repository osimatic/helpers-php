<?php

namespace Tests\Security;

use Osimatic\Security\RequestSignatureVerifier;
use PHPUnit\Framework\TestCase;

class RequestSignatureVerifierTest extends TestCase
{
    // ========================================
    // Helpers
    // ========================================

    /**
     * Builds a valid set of headers for the given parameters.
     */
    private function makeHeaders(
        string $secret,
        array $signedFields,
        array $postData,
        ?string $timestamp = null,
        string $nonce = 'test-nonce-abc123'
    ): array {
        $timestamp ??= (string) time();

        $parts = [];
        foreach ($signedFields as $key) {
            $parts[] = $key . '=' . (isset($postData[$key]) ? (string) $postData[$key] : '');
        }
        $canonical = $timestamp . "\n" . $nonce . "\n" . implode('&', $parts);
        $signature = base64_encode(hash_hmac('sha256', $canonical, $secret, true));

        return [
            'X-Timestamp' => $timestamp,
            'X-Nonce'     => $nonce,
            'X-Signature' => $signature,
        ];
    }

    // ========================================
    // Verification Methods Tests
    // ========================================

    public function testVerify(): void
    {
        $secret       = 'super-secret-key';
        $postData     = ['action' => 'submit', 'user_id' => '42', 'payload' => 'hello'];
        $signedFields = ['action', 'payload', 'user_id'];

        // --- Valid request: must not throw ---
        $headers = $this->makeHeaders($secret, $signedFields, $postData);
        RequestSignatureVerifier::verify($postData, $headers, $secret, $signedFields);
        self::assertTrue(true); // reached without exception

        // --- Missing X-Timestamp ---
        $headers = $this->makeHeaders($secret, $signedFields, $postData);
        unset($headers['X-Timestamp']);
        try {
            RequestSignatureVerifier::verify($postData, $headers, $secret, $signedFields);
            self::fail('Expected RuntimeException for missing X-Timestamp');
        } catch (\RuntimeException $e) {
            self::assertSame(400, $e->getCode());
        }

        // --- Missing X-Nonce ---
        $headers = $this->makeHeaders($secret, $signedFields, $postData);
        unset($headers['X-Nonce']);
        try {
            RequestSignatureVerifier::verify($postData, $headers, $secret, $signedFields);
            self::fail('Expected RuntimeException for missing X-Nonce');
        } catch (\RuntimeException $e) {
            self::assertSame(400, $e->getCode());
        }

        // --- Missing X-Signature ---
        $headers = $this->makeHeaders($secret, $signedFields, $postData);
        unset($headers['X-Signature']);
        try {
            RequestSignatureVerifier::verify($postData, $headers, $secret, $signedFields);
            self::fail('Expected RuntimeException for missing X-Signature');
        } catch (\RuntimeException $e) {
            self::assertSame(400, $e->getCode());
        }

        // --- Timestamp too old (> default tolerance) ---
        $expiredTimestamp = (string) (time() - RequestSignatureVerifier::DEFAULT_TIMESTAMP_TOLERANCE_SECONDS - 1);
        $headers = $this->makeHeaders($secret, $signedFields, $postData, $expiredTimestamp);
        try {
            RequestSignatureVerifier::verify($postData, $headers, $secret, $signedFields);
            self::fail('Expected RuntimeException for expired timestamp');
        } catch (\RuntimeException $e) {
            self::assertSame(400, $e->getCode());
        }

        // --- Timestamp too far in the future (> default tolerance) ---
        $futureTimestamp = (string) (time() + RequestSignatureVerifier::DEFAULT_TIMESTAMP_TOLERANCE_SECONDS + 1);
        $headers = $this->makeHeaders($secret, $signedFields, $postData, $futureTimestamp);
        try {
            RequestSignatureVerifier::verify($postData, $headers, $secret, $signedFields);
            self::fail('Expected RuntimeException for future timestamp');
        } catch (\RuntimeException $e) {
            self::assertSame(400, $e->getCode());
        }

        // --- Timestamp exactly at the tolerance boundary: must pass ---
        $boundaryTimestamp = (string) (time() - RequestSignatureVerifier::DEFAULT_TIMESTAMP_TOLERANCE_SECONDS);
        $headers = $this->makeHeaders($secret, $signedFields, $postData, $boundaryTimestamp);
        RequestSignatureVerifier::verify($postData, $headers, $secret, $signedFields);
        self::assertTrue(true);

        // --- Custom tolerance: tighter window rejects an otherwise valid timestamp ---
        $slightlyOldTimestamp = (string) (time() - 10);
        $headers = $this->makeHeaders($secret, $signedFields, $postData, $slightlyOldTimestamp);
        try {
            RequestSignatureVerifier::verify($postData, $headers, $secret, $signedFields, 5);
            self::fail('Expected RuntimeException with custom tight tolerance');
        } catch (\RuntimeException $e) {
            self::assertSame(400, $e->getCode());
        }

        // --- Custom tolerance: wider window accepts an older timestamp ---
        $olderTimestamp = (string) (time() - 10);
        $headers = $this->makeHeaders($secret, $signedFields, $postData, $olderTimestamp);
        RequestSignatureVerifier::verify($postData, $headers, $secret, $signedFields, 60);
        self::assertTrue(true);

        // --- Wrong secret: signature mismatch ---
        $headers = $this->makeHeaders('correct-secret', $signedFields, $postData);
        try {
            RequestSignatureVerifier::verify($postData, $headers, 'wrong-secret', $signedFields);
            self::fail('Expected RuntimeException for wrong secret');
        } catch (\RuntimeException $e) {
            self::assertSame(403, $e->getCode());
        }

        // --- Tampered post data: signature mismatch ---
        $headers = $this->makeHeaders($secret, $signedFields, $postData);
        $tamperedData = array_merge($postData, ['user_id' => '99']);
        try {
            RequestSignatureVerifier::verify($tamperedData, $headers, $secret, $signedFields);
            self::fail('Expected RuntimeException for tampered post data');
        } catch (\RuntimeException $e) {
            self::assertSame(403, $e->getCode());
        }

        // --- Tampered signature ---
        $headers = $this->makeHeaders($secret, $signedFields, $postData);
        $headers['X-Signature'] = base64_encode('definitely-not-a-valid-hmac');
        try {
            RequestSignatureVerifier::verify($postData, $headers, $secret, $signedFields);
            self::fail('Expected RuntimeException for tampered signature');
        } catch (\RuntimeException $e) {
            self::assertSame(403, $e->getCode());
        }

        // --- Missing field in postData: empty string used, must still match ---
        $postDataWithMissing = ['action' => 'submit', 'user_id' => '42']; // 'payload' absent
        $headers = $this->makeHeaders($secret, $signedFields, $postDataWithMissing);
        RequestSignatureVerifier::verify($postDataWithMissing, $headers, $secret, $signedFields);
        self::assertTrue(true);

        // --- Empty signed fields list ---
        $headers = $this->makeHeaders($secret, [], $postData);
        RequestSignatureVerifier::verify($postData, $headers, $secret, []);
        self::assertTrue(true);
    }
}