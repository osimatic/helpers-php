<?php

namespace Osimatic\Person;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use Osimatic\Network\HTTPRequestExecutor;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class GoogleContact
 * Provides functionality to retrieve and parse contacts from Google Contacts API.
 * This class handles OAuth authentication, API requests, and contact data normalization.
 */
class GoogleContact
{
	private \Google_Client $client;

	/** HTTP request executor for making API calls */
	private HTTPRequestExecutor $requestExecutor;

	/**
	 * GoogleContact constructor.
	 * Initializes the Google API client with contacts scope and online access type.
	 * @param string|null $clientId The OAuth 2.0 client ID
	 * @param string|null $secret The OAuth 2.0 client secret
	 * @param string|null $appName The application name to identify requests
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 * @param ClientInterface $httpClient The PSR-18 HTTP client instance used for making API requests (default: HTTPClient)
	 */
	public function __construct(
		?string $clientId=null,
		?string $secret=null,
		?string $appName=null,
		private readonly LoggerInterface $logger=new NullLogger(),
		ClientInterface $httpClient = new HTTPClient(),
	)
	{
		$this->requestExecutor = new HTTPRequestExecutor($httpClient, $logger);

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
	 * Sets the OAuth 2.0 client ID.
	 * @param string $clientId The client ID from Google Cloud Console
	 * @return self Returns this instance for method chaining
	 */
	public function setClientId(string $clientId): self
	{
		$this->client->setClientId($clientId);

		return $this;
	}

	/**
	 * Sets the OAuth 2.0 client secret.
	 * @param string $secret The client secret from Google Cloud Console
	 * @return self Returns this instance for method chaining
	 */
	public function setSecret(string $secret): self
	{
		$this->client->setClientSecret($secret);

		return $this;
	}

	/**
	 * Sets the application name to identify API requests.
	 * @param string $appName The application name
	 * @return self Returns this instance for method chaining
	 */
	public function setApplicationName(string $appName): self
	{
		$this->client->setApplicationName($appName);

		return $this;
	}

	/**
	 * Sets the OAuth redirect URI where users will be redirected after authorization.
	 * @param string $uri The redirect URI (must match the one configured in Google Cloud Console)
	 * @return self Returns this instance for method chaining
	 */
	public function setRedirectUri(string $uri): self
	{
		$this->client->setRedirectUri($uri);

		return $this;
	}

	/**
	 * Gets the OAuth authorization URL.
	 * Redirect users to this URL to begin the OAuth flow.
	 * @return string The authorization URL
	 */
	public function getUrl(): string
	{
		return $this->client->createAuthUrl();
	}

	/**
	 * Retrieves and parses contacts from Google Contacts API.
	 * Fetches up to 150 contacts and normalizes the data into a standardized format.
	 * @param string|null $code The OAuth authorization code received after user authorization
	 * @param bool $simplified Whether to return simplified contact data (single email/phone) or detailed data (default: true)
	 * @return array|null Array of contacts or null on failure
	 */
	public function getContacts(?string $code, bool $simplified=true): ?array
	{
		if (empty($accessToken = $this->getAccessToken($code))) {
			return null;
		}

		$url = 'https://www.google.com/m8/feeds/contacts/default/full';
		$queryData = [
			'max-results' => 150,
			'alt' => 'json',
			'v' => '3.0',
			'oauth_token' => $accessToken,
		];

		if (null === ($dataList = $this->requestExecutor->execute(HTTPMethod::GET, $url, $queryData, decodeJson: true))) {
			return null;
		}

		$googleContacts = $dataList['feed']['entry'];
		//dump($googleContacts);

		$contactsList = [];
		foreach ($googleContacts as $data) {
			$firstName = $data['gd$name']['gd$givenName']['$t'] ?? null;
			$lastName = $data['gd$name']['gd$familyName']['$t'] ?? null;
			$fullName = $data['gd$name']['gd$fullName']['$t'] ?? null;
			if (empty($firstName) && !empty($fullName) && $lastName !== $fullName) {
				//dump('$firstName empty', $fullName);
				$firstName = substr($fullName, 0, strpos($fullName, ' '));
			}
			if (empty($lastName) && !empty($fullName) && $firstName !== $fullName) {
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
	 * Parses a phone number from Google Contacts data.
	 * Extracts the phone number from the URI field and removes formatting characters.
	 * @param array $data The phone number data array from Google Contacts API
	 * @return string|null The parsed phone number or null if not found
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
	 * Extracts the relationship type from Google Contacts data.
	 * Parses the 'rel' field to determine if the data is for 'home', 'work', 'mobile', 'other', etc.
	 * @param array $data The data array containing a 'rel' field
	 * @return string|null The relationship type (e.g., 'home', 'work', 'mobile') or null if not found
	 */
	public static function getDataRelType(array $data): ?string
	{
		if (empty($relUrl = ($data['rel'] ?? null))) {
			return null;
		}
		return substr($relUrl, strpos($relUrl, '#')+1);
	}

	/**
	 * Exchanges the OAuth authorization code for an access token.
	 * This is a private method used internally to authenticate API requests.
	 * @param string|null $code The OAuth authorization code
	 * @return string|null The access token or null on failure
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
		catch (\InvalidArgumentException $e) {
			$this->logger->error($e->getMessage());
			return null;
		}

		//var_dump($accessTokenData);
		return $accessTokenData['access_token'] ?? null;
	}
}