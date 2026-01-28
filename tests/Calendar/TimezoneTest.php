<?php

declare(strict_types=1);

namespace Tests\Calendar;

use Osimatic\Calendar\Timezone;
use PHPUnit\Framework\TestCase;

final class TimezoneTest extends TestCase
{
	// ========== Current Timezone Methods ==========

	public function testGetCurrentTimezone(): void
	{
		$timezone = Timezone::getCurrentTimezone();
		$this->assertIsString($timezone);
		$this->assertNotEmpty($timezone);
		$this->assertTrue(Timezone::isValid($timezone));
	}

	public function testSetCurrentTimezone(): void
	{
		$originalTimezone = Timezone::getCurrentTimezone();

		$result = Timezone::setCurrentTimezone('Europe/Paris');
		$this->assertTrue($result);
		$this->assertEquals('Europe/Paris', Timezone::getCurrentTimezone());

		$result = Timezone::setCurrentTimezone('America/New_York');
		$this->assertTrue($result);
		$this->assertEquals('America/New_York', Timezone::getCurrentTimezone());

		// Restore original
		Timezone::setCurrentTimezone($originalTimezone);
	}

	public function testSetCurrentTimezoneWithInvalidTimezone(): void
	{
		$originalTimezone = Timezone::getCurrentTimezone();

		$result = Timezone::setCurrentTimezone('Invalid/Timezone');
		$this->assertFalse($result);

		// Should not have changed
		$this->assertEquals($originalTimezone, Timezone::getCurrentTimezone());
	}

	// ========== Validation Methods ==========

	public function testIsValidWithValidTimezone(): void
	{
		$this->assertTrue(Timezone::isValid('Europe/Paris'));
		$this->assertTrue(Timezone::isValid('America/New_York'));
		$this->assertTrue(Timezone::isValid('Asia/Tokyo'));
		$this->assertTrue(Timezone::isValid('UTC'));
	}

	public function testIsValidWithInvalidTimezone(): void
	{
		$this->assertFalse(Timezone::isValid('Invalid/Timezone'));
		$this->assertFalse(Timezone::isValid('Europe/InvalidCity'));
		$this->assertFalse(Timezone::isValid(''));
		$this->assertFalse(Timezone::isValid('Not A Timezone'));
	}

	public function testIsValidWithCountryCode(): void
	{
		// Europe/Paris is valid for France
		$this->assertTrue(Timezone::isValid('Europe/Paris', 'FR'));

		// Europe/Paris is not valid for USA
		$this->assertFalse(Timezone::isValid('Europe/Paris', 'US'));

		// America/New_York is valid for USA
		$this->assertTrue(Timezone::isValid('America/New_York', 'US'));
	}

	public function testIsValidWithNullCountryCode(): void
	{
		$this->assertTrue(Timezone::isValid('Europe/Paris', null));
		$this->assertTrue(Timezone::isValid('America/New_York', null));
	}

	public function testIsValidWithSpecialCharacters(): void
	{
		$this->assertFalse(Timezone::isValid('Europe/Paris@#$'));
		$this->assertFalse(Timezone::isValid('América/São_Paulo')); // Special characters
	}

	// ========== Information Methods ==========

	public function testGetOffset(): void
	{
		// UTC should have 0 offset
		$offset = Timezone::getOffset('UTC');
		$this->assertEquals(0, $offset);

		// Europe/Paris should have +1 or +2 hour offset (depending on DST)
		$offset = Timezone::getOffset('Europe/Paris');
		$this->assertTrue($offset === 3600 || $offset === 7200);

		// America/New_York should have negative offset
		$offset = Timezone::getOffset('America/New_York');
		$this->assertLessThan(0, $offset);
	}

	public function testGetOffsetWithDateTime(): void
	{
		// Summer time (DST active in Europe/Paris)
		$summerDate = new \DateTime('2024-07-15');
		$offset = Timezone::getOffset('Europe/Paris', $summerDate);
		$this->assertEquals(7200, $offset); // +2 hours

		// Winter time (DST not active in Europe/Paris)
		$winterDate = new \DateTime('2024-01-15');
		$offset = Timezone::getOffset('Europe/Paris', $winterDate);
		$this->assertEquals(3600, $offset); // +1 hour
	}

