<?php

namespace Tests\Calendar;

use Osimatic\Calendar\DatePeriod;
use PHPUnit\Framework\TestCase;

final class DatePeriodTest extends TestCase
{
	/* ===================== Jours ===================== */

	public function testGetNbDays(): void
	{
		// Même jour
		$start = new \DateTime('2024-01-15 10:00:00');
		$end = new \DateTime('2024-01-15 14:00:00');
		$this->assertEquals(0, DatePeriod::getNbDays($start, $end));

		// Avec l'heure prise en compte
		$this->assertLessThan(1, DatePeriod::getNbDays($start, $end, true));

		// 5 jours
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-20');
		$this->assertEquals(5, DatePeriod::getNbDays($start, $end));

		// Période négative (end avant start)
		$start = new \DateTime('2024-01-20');
		$end = new \DateTime('2024-01-15');
		$this->assertEquals(-5, DatePeriod::getNbDays($start, $end));
	}

	public function testGetNbRemainingDays(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-20');
		$this->assertEquals(5, DatePeriod::getNbRemainingDays($start, $end));

		// Même jour
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-15');
		$this->assertEquals(0, DatePeriod::getNbRemainingDays($start, $end));
	}

	public function testGetListOfDateDaysOfTheMonth(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-17');

		$days = DatePeriod::getListOfDateDaysOfTheMonth($start, $end);
		$this->assertCount(3, $days);
		$this->assertInstanceOf(\DateTime::class, $days[0]);
		$this->assertEquals('2024-01-15', $days[0]->format('Y-m-d'));
		$this->assertEquals('2024-01-16', $days[1]->format('Y-m-d'));
		$this->assertEquals('2024-01-17', $days[2]->format('Y-m-d'));
	}

	public function testGetListOfDateDaysOfTheMonthWithWeekDaysFilter(): void
	{
		// Du lundi 15 au vendredi 19 janvier 2024
		$start = new \DateTime('2024-01-15'); // Lundi
		$end = new \DateTime('2024-01-19');   // Vendredi

		// Filtrer seulement lundi (1) et vendredi (5)
		$days = DatePeriod::getListOfDateDaysOfTheMonth($start, $end, [1, 5]);
		$this->assertCount(2, $days);
		$this->assertEquals('2024-01-15', $days[0]->format('Y-m-d')); // Lundi
		$this->assertEquals('2024-01-19', $days[1]->format('Y-m-d')); // Vendredi
	}

	public function testGetListOfDaysOfTheMonth(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-17');

		$days = DatePeriod::getListOfDaysOfTheMonth($start, $end);
		$this->assertEquals(['2024-01-15', '2024-01-16', '2024-01-17'], $days);

		// Format personnalisé
		$days = DatePeriod::getListOfDaysOfTheMonth($start, $end, null, 'd/m/Y');
		$this->assertEquals(['15/01/2024', '16/01/2024', '17/01/2024'], $days);
	}

	/* ===================== Semaines ===================== */

	public function testGetNbFullWeeks(): void
	{
		// 14 jours = 2 semaines pleines
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-29');
		$this->assertEquals(2, DatePeriod::getNbFullWeeks($start, $end));

		// 10 jours = 1 semaine pleine
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-25');
		$this->assertEquals(1, DatePeriod::getNbFullWeeks($start, $end));

		// 6 jours = 0 semaine pleine
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-21');
		$this->assertEquals(0, DatePeriod::getNbFullWeeks($start, $end));
	}

	public function testGetNbWeeks(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-29');
		$nbWeeks = DatePeriod::getNbWeeks($start, $end);
		$this->assertGreaterThan(0, $nbWeeks);
	}

	public function testIsFullWeek(): void
	{
		// Du lundi au dimanche
		$start = new \DateTime('2024-01-15 00:00:00'); // Lundi
		$end = new \DateTime('2024-01-21 23:59:59');   // Dimanche
		$this->assertTrue(DatePeriod::isFullWeek($start, $end));

		// Du mardi au dimanche (pas une semaine complète)
		$start = new \DateTime('2024-01-16 00:00:00'); // Mardi
		$end = new \DateTime('2024-01-21 23:59:59');   // Dimanche
		$this->assertFalse(DatePeriod::isFullWeek($start, $end));

		// Du lundi au samedi (pas une semaine complète)
		$start = new \DateTime('2024-01-15 00:00:00'); // Lundi
		$end = new \DateTime('2024-01-20 23:59:59');   // Samedi
		$this->assertFalse(DatePeriod::isFullWeek($start, $end));
	}

