<?php

declare(strict_types=1);

namespace Tests\Calendar;

use Osimatic\Calendar\Timezone;
use PHPUnit\Framework\TestCase;

final class TimezoneTest extends TestCase
{
	/* ===================== check() ===================== */

	public function testCheckWithValidTimezone(): void
	{
		$this->assertTrue(Timezone::check('Europe/Paris'));
		$this->assertTrue(Timezone::check('America/New_York'));
		$this->assertTrue(Timezone::check('Asia/Tokyo'));
		$this->assertTrue(Timezone::check('UTC'));
	}

	public function testCheckWithInvalidTimezone(): void
	{
		$this->assertFalse(Timezone::check('Invalid/Timezone'));
		$this->assertFalse(Timezone::check('Europe/InvalidCity'));
		$this->assertFalse(Timezone::check(''));
		$this->assertFalse(Timezone::check('Not A Timezone'));
	}

	public function testCheckWithCountryCode(): void
	{
		// Europe/Paris is valid for France
		$this->assertTrue(Timezone::check('Europe/Paris', 'FR'));

		// Europe/Paris is not valid for USA
		$this->assertFalse(Timezone::check('Europe/Paris', 'US'));

		// America/New_York is valid for USA
		$this->assertTrue(Timezone::check('America/New_York', 'US'));
	}

	public function testCheckWithNullCountryCode(): void
	{
		$this->assertTrue(Timezone::check('Europe/Paris', null));
		$this->assertTrue(Timezone::check('America/New_York', null));
	}

	/* ===================== format() ===================== */

	public function testFormatWithValidTimezone(): void
	{
		$result = Timezone::format('Europe/Paris');

		$this->assertNotEmpty($result);
		$this->assertStringContainsString('Europe/Paris', $result);
	}

	public function testFormatWithInvalidTimezone(): void
	{
		$result = Timezone::format('Invalid/Timezone');

		$this->assertSame('', $result);
	}

	public function testFormatWithCountryEnabled(): void
	{
		$result = Timezone::format('Europe/Paris', true, false);

		$this->assertNotEmpty($result);
		$this->assertStringContainsString('Europe/Paris', $result);
	}

	public function testFormatWithCountryDisabled(): void
	{
		$result = Timezone::format('Europe/Paris', false, false);

		$this->assertNotEmpty($result);
		$this->assertStringContainsString('Europe/Paris', $result);
	}

	public function testFormatWithCitiesEnabled(): void
	{
		$result = Timezone::format('Europe/Paris', false, true);

		$this->assertNotEmpty($result);
		$this->assertStringContainsString('Europe/Paris', $result);
	}

	public function testFormatIsCaseInsensitive(): void
	{
		$result1 = Timezone::format('Europe/Paris');
		$result2 = Timezone::format('europe/paris');
		$result3 = Timezone::format('EUROPE/PARIS');

		$this->assertSame($result1, $result2);
		$this->assertSame($result1, $result3);
	}

	/* ===================== formatWithData() ===================== */

	public function testFormatWithDataBasic(): void
	{
		$result = Timezone::formatWithData('Europe/Paris', 'UTC+01:00', 'FR', ['Paris'], true, true);

		$this->assertStringContainsString('UTC+01:00', $result);
		$this->assertStringContainsString('Europe/Paris', $result);
		$this->assertStringContainsString('Paris', $result);
	}

	public function testFormatWithDataWithoutCountry(): void
	{
		$result = Timezone::formatWithData('Europe/Paris', 'UTC+01:00', 'FR', ['Paris'], false, true);

		$this->assertStringContainsString('UTC+01:00', $result);
		$this->assertStringContainsString('Europe/Paris', $result);
		$this->assertStringContainsString('Paris', $result);
	}

	public function testFormatWithDataWithoutCities(): void
	{
		$result = Timezone::formatWithData('Europe/Paris', 'UTC+01:00', 'FR', ['Paris'], true, false);

		$this->assertStringContainsString('UTC+01:00', $result);
		$this->assertStringContainsString('Europe/Paris', $result);
	}

	public function testFormatWithDataWithoutCountryAndCities(): void
	{
		$result = Timezone::formatWithData('Europe/Paris', 'UTC+01:00', 'FR', ['Paris'], false, false);

		$this->assertStringContainsString('UTC+01:00', $result);
		$this->assertStringContainsString('Europe/Paris', $result);
		$this->assertStringNotContainsString('(', $result);
		$this->assertStringNotContainsString(')', $result);
	}

