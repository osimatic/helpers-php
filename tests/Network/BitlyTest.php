<?php

declare(strict_types=1);

namespace Tests\Network;

use GuzzleHttp\Psr7\Response;
use Osimatic\Network\Bitly;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

final class BitlyTest extends TestCase
{
	private const string TEST_LOGIN = 'test-login';
	private const string TEST_API_KEY = 'test-api-key-12345';
	private const string TEST_LONG_URL = 'https://example.com/very-long-url-that-needs-shortening';
	private const string TEST_SHORT_URL = 'http://bit.ly/abc123';

	/**
	 * Helper method to create a PSR-7 Response with JSON body
	 * @param array $data Data to encode as JSON
	 * @param int $statusCode HTTP status code
	 * @return Response PSR-7 Response instance
	 */
	private function createJsonResponse(array $data, int $statusCode = 200): Response
	{
		return new Response($statusCode, ['Content-Type' => 'application/json'], json_encode($data));
	}

	/**
	 * Helper method to verify HTTP request contains authentication parameters
	 * @param mixed $request PSR-7 Request to verify
	 * @return bool True if request contains valid auth parameters
	 */
	private function assertRequestHasAuth($request): bool
	{
		$uri = (string) $request->getUri();
		return str_contains($uri, 'login=' . self::TEST_LOGIN)
			&& str_contains($uri, 'apiKey=' . self::TEST_API_KEY)
			&& str_contains($uri, 'version=2.0.1')
			&& str_contains($uri, 'format=json');
	}

	/* ===================== Constants ===================== */

	public function testApiUrlConstant(): void
	{
		self::assertSame('http://api.bit.ly/', Bitly::API_URL);
	}

	public function testApiVersionConstant(): void
	{
		self::assertSame('2.0.1', Bitly::API_VERSION);
	}

	/* ===================== Constructor ===================== */

	public function testConstructorWithAllParameters(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$httpClient = $this->createMock(ClientInterface::class);
		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, logger: $logger, httpClient: $httpClient);

