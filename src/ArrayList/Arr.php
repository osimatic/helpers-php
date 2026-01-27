<?php

namespace Osimatic\ArrayList;

/**
 * Utility class for array operations and manipulations.
 * Provides methods for:
 * - Array creation and value generation
 * - Searching, filtering, and existence checks
 * - Array transformations and modifications
 * - Grouping, partitioning, and aggregation
 * - Random selection and sampling
 * - Parsing and type conversion
 * - Deduplication and uniqueness
 * - Mathematical operations
 * - Key manipulation
 */
class Arr
{
	// ========== Array Creation & Generation ==========

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
	 * Wraps a value in an array if it's not already an array.
	 * Returns empty array for null values.
	 * @param mixed $value The value to wrap
	 * @return array The value wrapped in an array, or the array itself
	 */
	public static function wrap(mixed $value): array
	{
		if ($value === null) {
			return [];
		}
		return is_array($value) ? $value : [$value];
	}

	// ========== Search & Filtering ==========

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
	 * Searches an array using a user-defined callback function.
	 * Returns the key of the first element for which the callback returns true.
	 * @param array $arr The array to search
	 * @param callable $func Callback function that receives each value and returns boolean
	 * @return string|int|false The key of the matching element, or false if not found
	 */
	public static function searchByCallback(array $arr, callable $func): string|int|false
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
	public static function searchByValues(array $arr, array $values, bool $strict=true): string|int|false
	{
		foreach ($values as $v) {
			if (false !== ($key = array_search($v, $arr, $strict))) {
				return $key;
			}
		}
		return false;
	}

	/**
	 * Returns the first element of an array.
	 * Optionally filters by callback before returning.
	 * @param array $array The array to get the first element from
	 * @param callable|null $callback Optional callback to filter elements
	 * @param mixed $default Default value if no element is found (default: null)
	 * @return mixed The first element, or default if not found
	 */
	public static function first(array $array, ?callable $callback = null, mixed $default = null): mixed
	{
		if ($callback === null) {
			return empty($array) ? $default : reset($array);
		}

		foreach ($array as $value) {
			if ($callback($value)) {
				return $value;
			}
		}

		return $default;
	}

	/**
	 * Returns the last element of an array.
	 * Optionally filters by callback before returning.
	 * @param array $array The array to get the last element from
	 * @param callable|null $callback Optional callback to filter elements
	 * @param mixed $default Default value if no element is found (default: null)
	 * @return mixed The last element, or default if not found
	 */
	public static function last(array $array, ?callable $callback = null, mixed $default = null): mixed
	{
		if ($callback === null) {
			return empty($array) ? $default : end($array);
		}

		$filtered = array_filter($array, $callback);
		return empty($filtered) ? $default : end($filtered);
	}

	// ========== Contains & Existence Checks ==========

	/**
	 * Case-insensitive equivalent of in_array().
	 * Converts both needle and haystack values to lowercase before comparison.
	 * @param mixed $needle The value to search for
	 * @param array $haystack The array to search in
	 * @param bool $strict If true, also checks that types match (default: false)
	 * @return bool True if needle is found in the array, false otherwise
	 */
	public static function inArrayCaseInsensitive(mixed $needle, array $haystack, bool $strict=false): bool
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
	public static function containsAny(array $needles, array $haystack): bool
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
	public static function containsAll(array $needles, array $haystack): bool
	{
		return empty(array_diff($needles, $haystack));
	}

	// ========== Array Transformation ==========

	/**
	 * Prepends a string to the beginning of each array value.
	 * Preserves array keys while modifying values.
	 * @param array $array The array whose values to modify
	 * @param string $str String to prepend to each value
	 * @return array Array with modified values
	 */
	public static function prependToValues(array $array, string $str): array
	{
		foreach ($array as $key => $value) {
			$array[$key] = $str . $value;
		}
		return $array;
	}

	/**
	 * Appends a string to the end of each array value.
	 * Preserves array keys while modifying values.
	 * @param array $array The array whose values to modify
	 * @param string $str String to append to each value
	 * @return array Array with modified values
	 */
	public static function appendToValues(array $array, string $str): array
	{
		foreach ($array as $key => $value) {
			$array[$key] = $value . $str;
		}
		return $array;
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

		foreach ($array as $item) {
			if (!is_array($item) || $depth === 0) {
				$result[] = $item;
			} else {
				$result = array_merge($result, self::flatten($item, $depth - 1));
			}
		}

		return $result;
	}