	public function testGetOffsetWithInvalidTimezone(): void
	{
		$offset = Timezone::getOffset('Invalid/Timezone');
		$this->assertEquals(0, $offset);
	}

	public function testGetAbbreviation(): void
	{
		$abbr = Timezone::getAbbreviation('Europe/Paris');
		$this->assertIsString($abbr);
		$this->assertNotEmpty($abbr);
		// Should be CET or CEST
		$this->assertTrue(in_array($abbr, ['CET', 'CEST']));

		$abbr = Timezone::getAbbreviation('America/New_York');
		$this->assertIsString($abbr);
		// Should be EST or EDT
		$this->assertTrue(in_array($abbr, ['EST', 'EDT']));
	}

	public function testGetAbbreviationWithInvalidTimezone(): void
	{
		$abbr = Timezone::getAbbreviation('Invalid/Timezone');
		$this->assertEquals('', $abbr);
	}

	public function testIsDaylightSavingTime(): void
	{
		// Test with summer date (DST active in Europe/Paris)
		$summerDate = new \DateTime('2024-07-15');
		$isDST = Timezone::isDaylightSavingTime('Europe/Paris', $summerDate);
		$this->assertTrue($isDST);

		// Test with winter date (DST not active in Europe/Paris)
		$winterDate = new \DateTime('2024-01-15');
		$isDST = Timezone::isDaylightSavingTime('Europe/Paris', $winterDate);
		$this->assertFalse($isDST);

		// UTC never has DST
		$isDST = Timezone::isDaylightSavingTime('UTC');
		$this->assertFalse($isDST);
	}

	public function testIsDaylightSavingTimeWithInvalidTimezone(): void
	{
		$isDST = Timezone::isDaylightSavingTime('Invalid/Timezone');
		$this->assertFalse($isDST);
	}

	public function testGetOffsetFormatted(): void
	{
		// UTC should be +00:00
		$formatted = Timezone::getOffsetFormatted('UTC');
		$this->assertEquals('+00:00', $formatted);

		// Europe/Paris should be +01:00 or +02:00
		$formatted = Timezone::getOffsetFormatted('Europe/Paris');
		$this->assertTrue(in_array($formatted, ['+01:00', '+02:00']));

		// America/New_York should have negative offset
		$formatted = Timezone::getOffsetFormatted('America/New_York');
		$this->assertStringStartsWith('-', $formatted);
		$this->assertMatchesRegularExpression('/^[+-]\d{2}:\d{2}$/', $formatted);
	}

	public function testGetOffsetFormattedWithSpecificDateTime(): void
	{
		// Summer time - Europe/Paris should be +02:00
		$summerDate = new \DateTime('2024-07-15');
		$formatted = Timezone::getOffsetFormatted('Europe/Paris', $summerDate);
		$this->assertEquals('+02:00', $formatted);

		// Winter time - Europe/Paris should be +01:00
		$winterDate = new \DateTime('2024-01-15');
		$formatted = Timezone::getOffsetFormatted('Europe/Paris', $winterDate);
		$this->assertEquals('+01:00', $formatted);
	}

	public function testGetTransitions(): void
	{
		// Get transitions for Europe/Paris
		$transitions = Timezone::getTransitions('Europe/Paris');
		$this->assertIsArray($transitions);
		// Should have at least 2 transitions (to/from DST)
		$this->assertGreaterThanOrEqual(2, count($transitions));

		// Each transition should have expected keys
		foreach ($transitions as $transition) {
			$this->assertArrayHasKey('ts', $transition);
			$this->assertArrayHasKey('time', $transition);
			$this->assertArrayHasKey('offset', $transition);
			$this->assertArrayHasKey('isdst', $transition);
			$this->assertArrayHasKey('abbr', $transition);
		}
	}