		self::assertInstanceOf(Bitly::class, $bitly);
	}

	public function testConstructorWithMinimalParameters(): void
	{
		$bitly = new Bitly();

		self::assertInstanceOf(Bitly::class, $bitly);
	}

	public function testConstructorWithLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, logger: $logger);

		self::assertInstanceOf(Bitly::class, $bitly);
	}

	public function testConstructorWithHttpClientInjection(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		self::assertInstanceOf(Bitly::class, $bitly);
	}

	/* ===================== Setters ===================== */

	public function testSetLogin(): void
	{
		$bitly = new Bitly();

		$result = $bitly->setLogin('new-login');

		self::assertSame($bitly, $result);
	}

	public function testSetKey(): void
	{
		$bitly = new Bitly();

		$result = $bitly->setKey('new-api-key');

		self::assertSame($bitly, $result);
	}

	public function testFluentInterface(): void
	{
		$bitly = new Bitly();

		$result = $bitly
			->setLogin(self::TEST_LOGIN)
			->setKey(self::TEST_API_KEY);

		self::assertSame($bitly, $result);
	}

	/* ===================== shorten() ===================== */

	public function testShortenWithoutCredentials(): void
	{
		$bitly = new Bitly();

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNull($result);
	}

	public function testShortenWithoutLogin(): void
	{
		$bitly = new Bitly(key: self::TEST_API_KEY);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNull($result);
	}

	public function testShortenWithoutApiKey(): void
	{
		$bitly = new Bitly(login: self::TEST_LOGIN);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNull($result);
	}

	public function testShortenWithValidCredentials(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'results' => [
					self::TEST_LONG_URL => [
						'shortUrl' => self::TEST_SHORT_URL,
						'hash' => 'abc123',
					]
				],
				'statusCode' => 'OK'
			]));

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNotNull($result);
		self::assertSame(self::TEST_SHORT_URL, $result);
	}

	public function testShortenVerifiesRequestContainsLongUrl(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) {
				$uri = (string) $request->getUri();
				return str_contains($uri, 'shorten')
					&& str_contains($uri, 'longUrl=' . urlencode(self::TEST_LONG_URL))
					&& $this->assertRequestHasAuth($request);
			}))
			->willReturn($this->createJsonResponse([
				'results' => [self::TEST_LONG_URL => ['shortUrl' => self::TEST_SHORT_URL]]
			]));

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNotNull($result);
	}

	public function testShortenReturnsNullWhenNoShortUrl(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'results' => [],
				'statusCode' => 'ERROR'
			]));

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNull($result);
	}

	public function testShortenWithNetworkException(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new class('Network error') extends \RuntimeException implements ClientExceptionInterface {});

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNull($result);
	}

	public function testShortenWithDifferentUrls(): void
	{
		$url1 = 'https://example.com/page1';
		$url2 = 'https://example.com/page2';
		$short1 = 'http://bit.ly/page1';
		$short2 = 'http://bit.ly/page2';

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::exactly(2))
			->method('sendRequest')
			->willReturnOnConsecutiveCalls(
				$this->createJsonResponse(['results' => [$url1 => ['shortUrl' => $short1]]]),
				$this->createJsonResponse(['results' => [$url2 => ['shortUrl' => $short2]]])
			);

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result1 = $bitly->shorten($url1);
		$result2 = $bitly->shorten($url2);

		self::assertSame($short1, $result1);
		self::assertSame($short2, $result2);
	}

	public function testShortenWithUrlContainingQueryParameters(): void
	{
		$longUrl = 'https://example.com/page?param1=value1&param2=value2';
		$shortUrl = 'http://bit.ly/xyz789';

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'results' => [$longUrl => ['shortUrl' => $shortUrl]]
			]));

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result = $bitly->shorten($longUrl);

		self::assertSame($shortUrl, $result);
	}

	public function testShortenWithUrlContainingSpecialCharacters(): void
	{
		$longUrl = 'https://example.com/page?query=hello world&symbol=€';
		$shortUrl = 'http://bit.ly/special123';

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'results' => [$longUrl => ['shortUrl' => $shortUrl]]
			]));

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result = $bitly->shorten($longUrl);

		self::assertSame($shortUrl, $result);
	}

	/* ===================== Credentials Validation ===================== */

	public function testCredentialsValidationWithEmptyLogin(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('Bitly API credentials are not configured. Please set login and key.');

		$bitly = new Bitly('', self::TEST_API_KEY, logger: $logger);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNull($result);
	}

	public function testCredentialsValidationWithEmptyApiKey(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('Bitly API credentials are not configured. Please set login and key.');

		$bitly = new Bitly(self::TEST_LOGIN, '', logger: $logger);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNull($result);
	}

	public function testCredentialsValidationWithBothEmpty(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('Bitly API credentials are not configured. Please set login and key.');

		$bitly = new Bitly('', '', logger: $logger);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNull($result);
	}

	/* ===================== Response Structure Validation ===================== */

	public function testShortenWithCompleteResponse(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'errorCode' => 0,
				'errorMessage' => '',
				'results' => [
					self::TEST_LONG_URL => [
						'hash' => 'abc123',
						'shortKeywordUrl' => '',
						'shortUrl' => self::TEST_SHORT_URL,
						'userHash' => 'def456'
					]
				],
				'statusCode' => 'OK'
			]));

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertSame(self::TEST_SHORT_URL, $result);
	}

	public function testShortenWithMissingResultsKey(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'statusCode' => 'ERROR',
				'errorMessage' => 'Invalid URL'
			]));

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNull($result);
	}

	public function testShortenWithMissingShortUrlInResult(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'results' => [
					self::TEST_LONG_URL => [
						'hash' => 'abc123',
						// Missing shortUrl
					]
				],
				'statusCode' => 'OK'
			]));

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNull($result);
	}

	/* ===================== Method Chaining and Workflow ===================== */

	public function testCompleteWorkflowWithMethodChaining(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'results' => [self::TEST_LONG_URL => ['shortUrl' => self::TEST_SHORT_URL]]
			]));

		$bitly = new Bitly(httpClient: $httpClient);

		// Test method chaining
		$bitly
			->setLogin(self::TEST_LOGIN)
			->setKey(self::TEST_API_KEY);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNotNull($result);
		self::assertSame(self::TEST_SHORT_URL, $result);
	}

	public function testMultipleShorteningsWithSameClient(): void
	{
		$url1 = 'https://example.com/article/1';
		$url2 = 'https://example.com/article/2';
		$url3 = 'https://example.com/article/3';
		$short1 = 'http://bit.ly/art1';
		$short2 = 'http://bit.ly/art2';
		$short3 = 'http://bit.ly/art3';

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::exactly(3))
			->method('sendRequest')
			->willReturnOnConsecutiveCalls(
				$this->createJsonResponse(['results' => [$url1 => ['shortUrl' => $short1]]]),
				$this->createJsonResponse(['results' => [$url2 => ['shortUrl' => $short2]]]),
				$this->createJsonResponse(['results' => [$url3 => ['shortUrl' => $short3]]])
			);

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result1 = $bitly->shorten($url1);
		$result2 = $bitly->shorten($url2);
		$result3 = $bitly->shorten($url3);

		self::assertSame($short1, $result1);
		self::assertSame($short2, $result2);
		self::assertSame($short3, $result3);
	}

	/* ===================== Edge Cases ===================== */

	public function testShortenWithVeryLongUrl(): void
	{
		$longUrl = 'https://example.com/' . str_repeat('a', 2000);
		$shortUrl = 'http://bit.ly/long123';

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'results' => [$longUrl => ['shortUrl' => $shortUrl]]
			]));

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result = $bitly->shorten($longUrl);

		self::assertSame($shortUrl, $result);
	}

	public function testShortenWithHttpsUrl(): void
	{
		$longUrl = 'https://secure.example.com/path';
		$shortUrl = 'http://bit.ly/secure123';

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'results' => [$longUrl => ['shortUrl' => $shortUrl]]
			]));

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result = $bitly->shorten($longUrl);

		self::assertSame($shortUrl, $result);
	}

	public function testShortenWithHttpUrl(): void
	{
		$longUrl = 'http://example.com/path';
		$shortUrl = 'http://bit.ly/http123';

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'results' => [$longUrl => ['shortUrl' => $shortUrl]]
			]));

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result = $bitly->shorten($longUrl);

		self::assertSame($shortUrl, $result);
	}

	public function testShortenWithUrlFragment(): void
	{
		$longUrl = 'https://example.com/page#section';
		$shortUrl = 'http://bit.ly/frag123';

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'results' => [$longUrl => ['shortUrl' => $shortUrl]]
			]));

		$bitly = new Bitly(self::TEST_LOGIN, self::TEST_API_KEY, httpClient: $httpClient);

		$result = $bitly->shorten($longUrl);

		self::assertSame($shortUrl, $result);
	}

	public function testShortenLogsCredentialError(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('Bitly API credentials are not configured. Please set login and key.');

		$bitly = new Bitly(logger: $logger);

		$result = $bitly->shorten(self::TEST_LONG_URL);

		self::assertNull($result);
	}
}