<?php

namespace Tests\Calendar;

use Osimatic\Calendar\PublicHoliday;
use Osimatic\Calendar\PublicHolidayCalendar;
use Osimatic\Calendar\PublicHolidays;
use PHPUnit\Framework\TestCase;

class PublicHolidaysTest extends TestCase
{
	// ========================================
	// Tests for getEasterDateTime()
	// ========================================

	public function testGetEasterDateTime(): void
	{
		$easterDateTime = PublicHolidays::getEasterDateTime(2024);
		self::assertInstanceOf(\DateTime::class, $easterDateTime);
		self::assertEquals('2024-03-31', $easterDateTime->format('Y-m-d'));

		self::assertEquals('2025-04-20', PublicHolidays::getEasterDateTime(2025)->format('Y-m-d'));
		self::assertEquals('2026-04-05', PublicHolidays::getEasterDateTime(2026)->format('Y-m-d'));
		self::assertEquals('2023-04-09', PublicHolidays::getEasterDateTime(2023)->format('Y-m-d'));
		self::assertEquals('2020-04-12', PublicHolidays::getEasterDateTime(2020)->format('Y-m-d'));
	}

	// ========================================
	// Tests for getList()
	// ========================================

	public function testGetList(): void
	{
		// Unknown country returns empty array
		$holidays = PublicHolidays::getList('XX', 2024);
		self::assertIsArray($holidays);
		self::assertEmpty($holidays);

		// Alsace option adds holidays for France
		$holidaysWithoutAlsace = PublicHolidays::getList('FR', 2024);
		$holidaysWithAlsace = PublicHolidays::getList('FR', 2024, ['alsace' => true]);
		self::assertGreaterThan(count($holidaysWithoutAlsace), count($holidaysWithAlsace));

		// Result is sorted by timestamp
		$previousTimestamp = 0;
		foreach ($holidaysWithoutAlsace as $holiday) {
			self::assertGreaterThanOrEqual($previousTimestamp, $holiday->getTimestamp());
			$previousTimestamp = $holiday->getTimestamp();
		}

		// No duplicate keys
		$keys = [];
		foreach ($holidaysWithoutAlsace as $holiday) {
			$key = $holiday->getKey();
			self::assertNotContains($key, $keys, "Duplicate key found: {$key}");
			$keys[] = $key;
		}

		// Same number of holidays across years
		$holidays2023 = PublicHolidays::getList('FR', 2023);
		$holidays2024 = PublicHolidays::getList('FR', 2024);
		self::assertCount(count($holidays2023), $holidays2024);
		self::assertNotEquals($holidays2023[0]->getTimestamp(), $holidays2024[0]->getTimestamp());

		// Easter date changes with year
		$easter2023 = array_values(array_filter($holidays2023, fn ($h) => $h->getKey() === 'paques' && date('Y-m-d', $h->getTimestamp()) === '2023-04-09'));
		$easter2024 = array_values(array_filter($holidays2024, fn ($h) => $h->getKey() === 'paques' && date('Y-m-d', $h->getTimestamp()) === '2024-03-31'));
		self::assertNotEmpty($easter2023);
		self::assertNotEmpty($easter2024);
		self::assertNotEquals($easter2023[0]->getTimestamp(), $easter2024[0]->getTimestamp());

		// Edge cases
		self::assertEmpty(PublicHolidays::getList('', 2024));
		self::assertNotEmpty(PublicHolidays::getList('fr', 2024));
		self::assertNotEmpty(PublicHolidays::getList('FR', 1900));
		self::assertNotEmpty(PublicHolidays::getList('FR', 2100));
	}

	// ========================================
	// Tests for isPublicHoliday()
	// ========================================

