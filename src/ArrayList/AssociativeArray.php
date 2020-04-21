<?php

namespace Osimatic\Helpers\ArrayList;

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
		if ( count($words) <= 1 ) {
			$result = $words;
		}
		else {
			$result = array();
			for ( $i = 0; $i < count($words); ++$i ) {
				$firstword = $words[$i];
				$remainingwords = array();
				for ( $j = 0; $j < count($words); ++$j ) {
					if ( $i <> $j ) $remainingwords[] = $words[$j];
				}
				$combos = wordcombos($remainingwords);
				for ( $j = 0; $j < count($combos); ++$j ) {
					$result[] = $firstword . ' ' . $combos[$j];
				}
			}
		}
		return $result;
	}

}