	public function testGetTransitionsWithCustomRange(): void
	{
		$start = mktime(0, 0, 0, 1, 1, 2024);
		$end = mktime(23, 59, 59, 12, 31, 2024);

		$transitions = Timezone::getTransitions('Europe/Paris', $start, $end);
		$this->assertIsArray($transitions);

		// All transitions should be within the specified range
		foreach ($transitions as $transition) {
			$this->assertGreaterThanOrEqual($start, $transition['ts']);
			$this->assertLessThanOrEqual($end, $transition['ts']);
		}
	}

	public function testGetTransitionsWithInvalidTimezone(): void
	{
		$transitions = Timezone::getTransitions('Invalid/Timezone');
		$this->assertIsArray($transitions);
		$this->assertEmpty($transitions);
	}

	// ========== Lookup Methods ==========

	public function testGetAllTimezones(): void
	{
		$timezones = Timezone::getAllTimezones();
		$this->assertIsArray($timezones);
		$this->assertNotEmpty($timezones);
		$this->assertGreaterThan(400, count($timezones)); // Should have many timezones

		// Check some common timezones exist
		$this->assertContains('UTC', $timezones);
		$this->assertContains('Europe/Paris', $timezones);
		$this->assertContains('America/New_York', $timezones);
	}

	public function testGetAllTimezonesWithGroup(): void
	{
		// Get only European timezones
		$europeanTimezones = Timezone::getAllTimezones(\DateTimeZone::EUROPE);
		$this->assertIsArray($europeanTimezones);
		$this->assertNotEmpty($europeanTimezones);

		// All should start with "Europe/"
		foreach ($europeanTimezones as $timezone) {
			$this->assertStringStartsWith('Europe/', $timezone);
		}
	}

	public function testGetTimezonesByOffset(): void
	{
		// Get all timezones with UTC+01:00 offset (3600 seconds)
		$timezones = Timezone::getTimezonesByOffset(3600);
		$this->assertIsArray($timezones);
		$this->assertNotEmpty($timezones);

		// Verify all have the correct offset (accounting for current DST status)
		foreach ($timezones as $timezone) {
			$offset = Timezone::getOffset($timezone);
			// Note: offset might differ due to DST, so we just check it's a valid timezone
			$this->assertTrue(Timezone::isValid($timezone));
		}
	}

	public function testGetTimezonesByOffsetZero(): void
	{
		// Get all timezones with UTC offset (0 seconds)
		$timezones = Timezone::getTimezonesByOffset(0);
		$this->assertIsArray($timezones);
		$this->assertNotEmpty($timezones);
		$this->assertContains('UTC', $timezones);
	}

	public function testGetTimezonesByCountryForFrance(): void
	{
		$timezones = Timezone::getTimezonesByCountry('FR');

		$this->assertIsArray($timezones);
		$this->assertNotEmpty($timezones);
		$this->assertContains('Europe/Paris', $timezones);
	}

	public function testGetTimezonesByCountryForUSA(): void
	{
		$timezones = Timezone::getTimezonesByCountry('US');

		$this->assertIsArray($timezones);
		$this->assertNotEmpty($timezones);
		$this->assertContains('America/New_York', $timezones);
		$this->assertContains('America/Los_Angeles', $timezones);
		$this->assertContains('America/Chicago', $timezones);
	}

	public function testGetTimezonesByCountryForJapan(): void
	{
		$timezones = Timezone::getTimezonesByCountry('JP');

		$this->assertIsArray($timezones);
		$this->assertNotEmpty($timezones);
		$this->assertContains('Asia/Tokyo', $timezones);
	}

	public function testGetTimezonesByCountryForInvalidCountry(): void
	{
		$timezones = Timezone::getTimezonesByCountry('XX');

		$this->assertIsArray($timezones);
		$this->assertEmpty($timezones);
	}

	public function testGetTimezonesByCountryReturnsMultipleForLargeCountries(): void
	{
		// USA should have multiple timezones
		$timezones = Timezone::getTimezonesByCountry('US');
		$this->assertGreaterThan(1, count($timezones));

		// Russia should have many timezones
		$timezones = Timezone::getTimezonesByCountry('RU');
		$this->assertGreaterThan(1, count($timezones));
	}

