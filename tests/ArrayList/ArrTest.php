<?php

namespace Tests\ArrayList;

use Osimatic\ArrayList\Arr;
use PHPUnit\Framework\TestCase;

enum TestEnum {
	case A;
	case B;
}

final class ArrTest extends TestCase
{
	/* ===================== Comptage ===================== */

	public function testCountMultiArrays(): void
	{
		$array1 = [1, 2, 3];
		$array2 = [4, 5];
		$array3 = [6, 7, 8, 9];

		$count = Arr::countMultiArrays($array1, $array2, $array3);
		$this->assertEquals(9, $count);

		// Test avec un seul tableau
		$this->assertEquals(3, Arr::countMultiArrays($array1));

		// Test avec tableaux vides
		$this->assertEquals(0, Arr::countMultiArrays([], []));
	}

	/* ===================== Recherche ===================== */

	public function testGetClosestDefault(): void
	{
		$array = [1, 5, 10, 15, 20];

		$this->assertEquals(10, Arr::getClosest(9, $array));
		$this->assertEquals(15, Arr::getClosest(13, $array));
		$this->assertEquals(1, Arr::getClosest(0, $array));
		$this->assertEquals(20, Arr::getClosest(25, $array));
	}

	public function testGetClosestHigher(): void
	{
		$array = [1, 5, 10, 15, 20];

		$this->assertEquals(10, Arr::getClosest(9, $array, 'higher'));
		$this->assertEquals(15, Arr::getClosest(13, $array, 'higher'));
		$this->assertEquals(20, Arr::getClosest(20, $array, 'higher'));
		$this->assertNull(Arr::getClosest(25, $array, 'higher'));
	}

	public function testGetClosestLower(): void
	{
		$array = [1, 5, 10, 15, 20];

		$this->assertEquals(5, Arr::getClosest(9, $array, 'lower'));
		$this->assertEquals(10, Arr::getClosest(13, $array, 'lower'));
		$this->assertEquals(1, Arr::getClosest(1, $array, 'lower'));
		$this->assertNull(Arr::getClosest(0, $array, 'lower'));
	}

	public function testGetListValuesByListKeys(): void
	{
		$array = ['name' => 'John', 'age' => 30, 'city' => 'Paris'];
		$keys = ['name', 'country', 'age'];

		$result = Arr::getListValuesByListKeys($array, $keys);
		$this->assertEquals(['name' => 'John', 'country' => null, 'age' => 30], $result);

		// Test avec valeur par défaut
		$result = Arr::getListValuesByListKeys($array, $keys, 'N/A');
		$this->assertEquals(['name' => 'John', 'country' => 'N/A', 'age' => 30], $result);
	}

	public function testMultiExplode(): void
	{
		$result = Arr::multiExplode([',', ';', '|'], 'a,b;c|d');
		$this->assertEquals(['a', 'b', 'c', 'd'], $result);

		$result = Arr::multiExplode(['-', '_'], 'hello-world_test');
		$this->assertEquals(['hello', 'world', 'test'], $result);
	}

	public function testArrayValuesRecursive(): void
	{
		$array = ['a' => 1, 0 => 2, 'b' => ['c' => 3, 0 => 4]];
		$result = Arr::arrayValuesRecursive($array);

		$this->assertEquals(['a' => 1, 2, 'b' => ['c' => 3, 4]], $result);
	}

	public function testGetRandomValue(): void
	{
		$array = ['apple', 'banana', 'orange'];
		$value = Arr::getRandomValue($array);

		$this->assertContains($value, $array);
	}

	public function testGetRandomKey(): void
	{
		$array = ['fruit' => 'apple', 'color' => 'red', 'size' => 'medium'];
		$key = Arr::getRandomKey($array);

		$this->assertContains($key, ['fruit', 'color', 'size']);
	}

	public function testInArrayI(): void
	{
		$array = ['Apple', 'Banana', 'Orange'];

		$this->assertTrue(Arr::in_array_i('apple', $array));
		$this->assertTrue(Arr::in_array_i('BANANA', $array));
		$this->assertFalse(Arr::in_array_i('grape', $array));
	}

	public function testInArrayValues(): void
	{
		$haystack = ['apple', 'banana', 'orange'];
		$needles1 = ['grape', 'melon'];
		$needles2 = ['grape', 'apple'];

		$this->assertFalse(Arr::in_array_values($needles1, $haystack));
		$this->assertTrue(Arr::in_array_values($needles2, $haystack));
	}

	public function testArraySearchFunc(): void
	{
		$array = ['apple', 'banana', 'cherry'];

		$key = Arr::array_search_func($array, fn($v) => strlen($v) > 5);
		$this->assertEquals(1, $key);

		$key = Arr::array_search_func($array, fn($v) => strlen($v) > 10);
		$this->assertFalse($key);
	}

	public function testArraySearchValues(): void
	{
		$array = ['apple', 'banana', 'cherry'];
		$values1 = ['grape', 'melon'];
		$values2 = ['grape', 'banana'];

		$this->assertFalse(Arr::array_search_values($array, $values1));
		$this->assertEquals(1, Arr::array_search_values($array, $values2));
	}

