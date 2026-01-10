<?php

declare(strict_types=1);

namespace Tests\Location;

use Osimatic\Location\Country;
use PHPUnit\Framework\TestCase;

final class CountryTest extends TestCase
{
	/* ===================== Locale ===================== */

	public function testGetLocaleByCountryCodeValid(): void
	{
		$locale = Country::getLocaleByCountryCode('FR');
		$this->assertNotNull($locale);
		$this->assertStringContainsString('FR', $locale);

		$locale = Country::getLocaleByCountryCode('US');
		$this->assertNotNull($locale);
		$this->assertStringContainsString('US', $locale);

		$locale = Country::getLocaleByCountryCode('DE');
		$this->assertNotNull($locale);
		$this->assertStringContainsString('DE', $locale);
	}

	public function testGetLocaleByCountryCodeWithUK(): void
	{
		// UK should be converted to GB
		$locale = Country::getLocaleByCountryCode('UK');
		$this->assertNotNull($locale);
		$this->assertStringContainsString('GB', $locale);
	}

	public function testGetLocaleByCountryCodeInvalid(): void
	{
		$locale = Country::getLocaleByCountryCode('XX');
		$this->assertNull($locale);

		$locale = Country::getLocaleByCountryCode('ZZ');
		$this->assertNull($locale);
	}

	public function testGetLocaleByCountryCodeCaseInsensitive(): void
	{
		$locale = Country::getLocaleByCountryCode('fr');
		$this->assertNotNull($locale);
		$this->assertStringContainsString('FR', $locale);
	}

	/* ===================== Country Code ===================== */

	public function testParseWithValidCountryCode(): void
	{
		$this->assertSame('FR', Country::parse('FR'));
		$this->assertSame('US', Country::parse('US'));
		$this->assertSame('DE', Country::parse('DE'));
	}

	public function testParseWithCountryName(): void
	{
		$this->assertSame('FR', Country::parse('France'));
		$this->assertSame('US', Country::parse('États-Unis'));
		$this->assertSame('DE', Country::parse('Allemagne'));
	}

	public function testParseWithInvalidCountry(): void
	{
		$this->assertNull(Country::parse('InvalidCountry'));
		$this->assertNull(Country::parse('XX'));
		$this->assertNull(Country::parse(null));
	}

	public function testCheckCountryCodeValid(): void
	{
		$this->assertTrue(Country::checkCountryCode('FR'));
		$this->assertTrue(Country::checkCountryCode('US'));
		$this->assertTrue(Country::checkCountryCode('DE'));
		$this->assertTrue(Country::checkCountryCode('GB'));
	}

	public function testCheckCountryCodeInvalid(): void
	{
		$this->assertFalse(Country::checkCountryCode('XX'));
		$this->assertFalse(Country::checkCountryCode('ZZ'));
		$this->assertFalse(Country::checkCountryCode(null));
		$this->assertFalse(Country::checkCountryCode(''));
	}

	public function testGetCountryCodeFromLocale(): void
	{
		$this->assertSame('FR', Country::getCountryCodeFromLocale('fr_FR'));
		$this->assertSame('US', Country::getCountryCodeFromLocale('en_US'));
		$this->assertSame('DE', Country::getCountryCodeFromLocale('de_DE'));
		$this->assertSame('CA', Country::getCountryCodeFromLocale('fr_CA'));
	}

	public function testGetCountryCodeFromLocaleNull(): void
	{
		// Should return default locale's country
		$countryCode = Country::getCountryCodeFromLocale(null);
		$this->assertNotNull($countryCode);
	}

	public function testGetCountryCodeFromCountryNameValid(): void
	{
		$this->assertSame('FR', Country::getCountryCodeFromCountryName('France'));
		$this->assertSame('US', Country::getCountryCodeFromCountryName('États-Unis'));
		$this->assertSame('DE', Country::getCountryCodeFromCountryName('Allemagne'));
	}

	public function testGetCountryCodeFromCountryNameCaseInsensitive(): void
	{
		$this->assertSame('FR', Country::getCountryCodeFromCountryName('france'));
		$this->assertSame('FR', Country::getCountryCodeFromCountryName('FRANCE'));
		$this->assertSame('FR', Country::getCountryCodeFromCountryName('FrAnCe'));
	}

	public function testGetCountryCodeFromCountryNameInvalid(): void
	{
		$this->assertNull(Country::getCountryCodeFromCountryName('InvalidCountry'));
		$this->assertNull(Country::getCountryCodeFromCountryName(''));
		$this->assertNull(Country::getCountryCodeFromCountryName(null));
	}

