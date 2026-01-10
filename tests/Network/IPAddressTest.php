<?php

declare(strict_types=1);

namespace Tests\Network;

use Osimatic\Network\IPAddress;
use PHPUnit\Framework\TestCase;

final class IPAddressTest extends TestCase
{
	/* ===================== check() ===================== */

	public function testCheckWithValidIpV4(): void
	{
		$this->assertTrue(IPAddress::check('192.168.1.1'));
		$this->assertTrue(IPAddress::check('10.0.0.1'));
		$this->assertTrue(IPAddress::check('255.255.255.255'));
		$this->assertTrue(IPAddress::check('0.0.0.0'));
		$this->assertTrue(IPAddress::check('127.0.0.1'));
	}

	public function testCheckWithInvalidIpV4(): void
	{
		$this->assertFalse(IPAddress::check('999.999.999.999'));
		$this->assertFalse(IPAddress::check('192.168.1'));
		$this->assertFalse(IPAddress::check('192.168.1.1.1'));
		$this->assertFalse(IPAddress::check('not-an-ip'));
		$this->assertFalse(IPAddress::check(''));
		$this->assertFalse(IPAddress::check('256.1.1.1'));
	}

	public function testCheckWithIpV6(): void
	{
		// check() valide uniquement les IPv4, pas les IPv6
		$this->assertFalse(IPAddress::check('::1'));
		$this->assertFalse(IPAddress::check('2001:0db8:85a3::8a2e:0370:7334'));
	}

	/* ===================== checkIpV4() ===================== */

	public function testCheckIpV4WithValidAddresses(): void
	{
		$this->assertTrue(IPAddress::checkIpV4('192.168.1.1'));
		$this->assertTrue(IPAddress::checkIpV4('10.0.0.1'));
		$this->assertTrue(IPAddress::checkIpV4('172.16.0.1'));
		$this->assertTrue(IPAddress::checkIpV4('8.8.8.8'));
		$this->assertTrue(IPAddress::checkIpV4('1.1.1.1'));
	}

	public function testCheckIpV4WithInvalidAddresses(): void
	{
		$this->assertFalse(IPAddress::checkIpV4('999.999.999.999'));
		$this->assertFalse(IPAddress::checkIpV4('192.168.1'));
		$this->assertFalse(IPAddress::checkIpV4('192.168.1.1.1'));
		$this->assertFalse(IPAddress::checkIpV4('256.1.1.1'));
		$this->assertFalse(IPAddress::checkIpV4('-1.0.0.0'));
	}

	/* ===================== checkIpV6() ===================== */

	public function testCheckIpV6WithValidAddresses(): void
	{
		$this->assertTrue(IPAddress::checkIpV6('::1'));
		$this->assertTrue(IPAddress::checkIpV6('2001:0db8:85a3::8a2e:0370:7334'));
		$this->assertTrue(IPAddress::checkIpV6('2001:db8::1'));
		$this->assertTrue(IPAddress::checkIpV6('fe80::1'));
		$this->assertTrue(IPAddress::checkIpV6('::'));
	}

	public function testCheckIpV6WithInvalidAddresses(): void
	{
		$this->assertFalse(IPAddress::checkIpV6('192.168.1.1'));
		$this->assertFalse(IPAddress::checkIpV6('gggg::1'));
		$this->assertFalse(IPAddress::checkIpV6('not-an-ipv6'));
		$this->assertFalse(IPAddress::checkIpV6(''));
	}

	/* ===================== checkRange() ===================== */

	public function testCheckRangeWithValidRanges(): void
	{
		$this->assertTrue(IPAddress::checkRange('192.168.1.0-192.168.1.255'));
		$this->assertTrue(IPAddress::checkRange('10.0.0.0-10.255.255.255'));
		$this->assertTrue(IPAddress::checkRange('172.16.0.0-172.31.255.255'));
	}

	public function testCheckRangeWithSpaces(): void
	{
		$this->assertTrue(IPAddress::checkRange('192.168.1.0 - 192.168.1.255'));
	}

	public function testCheckRangeWithInvalidRanges(): void
	{
		$this->assertFalse(IPAddress::checkRange('192.168.1.0'));
		$this->assertFalse(IPAddress::checkRange('192.168.1.0-999.999.999.999'));
		$this->assertFalse(IPAddress::checkRange('not-an-ip-range'));
		$this->assertFalse(IPAddress::checkRange(''));
	}

	public function testCheckRangeWithCustomSeparator(): void
	{
		$this->assertTrue(IPAddress::checkRange('192.168.1.0|192.168.1.255', '|'));
		$this->assertTrue(IPAddress::checkRange('192.168.1.0..192.168.1.255', '..'));
	}

