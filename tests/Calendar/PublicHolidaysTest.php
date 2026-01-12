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

	public function testGetEasterDateTime2024(): void
	{
		$easterDateTime = PublicHolidays::getEasterDateTime(2024);
		self::assertInstanceOf(\DateTime::class, $easterDateTime);
		self::assertEquals('2024-03-31', $easterDateTime->format('Y-m-d'));
	}

	public function testGetEasterDateTime2025(): void
	{
		$easterDateTime = PublicHolidays::getEasterDateTime(2025);
		self::assertEquals('2025-04-20', $easterDateTime->format('Y-m-d'));
	}

	public function testGetEasterDateTime2026(): void
	{
		$easterDateTime = PublicHolidays::getEasterDateTime(2026);
		self::assertEquals('2026-04-05', $easterDateTime->format('Y-m-d'));
	}

	public function testGetEasterDateTime2023(): void
	{
		$easterDateTime = PublicHolidays::getEasterDateTime(2023);
		self::assertEquals('2023-04-09', $easterDateTime->format('Y-m-d'));
	}

	public function testGetEasterDateTime2020(): void
	{
		$easterDateTime = PublicHolidays::getEasterDateTime(2020);
		self::assertEquals('2020-04-12', $easterDateTime->format('Y-m-d'));
	}

	// ========================================
	// Tests for getList()
	// ========================================

	public function testGetListFrance(): void
	{
		$holidays = PublicHolidays::getList('FR', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);
	}

	public function testGetListBelgium(): void
	{
		$holidays = PublicHolidays::getList('BE', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);
	}

	public function testGetListLuxembourg(): void
	{
		$holidays = PublicHolidays::getList('LU', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);
	}

	public function testGetListSwitzerland(): void
	{
		$holidays = PublicHolidays::getList('CH', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);
	}

	public function testGetListMauritius(): void
	{
		$holidays = PublicHolidays::getList('MU', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);
	}

	public function testGetListMorocco(): void
	{
		$holidays = PublicHolidays::getList('MA', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		self::assertContainsOnlyInstancesOf(PublicHoliday::class, $holidays);
	}

	public function testGetListMartinique(): void
	{
		$holidays = PublicHolidays::getList('MQ', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
		// Martinique should have more holidays than mainland France
		$franceholidays = PublicHolidays::getList('FR', 2024);
		self::assertGreaterThan(count($franceholidays), count($holidays));
	}

	public function testGetListGuadeloupe(): void
	{
		$holidays = PublicHolidays::getList('GP', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
	}

	public function testGetListReunion(): void
	{
		$holidays = PublicHolidays::getList('RE', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
	}

	public function testGetListGuyane(): void
	{
		$holidays = PublicHolidays::getList('GF', 2024);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
	}

	public function testGetListUnknownCountry(): void
	{
		$holidays = PublicHolidays::getList('XX', 2024);
		self::assertIsArray($holidays);
		self::assertEmpty($holidays);
	}

	public function testGetListFranceWithAlsaceOption(): void
	{
		$holidaysWithoutAlsace = PublicHolidays::getList('FR', 2024);
		$holidaysWithAlsace = PublicHolidays::getList('FR', 2024, ['alsace' => true]);

		self::assertGreaterThan(count($holidaysWithoutAlsace), count($holidaysWithAlsace));
	}

	public function testGetListSortedByTimestamp(): void
	{
		$holidays = PublicHolidays::getList('FR', 2024);

		$previousTimestamp = 0;
		foreach ($holidays as $holiday) {
			self::assertGreaterThanOrEqual($previousTimestamp, $holiday->getTimestamp());
			$previousTimestamp = $holiday->getTimestamp();
		}
	}

	public function testGetListUniqueKeys(): void
	{
		$holidays = PublicHolidays::getList('FR', 2024);

		$keys = [];
		foreach ($holidays as $holiday) {
			$key = $holiday->getKey();
			self::assertNotContains($key, $keys, "Duplicate key found: {$key}");
			$keys[] = $key;
		}
	}

	// ========================================
	// Tests for isPublicHoliday()
	// ========================================

	public function testIsPublicHolidayNewYear(): void
	{
		$dateTime = new \DateTime('2024-01-01');
		self::assertTrue(PublicHolidays::isPublicHoliday($dateTime, 'FR'));
	}

	public function testIsPublicHolidayChristmas(): void
	{
		$dateTime = new \DateTime('2024-12-25');
		self::assertTrue(PublicHolidays::isPublicHoliday($dateTime, 'FR'));
	}

	public function testIsPublicHolidayBastilleDay(): void
	{
		$dateTime = new \DateTime('2024-07-14');
		self::assertTrue(PublicHolidays::isPublicHoliday($dateTime, 'FR'));
	}

	public function testIsPublicHolidayLabourDay(): void
	{
		$dateTime = new \DateTime('2024-05-01');
		self::assertTrue(PublicHolidays::isPublicHoliday($dateTime, 'FR'));
	}

	public function testIsPublicHolidayEaster2024(): void
	{
		$dateTime = new \DateTime('2024-03-31'); // Easter 2024
		self::assertTrue(PublicHolidays::isPublicHoliday($dateTime, 'FR'));
	}

	public function testIsPublicHolidayEasterMonday2024(): void
	{
		$dateTime = new \DateTime('2024-04-01'); // Easter Monday 2024
		self::assertTrue(PublicHolidays::isPublicHoliday($dateTime, 'FR'));
	}

	public function testIsPublicHolidayRegularDay(): void
	{
		$dateTime = new \DateTime('2024-03-15'); // Regular day
		self::assertFalse(PublicHolidays::isPublicHoliday($dateTime, 'FR'));
	}

	public function testIsPublicHolidayDifferentCountries(): void
	{
		// July 14 is a holiday in France but not in Belgium
		$dateTime = new \DateTime('2024-07-14');
		self::assertTrue(PublicHolidays::isPublicHoliday($dateTime, 'FR'));
		self::assertFalse(PublicHolidays::isPublicHoliday($dateTime, 'BE'));
	}

	public function testIsPublicHolidayBelgiumNationalDay(): void
	{
		// July 21 is Belgium's national day
		$dateTime = new \DateTime('2024-07-21');
		self::assertTrue(PublicHolidays::isPublicHoliday($dateTime, 'BE'));
		self::assertFalse(PublicHolidays::isPublicHoliday($dateTime, 'FR'));
	}

	public function testIsPublicHolidaySwissNationalDay(): void
	{
		// August 1 is Switzerland's national day
		$dateTime = new \DateTime('2024-08-01');
		self::assertTrue(PublicHolidays::isPublicHoliday($dateTime, 'CH'));
		self::assertFalse(PublicHolidays::isPublicHoliday($dateTime, 'FR'));
	}

	public function testIsPublicHolidayUnknownCountry(): void
	{
		$dateTime = new \DateTime('2024-01-01');
		self::assertFalse(PublicHolidays::isPublicHoliday($dateTime, 'XX'));
	}

	// ========================================
	// Tests for isDateCorrespondingToPublicHoliday()
	// ========================================

	public function testIsDateCorrespondingToPublicHolidayGregorian(): void
	{
		$publicHoliday = new PublicHoliday('Test Holiday', mktime(0, 0, 0, 12, 25, 2024));
		$dateTime = new \DateTime('2024-12-25');

		self::assertTrue(PublicHolidays::isDateCorrespondingToPublicHoliday($publicHoliday, $dateTime));
	}

	public function testIsDateCorrespondingToPublicHolidayGregorianNotMatching(): void
	{
		$publicHoliday = new PublicHoliday('Test Holiday', mktime(0, 0, 0, 12, 25, 2024));
		$dateTime = new \DateTime('2024-12-26');

		self::assertFalse(PublicHolidays::isDateCorrespondingToPublicHoliday($publicHoliday, $dateTime));
	}

	public function testIsDateCorrespondingToPublicHolidayGregorianWithTime(): void
	{
		// Should ignore time and only compare dates
		$publicHoliday = new PublicHoliday('Test Holiday', mktime(0, 0, 0, 12, 25, 2024));
		$dateTime = new \DateTime('2024-12-25 15:30:00');

		self::assertTrue(PublicHolidays::isDateCorrespondingToPublicHoliday($publicHoliday, $dateTime));
	}

	// ========================================
	// Tests by country - France
	// ========================================

	public function testFranceHasNewYear(): void
	{
		$holidays = PublicHolidays::getList('FR', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Jour de l’an') && date('Y-m-d', $holiday->getTimestamp()) === '2024-01-01' && $holiday->getKey() === '01-01'));
	}

	public function testFranceHasBastilleDay(): void
	{
		$holidays = PublicHolidays::getList('FR', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Fête nationale') && date('Y-m-d', $holiday->getTimestamp()) === '2024-07-14' && $holiday->getKey() === '07-14'));
	}

	public function testFranceHasVictoireDesAllies(): void
	{
		$holidays = PublicHolidays::getList('FR', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Victoire des Alliés') && date('Y-m-d', $holiday->getTimestamp()) === '2024-05-08' && $holiday->getKey() === '05-08'));
	}

	public function testFranceHasArmistice(): void
	{
		$holidays = PublicHolidays::getList('FR', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Armistice') && date('Y-m-d', $holiday->getTimestamp()) === '2024-11-11' && $holiday->getKey() === '11-11'));
	}

	public function testFranceHasAssomption(): void
	{
		$holidays = PublicHolidays::getList('FR', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Assomption') && date('Y-m-d', $holiday->getTimestamp()) === '2024-08-15' && $holiday->getKey() === '08-15'));
	}

	public function testFranceAlsaceHasGoodFriday(): void
	{
		$holidays = PublicHolidays::getList('FR', 2024, ['alsace' => true]);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => $holiday->getKey() === 'vendredi_saint' && date('Y-m-d', $holiday->getTimestamp()) === '2024-03-29'));
	}

	public function testFranceWithoutAlsaceNoGoodFriday(): void
	{
		$holidays = PublicHolidays::getList('FR', 2024);
		self::assertEmpty(array_filter($holidays, fn ($holiday) => $holiday->getKey() === 'vendredi_saint' && date('Y-m-d', $holiday->getTimestamp()) === '2024-03-29'));
	}

	public function testFranceAlsaceHasSaintStephen(): void
	{
		$holidays = PublicHolidays::getList('FR', 2024, ['alsace' => true]);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Saint Étienne') && date('Y-m-d', $holiday->getTimestamp()) === '2024-12-26' && $holiday->getKey() === '12-26'));
	}

	// ========================================
	// Tests by country - Belgium
	// ========================================

	public function testBelgiumHasNewYear(): void
	{
		$holidays = PublicHolidays::getList('BE', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Jour de l’an') && date('Y-m-d', $holiday->getTimestamp()) === '2024-01-01' && $holiday->getKey() === '01-01'));
	}

	public function testBelgiumHasNationalDay(): void
	{
		$holidays = PublicHolidays::getList('BE', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Fête nationale') && date('Y-m-d', $holiday->getTimestamp()) === '2024-07-21' && $holiday->getKey() === '07-21'));
	}

	public function testBelgiumHasLabourDay(): void
	{
		$holidays = PublicHolidays::getList('BE', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Fête du Travail') && date('Y-m-d', $holiday->getTimestamp()) === '2024-05-01' && $holiday->getKey() === '05-01'));
	}

	public function testBelgiumHasArmistice(): void
	{
		$holidays = PublicHolidays::getList('BE', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Armistice') && date('Y-m-d', $holiday->getTimestamp()) === '2024-11-11' && $holiday->getKey() === '11-11'));
	}

	// ========================================
	// Tests by country - Luxembourg
	// ========================================

	public function testLuxembourgHasNewYear(): void
	{
		$holidays = PublicHolidays::getList('LU', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Jour de l’an') && date('Y-m-d', $holiday->getTimestamp()) === '2024-01-01' && $holiday->getKey() === '01-01'));
	}

	public function testLuxembourgHasNationalDay(): void
	{
		$holidays = PublicHolidays::getList('LU', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Fête nationale') && date('Y-m-d', $holiday->getTimestamp()) === '2024-06-23' && $holiday->getKey() === '06-23'));
	}

	public function testLuxembourgHasLabourDay(): void
	{
		$holidays = PublicHolidays::getList('LU', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Fête du Travail') && date('Y-m-d', $holiday->getTimestamp()) === '2024-05-01' && $holiday->getKey() === '05-01'));
	}

	// ========================================
	// Tests by country - Switzerland
	// ========================================

	public function testSwitzerlandHasNewYear(): void
	{
		$holidays = PublicHolidays::getList('CH', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Jour de l’an') && date('Y-m-d', $holiday->getTimestamp()) === '2024-01-01' && $holiday->getKey() === '01-01'));
	}

	public function testSwitzerlandHasNationalDay(): void
	{
		$holidays = PublicHolidays::getList('CH', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Fête nationale') && date('Y-m-d', $holiday->getTimestamp()) === '2024-08-01' && $holiday->getKey() === '08-01'));
	}

	public function testSwitzerlandHasLabourDay(): void
	{
		$holidays = PublicHolidays::getList('CH', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Fête du Travail') && date('Y-m-d', $holiday->getTimestamp()) === '2024-05-01' && $holiday->getKey() === '05-01'));
	}

	// ========================================
	// Tests by country - Mauritius
	// ========================================

	public function testMauritiusHasNewYear(): void
	{
		$holidays = PublicHolidays::getList('MU', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Jour de l’an') && date('Y-m-d', $holiday->getTimestamp()) === '2024-01-01' && $holiday->getKey() === '01-01'));
	}

	public function testMauritiusHasNationalDay(): void
	{
		$holidays = PublicHolidays::getList('MU', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Fête nationale') && date('Y-m-d', $holiday->getTimestamp()) === '2024-03-12' && $holiday->getKey() === '03-12'));
	}

	public function testMauritiusHasAbolitionOfSlavery(): void
	{
		$holidays = PublicHolidays::getList('MU', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Abolition de l’esclavage') && date('Y-m-d', $holiday->getTimestamp()) === '2024-02-01' && $holiday->getKey() === '02-01'));
	}

	public function testMauritiusHasIndianHolidays(): void
	{
		$holidays = PublicHolidays::getList('MU', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => $holiday->getCalendar() === PublicHolidayCalendar::INDIAN));
	}

	// ========================================
	// Tests by country - Morocco
	// ========================================

	public function testMoroccoHasNewYear(): void
	{
		$holidays = PublicHolidays::getList('MA', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Jour de l’an') && date('Y-m-d', $holiday->getTimestamp()) === '2024-01-01' && $holiday->getKey() === '01-01'));
	}

	public function testMoroccoHasIndependenceManifesto(): void
	{
		$holidays = PublicHolidays::getList('MA', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Manifeste de l’Indépendance') && date('Y-m-d', $holiday->getTimestamp()) === '2024-01-11' && $holiday->getKey() === '01-11'));
	}

	public function testMoroccoHasThroneDay(): void
	{
		$holidays = PublicHolidays::getList('MA', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Fête du Trône') && date('Y-m-d', $holiday->getTimestamp()) === '2024-07-30' && $holiday->getKey() === '07-30'));
	}

	public function testMoroccoHasIslamicHolidays(): void
	{
		$holidays = PublicHolidays::getList('MA', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => $holiday->getCalendar() === PublicHolidayCalendar::HIJRI));
	}

	// ========================================
	// Tests by country - Martinique
	// ========================================

	public function testMartiniqueHasAbolitionOfSlavery(): void
	{
		$holidays = PublicHolidays::getList('MQ', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Abolition de l’esclavage') && date('Y-m-d', $holiday->getTimestamp()) === '2024-05-22' && $holiday->getKey() === '05-22'));
	}

	public function testMartiniqueHasMardiGras(): void
	{
		$holidays = PublicHolidays::getList('MQ', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => $holiday->getKey() === 'mardi_gras' && date('Y-m-d', $holiday->getTimestamp()) === '2024-02-13'));
	}

	public function testMartiniqueHasFeteVictorSchoelcher(): void
	{
		$holidays = PublicHolidays::getList('MQ', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Fête Victor Schœlcher') && date('Y-m-d', $holiday->getTimestamp()) === '2024-07-21' && $holiday->getKey() === '07-21'));
	}

	public function testMartiniqueHasDefunts(): void
	{
		$holidays = PublicHolidays::getList('MQ', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Défunts') && date('Y-m-d', $holiday->getTimestamp()) === '2024-11-02' && $holiday->getKey() === '11-02'));
	}

	// ========================================
	// Tests by country - Guadeloupe
	// ========================================

	public function testGuadeloupeAbolitionDateIsMay27(): void
	{
		$holidays = PublicHolidays::getList('GP', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Abolition de l’esclavage') && date('Y-m-d', $holiday->getTimestamp()) === '2024-05-27' && $holiday->getKey() === '05-27'));
	}

	public function testGuadeloupeHasFeteVictorSchoelcher(): void
	{
		$holidays = PublicHolidays::getList('GP', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Fête Victor Schœlcher') && date('Y-m-d', $holiday->getTimestamp()) === '2024-07-21' && $holiday->getKey() === '07-21'));
	}

	public function testGuadeloupeHasDefunts(): void
	{
		$holidays = PublicHolidays::getList('GP', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Défunts') && date('Y-m-d', $holiday->getTimestamp()) === '2024-11-02' && $holiday->getKey() === '11-02'));
	}

	// ========================================
	// Tests by country - Reunion
	// ========================================

	public function testReunionAbolitionDateIsDecember20(): void
	{
		$holidays = PublicHolidays::getList('RE', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Abolition de l’esclavage') && date('Y-m-d', $holiday->getTimestamp()) === '2024-12-20' && $holiday->getKey() === '12-20'));
	}

	public function testReunionHasNewYear(): void
	{
		$holidays = PublicHolidays::getList('RE', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Jour de l’an') && date('Y-m-d', $holiday->getTimestamp()) === '2024-01-01' && $holiday->getKey() === '01-01'));
	}

	public function testReunionHasLabourDay(): void
	{
		$holidays = PublicHolidays::getList('RE', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Fête du Travail') && date('Y-m-d', $holiday->getTimestamp()) === '2024-05-01' && $holiday->getKey() === '05-01'));
	}

	// ========================================
	// Tests by country - Guyane
	// ========================================

	public function testGuyaneAbolitionDateIsJune10(): void
	{
		$holidays = PublicHolidays::getList('GF', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Abolition de l’esclavage') && date('Y-m-d', $holiday->getTimestamp()) === '2024-06-10' && $holiday->getKey() === '06-10'));
	}

	public function testGuyaneHasNewYear(): void
	{
		$holidays = PublicHolidays::getList('GF', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Jour de l’an') && date('Y-m-d', $holiday->getTimestamp()) === '2024-01-01' && $holiday->getKey() === '01-01'));
	}

	public function testGuyaneHasLabourDay(): void
	{
		$holidays = PublicHolidays::getList('GF', 2024);
		self::assertNotEmpty(array_filter($holidays, fn ($holiday) => str_contains($holiday->getName(), 'Fête du Travail') && date('Y-m-d', $holiday->getTimestamp()) === '2024-05-01' && $holiday->getKey() === '05-01'));
	}

	// ========================================
	// Tests with different years
	// ========================================

	public function testGetListDifferentYears(): void
	{
		$holidays2023 = PublicHolidays::getList('FR', 2023);
		$holidays2024 = PublicHolidays::getList('FR', 2024);

		// Same number of holidays in both years
		self::assertCount(count($holidays2023), $holidays2024);

		// But timestamps should be different
		self::assertNotEquals($holidays2023[0]->getTimestamp(), $holidays2024[0]->getTimestamp());
	}

	public function testEasterChangesWithYear(): void
	{
		$holidays2023 = PublicHolidays::getList('FR', 2023);
		$holidays2024 = PublicHolidays::getList('FR', 2024);

		$easter2023Timestamp = null;
		$easter2024Timestamp = null;

		foreach ($holidays2023 as $holiday) {
			if ($holiday->getKey() === 'paques' && date('Y-m-d', $holiday->getTimestamp()) === '2023-04-09') {
				$easter2023Timestamp = $holiday->getTimestamp();
				break;
			}
		}

		foreach ($holidays2024 as $holiday) {
			if ($holiday->getKey() === 'paques' && date('Y-m-d', $holiday->getTimestamp()) === '2024-03-31') {
				$easter2024Timestamp = $holiday->getTimestamp();
				break;
			}
		}

		self::assertNotNull($easter2023Timestamp);
		self::assertNotNull($easter2024Timestamp);
		self::assertNotEquals($easter2023Timestamp, $easter2024Timestamp);
	}

	// ========================================
	// Edge cases
	// ========================================

	public function testGetListWithEmptyCountryCode(): void
	{
		$holidays = PublicHolidays::getList('', 2024);
		self::assertIsArray($holidays);
		self::assertEmpty($holidays);
	}

	public function testGetListWithLowercaseCountryCode(): void
	{
		$holidays = PublicHolidays::getList('fr', 2024);
		self::assertNotEmpty($holidays);
	}

	public function testGetListWithOldYear(): void
	{
		$holidays = PublicHolidays::getList('FR', 1900);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
	}

	public function testGetListWithFutureYear(): void
	{
		$holidays = PublicHolidays::getList('FR', 2100);
		self::assertIsArray($holidays);
		self::assertNotEmpty($holidays);
	}
}