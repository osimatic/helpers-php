<?php

declare(strict_types=1);

namespace Tests\Network;

use Osimatic\Network\URL;
use PHPUnit\Framework\TestCase;

final class URLTest extends TestCase
{
	/* ===================== check() ===================== */

	public function testCheckWithValidUrls(): void
	{
		$this->assertTrue(URL::check('http://example.com', true));
		$this->assertTrue(URL::check('https://example.com', true));
		$this->assertTrue(URL::check('https://www.example.com', true));
		$this->assertTrue(URL::check('https://example.com/path', true));
		$this->assertTrue(URL::check('https://example.com/path?query=1', true));
		$this->assertTrue(URL::check('https://example.com:8080', true));
		$this->assertTrue(URL::check('ftp://example.com', true));
	}

	public function testCheckWithInvalidUrls(): void
	{
		$this->assertFalse(URL::check('not a url', true));
		$this->assertFalse(URL::check('htp://wrong', true));
		$this->assertFalse(URL::check('', true));
	}

	public function testCheckWithoutSchemeValidation(): void
	{
		$this->assertTrue(URL::check('http://example.com', false));
		$this->assertTrue(URL::check('htp://example.com', false)); // schéma invalide mais non vérifié
	}

	/* ===================== format() ===================== */

	public function testFormatWithProtocol(): void
	{
		$this->assertSame('http://example.com', URL::format('http://example.com/', true));
		$this->assertSame('https://example.com', URL::format('https://EXAMPLE.COM/', true));
	}

	public function testFormatWithoutProtocol(): void
	{
		$this->assertSame('example.com', URL::format('http://example.com/'));
		$this->assertSame('example.com', URL::format('https://EXAMPLE.COM/'));
		$this->assertSame('www.example.com', URL::format('http://www.example.com/'));
	}

	public function testFormatRemovesTrailingSlash(): void
	{
		$this->assertSame('example.com', URL::format('http://example.com/'));
		$this->assertSame('example.com', URL::format('http://example.com/', false));
	}

	public function testFormatConvertsToLowercase(): void
	{
		$this->assertSame('example.com', URL::format('http://EXAMPLE.COM/'));
		$this->assertSame('www.example.com', URL::format('http://WWW.EXAMPLE.COM/'));
	}

	/* ===================== toLowerCase() ===================== */

	public function testToLowerCaseWithDomain(): void
	{
		$this->assertSame('http://example.com', URL::toLowerCase('http://EXAMPLE.COM'));
		$this->assertSame('https://www.example.com', URL::toLowerCase('https://WWW.EXAMPLE.COM'));
	}

	public function testToLowerCasePreservesPathCase(): void
	{
		$this->assertSame('http://example.com/Path', URL::toLowerCase('http://EXAMPLE.COM/Path'));
		$this->assertSame('https://example.com/MyPath/Test', URL::toLowerCase('https://EXAMPLE.COM/MyPath/Test'));
	}

	public function testToLowerCasePreservesQueryStringCase(): void
	{
		$this->assertSame('http://example.com?Arg=Value', URL::toLowerCase('http://EXAMPLE.COM?Arg=Value'));
		$this->assertSame('http://example.com/path?Test=ABC', URL::toLowerCase('http://EXAMPLE.COM/path?Test=ABC'));
	}

	public function testToLowerCaseWithDomainOnly(): void
	{
		$this->assertSame('http://example.com', URL::toLowerCase('http://EXAMPLE.COM'));
	}

	/* ===================== getTld() / getTopLevelDomain() ===================== */

	public function testGetTldWithPoint(): void
	{
		$this->assertSame('.com', URL::getTld('http://example.com'));
		$this->assertSame('.fr', URL::getTld('https://example.fr'));
		$this->assertSame('.org', URL::getTld('http://www.example.org'));
	}

	public function testGetTldWithoutPoint(): void
	{
		$this->assertSame('com', URL::getTld('http://example.com', false));
		$this->assertSame('fr', URL::getTld('https://example.fr', false));
		$this->assertSame('org', URL::getTld('http://www.example.org', false));
	}

	public function testGetTopLevelDomain(): void
	{
		$this->assertSame('.com', URL::getTopLevelDomain('http://example.com'));
		$this->assertSame('.fr', URL::getTopLevelDomain('https://example.fr'));
	}

	/* ===================== getSld() / getSecondLevelDomain() ===================== */

	public function testGetSldWithTld(): void
	{
		$this->assertSame('example.com', URL::getSld('http://example.com'));
		$this->assertSame('google.fr', URL::getSld('https://www.google.fr'));
		$this->assertSame('test.org', URL::getSld('http://sub.test.org'));
	}

	public function testGetSldWithoutTld(): void
	{
		$this->assertSame('example', URL::getSld('http://example.com', false));
		$this->assertSame('google', URL::getSld('https://www.google.fr', false));
		$this->assertSame('test', URL::getSld('http://sub.test.org', false));
	}

	public function testGetSecondLevelDomainWithTld(): void
	{
		$this->assertSame('example.com', URL::getSecondLevelDomain('http://example.com'));
		$this->assertSame('google.fr', URL::getSecondLevelDomain('https://google.fr'));
	}

	public function testGetSecondLevelDomainWithoutTld(): void
	{
		$this->assertSame('example', URL::getSecondLevelDomain('http://example.com', false));
		$this->assertSame('google', URL::getSecondLevelDomain('https://google.fr', false));
	}

