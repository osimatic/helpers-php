<?php

namespace Tests\ArrayList;

use Osimatic\ArrayList\Arr;
use PHPUnit\Framework\TestCase;

enum TestEnum {
	case A;
	case B;
}

enum TestBackedEnum: string {
	case  A = 'a';
	case B = 'b';
}

final class ArrTest extends TestCase
{
	/* ===================== Array Creation & Generation ===================== */

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

	public function testWrapWithString(): void
	{
		$result = Arr::wrap('hello');
		$this->assertEquals(['hello'], $result);
	}

	public function testWrapWithArray(): void
	{
		$result = Arr::wrap(['hello', 'world']);
		$this->assertEquals(['hello', 'world'], $result);
	}

	public function testWrapWithNull(): void
	{
		$result = Arr::wrap(null);
		$this->assertEquals([], $result);
	}

	public function testWrapWithInteger(): void
	{
		$result = Arr::wrap(42);
		$this->assertEquals([42], $result);
	}

	/* ===================== Search & Filtering ===================== */

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

	public function testSearchByCallback(): void
	{
		$array = ['apple', 'banana', 'cherry'];

		$key = Arr::searchByCallback($array, fn($v) => strlen($v) > 5);
		$this->assertEquals(1, $key);

		$key = Arr::searchByCallback($array, fn($v) => strlen($v) > 10);
		$this->assertFalse($key);
	}

	public function testSearchByValues(): void
	{
		$array = ['apple', 'banana', 'cherry'];
		$values1 = ['grape', 'melon'];
		$values2 = ['grape', 'banana'];

		$this->assertFalse(Arr::searchByValues($array, $values1));
		$this->assertEquals(1, Arr::searchByValues($array, $values2));
	}

	public function testFirstWithoutCallback(): void
	{
		$numbers = [1, 2, 3, 4, 5];
		$result = Arr::first($numbers);
		$this->assertEquals(1, $result);
	}

	public function testFirstWithCallback(): void
	{
		$numbers = [1, 2, 3, 4, 5];
		$result = Arr::first($numbers, fn($n) => $n > 3);
		$this->assertEquals(4, $result);
	}

	public function testFirstWithEmptyArrayAndDefault(): void
	{
		$result = Arr::first([], default: 'none');
		$this->assertEquals('none', $result);
	}

	public function testFirstWithNoMatch(): void
	{
		$numbers = [1, 2, 3];
		$result = Arr::first($numbers, fn($n) => $n > 10, 'default');
		$this->assertEquals('default', $result);
	}

	public function testLastWithoutCallback(): void
	{
		$numbers = [1, 2, 3, 4, 5];
		$result = Arr::last($numbers);
		$this->assertEquals(5, $result);
	}

	public function testLastWithCallback(): void
	{
		$numbers = [1, 2, 3, 4, 5];
		$result = Arr::last($numbers, fn($n) => $n < 3);
		$this->assertEquals(2, $result);
	}

	public function testLastWithEmptyArrayAndDefault(): void
	{
		$result = Arr::last([], default: 'none');
		$this->assertEquals('none', $result);
	}

	public function testLastWithNoMatch(): void
	{
		$numbers = [1, 2, 3];
		$result = Arr::last($numbers, fn($n) => $n > 10, 'default');
		$this->assertEquals('default', $result);
	}

	/* ===================== Contains & Existence Checks ===================== */

	public function testInArrayCaseInsensitive(): void
	{
		$array = ['Apple', 'Banana', 'Orange'];

		$this->assertTrue(Arr::inArrayCaseInsensitive('apple', $array));
		$this->assertTrue(Arr::inArrayCaseInsensitive('BANANA', $array));
		$this->assertFalse(Arr::inArrayCaseInsensitive('grape', $array));
	}

	public function testContainsAny(): void
	{
		$haystack = ['apple', 'banana', 'orange'];
		$needles1 = ['grape', 'melon'];
		$needles2 = ['grape', 'apple'];

		$this->assertFalse(Arr::containsAny($needles1, $haystack));
		$this->assertTrue(Arr::containsAny($needles2, $haystack));
	}

