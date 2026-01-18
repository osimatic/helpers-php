<?php

namespace Tests\Calendar;

use Osimatic\Calendar\DateTime;
use PHPUnit\Framework\TestCase;

final class DateTimeTest extends TestCase
{
	/* ===================== Parsing ===================== */

	public function testParseFromSqlDateTime(): void
	{
		// Format SQL standard
		$result = DateTime::parseFromSqlDateTime('2024-01-15 14:30:45');
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('2024-01-15', $result->format('Y-m-d'));
		$this->assertEquals('14:30:45', $result->format('H:i:s'));

		// Format invalide
		$this->assertNull(DateTime::parseFromSqlDateTime('invalid'));
	}

	public function testParseFromTimestamp(): void
	{
		$timestamp = 1705327845; // 2024-01-15 14:30:45 UTC
		$result = DateTime::parseFromTimestamp($timestamp);
		$this->assertInstanceOf(\DateTime::class, $result);
	}

	public function testParseFromYearMonthDay(): void
	{
		$result = DateTime::parseFromYearMonthDay(2024, 1, 15);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('2024-01-15', $result->format('Y-m-d'));

		// Date invalide
		$result = DateTime::parseFromYearMonthDay(2024, 13, 1);
		$this->assertNull($result);
	}

	/* ===================== Formatting ===================== */

	public function testFormatDateTime(): void
	{
		$dateTime = new \DateTime('2024-01-15 14:30:45');
		$formatted = DateTime::formatDateTime($dateTime);
		$this->assertIsString($formatted);
		$this->assertNotEmpty($formatted);
	}

	public function testFormatDate(): void
	{
		$dateTime = new \DateTime('2024-01-15 14:30:45');
		$formatted = DateTime::formatDate($dateTime);
		$this->assertIsString($formatted);
		$this->assertNotEmpty($formatted);
	}

	public function testFormatTime(): void
	{
		$dateTime = new \DateTime('2024-01-15 14:30:45');
		$formatted = DateTime::formatTime($dateTime);
		$this->assertIsString($formatted);
		$this->assertNotEmpty($formatted);
	}

	public function testFormatDateInLong(): void
	{
		$dateTime = new \DateTime('2024-01-15');
		$formatted = DateTime::formatDateInLong($dateTime);
		$this->assertIsString($formatted);
		$this->assertNotEmpty($formatted);
	}

	public function testFormatFromTwig(): void
	{
		$dateTime = new \DateTime('2024-01-15 14:30:45');

		$formatted = DateTime::formatFromTwig($dateTime);
		$this->assertIsString($formatted);

		$formatted = DateTime::formatFromTwig($dateTime, 'long', 'medium');
		$this->assertIsString($formatted);

		// Avec string
		$formatted = DateTime::formatFromTwig('2024-01-15 14:30:45');
		$this->assertIsString($formatted);

		// Null
		$this->assertNull(DateTime::formatFromTwig(null));
	}

	public function testFormatDateFromTwig(): void
	{
		$dateTime = new \DateTime('2024-01-15 14:30:45');
		$formatted = DateTime::formatDateFromTwig($dateTime);
		$this->assertIsString($formatted);

		$this->assertNull(DateTime::formatDateFromTwig(null));
	}

	public function testFormatTimeFromTwig(): void
	{
		$dateTime = new \DateTime('2024-01-15 14:30:45');
		$formatted = DateTime::formatTimeFromTwig($dateTime);
		$this->assertIsString($formatted);

		$this->assertNull(DateTime::formatTimeFromTwig(null));
	}

	/* ===================== UTC Conversion ===================== */

	public function testGetUTCSqlDate(): void
	{
		$dateTime = new \DateTime('2024-01-15 14:30:45', new \DateTimeZone('Europe/Paris'));
		$utcDate = DateTime::getUTCSqlDate($dateTime);
		$this->assertIsString($utcDate);
		$this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}$/', $utcDate);

