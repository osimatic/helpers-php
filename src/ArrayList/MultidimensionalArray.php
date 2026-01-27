<?php

namespace Osimatic\ArrayList;

/**
 * Utility class for multidimensional array operations and manipulations.
 * Provides methods for:
 * - Recursive counting and depth analysis
 * - Dot notation access (get, set, has, forget, pull)
 * - Extracting values from nested structures
 * - Advanced searching with conditions (where, whereIn, whereNotNull)
 * - Recursive transformations (map, filter, flatten)
 * - Modifying nested structures
 * - Advanced sorting with multiple criteria
 * - Array comparison and merging
 */
class MultidimensionalArray
{
	// ========== Counting & Analysis ==========

	/**
	 * Recursively counts all non-array values in a multidimensional array.
	 * Counts all scalar values at all nesting levels.
	 * @param array $array The multidimensional array to count
	 * @return int Total number of non-array values at all nesting levels
	 */
	public static function count(array $array): int
	{
		$count = 0;
		foreach ($array as $value) {
			if (is_array($value)) {
				$count += self::count($value);
			} else {
				$count++;
			}
		}
		return $count;
	}

	/**
	 * Calculates the maximum depth of a multidimensional array.
	 * Returns 1 for a flat array, 2 for array of arrays, etc.
	 * @param array $array The array to analyze
	 * @return int Maximum depth (minimum 1 for non-empty array, 0 for empty)
	 */
	public static function depth(array $array): int
	{
		if (empty($array)) {
			return 0;
		}

		$maxDepth = 1;
		foreach ($array as $value) {
			if (is_array($value)) {
				$depth = 1 + self::depth($value);
				$maxDepth = max($maxDepth, $depth);
			}
		}
		return $maxDepth;
	}

	/**
	 * Checks if an array is multidimensional (contains at least one array).
	 * @param array $array The array to check
	 * @return bool True if array contains at least one array value
	 */
	public static function isMultidimensional(array $array): bool
	{
		foreach ($array as $value) {
			if (is_array($value)) {
				return true;
			}
		}
		return false;
	}

	// ========== Transformation ==========

	/**
	 * Recursively reindexes keys in a nested array with numeric indices.
	 * Can reindex all keys or only numeric keys based on parameter.
	 * @param array $array The array to reindex recursively
	 * @param bool $onlyNumeric If true, only numeric keys are reindexed; if false, all keys are reindexed (default: false)
	 * @return array The reindexed array with numeric keys at specified levels
	 */
	public static function reindexRecursive(array $array, bool $onlyNumeric = false): array
	{
		$result = [];
		foreach ($array as $key => $value) {
			if ($onlyNumeric && !is_numeric($key)) {
				// Preserve non-numeric keys if onlyNumeric is true
				$result[$key] = is_array($value) ? self::reindexRecursive($value, $onlyNumeric) : $value;
			} else {
				// Reindex with numeric keys
				$result[] = is_array($value) ? self::reindexRecursive($value, $onlyNumeric) : $value;
			}
		}
		return $result;
	}

	/**
	 * Applies a callback function recursively to all values in a nested array.
	 * Preserves array structure and keys while transforming values.
	 * @param array $array The array to map over (can be multidimensional)
	 * @param callable $callback Function to apply to each non-array value
	 * @return array The mapped array with same structure
	 */
	public static function mapRecursive(array $array, callable $callback): array
	{
		$result = [];
		foreach ($array as $key => $value) {
			$result[$key] = is_array($value) ? self::mapRecursive($value, $callback) : $callback($value);
		}
		return $result;
	}

	/**
	 * Recursively filters array elements using a callback function.
	 * Removes elements for which callback returns false at any nesting level.
	 * @param array $array The array to filter
	 * @param callable $callback Function that receives value and returns boolean
	 * @param bool $preserveStructure If true, preserves nested array structure; if false, returns flat array of matching values (default: true)
	 * @return array Filtered array with or without structure based on $preserveStructure
	 */
	public static function filterRecursive(array $array, callable $callback, bool $preserveStructure = true): array
	{
		if ($preserveStructure) {
			// Preserve nested structure
			$result = [];
			foreach ($array as $key => $value) {
				if (is_array($value)) {
					$filtered = self::filterRecursive($value, $callback, true);
					if (!empty($filtered)) {
						$result[$key] = $filtered;
					}
				} elseif ($callback($value)) {
					$result[$key] = $value;
				}
			}
			return $result;
		}
		
		// Flatten and return only matching values
		$result = [];
		foreach ($array as $value) {
			if (is_array($value)) {
				$result = array_merge($result, self::filterRecursive($value, $callback, false));
			} elseif ($callback($value)) {
				$result[] = $value;
			}
		}
		return $result;
	}

