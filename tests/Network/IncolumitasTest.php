<?php

declare(strict_types=1);

namespace Tests\Network;

use GuzzleHttp\Psr7\Response;
use Osimatic\Network\Incolumitas;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

final class IncolumitasTest extends TestCase
{
	private const string TEST_IP_ADDRESS = '8.8.8.8';
	private const string TEST_IPV6_ADDRESS = '2001:4860:4860::8888';

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
	 * Helper method to create a typical Incolumitas API response
	 * @param string $ipAddress IP address
	 * @param bool $isVpn Whether IP is a VPN
	 * @param string $countryCode Country code
	 * @param int|null $asn ASN number
	 * @return array API response structure
	 */
	private function createIncolumitasResponse(
		string $ipAddress,
		bool $isVpn = false,
		string $countryCode = 'US',
		?int $asn = 15169
	): array {
		return [
			'ip' => $ipAddress,
			'is_vpn' => $isVpn,
			'is_datacenter' => false,
			'is_tor' => false,
			'is_proxy' => false,
			'is_hosting' => false,
			'location' => [
				'country_code' => $countryCode,
				'country' => 'United States',
				'city' => 'Mountain View',
				'latitude' => 37.386,
				'longitude' => -122.0838,
			],
			'asn' => [
				'asn' => $asn,
				'org' => 'Google LLC',
				'route' => '8.8.8.0/24',
			]
		];
	}

	/* ===================== Constants ===================== */

	public function testApiUrlConstant(): void
	{
		self::assertSame('https://api.incolumitas.com/', Incolumitas::API_URL);
	}

	/* ===================== Constructor ===================== */

	public function testConstructorWithAllParameters(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$httpClient = $this->createMock(ClientInterface::class);

		$incolumitas = new Incolumitas(logger: $logger, httpClient: $httpClient);

		self::assertInstanceOf(Incolumitas::class, $incolumitas);
	}

	public function testConstructorWithMinimalParameters(): void
	{
		$incolumitas = new Incolumitas();

		self::assertInstanceOf(Incolumitas::class, $incolumitas);
	}

	public function testConstructorWithLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$incolumitas = new Incolumitas(logger: $logger);

