<?php

declare(strict_types=1);

namespace Tests\Calendar;

use Osimatic\Calendar\PublicHoliday;
use Osimatic\Calendar\PublicHolidayCalendar;
use PHPUnit\Framework\TestCase;

final class PublicHolidayTest extends TestCase
{
	/* ===================== Constructor ===================== */

	public function testConstructorWithMinimalParameters(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('New Year', $timestamp);

		$this->assertInstanceOf(PublicHoliday::class, $holiday);
		$this->assertSame('New Year (1er janvier)', $holiday->getName());
		$this->assertSame($timestamp, $holiday->getTimestamp());
	}

	public function testConstructorWithAllParameters(): void
	{
		$timestamp = strtotime('2024-07-14');
		$holiday = new PublicHoliday(
			name: 'Bastille Day',
			timestamp: $timestamp,
			key: '07-14',
			fullName: 'French National Day',
			isFixedDate: true,
			calendar: PublicHolidayCalendar::GREGORIAN
		);

		$this->assertSame('Bastille Day (14 juillet)', $holiday->getName());
		$this->assertSame($timestamp, $holiday->getTimestamp());
		$this->assertSame('07-14', $holiday->getKey());
		$this->assertSame('French National Day', $holiday->getFullName());
		$this->assertTrue($holiday->isFixedDate());
		$this->assertSame(PublicHolidayCalendar::GREGORIAN, $holiday->getCalendar());
	}

	public function testConstructorGeneratesKeyFromTimestampWhenNotProvided(): void
	{
		$timestamp = strtotime('2024-12-25');
		$holiday = new PublicHoliday('Christmas', $timestamp);

		$this->assertSame('12-25', $holiday->getKey());
	}

	public function testConstructorAddsDateToNameForGregorianCalendar(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('New Year', $timestamp, '01-01');

		// Should add "(1er janvier)" to the name
		$this->assertStringContainsString('1er janvier', $holiday->getName());
	}

	public function testConstructorAddsDateToNameForDayTwo(): void
	{
		$timestamp = strtotime('2024-01-02');
		$holiday = new PublicHoliday('Test Holiday', $timestamp, '01-02');

		// Day 2 should not have "er" suffix
		$this->assertStringContainsString('2 janvier', $holiday->getName());
		$this->assertStringNotContainsString('2er', $holiday->getName());
	}

	public function testConstructorDoesNotAddDateWhenKeyIsNotDatePattern(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('Easter', $timestamp, 'EASTER');

		// Should not add date when key is not in mm-dd format
		$this->assertSame('Easter', $holiday->getName());
	}

	public function testConstructorWithNullFullName(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('New Year', $timestamp, '01-01', null);

		$this->assertNull($holiday->getFullName());
	}

	public function testConstructorWithIsFixedDateFalse(): void
	{
		$timestamp = strtotime('2024-04-01'); // Easter Monday varies each year
		$holiday = new PublicHoliday('Easter Monday', $timestamp, 'EASTER_MONDAY', null, false);

		$this->assertFalse($holiday->isFixedDate());
	}

	/* ===================== Getters and Setters ===================== */

	public function testGetAndSetKey(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('New Year', $timestamp);

		$this->assertSame('01-01', $holiday->getKey());

		$holiday->setKey('NEW_YEAR');
		$this->assertSame('NEW_YEAR', $holiday->getKey());
	}

	public function testGetAndSetCalendar(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('Holiday', $timestamp);

		$this->assertSame(PublicHolidayCalendar::GREGORIAN, $holiday->getCalendar());

		$holiday->setCalendar(PublicHolidayCalendar::HIJRI);
		$this->assertSame(PublicHolidayCalendar::HIJRI, $holiday->getCalendar());
	}

	public function testGetAndSetTimestamp(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('Holiday', $timestamp);

		$this->assertSame($timestamp, $holiday->getTimestamp());

		$newTimestamp = strtotime('2024-12-25');
		$holiday->setTimestamp($newTimestamp);
		$this->assertSame($newTimestamp, $holiday->getTimestamp());
	}

	public function testGetAndSetName(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('New Year', $timestamp);

		$this->assertStringContainsString('New Year', $holiday->getName());

		$holiday->setName('Updated Name');
		$this->assertSame('Updated Name', $holiday->getName());
	}

	public function testGetAndSetFullName(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('New Year', $timestamp, '01-01', 'New Year\'s Day');

		$this->assertSame('New Year\'s Day', $holiday->getFullName());

		$holiday->setFullName('First Day of the Year');
		$this->assertSame('First Day of the Year', $holiday->getFullName());
	}

