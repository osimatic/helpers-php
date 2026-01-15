<?php

declare(strict_types=1);

namespace Tests\Text;

use Osimatic\Text\StringGenerator;
use PHPUnit\Framework\TestCase;

final class StringGeneratorTest extends TestCase
{
	/* ===================== getRandomPronounceableWord() ===================== */

	public function testGetRandomPronounceableWord(): void
	{
		$word = StringGenerator::getRandomPronounceableWord(10);
		$this->assertSame(10, strlen($word));
		$this->assertMatchesRegularExpression('/^[a-z]+$/', $word);
	}

	/* ===================== getRandomString() ===================== */

	public function testGetRandomString(): void
	{
		$str = StringGenerator::getRandomString(10, 'abc');
		$this->assertSame(10, strlen($str));
		$this->assertMatchesRegularExpression('/^[abc]+$/', $str);
	}

	/* ===================== getRandomAlphaString() ===================== */

	public function testGetRandomAlphaString(): void
	{
		$str = StringGenerator::getRandomAlphaString(10);
		$this->assertSame(10, strlen($str));
		$this->assertMatchesRegularExpression('/^[a-z]+$/', $str);
	}

	public function testGetRandomAlphaStringUppercase(): void
	{
		$str = StringGenerator::getRandomAlphaString(10, uppercaseEnabled: true, lowercaseEnabled: false);
		$this->assertSame(10, strlen($str));
		$this->assertMatchesRegularExpression('/^[A-Z]+$/', $str);
	}

	/* ===================== getRandomNumericString() ===================== */

	public function testGetRandomNumericString(): void
	{
		$str = StringGenerator::getRandomNumericString(10);
		$this->assertSame(10, strlen($str));
		$this->assertMatchesRegularExpression('/^[0-9]+$/', $str);
	}

	public function testGetRandomNumericStringNoZeroStart(): void
	{
		$str = StringGenerator::getRandomNumericString(10, startWith0: false);
		$this->assertSame(10, strlen($str));
		$this->assertNotSame('0', $str[0]);
	}

	/* ===================== getRandomAlphanumericString() ===================== */

	public function testGetRandomAlphanumericString(): void
	{
		$str = StringGenerator::getRandomAlphanumericString(10);
		$this->assertSame(10, strlen($str));
		$this->assertMatchesRegularExpression('/^[a-z0-9]+$/', $str);
		// Must contain at least one letter AND one digit
		$this->assertMatchesRegularExpression('/[a-z]/', $str);
		$this->assertMatchesRegularExpression('/[0-9]/', $str);
	}
}