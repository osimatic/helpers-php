<?php

namespace Osimatic\Network;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Bitly
 * Client for the Bit.ly URL shortening service
 */
class Bitly
{
	private HTTPClient $httpClient;

	/**
	 * @param string|null $login Bit.ly login username
	 * @param string|null $key Bit.ly API key
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		private ?string $login=null,
		private ?string $key=null,
		LoggerInterface $logger=new NullLogger(),
	) {
		$this->httpClient = new HTTPClient($logger);
	}

	/**
	 * Sets the Bit.ly login username
	 * @param string $login the Bit.ly login username
	 * @return self
	 */
	public function setLogin(string $login): self
	{
		$this->login = $login;

		return $this;
	}

	/**
	 * Sets the Bit.ly API key
	 * @param string $key the Bit.ly API key
	 * @return self
	 */
	public function setKey(string $key): self
	{
		$this->key = $key;

		return $this;
	}

	/**
	 * Uses the Bit.ly API to shorten a URL
	 * @param string $url the URL to shorten
	 * @return string|null shortened URL, null if an error occurred
	 */
	public function shorten(string $url): ?string
	{
		$version = '2.0.1';

		$bitlyUrl = 'http://api.bit.ly/shorten';
		$queryData = [
			'version' => $version,
			'longUrl' => urlencode($url),
			'login' => $this->login,
			'apiKey' => $this->key,
			'format' => 'json'
		];
		if (null === ($json = $this->httpClient->jsonRequest(HTTPMethod::GET, $bitlyUrl, queryData: $queryData))) {
			return null;
		}

		return $json['results'][$url]['shortUrl'] ?? null;
	}
}