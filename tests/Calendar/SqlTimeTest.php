<?php

namespace Tests\Calendar;

use Osimatic\Calendar\SqlTime;
use PHPUnit\Framework\TestCase;

final class SqlTimeTest extends TestCase
{
	// ========== Parsing & Validation Methods Tests ==========

	public function testParse(): void
	{
		// Format complet
		$this->assertEquals('14:30:45', SqlTime::parse('14:30:45'));

		// Format sans secondes
		$this->assertEquals('14:30:00', SqlTime::parse('14:30'));

		// Format depuis array
		$this->assertEquals('14:30:45', SqlTime::parse(['date' => '2024-01-15 14:30:45']));

		// Invalid time
		$this->assertNull(SqlTime::parse('invalid-time'));
		$this->assertNull(SqlTime::parse(''));
	}

	public function testIsValid(): void
	{
		// Heures valides
		$this->assertTrue(SqlTime::isValid('00:00:00'));
		$this->assertTrue(SqlTime::isValid('12:30:45'));
		$this->assertTrue(SqlTime::isValid('23:59:59'));

		// Heures invalides
		$this->assertFalse(SqlTime::isValid('24:00:00')); // Heure 24
		$this->assertFalse(SqlTime::isValid('12:60:00')); // Minute 60
		$this->assertFalse(SqlTime::isValid('12:30:60')); // Second 60
		$this->assertFalse(SqlTime::isValid('-1:00:00')); // Heure négative
		$this->assertFalse(SqlTime::isValid('12:-1:00')); // Minute négative
		$this->assertFalse(SqlTime::isValid('invalid'));
		$this->assertFalse(SqlTime::isValid(null));
	}

	public function testIsValidMethod(): void
	{
		// Heures valides
		$this->assertTrue(SqlTime::isValid('00:00:00'));
		$this->assertTrue(SqlTime::isValid('12:30:45'));
		$this->assertTrue(SqlTime::isValid('23:59:59'));
		$this->assertTrue(SqlTime::isValid('14:30'));

		// Heures invalides
		$this->assertFalse(SqlTime::isValid('24:00:00')); // Heure 24
		$this->assertFalse(SqlTime::isValid('12:60:00')); // Minute 60
		$this->assertFalse(SqlTime::isValid('-1:00:00')); // Heure négative
		$this->assertFalse(SqlTime::isValid('12:-1:00')); // Minute négative
		$this->assertFalse(SqlTime::isValid('invalid'));
		$this->assertFalse(SqlTime::isValid(null));
	}

	// ========== Extraction Methods Tests ==========

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
		// Utiliser une heure fixe en milieu de journée (14:00)
		$currentHour = (int) date('H');

		// Test avec une heure dans le futur seulement si on est avant 14h
		if ($currentHour < 14) {
			$futureTimestamp = strtotime(date('Y-m-d') . ' 14:00:00');
			$futureTime = '14:00:00';
			$expectedSeconds = $futureTimestamp - time();

			$seconds = SqlTime::getNbSecondsFromNow($futureTime);
			$this->assertGreaterThan($expectedSeconds - 100, $seconds);
			$this->assertLessThan($expectedSeconds + 100, $seconds);
		}

		// Test avec une heure dans le passé seulement si on est après 10h
		if ($currentHour > 10) {
			$pastTimestamp = strtotime(date('Y-m-d') . ' 10:00:00');
			$pastTime = '10:00:00';
			$expectedSeconds = $pastTimestamp - time();

			$seconds = SqlTime::getNbSecondsFromNow($pastTime);
			$this->assertLessThan($expectedSeconds + 100, $seconds);
			$this->assertGreaterThan($expectedSeconds - 100, $seconds);
		}

		// S'assurer qu'au moins un test a été exécuté
		$this->assertTrue($currentHour < 14 || $currentHour > 10);
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
		$currentHour = (int) date('H');

		// Test avec une heure dans le passé seulement si on est après 10h
		if ($currentHour > 10) {
			$pastTime = '10:00:00';
			$this->assertTrue(SqlTime::isBeforeNow($pastTime));
		}

		// Test avec une heure dans le futur seulement si on est avant 14h
		if ($currentHour < 14) {
			$futureTime = '14:00:00';
			$this->assertFalse(SqlTime::isBeforeNow($futureTime));
		}

