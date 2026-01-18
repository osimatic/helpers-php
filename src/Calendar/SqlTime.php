<?php

namespace Osimatic\Calendar;

/**
 * Utility class for SQL TIME format manipulation and operations.
 * Handles time strings in SQL TIME format (HH:MM:SS).
 * Provides methods for:
 * - Parsing and validating SQL TIME strings
 * - Extracting time components (hour, minute, second)
 * - Creating SQL TIME strings from components
 * - Comparing times and calculating time differences
 */
class SqlTime
{
	/**
	 * Parses various time formats and returns a SQL TIME string.
	 * Handles array format from database results and ensures seconds are included.
	 * @param mixed $time Time value (string like "HH:MM" or "HH:MM:SS", or array with 'date' key)
	 * @return string|null SQL TIME format string (HH:MM:SS), or null if invalid
	 */
	public static function parse($time): ?string
	{
		if (is_array($time) && !empty($time['date'])) {
			$time = substr($time['date'], 11, 8);
		}

		// If time without seconds, add them
		if (strlen($time) === 5) {
			$time .= ':00';
		}

		return $time;
	}

	/**
	 * Validates if a SQL TIME string is valid.
	 * Checks if hour is 0-23 and minute is 0-59.
	 * @param string|null $time SQL TIME string to validate (e.g., "14:30:00")
	 * @return bool True if valid time, false otherwise
	 */
	public static function check(?string $time): bool
	{
		$timeArr = explode(':', $time);
		$hour = (int) ($timeArr[0] ?? -1);
		$minute = (int) ($timeArr[1] ?? -1);

		return ($hour >= 0 && $hour < 24 && $minute >= 0 && $minute < 60);
	}


	// ========== Extraction Methods ==========

	/**
	 * Extracts the hour component from a SQL TIME string.
	 * @param string $sqlTime SQL TIME format string (e.g., "14:30:45")
	 * @return int The hour (0-23)
	 * TODO: Extract via substr for better performance
	 */
	public static function getHour(string $sqlTime): int
	{
		return (int) date('H', strtotime('1970-01-01 '.$sqlTime));
	}

	/**
	 * Extracts the minute component from a SQL TIME string.
	 * @param string $sqlTime SQL TIME format string (e.g., "14:30:45")
	 * @return int The minute (0-59)
	 */
	public static function getMinute(string $sqlTime): int
	{
		return (int) date('i', strtotime('1970-01-01 '.$sqlTime));
	}

	/**
	 * Extracts the second component from a SQL TIME string.
	 * @param string $sqlTime SQL TIME format string (e.g., "14:30:45")
	 * @return int The second (0-59)
	 */
	public static function getSecond(string $sqlTime): int
	{
		return (int) date('s', strtotime('1970-01-01 '.$sqlTime));
	}


	// ========== Creation Methods ==========

	/**
	 * Creates a SQL TIME string from hour, minute, and second components.
	 * All components are zero-padded to 2 digits.
	 * @param int $hour The hour (0-23)
	 * @param int $minute The minute (0-59)
	 * @param int $second The second (0-59), default 0
	 * @return string SQL TIME format string (HH:MM:SS)
	 */
	public static function get(int $hour, int $minute, int $second=0): string
	{
		return sprintf('%02d', ((int) $hour)).':'.sprintf('%02d', ((int) $minute)).':'.sprintf('%02d', ((int) $second));
	}


	// ========== Comparison Methods ==========

	/**
	 * Calculates the number of seconds between two SQL TIME strings.
	 * Returns positive if sqlTime1 is after sqlTime2, negative if before.
	 * @param string $sqlTime1 First SQL TIME string (e.g., "15:00:00")
	 * @param string $sqlTime2 Second SQL TIME string (e.g., "14:00:00")
	 * @return int Number of seconds between the two times
	 */
	public static function getNbSecondsFromTime(string $sqlTime1, string $sqlTime2): int
	{
		return (int) (strtotime(date('Y-m-d').' '.$sqlTime1) - strtotime(date('Y-m-d').' '.$sqlTime2));
	}

	/**
	 * Calculates the number of seconds from now until the specified time.
	 * Returns positive if time is in the future, negative if in the past.
	 * Uses today's date with the given time.
	 * @param string $sqlTime SQL TIME format string (e.g., "14:00:00")
	 * @return int Number of seconds from now to the specified time
	 */
	public static function getNbSecondsFromNow(string $sqlTime): int
	{
		return (int) (strtotime(date('Y-m-d').' '.$sqlTime) - time());
	}

	/**
	 * Checks if the first time is before the second time.
	 * @param string $sqlTime1 First SQL TIME string
	 * @param string $sqlTime2 Second SQL TIME string
	 * @return bool True if sqlTime1 is before sqlTime2, false otherwise
	 */
	public static function isBeforeTime(string $sqlTime1, string $sqlTime2): bool
	{
		return self::getNbSecondsFromTime($sqlTime1, $sqlTime2) < 0;
	}

	/**
	 * Checks if the first time is after the second time.
	 * @param string $sqlTime1 First SQL TIME string
	 * @param string $sqlTime2 Second SQL TIME string
	 * @return bool True if sqlTime1 is after sqlTime2, false otherwise
	 */
	public static function isAfterTime(string $sqlTime1, string $sqlTime2): bool
	{
		return self::getNbSecondsFromTime($sqlTime1, $sqlTime2) > 0;
	}

	/**
	 * Checks if the specified time is before the current time.
	 * Uses today's date with the given time.
	 * @param string $sqlTime SQL TIME format string
	 * @return bool True if the time is in the past (today), false otherwise
	 */
	public static function isBeforeNow(string $sqlTime): bool
	{
		return self::getNbSecondsFromNow($sqlTime) < 0;
	}

	/**
	 * Checks if the specified time is after the current time.
	 * Uses today's date with the given time.
	 * @param string $sqlTime SQL TIME format string
	 * @return bool True if the time is in the future (today), false otherwise
	 */
	public static function isAfterNow(string $sqlTime): bool
	{
		return self::getNbSecondsFromNow($sqlTime) > 0;
	}

}