	public function testGetAndSetIsFixedDate(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('Holiday', $timestamp);

		$this->assertTrue($holiday->isFixedDate());

		$holiday->setIsFixedDate(false);
		$this->assertFalse($holiday->isFixedDate());
	}

	/* ===================== getMonth() ===================== */

	public function testGetMonthForGregorianCalendar(): void
	{
		$timestamp = strtotime('2024-07-14'); // July 14
		$holiday = new PublicHoliday('Bastille Day', $timestamp, '07-14', null, true, PublicHolidayCalendar::GREGORIAN);

		$this->assertSame(7, $holiday->getMonth());
	}

	public function testGetMonthForJanuary(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('New Year', $timestamp);

		$this->assertSame(1, $holiday->getMonth());
	}

	public function testGetMonthForDecember(): void
	{
		$timestamp = strtotime('2024-12-25');
		$holiday = new PublicHoliday('Christmas', $timestamp);

		$this->assertSame(12, $holiday->getMonth());
	}

	/* ===================== getDay() ===================== */

	public function testGetDayForGregorianCalendar(): void
	{
		$timestamp = strtotime('2024-07-14');
		$holiday = new PublicHoliday('Bastille Day', $timestamp, '07-14', null, true, PublicHolidayCalendar::GREGORIAN);

		$this->assertSame(14, $holiday->getDay());
	}

	public function testGetDayForFirstDay(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('New Year', $timestamp);

		$this->assertSame(1, $holiday->getDay());
	}

	public function testGetDayForLastDayOfMonth(): void
	{
		$timestamp = strtotime('2024-01-31');
		$holiday = new PublicHoliday('Test Holiday', $timestamp);

		$this->assertSame(31, $holiday->getDay());
	}

	/* ===================== Name formatting - Different months ===================== */

	public function testConstructorNameFormattingForJanuary(): void
	{
		$timestamp = strtotime('2024-01-15');
		$holiday = new PublicHoliday('Test', $timestamp, '01-15');

		$this->assertStringContainsString('15 janvier', $holiday->getName());
	}

	public function testConstructorNameFormattingForFebruary(): void
	{
		$timestamp = strtotime('2024-02-14');
		$holiday = new PublicHoliday('Valentine', $timestamp, '02-14');

		$this->assertStringContainsString('14 février', $holiday->getName());
	}

	public function testConstructorNameFormattingForMarch(): void
	{
		$timestamp = strtotime('2024-03-08');
		$holiday = new PublicHoliday('Women\'s Day', $timestamp, '03-08');

		$this->assertStringContainsString('8 mars', $holiday->getName());
	}

	public function testConstructorNameFormattingForApril(): void
	{
		$timestamp = strtotime('2024-04-01');
		$holiday = new PublicHoliday('April Fools', $timestamp, '04-01');

		$this->assertStringContainsString('1er avril', $holiday->getName());
	}

	public function testConstructorNameFormattingForMay(): void
	{
		$timestamp = strtotime('2024-05-01');
		$holiday = new PublicHoliday('Labour Day', $timestamp, '05-01');

		$this->assertStringContainsString('1er mai', $holiday->getName());
	}

	public function testConstructorNameFormattingForJune(): void
	{
		$timestamp = strtotime('2024-06-21');
		$holiday = new PublicHoliday('Summer Solstice', $timestamp, '06-21');

		$this->assertStringContainsString('21 juin', $holiday->getName());
	}

	public function testConstructorNameFormattingForJuly(): void
	{
		$timestamp = strtotime('2024-07-14');
		$holiday = new PublicHoliday('Bastille Day', $timestamp, '07-14');

		$this->assertStringContainsString('14 juillet', $holiday->getName());
	}

	public function testConstructorNameFormattingForAugust(): void
	{
		$timestamp = strtotime('2024-08-15');
		$holiday = new PublicHoliday('Assumption', $timestamp, '08-15');

		$this->assertStringContainsString('15 août', $holiday->getName());
	}

	public function testConstructorNameFormattingForSeptember(): void
	{
		$timestamp = strtotime('2024-09-01');
		$holiday = new PublicHoliday('Back to School', $timestamp, '09-01');

		$this->assertStringContainsString('1er septembre', $holiday->getName());
	}

	public function testConstructorNameFormattingForOctober(): void
	{
		$timestamp = strtotime('2024-10-31');
		$holiday = new PublicHoliday('Halloween', $timestamp, '10-31');

		$this->assertStringContainsString('31 octobre', $holiday->getName());
	}

