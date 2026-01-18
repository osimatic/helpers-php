<?php

namespace Osimatic\Bank;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Response;

/**
 * Revolut payment gateway integration class
 * Handles credit card payments via the Revolut Merchant API
 * Supports authorization, capture, and order retrieval operations
 * API Reference: https://developer.revolut.com/docs/merchant-api
 */
class Revolut
{
	/**
	 * Revolut sandbox API endpoint for testing environment
	 */
	private const string URL_SANDBOX_PAYMENT = 'https://sandbox-merchant.revolut.com/api/1.0/orders';

	/**
	 * Revolut production API endpoint for live transactions
	 */
	private const string URL_PROD_PAYMENT = 'https://merchant.revolut.com/api/1.0/orders';

	/**
	 * Manual capture mode - authorization only, requires separate capture call
	 */
	private const string CAPTURE_MODE_MANUAL = 'MANUAL';

	/**
	 * Automatic capture mode - authorization and immediate debit
	 */
	private const string CAPTURE_MODE_AUTO = 'AUTOMATIC';

	/**
	 * Test mode flag
	 * When true, uses Revolut sandbox environment for development/testing
	 * @var bool
	 */
	private bool $isTest = false;

	/**
	 * Transaction amount in minor currency units (cents)
	 * For example: 1000 = 10.00 EUR
	 * @var int
	 */
	private int $amount = 0;

	/**
	 * Currency code (3 alphabetic characters ISO 4217)
	 * Default: EUR
	 * @var string
	 */
	private string $currency = 'EUR';

	/**
	 * Bank card operation type
	 * Determines whether to authorize only or authorize and immediately debit
	 * @var BankCardOperation
	 */
	private BankCardOperation $bankCardOperation = BankCardOperation::AUTHORIZATION_AND_DEBIT;

	/**
	 * Merchant's unique purchase/order reference
	 * Used to track the order in the merchant's system
	 * @var string|null
	 */
	private ?string $purchaseReference = null;

	/**
	 * HTTP client for API communication
	 * Handles HTTP requests to the Revolut API
	 * @var HTTPClient
	 */
	private HTTPClient $httpClient;

	/**
	 * Initialize Revolut payment gateway integration
	 * Configures API credentials and HTTP client for Revolut Merchant API communication
	 * @param string $publicKey The Revolut public API key for merchant identification
	 * @param string $secretKey The Revolut secret API key used as Bearer token for API authentication
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 */
	public function __construct(
		private string          $publicKey,
		private string          $secretKey,
		private LoggerInterface $logger = new NullLogger(),
	)
	{
		$this->httpClient = new HTTPClient($logger);
	}

	/**
	 * Sets the logger for error and debugging information.
	 * @param LoggerInterface $logger The PSR-3 logger instance
	 * @return self Returns this instance for method chaining
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * Set the Revolut public API key
	 * The public key provided by Revolut for merchant identification
	 * @param string $publicKey The Revolut public API key
	 * @return self Returns this instance for method chaining
	 */
	public function setPublicKey(string $publicKey): self
	{
		$this->publicKey = $publicKey;

		return $this;
	}

	/**
	 * Set the Revolut secret API key
	 * The secret key provided by Revolut for API authentication
	 * Used as Bearer token for API requests
	 * @param string $secretKey The Revolut secret API key
	 * @return self Returns this instance for method chaining
	 */
	public function setSecretKey(string $secretKey): self
	{
		$this->secretKey = $secretKey;

		return $this;
	}

	/**
	 * Enable or disable test mode
	 * In test mode, uses Revolut sandbox environment for development/testing
	 * @param bool $isTest True to enable test mode, false for production
	 * @return self Returns this instance for method chaining
	 */
	public function setIsTest(bool $isTest): self
	{
		$this->isTest = $isTest;

		return $this;
	}

	/**
	 * Set the transaction amount
	 * Amount must be specified in minor currency units (cents)
	 * For example: 1000 = 10.00 EUR
	 * @param int $amount The amount in minor currency units
	 * @return self Returns this instance for method chaining
	 */
	public function setAmount(int $amount): self
	{
		$this->amount = $amount;

		return $this;
	}

	/**
	 * Set the merchant's unique purchase/order reference
	 * Used to track and identify the order in the merchant's system
	 * Sent to Revolut as merchant_order_ext_ref parameter
	 * @param string $purchaseReference The unique order reference
	 * @return self Returns this instance for method chaining
	 */
	public function setPurchaseReference(string $purchaseReference): self
	{
		$this->purchaseReference = $purchaseReference;

		return $this;
	}

