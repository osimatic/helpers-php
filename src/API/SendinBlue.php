<?php

namespace Osimatic\API;

use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class SendinBlue
 * @package Osimatic\Helpers\API
 */
class SendinBlue
{
	public function __construct(
		private ?string $apiKey = null,
		private LoggerInterface $logger=new NullLogger(),
	) {}

	/**
	 * @param string $apiKey
	 * @return self
	 */
	public function setApiKey(string $apiKey): self
	{
		$this->apiKey = $apiKey;

		return $this;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return self
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * @param int $contactListId
	 * @param string $email
	 * @param string|null $firstName
	 * @param string|null $lastName
	 * @param string|null $companyName
	 * @param string|null $mobileNumber
	 * @return bool
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