	public function testFormatWithDataWithMultipleCities(): void
	{
		$result = Timezone::formatWithData('America/New_York', 'UTC-05:00', 'US', ['New York', 'Boston', 'Philadelphia'], true, true);

		$this->assertStringContainsString('UTC-05:00', $result);
		$this->assertStringContainsString('America/New_York', $result);
		$this->assertStringContainsString('New York', $result);
		$this->assertStringContainsString('Boston', $result);
		$this->assertStringContainsString('Philadelphia', $result);
		$this->assertStringContainsString(', ', $result);
	}

	public function testFormatWithDataWithNullCountry(): void
	{
		$result = Timezone::formatWithData('UTC', 'UTC+00:00', null, [], true, true);

		$this->assertStringContainsString('UTC+00:00', $result);
		$this->assertStringContainsString('UTC', $result);
	}

	public function testFormatWithDataWithEmptyCities(): void
	{
		$result = Timezone::formatWithData('Europe/Paris', 'UTC+01:00', 'FR', [], true, true);

		$this->assertStringContainsString('UTC+01:00', $result);
		$this->assertStringContainsString('Europe/Paris', $result);
	}

	public function testFormatWithDataWithCountryName(): void
	{
		$result = Timezone::formatWithData('Europe/Paris', 'UTC+01:00', 'France', ['Paris'], true, false);

		$this->assertStringContainsString('UTC+01:00', $result);
		$this->assertStringContainsString('Europe/Paris', $result);
		$this->assertStringContainsString('France', $result);
	}

	public function testFormatWithDataStructure(): void
	{
		$result = Timezone::formatWithData('Europe/Paris', 'UTC+01:00', 'FR', ['Paris', 'Lyon'], true, true);

		// Check format: UTC - TimezoneName (Country : Cities)
		$this->assertMatchesRegularExpression('/UTC[+-]\d{2}:\d{2}\s*-\s*[\w\/]+/', $result);
		$this->assertStringContainsString('(', $result);
		$this->assertStringContainsString(')', $result);
		$this->assertStringContainsString(':', $result);
	}

	/* ===================== getListTimeZonesOfCountry() ===================== */

	public function testGetListTimeZonesOfCountryForFrance(): void
	{
		$timezones = Timezone::getListTimeZonesOfCountry('FR');

		$this->assertIsArray($timezones);
		$this->assertNotEmpty($timezones);
		$this->assertContains('Europe/Paris', $timezones);
	}

	public function testGetListTimeZonesOfCountryForUSA(): void
	{
		$timezones = Timezone::getListTimeZonesOfCountry('US');

		$this->assertIsArray($timezones);
		$this->assertNotEmpty($timezones);
		$this->assertContains('America/New_York', $timezones);
		$this->assertContains('America/Los_Angeles', $timezones);
		$this->assertContains('America/Chicago', $timezones);
	}

	public function testGetListTimeZonesOfCountryForJapan(): void
	{
		$timezones = Timezone::getListTimeZonesOfCountry('JP');

		$this->assertIsArray($timezones);
		$this->assertNotEmpty($timezones);
		$this->assertContains('Asia/Tokyo', $timezones);
	}

	public function testGetListTimeZonesOfCountryForInvalidCountry(): void
	{
		$timezones = Timezone::getListTimeZonesOfCountry('XX');

		$this->assertIsArray($timezones);
		$this->assertEmpty($timezones);
	}

	public function testGetListTimeZonesOfCountryReturnsMultipleForLargeCountries(): void
	{
		// USA should have multiple timezones
		$timezones = Timezone::getListTimeZonesOfCountry('US');
		$this->assertGreaterThan(1, count($timezones));

		// Russia should have many timezones
		$timezones = Timezone::getListTimeZonesOfCountry('RU');
		$this->assertGreaterThan(1, count($timezones));
	}

	/* ===================== getTimeZoneOfCountry() ===================== */

	public function testGetTimeZoneOfCountryForFrance(): void
	{
		$timezone = Timezone::getTimeZoneOfCountry('FR');

		$this->assertNotNull($timezone);
		$this->assertSame('Europe/Paris', $timezone);
	}

	public function testGetTimeZoneOfCountryForUSA(): void
	{
		$timezone = Timezone::getTimeZoneOfCountry('US');

		$this->assertNotNull($timezone);
		$this->assertIsString($timezone);
		$this->assertStringStartsWith('America/', $timezone);
	}

