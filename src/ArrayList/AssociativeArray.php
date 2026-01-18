<?php

namespace Osimatic\ArrayList;

/**
 * Utility class for associative array operations and manipulations.
 * Provides methods for working with key-value paired arrays.
 */
class AssociativeArray
{
	/**
	 * Generates all possible combinations of words in different orders.
	 * Recursively creates permutations where each word appears in each position once.
	 * Words are joined with spaces in the result.
	 * Example: ['a', 'b'] returns ['a b', 'b a']
	 * @param array $words Array of words to combine
	 * @return array Array of all word permutations as space-separated strings
	 */
	public static function getAllCombinations(array $words): array
	{
		if (count($words) <= 1) {
			return $words;
		}

		$result = [];
		for ($i = 0, $iMax = count($words); $i < $iMax; ++$i ) {
			$firstWord = $words[$i];
			$remainingWords = array();
			for ($j = 0, $jMax = count($words); $j < $jMax; ++$j ) {
				if ( $i <> $j ) {
					$remainingWords[] = $words[$j];
				}
			}
			$combos = self::getAllCombinations($remainingWords);
			for ($j = 0, $jMax = count($combos); $j < $jMax; ++$j ) {
				$result[] = $firstWord . ' ' . $combos[$j];
			}
		}

		return $result;
	}

}