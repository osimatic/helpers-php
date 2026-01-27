<?php

namespace Tests\Calendar;

use Osimatic\Calendar\Time;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Time class - methods that do NOT take DateTime objects as parameters
 */
final class TimeTest extends TestCase
{
	// ========== Creation Methods Tests ==========

	public function testCreate(): void
	{
		// Valid time
		$result = Time::create(14, 30, 45);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('14:30:45', $result->format('H:i:s'));

		// Valid time with default seconds
		$result = Time::create(10, 15);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('10:15:00', $result->format('H:i:s'));

		// Invalid time (hour 25)
		$this->assertNull(Time::create(25, 0, 0));

		// Invalid time (minute 60)
		$this->assertNull(Time::create(12, 60, 0));

		// Invalid time (second 60)
		$this->assertNull(Time::create(12, 30, 60));
	}

	public function testCreateFromComponents(): void
	{
		// Alias for create()
		$result = Time::createFromComponents(14, 30, 45);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('14:30:45', $result->format('H:i:s'));

		// Invalid time
		$this->assertNull(Time::createFromComponents(24, 0, 0));
	}

	// ========== Parsing Methods Tests ==========

	public function testParse(): void
	{
		// Parse valid time
		$result = Time::parse('14:30:45');
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('14:30:45', $result->format('H:i:s'));

		// Parse with custom separator
		$result = Time::parse('14h30m45', 'h', 1, 2, 3);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('14:30:45', $result->format('H:i:s'));

		// Parse invalid
		$this->assertNull(Time::parse('invalid'));
		$this->assertNull(Time::parse(null));
		$this->assertNull(Time::parse(''));
	}

	public function testParseToSqlTime(): void
	{
		// Parse standard format
		$this->assertEquals('14:30:45', Time::parseToSqlTime('14:30:45'));
		$this->assertEquals('00:00:00', Time::parseToSqlTime('00:00:00'));
		$this->assertEquals('23:59:59', Time::parseToSqlTime('23:59:59'));

		// Parse with only hours and minutes
		$result = Time::parseToSqlTime('14:30');
		$this->assertIsString($result);
		$this->assertStringStartsWith('14:30', $result);

		// Custom separator
		$this->assertEquals('14:30:00', Time::parseToSqlTime('14h30m00', 'h', 1, 2, 3));

		// Invalid values
		$this->assertNull(Time::parseToSqlTime('invalid'));
		$this->assertNull(Time::parseToSqlTime(null));
		$this->assertNull(Time::parseToSqlTime(''));
	}

	// ========== Validation Methods Tests ==========

	public function testCheck(): void
	{
		// Valid times
		$this->assertTrue(Time::check(0, 0, 0));
		$this->assertTrue(Time::check(12, 30, 45));
		$this->assertTrue(Time::check(23, 59, 59));
		$this->assertTrue(Time::check(14, 30)); // Default seconds = 0

		// Invalid times - hour out of range
		$this->assertFalse(Time::check(24, 0, 0));
		$this->assertFalse(Time::check(-1, 0, 0));

		// Invalid times - minute out of range
		$this->assertFalse(Time::check(12, 60, 0));
		$this->assertFalse(Time::check(12, -1, 0));

		// Invalid times - second out of range
		$this->assertFalse(Time::check(12, 30, 60));
		$this->assertFalse(Time::check(12, 30, -1));
	}

	public function testCheckValue(): void
	{
		// Valid values
		$this->assertTrue(Time::checkValue('14:30:45'));
		$this->assertTrue(Time::checkValue('00:00:00'));
		$this->assertTrue(Time::checkValue('23:59:59'));
		$this->assertTrue(Time::checkValue('14h30', 'h'));

		// Invalid values - time component out of range
		$this->assertFalse(Time::checkValue('24:00:00'));
		$this->assertFalse(Time::checkValue('12:60:00'));
		$this->assertFalse(Time::checkValue('12:30:60'));

		// Invalid values - format
		$this->assertFalse(Time::checkValue('invalid'));
		$this->assertFalse(Time::checkValue(null));
		$this->assertFalse(Time::checkValue(''));
	}

	// ========== Formatting Methods Tests ==========

	public function testFormatHour(): void
	{
		$this->assertEquals('00h', Time::formatHour(0));
		$this->assertEquals('09h', Time::formatHour(9));
		$this->assertEquals('14h', Time::formatHour(14));
		$this->assertEquals('23h', Time::formatHour(23));
	}

	public function testFormatDuration(): void
	{
		// Test with short format
		$this->assertEquals('00:00:00', Time::formatDuration(0, true));
		$this->assertEquals('00:01:30', Time::formatDuration(90, true));
		$this->assertEquals('02:30:45', Time::formatDuration(9045, true)); // 2h 30m 45s

		// Test with long format (default)
		$this->assertEquals('0s', Time::formatDuration(0));
		$this->assertEquals('1m 30s', Time::formatDuration(90));
		$this->assertEquals('2h 30m 45s', Time::formatDuration(9045));

		// Test hours only
		$this->assertEquals('2h', Time::formatDuration(7200)); // 2 hours

		// Test minutes only
		$this->assertEquals('30m', Time::formatDuration(1800)); // 30 minutes

		// Test complex duration
		$this->assertEquals('1h 15m', Time::formatDuration(4500)); // 1h 15m
	}
}