	/* ===================== France Overseas & European Union ===================== */

	public function testIsCountryInFranceOverseasWithCountryCode(): void
	{
		$this->assertTrue(Country::isCountryInFranceOverseas('RE')); // Réunion
		$this->assertTrue(Country::isCountryInFranceOverseas('GP')); // Guadeloupe
		$this->assertTrue(Country::isCountryInFranceOverseas('MQ')); // Martinique
		$this->assertTrue(Country::isCountryInFranceOverseas('YT')); // Mayotte
		$this->assertTrue(Country::isCountryInFranceOverseas('GF')); // Guyane

		$this->assertFalse(Country::isCountryInFranceOverseas('FR'));
		$this->assertFalse(Country::isCountryInFranceOverseas('US'));
		$this->assertFalse(Country::isCountryInFranceOverseas(null));
	}

	public function testIsCountryInFranceOverseasWithZipCode(): void
	{
		// France (not overseas) with overseas zip code should return true
		$this->assertTrue(Country::isCountryInFranceOverseas('FR', '97100'));
		$this->assertTrue(Country::isCountryInFranceOverseas('FR', '97200'));
		$this->assertTrue(Country::isCountryInFranceOverseas('FR', '97400'));

		// France with mainland zip code should return false
		$this->assertFalse(Country::isCountryInFranceOverseas('FR', '75001'));
		$this->assertFalse(Country::isCountryInFranceOverseas('FR', '69001'));

		// Other country with overseas zip code should return true
		$this->assertTrue(Country::isCountryInFranceOverseas('XX', '97100'));
	}

	public function testIsCountryInEuropeanUnion(): void
	{
		// EU countries
		$this->assertTrue(Country::isCountryInEuropeanUnion('FR'));
		$this->assertTrue(Country::isCountryInEuropeanUnion('DE'));
		$this->assertTrue(Country::isCountryInEuropeanUnion('IT'));
		$this->assertTrue(Country::isCountryInEuropeanUnion('ES'));
		$this->assertTrue(Country::isCountryInEuropeanUnion('BE'));
		$this->assertTrue(Country::isCountryInEuropeanUnion('NL'));
		$this->assertTrue(Country::isCountryInEuropeanUnion('PL'));

		// Non-EU countries
		$this->assertFalse(Country::isCountryInEuropeanUnion('US'));
		$this->assertFalse(Country::isCountryInEuropeanUnion('CH'));
		$this->assertFalse(Country::isCountryInEuropeanUnion('NO'));
		$this->assertFalse(Country::isCountryInEuropeanUnion('GB')); // UK left EU (Brexit)
		$this->assertFalse(Country::isCountryInEuropeanUnion(null));
	}

	public function testGetCountryNumericCodeFromCountryCode(): void
	{
		$this->assertSame(250, Country::getCountryNumericCodeFromCountryCode('FR'));
		$this->assertSame(840, Country::getCountryNumericCodeFromCountryCode('US'));
		$this->assertSame(276, Country::getCountryNumericCodeFromCountryCode('DE'));
	}

	public function testGetCountryNumericCodeFromCountryCodeInvalid(): void
	{
		$this->assertNull(Country::getCountryNumericCodeFromCountryCode('XX'));
		$this->assertNull(Country::getCountryNumericCodeFromCountryCode(null));
		$this->assertNull(Country::getCountryNumericCodeFromCountryCode(''));
	}

	/* ===================== Country Name ===================== */

	public function testGetCountryNameFromCountryCode(): void
	{
		$name = Country::getCountryNameFromCountryCode('FR');
		$this->assertNotNull($name);
		$this->assertNotEmpty($name);

		$name = Country::getCountryNameFromCountryCode('US');
		$this->assertNotNull($name);
		$this->assertNotEmpty($name);

		$name = Country::getCountryNameFromCountryCode('DE');
		$this->assertNotNull($name);
		$this->assertNotEmpty($name);
	}

	public function testGetCountryNameFromLocale(): void
	{
		$name = Country::getCountryNameFromLocale('fr_FR');
		$this->assertNotNull($name);
		$this->assertNotEmpty($name);

		$name = Country::getCountryNameFromLocale('en_US');
		$this->assertNotNull($name);
		$this->assertNotEmpty($name);
	}

	public function testGetCountryNameFromLocaleNull(): void
	{
		// Should use default locale
		$name = Country::getCountryNameFromLocale(null);
		$this->assertNotNull($name);
		$this->assertNotEmpty($name);
	}

