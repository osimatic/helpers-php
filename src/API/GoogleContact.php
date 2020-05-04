<?php

namespace Osimatic\Helpers\API;

/**
 * Class GoogleContact
 * @package Osimatic\Helpers\API
 */
class GoogleContact
{
	private $client;

	/**
	 * GoogleContact constructor.
	 * @param string|null $clientId
	 * @param string|null $secret
	 * @param string|null $appName
	 */
	public function __construct(?string $clientId=null, ?string $secret=null, ?string $appName=null)
	{
		$this->client = new \Google_Client();
		$this->client->setApplicationName('');
		$this->client->setScopes('https://www.googleapis.com/auth/contacts');
		$this->client->setAccessType('online');

		if (!empty($clientId)) {
			$this->setClientId($clientId);
		}
		if (!empty($secret)) {
			$this->setSecret($secret);
		}
		if (!empty($appName)) {
			$this->setApplicationName($appName);
		}
	}

	/**
	 * @param string $clientId
	 * @return self
	 */
	public function setClientId(string $clientId): self
	{
		$this->client->setClientId($clientId);

		return $this;
	}

	/**
	 * @param string $secret
	 * @return self
	 */
	public function setSecret(string $secret): self
	{
		$this->client->setClientSecret($secret);

		return $this;
	}

	/**
	 * @param string $appName
	 * @return self
	 */
	public function setApplicationName(string $appName): self
	{
		$this->client->setApplicationName($appName);

		return $this;
	}

	/**
	 * @param string $uri
	 * @return self
	 */
	public function setRedirectUri(string $uri): self
	{
		$this->client->setRedirectUri($uri);

		return $this;
	}

	/**
	 * @return string
	 */
	public function getUrl(): string
	{
		return $this->client->createAuthUrl();
	}

	/**
	 * @param string|null $code
	 * @param bool $simplified
	 * @return array|null
	 */
	public function getContacts(?string $code, bool $simplified=true): ?array
	{
		if (empty($accessToken = $this->getAccessToken($code))) {
			return null;
		}

		$clientHTTP = new \GuzzleHttp\Client();
		try {
			$res = $clientHTTP->request('GET', 'https://www.google.com/m8/feeds/contacts/default/full?max-results=150&alt=json&v=3.0&oauth_token='.$accessToken);
		} catch (\GuzzleHttp\Exception\GuzzleException $e) {
			//var_dump($e->getMessage());
			return null;
		}

		$dataList = \GuzzleHttp\json_decode((string) $res->getBody(), true);
		//dump($dataList);

		$googleContacts = $dataList['feed']['entry'];
		//dump($googleContacts);

		$contactsList = [];
		foreach ($googleContacts as $data) {
			$firstName = $data['gd$name']['gd$givenName']['$t'] ?? null;
			$lastName = $data['gd$name']['gd$familyName']['$t'] ?? null;
			$fullName = $data['gd$name']['gd$fullName']['$t'] ?? null;
			if (empty($firstName) && !empty($fullName) && $lastName != $fullName) {
				//dump('$firstName empty', $fullName);
				$firstName = substr($fullName, 0, strpos($fullName, ' '));
			}
			if (empty($lastName) && !empty($fullName) && $firstName != $fullName) {
				//dump('$lastName empty', $fullName);
				$lastName = substr($fullName, strpos($fullName, ' '));
			}

			$homeEmail = null;
			$workEmail = null;
			$otherEmail = null;
			foreach ($data['gd$email'] ?? [] as $emailData) {
				switch (self::getDataRelType($emailData)) {
					case 'home': $homeEmail = $emailData['address'] ?? null; break;
					case 'work': $workEmail = $emailData['address'] ?? null; break;
					case 'other': $otherEmail = $emailData['address'] ?? null; break;
				}
			}

			$homeNumber = null;
			$mobileNumber = null;
			$workNumber = null;
			$otherNumber = null;
			foreach ($data['gd$phoneNumber'] ?? [] as $numberData) {
				//var_dump(self::getDataRelType($numberData));
				switch (self::getDataRelType($numberData)) {
					case 'home': $homeNumber = self::parsePhoneNumber($numberData); break;
					case 'work': $workNumber = self::parsePhoneNumber($numberData); break;
					case 'mobile': $mobileNumber = self::parsePhoneNumber($numberData); break;
					case 'other': $otherNumber = self::parsePhoneNumber($numberData); break;
				}
			}

			if ($simplified) {
				$contactsList[] = [
					'first_name' => $firstName,
					'last_name' => $lastName,
					'email' => $homeEmail ?? $workEmail ?? $otherEmail,
					'home_number' => $homeNumber ?? $workNumber ?? $otherNumber,
					'mobile_number' => $mobileNumber,
				];
			}
			else {
				$contactsList[] = [
					'first_name' => $firstName,
					'last_name' => $lastName,
					'home_email' => $homeEmail,
					'work_email' => $workEmail,
					'other_email' => $otherEmail,
					'home_number' => $homeNumber,
					'mobile_number' => $mobileNumber,
					'work_number' => $workNumber,
					'other_number' => $otherNumber,
				];
			}
		}
		//dump($contactsList);
		return $contactsList;
	}

	/**
	 * @param array $data
	 * @return string|null
	 */
	public static function parsePhoneNumber(array $data): ?string
	{
		if (empty($number = ($data['uri'] ?? null))) {
			return null;
		}
		$number = substr($number, 4);
		return str_replace('-', '', $number);
	}

	/**
	 * @param array $data
	 * @return string|null
	 */
	public static function getDataRelType(array $data): ?string
	{
		if (empty($relUrl = ($data['rel'] ?? null))) {
			return null;
		}
		return substr($relUrl, strpos($relUrl, '#')+1);
	}

	/**
	 * @param string|null $code
	 * @return string|null
	 */
	private function getAccessToken(?string $code): ?string
	{
		//var_dump($code);
		if (empty($code)) {
			return null;
		}

		//https://developers.google.com/api-client-library/php/auth/web-app
		// Step 5: Exchange authorization code for refresh and access tokens
		try {
			$this->client->setAccessToken($this->client->fetchAccessTokenWithAuthCode($code));
			$accessTokenData = $this->client->getAccessToken();
		}
		catch (\InvalidArgumentException $exception) {
			//var_dump($exception->getMessage());
			return null;
		}

		//var_dump($accessTokenData);
		return $accessTokenData['access_token'] ?? null;
	}

}