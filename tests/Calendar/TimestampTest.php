<?php

namespace Tests\Calendar;

use Osimatic\Calendar\DateTime;
use Osimatic\Calendar\Timestamp;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Timestamp class - Unix timestamp manipulation
 */
final class TimestampTest extends TestCase
{
	// ========== Current Time Methods Tests ==========

	public function testGetCurrentTimestamp(): void
	{
		$timestamp = Timestamp::getCurrentTimestamp();
		$this->assertIsInt($timestamp);
		$this->assertGreaterThan(0, $timestamp);

		// Verify it's close to PHP's time()
		$this->assertEqualsWithDelta(time(), $timestamp, 1);
	}

	// ========== Creation Methods Tests ==========

	public function testFromDateTime(): void
	{
		$dateTime = new \DateTime('2024-01-15 14:30:45');
		$timestamp = Timestamp::fromDateTime($dateTime);

		$this->assertIsInt($timestamp);
		$this->assertEquals('2024-01-15', date('Y-m-d', $timestamp));
		$this->assertEquals('14:30:45', date('H:i:s', $timestamp));
	}

	public function testCreate(): void
	{
		// With time
		$timestamp = Timestamp::create(2024, 1, 15, 14, 30, 45);
		$this->assertEquals('2024-01-15', date('Y-m-d', $timestamp));
		$this->assertEquals('14:30:45', date('H:i:s', $timestamp));

		// Without time (defaults to midnight)
		$timestamp = Timestamp::create(2024, 1, 15);
		$this->assertEquals('2024-01-15', date('Y-m-d', $timestamp));
		$this->assertEquals('00:00:00', date('H:i:s', $timestamp));
	}

	public function testCreateFromDate(): void
	{
		$timestamp = Timestamp::createFromDate(2024, 1, 15);
		$this->assertEquals('2024-01-15', date('Y-m-d', $timestamp));
		$this->assertEquals('00:00:00', date('H:i:s', $timestamp));

		$timestamp = Timestamp::createFromDate(2023, 12, 31);
		$this->assertEquals('2023-12-31', date('Y-m-d', $timestamp));
	}

	// ========== Conversion Methods Tests ==========

	public function testToDateTime(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);
		$dateTime = Timestamp::toDateTime($timestamp);

