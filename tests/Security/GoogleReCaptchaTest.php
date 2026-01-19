<?php

namespace Tests\Security;

use Osimatic\Security\GoogleReCaptcha;
use PHPUnit\Framework\TestCase;

class GoogleReCaptchaTest extends TestCase
{
	private const string TEST_SITE_KEY = 'test-site-key-123456';
	private const string TEST_SECRET = 'test-secret-123456';

	public function testCanBeInstantiatedWithoutParameters(): void
	{
		$recaptcha = new GoogleReCaptcha();
		self::assertInstanceOf(GoogleReCaptcha::class, $recaptcha);
	}

	public function testCanBeInstantiatedWithParameters(): void
	{
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, self::TEST_SECRET);
		self::assertInstanceOf(GoogleReCaptcha::class, $recaptcha);
	}

	public function testSetSiteKeyReturnsOwnInstance(): void
	{
		$recaptcha = new GoogleReCaptcha();
		$result = $recaptcha->setSiteKey(self::TEST_SITE_KEY);
		self::assertSame($recaptcha, $result);
	}

	public function testSetSecretReturnsOwnInstance(): void
	{
		$recaptcha = new GoogleReCaptcha();
		$result = $recaptcha->setSecret(self::TEST_SECRET);
		self::assertSame($recaptcha, $result);
	}

	public function testMethodChainingWorks(): void
	{
		$recaptcha = new GoogleReCaptcha();
		$result = $recaptcha
			->setSiteKey(self::TEST_SITE_KEY)
			->setSecret(self::TEST_SECRET);
		self::assertSame($recaptcha, $result);
	}

	public function testCheckWithNullResponseReturnsFalse(): void
	{
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, self::TEST_SECRET);
		$result = $recaptcha->check(null);
		self::assertFalse($result);
	}

	public function testCheckWithEmptyResponseReturnsFalse(): void
	{
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, self::TEST_SECRET);
		$result = $recaptcha->check('');
		self::assertFalse($result);
	}

	public function testGetFormFieldReturnsCorrectHtml(): void
	{
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, self::TEST_SECRET);
		$html = $recaptcha->getFormField();

		self::assertStringContainsString('g-recaptcha', $html);
		self::assertStringContainsString(self::TEST_SITE_KEY, $html);
		self::assertStringContainsString('data-sitekey', $html);
	}

	public function testGetFormFieldReturnsDiv(): void
	{
		$recaptcha = new GoogleReCaptcha(self::TEST_SITE_KEY, self::TEST_SECRET);
		$html = $recaptcha->getFormField();

		self::assertStringStartsWith('<div', $html);
		self::assertStringEndsWith('</div>', $html);
	}

	public function testGetJavaScriptUrlWithDefaultLocale(): void
	{
		$recaptcha = new GoogleReCaptcha();
		$url = $recaptcha->getJavaScriptUrl();

		self::assertStringContainsString('https://www.google.com/recaptcha/api.js', $url);
		self::assertStringContainsString('hl=en', $url);
	}

	public function testGetJavaScriptUrlWithCustomLocale(): void
	{
		$recaptcha = new GoogleReCaptcha();
		$url = $recaptcha->getJavaScriptUrl('fr');

		self::assertStringContainsString('https://www.google.com/recaptcha/api.js', $url);
		self::assertStringContainsString('hl=fr', $url);
	}

	public function testGetJavaScriptUrlWithSpanishLocale(): void
	{
		$recaptcha = new GoogleReCaptcha();
		$url = $recaptcha->getJavaScriptUrl('es');

		self::assertStringContainsString('hl=es', $url);
	}

	public function testGetJavaScriptUrlWithGermanLocale(): void
	{
		$recaptcha = new GoogleReCaptcha();
		$url = $recaptcha->getJavaScriptUrl('de');

		self::assertStringContainsString('hl=de', $url);
	}

	#[\PHPUnit\Framework\Attributes\DataProvider('localesProvider')]
	public function testGetJavaScriptUrlWithMultipleLocales(string $locale): void
	{
		$recaptcha = new GoogleReCaptcha();
		$url = $recaptcha->getJavaScriptUrl($locale);

		self::assertStringContainsString('https://www.google.com/recaptcha/api.js', $url);
		self::assertStringContainsString('hl=' . $locale, $url);
	}

	public static function localesProvider(): array
	{
		return [
			['en'],
			['fr'],
			['es'],
			['de'],
			['it'],
			['pt'],
			['nl'],
			['pl'],
			['ru'],
			['ja'],
			['zh'],
		];
	}
}