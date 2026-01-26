<?php

declare(strict_types=1);

namespace Tests\Network;

use GuzzleHttp\Psr7\Response;
use Osimatic\Network\VPNAPI;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

final class VPNAPITest extends TestCase
{
	private const string TEST_API_KEY = 'test-vpnapi-key-12345';
	private const string TEST_IP_ADDRESS = '8.8.8.8';
	private const string TEST_VPN_IP = '185.220.101.1';

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
	 * Helper method to create a standard VPNAPI response
	 * @param string $ipAddress The IP address
	 * @param bool $isVpn Whether the IP is a VPN
	 * @param string $countryCode Country code
	 * @return array The API response array
	 */
	private function createVPNAPIResponse(string $ipAddress, bool $isVpn = false, string $countryCode = 'US'): array
	{
		return [
			'ip' => $ipAddress,
			'security' => [
				'vpn' => $isVpn,
				'proxy' => false,
				'tor' => false,
				'relay' => false
			],
			'location' => [
				'city' => 'Mountain View',
				'region' => 'California',
				'country' => 'United States',
				'country_code' => $countryCode,
				'continent' => 'North America',
				'continent_code' => 'NA',
				'latitude' => 37.386,
				'longitude' => -122.0838,
				'time_zone' => 'America/Los_Angeles',
				'locale_code' => 'en',
				'metro_code' => '807',
				'is_in_european_union' => false
			],
			'network' => [
				'network' => '8.8.8.0/24',
				'autonomous_system_number' => 15169,
				'autonomous_system_organization' => 'Google LLC'
			]
		];
	}

	/* ===================== Constants ===================== */

	public function testApiUrlConstant(): void
	{
		self::assertSame('https://vpnapi.io/', VPNAPI::API_URL);
	}

	/* ===================== Constructor ===================== */

	public function testConstructorWithAllParameters(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$httpClient = $this->createMock(ClientInterface::class);
		$vpnapi = new VPNAPI(self::TEST_API_KEY, logger: $logger, httpClient: $httpClient);

		self::assertInstanceOf(VPNAPI::class, $vpnapi);
	}

	public function testConstructorWithMinimalParameters(): void
	{
		$vpnapi = new VPNAPI();

		self::assertInstanceOf(VPNAPI::class, $vpnapi);
	}

	public function testConstructorWithLogger(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$vpnapi = new VPNAPI(self::TEST_API_KEY, logger: $logger);

		self::assertInstanceOf(VPNAPI::class, $vpnapi);
	}

	public function testConstructorWithHttpClientInjection(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$vpnapi = new VPNAPI(self::TEST_API_KEY, httpClient: $httpClient);

		self::assertInstanceOf(VPNAPI::class, $vpnapi);
	}

	/* ===================== Setters ===================== */

	public function testSetKey(): void
	{
		$vpnapi = new VPNAPI();

		$result = $vpnapi->setKey('new-api-key');

		self::assertSame($vpnapi, $result);
	}

	public function testFluentInterface(): void
	{
		$vpnapi = new VPNAPI();

		$result = $vpnapi->setKey(self::TEST_API_KEY);

		self::assertSame($vpnapi, $result);
	}

	/* ===================== getIpInfos() ===================== */

	public function testGetIpInfosWithoutApiKey(): void
	{
		$vpnapi = new VPNAPI();

		$result = $vpnapi->getIpInfos(self::TEST_IP_ADDRESS);

		self::assertNull($result);
	}

