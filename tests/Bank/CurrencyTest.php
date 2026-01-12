<?php

declare(strict_types=1);

namespace Tests\Bank;

use Osimatic\Bank\Currency;
use PHPUnit\Framework\TestCase;

final class CurrencyTest extends TestCase
{
	/* ===================== check() ===================== */

	public function testCheckWithValidCurrency(): void
	{
		$this->assertTrue(Currency::check('EUR'));
		$this->assertTrue(Currency::check('USD'));
		$this->assertTrue(Currency::check('GBP'));
		$this->assertTrue(Currency::check('JPY'));
		$this->assertTrue(Currency::check('CHF'));
		$this->assertTrue(Currency::check('CAD'));
		$this->assertTrue(Currency::check('AUD'));
	}

	public function testCheckWithInvalidCurrency(): void
	{
		$this->assertFalse(Currency::check('INVALID'));
		$this->assertFalse(Currency::check('ZZZ'));
		$this->assertFalse(Currency::check('123'));
		$this->assertFalse(Currency::check(''));
		$this->assertFalse(Currency::check('EU'));
		$this->assertFalse(Currency::check('EURO'));
	}

	/* ===================== getNumericCode() ===================== */

	public function testGetNumericCodeWithEur(): void
	{
		$this->assertSame(978, Currency::getNumericCode('EUR'));
	}

	public function testGetNumericCodeWithUsd(): void
	{
		$this->assertSame(840, Currency::getNumericCode('USD'));
	}

	public function testGetNumericCodeWithGbp(): void
	{
		$this->assertSame(826, Currency::getNumericCode('GBP'));
	}

	public function testGetNumericCodeWithJpy(): void
	{
		$this->assertSame(392, Currency::getNumericCode('JPY'));
	}

	public function testGetNumericCodeWithChf(): void
	{
		$this->assertSame(756, Currency::getNumericCode('CHF'));
	}

	/* ===================== getCurrencyOfCountry() ===================== */

	public function testGetCurrencyOfCountryWithFrance(): void
	{
		$this->assertSame('EUR', Currency::getCurrencyOfCountry('FR'));
	}

	public function testGetCurrencyOfCountryWithUnitedStates(): void
	{
		$this->assertSame('USD', Currency::getCurrencyOfCountry('US'));
	}

	public function testGetCurrencyOfCountryWithUnitedKingdom(): void
	{
		$this->assertSame('GBP', Currency::getCurrencyOfCountry('GB'));
	}

	public function testGetCurrencyOfCountryWithJapan(): void
	{
		$this->assertSame('JPY', Currency::getCurrencyOfCountry('JP'));
	}

	public function testGetCurrencyOfCountryWithSwitzerland(): void
	{
		$this->assertSame('CHF', Currency::getCurrencyOfCountry('CH'));
	}

	public function testGetCurrencyOfCountryWithGermany(): void
	{
		$this->assertSame('EUR', Currency::getCurrencyOfCountry('DE'));
	}

	public function testGetCurrencyOfCountryWithCanada(): void
	{
		$this->assertSame('CAD', Currency::getCurrencyOfCountry('CA'));
	}

	/* ===================== getNumericCodeOfCountry() ===================== */

	public function testGetNumericCodeOfCountryWithFrance(): void
	{
		$this->assertSame(978, Currency::getNumericCodeOfCountry('FR'));
	}

	public function testGetNumericCodeOfCountryWithUnitedStates(): void
	{
		$this->assertSame(840, Currency::getNumericCodeOfCountry('US'));
	}

	public function testGetNumericCodeOfCountryWithUnitedKingdom(): void
	{
		$this->assertSame(826, Currency::getNumericCodeOfCountry('GB'));
	}

	public function testGetNumericCodeOfCountryWithJapan(): void
	{
		$this->assertSame(392, Currency::getNumericCodeOfCountry('JP'));
	}

	/* ===================== format() ===================== */

	public function testFormatWithEur(): void
	{
		$result = Currency::format(1234.56, 'EUR');

		// Le résultat dépend de la locale, mais devrait contenir le montant
		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	public function testFormatWithUsd(): void
	{
		$result = Currency::format(1234.56, 'USD');

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	public function testFormatWithZero(): void
	{
		$result = Currency::format(0, 'EUR');

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	public function testFormatWithNegativeAmount(): void
	{
		$result = Currency::format(-1234.56, 'EUR');

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	public function testFormatWithCustomDecimals(): void
	{
		$result = Currency::format(1234.5678, 'EUR', 3);

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	public function testFormatWithZeroDecimals(): void
	{
		$result = Currency::format(1234.56, 'EUR', 0);

		$this->assertIsString($result);
		$this->assertNotEmpty($result);
	}

	/* ===================== formatWithCode() ===================== */

	public function testFormatWithCodeWithEur(): void
	{
		$result = Currency::formatWithCode(1234.56, 'EUR');

		$this->assertIsString($result);
		$this->assertStringContainsString('EUR', $result);
		$this->assertStringContainsString('1', $result);
		$this->assertStringContainsString('234', $result);
	}

	public function testFormatWithCodeWithUsd(): void
	{
		$result = Currency::formatWithCode(1234.56, 'USD');

		$this->assertIsString($result);
		$this->assertStringContainsString('USD', $result);
	}

	public function testFormatWithCodeWithZero(): void
	{
		$result = Currency::formatWithCode(0, 'EUR');

		$this->assertIsString($result);
		$this->assertStringContainsString('EUR', $result);
		$this->assertStringContainsString('0', $result);
	}

	public function testFormatWithCodeWithNegativeAmount(): void
	{
		$result = Currency::formatWithCode(-1234.56, 'EUR');

		$this->assertIsString($result);
		$this->assertStringContainsString('EUR', $result);
	}

	public function testFormatWithCodeWithCustomDecimals(): void
	{
		$result = Currency::formatWithCode(1234.5678, 'EUR', 3);

		$this->assertIsString($result);
		$this->assertStringContainsString('EUR', $result);
	}

	public function testFormatWithCodeWithZeroDecimals(): void
	{
		$result = Currency::formatWithCode(1234.56, 'EUR', 0);

		$this->assertIsString($result);
		$this->assertStringContainsString('EUR', $result);
		$this->assertStringContainsString('235', $result); // Rounded
	}

	/* ===================== Integration tests ===================== */

	public function testGetNumericCodeOfCountryMatchesGetNumericCode(): void
	{
		$countryCode = 'FR';
		$currencyCode = Currency::getCurrencyOfCountry($countryCode);

		$this->assertSame(
			Currency::getNumericCode($currencyCode),
			Currency::getNumericCodeOfCountry($countryCode)
		);
	}

	public function testMultipleCurrenciesAreValid(): void
	{
		$currencies = ['EUR', 'USD', 'GBP', 'JPY', 'CHF', 'CAD', 'AUD', 'CNY', 'INR', 'BRL'];

		foreach ($currencies as $currency) {
			$this->assertTrue(Currency::check($currency), "Currency $currency should be valid");
		}
	}
}