	/**
	 * Extracts a column from an array of arrays or objects.
	 * Supports extraction by key name or callback function.
	 * @param array $array Array of arrays or objects to extract from
	 * @param string|callable $keyOrCallback Key name to extract or callback function
	 * @param string|null $indexBy Optional key to use for indexing the result (default: null)
	 * @return array Extracted values, optionally indexed by specified key
	 */
	public static function pluck(array $array, string|callable $keyOrCallback, ?string $indexBy = null): array
	{
		$result = [];

		foreach ($array as $item) {
			if (is_callable($keyOrCallback)) {
				$value = $keyOrCallback($item);
			} elseif (is_array($item)) {
				$value = $item[$keyOrCallback] ?? null;
			} else {
				$value = $item->$keyOrCallback ?? null;
			}

			if ($indexBy !== null) {
				$key = is_array($item) ? ($item[$indexBy] ?? null) : ($item->$indexBy ?? null);
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
	 * Re-indexes an array by a specific key or callback result.
	 * @param array $array Array to re-index
	 * @param string|callable $keyOrCallback Key name or callback to determine new keys
	 * @return array Re-indexed array
	 */
	public static function keyBy(array $array, string|callable $keyOrCallback): array
	{
		$result = [];

		foreach ($array as $item) {
			if (is_callable($keyOrCallback)) {
				$key = $keyOrCallback($item);
			} elseif (is_array($item)) {
				$key = $item[$keyOrCallback] ?? null;
			} else {
				$key = $item->$keyOrCallback ?? null;
			}

			if ($key !== null) {
				$result[$key] = $item;
			}
		}

		return $result;
	}

	/**
	 * Unwraps an array by returning its single element, or the array itself if it has multiple elements.
	 * Returns the array itself if it has zero or multiple elements.
	 * @param array $array The array to unwrap
	 * @return mixed Single element if array has exactly one element, otherwise the array itself
	 */
	public static function unwrap(array $array): mixed
	{
		return count($array) === 1 ? reset($array) : $array;
	}

	// ========== Grouping & Partitioning ==========

	/**
	 * Groups array elements by a key or callback result.
	 * @param array $array Array to group
	 * @param string|callable $keyOrCallback Key name or callback to determine grouping
	 * @return array Grouped array where keys are group identifiers
	 */
	public static function groupBy(array $array, string|callable $keyOrCallback): array
	{
		$result = [];

		foreach ($array as $item) {
			if (is_callable($keyOrCallback)) {
				$group = $keyOrCallback($item);
			} elseif (is_array($item)) {
				$group = $item[$keyOrCallback] ?? '';
			} else {
				$group = $item->$keyOrCallback ?? '';
			}

			$result[$group][] = $item;
		}

		return $result;
	}

	/**
	 * Partitions an array into two arrays based on a callback predicate.
	 * Returns [truthyElements, falsyElements].
	 * @param array $array Array to partition
	 * @param callable $callback Predicate function that returns boolean
	 * @return array Array containing two arrays: [truthyElements, falsyElements]
	 */
	public static function partition(array $array, callable $callback): array
	{
		$truthy = [];
		$falsy = [];

		foreach ($array as $key => $value) {
			if ($callback($value, $key)) {
				$truthy[] = $value;
			} else {
				$falsy[] = $value;
			}
		}

		return [$truthy, $falsy];
	}

	// ========== Random Selection ==========

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

	// ========== Parsing & Type Conversion ==========

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
		if (!($a = explode($delimiter, $string))) {
			return [];
		}

		$ka = [];
		foreach ($a as $s) {
			if ($s) {
				if ($pos = strpos($s, $kv)) {
					$ka[trim(substr($s, 0, $pos))] = trim(substr($s, $pos + strlen($kv)));
				}
				else {
					$ka[] = trim($s);
				}
			}
		}
		return $ka;
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
		return self::parseListByCallback($values, [$enumClassName, 'tryFrom']);
	}

	/**
	 * Parses an array of values using a custom callable function.
	 * Filters out null/false results and reindexes the array.
	 * @param array $values Array of raw values to parse
	 * @param callable $parseFunction Callable that receives a value and returns parsed result or null
	 * @return array Array of successfully parsed values (reindexed)
	 */
	public static function parseListByCallback(array $values, callable $parseFunction): array
	{
		return array_values(array_filter(array_map(static fn(mixed $value) => $parseFunction($value), $values)));
	}

	// ========== Uniqueness & Deduplication ==========

	/**
	 * Removes duplicate enum values from an array.
	 * Creates unique keys based on enum class name and value name.
	 * @param \UnitEnum[] $values Array of enum instances (may contain duplicates)
	 * @return array Array of unique enum instances (reindexed)
	 */
	public static function uniqueEnums(array $values): array
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
	public static function uniqueByCallback(array $values, callable $getUniqValue): array
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

	// ========== Extraction & Selection ==========

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

	// ========== Mathematical Operations ==========

	/**
	 * Calculates the cumulative sum of array values.
	 * Each element in the result is the sum of all previous elements plus the current one.
	 * Example: [1, 2, 3, 4] becomes [1, 3, 6, 10]
	 * @param array $array Array of numeric values
	 * @return array Array of cumulative sums
	 */
	public static function cumulativeSum(array $array): array
	{
		$cumulativeArray = [];
		$sum = 0;
		foreach ($array as $value) {
			$sum += $value;
			$cumulativeArray[] = $sum;
		}
		return $cumulativeArray;
	}

	// ========== Counting ==========

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

	// ========== Key Manipulation ==========

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

	// ========== Comparison & Utilities ==========

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
	public static function compareValue(mixed $val1, mixed $val2, bool $naturalOrder = false, bool $caseSensitive = false): int
	{
		if (is_numeric($val1) && is_numeric($val2)) {
			return $val1 <=> $val2;
		}

		if ($naturalOrder) {
			if ($caseSensitive) {
				return strnatcmp($val1, $val2) <=> 0; // Natural order comparison, case-sensitive
			}
			return strnatcasecmp($val1, $val2) <=> 0; // Natural order comparison, case-insensitive
		}

		if ($caseSensitive) {
			return strcmp($val1, $val2) <=> 0; // String comparison, case-sensitive
		}
		return strcasecmp($val1, $val2) <=> 0; // String comparison, case-insensitive
	}

	// ========== Permutations & Combinations ==========

	/**
	 * Generates all possible permutations of array elements.
	 * Recursively creates all possible orderings where each element appears in each position once.
	 * Elements are joined with spaces in the result.
	 * Mathematical note: Returns n! permutations for n elements.
	 * Example: ['a', 'b'] returns ['a b', 'b a']
	 * Example: ['a', 'b', 'c'] returns ['a b c', 'a c b', 'b a c', 'b c a', 'c a b', 'c b a']
	 * @param array $items Array of items to permute
	 * @return array Array of all permutations as space-separated strings
	 */
	public static function getPermutations(array $items): array
	{
		if (count($items) <= 1) {
			return $items;
		}

		$result = [];
		for ($i = 0, $iMax = count($items); $i < $iMax; ++$i) {
			$firstItem = $items[$i];
			$remainingItems = [];
			for ($j = 0, $jMax = count($items); $j < $jMax; ++$j) {
				if ($i !== $j) {
					$remainingItems[] = $items[$j];
				}
			}
			$permutations = self::getPermutations($remainingItems);
			for ($j = 0, $jMax = count($permutations); $j < $jMax; ++$j) {
				$result[] = $firstItem . ' ' . $permutations[$j];
			}
		}

		return $result;
	}

	/**
	 * Generates all possible combinations of k elements from an array.
	 * Order is NOT important (unlike permutations). Elements are joined with spaces.
	 * Mathematical note: Returns C(n,k) = n! / (k! * (n-k)!) combinations.
	 * Example: getCombinations(['a', 'b', 'c'], 2) returns ['a b', 'a c', 'b c']
	 * Example: getCombinations(['1', '2', '3', '4'], 3) returns ['1 2 3', '1 2 4', '1 3 4', '2 3 4']
	 * @param array $items Array of items to combine
	 * @param int $size Number of elements in each combination (must be > 0 and <= count($items))
	 * @return array Array of all combinations as space-separated strings
	 */
	public static function getCombinations(array $items, int $size): array
	{
		$items = array_values($items); // Reindex to ensure numeric keys
		$n = count($items);

		if ($size <= 0 || $size > $n) {
			return [];
		}

		if ($size === 1) {
			return $items;
		}

		if ($size === $n) {
			return [implode(' ', $items)];
		}

		$result = [];
		for ($i = 0; $i <= $n - $size; ++$i) {
			$first = $items[$i];
			$remaining = array_slice($items, $i + 1);
			$subCombinations = self::getCombinations($remaining, $size - 1);

			foreach ($subCombinations as $combination) {
				$result[] = $first . ' ' . $combination;
			}
		}

		return $result;
	}

	/**
	 * Generates the power set of an array (all possible combinations of all sizes).
	 * Returns all subsets from size 0 (empty set) to size n (full set).
	 * Mathematical note: Returns 2^n subsets for n elements.
	 * Example: getPowerSet(['a', 'b']) returns [[], ['a'], ['b'], ['a', 'b']]
	 * Example: getPowerSet(['x', 'y', 'z']) returns 8 subsets (2^3)
	 * @param array $items Array of items
	 * @param bool $includeEmpty If true, includes empty subset; if false, excludes it (default: true)
	 * @return array Array of all subsets (each subset is an array)
	 */
	public static function getPowerSet(array $items, bool $includeEmpty = true): array
	{
		$items = array_values($items); // Reindex
		$n = count($items);
		$powerSetSize = pow(2, $n);
		$result = [];

		for ($i = 0; $i < $powerSetSize; ++$i) {
			$subset = [];
			for ($j = 0; $j < $n; ++$j) {
				// Check if jth bit is set in counter i
				if ($i & (1 << $j)) {
					$subset[] = $items[$j];
				}
			}

			// Skip empty subset if requested
			if (!$includeEmpty && empty($subset)) {
				continue;
			}

			$result[] = $subset;
		}

		return $result;
	}

	// ========================================
	// DEPRECATED METHODS (Backward Compatibility)
	// ========================================
	// These methods are kept for backward compatibility and will be removed in a future major version.
	// Please update your code to use the new method names.

	/**
	 * @deprecated Use searchByCallback() instead
	 */
	public static function array_search_func(array $arr, callable $func): string|int|false
	{
		return self::searchByCallback($arr, $func);
	}

	/**
	 * @deprecated Use searchByValues() instead
	 */
	public static function array_search_values(array $arr, array $values, bool $strict=true): string|int|false
	{
		return self::searchByValues($arr, $values, $strict);
	}

	/**
	 * @deprecated Use cumulativeSum() instead
	 */
	public static function array_cumulative_sum(array $array): array
	{
		return self::cumulativeSum($array);
	}

	/**
	 * @deprecated Use uniqueEnums() instead
	 */
	public static function enum_array_unique(array $values): array
	{
		return self::uniqueEnums($values);
	}

	/**
	 * @deprecated Use uniqueByCallback() instead
	 */
	public static function collection_array_unique(array $values, callable $getUniqValue): array
	{
		return self::uniqueByCallback($values, $getUniqValue);
	}

	/**
	 * @deprecated Use inArrayCaseInsensitive() instead
	 */
	public static function in_array_i(mixed $needle, array $haystack, bool $strict=false): bool
	{
		return self::inArrayCaseInsensitive($needle, $haystack, $strict);
	}

	/**
	 * @deprecated Use containsAny() instead
	 */
	public static function in_array_any(array $needles, array $haystack): bool
	{
		return self::containsAny($needles, $haystack);
	}

	/**
	 * @deprecated Use containsAll() instead
	 */
	public static function in_array_all(array $needles, array $haystack): bool
	{
		return self::containsAll($needles, $haystack);
	}

	/**
	 * @deprecated Use parseListByCallback() instead
	 */
	public static function parseEnumListFromCallable(array $values, callable $parseFunction): array
	{
		return self::parseListByCallback($values, $parseFunction);
	}

	/**
	 * @deprecated Use prependToValues() instead
	 */
	public static function concatenateStringAtBeginningOnValues(array $array, string $str): array
	{
		return self::prependToValues($array, $str);
	}

	/**
	 * @deprecated Use appendToValues() instead
	 */
	public static function concatenateStringAtEndOnValues(array $array, string $str): array
	{
		return self::appendToValues($array, $str);
	}

	/**
	 * @deprecated Internal method, use prependToValues() or appendToValues() instead
	 */
	public static function concatenateStringOnValues(array $array, string $str, bool $beginning=true): array
	{
		return $beginning ? self::prependToValues($array, $str) : self::appendToValues($array, $str);
	}

	/**
	 * @deprecated Use MultidimensionalArray::reindexRecursive() instead
	 */
	public static function arrayValuesRecursive(array $array): array
	{
		return \Osimatic\ArrayList\MultidimensionalArray::reindexRecursive($array);
	}

	/**
	 * @deprecated Use MultidimensionalArray::mapRecursive() instead
	 */
	public static function mapRecursive(array $array, callable $callable): array
	{
		return \Osimatic\ArrayList\MultidimensionalArray::mapRecursive($array, $callable);
	}

	/**
	 * @deprecated Use containsAny() instead
	 */
	public static function in_array_values(array $arrayNeedle, array $haystack): bool
	{
		return self::containsAny($arrayNeedle, $haystack);
	}
}