<?php

namespace Osimatic\ArrayList;

/**
 * Utility class for array operations and manipulations.
 * Provides methods for:
 * - Counting and searching array elements
 * - Finding closest values and random elements
 * - Array value generation and modification
 * - Case-insensitive and custom searches
 * - Enum and collection operations
 * - Recursive operations on nested arrays
 * - Sorting algorithms
 */
class Arr
{
	// ========== Counting Methods ==========

	/**
	 * Sums the number of values from each array passed as parameter.
	 * Accepts variable number of arrays and returns the total count of all elements.
	 * @param array ...$arrays Variable number of arrays to count
	 * @return int Total number of elements across all arrays
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

	// ========== Search Methods ==========

	/**
	 * Finds the closest value to a search number in an array.
	 * Supports three methods: 'default' (closest in either direction), 'higher' (closest value >= search), 'lower' (closest value <= search).
	 * @param float $search The value to search for
	 * @param array $array Array of numeric values to search in
	 * @param string $method Search method: 'default', 'higher', or 'lower' (default: 'default')
	 * @return float|null The closest value found, or null if array is empty
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
	 * Returns only the array items specified by the list of keys.
	 * Will return a default value for keys that don't exist in the source array.
	 * @param array $array The source array to extract values from
	 * @param array $listKeys Array of keys to extract
	 * @param mixed $default Default value to use when a key is not found (default: null)
	 * @return array Associative array with requested keys and their values or defaults
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
	 * Explodes a string using multiple delimiters.
	 * Replaces all delimiters with the first one, then performs a single explode operation.
	 * @param array $delimiters Array of delimiter strings to split by
	 * @param string $string The string to explode
	 * @return array Array of string parts
	 */
	public static function multiExplode(array $delimiters, string $string): array
	{
		$string = str_replace($delimiters, $delimiters[0], $string);
		return explode($delimiters[0], $string);
	}

	/**
	 * Recursively reindexes numeric keys in a nested array.
	 * Non-numeric keys are preserved with their original names.
	 * @param array $array The array to reindex recursively
	 * @return array The reindexed array
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
	 * Returns a random element from an array.
	 * Uses cryptographically secure random_int() for selection.
	 * @param array $array The array to select from
	 * @return mixed A random value from the array
	 */
	public static function getRandomValue(array $array): mixed
	{
		$values = array_values($array);
		return $values[random_int(0, count($values) - 1)];
		// return $array[array_rand($array)];
	}

	/**
	 * Returns a random key from an array.
	 * Uses cryptographically secure random_int() for selection.
	 * @param array $array The array to select a key from
	 * @return string|int A random key from the array
	 */
	public static function getRandomKey(array $array): string|int
	{
		$keys = array_keys($array);
		return $keys[random_int(0, count($keys) - 1)];
	}

	/**
	 * Case-insensitive equivalent of in_array().
	 * Converts both needle and haystack values to lowercase before comparison.
	 * @param mixed $needle The value to search for
	 * @param array $haystack The array to search in
	 * @param bool $strict If true, also checks that types match (default: false)
	 * @return bool True if needle is found in the array, false otherwise
	 */
	public static function in_array_i(mixed $needle, array $haystack, bool $strict=false): bool
	{
		return in_array(mb_strtolower($needle), array_map(mb_strtolower(...), $haystack), $strict);
	}

	/**
	 * Checks if ANY of the needle values exists in the haystack array.
	 * Uses array_intersect to find common values.
	 * @param array $needles The values to search for
	 * @param array $haystack The array to search in
	 * @return bool True if at least one value from needles is found in haystack, false otherwise
	 */
	public static function in_array_any(array $needles, array $haystack): bool
	{
		return !empty(array_intersect($needles, $haystack));
	}

	/**
	 * Checks if ALL of the needle values exist in the haystack array.
	 * Uses array_diff to check if any needles are missing.
	 * @param array $needles The values to search for
	 * @param array $haystack The array to search in
	 * @return bool True if all values from needles are found in haystack, false otherwise
	 */
	public static function in_array_all(array $needles, array $haystack): bool
	{
		return empty(array_diff($needles, $haystack));
	}

