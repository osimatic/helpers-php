<?php

namespace Tests\ArrayList;

use Osimatic\ArrayList\MultidimensionalArray;
use PHPUnit\Framework\TestCase;

final class MultidimensionalArrayTest extends TestCase
{
	/* ===================== Counting & Analysis ===================== */

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

	public function testDepth(): void
	{
		$array = [1, 2, 3];
		$this->assertEquals(1, MultidimensionalArray::depth($array));

		$array = [1, [2, 3]];
		$this->assertEquals(2, MultidimensionalArray::depth($array));

		$array = [1, [2, [3, [4, 5]]]];
		$this->assertEquals(4, MultidimensionalArray::depth($array));

		$array = [[['nested' => 'value']]];
		$this->assertEquals(3, MultidimensionalArray::depth($array));
	}

	public function testDepthWithEmptyArray(): void
	{
		$array = [];
		$this->assertEquals(0, MultidimensionalArray::depth($array));
	}

	public function testIsMultidimensional(): void
	{
		$array = [1, [2, 3]];
		$this->assertTrue(MultidimensionalArray::isMultidimensional($array));

		$array = [1, 2, 3];
		$this->assertFalse(MultidimensionalArray::isMultidimensional($array));

		$array = [];
		$this->assertFalse(MultidimensionalArray::isMultidimensional($array));

		$array = ['key' => ['nested' => 'value']];
		$this->assertTrue(MultidimensionalArray::isMultidimensional($array));
	}

	public function testCountWithMixedTypes(): void
	{
		$array = [1, 'string', null, false, [2, 3], ['nested' => [4, 5]]];
		$this->assertEquals(8, MultidimensionalArray::count($array)); // 1, 'string', null, false, 2, 3, 4, 5
	}

	public function testDepthWithMixedNesting(): void
	{
		$array = [
			1,
			[2],
			[[3]],
			[[[4]]],
		];
		$this->assertEquals(4, MultidimensionalArray::depth($array));
	}

	/* ===================== Transformation ===================== */

	public function testReindexRecursive(): void
	{
		$array = [
			'a' => 1,
			'b' => [
				'c' => 2,
				'd' => 3,
			],
			'e' => 4,
		];

		$result = MultidimensionalArray::reindexRecursive($array);

		$this->assertEquals([1, [2, 3], 4], $result);
		$this->assertEquals([0, 1, 2], array_keys($result));
		$this->assertEquals([0, 1], array_keys($result[1]));
	}

	public function testReindexRecursiveWithOnlyNumeric(): void
	{
		$array = [
			0 => 'first',
			'name' => 'John',
			2 => 'third',
			'age' => [
				0 => 30,
				'verified' => true,
				1 => 'active'
			]
		];

		$result = MultidimensionalArray::reindexRecursive($array, true);

		// Numeric keys should be reindexed
		$this->assertEquals('first', $result[0]);
		$this->assertEquals('third', $result[1]);

		// Non-numeric keys should be preserved
		$this->assertArrayHasKey('name', $result);
		$this->assertEquals('John', $result['name']);
		$this->assertArrayHasKey('age', $result);

		// Check nested array
		$this->assertEquals(30, $result['age'][0]);
		$this->assertEquals('active', $result['age'][1]);
		$this->assertArrayHasKey('verified', $result['age']);
		$this->assertTrue($result['age']['verified']);
	}

	public function testMapRecursive(): void
	{
		$array = [1, [2, 3], 4];
		$result = MultidimensionalArray::mapRecursive($array, fn($value) => $value * 2);
		$this->assertEquals([2, [4, 6], 8], $result);

		$array = ['a' => 'hello', 'b' => ['c' => 'world']];
		$result = MultidimensionalArray::mapRecursive($array, fn($value) => strtoupper($value));
		$this->assertEquals(['a' => 'HELLO', 'b' => ['c' => 'WORLD']], $result);
	}

	public function testFilterRecursive(): void
	{
		$array = [1, 2, [3, 4, [5, 6]], 7];
		$result = MultidimensionalArray::filterRecursive($array, fn($value) => $value > 3, false);

		// Without structure, returns flat array of matching values
		$this->assertEquals([4, 5, 6, 7], $result);
	}

	public function testFilterRecursiveWithStructure(): void
	{
		$array = [1, 2, [3, 4, [5, 6]], 7];
		$result = MultidimensionalArray::filterRecursive($array, fn($value) => $value > 3, true);

		// With structure preserved
		$this->assertArrayHasKey(2, $result); // Nested array at index 2
		$this->assertArrayHasKey(3, $result); // Value 7 at index 3
	}