		self::assertInstanceOf(Incolumitas::class, $incolumitas);
	}

	public function testConstructorWithHttpClientInjection(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$incolumitas = new Incolumitas(httpClient: $httpClient);

		self::assertInstanceOf(Incolumitas::class, $incolumitas);
	}

	/* ===================== getIpInfos() ===================== */

	public function testGetIpInfosWithValidIp(): void
	{
		$responseData = $this->createIncolumitasResponse(self::TEST_IP_ADDRESS);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$incolumitas = new Incolumitas(httpClient: $httpClient);

		$result = $incolumitas->getIpInfos(self::TEST_IP_ADDRESS);

		self::assertIsArray($result);
		self::assertSame(self::TEST_IP_ADDRESS, $result['ip']);
	}

	public function testGetIpInfosVerifiesRequestUrl(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) {
				$uri = (string) $request->getUri();
				return str_contains($uri, 'api.incolumitas.com')
					&& str_contains($uri, '?q=' . self::TEST_IP_ADDRESS)
					&& $request->getMethod() === 'GET';
			}))
			->willReturn($this->createJsonResponse($this->createIncolumitasResponse(self::TEST_IP_ADDRESS)));

		$incolumitas = new Incolumitas(httpClient: $httpClient);

		$incolumitas->getIpInfos(self::TEST_IP_ADDRESS);
	}

	public function testGetIpInfosWithIpv6Address(): void
	{
		$responseData = $this->createIncolumitasResponse(self::TEST_IPV6_ADDRESS);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$incolumitas = new Incolumitas(httpClient: $httpClient);

		$result = $incolumitas->getIpInfos(self::TEST_IPV6_ADDRESS);

		self::assertIsArray($result);
		self::assertSame(self::TEST_IPV6_ADDRESS, $result['ip']);
	}

	public function testGetIpInfosReturnsNullOnNetworkException(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new class('Network error') extends \RuntimeException implements ClientExceptionInterface {});

		$incolumitas = new Incolumitas(httpClient: $httpClient);

		$result = $incolumitas->getIpInfos(self::TEST_IP_ADDRESS);

		self::assertNull($result);
	}

	public function testGetIpInfosReturnsErrorOnApiError(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse(['error' => 'Invalid IP'], 400));

		$incolumitas = new Incolumitas(httpClient: $httpClient);

		$result = $incolumitas->getIpInfos('invalid-ip');

		self::assertIsArray($result);
		self::assertArrayHasKey('error', $result);
	}

	public function testGetIpInfosWithPrivateIp(): void
	{
		$privateIp = '192.168.1.1';
		$responseData = $this->createIncolumitasResponse($privateIp, false, 'PRIVATE');

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$incolumitas = new Incolumitas(httpClient: $httpClient);

		$result = $incolumitas->getIpInfos($privateIp);

		self::assertIsArray($result);
		self::assertSame($privateIp, $result['ip']);
	}

	/* ===================== Static method: isVpn() ===================== */

	public function testIsVpnReturnsTrueWhenVpnDetected(): void
	{
		$result = ['is_vpn' => true];

		self::assertTrue(Incolumitas::isVpn($result));
	}

	public function testIsVpnReturnsFalseWhenNoVpnDetected(): void
	{
		$result = ['is_vpn' => false];

		self::assertFalse(Incolumitas::isVpn($result));
	}

	public function testIsVpnReturnsFalseWhenKeyMissing(): void
	{
		$result = [];

		self::assertFalse(Incolumitas::isVpn($result));
	}

	public function testIsVpnReturnsFalseWhenKeyIsNull(): void
	{
		$result = ['is_vpn' => null];

		self::assertFalse(Incolumitas::isVpn($result));
	}

	/* ===================== Static method: getAsn() ===================== */

	public function testGetAsnReturnsAsnNumber(): void
	{
		$result = ['asn' => ['asn' => 15169]];

		$asn = Incolumitas::getAsn($result);

		self::assertSame(15169, $asn);
	}

	public function testGetAsnReturnsNullWhenAsnMissing(): void
	{
		$result = [];

		$asn = Incolumitas::getAsn($result);

		self::assertNull($asn);
	}

	public function testGetAsnReturnsNullWhenAsnArrayMissing(): void
	{
		$result = ['asn' => []];

		$asn = Incolumitas::getAsn($result);

		self::assertNull($asn);
	}

	public function testGetAsnReturnsNullWhenAsnIsNull(): void
	{
		$result = ['asn' => ['asn' => null]];

		$asn = Incolumitas::getAsn($result);

		self::assertNull($asn);
	}

	public function testGetAsnWithLargeAsnNumber(): void
	{
		$result = ['asn' => ['asn' => 4294967295]]; // Max 32-bit unsigned int

		$asn = Incolumitas::getAsn($result);

		self::assertSame(4294967295, $asn);
	}

	public function testGetAsnWithZero(): void
	{
		$result = ['asn' => ['asn' => 0]];

		$asn = Incolumitas::getAsn($result);

		self::assertSame(0, $asn);
	}

	/* ===================== Static method: getCountryCode() ===================== */

	public function testGetCountryCodeReturnsUppercaseCode(): void
	{
		$result = ['location' => ['country_code' => 'us']];

		$countryCode = Incolumitas::getCountryCode($result);

		self::assertSame('US', $countryCode);
	}

	public function testGetCountryCodeReturnsNullWhenLocationMissing(): void
	{
		$result = [];

		$countryCode = Incolumitas::getCountryCode($result);

		self::assertNull($countryCode);
	}

	public function testGetCountryCodeReturnsNullWhenCountryCodeMissing(): void
	{
		$result = ['location' => []];

		$countryCode = Incolumitas::getCountryCode($result);

		self::assertNull($countryCode);
	}

	public function testGetCountryCodeReturnsNullWhenCountryCodeEmpty(): void
	{
		$result = ['location' => ['country_code' => '']];

		$countryCode = Incolumitas::getCountryCode($result);

		self::assertNull($countryCode);
	}

	public function testGetCountryCodeReturnsNullWhenCountryCodeIsNull(): void
	{
		$result = ['location' => ['country_code' => null]];

		$countryCode = Incolumitas::getCountryCode($result);

		self::assertNull($countryCode);
	}

	public function testGetCountryCodeWithAlreadyUppercase(): void
	{
		$result = ['location' => ['country_code' => 'FR']];

		$countryCode = Incolumitas::getCountryCode($result);

		self::assertSame('FR', $countryCode);
	}

	public function testGetCountryCodeWithMixedCase(): void
	{
		$result = ['location' => ['country_code' => 'De']];

		$countryCode = Incolumitas::getCountryCode($result);

		self::assertSame('DE', $countryCode);
	}

	/* ===================== Integration scenarios ===================== */

	public function testCompleteWorkflowWithVpnDetection(): void
	{
		$responseData = $this->createIncolumitasResponse('1.2.3.4', true, 'US', 12345);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$incolumitas = new Incolumitas(httpClient: $httpClient);

		$result = $incolumitas->getIpInfos('1.2.3.4');

		self::assertIsArray($result);
		self::assertTrue(Incolumitas::isVpn($result));
		self::assertSame(12345, Incolumitas::getAsn($result));
		self::assertSame('US', Incolumitas::getCountryCode($result));
	}

	public function testCompleteWorkflowWithNonVpnIp(): void
	{
		$responseData = $this->createIncolumitasResponse(self::TEST_IP_ADDRESS, false, 'FR', 15169);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$incolumitas = new Incolumitas(httpClient: $httpClient);

		$result = $incolumitas->getIpInfos(self::TEST_IP_ADDRESS);

		self::assertIsArray($result);
		self::assertFalse(Incolumitas::isVpn($result));
		self::assertSame(15169, Incolumitas::getAsn($result));
		self::assertSame('FR', Incolumitas::getCountryCode($result));
	}

	/* ===================== Multiple operations ===================== */

	public function testMultipleIpLookups(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::exactly(3))
			->method('sendRequest')
			->willReturnOnConsecutiveCalls(
				$this->createJsonResponse($this->createIncolumitasResponse('1.1.1.1', false, 'US')),
				$this->createJsonResponse($this->createIncolumitasResponse('8.8.8.8', false, 'US')),
				$this->createJsonResponse($this->createIncolumitasResponse('9.9.9.9', false, 'US'))
			);

		$incolumitas = new Incolumitas(httpClient: $httpClient);

		$result1 = $incolumitas->getIpInfos('1.1.1.1');
		$result2 = $incolumitas->getIpInfos('8.8.8.8');
		$result3 = $incolumitas->getIpInfos('9.9.9.9');

		self::assertIsArray($result1);
		self::assertIsArray($result2);
		self::assertIsArray($result3);
		self::assertSame('1.1.1.1', $result1['ip']);
		self::assertSame('8.8.8.8', $result2['ip']);
		self::assertSame('9.9.9.9', $result3['ip']);
	}

	/* ===================== Edge cases ===================== */

	public function testGetIpInfosWithLoopbackAddress(): void
	{
		$loopbackIp = '127.0.0.1';
		$responseData = $this->createIncolumitasResponse($loopbackIp, false, 'LOOPBACK', null);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$incolumitas = new Incolumitas(httpClient: $httpClient);

		$result = $incolumitas->getIpInfos($loopbackIp);

		self::assertIsArray($result);
		self::assertSame($loopbackIp, $result['ip']);
	}

	public function testGetIpInfosWithEmptyResponse(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([]));

		$incolumitas = new Incolumitas(httpClient: $httpClient);

		$result = $incolumitas->getIpInfos(self::TEST_IP_ADDRESS);

		self::assertIsArray($result);
		self::assertEmpty($result);
	}

	public function testGetIpInfosWithPartialResponse(): void
	{
		$partialResponse = [
			'ip' => self::TEST_IP_ADDRESS,
			'is_vpn' => false,
			// Missing location and asn data
		];

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($partialResponse));

		$incolumitas = new Incolumitas(httpClient: $httpClient);

		$result = $incolumitas->getIpInfos(self::TEST_IP_ADDRESS);

		self::assertIsArray($result);
		self::assertFalse(Incolumitas::isVpn($result));
		self::assertNull(Incolumitas::getAsn($result));
		self::assertNull(Incolumitas::getCountryCode($result));
	}

	/* ===================== Logger integration ===================== */

	public function testConstructorWithLoggerDoesNotThrow(): void
	{
		$logger = $this->createMock(LoggerInterface::class);

		$incolumitas = new Incolumitas(logger: $logger);

		self::assertInstanceOf(Incolumitas::class, $incolumitas);
	}

	public function testGetIpInfosLogsOnException(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error');

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new class('Network error') extends \RuntimeException implements ClientExceptionInterface {});

		$incolumitas = new Incolumitas(logger: $logger, httpClient: $httpClient);

		$result = $incolumitas->getIpInfos(self::TEST_IP_ADDRESS);

		self::assertNull($result);
	}
}