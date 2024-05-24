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
			$firstword = $words[$i];
			$remainingwords = array();
			for ($j = 0, $jMax = count($words); $j < $jMax; ++$j ) {
				if ( $i <> $j ) {
					$remainingwords[] = $words[$j];
				}
			}
			$combos = self::getAllCombinations($remainingwords);
			for ($j = 0, $jMax = count($combos); $j < $jMax; ++$j ) {
				$result[] = $firstword . ' ' . $combos[$j];
			}
		}

		return $result;
	}

}