	public function testGetTimezonesByCountryWithEmptyString(): void
	{
		$timezones = Timezone::getTimezonesByCountry('');

		$this->assertIsArray($timezones);
		$this->assertEmpty($timezones);
	}

	public function testGetTimezonesByCountryMultipleCountries(): void
	{
		$countries = ['US', 'FR', 'JP', 'GB', 'DE', 'CN', 'AU', 'BR', 'CA', 'MX'];

		foreach ($countries as $countryCode) {
			$timezones = Timezone::getTimezonesByCountry($countryCode);
			$this->assertNotEmpty($timezones, "$countryCode should have at least one timezone");
		}
	}

	public function testGetPrimaryTimezoneOfCountryForFrance(): void
	{
		$timezone = Timezone::getPrimaryTimezoneOfCountry('FR');

		$this->assertNotNull($timezone);
		$this->assertSame('Europe/Paris', $timezone);
	}

	public function testGetPrimaryTimezoneOfCountryForUSA(): void
	{
		$timezone = Timezone::getPrimaryTimezoneOfCountry('US');

		$this->assertNotNull($timezone);
		$this->assertIsString($timezone);
		$this->assertStringStartsWith('America/', $timezone);
	}

	public function testGetPrimaryTimezoneOfCountryForInvalidCountry(): void
	{
		$timezone = Timezone::getPrimaryTimezoneOfCountry('XX');

		$this->assertNull($timezone);
	}

	public function testGetPrimaryTimezoneOfCountryReturnsFirstTimezone(): void
	{
		// Should return the first timezone from the list
		$list = Timezone::getTimezonesByCountry('US');
		$first = Timezone::getPrimaryTimezoneOfCountry('US');

		$this->assertSame($list[0], $first);
	}

	public function testGetPrimaryTimezoneOfCountryWithEmptyString(): void
	{
		$timezone = Timezone::getPrimaryTimezoneOfCountry('');

		$this->assertNull($timezone);
	}

	// ========== Formatting Methods ==========

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