	/**
	 * Flattens a multidimensional array to a single dimension.
	 * @param array $array The array to flatten
	 * @param int $depth Maximum depth to flatten (default: PHP_INT_MAX for complete flattening)
	 * @return array The flattened array
	 */
	public static function flatten(array $array, int $depth = PHP_INT_MAX): array
	{
		$result = [];
		foreach ($array as $value) {
			if (!is_array($value) || $depth === 0) {
				$result[] = $value;
			} else {
				$result = array_merge($result, self::flatten($value, $depth - 1));
			}
		}
		return $result;
	}

	// ========== Access & Retrieval (Dot Notation) ==========

	/**
	 * Gets a value from a nested array using dot notation.
	 * Example: get($array, 'user.profile.name', 'default')
	 * @param array $array The array to search in
	 * @param string $path Dot-separated path to the value (e.g., 'user.address.city')
	 * @param mixed $default Default value if path not found (default: null)
	 * @return mixed The value at the path, or default if not found
	 */
	public static function get(array $array, string $path, mixed $default = null): mixed
	{
		$keys = explode('.', $path);
		foreach ($keys as $key) {
			if (!is_array($array) || !array_key_exists($key, $array)) {
				return $default;
			}
			$array = $array[$key];
		}
		return $array;
	}

	/**
	 * Checks if a path exists in a nested array using dot notation.
	 * Example: has($array, 'user.profile.name')
	 * @param array $array The array to check
	 * @param string $path Dot-separated path to check
	 * @return bool True if the path exists, false otherwise
	 */
	public static function has(array $array, string $path): bool
	{
		$keys = explode('.', $path);
		foreach ($keys as $key) {
			if (!is_array($array) || !array_key_exists($key, $array)) {
				return false;
			}
			$array = $array[$key];
		}
		return true;
	}

	/**
	 * Extracts a column from an array of arrays using dot notation.
	 * Supports extraction by key path or callback function.
	 * @param array $array Array of arrays to extract from
	 * @param string|callable $keyOrCallback Key path (dot notation) or callback function
	 * @param string|null $indexBy Optional key to use for indexing the result
	 * @return array Extracted values, optionally indexed by specified key
	 */
	public static function pluck(array $array, string|callable $keyOrCallback, ?string $indexBy = null): array
	{
		$result = [];
		foreach ($array as $item) {
			if (is_callable($keyOrCallback)) {
				$value = $keyOrCallback($item);
			} elseif (is_array($item)) {
				$value = self::get($item, $keyOrCallback);
				// Skip items where the key doesn't exist
				if ($value === null && !self::has($item, $keyOrCallback)) {
					continue;
				}
			} else {
				$value = $item->$keyOrCallback ?? null;
			}

			if ($indexBy !== null) {
				$key = is_array($item) ? self::get($item, $indexBy) : ($item->$indexBy ?? null);
				if ($key !== null) {
					$result[$key] = $value;
				}
			} else {
				$result[] = $value;
			}
		}
		return $result;
	}

	/**
	 * Extracts values from subarrays by a specific key, without preserving parent keys.
	 * Returns a numerically indexed array of extracted values.
	 * @param array $array Array of subarrays to search in
	 * @param string $key The key to look for in each subarray
	 * @return array Indexed array of extracted values
	 */
	public static function getValuesByKey(array $array, string $key): array
	{
		$values = [];
		foreach ($array as $subArray) {
			if (is_array($subArray) && array_key_exists($key, $subArray)) {
				$values[] = $subArray[$key];
			}
		}
		return $values;
	}

	/**
	 * Extracts values from subarrays by a specific key, preserving parent keys.
	 * Returns an associative array where keys from the parent array are preserved.
	 * @param array $array Array of subarrays to search in
	 * @param string $key The key to look for in each subarray
	 * @return array Associative array with parent keys and extracted values
	 */
	public static function getValuesWithKeysByKey(array $array, string $key): array
	{
		$values = [];
		foreach ($array as $parentKey => $subArray) {
			if (is_array($subArray) && array_key_exists($key, $subArray)) {
				$values[$parentKey] = $subArray[$key];
			}
		}
		return $values;
	}

