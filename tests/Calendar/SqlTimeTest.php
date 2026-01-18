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

	public function testGet(): void
	{
		// Avec secondes
		$this->assertEquals('14:30:45', SqlTime::get(14, 30, 45));

		// Sans secondes (valeur par défaut 0)
		$this->assertEquals('14:30:00', SqlTime::get(14, 30));

		// Minuit
		$this->assertEquals('00:00:00', SqlTime::get(0, 0, 0));

		// 23:59:59
		$this->assertEquals('23:59:59', SqlTime::get(23, 59, 59));

		// Formatage avec zéros à gauche
		$this->assertEquals('01:05:09', SqlTime::get(1, 5, 9));
	}

	public function testGetNbSecondsFromTime(): void
	{
		// 1 heure de différence (3600 secondes)
		$this->assertEquals(3600, SqlTime::getNbSecondsFromTime('15:00:00', '14:00:00'));

		// 30 minutes de différence (1800 secondes)
		$this->assertEquals(1800, SqlTime::getNbSecondsFromTime('14:30:00', '14:00:00'));

		// Même heure
		$this->assertEquals(0, SqlTime::getNbSecondsFromTime('14:00:00', '14:00:00'));

		// Différence négative
		$this->assertEquals(-3600, SqlTime::getNbSecondsFromTime('14:00:00', '15:00:00'));

		// Grande différence (10 heures)
		$this->assertEquals(36000, SqlTime::getNbSecondsFromTime('20:00:00', '10:00:00'));
	}

	public function testGetNbSecondsFromNow(): void
	{
		// Utiliser une heure dans le futur
		$futureTime = date('H:i:s', time() + 3600);
		$seconds = SqlTime::getNbSecondsFromNow($futureTime);
		$this->assertGreaterThan(3500, $seconds); // ~3600 seconds
		$this->assertLessThan(3700, $seconds);

		// Utiliser une heure dans le passé
		$pastTime = date('H:i:s', time() - 3600);
		$seconds = SqlTime::getNbSecondsFromNow($pastTime);
		$this->assertLessThan(-3500, $seconds);
		$this->assertGreaterThan(-3700, $seconds);
	}

	public function testIsBeforeTime(): void
	{
		// 14:00 est avant 15:00
		$this->assertTrue(SqlTime::isBeforeTime('14:00:00', '15:00:00'));

		// 15:00 n'est pas avant 14:00
		$this->assertFalse(SqlTime::isBeforeTime('15:00:00', '14:00:00'));

		// Même heure n'est pas avant
		$this->assertFalse(SqlTime::isBeforeTime('14:00:00', '14:00:00'));

		// 14:30 est avant 14:31
		$this->assertTrue(SqlTime::isBeforeTime('14:30:00', '14:31:00'));
	}

	public function testIsAfterTime(): void
	{
		// 15:00 est après 14:00
		$this->assertTrue(SqlTime::isAfterTime('15:00:00', '14:00:00'));

		// 14:00 n'est pas après 15:00
		$this->assertFalse(SqlTime::isAfterTime('14:00:00', '15:00:00'));

		// Même heure n'est pas après
		$this->assertFalse(SqlTime::isAfterTime('14:00:00', '14:00:00'));

		// 14:31 est après 14:30
		$this->assertTrue(SqlTime::isAfterTime('14:31:00', '14:30:00'));
	}

	public function testIsBeforeNow(): void
	{
		// Une heure dans le passé
		$pastTime = date('H:i:s', time() - 3600);
		$this->assertTrue(SqlTime::isBeforeNow($pastTime));

		// Une heure dans le futur
		$futureTime = date('H:i:s', time() + 3600);
		$this->assertFalse(SqlTime::isBeforeNow($futureTime));
	}

	public function testIsAfterNow(): void
	{
		// Une heure dans le futur
		$futureTime = date('H:i:s', time() + 3600);
		$this->assertTrue(SqlTime::isAfterNow($futureTime));

		// Une heure dans le passé
		$pastTime = date('H:i:s', time() - 3600);
		$this->assertFalse(SqlTime::isAfterNow($pastTime));
	}
}