	/* ===================== checkRangeOfIpV6() ===================== */

	public function testCheckRangeOfIpV6WithValidRanges(): void
	{
		$this->assertTrue(IPAddress::checkRangeOfIpV6('2001:db8::1-2001:db8::ffff'));
		$this->assertTrue(IPAddress::checkRangeOfIpV6('fe80::1-fe80::ff'));
	}

	public function testCheckRangeOfIpV6WithInvalidRanges(): void
	{
		$this->assertFalse(IPAddress::checkRangeOfIpV6('192.168.1.0-192.168.1.255'));
		$this->assertFalse(IPAddress::checkRangeOfIpV6('::1'));
		$this->assertFalse(IPAddress::checkRangeOfIpV6(''));
	}

	/* ===================== isInRangeOfIpAddressRange() ===================== */

	public function testIsInRangeOfIpAddressRangeWithIpInRange(): void
	{
		$this->assertTrue(IPAddress::isInRangeOfIpAddressRange('192.168.1.100', '192.168.1.0-192.168.1.255'));
		$this->assertTrue(IPAddress::isInRangeOfIpAddressRange('10.0.0.50', '10.0.0.0-10.0.0.255'));
		$this->assertTrue(IPAddress::isInRangeOfIpAddressRange('192.168.1.0', '192.168.1.0-192.168.1.255'));
		$this->assertTrue(IPAddress::isInRangeOfIpAddressRange('192.168.1.255', '192.168.1.0-192.168.1.255'));
	}

	public function testIsInRangeOfIpAddressRangeWithIpOutOfRange(): void
	{
		$this->assertFalse(IPAddress::isInRangeOfIpAddressRange('192.168.2.100', '192.168.1.0-192.168.1.255'));
		$this->assertFalse(IPAddress::isInRangeOfIpAddressRange('10.1.0.50', '10.0.0.0-10.0.0.255'));
	}

	public function testIsInRangeOfIpAddressRangeWithInvalidRange(): void
	{
		$this->assertFalse(IPAddress::isInRangeOfIpAddressRange('192.168.1.100', '192.168.1.0'));
	}

	/* ===================== isInRangeOfIpAddresses() ===================== */

	public function testIsInRangeOfIpAddressesWithIpInRange(): void
	{
		$this->assertTrue(IPAddress::isInRangeOfIpAddresses('192.168.1.100', '192.168.1.0', '192.168.1.255'));
		$this->assertTrue(IPAddress::isInRangeOfIpAddresses('10.0.0.50', '10.0.0.0', '10.0.0.255'));
	}

	public function testIsInRangeOfIpAddressesWithIpOutOfRange(): void
	{
		$this->assertFalse(IPAddress::isInRangeOfIpAddresses('192.168.2.100', '192.168.1.0', '192.168.1.255'));
		$this->assertFalse(IPAddress::isInRangeOfIpAddresses('10.1.0.50', '10.0.0.0', '10.0.0.255'));
	}

	public function testIsInRangeOfIpAddressesAtBoundaries(): void
	{
		$this->assertTrue(IPAddress::isInRangeOfIpAddresses('192.168.1.0', '192.168.1.0', '192.168.1.255'));
		$this->assertTrue(IPAddress::isInRangeOfIpAddresses('192.168.1.255', '192.168.1.0', '192.168.1.255'));
	}

	/* ===================== correspondToIpAddress() ===================== */

	public function testCorrespondToIpAddressWithExactMatch(): void
	{
		$this->assertTrue(IPAddress::correspondToIpAddress('192.168.1.1', '192.168.1.1'));
		$this->assertTrue(IPAddress::correspondToIpAddress('10.0.0.1', '10.0.0.1'));
	}

	public function testCorrespondToIpAddressWithNoMatch(): void
	{
		$this->assertFalse(IPAddress::correspondToIpAddress('192.168.1.1', '192.168.1.2'));
		$this->assertFalse(IPAddress::correspondToIpAddress('10.0.0.1', '10.0.0.2'));
	}

	public function testCorrespondToIpAddressWithWildcard(): void
	{
		$this->assertTrue(IPAddress::correspondToIpAddress('192.168.1.1', '192.168.1.%'));
		$this->assertTrue(IPAddress::correspondToIpAddress('192.168.1.100', '192.168.1.%'));
		$this->assertTrue(IPAddress::correspondToIpAddress('192.168.1.255', '192.168.1.%'));
		$this->assertTrue(IPAddress::correspondToIpAddress('192.168.100.1', '192.168.%'));
		$this->assertTrue(IPAddress::correspondToIpAddress('192.100.1.1', '192.%'));
	}

