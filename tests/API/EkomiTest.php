<?php

declare(strict_types=1);

namespace Tests\API;

use Osimatic\API\Ekomi;
use Osimatic\Network\HTTPClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class EkomiTest extends TestCase
{
	/**
	 * Helper method to create an HTTPClient instance for testing
	 */
	private function createHttpClient(): HTTPClient
	{
		return new HTTPClient();
	}

	/* ===================== Constants ===================== */

	public function testUrlConstant(): void
	{
		$this->assertSame('https://api.ekomi.de/v3/', Ekomi::URL);
	}

	public function testScriptVersionConstant(): void
	{
		$this->assertSame('1.0.0', Ekomi::SCRIPT_VERSION);
	}

	/* ===================== Constructor ===================== */

	public function testConstructorWithAllParameters(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$httpClient = $this->createHttpClient();
		$ekomi = new Ekomi('interface123', 'password123', logger: $logger, httpClient: $httpClient);

		$this->assertInstanceOf(Ekomi::class, $ekomi);
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
		$httpClient = $this->createHttpClient();
		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$this->assertInstanceOf(Ekomi::class, $ekomi);
	}

	/* ===================== Setters ===================== */

	public function testSetLogger(): void
	{
		$ekomi = new Ekomi();
		$logger = $this->createMock(LoggerInterface::class);

		$result = $ekomi->setLogger($logger);

		$this->assertSame($ekomi, $result);
	}

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
		$logger = $this->createMock(LoggerInterface::class);
		$ekomi = new Ekomi();

		$result = $ekomi
			->setLogger($logger)
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
		// Create mock HTTPClient that returns a simulated API response
		$httpClient = $this->createMock(HTTPClient::class);
		$httpClient->expects($this->once())
			->method('jsonRequest')
			->with(
				$this->anything(), // HTTPMethod::GET
				$this->stringContains('putOrder?order_id=ORDER123'),
				$this->anything()  // queryData
			)
			->willReturn([
				'link' => 'https://ekomi.example.com/feedback/ORDER123',
				'status' => 'success'
			]);

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getFeedbackLink('ORDER123');

		$this->assertNotNull($result);
		$this->assertSame('https://ekomi.example.com/feedback/ORDER123', $result);
	}

	public function testGetFeedbackLinkReturnsNullWhenNoLink(): void
	{
		// Create mock HTTPClient that returns a response without 'link' key
		$httpClient = $this->createMock(HTTPClient::class);
		$httpClient->expects($this->once())
			->method('jsonRequest')
			->willReturn([
				'status' => 'success',
				'message' => 'No link available'
			]);

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getFeedbackLink('ORDER123');

		$this->assertNull($result);
	}

	public function testGetFeedbackLinkReturnsNullWhenHttpClientReturnsNull(): void
	{
		// Create mock HTTPClient that returns null (simulating API failure)
		$httpClient = $this->createMock(HTTPClient::class);
		$httpClient->expects($this->once())
			->method('jsonRequest')
			->willReturn(null);

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getFeedbackLink('ORDER123');

		$this->assertNull($result);
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
		// Mock HTTPClient to test each valid range
		$httpClient = $this->createMock(HTTPClient::class);
		$httpClient->expects($this->once())
			->method('jsonRequest')
			->with(
				$this->anything(),
				$this->stringContains('getFeedback?range=' . $range),
				$this->anything()
			)
			->willReturn([
				'feedbacks' => [
					[
						'order_id' => 'TEST_ORDER',
						'rating' => 5,
						'comment' => 'Test feedback for ' . $range,
					]
				],
				'range' => $range
			]);

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
		// Mock HTTPClient to test default range behavior
		$httpClient = $this->createMock(HTTPClient::class);
		$httpClient->expects($this->once())
			->method('jsonRequest')
			->with(
				$this->anything(),
				$this->stringContains('getFeedback?range=all'), // default is 'all'
				$this->anything()
			)
			->willReturn([
				'feedbacks' => [
					[
						'order_id' => 'DEFAULT_TEST',
						'rating' => 4,
					]
				],
				'total' => 1
			]);

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getListFeedback(); // No parameter = default 'all'

		$this->assertNotNull($result);
		$this->assertIsArray($result);
		$this->assertArrayHasKey('feedbacks', $result);
		$this->assertSame(1, $result['total']);
	}

	public function testGetListFeedbackWithValidCredentials(): void
	{
		// Create mock HTTPClient that returns a simulated API response
		$httpClient = $this->createMock(HTTPClient::class);
		$httpClient->expects($this->once())
			->method('jsonRequest')
			->with(
				$this->anything(), // HTTPMethod::GET
				$this->stringContains('getFeedback?range=all'),
				$this->anything()  // queryData
			)
			->willReturn([
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
			]);

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getListFeedback('all');

		$this->assertNotNull($result);
		$this->assertIsArray($result);
		$this->assertArrayHasKey('feedbacks', $result);
		$this->assertCount(2, $result['feedbacks']);
		$this->assertSame('ORDER123', $result['feedbacks'][0]['order_id']);
		$this->assertSame(5, $result['feedbacks'][0]['rating']);
	}

	public function testGetListFeedbackReturnsNullWhenHttpClientReturnsNull(): void
	{
		// Create mock HTTPClient that returns null (simulating API failure)
		$httpClient = $this->createMock(HTTPClient::class);
		$httpClient->expects($this->once())
			->method('jsonRequest')
			->willReturn(null);

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getListFeedback('month');

		$this->assertNull($result);
	}

	public function testGetListFeedbackWithDifferentRanges(): void
	{
		// Test with 'week' range
		$httpClient = $this->createMock(HTTPClient::class);
		$httpClient->expects($this->once())
			->method('jsonRequest')
			->with(
				$this->anything(),
				$this->stringContains('getFeedback?range=week'),
				$this->anything()
			)
			->willReturn([
				'feedbacks' => [],
				'total' => 0
			]);

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
		// Create mock HTTPClient that returns a simulated API response
		$httpClient = $this->createMock(HTTPClient::class);
		$httpClient->expects($this->once())
			->method('jsonRequest')
			->with(
				$this->anything(), // HTTPMethod::GET
				$this->stringContains('getSnapshot?range=all'),
				$this->anything()  // queryData
			)
			->willReturn([
				'info' => [
					'fb_avg' => 4.5,
					'fb_count' => 150
				],
				'status' => 'success'
			]);

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getAverage();

		$this->assertNotNull($result);
		$this->assertIsArray($result);
		$this->assertCount(2, $result);
		$this->assertSame(4.5, $result[0]); // average rating
		$this->assertSame(150, $result[1]); // feedback count
	}

	public function testGetAverageReturnsNullWhenHttpClientReturnsNull(): void
	{
		// Create mock HTTPClient that returns null (simulating API failure)
		$httpClient = $this->createMock(HTTPClient::class);
		$httpClient->expects($this->once())
			->method('jsonRequest')
			->willReturn(null);

		$ekomi = new Ekomi('interface123', 'password123', httpClient: $httpClient);

		$result = $ekomi->getAverage();

		$this->assertNull($result);
	}

	public function testGetAverageWithDifferentValues(): void
	{
		// Test with different average and count values
		$httpClient = $this->createMock(HTTPClient::class);
		$httpClient->expects($this->once())
			->method('jsonRequest')
			->willReturn([
				'info' => [
					'fb_avg' => 3.8,
					'fb_count' => 42
				]
			]);

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
		// Mock HTTPClient to simulate successful API calls
		$httpClient = $this->createMock(HTTPClient::class);
		$httpClient->expects($this->exactly(3))
			->method('jsonRequest')
			->willReturnOnConsecutiveCalls(
				// First call: getFeedbackLink
				[
					'link' => 'https://ekomi.example.com/feedback/ORDER123',
					'status' => 'success'
				],
				// Second call: getListFeedback
				[
					'feedbacks' => [
						['order_id' => 'ORDER123', 'rating' => 5]
					],
					'total' => 1
				],
				// Third call: getAverage
				[
					'info' => [
						'fb_avg' => 4.5,
						'fb_count' => 100
					]
				]
			);

		$logger = $this->createMock(LoggerInterface::class);
		$ekomi = new Ekomi(httpClient: $httpClient);

		// Test method chaining
		$ekomi
			->setLogger($logger)
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
