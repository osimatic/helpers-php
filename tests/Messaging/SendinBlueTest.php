<?php

declare(strict_types=1);

namespace Tests\Messaging;

use GuzzleHttp\Psr7\Response;
use Osimatic\Messaging\SendinBlue;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

final class SendinBlueTest extends TestCase
{
	private const string TEST_API_KEY = 'test-sendinblue-api-key-12345';
	private const int TEST_LIST_ID = 123;
	private const string TEST_EMAIL = 'test@example.com';

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
	 * Helper method to verify HTTP request contains authentication header
	 * @param mixed $request PSR-7 Request to verify
	 * @return bool True if request contains valid auth header
	 */
	private function assertRequestHasAuth($request): bool
	{
		return $request->hasHeader('api-key')
			&& $request->getHeaderLine('api-key') === self::TEST_API_KEY
			&& $request->hasHeader('accept')
			&& $request->getHeaderLine('accept') === 'application/json';
	}

	/* ===================== Constants ===================== */

	public function testApiUrlConstant(): void
	{
		self::assertSame('https://api.sendinblue.com/v3/', SendinBlue::API_URL);
	}

	/* ===================== Constructor ===================== */

	public function testConstructorWithAllParameters(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$httpClient = $this->createMock(ClientInterface::class);
		$sendinblue = new SendinBlue(self::TEST_API_KEY, logger: $logger, httpClient: $httpClient);

		self::assertInstanceOf(SendinBlue::class, $sendinblue);
	}

	public function testConstructorWithMinimalParameters(): void
	{
		$sendinblue = new SendinBlue();

		self::assertInstanceOf(SendinBlue::class, $sendinblue);
	}

	public function testConstructorWithLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$sendinblue = new SendinBlue(self::TEST_API_KEY, logger: $logger);