	public function testIsPublicHoliday(): void
	{
		// French public holidays
		self::assertTrue(PublicHolidays::isPublicHoliday(new \DateTime('2024-01-01'), 'FR'));
		self::assertTrue(PublicHolidays::isPublicHoliday(new \DateTime('2024-12-25'), 'FR'));
		self::assertTrue(PublicHolidays::isPublicHoliday(new \DateTime('2024-07-14'), 'FR'));
		self::assertTrue(PublicHolidays::isPublicHoliday(new \DateTime('2024-05-01'), 'FR'));
		self::assertTrue(PublicHolidays::isPublicHoliday(new \DateTime('2024-03-31'), 'FR')); // Easter 2024
		self::assertTrue(PublicHolidays::isPublicHoliday(new \DateTime('2024-04-01'), 'FR')); // Easter Monday 2024

		// Regular day is not a holiday
		self::assertFalse(PublicHolidays::isPublicHoliday(new \DateTime('2024-03-15'), 'FR'));

		// July 14 is a holiday in France but not in Belgium
		self::assertTrue(PublicHolidays::isPublicHoliday(new \DateTime('2024-07-14'), 'FR'));
		self::assertFalse(PublicHolidays::isPublicHoliday(new \DateTime('2024-07-14'), 'BE'));

		// July 21 is Belgium's national day, not France's
		self::assertTrue(PublicHolidays::isPublicHoliday(new \DateTime('2024-07-21'), 'BE'));
		self::assertFalse(PublicHolidays::isPublicHoliday(new \DateTime('2024-07-21'), 'FR'));

		// August 1 is Switzerland's national day, not France's
		self::assertTrue(PublicHolidays::isPublicHoliday(new \DateTime('2024-08-01'), 'CH'));
		self::assertFalse(PublicHolidays::isPublicHoliday(new \DateTime('2024-08-01'), 'FR'));

		// Unknown country has no holidays
		self::assertFalse(PublicHolidays::isPublicHoliday(new \DateTime('2024-01-01'), 'XX'));
	}

	// ========================================
	// Tests for isDateCorrespondingToPublicHoliday()
	// ========================================

	public function testIsDateCorrespondingToPublicHoliday(): void
	{
		$publicHoliday = new PublicHoliday('Test Holiday', mktime(0, 0, 0, 12, 25, 2024));

		// Matching date
		self::assertTrue(PublicHolidays::isDateCorrespondingToPublicHoliday($publicHoliday, new \DateTime('2024-12-25')));

		// Non-matching date
		self::assertFalse(PublicHolidays::isDateCorrespondingToPublicHoliday($publicHoliday, new \DateTime('2024-12-26')));

		// Should ignore time and only compare dates
		self::assertTrue(PublicHolidays::isDateCorrespondingToPublicHoliday($publicHoliday, new \DateTime('2024-12-25 15:30:00')));
	}

	// ========================================
	// Tests for getSupportedCountries()
	// ========================================

	public function testGetSupportedCountries(): void
	{
		$countries = PublicHolidays::getSupportedCountries();
		self::assertIsArray($countries);

		$expected = ['FR', 'MQ', 'GP', 'RE', 'GF', 'BE', 'LU', 'CH', 'MU', 'MA', 'DE', 'ES', 'IT', 'GB', 'NL', 'PL', 'PT', 'DZ', 'TN', 'SN', 'CI', 'CM'];
		foreach ($expected as $country) {
			self::assertContains($country, $countries, "Country $country should be in supported countries");
		}
		self::assertCount(count($expected), $countries);
	}

	// ========================================
	// Tests for isSupportedCountry()
	// ========================================

	public function testIsSupportedCountry(): void
	{
		// Supported countries
		self::assertTrue(PublicHolidays::isSupportedCountry('FR'));
		self::assertTrue(PublicHolidays::isSupportedCountry('BE'));
		self::assertTrue(PublicHolidays::isSupportedCountry('DE'));
		self::assertTrue(PublicHolidays::isSupportedCountry('GB'));
		self::assertTrue(PublicHolidays::isSupportedCountry('MQ'));
		self::assertTrue(PublicHolidays::isSupportedCountry('DZ'));

		// Case insensitive
		self::assertTrue(PublicHolidays::isSupportedCountry('fr'));
		self::assertTrue(PublicHolidays::isSupportedCountry('be'));

		// Unsupported countries
		self::assertFalse(PublicHolidays::isSupportedCountry('XX'));
		self::assertFalse(PublicHolidays::isSupportedCountry('US'));
		self::assertFalse(PublicHolidays::isSupportedCountry(''));
	}

	// ========================================
	// Tests by country - France (FR)
	// ========================================

