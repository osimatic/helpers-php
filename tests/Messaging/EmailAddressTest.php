<?php

declare(strict_types=1);

namespace Tests\Messaging;

use Osimatic\Messaging\EmailAddress;
use PHPUnit\Framework\TestCase;

final class EmailAddressTest extends TestCase
{
	/* ===================== isValid() ===================== */

	public function testIsValidWithValidEmails(): void
	{
		$this->assertTrue(EmailAddress::isValid('user@example.com'));
		$this->assertTrue(EmailAddress::isValid('test.user@example.com'));
		$this->assertTrue(EmailAddress::isValid('user+tag@example.com'));
		$this->assertTrue(EmailAddress::isValid('user_name@example.com'));
		$this->assertTrue(EmailAddress::isValid('user123@example.co.uk'));
		$this->assertTrue(EmailAddress::isValid('user@subdomain.example.com'));
	}

	public function testIsValidWithInvalidEmails(): void
	{
		$this->assertFalse(EmailAddress::isValid('invalid'));
		$this->assertFalse(EmailAddress::isValid('invalid@'));
		$this->assertFalse(EmailAddress::isValid('@example.com'));
		$this->assertFalse(EmailAddress::isValid('user @example.com'));
		$this->assertFalse(EmailAddress::isValid('user@example'));
		$this->assertFalse(EmailAddress::isValid(''));
	}

	public function testIsValidWithSpecialCharacters(): void
	{
		$this->assertTrue(EmailAddress::isValid('user.name+tag@example.com'));
		$this->assertTrue(EmailAddress::isValid('user_name@example.com'));
		$this->assertTrue(EmailAddress::isValid('123@example.com'));
	}

	public function testIsValidWithInternationalDomains(): void
	{
		$this->assertTrue(EmailAddress::isValid('user@example.fr'));
		$this->assertTrue(EmailAddress::isValid('user@example.co.uk'));
		$this->assertTrue(EmailAddress::isValid('user@example.org'));
	}

	/* ===================== getHost() ===================== */

	public function testGetHostWithValidEmail(): void
	{
		$this->assertSame('example.com', EmailAddress::getHost('user@example.com'));
		$this->assertSame('subdomain.example.com', EmailAddress::getHost('user@subdomain.example.com'));
		$this->assertSame('example.co.uk', EmailAddress::getHost('test@example.co.uk'));
	}

	public function testGetHostWithoutAtSymbol(): void
	{
		$this->assertNull(EmailAddress::getHost('invalid-email'));
		$this->assertNull(EmailAddress::getHost('no-at-symbol.com'));
	}

	public function testGetHostWithMultipleAtSymbols(): void
	{
		// Should return everything after the first @
		$result = EmailAddress::getHost('user@test@example.com');
		$this->assertSame('test@example.com', $result);
	}

	public function testGetHostWithEmptyDomain(): void
	{
		$result = EmailAddress::getHost('user@');
		$this->assertSame('', $result);
	}

	/* ===================== getTld() ===================== */

	public function testGetTldWithPoint(): void
	{
		$this->assertSame('.com', EmailAddress::getTld('user@example.com'));
		$this->assertSame('.fr', EmailAddress::getTld('user@example.fr'));
		$this->assertSame('.org', EmailAddress::getTld('user@example.org'));
	}

	public function testGetTldWithoutPoint(): void
	{
		$this->assertSame('com', EmailAddress::getTld('user@example.com', false));
		$this->assertSame('fr', EmailAddress::getTld('user@example.fr', false));
		$this->assertSame('org', EmailAddress::getTld('user@example.org', false));
	}

	public function testGetTldWithSubdomain(): void
	{
		$this->assertSame('.com', EmailAddress::getTld('user@subdomain.example.com'));
		$this->assertSame('.uk', EmailAddress::getTld('user@example.co.uk'));
	}

	public function testGetTldWithMultipleLevelTld(): void
	{
		$this->assertSame('.uk', EmailAddress::getTld('user@example.co.uk'));
		$this->assertSame('.br', EmailAddress::getTld('user@example.com.br'));
	}
}