	public function testIsFullWeeks(): void
	{
		// Du lundi au dimanche
		$start = new \DateTime('2024-01-15'); // Lundi
		$end = new \DateTime('2024-01-21');   // Dimanche
		$this->assertTrue(DatePeriod::isFullWeeks($start, $end));

		// Du lundi au dimanche (2 semaines)
		$start = new \DateTime('2024-01-15'); // Lundi
		$end = new \DateTime('2024-01-28');   // Dimanche
		$this->assertTrue(DatePeriod::isFullWeeks($start, $end));

		// Du mardi au dimanche
		$start = new \DateTime('2024-01-16'); // Mardi
		$end = new \DateTime('2024-01-21');   // Dimanche
		$this->assertFalse(DatePeriod::isFullWeeks($start, $end));
	}

	/* ===================== Mois ===================== */

	public function testGetNbFullMonths(): void
	{
		// 2 mois complets
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-03-15');
		$this->assertEquals(2, DatePeriod::getNbFullMonths($start, $end));

		// Même mois
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-20');
		$this->assertEquals(0, DatePeriod::getNbFullMonths($start, $end));

		// 12 mois = 1 an
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2025-01-01');
		$this->assertEquals(12, DatePeriod::getNbFullMonths($start, $end));
	}

	public function testGetNbMonths(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-03-15');
		$nbMonths = DatePeriod::getNbMonths($start, $end);
		$this->assertGreaterThan(0, $nbMonths);
	}

	public function testGetListOfMonths(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-03-15');

		$months = DatePeriod::getListOfMonths($start, $end);
		$this->assertGreaterThan(0, count($months));
		$this->assertIsString($months[0]);
	}

	public function testIsFullMonth(): void
	{
		// Mois complet (janvier 2024)
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-01-31');
		$this->assertTrue(DatePeriod::isFullMonth($start, $end));

		// Février (année bissextile)
		$start = new \DateTime('2024-02-01');
		$end = new \DateTime('2024-02-29');
		$this->assertTrue(DatePeriod::isFullMonth($start, $end));

		// Pas un mois complet (commence au 2)
		$start = new \DateTime('2024-01-02');
		$end = new \DateTime('2024-01-31');
		$this->assertFalse(DatePeriod::isFullMonth($start, $end));

		// Pas un mois complet (termine au 30)
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-01-30');
		$this->assertFalse(DatePeriod::isFullMonth($start, $end));
	}

	public function testIsFullMonths(): void
	{
		// 2 mois complets
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-02-29');
		$this->assertTrue(DatePeriod::isFullMonths($start, $end));

		// Commence au 2
		$start = new \DateTime('2024-01-02');
		$end = new \DateTime('2024-02-29');
		$this->assertFalse(DatePeriod::isFullMonths($start, $end));

		// Termine au 28 au lieu de 29
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-02-28');
		$this->assertFalse(DatePeriod::isFullMonths($start, $end));
	}

	/* ===================== Année ===================== */

	public function testIsFullYear(): void
	{
		// Année complète 2024
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-12-31');
		$this->assertTrue(DatePeriod::isFullYear($start, $end));

		// Pas une année complète (commence au 2)
		$start = new \DateTime('2024-01-02');
		$end = new \DateTime('2024-12-31');
		$this->assertFalse(DatePeriod::isFullYear($start, $end));

		// Pas une année complète (termine au 30)
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-12-30');
		$this->assertFalse(DatePeriod::isFullYear($start, $end));

		// Deux années différentes
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2025-12-31');
		$this->assertFalse(DatePeriod::isFullYear($start, $end));
	}

	public function testGetNbYears(): void
	{
		// 1 an
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2025-01-01');
		$this->assertEquals(1, DatePeriod::getNbYears($start, $end));

		// 2 ans
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2026-01-01');
		$this->assertEquals(2, DatePeriod::getNbYears($start, $end));

		// Moins d'un an
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-06-01');
		$this->assertEquals(0, DatePeriod::getNbYears($start, $end));
	}

