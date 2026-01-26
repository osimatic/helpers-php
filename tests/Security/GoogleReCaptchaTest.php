<?php

declare(strict_types=1);

namespace Tests\Security;

use GuzzleHttp\Psr7\Response;
use Osimatic\Security\GoogleReCaptcha;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

final class GoogleReCaptchaTest extends TestCase
{
	private const string TEST_SITE_KEY = 'test-site-key-123456';
	private const string TEST_SECRET = 'test-secret-123456';
	private const string TEST_RESPONSE_TOKEN = 'test-response-token-xyz';

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

	/* ===================== Constructor ===================== */

	public function testConstructorWithMinimalParameters(): void
	{
		$recaptcha = new GoogleReCaptcha();

		self::assertInstanceOf(GoogleReCaptcha::class, $recaptcha);
	}

	public function testConstructorWithAllParameters(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$httpClient = $this->createMock(ClientInterface::class);

		$recaptcha = new GoogleReCaptcha(
			self::TEST_SITE_KEY,
			self::TEST_SECRET,
			logger: $logger,
			httpClient: $httpClient
		);

		self::assertInstanceOf(GoogleReCaptcha::class, $recaptcha);
	}

	public function testConstructorWithSiteKeyAndSecret(): void
	{
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, self::TEST_SECRET);

