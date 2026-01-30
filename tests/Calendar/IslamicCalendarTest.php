<?php

namespace Tests\Calendar;

use Osimatic\Calendar\IslamicCalendar;
use PHPUnit\Framework\TestCase;

final class IslamicCalendarTest extends TestCase
{
	/* ========== Constants ========== */

	public function testIslamicMonthsArConstant(): void
	{
		$this->assertCount(12, IslamicCalendar::ISLAMIC_MONTHS_AR);
		$this->assertEquals('محرم', IslamicCalendar::ISLAMIC_MONTHS_AR[0]);
		$this->assertEquals('ذو الحجة', IslamicCalendar::ISLAMIC_MONTHS_AR[11]);
	}

	public function testIslamicMonthsEnConstant(): void
	{
		$this->assertCount(12, IslamicCalendar::ISLAMIC_MONTHS_EN);
		$this->assertEquals('Muharram', IslamicCalendar::ISLAMIC_MONTHS_EN[0]);
		$this->assertEquals('Dhu al-Hijjah', IslamicCalendar::ISLAMIC_MONTHS_EN[11]);
	}

	public function testIslamicWeekdaysArConstant(): void
	{
		$this->assertCount(7, IslamicCalendar::ISLAMIC_WEEKDAYS_AR);
		$this->assertEquals('الأحد', IslamicCalendar::ISLAMIC_WEEKDAYS_AR[0]);
		$this->assertEquals('السبت', IslamicCalendar::ISLAMIC_WEEKDAYS_AR[6]);
	}

	public function testIslamicWeekdaysEnConstant(): void
	{
		$this->assertCount(7, IslamicCalendar::ISLAMIC_WEEKDAYS_EN);
		$this->assertEquals('Al-Ahad', IslamicCalendar::ISLAMIC_WEEKDAYS_EN[0]);
		$this->assertEquals('As-Sabt', IslamicCalendar::ISLAMIC_WEEKDAYS_EN[6]);
	}

	/* ========== Timestamp Methods ========== */

	public function testGetTimestamp(): void
	{
		// 1 Muharram 1446 = July 7, 2024 (approximately)
		$timestamp = IslamicCalendar::getTimestamp(1446, 1, 1, 0, 0, 0);
		$this->assertIsInt($timestamp);
		$this->assertGreaterThan(0, $timestamp);

		// Test with time components
		$timestamp = IslamicCalendar::getTimestamp(1446, 1, 1, 14, 30, 45);
		$this->assertEquals('14:30:45', date('H:i:s', $timestamp));
	}

	/* ========== Conversion Methods ========== */

	public function testConvertIslamicDateToGregorianDate(): void
	{
		// 1 Ramadan 1445 = March 11, 2024 (approximately)
		[$year, $month, $day] = IslamicCalendar::convertIslamicDateToGregorianDate(1445, 9, 1);
		$this->assertEquals(2024, $year);
		$this->assertEquals(3, $month);
		$this->assertEquals(11, $day);
	}

	public function testConvertGregorianDateToIslamicDate(): void
	{
		// March 11, 2024 = 1 Ramadan 1445 (approximately)
		[$year, $month, $day] = IslamicCalendar::convertGregorianDateToIslamicDate(2024, 3, 11);
		$this->assertEquals(1445, $year);
		$this->assertEquals(9, $month);
		$this->assertEquals(1, $day);
	}

	public function testConvertTimestampToIslamicDate(): void
	{
		// March 11, 2024 00:00:00 = 1 Ramadan 1445 (approximately)
		$timestamp = mktime(0, 0, 0, 3, 11, 2024);
		[$year, $month, $day] = IslamicCalendar::convertTimestampToIslamicDate($timestamp);
		$this->assertEquals(1445, $year);
		$this->assertEquals(9, $month);
		$this->assertEquals(1, $day);
	}

	public function testRoundTripConversion(): void
	{
		// Test round-trip conversion: Islamic -> Gregorian -> Islamic
		$originalYear = 1445;
		$originalMonth = 6;
		$originalDay = 15;

		[$gregYear, $gregMonth, $gregDay] = IslamicCalendar::convertIslamicDateToGregorianDate($originalYear, $originalMonth, $originalDay);
		[$islamicYear, $islamicMonth, $islamicDay] = IslamicCalendar::convertGregorianDateToIslamicDate($gregYear, $gregMonth, $gregDay);

		$this->assertEquals($originalYear, $islamicYear);
		$this->assertEquals($originalMonth, $islamicMonth);
		$this->assertEquals($originalDay, $islamicDay);
	}

