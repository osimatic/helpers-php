<?php

declare(strict_types=1);

namespace Tests\Network;

use Osimatic\Network\DNS;
use PHPUnit\Framework\TestCase;

final class DNSTest extends TestCase
{
	/* ===================== isValid() ===================== */

	public function testIsValidWithValidDomains(): void
	{
		$this->assertTrue(DNS::isValid('example.com'));
		$this->assertTrue(DNS::isValid('www.example.com'));
		$this->assertTrue(DNS::isValid('sub.example.com'));
		$this->assertTrue(DNS::isValid('example.fr'));
		$this->assertTrue(DNS::isValid('my-domain.com'));
		$this->assertTrue(DNS::isValid('example123.com'));
		$this->assertTrue(DNS::isValid('123example.com'));
	}

	public function testIsValidWithInvalidDomains(): void
	{
		$this->assertFalse(DNS::isValid(''));
		$this->assertFalse(DNS::isValid('not a domain'));
		$this->assertFalse(DNS::isValid('-invalid.com'));
		$this->assertFalse(DNS::isValid('invalid-.com'));
		$this->assertFalse(DNS::isValid('invalid..com'));
		$this->assertFalse(DNS::isValid('.invalid.com'));
		$this->assertFalse(DNS::isValid('invalid.com.'));
	}

	public function testIsValidWithSubdomains(): void
	{
		$this->assertTrue(DNS::isValid('www.example.com'));
		$this->assertTrue(DNS::isValid('api.example.com'));
		$this->assertTrue(DNS::isValid('sub.domain.example.com'));
		$this->assertTrue(DNS::isValid('very.long.sub.domain.example.com'));
	}

	public function testIsValidWithSpecialCharacters(): void
	{
		$this->assertFalse(DNS::isValid('example_test.com')); // underscore not allowed
		$this->assertFalse(DNS::isValid('example@test.com')); // @ not allowed
		$this->assertFalse(DNS::isValid('example test.com')); // space not allowed
	}

	/* ===================== getTld() / getTopLevelDomain() ===================== */

	public function testGetTldWithPoint(): void
	{
		$this->assertSame('.com', DNS::getTld('example.com'));
		$this->assertSame('.fr', DNS::getTld('example.fr'));
		$this->assertSame('.org', DNS::getTld('example.org'));
		$this->assertSame('.net', DNS::getTld('example.net'));
	}

	public function testGetTldWithoutPoint(): void
	{
		$this->assertSame('com', DNS::getTld('example.com', false));
		$this->assertSame('fr', DNS::getTld('example.fr', false));
		$this->assertSame('org', DNS::getTld('example.org', false));
		$this->assertSame('net', DNS::getTld('example.net', false));
	}

	public function testGetTldWithSubdomains(): void
	{
		$this->assertSame('.com', DNS::getTld('www.example.com'));
		$this->assertSame('.fr', DNS::getTld('api.sub.example.fr'));
		$this->assertSame('com', DNS::getTld('www.example.com', false));
	}

	public function testGetTopLevelDomain(): void
	{
		$this->assertSame('.com', DNS::getTopLevelDomain('example.com'));
		$this->assertSame('.fr', DNS::getTopLevelDomain('example.fr'));
		$this->assertSame('.org', DNS::getTopLevelDomain('www.example.org'));
	}

	/* ===================== getSld() / getSecondLevelDomain() ===================== */

	public function testGetSldWithTld(): void
	{
		$this->assertSame('example.com', DNS::getSld('example.com'));
		$this->assertSame('example.fr', DNS::getSld('example.fr'));
		$this->assertSame('google.com', DNS::getSld('www.google.com'));
		$this->assertSame('test.org', DNS::getSld('api.test.org'));
	}

	public function testGetSldWithoutTld(): void
	{
		$this->assertSame('example', DNS::getSld('example.com', false));
		$this->assertSame('example', DNS::getSld('example.fr', false));
		$this->assertSame('google', DNS::getSld('www.google.com', false));
		$this->assertSame('test', DNS::getSld('api.test.org', false));
	}

	public function testGetSldWithMultipleSubdomains(): void
	{
		$this->assertSame('example.com', DNS::getSld('sub1.sub2.example.com'));
		$this->assertSame('example', DNS::getSld('sub1.sub2.example.com', false));
		$this->assertSame('test.fr', DNS::getSld('a.b.c.test.fr'));
		$this->assertSame('test', DNS::getSld('a.b.c.test.fr', false));
	}

	public function testGetSldWithSimpleDomain(): void
	{
		$this->assertSame('example.com', DNS::getSld('example.com'));
		$this->assertSame('example', DNS::getSld('example.com', false));
	}

	public function testGetSecondLevelDomain(): void
	{
		$this->assertSame('example.com', DNS::getSecondLevelDomain('example.com'));
		$this->assertSame('example', DNS::getSecondLevelDomain('example.com', false));
		$this->assertSame('google.fr', DNS::getSecondLevelDomain('www.google.fr'));
		$this->assertSame('google', DNS::getSecondLevelDomain('www.google.fr', false));
	}
}