	public function testConstructorNameFormattingForNovember(): void
	{
		$timestamp = strtotime('2024-11-11');
		$holiday = new PublicHoliday('Armistice Day', $timestamp, '11-11');

		$this->assertStringContainsString('11 novembre', $holiday->getName());
	}

	public function testConstructorNameFormattingForDecember(): void
	{
		$timestamp = strtotime('2024-12-25');
		$holiday = new PublicHoliday('Christmas', $timestamp, '12-25');

		$this->assertStringContainsString('25 décembre', $holiday->getName());
	}

	/* ===================== Real-world holidays ===================== */

	public function testNewYearsDayFrance(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday(
			name: 'Jour de l\'an',
			timestamp: $timestamp,
			key: '01-01',
			fullName: 'Premier jour de l\'année',
			isFixedDate: true
		);

		$this->assertStringContainsString('Jour de l\'an', $holiday->getName());
		$this->assertStringContainsString('1er janvier', $holiday->getName());
		$this->assertSame('Premier jour de l\'année', $holiday->getFullName());
		$this->assertTrue($holiday->isFixedDate());
		$this->assertSame(1, $holiday->getMonth());
		$this->assertSame(1, $holiday->getDay());
	}

	public function testChristmas(): void
	{
		$timestamp = strtotime('2024-12-25');
		$holiday = new PublicHoliday(
			name: 'Noël',
			timestamp: $timestamp,
			key: '12-25',
			fullName: 'Nativité',
			isFixedDate: true
		);

		$this->assertStringContainsString('Noël', $holiday->getName());
		$this->assertStringContainsString('25 décembre', $holiday->getName());
		$this->assertSame('Nativité', $holiday->getFullName());
		$this->assertTrue($holiday->isFixedDate());
		$this->assertSame(12, $holiday->getMonth());
		$this->assertSame(25, $holiday->getDay());
	}

	public function testBastilleDayFrance(): void
	{
		$timestamp = strtotime('2024-07-14');
		$holiday = new PublicHoliday(
			name: 'Fête nationale',
			timestamp: $timestamp,
			key: '07-14',
			fullName: 'Prise de la Bastille',
			isFixedDate: true
		);

		$this->assertStringContainsString('Fête nationale', $holiday->getName());
		$this->assertStringContainsString('14 juillet', $holiday->getName());
		$this->assertSame('Prise de la Bastille', $holiday->getFullName());
		$this->assertTrue($holiday->isFixedDate());
		$this->assertSame(7, $holiday->getMonth());
		$this->assertSame(14, $holiday->getDay());
	}

	public function testEasterMonday(): void
	{
		// Easter Monday is a moveable holiday
		$timestamp = strtotime('2024-04-01'); // Easter Monday 2024
		$holiday = new PublicHoliday(
			name: 'Lundi de Pâques',
			timestamp: $timestamp,
			key: 'EASTER_MONDAY',
			fullName: 'Lendemain de Pâques',
			isFixedDate: false
		);

		$this->assertSame('Lundi de Pâques', $holiday->getName());
		$this->assertSame('Lendemain de Pâques', $holiday->getFullName());
		$this->assertFalse($holiday->isFixedDate());
		$this->assertSame('EASTER_MONDAY', $holiday->getKey());
	}

	public function testLabourDay(): void
	{
		$timestamp = strtotime('2024-05-01');
		$holiday = new PublicHoliday(
			name: 'Fête du travail',
			timestamp: $timestamp,
			key: '05-01',
			fullName: 'Journée internationale des travailleurs',
			isFixedDate: true
		);

		$this->assertStringContainsString('Fête du travail', $holiday->getName());
		$this->assertStringContainsString('1er mai', $holiday->getName());
		$this->assertSame('Journée internationale des travailleurs', $holiday->getFullName());
		$this->assertTrue($holiday->isFixedDate());
	}

	/* ===================== Edge cases ===================== */

	public function testConstructorWithEmptyName(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('', $timestamp);

		$this->assertStringContainsString('1er janvier', $holiday->getName());
	}

	public function testConstructorWithVeryLongName(): void
	{
		$timestamp = strtotime('2024-01-01');
		$longName = str_repeat('Very Long Holiday Name ', 10);
		$holiday = new PublicHoliday($longName, $timestamp);

		$this->assertStringContainsString($longName, $holiday->getName());
		$this->assertStringContainsString('1er janvier', $holiday->getName());
	}

	public function testSetTimestampChangesMonthAndDay(): void
	{
		$timestamp1 = strtotime('2024-01-01');
		$holiday = new PublicHoliday('Holiday', $timestamp1);

		$this->assertSame(1, $holiday->getMonth());
		$this->assertSame(1, $holiday->getDay());

		$timestamp2 = strtotime('2024-12-31');
		$holiday->setTimestamp($timestamp2);

		$this->assertSame(12, $holiday->getMonth());
		$this->assertSame(31, $holiday->getDay());
	}

