<?php

namespace Tests\ArrayList;

use Osimatic\ArrayList\MultidimensionalArray;
use PHPUnit\Framework\TestCase;

final class MultidimensionalArrayTest extends TestCase
{
	/* ===================== Méthodes de comptage ===================== */

	public function testCount(): void
	{
		$array = [1, 2, [3, 4, [5, 6]]];
		$this->assertEquals(6, MultidimensionalArray::count($array));

		$array = [1, 2, 3];
		$this->assertEquals(3, MultidimensionalArray::count($array));

		$array = [];
		$this->assertEquals(0, MultidimensionalArray::count($array));

		$array = [[1, 2], [3, 4], [5, 6]];
		$this->assertEquals(6, MultidimensionalArray::count($array));
	}

	/* ===================== Méthodes de récupération ===================== */

	public function testGetValuesWithKeysByKey(): void
	{
		$array = [
			'user1' => ['name' => 'John', 'age' => 30],
			'user2' => ['name' => 'Jane', 'age' => 25],
			'user3' => ['city' => 'Paris'],
		];

		$result = MultidimensionalArray::getValuesWithKeysByKey($array, 'name');
		$this->assertEquals(['user1' => 'John', 'user2' => 'Jane'], $result);

		$result = MultidimensionalArray::getValuesWithKeysByKey($array, 'age');
		$this->assertEquals(['user1' => 30, 'user2' => 25], $result);

		$result = MultidimensionalArray::getValuesWithKeysByKey($array, 'city');
		$this->assertEquals(['user3' => 'Paris'], $result);

		// Clé inexistante
		$result = MultidimensionalArray::getValuesWithKeysByKey($array, 'country');
		$this->assertEquals([], $result);
	}

	public function testGetValuesByKeyarray(): void
	{
		$array = [
			['name' => 'John', 'age' => 30],
			['name' => 'Jane', 'age' => 25],
			['city' => 'Paris'],
		];

		$result = MultidimensionalArray::getValuesByKeyarray($array, 'name');
		$this->assertEquals(['John', 'Jane'], $result);

		$result = MultidimensionalArray::getValuesByKeyarray($array, 'age');
		$this->assertEquals([30, 25], $result);

		// Clé inexistante
		$result = MultidimensionalArray::getValuesByKeyarray($array, 'country');
		$this->assertEquals([], $result);
	}

	/* ===================== Méthodes de modification ===================== */

	public function testAddKeyAndValue(): void
	{
		$array = [
			['name' => 'John'],
			['name' => 'Jane'],
		];

		MultidimensionalArray::addKeyAndValue($array, 'status', 'active');

		$this->assertArrayHasKey('status', $array[0]);
	}

	/* ===================== Méthodes de recherche ===================== */

	public function testGetValue(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
			['id' => 3, 'name' => 'Bob'],
		];

		$result = MultidimensionalArray::getValue($array, 2, 'id');
		$this->assertEquals(['id' => 2, 'name' => 'Jane'], $result);

		$result = MultidimensionalArray::getValue($array, 'Bob', 'name');
		$this->assertEquals(['id' => 3, 'name' => 'Bob'], $result);

