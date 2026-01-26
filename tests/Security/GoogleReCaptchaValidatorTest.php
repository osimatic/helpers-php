<?php

namespace Tests\Security;

use GuzzleHttp\Psr7\Response;
use Osimatic\Security\GoogleReCaptchaValidator;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

class GoogleReCaptchaValidatorTest extends TestCase
{
	private const string TEST_SITE_KEY = 'test-site-key';
	private const string TEST_SECRET = 'test-secret';
	private const string TEST_RESPONSE_TOKEN = 'test-recaptcha-response-token';

	// ========== Constructor Tests ==========

	public function testConstructorWithAllParameters(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$httpClient = $this->createMock(ClientInterface::class);

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			logger: $logger,
			httpClient: $httpClient
		);

		self::assertInstanceOf(GoogleReCaptchaValidator::class, $validator);
	}

	public function testConstructorWithMinimalParameters(): void
	{
		$validator = new GoogleReCaptchaValidator(self::TEST_SITE_KEY, self::TEST_SECRET);
		self::assertInstanceOf(GoogleReCaptchaValidator::class, $validator);
	}

	public function testConstructorWithoutParameters(): void
	{
		$validator = new GoogleReCaptchaValidator();
		self::assertInstanceOf(GoogleReCaptchaValidator::class, $validator);
	}

	public function testValidatorIsReadonly(): void
	{
		$validator = new GoogleReCaptchaValidator(self::TEST_SITE_KEY, self::TEST_SECRET);
		$reflection = new \ReflectionClass($validator);
		self::assertTrue($reflection->isReadOnly());
	}

	// ========== Successful Validation Tests ==========

	public function testValidatorWithValidResponseReturnsTrue(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn(new Response(200, [], json_encode(['success' => true])));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator(self::TEST_RESPONSE_TOKEN);

		self::assertTrue($result);
	}

	public function testValidatorWithSuccessfulVerification(): void
	{
		$responseBody = json_encode([
			'success' => true,
			'challenge_ts' => '2024-01-26T12:00:00Z',
			'hostname' => 'example.com',
		]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn(new Response(200, [], $responseBody));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator(self::TEST_RESPONSE_TOKEN);

		self::assertTrue($result);
	}

	// ========== Failed Validation Tests ==========

	public function testValidatorWithInvalidResponseReturnsFalse(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn(new Response(200, [], json_encode(['success' => false])));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	public function testValidatorWithFailedVerificationAndErrorCodes(): void
	{
		$responseBody = json_encode([
			'success' => false,
			'error-codes' => [
				'invalid-input-response',
				'timeout-or-duplicate',
			],
		]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn(new Response(200, [], $responseBody));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	// ========== Empty/Null Response Tests ==========

	public function testValidatorWithNullResponseReturnsFalse(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::never())->method('sendRequest');

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator(null);

		self::assertFalse($result);
	}

	public function testValidatorWithEmptyResponseReturnsFalse(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::never())->method('sendRequest');

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator('');

		self::assertFalse($result);
	}

	public function testValidatorWithWhitespaceOnlyResponseReturnsFalse(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn(new Response(200, [], json_encode(['success' => false])));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator('   ');

		self::assertFalse($result);
	}

	// ========== Missing Configuration Tests ==========

	public function testValidatorWithoutSecretReturnsFalse(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('Google reCAPTCHA secret key (private key) is not configured. Please set secret key.');

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::never())->method('sendRequest');

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: null,
			logger: $logger,
			httpClient: $httpClient
		);

		$result = $validator(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	public function testValidatorWithEmptySecretReturnsFalse(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('Google reCAPTCHA secret key (private key) is not configured. Please set secret key.');

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::never())->method('sendRequest');

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: '',
			logger: $logger,
			httpClient: $httpClient
		);

		$result = $validator(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	// ========== HTTP Error Tests ==========

	public function testValidatorWithHttpErrorReturnsFalse(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn(new Response(500, [], 'Internal Server Error'));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	public function testValidatorWithInvalidJsonResponseReturnsFalse(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn(new Response(200, [], 'invalid json'));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	public function testValidatorWithEmptyResponseBodyReturnsFalse(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn(new Response(200, [], ''));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	public function testValidatorWithNetworkExceptionReturnsFalse(): void
	{
		$exception = new class('Network error') extends \RuntimeException implements ClientExceptionInterface {};

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException($exception);

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	// ========== Edge Cases ==========

	public function testValidatorWithMissingSuccessFieldReturnsFalse(): void
	{
		$responseBody = json_encode([
			'challenge_ts' => '2024-01-26T12:00:00Z',
			'hostname' => 'example.com',
		]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn(new Response(200, [], $responseBody));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	public function testValidatorWithSuccessFieldNotBooleanReturnsFalse(): void
	{
		$responseBody = json_encode(['success' => 'true']); // String instead of boolean

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn(new Response(200, [], $responseBody));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	// ========== Multiple Invocations ==========

	public function testValidatorCanBeInvokedMultipleTimes(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::exactly(3))
			->method('sendRequest')
			->willReturnOnConsecutiveCalls(
				new Response(200, [], json_encode(['success' => true])),
				new Response(200, [], json_encode(['success' => false])),
				new Response(200, [], json_encode(['success' => true]))
			);

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		self::assertTrue($validator('token1'));
		self::assertFalse($validator('token2'));
		self::assertTrue($validator('token3'));
	}

	// ========== Different Response Tokens ==========

	public function testValidatorWithLongResponseToken(): void
	{
		$longToken = str_repeat('a', 1000);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn(new Response(200, [], json_encode(['success' => true])));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator($longToken);

		self::assertTrue($result);
	}

	public function testValidatorWithSpecialCharactersInToken(): void
	{
		$specialToken = 'token-with-special_chars.123';

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn(new Response(200, [], json_encode(['success' => true])));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $validator($specialToken);

		self::assertTrue($result);
	}

	// ========== Logger Integration Tests ==========

	public function testValidatorLogsErrorWhenSecretIsMissing(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with(self::stringContains('secret key'));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: null,
			logger: $logger
		);

		$validator(self::TEST_RESPONSE_TOKEN);
	}

	public function testValidatorWithCustomLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->method('sendRequest')
			->willReturn(new Response(200, [], json_encode(['success' => true])));

		$validator = new GoogleReCaptchaValidator(
			siteKey: self::TEST_SITE_KEY,
			secret: self::TEST_SECRET,
			logger: $logger,
			httpClient: $httpClient
		);

		$result = $validator(self::TEST_RESPONSE_TOKEN);

		self::assertTrue($result);
	}
}