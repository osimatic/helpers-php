<?php

declare(strict_types=1);

namespace Tests\API;

use GuzzleHttp\Psr7\Response;
use Osimatic\API\Ekomi;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

final class EkomiTest extends TestCase
{
	private const string TEST_INTERFACE_ID = 'test-interface-123';
	private const string TEST_INTERFACE_PASSWORD = 'test-password-456';
	private const string TEST_ORDER_ID = 'ORDER-12345';

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
		return str_contains($uri, 'auth=' . self::TEST_INTERFACE_ID . '%7C' . self::TEST_INTERFACE_PASSWORD)
			&& str_contains($uri, 'version=cust-1.0.0')
			&& str_contains($uri, 'type=json');
	}

	/* ===================== Constants ===================== */

	public function testUrlConstant(): void
	{
		$this->assertSame('https://api.ekomi.de/v3/', Ekomi::API_URL);
	}

	public function testScriptVersionConstant(): void
	{
		$this->assertSame('1.0.0', Ekomi::SCRIPT_VERSION);
	}

	/* ===================== Constructor ===================== */

	public function testConstructorWithAllParameters(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$httpClient = $this->createMock(ClientInterface::class);
		$ekomi = new Ekomi('interface123', 'password123', logger: $logger, httpClient: $httpClient);

		self::assertInstanceOf(Ekomi::class, $ekomi);
	}

	public function testConstructorWithMinimalParameters(): void
	{
		$ekomi = new Ekomi();

		$this->assertInstanceOf(Ekomi::class, $ekomi);
	}

	public function testConstructorWithLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$ekomi = new Ekomi('interface123', 'password123', logger: $logger);

		$this->assertInstanceOf(Ekomi::class, $ekomi);
	}

	public function testConstructorWithHttpClientInjection(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		self::assertInstanceOf(Ekomi::class, $ekomi);
	}

	/* ===================== Setters ===================== */

	public function testSetInterfaceId(): void
	{
		$ekomi = new Ekomi();

		$result = $ekomi->setInterfaceId('newInterface123');

		$this->assertSame($ekomi, $result);
	}

	public function testSetInterfacePassword(): void
	{
		$ekomi = new Ekomi();

		$result = $ekomi->setInterfacePassword('newPassword123');

		$this->assertSame($ekomi, $result);
	}

	public function testFluentInterface(): void
	{
		$ekomi = new Ekomi();

		$result = $ekomi
			->setInterfaceId('interface123')
			->setInterfacePassword('password123');

		$this->assertSame($ekomi, $result);
	}

	/* ===================== getFeedbackLink() ===================== */

	public function testGetFeedbackLinkWithoutCredentials(): void
	{
		$ekomi = new Ekomi();

		$result = $ekomi->getFeedbackLink('ORDER123');

		$this->assertNull($result);
	}

	public function testGetFeedbackLinkWithoutInterfaceId(): void
	{
		$ekomi = new Ekomi(interfacePassword: 'password123');

		$result = $ekomi->getFeedbackLink('ORDER123');

		$this->assertNull($result);
	}

	public function testGetFeedbackLinkWithoutInterfacePassword(): void
	{
		$ekomi = new Ekomi(interfaceId: 'interface123');

		$result = $ekomi->getFeedbackLink('ORDER123');

		$this->assertNull($result);
	}

	public function testGetFeedbackLinkWithEmptyOrderId(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with('Order ID cannot be empty');

		$ekomi = new Ekomi('interface123', 'password123', logger: $logger);

		$result = $ekomi->getFeedbackLink('');

		$this->assertNull($result);
	}

	public function testGetFeedbackLinkWithZeroOrderId(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with('Order ID cannot be empty');

		$ekomi = new Ekomi('interface123', 'password123', logger: $logger);

		$result = $ekomi->getFeedbackLink(0);

		$this->assertNull($result);
	}

	public function testGetFeedbackLinkWithValidParameters(): void
	{
		// Create mock ClientInterface that returns a simulated API response
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'link' => 'https://ekomi.example.com/feedback/ORDER123',
				'status' => 'success'
			]));

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getFeedbackLink('ORDER123');

		$this->assertNotNull($result);
		$this->assertSame('https://ekomi.example.com/feedback/ORDER123', $result);
	}

	public function testGetFeedbackLinkReturnsNullWhenNoLink(): void
	{
		// Create mock ClientInterface that returns a response without 'link' key
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'status' => 'success',
				'message' => 'No link available'
			]));

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getFeedbackLink('ORDER123');

		$this->assertNull($result);
	}

	public function testGetFeedbackLinkWithNetworkException(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new class('Network error') extends \RuntimeException implements ClientExceptionInterface {});

		$ekomi = new Ekomi(self::TEST_INTERFACE_ID, self::TEST_INTERFACE_PASSWORD, httpClient: $httpClient);

		$result = $ekomi->getFeedbackLink(self::TEST_ORDER_ID);

		self::assertNull($result);
	}

	public function testGetFeedbackLinkVerifiesRequestUrlContainsOrderId(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) {
				$uri = (string) $request->getUri();
				return str_contains($uri, 'putOrder?order_id=' . self::TEST_ORDER_ID)
					&& $this->assertRequestHasAuth($request);
			}))
			->willReturn($this->createJsonResponse(['link' => 'https://ekomi.example.com/feedback']));

		$ekomi = new Ekomi(self::TEST_INTERFACE_ID, self::TEST_INTERFACE_PASSWORD, httpClient: $httpClient);

		$result = $ekomi->getFeedbackLink(self::TEST_ORDER_ID);

		self::assertNotNull($result);
	}

	public function testGetFeedbackLinkWithSpecialCharactersInOrderId(): void
	{
		$orderId = 'ORDER-2025/01#123';
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) use ($orderId) {
				$uri = (string) $request->getUri();
				return str_contains($uri, 'putOrder?order_id=' . $orderId);
			}))
			->willReturn($this->createJsonResponse(['link' => 'https://ekomi.example.com/feedback']));

		$ekomi = new Ekomi(self::TEST_INTERFACE_ID, self::TEST_INTERFACE_PASSWORD, httpClient: $httpClient);

		$result = $ekomi->getFeedbackLink($orderId);

		self::assertNotNull($result);
	}

	public function testGetFeedbackLinkWithIntegerOrderId(): void
	{
		$orderId = 12345;
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) use ($orderId) {
				$uri = (string) $request->getUri();
				return str_contains($uri, 'putOrder?order_id=' . $orderId);
			}))
			->willReturn($this->createJsonResponse(['link' => 'https://ekomi.example.com/feedback/' . $orderId]));

		$ekomi = new Ekomi(self::TEST_INTERFACE_ID, self::TEST_INTERFACE_PASSWORD, httpClient: $httpClient);

		$result = $ekomi->getFeedbackLink($orderId);

		self::assertNotNull($result);
		self::assertSame('https://ekomi.example.com/feedback/' . $orderId, $result);
	}

	public function testGetFeedbackLinkLogsCredentialError(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('eKomi API credentials are not configured. Please set interfaceId and interfacePassword.');

		$ekomi = new Ekomi(logger: $logger);

		$result = $ekomi->getFeedbackLink(self::TEST_ORDER_ID);

		self::assertNull($result);
	}

	/* ===================== getListFeedback() ===================== */

	public function testGetListFeedbackWithoutCredentials(): void
	{
		$ekomi = new Ekomi();

		$result = $ekomi->getListFeedback();

		$this->assertNull($result);
	}

	public function testGetListFeedbackWithInvalidRange(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with($this->stringContains('Invalid range parameter'));

		$ekomi = new Ekomi('interface123', 'password123', logger: $logger);

		$result = $ekomi->getListFeedback('invalid_range');

		$this->assertNull($result);
	}

	#[DataProvider('validRangeProvider')]
	public function testGetListFeedbackWithValidRanges(string $range): void
	{
		// Mock ClientInterface to test each valid range
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'feedbacks' => [
					[
						'order_id' => 'TEST_ORDER',
						'rating' => 5,
						'comment' => 'Test feedback for ' . $range,
					]
				],
				'range' => $range
			]));

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getListFeedback($range);

		$this->assertNotNull($result);
		$this->assertIsArray($result);
		$this->assertArrayHasKey('feedbacks', $result);
		$this->assertArrayHasKey('range', $result);
		$this->assertSame($range, $result['range']);
	}

	public static function validRangeProvider(): array
	{
		return [
			['all'],
			['month'],
			['week'],
			['day'],
			['year'],
		];
	}

	public function testGetListFeedbackDefaultRange(): void
	{
		// Mock ClientInterface to test default range behavior
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'feedbacks' => [
					[
						'order_id' => 'DEFAULT_TEST',
						'rating' => 4,
					]
				],
				'total' => 1
			]));

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getListFeedback(); // No parameter = default 'all'

		$this->assertNotNull($result);
		$this->assertIsArray($result);
		$this->assertArrayHasKey('feedbacks', $result);
		$this->assertSame(1, $result['total']);
	}

	public function testGetListFeedbackWithValidCredentials(): void
	{
		// Create mock ClientInterface that returns a simulated API response
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'feedbacks' => [
					[
						'order_id' => 'ORDER123',
						'rating' => 5,
						'comment' => 'Great product!',
						'date' => '2024-01-15'
					],
					[
						'order_id' => 'ORDER456',
						'rating' => 4,
						'comment' => 'Good quality',
						'date' => '2024-01-14'
					]
				],
				'total' => 2,
				'status' => 'success'
			]));

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getListFeedback('all');

		$this->assertNotNull($result);
		$this->assertIsArray($result);
		$this->assertArrayHasKey('feedbacks', $result);
		$this->assertCount(2, $result['feedbacks']);
		$this->assertSame('ORDER123', $result['feedbacks'][0]['order_id']);
		$this->assertSame(5, $result['feedbacks'][0]['rating']);
	}

	public function testGetListFeedbackWithNetworkException(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new class('Network error') extends \RuntimeException implements ClientExceptionInterface {});

		$ekomi = new Ekomi(self::TEST_INTERFACE_ID, self::TEST_INTERFACE_PASSWORD, httpClient: $httpClient);

		$result = $ekomi->getListFeedback('month');

		self::assertNull($result);
	}

	public function testGetListFeedbackVerifiesRequestUrlContainsRange(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) {
				$uri = (string) $request->getUri();
				return str_contains($uri, 'getFeedback?range=week')
					&& $this->assertRequestHasAuth($request);
			}))
			->willReturn($this->createJsonResponse(['feedbacks' => []]));

		$ekomi = new Ekomi(self::TEST_INTERFACE_ID, self::TEST_INTERFACE_PASSWORD, httpClient: $httpClient);

		$result = $ekomi->getListFeedback('week');

		self::assertNotNull($result);
	}

	public function testGetListFeedbackWithDifferentRanges(): void
	{
		// Test with 'week' range
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'feedbacks' => [],
				'total' => 0
			]));

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getListFeedback('week');

		$this->assertNotNull($result);
		$this->assertIsArray($result);
		$this->assertArrayHasKey('feedbacks', $result);
		$this->assertEmpty($result['feedbacks']);
	}

	/* ===================== getAverage() ===================== */

	public function testGetAverageWithoutCredentials(): void
	{
		$ekomi = new Ekomi();

		$result = $ekomi->getAverage();

		$this->assertNull($result);
	}

	public function testGetAverageWithoutInterfaceId(): void
	{
		$ekomi = new Ekomi(interfacePassword: 'password123');

		$result = $ekomi->getAverage();

		$this->assertNull($result);
	}

	public function testGetAverageWithoutInterfacePassword(): void
	{
		$ekomi = new Ekomi(interfaceId: 'interface123');

		$result = $ekomi->getAverage();

		$this->assertNull($result);
	}

	public function testGetAverageWithValidCredentials(): void
	{
		// Create mock ClientInterface that returns a simulated API response
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'info' => [
					'fb_avg' => 4.5,
					'fb_count' => 150
				],
				'status' => 'success'
			]));

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getAverage();

		$this->assertNotNull($result);
		$this->assertIsArray($result);
		$this->assertCount(2, $result);
		$this->assertSame(4.5, $result[0]); // average rating
		$this->assertSame(150, $result[1]); // feedback count
	}

	public function testGetAverageWithNetworkException(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new class('Network error') extends \RuntimeException implements ClientExceptionInterface {});

		$ekomi = new Ekomi(self::TEST_INTERFACE_ID, self::TEST_INTERFACE_PASSWORD, httpClient: $httpClient);

		$result = $ekomi->getAverage();

		self::assertNull($result);
	}

	public function testGetAverageVerifiesRequestUrlContainsSnapshot(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) {
				$uri = (string) $request->getUri();
				return str_contains($uri, 'getSnapshot?range=all')
					&& $this->assertRequestHasAuth($request);
			}))
			->willReturn($this->createJsonResponse(['info' => ['fb_avg' => 4.0, 'fb_count' => 100]]));

		$ekomi = new Ekomi(self::TEST_INTERFACE_ID, self::TEST_INTERFACE_PASSWORD, httpClient: $httpClient);

		$result = $ekomi->getAverage();

		self::assertNotNull($result);
	}

	public function testGetAverageWithDifferentValues(): void
	{
		// Test with different average and count values
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'info' => [
					'fb_avg' => 3.8,
					'fb_count' => 42
				]
			]));

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getAverage();

		$this->assertNotNull($result);
		$this->assertSame(3.8, $result[0]);
		$this->assertSame(42, $result[1]);
	}

	/* ===================== Credentials Validation ===================== */

	public function testCredentialsValidationWithEmptyInterfaceId(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with('eKomi API credentials are not configured. Please set interfaceId and interfacePassword.');

		$ekomi = new Ekomi('', 'password123', logger: $logger);

		$result = $ekomi->getAverage();

		$this->assertNull($result);
	}

	public function testCredentialsValidationWithEmptyInterfacePassword(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with('eKomi API credentials are not configured. Please set interfaceId and interfacePassword.');

		$ekomi = new Ekomi('interface123', '', logger: $logger);

		$result = $ekomi->getAverage();

		$this->assertNull($result);
	}

	public function testCredentialsValidationWithBothEmpty(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects($this->once())
			->method('error')
			->with('eKomi API credentials are not configured. Please set interfaceId and interfacePassword.');

		$ekomi = new Ekomi('', '', logger: $logger);

		$result = $ekomi->getAverage();

		$this->assertNull($result);
	}

	/* ===================== Method Chaining ===================== */

	public function testCompleteWorkflow(): void
	{
		// Mock ClientInterface to simulate successful API calls
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects($this->exactly(3))
			->method('sendRequest')
			->willReturnOnConsecutiveCalls(
				// First call: getFeedbackLink
				$this->createJsonResponse([
					'link' => 'https://ekomi.example.com/feedback/ORDER123',
					'status' => 'success'
				]),
				// Second call: getListFeedback
				$this->createJsonResponse([
					'feedbacks' => [
						['order_id' => 'ORDER123', 'rating' => 5]
					],
					'total' => 1
				]),
				// Third call: getAverage
				$this->createJsonResponse([
					'info' => [
						'fb_avg' => 4.5,
						'fb_count' => 100
					]
				])
			);

		$ekomi = new Ekomi(httpClient: $httpClient);

		// Test method chaining
		$ekomi
			->setInterfaceId('interface123')
			->setInterfacePassword('password123');

		// Test that all methods work correctly with mocked API
		$feedbackLink = $ekomi->getFeedbackLink('ORDER123');
		$this->assertNotNull($feedbackLink);
		$this->assertSame('https://ekomi.example.com/feedback/ORDER123', $feedbackLink);

		$listFeedback = $ekomi->getListFeedback('all');
		$this->assertNotNull($listFeedback);
		$this->assertArrayHasKey('feedbacks', $listFeedback);
		$this->assertSame(1, $listFeedback['total']);

		$average = $ekomi->getAverage();
		$this->assertNotNull($average);
		$this->assertIsArray($average);
		$this->assertSame(4.5, $average[0]);
		$this->assertSame(100, $average[1]);
	}
}
