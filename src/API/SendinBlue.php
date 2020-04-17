<?php

namespace Osimatic\Helpers\API;

use Psr\Http\Message\ResponseInterface;

/**
 * Class SendinBlue
 * @package Osimatic\Helpers\API
 */
class SendinBlue
{
	private $apiKey;

	/**
	 * SendinBlue constructor.
	 * @param string|null $apiKey
	 */
	public function __construct(?string $apiKey=null)
	{
		$this->apiKey = $apiKey;
	}

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
	private function post($url, array $queryData=[]): ?ResponseInterface
	{
		$httpClient = new \GuzzleHttp\Client();
		try {
			$options = [
				'http_errors' => false,
				'headers' => $this->getHeaders()
			];
			$options[\GuzzleHttp\RequestOptions::JSON] = $queryData;
			$res = $httpClient->request('POST', $url, $options);
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			//var_dump($e->getMessage());
			return null;
		}
		return $res;
	}

	private function getHeaders(): array
	{
		return [
			'api-key' => $this->apiKey,
			'content-type' => 'application/json',
			'accept' => 'application/json',
		];
	}
}