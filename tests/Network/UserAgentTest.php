<?php

declare(strict_types=1);

namespace Tests\Network;

use Osimatic\Network\DeviceType;
use Osimatic\Network\UserAgent;
use PHPUnit\Framework\TestCase;

final class UserAgentTest extends TestCase
{
	/* ===================== __construct() / parse() ===================== */

	public function testConstructWithChromeDesktopUserAgent(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$this->assertInstanceOf(UserAgent::class, $userAgent);
		$this->assertSame('Chrome', $userAgent->browserName);
		$this->assertSame('Windows', $userAgent->osName);
		$this->assertFalse($userAgent->deviceIsMobile);
	}

	public function testParseStaticMethod(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = UserAgent::parse($userAgentString);

		$this->assertInstanceOf(UserAgent::class, $userAgent);
		$this->assertSame('Chrome', $userAgent->browserName);
	}

	public function testConstructWithFirefoxUserAgent(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:120.0) Gecko/20100101 Firefox/120.0';
		$userAgent = new UserAgent($userAgentString);

		$this->assertSame('Firefox', $userAgent->browserName);
		$this->assertSame('Windows', $userAgent->osName);
		$this->assertFalse($userAgent->deviceIsMobile);
	}

	public function testConstructWithSafariMacUserAgent(): void
	{
		$userAgentString = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Safari/605.1.15';
		$userAgent = new UserAgent($userAgentString);

		$this->assertSame('Safari', $userAgent->browserName);
		$this->assertSame('macOS', $userAgent->osName);
		$this->assertFalse($userAgent->deviceIsMobile);
	}

	public function testConstructWithiPhoneUserAgent(): void
	{
		$userAgentString = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
		$userAgent = new UserAgent($userAgentString);

		$this->assertSame('Safari', $userAgent->browserName);
		$this->assertSame('iOS', $userAgent->osName);
		$this->assertTrue($userAgent->deviceIsMobile);
		$this->assertSame('Apple', $userAgent->deviceManufacturer);
	}

	public function testConstructWithAndroidChromeUserAgent(): void
	{
		$userAgentString = 'Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Mobile Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$this->assertSame('Chrome', $userAgent->browserName);
		$this->assertSame('Android', $userAgent->osName);
		$this->assertTrue($userAgent->deviceIsMobile);
	}

	public function testConstructWithEdgeUserAgent(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36 Edg/120.0.0.0';
		$userAgent = new UserAgent($userAgentString);

		$this->assertSame('Edge', $userAgent->browserName);
		$this->assertSame('Windows', $userAgent->osName);
		$this->assertFalse($userAgent->deviceIsMobile);
	}

	public function testConstructWithEmptyUserAgent(): void
	{
		$userAgent = new UserAgent('');

		$this->assertNull($userAgent->browserName);
		$this->assertNull($userAgent->osName);
		$this->assertFalse($userAgent->deviceIsMobile);
	}

	/* ===================== getInfosDisplay() ===================== */

	public function testGetInfosDisplayWithDefaultSeparator(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$display = $userAgent->getInfosDisplay();

		$this->assertStringContainsString('Windows', $display);
		$this->assertStringContainsString('Chrome', $display);
		$this->assertStringContainsString(' — ', $display);
	}

	public function testGetInfosDisplayWithCustomSeparator(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$display = $userAgent->getInfosDisplay(' | ');

		$this->assertStringContainsString('Windows', $display);
		$this->assertStringContainsString('Chrome', $display);
		$this->assertStringContainsString(' | ', $display);
		$this->assertStringNotContainsString(' — ', $display);
	}

	public function testGetInfosDisplayWithMobileDevice(): void
	{
		$userAgentString = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
		$userAgent = new UserAgent($userAgentString);

		$display = $userAgent->getInfosDisplay();

		$this->assertStringContainsString('iOS', $display);
		$this->assertStringContainsString('Safari', $display);
	}

	/* ===================== getData() ===================== */

	public function testGetDataReturnsArrayWithOsBrowserDevice(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$data = $userAgent->getData();

		$this->assertIsArray($data);
		$this->assertArrayHasKey('os', $data);
		$this->assertArrayHasKey('browser', $data);
		$this->assertArrayHasKey('device', $data);
	}

	public function testGetDataWithChromeWindows(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$data = $userAgent->getData();

		$this->assertStringContainsString('Windows', $data['os']);
		$this->assertStringContainsString('Chrome', $data['browser']);
	}

	public function testGetDataWithiPhone(): void
	{
		$userAgentString = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
		$userAgent = new UserAgent($userAgentString);

		$data = $userAgent->getData();

		$this->assertStringContainsString('iOS', $data['os']);
		$this->assertStringContainsString('Safari', $data['browser']);
		$this->assertNotNull($data['device']);
	}

	public function testGetDataWithOsVersion(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$data = $userAgent->getData();

		$this->assertNotNull($data['os']);
		$this->assertStringContainsString('10', $data['os']);
	}

	public function testGetDataWithBrowserVersion(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$data = $userAgent->getData();

		$this->assertNotNull($data['browser']);
		$this->assertStringContainsString('Chrome', $data['browser']);
	}

	public function testGetDataWithEmptyUserAgent(): void
	{
		$userAgent = new UserAgent('');

		$data = $userAgent->getData();

		$this->assertNull($data['os']);
		$this->assertNull($data['browser']);
		$this->assertNull($data['device']);
	}

	/* ===================== jsonSerialize() ===================== */

	public function testJsonSerializeReturnsArray(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$json = $userAgent->jsonSerialize();

		$this->assertIsArray($json);
		$this->assertArrayHasKey('user_agent_desc', $json);
		$this->assertArrayHasKey('browser_name', $json);
		$this->assertArrayHasKey('os_name', $json);
		$this->assertArrayHasKey('device_type', $json);
		$this->assertArrayHasKey('device_is_mobile', $json);
		$this->assertArrayHasKey('device_manufacturer', $json);
		$this->assertArrayHasKey('device_model', $json);
	}

	public function testJsonSerializeWithChromeWindows(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$json = $userAgent->jsonSerialize();

		$this->assertSame('Chrome', $json['browser_name']);
		$this->assertSame('Windows', $json['os_name']);
		$this->assertFalse($json['device_is_mobile']);
	}

	public function testJsonSerializeWithiPhone(): void
	{
		$userAgentString = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
		$userAgent = new UserAgent($userAgentString);

		$json = $userAgent->jsonSerialize();

		$this->assertSame('Safari', $json['browser_name']);
		$this->assertSame('iOS', $json['os_name']);
		$this->assertTrue($json['device_is_mobile']);
		$this->assertSame('Apple', $json['device_manufacturer']);
	}

	public function testJsonEncode(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$jsonString = json_encode($userAgent);

		$this->assertIsString($jsonString);
		$this->assertStringContainsString('Chrome', $jsonString);
		$this->assertStringContainsString('Windows', $jsonString);
	}

	/* ===================== __toString() ===================== */

	public function testToStringReturnsReadableRepresentation(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$string = (string) $userAgent;

		$this->assertIsString($string);
		$this->assertNotEmpty($string);
	}

	public function testToStringMatchesReadableRepresentationProperty(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$this->assertSame($userAgent->readableRepresentation, (string) $userAgent);
	}

	/* ===================== Properties ===================== */

	public function testBrowserNameProperty(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$this->assertIsString($userAgent->browserName);
		$this->assertSame('Chrome', $userAgent->browserName);
	}

	public function testBrowserVersionProperty(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$this->assertIsString($userAgent->browserVersion);
		$this->assertNotEmpty($userAgent->browserVersion);
	}

	public function testOsNameProperty(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$this->assertIsString($userAgent->osName);
		$this->assertSame('Windows', $userAgent->osName);
	}

	public function testOsVersionProperty(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$this->assertIsString($userAgent->osVersion);
		$this->assertNotEmpty($userAgent->osVersion);
	}

	public function testDeviceIsMobileProperty(): void
	{
		$desktopUA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$desktopUserAgent = new UserAgent($desktopUA);
		$this->assertFalse($desktopUserAgent->deviceIsMobile);

		$mobileUA = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
		$mobileUserAgent = new UserAgent($mobileUA);
		$this->assertTrue($mobileUserAgent->deviceIsMobile);
	}

	public function testDeviceTypeProperty(): void
	{
		$userAgentString = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
		$userAgent = new UserAgent($userAgentString);

		$this->assertInstanceOf(DeviceType::class, $userAgent->deviceType);
	}

	public function testDeviceManufacturerProperty(): void
	{
		$userAgentString = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
		$userAgent = new UserAgent($userAgentString);

		$this->assertIsString($userAgent->deviceManufacturer);
		$this->assertSame('Apple', $userAgent->deviceManufacturer);
	}

	public function testDeviceModelProperty(): void
	{
		$userAgentString = 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1';
		$userAgent = new UserAgent($userAgentString);

		$this->assertIsString($userAgent->deviceModel);
		$this->assertSame('iPhone', $userAgent->deviceModel);
	}

	public function testParserProperty(): void
	{
		$userAgentString = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';
		$userAgent = new UserAgent($userAgentString);

		$this->assertIsObject($userAgent->parser);
		$this->assertInstanceOf(\WhichBrowser\Parser::class, $userAgent->parser);
	}
}