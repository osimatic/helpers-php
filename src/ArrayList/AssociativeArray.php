<?php

namespace Osimatic\ArrayList;

/**
 * Class AssociativeArray
 * @package Osimatic\Helpers\ArrayList
 */
class AssociativeArray
{
	/**
	 * @param array $words
	 * @return array
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