	public function testGetIpInfosWithValidApiKey(): void
	{
		$responseData = $this->createVPNAPIResponse(self::TEST_IP_ADDRESS);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$vpnapi = new VPNAPI(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $vpnapi->getIpInfos(self::TEST_IP_ADDRESS);

		self::assertNotNull($result);
		self::assertIsArray($result);
		self::assertArrayHasKey('ip', $result);
		self::assertArrayHasKey('security', $result);
		self::assertArrayHasKey('location', $result);
	}

	public function testGetIpInfosVerifiesRequestUrl(): void
	{
		$responseData = $this->createVPNAPIResponse(self::TEST_IP_ADDRESS);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) {
				$uri = (string) $request->getUri();
				return str_contains($uri, 'vpnapi.io/api/' . self::TEST_IP_ADDRESS)
					&& str_contains($uri, 'key=' . self::TEST_API_KEY);
			}))
			->willReturn($this->createJsonResponse($responseData));

		$vpnapi = new VPNAPI(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $vpnapi->getIpInfos(self::TEST_IP_ADDRESS);

		self::assertNotNull($result);
	}

	public function testGetIpInfosWithNetworkException(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new class('Network error') extends \RuntimeException implements ClientExceptionInterface {});

		$vpnapi = new VPNAPI(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $vpnapi->getIpInfos(self::TEST_IP_ADDRESS);

		self::assertNull($result);
	}

	public function testGetIpInfosWithVpnIp(): void
	{
		$responseData = $this->createVPNAPIResponse(self::TEST_VPN_IP, isVpn: true);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$vpnapi = new VPNAPI(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $vpnapi->getIpInfos(self::TEST_VPN_IP);

		self::assertNotNull($result);
		self::assertTrue($result['security']['vpn']);
	}

	public function testGetIpInfosWithDifferentIpAddresses(): void
	{
		$ip1 = '1.1.1.1';
		$ip2 = '8.8.8.8';
		$ip3 = '185.220.101.1';

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::exactly(3))
			->method('sendRequest')
			->willReturnOnConsecutiveCalls(
				$this->createJsonResponse($this->createVPNAPIResponse($ip1)),
				$this->createJsonResponse($this->createVPNAPIResponse($ip2)),
				$this->createJsonResponse($this->createVPNAPIResponse($ip3, isVpn: true))
			);

		$vpnapi = new VPNAPI(self::TEST_API_KEY, httpClient: $httpClient);

		$result1 = $vpnapi->getIpInfos($ip1);
		$result2 = $vpnapi->getIpInfos($ip2);
		$result3 = $vpnapi->getIpInfos($ip3);

		self::assertNotNull($result1);
		self::assertNotNull($result2);
		self::assertNotNull($result3);
		self::assertFalse($result1['security']['vpn']);
		self::assertFalse($result2['security']['vpn']);
		self::assertTrue($result3['security']['vpn']);
	}

	public function testGetIpInfosWithIPv6Address(): void
	{
		$ipv6 = '2001:4860:4860::8888';
		$responseData = $this->createVPNAPIResponse($ipv6);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) use ($ipv6) {
				$uri = (string) $request->getUri();
				return str_contains($uri, $ipv6);
			}))
			->willReturn($this->createJsonResponse($responseData));

		$vpnapi = new VPNAPI(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $vpnapi->getIpInfos($ipv6);

		self::assertNotNull($result);
	}

	public function testGetIpInfosWithDifferentCountries(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::exactly(3))
			->method('sendRequest')
			->willReturnOnConsecutiveCalls(
				$this->createJsonResponse($this->createVPNAPIResponse('1.1.1.1', countryCode: 'US')),
				$this->createJsonResponse($this->createVPNAPIResponse('8.8.8.8', countryCode: 'FR')),
				$this->createJsonResponse($this->createVPNAPIResponse('9.9.9.9', countryCode: 'GB'))
			);

		$vpnapi = new VPNAPI(self::TEST_API_KEY, httpClient: $httpClient);

		$result1 = $vpnapi->getIpInfos('1.1.1.1');
		$result2 = $vpnapi->getIpInfos('8.8.8.8');
		$result3 = $vpnapi->getIpInfos('9.9.9.9');

		self::assertSame('US', $result1['location']['country_code']);
		self::assertSame('FR', $result2['location']['country_code']);
		self::assertSame('GB', $result3['location']['country_code']);
	}

	/* ===================== Static method: isVpn() ===================== */

	public function testIsVpnReturnsTrueWhenVpnDetected(): void
	{
		$result = [
			'security' => [
				'vpn' => true,
				'proxy' => false
			]
		];

		$isVpn = VPNAPI::isVpn($result);

		self::assertTrue($isVpn);
	}

	public function testIsVpnReturnsFalseWhenNoVpn(): void
	{
		$result = [
			'security' => [
				'vpn' => false,
				'proxy' => false
			]
		];

		$isVpn = VPNAPI::isVpn($result);

		self::assertFalse($isVpn);
	}

	public function testIsVpnReturnsFalseWhenSecurityKeyMissing(): void
	{
		$result = [];

		$isVpn = VPNAPI::isVpn($result);

		self::assertFalse($isVpn);
	}

	public function testIsVpnReturnsFalseWhenVpnKeyMissing(): void
	{
		$result = [
			'security' => [
				'proxy' => false
			]
		];

		$isVpn = VPNAPI::isVpn($result);

		self::assertFalse($isVpn);
	}

	/* ===================== Static method: getCountryCode() ===================== */

	public function testGetCountryCodeReturnsUppercaseCode(): void
	{
		$result = [
			'location' => [
				'country_code' => 'us'
			]
		];

		$countryCode = VPNAPI::getCountryCode($result);

		self::assertSame('US', $countryCode);
	}

	public function testGetCountryCodeWithUppercaseInput(): void
	{
		$result = [
			'location' => [
				'country_code' => 'FR'
			]
		];

		$countryCode = VPNAPI::getCountryCode($result);

		self::assertSame('FR', $countryCode);
	}

	public function testGetCountryCodeWithMixedCase(): void
	{
		$result = [
			'location' => [
				'country_code' => 'Gb'
			]
		];

		$countryCode = VPNAPI::getCountryCode($result);

		self::assertSame('GB', $countryCode);
	}

	public function testGetCountryCodeReturnsNullWhenLocationKeyMissing(): void
	{
		$result = [];

		$countryCode = VPNAPI::getCountryCode($result);

		self::assertNull($countryCode);
	}

	public function testGetCountryCodeReturnsNullWhenCountryCodeKeyMissing(): void
	{
		$result = [
			'location' => [
				'city' => 'Paris'
			]
		];

		$countryCode = VPNAPI::getCountryCode($result);

		self::assertNull($countryCode);
	}

	public function testGetCountryCodeReturnsNullWhenCountryCodeIsEmpty(): void
	{
		$result = [
			'location' => [
				'country_code' => ''
			]
		];

		$countryCode = VPNAPI::getCountryCode($result);

		self::assertNull($countryCode);
	}

	/* ===================== Credentials Validation ===================== */

	public function testGetIpInfosLogsErrorWithoutApiKey(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('VPNAPI.io API key is missing');

		$vpnapi = new VPNAPI(logger: $logger);

		$result = $vpnapi->getIpInfos(self::TEST_IP_ADDRESS);

		self::assertNull($result);
	}

	public function testGetIpInfosWithEmptyApiKey(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('VPNAPI.io API key is missing');

		$vpnapi = new VPNAPI('', logger: $logger);

		$result = $vpnapi->getIpInfos(self::TEST_IP_ADDRESS);

		self::assertNull($result);
	}

	/* ===================== Workflow tests ===================== */

	public function testCompleteWorkflowWithMethodChaining(): void
	{
		$responseData = $this->createVPNAPIResponse(self::TEST_IP_ADDRESS);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$vpnapi = new VPNAPI(httpClient: $httpClient);
		$vpnapi->setKey(self::TEST_API_KEY);

		$result = $vpnapi->getIpInfos(self::TEST_IP_ADDRESS);

		self::assertNotNull($result);
		self::assertFalse(VPNAPI::isVpn($result));
		self::assertSame('US', VPNAPI::getCountryCode($result));
	}

	public function testCompleteVpnDetectionWorkflow(): void
	{
		$responseData = $this->createVPNAPIResponse(self::TEST_VPN_IP, isVpn: true, countryCode: 'NL');

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$vpnapi = new VPNAPI(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $vpnapi->getIpInfos(self::TEST_VPN_IP);

		self::assertNotNull($result);
		self::assertTrue(VPNAPI::isVpn($result));
		self::assertSame('NL', VPNAPI::getCountryCode($result));
	}

	/* ===================== Edge cases ===================== */

	public function testGetIpInfosWithPrivateIp(): void
	{
		$privateIp = '192.168.1.1';
		$responseData = $this->createVPNAPIResponse($privateIp);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$vpnapi = new VPNAPI(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $vpnapi->getIpInfos($privateIp);

		self::assertNotNull($result);
	}

	public function testGetIpInfosWithLocalhostIp(): void
	{
		$localhostIp = '127.0.0.1';
		$responseData = $this->createVPNAPIResponse($localhostIp);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$vpnapi = new VPNAPI(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $vpnapi->getIpInfos($localhostIp);

		self::assertNotNull($result);
	}

	public function testIsVpnWithCompleteSecurityData(): void
	{
		$result = [
			'security' => [
				'vpn' => true,
				'proxy' => true,
				'tor' => false,
				'relay' => false
			]
		];

		$isVpn = VPNAPI::isVpn($result);

		self::assertTrue($isVpn);
	}

	public function testGetCountryCodeWithThreeLetterCode(): void
	{
		// Some APIs might return 3-letter codes
		$result = [
			'location' => [
				'country_code' => 'usa'
			]
		];

		$countryCode = VPNAPI::getCountryCode($result);

		self::assertSame('USA', $countryCode);
	}

	/* ===================== Multiple operations ===================== */

	public function testMultipleIpChecksWithSameClient(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::exactly(5))
			->method('sendRequest')
			->willReturnOnConsecutiveCalls(
				$this->createJsonResponse($this->createVPNAPIResponse('1.1.1.1')),
				$this->createJsonResponse($this->createVPNAPIResponse('8.8.8.8')),
				$this->createJsonResponse($this->createVPNAPIResponse('9.9.9.9', isVpn: true)),
				$this->createJsonResponse($this->createVPNAPIResponse('4.4.4.4')),
				$this->createJsonResponse($this->createVPNAPIResponse('5.5.5.5', isVpn: true))
			);

		$vpnapi = new VPNAPI(self::TEST_API_KEY, httpClient: $httpClient);

		$ips = ['1.1.1.1', '8.8.8.8', '9.9.9.9', '4.4.4.4', '5.5.5.5'];
		$results = [];

		foreach ($ips as $ip) {
			$results[$ip] = $vpnapi->getIpInfos($ip);
		}

		self::assertCount(5, $results);
		self::assertFalse(VPNAPI::isVpn($results['1.1.1.1']));
		self::assertFalse(VPNAPI::isVpn($results['8.8.8.8']));
		self::assertTrue(VPNAPI::isVpn($results['9.9.9.9']));
		self::assertFalse(VPNAPI::isVpn($results['4.4.4.4']));
		self::assertTrue(VPNAPI::isVpn($results['5.5.5.5']));
	}
}