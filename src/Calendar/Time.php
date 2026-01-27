<?php

namespace Osimatic\Calendar;

/**
 * Time utility class providing static methods for time parsing, validation, and formatting.
 * This class contains methods that do NOT take DateTime objects as parameters.
 * For methods that work with DateTime objects, see the DateTime class.
 *
 * Organized categories:
 * - Creation: Create DateTime objects with specific times
 * - Parsing: Parse time strings in various formats
 * - Validation: Validate time components and strings
 * - Formatting: Format time components (hours, durations)
 */
class Time
{
	// ========== Creation Methods ==========

	/**
	 * Creates a DateTime object from time components on today's date.
	 * @param int $hour The hour (0-23)
	 * @param int $minute The minute (0-59, default: 0)
	 * @param int $second The second (0-59, default: 0)
	 * @return \DateTime|null DateTime object if valid, null otherwise
	 */
	public static function create(int $hour, int $minute = 0, int $second = 0): ?\DateTime
	{
		if (!self::check($hour, $minute, $second)) {
			return null;
		}

		try {
			$dateTime = new \DateTime();
			$dateTime->setTime($hour, $minute, $second);
			return $dateTime;
		} catch (\Exception) {}
		return null;
	}

	/**
	 * Creates a DateTime object from time components (alias for create).
	 * @param int $hour The hour (0-23)
	 * @param int $minute The minute (0-59)
	 * @param int $second The second (0-59)
	 * @return \DateTime|null DateTime object if valid, null otherwise
	 */
	public static function createFromComponents(int $hour, int $minute, int $second = 0): ?\DateTime
	{
		return self::create($hour, $minute, $second);
	}

	// ========== Parsing Methods ==========

	/**
	 * Parses a time string and returns it as a DateTime object.
	 * The date portion is set to today's date, only the time is parsed.
	 * Supports various input formats with configurable separators and component positions.
	 * @param string|null $enteredTime The time string to parse (e.g., "14:30", "2h30m", "14:30:00")
	 * @param string $separator The separator character between components (default: ":")
	 * @param int $hourPos The position of hour in the string (1-indexed, default: 1)
	 * @param int $minutePos The position of minute in the string (1-indexed, default: 2)
	 * @param int $secondPos The position of second in the string (1-indexed, default: 3)
	 * @return \DateTime|null A DateTime object with today's date and parsed time, or null if parsing fails
	 */
	public static function parse(?string $enteredTime, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): ?\DateTime
	{
		if (null === ($sqlTime = self::parseToSqlTime($enteredTime, $separator, $hourPos, $minutePos, $secondPos))) {
			return null;
		}

		return DateTime::parseFromSqlDateTime(date('Y-m-d') . ' ' . $sqlTime);
	}

	/**
	 * Parses a time string and returns it in SQL TIME format (HH:MM:SS).
	 * Supports various input formats with configurable separators and component positions.
	 * Handles alphanumeric separators by extracting all numbers from the string.
	 * @param string|null $enteredTime The time string to parse (e.g., "14:30", "2h30m", "14:30:00")
	 * @param string $separator The separator character between components (default: ":")
	 * @param int $hourPos The position of hour in the string (1-indexed, default: 1)
	 * @param int $minutePos The position of minute in the string (1-indexed, default: 2)
	 * @param int $secondPos The position of second in the string (1-indexed, default: 3)
	 * @return string|null The SQL TIME format string (HH:MM:SS), or null if parsing fails
	 */
	public static function parseToSqlTime(?string $enteredTime, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): ?string
	{
		if (null === ($timeArray = self::_parse($enteredTime, $separator, $hourPos, $minutePos, $secondPos))) {
			return null;
		}

		return date('H:i:s', mktime($timeArray[0], $timeArray[1], $timeArray[2]));
	}