		$this->assertInstanceOf(\DateTime::class, $dateTime);
		$this->assertEquals('2024-01-15', $dateTime->format('Y-m-d'));
		$this->assertEquals('14:30:45', $dateTime->format('H:i:s'));
	}

	// ========== Validation & Comparison Methods Tests ==========

	public function testIsDateInThePast(): void
	{
		// Date in the past
		$yesterday = mktime(0, 0, 0, (int) date('m'), (int) date('d') - 1, (int) date('Y'));
		$this->assertTrue(Timestamp::isDateInThePast($yesterday));

		// Today (start of day)
		$today = mktime(0, 0, 0, (int) date('m'), (int) date('d'), (int) date('Y'));
		$this->assertFalse(Timestamp::isDateInThePast($today));

		// Date in the future
		$tomorrow = mktime(0, 0, 0, (int) date('m'), (int) date('d') + 1, (int) date('Y'));
		$this->assertFalse(Timestamp::isDateInThePast($tomorrow));
	}

	public function testIsDateInTheFuture(): void
	{
		// Date in the past
		$yesterday = mktime(0, 0, 0, (int) date('m'), (int) date('d') - 1, (int) date('Y'));
		$this->assertFalse(Timestamp::isDateInTheFuture($yesterday));

		// Today (should be false)
		$today = mktime(0, 0, 0, (int) date('m'), (int) date('d'), (int) date('Y'));
		$this->assertFalse(Timestamp::isDateInTheFuture($today));

		// Date in the future
		$tomorrow = mktime(0, 0, 0, (int) date('m'), (int) date('d') + 1, (int) date('Y'));
		$this->assertTrue(Timestamp::isDateInTheFuture($tomorrow));
	}

	public function testIsTimeInThePast(): void
	{
		// 1 hour ago
		$oneHourAgo = time() - 3600;
		$this->assertTrue(Timestamp::isTimeInThePast($oneHourAgo));

		// In 1 hour
		$oneHourLater = time() + 3600;
		$this->assertFalse(Timestamp::isTimeInThePast($oneHourLater));
	}

	public function testIsTimeInTheFuture(): void
	{
		// 1 hour ago
		$oneHourAgo = time() - 3600;
		$this->assertFalse(Timestamp::isTimeInTheFuture($oneHourAgo));

		// In 1 hour
		$oneHourLater = time() + 3600;
		$this->assertTrue(Timestamp::isTimeInTheFuture($oneHourLater));
	}

	public function testIsBetween(): void
	{
		$start = mktime(10, 0, 0, 1, 15, 2024);
		$middle = mktime(12, 0, 0, 1, 15, 2024);
		$end = mktime(14, 0, 0, 1, 15, 2024);
		$outside = mktime(16, 0, 0, 1, 15, 2024);

		// Within range (inclusive)
		$this->assertTrue(Timestamp::isBetween($middle, $start, $end));
		$this->assertTrue(Timestamp::isBetween($start, $start, $end, true));
		$this->assertTrue(Timestamp::isBetween($end, $start, $end, true));

		// Within range (exclusive)
		$this->assertTrue(Timestamp::isBetween($middle, $start, $end, false));
		$this->assertFalse(Timestamp::isBetween($start, $start, $end, false));
		$this->assertFalse(Timestamp::isBetween($end, $start, $end, false));

		// Outside range
		$this->assertFalse(Timestamp::isBetween($outside, $start, $end));
	}

	// ========== Calculation Methods Tests ==========

	public function testAddSeconds(): void
	{
		$timestamp = mktime(10, 0, 0, 1, 15, 2024);
		$result = Timestamp::addSeconds($timestamp, 90);
		$this->assertEquals('10:01:30', date('H:i:s', $result));
	}

	public function testSubSeconds(): void
	{
		$timestamp = mktime(10, 1, 30, 1, 15, 2024);
		$result = Timestamp::subSeconds($timestamp, 45);
		$this->assertEquals('10:00:45', date('H:i:s', $result));
	}

	public function testAddMinutes(): void
	{
		$timestamp = mktime(10, 0, 0, 1, 15, 2024);
		$result = Timestamp::addMinutes($timestamp, 45);
		$this->assertEquals('10:45:00', date('H:i:s', $result));
	}

	public function testSubMinutes(): void
	{
		$timestamp = mktime(10, 45, 0, 1, 15, 2024);
		$result = Timestamp::subMinutes($timestamp, 30);
		$this->assertEquals('10:15:00', date('H:i:s', $result));
	}

	public function testAddHours(): void
	{
		$timestamp = mktime(10, 0, 0, 1, 15, 2024);
		$result = Timestamp::addHours($timestamp, 3);
		$this->assertEquals('13:00:00', date('H:i:s', $result));
	}

	public function testSubHours(): void
	{
		$timestamp = mktime(15, 0, 0, 1, 15, 2024);
		$result = Timestamp::subHours($timestamp, 3);
		$this->assertEquals('12:00:00', date('H:i:s', $result));
	}

	public function testAddDays(): void
	{
		$timestamp = mktime(0, 0, 0, 1, 15, 2024);
		$result = Timestamp::addDays($timestamp, 5);
		$this->assertEquals('2024-01-20', date('Y-m-d', $result));
	}

	public function testSubDays(): void
	{
		$timestamp = mktime(0, 0, 0, 1, 15, 2024);
		$result = Timestamp::subDays($timestamp, 5);
		$this->assertEquals('2024-01-10', date('Y-m-d', $result));
	}

	public function testGetStartOfDay(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);
		$result = Timestamp::getStartOfDay($timestamp);
		$this->assertEquals('2024-01-15', date('Y-m-d', $result));
		$this->assertEquals('00:00:00', date('H:i:s', $result));
	}

	public function testGetEndOfDay(): void
	{
		$timestamp = mktime(10, 0, 0, 1, 15, 2024);
		$result = Timestamp::getEndOfDay($timestamp);
		$this->assertEquals('2024-01-15', date('Y-m-d', $result));
		$this->assertEquals('23:59:59', date('H:i:s', $result));
	}

	// ========== Day of Week Methods Tests ==========

	public function testGetDayOfWeek(): void
	{
		// 2024-01-15 is a Monday (1)
		$monday = mktime(0, 0, 0, 1, 15, 2024);
		$this->assertEquals(1, Timestamp::getDayOfWeek($monday));

		// 2024-01-21 is a Sunday (7)
		$sunday = mktime(0, 0, 0, 1, 21, 2024);
		$this->assertEquals(7, Timestamp::getDayOfWeek($sunday));
	}

	public function testGetNextDayOfWeek(): void
	{
		// 2024-01-15 is a Monday (1)
		// Find next Wednesday (3)
		$timestamp = Timestamp::getNextDayOfWeek(2024, 1, 15, 3);
		$this->assertEquals(3, (int) date('N', $timestamp)); // 3 = Wednesday
		$this->assertEquals('2024-01-17', date('Y-m-d', $timestamp));

		// Find same day of week
		$timestamp = Timestamp::getNextDayOfWeek(2024, 1, 15, 1);
		$this->assertEquals(1, (int) date('N', $timestamp)); // 1 = Monday
		$this->assertEquals('2024-01-15', date('Y-m-d', $timestamp));

		// Find Sunday from Monday
		$timestamp = Timestamp::getNextDayOfWeek(2024, 1, 15, 7);
		$this->assertEquals(7, (int) date('N', $timestamp)); // 7 = Sunday
		$this->assertEquals('2024-01-21', date('Y-m-d', $timestamp));
	}

	public function testGetPreviousDayOfWeek(): void
	{
		// 2024-01-15 is a Monday (1)
		// Find previous Friday (5)
		$timestamp = Timestamp::getPreviousDayOfWeek(2024, 1, 15, 5);
		$this->assertEquals(5, (int) date('N', $timestamp)); // 5 = Friday
		$this->assertEquals('2024-01-12', date('Y-m-d', $timestamp));

		// Find same day of week
		$timestamp = Timestamp::getPreviousDayOfWeek(2024, 1, 15, 1);
		$this->assertEquals(1, (int) date('N', $timestamp)); // 1 = Monday
		$this->assertEquals('2024-01-15', date('Y-m-d', $timestamp));
	}

	public function testGetNextDayOfWeekFromTimestamp(): void
	{
		// From January 15, 2024 (Monday), find next Wednesday
		$baseTimestamp = mktime(0, 0, 0, 1, 15, 2024);
		$timestamp = Timestamp::getNextDayOfWeekFromTimestamp($baseTimestamp, 3);
		$this->assertEquals(3, (int) date('N', $timestamp));
		$this->assertEquals('2024-01-17', date('Y-m-d', $timestamp));
	}

	public function testGetPreviousDayOfWeekFromTimestamp(): void
	{
		// From January 15, 2024 (Monday), find previous Friday
		$baseTimestamp = mktime(0, 0, 0, 1, 15, 2024);
		$timestamp = Timestamp::getPreviousDayOfWeekFromTimestamp($baseTimestamp, 5);
		$this->assertEquals(5, (int) date('N', $timestamp));
		$this->assertEquals('2024-01-12', date('Y-m-d', $timestamp));
	}

	// ========== Formatting Methods Tests ==========

	// IntlDateFormatter methods

	public function testFormat(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);

		// Short format

		// fr_FR
		$formatted = Timestamp::format($timestamp, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT, 'fr_FR');
		$this->assertEqualsIgnoringCase('15/01/2024 14:30', $formatted);

		// en_GB
		$formatted = Timestamp::format($timestamp, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT, 'en_GB');
		$this->assertEqualsIgnoringCase('15/01/2024, 14:30', $formatted);

		// en_US
		$formatted = Timestamp::format($timestamp, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT, 'en_US');
		$this->assertEqualsIgnoringCase('1/15/24, 2:30 PM', $formatted);

		// Medium format

		// fr_FR
		$formatted = Timestamp::format($timestamp, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM, 'fr_FR');
		$this->assertEqualsIgnoringCase('15 janv. 2024, 14:30:45', $formatted);

		// en_GB
		$formatted = Timestamp::format($timestamp, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM, 'en_GB');
		$this->assertEqualsIgnoringCase('15 Jan 2024, 14:30:45', $formatted);

		// en_US
		$formatted = Timestamp::format($timestamp, \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM, 'en_US');
		$this->assertEqualsIgnoringCase('Jan 15, 2024, 2:30:45 PM', $formatted);

		// Long format

		// fr_FR
		$formatted = Timestamp::format($timestamp, \IntlDateFormatter::LONG, \IntlDateFormatter::LONG, 'fr_FR');
		$this->assertEqualsIgnoringCase('15 janvier 2024 à 14:30:45 UTC', $formatted);

		// en_GB
		$formatted = Timestamp::format($timestamp, \IntlDateFormatter::LONG, \IntlDateFormatter::LONG, 'en_GB');
		$this->assertEqualsIgnoringCase('15 January 2024 at 14:30:45 UTC', $formatted);

		// en_US
		$formatted = Timestamp::format($timestamp, \IntlDateFormatter::LONG, \IntlDateFormatter::LONG, 'en_US');
		$this->assertEqualsIgnoringCase('January 15, 2024 at 2:30:45 PM UTC', $formatted);

		// Full format

		// fr_FR
		$formatted = Timestamp::format($timestamp, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'fr_FR');
		$this->assertEqualsIgnoringCase('lundi 15 janvier 2024 à 14:30:45 temps universel coordonné', $formatted);

		// en_GB
		$formatted = Timestamp::format($timestamp, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'en_GB');
		$this->assertEqualsIgnoringCase('Monday 15 January 2024 at 14:30:45 Coordinated Universal Time', $formatted);

		// en_US
		$formatted = Timestamp::format($timestamp, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL, 'en_US');
		$this->assertEqualsIgnoringCase('Monday, January 15, 2024 at 2:30:45 PM Coordinated Universal Time', $formatted);
	}

	public function testFormatDateTime(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);

		// Short format

		// fr_FR
		$formatted = Timestamp::formatDateTime($timestamp, 'fr_FR', \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
		$this->assertEqualsIgnoringCase('15/01/2024 14:30', $formatted);

		// en_GB
		$formatted = Timestamp::formatDateTime($timestamp, 'en_GB', \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
		$this->assertEqualsIgnoringCase('15/01/2024, 14:30', $formatted);

		// en_US
		$formatted = Timestamp::formatDateTime($timestamp, 'en_US', \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
		$this->assertEqualsIgnoringCase('1/15/24, 2:30 PM', $formatted);

		// Medium format

		// fr_FR
		$formatted = Timestamp::formatDateTime($timestamp, 'fr_FR', \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM);
		$this->assertEqualsIgnoringCase('15 janv. 2024, 14:30:45', $formatted);

		// en_GB
		$formatted = Timestamp::formatDateTime($timestamp, 'en_GB', \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM);
		$this->assertEqualsIgnoringCase('15 Jan 2024, 14:30:45', $formatted);

		// en_US
		$formatted = Timestamp::formatDateTime($timestamp, 'en_US', \IntlDateFormatter::MEDIUM, \IntlDateFormatter::MEDIUM);
		$this->assertEqualsIgnoringCase('Jan 15, 2024, 2:30:45 PM', $formatted);

		// Long format

		// fr_FR
		$formatted = Timestamp::formatDateTime($timestamp, 'fr_FR', \IntlDateFormatter::LONG, \IntlDateFormatter::LONG);
		$this->assertEqualsIgnoringCase('15 janvier 2024 à 14:30:45 UTC', $formatted);

		// en_GB
		$formatted = Timestamp::formatDateTime($timestamp, 'en_GB', \IntlDateFormatter::LONG, \IntlDateFormatter::LONG);
		$this->assertEqualsIgnoringCase('15 January 2024 at 14:30:45 UTC', $formatted);

		// en_US
		$formatted = Timestamp::formatDateTime($timestamp, 'en_US', \IntlDateFormatter::LONG, \IntlDateFormatter::LONG);
		$this->assertEqualsIgnoringCase('January 15, 2024 at 2:30:45 PM UTC', $formatted);
		// Full format

		// fr_FR
		$formatted = Timestamp::formatDateTime($timestamp, 'fr_FR', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL);
		$this->assertEqualsIgnoringCase('lundi 15 janvier 2024 à 14:30:45 temps universel coordonné', $formatted);

		// en_GB
		$formatted = Timestamp::formatDateTime($timestamp, 'en_GB', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL);
		$this->assertEqualsIgnoringCase('Monday 15 January 2024 at 14:30:45 Coordinated Universal Time', $formatted);

		// en_US
		$formatted = Timestamp::formatDateTime($timestamp, 'en_US', \IntlDateFormatter::FULL, \IntlDateFormatter::FULL);
		$this->assertEqualsIgnoringCase('Monday, January 15, 2024 at 2:30:45 PM Coordinated Universal Time', $formatted);
	}

	// Date Formatting Methods

	public function testFormatDate(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);

		$this->assertEqualsIgnoringCase('15/01/2024', Timestamp::formatDate($timestamp, 'fr_FR'));
		$this->assertEqualsIgnoringCase('15/01/2024', Timestamp::formatDate($timestamp, 'en_GB'));
		$this->assertEqualsIgnoringCase('1/15/24', Timestamp::formatDate($timestamp, 'en_US'));
	}

	public function testFormatDateShort(): void
	{
		$timestamp = mktime(0, 0, 0, 1, 15, 2024);

		$this->assertEqualsIgnoringCase('15/01/2024', Timestamp::formatDateShort($timestamp, 'fr_FR'));
		$this->assertEqualsIgnoringCase('15/01/2024', Timestamp::formatDateShort($timestamp, 'en_GB'));
		$this->assertEqualsIgnoringCase('1/15/24', Timestamp::formatDateShort($timestamp, 'en_US'));
	}

	public function testFormatDateMedium(): void
	{
		$timestamp = mktime(0, 0, 0, 1, 15, 2024);

		$this->assertEqualsIgnoringCase('15 janv. 2024', Timestamp::formatDateMedium($timestamp, 'fr_FR'));
		$this->assertEqualsIgnoringCase('15 Jan 2024', Timestamp::formatDateMedium($timestamp, 'en_GB'));
		$this->assertEqualsIgnoringCase('Jan 15, 2024', Timestamp::formatDateMedium($timestamp, 'en_US'));
	}

	public function testFormatDateLong(): void
	{
		$timestamp = mktime(0, 0, 0, 1, 15, 2024);

		$this->assertEqualsIgnoringCase('15 janvier 2024', Timestamp::formatDateLong($timestamp, 'fr_FR'));
		$this->assertEqualsIgnoringCase('15 January 2024', Timestamp::formatDateLong($timestamp, 'en_GB'));
		$this->assertEqualsIgnoringCase('January 15, 2024', Timestamp::formatDateLong($timestamp, 'en_US'));
	}

	public function testFormatDateFull(): void
	{
		$timestamp = mktime(0, 0, 0, 1, 15, 2024);

		$this->assertEqualsIgnoringCase('lundi 15 janvier 2024', Timestamp::formatDateFull($timestamp, 'fr_FR'));
		$this->assertEqualsIgnoringCase('Monday 15 January 2024', Timestamp::formatDateFull($timestamp, 'en_GB'));
		$this->assertEqualsIgnoringCase('Monday, January 15, 2024', Timestamp::formatDateFull($timestamp, 'en_US'));
	}

	public function testFormatDateISO(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);
		$formatted = Timestamp::formatDateISO($timestamp);
		$this->assertEquals('2024-01-15', $formatted);
	}

	// Time Formatting Methods

	public function testFormatTime(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);

		$this->assertEqualsIgnoringCase('14:30', Timestamp::formatTime($timestamp, 'fr_FR'));
		$this->assertEqualsIgnoringCase('14:30', Timestamp::formatTime($timestamp, 'en_GB'));
		$this->assertEqualsIgnoringCase('2:30 PM', Timestamp::formatTime($timestamp, 'en_US'));
	}

	public function testFormatTimeShort(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);

		$this->assertEqualsIgnoringCase('14:30', Timestamp::formatTimeShort($timestamp, 'fr_FR'));
		$this->assertEqualsIgnoringCase('14:30', Timestamp::formatTimeShort($timestamp, 'en_GB'));
		$this->assertEqualsIgnoringCase('2:30 PM', Timestamp::formatTimeShort($timestamp, 'en_US'));
	}

	public function testFormatTimeMedium(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);

		$this->assertEqualsIgnoringCase('14:30:45', Timestamp::formatTimeMedium($timestamp, 'fr_FR'));
		$this->assertEqualsIgnoringCase('14:30:45', Timestamp::formatTimeMedium($timestamp, 'en_GB'));
		$this->assertEqualsIgnoringCase('2:30:45 PM', Timestamp::formatTimeMedium($timestamp, 'en_US'));
	}

	public function testFormatTimeLong(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);

		$this->assertEqualsIgnoringCase('14:30:45 UTC', Timestamp::formatTimeLong($timestamp, 'fr_FR'));
		$this->assertEqualsIgnoringCase('14:30:45 UTC', Timestamp::formatTimeLong($timestamp, 'en_GB'));
		$this->assertEqualsIgnoringCase('2:30:45 PM UTC', Timestamp::formatTimeLong($timestamp, 'en_US'));
	}

	public function testFormatTimeISO(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);
		$formatted = Timestamp::formatTimeISO($timestamp);
		$this->assertEquals('14:30:45', $formatted);

		$timestamp = mktime(0, 0, 0, 1, 15, 2024);
		$formatted = Timestamp::formatTimeISO($timestamp);
		$this->assertEquals('00:00:00', $formatted);

		$timestamp = mktime(23, 59, 59, 1, 15, 2024);
		$formatted = Timestamp::formatTimeISO($timestamp);
		$this->assertEquals('23:59:59', $formatted);
	}

	// ========== DEPRECATED Methods Tests ==========

	public function testGetByYearMonthDay(): void
	{
		// Test deprecated method still works
		$timestamp = Timestamp::getByYearMonthDay(2024, 1, 15);
		$this->assertEquals('2024-01-15', date('Y-m-d', $timestamp));
		$this->assertEquals('00:00:00', date('H:i:s', $timestamp));
	}

	public function testGetTimestampNextDayOfWeekByYearMonthDay(): void
	{
		// Test deprecated method still works (parameter order is different)
		$timestamp = Timestamp::getTimestampNextDayOfWeekByYearMonthDay(3, 2024, 1, 15);
		$this->assertEquals(3, (int) date('N', $timestamp));
		$this->assertEquals('2024-01-17', date('Y-m-d', $timestamp));
	}

	public function testGetTimestampPreviousDayOfWeekByYearMonthDay(): void
	{
		// Test deprecated method still works (parameter order is different)
		$timestamp = Timestamp::getTimestampPreviousDayOfWeekByYearMonthDay(5, 2024, 1, 15);
		$this->assertEquals(5, (int) date('N', $timestamp));
		$this->assertEquals('2024-01-12', date('Y-m-d', $timestamp));
	}

	public function testGetNextDayOfWeekOfWeek(): void
	{
		// Test deprecated method still works (parameter order is different)
		$baseTimestamp = mktime(0, 0, 0, 1, 15, 2024);
		$timestamp = Timestamp::getNextDayOfWeekOfWeek(3, $baseTimestamp);
		$this->assertEquals(3, (int) date('N', $timestamp));
		$this->assertEquals('2024-01-17', date('Y-m-d', $timestamp));
	}

	public function testGetPreviousDayOfWeekOfWeek(): void
	{
		// Test deprecated method still works (parameter order is different)
		$baseTimestamp = mktime(0, 0, 0, 1, 15, 2024);
		$timestamp = Timestamp::getPreviousDayOfWeekOfWeek(5, $baseTimestamp);
		$this->assertEquals(5, (int) date('N', $timestamp));
		$this->assertEquals('2024-01-12', date('Y-m-d', $timestamp));
	}
}