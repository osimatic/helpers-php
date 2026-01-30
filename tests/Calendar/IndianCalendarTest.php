<?php

namespace Tests\Calendar;

use Osimatic\Calendar\IndianCalendar;
use PHPUnit\Framework\TestCase;

final class IndianCalendarTest extends TestCase
{
	/* ========== Constants ========== */

	public function testIndianCivilWeekdaysConstant(): void
	{
		$this->assertCount(7, IndianCalendar::INDIAN_CIVIL_WEEKDAYS);
		$this->assertEquals('Ravivara', IndianCalendar::INDIAN_CIVIL_WEEKDAYS[0]);
		$this->assertEquals('Sanivara', IndianCalendar::INDIAN_CIVIL_WEEKDAYS[6]);
	}

	public function testIndianMonthsConstant(): void
	{
		$this->assertCount(12, IndianCalendar::INDIAN_MONTHS);
		$this->assertEquals('Chaitra', IndianCalendar::INDIAN_MONTHS[0]);
		$this->assertEquals('Phalguna', IndianCalendar::INDIAN_MONTHS[11]);
	}

	/* ========== Timestamp Methods ========== */

	public function testGetTimestamp(): void
	{
		// Indian date: 1 Chaitra 1945 (equivalent to March 22, 2023)
		$timestamp = IndianCalendar::getTimestamp(1945, 1, 1, 0, 0, 0);
		$this->assertIsInt($timestamp);
		$this->assertGreaterThan(0, $timestamp);

		// Verify the conversion is correct
		$gregorianDate = date('Y-m-d', $timestamp);
		$this->assertEquals('2023-03-22', $gregorianDate);

		// Test with time components
		$timestamp = IndianCalendar::getTimestamp(1945, 1, 1, 14, 30, 45);
		$this->assertEquals('14:30:45', date('H:i:s', $timestamp));
	}

	/* ========== Conversion Methods ========== */

	public function testConvertIndianDateToGregorianDate(): void
	{
		// 1 Chaitra 1945 = March 22, 2023
		[$year, $month, $day] = IndianCalendar::convertIndianDateToGregorianDate(1945, 1, 1);
		$this->assertEquals(2023, $year);
		$this->assertEquals(3, $month);
		$this->assertEquals(22, $day);

		// 15 Chaitra 1945 = April 5, 2023
		[$year, $month, $day] = IndianCalendar::convertIndianDateToGregorianDate(1945, 1, 15);
		$this->assertEquals(2023, $year);
		$this->assertEquals(4, $month);
		$this->assertEquals(5, $day);

		// 1 Vaisakha 1945 = April 21, 2023
		[$year, $month, $day] = IndianCalendar::convertIndianDateToGregorianDate(1945, 2, 1);
		$this->assertEquals(2023, $year);
		$this->assertEquals(4, $month);
		$this->assertEquals(21, $day);
	}

	public function testConvertGregorianDateToIndianDate(): void
	{
		// March 22, 2023 = 1 Chaitra 1945
		[$year, $month, $day] = IndianCalendar::convertGregorianDateToIndianDate(2023, 3, 22);
		$this->assertEquals(1945, $year);
		$this->assertEquals(1, $month);
		$this->assertEquals(1, $day);

		// April 5, 2023 = 15 Chaitra 1945
		[$year, $month, $day] = IndianCalendar::convertGregorianDateToIndianDate(2023, 4, 5);
		$this->assertEquals(1945, $year);
		$this->assertEquals(1, $month);
		$this->assertEquals(15, $day);

		// April 21, 2023 = 1 Vaisakha 1945
		[$year, $month, $day] = IndianCalendar::convertGregorianDateToIndianDate(2023, 4, 21);
		$this->assertEquals(1945, $year);
		$this->assertEquals(2, $month);
		$this->assertEquals(1, $day);
	}

	public function testConvertTimestampToIndianDate(): void
	{
		// March 22, 2023 00:00:00 = 1 Chaitra 1945
		$timestamp = mktime(0, 0, 0, 3, 22, 2023);
		[$year, $month, $day] = IndianCalendar::convertTimestampToIndianDate($timestamp);
		$this->assertEquals(1945, $year);
		$this->assertEquals(1, $month);
		$this->assertEquals(1, $day);
	}