		// Valeur inexistante
		$result = MultidimensionalArray::getValue($array, 99, 'id');
		$this->assertNull($result);
	}

	public function testIsValueExist(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
			['id' => 3, 'name' => 'Bob'],
		];

		$this->assertTrue(MultidimensionalArray::isValueExist($array, 2, 'id'));
		$this->assertTrue(MultidimensionalArray::isValueExist($array, 'Jane', 'name'));
		$this->assertFalse(MultidimensionalArray::isValueExist($array, 99, 'id'));
		$this->assertFalse(MultidimensionalArray::isValueExist($array, 'Alice', 'name'));
	}

	public function testInArrayRecursive(): void
	{
		$haystack = [1, 2, [3, 4, [5, 6]]];

		$this->assertTrue(MultidimensionalArray::inArrayRecursive(1, $haystack));
		$this->assertTrue(MultidimensionalArray::inArrayRecursive(5, $haystack));
		$this->assertTrue(MultidimensionalArray::inArrayRecursive(6, $haystack));
		$this->assertFalse(MultidimensionalArray::inArrayRecursive(7, $haystack));

		// Test avec strict
		$haystack = [1, '2', [3, '4']];
		$this->assertFalse(MultidimensionalArray::inArrayRecursive(2, $haystack, true));
		$this->assertTrue(MultidimensionalArray::inArrayRecursive(2, $haystack, false));
		$this->assertTrue(MultidimensionalArray::inArrayRecursive('4', $haystack, true));
	}

	/* ===================== Tri ===================== */

	public function testSortSimple(): void
	{
		$array = [
			['name' => 'Charlie', 'age' => 30],
			['name' => 'Alice', 'age' => 25],
			['name' => 'Bob', 'age' => 35],
		];

		// Tri par nom
		MultidimensionalArray::sort($array, [['name']]);
		$this->assertEquals('Alice', $array[0]['name']);
		$this->assertEquals('Bob', $array[1]['name']);
		$this->assertEquals('Charlie', $array[2]['name']);
	}

	public function testSortDescending(): void
	{
		$array = [
			['name' => 'Alice', 'age' => 25],
			['name' => 'Charlie', 'age' => 30],
			['name' => 'Bob', 'age' => 35],
		];

		// Tri décroissant par âge
		MultidimensionalArray::sort($array, [['age', false]]);
		$this->assertEquals(35, $array[0]['age']);
		$this->assertEquals(30, $array[1]['age']);
		$this->assertEquals(25, $array[2]['age']);
	}

	public function testSortMultipleCriteria(): void
	{
		$array = [
			['department' => 'IT', 'name' => 'Charlie', 'age' => 30],
			['department' => 'HR', 'name' => 'Alice', 'age' => 25],
			['department' => 'IT', 'name' => 'Bob', 'age' => 35],
			['department' => 'IT', 'name' => 'Alice', 'age' => 28],
		];

		// Tri par département puis par nom
		MultidimensionalArray::sort($array, [['department'], ['name']]);
		$this->assertEquals('HR', $array[0]['department']);
		$this->assertEquals('Alice', $array[1]['name']); // IT - Alice
		$this->assertEquals('Bob', $array[2]['name']);   // IT - Bob
		$this->assertEquals('Charlie', $array[3]['name']); // IT - Charlie
	}

	public function testSortNaturalOrder(): void
	{
		$array = [
			['file' => 'file10.txt'],
			['file' => 'file2.txt'],
			['file' => 'file1.txt'],
			['file' => 'file20.txt'],
		];

		// Tri ordre naturel
		MultidimensionalArray::sort($array, [['file', true, true]]);
		$this->assertEquals('file1.txt', $array[0]['file']);
		$this->assertEquals('file2.txt', $array[1]['file']);
		$this->assertEquals('file10.txt', $array[2]['file']);
		$this->assertEquals('file20.txt', $array[3]['file']);
	}

	public function testSortCaseSensitive(): void
	{
		$array = [
			['name' => 'bob'],
			['name' => 'Alice'],
			['name' => 'Charlie'],
		];

		// Tri sensible à la casse
		MultidimensionalArray::sort($array, [['name', true, false, true]]);
		$this->assertEquals('Alice', $array[0]['name']);
		$this->assertEquals('Charlie', $array[1]['name']);
		$this->assertEquals('bob', $array[2]['name']);
	}

	public function testSortEmptyArray(): void
	{
		$array = [];
		MultidimensionalArray::sort($array, [['name']]);
		$this->assertEquals([], $array);
	}

	public function testSortWithEmptyCriteria(): void
	{
		$array = [
			['name' => 'Bob'],
			['name' => 'Alice'],
		];

		MultidimensionalArray::sort($array, []);
		// Le tableau ne devrait pas être modifié
		$this->assertEquals('Bob', $array[0]['name']);
	}

	public function testKsortRecursive(): void
	{
		$array = [
			'z' => 1,
			'a' => [
				'y' => 2,
				'b' => 3,
			],
			'm' => 4,
		];

		MultidimensionalArray::ksortRecursive($array);

		$keys = array_keys($array);
		$this->assertEquals(['a', 'm', 'z'], $keys);

		$subKeys = array_keys($array['a']);
		$this->assertEquals(['b', 'y'], $subKeys);
	}

	public function testKsortRecursiveNumeric(): void
	{
		$array = [
			3 => 'three',
			1 => [
				2 => 'two',
				1 => 'one',
			],
			2 => 'two',
		];

		MultidimensionalArray::ksortRecursive($array, SORT_NUMERIC);

		$keys = array_keys($array);
		$this->assertEquals([1, 2, 3], $keys);

		$subKeys = array_keys($array[1]);
		$this->assertEquals([1, 2], $subKeys);
	}
}