	public function testContainsAll(): void
	{
		$haystack = ['apple', 'banana', 'orange', 'grape'];

		// Toutes les valeurs sont présentes
		$needles1 = ['apple', 'banana'];
		$this->assertTrue(Arr::containsAll($needles1, $haystack));

		// Une seule valeur présente
		$needles2 = ['apple', 'melon'];
		$this->assertFalse(Arr::containsAll($needles2, $haystack));

		// Aucune valeur présente
		$needles3 = ['melon', 'kiwi'];
		$this->assertFalse(Arr::containsAll($needles3, $haystack));

		// Tableau vide
		$needles4 = [];
		$this->assertTrue(Arr::containsAll($needles4, $haystack));
	}

	/* ===================== Array Transformation ===================== */

	public function testPrependToValues(): void
	{
		$array = ['apple', 'banana'];
		$result = Arr::prependToValues($array, 'fruit_');
		$this->assertEquals(['fruit_apple', 'fruit_banana'], $result);
	}

	public function testAppendToValues(): void
	{
		$array = ['test', 'demo'];
		$result = Arr::appendToValues($array, '.php');
		$this->assertEquals(['test.php', 'demo.php'], $result);
	}

	public function testFlattenWithDefaultDepth(): void
	{
		$array = [1, [2, [3, 4]], 5];
		$result = Arr::flatten($array);
		$this->assertEquals([1, 2, 3, 4, 5], $result);
	}

	public function testFlattenWithLimitedDepth(): void
	{
		$array = [1, [2, [3, 4]], 5];
		$result = Arr::flatten($array, 1);
		$this->assertEquals([1, 2, [3, 4], 5], $result);
	}

	public function testFlattenWithEmptyArray(): void
	{
		$result = Arr::flatten([]);
		$this->assertEquals([], $result);
	}

	public function testFlattenWithAlreadyFlatArray(): void
	{
		$array = [1, 2, 3, 4, 5];
		$result = Arr::flatten($array);
		$this->assertEquals([1, 2, 3, 4, 5], $result);
	}

	public function testPluckWithStringKey(): void
	{
		$users = [
			['id' => 1, 'name' => 'Alice'],
			['id' => 2, 'name' => 'Bob'],
			['id' => 3, 'name' => 'Charlie']
		];
		$result = Arr::pluck($users, 'name');
		$this->assertEquals(['Alice', 'Bob', 'Charlie'], $result);
	}

	public function testPluckWithCallback(): void
	{
		$users = [
			['id' => 1, 'name' => 'Alice'],
			['id' => 2, 'name' => 'Bob']
		];
		$result = Arr::pluck($users, fn($u) => strtoupper($u['name']));
		$this->assertEquals(['ALICE', 'BOB'], $result);
	}

	public function testPluckWithIndexBy(): void
	{
		$users = [
			['id' => 1, 'name' => 'Alice'],
			['id' => 2, 'name' => 'Bob']
		];
		$result = Arr::pluck($users, 'name', 'id');
		$this->assertEquals([1 => 'Alice', 2 => 'Bob'], $result);
	}

	public function testPluckWithNonExistentKey(): void
	{
		$users = [
			['id' => 1, 'name' => 'Alice'],
			['id' => 2, 'name' => 'Bob']
		];
		$result = Arr::pluck($users, 'age');
		$this->assertEquals([null, null], $result);
	}

	public function testKeyByStringKey(): void
	{
		$users = [
			['id' => 1, 'name' => 'Alice'],
			['id' => 2, 'name' => 'Bob']
		];
		$result = Arr::keyBy($users, 'id');
		$this->assertEquals([
			1 => ['id' => 1, 'name' => 'Alice'],
			2 => ['id' => 2, 'name' => 'Bob']
		], $result);
	}