	public function testGetYearFromStartDateAndEndDate(): void
	{
		// Année complète valide
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-12-31');
		$this->assertEquals(2024, DatePeriod::getYearFromStartDateAndEndDate($start, $end));

		// Pas une année complète (commence au 2)
		$start = new \DateTime('2024-01-02');
		$end = new \DateTime('2024-12-31');
		$this->assertNull(DatePeriod::getYearFromStartDateAndEndDate($start, $end));

		// Pas une année complète (termine au 30)
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-12-30');
		$this->assertNull(DatePeriod::getYearFromStartDateAndEndDate($start, $end));

		// Années différentes
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2025-12-31');
		$this->assertNull(DatePeriod::getYearFromStartDateAndEndDate($start, $end));
	}

	/* ===================== Interval / Label ===================== */

	public function testGetLabel(): void
	{
		// Même jour
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-15');
		$label = DatePeriod::getLabel($start, $end);
		$this->assertStringContainsString('le', $label);
		$this->assertStringContainsString('15', $label);

		// Mois complet
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-01-31');
		$label = DatePeriod::getLabel($start, $end);
		$this->assertStringContainsStringIgnoringCase('janvier', $label);
		$this->assertStringContainsString('2024', $label);

		// Année complète
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-12-31');
		$label = DatePeriod::getLabel($start, $end);
		$this->assertStringContainsString('2024', $label);

		// Période quelconque
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-03-20');
		$label = DatePeriod::getLabel($start, $end);
		$this->assertStringContainsString('du', $label);
		$this->assertStringContainsString('au', $label);
	}

	public function testGetListOfPeriod(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-03-15');

		// Test HOUR
		$result = DatePeriod::getListOfPeriod(\Osimatic\Calendar\PeriodType::HOUR, $start, $end);
		$this->assertIsArray($result);
		$this->assertCount(24, $result);
		$this->assertContains('0', $result);
		$this->assertContains('23', $result);

		// Test DAY_OF_MONTH
		$result = DatePeriod::getListOfPeriod(\Osimatic\Calendar\PeriodType::DAY_OF_MONTH, $start, $end);
		$this->assertIsArray($result);
		$this->assertGreaterThan(0, count($result));

		// Test WEEK
		$result = DatePeriod::getListOfPeriod(\Osimatic\Calendar\PeriodType::WEEK, $start, $end);
		$this->assertIsArray($result);
		$this->assertGreaterThan(0, count($result));

		// Test MONTH
		$result = DatePeriod::getListOfPeriod(\Osimatic\Calendar\PeriodType::MONTH, $start, $end);
		$this->assertIsArray($result);
		$this->assertGreaterThan(0, count($result));

		// Test DAY_OF_WEEK
		$result = DatePeriod::getListOfPeriod(\Osimatic\Calendar\PeriodType::DAY_OF_WEEK, $start, $end);
		$this->assertIsArray($result);
		$this->assertCount(7, $result);
		$this->assertContains('1', $result);
		$this->assertContains('7', $result);
	}

	public function testGetListOfDateWeeks(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-02-15');

		$weeks = DatePeriod::getListOfDateWeeks($start, $end);
		$this->assertIsArray($weeks);
		$this->assertGreaterThan(0, count($weeks));
		$this->assertInstanceOf(\DateTime::class, $weeks[0]);

		// Vérifier que chaque élément est un lundi
		foreach ($weeks as $week) {
			$this->assertEquals(1, (int) $week->format('N')); // N=1 pour lundi
		}
	}

	public function testGetListOfDateMonths(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-04-15');

		$months = DatePeriod::getListOfDateMonths($start, $end);
		$this->assertIsArray($months);
		$this->assertGreaterThan(0, count($months));
		$this->assertInstanceOf(\DateTime::class, $months[0]);

		// Vérifier que chaque élément est le premier jour d'un mois
		foreach ($months as $month) {
			$this->assertEquals(1, (int) $month->format('d'));
		}
	}

	/* ===================== Deprecated methods ===================== */

	public function testGetListDaysOfMonths(): void
	{
		// Test deprecated method (should work like getListOfDateDaysOfTheMonth)
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-17');

		$days = DatePeriod::getListDaysOfMonths($start, $end);
		$this->assertCount(3, $days);
		$this->assertInstanceOf(\DateTime::class, $days[0]);
	}

