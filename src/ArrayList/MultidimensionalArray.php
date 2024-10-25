<?php

namespace Osimatic\ArrayList;

/**
 * Class MultidimensionalArray
 * @package Osimatic\Helpers\ArrayList
 */
class MultidimensionalArray
{
	// ========== Méthodes de comptage ==========

	/**
	 * Compte toutes le nombre de valeur dans un tableau multidimensionnel
	 * (toutes les valeurs du tableau principal et de ses éventuels tableau enfants, sans limite de profondeur)
	 * @param array $array
	 * @return int
	 */
	public static function count(array $array): int
	{
		$count = 0;
		foreach ($array as $subArray) {
			if (!is_array($subArray)) {
				$count++;
			}
			else {
				$count = ($count + self::count($subArray));
			}
		}
		return $count;
	}


	// ========== Méthodes de récupération ==========

	/**
	 * @param array $array
	 * @param $key
	 * @return array
	 */
	public static function getValuesWithKeysByKey(array $array, $key): array
	{
		$listeValues = array();
		foreach ($array as $keyArray => $subArray) {
			if (isset($subArray[$key])) {
				$listeValues[$keyArray] = $subArray[$key];
			}
		}
		return $listeValues;
	}

	/**
	 * @param array $array
	 * @param $key
	 * @return array
	 */
	public static function getValuesByKeyarray(array $array, $key): array
	{
		$listeValues = array();
		foreach ($array as $subArray) {
			if (isset($subArray[$key])) {
				$listeValues[] = $subArray[$key];
			}
		}
		return $listeValues;
	}

	// ========== Méthodes de modification ==========

	/**
	 * @param array $array
	 * @param $keyAdded
	 * @param $valueAdded
	 */
	public static function addKeyAndValue(array $array, $keyAdded, $valueAdded): void
	{
		foreach ($array as $key => $tabGraph) {
			$array[$key][$keyAdded] = $valueAdded;
		}
	}

	// ========== Méthodes de recherche ==========

	/**
	 * @param array $array
	 * @param $value
	 * @param $key
	 * @return mixed|null
	 */
	public static function getValue(array $array, $value, $key): mixed
	{
		foreach ($array as $subArray) {
			if (isset($subArray[$key]) && $subArray[$key] == $value) {
				return $subArray;
			}
		}
		return null;
	}

	/**
	 * @param array $array
	 * @param $value
	 * @param $key
	 * @return bool
	 */
	public static function isValueExist(array $array, $value, $key): bool
	{
		foreach ($array as $subArray) {
			if (isset($subArray[$key]) && $subArray[$key] == $value) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Similaire à la fonction in_array fonctionnant avec des tableaux multidimensionnels
	 * @param mixed $needle La valeur recherchée.
	 * @param array $haystack Le tableau multidimensionnel.
	 * @param bool $strict Le troisième paramètre strict est optionnel. S'il vaut TRUE alors in_array() vérifiera aussi que le type du paramètre needle correspond au type de la valeur trouvée dans haystack.
	 * @return bool Retourne TRUE si needle est trouvé dans le tableau, FALSE sinon.
	 */
	public static function inArrayRecursive(mixed $needle, array $haystack, bool $strict = false): bool
	{
		foreach ($haystack as $item) {
			if (($strict ? $item === $needle : $item == $needle) || (is_array($item) && self::inArrayRecursive($needle, $item, $strict))) {
				return true;
			}
		}
		return false;
	}


	// ========== Tri ==========

	private static ?array $sortListColumnSorting = null;
	private static ?int $sortDepth = null;

	/**
	 * Tri un tableau multidimensionnel.
	 * @param $array array le tableau multidimensionnel à trier.
	 * @param $listColumnSorting mixed les critères de tri. Peut être :
	 * - La clé selon laquelle le tableau va être trié (sous forme d'entier ou de chaîne de caractère)
	 * - Un tableau avec en index :
	 * 		-> 0 : nom de la clé
	 * 		-> 1 : true pour un tri croissant, false pour un tri décroissant (true par défaut)
	 * 		-> 2 : true pour un tri en ordre naturel, false pour un tri normal (false par défaut)
	 * 		-> 3 : true pour un tri en tenant compte de la casse, false pour un tri normal (false par défaut)
	 * - Un tableau de plusieurs tableaux de critères (pour la structure d'un tableau de critère, voir ci dessus)
	 */
	public static function sort(array &$array, array $listColumnSorting): void
	{
		if (empty($listColumnSorting)) {
			return;
		}

		if (isset($listColumnSorting[0]) && !is_array($listColumnSorting[0])) {
			$listColumnSorting = [$listColumnSorting];
		}

		self::$sortListColumnSorting = $listColumnSorting;
		self::$sortDepth = 0;

		usort($array, self::sortCompareRecursive(...));
	}

	private static function sortCompareRecursive(array $sousArray1, array $sousArray2): int
	{
		//if (self::$sortDepth == 0) {
		//
		//}

		if (!isset(self::$sortListColumnSorting[self::$sortDepth])) {
			self::$sortDepth = 0;
			return 1;
		}

		$triAsc = self::$sortListColumnSorting[self::$sortDepth][1] ?? true;
		$ordreNaturel = self::$sortListColumnSorting[self::$sortDepth][2] ?? false;
		$caseSensitive = self::$sortListColumnSorting[self::$sortDepth][3] ?? false;

		$colonneTriCourante = self::$sortListColumnSorting[self::$sortDepth][0];
		if ($triAsc) {
			$val1 = $sousArray1[$colonneTriCourante];
			$val2 = $sousArray2[$colonneTriCourante];
		}
		else {
			$val1 = $sousArray2[$colonneTriCourante];
			$val2 = $sousArray1[$colonneTriCourante];
		}

		$cmp = self::compareValue($val1, $val2, $ordreNaturel, $caseSensitive);
		if ($cmp === 0) {
			self::$sortDepth++;
			return self::sortCompareRecursive($sousArray1, $sousArray2);
		}
		self::$sortDepth = 0;
		return $cmp;
	}

	private static function compareValue($val1, $val2, bool $ordreNaturel=false, bool $caseSensitive=false): int
	{
		if (is_numeric($val1) && is_numeric($val2)) {
			return $val1 <=> $val2;
		}

		if ($ordreNaturel) {
			if ($caseSensitive) {
				return strnatcmp($val1, $val2); // Comparaison ordre naturel, sensible à la casse
			}
			return strnatcasecmp($val1, $val2); // Comparaison ordre naturel, insensible à la casse
		}

		if ($caseSensitive) {
			return strcmp($val1, $val2); // Comparaison, sensible à la casse
		}
		return strcasecmp($val1, $val2); // Comparaison, insensible à la casse
	}

	/**
	 * @param array $array
	 * @param int $sort_flags
	 */
	public static function ksortRecursive(array &$array, int $sort_flags = SORT_REGULAR): void
	{
		ksort($array, $sort_flags);
		foreach ($array as &$arr) {
			self::ksortRecursive($arr, $sort_flags);
		}
	}
}