	/**
	 * Searches an array using a user-defined callback function.
	 * Returns the key of the first element for which the callback returns true.
	 * @param array $arr The array to search
	 * @param callable $func Callback function that receives each value and returns boolean
	 * @return string|int|false The key of the matching element, or false if not found
	 */
	public static function array_search_func(array $arr, callable $func): string|int|false
	{
		foreach ($arr as $key => $v) {
			if ($func($v)) {
				return $key;
			}
		}
		return false;
	}

	/**
	 * Searches an array for the first occurrence of any of the given values.
	 * Returns the key of the first match found.
	 * @param array $arr The array to search in
	 * @param array $values Array of values to search for
	 * @param bool $strict If true, performs strict type checking (default: true)
	 * @return string|int|false The key of the first matching value, or false if none found
	 */
	public static function array_search_values(array $arr, array $values, bool $strict=true): string|int|false
	{
		foreach ($values as $v) {
			if (false !== ($key = array_search($v, $arr, $strict))) {
				return $key;
			}
		}
		return false;
	}

	/**
	 * Parses an array of values into an array of enum instances.
	 * Uses the enum's tryFrom() method to parse each value, filtering out null results.
	 * @param array $values Array of raw values to parse
	 * @param string $enumClassName Fully qualified enum class name
	 * @return array Array of successfully parsed enum instances (indexed)
	 */
	public static function parseEnumList(array $values, string $enumClassName): array
	{
		return self::parseEnumListFromCallable($values, [$enumClassName, 'tryFrom']);
	}

	/**
	 * Parses an array of values using a custom callable function.
	 * Filters out null/false results and reindexes the array.
	 * @param array $values Array of raw values to parse
	 * @param callable $parseFunction Callable that receives a value and returns parsed result or null
	 * @return array Array of successfully parsed values (reindexed)
	 */
	public static function parseEnumListFromCallable(array $values, callable $parseFunction): array
	{
		return array_values(array_filter(array_map(static fn(mixed $value) => $parseFunction($value), $values)));
	}

	/**
	 * Removes duplicate enum values from an array.
	 * Creates unique keys based on enum class name and value name.
	 * @param \UnitEnum[] $values Array of enum instances (may contain duplicates)
	 * @return array Array of unique enum instances (reindexed)
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

	/**
	 * Removes duplicate objects from an array based on a custom unique value.
	 * Uses a callable to extract the unique identifier from each object.
	 * @param array $values Array of objects
	 * @param callable $getUniqValue Callable that receives an object and returns its unique identifier
	 * @return array Array of unique objects (reindexed)
	 */
	public static function collection_array_unique(array $values, callable $getUniqValue): array
	{
		return array_values(array_filter($values, static function($obj) use ($getUniqValue) {
			static $list = [];
			$uniqValue = $getUniqValue($obj);
			if (in_array($uniqValue, $list, true)) {
				return false;
			}
			$list[] = $uniqValue;
			return true;
		}));
	}

	// ========== Value Generation Methods ==========

