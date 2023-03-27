<?php

namespace Osimatic\Helpers\ArrayList;

/**
 * Class SimpleArray
 * @package Osimatic\Helpers\ArrayList
 */
class Arr
{
	// ========== Comptage ==========

	/**
	 * Additionne le nombre de valeurs de chaque tableau passé en paramètre
	 * @param array $array1
	 * @param array $array2 etc.
	 * @return int
	 */
	public static function countMultiArrays(): int
	{
		$listArrays = func_get_args();
		$count = 0;
		foreach ($listArrays as $array) {
			$count += count($array);
		}
		return $count;
	}

	// ========== Recherche ==========

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
	 * Returns only the array items specified.  Will return a default value if it is not set.
	 * @param array $array
	 * @param array $listKeys
	 * @param mixed $default
	 * @return array
	 */
	public static function getListValuesByListKeys(array $array, array $listKeys, mixed $default=null): array
	{
		$return = [];
		foreach ($listKeys as $key) {
			if (isset($array[$key])) {
				$return[$key] = $array[$key];
			}
			else {
				$return[$key] = $default;
			}
		}

		return $return;
	}

	/**
	 * @param array $delimiters
	 * @param string $string
	 * @return array
	 */
	public static function multiExplode(array $delimiters, string $string): array
	{
		$string = str_replace($delimiters, $delimiters[0], $string);
		return explode($delimiters[0], $string);
	}

	/**
	 * @param array $array
	 * @return array
	 */
	public static function arrayValuesRecursive(array $array): array
	{
		$temp = array();
		foreach ($array as $key => $value) {
			if (is_numeric($key)) {
				$temp[] = is_array($value) ? self::arrayValuesRecursive($value) : $value;
			}
			else {
				$temp[$key] = is_array($value) ? self::arrayValuesRecursive($value) : $value;
			}
		}
		return $temp;
	}

	/**
	 * Random Element - Takes an array as input and returns a random element
	 * @param array $array
	 * @return mixed depends on what the array contains
	 */
	public static function getRandomValue(array $array): mixed
	{
		$values = array_values($array);
		return $values[mt_rand(0, count($values) - 1)];
		// return $array[array_rand($array)];
	}

	/**
	 * @param array $array
	 * @return mixed depends on what the array contains
	 */
	public static function getRandomKey(array $array): mixed
	{
		$keys = array_keys($array);
		return $keys[mt_rand(0, count($keys) - 1)];
	}

