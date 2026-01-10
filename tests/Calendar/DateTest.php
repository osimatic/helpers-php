<?php

namespace Tests\Calendar;

use Osimatic\Calendar\Date;
use PHPUnit\Framework\TestCase;

final class DateTest extends TestCase
{
	public function testParse(): void
	{
		// Format ISO date-time
		$result = Date::parse('2024-01-15T14:30:45');
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('2024-01-15', $result->format('Y-m-d'));

		// Format yyyymmddhhiiss
		$result = Date::parse('20240115143045');
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('2024-01-15', $result->format('Y-m-d'));
		$this->assertEquals('14:30:45', $result->format('H:i:s'));

		// Format date simple
		$result = Date::parse('2024-01-15');
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('2024-01-15', $result->format('Y-m-d'));

		// Valeur vide
		$this->assertNull(Date::parse(''));

		// Valeur invalide
		$this->assertNull(Date::parse('invalid-date'));
	}

	public function testGetDayName(): void
	{
		// Lundi
		$this->assertNotEmpty(Date::getDayName(1));
		$this->assertIsString(Date::getDayName(1));

		// En français
		$dayName = Date::getDayName(1, 'fr_FR');
		$this->assertStringContainsStringIgnoringCase('lundi', $dayName);

		// En anglais
		$dayName = Date::getDayName(1, 'en_US');
		$this->assertStringContainsStringIgnoringCase('monday', $dayName);

		// Dimanche
		$this->assertNotEmpty(Date::getDayName(7));
	}

	public function testGetMonthName(): void
	{
		// Janvier
		$this->assertNotEmpty(Date::getMonthName(1));
		$this->assertIsString(Date::getMonthName(1));

		// En français
		$monthName = Date::getMonthName(1, 'fr_FR');
		$this->assertStringContainsStringIgnoringCase('janvier', $monthName);

		// En anglais
		$monthName = Date::getMonthName(1, 'en_US');
		$this->assertStringContainsStringIgnoringCase('january', $monthName);

		// Décembre
		$this->assertNotEmpty(Date::getMonthName(12));
	}

	public function testGetNumberOfDaysInMonth(): void
	{
		// Janvier (31 jours)
		$this->assertEquals(31, Date::getNumberOfDaysInMonth(2024, 1));

		// Février année bissextile (29 jours)
		$this->assertEquals(29, Date::getNumberOfDaysInMonth(2024, 2));

		// Février année non bissextile (28 jours)
		$this->assertEquals(28, Date::getNumberOfDaysInMonth(2023, 2));

		// Avril (30 jours)
		$this->assertEquals(30, Date::getNumberOfDaysInMonth(2024, 4));

		// Décembre (31 jours)
		$this->assertEquals(31, Date::getNumberOfDaysInMonth(2024, 12));
	}

	public function testIsLeapYear(): void
	{
		// Années bissextiles
		$this->assertEquals(1, Date::isLeapYear(2024));
		$this->assertEquals(1, Date::isLeapYear(2000));
		$this->assertEquals(1, Date::isLeapYear(2400));

		// Années non bissextiles
		$this->assertEquals(0, Date::isLeapYear(2023));
		$this->assertEquals(0, Date::isLeapYear(1900));
		$this->assertEquals(0, Date::isLeapYear(2100));
	}
}