	public function testFormatCountryNameFromTwig(): void
	{
		$this->assertNotEmpty(Country::formatCountryNameFromTwig('FR'));
		$this->assertNotEmpty(Country::formatCountryNameFromTwig('US'));
		$this->assertNotEmpty(Country::formatCountryNameFromTwig('DE'));
	}

	public function testFormatCountryNameFromTwigNull(): void
	{
		$this->assertSame('', Country::formatCountryNameFromTwig(null));
	}

	/* ===================== Language ===================== */

	public function testGetLanguageFromCountryCode(): void
	{
		$language = Country::getLanguageFromCountryCode('FR');
		$this->assertNotNull($language);
		$this->assertNotEmpty($language);

		$language = Country::getLanguageFromCountryCode('US');
		$this->assertNotNull($language);
		$this->assertNotEmpty($language);

		$language = Country::getLanguageFromCountryCode('DE');
		$this->assertNotNull($language);
		$this->assertNotEmpty($language);
	}

	public function testGetLanguageFromCountryCodeInvalid(): void
	{
		$language = Country::getLanguageFromCountryCode('XX');
		$this->assertNull($language);
	}

	public function testGetLanguageFromLocale(): void
	{
		$language = Country::getLanguageFromLocale('fr_FR');
		$this->assertNotNull($language);
		$this->assertNotEmpty($language);

		$language = Country::getLanguageFromLocale('en_US');
		$this->assertNotNull($language);
		$this->assertNotEmpty($language);
	}

	public function testGetLanguageFromLocaleNull(): void
	{
		// Should use default locale
		$language = Country::getLanguageFromLocale(null);
		$this->assertNotNull($language);
		$this->assertNotEmpty($language);
	}

	/* ===================== Flag ===================== */

	public function testGetFlagCountryIsoCodeFranceOverseas(): void
	{
		$this->assertSame('FR', Country::getFlagCountryIsoCode('YT')); // Mayotte
		$this->assertSame('FR', Country::getFlagCountryIsoCode('GF')); // Guyane
		$this->assertSame('FR', Country::getFlagCountryIsoCode('GP')); // Guadeloupe
		$this->assertSame('FR', Country::getFlagCountryIsoCode('MQ')); // Martinique
		$this->assertSame('FR', Country::getFlagCountryIsoCode('RE')); // Réunion
		$this->assertSame('FR', Country::getFlagCountryIsoCode('MF')); // Saint-Martin
		$this->assertSame('FR', Country::getFlagCountryIsoCode('CP')); // Clipperton
		$this->assertSame('FR', Country::getFlagCountryIsoCode('WF')); // Wallis-et-Futuna
	}

	public function testGetFlagCountryIsoCodeUKOverseas(): void
	{
		$this->assertSame('GB', Country::getFlagCountryIsoCode('SH')); // Sainte-Hélène
		$this->assertSame('GB', Country::getFlagCountryIsoCode('TA')); // Tristan da Cunha
	}

	public function testGetFlagCountryIsoCodeSpainOverseas(): void
	{
		$this->assertSame('ES', Country::getFlagCountryIsoCode('IC')); // Îles Canaries
	}

	public function testGetFlagCountryIsoCodeRegular(): void
	{
		$this->assertSame('FR', Country::getFlagCountryIsoCode('FR'));
		$this->assertSame('US', Country::getFlagCountryIsoCode('US'));
		$this->assertSame('DE', Country::getFlagCountryIsoCode('DE'));
		$this->assertNull(Country::getFlagCountryIsoCode(null));
	}

	/* ===================== European Union Constant ===================== */

	public function testEuropeanUnionConstantContainsExpectedCountries(): void
	{
		$this->assertContains('FR', Country::EUROPEAN_UNION);
		$this->assertContains('DE', Country::EUROPEAN_UNION);
		$this->assertContains('IT', Country::EUROPEAN_UNION);
		$this->assertContains('ES', Country::EUROPEAN_UNION);
		$this->assertContains('BE', Country::EUROPEAN_UNION);
		$this->assertContains('NL', Country::EUROPEAN_UNION);
		$this->assertContains('PL', Country::EUROPEAN_UNION);
		$this->assertNotContains('GB', Country::EUROPEAN_UNION); // UK left EU (Brexit)
	}

	public function testEuropeanUnionConstantCount(): void
	{
		// As per the class definition, there should be 27 countries
		$this->assertCount(27, Country::EUROPEAN_UNION);
	}
}