	public function testEnumArrayUnique(): void
	{
		$values = [TestEnum::A, TestEnum::B, TestEnum::A, TestEnum::B];
		$result = Arr::enum_array_unique($values);

		$this->assertCount(2, $result);
		$this->assertContains(TestEnum::A, $result);
		$this->assertContains(TestEnum::B, $result);
	}

	public function testCollectionArrayUnique(): void
	{
		$objects = [
			(object)['id' => 1, 'name' => 'John'],
			(object)['id' => 2, 'name' => 'Jane'],
			(object)['id' => 1, 'name' => 'John Duplicate'],
		];

		$result = Arr::collection_array_unique($objects, fn($obj) => $obj->id);
		$this->assertCount(2, $result);
	}

	/* ===================== Génération de valeur ===================== */

	public function testGetArrayWithSameValues(): void
	{
		$result = Arr::getArrayWithSameValues('test', 5);
		$this->assertEquals(['test', 'test', 'test', 'test', 'test'], $result);

		$result = Arr::getArrayWithSameValues(42, 3);
		$this->assertEquals([42, 42, 42], $result);
	}

	public function testGetArrayWithNumericValues(): void
	{
		$result = Arr::getArrayWithNumericValues(1, 5);
		$this->assertEquals([1, 2, 3, 4, 5], $result);

		$result = Arr::getArrayWithNumericValues(0, 10, 2);
		$this->assertEquals([0, 2, 4, 6, 8, 10], $result);
	}

	public function testGetArrayWithNbNumericValues(): void
	{
		$result = Arr::getArrayWithNbNumericValues(5);
		$this->assertEquals([1, 2, 3, 4, 5], $result);

		$result = Arr::getArrayWithNbNumericValues(4, 10, 5);
		$this->assertEquals([10, 15, 20, 25], $result);
	}

	public function testString2KeyedArray(): void
	{
		$result = Arr::string2KeyedArray('key1=>value1,key2=>value2');
		$this->assertEquals(['key1' => 'value1', 'key2' => 'value2'], $result);

		$result = Arr::string2KeyedArray('a:1;b:2', ';', ':');
		$this->assertEquals(['a' => '1', 'b' => '2'], $result);

		// Test sans délimiteur key/value
		$result = Arr::string2KeyedArray('value1,value2,value3');
		$this->assertEquals(['value1', 'value2', 'value3'], $result);
	}

	/* ===================== Modification de valeur ===================== */

	public function testConcatenateStringAtBeginningOnValues(): void
	{
		$array = ['apple', 'banana'];
		$result = Arr::concatenateStringAtBeginningOnValues($array, 'fruit_');
		$this->assertEquals(['fruit_apple', 'fruit_banana'], $result);
	}

	public function testConcatenateStringAtEndOnValues(): void
	{
		$array = ['test', 'demo'];
		$result = Arr::concatenateStringAtEndOnValues($array, '.php');
		$this->assertEquals(['test.php', 'demo.php'], $result);
	}

	/* ===================== Suppression de valeur ===================== */

	public function testDeleteListKeys(): void
	{
		$array = ['name' => 'John', 'age' => 30, 'city' => 'Paris', 'country' => 'France'];
		$result = Arr::deleteListKeys($array, ['age', 'country']);
		$this->assertEquals(['name' => 'John', 'city' => 'Paris'], $result);
	}

	public function testDeleteKey(): void
	{
		$array = ['name' => 'John', 'age' => 30];
		$result = Arr::deleteKey($array, 'age');
		$this->assertEquals(['name' => 'John'], $result);
	}

	/* ===================== Opération sur les valeurs ===================== */

	public function testArrayCumulativeSum(): void
	{
		$array = [1, 2, 3, 4, 5];
		$result = Arr::array_cumulative_sum($array);
		$this->assertEquals([1, 3, 6, 10, 15], $result);

		$array = [10, -5, 3];
		$result = Arr::array_cumulative_sum($array);
		$this->assertEquals([10, 5, 8], $result);
	}

	/* ===================== Map ===================== */

	public function testMapRecursive(): void
	{
		$array = [1, 2, [3, 4, [5, 6]]];
		$result = Arr::mapRecursive($array, fn($v) => $v * 2);
		$this->assertEquals([2, 4, [6, 8, [10, 12]]], $result);
	}

	/* ===================== Tri ===================== */

	public function testQuickSort(): void
	{
		$array = [3, 1, 4, 1, 5, 9, 2, 6];
		$result = Arr::quickSort($array);
		$this->assertEquals([1, 1, 2, 3, 4, 5, 6, 9], array_values($result));
	}

	public function testCompareValue(): void
	{
		// Numérique
		$this->assertLessThan(0, Arr::compareValue(1, 2));
		$this->assertGreaterThan(0, Arr::compareValue(2, 1));
		$this->assertEquals(0, Arr::compareValue(5, 5));

		// Strings
		$this->assertLessThan(0, Arr::compareValue('a', 'b'));
		$this->assertGreaterThan(0, Arr::compareValue('b', 'a'));

		// Case sensitive
		$this->assertNotEquals(0, Arr::compareValue('A', 'a', false, true));
		$this->assertEquals(0, Arr::compareValue('A', 'a', false, false));

		// Natural order
		$this->assertLessThan(0, Arr::compareValue('file2', 'file10', true));
		$this->assertGreaterThan(0, Arr::compareValue('file2', 'file10', false));
	}
}