	// ========== Search ==========

	/**
	 * Finds and returns the first subarray where a specific key has a specific value.
	 * Searches through subarrays and returns the entire matching subarray.
	 * @param array $array Array of subarrays to search in
	 * @param string $key The key to check in each subarray
	 * @param mixed $value The value to search for
	 * @param bool $strict If true, performs strict type checking (default: false)
	 * @return mixed The first matching subarray, or null if not found
	 */
	public static function findByKeyValue(array $array, string $key, mixed $value, bool $strict = false): mixed
	{
		foreach ($array as $subArray) {
			if (is_array($subArray) && array_key_exists($key, $subArray)) {
				if ($strict ? $subArray[$key] === $value : $subArray[$key] == $value) {
					return $subArray;
				}
			}
		}
		return null;
	}

	/**
	 * Checks if a specific value exists for a specific key in any subarray.
	 * Returns true if at least one subarray contains the key with the matching value.
	 * @param array $array Array of subarrays to search in
	 * @param string $key The key to check in each subarray
	 * @param mixed $value The value to search for
	 * @param bool $strict If true, performs strict type checking (default: false)
	 * @return bool True if the value is found, false otherwise
	 */
	public static function existsByKeyValue(array $array, string $key, mixed $value, bool $strict = false): bool
	{
		foreach ($array as $subArray) {
			if (is_array($subArray) && array_key_exists($key, $subArray)) {
				if ($strict ? $subArray[$key] === $value : $subArray[$key] == $value) {
					return true;
				}
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

	/**
	 * Filters array of subarrays by a condition on a specific key.
	 * Supports operators: =, ==, ===, !=, !==, <, >, <=, >=, in, not_in
	 * @param array $array Array of subarrays to filter
	 * @param string $key The key to check in each subarray
	 * @param string $operator Comparison operator
	 * @param mixed $value The value to compare against
	 * @return array Filtered array of subarrays
	 */
	public static function where(array $array, string $key, string $operator, mixed $value): array
	{
		return array_filter($array, function($item) use ($key, $operator, $value) {
			if (!is_array($item) || !array_key_exists($key, $item)) {
				return false;
			}

			$itemValue = $item[$key];

			return match($operator) {
				'=', '==' => $itemValue == $value,
				'===' => $itemValue === $value,
				'!=', '<>' => $itemValue != $value,
				'!==' => $itemValue !== $value,
				'<' => $itemValue < $value,
				'>' => $itemValue > $value,
				'<=' => $itemValue <= $value,
				'>=' => $itemValue >= $value,
				'in' => is_array($value) && in_array($itemValue, $value, false),
				'not_in' => is_array($value) && !in_array($itemValue, $value, false),
				default => false
			};
		});
	}

	/**
	 * Filters array of subarrays where a key's value is in a list of values.
	 * @param array $array Array of subarrays to filter
	 * @param string $key The key to check in each subarray
	 * @param array $values Array of values to match against
	 * @param bool $strict If true, performs strict type checking (default: false)
	 * @return array Filtered array of subarrays
	 */
	public static function whereIn(array $array, string $key, array $values, bool $strict = false): array
	{
		return array_filter($array, function($item) use ($key, $values, $strict) {
			if (!is_array($item) || !array_key_exists($key, $item)) {
				return false;
			}
			return in_array($item[$key], $values, $strict);
		});
	}

	/**
	 * Filters array of subarrays where a key's value is not null.
	 * @param array $array Array of subarrays to filter
	 * @param string $key The key to check in each subarray
	 * @return array Filtered array of subarrays
	 */
	public static function whereNotNull(array $array, string $key): array
	{
		return array_filter($array, function($item) use ($key) {
			return is_array($item) && array_key_exists($key, $item) && $item[$key] !== null;
		});
	}

	/**
	 * Finds all subarrays where a specific key has a specific value.
	 * Returns all matching subarrays (not just the first one).
	 * @param array $array Array of subarrays to search in
	 * @param string $key The key to check in each subarray
	 * @param mixed $value The value to search for
	 * @param bool $strict If true, performs strict type checking (default: false)
	 * @return array Array of all matching subarrays
	 */
	public static function findAll(array $array, string $key, mixed $value, bool $strict = false): array
	{
		$results = [];
		foreach ($array as $subArray) {
			if (is_array($subArray) && array_key_exists($key, $subArray)) {
				if ($strict ? $subArray[$key] === $value : $subArray[$key] == $value) {
					$results[] = $subArray;
				}
			}
		}
		return $results;
	}

	// ========== Modification ==========

	/**
	 * Sets a value in a nested array using dot notation.
	 * Creates intermediate arrays if they don't exist.
	 * Example: set($array, 'user.profile.name', 'John')
	 * @param array $array The array to modify (passed by reference)
	 * @param string $path Dot-separated path where to set the value
	 * @param mixed $value The value to set
	 * @return void
	 */
	public static function set(array &$array, string $path, mixed $value): void
	{
		$keys = explode('.', $path);
		$current = &$array;

		foreach ($keys as $i => $key) {
			if ($i === count($keys) - 1) {
				$current[$key] = $value;
			} else {
				if (!isset($current[$key]) || !is_array($current[$key])) {
					$current[$key] = [];
				}
				$current = &$current[$key];
			}
		}
	}

	/**
	 * Adds a key-value pair to all subarrays at first level.
	 * Modifies the array in-place by reference.
	 * @param array $array The array to modify (passed by reference)
	 * @param string $key The key to add to each subarray
	 * @param mixed $value The value to assign to the new key
	 * @return void
	 */
	public static function addKeyAndValue(array &$array, string $key, mixed $value): void
	{
		foreach ($array as $arrayKey => $subArray) {
			if (is_array($subArray)) {
				$array[$arrayKey][$key] = $value;
			}
		}
	}

	/**
	 * Adds a key-value pair to all arrays at all nesting levels.
	 * Recursively modifies the array in-place by reference.
	 * @param array $array The array to modify (passed by reference)
	 * @param string $key The key to add to each array
	 * @param mixed $value The value to assign to the new key
	 * @return void
	 */
	public static function addKeyAndValueRecursive(array &$array, string $key, mixed $value): void
	{
		$array[$key] = $value;
		foreach ($array as $arrayKey => &$subArray) {
			if (is_array($subArray) && $arrayKey !== $key) {
				self::addKeyAndValueRecursive($subArray, $key, $value);
			}
		}
	}

	/**
	 * Removes a value from a nested array using dot notation.
	 * Example: forget($array, 'user.profile.name')
	 * @param array $array The array to modify (passed by reference)
	 * @param string $path Dot-separated path to remove
	 * @return void
	 */
	public static function forget(array &$array, string $path): void
	{
		$keys = explode('.', $path);
		$current = &$array;

		foreach ($keys as $i => $key) {
			if ($i === count($keys) - 1) {
				unset($current[$key]);
				return;
			}

			if (!isset($current[$key]) || !is_array($current[$key])) {
				return;
			}

			$current = &$current[$key];
		}
	}

	/**
	 * Retrieves a value from a nested array using dot notation and removes it.
	 * Example: pull($array, 'user.profile.name', 'default')
	 * @param array $array The array to modify (passed by reference)
	 * @param string $path Dot-separated path to retrieve and remove
	 * @param mixed $default Default value if path not found
	 * @return mixed The value at the path, or default if not found
	 */
	public static function pull(array &$array, string $path, mixed $default = null): mixed
	{
		$value = self::get($array, $path, $default);
		self::forget($array, $path);
		return $value;
	}

	// ========== Merge & Comparison ==========

	/**
	 * Recursively merges two or more arrays.
	 * Later arrays override earlier ones for non-array values.
	 * Nested arrays are merged recursively.
	 * @param array ...$arrays Arrays to merge
	 * @return array Merged array
	 */
	public static function mergeRecursive(array ...$arrays): array
	{
		$result = [];
		foreach ($arrays as $array) {
			foreach ($array as $key => $value) {
				if (is_array($value) && isset($result[$key]) && is_array($result[$key])) {
					$result[$key] = self::mergeRecursive($result[$key], $value);
				} else {
					$result[$key] = $value;
				}
			}
		}
		return $result;
	}

	/**
	 * Computes the difference between arrays at first level.
	 * Returns elements in first array that are not in other arrays.
	 * @param array $array1 The array to compare from
	 * @param array ...$arrays Arrays to compare against
	 * @return array Array containing elements from array1 not in other arrays
	 */
	public static function diff(array $array1, array ...$arrays): array
	{
		return array_diff($array1, ...$arrays);
	}

	/**
	 * Recursively computes the difference between nested arrays.
	 * Compares array structures deeply.
	 * @param array $array1 The array to compare from
	 * @param array $array2 The array to compare against
	 * @return array Array containing differences
	 */
	public static function diffRecursive(array $array1, array $array2): array
	{
		$diff = [];
		foreach ($array1 as $key => $value) {
			if (!array_key_exists($key, $array2)) {
				$diff[$key] = $value;
			} elseif (is_array($value) && is_array($array2[$key])) {
				$recursiveDiff = self::diffRecursive($value, $array2[$key]);
				if (!empty($recursiveDiff)) {
					$diff[$key] = $recursiveDiff;
				}
			} elseif ($value !== $array2[$key]) {
				$diff[$key] = $value;
			}
		}
		return $diff;
	}

	/**
	 * Computes the intersection of arrays.
	 * Returns elements present in all arrays.
	 * @param array ...$arrays Arrays to intersect
	 * @return array Array containing elements present in all arrays
	 */
	public static function intersect(array ...$arrays): array
	{
		if (empty($arrays)) {
			return [];
		}
		return array_intersect(...$arrays);
	}

	// ========== Sorting ==========

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
	 * @param array $subArray1 First subarray to compare
	 * @param array $subArray2 Second subarray to compare
	 * @return int Comparison result: -1, 0, or 1
	 */
	private static function sortCompareRecursive(array $subArray1, array $subArray2): int
	{
		if (!isset(self::$sortListColumnSorting[self::$sortDepth])) {
			self::$sortDepth = 0;
			return 0;
		}

		$ascending = self::$sortListColumnSorting[self::$sortDepth][1] ?? true;
		$naturalOrder = self::$sortListColumnSorting[self::$sortDepth][2] ?? false;
		$caseSensitive = self::$sortListColumnSorting[self::$sortDepth][3] ?? false;

		$currentColumn = self::$sortListColumnSorting[self::$sortDepth][0];

		$val1 = $ascending ? $subArray1[$currentColumn] : $subArray2[$currentColumn];
		$val2 = $ascending ? $subArray2[$currentColumn] : $subArray1[$currentColumn];

		$comparison = Arr::compareValue($val1, $val2, $naturalOrder, $caseSensitive);

		if ($comparison === 0) {
			self::$sortDepth++;
			return self::sortCompareRecursive($subArray1, $subArray2);
		}

		self::$sortDepth = 0;
		return $comparison;
	}

	/**
	 * Recursively sorts an array by keys at all nesting levels.
	 * Applies ksort() to the main array and recursively to all nested arrays.
	 * Modifies the array in-place by reference.
	 * @param array $array The array to sort recursively (passed by reference)
	 * @param int $sortFlags Sort behavior flags (SORT_REGULAR, SORT_NUMERIC, SORT_STRING, etc.)
	 * @return void
	 */
	public static function ksortRecursive(array &$array, int $sortFlags = SORT_REGULAR): void
	{
		ksort($array, $sortFlags);
		foreach ($array as &$value) {
			if (is_array($value)) {
				self::ksortRecursive($value, $sortFlags);
			}
		}
	}

	// ========== DEPRECATED METHODS ==========
	// Backward compatibility. Will be removed in a future major version. Please update your code to use the new method names.

	/**
	 * @deprecated Use getValuesByKey() instead (typo fix: keyarray → key)
	 */
	public static function getValuesByKeyarray(array $array, $key): array
	{
		return self::getValuesByKey($array, $key);
	}

	/**
	 * @deprecated Use findByKeyValue() instead (renamed for clarity, added strict parameter)
	 */
	public static function getValue(array $array, $value, $key): mixed
	{
		return self::findByKeyValue($array, $key, $value, false);
	}

	/**
	 * @deprecated Use existsByKeyValue() instead (renamed for clarity, added strict parameter)
	 */
	public static function isValueExist(array $array, $value, $key): bool
	{
		return self::existsByKeyValue($array, $key, $value, false);
	}
}