	/* ========== Validation Methods ========== */

	public function testIsValidDate(): void
	{
		// Valid dates
		$this->assertTrue(IslamicCalendar::isValidDate(1445, 1, 1));
		$this->assertTrue(IslamicCalendar::isValidDate(1445, 1, 30)); // Odd month has 30 days
		$this->assertTrue(IslamicCalendar::isValidDate(1445, 2, 29)); // Even month has 29 days
		$this->assertTrue(IslamicCalendar::isValidDate(1445, 9, 30)); // Ramadan (odd) has 30 days
		$this->assertTrue(IslamicCalendar::isValidDate(1445, 12, 30)); // 12th month can have 30 days in leap year

		// Invalid year
		$this->assertFalse(IslamicCalendar::isValidDate(0, 1, 1));
		$this->assertFalse(IslamicCalendar::isValidDate(-1, 1, 1));

		// Invalid month
		$this->assertFalse(IslamicCalendar::isValidDate(1445, 0, 1));
		$this->assertFalse(IslamicCalendar::isValidDate(1445, 13, 1));

		// Invalid day
		$this->assertFalse(IslamicCalendar::isValidDate(1445, 1, 0));
		$this->assertFalse(IslamicCalendar::isValidDate(1445, 1, 31)); // Muharram has only 30 days
		$this->assertFalse(IslamicCalendar::isValidDate(1445, 2, 30)); // Safar has only 29 days
	}

	public function testIsLeapYear(): void
	{
		// Leap years in 30-year cycle: 2, 5, 7, 10, 13, 16, 18, 21, 24, 26, 29
		$this->assertTrue(IslamicCalendar::isLeapYear(1445)); // (1445-1) % 30 = 1444 % 30 = 4, +1 = 5 (leap)
		$this->assertTrue(IslamicCalendar::isLeapYear(1442)); // (1442-1) % 30 = 1441 % 30 = 1, +1 = 2 (leap)
		$this->assertTrue(IslamicCalendar::isLeapYear(1447)); // (1447-1) % 30 = 1446 % 30 = 6, +1 = 7 (leap)

		// Non-leap years
		$this->assertFalse(IslamicCalendar::isLeapYear(1443)); // (1443-1) % 30 = 1442 % 30 = 2, +1 = 3 (not leap)
		$this->assertFalse(IslamicCalendar::isLeapYear(1444)); // (1444-1) % 30 = 1443 % 30 = 3, +1 = 4 (not leap)
	}

	/* ========== Utility Methods ========== */

	public function testGetMonthName(): void
	{
		// English names
		$this->assertEquals('Muharram', IslamicCalendar::getMonthName(1, 'en'));
		$this->assertEquals('Safar', IslamicCalendar::getMonthName(2, 'en'));
		$this->assertEquals('Ramadan', IslamicCalendar::getMonthName(9, 'en'));
		$this->assertEquals('Dhu al-Hijjah', IslamicCalendar::getMonthName(12, 'en'));

		// Arabic names
		$this->assertEquals('محرم', IslamicCalendar::getMonthName(1, 'ar'));
		$this->assertEquals('صفر', IslamicCalendar::getMonthName(2, 'ar'));
		$this->assertEquals('رمضان', IslamicCalendar::getMonthName(9, 'ar'));
		$this->assertEquals('ذو الحجة', IslamicCalendar::getMonthName(12, 'ar'));

		// Invalid month
		$this->assertNull(IslamicCalendar::getMonthName(0, 'en'));
		$this->assertNull(IslamicCalendar::getMonthName(13, 'en'));
	}

	public function testGetWeekdayName(): void
	{
		// English names
		$this->assertEquals('Al-Ahad', IslamicCalendar::getWeekdayName(0, 'en')); // Sunday
		$this->assertEquals('Al-Ithnayn', IslamicCalendar::getWeekdayName(1, 'en')); // Monday
		$this->assertEquals('Al-Jumu\'ah', IslamicCalendar::getWeekdayName(5, 'en')); // Friday
		$this->assertEquals('As-Sabt', IslamicCalendar::getWeekdayName(6, 'en')); // Saturday

		// Arabic names
		$this->assertEquals('الأحد', IslamicCalendar::getWeekdayName(0, 'ar')); // Sunday
		$this->assertEquals('الاثنين', IslamicCalendar::getWeekdayName(1, 'ar')); // Monday
		$this->assertEquals('الجمعة', IslamicCalendar::getWeekdayName(5, 'ar')); // Friday
		$this->assertEquals('السبت', IslamicCalendar::getWeekdayName(6, 'ar')); // Saturday

		// Invalid weekday
		$this->assertNull(IslamicCalendar::getWeekdayName(-1, 'en'));
		$this->assertNull(IslamicCalendar::getWeekdayName(7, 'en'));
	}

