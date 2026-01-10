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
}