	public function testCorrespondToIpAddressWithWildcardNoMatch(): void
	{
		$this->assertFalse(IPAddress::correspondToIpAddress('192.168.2.1', '192.168.1.%'));
		$this->assertFalse(IPAddress::correspondToIpAddress('10.0.0.1', '192.168.%'));
	}

	/* ===================== isInRangeOfIpAddressesCidr() ===================== */

	public function testIsInRangeOfIpAddressesCidrWithIpInRange(): void
	{
		$this->assertTrue(IPAddress::isInRangeOfIpAddressesCidr('192.168.1.100', '192.168.1.0/24'));
		$this->assertTrue(IPAddress::isInRangeOfIpAddressesCidr('10.0.0.50', '10.0.0.0/24'));
		$this->assertTrue(IPAddress::isInRangeOfIpAddressesCidr('172.16.50.1', '172.16.0.0/16'));
	}

	public function testIsInRangeOfIpAddressesCidrWithIpOutOfRange(): void
	{
		$this->assertFalse(IPAddress::isInRangeOfIpAddressesCidr('192.168.2.100', '192.168.1.0/24'));
		$this->assertFalse(IPAddress::isInRangeOfIpAddressesCidr('10.1.0.50', '10.0.0.0/24'));
	}

	public function testIsInRangeOfIpAddressesCidrWithDifferentMasks(): void
	{
		// /32 = une seule adresse
		$this->assertTrue(IPAddress::isInRangeOfIpAddressesCidr('192.168.1.1', '192.168.1.1/32'));
		$this->assertFalse(IPAddress::isInRangeOfIpAddressesCidr('192.168.1.2', '192.168.1.1/32'));

		// /8 = très large plage
		$this->assertTrue(IPAddress::isInRangeOfIpAddressesCidr('10.50.100.200', '10.0.0.0/8'));
		$this->assertTrue(IPAddress::isInRangeOfIpAddressesCidr('10.255.255.255', '10.0.0.0/8'));
		$this->assertFalse(IPAddress::isInRangeOfIpAddressesCidr('11.0.0.1', '10.0.0.0/8'));
	}

	/* ===================== isIpAddressInListOfIpAddress() ===================== */

	public function testIsIpAddressInListOfIpAddressWithExactMatch(): void
	{
		$list = ['192.168.1.1', '192.168.1.2', '192.168.1.3'];
		$this->assertTrue(IPAddress::isIpAddressInListOfIpAddress('192.168.1.1', $list));
		$this->assertTrue(IPAddress::isIpAddressInListOfIpAddress('192.168.1.2', $list));
		$this->assertFalse(IPAddress::isIpAddressInListOfIpAddress('192.168.1.4', $list));
	}

	public function testIsIpAddressInListOfIpAddressWithRange(): void
	{
		$list = ['192.168.1.0-192.168.1.255', '10.0.0.0-10.0.0.255'];
		$this->assertTrue(IPAddress::isIpAddressInListOfIpAddress('192.168.1.100', $list));
		$this->assertTrue(IPAddress::isIpAddressInListOfIpAddress('10.0.0.50', $list));
		$this->assertFalse(IPAddress::isIpAddressInListOfIpAddress('192.168.2.1', $list));
	}

	public function testIsIpAddressInListOfIpAddressWithWildcard(): void
	{
		$list = ['192.168.1.%', '10.0.0.%'];
		$this->assertTrue(IPAddress::isIpAddressInListOfIpAddress('192.168.1.1', $list));
		$this->assertTrue(IPAddress::isIpAddressInListOfIpAddress('192.168.1.255', $list));
		$this->assertTrue(IPAddress::isIpAddressInListOfIpAddress('10.0.0.1', $list));
		$this->assertFalse(IPAddress::isIpAddressInListOfIpAddress('192.168.2.1', $list));
	}

	public function testIsIpAddressInListOfIpAddressWithMixedFormats(): void
	{
		$list = ['192.168.1.1', '192.168.2.0-192.168.2.255', '10.0.0.%'];
		$this->assertTrue(IPAddress::isIpAddressInListOfIpAddress('192.168.1.1', $list));
		$this->assertTrue(IPAddress::isIpAddressInListOfIpAddress('192.168.2.100', $list));
		$this->assertTrue(IPAddress::isIpAddressInListOfIpAddress('10.0.0.50', $list));
		$this->assertFalse(IPAddress::isIpAddressInListOfIpAddress('192.168.3.1', $list));
	}

	public function testIsIpAddressInListOfIpAddressWithEmptyList(): void
	{
		$this->assertFalse(IPAddress::isIpAddressInListOfIpAddress('192.168.1.1', []));
	}
}