	public function testRoundTripConversion(): void
	{
		// Test round-trip conversion: Indian -> Gregorian -> Indian
		$originalIndianYear = 1945;
		$originalIndianMonth = 6;
		$originalIndianDay = 15;

		[$gregYear, $gregMonth, $gregDay] = IndianCalendar::convertIndianDateToGregorianDate($originalIndianYear, $originalIndianMonth, $originalIndianDay);
		[$indianYear, $indianMonth, $indianDay] = IndianCalendar::convertGregorianDateToIndianDate($gregYear, $gregMonth, $gregDay);

		$this->assertEquals($originalIndianYear, $indianYear);
		$this->assertEquals($originalIndianMonth, $indianMonth);
		$this->assertEquals($originalIndianDay, $indianDay);
	}

	/* ========== Validation Methods ========== */

	public function testIsValidDate(): void
	{
		// Valid dates
		$this->assertTrue(IndianCalendar::isValidDate(1945, 1, 1));
		$this->assertTrue(IndianCalendar::isValidDate(1945, 1, 30));
		$this->assertTrue(IndianCalendar::isValidDate(1945, 2, 31));
		$this->assertTrue(IndianCalendar::isValidDate(1945, 7, 30));
		$this->assertTrue(IndianCalendar::isValidDate(1945, 12, 30));

		// Invalid year
		$this->assertFalse(IndianCalendar::isValidDate(-1, 1, 1));

		// Invalid month
		$this->assertFalse(IndianCalendar::isValidDate(1945, 0, 1));
		$this->assertFalse(IndianCalendar::isValidDate(1945, 13, 1));

		// Invalid day
		$this->assertFalse(IndianCalendar::isValidDate(1945, 1, 0));
		$this->assertFalse(IndianCalendar::isValidDate(1945, 1, 32));
		$this->assertFalse(IndianCalendar::isValidDate(1945, 2, 32)); // Month 2 has 31 days
		$this->assertFalse(IndianCalendar::isValidDate(1945, 7, 31)); // Month 7 has 30 days
	}

	/* ========== Utility Methods ========== */

	public function testGetMonthName(): void
	{
		$this->assertEquals('Chaitra', IndianCalendar::getMonthName(1));
		$this->assertEquals('Vaisakha', IndianCalendar::getMonthName(2));
		$this->assertEquals('Jyaistha', IndianCalendar::getMonthName(3));
		$this->assertEquals('Asadha', IndianCalendar::getMonthName(4));
		$this->assertEquals('Sravana', IndianCalendar::getMonthName(5));
		$this->assertEquals('Bhadra', IndianCalendar::getMonthName(6));
		$this->assertEquals('Asvina', IndianCalendar::getMonthName(7));
		$this->assertEquals('Kartika', IndianCalendar::getMonthName(8));
		$this->assertEquals('Agrahayana', IndianCalendar::getMonthName(9));
		$this->assertEquals('Pausa', IndianCalendar::getMonthName(10));
		$this->assertEquals('Magha', IndianCalendar::getMonthName(11));
		$this->assertEquals('Phalguna', IndianCalendar::getMonthName(12));

		// Invalid month
		$this->assertNull(IndianCalendar::getMonthName(0));
		$this->assertNull(IndianCalendar::getMonthName(13));
	}

	public function testGetWeekdayName(): void
	{
		$this->assertEquals('Ravivara', IndianCalendar::getWeekdayName(0)); // Sunday
		$this->assertEquals('Somavara', IndianCalendar::getWeekdayName(1)); // Monday
		$this->assertEquals('Mangalavara', IndianCalendar::getWeekdayName(2)); // Tuesday
		$this->assertEquals('Budhavara', IndianCalendar::getWeekdayName(3)); // Wednesday
		$this->assertEquals('Brahaspativara', IndianCalendar::getWeekdayName(4)); // Thursday
		$this->assertEquals('Sukravara', IndianCalendar::getWeekdayName(5)); // Friday
		$this->assertEquals('Sanivara', IndianCalendar::getWeekdayName(6)); // Saturday

		// Invalid weekday
		$this->assertNull(IndianCalendar::getWeekdayName(-1));
		$this->assertNull(IndianCalendar::getWeekdayName(7));
	}