	public function testGetNbDaysBetweenDatesAndTimes(): void
	{
		// Test deprecated method (should work like getNbDays with withTimes=true)
		$start = new \DateTime('2024-01-15 10:00:00');
		$end = new \DateTime('2024-01-15 14:00:00');
		$result = DatePeriod::getNbDaysBetweenDatesAndTimes($start, $end);
		$this->assertLessThan(1, $result);
	}

	/* ===================== getLabel() - Cas limites ===================== */

	public function testGetLabelWithDifferentYears(): void
	{
		// Période sur plusieurs années
		$start = new \DateTime('2024-12-15');
		$end = new \DateTime('2025-01-15');
		$label = DatePeriod::getLabel($start, $end);
		$this->assertStringContainsString('du', $label);
		$this->assertStringContainsString('au', $label);
		$this->assertStringContainsString('2024', $label);
		$this->assertStringContainsString('2025', $label);
	}

	public function testGetLabelWithSameYearMultipleMonths(): void
	{
		// Période sur plusieurs mois de la même année
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-03-20');
		$label = DatePeriod::getLabel($start, $end);
		$this->assertStringContainsString('du', $label);
		$this->assertStringContainsString('au', $label);
	}

	public function testGetLabelWithPartialMonth(): void
	{
		// Période partielle dans un mois
		$start = new \DateTime('2024-01-10');
		$end = new \DateTime('2024-01-20');
		$label = DatePeriod::getLabel($start, $end);
		$this->assertStringContainsString('du', $label);
		$this->assertStringContainsString('au', $label);
	}

	/* ===================== getListOfPeriod() - Cas non géré ===================== */

	public function testGetListOfPeriodWithUnsupportedType(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-03-15');

		// Test avec un type non géré (YEAR n'est pas dans la méthode)
		$result = DatePeriod::getListOfPeriod(\Osimatic\Calendar\PeriodType::YEAR, $start, $end);
		$this->assertNull($result);
	}

	/* ===================== getListOfWeeks() - Format personnalisé ===================== */

	public function testGetListOfWeeksWithCustomFormat(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-02-15');

		// Test avec format personnalisé
		$weeks = DatePeriod::getListOfWeeks($start, $end, 'Y-m-d');
		$this->assertIsArray($weeks);
		$this->assertGreaterThan(0, count($weeks));
		$this->assertIsString($weeks[0]);
		$this->assertMatchesRegularExpression('/\d{4}-\d{2}-\d{2}/', $weeks[0]);
	}

	public function testGetListOfWeeksWithWeekNumberFormat(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-02-15');

		// Test avec format semaine ISO
		$weeks = DatePeriod::getListOfWeeks($start, $end, 'Y-W');
		$this->assertIsArray($weeks);
		$this->assertGreaterThan(0, count($weeks));
		foreach ($weeks as $week) {
			$this->assertMatchesRegularExpression('/\d{4}-\d{2}/', $week);
		}
	}

	/* ===================== getListOfMonths() - Format personnalisé ===================== */

	public function testGetListOfMonthsWithCustomFormat(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-04-15');

		// Test avec format personnalisé
		$months = DatePeriod::getListOfMonths($start, $end, 'Y-m');
		$this->assertIsArray($months);
		$this->assertGreaterThan(0, count($months));
		$this->assertIsString($months[0]);
		$this->assertMatchesRegularExpression('/\d{4}-\d{2}/', $months[0]);
	}

	public function testGetListOfMonthsWithMonthNameFormat(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-03-15');

		// Test avec format nom de mois
		$months = DatePeriod::getListOfMonths($start, $end, 'F Y');
		$this->assertIsArray($months);
		$this->assertGreaterThan(0, count($months));
		$this->assertIsString($months[0]);
	}

	/* ===================== Edge cases et cas limites ===================== */

	public function testGetNbDaysWithSameDateTime(): void
	{
		$date = new \DateTime('2024-01-15 12:00:00');
		$this->assertEquals(0, DatePeriod::getNbDays($date, $date));
		$this->assertEquals(0, DatePeriod::getNbDays($date, $date, true));
	}

	public function testGetNbRemainingDaysWithSameDateTime(): void
	{
		$date = new \DateTime('2024-01-15 12:00:00');
		$this->assertEquals(0, DatePeriod::getNbRemainingDays($date, $date));
	}

	public function testGetListOfDateDaysOfTheMonthWithSingleDay(): void
	{
		$date = new \DateTime('2024-01-15');
		$days = DatePeriod::getListOfDateDaysOfTheMonth($date, $date);
		$this->assertCount(1, $days);
		$this->assertEquals('2024-01-15', $days[0]->format('Y-m-d'));
	}

