<?php

namespace Osimatic\Messaging;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * SendinBlue (Brevo) API client for managing contacts and lists.
 * This class provides methods to interact with the SendinBlue (now Brevo) email marketing platform API.
 */
class SendinBlue
{
	/**
	 * Construct a new SendinBlue client instance.
	 * @param string|null $apiKey The SendinBlue API key for authentication
	 * @param LoggerInterface $logger The PSR-3 logger instance for debugging (default: NullLogger)
	 */
	public function __construct(
		private ?string $apiKey = null,
		private LoggerInterface $logger=new NullLogger(),
	) {}

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
	 * Create or update a contact in a SendinBlue contact list.
	 * @param int $contactListId The ID of the contact list
	 * @param string $email The contact's email address
	 * @param string|null $firstName The contact's first name (optional)
	 * @param string|null $lastName The contact's last name (optional)
	 * @param string|null $companyName The contact's company name (optional)
	 * @param string|null $mobileNumber The contact's mobile phone number (optional)
	 * @return bool True if the contact was created/updated successfully, false otherwise
	 */
	public function createContact(int $contactListId, string $email, ?string $firstName=null, ?string $lastName=null, ?string $companyName=null, ?string $mobileNumber=null): bool
	{
		$url = 'https://api.sendinblue.com/v3/contacts';
		$data = [
			'email' => $email,
			'listIds' => [$contactListId],
			'attributes' => [
				'SOCIETE' => $companyName,
				'PRENOM' => $firstName,
				'NOM' => $lastName,
				'SMS' => $mobileNumber,
			]
		];
		$response = $this->post($url, $data);
		//var_dump((string)$response->getBody());
		if (null === $response) {
			return false;
		}
		return 200 === $response->getStatusCode();
	}

	/**
	 * @param string $url
	 * @param array $queryData
	 * @return null|ResponseInterface
	 */
	private function post(string $url, array $queryData=[]): ?ResponseInterface
	{
		$httpClient = new \GuzzleHttp\Client();
		try {
			$options = [
				'http_errors' => false,
				'headers' => $this->getHeaders()
			];
			$options[\GuzzleHttp\RequestOptions::JSON] = $queryData;
			$res = $httpClient->request('POST', $url, $options);
		}
		catch (\Exception | \GuzzleHttp\Exception\GuzzleException $e) {
			$this->logger->error($e->getMessage());
			return null;
		}
		return $res;
	}

	/**
	 * @return array
	 */
	private function getHeaders(): array
	{
		return [
			'api-key' => $this->apiKey,
			'content-type' => 'application/json',
			'accept' => 'application/json',
		];
	}
}