		// S'assurer qu'au moins un test a été exécuté
		$this->assertTrue($currentHour < 14 || $currentHour > 10);
	}

	public function testIsAfterNow(): void
	{
		$currentHour = (int) date('H');

		// Test avec une heure dans le futur seulement si on est avant 14h
		if ($currentHour < 14) {
			$futureTime = '14:00:00';
			$this->assertTrue(SqlTime::isAfterNow($futureTime));
		}

		// Test avec une heure dans le passé seulement si on est après 10h
		if ($currentHour > 10) {
			$pastTime = '10:00:00';
			$this->assertFalse(SqlTime::isAfterNow($pastTime));
		}

		// S'assurer qu'au moins un test a été exécuté
		$this->assertTrue($currentHour < 14 || $currentHour > 10);
	}

	// ========== Creation Methods Tests ==========

	public function testCreate(): void
	{
		// Avec secondes
		$this->assertEquals('14:30:45', SqlTime::create(14, 30, 45));

		// Sans secondes (valeur par défaut 0)
		$this->assertEquals('14:30:00', SqlTime::create(14, 30));

		// Minuit
		$this->assertEquals('00:00:00', SqlTime::create(0, 0, 0));

		// 23:59:59
		$this->assertEquals('23:59:59', SqlTime::create(23, 59, 59));

		// Formatage avec zéros à gauche
		$this->assertEquals('01:05:09', SqlTime::create(1, 5, 9));
	}

	public function testNow(): void
	{
		$now = SqlTime::now();
		$this->assertTrue(SqlTime::isValid($now));
		$this->assertMatchesRegularExpression('/\d{2}:\d{2}:\d{2}/', $now);

		// Verify it's close to current time
		$currentTime = date('H:i:s');
		$this->assertEqualsWithDelta(
			strtotime('1970-01-01 ' . $currentTime),
			strtotime('1970-01-01 ' . $now),
			2 // within 2 seconds
		);
	}

	// ========== Conversion Methods Tests ==========

	public function testToDateTime(): void
	{
		$dateTime = SqlTime::toDateTime('14:30:45');
		$this->assertInstanceOf(\DateTime::class, $dateTime);
		$this->assertEquals('14:30:45', $dateTime->format('H:i:s'));
		$this->assertEquals(date('Y-m-d'), $dateTime->format('Y-m-d')); // Today's date
	}

	public function testToSeconds(): void
	{
		// Midnight
		$this->assertEquals(0, SqlTime::toSeconds('00:00:00'));

		// 1 hour
		$this->assertEquals(3600, SqlTime::toSeconds('01:00:00'));

		// 14:30:45 = 14*3600 + 30*60 + 45 = 50400 + 1800 + 45 = 52245
		$this->assertEquals(52245, SqlTime::toSeconds('14:30:45'));

		// End of day
		$this->assertEquals(86399, SqlTime::toSeconds('23:59:59'));
	}

	public function testFromSeconds(): void
	{
		// Midnight
		$this->assertEquals('00:00:00', SqlTime::fromSeconds(0));

		// 1 hour
		$this->assertEquals('01:00:00', SqlTime::fromSeconds(3600));

		// 14:30:45
		$this->assertEquals('14:30:45', SqlTime::fromSeconds(52245));

		// End of day
		$this->assertEquals('23:59:59', SqlTime::fromSeconds(86399));

		// 90 seconds = 1 minute 30 seconds
		$this->assertEquals('00:01:30', SqlTime::fromSeconds(90));
	}

	// ========== Calculation Methods Tests ==========

	public function testAddSeconds(): void
	{
		// Add 30 seconds
		$this->assertEquals('14:30:30', SqlTime::addSeconds('14:30:00', 30));

		// Add 90 seconds (1 minute 30 seconds)
		$this->assertEquals('14:31:30', SqlTime::addSeconds('14:30:00', 90));

		// Add 0 seconds
		$this->assertEquals('14:30:00', SqlTime::addSeconds('14:30:00', 0));

		// Wrap around midnight
		$this->assertEquals('00:00:30', SqlTime::addSeconds('23:59:45', 45));

		// Add negative seconds (same as subtract)
		$this->assertEquals('14:29:30', SqlTime::addSeconds('14:30:00', -30));
	}

	public function testSubSeconds(): void
	{
		// Subtract 30 seconds
		$this->assertEquals('14:29:30', SqlTime::subSeconds('14:30:00', 30));

		// Subtract 90 seconds
		$this->assertEquals('14:28:30', SqlTime::subSeconds('14:30:00', 90));

		// Subtract 0 seconds
		$this->assertEquals('14:30:00', SqlTime::subSeconds('14:30:00', 0));

		// Wrap around midnight (backwards)
		$this->assertEquals('23:59:30', SqlTime::subSeconds('00:00:00', 30));
	}

	public function testAddMinutes(): void
	{
		// Add 30 minutes
		$this->assertEquals('15:00:00', SqlTime::addMinutes('14:30:00', 30));

		// Add 90 minutes (1.5 hours)
		$this->assertEquals('16:00:00', SqlTime::addMinutes('14:30:00', 90));

		// Add 0 minutes
		$this->assertEquals('14:30:00', SqlTime::addMinutes('14:30:00', 0));

		// Wrap around midnight
		$this->assertEquals('00:30:00', SqlTime::addMinutes('23:45:00', 45));
	}

	public function testSubMinutes(): void
	{
		// Subtract 30 minutes
		$this->assertEquals('14:00:00', SqlTime::subMinutes('14:30:00', 30));

		// Subtract 90 minutes
		$this->assertEquals('13:00:00', SqlTime::subMinutes('14:30:00', 90));

		// Subtract 0 minutes
		$this->assertEquals('14:30:00', SqlTime::subMinutes('14:30:00', 0));

		// Wrap around midnight (backwards)
		$this->assertEquals('23:30:00', SqlTime::subMinutes('00:15:00', 45));
	}

	public function testAddHours(): void
	{
		// Add 2 hours
		$this->assertEquals('16:30:00', SqlTime::addHours('14:30:00', 2));

		// Add 10 hours
		$this->assertEquals('00:30:00', SqlTime::addHours('14:30:00', 10));

		// Add 0 hours
		$this->assertEquals('14:30:00', SqlTime::addHours('14:30:00', 0));

		// Wrap around midnight
		$this->assertEquals('01:00:00', SqlTime::addHours('22:00:00', 3));
	}

	public function testSubHours(): void
	{
		// Subtract 2 hours
		$this->assertEquals('12:30:00', SqlTime::subHours('14:30:00', 2));

		// Subtract 15 hours
		$this->assertEquals('23:30:00', SqlTime::subHours('14:30:00', 15));

		// Subtract 0 hours
		$this->assertEquals('14:30:00', SqlTime::subHours('14:30:00', 0));

		// Wrap around midnight (backwards)
		$this->assertEquals('21:00:00', SqlTime::subHours('02:00:00', 5));
	}

	// ========== Comparison Methods Tests ==========

	public function testGetSecondsBetween(): void
	{
		// 1 hour difference (3600 seconds)
		$this->assertEquals(3600, SqlTime::getSecondsBetween('15:00:00', '14:00:00'));

		// 30 minutes difference (1800 seconds)
		$this->assertEquals(1800, SqlTime::getSecondsBetween('14:30:00', '14:00:00'));

		// Same time
		$this->assertEquals(0, SqlTime::getSecondsBetween('14:00:00', '14:00:00'));

		// Negative difference
		$this->assertEquals(-3600, SqlTime::getSecondsBetween('14:00:00', '15:00:00'));

		// Large difference (10 hours)
		$this->assertEquals(36000, SqlTime::getSecondsBetween('20:00:00', '10:00:00'));
	}

	public function testGetSecondsFromNow(): void
	{
		$currentHour = (int) date('H');

		// Test avec une heure dans le futur seulement si on est avant 14h
		if ($currentHour < 14) {
			$futureTimestamp = strtotime(date('Y-m-d') . ' 14:00:00');
			$futureTime = '14:00:00';
			$expectedSeconds = $futureTimestamp - time();

			$seconds = SqlTime::getSecondsFromNow($futureTime);
			$this->assertGreaterThan($expectedSeconds - 100, $seconds);
			$this->assertLessThan($expectedSeconds + 100, $seconds);
		}

		// Test avec une heure dans le passé seulement si on est après 10h
		if ($currentHour > 10) {
			$pastTimestamp = strtotime(date('Y-m-d') . ' 10:00:00');
			$pastTime = '10:00:00';
			$expectedSeconds = $pastTimestamp - time();

			$seconds = SqlTime::getSecondsFromNow($pastTime);
			$this->assertLessThan($expectedSeconds + 100, $seconds);
			$this->assertGreaterThan($expectedSeconds - 100, $seconds);
		}

		// S'assurer qu'au moins un test a été exécuté
		$this->assertTrue($currentHour < 14 || $currentHour > 10);
	}

	public function testIsBefore(): void
	{
		// 14:00 is before 15:00
		$this->assertTrue(SqlTime::isBefore('14:00:00', '15:00:00'));

		// 15:00 is not before 14:00
		$this->assertFalse(SqlTime::isBefore('15:00:00', '14:00:00'));

		// Same time is not before
		$this->assertFalse(SqlTime::isBefore('14:00:00', '14:00:00'));

		// 14:30 is before 14:31
		$this->assertTrue(SqlTime::isBefore('14:30:00', '14:31:00'));
	}

	public function testIsAfter(): void
	{
		// 15:00 is after 14:00
		$this->assertTrue(SqlTime::isAfter('15:00:00', '14:00:00'));

		// 14:00 is not after 15:00
		$this->assertFalse(SqlTime::isAfter('14:00:00', '15:00:00'));

		// Same time is not after
		$this->assertFalse(SqlTime::isAfter('14:00:00', '14:00:00'));

		// 14:31 is after 14:30
		$this->assertTrue(SqlTime::isAfter('14:31:00', '14:30:00'));
	}

	public function testIsEqual(): void
	{
		// Same time
		$this->assertTrue(SqlTime::isEqual('14:30:00', '14:30:00'));

		// Different times
		$this->assertFalse(SqlTime::isEqual('14:30:00', '14:31:00'));
		$this->assertFalse(SqlTime::isEqual('14:30:00', '15:30:00'));
		$this->assertFalse(SqlTime::isEqual('14:30:00', '14:30:01'));
	}

	// ========== Formatting Methods Tests ==========

	public function testFormat(): void
	{
		// Test with specific locale for predictable results
		// Note: IntlDateFormatter uses U+202F (NARROW NO-BREAK SPACE) before AM/PM
		$formatted = SqlTime::format('14:30:45', 'en_US', \IntlDateFormatter::SHORT);
		$this->assertEquals("2:30\u{202F}PM", $formatted);

		$formatted = SqlTime::format('14:30:45', 'en_US', \IntlDateFormatter::MEDIUM);
		$this->assertEquals("2:30:45\u{202F}PM", $formatted);

		$formatted = SqlTime::format('23:59:59', 'en_US', \IntlDateFormatter::SHORT);
		$this->assertEquals("11:59\u{202F}PM", $formatted);

		$formatted = SqlTime::format('00:00:00', 'en_US', \IntlDateFormatter::SHORT);
		$this->assertEquals("12:00\u{202F}AM", $formatted);
	}

	public function testFormatString(): void
	{
		// formatString just returns the input (SQL TIME is already HH:MM:SS)
		$this->assertEquals('14:30:45', SqlTime::formatString('14:30:45'));
		$this->assertEquals('00:00:00', SqlTime::formatString('00:00:00'));
		$this->assertEquals('23:59:59', SqlTime::formatString('23:59:59'));
	}

	public function testFormatShort(): void
	{
		$formatted = SqlTime::formatShort('14:30:45');
		// Should be in HH:MM format
		$this->assertMatchesRegularExpression('/\d{1,2}:\d{2}/', $formatted);
	}

	public function testFormatLong(): void
	{
		// With seconds (default)
		$formatted = SqlTime::formatLong('14:30:45', true);
		$this->assertNotEmpty($formatted);
		$this->assertIsString($formatted);

		// Without seconds
		$formatted = SqlTime::formatLong('14:30:45', false);
		$this->assertNotEmpty($formatted);
		$this->assertIsString($formatted);
	}

	public function testFormatISO(): void
	{
		// SQL TIME is already ISO 8601, so should return as-is
		$this->assertEquals('14:30:45', SqlTime::formatISO('14:30:45'));
		$this->assertEquals('00:00:00', SqlTime::formatISO('00:00:00'));
		$this->assertEquals('23:59:59', SqlTime::formatISO('23:59:59'));
	}
}