	public function testGetTimeZoneOfCountryForInvalidCountry(): void
	{
		$timezone = Timezone::getTimeZoneOfCountry('XX');

		$this->assertNull($timezone);
	}

	public function testGetTimeZoneOfCountryReturnsFirstTimezone(): void
	{
		// Should return the first timezone from the list
		$list = Timezone::getListTimeZonesOfCountry('US');
		$first = Timezone::getTimeZoneOfCountry('US');

		$this->assertSame($list[0], $first);
	}

	/* ===================== getListTimeZonesLabel() ===================== */

	public function testGetListTimeZonesLabelReturnsArray(): void
	{
		$labels = Timezone::getListTimeZonesLabel();

		$this->assertIsArray($labels);
		$this->assertNotEmpty($labels);
	}

	public function testGetListTimeZonesLabelHasTimezoneAsKeys(): void
	{
		$labels = Timezone::getListTimeZonesLabel();

		// Check that some common timezones exist as keys
		$this->assertArrayHasKey('Europe/Paris', $labels);
		$this->assertArrayHasKey('America/New_York', $labels);
		$this->assertArrayHasKey('Asia/Tokyo', $labels);
	}

	public function testGetListTimeZonesLabelValuesContainTimezoneNames(): void
	{
		$labels = Timezone::getListTimeZonesLabel();

		foreach ($labels as $timezone => $label) {
			$this->assertStringContainsString($timezone, $label);
		}
	}

	public function testGetListTimeZonesLabelWithCountryAndCities(): void
	{
		$labels = Timezone::getListTimeZonesLabel(true, true);

		$this->assertIsArray($labels);
		$this->assertNotEmpty($labels);

		// Labels should contain parentheses when country/cities are included
		$hasParentheses = false;
		foreach ($labels as $label) {
			if (str_contains($label, '(') && str_contains($label, ')')) {
				$hasParentheses = true;
				break;
			}
		}
		$this->assertTrue($hasParentheses, 'At least some labels should contain parentheses for country/cities');
	}

	public function testGetListTimeZonesLabelWithoutCountryAndCities(): void
	{
		$labels = Timezone::getListTimeZonesLabel(false, false);

		$this->assertIsArray($labels);
		$this->assertNotEmpty($labels);

		// Check that labels don't have parentheses
		foreach ($labels as $label) {
			$this->assertStringNotContainsString('(', $label);
			$this->assertStringNotContainsString(')', $label);
		}
	}

	public function testGetListTimeZonesLabelStructure(): void
	{
		$labels = Timezone::getListTimeZonesLabel(true, true);

		// Each label should follow the format: UTC - TimezoneName
		// UTC format can be "UTC", "UTC-11", or "UTC-11:00"
		foreach ($labels as $timezone => $label) {
			$this->assertMatchesRegularExpression('/UTC([+-]\d{1,2}(:\d{2})?)?\s*-\s*[\w\/]+/', $label, "Label for $timezone should match expected format");
		}
	}

	/* ===================== getListTimeZones() ===================== */

	public function testGetListTimeZonesReturnsArray(): void
	{
		$timezones = Timezone::getListTimeZones();

		$this->assertIsArray($timezones);
		$this->assertNotEmpty($timezones);
	}

	public function testGetListTimeZonesHasExpectedStructure(): void
	{
		$timezones = Timezone::getListTimeZones();

		// Check that it has some common timezones
		$this->assertArrayHasKey('Europe/Paris', $timezones);
		$this->assertArrayHasKey('America/New_York', $timezones);
		$this->assertArrayHasKey('Asia/Tokyo', $timezones);
	}

	public function testGetListTimeZonesDataStructure(): void
	{
		$timezones = Timezone::getListTimeZones();

		// Check data structure for a specific timezone
		if (isset($timezones['Europe/Paris'])) {
			$data = $timezones['Europe/Paris'];
			$this->assertIsArray($data);
			$this->assertArrayHasKey('utc', $data);
			$this->assertArrayHasKey('country', $data);
		}
	}