	/* ===================== getScheme() ===================== */

	public function testGetSchemeWithSeparator(): void
	{
		$this->assertSame('http://', URL::getScheme('http://example.com'));
		$this->assertSame('https://', URL::getScheme('https://example.com'));
		$this->assertSame('ftp://', URL::getScheme('ftp://example.com'));
	}

	public function testGetSchemeWithoutSeparator(): void
	{
		$this->assertSame('http', URL::getScheme('http://example.com', false));
		$this->assertSame('https', URL::getScheme('https://example.com', false));
		$this->assertSame('ftp', URL::getScheme('ftp://example.com', false));
	}

	public function testGetSchemeWithDefaultHttp(): void
	{
		$this->assertSame('http://', URL::getScheme('example.com'));
		$this->assertSame('http', URL::getScheme('example.com', false));
	}

	public function testGetSchemeWithEmptyReturn(): void
	{
		$this->assertSame('', URL::getScheme('example.com', true, true));
		$this->assertSame('', URL::getScheme('example.com', false, true));
	}

	/* ===================== getHost() ===================== */

	public function testGetHostWithSlashAtEnd(): void
	{
		$this->assertSame('example.com/', URL::getHost('http://example.com'));
		$this->assertSame('www.example.com/', URL::getHost('https://www.example.com'));
	}

	public function testGetHostWithoutSlashAtEnd(): void
	{
		$this->assertSame('example.com', URL::getHost('http://example.com', false));
		$this->assertSame('www.example.com', URL::getHost('https://www.example.com/', false));
	}

	public function testGetHostDeleteSlashAtEnd(): void
	{
		$this->assertSame('example.com', URL::getHost('http://example.com/', false, true));
		$this->assertSame('example.com', URL::getHost('http://example.com', false, true));
	}

	public function testGetHostWithPort(): void
	{
		$this->assertSame('example.com/', URL::getHost('http://example.com:8080'));
	}

	/* ===================== getPort() ===================== */

	public function testGetPortWithPort(): void
	{
		$this->assertSame('8080', URL::getPort('http://example.com:8080'));
		$this->assertSame('3000', URL::getPort('https://example.com:3000/path'));
		$this->assertSame('443', URL::getPort('https://example.com:443'));
	}

	public function testGetPortWithoutPort(): void
	{
		$this->assertNull(URL::getPort('http://example.com'));
		$this->assertNull(URL::getPort('https://example.com/path'));
	}

	/* ===================== getPath() ===================== */

	public function testGetPathWithSlashAtBeginning(): void
	{
		$this->assertSame('/path', URL::getPath('http://example.com/path'));
		$this->assertSame('/path/to/page', URL::getPath('https://example.com/path/to/page'));
	}

	public function testGetPathWithoutSlashAtBeginning(): void
	{
		$this->assertSame('path', URL::getPath('http://example.com/path', false));
		$this->assertSame('path/to/page', URL::getPath('https://example.com/path/to/page', false));
	}

	public function testGetPathWithoutPath(): void
	{
		$this->assertSame('/', URL::getPath('http://example.com'));
		$this->assertSame('/', URL::getPath('https://example.com/'));
	}

	public function testGetPathWithQueryString(): void
	{
		$this->assertSame('/path', URL::getPath('http://example.com/path?query=1'));
	}

	/* ===================== getFile() ===================== */

	public function testGetFileWithFile(): void
	{
		$this->assertSame('page.html', URL::getFile('http://example.com/path/page.html'));
		$this->assertSame('document.pdf', URL::getFile('https://example.com/files/document.pdf'));
		$this->assertSame('index.php', URL::getFile('http://example.com/index.php'));
	}

	public function testGetFileWithoutFile(): void
	{
		$this->assertSame('', URL::getFile('http://example.com'));
		$this->assertSame('', URL::getFile('http://example.com/path/'));
	}

	public function testGetFileWithQueryString(): void
	{
		$this->assertSame('page.html', URL::getFile('http://example.com/page.html?id=1'));
	}

	/* ===================== getQueryString() ===================== */

	public function testGetQueryStringWithoutSeparator(): void
	{
		$this->assertSame('query=1', URL::getQueryString('http://example.com?query=1'));
		$this->assertSame('id=1&page=2', URL::getQueryString('http://example.com?id=1&page=2'));
	}

	public function testGetQueryStringWithSeparator(): void
	{
		$this->assertSame('?query=1', URL::getQueryString('http://example.com?query=1', true));
		$this->assertSame('?id=1&page=2', URL::getQueryString('http://example.com?id=1&page=2', true));
	}

	public function testGetQueryStringWithoutQueryString(): void
	{
		$this->assertSame('', URL::getQueryString('http://example.com'));
		$this->assertSame('', URL::getQueryString('http://example.com/path'));
	}

	public function testGetQueryStringWithSeparatorIfEmpty(): void
	{
		$this->assertSame('?', URL::getQueryString('http://example.com', true, true));
		$this->assertSame('', URL::getQueryString('http://example.com', true, false));
	}

	public function testGetQueryStringWithPath(): void
	{
		$this->assertSame('id=1', URL::getQueryString('http://example.com/path?id=1'));
		$this->assertSame('?id=1', URL::getQueryString('http://example.com/path?id=1', true));
	}
}