	/**
	 * Equivalent de la fonction in_array mais ne tenant pas compte de la casse
	 * @param mixed $needle La valeur recherchée.
	 * @param array $haystack Le tableau
	 * @param bool $strict Optionnel. S'il vaut TRUE alors in_array() vérifiera aussi que le type du paramètre needle correspond au type de la valeur trouvée dans haystack.
	 * @return bool Retourne TRUE si needle est trouvé dans le tableau, FALSE sinon.
	 */
	public static function in_array_i(mixed $needle, array $haystack, bool $strict=false): bool
	{
		return in_array(strtolower($needle), array_map('mb_strtolower', $haystack), $strict);
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

	/**
	 * @param array $values
	 * @param string $enumClassName
	 * @return array
	 */
	public static function parseEnumList(array $values, string $enumClassName): array
	{
		return self::parseEnumListFromCallable($values, [$enumClassName, 'tryFrom']);
	}

	/**
	 * @param array $values
	 * @param callable $parseFunction
	 * @return array
	 */
	public static function parseEnumListFromCallable(array $values, callable $parseFunction): array
	{
		return array_values(array_filter(array_map(static fn(mixed $value) => $parseFunction($value), $values)));
	}

	/**
	 * @param \UnitEnum[] $values
	 * @return array
	 */
	public static function enum_array_unique(array $values): array
	{
		$unique = [];
		foreach ($values as $value) {
			$key = \get_class($value) . ':' . $value->name;
			$unique[$key] = $value;
		}
		return \array_values($unique);
	}



	// ========== Génération de valeur ==========

	/**
	 * @param mixed $value
	 * @param int $nbValues
	 * @return array
	 */
	public static function getArrayWithSameValues(mixed $value, int $nbValues): array
	{
		$array = [];
		for ($nb=1; $nb<=$nbValues; $nb++) {
			$array[] = $value;
		}
		return $array;
	}

	/**
	 * @param int $numberBegin
	 * @param int $numberFinish
	 * @param int $step
	 * @return array
	 */
	public static function getArrayWithNumericValues(int $numberBegin, int $numberFinish, int $step=1): array
	{
		$array = [];
		for ($value=$numberBegin; $value<=$numberFinish; $value+=$step) {
			$array[] = $value;
		}
		return $array;
	}

	/**
	 * @param int $numberOfValues
	 * @param int $numberBegin
	 * @param int $step
	 * @return array
	 */
	public static function getArrayWithNbNumericValues(int $numberOfValues, int $numberBegin=1, int $step=1): array
	{
		$array = [];
		$value = $numberBegin;
		for ($numValue=0; $numValue<$numberOfValues; $numValue++) {
			$array[] = $value;
			$value += $step;
		}
		return $array;
	}

	/**
	 * @param string $string
	 * @param string $delimiter
	 * @param string $kv
	 * @return array
	 */
	public static function string2KeyedArray(string $string, string $delimiter = ',', string $kv = '=>'): array
	{
		if (!($a = explode($delimiter, $string))) { // create parts
			return [];
		}

		foreach ($a as $s) {
			if ($s) {
				if ($pos = strpos($s, $kv)) { // key/value delimiter
					$ka[trim(substr($s, 0, $pos))] = trim(substr($s, $pos + strlen($kv)));
				}
				else { // key delimiter not found
					$ka[] = trim($s);
				}
			}
		}
		return $ka;
	}

	// ========== Modification de valeur ==========

	/**
	 * @param array $array
	 * @param string $str
	 * @return array
	 */
	public static function concatenateStringAtBeginningOnValues(array $array, string $str): array
	{
		return self::concatenateStringOnValues($array, $str, true);
	}

	/**
	 * @param array $array
	 * @param string $str
	 * @return array
	 */
	public static function concatenateStringAtEndOnValues(array $array, string $str): array
	{
		return self::concatenateStringOnValues($array, $str, false);
	}

	/**
	 * @param array $array
	 * @param string $str
	 * @param bool $beginning
	 * @return array
	 */
	public static function concatenateStringOnValues(array $array, string $str, bool $beginning=true): array
	{
		foreach ($array as $key => $value) {
			$array[$key] = ($beginning?$str:'') . $value . (!$beginning?$str:'');
		}
		return $array;
	}

	// ========== Suppression de valeur ==========

	/**
	 * @param array $array
	 * @param array $keys
	 * @return array
	 */
	public static function deleteListKeys(array $array, array $keys=[]): array
	{
		foreach ($array as $key => $value) {
			if (in_array($key, $keys, true)) {
				unset($array[$key]);
			}
		}
		return $array;
	}

	/**
	 * @param array $array
	 * @param string $key
	 * @return array
	 */
	public static function deleteKey(array $array, string $key): array
	{
		return self::deleteListKeys($array, [$key]);
	}

	// ========== Opération sur les valeurs ==========

	/**
	 * @param array $array
	 * @return array
	 */
	public static function array_cumulative_sum(array $array): array
	{
		$cumulativeArray = [];
		$sum = 0;
		foreach ($array as $value) {
			$sum += $value;
			$cumulativeArray[] = $sum;
		}
		return $cumulativeArray;
	}


	// ========== Map ==========

	/**
	 * @param array $array
	 * @param callable $callable
	 * @return array
	 */
	public static function mapRecursive(array $array, callable $callable): array
	{
		$arrayMapped = array();
		foreach ($array as $key => $subArray) {
			if (is_array($subArray)) {
				$arrayMapped[$key] = self::mapRecursive($subArray, $callable);
			}
			else {
				$arrayMapped[$key] = $callable($subArray);
			}
		}
		return $arrayMapped;
	}

	// ========== Tri ==========

	/**
	 * @param array $array
	 * @return array
	 */
	public static function quickSort(array $array): array
	{
		if (count($array) < 2) {
			return $array;
		}

		$left = $right = array();
		reset($array);
		$pivot_key  = key($array);
		$pivot  = array_shift($array);
		foreach($array as $k => $v) {
			if ($v < $pivot) {
				$left[$k] = $v;
			}
			else {
				$right[$k] = $v;
			}
		}
		return array_merge(self::quickSort($left), array($pivot_key => $pivot), self::quickSort($right));
	}

	/**
	 * @param mixed $val1
	 * @param mixed $val2
	 * @param bool $naturalOrder
	 * @param bool $caseSensitive
	 * @return int
	 */
	public static function compareValue(mixed $val1, mixed $val2, bool $naturalOrder=false, bool $caseSensitive=false): int {
		if (is_numeric($val1) && is_numeric($val2)) {
			return $val1 <=> $val2;
		}

		if ($naturalOrder) {
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

}