	public function testGetListTimeZonesUtcFormat(): void
	{
		$timezones = Timezone::getListTimeZones();

		foreach ($timezones as $timezone => $data) {
			if (isset($data['utc'])) {
				// UTC offset should match format: UTC, UTC-11, UTC+01, UTC-11:00, or UTC+01:00
				$this->assertMatchesRegularExpression('/^UTC([+-]\d{1,2}(:\d{2})?)?$/', $data['utc'], "UTC format for $timezone should be valid");
			}
		}
	}

	public function testGetListTimeZonesHasCitiesForSomeTimezones(): void
	{
		$timezones = Timezone::getListTimeZones();

		// Check that at least some timezones have cities data
		$hasCities = false;
		foreach ($timezones as $data) {
			if (isset($data['cities']) && !empty($data['cities'])) {
				$hasCities = true;
				$this->assertIsArray($data['cities']);
				break;
			}
		}

		$this->assertTrue($hasCities, 'At least some timezones should have cities data');
	}

	/* ===================== Integration tests ===================== */

	public function testFormatUsesGetListTimeZones(): void
	{
		// Format should use data from getListTimeZones
		$timezones = Timezone::getListTimeZones();

		foreach ($timezones as $timezone => $data) {
			$formatted = Timezone::format($timezone, true, true);

			// Should not be empty if timezone exists in list
			$this->assertNotEmpty($formatted, "Format should work for $timezone");

			// Only test a few to keep test fast
			if (in_array($timezone, ['Europe/Paris', 'America/New_York', 'Asia/Tokyo'])) {
				break;
			}
		}
	}

	public function testGetListTimeZonesLabelUsesFormatWithData(): void
	{
		// getListTimeZonesLabel should produce consistent results with formatWithData
		$timezones = Timezone::getListTimeZones();
		$labels = Timezone::getListTimeZonesLabel(true, true);

		// Check a few specific timezones
		foreach (['Europe/Paris', 'America/New_York', 'Asia/Tokyo'] as $timezone) {
			if (isset($timezones[$timezone]) && isset($labels[$timezone])) {
				$data = $timezones[$timezone];
				$expectedLabel = Timezone::formatWithData(
					$timezone,
					$data['utc'],
					$data['country'],
					$data['cities'] ?? [],
					true,
					true
				);

				$this->assertSame($expectedLabel, $labels[$timezone], "Label for $timezone should match formatWithData output");
			}
		}
	}

	/* ===================== Edge cases ===================== */

	public function testFormatWithEmptyString(): void
	{
		$result = Timezone::format('');

		$this->assertSame('', $result);
	}

	public function testCheckWithSpecialCharacters(): void
	{
		$this->assertFalse(Timezone::check('Europe/Paris@#$'));
		$this->assertFalse(Timezone::check('América/São_Paulo')); // Special characters
	}

	public function testGetTimeZoneOfCountryWithEmptyString(): void
	{
		$timezone = Timezone::getTimeZoneOfCountry('');

		$this->assertNull($timezone);
	}

	public function testFormatWithDataWithEmptyTimezone(): void
	{
		$result = Timezone::formatWithData('', 'UTC+00:00', 'FR', [], true, true);

		$this->assertStringContainsString('UTC+00:00', $result);
	}

	/* ===================== Real-world scenarios ===================== */

	public function testCommonTimezonesAreValid(): void
	{
		$commonTimezones = [
			'UTC',
			'Europe/London',
			'Europe/Paris',
			'Europe/Berlin',
			'America/New_York',
			'America/Chicago',
			'America/Los_Angeles',
			'Asia/Tokyo',
			'Asia/Shanghai',
			'Australia/Sydney',
		];

		foreach ($commonTimezones as $timezone) {
			$this->assertTrue(Timezone::check($timezone), "$timezone should be valid");
		}
	}

	public function testMultipleCountriesHaveTimezones(): void
	{
		$countries = ['US', 'FR', 'JP', 'GB', 'DE', 'CN', 'AU', 'BR', 'CA', 'MX'];

		foreach ($countries as $countryCode) {
			$timezones = Timezone::getListTimeZonesOfCountry($countryCode);
			$this->assertNotEmpty($timezones, "$countryCode should have at least one timezone");
		}
	}

	public function testFormatProducesReadableOutput(): void
	{
		$formatted = Timezone::format('Europe/Paris', true, true);

		// Should contain all key elements in a readable format
		$this->assertNotEmpty($formatted);
		$this->assertStringContainsString('UTC', $formatted);
		$this->assertStringContainsString('Europe/Paris', $formatted);
		$this->assertStringContainsString(' - ', $formatted);
	}
}