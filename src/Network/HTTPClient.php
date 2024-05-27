<?php

namespace Osimatic\Network;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class HTTPClient
{
	public function __construct(
		private LoggerInterface $logger=new NullLogger(),
	) {}

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
	 * @param HTTPMethod $method
	 * @param string $url
	 * @param array $queryData
	 * @param array $headers
	 * @param bool $jsonBody
	 * @return ResponseInterface|null
	 */
	public function request(HTTPMethod $method, string $url, array $queryData = [], array $headers = [], bool $jsonBody = false): ?ResponseInterface
	{
		$client = new \GuzzleHttp\Client();
		try {
			$options = [
				'http_errors' => false,
				'headers' => $headers
			];

			if (HTTPMethod::GET === $method) {
				if (!empty($queryData)) {
					$url .= (!str_contains($url, '?') ? '?' : '').http_build_query($queryData);
				}
			}
			else {
				if (true === $jsonBody) {
					$options['json'] = $queryData;
				} else {
					$options['form_params'] = $queryData;
				}
			}

			return $client->request($method->value, $url, $options);
		}
		catch (\Exception | GuzzleException $e) {
			$this->logger->error('Erreur pendant la requête '.($method->value).' vers l\'URL '.$url.'. Message d\'erreur : '.$e->getMessage());
		}
		return null;
	}

	/**
	 * @param HTTPMethod $method
	 * @param string $url
	 * @param array $queryData
	 * @param array $headers
	 * @param bool $jsonBody
	 * @return mixed|null
	 */
	public function jsonRequest(HTTPMethod $method, string $url, array $queryData = [], array $headers = [], bool $jsonBody = false): mixed
	{
		if (null === ($res = self::request($method, $url, $queryData, $headers, $jsonBody))) {
			return null;
		}

		try {
			return \GuzzleHttp\Utils::jsonDecode((string) $res->getBody(), true);
		}
		catch (\Exception $e) {
			$this->logger->error('Erreur pendant le décodage du résultat. Erreur : '.$e->getMessage());
		}
		return null;
	}

	/**
	 * @param HTTPMethod $method
	 * @param string $url
	 * @param array $queryData
	 * @param array $headers
	 * @param bool $jsonBody
	 * @return string|null
	 */
	public function stringRequest(HTTPMethod $method, string $url, array $queryData = [], array $headers = [], bool $jsonBody = false): ?string
	{
		if (null === ($res = self::request($method, $url, $queryData, $headers, $jsonBody))) {
			return null;
		}

		return (string) $res->getBody();
	}

}