	public function testFilterRecursiveWithEmptyResult(): void
	{
		$array = [1, 2, [3, 4]];
		$result = MultidimensionalArray::filterRecursive($array, fn($value) => $value > 10);
		$this->assertEquals([], $result);
	}

	public function testFlatten(): void
	{
		$array = [1, [2, [3, 4]], 5];
		$result = MultidimensionalArray::flatten($array);
		$this->assertEquals([1, 2, 3, 4, 5], $result);

		$array = ['a' => 1, 'b' => ['c' => 2, 'd' => 3]];
		$result = MultidimensionalArray::flatten($array);
		$this->assertEquals([1, 2, 3], $result);
	}

	public function testFlattenWithDepthLimit(): void
	{
		$array = [1, [2, [3, 4]], 5];
		$result = MultidimensionalArray::flatten($array, 1);
		$this->assertEquals([1, 2, [3, 4], 5], $result);

		$result = MultidimensionalArray::flatten($array, 2);
		$this->assertEquals([1, 2, 3, 4, 5], $result);
	}

	public function testFlattenWithEmptyArray(): void
	{
		$array = [];
		$result = MultidimensionalArray::flatten($array);
		$this->assertEquals([], $result);
	}

	public function testFlattenWithFlatArray(): void
	{
		$array = [1, 2, 3, 4, 5];
		$result = MultidimensionalArray::flatten($array);
		$this->assertEquals([1, 2, 3, 4, 5], $result);
	}

	public function testFlattenWithDepthZero(): void
	{
		$array = [1, [2, [3, 4]]];
		$result = MultidimensionalArray::flatten($array, 0);
		$this->assertEquals([1, [2, [3, 4]]], $result);
	}

	public function testFilterRecursiveRemovesEmptyNestedArrays(): void
	{
		$array = [
			1,
			[2, [99]], // Inner array will be empty after filtering
			3,
		];

		$result = MultidimensionalArray::filterRecursive($array, fn($v) => $v < 10, true);

		// The nested array [2] remains even though the inner [99] is filtered out
		$this->assertCount(3, $result); // 1, [2], and 3 remain
		$this->assertEquals(1, $result[0]);
		$this->assertIsArray($result[1]); // Nested array with [2]
		$this->assertEquals(2, $result[1][0]);
		$this->assertEquals(3, $result[2]);
	}

	/* ===================== Access & Retrieval (Dot Notation) ===================== */

	public function testGet(): void
	{
		$array = [
			'user' => [
				'profile' => [
					'name' => 'John',
					'age' => 30,
				],
			],
		];

		$this->assertEquals('John', MultidimensionalArray::get($array, 'user.profile.name'));
		$this->assertEquals(30, MultidimensionalArray::get($array, 'user.profile.age'));
		$this->assertEquals(['name' => 'John', 'age' => 30], MultidimensionalArray::get($array, 'user.profile'));
	}

	public function testGetWithDefault(): void
	{
		$array = ['user' => ['name' => 'John']];

		$this->assertEquals('default', MultidimensionalArray::get($array, 'user.email', 'default'));
		$this->assertEquals('default', MultidimensionalArray::get($array, 'nonexistent.path', 'default'));
	}

	public function testGetWithNonExistentPath(): void
	{
		$array = ['user' => ['name' => 'John']];

		$this->assertNull(MultidimensionalArray::get($array, 'user.profile.name'));
		$this->assertNull(MultidimensionalArray::get($array, 'invalid'));
	}

	public function testGetWithSingleKey(): void
	{
		$array = ['name' => 'John', 'age' => 30];

		$this->assertEquals('John', MultidimensionalArray::get($array, 'name'));
		$this->assertEquals(30, MultidimensionalArray::get($array, 'age'));
	}

	public function testHas(): void
	{
		$array = [
			'user' => [
				'profile' => [
					'name' => 'John',
				],
			],
		];

		$this->assertTrue(MultidimensionalArray::has($array, 'user.profile.name'));
		$this->assertTrue(MultidimensionalArray::has($array, 'user.profile'));
		$this->assertTrue(MultidimensionalArray::has($array, 'user'));
		$this->assertFalse(MultidimensionalArray::has($array, 'user.profile.email'));
		$this->assertFalse(MultidimensionalArray::has($array, 'nonexistent'));
	}