	/**
	 * Set the bank card operation type
	 * Determines the payment flow: authorization only or authorization with immediate debit
	 * AUTHORIZATION_ONLY requires a separate capture() call to finalize the payment
	 * AUTHORIZATION_AND_DEBIT captures funds immediately
	 * @param BankCardOperation $bankCardOperation The bank card operation type
	 * @return self Returns this instance for method chaining
	 */
	public function setBankCardOperation(BankCardOperation $bankCardOperation): self
	{
		$this->bankCardOperation = $bankCardOperation;

		return $this;
	}


	/**
	 * Create a new payment order
	 * Initiates a new payment with Revolut using the configured amount and capture mode
	 * @return RevolutResponse|null The response from Revolut API, or null on failure
	 */
	public function newPayment(): ?RevolutResponse
	{
		$payload = [
			'amount' => $this->amount,
			'currency' => $this->currency,
			'capture_mode' => $this->getCaptureMode(),
			'merchant_order_ext_ref' => $this->purchaseReference,
		];

		return $this->doRequest('', $payload, HTTPMethod::POST);
	}

	/**
	 * Capture (finalize) an order whose capture mode is set to CAPTURE_MODE_MANUAL (authorization only)
	 * Use this to capture funds after a successful authorization-only payment
	 * @param string $orderId The Revolut order ID to capture
	 * @return RevolutResponse|null The response from Revolut API, or null on failure
	 */
	public function capture(string $orderId): ?RevolutResponse
	{
		if (empty($orderId)) {
			$this->logger?->error('Revolut capture failed: orderId cannot be empty');
			return null;
		}

		return $this->doRequest('/' . $orderId . '/capture', ['amount' => $this->amount], HTTPMethod::POST);
	}

	/**
	 * Retrieve order details from Revolut
	 * @param string $orderId The Revolut order ID to retrieve
	 * @return RevolutResponse|null The response from Revolut API, or null on failure
	 */
	public function getOrder(string $orderId): ?RevolutResponse
	{
		if (empty($orderId)) {
			$this->logger?->error('Revolut getOrder failed: orderId cannot be empty');
			return null;
		}

		return $this->doRequest('/' . $orderId, [], HTTPMethod::GET);
	}

	/**
	 * Execute HTTP request to Revolut API
	 * Handles authentication, error logging, and response parsing
	 * @param string $requestUrl URL path to append to the base API URL
	 * @param array $payload Request payload data
	 * @param HTTPMethod $httpMethod HTTP method (GET or POST)
	 * @return RevolutResponse|null Parsed response or null on failure
	 */
	private function doRequest(string $requestUrl, array $payload, HTTPMethod $httpMethod): ?RevolutResponse
	{
		$url = ($this->isTest ? self::URL_SANDBOX_PAYMENT : self::URL_PROD_PAYMENT).$requestUrl;

		// Log
		$this->logger?->info('URL : ' . $url);
		$this->logger?->info('Payload : ' . json_encode($payload, JSON_THROW_ON_ERROR));

		// Call Revolut URL via GET (get order) or POST (authorize / pay order)
		$res = $this->httpClient->request($httpMethod, $url, queryData: $payload, headers: ['Authorization' => 'Bearer ' . $this->secretKey], jsonBody: HTTPMethod::POST === $httpMethod);

		if (null === $res) {
			$this->logger?->error('Revolut API call failed');
			return null;
		}

		$statusCode = $res->getStatusCode();

		if (Response::HTTP_UNAUTHORIZED === $statusCode) {
			$this->logger?->error('Revolut API call failed: auth token incorrect or expired');
			return null;
		}

		if (Response::HTTP_BAD_REQUEST === $statusCode) {
			$this->logger?->error('Revolut API call failed: incorrect parameter format');
			return null;
		}

		if (!in_array($statusCode, [Response::HTTP_OK, Response::HTTP_CREATED], true)) {
			$this->logger?->error('Revolut API call failed: unknown error (HTTP ' . $statusCode . ')');
			return null;
		}

		$responseBody = (string)$res->getBody();
		$this->logger?->info('Revolut API response: ' . $responseBody);

		try {
			$responseData = json_decode($responseBody, true, 512, JSON_THROW_ON_ERROR);
			return RevolutResponse::getFromRequest($responseData);
		} catch (\JsonException $e) {
			$this->logger?->error('Revolut API response parsing failed: ' . $e->getMessage());
			return null;
		}
	}

	/**
	 * Determine capture mode based on bank card operation
	 * @return string CAPTURE_MODE_MANUAL (authorization only) or CAPTURE_MODE_AUTO (immediate debit)
	 */
	private function getCaptureMode(): string
	{
		if (BankCardOperation::AUTHORIZATION_ONLY === $this->bankCardOperation) {
			return self::CAPTURE_MODE_MANUAL;
		}
		return self::CAPTURE_MODE_AUTO;
	}
}