	public function testKeyByCallback(): void
	{
		$users = [
			['id' => 1, 'name' => 'Alice'],
			['id' => 2, 'name' => 'Bob']
		];
		$result = Arr::keyBy($users, fn($u) => 'user_' . $u['id']);
		$this->assertEquals([
			'user_1' => ['id' => 1, 'name' => 'Alice'],
			'user_2' => ['id' => 2, 'name' => 'Bob']
		], $result);
	}

	public function testKeyByWithDuplicateKeys(): void
	{
		$users = [
			['id' => 1, 'name' => 'Alice'],
			['id' => 1, 'name' => 'Bob']
		];
		$result = Arr::keyBy($users, 'id');
		// Le dernier écrase le premier
		$this->assertEquals([
			1 => ['id' => 1, 'name' => 'Bob']
		], $result);
	}

	public function testUnwrapWithSingleElement(): void
	{
		$result = Arr::unwrap(['hello']);
		$this->assertEquals('hello', $result);
	}

	public function testUnwrapWithMultipleElements(): void
	{
		$result = Arr::unwrap(['hello', 'world']);
		$this->assertEquals(['hello', 'world'], $result);
	}

	public function testUnwrapWithEmptyArray(): void
	{
		$result = Arr::unwrap([]);
		$this->assertEquals([], $result);
	}

	/* ===================== Grouping & Partitioning ===================== */

	public function testGroupByStringKey(): void
	{
		$users = [
			['name' => 'Alice', 'role' => 'admin'],
			['name' => 'Bob', 'role' => 'user'],
			['name' => 'Charlie', 'role' => 'admin']
		];
		$result = Arr::groupBy($users, 'role');
		$this->assertEquals([
			'admin' => [
				['name' => 'Alice', 'role' => 'admin'],
				['name' => 'Charlie', 'role' => 'admin']
			],
			'user' => [
				['name' => 'Bob', 'role' => 'user']
			]
		], $result);
	}

	public function testGroupByCallback(): void
	{
		$numbers = [1, 2, 3, 4, 5, 6];
		$result = Arr::groupBy($numbers, fn($n) => $n % 2 === 0 ? 'even' : 'odd');
		$this->assertEquals([
			'odd' => [1, 3, 5],
			'even' => [2, 4, 6]
		], $result);
	}

	public function testGroupByWithEmptyArray(): void
	{
		$result = Arr::groupBy([], 'key');
		$this->assertEquals([], $result);
	}

	public function testGroupByWithNonExistentKey(): void
	{
		$users = [
			['name' => 'Alice'],
			['name' => 'Bob']
		];
		$result = Arr::groupBy($users, 'role');
		$this->assertArrayHasKey('', $result);
		$this->assertCount(2, $result['']);
	}

	public function testPartitionWithEvenOddNumbers(): void
	{
		$numbers = [1, 2, 3, 4, 5, 6];
		[$even, $odd] = Arr::partition($numbers, fn($n) => $n % 2 === 0);
		$this->assertEquals([2, 4, 6], $even);
		$this->assertEquals([1, 3, 5], $odd);
	}

	public function testPartitionWithAllTrue(): void
	{
		$numbers = [2, 4, 6];
		[$even, $odd] = Arr::partition($numbers, fn($n) => $n % 2 === 0);
		$this->assertEquals([2, 4, 6], $even);
		$this->assertEquals([], $odd);
	}

	public function testPartitionWithAllFalse(): void
	{
		$numbers = [1, 3, 5];
		[$even, $odd] = Arr::partition($numbers, fn($n) => $n % 2 === 0);
		$this->assertEquals([], $even);
		$this->assertEquals([1, 3, 5], $odd);
	}

	public function testPartitionWithEmptyArray(): void
	{
		[$even, $odd] = Arr::partition([], fn($n) => $n % 2 === 0);
		$this->assertEquals([], $even);
		$this->assertEquals([], $odd);
	}

	/* ===================== Random Selection ===================== */

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

