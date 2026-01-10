<?php

namespace Tests\Calendar;

use Osimatic\Calendar\SqlDate;
use PHPUnit\Framework\TestCase;

final class SqlDateTest extends TestCase
{
	public function testParse(): void
	{
		// Format ISO
		$this->assertEquals('2024-01-15', SqlDate::parse('2024-01-15'));

		// Format français
		$this->assertEquals('2024-01-15', SqlDate::parse('15/01/2024'));

		// Format avec texte
		$this->assertEquals('2024-12-25', SqlDate::parse('2024-12-25'));

		// Format array
		$this->assertEquals('2024-03-20', SqlDate::parse(['date' => '2024-03-20 12:30:45']));

		// Date invalide
		$this->assertNull(SqlDate::parse('invalid-date'));
		$this->assertNull(SqlDate::parse(''));
	}

	public function testCheck(): void
	{
		// Dates valides
		$this->assertTrue(SqlDate::check('2024-01-15'));
		$this->assertTrue(SqlDate::check('2024-12-31'));
		$this->assertTrue(SqlDate::check('2024-02-29')); // Année bissextile

		// Dates invalides
		$this->assertFalse(SqlDate::check('2023-02-29')); // Pas année bissextile
		$this->assertFalse(SqlDate::check('2024-13-01')); // Mois invalide
		$this->assertFalse(SqlDate::check('2024-01-32')); // Jour invalide
		$this->assertFalse(SqlDate::check('2024-00-15')); // Mois 0
		$this->assertFalse(SqlDate::check('invalid'));
		$this->assertFalse(SqlDate::check(null));
	}

	public function testGetYear(): void
	{
		$this->assertEquals(2024, SqlDate::getYear('2024-01-15'));
		$this->assertEquals(2023, SqlDate::getYear('2023-12-31'));
		$this->assertEquals(2000, SqlDate::getYear('2000-06-15'));
	}

	public function testGetMonth(): void
	{
		$this->assertEquals(1, SqlDate::getMonth('2024-01-15'));
		$this->assertEquals(12, SqlDate::getMonth('2024-12-31'));
		$this->assertEquals(6, SqlDate::getMonth('2024-06-15'));
	}

	public function testGetDay(): void
	{
		$this->assertEquals(15, SqlDate::getDay('2024-01-15'));
		$this->assertEquals(31, SqlDate::getDay('2024-12-31'));
		$this->assertEquals(1, SqlDate::getDay('2024-06-01'));
	}
}