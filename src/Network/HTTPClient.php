<?php

namespace Osimatic\Network;

use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class HTTPClient
 * HTTP client wrapper using Guzzle for making HTTP requests
 */
class HTTPClient
{
	/**
	 * @param LoggerInterface $logger
	 */
	public function __construct(
		private LoggerInterface $logger=new NullLogger(),
	) {}

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
	 * Executes an HTTP request
	 * @param HTTPMethod $method HTTP method to use
	 * @param string $url target URL
	 * @param array $queryData query parameters (for GET) or body data (for other methods)
	 * @param array $headers HTTP headers
	 * @param bool $jsonBody if true, send body as JSON; if false, send as form data (for non-GET requests)
	 * @param array $options additional Guzzle options
	 * @return ResponseInterface|null the HTTP response, null if request failed
	 */
	public function request(HTTPMethod $method, string $url, array $queryData = [], array $headers = [], bool $jsonBody = false, array $options = []): ?ResponseInterface
	{
		$client = new \GuzzleHttp\Client();
		try {
			$options = array_merge($options, [
				'http_errors' => false,
				'headers' => $headers
			]);

			if (HTTPMethod::GET === $method) {
				if (!empty($queryData)) {
					$url .= (!str_contains($url, '?') ? '?' : '&').http_build_query($queryData);
				}
			}
			else {
				if ($jsonBody) {
					$options['json'] = $queryData;
				} else {
					$options['form_params'] = $queryData;
				}
			}

			return $client->request($method->value, $url, $options);
		}
		catch (\Exception | GuzzleException $e) {
			$this->logger->error('Error during '.$method->value.' request to URL '.$url.'. Error message: '.$e->getMessage());
		}
		return null;
	}

	/**
	 * Executes an HTTP request and returns the JSON-decoded response
	 * @param HTTPMethod $method HTTP method to use
	 * @param string $url target URL
	 * @param array $queryData query parameters (for GET) or body data (for other methods)
	 * @param array $headers HTTP headers
	 * @param bool $jsonBody if true, send body as JSON; if false, send as form data (for non-GET requests)
	 * @param array $options additional Guzzle options
	 * @return mixed|null the JSON-decoded response, null if request failed or decoding failed
	 */
	public function jsonRequest(HTTPMethod $method, string $url, array $queryData = [], array $headers = [], bool $jsonBody = false, array $options = []): mixed
	{
		if (null === ($res = $this->request($method, $url, $queryData, $headers, $jsonBody, $options))) {
			return null;
		}

		try {
			return \GuzzleHttp\Utils::jsonDecode((string) $res->getBody(), true);
		}
		catch (\Exception $e) {
			$this->logger->error('Error during result decoding. Error: '.$e->getMessage());
		}
		return null;
	}

	/**
	 * Executes an HTTP request and returns the response body as a string
	 * @param HTTPMethod $method HTTP method to use
	 * @param string $url target URL
	 * @param array $queryData query parameters (for GET) or body data (for other methods)
	 * @param array $headers HTTP headers
	 * @param bool $jsonBody if true, send body as JSON; if false, send as form data (for non-GET requests)
	 * @param array $options additional Guzzle options
	 * @return string|null the response body as string, null if request failed
	 */
	public function stringRequest(HTTPMethod $method, string $url, array $queryData = [], array $headers = [], bool $jsonBody = false, array $options = []): ?string
	{
		if (null === ($res = $this->request($method, $url, $queryData, $headers, $jsonBody, $options))) {
			return null;
		}

		return (string) $res->getBody();
	}

}