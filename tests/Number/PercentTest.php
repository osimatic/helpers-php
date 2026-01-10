<?php

namespace Tests\Number;

use Osimatic\Number\Percent;
use PHPUnit\Framework\TestCase;

final class PercentTest extends TestCase
{
	protected function setUp(): void
	{
		// Set default locale to English for consistent test results
		\Locale::setDefault('en_US');
	}

	/* ===================== Formatting ===================== */

	public function testFormat(): void
	{
		// Basic percentage formatting
		$result = Percent::format(50);
		$this->assertIsString($result);
		$this->assertStringContainsString('50', $result);
		$this->assertMatchesRegularExpression('/[0-9]/', $result);

		// With decimals (default 2)
		$result = Percent::format(33.33);
		$this->assertIsString($result);
		$this->assertMatchesRegularExpression('/[0-9]/', $result);

		// Custom decimal places
		$result = Percent::format(33.333, 3);
		$this->assertIsString($result);
		$this->assertMatchesRegularExpression('/[0-9]/', $result);

		// Zero
		$result = Percent::format(0);
		$this->assertIsString($result);
		$this->assertMatchesRegularExpression('/0/', $result);

		// 100%
		$result = Percent::format(100);
		$this->assertIsString($result);
		$this->assertMatchesRegularExpression('/100/', $result);

		// Negative percentage
		$result = Percent::format(-25);
		$this->assertIsString($result);
		$this->assertMatchesRegularExpression('/-/', $result);
	}

	public function testFormatWithDifferentDecimals(): void
	{
		// 0 decimals
		$result = Percent::format(75, 0);
		$this->assertIsString($result);

		// 1 decimal
		$result = Percent::format(75.5, 1);
		$this->assertIsString($result);

		// 4 decimals
		$result = Percent::format(75.5555, 4);
		$this->assertIsString($result);
	}

	public function testFormatSmallNumbers(): void
	{
		// Very small percentage
		$result = Percent::format(0.01);
		$this->assertIsString($result);

		// Fractional percentage
		$result = Percent::format(0.5);
		$this->assertIsString($result);
	}

	public function testFormatLargeNumbers(): void
	{
		// Over 100%
		$result = Percent::format(150);
		$this->assertIsString($result);
		$this->assertMatchesRegularExpression('/1/', $result);

		// Much larger
		$result = Percent::format(1000);
		$this->assertIsString($result);
	}

	/* ===================== Formatting with French Locale ===================== */

	public function testFormatWithFrenchLocale(): void
	{
		// Change to French locale
		\Locale::setDefault('fr_FR');

		// French format uses comma as decimal separator
		$result = Percent::format(50);
		$this->assertIsString($result);
		$this->assertStringContainsString('50', $result);

		$result = Percent::format(33.33);
		$this->assertIsString($result);
		$this->assertMatchesRegularExpression('/33/', $result);

		// Restore English locale
		\Locale::setDefault('en_US');
	}
}