	public function testPluck(): void
	{
		$array = [
			['id' => 1, 'name' => 'John', 'age' => 30],
			['id' => 2, 'name' => 'Jane', 'age' => 25],
			['id' => 3, 'name' => 'Bob', 'age' => 35],
		];

		$this->assertEquals(['John', 'Jane', 'Bob'], MultidimensionalArray::pluck($array, 'name'));
		$this->assertEquals([30, 25, 35], MultidimensionalArray::pluck($array, 'age'));
	}

	public function testPluckWithDotNotation(): void
	{
		$array = [
			['user' => ['profile' => ['name' => 'John']]],
			['user' => ['profile' => ['name' => 'Jane']]],
		];

		$result = MultidimensionalArray::pluck($array, 'user.profile.name');
		$this->assertEquals(['John', 'Jane'], $result);
	}

	public function testPluckWithNonExistentKey(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
		];

		$result = MultidimensionalArray::pluck($array, 'email');
		$this->assertEquals([], $result);
	}

	public function testPluckWithCallable(): void
	{
		$array = [
			['id' => 1, 'name' => 'john'],
			['id' => 2, 'name' => 'jane'],
			['id' => 3, 'name' => 'bob']
		];

		// Extract with callback transformation
		$result = MultidimensionalArray::pluck($array, fn($item) => strtoupper($item['name']));
		$this->assertEquals(['JOHN', 'JANE', 'BOB'], $result);

		// Extract with callback and indexBy
		$result = MultidimensionalArray::pluck($array, fn($item) => strtoupper($item['name']), 'id');
		$this->assertEquals([
			1 => 'JOHN',
			2 => 'JANE',
			3 => 'BOB'
		], $result);
	}

	public function testPluckWithObjects(): void
	{
		$obj1 = new \stdClass();
		$obj1->id = 1;
		$obj1->name = 'John';

		$obj2 = new \stdClass();
		$obj2->id = 2;
		$obj2->name = 'Jane';

		$array = [$obj1, $obj2];

		$result = MultidimensionalArray::pluck($array, 'name');
		$this->assertEquals(['John', 'Jane'], $result);

		$result = MultidimensionalArray::pluck($array, 'name', 'id');
		$this->assertEquals([1 => 'John', 2 => 'Jane'], $result);
	}

	public function testPluckWithObjectsAndMissingProperty(): void
	{
		$obj1 = new \stdClass();
		$obj1->id = 1;
		$obj1->name = 'John';

		$obj2 = new \stdClass();
		$obj2->id = 2;
		// No name property

		$array = [$obj1, $obj2];

		$result = MultidimensionalArray::pluck($array, 'name', 'id');
		$this->assertEquals([1 => 'John', 2 => null], $result);
	}

	public function testGetValuesByKey(): void
	{
		$array = [
			['name' => 'John', 'age' => 30],
			['name' => 'Jane', 'age' => 25],
			['city' => 'Paris'],
		];

		$result = MultidimensionalArray::getValuesByKey($array, 'name');
		$this->assertEquals(['John', 'Jane'], $result);

		$result = MultidimensionalArray::getValuesByKey($array, 'age');
		$this->assertEquals([30, 25], $result);

		// Non-existent key
		$result = MultidimensionalArray::getValuesByKey($array, 'country');
		$this->assertEquals([], $result);
	}

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

		// Non-existent key
		$result = MultidimensionalArray::getValuesWithKeysByKey($array, 'country');
		$this->assertEquals([], $result);
	}

	/* ===================== Search ===================== */

	public function testFindByKeyValue(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
			['id' => 3, 'name' => 'Bob'],
		];

		$result = MultidimensionalArray::findByKeyValue($array, 'id', 2);
		$this->assertEquals(['id' => 2, 'name' => 'Jane'], $result);

		$result = MultidimensionalArray::findByKeyValue($array, 'name', 'Bob');
		$this->assertEquals(['id' => 3, 'name' => 'Bob'], $result);

		// Non-existent value
		$result = MultidimensionalArray::findByKeyValue($array, 'id', 99);
		$this->assertNull($result);
	}

	public function testFindByKeyValueStrict(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			['id' => '2', 'name' => 'Jane'],
			['id' => 3, 'name' => 'Bob'],
		];

		// Non-strict: type coercion
		$result = MultidimensionalArray::findByKeyValue($array, 'id', 2, false);
		$this->assertEquals(['id' => '2', 'name' => 'Jane'], $result);

		// Strict: exact type match
		$result = MultidimensionalArray::findByKeyValue($array, 'id', 2, true);
		$this->assertNull($result);

		$result = MultidimensionalArray::findByKeyValue($array, 'id', '2', true);
		$this->assertEquals(['id' => '2', 'name' => 'Jane'], $result);
	}

	public function testExistsByKeyValue(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
			['id' => 3, 'name' => 'Bob'],
		];

		$this->assertTrue(MultidimensionalArray::existsByKeyValue($array, 'id', 2));
		$this->assertTrue(MultidimensionalArray::existsByKeyValue($array, 'name', 'Jane'));
		$this->assertFalse(MultidimensionalArray::existsByKeyValue($array, 'id', 99));
		$this->assertFalse(MultidimensionalArray::existsByKeyValue($array, 'name', 'Alice'));
	}

	public function testExistsByKeyValueStrict(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			['id' => '2', 'name' => 'Jane'],
		];

		// Non-strict
		$this->assertTrue(MultidimensionalArray::existsByKeyValue($array, 'id', 2, false));

		// Strict
		$this->assertFalse(MultidimensionalArray::existsByKeyValue($array, 'id', 2, true));
		$this->assertTrue(MultidimensionalArray::existsByKeyValue($array, 'id', '2', true));
	}

	public function testInArrayRecursive(): void
	{
		$haystack = [1, 2, [3, 4, [5, 6]]];

		$this->assertTrue(MultidimensionalArray::inArrayRecursive(1, $haystack));
		$this->assertTrue(MultidimensionalArray::inArrayRecursive(5, $haystack));
		$this->assertTrue(MultidimensionalArray::inArrayRecursive(6, $haystack));
		$this->assertFalse(MultidimensionalArray::inArrayRecursive(7, $haystack));

		// Test with strict
		$haystack = [1, '2', [3, '4']];
		$this->assertFalse(MultidimensionalArray::inArrayRecursive(2, $haystack, true));
		$this->assertTrue(MultidimensionalArray::inArrayRecursive(2, $haystack, false));
		$this->assertTrue(MultidimensionalArray::inArrayRecursive('4', $haystack, true));
	}

	public function testWhereEquals(): void
	{
		$array = [
			['id' => 1, 'name' => 'John', 'age' => 30],
			['id' => 2, 'name' => 'Jane', 'age' => 25],
			['id' => 3, 'name' => 'Bob', 'age' => 30],
		];

		$result = MultidimensionalArray::where($array, 'age', '=', 30);
		$this->assertCount(2, $result);
		$this->assertEquals('John', $result[0]['name']);
		$this->assertEquals('Bob', $result[2]['name']);
	}

	public function testWhereStrictEquals(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			['id' => '2', 'name' => 'Jane'],
			['id' => 3, 'name' => 'Bob'],
		];

		// == comparison (type coercion: '2' == 2 is true)
		$result = MultidimensionalArray::where($array, 'id', '==', 2);
		$this->assertCount(1, $result); // Only '2' matches with type coercion
		$this->assertEquals('Jane', $result[1]['name']);

		// === comparison (strict: no type coercion)
		$result = MultidimensionalArray::where($array, 'id', '===', 2);
		$this->assertCount(0, $result); // No exact match with integer 2

		// === comparison with string
		$result = MultidimensionalArray::where($array, 'id', '===', '2');
		$this->assertCount(1, $result); // Exact match with string '2'
		$this->assertEquals('Jane', $result[1]['name']);
	}

	public function testWhereNotEquals(): void
	{
		$array = [
			['id' => 1, 'status' => 'active'],
			['id' => 2, 'status' => 'inactive'],
			['id' => 3, 'status' => 'active'],
		];

		$result = MultidimensionalArray::where($array, 'status', '!=', 'active');
		$this->assertCount(1, $result);
		$this->assertEquals('inactive', $result[1]['status']);
	}

	public function testWhereComparison(): void
	{
		$array = [
			['id' => 1, 'age' => 20],
			['id' => 2, 'age' => 30],
			['id' => 3, 'age' => 40],
		];

		$result = MultidimensionalArray::where($array, 'age', '>', 25);
		$this->assertCount(2, $result);

		$result = MultidimensionalArray::where($array, 'age', '<', 35);
		$this->assertCount(2, $result);

		$result = MultidimensionalArray::where($array, 'age', '>=', 30);
		$this->assertCount(2, $result);

		$result = MultidimensionalArray::where($array, 'age', '<=', 30);
		$this->assertCount(2, $result);
	}

	public function testWhereIn(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
			['id' => 3, 'name' => 'Bob'],
			['id' => 4, 'name' => 'Alice'],
		];

		$result = MultidimensionalArray::whereIn($array, 'name', ['John', 'Bob']);
		$this->assertCount(2, $result);
		$this->assertEquals('John', $result[0]['name']);
		$this->assertEquals('Bob', $result[2]['name']);
	}

	public function testWhereInWithEmptyValues(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
		];

		$result = MultidimensionalArray::whereIn($array, 'name', []);
		$this->assertEquals([], $result);
	}

	public function testWhereInOperator(): void
	{
		$array = [
			['id' => 1, 'status' => 'active'],
			['id' => 2, 'status' => 'pending'],
			['id' => 3, 'status' => 'inactive'],
			['id' => 4, 'status' => 'active'],
		];

		$result = MultidimensionalArray::where($array, 'status', 'in', ['active', 'pending']);
		$this->assertCount(3, $result);
		$this->assertEquals('active', $result[0]['status']);
		$this->assertEquals('pending', $result[1]['status']);
		$this->assertEquals('active', $result[3]['status']);
	}

	public function testWhereNotInOperator(): void
	{
		$array = [
			['id' => 1, 'status' => 'active'],
			['id' => 2, 'status' => 'pending'],
			['id' => 3, 'status' => 'inactive'],
		];

		$result = MultidimensionalArray::where($array, 'status', 'not_in', ['active', 'pending']);
		$this->assertCount(1, $result);
		$this->assertEquals('inactive', $result[2]['status']);
	}

	public function testWhereWithInvalidOperator(): void
	{
		$array = [
			['id' => 1, 'value' => 10],
			['id' => 2, 'value' => 20],
		];

		$result = MultidimensionalArray::where($array, 'value', 'INVALID_OP', 10);
		$this->assertEquals([], $result);
	}

	public function testWhereWithNonArrayItems(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			'not an array',
			['id' => 2, 'name' => 'Jane'],
			null,
			['name' => 'Bob'], // Missing 'id' key
		];

		$result = MultidimensionalArray::where($array, 'id', '=', 2);
		$this->assertCount(1, $result);
		$this->assertEquals('Jane', $result[2]['name']);
	}

	public function testWhereInStrict(): void
	{
		$array = [
			['id' => 1, 'value' => 1],
			['id' => 2, 'value' => '2'],
			['id' => 3, 'value' => 3],
		];

		// Non-strict
		$result = MultidimensionalArray::whereIn($array, 'value', [1, 2, 3], false);
		$this->assertCount(3, $result);

		// Strict
		$result = MultidimensionalArray::whereIn($array, 'value', [1, 2, 3], true);
		$this->assertCount(2, $result); // Only integer values match
	}

	public function testWhereInWithNonArrayItems(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			'not an array',
			['id' => 2, 'name' => 'Jane'],
			['name' => 'Bob'], // Missing 'id' key
		];

		$result = MultidimensionalArray::whereIn($array, 'id', [1, 2]);
		$this->assertCount(2, $result);
	}

	public function testWhereNotNull(): void
	{
		$array = [
			['id' => 1, 'name' => 'John', 'email' => 'john@example.com'],
			['id' => 2, 'name' => 'Jane', 'email' => null],
			['id' => 3, 'name' => 'Bob'],
			['id' => 4, 'name' => 'Alice', 'email' => 'alice@example.com'],
		];

		$result = MultidimensionalArray::whereNotNull($array, 'email');
		$this->assertCount(2, $result);
		$this->assertEquals('John', $result[0]['name']);
		$this->assertEquals('Alice', $result[3]['name']);
	}

	public function testFindAll(): void
	{
		$array = [
			['id' => 1, 'status' => 'active'],
			['id' => 2, 'status' => 'inactive'],
			['id' => 3, 'status' => 'active'],
			['id' => 4, 'status' => 'pending'],
		];

		$result = MultidimensionalArray::findAll($array, 'status', 'active');
		$this->assertCount(2, $result);
		$this->assertEquals(1, $result[0]['id']);
		$this->assertEquals(3, $result[1]['id']);
	}

	public function testFindAllWithNoMatches(): void
	{
		$array = [
			['id' => 1, 'status' => 'active'],
			['id' => 2, 'status' => 'inactive'],
		];

		$result = MultidimensionalArray::findAll($array, 'status', 'pending');
		$this->assertEquals([], $result);
	}

	public function testFindAllStrict(): void
	{
		$array = [
			['id' => 1, 'value' => 1],
			['id' => 2, 'value' => '1'],
			['id' => 3, 'value' => 1],
		];

		// Non-strict
		$result = MultidimensionalArray::findAll($array, 'value', 1, false);
		$this->assertCount(3, $result);

		// Strict
		$result = MultidimensionalArray::findAll($array, 'value', 1, true);
		$this->assertCount(2, $result);
	}

	/* ===================== Modification ===================== */

	public function testSet(): void
	{
		$array = [
			'user' => [
				'profile' => [
					'name' => 'John',
				],
			],
		];

		MultidimensionalArray::set($array, 'user.profile.name', 'Jane');
		$this->assertEquals('Jane', $array['user']['profile']['name']);

		MultidimensionalArray::set($array, 'user.profile.age', 30);
		$this->assertEquals(30, $array['user']['profile']['age']);
	}

	public function testSetWithDeepNesting(): void
	{
		$array = [];

		MultidimensionalArray::set($array, 'level1.level2.level3.value', 'deep');
		$this->assertEquals('deep', $array['level1']['level2']['level3']['value']);
	}

	public function testSetCreatesIntermediateArrays(): void
	{
		$array = ['user' => ['name' => 'John']];

		MultidimensionalArray::set($array, 'user.profile.bio', 'Developer');
		$this->assertTrue(is_array($array['user']['profile']));
		$this->assertEquals('Developer', $array['user']['profile']['bio']);
	}

	public function testSetWithSingleKey(): void
	{
		$array = ['name' => 'John'];

		MultidimensionalArray::set($array, 'age', 30);
		$this->assertEquals(30, $array['age']);
	}

	public function testAddKeyAndValue(): void
	{
		$array = [
			['name' => 'John'],
			['name' => 'Jane'],
		];

		MultidimensionalArray::addKeyAndValue($array, 'status', 'active');

		$this->assertArrayHasKey('status', $array[0]);
		$this->assertEquals('active', $array[0]['status']);
		$this->assertEquals('active', $array[1]['status']);
	}

	public function testAddKeyAndValueRecursive(): void
	{
		$array = [
			'users' => [
				['name' => 'John'],
				['name' => 'Jane'],
			],
			'admins' => [
				['name' => 'Admin1'],
			],
		];

		MultidimensionalArray::addKeyAndValueRecursive($array, 'status', 'active');

		$this->assertEquals('active', $array['users'][0]['status']);
		$this->assertEquals('active', $array['users'][1]['status']);
		$this->assertEquals('active', $array['admins'][0]['status']);
	}

	public function testForget(): void
	{
		$array = [
			'user' => [
				'profile' => [
					'name' => 'John',
					'age' => 30,
				],
			],
		];

		MultidimensionalArray::forget($array, 'user.profile.age');
		$this->assertArrayNotHasKey('age', $array['user']['profile']);
		$this->assertArrayHasKey('name', $array['user']['profile']);
	}

	public function testForgetWithNonExistentPath(): void
	{
		$array = ['user' => ['name' => 'John']];

		MultidimensionalArray::forget($array, 'user.profile.age');
		$this->assertEquals(['user' => ['name' => 'John']], $array);
	}

	public function testPull(): void
	{
		$array = [
			'user' => [
				'profile' => [
					'name' => 'John',
					'age' => 30,
				],
			],
		];

		$value = MultidimensionalArray::pull($array, 'user.profile.age');
		$this->assertEquals(30, $value);
		$this->assertArrayNotHasKey('age', $array['user']['profile']);
		$this->assertArrayHasKey('name', $array['user']['profile']);
	}

	public function testPullWithDefault(): void
	{
		$array = ['user' => ['name' => 'John']];

		$value = MultidimensionalArray::pull($array, 'user.email', 'default@example.com');
		$this->assertEquals('default@example.com', $value);
	}

	/* ===================== Merge & Comparison ===================== */

	public function testMergeRecursive(): void
	{
		$array1 = [
			'user' => [
				'name' => 'John',
				'age' => 30,
			],
		];

		$array2 = [
			'user' => [
				'age' => 35,
				'email' => 'john@example.com',
			],
		];

		$result = MultidimensionalArray::mergeRecursive($array1, $array2);

		$this->assertEquals('John', $result['user']['name']);
		$this->assertEquals(35, $result['user']['age']);
		$this->assertEquals('john@example.com', $result['user']['email']);
	}

	public function testMergeRecursiveWithMultipleArrays(): void
	{
		$array1 = ['a' => 1, 'b' => ['c' => 2]];
		$array2 = ['b' => ['d' => 3], 'e' => 4];
		$array3 = ['b' => ['e' => 5], 'f' => 6];

		$result = MultidimensionalArray::mergeRecursive($array1, $array2, $array3);

		$this->assertEquals(1, $result['a']);
		$this->assertEquals(2, $result['b']['c']);
		$this->assertEquals(3, $result['b']['d']);
		$this->assertEquals(5, $result['b']['e']);
		$this->assertEquals(4, $result['e']);
		$this->assertEquals(6, $result['f']);
	}

	public function testDiff(): void
	{
		$array1 = ['a' => 1, 'b' => 2, 'c' => 3];
		$array2 = ['a' => 1, 'b' => 4];

		$result = MultidimensionalArray::diff($array1, $array2);

		$this->assertEquals(['b' => 2, 'c' => 3], $result);
	}

	public function testDiffWithMultipleArrays(): void
	{
		$array1 = ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4];
		$array2 = ['a' => 1];
		$array3 = ['b' => 2];

		$result = MultidimensionalArray::diff($array1, $array2, $array3);

		$this->assertEquals(['c' => 3, 'd' => 4], $result);
	}

	public function testDiffRecursive(): void
	{
		$array1 = [
			'user' => [
				'name' => 'John',
				'age' => 30,
				'profile' => [
					'bio' => 'Developer',
				],
			],
		];

		$array2 = [
			'user' => [
				'name' => 'John',
				'profile' => [
					'bio' => 'Developer',
				],
			],
		];

		$result = MultidimensionalArray::diffRecursive($array1, $array2);

		$this->assertArrayHasKey('user', $result);
		$this->assertEquals(['age' => 30], $result['user']);
	}

	public function testIntersect(): void
	{
		$array1 = ['a' => 1, 'b' => 2, 'c' => 3];
		$array2 = ['a' => 1, 'b' => 4, 'd' => 5];

		$result = MultidimensionalArray::intersect($array1, $array2);

		$this->assertEquals(['a' => 1], $result);
	}

	public function testIntersectWithMultipleArrays(): void
	{
		$array1 = ['a' => 1, 'b' => 2, 'c' => 3];
		$array2 = ['a' => 1, 'b' => 2, 'd' => 4];
		$array3 = ['a' => 1, 'e' => 5];

		$result = MultidimensionalArray::intersect($array1, $array2, $array3);

		$this->assertEquals(['a' => 1], $result);
	}

	/* ===================== Sorting ===================== */

	public function testSortSimple(): void
	{
		$array = [
			['name' => 'Charlie', 'age' => 30],
			['name' => 'Alice', 'age' => 25],
			['name' => 'Bob', 'age' => 35],
		];

		// Sort by name
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

		// Descending sort by age
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

		// Sort by department then by name
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

		// Natural order sort
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

		// Case-sensitive sort
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
		// Array should not be modified
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

	public function testSortWithSimpleCriterion(): void
	{
		$array = [
			['name' => 'Charlie'],
			['name' => 'Alice'],
			['name' => 'Bob'],
		];

		// Pass single criterion without wrapping in array
		MultidimensionalArray::sort($array, ['name']);

		$this->assertEquals('Alice', $array[0]['name']);
		$this->assertEquals('Bob', $array[1]['name']);
		$this->assertEquals('Charlie', $array[2]['name']);
	}

	public function testSortWithNumericValues(): void
	{
		$array = [
			['id' => 10, 'value' => 100],
			['id' => 2, 'value' => 50],
			['id' => 30, 'value' => 75],
		];

		MultidimensionalArray::sort($array, [['id', true]]);

		$this->assertEquals(2, $array[0]['id']);
		$this->assertEquals(10, $array[1]['id']);
		$this->assertEquals(30, $array[2]['id']);
	}

	public function testSortWithEqualValuesUsesSecondCriterion(): void
	{
		$array = [
			['priority' => 1, 'name' => 'Zebra'],
			['priority' => 2, 'name' => 'Apple'],
			['priority' => 1, 'name' => 'Alpha'],
			['priority' => 2, 'name' => 'Beta'],
		];

		MultidimensionalArray::sort($array, [['priority'], ['name']]);

		// Priority 1, then by name
		$this->assertEquals('Alpha', $array[0]['name']);
		$this->assertEquals('Zebra', $array[1]['name']);
		// Priority 2, then by name
		$this->assertEquals('Apple', $array[2]['name']);
		$this->assertEquals('Beta', $array[3]['name']);
	}

	/* ===================== Additional Edge Cases ===================== */

	public function testGetWithNumericKeys(): void
	{
		$array = [
			0 => [
				1 => [
					2 => 'value',
				],
			],
		];

		$this->assertEquals('value', MultidimensionalArray::get($array, '0.1.2'));
	}

	public function testHasWithNumericKeys(): void
	{
		$array = [
			0 => [1 => ['2' => 'value']],
		];

		$this->assertTrue(MultidimensionalArray::has($array, '0.1.2'));
		$this->assertFalse(MultidimensionalArray::has($array, '0.1.3'));
	}

	public function testSetOverridesNonArrayValue(): void
	{
		$array = [
			'user' => 'simple string',
		];

		// Should override the string with nested array
		MultidimensionalArray::set($array, 'user.profile.name', 'John');

		$this->assertIsArray($array['user']);
		$this->assertEquals('John', $array['user']['profile']['name']);
	}

	public function testForgetWithPartiallyNonExistentPath(): void
	{
		$array = ['user' => 'not an array'];

		// Should do nothing when path doesn't exist
		MultidimensionalArray::forget($array, 'user.profile.name');

		$this->assertEquals(['user' => 'not an array'], $array);
	}

	public function testForgetWithNonArrayIntermediate(): void
	{
		$array = [
			'user' => [
				'profile' => 'string value',
			],
		];

		MultidimensionalArray::forget($array, 'user.profile.name');

		// Should stop when encountering non-array
		$this->assertEquals('string value', $array['user']['profile']);
	}

	public function testAddKeyAndValueRecursiveAvoidsInfiniteLoop(): void
	{
		$array = [
			'level1' => [
				'level2' => 'value',
			],
		];

		// Add 'newkey' at all levels
		MultidimensionalArray::addKeyAndValueRecursive($array, 'newkey', 'newvalue');

		$this->assertEquals('newvalue', $array['newkey']);
		$this->assertEquals('newvalue', $array['level1']['newkey']);
		// level2 is a string, it won't have the new key added
		$this->assertIsString($array['level1']['level2']);
		$this->assertEquals('value', $array['level1']['level2']);
	}

	public function testIntersectWithEmptyArray(): void
	{
		$result = MultidimensionalArray::intersect();
		$this->assertEquals([], $result);
	}

	public function testDiffRecursiveWithKeyOnlyInFirstArray(): void
	{
		$array1 = [
			'user' => [
				'name' => 'John',
				'email' => 'john@example.com',
			],
		];

		$array2 = [
			'user' => [
				'name' => 'John',
			],
		];

		$result = MultidimensionalArray::diffRecursive($array1, $array2);

		$this->assertArrayHasKey('user', $result);
		$this->assertEquals(['email' => 'john@example.com'], $result['user']);
	}

	public function testDiffRecursiveWithIdenticalArrays(): void
	{
		$array1 = [
			'user' => [
				'name' => 'John',
				'age' => 30,
			],
		];

		$array2 = $array1;

		$result = MultidimensionalArray::diffRecursive($array1, $array2);
		$this->assertEquals([], $result);
	}

	public function testDiffRecursiveWithDifferentValues(): void
	{
		$array1 = ['key' => 'value1'];
		$array2 = ['key' => 'value2'];

		$result = MultidimensionalArray::diffRecursive($array1, $array2);
		$this->assertEquals(['key' => 'value1'], $result);
	}

	public function testMergeRecursiveOverridesScalarWithArray(): void
	{
		$array1 = ['key' => 'scalar value'];
		$array2 = ['key' => ['nested' => 'array']];

		$result = MultidimensionalArray::mergeRecursive($array1, $array2);

		$this->assertEquals(['nested' => 'array'], $result['key']);
	}

	public function testMergeRecursiveOverridesArrayWithScalar(): void
	{
		$array1 = ['key' => ['nested' => 'array']];
		$array2 = ['key' => 'scalar value'];

		$result = MultidimensionalArray::mergeRecursive($array1, $array2);

		$this->assertEquals('scalar value', $result['key']);
	}

	/* ===================== DEPRECATED METHODS ===================== */

	public function testDeprecatedGetValuesByKeyarray(): void
	{
		$array = [
			['name' => 'John', 'age' => 30],
			['name' => 'Jane', 'age' => 25],
		];

		$result = MultidimensionalArray::getValuesByKeyarray($array, 'name');
		$this->assertEquals(['John', 'Jane'], $result);
	}

	public function testDeprecatedGetValue(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
		];

		$result = MultidimensionalArray::getValue($array, 2, 'id');
		$this->assertEquals(['id' => 2, 'name' => 'Jane'], $result);
	}

	public function testDeprecatedIsValueExist(): void
	{
		$array = [
			['id' => 1, 'name' => 'John'],
			['id' => 2, 'name' => 'Jane'],
		];

		$this->assertTrue(MultidimensionalArray::isValueExist($array, 2, 'id'));
		$this->assertFalse(MultidimensionalArray::isValueExist($array, 99, 'id'));
	}
}