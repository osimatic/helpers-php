<?php

namespace Osimatic\ArrayList;

/**
 * Utility class for multidimensional array operations and manipulations.
 * Provides methods for:
 * - Recursive counting of nested arrays
 * - Extracting values from nested structures by keys
 * - Modifying all subarrays with new key-value pairs
 * - Searching for values within nested arrays
 * - Advanced sorting of multidimensional arrays with multiple criteria
 */
class MultidimensionalArray
{
	// ========== Counting Methods ==========

	/**
	 * Recursively counts all values in a multidimensional array.
	 * Counts all values in the main array and all nested child arrays, with no depth limit.
	 * @param array $array The multidimensional array to count
	 * @return int Total number of values (non-array elements) at all nesting levels
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


	// ========== Retrieval Methods ==========

	/**
	 * Extracts values from subarrays by a specific key, preserving parent keys.
	 * Returns an associative array where keys from the parent array are preserved.
	 * @param array $array Array of subarrays to search in
	 * @param mixed $key The key to look for in each subarray
	 * @return array Associative array with parent keys and extracted values
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
	 * Extracts values from subarrays by a specific key, without preserving parent keys.
	 * Returns a numerically indexed array of extracted values.
	 * @param array $array Array of subarrays to search in
	 * @param mixed $key The key to look for in each subarray
	 * @return array Indexed array of extracted values
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

	// ========== Modification Methods ==========

	/**
	 * Adds a key-value pair to all subarrays in the multidimensional array.
	 * Modifies the array in-place by reference.
	 * @param array $array The multidimensional array to modify (passed by reference)
	 * @param mixed $keyAdded The key to add to each subarray
	 * @param mixed $valueAdded The value to assign to the new key
	 * @return void
	 */
	public static function addKeyAndValue(array &$array, $keyAdded, $valueAdded): void
	{
		foreach ($array as $key => $tabGraph) {
			$array[$key][$keyAdded] = $valueAdded;
		}
	}

	// ========== Search Methods ==========

	/**
	 * Finds and returns the first subarray where a specific key has a specific value.
	 * Searches through subarrays and returns the entire matching subarray.
	 * @param array $array Array of subarrays to search in
	 * @param mixed $value The value to search for
	 * @param mixed $key The key to check in each subarray
	 * @return mixed The first matching subarray, or null if not found
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
	 * Checks if a specific value exists for a specific key in any subarray.
	 * Returns true if at least one subarray contains the key with the matching value.
	 * @param array $array Array of subarrays to search in
	 * @param mixed $value The value to search for
	 * @param mixed $key The key to check in each subarray
	 * @return bool True if the value is found, false otherwise
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
	 * Recursive equivalent of in_array() for multidimensional arrays.
	 * Searches for a value at any depth in the array structure.
	 * @param mixed $needle The value to search for
	 * @param array $haystack The multidimensional array to search in
	 * @param bool $strict If true, performs strict type checking (default: false)
	 * @return bool True if needle is found in the array at any level, false otherwise
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

	// ========== Sorting Methods ==========

	private static ?array $sortListColumnSorting = null;
	private static ?int $sortDepth = null;

	/**
	 * Sorts a multidimensional array by one or more keys with configurable criteria.
	 * Modifies the array in-place by reference.
	 * Supports multiple sort criteria with fallback to next criterion when values are equal.
	 *
	 * Sort criteria format:
	 * - Simple: ['keyName'] - sorts by single key, ascending
	 * - Single criterion: ['keyName', ascending, naturalOrder, caseSensitive]
	 *   - Index 0: key name (string or int)
	 *   - Index 1: true for ascending, false for descending (default: true)
	 *   - Index 2: true for natural order sort, false for normal (default: false)
	 *   - Index 3: true for case-sensitive sort, false for case-insensitive (default: false)
	 * - Multiple criteria: [['key1', true], ['key2', false]] - array of criterion arrays
	 *
	 * @param array $array The multidimensional array to sort (passed by reference)
	 * @param array $listColumnSorting Sort criteria (see format above)
	 * @return void
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

	/**
	 * Recursive comparison function for multidimensional array sorting.
	 * Compares two subarrays according to sort criteria, recursively moving to next criterion if values are equal.
	 * Uses static properties to maintain state across recursive calls.
	 * @param array $sousArray1 First subarray to compare
	 * @param array $sousArray2 Second subarray to compare
	 * @return int Comparison result: -1, 0, or 1
	 */
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

	/**
	 * Compares two values with configurable comparison method.
	 * Supports numeric comparison, natural order, and case sensitivity options.
	 * @param mixed $val1 First value to compare
	 * @param mixed $val2 Second value to compare
	 * @param bool $ordreNaturel If true, uses natural order comparison (default: false)
	 * @param bool $caseSensitive If true, comparison is case-sensitive (default: false)
	 * @return int Comparison result: -1 if val1 < val2, 0 if equal, 1 if val1 > val2
	 */
	private static function compareValue($val1, $val2, bool $ordreNaturel=false, bool $caseSensitive=false): int
	{
		if (is_numeric($val1) && is_numeric($val2)) {
			return $val1 <=> $val2;
		}

		if ($ordreNaturel) {
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
	 * Recursively sorts an array by keys at all nesting levels.
	 * Applies ksort() to the main array and recursively to all nested arrays.
	 * Modifies the array in-place by reference.
	 * @param array $array The array to sort recursively (passed by reference)
	 * @param int $sort_flags Sort behavior flags (SORT_REGULAR, SORT_NUMERIC, SORT_STRING, etc.)
	 * @return void
	 */
	public static function ksortRecursive(array &$array, int $sort_flags = SORT_REGULAR): void
	{
		ksort($array, $sort_flags);
		foreach ($array as &$arr) {
			if (is_array($arr)) {
				self::ksortRecursive($arr, $sort_flags);
			}
		}
	}
}