	/* ===================== Parsing & Type Conversion ===================== */

	public function testMultiExplode(): void
	{
		$result = Arr::multiExplode([',', ';', '|'], 'a,b;c|d');
		$this->assertEquals(['a', 'b', 'c', 'd'], $result);

		$result = Arr::multiExplode(['-', '_'], 'hello-world_test');
		$this->assertEquals(['hello', 'world', 'test'], $result);
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

	public function testParseEnumList(): void
	{
		// parseEnumList requires a backed enum with tryFrom() method
		$values = ['a', 'b', 'invalid'];
		$result = Arr::parseEnumList($values, TestBackedEnum::class);
		$this->assertCount(2, $result);
		$this->assertContains(TestBackedEnum::A, $result);
		$this->assertContains(TestBackedEnum::B, $result);
	}

	public function testParseListByCallback(): void
	{
		$values = ['1', '2', 'invalid', '3'];
		$result = Arr::parseListByCallback($values, function($v) {
			$int = (int)$v;
			return $int > 0 ? $int : null;
		});
		$this->assertEquals([1, 2, 3], $result);
	}

	/* ===================== Uniqueness & Deduplication ===================== */

	public function testUniqueEnums(): void
	{
		$values = [TestEnum::A, TestEnum::B, TestEnum::A, TestEnum::B];
		$result = Arr::uniqueEnums($values);

		$this->assertCount(2, $result);
		$this->assertContains(TestEnum::A, $result);
		$this->assertContains(TestEnum::B, $result);
	}

	public function testUniqueByCallback(): void
	{
		$objects = [
			(object)['id' => 1, 'name' => 'John'],
			(object)['id' => 2, 'name' => 'Jane'],
			(object)['id' => 1, 'name' => 'John Duplicate'],
		];

		$result = Arr::uniqueByCallback($objects, fn($obj) => $obj->id);
		$this->assertCount(2, $result);
	}

	/* ===================== Extraction & Selection ===================== */

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

	/* ===================== Mathematical Operations ===================== */

	public function testCumulativeSum(): void
	{
		$array = [1, 2, 3, 4, 5];
		$result = Arr::cumulativeSum($array);
		$this->assertEquals([1, 3, 6, 10, 15], $result);

		$array = [10, -5, 3];
		$result = Arr::cumulativeSum($array);
		$this->assertEquals([10, 5, 8], $result);
	}

	/* ===================== Counting ===================== */

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

	/* ===================== Key Manipulation ===================== */

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

	/* ===================== Comparison & Utilities ===================== */

	public function testCompareValueWithNumericValuesEqual(): void
	{
		$result = Arr::compareValue(5, 5);
		$this->assertEquals(0, $result);
	}

	public function testCompareValueWithNumericValuesFirstLess(): void
	{
		$result = Arr::compareValue(3, 7);
		$this->assertEquals(-1, $result);
	}

	public function testCompareValueWithNumericValuesFirstGreater(): void
	{
		$result = Arr::compareValue(10, 2);
		$this->assertEquals(1, $result);
	}

	public function testCompareValueWithFloatValues(): void
	{
		$result1 = Arr::compareValue(3.14, 3.14);
		$result2 = Arr::compareValue(2.5, 3.5);
		$result3 = Arr::compareValue(5.5, 1.1);

		$this->assertEquals(0, $result1);
		$this->assertEquals(-1, $result2);
		$this->assertEquals(1, $result3);
	}

	public function testCompareValueWithStringsCaseInsensitive(): void
	{
		$result1 = Arr::compareValue('apple', 'APPLE', false, false);
		$result2 = Arr::compareValue('banana', 'cherry', false, false);
		$result3 = Arr::compareValue('zebra', 'ant', false, false);

		$this->assertEquals(0, $result1);
		$this->assertEquals(-1, $result2);
		$this->assertEquals(1, $result3);
	}

	public function testCompareValueWithStringsCaseSensitive(): void
	{
		$result1 = Arr::compareValue('apple', 'apple', false, true);
		$result2 = Arr::compareValue('apple', 'Apple', false, true);
		$result3 = Arr::compareValue('Apple', 'apple', false, true);

		$this->assertEquals(0, $result1);
		$this->assertEquals(1, $result2); // 'apple' > 'Apple' (lowercase > uppercase in ASCII)
		$this->assertEquals(-1, $result3);
	}

	public function testCompareValueWithNaturalOrderCaseInsensitive(): void
	{
		$result1 = Arr::compareValue('file2.txt', 'file10.txt', true, false);
		$result2 = Arr::compareValue('file10.txt', 'file2.txt', true, false);
		$result3 = Arr::compareValue('image1', 'IMAGE1', true, false);

		$this->assertEquals(-1, $result1); // natural order: 2 < 10
		$this->assertEquals(1, $result2);
		$this->assertEquals(0, $result3);
	}

	public function testCompareValueWithNaturalOrderCaseSensitive(): void
	{
		$result1 = Arr::compareValue('file2.txt', 'file10.txt', true, true);
		$result2 = Arr::compareValue('File2', 'file2', true, true);

		$this->assertEquals(-1, $result1); // natural order: 2 < 10
		$this->assertNotEquals(0, $result2); // case-sensitive, so different
	}

	public function testCompareValueWithDefaultParameters(): void
	{
		// Default: case-insensitive string comparison
		$result1 = Arr::compareValue('abc', 'ABC');
		$result2 = Arr::compareValue('abc', 'def');
		$result3 = Arr::compareValue('xyz', 'abc');

		$this->assertEquals(0, $result1);
		$this->assertEquals(-1, $result2);
		$this->assertEquals(1, $result3);
	}

	public function testCompareValueWithEmptyStrings(): void
	{
		$result1 = Arr::compareValue('', '');
		$result2 = Arr::compareValue('', 'a');
		$result3 = Arr::compareValue('a', '');

		$this->assertEquals(0, $result1);
		$this->assertEquals(-1, $result2);
		$this->assertEquals(1, $result3);
	}

	public function testCompareValueWithMixedTypes(): void
	{
		// When both are numeric strings, they should be compared as numbers
		$result = Arr::compareValue('10', '2');
		$this->assertEquals(1, $result); // 10 > 2 (numeric comparison)
	}

	/* ===================== Permutations & Combinations ===================== */

	public function testGetPermutationsWithSingleItem(): void
	{
		$items = ['hello'];
		$result = Arr::getPermutations($items);
		$this->assertEquals(['hello'], $result);
	}

	public function testGetPermutationsWithEmptyArray(): void
	{
		$items = [];
		$result = Arr::getPermutations($items);
		$this->assertEquals([], $result);
	}

	public function testGetPermutationsWithTwoItems(): void
	{
		$items = ['hello', 'world'];
		$result = Arr::getPermutations($items);

		$this->assertCount(2, $result);
		$this->assertContains('hello world', $result);
		$this->assertContains('world hello', $result);
	}

	public function testGetPermutationsWithThreeItems(): void
	{
		$items = ['a', 'b', 'c'];
		$result = Arr::getPermutations($items);

		// 3! = 6 permutations
		$this->assertCount(6, $result);

		$expected = [
			'a b c',
			'a c b',
			'b a c',
			'b c a',
			'c a b',
			'c b a'
		];

		foreach ($expected as $permutation) {
			$this->assertContains($permutation, $result);
		}
	}

	public function testGetPermutationsWithFourItems(): void
	{
		$items = ['un', 'deux', 'trois', 'quatre'];
		$result = Arr::getPermutations($items);

		// 4! = 24 permutations
		$this->assertCount(24, $result);

		// Check specific permutations
		$this->assertContains('un deux trois quatre', $result);
		$this->assertContains('quatre trois deux un', $result);
		$this->assertContains('deux un quatre trois', $result);
	}

	public function testGetPermutationsPreservesItemsIntegrity(): void
	{
		$items = ['first', 'second', 'third'];
		$result = Arr::getPermutations($items);

		// Verify that each permutation contains all items
		foreach ($result as $permutation) {
			$this->assertStringContainsString('first', $permutation);
			$this->assertStringContainsString('second', $permutation);
			$this->assertStringContainsString('third', $permutation);

			// Verify exactly 2 spaces (3 items)
			$this->assertEquals(2, substr_count($permutation, ' '));
		}
	}

	public function testGetPermutationsWithSpecialCharacters(): void
	{
		$items = ['hello!', 'world?', 'test@'];
		$result = Arr::getPermutations($items);

		$this->assertCount(6, $result);
		$this->assertContains('hello! world? test@', $result);
	}

	public function testGetPermutationsWithNumbers(): void
	{
		$items = ['1', '2', '3'];
		$result = Arr::getPermutations($items);

		$this->assertCount(6, $result);
		$this->assertContains('1 2 3', $result);
		$this->assertContains('3 2 1', $result);
	}

	public function testGetCombinationsWithTwoFromThree(): void
	{
		$items = ['a', 'b', 'c'];
		$result = Arr::getCombinations($items, 2);

		// C(3,2) = 3 combinations
		$this->assertCount(3, $result);
		$this->assertContains('a b', $result);
		$this->assertContains('a c', $result);
		$this->assertContains('b c', $result);
		// Should NOT contain reverse orders (not permutations)
		$this->assertNotContains('b a', $result);
	}

	public function testGetCombinationsWithThreeFromFour(): void
	{
		$items = ['1', '2', '3', '4'];
		$result = Arr::getCombinations($items, 3);

		// C(4,3) = 4 combinations
		$this->assertCount(4, $result);
		$this->assertContains('1 2 3', $result);
		$this->assertContains('1 2 4', $result);
		$this->assertContains('1 3 4', $result);
		$this->assertContains('2 3 4', $result);
	}

	public function testGetCombinationsWithSizeOne(): void
	{
		$items = ['a', 'b', 'c'];
		$result = Arr::getCombinations($items, 1);

		$this->assertCount(3, $result);
		$this->assertEquals(['a', 'b', 'c'], $result);
	}

	public function testGetCombinationsWithFullSize(): void
	{
		$items = ['x', 'y', 'z'];
		$result = Arr::getCombinations($items, 3);

		// Only one way to choose all elements
		$this->assertCount(1, $result);
		$this->assertEquals(['x y z'], $result);
	}

	public function testGetCombinationsWithInvalidSize(): void
	{
		$items = ['a', 'b', 'c'];

		// Size too large
		$result = Arr::getCombinations($items, 4);
		$this->assertEquals([], $result);

		// Size zero
		$result = Arr::getCombinations($items, 0);
		$this->assertEquals([], $result);

		// Negative size
		$result = Arr::getCombinations($items, -1);
		$this->assertEquals([], $result);
	}

	public function testGetCombinationsWithEmptyArray(): void
	{
		$result = Arr::getCombinations([], 1);
		$this->assertEquals([], $result);
	}

	public function testGetPowerSetWithTwoElements(): void
	{
		$items = ['a', 'b'];
		$result = Arr::getPowerSet($items);

		// 2^2 = 4 subsets
		$this->assertCount(4, $result);
		$this->assertContains([], $result);
		$this->assertContains(['a'], $result);
		$this->assertContains(['b'], $result);
		$this->assertContains(['a', 'b'], $result);
	}

	public function testGetPowerSetWithThreeElements(): void
	{
		$items = ['x', 'y', 'z'];
		$result = Arr::getPowerSet($items);

		// 2^3 = 8 subsets
		$this->assertCount(8, $result);
		$this->assertContains([], $result);
		$this->assertContains(['x'], $result);
		$this->assertContains(['y'], $result);
		$this->assertContains(['z'], $result);
		$this->assertContains(['x', 'y'], $result);
		$this->assertContains(['x', 'z'], $result);
		$this->assertContains(['y', 'z'], $result);
		$this->assertContains(['x', 'y', 'z'], $result);
	}

	public function testGetPowerSetWithoutEmpty(): void
	{
		$items = ['a', 'b'];
		$result = Arr::getPowerSet($items, false);

		// 2^2 - 1 = 3 subsets (excluding empty set)
		$this->assertCount(3, $result);
		$this->assertNotContains([], $result);
		$this->assertContains(['a'], $result);
		$this->assertContains(['b'], $result);
		$this->assertContains(['a', 'b'], $result);
	}

	public function testGetPowerSetWithSingleElement(): void
	{
		$items = ['x'];
		$result = Arr::getPowerSet($items);

		// 2^1 = 2 subsets
		$this->assertCount(2, $result);
		$this->assertContains([], $result);
		$this->assertContains(['x'], $result);
	}

	public function testGetPowerSetWithEmptyArray(): void
	{
		$result = Arr::getPowerSet([]);

		// 2^0 = 1 subset (only empty set)
		$this->assertCount(1, $result);
		$this->assertEquals([[]], $result);
	}

	public function testGetPowerSetPreservesValues(): void
	{
		$items = ['first', 'second'];
		$result = Arr::getPowerSet($items);

		// Verify each subset contains only original values
		foreach ($result as $subset) {
			foreach ($subset as $value) {
				$this->assertContains($value, $items);
			}
		}
	}

	/* ===================== DEPRECATED METHODS ===================== */

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

	public function testArrayCumulativeSum(): void
	{
		$array = [1, 2, 3, 4, 5];
		$result = Arr::array_cumulative_sum($array);
		$this->assertEquals([1, 3, 6, 10, 15], $result);

		$array = [10, -5, 3];
		$result = Arr::array_cumulative_sum($array);
		$this->assertEquals([10, 5, 8], $result);
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

	public function testInArrayI(): void
	{
		$array = ['Apple', 'Banana', 'Orange'];

		$this->assertTrue(Arr::in_array_i('apple', $array));
		$this->assertTrue(Arr::in_array_i('BANANA', $array));
		$this->assertFalse(Arr::in_array_i('grape', $array));
	}

	public function testInArrayAny(): void
	{
		$haystack = ['apple', 'banana', 'orange'];
		$needles1 = ['grape', 'melon'];
		$needles2 = ['grape', 'apple'];

		$this->assertFalse(Arr::in_array_any($needles1, $haystack));
		$this->assertTrue(Arr::in_array_any($needles2, $haystack));
	}

	public function testInArrayAll(): void
	{
		$haystack = ['apple', 'banana', 'orange', 'grape'];

		// Toutes les valeurs sont présentes
		$needles1 = ['apple', 'banana'];
		$this->assertTrue(Arr::in_array_all($needles1, $haystack));

		// Une seule valeur présente
		$needles2 = ['apple', 'melon'];
		$this->assertFalse(Arr::in_array_all($needles2, $haystack));

		// Aucune valeur présente
		$needles3 = ['melon', 'kiwi'];
		$this->assertFalse(Arr::in_array_all($needles3, $haystack));

		// Tableau vide
		$needles4 = [];
		$this->assertTrue(Arr::in_array_all($needles4, $haystack));
	}

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

	public function testArrayValuesRecursive(): void
	{
		$array = ['a' => 1, 0 => 2, 'b' => ['c' => 3, 0 => 4]];
		$result = Arr::arrayValuesRecursive($array);

		// New behavior: reindexRecursive now reindexes ALL keys by default (not just numeric)
		$this->assertEquals([1, 2, [3, 4]], $result);
	}

	public function testMapRecursive(): void
	{
		$array = [1, 2, [3, 4, [5, 6]]];
		$result = Arr::mapRecursive($array, fn($v) => $v * 2);
		$this->assertEquals([2, 4, [6, 8, [10, 12]]], $result);
	}
}