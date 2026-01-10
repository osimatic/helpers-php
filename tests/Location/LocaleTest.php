<?php

declare(strict_types=1);

namespace Tests\Location;

use Osimatic\Location\Locale;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class LocaleTest extends TestCase
{
	/* ===================== getFromRequest() ===================== */

	public function testGetFromRequestWithAcceptLanguageHeader(): void
	{
		$request = new Request();
		$request->headers->set('Accept-Language', 'fr-FR');

		$locale = Locale::getFromRequest($request);
		$this->assertSame('fr_FR', $locale);
	}

	public function testGetFromRequestWithoutAcceptLanguageHeader(): void
	{
		$request = new Request();
		$defaultLocale = 'en_US';

		$locale = Locale::getFromRequest($request, $defaultLocale);
		$this->assertSame($defaultLocale, $locale);
	}

	public function testGetFromRequestReplacesHyphenWithUnderscore(): void
	{
		$request = new Request();
		$request->headers->set('Accept-Language', 'en-GB');

		$locale = Locale::getFromRequest($request);
		$this->assertSame('en_GB', $locale);
	}

	public function testGetFromRequestWithNullDefaultLocale(): void
	{
		$request = new Request();

		$locale = Locale::getFromRequest($request, null);
		$this->assertIsString($locale);
		$this->assertNotEmpty($locale);
	}

	/* ===================== setFromRequest() ===================== */

	public function testSetFromRequest(): void
	{
		$request = new Request();
		$request->headers->set('Accept-Language', 'fr_FR');

		$locale = Locale::setFromRequest($request);
		$this->assertSame('fr_FR', $locale);
		$this->assertSame('fr_FR', $request->getLocale());
	}

	public function testSetFromRequestWithDefaultLocale(): void
	{
		$request = new Request();
		$defaultLocale = 'de_DE';

		$locale = Locale::setFromRequest($request, $defaultLocale);
		$this->assertSame($defaultLocale, $locale);
		$this->assertSame($defaultLocale, $request->getLocale());
	}

	/* ===================== getFromHttpHeader() ===================== */

	public function testGetFromHttpHeaderWithAcceptLanguage(): void
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR';

		$locale = Locale::getFromHttpHeader();
		$this->assertSame('fr_FR', $locale);

		unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	}

	public function testGetFromHttpHeaderWithoutHeader(): void
	{
		unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);

		$defaultLocale = 'en_US';
		$locale = Locale::getFromHttpHeader($defaultLocale);
		$this->assertSame($defaultLocale, $locale);
	}

	public function testGetFromHttpHeaderCustomHeaderKey(): void
	{
		$_SERVER['HTTP_X_CUSTOM_LANGUAGE'] = 'es-ES';

		$locale = Locale::getFromHttpHeader(null, 'X-Custom-Language');
		$this->assertSame('es_ES', $locale);

		unset($_SERVER['HTTP_X_CUSTOM_LANGUAGE']);
	}

	public function testGetFromHttpHeaderReplacesHyphenWithUnderscore(): void
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'en-GB';

		$locale = Locale::getFromHttpHeader();
		$this->assertSame('en_GB', $locale);

		unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	}

	/* ===================== setFromHttpHeader() ===================== */

	public function testSetFromHttpHeader(): void
	{
		$_SERVER['HTTP_ACCEPT_LANGUAGE'] = 'fr-FR';

		$locale = Locale::setFromHttpHeader();
		$this->assertSame('fr_FR', $locale);

		unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);
	}

	public function testSetFromHttpHeaderWithDefaultLocale(): void
	{
		unset($_SERVER['HTTP_ACCEPT_LANGUAGE']);

		$defaultLocale = 'de_DE';
		$locale = Locale::setFromHttpHeader($defaultLocale);
		$this->assertSame($defaultLocale, $locale);
	}

	/* ===================== get() ===================== */

	public function testGet(): void
	{
		$locale = Locale::get();
		$this->assertIsString($locale);
		$this->assertNotEmpty($locale);
	}
}