<?php

namespace Osimatic\Bank;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Revolut
{
	private const string URL_SANDBOX_PAYMENT = 'https://sandbox-merchant.revolut.com/api/1.0/orders';
	private const string URL_PROD_PAYMENT = 'https://merchant.revolut.com/api/1.0/orders';

	private const string CAPTURE_MODE_MANUAL = 'MANUAL'; //Autorisation seule
	private const string CAPTURE_MODE_AUTO = 'AUTOMATIC'; //Autorisation & Débit

	private bool $isTest = false;
	private int $amount = 0;
	private string $currency = 'EUR';
	private BankCardOperation $bankCardOperation = BankCardOperation::AUTHORIZATION_AND_DEBIT;
	private ?string $purchaseReference = null;

	private HTTPClient $httpClient;

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
	 * @param LoggerInterface $logger The logger instance
	 * @return self Returns this instance for method chaining
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * @param string $publicKey
	 * @return self
	 */
	public function setPublicKey(string $publicKey): self
	{
		$this->publicKey = $publicKey;

		return $this;
	}

	/**
	 * @param string $secretKey
	 * @return self
	 */
	public function setSecretKey(string $secretKey): self
	{
		$this->secretKey = $secretKey;

		return $this;
	}

	/**
	 * @param bool $isTest
	 * @return self
	 */
	public function setIsTest(bool $isTest): self
	{
		$this->isTest = $isTest;

		return $this;
	}

	/**
	 * @param int $amount
	 * @return self
	 */
	public function setAmount(int $amount): self
	{
		$this->amount = $amount;

		return $this;
	}

	/**
	 * @param string $purchaseReference
	 * @return self
	 */
	public function setPurchaseReference(string $purchaseReference): self
	{
		$this->purchaseReference = $purchaseReference;

		return $this;
	}

	/**
	 * @param BankCardOperation $bankCardOperation
	 * @return self
	 */
	public function setBankCardOperation(BankCardOperation $bankCardOperation): self
	{
		$this->bankCardOperation = $bankCardOperation;

		return $this;
	}


	/**
	 * @return RevolutResponse|null
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
	 * Débit final d'une commande dont la capture est définie sur CAPTURE_MODE_MANUAL (autorisation seule)
	 * @param string $orderId
	 * @return RevolutResponse|null
	 */
	public function capture(string $orderId): ?RevolutResponse
	{
		if (empty($orderId)) {
			return null;
		}

		return $this->doRequest('/' . $orderId . '/capture', ['amount' => $this->amount], HTTPMethod::POST);
	}

	/**
	 * @param string $orderId
	 * @return RevolutResponse|null
	 */
	public function getOrder(string $orderId): ?RevolutResponse
	{
		if (empty($orderId)) {
			return null;
		}

		return $this->doRequest('/' . $orderId, [], HTTPMethod::GET);
	}

	/**
	 * @param string $requestUrl
	 * @param array $payload
	 * @param HTTPMethod $httpMethod
	 * @return RevolutResponse|null
	 */
	private function doRequest(string $requestUrl, array $payload, HTTPMethod $httpMethod): ?RevolutResponse
	{
		$url = ($this->isTest ? self::URL_SANDBOX_PAYMENT : self::URL_PROD_PAYMENT).$requestUrl;

		// Log
		$this->logger?->info('URL : ' . $url);
		$this->logger?->info('Payload : ' . json_encode($payload));

		// Appel de l'URL Revolut via GET (get order) ou POST (authorize / pay order)
		$res = $this->httpClient->request($httpMethod, $url, queryData: $payload, headers: ['Authorization' => 'Bearer ' . $this->secretKey], jsonBody: HTTPMethod::POST === $httpMethod);

		if (null === $res) {
			$this->logger?->error('Appel Revolut échoué');
			return null;
		}

		$statusCode = $res->getStatusCode();

		if (401 === $statusCode) {
			$this->logger?->error('Appel Revolut échoué : auth token incorrect ou expiré');
			return null;
		}

		if (400 !== $statusCode && 201 !== $statusCode && 200 !== $statusCode) {
			$this->logger?->error('Appel Revolut échoué : erreur inconnue');
			return null;
		}

		if (400 === $statusCode) {
			$this->logger?->error('Appel Revolut échoué : format paramètres incorrect');
		}

		$res = (string)$res->getBody();
		$this->logger?->info('Résultat appel Revolut : ' . $res);

		return RevolutResponse::getFromRequest(json_decode($res, true));
	}

	/**
	 * Authorisation seule (CAPTURE_MODE_MANUAL) OU débit immédiat (CAPTURE_MODE_AUTO)
	 * @return string
	 */
	private function getCaptureMode(): string
	{
		if (BankCardOperation::AUTHORIZATION_ONLY === $this->bankCardOperation) {
			return self::CAPTURE_MODE_MANUAL;
		}
		return self::CAPTURE_MODE_AUTO;
	}
}