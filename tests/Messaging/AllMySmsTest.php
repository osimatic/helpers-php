<?php

declare(strict_types=1);

namespace Tests\Messaging;

use Osimatic\Messaging\AllMySms;
use Osimatic\Messaging\SMS;
use Osimatic\Network\HTTPMethod;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

final class AllMySmsTest extends TestCase
{
	private const string TEST_LOGIN = 'test-login';
	private const string TEST_API_KEY = 'test-api-key-123456';
	private const string TEST_SENDER = 'MyCompany';
	private const string TEST_PHONE = '+33612345678';
	private const string TEST_MESSAGE = 'Test SMS message';

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
		$stream->method('getContents')->willReturn($jsonBody);

		$response = $this->createMock(ResponseInterface::class);
		$response->method('getBody')->willReturn($stream);
		$response->method('getStatusCode')->willReturn($statusCode);

		return $response;
	}

	/**
	 * Helper method to create a mock SMS
	 * @param array $recipients Array of phone numbers
	 * @param string $sender Sender name
	 * @param string $text Message text
	 * @return SMS
	 */
	private function createSms(array $recipients, string $sender = self::TEST_SENDER, string $text = self::TEST_MESSAGE): SMS
	{
		$sms = new SMS();
		$sms->setSenderName($sender)
			->setText($text)
			->setListRecipient($recipients);

		return $sms;
	}

	/* ===================== Constructor ===================== */

	public function testConstructorWithAllParameters(): void
	{
		$allMySms = new AllMySms(self::TEST_LOGIN, self::TEST_API_KEY);

		$this->assertInstanceOf(AllMySms::class, $allMySms);
	}

	public function testConstructorWithHttpClientAndLogger(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$logger = $this->createMock(LoggerInterface::class);

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			logger: $logger,
			httpClient: $httpClient
		);

		$this->assertInstanceOf(AllMySms::class, $allMySms);
	}

	/* ===================== Setters ===================== */

	public function testSetLogin(): void
	{
		$allMySms = new AllMySms(self::TEST_LOGIN, self::TEST_API_KEY);

		$result = $allMySms->setLogin('new-login');

		$this->assertSame($allMySms, $result);
	}

	public function testSetApiKey(): void
	{
		$allMySms = new AllMySms(self::TEST_LOGIN, self::TEST_API_KEY);

		$result = $allMySms->setApiKey('new-api-key');

		$this->assertSame($allMySms, $result);
	}

	public function testFluentInterface(): void
	{
		$allMySms = new AllMySms(self::TEST_LOGIN, self::TEST_API_KEY);

		$result = $allMySms
			->setLogin('new-login')
			->setApiKey('new-api-key');

		$this->assertSame($allMySms, $result);
	}

	/* ===================== getAuthToken() ===================== */

	public function testGetAuthToken(): void
	{
		$allMySms = new AllMySms(self::TEST_LOGIN, self::TEST_API_KEY);

		$token = $allMySms->getAuthToken();

		$expectedToken = base64_encode(self::TEST_LOGIN . ':' . self::TEST_API_KEY);
		$this->assertEquals($expectedToken, $token);
	}

	public function testGetAuthTokenAfterSetLogin(): void
	{
		$allMySms = new AllMySms(self::TEST_LOGIN, self::TEST_API_KEY);
		$allMySms->setLogin('updated-login');

		$token = $allMySms->getAuthToken();

		$expectedToken = base64_encode('updated-login:' . self::TEST_API_KEY);
		$this->assertEquals($expectedToken, $token);
	}

	public function testGetAuthTokenAfterSetApiKey(): void
	{
		$allMySms = new AllMySms(self::TEST_LOGIN, self::TEST_API_KEY);
		$allMySms->setApiKey('updated-api-key');

		$token = $allMySms->getAuthToken();

		$expectedToken = base64_encode(self::TEST_LOGIN . ':updated-api-key');
		$this->assertEquals($expectedToken, $token);
	}

	/* ===================== send() - Successful Sending ===================== */

	public function testSendWithSuccessStatus100(): void
	{
		$response = $this->createJsonResponse([
			'status' => 100,
			'message' => 'Le message a été envoyé',
		]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($response);

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('info')
			->with('AllMySms API response');

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			logger: $logger,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE]);

		// Should not throw any exception
		$allMySms->send($sms);
	}

	public function testSendWithSuccessStatus101(): void
	{
		$response = $this->createJsonResponse([
			'status' => 101,
			'message' => 'Le message a été programmé pour un envoi différé',
		]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($response);

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('info');

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			logger: $logger,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE]);

		// Should not throw any exception
		$allMySms->send($sms);
	}

	public function testSendWithMultipleRecipients(): void
	{
		$recipients = ['+33612345678', '+33687654321', '+33698765432'];

		$response = $this->createJsonResponse([
			'status' => 100,
			'message' => 'Le message a été envoyé',
		]);

		$httpClient = $this->createMock(ClientInterface::class);
		// Expects 3 calls (one per recipient)
		$httpClient->expects(self::exactly(3))
			->method('sendRequest')
			->willReturn($response);

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::exactly(3))
			->method('info');

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			logger: $logger,
			httpClient: $httpClient
		);

		$sms = $this->createSms($recipients);

		$allMySms->send($sms);
	}

	public function testSendVerifiesRequestHeaders(): void
	{
		$response = $this->createJsonResponse(['status' => 100]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function (RequestInterface $request) {
				// Verify Authorization header
				$authHeaders = $request->getHeader('Authorization');
				$this->assertCount(1, $authHeaders);
				$this->assertStringStartsWith('Basic ', $authHeaders[0]);

				// Verify URL
				$this->assertEquals(AllMySms::API_URL . 'sms/send', (string) $request->getUri());

				// Verify method
				$this->assertEquals('POST', $request->getMethod());

				return true;
			}))
			->willReturn($response);

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE]);
		$allMySms->send($sms);
	}

	public function testSendVerifiesRequestBody(): void
	{
		$response = $this->createJsonResponse(['status' => 100]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function (RequestInterface $request) {
				$body = (string) $request->getBody();
				$data = json_decode($body, true);

				$this->assertIsArray($data);
				$this->assertArrayHasKey('from', $data);
				$this->assertArrayHasKey('to', $data);
				$this->assertArrayHasKey('text', $data);
				$this->assertEquals(self::TEST_SENDER, $data['from']);
				$this->assertEquals(self::TEST_PHONE, $data['to']);
				$this->assertEquals(self::TEST_MESSAGE, $data['text']);

				return true;
			}))
			->willReturn($response);

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE]);
		$allMySms->send($sms);
	}

	/* ===================== send() - Error Handling ===================== */

	public function testSendWithNullResponse(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new \RuntimeException('Connection failed'));

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with(self::stringContains('AllMySms API error'));

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			logger: $logger,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('AllMySms API error');

		$allMySms->send($sms);
	}

	public function testSendWithApiErrorStatus102(): void
	{
		$response = $this->createJsonResponse([
			'status' => 102,
			'message' => 'Problème de connexion – Aucun compte ne correspond aux clientcode et passcode spécifiés',
		]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($response);

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::exactly(2))
			->method('error');

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			logger: $logger,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('AllMySms API error (status 102)');

		$allMySms->send($sms);
	}

	public function testSendWithApiErrorStatus103(): void
	{
		$response = $this->createJsonResponse([
			'status' => 103,
			'message' => 'Crédit SMS épuisé. Veuillez re-créditer votre compte sur AllMySMS.com',
		]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($response);

		$logger = $this->createMock(LoggerInterface::class);

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			logger: $logger,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessageMatches('/AllMySms API error.*status 103/');

		$allMySms->send($sms);
	}

	public function testSendWithApiErrorStatusWithoutMessage(): void
	{
		$response = $this->createJsonResponse([
			'status' => 110,
		]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($response);

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::exactly(2))
			->method('error');

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			logger: $logger,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE]);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('AllMySms API error (status 110)');

		$allMySms->send($sms);
	}

	public function testSendWithNetworkException(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new \RuntimeException('Network timeout'));

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with(self::stringContains('Network timeout'));

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			logger: $logger,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE]);

		$this->expectException(\Exception::class);

		$allMySms->send($sms);
	}

	/* ===================== send() - Partial Failures ===================== */

	public function testSendWithMultipleRecipientsPartialFailure(): void
	{
		$recipients = ['+33612345678', '+33687654321'];

		$successResponse = $this->createJsonResponse(['status' => 100]);
		$failureResponse = $this->createJsonResponse([
			'status' => 103,
			'message' => 'Crédit SMS épuisé',
		]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::exactly(2))
			->method('sendRequest')
			->willReturnOnConsecutiveCalls($successResponse, $failureResponse);

		$logger = $this->createMock(LoggerInterface::class);

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			logger: $logger,
			httpClient: $httpClient
		);

		$sms = $this->createSms($recipients);

		// Should throw exception on second recipient
		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('Crédit SMS épuisé');

		$allMySms->send($sms);
	}

	/* ===================== send() - Edge Cases ===================== */

	public function testSendWithEmptyRecipientList(): void
	{
		$response = $this->createJsonResponse(['status' => 100]);

		$httpClient = $this->createMock(ClientInterface::class);
		// Should not be called since there are no recipients
		$httpClient->expects(self::never())
			->method('sendRequest');

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			httpClient: $httpClient
		);

		$sms = $this->createSms([]); // Empty recipients

		// Should not throw exception, just do nothing
		$allMySms->send($sms);
	}

	public function testSendWithLongMessage(): void
	{
		$longMessage = str_repeat('A', 500); // 500 characters (> 160 for standard SMS)

		$response = $this->createJsonResponse(['status' => 100]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function (RequestInterface $request) use ($longMessage) {
				$body = (string) $request->getBody();
				$data = json_decode($body, true);

				// Verify the long message is preserved in the request
				$this->assertEquals($longMessage, $data['text']);

				return true;
			}))
			->willReturn($response);

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE], self::TEST_SENDER, $longMessage);

		$allMySms->send($sms);
	}

	public function testSendWithSpecialCharacters(): void
	{
		$messageWithSpecialChars = "Hello! éàù ñ 中文 🎉";

		$response = $this->createJsonResponse(['status' => 100]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function (RequestInterface $request) use ($messageWithSpecialChars) {
				$body = (string) $request->getBody();
				$data = json_decode($body, true);

				// Verify special characters are preserved
				$this->assertEquals($messageWithSpecialChars, $data['text']);

				return true;
			}))
			->willReturn($response);

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE], self::TEST_SENDER, $messageWithSpecialChars);

		$allMySms->send($sms);
	}

	/* ===================== Logging ===================== */

	public function testSendLogsInfoOnSuccess(): void
	{
		$response = $this->createJsonResponse(['status' => 100, 'message' => 'Success']);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($response);

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('info')
			->with(
				'AllMySms API response',
				self::callback(function ($context) {
					return isset($context['response'])
						&& is_array($context['response'])
						&& $context['response']['status'] === 100;
				})
			);

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			logger: $logger,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE]);
		$allMySms->send($sms);
	}

	public function testSendLogsErrorOnApiError(): void
	{
		$response = $this->createJsonResponse([
			'status' => 104,
			'message' => 'Crédit insuffisant',
		]);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($response);

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::exactly(2))
			->method('error');

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			logger: $logger,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE]);

		try {
			$allMySms->send($sms);
			$this->fail('Should have thrown an exception');
		} catch (\Exception $e) {
			// Expected
		}
	}

	public function testSendLogsErrorOnException(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new \RuntimeException('Connection failed'));

		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with(self::stringContains('Connection failed'));

		$allMySms = new AllMySms(
			login: self::TEST_LOGIN,
			apiKey: self::TEST_API_KEY,
			logger: $logger,
			httpClient: $httpClient
		);

		$sms = $this->createSms([self::TEST_PHONE]);

		try {
			$allMySms->send($sms);
			$this->fail('Should have thrown an exception');
		} catch (\Exception $e) {
			// Expected
		}
	}
}