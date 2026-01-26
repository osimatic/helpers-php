<?php

declare(strict_types=1);

namespace Tests\API;

use Osimatic\API\Mantis;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

final class MantisTest extends TestCase
{
	private const string TEST_URL = 'https://mantis.example.com';
	private const string TEST_API_TOKEN = 'test-api-token-123456789';
	private const string TEST_USER_ID = 'user123';

	/**
	 * Helper method to create a mock PSR-7 Response with JSON body
	 * @param array $data Data to encode as JSON
	 * @param int $statusCode HTTP status code
	 * @return ResponseInterface Mocked PSR-7 Response instance
	 */
	private function createJsonResponse(array $data, int $statusCode = 200): ResponseInterface
	{
		$jsonBody = json_encode($data);

		$stream = $this->createMock(StreamInterface::class);
		$stream->method('__toString')->willReturn($jsonBody);

		$response = $this->createMock(ResponseInterface::class);
		$response->method('getBody')->willReturn($stream);
		$response->method('getStatusCode')->willReturn($statusCode);

		return $response;
	}

	/* ===================== Constructor ===================== */

	public function testConstructorWithAllParameters(): void
	{
		$mantis = new Mantis(self::TEST_URL, self::TEST_API_TOKEN, self::TEST_USER_ID);

		$this->assertInstanceOf(Mantis::class, $mantis);
	}

	public function testConstructorWithHttpClientAndLogger(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$logger = $this->createMock(LoggerInterface::class);

		$mantis = new Mantis(
			url: self::TEST_URL,
			apiToken: self::TEST_API_TOKEN,
			userId: self::TEST_USER_ID,
			logger: $logger,
			httpClient: $httpClient
		);

		$this->assertInstanceOf(Mantis::class, $mantis);
	}

	public function testConstructorWithoutParameters(): void
	{
		$mantis = new Mantis();

		$this->assertInstanceOf(Mantis::class, $mantis);
	}

	public function testConstructorWithPartialParameters(): void
	{
		$mantis = new Mantis(self::TEST_URL);

		$this->assertInstanceOf(Mantis::class, $mantis);
	}

	public function testConstructorWithUrlAndApiToken(): void
	{
		$mantis = new Mantis(self::TEST_URL, self::TEST_API_TOKEN);

		$this->assertInstanceOf(Mantis::class, $mantis);
	}

	/* ===================== Setters ===================== */

	public function testSetUrl(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUrl(self::TEST_URL);

		$this->assertSame($mantis, $result);
	}

	public function testSetApiToken(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setApiToken(self::TEST_API_TOKEN);

		$this->assertSame($mantis, $result);
	}

	public function testSetUserId(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUserId(self::TEST_USER_ID);

		$this->assertSame($mantis, $result);
	}

	public function testFluentInterface(): void
	{
		$mantis = new Mantis();

		$result = $mantis
			->setUrl(self::TEST_URL)
			->setApiToken(self::TEST_API_TOKEN)
			->setUserId(self::TEST_USER_ID);

		$this->assertSame($mantis, $result);
	}

	/* ===================== addIssue() - Validation ===================== */