	public function testGetListOfDateDaysOfTheMonthWithEmptyWeekDaysFilter(): void
	{
		$start = new \DateTime('2024-01-15'); // Lundi
		$end = new \DateTime('2024-01-19');   // Vendredi

		// Filtrer avec un tableau vide (aucun jour ne correspond)
		$days = DatePeriod::getListOfDateDaysOfTheMonth($start, $end, []);
		$this->assertCount(0, $days);
	}

	public function testGetListOfDateDaysOfTheMonthWithNonExistentWeekDay(): void
	{
		$start = new \DateTime('2024-01-15'); // Lundi
		$end = new \DateTime('2024-01-19');   // Vendredi

		// Filtrer avec un jour qui n'existe pas dans la période (dimanche = 7)
		$days = DatePeriod::getListOfDateDaysOfTheMonth($start, $end, [7]);
		$this->assertCount(0, $days);
	}

	public function testGetListOfDaysOfTheMonthWithCustomFormat(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-17');

		// Test avec format complet
		$days = DatePeriod::getListOfDaysOfTheMonth($start, $end, null, 'l, d F Y');
		$this->assertCount(3, $days);
		$this->assertIsString($days[0]);
	}

	public function testGetNbFullWeeksWithZeroDays(): void
	{
		$date = new \DateTime('2024-01-15');
		$this->assertEquals(0, DatePeriod::getNbFullWeeks($date, $date));
	}

	public function testGetNbWeeksWithSameDate(): void
	{
		$date = new \DateTime('2024-01-15');
		$nbWeeks = DatePeriod::getNbWeeks($date, $date);
		$this->assertGreaterThanOrEqual(0, $nbWeeks);
	}

	public function testIsFullWeekWithExactlySevenDays(): void
	{
		// Du lundi 00:00:00 au dimanche 23:59:59
		$start = new \DateTime('2024-01-15 00:00:00'); // Lundi
		$end = new \DateTime('2024-01-21 23:59:59');   // Dimanche
		$this->assertTrue(DatePeriod::isFullWeek($start, $end));
	}

	public function testIsFullWeekWithFiveDays(): void
	{
		// Du lundi au vendredi (seulement 4 jours de différence)
		$start = new \DateTime('2024-01-15 00:00:00'); // Lundi
		$end = new \DateTime('2024-01-19 23:59:59');   // Vendredi
		$this->assertFalse(DatePeriod::isFullWeek($start, $end));
	}

	public function testGetNbFullMonthsWithSameDate(): void
	{
		$date = new \DateTime('2024-01-15');
		$this->assertEquals(0, DatePeriod::getNbFullMonths($date, $date));
	}

	public function testGetNbMonthsWithSameDate(): void
	{
		$date = new \DateTime('2024-01-15');
		$nbMonths = DatePeriod::getNbMonths($date, $date);
		$this->assertGreaterThanOrEqual(0, $nbMonths);
	}

	public function testIsFullMonthWithFebruaryNonLeapYear(): void
	{
		// Février année non bissextile (2023)
		$start = new \DateTime('2023-02-01');
		$end = new \DateTime('2023-02-28');
		$this->assertTrue(DatePeriod::isFullMonth($start, $end));
	}

	public function testIsFullMonthsWithFebruaryNonLeapYear(): void
	{
		// Période de janvier à février année non bissextile
		$start = new \DateTime('2023-01-01');
		$end = new \DateTime('2023-02-28');
		$this->assertTrue(DatePeriod::isFullMonths($start, $end));
	}

	public function testIsFullMonthWithDifferentMonths(): void
	{
		// Du 1er janvier au 31 mars (plusieurs mois)
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-03-31');
		$this->assertFalse(DatePeriod::isFullMonth($start, $end)); // isFullMonth vérifie un SEUL mois
	}

	public function testGetNbYearsWithZeroDays(): void
	{
		$date = new \DateTime('2024-01-15');
		$this->assertEquals(0, DatePeriod::getNbYears($date, $date));
	}

	public function testGetNbYearsWithThreeYears(): void
	{
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2027-01-01');
		$this->assertEquals(3, DatePeriod::getNbYears($start, $end));
	}