	public function testGetNbDaysOfMonth(): void
	{
		// Test standard tabular calendar (umAlqoura = false)
		// Odd months have 30 days
		$this->assertEquals(30, IslamicCalendar::getNbDaysOfMonth(1445, 1, false)); // Muharram
		$this->assertEquals(30, IslamicCalendar::getNbDaysOfMonth(1445, 3, false)); // Rabi' al-awwal
		$this->assertEquals(30, IslamicCalendar::getNbDaysOfMonth(1445, 9, false)); // Ramadan
		$this->assertEquals(30, IslamicCalendar::getNbDaysOfMonth(1445, 11, false)); // Dhu al-Qi'dah

		// Even months have 29 days
		$this->assertEquals(29, IslamicCalendar::getNbDaysOfMonth(1445, 2, false)); // Safar
		$this->assertEquals(29, IslamicCalendar::getNbDaysOfMonth(1445, 4, false)); // Rabi' al-thani
		$this->assertEquals(29, IslamicCalendar::getNbDaysOfMonth(1445, 10, false)); // Shawwal

		// 12th month: 29 days in common years, 30 days in leap years
		$this->assertEquals(30, IslamicCalendar::getNbDaysOfMonth(1445, 12, false)); // Leap year
		$this->assertEquals(29, IslamicCalendar::getNbDaysOfMonth(1444, 12, false)); // Non-leap year

		// Invalid month
		$this->assertNull(IslamicCalendar::getNbDaysOfMonth(1445, 0, false));
		$this->assertNull(IslamicCalendar::getNbDaysOfMonth(1445, 13, false));

		// Test Um-Al-Qura calendar (umAlqoura = true)
		// Test with a valid year in the Um-Al-Qura range
		$days = IslamicCalendar::getNbDaysOfMonth(1430, 9, true);
		$this->assertGreaterThan(0, $days);
		$this->assertLessThanOrEqual(30, $days);

		// Test outside valid range
		$this->assertEquals(0, IslamicCalendar::getNbDaysOfMonth(1300, 1, true));
		$this->assertEquals(0, IslamicCalendar::getNbDaysOfMonth(1500, 1, true));
	}

	public function testFormat(): void
	{
		// Long format - English
		$this->assertEquals('15 Ramadan 1445', IslamicCalendar::format(1445, 9, 15, 'long', 'en'));
		$this->assertEquals('1 Muharram 1446', IslamicCalendar::format(1446, 1, 1, 'long', 'en'));

		// Long format - Arabic
		$this->assertEquals('15 رمضان 1445', IslamicCalendar::format(1445, 9, 15, 'long', 'ar'));

		// Medium format - English (abbreviated month name)
		$this->assertEquals('15 Ram 1445', IslamicCalendar::format(1445, 9, 15, 'medium', 'en'));
		$this->assertEquals('1 Muh 1446', IslamicCalendar::format(1446, 1, 1, 'medium', 'en'));

		// Short format
		$this->assertEquals('15/9/1445', IslamicCalendar::format(1445, 9, 15, 'short', 'en'));
		$this->assertEquals('1/1/1446', IslamicCalendar::format(1446, 1, 1, 'short', 'en'));

		// ISO format
		$this->assertEquals('1445-09-15', IslamicCalendar::format(1445, 9, 15, 'iso', 'en'));
		$this->assertEquals('1446-01-01', IslamicCalendar::format(1446, 1, 1, 'iso', 'en'));
		$this->assertEquals('1445-12-30', IslamicCalendar::format(1445, 12, 30, 'iso', 'en'));

		// Full format - needs to calculate weekday from Gregorian conversion
		$formatted = IslamicCalendar::format(1445, 9, 1, 'full', 'en');
		$this->assertNotNull($formatted);
		$this->assertStringContainsString('Ramadan', $formatted);
		$this->assertStringContainsString('1445', $formatted);

		// Default format (long)
		$this->assertEquals('15 Ramadan 1445', IslamicCalendar::format(1445, 9, 15));

		// Invalid date
		$this->assertNull(IslamicCalendar::format(1445, 13, 1, 'long', 'en'));
		$this->assertNull(IslamicCalendar::format(1445, 1, 32, 'long', 'en'));

		// Invalid format
		$this->assertNull(IslamicCalendar::format(1445, 9, 15, 'invalid', 'en'));
	}
}