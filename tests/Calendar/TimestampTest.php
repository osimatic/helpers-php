<?php

namespace Tests\Calendar;

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
		$formatted = Timestamp::format($timestamp, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT, 'en_US');
		$this->assertIsString($formatted);
		$this->assertNotEmpty($formatted);
	}

	public function testFormatDateTime(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);
		$formatted = Timestamp::formatDateTime($timestamp, 'en_US');
		$this->assertIsString($formatted);
		$this->assertNotEmpty($formatted);
	}

	public function testFormatDate(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);
		$formatted = Timestamp::formatDate($timestamp, 'en_US');
		$this->assertIsString($formatted);
		$this->assertNotEmpty($formatted);
	}

	public function testFormatDateInLong(): void
	{
		$timestamp = mktime(0, 0, 0, 1, 15, 2024);
		$formatted = Timestamp::formatDateInLong($timestamp, 'en_US');
		$this->assertIsString($formatted);
		$this->assertNotEmpty($formatted);
	}

	public function testFormatTime(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);
		$formatted = Timestamp::formatTime($timestamp, 'en_US');
		$this->assertIsString($formatted);
		$this->assertNotEmpty($formatted);
	}

	// Date Formatting Methods

	public function testFormatDateShort(): void
	{
		$timestamp = mktime(0, 0, 0, 1, 15, 2024);

		// Default format (EU): DD/MM/YYYY
		$formatted = Timestamp::formatDateShort($timestamp);
		$this->assertEquals('15/01/2024', $formatted);

		// US format: MM/DD/YYYY
		$formatted = Timestamp::formatDateShort($timestamp, '/', 'US');
		$this->assertEquals('01/15/2024', $formatted);

		// With different separator
		$formatted = Timestamp::formatDateShort($timestamp, '-');
		$this->assertEquals('15-01-2024', $formatted);
	}

	public function testFormatDateMedium(): void
	{
		$timestamp = mktime(0, 0, 0, 1, 15, 2024);
		$formatted = Timestamp::formatDateMedium($timestamp, 'en_US');
		$this->assertIsString($formatted);
		$this->assertNotEmpty($formatted);
	}

	public function testFormatDateLong(): void
	{
		$timestamp = mktime(0, 0, 0, 1, 15, 2024);
		$formatted = Timestamp::formatDateLong($timestamp, 'en_US');
		$this->assertIsString($formatted);
		$this->assertNotEmpty($formatted);
	}

	public function testFormatDateISO(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);
		$formatted = Timestamp::formatDateISO($timestamp);
		$this->assertEquals('2024-01-15', $formatted);
	}

	// Time Formatting Methods

	public function testFormatTimeString(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);
		$formatted = Timestamp::formatTimeString($timestamp);
		$this->assertEquals('14:30:45', $formatted);
	}

	public function testFormatTimeShort(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);
		$formatted = Timestamp::formatTimeShort($timestamp);
		$this->assertEquals('14:30', $formatted);
	}

	public function testFormatTimeLong(): void
	{
		$timestamp = mktime(14, 30, 45, 1, 15, 2024);

		// With seconds (default)
		$formatted = Timestamp::formatTimeLong($timestamp);
		$this->assertEquals('14:30:45', $formatted);

		// Without seconds
		$formatted = Timestamp::formatTimeLong($timestamp, false);
		$this->assertEquals('14:30', $formatted);
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