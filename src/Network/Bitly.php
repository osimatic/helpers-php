<?php

namespace Osimatic\Network;

use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Bitly
 * Client for the Bit.ly URL shortening service
 */
class Bitly
{
	/** Base URL for the Bitly API endpoint */
	public const string API_URL = 'http://api.bit.ly/';

	public const string API_VERSION = '2.0.1';

	/** HTTP request executor for making API calls */
	private HTTPRequestExecutor $requestExecutor;

	/**
	 * @param string|null $login Bit.ly login username
	 * @param string|null $key Bit.ly API key
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 * @param ClientInterface $httpClient The PSR-18 HTTP client instance used for making API requests (default: HTTPClient)
	 */
	public function __construct(
		private ?string $login=null,
		private ?string $key=null,
		private readonly LoggerInterface $logger = new NullLogger(),
		ClientInterface $httpClient = new HTTPClient(),
	)
	{
		$this->requestExecutor = new HTTPRequestExecutor($httpClient, $logger);
	}

	/**
	 * Sets the Bit.ly login username
	 * @param string $login the Bit.ly login username
	 * @return self Returns this instance for method chaining
	 */
	public function setLogin(string $login): self
	{
		$this->login = $login;

		return $this;
	}

	/**
	 * Sets the Bit.ly API key
	 * @param string $key the Bit.ly API key
	 * @return self Returns this instance for method chaining
	 */
	public function setKey(string $key): self
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * Uses the Bit.ly API to shorten a URL
	 * @param string $url the long URL to be shortened
	 * @return string|null shortened URL, null if an error occurred
	 */
	public function shorten(string $url): ?string
	{
		if (null === ($result = $this->sendRequest(self::API_URL.'shorten', ['longUrl' => $url]))) {
			return null;
		}

		return $result['results'][$url]['shortUrl'] ?? null;
	}

	/**
	 * Executes an HTTP request to the Bitly API with authentication credentials.
	 * This method handles the common request logic for all API calls, including authentication and JSON response parsing.
	 * @param string $url The complete API endpoint URL to request
	 * @param array $queryData The request payload data
	 * @param HTTPMethod $httpMethod HTTP method to use
	 * @return array|null The decoded JSON response as an associative array if successful, null on failure
	 */
	private function sendRequest(string $url, array $queryData=[], HTTPMethod $httpMethod=HTTPMethod::GET): ?array
	{
		if (empty($this->login) || empty($this->key)) {
			$this->logger->error('Bitly API credentials are not configured. Please set login and key.');
			return null;
		}

		$queryData = array_merge($queryData, [
			'version' => self::API_VERSION,
			'login' => $this->login,
			'apiKey' => $this->key,
			'format' => 'json'
		]);

		return $this->requestExecutor->execute($httpMethod, $url, $queryData, decodeJson: true);
	}

}