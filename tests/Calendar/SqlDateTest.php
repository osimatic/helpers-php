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

	public function testGet(): void
	{
		// Date normale
		$this->assertEquals('2024-01-15', SqlDate::get(2024, 1, 15));

		// Date avec zéros à gauche
		$this->assertEquals('2024-01-01', SqlDate::get(2024, 1, 1));
		$this->assertEquals('2024-12-31', SqlDate::get(2024, 12, 31));

		// Formatage correct des mois/jours < 10
		$this->assertEquals('2024-03-05', SqlDate::get(2024, 3, 5));
		$this->assertEquals('2024-10-25', SqlDate::get(2024, 10, 25));
	}

	public function testGetFirstDayOfWeek(): void
	{
		// Semaine 1 de 2024 (année qui commence un lundi)
		$result = SqlDate::getFirstDayOfWeek(2024, 1);
		$this->assertEquals('2024-01-01', $result);

		// Semaine 2 de 2024
		$result = SqlDate::getFirstDayOfWeek(2024, 2);
		$this->assertEquals('2024-01-08', $result);

		// Semaine 10 de 2024
		$result = SqlDate::getFirstDayOfWeek(2024, 10);
		$this->assertMatchesRegularExpression('/2024-\d{2}-\d{2}/', $result);

		// Vérifier que le résultat est toujours un lundi
		$timestamp = strtotime($result);
		$this->assertEquals(1, (int) date('N', $timestamp)); // N=1 pour lundi
	}

	public function testGetLastDayOfWeek(): void
	{
		// Dernière jour de la semaine 1 de 2024
		$result = SqlDate::getLastDayOfWeek(2024, 1);
		$this->assertEquals('2024-01-07', $result);

		// Dernière jour de la semaine 2 de 2024
		$result = SqlDate::getLastDayOfWeek(2024, 2);
		$this->assertEquals('2024-01-14', $result);

		// Vérifier que le résultat est toujours un dimanche
		$timestamp = strtotime($result);
		$this->assertEquals(7, (int) date('N', $timestamp)); // N=7 pour dimanche
	}

	public function testGetFirstDayOfMonth(): void
	{
		// Premier jour de janvier
		$this->assertEquals('2024-01-01', SqlDate::getFirstDayOfMonth(2024, 1));

		// Premier jour de février
		$this->assertEquals('2024-02-01', SqlDate::getFirstDayOfMonth(2024, 2));

		// Premier jour de décembre
		$this->assertEquals('2024-12-01', SqlDate::getFirstDayOfMonth(2024, 12));

		// Année bissextile
		$this->assertEquals('2024-02-01', SqlDate::getFirstDayOfMonth(2024, 2));

		// Année non bissextile
		$this->assertEquals('2023-02-01', SqlDate::getFirstDayOfMonth(2023, 2));
	}

	public function testGetLastDayOfMonth(): void
	{
		// Dernier jour de janvier (31)
		$this->assertEquals('2024-01-31', SqlDate::getLastDayOfMonth(2024, 1));

		// Dernier jour de février année bissextile (29)
		$this->assertEquals('2024-02-29', SqlDate::getLastDayOfMonth(2024, 2));

		// Dernier jour de février année non bissextile (28)
		$this->assertEquals('2023-02-28', SqlDate::getLastDayOfMonth(2023, 2));

		// Dernier jour d'avril (30)
		$this->assertEquals('2024-04-30', SqlDate::getLastDayOfMonth(2024, 4));

		// Dernier jour de décembre (31)
		$this->assertEquals('2024-12-31', SqlDate::getLastDayOfMonth(2024, 12));
	}
}