	public function testAddIssueWithoutUrl(): void
	{
		$mantis = new Mantis(null, self::TEST_API_TOKEN, self::TEST_USER_ID);

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	public function testAddIssueWithEmptyUrl(): void
	{
		$mantis = new Mantis('', self::TEST_API_TOKEN, self::TEST_USER_ID);

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	public function testAddIssueWithoutApiToken(): void
	{
		$mantis = new Mantis(self::TEST_URL, null, self::TEST_USER_ID);

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	public function testAddIssueWithEmptyApiToken(): void
	{
		$mantis = new Mantis(self::TEST_URL, '', self::TEST_USER_ID);

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	/* ===================== addIssue() with HTTP Client Mock ===================== */

	public function testAddIssueWithMinimalParametersReturnsIssueId(): void
	{
		$expectedIssueId = 12345;
		$response = $this->createJsonResponse([
			'issue' => [
				'id' => $expectedIssueId,
				'summary' => 'Test Issue',
			]
		], 201);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($response);

		$mantis = new Mantis(
			url: self::TEST_URL,
			apiToken: self::TEST_API_TOKEN,
			userId: null,
			httpClient: $httpClient
		);

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertSame($expectedIssueId, $result);
	}

	public function testAddIssueWithAllParametersReturnsIssueId(): void
	{
		$expectedIssueId = 67890;
		$response = $this->createJsonResponse([
			'issue' => [
				'id' => $expectedIssueId,
				'summary' => 'Complex Issue',
			]
		], 201);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($response);

		$mantis = new Mantis(
			url: self::TEST_URL,
			apiToken: self::TEST_API_TOKEN,
			userId: self::TEST_USER_ID,
			httpClient: $httpClient
		);

		$result = $mantis->addIssue(
			projectId: 5,
			title: 'Complex Issue',
			desc: 'Detailed Description',
			severity: 60,
			projectName: 'My Project',
			category: 'Bug',
			priority: 70,
			reproducibility: 'Always',
			customFields: ['field1' => 'value1', 'field2' => 'value2']
		);

		$this->assertSame($expectedIssueId, $result);
	}

	public function testAddIssueWithNetworkExceptionReturnsFalse(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException($this->createMock(ClientExceptionInterface::class));

		$mantis = new Mantis(
			url: self::TEST_URL,
			apiToken: self::TEST_API_TOKEN,
			userId: self::TEST_USER_ID,
			httpClient: $httpClient
		);

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	public function testAddIssueWithInvalidResponseReturnsFalse(): void
	{
		$response = $this->createJsonResponse(['error' => 'Something went wrong'], 400);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($response);

		$mantis = new Mantis(
			url: self::TEST_URL,
			apiToken: self::TEST_API_TOKEN,
			userId: self::TEST_USER_ID,
			httpClient: $httpClient
		);

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	/* ===================== Logger Integration ===================== */

	public function testAddIssueLogsErrorWhenUrlMissing(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('Mantis: URL is required for REST API calls');

		$mantis = new Mantis(
			url: null,
			apiToken: self::TEST_API_TOKEN,
			userId: self::TEST_USER_ID,
			logger: $logger
		);

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	public function testAddIssueLogsErrorWhenApiTokenMissing(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('Mantis: API token is required for authentication');

		$mantis = new Mantis(
			url: self::TEST_URL,
			apiToken: null,
			userId: self::TEST_USER_ID,
			logger: $logger
		);

		$result = $mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);

		$this->assertFalse($result);
	}

	public function testAddIssueLogsSuccessWhenIssueCreated(): void
	{
		$expectedIssueId = 999;
		$response = $this->createJsonResponse([
			'issue' => [
				'id' => $expectedIssueId,
				'summary' => 'Test Issue',
			]
		], 201);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($response);

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('info')
			->with(
				'Mantis: Issue created successfully via REST API',
				self::callback(function ($context) use ($expectedIssueId) {
					return $context['issue_id'] === $expectedIssueId
						&& $context['project_id'] === 1
						&& $context['title'] === 'Test Issue';
				})
			);

		$mantis = new Mantis(
			url: self::TEST_URL,
			apiToken: self::TEST_API_TOKEN,
			userId: self::TEST_USER_ID,
			logger: $logger,
			httpClient: $httpClient
		);

		$mantis->addIssue(
			projectId: 1,
			title: 'Test Issue',
			desc: 'Test Description',
			severity: 50
		);
	}

	public function testAddIssueLogsErrorOnException(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new \RuntimeException('Connection failed'));

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with(
				'Mantis: Error while creating issue via REST API',
				self::callback(function ($context) {
					return isset($context['exception'])
						&& isset($context['message'])
						&& $context['project_id'] === 1
						&& $context['title'] === 'Bug Report';
				})
			);

		$mantis = new Mantis(
			url: self::TEST_URL,
			apiToken: self::TEST_API_TOKEN,
			userId: self::TEST_USER_ID,
			logger: $logger,
			httpClient: $httpClient
		);

		$mantis->addIssue(
			projectId: 1,
			title: 'Bug Report',
			desc: 'Description',
			severity: 50
		);
	}

	/* ===================== URL Variations ===================== */

	public function testSetUrlWithTrailingSlash(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUrl('https://mantis.example.com/');

		$this->assertSame($mantis, $result);
	}

	public function testSetUrlWithoutTrailingSlash(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUrl('https://mantis.example.com');

		$this->assertSame($mantis, $result);
	}

	public function testSetUrlWithSubdirectory(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUrl('https://example.com/mantis/');

		$this->assertSame($mantis, $result);
	}

	public function testSetUrlWithPort(): void
	{
		$mantis = new Mantis();

		$result = $mantis->setUrl('https://mantis.example.com:8080/');

		$this->assertSame($mantis, $result);
	}
}