	/**
	 * Internal method to parse a time string into components.
	 * Handles various separator types including alphanumeric (e.g., "2h30m").
	 * When alphanumeric separators are detected, all numbers are extracted.
	 * @param string|null $enteredTime The time string to parse
	 * @param string $separator The separator character between components (default: ":")
	 * @param int $hourPos The position of hour in the string (1-indexed, default: 1)
	 * @param int $minutePos The position of minute in the string (1-indexed, default: 2)
	 * @param int $secondPos The position of second in the string (1-indexed, default: 3)
	 * @return array|null Array of [hour, minute, second] integers, or null if parsing fails
	 */
	public static function _parse(?string $enteredTime, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): ?array
	{
		if (empty($enteredTime)) {
			return null;
		}

		// If separator is not standard, extract all numbers
		if (preg_match('/\d+[a-zA-Z]+\d+/', $enteredTime)) {
			// Contains alphanumeric separators, extract numbers
			preg_match_all('/\d+/', $enteredTime, $matches);
			$timeArray = $matches[0];
		} else {
			$timeArray = explode($separator, $enteredTime);
		}

		--$hourPos;
		--$minutePos;
		--$secondPos;

		if (!isset($timeArray[$hourPos]) || !is_numeric($timeArray[$hourPos])) {
			return null;
		}
		if (!isset($timeArray[$minutePos]) || !is_numeric($timeArray[$minutePos])) {
			return null;
		}
		if (isset($timeArray[$secondPos]) && !is_numeric($timeArray[$secondPos])) {
			return null;
		}

		$hour = (int) $timeArray[$hourPos];
		$minute = (int) $timeArray[$minutePos];
		$second = (int) ($timeArray[$secondPos] ?? 0);

		if (!self::check($hour, $minute, $second)) {
			return null;
		}

		return [$hour, $minute, $second];
	}

	// ========== Validation Methods ==========

	/**
	 * Validates time components are within valid ranges.
	 * Hour must be 0-23, minute 0-59, second 0-59.
	 * @param int $hour The hour (0-23)
	 * @param int $minute The minute (0-59)
	 * @param int $second The second (0-59), default 0
	 * @return bool True if all components are valid, false otherwise
	 */
	public static function check(int $hour, int $minute, int $second=0): bool
	{
		return ($hour >= 0 && $hour < 24 && $minute >= 0 && $minute < 60 && $second >= 0 && $second < 60);
	}

	/**
	 * Validates a time string by attempting to parse it.
	 * Supports various formats with configurable separators and component positions.
	 * @param string|null $enteredTime The time string to validate (e.g., "14:30:00", "2:30pm")
	 * @param string $separator The separator character between components (default: ":")
	 * @param int $hourPos The position of hour in the string (1-indexed, default: 1)
	 * @param int $minutePos The position of minute in the string (1-indexed, default: 2)
	 * @param int $secondPos The position of second in the string (1-indexed, default: 3)
	 * @return bool True if the time string is valid and parseable, false otherwise
	 */
	public static function checkValue(?string $enteredTime, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): bool
	{
		return null !== self::_parse($enteredTime, $separator, $hourPos, $minutePos, $secondPos);
	}

	// ========== Formatting Methods ==========

	/**
	 * Formats an hour value as a string with "h" suffix.
	 * Always returns a zero-padded 2-digit hour.
	 * @param int $hour The hour value (0-23)
	 * @return string The formatted hour (e.g., "09h", "14h")
	 */
	public static function formatHour(int $hour): string
	{
		return sprintf('%02d', $hour).'h';
	}

	/**
	 * Formats a duration in seconds as a human-readable string.
	 * @param int $seconds The duration in seconds
	 * @param bool $short Whether to use short format (default: false)
	 * @return string Formatted duration (e.g., "2h 30m 45s" or "2:30:45")
	 */
	public static function formatDuration(int $seconds, bool $short = false): string
	{
		$hours = floor($seconds / 3600);
		$minutes = floor(($seconds % 3600) / 60);
		$secs = $seconds % 60;

		if ($short) {
			return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
		}

		$parts = [];
		if ($hours > 0) {
			$parts[] = $hours . 'h';
		}
		if ($minutes > 0) {
			$parts[] = $minutes . 'm';
		}
		if ($secs > 0 || empty($parts)) {
			$parts[] = $secs . 's';
		}

		return implode(' ', $parts);
	}
}