		self::assertInstanceOf(SendinBlue::class, $sendinblue);
	}

	public function testConstructorWithHttpClientInjection(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		self::assertInstanceOf(SendinBlue::class, $sendinblue);
	}

	/* ===================== Setters ===================== */

	public function testSetApiKey(): void
	{
		$sendinblue = new SendinBlue();

		$result = $sendinblue->setApiKey('new-api-key');

		self::assertSame($sendinblue, $result);
	}

	public function testFluentInterface(): void
	{
		$sendinblue = new SendinBlue();

		$result = $sendinblue->setApiKey(self::TEST_API_KEY);

		self::assertSame($sendinblue, $result);
	}

	/* ===================== createContact() ===================== */

	public function testCreateContactWithoutApiKey(): void
	{
		$sendinblue = new SendinBlue();

		$result = $sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL);

		self::assertFalse($result);
	}

	public function testCreateContactWithValidApiKey(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse(['id' => 1], 200));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL);

		self::assertTrue($result);
	}

	public function testCreateContactVerifiesRequestStructure(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) {
				$uri = (string) $request->getUri();
				$body = (string) $request->getBody();
				$bodyData = json_decode($body, true);

				return str_contains($uri, 'contacts')
					&& $this->assertRequestHasAuth($request)
					&& $request->getMethod() === 'POST'
					&& $bodyData['email'] === self::TEST_EMAIL
					&& $bodyData['listIds'] === [self::TEST_LIST_ID];
			}))
			->willReturn($this->createJsonResponse(['id' => 1], 200));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL);

		self::assertTrue($result);
	}

	public function testCreateContactWithAttributes(): void
	{
		$attributes = [
			'FNAME' => 'John',
			'LNAME' => 'Doe',
			'COMPANY' => 'Acme Inc',
			'SMS' => '+33612345678'
		];

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) use ($attributes) {
				$body = (string) $request->getBody();
				$bodyData = json_decode($body, true);

				return $bodyData['attributes'] === $attributes;
			}))
			->willReturn($this->createJsonResponse(['id' => 1], 200));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL, $attributes);

		self::assertTrue($result);
	}

	public function testCreateContactWithEmptyAttributes(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) {
				$body = (string) $request->getBody();
				$bodyData = json_decode($body, true);

				return $bodyData['attributes'] === [];
			}))
			->willReturn($this->createJsonResponse(['id' => 1], 200));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL, []);

		self::assertTrue($result);
	}

	public function testCreateContactWithNetworkException(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new class('Network error') extends \RuntimeException implements ClientExceptionInterface {});

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL);

		self::assertFalse($result);
	}

	public function testCreateContactWithApiError(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse(['code' => 'invalid_parameter', 'message' => 'Invalid email'], 400));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, 'invalid-email');

		self::assertFalse($result);
	}

	public function testCreateContactReturnsTrue201(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse(['id' => 1], 201));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL);

		self::assertFalse($result); // Only 200 returns true
	}

	public function testCreateContactWithDifferentListIds(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::exactly(3))
			->method('sendRequest')
			->willReturn($this->createJsonResponse(['id' => 1], 200));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result1 = $sendinblue->createContact(1, self::TEST_EMAIL);
		$result2 = $sendinblue->createContact(2, 'user2@example.com');
		$result3 = $sendinblue->createContact(999, 'user3@example.com');

		self::assertTrue($result1);
		self::assertTrue($result2);
		self::assertTrue($result3);
	}

	public function testCreateContactWithSpecialCharactersInEmail(): void
	{
		$specialEmail = 'user+tag@sub-domain.example.com';

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) use ($specialEmail) {
				$body = (string) $request->getBody();
				$bodyData = json_decode($body, true);

				return $bodyData['email'] === $specialEmail;
			}))
			->willReturn($this->createJsonResponse(['id' => 1], 200));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, $specialEmail);

		self::assertTrue($result);
	}

	public function testCreateContactWithUnicodeAttributes(): void
	{
		$attributes = [
			'FNAME' => 'François',
			'LNAME' => 'Müller',
			'CITY' => 'São Paulo'
		];

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse(['id' => 1], 200));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL, $attributes);

		self::assertTrue($result);
	}

	/* ===================== Credentials Validation ===================== */

	public function testCreateContactLogsErrorWithoutApiKey(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('SendinBlue API key is not configured. Please set apiKey.');

		$sendinblue = new SendinBlue(logger: $logger);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL);

		self::assertFalse($result);
	}

	public function testCreateContactWithEmptyApiKey(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('SendinBlue API key is not configured. Please set apiKey.');

		$sendinblue = new SendinBlue('', logger: $logger);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL);

		self::assertFalse($result);
	}

	/* ===================== HTTP Headers Verification ===================== */

	public function testCreateContactSendsCorrectHeaders(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) {
				return $request->hasHeader('api-key')
					&& $request->hasHeader('accept')
					&& $request->hasHeader('Content-Type')
					&& $request->getHeaderLine('Content-Type') === 'application/json';
			}))
			->willReturn($this->createJsonResponse(['id' => 1], 200));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL);
	}

	/* ===================== Multiple operations ===================== */

	public function testMultipleContactCreationsWithSameClient(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::exactly(3))
			->method('sendRequest')
			->willReturnOnConsecutiveCalls(
				$this->createJsonResponse(['id' => 1], 200),
				$this->createJsonResponse(['id' => 2], 200),
				$this->createJsonResponse(['id' => 3], 200)
			);

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result1 = $sendinblue->createContact(self::TEST_LIST_ID, 'user1@example.com');
		$result2 = $sendinblue->createContact(self::TEST_LIST_ID, 'user2@example.com');
		$result3 = $sendinblue->createContact(self::TEST_LIST_ID, 'user3@example.com');

		self::assertTrue($result1);
		self::assertTrue($result2);
		self::assertTrue($result3);
	}

	/* ===================== Workflow tests ===================== */

	public function testCompleteWorkflowWithMethodChaining(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse(['id' => 1], 200));

		$sendinblue = new SendinBlue(httpClient: $httpClient);

		// Test method chaining
		$sendinblue->setApiKey(self::TEST_API_KEY);

		$result = $sendinblue->createContact(
			self::TEST_LIST_ID,
			self::TEST_EMAIL,
			['FNAME' => 'John', 'LNAME' => 'Doe']
		);

		self::assertTrue($result);
	}

	/* ===================== Edge cases ===================== */

	public function testCreateContactWithLargeListId(): void
	{
		$largeListId = 2147483647; // Max int32

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) use ($largeListId) {
				$body = (string) $request->getBody();
				$bodyData = json_decode($body, true);

				return $bodyData['listIds'] === [$largeListId];
			}))
			->willReturn($this->createJsonResponse(['id' => 1], 200));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $sendinblue->createContact($largeListId, self::TEST_EMAIL);

		self::assertTrue($result);
	}

	public function testCreateContactWithManyAttributes(): void
	{
		$manyAttributes = [];
		for ($i = 1; $i <= 20; $i++) {
			$manyAttributes["FIELD_$i"] = "Value $i";
		}

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse(['id' => 1], 200));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL, $manyAttributes);

		self::assertTrue($result);
	}

	public function testCreateContactWithNumericAttributes(): void
	{
		$attributes = [
			'AGE' => 25,
			'SCORE' => 98.5,
			'COUNT' => 0
		];

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse(['id' => 1], 200));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL, $attributes);

		self::assertTrue($result);
	}

	public function testCreateContactWithBooleanAttributes(): void
	{
		$attributes = [
			'IS_ACTIVE' => true,
			'HAS_NEWSLETTER' => false
		];

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse(['id' => 1], 200));

		$sendinblue = new SendinBlue(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $sendinblue->createContact(self::TEST_LIST_ID, self::TEST_EMAIL, $attributes);

		self::assertTrue($result);
	}
}