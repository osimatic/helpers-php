<?php

declare(strict_types=1);

namespace Tests\Network;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class HTTPClientTest extends TestCase
{
	/* ===================== setLogger() ===================== */

	public function testSetLoggerReturnsInstance(): void
	{
		$client = new HTTPClient();
		$logger = $this->createMock(LoggerInterface::class);

		$result = $client->setLogger($logger);

		$this->assertInstanceOf(HTTPClient::class, $result);
		$this->assertSame($client, $result);
	}

	public function testSetLoggerAllowsFluentInterface(): void
	{
		$client = new HTTPClient();
		$logger = $this->createMock(LoggerInterface::class);

		// Test fluent interface
		$result = $client->setLogger($logger)->setLogger($logger);

		$this->assertInstanceOf(HTTPClient::class, $result);
	}

	/* ===================== Constructor ===================== */

	public function testConstructorWithLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$client = new HTTPClient($logger);

		$this->assertInstanceOf(HTTPClient::class, $client);
	}

	public function testConstructorWithoutLogger(): void
	{
		$client = new HTTPClient();

		$this->assertInstanceOf(HTTPClient::class, $client);
	}

	/* ===================== request() - Note importante ===================== */

	/**
	 * Note: Les tests suivants sont limités car la classe HTTPClient crée
	 * directement une instance de GuzzleHttp\Client dans la méthode request(),
	 * ce qui rend difficile le mocking sans refactoring de la classe.
	 *
	 * Pour des tests complets, il faudrait :
	 * - Injecter le GuzzleHttp\Client via le constructeur
	 * - Ou utiliser un environnement de test avec un serveur HTTP mock réel
	 * - Ou refactorer la classe pour utiliser une factory pour créer le client
	 *
	 * Les tests ci-dessous vérifient principalement la logique de la classe
	 * et nécessitent une connexion réseau réelle ou un environnement de test approprié.
	 */

	/* ===================== Tests basiques de méthode HTTP ===================== */

	/**
	 * Test with httpbin.org (public HTTP testing service)
	 * This test requires an Internet connection
	 *
	 * @group integration
	 */
	public function testRequestGetWithRealHttpCall(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->request(HTTPMethod::GET, 'https://httpbin.org/get');

		$this->assertNotNull($response);
		$this->assertEquals(200, $response->getStatusCode());
	}

	/**
	 * @group integration
	 */
	public function testRequestPostWithRealHttpCall(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->request(
			HTTPMethod::POST,
			'https://httpbin.org/post',
			['key' => 'value']
		);

		$this->assertNotNull($response);
		$this->assertEquals(200, $response->getStatusCode());
	}

	/**
	 * @group integration
	 */
	public function testRequestWithQueryParameters(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->request(
			HTTPMethod::GET,
			'https://httpbin.org/get',
			['param1' => 'value1', 'param2' => 'value2']
		);

		$this->assertNotNull($response);
		$body = json_decode((string) $response->getBody(), true);
		$this->assertArrayHasKey('args', $body);
		$this->assertEquals('value1', $body['args']['param1']);
		$this->assertEquals('value2', $body['args']['param2']);
	}

	/**
	 * @group integration
	 */
	public function testRequestWithHeaders(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->request(
			HTTPMethod::GET,
			'https://httpbin.org/headers',
			[],
			['X-Custom-Header' => 'test-value']
		);

		$this->assertNotNull($response);
		$body = json_decode((string) $response->getBody(), true);
		$this->assertArrayHasKey('headers', $body);
	}

	/**
	 * @group integration
	 */
	public function testRequestWithJsonBody(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->request(
			HTTPMethod::POST,
			'https://httpbin.org/post',
			['key' => 'value', 'nested' => ['foo' => 'bar']],
			['Content-Type' => 'application/json'],
			true // jsonBody
		);

		$this->assertNotNull($response);
		$body = json_decode((string) $response->getBody(), true);
		$this->assertArrayHasKey('json', $body);
		$this->assertEquals('value', $body['json']['key']);
	}

	/**
	 * @group integration
	 */
	public function testRequestWithFormParams(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->request(
			HTTPMethod::POST,
			'https://httpbin.org/post',
			['key' => 'value'],
			[],
			false // formParams
		);

		$this->assertNotNull($response);
		$body = json_decode((string) $response->getBody(), true);
		$this->assertArrayHasKey('form', $body);
		$this->assertEquals('value', $body['form']['key']);
	}

	/* ===================== jsonRequest() ===================== */

	/**
	 * @group integration
	 */
	public function testJsonRequestReturnsArray(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$result = $client->jsonRequest(HTTPMethod::GET, 'https://httpbin.org/json');

		$this->assertIsArray($result);
	}

	/**
	 * @group integration
	 */
	public function testJsonRequestWithInvalidUrl(): void
	{
		$client = new HTTPClient();
		$result = $client->jsonRequest(HTTPMethod::GET, 'https://invalid-url-that-does-not-exist-12345.com');

		$this->assertNull($result);
	}

	/* ===================== stringRequest() ===================== */

	/**
	 * @group integration
	 */
	public function testStringRequestReturnsString(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$result = $client->stringRequest(HTTPMethod::GET, 'https://httpbin.org/html');

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	/**
	 * @group integration
	 */
	public function testStringRequestWithInvalidUrl(): void
	{
		$client = new HTTPClient();
		$result = $client->stringRequest(HTTPMethod::GET, 'https://invalid-url-that-does-not-exist-12345.com');

		$this->assertNull($result);
	}

	/* ===================== Error handling ===================== */

	public function testRequestWithInvalidUrlReturnsNull(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with($this->stringContains('Error during'));

		$client = new HTTPClient($logger);
		$result = $client->request(HTTPMethod::GET, 'invalid-url');

		$this->assertNull($result);
	}

	public function testJsonRequestLogsErrorOnInvalidJson(): void
	{
		$this->markTestSkipped('Difficult to test without refactoring the class');
	}

	/* ===================== HTTPMethod enum tests ===================== */

	public function testRequestWithDifferentHttpMethods(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();

		// Test GET
		$response = $client->request(HTTPMethod::GET, 'https://httpbin.org/get');
		$this->assertNotNull($response);

		// Test POST
		$response = $client->request(HTTPMethod::POST, 'https://httpbin.org/post');
		$this->assertNotNull($response);

		// Test PATCH
		$response = $client->request(HTTPMethod::PATCH, 'https://httpbin.org/patch');
		$this->assertNotNull($response);

		// Test DELETE
		$response = $client->request(HTTPMethod::DELETE, 'https://httpbin.org/delete');
		$this->assertNotNull($response);
	}

	/* ===================== URL building tests ===================== */

	/**
	 * @group integration
	 */
	public function testGetRequestAppendsQueryParamsToUrl(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();

		// URL without existing query params
		$response = $client->request(
			HTTPMethod::GET,
			'https://httpbin.org/get',
			['foo' => 'bar', 'baz' => 'qux']
		);

		$this->assertNotNull($response);
		$body = json_decode((string) $response->getBody(), true);
		$this->assertEquals('bar', $body['args']['foo']);
		$this->assertEquals('qux', $body['args']['baz']);
	}

	/**
	 * @group integration
	 */
	public function testGetRequestAppendsQueryParamsToUrlWithExistingParams(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();

		// URL with existing query params
		$response = $client->request(
			HTTPMethod::GET,
			'https://httpbin.org/get?existing=param',
			['new' => 'value']
		);

		$this->assertNotNull($response);
		$body = json_decode((string) $response->getBody(), true);
		$this->assertEquals('param', $body['args']['existing']);
		$this->assertEquals('value', $body['args']['new']);
	}

	/* ===================== Options tests ===================== */

	/**
	 * @group integration
	 */
	public function testRequestWithCustomOptions(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->request(
			HTTPMethod::GET,
			'https://httpbin.org/delay/1',
			[],
			[],
			false,
			['timeout' => 5]
		);

		$this->assertNotNull($response);
	}
}
