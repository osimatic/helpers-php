<?php

namespace Tests\Calendar;

use Osimatic\Calendar\Time;
use PHPUnit\Framework\TestCase;

final class TimeTest extends TestCase
{
	public function testFormatHour(): void
	{
		$this->assertEquals('00h', Time::formatHour(0));
		$this->assertEquals('09h', Time::formatHour(9));
		$this->assertEquals('14h', Time::formatHour(14));
		$this->assertEquals('23h', Time::formatHour(23));
	}

	public function testCheck(): void
	{
		// Heures valides
		$this->assertTrue(Time::check(0, 0, 0));
		$this->assertTrue(Time::check(12, 30, 45));
		$this->assertTrue(Time::check(23, 59, 59));
		$this->assertTrue(Time::check(14, 30));

		// Heures invalides
		$this->assertFalse(Time::check(24, 0, 0)); // Heure 24
		$this->assertFalse(Time::check(12, 60, 0)); // Minute 60
		$this->assertFalse(Time::check(12, 30, 60)); // Seconde 60
		$this->assertFalse(Time::check(-1, 0, 0)); // Heure négative
		$this->assertFalse(Time::check(12, -1, 0)); // Minute négative
		$this->assertFalse(Time::check(12, 30, -1)); // Seconde négative
	}

	public function testCheckValue(): void
	{
		// Valeurs valides
		$this->assertTrue(Time::checkValue('14:30:45'));
		$this->assertTrue(Time::checkValue('00:00:00'));
		$this->assertTrue(Time::checkValue('23:59:59'));
		$this->assertTrue(Time::checkValue('14h30', 'h'));

		// Valeurs invalides
		$this->assertFalse(Time::checkValue('24:00:00'));
		$this->assertFalse(Time::checkValue('12:60:00'));
		$this->assertFalse(Time::checkValue('invalid'));
		$this->assertFalse(Time::checkValue(null));
		$this->assertFalse(Time::checkValue(''));
	}

	public function testParseToSqlTime(): void
	{
		// Parse standard
		$this->assertEquals('14:30:45', Time::parseToSqlTime('14:30:45'));
		$this->assertEquals('00:00:00', Time::parseToSqlTime('00:00:00'));
		$this->assertEquals('23:59:59', Time::parseToSqlTime('23:59:59'));

		// Séparateur personnalisé
		$this->assertEquals('14:30:00', Time::parseToSqlTime('14h30m00', 'h', 1, 2, 3));

		// Valeur invalide
		$this->assertNull(Time::parseToSqlTime('invalid'));
		$this->assertNull(Time::parseToSqlTime(null));
	}

	public function testParse(): void
	{
		// Parse valide
		$result = Time::parse('14:30:45');
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('14:30:45', $result->format('H:i:s'));

		// Parse invalide
		$this->assertNull(Time::parse('invalid'));
		$this->assertNull(Time::parse(null));
	}
}