	/**
	 * Creates an array filled with the same value repeated multiple times.
	 * @param mixed $value The value to repeat
	 * @param int $nbValues Number of times to repeat the value
	 * @return array Array containing the value repeated nbValues times
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
	 * Creates an array of sequential numeric values within a range.
	 * Similar to PHP's range() function but with customizable step.
	 * @param int $numberBegin Starting number (inclusive)
	 * @param int $numberFinish Ending number (inclusive)
	 * @param int $step Step increment between values (default: 1)
	 * @return array Array of numeric values from start to finish
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
	 * Creates an array with a specific number of sequential numeric values.
	 * Differs from getArrayWithNumericValues by specifying count instead of end value.
	 * @param int $numberOfValues Number of values to generate
	 * @param int $numberBegin Starting number (default: 1)
	 * @param int $step Step increment between values (default: 1)
	 * @return array Array of numberOfValues sequential numbers
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
	 * Parses a string into an associative array with key-value pairs.
	 * Example: "key1=>value1,key2=>value2" becomes ['key1'=>'value1', 'key2'=>'value2']
	 * Values without key delimiter are added with numeric keys.
	 * @param string $string The string to parse
	 * @param string $delimiter Delimiter between pairs (default: ',')
	 * @param string $kv Key-value separator (default: '=>')
	 * @return array Associative array of parsed key-value pairs
	 */
	public static function string2KeyedArray(string $string, string $delimiter = ',', string $kv = '=>'): array
	{
		if (!($a = explode($delimiter, $string))) { // create parts
			return [];
		}

		$ka = [];
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

	// ========== Value Modification Methods ==========

	/**
	 * Prepends a string to the beginning of each array value.
	 * Convenience method that calls concatenateStringOnValues with beginning=true.
	 * @param array $array The array whose values to modify
	 * @param string $str String to prepend to each value
	 * @return array Array with modified values
	 */
	public static function concatenateStringAtBeginningOnValues(array $array, string $str): array
	{
		return self::concatenateStringOnValues($array, $str, true);
	}

	/**
	 * Appends a string to the end of each array value.
	 * Convenience method that calls concatenateStringOnValues with beginning=false.
	 * @param array $array The array whose values to modify
	 * @param string $str String to append to each value
	 * @return array Array with modified values
	 */
	public static function concatenateStringAtEndOnValues(array $array, string $str): array
	{
		return self::concatenateStringOnValues($array, $str, false);
	}

	/**
	 * Concatenates a string to each array value (beginning or end).
	 * Preserves array keys while modifying values.
	 * @param array $array The array whose values to modify
	 * @param string $str String to concatenate with each value
	 * @param bool $beginning If true, prepends string; if false, appends string (default: true)
	 * @return array Array with modified values
	 */
	public static function concatenateStringOnValues(array $array, string $str, bool $beginning=true): array
	{
		foreach ($array as $key => $value) {
			$array[$key] = ($beginning?$str:'') . $value . (!$beginning?$str:'');
		}
		return $array;
	}

	// ========== Value Removal Methods ==========

	/**
	 * Removes multiple keys from an array.
	 * Returns a new array without the specified keys.
	 * @param array $array The source array
	 * @param array $keys Array of keys to remove (default: empty array)
	 * @return array Array with specified keys removed
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
	 * Removes a single key from an array.
	 * Convenience method that calls deleteListKeys with a single key.
	 * @param array $array The source array
	 * @param string $key The key to remove
	 * @return array Array with the key removed
	 */
	public static function deleteKey(array $array, string $key): array
	{
		return self::deleteListKeys($array, [$key]);
	}

	// ========== Value Operations ==========

	/**
	 * Calculates the cumulative sum of array values.
	 * Each element in the result is the sum of all previous elements plus the current one.
	 * Example: [1, 2, 3, 4] becomes [1, 3, 6, 10]
	 * @param array $array Array of numeric values
	 * @return array Array of cumulative sums
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

	// ========== Mapping Methods ==========

	/**
	 * Applies a callback function recursively to all values in a nested array.
	 * Preserves array structure and keys while transforming values.
	 * @param array $array The array to map over (can be multidimensional)
	 * @param callable $callable Function to apply to each non-array value
	 * @return array The mapped array with same structure
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

	// ========== Sorting Methods ==========

	/**
	 * Sorts an array using the QuickSort algorithm.
	 * Recursively partitions the array around a pivot element.
	 * Preserves keys from the original array.
	 * @param array $array The array to sort
	 * @return array The sorted array with preserved keys
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
	 * Compares two values with configurable comparison method.
	 * Supports numeric comparison, natural order, and case sensitivity options.
	 * Returns -1 if val1 < val2, 0 if equal, 1 if val1 > val2.
	 * @param mixed $val1 First value to compare
	 * @param mixed $val2 Second value to compare
	 * @param bool $naturalOrder If true, uses natural order comparison (default: false)
	 * @param bool $caseSensitive If true, comparison is case-sensitive (default: false)
	 * @return int Comparison result: -1, 0, or 1
	 */
	public static function compareValue(mixed $val1, mixed $val2, bool $naturalOrder=false, bool $caseSensitive=false): int {
		if (is_numeric($val1) && is_numeric($val2)) {
			return $val1 <=> $val2;
		}

		if ($naturalOrder) {
			if ($caseSensitive) {
				return strnatcmp($val1, $val2); // Natural order comparison, case-sensitive
			}
			return strnatcasecmp($val1, $val2); // Natural order comparison, case-insensitive
		}

		if ($caseSensitive) {
			return strcmp($val1, $val2); // String comparison, case-sensitive
		}
		return strcasecmp($val1, $val2); // String comparison, case-insensitive
	}

	/**
	 * @deprecated use in_array_any instead
	 */
	public static function in_array_values(array $arrayNeedle, array $haystack): bool
	{
		return self::in_array_any($arrayNeedle, $haystack);
	}

}