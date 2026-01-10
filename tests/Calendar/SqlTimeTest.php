<?php

namespace Tests\Calendar;

use Osimatic\Calendar\SqlTime;
use PHPUnit\Framework\TestCase;

final class SqlTimeTest extends TestCase
{
	public function testParse(): void
	{
		// Format complet
		$this->assertEquals('14:30:45', SqlTime::parse('14:30:45'));

		// Format sans secondes
		$this->assertEquals('14:30:00', SqlTime::parse('14:30'));

		// Format depuis array
		$this->assertEquals('14:30:45', SqlTime::parse(['date' => '2024-01-15 14:30:45']));
	}

	public function testCheck(): void
	{
		// Heures valides
		$this->assertTrue(SqlTime::check('00:00:00'));
		$this->assertTrue(SqlTime::check('12:30:45'));
		$this->assertTrue(SqlTime::check('23:59:59'));
		$this->assertTrue(SqlTime::check('14:30'));

		// Heures invalides
		$this->assertFalse(SqlTime::check('24:00:00')); // Heure 24
		$this->assertFalse(SqlTime::check('12:60:00')); // Minute 60
		$this->assertFalse(SqlTime::check('-1:00:00')); // Heure négative
		$this->assertFalse(SqlTime::check('12:-1:00')); // Minute négative
		$this->assertFalse(SqlTime::check('invalid'));
		$this->assertFalse(SqlTime::check(null));
	}

	public function testGetHour(): void
	{
		$this->assertEquals(0, SqlTime::getHour('00:00:00'));
		$this->assertEquals(14, SqlTime::getHour('14:30:45'));
		$this->assertEquals(23, SqlTime::getHour('23:59:59'));
	}

	public function testGetMinute(): void
	{
		$this->assertEquals(0, SqlTime::getMinute('14:00:00'));
		$this->assertEquals(30, SqlTime::getMinute('14:30:45'));
		$this->assertEquals(59, SqlTime::getMinute('14:59:45'));
	}

	public function testGetSecond(): void
	{
		$this->assertEquals(0, SqlTime::getSecond('14:30:00'));
		$this->assertEquals(45, SqlTime::getSecond('14:30:45'));
		$this->assertEquals(59, SqlTime::getSecond('14:30:59'));
	}
}