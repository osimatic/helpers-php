<?php

namespace Tests\ArrayList;

use Osimatic\ArrayList\AssociativeArray;
use PHPUnit\Framework\TestCase;

final class AssociativeArrayTest extends TestCase
{
	public function testGetAllCombinationsWithSingleWord(): void
	{
		$words = ['hello'];
		$result = AssociativeArray::getAllCombinations($words);
		$this->assertEquals(['hello'], $result);
	}

	public function testGetAllCombinationsWithEmptyArray(): void
	{
		$words = [];
		$result = AssociativeArray::getAllCombinations($words);
		$this->assertEquals([], $result);
	}

	public function testGetAllCombinationsWithTwoWords(): void
	{
		$words = ['hello', 'world'];
		$result = AssociativeArray::getAllCombinations($words);

		$this->assertCount(2, $result);
		$this->assertContains('hello world', $result);
		$this->assertContains('world hello', $result);
	}

	public function testGetAllCombinationsWithThreeWords(): void
	{
		$words = ['a', 'b', 'c'];
		$result = AssociativeArray::getAllCombinations($words);

		// 3! = 6 combinaisons possibles
		$this->assertCount(6, $result);

		$expected = [
			'a b c',
			'a c b',
			'b a c',
			'b c a',
			'c a b',
			'c b a'
		];

		foreach ($expected as $combination) {
			$this->assertContains($combination, $result);
		}
	}

	public function testGetAllCombinationsWithFourWords(): void
	{
		$words = ['un', 'deux', 'trois', 'quatre'];
		$result = AssociativeArray::getAllCombinations($words);

		// 4! = 24 combinaisons possibles
		$this->assertCount(24, $result);

		// Vérifier quelques combinaisons spécifiques
		$this->assertContains('un deux trois quatre', $result);
		$this->assertContains('quatre trois deux un', $result);
		$this->assertContains('deux un quatre trois', $result);
	}

	public function testGetAllCombinationsPreservesWordsIntegrity(): void
	{
		$words = ['first', 'second', 'third'];
		$result = AssociativeArray::getAllCombinations($words);

		// Vérifier que chaque combinaison contient tous les mots
		foreach ($result as $combination) {
			$this->assertStringContainsString('first', $combination);
			$this->assertStringContainsString('second', $combination);
			$this->assertStringContainsString('third', $combination);

			// Vérifier qu'il y a exactement 2 espaces (3 mots)
			$this->assertEquals(2, substr_count($combination, ' '));
		}
	}

	public function testGetAllCombinationsWithSpecialCharacters(): void
	{
		$words = ['hello!', 'world?', 'test@'];
		$result = AssociativeArray::getAllCombinations($words);

		$this->assertCount(6, $result);
		$this->assertContains('hello! world? test@', $result);
	}

	public function testGetAllCombinationsWithNumbers(): void
	{
		$words = ['1', '2', '3'];
		$result = AssociativeArray::getAllCombinations($words);

		$this->assertCount(6, $result);
		$this->assertContains('1 2 3', $result);
		$this->assertContains('3 2 1', $result);
	}
}