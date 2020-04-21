<?php

namespace Osimatic\Helpers\ArrayList;

/**
 * Class SimpleArray
 * @package Osimatic\Helpers\ArrayList
 */
class SimpleArray
{
	/**
	 * @param float $search
	 * @param array $array
	 * @param string $method
	 * @return float|null
	 */
	public static function getClosest(float $search, array $array, string $method='default'): ?float
	{
		$closest = null;
		//rsort($arr);
		foreach ($array as $item) {
			if ('higher' === $method) {
				if (($closest === null || abs($search - $closest) > abs($item - $search)) && $item >= $search) {
					$closest = $item;
				}
			}
			else if ('lower' === $method) {
				if (($closest === null || abs($search - $closest) > abs($item - $search)) && $item <= $search) {
					$closest = $item;
				}
			}
			else {
				if ($closest === null || abs($search - $closest) > abs($item - $search)) {
					$closest = $item;
				}
			}
		}
		return $closest;
	}

	/**
	 * Equivalent de la fonction in_array mais ne tenant pas compte de la casse
	 * @param mixed $needle La valeur recherchée.
	 * @param array $haystack Le tableau
	 * @param bool $strict Optionnel. S'il vaut TRUE alors in_array() vérifiera aussi que le type du paramètre needle correspond au type de la valeur trouvée dans haystack.
	 * @return bool Retourne TRUE si needle est trouvé dans le tableau, FALSE sinon.
	 */
	public static function in_array_i($needle, array $haystack, bool $strict=false): bool
	{
		return in_array(strtolower($needle), array_map('strtolower', $haystack), $strict);
	}

	/**
	 * Equivalent de la fonction in_array mais en recherchant de multiples valeurs contenu dans un tableau
	 * @param array $arrayNeedle Les valeurs recherchées.
	 * @param array $haystack Le tableau
	 * @return bool Retourne TRUE si une des valeurs dans arrayNeedle est trouvé dans le tableau, FALSE sinon.
	 */
	public static function in_array_values(array $arrayNeedle, array $haystack): bool
	{
		return count(array_intersect(array_flip($arrayNeedle), $haystack)) > 0;
	}

}