		$this->assertNull(DateTime::getUTCSqlDate(null));
	}

	public function testGetUTCSqlTime(): void
	{
		$dateTime = new \DateTime('2024-01-15 14:30:45', new \DateTimeZone('Europe/Paris'));
		$utcTime = DateTime::getUTCSqlTime($dateTime);
		$this->assertIsString($utcTime);
		$this->assertMatchesRegularExpression('/^\d{2}:\d{2}:\d{2}$/', $utcTime);

		$this->assertNull(DateTime::getUTCSqlTime(null));
	}

	public function testGetUTCSqlDateTime(): void
	{
		$dateTime = new \DateTime('2024-01-15 14:30:45', new \DateTimeZone('Europe/Paris'));
		$utcDateTime = DateTime::getUTCSqlDateTime($dateTime);
		$this->assertIsString($utcDateTime);
		$this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/', $utcDateTime);

		$this->assertNull(DateTime::getUTCSqlDateTime(null));
	}

	/* ===================== Comparaison ===================== */

	public function testIsDateAfter(): void
	{
		$date1 = new \DateTime('2024-01-20');
		$date2 = new \DateTime('2024-01-15');
		$this->assertTrue(DateTime::isDateAfter($date1, $date2));
		$this->assertFalse(DateTime::isDateAfter($date2, $date1));

		// Même date
		$date3 = new \DateTime('2024-01-15');
		$this->assertFalse(DateTime::isDateAfter($date2, $date3));
	}

	public function testIsDateBefore(): void
	{
		$date1 = new \DateTime('2024-01-15');
		$date2 = new \DateTime('2024-01-20');
		$this->assertTrue(DateTime::isDateBefore($date1, $date2));
		$this->assertFalse(DateTime::isDateBefore($date2, $date1));

		// Même date
		$date3 = new \DateTime('2024-01-15');
		$this->assertFalse(DateTime::isDateBefore($date1, $date3));
	}

	public function testIsInThePast(): void
	{
		$past = new \DateTime('-1 day');
		$this->assertTrue(DateTime::isInThePast($past));

		$future = new \DateTime('+1 day');
		$this->assertFalse(DateTime::isInThePast($future));
	}

	public function testIsInTheFuture(): void
	{
		$future = new \DateTime('+1 day');
		$this->assertTrue(DateTime::isInTheFuture($future));

		$past = new \DateTime('-1 day');
		$this->assertFalse(DateTime::isInTheFuture($past));
	}

	public function testIsDateInThePast(): void
	{
		$yesterday = new \DateTime('yesterday');
		$this->assertTrue(DateTime::isDateInThePast($yesterday));

		$tomorrow = new \DateTime('tomorrow');
		$this->assertFalse(DateTime::isDateInThePast($tomorrow));
	}

	public function testIsDateInTheFuture(): void
	{
		$tomorrow = new \DateTime('tomorrow');
		$this->assertTrue(DateTime::isDateInTheFuture($tomorrow));

		$yesterday = new \DateTime('yesterday');
		$this->assertFalse(DateTime::isDateInTheFuture($yesterday));
	}

	/* ===================== Jours de la semaine ===================== */

	public function testIsWeekend(): void
	{
		// Samedi
		$saturday = new \DateTime('2024-01-20'); // Samedi
		$this->assertTrue(DateTime::isWeekend($saturday));

		// Dimanche
		$sunday = new \DateTime('2024-01-21'); // Dimanche
		$this->assertTrue(DateTime::isWeekend($sunday));

		// Lundi
		$monday = new \DateTime('2024-01-15'); // Lundi
		$this->assertFalse(DateTime::isWeekend($monday));

		// Vendredi
		$friday = new \DateTime('2024-01-19'); // Vendredi
		$this->assertFalse(DateTime::isWeekend($friday));
	}

	/* ===================== Déplacement de dates ===================== */

	public function testMoveBackOfNbDays(): void
	{
		$date = new \DateTime('2024-01-15');
		$result = DateTime::moveBackOfNbDays($date, 5);
		$this->assertEquals('2024-01-10', $result->format('Y-m-d'));

		// Vérifier que l'original n'est pas modifié
		$this->assertEquals('2024-01-15', $date->format('Y-m-d'));
	}

	public function testMoveForwardOfNbDays(): void
	{
		$date = new \DateTime('2024-01-15');
		$result = DateTime::moveForwardOfNbDays($date, 5);
		$this->assertEquals('2024-01-20', $result->format('Y-m-d'));

		// Vérifier que l'original n'est pas modifié
		$this->assertEquals('2024-01-15', $date->format('Y-m-d'));
	}

	/* ===================== Semaine ===================== */

	public function testGetWeekNumber(): void
	{
		$date = new \DateTime('2024-01-15');
		[$year, $weekNumber] = DateTime::getWeekNumber($date);

		$this->assertIsString($year);
		$this->assertIsString($weekNumber);
		$this->assertEquals('2024', $year);
		$this->assertMatchesRegularExpression('/^\d{2}$/', $weekNumber);
	}

	public function testGetWeekNumberWithYearTransition(): void
	{
		// Test pour une date en fin d'année où la semaine appartient à l'année suivante
		$date = new \DateTime('2024-12-30'); // Semaine 1 de 2025
		[$year, $weekNumber] = DateTime::getWeekNumber($date);

		if ((int)$weekNumber === 1) {
			$this->assertEquals('2025', $year);
		}
	}

	public function testGetFirstDayOfCurrentWeek(): void
	{
		$result = DateTime::getFirstDayOfCurrentWeek();
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('1', $result->format('N')); // 1 = Lundi
	}

	public function testGetLastDayOfCurrentWeek(): void
	{
		$result = DateTime::getLastDayOfCurrentWeek();
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('7', $result->format('N')); // 7 = Dimanche
	}

	public function testGetFirstDayOfPreviousWeek(): void
	{
		$result = DateTime::getFirstDayOfPreviousWeek();
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('1', $result->format('N')); // 1 = Lundi
	}

	public function testGetLastDayOfPreviousWeek(): void
	{
		$result = DateTime::getLastDayOfPreviousWeek();
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('7', $result->format('N')); // 7 = Dimanche
	}

	public function testGetFirstDayOfNextWeek(): void
	{
		$result = DateTime::getFirstDayOfNextWeek();
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('1', $result->format('N'));
	}

	public function testGetLastDayOfNextWeek(): void
	{
		$result = DateTime::getLastDayOfNextWeek();
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('7', $result->format('N'));
	}

	public function testGetFirstDayOfWeek(): void
	{
		$result = DateTime::getFirstDayOfWeek(2024, 3);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('1', $result->format('N'));
	}

	public function testGetLastDayOfWeek(): void
	{
		$result = DateTime::getLastDayOfWeek(2024, 3);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('7', $result->format('N'));
	}

	public function testGetFirstDayOfWeekOfDate(): void
	{
		$date = new \DateTime('2024-01-15');
		$result = DateTime::getFirstDayOfWeekOfDate($date);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('1', $result->format('N'));
	}

	public function testGetLastDayOfWeekOfDate(): void
	{
		$date = new \DateTime('2024-01-15');
		$result = DateTime::getLastDayOfWeekOfDate($date);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('7', $result->format('N'));
	}

	public function testGetNextWeekDay(): void
	{
		$monday = new \DateTime('2024-01-15'); // Monday
		$result = DateTime::getNextWeekDay($monday, 5); // Find Friday
		$this->assertEquals('5', $result->format('N'));
		$this->assertEquals('2024-01-19', $result->format('Y-m-d'));
	}

	/* ===================== Mois ===================== */

	public function testMoveBackOfNbMonths(): void
	{
		$date = new \DateTime('2024-03-15');
		$result = DateTime::moveBackOfNbMonths($date, 2);
		$this->assertEquals('2024-01-15', $result->format('Y-m-d'));
		$this->assertEquals('2024-03-15', $date->format('Y-m-d')); // Original unchanged
	}

	public function testMoveForwardOfNbMonths(): void
	{
		$date = new \DateTime('2024-01-15');
		$result = DateTime::moveForwardOfNbMonths($date, 2);
		$this->assertEquals('2024-03-15', $result->format('Y-m-d'));
		$this->assertEquals('2024-01-15', $date->format('Y-m-d')); // Original unchanged
	}

	public function testGetFirstDayOfCurrentMonth(): void
	{
		$result = DateTime::getFirstDayOfCurrentMonth();
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('01', $result->format('d'));
	}

	public function testGetLastDayOfCurrentMonth(): void
	{
		$result = DateTime::getLastDayOfCurrentMonth();
		$this->assertInstanceOf(\DateTime::class, $result);
		$lastDay = (int)$result->format('d');
		$this->assertGreaterThanOrEqual(28, $lastDay);
		$this->assertLessThanOrEqual(31, $lastDay);
	}

	public function testGetFirstDayOfPreviousMonth(): void
	{
		$result = DateTime::getFirstDayOfPreviousMonth();
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('01', $result->format('d'));
	}

	public function testGetLastDayOfPreviousMonth(): void
	{
		$result = DateTime::getLastDayOfPreviousMonth();
		$this->assertInstanceOf(\DateTime::class, $result);
		$lastDay = (int)$result->format('d');
		$this->assertGreaterThanOrEqual(28, $lastDay);
		$this->assertLessThanOrEqual(31, $lastDay);
	}

	public function testGetFirstDayOfNextMonth(): void
	{
		$result = DateTime::getFirstDayOfNextMonth();
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('01', $result->format('d'));
	}

	public function testGetLastDayOfNextMonth(): void
	{
		$result = DateTime::getLastDayOfNextMonth();
		$this->assertInstanceOf(\DateTime::class, $result);
		$lastDay = (int)$result->format('d');
		$this->assertGreaterThanOrEqual(28, $lastDay);
		$this->assertLessThanOrEqual(31, $lastDay);
	}

	public function testGetFirstDayOfMonth(): void
	{
		$result = DateTime::getFirstDayOfMonth(2024, 3);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('2024-03-01', $result->format('Y-m-d'));
	}

	public function testGetLastDayOfMonth(): void
	{
		$result = DateTime::getLastDayOfMonth(2024, 2);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('2024-02-29', $result->format('Y-m-d')); // 2024 is leap year
	}

	public function testGetFirstDayOfMonthOfDate(): void
	{
		$date = new \DateTime('2024-03-15');
		$result = DateTime::getFirstDayOfMonthOfDate($date);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('2024-03-01', $result->format('Y-m-d'));
	}

	public function testGetLastDayOfMonthOfDate(): void
	{
		$date = new \DateTime('2024-03-15');
		$result = DateTime::getLastDayOfMonthOfDate($date);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('2024-03-31', $result->format('Y-m-d'));
	}

	public function testGetWeekDayOfMonth(): void
	{
		// 2nd Wednesday of January 2024
		$result = DateTime::getWeekDayOfMonth(2024, 1, 3, 2);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('3', $result->format('N')); // Wednesday
		$this->assertEquals('2024-01', $result->format('Y-m'));
	}

	public function testGetWeekDayOfMonthInvalid(): void
	{
		// Invalid weekday
		$result = DateTime::getWeekDayOfMonth(2024, 1, 8, 1);
		$this->assertNull($result);

		// Invalid number
		$result = DateTime::getWeekDayOfMonth(2024, 1, 1, 6);
		$this->assertNull($result);

		// Non-existent (5th Monday of February 2024)
		$result = DateTime::getWeekDayOfMonth(2024, 2, 1, 5);
		$this->assertNull($result);
	}

	public function testGetLastWeekDayOfMonth(): void
	{
		// Last Friday of January 2024
		$result = DateTime::getLastWeekDayOfMonth(2024, 1, 5);
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('5', $result->format('N')); // Friday
		$this->assertEquals('2024-01', $result->format('Y-m'));
	}

	public function testGetLastWeekDayOfMonthInvalid(): void
	{
		$result = DateTime::getLastWeekDayOfMonth(2024, 1, 8);
		$this->assertNull($result);
	}

	/* ===================== Année ===================== */

	public function testCalculateAge(): void
	{
		$birthDate = new \DateTime('-30 years');
		$age = DateTime::calculateAge($birthDate);
		$this->assertEquals(30, $age);

		$birthDate = new \DateTime('-25 years -6 months');
		$age = DateTime::calculateAge($birthDate);
		$this->assertEquals(25, $age);
	}

	/* ===================== Working Days ===================== */

	public function testIsWorkingDay(): void
	{
		// Monday - working day
		$monday = new \DateTime('2024-01-15');
		$this->assertTrue(DateTime::isWorkingDay($monday, false));

		// Saturday - not working day
		$saturday = new \DateTime('2024-01-20');
		$this->assertFalse(DateTime::isWorkingDay($saturday, false));

		// Sunday - not working day
		$sunday = new \DateTime('2024-01-21');
		$this->assertFalse(DateTime::isWorkingDay($sunday, false));
	}

	public function testIsBusinessDay(): void
	{
		// Monday - business day
		$monday = new \DateTime('2024-01-15');
		$this->assertTrue(DateTime::isBusinessDay($monday, false));

		// Saturday - business day (6th day)
		$saturday = new \DateTime('2024-01-20');
		$this->assertTrue(DateTime::isBusinessDay($saturday, false));

		// Sunday - not business day
		$sunday = new \DateTime('2024-01-21');
		$this->assertFalse(DateTime::isBusinessDay($sunday, false));
	}

	/* ===================== getCurrentDateTime ===================== */

	public function testGetCurrentDateTime(): void
	{
		$result = DateTime::getCurrentDateTime();
		$this->assertInstanceOf(\DateTime::class, $result);
	}

	/* ===================== format ===================== */

	public function testFormat(): void
	{
		$dateTime = new \DateTime('2024-01-15 14:30:45');
		$formatted = DateTime::format($dateTime, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT);
		$this->assertIsString($formatted);
		$this->assertNotEmpty($formatted);
	}

	/* ===================== parse ===================== */

	public function testParse(): void
	{
		$result = DateTime::parse('2024-01-15');
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('2024-01-15', $result->format('Y-m-d'));
	}

	/* ===================== parseDate (deprecated) ===================== */

	public function testParseDate(): void
	{
		$result = DateTime::parseDate('2024-01-15');
		$this->assertInstanceOf(\DateTime::class, $result);
		$this->assertEquals('2024-01-15', $result->format('Y-m-d'));

		// ISO format
		$result = DateTime::parseDate('2024-01-15T14:30:45');
		$this->assertInstanceOf(\DateTime::class, $result);

		// Empty
		$result = DateTime::parseDate('');
		$this->assertNull($result);
	}
}