	public function testGetDaysInMonth(): void
	{
		// Month 1 (Chaitra) - 30 days in common year
		$this->assertEquals(30, IndianCalendar::getDaysInMonth(1945, 1));

		// Month 1 (Chaitra) - 31 days in leap year (1944 + 78 = 2022, not a leap year; 1946 + 78 = 2024, leap year)
		$this->assertEquals(31, IndianCalendar::getDaysInMonth(1946, 1));

		// Months 2-6 have 31 days
		$this->assertEquals(31, IndianCalendar::getDaysInMonth(1945, 2));
		$this->assertEquals(31, IndianCalendar::getDaysInMonth(1945, 3));
		$this->assertEquals(31, IndianCalendar::getDaysInMonth(1945, 4));
		$this->assertEquals(31, IndianCalendar::getDaysInMonth(1945, 5));
		$this->assertEquals(31, IndianCalendar::getDaysInMonth(1945, 6));

		// Months 7-12 have 30 days
		$this->assertEquals(30, IndianCalendar::getDaysInMonth(1945, 7));
		$this->assertEquals(30, IndianCalendar::getDaysInMonth(1945, 8));
		$this->assertEquals(30, IndianCalendar::getDaysInMonth(1945, 9));
		$this->assertEquals(30, IndianCalendar::getDaysInMonth(1945, 10));
		$this->assertEquals(30, IndianCalendar::getDaysInMonth(1945, 11));
		$this->assertEquals(30, IndianCalendar::getDaysInMonth(1945, 12));

		// Invalid month
		$this->assertNull(IndianCalendar::getDaysInMonth(1945, 0));
		$this->assertNull(IndianCalendar::getDaysInMonth(1945, 13));
	}

	public function testFormat(): void
	{
		// Long format
		$this->assertEquals('15 Chaitra 1945', IndianCalendar::format(1945, 1, 15, 'long'));
		$this->assertEquals('1 Vaisakha 1945', IndianCalendar::format(1945, 2, 1, 'long'));

		// Short format
		$this->assertEquals('15/1/1945', IndianCalendar::format(1945, 1, 15, 'short'));
		$this->assertEquals('1/2/1945', IndianCalendar::format(1945, 2, 1, 'short'));

		// Medium format (abbreviated month name)
		$this->assertEquals('15 Cha 1945', IndianCalendar::format(1945, 1, 15, 'medium'));
		$this->assertEquals('1 Vai 1945', IndianCalendar::format(1945, 2, 1, 'medium'));
		$this->assertEquals('30 Agr 1945', IndianCalendar::format(1945, 9, 30, 'medium'));

		// ISO format
		$this->assertEquals('1945-01-15', IndianCalendar::format(1945, 1, 15, 'iso'));
		$this->assertEquals('1945-02-01', IndianCalendar::format(1945, 2, 1, 'iso'));
		$this->assertEquals('1945-12-30', IndianCalendar::format(1945, 12, 30, 'iso'));

		// Full format (with weekday name) - March 22, 2023 = 1 Chaitra 1945 = Wednesday
		$this->assertEquals('Budhavara 1 Chaitra 1945', IndianCalendar::format(1945, 1, 1, 'full'));
		// April 21, 2023 = 1 Vaisakha 1945 = Friday
		$this->assertEquals('Sukravara 1 Vaisakha 1945', IndianCalendar::format(1945, 2, 1, 'full'));

		// Default format (long)
		$this->assertEquals('15 Chaitra 1945', IndianCalendar::format(1945, 1, 15));

		// Invalid date
		$this->assertNull(IndianCalendar::format(1945, 13, 1, 'long'));
		$this->assertNull(IndianCalendar::format(1945, 1, 32, 'long'));

		// Invalid format
		$this->assertNull(IndianCalendar::format(1945, 1, 15, 'invalid'));
	}
}