	public function testGetYearFromStartDateAndEndDateWithMidYear(): void
	{
		// Du 1er janvier au 30 juin (pas une année complète)
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-06-30');
		$this->assertNull(DatePeriod::getYearFromStartDateAndEndDate($start, $end));
	}

	public function testGetListOfDateWeeksWithSameWeek(): void
	{
		$start = new \DateTime('2024-01-15'); // Lundi
		$end = new \DateTime('2024-01-19');   // Vendredi (même semaine)

		$weeks = DatePeriod::getListOfDateWeeks($start, $end);
		$this->assertIsArray($weeks);
		$this->assertGreaterThanOrEqual(1, count($weeks));
	}

	public function testGetListOfDateMonthsWithSameMonth(): void
	{
		$start = new \DateTime('2024-01-15');
		$end = new \DateTime('2024-01-25');

		$months = DatePeriod::getListOfDateMonths($start, $end);
		$this->assertIsArray($months);
		$this->assertGreaterThanOrEqual(1, count($months));
		$this->assertEquals('2024-01-01', $months[0]->format('Y-m-d'));
	}

	public function testGetListOfDateMonthsAcrossYear(): void
	{
		$start = new \DateTime('2024-11-15');
		$end = new \DateTime('2025-02-15');

		$months = DatePeriod::getListOfDateMonths($start, $end);
		$this->assertIsArray($months);
		$this->assertGreaterThan(2, count($months));

		// Vérifier que les mois traversent bien les années
		$yearChanges = false;
		for ($i = 0; $i < count($months) - 1; $i++) {
			if ($months[$i]->format('Y') !== $months[$i + 1]->format('Y')) {
				$yearChanges = true;
				break;
			}
		}
		$this->assertTrue($yearChanges);
	}

	public function testGetListOfDateWeeksAcrossYear(): void
	{
		$start = new \DateTime('2024-12-15');
		$end = new \DateTime('2025-01-15');

		$weeks = DatePeriod::getListOfDateWeeks($start, $end);
		$this->assertIsArray($weeks);
		$this->assertGreaterThan(0, count($weeks));
	}

	public function testGetNbDaysWithLargePeriod(): void
	{
		// Période de plusieurs années
		$start = new \DateTime('2020-01-01');
		$end = new \DateTime('2024-12-31');
		$nbDays = DatePeriod::getNbDays($start, $end);
		$this->assertGreaterThan(1000, $nbDays);
		$this->assertEquals(1826, $nbDays); // 5 ans (avec une année bissextile)
	}

	public function testGetNbRemainingDaysWithFullMonth(): void
	{
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-02-01');
		$remaining = DatePeriod::getNbRemainingDays($start, $end);
		// getNbRemainingDays utilise ->d qui retourne les jours après les mois complets
		// Entre 2024-01-01 et 2024-02-01 il y a 1 mois et 0 jours restants
		$this->assertEquals(0, $remaining);
	}

	public function testIsFullWeeksWithMultipleWeeks(): void
	{
		// Du lundi 1er au dimanche 28 janvier 2024 (4 semaines complètes)
		$start = new \DateTime('2024-01-01'); // Lundi
		$end = new \DateTime('2024-01-28');   // Dimanche
		$this->assertTrue(DatePeriod::isFullWeeks($start, $end));
	}

	public function testGetLabelForSameDay(): void
	{
		$date = new \DateTime('2024-03-15');
		$label = DatePeriod::getLabel($date, $date);
		$this->assertStringContainsString('le', $label);
		$this->assertStringContainsString('15', $label);
		$this->assertStringContainsString('mars', strtolower($label));
		$this->assertStringContainsString('2024', $label);
	}

	public function testGetLabelForFullYear(): void
	{
		$start = new \DateTime('2024-01-01');
		$end = new \DateTime('2024-12-31');
		$label = DatePeriod::getLabel($start, $end);
		$this->assertStringContainsString('en', $label);
		$this->assertStringContainsString('2024', $label);
		$this->assertStringNotContainsString('du', $label);
	}

	public function testGetLabelForFullMonth(): void
	{
		$start = new \DateTime('2024-03-01');
		$end = new \DateTime('2024-03-31');
		$label = DatePeriod::getLabel($start, $end);
		$this->assertStringContainsString('en', $label);
		$this->assertStringContainsString('mars', strtolower($label));
		$this->assertStringContainsString('2024', $label);
	}
}