	public function testMultipleHolidaysWithSameDate(): void
	{
		$timestamp = strtotime('2024-01-01');

		$holiday1 = new PublicHoliday('Holiday 1', $timestamp, '01-01', 'First Holiday');
		$holiday2 = new PublicHoliday('Holiday 2', $timestamp, '01-01', 'Second Holiday');

		$this->assertStringContainsString('Holiday 1', $holiday1->getName());
		$this->assertStringContainsString('Holiday 2', $holiday2->getName());
		$this->assertSame('First Holiday', $holiday1->getFullName());
		$this->assertSame('Second Holiday', $holiday2->getFullName());
	}

	public function testKeyWithLeadingZeros(): void
	{
		$timestamp = strtotime('2024-01-05');
		$holiday = new PublicHoliday('Holiday', $timestamp, '01-05');

		$this->assertSame('01-05', $holiday->getKey());
	}

	public function testKeyWithoutLeadingZeros(): void
	{
		$timestamp = strtotime('2024-01-05');
		$holiday = new PublicHoliday('Holiday', $timestamp, '1-5');

		$this->assertSame('1-5', $holiday->getKey());
	}

	/* ===================== Calendar types ===================== */

	public function testGregorianCalendar(): void
	{
		$timestamp = strtotime('2024-07-14');
		$holiday = new PublicHoliday('Holiday', $timestamp, '07-14', null, true, PublicHolidayCalendar::GREGORIAN);

		$this->assertSame(PublicHolidayCalendar::GREGORIAN, $holiday->getCalendar());
	}

	public function testHijriCalendar(): void
	{
		$timestamp = strtotime('2024-04-10'); // Eid al-Fitr 2024 (approximate)
		$holiday = new PublicHoliday('Eid al-Fitr', $timestamp, 'EID_FITR', null, false, PublicHolidayCalendar::HIJRI);

		$this->assertSame(PublicHolidayCalendar::HIJRI, $holiday->getCalendar());
		$this->assertFalse($holiday->isFixedDate()); // Islamic holidays move in Gregorian calendar
	}

	public function testIndianCalendar(): void
	{
		$timestamp = strtotime('2024-11-01'); // Diwali 2024 (approximate)
		$holiday = new PublicHoliday('Diwali', $timestamp, 'DIWALI', null, false, PublicHolidayCalendar::INDIAN);

		$this->assertSame(PublicHolidayCalendar::INDIAN, $holiday->getCalendar());
		$this->assertFalse($holiday->isFixedDate());
	}

	/* ===================== Date edge cases ===================== */

	public function testLeapYearFebruary29(): void
	{
		$timestamp = strtotime('2024-02-29'); // 2024 is a leap year
		$holiday = new PublicHoliday('Leap Day', $timestamp, '02-29');

		$this->assertSame(2, $holiday->getMonth());
		$this->assertSame(29, $holiday->getDay());
		$this->assertStringContainsString('29 février', $holiday->getName());
	}

	public function testFirstDayOfYear(): void
	{
		$timestamp = strtotime('2024-01-01');
		$holiday = new PublicHoliday('First Day', $timestamp, '01-01');

		$this->assertSame(1, $holiday->getMonth());
		$this->assertSame(1, $holiday->getDay());
		$this->assertStringContainsString('1er janvier', $holiday->getName());
	}

	public function testLastDayOfYear(): void
	{
		$timestamp = strtotime('2024-12-31');
		$holiday = new PublicHoliday('Last Day', $timestamp, '12-31');

		$this->assertSame(12, $holiday->getMonth());
		$this->assertSame(31, $holiday->getDay());
		$this->assertStringContainsString('31 décembre', $holiday->getName());
	}

	/* ===================== Timestamp consistency ===================== */

	public function testTimestampConsistencyBetweenGetterAndConstructor(): void
	{
		$timestamp = strtotime('2024-06-15 14:30:00');
		$holiday = new PublicHoliday('Holiday', $timestamp);

		$this->assertSame($timestamp, $holiday->getTimestamp());
	}

	public function testTimestampPreservesTime(): void
	{
		// Create timestamp with specific time
		$timestamp = strtotime('2024-06-15 14:30:00');
		$holiday = new PublicHoliday('Holiday', $timestamp);

		// Month and day should still work correctly
		$this->assertSame(6, $holiday->getMonth());
		$this->assertSame(15, $holiday->getDay());
	}
}