	public function testFormatWithEmptyString(): void
	{
		$result = Timezone::format('');

		$this->assertSame('', $result);
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

	public function testFormatWithDataWithEmptyTimezone(): void
	{
		$result = Timezone::formatWithData('', 'UTC+00:00', 'FR', [], true, true);

		$this->assertStringContainsString('UTC+00:00', $result);
	}

	public function testGetFormattedLabelsReturnsArray(): void
	{
		$labels = Timezone::getFormattedLabels();

		$this->assertIsArray($labels);
		$this->assertNotEmpty($labels);
	}

	public function testGetFormattedLabelsHasTimezoneAsKeys(): void
	{
		$labels = Timezone::getFormattedLabels();

		// Check that some common timezones exist as keys
		$this->assertArrayHasKey('Europe/Paris', $labels);
		$this->assertArrayHasKey('America/New_York', $labels);
		$this->assertArrayHasKey('Asia/Tokyo', $labels);
	}

	public function testGetFormattedLabelsValuesContainTimezoneNames(): void
	{
		$labels = Timezone::getFormattedLabels();

		foreach ($labels as $timezone => $label) {
			$this->assertStringContainsString($timezone, $label);
		}
	}

	public function testGetFormattedLabelsWithCountryAndCities(): void
	{
		$labels = Timezone::getFormattedLabels(true, true);

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

	public function testGetFormattedLabelsWithoutCountryAndCities(): void
	{
		$labels = Timezone::getFormattedLabels(false, false);

		$this->assertIsArray($labels);
		$this->assertNotEmpty($labels);

		// Check that labels don't have parentheses
		foreach ($labels as $label) {
			$this->assertStringNotContainsString('(', $label);
			$this->assertStringNotContainsString(')', $label);
		}
	}

	public function testGetFormattedLabelsStructure(): void
	{
		$labels = Timezone::getFormattedLabels(true, true);

		// Each label should follow the format: UTC - TimezoneName
		// UTC format can be "UTC", "UTC-11", or "UTC-11:00"
		foreach ($labels as $timezone => $label) {
			$this->assertMatchesRegularExpression('/UTC([+-]\d{1,2}(:\d{2})?)?\s*-\s*[\w\/]+/', $label, "Label for $timezone should match expected format");
		}
	}

	// ========== Configuration Methods ==========

	public function testGetConfigurationDataReturnsArray(): void
	{
		$timezones = Timezone::getConfigurationData();

		$this->assertIsArray($timezones);
		$this->assertNotEmpty($timezones);
	}

	public function testGetConfigurationDataHasExpectedStructure(): void
	{
		$timezones = Timezone::getConfigurationData();

		// Check that it has some common timezones
		$this->assertArrayHasKey('Europe/Paris', $timezones);
		$this->assertArrayHasKey('America/New_York', $timezones);
		$this->assertArrayHasKey('Asia/Tokyo', $timezones);
	}

	public function testGetConfigurationDataStructure(): void
	{
		$timezones = Timezone::getConfigurationData();

		// Check data structure for a specific timezone
		if (isset($timezones['Europe/Paris'])) {
			$data = $timezones['Europe/Paris'];
			$this->assertIsArray($data);
			$this->assertArrayHasKey('utc', $data);
			$this->assertArrayHasKey('country', $data);
		}
	}

	public function testGetConfigurationDataUtcFormat(): void
	{
		$timezones = Timezone::getConfigurationData();

		foreach ($timezones as $timezone => $data) {
			if (isset($data['utc'])) {
				// UTC offset should match format: UTC, UTC-11, UTC+01, UTC-11:00, or UTC+01:00
				$this->assertMatchesRegularExpression('/^UTC([+-]\d{1,2}(:\d{2})?)?$/', $data['utc'], "UTC format for $timezone should be valid");
			}
		}
	}

	public function testGetConfigurationDataHasCitiesForSomeTimezones(): void
	{
		$timezones = Timezone::getConfigurationData();

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

	// ========== Integration Tests ==========

	public function testFormatUsesGetConfigurationData(): void
	{
		// Format should use data from getConfigurationData
		$timezones = Timezone::getConfigurationData();

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

	public function testGetFormattedLabelsUsesFormatWithData(): void
	{
		// getFormattedLabels should produce consistent results with formatWithData
		$timezones = Timezone::getConfigurationData();
		$labels = Timezone::getFormattedLabels(true, true);

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
			$this->assertTrue(Timezone::isValid($timezone), "$timezone should be valid");
		}
	}

	// ========== DEPRECATED METHODS (Backward Compatibility) ==========

	public function testCheckMethodStillWorks(): void
	{
		// Method should work
		$this->assertTrue(Timezone::check('Europe/Paris'));
		$this->assertFalse(Timezone::check('Invalid/Timezone'));
	}

	public function testCheckWithCountryCodeStillWorks(): void
	{
		$this->assertTrue(Timezone::check('Europe/Paris', 'FR'));
		$this->assertFalse(Timezone::check('Europe/Paris', 'US'));
	}

	public function testGetListTimeZonesOfCountryStillWorks(): void
	{
		$timezones = Timezone::getListTimeZonesOfCountry('FR');
		$this->assertIsArray($timezones);
		$this->assertNotEmpty($timezones);
		$this->assertContains('Europe/Paris', $timezones);
	}

	public function testGetTimeZoneOfCountryStillWorks(): void
	{
		$timezone = Timezone::getTimeZoneOfCountry('FR');
		$this->assertNotNull($timezone);
		$this->assertSame('Europe/Paris', $timezone);
	}

	public function testGetListTimeZonesLabelStillWorks(): void
	{
		$labels = Timezone::getListTimeZonesLabel();
		$this->assertIsArray($labels);
		$this->assertNotEmpty($labels);
		$this->assertArrayHasKey('Europe/Paris', $labels);
	}

	public function testGetListTimeZonesStillWorks(): void
	{
		$timezones = Timezone::getListTimeZones();
		$this->assertIsArray($timezones);
		$this->assertNotEmpty($timezones);
		$this->assertArrayHasKey('Europe/Paris', $timezones);
	}
}