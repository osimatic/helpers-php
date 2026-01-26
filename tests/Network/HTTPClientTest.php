<?php

declare(strict_types=1);

namespace Tests\Network;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

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

	public function testConstructorWithDefaultOptions(): void
	{
		$client = new HTTPClient(
			logger: new NullLogger(),
			defaultOptions: ['timeout' => 10, 'verify' => false]
		);

		$this->assertInstanceOf(HTTPClient::class, $client);
	}

	/* ===================== PSR-18 ClientInterface Implementation ===================== */

	public function testImplementsClientInterface(): void
	{
		$client = new HTTPClient();

		$this->assertInstanceOf(ClientInterface::class, $client);
	}

	#[Group("integration")]
	public function testSendRequestWithGetRequest(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$request = new Request('GET', 'https://httpbin.org/get');

		$response = $client->sendRequest($request);

		$this->assertNotNull($response);
		$this->assertEquals(200, $response->getStatusCode());
	}

	#[Group("integration")]
	public function testSendRequestWithPostRequest(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$request = new Request(
			'POST',
			'https://httpbin.org/post',
			['Content-Type' => 'application/json'],
			json_encode(['key' => 'value'])
		);

		$response = $client->sendRequest($request);

		$this->assertNotNull($response);
		$this->assertEquals(200, $response->getStatusCode());
	}

	public function testSendRequestThrowsExceptionOnInvalidUrl(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with($this->stringContains('Error during PSR-18 request'));

		$client = new HTTPClient($logger);
		$request = new Request('GET', 'invalid-url');

		$this->expectException(\Psr\Http\Client\ClientExceptionInterface::class);
		$client->sendRequest($request);
	}

	#[Group("integration")]
	public function testSendRequestWithCustomHeaders(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$request = new Request(
			'GET',
			'https://httpbin.org/headers',
			['X-Custom-Header' => 'test-value', 'User-Agent' => 'HTTPClient/1.0']
		);

		$response = $client->sendRequest($request);

		$this->assertNotNull($response);
		$body = json_decode((string) $response->getBody(), true);
		$this->assertArrayHasKey('headers', $body);
		$this->assertEquals('test-value', $body['headers']['X-Custom-Header']);
	}

	/* ===================== send() ===================== */

	/**
	 * Test with httpbin.org (public HTTP testing service)
	 * This test requires an Internet connection
	 */
	#[Group("integration")]
	public function testSendGetRequest(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->send(HTTPMethod::GET, 'https://httpbin.org/get');

		$this->assertNotNull($response);
		$this->assertEquals(200, $response->getStatusCode());
	}

	#[Group("integration")]
	public function testSendPostRequest(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->send(
			HTTPMethod::POST,
			'https://httpbin.org/post',
			['key' => 'value']
		);

		$this->assertNotNull($response);
		$this->assertEquals(200, $response->getStatusCode());
	}

	#[Group("integration")]
	public function testSendWithQueryParameters(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->send(
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

	#[Group("integration")]
	public function testSendWithHeaders(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->send(
			HTTPMethod::GET,
			'https://httpbin.org/headers',
			[],
			['X-Custom-Header' => 'test-value']
		);

		$this->assertNotNull($response);
		$body = json_decode((string) $response->getBody(), true);
		$this->assertArrayHasKey('headers', $body);
	}

	#[Group("integration")]
	public function testSendWithJsonBody(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->send(
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

	#[Group("integration")]
	public function testSendWithFormParams(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->send(
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

	public function testSendWithInvalidUrlReturnsNull(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with($this->stringContains('Error during'));

		$client = new HTTPClient($logger);
		$result = $client->send(HTTPMethod::GET, 'invalid-url');

		$this->assertNull($result);
	}

	/* ===================== execute() ===================== */

	#[Group("integration")]
	public function testExecuteReturnsRawStringByDefault(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$result = $client->execute(HTTPMethod::GET, 'https://httpbin.org/html');

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	#[Group("integration")]
	public function testExecuteWithDecodeJsonReturnsArray(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$result = $client->execute(HTTPMethod::GET, 'https://httpbin.org/json', decodeJson: true);

		$this->assertIsArray($result);
	}

	#[Group("integration")]
	public function testExecuteWithoutDecodeJsonReturnsString(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$result = $client->execute(HTTPMethod::GET, 'https://httpbin.org/json', decodeJson: false);

		$this->assertIsString($result);
		$this->assertJson($result);
	}

	#[Group("integration")]
	public function testExecuteWithGetRequest(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$result = $client->execute(
			HTTPMethod::GET,
			'https://httpbin.org/get',
			['param' => 'value'],
			decodeJson: true
		);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('args', $result);
		$this->assertEquals('value', $result['args']['param']);
	}

	#[Group("integration")]
	public function testExecuteWithPostJsonBody(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$result = $client->execute(
			HTTPMethod::POST,
			'https://httpbin.org/post',
			['key' => 'value'],
			[],
			true, // jsonBody
			true  // decodeJson
		);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('json', $result);
		$this->assertEquals('value', $result['json']['key']);
	}

	#[Group("integration")]
	public function testExecuteWithPostFormParams(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$result = $client->execute(
			HTTPMethod::POST,
			'https://httpbin.org/post',
			['key' => 'value'],
			[],
			false, // formParams
			true   // decodeJson
		);

		$this->assertIsArray($result);
		$this->assertArrayHasKey('form', $result);
		$this->assertEquals('value', $result['form']['key']);
	}

	public function testExecuteWithInvalidUrlReturnsNull(): void
	{
		$client = new HTTPClient();
		$result = $client->execute(HTTPMethod::GET, 'https://invalid-url-that-does-not-exist-12345.com');

		$this->assertNull($result);
	}

	public function testExecuteLogsErrorOnInvalidJson(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with($this->stringContains('Error during JSON decoding'));

		$client = new HTTPClient($logger);
		// httpbin.org/html returns HTML, not JSON
		$result = $client->execute(HTTPMethod::GET, 'https://httpbin.org/html', decodeJson: true);

		$this->assertNull($result);
	}

	/* ===================== request() - DEPRECATED ===================== */

	/**
	 * Note: Les tests suivants vérifient la rétrocompatibilité des méthodes obsolètes
	 */

	/* ===================== Tests basiques de méthode HTTP ===================== */

	/**
	 * Test with httpbin.org (public HTTP testing service)
	 * This test requires an Internet connection
	 */
	#[Group("integration")]
	public function testRequestGetWithRealHttpCall(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$response = $client->request(HTTPMethod::GET, 'https://httpbin.org/get');

		$this->assertNotNull($response);
		$this->assertEquals(200, $response->getStatusCode());
	}

	#[Group("integration")]
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

	#[Group("integration")]
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

	#[Group("integration")]
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

	#[Group("integration")]
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

	#[Group("integration")]
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

	/* ===================== jsonRequest() - DEPRECATED ===================== */

	#[Group("integration")]
	public function testJsonRequestReturnsArray(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$result = $client->jsonRequest(HTTPMethod::GET, 'https://httpbin.org/json');

		$this->assertIsArray($result);
	}

	#[Group("integration")]
	public function testJsonRequestWithInvalidUrl(): void
	{
		$client = new HTTPClient();
		$result = $client->jsonRequest(HTTPMethod::GET, 'https://invalid-url-that-does-not-exist-12345.com');

		$this->assertNull($result);
	}

	public function testJsonRequestIsAliasForExecuteWithDecodeJson(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();

		// Both methods should return the same result
		$result1 = $client->jsonRequest(HTTPMethod::GET, 'https://httpbin.org/json');
		$result2 = $client->execute(HTTPMethod::GET, 'https://httpbin.org/json', decodeJson: true);

		$this->assertEquals($result1, $result2);
	}

	/* ===================== stringRequest() - DEPRECATED ===================== */

	#[Group("integration")]
	public function testStringRequestReturnsString(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();
		$result = $client->stringRequest(HTTPMethod::GET, 'https://httpbin.org/html');

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	#[Group("integration")]
	public function testStringRequestWithInvalidUrl(): void
	{
		$client = new HTTPClient();
		$result = $client->stringRequest(HTTPMethod::GET, 'https://invalid-url-that-does-not-exist-12345.com');

		$this->assertNull($result);
	}

	public function testStringRequestIsAliasForExecuteWithoutDecodeJson(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();

		// Both methods should return the same result
		$result1 = $client->stringRequest(HTTPMethod::GET, 'https://httpbin.org/html');
		$result2 = $client->execute(HTTPMethod::GET, 'https://httpbin.org/html', decodeJson: false);

		$this->assertEquals($result1, $result2);
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

	public function testRequestIsAliasForSend(): void
	{
		$this->markTestSkipped('This test requires an Internet connection and calls an external service');

		$client = new HTTPClient();

		// Both methods should return the same result
		$result1 = $client->request(HTTPMethod::GET, 'https://httpbin.org/get');
		$result2 = $client->send(HTTPMethod::GET, 'https://httpbin.org/get');

		$this->assertEquals($result1->getStatusCode(), $result2->getStatusCode());
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

	#[Group("integration")]
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

	#[Group("integration")]
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

	#[Group("integration")]
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
