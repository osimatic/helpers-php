<?php

namespace Tests\Calendar;

use Osimatic\Calendar\Timestamp;
use PHPUnit\Framework\TestCase;

final class TimestampTest extends TestCase
{
	public function testGetByYearMonthDay(): void
	{
		$timestamp = Timestamp::getByYearMonthDay(2024, 1, 15);
		$this->assertEquals('2024-01-15', date('Y-m-d', $timestamp));
		$this->assertEquals('00:00:00', date('H:i:s', $timestamp));

		$timestamp = Timestamp::getByYearMonthDay(2023, 12, 31);
		$this->assertEquals('2023-12-31', date('Y-m-d', $timestamp));
	}

	public function testIsDateInThePast(): void
	{
		// Date dans le passé
		$yesterday = mktime(0, 0, 0, date('m'), date('d') - 1, date('Y'));
		$this->assertTrue(Timestamp::isDateInThePast($yesterday));

		// Date d'aujourd'hui (début de journée)
		$today = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
		$this->assertFalse(Timestamp::isDateInThePast($today));

		// Date dans le futur
		$tomorrow = mktime(0, 0, 0, date('m'), date('d') + 1, date('Y'));
		$this->assertFalse(Timestamp::isDateInThePast($tomorrow));
	}

	public function testIsTimeInThePast(): void
	{
		// Il y a 1 heure
		$oneHourAgo = time() - 3600;
		$this->assertTrue(Timestamp::isTimeInThePast($oneHourAgo));

		// Dans 1 heure
		$oneHourLater = time() + 3600;
		$this->assertFalse(Timestamp::isTimeInThePast($oneHourLater));
	}

	public function testGetTimestampNextDayOfWeekByYearMonthDay(): void
	{
		// 2024-01-15 est un lundi (1)
		// Chercher le prochain mercredi (3)
		$timestamp = Timestamp::getTimestampNextDayOfWeekByYearMonthDay(3, 2024, 1, 15);
		$this->assertEquals(3, date('N', $timestamp)); // 3 = mercredi
		$this->assertEquals('2024-01-17', date('Y-m-d', $timestamp));

		// Chercher le même jour de la semaine
		$timestamp = Timestamp::getTimestampNextDayOfWeekByYearMonthDay(1, 2024, 1, 15);
		$this->assertEquals(1, date('N', $timestamp)); // 1 = lundi
		$this->assertEquals('2024-01-15', date('Y-m-d', $timestamp));

		// Chercher dimanche à partir de lundi
		$timestamp = Timestamp::getTimestampNextDayOfWeekByYearMonthDay(7, 2024, 1, 15);
		$this->assertEquals(7, date('N', $timestamp)); // 7 = dimanche
		$this->assertEquals('2024-01-21', date('Y-m-d', $timestamp));
	}

	public function testGetTimestampPreviousDayOfWeekByYearMonthDay(): void
	{
		// 2024-01-15 est un lundi (1)
		// Chercher le vendredi précédent (5)
		$timestamp = Timestamp::getTimestampPreviousDayOfWeekByYearMonthDay(5, 2024, 1, 15);
		$this->assertEquals(5, date('N', $timestamp)); // 5 = vendredi
		$this->assertEquals('2024-01-12', date('Y-m-d', $timestamp));

		// Chercher le même jour de la semaine
		$timestamp = Timestamp::getTimestampPreviousDayOfWeekByYearMonthDay(1, 2024, 1, 15);
		$this->assertEquals(1, date('N', $timestamp)); // 1 = lundi
		$this->assertEquals('2024-01-15', date('Y-m-d', $timestamp));
	}

	public function testGetNextDayOfWeekOfWeek(): void
	{
		// À partir du 15 janvier 2024 (lundi), chercher le prochain mercredi
		$baseTimestamp = mktime(0, 0, 0, 1, 15, 2024);
		$timestamp = Timestamp::getNextDayOfWeekOfWeek(3, $baseTimestamp);
		$this->assertEquals(3, date('N', $timestamp));
		$this->assertEquals('2024-01-17', date('Y-m-d', $timestamp));
	}

	public function testGetPreviousDayOfWeekOfWeek(): void
	{
		// À partir du 15 janvier 2024 (lundi), chercher le vendredi précédent
		$baseTimestamp = mktime(0, 0, 0, 1, 15, 2024);
		$timestamp = Timestamp::getPreviousDayOfWeekOfWeek(5, $baseTimestamp);
		$this->assertEquals(5, date('N', $timestamp));
		$this->assertEquals('2024-01-12', date('Y-m-d', $timestamp));
	}
}