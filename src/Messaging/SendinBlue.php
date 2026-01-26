<?php

namespace Osimatic\Messaging;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use Osimatic\Network\HTTPRequestExecutor;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * SendinBlue (Brevo) API client for managing contacts and lists.
 * This class provides methods to interact with the SendinBlue (now Brevo) email marketing platform API.
 */
class SendinBlue
{
	public const string API_URL = 'https://api.sendinblue.com/v3/';

	/** HTTP request executor for making API calls */
	private HTTPRequestExecutor $requestExecutor;

	/**
	 * Construct a new SendinBlue client instance.
	 * @param string|null $apiKey The SendinBlue API key for authentication
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 * @param ClientInterface $httpClient The PSR-18 HTTP client instance used for making API requests (default: HTTPClient)
	 */
	public function __construct(
		private ?string $apiKey = null,
		private readonly LoggerInterface $logger=new NullLogger(),
		ClientInterface $httpClient = new HTTPClient(),
	) {
		$this->requestExecutor = new HTTPRequestExecutor($httpClient, $logger);
	}

	/**
	 * Set the SendinBlue API key.
	 * @param string $apiKey The API key for authentication
	 * @return self Returns this instance for method chaining
	 */
	public function setApiKey(string $apiKey): self
	{
		$this->apiKey = $apiKey;

		return $this;
	}

	/**
	 * Create or update a contact in a SendinBlue contact list.
	 * @param int $contactListId The ID of the contact list
	 * @param string $email The contact's email address
	 * @param array $attributes Custom contact attributes (e.g., ['FNAME' => 'John', 'LNAME' => 'Doe', 'COMPANY' => 'Company', 'SMS' => '+33612345678'])
	 * @return bool True if the contact was created/updated successfully, false otherwise
	 */
	public function createContact(int $contactListId, string $email, array $attributes = []): bool
	{
		$url = self::API_URL.'contacts';
		$data = [
			'email' => $email,
			'listIds' => [$contactListId],
			'attributes' => $attributes
		];

		if (null === ($response = $this->sendRequest($url, $data, HTTPMethod::POST))) {
			return false;
		}
		return 200 === $response->getStatusCode();
	}

	/**
	 * Executes an HTTP request to the SendinBlue (Brevo) API with authentication credentials.
	 * This method handles the common request logic for all API calls, including authentication and JSON response parsing.
	 * @param string $url The complete API endpoint URL to request
	 * @param array $queryData The request payload data
	 * @param HTTPMethod $httpMethod HTTP method to use
	 * @return ResponseInterface|null The decoded JSON response as an associative array if successful, null on failure
	 */
	private function sendRequest(string $url, array $queryData=[], HTTPMethod $httpMethod=HTTPMethod::GET): ?ResponseInterface
	{
		if (empty($this->apiKey)) {
			$this->logger->error('SendinBlue API key is not configured. Please set apiKey.');
			return null;
		}

		$headers = [
			'api-key' => $this->apiKey,
			'accept' => 'application/json',
		];

		return $this->requestExecutor->send($httpMethod, $url, $queryData, $headers, jsonBody: true);
	}
}