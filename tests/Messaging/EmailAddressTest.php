<?php

declare(strict_types=1);

namespace Tests\Messaging;

use Osimatic\Messaging\EmailAddress;
use PHPUnit\Framework\TestCase;

final class EmailAddressTest extends TestCase
{
	/* ===================== check() ===================== */

	public function testCheckWithValidEmails(): void
	{
		$this->assertTrue(EmailAddress::check('user@example.com'));
		$this->assertTrue(EmailAddress::check('test.user@example.com'));
		$this->assertTrue(EmailAddress::check('user+tag@example.com'));
		$this->assertTrue(EmailAddress::check('user_name@example.com'));
		$this->assertTrue(EmailAddress::check('user123@example.co.uk'));
		$this->assertTrue(EmailAddress::check('user@subdomain.example.com'));
	}

	public function testCheckWithInvalidEmails(): void
	{
		$this->assertFalse(EmailAddress::check('invalid'));
		$this->assertFalse(EmailAddress::check('invalid@'));
		$this->assertFalse(EmailAddress::check('@example.com'));
		$this->assertFalse(EmailAddress::check('user @example.com'));
		$this->assertFalse(EmailAddress::check('user@example'));
		$this->assertFalse(EmailAddress::check(''));
	}

	public function testCheckWithSpecialCharacters(): void
	{
		$this->assertTrue(EmailAddress::check('user.name+tag@example.com'));
		$this->assertTrue(EmailAddress::check('user_name@example.com'));
		$this->assertTrue(EmailAddress::check('123@example.com'));
	}

	public function testCheckWithInternationalDomains(): void
	{
		$this->assertTrue(EmailAddress::check('user@example.fr'));
		$this->assertTrue(EmailAddress::check('user@example.co.uk'));
		$this->assertTrue(EmailAddress::check('user@example.org'));
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