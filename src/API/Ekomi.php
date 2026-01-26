<?php

namespace Osimatic\API;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use Osimatic\Network\HTTPRequestExecutor;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Client for interacting with the eKomi API (v3).
 * eKomi is a customer review and rating platform that allows businesses to collect, manage, and display customer feedback.
 * This class provides methods to retrieve feedback links, customer reviews, and rating statistics.
 */
class Ekomi
{
	/** Base URL for the eKomi API v3 endpoint */
	public const string API_URL = 'https://api.ekomi.de/v3/';

	/** Script version identifier used in API requests */
	public const string SCRIPT_VERSION = '1.0.0';

	/** HTTP request executor for making API calls */
	private HTTPRequestExecutor $requestExecutor;

	/**
	 * Initializes a new eKomi API client instance.
	 * @param string|null $interfaceId The eKomi interface ID for API authentication
	 * @param string|null $interfacePassword The eKomi interface password for API authentication
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 * @param ClientInterface $httpClient The PSR-18 HTTP client instance used for making API requests (default: HTTPClient)
	 */
	public function __construct(
		private ?string $interfaceId = null,
		private ?string $interfacePassword = null,
		private readonly LoggerInterface $logger = new NullLogger(),
		ClientInterface $httpClient = new HTTPClient(),
	)
	{
		$this->requestExecutor = new HTTPRequestExecutor($httpClient, $logger);
	}

	/**
	 * Sets the eKomi interface ID for API authentication.
	 * @param string $interfaceId The eKomi interface ID
	 * @return self Returns this instance for method chaining
	 */
	public function setInterfaceId(string $interfaceId): self
	{
		$this->interfaceId = $interfaceId;

		return $this;
	}

	/**
	 * Sets the eKomi interface password for API authentication.
	 * @param string $interfacePassword The eKomi interface password
	 * @return self Returns this instance for method chaining
	 */
	public function setInterfacePassword(string $interfacePassword): self
	{
		$this->interfacePassword = $interfacePassword;

		return $this;
	}

	/**
	 * Retrieves the customer feedback link for a specific order from the eKomi API.
	 * This link can be used to direct customers to leave feedback about their order.
	 * @param string|int $orderId The unique order identifier
	 * @return string|null The feedback link URL if successful, null on failure
	 */
	public function getFeedbackLink(string|int $orderId): ?string
	{
		if (empty($orderId)) {
			$this->logger->error('Order ID cannot be empty');
			return null;
		}

		if (null === ($result = $this->sendRequest(self::API_URL.'putOrder?order_id='.$orderId))) {
			return null;
		}

		return $result['link'] ?? null;
	}

	/**
	 * Retrieves a list of customer feedback entries from the eKomi API.
	 * @param string $range The time range for feedback retrieval (e.g., 'all', 'month', 'week'). Default is 'all'
	 * @return array<string, mixed>|null Array of feedback entries if successful, null on failure
	 */
	public function getListFeedback(string $range='all'): ?array
	{
		$validRanges = ['all', 'month', 'week', 'day', 'year'];
		if (!in_array($range, $validRanges, true)) {
			$this->logger->error('Invalid range parameter. Valid values are: ' . implode(', ', $validRanges));
			return null;
		}

		if (null === ($result = $this->sendRequest(self::API_URL.'getFeedback?range='.$range))) {
			return null;
		}

		return $result;
	}

	/**
	 * Retrieves the average rating and total feedback count from the eKomi API.
	 * @return array{0: float, 1: int}|null Array containing [average rating, feedback count] if successful, null on failure
	 */
	public function getAverage(): ?array
	{
		if (null === ($result = $this->sendRequest(self::API_URL.'getSnapshot?range=all'))) {
			return null;
		}

		return [$result['info']['fb_avg'], $result['info']['fb_count']];
	}

	/**
	 * Executes an HTTP request to the eKomi API with authentication credentials.
	 * This method handles the common request logic for all API calls, including authentication and JSON response parsing.
	 * @param string $url The complete API endpoint URL to request
	 * @param array $queryData The request payload data
	 * @param HTTPMethod $httpMethod HTTP method to use
	 * @return array|null The decoded JSON response as an associative array if successful, null on failure
	 */
	private function sendRequest(string $url, array $queryData=[], HTTPMethod $httpMethod=HTTPMethod::GET): ?array
	{
		if (empty($this->interfaceId) || empty($this->interfacePassword)) {
			$this->logger->error('eKomi API credentials are not configured. Please set interfaceId and interfacePassword.');
			return null;
		}

		$queryData = array_merge($queryData, [
			'auth' => $this->interfaceId.'|'.$this->interfacePassword,
			'version' => 'cust-'.self::SCRIPT_VERSION,
			'type' => 'json',
		]);

		return $this->requestExecutor->execute($httpMethod, $url, $queryData, decodeJson: true);
	}
}