	public function testGetListFrance(): void
	{
		$holidays = PublicHolidays::getList('FR', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		// Key civil holidays
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête nationale') && date('Y-m-d', $h->getTimestamp()) === '2024-07-14' && $h->getKey() === '07-14'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Victoire des Alliés') && date('Y-m-d', $h->getTimestamp()) === '2024-05-08' && $h->getKey() === '05-08'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Armistice') && date('Y-m-d', $h->getTimestamp()) === '2024-11-11' && $h->getKey() === '11-11'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Assomption') && date('Y-m-d', $h->getTimestamp()) === '2024-08-15' && $h->getKey() === '08-15'));

		// Alsace: Good Friday and Saint Stephen are included only with the option
		$holidaysAlsace = PublicHolidays::getList('FR', 2024, ['alsace' => true]);
		self::assertNotEmpty(array_filter($holidaysAlsace, fn ($h) => $h->getKey() === 'vendredi_saint' && date('Y-m-d', $h->getTimestamp()) === '2024-03-29'));
		self::assertNotEmpty(array_filter($holidaysAlsace, fn ($h) => str_contains($h->getName(), 'Saint Étienne') && date('Y-m-d', $h->getTimestamp()) === '2024-12-26' && $h->getKey() === '12-26'));
		self::assertEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'vendredi_saint' && date('Y-m-d', $h->getTimestamp()) === '2024-03-29'));
	}

	// ========================================
	// Tests by country - Belgium (BE)
	// ========================================

	public function testGetListBelgium(): void
	{
		$holidays = PublicHolidays::getList('BE', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête nationale') && date('Y-m-d', $h->getTimestamp()) === '2024-07-21' && $h->getKey() === '07-21'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête du Travail') && date('Y-m-d', $h->getTimestamp()) === '2024-05-01' && $h->getKey() === '05-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Armistice') && date('Y-m-d', $h->getTimestamp()) === '2024-11-11' && $h->getKey() === '11-11'));
	}

	// ========================================
	// Tests by country - Luxembourg (LU)
	// ========================================

	public function testGetListLuxembourg(): void
	{
		$holidays = PublicHolidays::getList('LU', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête nationale') && date('Y-m-d', $h->getTimestamp()) === '2024-06-23' && $h->getKey() === '06-23'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête du Travail') && date('Y-m-d', $h->getTimestamp()) === '2024-05-01' && $h->getKey() === '05-01'));
	}

	// ========================================
	// Tests by country - Switzerland (CH)
	// ========================================

	public function testGetListSwitzerland(): void
	{
		$holidays = PublicHolidays::getList('CH', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête nationale') && date('Y-m-d', $h->getTimestamp()) === '2024-08-01' && $h->getKey() === '08-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête du Travail') && date('Y-m-d', $h->getTimestamp()) === '2024-05-01' && $h->getKey() === '05-01'));
	}

	// ========================================
	// Tests by country - Mauritius (MU)
	// ========================================

	public function testGetListMauritius(): void
	{
		$holidays = PublicHolidays::getList('MU', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête nationale') && date('Y-m-d', $h->getTimestamp()) === '2024-03-12' && $h->getKey() === '03-12'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Abolition de l’esclavage') && date('Y-m-d', $h->getTimestamp()) === '2024-02-01' && $h->getKey() === '02-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getCalendar() === PublicHolidayCalendar::INDIAN));
	}

	// ========================================
	// Tests by country - Morocco (MA)
	// ========================================

	public function testGetListMorocco(): void
	{
		$holidays = PublicHolidays::getList('MA', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Manifeste de l’Indépendance') && date('Y-m-d', $h->getTimestamp()) === '2024-01-11' && $h->getKey() === '01-11'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête du Trône') && date('Y-m-d', $h->getTimestamp()) === '2024-07-30' && $h->getKey() === '07-30'));

		// Islamic holidays 2024
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'aid_el_fitr' && date('Y-m-d', $h->getTimestamp()) === '2024-04-10'));    // 1 Shawwal 1445
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'aid_al_adha' && date('Y-m-d', $h->getTimestamp()) === '2024-06-16'));   // 10 Dhu al-Hijjah 1445
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'jour_an_hegire' && date('Y-m-d', $h->getTimestamp()) === '2024-07-07')); // 1 Muharram 1446
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'al_mawlid' && date('Y-m-d', $h->getTimestamp()) === '2024-09-15'));      // 12 Rabi al-awwal 1446
	}

	// ========================================
	// Tests by country - Martinique (MQ)
	// ========================================

	public function testGetListMartinique(): void
	{
		$holidays = PublicHolidays::getList('MQ', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);

		// Martinique has more holidays than mainland France
		self::assertGreaterThan(count(PublicHolidays::getList('FR', 2024)), count($holidays));

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Abolition de l’esclavage') && date('Y-m-d', $h->getTimestamp()) === '2024-05-22' && $h->getKey() === '05-22'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'mardi_gras' && date('Y-m-d', $h->getTimestamp()) === '2024-02-13'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête Victor Schœlcher') && date('Y-m-d', $h->getTimestamp()) === '2024-07-21' && $h->getKey() === '07-21'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Défunts') && date('Y-m-d', $h->getTimestamp()) === '2024-11-02' && $h->getKey() === '11-02'));
	}

	// ========================================
	// Tests by country - Guadeloupe (GP)
	// ========================================

	public function testGetListGuadeloupe(): void
	{
		$holidays = PublicHolidays::getList('GP', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Abolition de l’esclavage') && date('Y-m-d', $h->getTimestamp()) === '2024-05-27' && $h->getKey() === '05-27'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête Victor Schœlcher') && date('Y-m-d', $h->getTimestamp()) === '2024-07-21' && $h->getKey() === '07-21'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Défunts') && date('Y-m-d', $h->getTimestamp()) === '2024-11-02' && $h->getKey() === '11-02'));
	}

	// ========================================
	// Tests by country - Réunion (RE)
	// ========================================

	public function testGetListReunion(): void
	{
		$holidays = PublicHolidays::getList('RE', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Abolition de l’esclavage') && date('Y-m-d', $h->getTimestamp()) === '2024-12-20' && $h->getKey() === '12-20'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête du Travail') && date('Y-m-d', $h->getTimestamp()) === '2024-05-01' && $h->getKey() === '05-01'));
	}

	// ========================================
	// Tests by country - French Guiana (GF)
	// ========================================

	public function testGetListGuyane(): void
	{
		$holidays = PublicHolidays::getList('GF', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Abolition de l’esclavage') && date('Y-m-d', $h->getTimestamp()) === '2024-06-10' && $h->getKey() === '06-10'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête du Travail') && date('Y-m-d', $h->getTimestamp()) === '2024-05-01' && $h->getKey() === '05-01'));
	}

	// ========================================
	// Tests by country - Germany (DE)
	// ========================================

	public function testGetListGermany(): void
	{
		$holidays = PublicHolidays::getList('DE', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête nationale') && date('Y-m-d', $h->getTimestamp()) === '2024-10-03' && $h->getKey() === '10-03'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'vendredi_saint' && date('Y-m-d', $h->getTimestamp()) === '2024-03-29')); // Good Friday 2024
	}

	// ========================================
	// Tests by country - Spain (ES)
	// ========================================

	public function testGetListSpain(): void
	{
		$holidays = PublicHolidays::getList('ES', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête nationale') && date('Y-m-d', $h->getTimestamp()) === '2024-10-12' && $h->getKey() === '10-12'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Noël') && date('Y-m-d', $h->getTimestamp()) === '2024-12-25' && $h->getKey() === '12-25'));
	}

	// ========================================
	// Tests by country - Italy (IT)
	// ========================================

	public function testGetListItaly(): void
	{
		$holidays = PublicHolidays::getList('IT', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête de la République') && date('Y-m-d', $h->getTimestamp()) === '2024-06-02' && $h->getKey() === '06-02'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'lundi_paques' && date('Y-m-d', $h->getTimestamp()) === '2024-04-01')); // Easter Monday 2024
	}

	// ========================================
	// Tests by country - United Kingdom (GB)
	// ========================================

	public function testGetListUnitedKingdom(): void
	{
		$holidays = PublicHolidays::getList('GB', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'may_bank_holiday' && date('Y-m-d', $h->getTimestamp()) === '2024-05-06')); // First Monday of May 2024
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Noël') && date('Y-m-d', $h->getTimestamp()) === '2024-12-25' && $h->getKey() === '12-25'));
	}

	// ========================================
	// Tests by country - Netherlands (NL)
	// ========================================

	public function testGetListNetherlands(): void
	{
		$holidays = PublicHolidays::getList('NL', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête du Roi') && date('Y-m-d', $h->getTimestamp()) === '2024-04-27' && $h->getKey() === '04-27'));
	}

	// ========================================
	// Tests by country - Poland (PL)
	// ========================================

	public function testGetListPoland(): void
	{
		$holidays = PublicHolidays::getList('PL', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête nationale') && date('Y-m-d', $h->getTimestamp()) === '2024-05-03' && $h->getKey() === '05-03'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'fete_dieu' && date('Y-m-d', $h->getTimestamp()) === '2024-05-30')); // Corpus Christi: Easter 2024 (Mar 31) + 60 days
	}

	// ========================================
	// Tests by country - Portugal (PT)
	// ========================================

	public function testGetListPortugal(): void
	{
		$holidays = PublicHolidays::getList('PT', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête nationale') && date('Y-m-d', $h->getTimestamp()) === '2024-06-10' && $h->getKey() === '06-10'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'mardi_gras' && date('Y-m-d', $h->getTimestamp()) === '2024-02-13')); // Easter 2024 (Mar 31) - 47 days
	}

	// ========================================
	// Tests by country - Algeria (DZ)
	// ========================================

	public function testGetListAlgeria(): void
	{
		$holidays = PublicHolidays::getList('DZ', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête nationale') && date('Y-m-d', $h->getTimestamp()) === '2024-07-05' && $h->getKey() === '07-05'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'aid_el_fitr' && date('Y-m-d', $h->getTimestamp()) === '2024-04-10'));  // Eid al-Fitr 2024
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'aid_al_adha' && date('Y-m-d', $h->getTimestamp()) === '2024-06-16')); // Eid al-Adha 2024
	}

	// ========================================
	// Tests by country - Tunisia (TN)
	// ========================================

	public function testGetListTunisia(): void
	{
		$holidays = PublicHolidays::getList('TN', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'aid_el_fitr' && date('Y-m-d', $h->getTimestamp()) === '2024-04-10')); // Eid al-Fitr 2024
	}

	// ========================================
	// Tests by country - Senegal (SN)
	// ========================================

	public function testGetListSenegal(): void
	{
		$holidays = PublicHolidays::getList('SN', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête nationale') && date('Y-m-d', $h->getTimestamp()) === '2024-04-04' && $h->getKey() === '04-04'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'aid_el_fitr' && date('Y-m-d', $h->getTimestamp()) === '2024-04-10')); // Eid al-Fitr 2024
	}

	// ========================================
	// Tests by country - Ivory Coast (CI)
	// ========================================

	public function testGetListIvoryCoast(): void
	{
		$holidays = PublicHolidays::getList('CI', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête nationale') && date('Y-m-d', $h->getTimestamp()) === '2024-08-07' && $h->getKey() === '08-07'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'aid_el_fitr' && date('Y-m-d', $h->getTimestamp()) === '2024-04-10')); // Eid al-Fitr 2024
	}

	// ========================================
	// Tests by country - Cameroon (CM)
	// ========================================

	public function testGetListCameroon(): void
	{
		$holidays = PublicHolidays::getList('CM', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);

		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Jour de l’an') && date('Y-m-d', $h->getTimestamp()) === '2024-01-01' && $h->getKey() === '01-01'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => str_contains($h->getName(), 'Fête nationale') && date('Y-m-d', $h->getTimestamp()) === '2024-05-20' && $h->getKey() === '05-20'));
		self::assertNotEmpty(array_filter($holidays, fn ($h) => $h->getKey() === 'aid_el_fitr' && date('Y-m-d', $h->getTimestamp()) === '2024-04-10')); // Eid al-Fitr 2024
	}
}