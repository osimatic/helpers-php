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
		$this->assertTrue(Country::isValidCountryCode('FR'));
		$this->assertTrue(Country::isValidCountryCode('US'));
		$this->assertTrue(Country::isValidCountryCode('DE'));
		$this->assertTrue(Country::isValidCountryCode('GB'));
	}

	public function testCheckCountryCodeInvalid(): void
	{
		$this->assertFalse(Country::isValidCountryCode('XX'));
		$this->assertFalse(Country::isValidCountryCode('ZZ'));
		$this->assertFalse(Country::isValidCountryCode(null));
		$this->assertFalse(Country::isValidCountryCode(''));
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

	/* ===================== isSchengenArea() ===================== */

	public function testIsSchengenAreaWithSchengenMembers(): void
	{
		// Core Schengen countries
		$this->assertTrue(Country::isSchengenArea('FR')); // France
		$this->assertTrue(Country::isSchengenArea('DE')); // Germany
		$this->assertTrue(Country::isSchengenArea('IT')); // Italy
		$this->assertTrue(Country::isSchengenArea('ES')); // Spain
		$this->assertTrue(Country::isSchengenArea('NL')); // Netherlands
		$this->assertTrue(Country::isSchengenArea('BE')); // Belgium
		$this->assertTrue(Country::isSchengenArea('PT')); // Portugal
		$this->assertTrue(Country::isSchengenArea('GR')); // Greece
		$this->assertTrue(Country::isSchengenArea('AT')); // Austria
		$this->assertTrue(Country::isSchengenArea('LU')); // Luxembourg
	}

	public function testIsSchengenAreaWithNordicCountries(): void
	{
		$this->assertTrue(Country::isSchengenArea('SE')); // Sweden
		$this->assertTrue(Country::isSchengenArea('DK')); // Denmark
		$this->assertTrue(Country::isSchengenArea('FI')); // Finland
		$this->assertTrue(Country::isSchengenArea('NO')); // Norway (not EU but Schengen)
		$this->assertTrue(Country::isSchengenArea('IS')); // Iceland (not EU but Schengen)
	}

	public function testIsSchengenAreaWithEasternEuropeMembers(): void
	{
		$this->assertTrue(Country::isSchengenArea('PL')); // Poland
		$this->assertTrue(Country::isSchengenArea('CZ')); // Czech Republic
		$this->assertTrue(Country::isSchengenArea('SK')); // Slovakia
		$this->assertTrue(Country::isSchengenArea('HU')); // Hungary
		$this->assertTrue(Country::isSchengenArea('SI')); // Slovenia
		$this->assertTrue(Country::isSchengenArea('EE')); // Estonia
		$this->assertTrue(Country::isSchengenArea('LV')); // Latvia
		$this->assertTrue(Country::isSchengenArea('LT')); // Lithuania
	}

	public function testIsSchengenAreaWithSmallCountries(): void
	{
		$this->assertTrue(Country::isSchengenArea('MT')); // Malta
		$this->assertTrue(Country::isSchengenArea('LI')); // Liechtenstein (not EU but Schengen)
		$this->assertTrue(Country::isSchengenArea('CH')); // Switzerland (not EU but Schengen)
	}

	public function testIsSchengenAreaWithNonSchengenEUCountries(): void
	{
		// EU countries that are NOT in Schengen
		$this->assertFalse(Country::isSchengenArea('IE')); // Ireland (EU but not Schengen)
		$this->assertFalse(Country::isSchengenArea('RO')); // Romania (EU but not Schengen yet)
		$this->assertFalse(Country::isSchengenArea('BG')); // Bulgaria (EU but not Schengen yet)
		$this->assertFalse(Country::isSchengenArea('HR')); // Croatia (EU but not Schengen yet)
		$this->assertFalse(Country::isSchengenArea('CY')); // Cyprus (EU but not Schengen)
	}

	public function testIsSchengenAreaWithNonEuropeanCountries(): void
	{
		$this->assertFalse(Country::isSchengenArea('US')); // United States
		$this->assertFalse(Country::isSchengenArea('CA')); // Canada
		$this->assertFalse(Country::isSchengenArea('AU')); // Australia
		$this->assertFalse(Country::isSchengenArea('JP')); // Japan
		$this->assertFalse(Country::isSchengenArea('CN')); // China
		$this->assertFalse(Country::isSchengenArea('BR')); // Brazil
	}

	public function testIsSchengenAreaWithBrexitCountry(): void
	{
		// UK left both EU and Schengen (was never in Schengen)
		$this->assertFalse(Country::isSchengenArea('GB'));
	}

	public function testIsSchengenAreaWithOtherEuropeanNonSchengen(): void
	{
		$this->assertFalse(Country::isSchengenArea('GB')); // United Kingdom
		$this->assertFalse(Country::isSchengenArea('RU')); // Russia
		$this->assertFalse(Country::isSchengenArea('UA')); // Ukraine
		$this->assertFalse(Country::isSchengenArea('TR')); // Turkey
		$this->assertFalse(Country::isSchengenArea('RS')); // Serbia
		$this->assertFalse(Country::isSchengenArea('AL')); // Albania
	}

	public function testIsSchengenAreaTotalCount(): void
	{
		// Verify we have all 26 Schengen countries
		$schengenCountries = [
			'AT', 'BE', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU',
			'IS', 'IT', 'LV', 'LI', 'LT', 'LU', 'MT', 'NL', 'NO', 'PL',
			'PT', 'SK', 'SI', 'ES', 'SE', 'CH',
		];

		foreach ($schengenCountries as $country) {
			$this->assertTrue(Country::isSchengenArea($country), "Country $country should be in Schengen");
		}

		$this->assertCount(26, $schengenCountries);
	}

	/* ===================== getCountriesByContinent() ===================== */

	public function testGetCountriesByContinentEurope(): void
	{
		$countries = Country::getCountriesByContinent(\Osimatic\Location\Continent::EUROPE);

		$this->assertIsArray($countries);
		$this->assertNotEmpty($countries);

		// Check major European countries
		$this->assertContains('FR', $countries); // France
		$this->assertContains('DE', $countries); // Germany
		$this->assertContains('IT', $countries); // Italy
		$this->assertContains('ES', $countries); // Spain
		$this->assertContains('GB', $countries); // United Kingdom
		$this->assertContains('PL', $countries); // Poland
		$this->assertContains('RU', $countries); // Russia
		$this->assertContains('UA', $countries); // Ukraine
		$this->assertContains('SE', $countries); // Sweden
		$this->assertContains('NO', $countries); // Norway
		$this->assertContains('CH', $countries); // Switzerland
		$this->assertContains('GR', $countries); // Greece

		// Should NOT contain non-European countries
		$this->assertNotContains('US', $countries);
		$this->assertNotContains('CN', $countries);
		$this->assertNotContains('BR', $countries);

		// Should have a significant number of countries (40+)
		$this->assertGreaterThan(40, count($countries));
	}

	public function testGetCountriesByContinentAsia(): void
	{
		$countries = Country::getCountriesByContinent(\Osimatic\Location\Continent::ASIA);

		$this->assertIsArray($countries);
		$this->assertNotEmpty($countries);

		// Check major Asian countries
		$this->assertContains('CN', $countries); // China
		$this->assertContains('JP', $countries); // Japan
		$this->assertContains('IN', $countries); // India
		$this->assertContains('KR', $countries); // South Korea
		$this->assertContains('TH', $countries); // Thailand
		$this->assertContains('VN', $countries); // Vietnam
		$this->assertContains('ID', $countries); // Indonesia
		$this->assertContains('PK', $countries); // Pakistan
		$this->assertContains('BD', $countries); // Bangladesh
		$this->assertContains('PH', $countries); // Philippines
		$this->assertContains('MY', $countries); // Malaysia
		$this->assertContains('SG', $countries); // Singapore

		// Should NOT contain non-Asian countries
		$this->assertNotContains('FR', $countries);
		$this->assertNotContains('US', $countries);
		$this->assertNotContains('AU', $countries);
	}

	public function testGetCountriesByContinentAfrica(): void
	{
		$countries = Country::getCountriesByContinent(\Osimatic\Location\Continent::AFRICA);

		$this->assertIsArray($countries);
		$this->assertNotEmpty($countries);

		// Check major African countries
		$this->assertContains('EG', $countries); // Egypt
		$this->assertContains('ZA', $countries); // South Africa
		$this->assertContains('NG', $countries); // Nigeria
		$this->assertContains('KE', $countries); // Kenya
		$this->assertContains('MA', $countries); // Morocco
		$this->assertContains('DZ', $countries); // Algeria
		$this->assertContains('TN', $countries); // Tunisia
		$this->assertContains('ET', $countries); // Ethiopia
		$this->assertContains('GH', $countries); // Ghana
		$this->assertContains('SN', $countries); // Senegal

		// Should NOT contain non-African countries
		$this->assertNotContains('US', $countries);
		$this->assertNotContains('FR', $countries);
		$this->assertNotContains('BR', $countries);

		// Should have 50+ countries
		$this->assertGreaterThan(50, count($countries));
	}

	public function testGetCountriesByContinentNorthAmerica(): void
	{
		$countries = Country::getCountriesByContinent(\Osimatic\Location\Continent::NORTH_AMERICA);

		$this->assertIsArray($countries);
		$this->assertNotEmpty($countries);

		// Check major North American countries
		$this->assertContains('US', $countries); // United States
		$this->assertContains('CA', $countries); // Canada
		$this->assertContains('MX', $countries); // Mexico
		$this->assertContains('GT', $countries); // Guatemala
		$this->assertContains('CR', $countries); // Costa Rica
		$this->assertContains('PA', $countries); // Panama
		$this->assertContains('CU', $countries); // Cuba
		$this->assertContains('HT', $countries); // Haiti
		$this->assertContains('DO', $countries); // Dominican Republic
		$this->assertContains('JM', $countries); // Jamaica

		// Should NOT contain South American countries
		$this->assertNotContains('BR', $countries);
		$this->assertNotContains('AR', $countries);
		$this->assertNotContains('CL', $countries);
	}

	public function testGetCountriesByContinentSouthAmerica(): void
	{
		$countries = Country::getCountriesByContinent(\Osimatic\Location\Continent::SOUTH_AMERICA);

		$this->assertIsArray($countries);
		$this->assertNotEmpty($countries);

		// Check all South American countries
		$this->assertContains('BR', $countries); // Brazil
		$this->assertContains('AR', $countries); // Argentina
		$this->assertContains('CL', $countries); // Chile
		$this->assertContains('CO', $countries); // Colombia
		$this->assertContains('PE', $countries); // Peru
		$this->assertContains('VE', $countries); // Venezuela
		$this->assertContains('EC', $countries); // Ecuador
		$this->assertContains('BO', $countries); // Bolivia
		$this->assertContains('PY', $countries); // Paraguay
		$this->assertContains('UY', $countries); // Uruguay
		$this->assertContains('GY', $countries); // Guyana
		$this->assertContains('SR', $countries); // Suriname

		// Should NOT contain North American countries
		$this->assertNotContains('US', $countries);
		$this->assertNotContains('CA', $countries);
		$this->assertNotContains('MX', $countries);

		// Should have exactly 12 countries
		$this->assertCount(12, $countries);
	}

	public function testGetCountriesByContinentOceania(): void
	{
		$countries = Country::getCountriesByContinent(\Osimatic\Location\Continent::OCEANIA);

		$this->assertIsArray($countries);
		$this->assertNotEmpty($countries);

		// Check major Oceania countries
		$this->assertContains('AU', $countries); // Australia
		$this->assertContains('NZ', $countries); // New Zealand
		$this->assertContains('FJ', $countries); // Fiji
		$this->assertContains('PG', $countries); // Papua New Guinea
		$this->assertContains('WS', $countries); // Samoa
		$this->assertContains('TO', $countries); // Tonga
		$this->assertContains('VU', $countries); // Vanuatu
		$this->assertContains('SB', $countries); // Solomon Islands

		// Should NOT contain Asian countries
		$this->assertNotContains('JP', $countries);
		$this->assertNotContains('CN', $countries);
		$this->assertNotContains('ID', $countries);
	}

	public function testGetCountriesByContinentMiddleEast(): void
	{
		$countries = Country::getCountriesByContinent(\Osimatic\Location\Continent::MIDDLE_EAST);

		$this->assertIsArray($countries);
		$this->assertNotEmpty($countries);

		// Check major Middle Eastern countries
		$this->assertContains('SA', $countries); // Saudi Arabia
		$this->assertContains('AE', $countries); // United Arab Emirates
		$this->assertContains('IL', $countries); // Israel
		$this->assertContains('JO', $countries); // Jordan
		$this->assertContains('LB', $countries); // Lebanon
		$this->assertContains('SY', $countries); // Syria
		$this->assertContains('IQ', $countries); // Iraq
		$this->assertContains('IR', $countries); // Iran
		$this->assertContains('YE', $countries); // Yemen
		$this->assertContains('OM', $countries); // Oman
		$this->assertContains('KW', $countries); // Kuwait
		$this->assertContains('QA', $countries); // Qatar
		$this->assertContains('BH', $countries); // Bahrain
		$this->assertContains('TR', $countries); // Turkey

		// Should NOT contain non-Middle Eastern countries
		$this->assertNotContains('PK', $countries); // Pakistan is in Asia
		$this->assertNotContains('AF', $countries); // Afghanistan is in Asia
		$this->assertNotContains('EG', $countries); // Egypt is in Africa
	}

	public function testGetCountriesByContinentReturnsUniqueCountries(): void
	{
		// Test that each continent returns unique country codes (no duplicates)
		$continents = [
			\Osimatic\Location\Continent::EUROPE,
			\Osimatic\Location\Continent::ASIA,
			\Osimatic\Location\Continent::AFRICA,
			\Osimatic\Location\Continent::NORTH_AMERICA,
			\Osimatic\Location\Continent::SOUTH_AMERICA,
			\Osimatic\Location\Continent::OCEANIA,
		];

		foreach ($continents as $continent) {
			$countries = Country::getCountriesByContinent($continent);
			$unique = array_unique($countries);
			$this->assertCount(count($countries), $unique, "Continent {$continent->name} should have unique country codes");
		}
	}

	/* ===================== getContinentByCountry() ===================== */

	public function testGetContinentByCountryEuropeanCountries(): void
	{
		// Major European countries
		$this->assertSame(\Osimatic\Location\Continent::EUROPE, Country::getContinentByCountry('FR'));
		$this->assertSame(\Osimatic\Location\Continent::EUROPE, Country::getContinentByCountry('DE'));
		$this->assertSame(\Osimatic\Location\Continent::EUROPE, Country::getContinentByCountry('IT'));
		$this->assertSame(\Osimatic\Location\Continent::EUROPE, Country::getContinentByCountry('ES'));
		$this->assertSame(\Osimatic\Location\Continent::EUROPE, Country::getContinentByCountry('GB'));
		$this->assertSame(\Osimatic\Location\Continent::EUROPE, Country::getContinentByCountry('PL'));
		$this->assertSame(\Osimatic\Location\Continent::EUROPE, Country::getContinentByCountry('RU'));
		$this->assertSame(\Osimatic\Location\Continent::EUROPE, Country::getContinentByCountry('CH'));
		$this->assertSame(\Osimatic\Location\Continent::EUROPE, Country::getContinentByCountry('NO'));
		$this->assertSame(\Osimatic\Location\Continent::EUROPE, Country::getContinentByCountry('SE'));
	}

	public function testGetContinentByCountryAsianCountries(): void
	{
		// Major Asian countries
		$this->assertSame(\Osimatic\Location\Continent::ASIA, Country::getContinentByCountry('CN'));
		$this->assertSame(\Osimatic\Location\Continent::ASIA, Country::getContinentByCountry('JP'));
		$this->assertSame(\Osimatic\Location\Continent::ASIA, Country::getContinentByCountry('IN'));
		$this->assertSame(\Osimatic\Location\Continent::ASIA, Country::getContinentByCountry('KR'));
		$this->assertSame(\Osimatic\Location\Continent::ASIA, Country::getContinentByCountry('TH'));
		$this->assertSame(\Osimatic\Location\Continent::ASIA, Country::getContinentByCountry('VN'));
		$this->assertSame(\Osimatic\Location\Continent::ASIA, Country::getContinentByCountry('ID'));
		$this->assertSame(\Osimatic\Location\Continent::ASIA, Country::getContinentByCountry('PK'));
		$this->assertSame(\Osimatic\Location\Continent::ASIA, Country::getContinentByCountry('BD'));
		$this->assertSame(\Osimatic\Location\Continent::ASIA, Country::getContinentByCountry('MY'));
	}

	public function testGetContinentByCountryAfricanCountries(): void
	{
		// Major African countries
		$this->assertSame(\Osimatic\Location\Continent::AFRICA, Country::getContinentByCountry('EG'));
		$this->assertSame(\Osimatic\Location\Continent::AFRICA, Country::getContinentByCountry('ZA'));
		$this->assertSame(\Osimatic\Location\Continent::AFRICA, Country::getContinentByCountry('NG'));
		$this->assertSame(\Osimatic\Location\Continent::AFRICA, Country::getContinentByCountry('KE'));
		$this->assertSame(\Osimatic\Location\Continent::AFRICA, Country::getContinentByCountry('MA'));
		$this->assertSame(\Osimatic\Location\Continent::AFRICA, Country::getContinentByCountry('DZ'));
		$this->assertSame(\Osimatic\Location\Continent::AFRICA, Country::getContinentByCountry('TN'));
		$this->assertSame(\Osimatic\Location\Continent::AFRICA, Country::getContinentByCountry('ET'));
		$this->assertSame(\Osimatic\Location\Continent::AFRICA, Country::getContinentByCountry('GH'));
	}

	public function testGetContinentByCountryNorthAmericanCountries(): void
	{
		// Major North American countries
		$this->assertSame(\Osimatic\Location\Continent::NORTH_AMERICA, Country::getContinentByCountry('US'));
		$this->assertSame(\Osimatic\Location\Continent::NORTH_AMERICA, Country::getContinentByCountry('CA'));
		$this->assertSame(\Osimatic\Location\Continent::NORTH_AMERICA, Country::getContinentByCountry('MX'));
		$this->assertSame(\Osimatic\Location\Continent::NORTH_AMERICA, Country::getContinentByCountry('CU'));
		$this->assertSame(\Osimatic\Location\Continent::NORTH_AMERICA, Country::getContinentByCountry('JM'));
		$this->assertSame(\Osimatic\Location\Continent::NORTH_AMERICA, Country::getContinentByCountry('HT'));
		$this->assertSame(\Osimatic\Location\Continent::NORTH_AMERICA, Country::getContinentByCountry('DO'));
	}

	public function testGetContinentByCountrySouthAmericanCountries(): void
	{
		// All South American countries
		$this->assertSame(\Osimatic\Location\Continent::SOUTH_AMERICA, Country::getContinentByCountry('BR'));
		$this->assertSame(\Osimatic\Location\Continent::SOUTH_AMERICA, Country::getContinentByCountry('AR'));
		$this->assertSame(\Osimatic\Location\Continent::SOUTH_AMERICA, Country::getContinentByCountry('CL'));
		$this->assertSame(\Osimatic\Location\Continent::SOUTH_AMERICA, Country::getContinentByCountry('CO'));
		$this->assertSame(\Osimatic\Location\Continent::SOUTH_AMERICA, Country::getContinentByCountry('PE'));
		$this->assertSame(\Osimatic\Location\Continent::SOUTH_AMERICA, Country::getContinentByCountry('VE'));
		$this->assertSame(\Osimatic\Location\Continent::SOUTH_AMERICA, Country::getContinentByCountry('EC'));
		$this->assertSame(\Osimatic\Location\Continent::SOUTH_AMERICA, Country::getContinentByCountry('BO'));
		$this->assertSame(\Osimatic\Location\Continent::SOUTH_AMERICA, Country::getContinentByCountry('PY'));
		$this->assertSame(\Osimatic\Location\Continent::SOUTH_AMERICA, Country::getContinentByCountry('UY'));
	}

	public function testGetContinentByCountryOceaniaCountries(): void
	{
		// Major Oceania countries
		$this->assertSame(\Osimatic\Location\Continent::OCEANIA, Country::getContinentByCountry('AU'));
		$this->assertSame(\Osimatic\Location\Continent::OCEANIA, Country::getContinentByCountry('NZ'));
		$this->assertSame(\Osimatic\Location\Continent::OCEANIA, Country::getContinentByCountry('FJ'));
		$this->assertSame(\Osimatic\Location\Continent::OCEANIA, Country::getContinentByCountry('PG'));
		$this->assertSame(\Osimatic\Location\Continent::OCEANIA, Country::getContinentByCountry('WS'));
	}

	public function testGetContinentByCountryMiddleEastCountries(): void
	{
		// Middle Eastern countries
		$this->assertSame(\Osimatic\Location\Continent::MIDDLE_EAST, Country::getContinentByCountry('SA'));
		$this->assertSame(\Osimatic\Location\Continent::MIDDLE_EAST, Country::getContinentByCountry('AE'));
		$this->assertSame(\Osimatic\Location\Continent::MIDDLE_EAST, Country::getContinentByCountry('IL'));
		$this->assertSame(\Osimatic\Location\Continent::MIDDLE_EAST, Country::getContinentByCountry('JO'));
		$this->assertSame(\Osimatic\Location\Continent::MIDDLE_EAST, Country::getContinentByCountry('LB'));
		$this->assertSame(\Osimatic\Location\Continent::MIDDLE_EAST, Country::getContinentByCountry('IQ'));
		$this->assertSame(\Osimatic\Location\Continent::MIDDLE_EAST, Country::getContinentByCountry('KW'));
		$this->assertSame(\Osimatic\Location\Continent::MIDDLE_EAST, Country::getContinentByCountry('QA'));
	}

	public function testGetContinentByCountryInvalidCountryCode(): void
	{
		// Invalid or non-existent country codes should return null
		$this->assertNull(Country::getContinentByCountry('XX'));
		$this->assertNull(Country::getContinentByCountry('ZZ'));
		$this->assertNull(Country::getContinentByCountry('AAA'));
		$this->assertNull(Country::getContinentByCountry(''));
	}

	public function testGetContinentByCountryConsistency(): void
	{
		// Test that getContinentByCountry is consistent with getCountriesByContinent
		$continents = [
			\Osimatic\Location\Continent::EUROPE,
			\Osimatic\Location\Continent::ASIA,
			\Osimatic\Location\Continent::AFRICA,
			\Osimatic\Location\Continent::NORTH_AMERICA,
			\Osimatic\Location\Continent::SOUTH_AMERICA,
			\Osimatic\Location\Continent::OCEANIA,
			\Osimatic\Location\Continent::MIDDLE_EAST,
		];

		foreach ($continents as $continent) {
			$countries = Country::getCountriesByContinent($continent);
			foreach ($countries as $countryCode) {
				$foundContinent = Country::getContinentByCountry($countryCode);
				$this->assertNotNull($foundContinent, "Country $countryCode from {$continent->name} should have a continent");
			}
		}
	}

	public function testGetContinentByCountryReturnsFirstMatch(): void
	{
		// Countries that appear in multiple regions should return the first match found
		// For example, Mexico appears in both NORTH_AMERICA and CENTRAL_AMERICA
		$continent = Country::getContinentByCountry('MX');
		$this->assertNotNull($continent);
		$this->assertInstanceOf(\Osimatic\Location\Continent::class, $continent);

		// Cuba appears in both NORTH_AMERICA and CARIBBEAN
		$continent = Country::getContinentByCountry('CU');
		$this->assertNotNull($continent);
		$this->assertInstanceOf(\Osimatic\Location\Continent::class, $continent);
	}

	public function testGetContinentByCountryAllContinentsHaveCountries(): void
	{
		// Verify that all continents (except Antarctica) have at least one country
		$continentsWithCountries = [
			\Osimatic\Location\Continent::EUROPE,
			\Osimatic\Location\Continent::ASIA,
			\Osimatic\Location\Continent::AFRICA,
			\Osimatic\Location\Continent::NORTH_AMERICA,
			\Osimatic\Location\Continent::SOUTH_AMERICA,
			\Osimatic\Location\Continent::OCEANIA,
			\Osimatic\Location\Continent::MIDDLE_EAST,
		];

		foreach ($continentsWithCountries as $continent) {
			$countries = Country::getCountriesByContinent($continent);
			$this->assertNotEmpty($countries, "Continent {$continent->name} should have at least one country");
		}
	}
}