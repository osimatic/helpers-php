<?php

namespace Tests\Calendar;

use Osimatic\Calendar\Date;
use Osimatic\Calendar\DateTime;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Date class - methods that do NOT take DateTime objects as parameters
 */
class DateTest extends TestCase
{
	// ========== Parsing Methods Tests ==========

	public function testParse(): void
	{
		$date = Date::parse('2024-01-15');
		$this->assertInstanceOf(\DateTime::class, $date);
		$this->assertEquals('2024-01-15', $date->format('Y-m-d'));

		$date = Date::parse('20240115');
		$this->assertInstanceOf(\DateTime::class, $date);
		$this->assertEquals('2024-01-15', $date->format('Y-m-d'));

		$this->assertNull(Date::parse('invalid'));
		$this->assertNull(Date::parse(''));
	}

	public function testParseOrNull(): void
	{
		$date = Date::parseOrNull('2024-01-15');
		$this->assertInstanceOf(\DateTime::class, $date);

		$this->assertNull(Date::parseOrNull('invalid'));
	}

	public function testParseOrThrow(): void
	{
		$date = Date::parseOrThrow('2024-01-15');
		$this->assertInstanceOf(\DateTime::class, $date);

		$this->expectException(\InvalidArgumentException::class);
		Date::parseOrThrow('invalid');
	}

	// ========== Day Names Tests ==========

	public function testGetDayName(): void
	{
		// Test English locale
		$this->assertEquals('Monday', Date::getDayName(1, 'en_US'));
		$this->assertEquals('Sunday', Date::getDayName(7, 'en_US'));

		// Test French locale (ucfirst is applied)
		$this->assertEquals('Lundi', Date::getDayName(1, 'fr_FR'));
		$this->assertEquals('Dimanche', Date::getDayName(7, 'fr_FR'));
	}

	public function testGetDayNameShort(): void
	{
		// Test English locale
		$dayName = Date::getDayNameShort(1, 'en_US');
		$this->assertNotEmpty($dayName);
		$this->assertStringContainsString('Mon', $dayName);

		$dayName = Date::getDayNameShort(7, 'en_US');
		$this->assertNotEmpty($dayName);
		$this->assertStringContainsString('Sun', $dayName);
	}

	// ========== Month Names Tests ==========

	public function testGetMonthName(): void
	{
		// Test English locale
		$this->assertEquals('January', Date::getMonthName(1, 'en_US'));
		$this->assertEquals('December', Date::getMonthName(12, 'en_US'));

		// Test French locale (ucfirst is applied)
		$this->assertEquals('Janvier', Date::getMonthName(1, 'fr_FR'));
		$this->assertEquals('Décembre', Date::getMonthName(12, 'fr_FR'));
	}

	public function testGetMonthNameShort(): void
	{
		// Test English locale
		$monthName = Date::getMonthNameShort(1, 'en_US');
		$this->assertNotEmpty($monthName);
		$this->assertStringContainsString('Jan', $monthName);

		$monthName = Date::getMonthNameShort(12, 'en_US');
		$this->assertNotEmpty($monthName);
		$this->assertStringContainsString('Dec', $monthName);
	}

	public function testGetMonthsInYearArray(): void
	{
		$months = Date::getMonthsInYearArray('en_US');
		$this->assertIsArray($months);
		$this->assertCount(12, $months);
		$this->assertEquals('January', $months[1]);
		$this->assertEquals('December', $months[12]);
	}

	// ========== Calendar Info Tests ==========

	public function testGetNumberOfDaysInMonth(): void
	{
		$this->assertEquals(31, Date::getNumberOfDaysInMonth(2024, 1)); // January
		$this->assertEquals(29, Date::getNumberOfDaysInMonth(2024, 2)); // February (leap year)
		$this->assertEquals(28, Date::getNumberOfDaysInMonth(2023, 2)); // February (non-leap year)
		$this->assertEquals(30, Date::getNumberOfDaysInMonth(2024, 4)); // April
		$this->assertEquals(31, Date::getNumberOfDaysInMonth(2024, 12)); // December
	}

	public function testGetDaysInMonthArray(): void
	{
		$days = Date::getDaysInMonthArray(2024, 2);
		$this->assertIsArray($days);
		$this->assertCount(29, $days); // 2024 is a leap year
		$this->assertInstanceOf(\DateTime::class, $days[0]);
		$this->assertEquals('2024-02-01', $days[0]->format('Y-m-d'));
		$this->assertEquals('2024-02-29', $days[28]->format('Y-m-d'));
	}

	public function testIsLeapYear(): void
	{
		$this->assertTrue(Date::isLeapYear(2024));
		$this->assertTrue(Date::isLeapYear(2000));
		$this->assertFalse(Date::isLeapYear(2023));
		$this->assertFalse(Date::isLeapYear(1900));
	}

	public function testGetNumberOfDaysInYear(): void
	{
		$this->assertEquals(366, Date::getNumberOfDaysInYear(2024)); // Leap year
		$this->assertEquals(365, Date::getNumberOfDaysInYear(2023)); // Non-leap year
		$this->assertEquals(366, Date::getNumberOfDaysInYear(2000)); // Leap year
		$this->assertEquals(365, Date::getNumberOfDaysInYear(1900)); // Not a leap year
	}

	public function testGetWeeksInYear(): void
	{
		$weeks2024 = Date::getWeeksInYear(2024);
		$this->assertGreaterThanOrEqual(52, $weeks2024);
		$this->assertLessThanOrEqual(53, $weeks2024);

		$weeks2023 = Date::getWeeksInYear(2023);
		$this->assertGreaterThanOrEqual(52, $weeks2023);
		$this->assertLessThanOrEqual(53, $weeks2023);
	}

	// ========== Validation Tests ==========

	public function testIsValid(): void
	{
		$this->assertTrue(Date::isValid('2024-01-15'));
		$this->assertTrue(Date::isValid('20240115'));
		$this->assertFalse(Date::isValid('invalid'));
		$this->assertFalse(Date::isValid(''));
		$this->assertFalse(Date::isValid('2024-13-01')); // Invalid month
	}
}