		self::assertInstanceOf(GoogleReCaptcha::class, $recaptcha);
	}

	public function testConstructorWithLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$recaptcha = new GoogleReCaptcha(logger: $logger);

		self::assertInstanceOf(GoogleReCaptcha::class, $recaptcha);
	}

	public function testConstructorWithHttpClientInjection(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$recaptcha = new GoogleReCaptcha(httpClient: $httpClient);

		self::assertInstanceOf(GoogleReCaptcha::class, $recaptcha);
	}

	/* ===================== Setters ===================== */

	public function testSetSiteKey(): void
	{
		$recaptcha = new GoogleReCaptcha();

		$result = $recaptcha->setSiteKey(self::TEST_SITE_KEY);

		self::assertSame($recaptcha, $result);
	}

	public function testSetSecret(): void
	{
		$recaptcha = new GoogleReCaptcha();

		$result = $recaptcha->setSecret(self::TEST_SECRET);

		self::assertSame($recaptcha, $result);
	}

	public function testFluentInterface(): void
	{
		$recaptcha = new GoogleReCaptcha();

		$result = $recaptcha
			->setSiteKey(self::TEST_SITE_KEY)
			->setSecret(self::TEST_SECRET);

		self::assertSame($recaptcha, $result);
	}

	/* ===================== check() ===================== */

	public function testCheckReturnsFalseWithoutSecret(): void
	{
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY);

		$result = $recaptcha->check(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	public function testCheckReturnsFalseWithEmptySecret(): void
	{
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, '');

		$result = $recaptcha->check(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	public function testCheckReturnsFalseWithNullResponse(): void
	{
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, self::TEST_SECRET);

		$result = $recaptcha->check(null);

		self::assertFalse($result);
	}

	public function testCheckReturnsFalseWithEmptyResponse(): void
	{
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, self::TEST_SECRET);

		$result = $recaptcha->check('');

		self::assertFalse($result);
	}

	public function testCheckReturnsTrueOnSuccessfulVerification(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse(['success' => true]));

		$recaptcha = new GoogleReCaptcha(
			self::TEST_SITE_KEY,
			self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $recaptcha->check(self::TEST_RESPONSE_TOKEN);

		self::assertTrue($result);
	}

	public function testCheckReturnsFalseOnFailedVerification(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'success' => false,
				'error-codes' => ['invalid-input-response']
			]));

		$recaptcha = new GoogleReCaptcha(
			self::TEST_SITE_KEY,
			self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $recaptcha->check(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	public function testCheckVerifiesRequestStructure(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) {
				$uri = (string) $request->getUri();
				return str_contains($uri, 'www.google.com/recaptcha/api/siteverify')
					&& str_contains($uri, 'secret=' . self::TEST_SECRET)
					&& str_contains($uri, 'response=' . self::TEST_RESPONSE_TOKEN)
					&& $request->getMethod() === 'GET';
			}))
			->willReturn($this->createJsonResponse(['success' => true]));

		$recaptcha = new GoogleReCaptcha(
			self::TEST_SITE_KEY,
			self::TEST_SECRET,
			httpClient: $httpClient
		);

		$recaptcha->check(self::TEST_RESPONSE_TOKEN);
	}

	public function testCheckReturnsFalseOnNetworkException(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new class('Network error') extends \RuntimeException implements ClientExceptionInterface {});

		$recaptcha = new GoogleReCaptcha(
			self::TEST_SITE_KEY,
			self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $recaptcha->check(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	public function testCheckReturnsFalseWhenSuccessKeyMissing(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([]));

		$recaptcha = new GoogleReCaptcha(
			self::TEST_SITE_KEY,
			self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $recaptcha->check(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	public function testCheckReturnsFalseWhenSuccessIsNotBoolean(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse(['success' => 'true'])); // String instead of boolean

		$recaptcha = new GoogleReCaptcha(
			self::TEST_SITE_KEY,
			self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $recaptcha->check(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	public function testCheckWithCompleteSuccessResponse(): void
	{
		$responseData = [
			'success' => true,
			'challenge_ts' => '2023-01-01T12:00:00Z',
			'hostname' => 'example.com',
		];

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$recaptcha = new GoogleReCaptcha(
			self::TEST_SITE_KEY,
			self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result = $recaptcha->check(self::TEST_RESPONSE_TOKEN);

		self::assertTrue($result);
	}

	public function testCheckWithDifferentErrorCodes(): void
	{
		$errorCodes = [
			['missing-input-secret'],
			['invalid-input-secret'],
			['missing-input-response'],
			['invalid-input-response'],
			['bad-request'],
			['timeout-or-duplicate'],
		];

		foreach ($errorCodes as $errorCode) {
			$httpClient = $this->createMock(ClientInterface::class);
			$httpClient->expects(self::once())
				->method('sendRequest')
				->willReturn($this->createJsonResponse([
					'success' => false,
					'error-codes' => $errorCode
				]));

			$recaptcha = new GoogleReCaptcha(
				self::TEST_SITE_KEY,
				self::TEST_SECRET,
				httpClient: $httpClient
			);

			$result = $recaptcha->check(self::TEST_RESPONSE_TOKEN);

			self::assertFalse($result, 'Failed for error code: ' . implode(', ', $errorCode));
		}
	}

	/* ===================== getFormField() ===================== */

	public function testGetFormFieldReturnsDiv(): void
	{
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, self::TEST_SECRET);

		$html = $recaptcha->getFormField();

		self::assertStringStartsWith('<div', $html);
		self::assertStringEndsWith('</div>', $html);
	}

	public function testGetFormFieldContainsRecaptchaClass(): void
	{
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, self::TEST_SECRET);

		$html = $recaptcha->getFormField();

		self::assertStringContainsString('g-recaptcha', $html);
	}

	public function testGetFormFieldContainsSiteKey(): void
	{
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, self::TEST_SECRET);

		$html = $recaptcha->getFormField();

		self::assertStringContainsString(self::TEST_SITE_KEY, $html);
		self::assertStringContainsString('data-sitekey', $html);
	}

	public function testGetFormFieldWithDifferentSiteKey(): void
	{
		$customSiteKey = 'custom-site-key-789';
		$recaptcha = new GoogleReCaptcha($customSiteKey, self::TEST_SECRET);

		$html = $recaptcha->getFormField();

		self::assertStringContainsString($customSiteKey, $html);
	}

	public function testGetFormFieldAfterSetSiteKey(): void
	{
		$newSiteKey = 'new-site-key-999';
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, self::TEST_SECRET);
		$recaptcha->setSiteKey($newSiteKey);

		$html = $recaptcha->getFormField();

		self::assertStringContainsString($newSiteKey, $html);
		self::assertStringNotContainsString(self::TEST_SITE_KEY, $html);
	}

	/* ===================== getJavaScriptUrl() ===================== */

	public function testGetJavaScriptUrlWithDefaultLocale(): void
	{
		$recaptcha = new GoogleReCaptcha();

		$url = $recaptcha->getJavaScriptUrl();

		self::assertStringContainsString('https://www.google.com/recaptcha/api.js', $url);
		self::assertStringContainsString('hl=en', $url);
	}

	public function testGetJavaScriptUrlWithFrenchLocale(): void
	{
		$recaptcha = new GoogleReCaptcha();

		$url = $recaptcha->getJavaScriptUrl('fr');

		self::assertStringContainsString('https://www.google.com/recaptcha/api.js', $url);
		self::assertStringContainsString('hl=fr', $url);
	}

	public function testGetJavaScriptUrlWithSpanishLocale(): void
	{
		$recaptcha = new GoogleReCaptcha();

		$url = $recaptcha->getJavaScriptUrl('es');

		self::assertStringContainsString('hl=es', $url);
	}

	public function testGetJavaScriptUrlWithGermanLocale(): void
	{
		$recaptcha = new GoogleReCaptcha();

		$url = $recaptcha->getJavaScriptUrl('de');

		self::assertStringContainsString('hl=de', $url);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('localesProvider')]
	public function testGetJavaScriptUrlWithMultipleLocales(string $locale): void
	{
		$recaptcha = new GoogleReCaptcha();

		$url = $recaptcha->getJavaScriptUrl($locale);

		self::assertStringContainsString('https://www.google.com/recaptcha/api.js', $url);
		self::assertStringContainsString('hl=' . $locale, $url);
	}

	public static function localesProvider(): array
	{
		return [
			['en'],
			['fr'],
			['es'],
			['de'],
			['it'],
			['pt'],
			['nl'],
			['pl'],
			['ru'],
			['ja'],
			['zh'],
			['ar'],
			['ko'],
			['hi'],
		];
	}

	/* ===================== Logger integration ===================== */

	public function testCheckLogsErrorWithoutSecret(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('Google reCAPTCHA secret key (private key) is not configured. Please set secret key.');

		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, logger: $logger);

		$result = $recaptcha->check(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	public function testCheckLogsErrorOnException(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error');

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new class('Network error') extends \RuntimeException implements ClientExceptionInterface {});

		$recaptcha = new GoogleReCaptcha(
			self::TEST_SITE_KEY,
			self::TEST_SECRET,
			logger: $logger,
			httpClient: $httpClient
		);

		$result = $recaptcha->check(self::TEST_RESPONSE_TOKEN);

		self::assertFalse($result);
	}

	/* ===================== Integration scenarios ===================== */

	public function testCompleteWorkflowWithSuccessfulVerification(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse(['success' => true]));

		$recaptcha = new GoogleReCaptcha(httpClient: $httpClient);
		$recaptcha
			->setSiteKey(self::TEST_SITE_KEY)
			->setSecret(self::TEST_SECRET);

		// Generate form field
		$formField = $recaptcha->getFormField();
		self::assertStringContainsString(self::TEST_SITE_KEY, $formField);

		// Generate JS URL
		$jsUrl = $recaptcha->getJavaScriptUrl('en');
		self::assertStringContainsString('api.js', $jsUrl);

		// Verify response
		$result = $recaptcha->check(self::TEST_RESPONSE_TOKEN);
		self::assertTrue($result);
	}

	public function testMultipleCheckCallsWithSameClient(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::exactly(3))
			->method('sendRequest')
			->willReturnOnConsecutiveCalls(
				$this->createJsonResponse(['success' => true]),
				$this->createJsonResponse(['success' => false]),
				$this->createJsonResponse(['success' => true])
			);

		$recaptcha = new GoogleReCaptcha(
			self::TEST_SITE_KEY,
			self::TEST_SECRET,
			httpClient: $httpClient
		);

		$result1 = $recaptcha->check('token1');
		$result2 = $recaptcha->check('token2');
		$result3 = $recaptcha->check('token3');

		self::assertTrue($result1);
		self::assertFalse($result2);
		self::assertTrue($result3);
	}

	/* ===================== Edge cases ===================== */

	public function testGetFormFieldWithNullSiteKey(): void
	{
		$recaptcha = new GoogleReCaptcha();

		$html = $recaptcha->getFormField();

		self::assertStringContainsString('g-recaptcha', $html);
		self::assertStringContainsString('data-sitekey=""', $html);
	}

	public function testGetJavaScriptUrlWithEmptyLocale(): void
	{
		$recaptcha = new GoogleReCaptcha();

		$url = $recaptcha->getJavaScriptUrl('');

		self::assertStringContainsString('https://www.google.com/recaptcha/api.js', $url);
		self::assertStringContainsString('hl=', $url);
	}
}