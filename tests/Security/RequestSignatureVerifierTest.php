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
    // Constructor & Configuration Tests
    // ========================================

    public function testConstructor(): void
    {
        // Default constructor: secret is empty, tolerance is 300
        $verifier = new RequestSignatureVerifier();
        self::assertInstanceOf(RequestSignatureVerifier::class, $verifier);

        // Constructor with all parameters
        $verifier = new RequestSignatureVerifier('my-secret', 60);
        self::assertInstanceOf(RequestSignatureVerifier::class, $verifier);
    }

    public function testSetSecret(): void
    {
        $verifier = new RequestSignatureVerifier();
        $result   = $verifier->setSecret('new-secret');

        // Returns self for chaining
        self::assertSame($verifier, $result);

        // A valid request signed with 'new-secret' must now pass
        $postData     = ['field' => 'value'];
        $signedFields = ['field'];
        $headers      = $this->makeHeaders('new-secret', $signedFields, $postData);
        $verifier->verify($postData, $headers, $signedFields);
        self::assertTrue(true);
    }

    public function testSetToleranceSeconds(): void
    {
        $verifier = new RequestSignatureVerifier('secret');
        $result   = $verifier->setToleranceSeconds(10);

        // Returns self for chaining
        self::assertSame($verifier, $result);

        // A 20-second-old request must be rejected with the new tight tolerance
        $oldTimestamp = (string) (time() - 20);
        $postData     = ['field' => 'value'];
        $signedFields = ['field'];
        $headers      = $this->makeHeaders('secret', $signedFields, $postData, $oldTimestamp);
        self::assertFalse($verifier->verify($postData, $headers, $signedFields));
    }

    // ========================================
    // Verification Methods Tests
    // ========================================

    public function testVerify(): void
    {
        $secret       = 'super-secret-key';
        $postData     = ['action' => 'submit', 'user_id' => '42', 'payload' => 'hello'];
        $signedFields = ['action', 'payload', 'user_id'];
        $verifier     = new RequestSignatureVerifier($secret);

        // --- Valid request: must not throw ---
        $headers = $this->makeHeaders($secret, $signedFields, $postData);
        $verifier->verify($postData, $headers, $signedFields);
        self::assertTrue(true);

        // --- Missing X-Timestamp ---
        $headers = $this->makeHeaders($secret, $signedFields, $postData);
        unset($headers['X-Timestamp']);
        self::assertFalse($verifier->verify($postData, $headers, $signedFields));

        // --- Missing X-Nonce ---
        $headers = $this->makeHeaders($secret, $signedFields, $postData);
        unset($headers['X-Nonce']);
        self::assertFalse($verifier->verify($postData, $headers, $signedFields));

        // --- Missing X-Signature ---
        $headers = $this->makeHeaders($secret, $signedFields, $postData);
        unset($headers['X-Signature']);
        self::assertFalse($verifier->verify($postData, $headers, $signedFields));

        // --- Timestamp too old (> default tolerance) ---
        $expiredTimestamp = (string) (time() - RequestSignatureVerifier::DEFAULT_TIMESTAMP_TOLERANCE_SECONDS - 1);
        $headers = $this->makeHeaders($secret, $signedFields, $postData, $expiredTimestamp);
        self::assertFalse($verifier->verify($postData, $headers, $signedFields));

        // --- Timestamp too far in the future (> default tolerance) ---
        $futureTimestamp = (string) (time() + RequestSignatureVerifier::DEFAULT_TIMESTAMP_TOLERANCE_SECONDS + 1);
        $headers = $this->makeHeaders($secret, $signedFields, $postData, $futureTimestamp);
        self::assertFalse($verifier->verify($postData, $headers, $signedFields));

        // --- Timestamp exactly at the tolerance boundary: must pass ---
        $boundaryTimestamp = (string) (time() - RequestSignatureVerifier::DEFAULT_TIMESTAMP_TOLERANCE_SECONDS);
        $headers = $this->makeHeaders($secret, $signedFields, $postData, $boundaryTimestamp);
        $verifier->verify($postData, $headers, $signedFields);
        self::assertTrue(true);

        // --- Custom tight tolerance: 20-second-old request is rejected ---
        $tightVerifier    = new RequestSignatureVerifier($secret, 5);
        $slightlyOldTimestamp = (string) (time() - 20);
        $headers = $this->makeHeaders($secret, $signedFields, $postData, $slightlyOldTimestamp);
        self::assertFalse($tightVerifier->verify($postData, $headers, $signedFields));

        // --- Custom wide tolerance: 20-second-old request is accepted ---
        $wideVerifier = new RequestSignatureVerifier($secret, 60);
        $headers      = $this->makeHeaders($secret, $signedFields, $postData, $slightlyOldTimestamp);
        $wideVerifier->verify($postData, $headers, $signedFields);
        self::assertTrue(true);

        // --- Wrong secret: signature mismatch ---
        $headers = $this->makeHeaders('correct-secret', $signedFields, $postData);
        self::assertFalse((new RequestSignatureVerifier('wrong-secret'))->verify($postData, $headers, $signedFields));

        // --- Tampered post data: signature mismatch ---
        $headers      = $this->makeHeaders($secret, $signedFields, $postData);
        $tamperedData = array_merge($postData, ['user_id' => '99']);
        self::assertFalse($verifier->verify($tamperedData, $headers, $signedFields));

        // --- Tampered signature ---
        $headers                = $this->makeHeaders($secret, $signedFields, $postData);
        $headers['X-Signature'] = base64_encode('definitely-not-a-valid-hmac');
        self::assertFalse($verifier->verify($postData, $headers, $signedFields));

        // --- Missing field in postData: empty string used, must still match ---
        $postDataWithMissing = ['action' => 'submit', 'user_id' => '42']; // 'payload' absent
        $headers             = $this->makeHeaders($secret, $signedFields, $postDataWithMissing);
        $verifier->verify($postDataWithMissing, $headers, $signedFields);
        self::assertTrue(true);

        // --- Empty signed fields list ---
        $headers = $this->makeHeaders($secret, [], $postData);
        $verifier->verify($postData, $headers, []);
        self::assertTrue(true);
    }
}