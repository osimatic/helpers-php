<?php

namespace Osimatic\Calendar;

/**
 * Utility class for SQL TIME format manipulation and operations.
 * Handles time strings in SQL TIME format (HH:MM:SS).
 * This class works exclusively with SQL TIME strings, not DateTime objects.
 *
 * Organized categories:
 * - Parsing & Validation: Parse and validate SQL TIME strings
 * - Extraction: Extract time components (hour, minute, second)
 * - Creation: Create SQL TIME strings from components
 * - Conversion: Convert between SQL TIME and other formats
 * - Calculation: Add/subtract time units, calculate differences
 * - Comparison: Compare times, check before/after
 * - Formatting: Format SQL TIME strings
 */
class SqlTime
{
	// ========== Parsing & Validation Methods ==========

	/**
	 * Parses various time formats and returns a SQL TIME string.
	 * Handles array format from database results and ensures seconds are included.
	 * @param mixed $time Time value (string like "HH:MM" or "HH:MM:SS", or array with 'date' key)
	 * @return string|null SQL TIME format string (HH:MM:SS), or null if invalid
	 */
	public static function parse($time): ?string
	{
		if (empty($time)) {
			return null;
		}

		if (is_array($time) && !empty($time['date'])) {
			$time = substr($time['date'], 11, 8);
		}

		// If time without seconds, add them
		if (strlen($time) === 5) {
			$time .= ':00';
		}

		// Validate the time
		if (!self::isValid($time)) {
			return null;
		}

		return $time;
	}

	/**
	 * Validates if a SQL TIME string is valid.
	 * Checks if hour is 0-23, minute is 0-59, and second is 0-59.
	 * @param string|null $time SQL TIME string to validate (e.g., "14:30:00")
	 * @return bool True if valid time, false otherwise
	 */
	public static function isValid(?string $time): bool
	{
		if (empty($time)) {
			return false;
		}

		$timeArr = explode(':', $time);
		$hour = (int) ($timeArr[0] ?? -1);
		$minute = (int) ($timeArr[1] ?? -1);
		$second = (int) ($timeArr[2] ?? 0);

		return ($hour >= 0 && $hour < 24 && $minute >= 0 && $minute < 60 && $second >= 0 && $second < 60);
	}

	// ========== Extraction Methods ==========

	/**
	 * Extracts the hour component from a SQL TIME string.
	 * Uses substr for optimal performance.
	 * @param string $sqlTime SQL TIME format string (e.g., "14:30:45")
	 * @return int The hour (0-23)
	 */
	public static function getHour(string $sqlTime): int
	{
		return (int) substr($sqlTime, 0, 2);
	}

	/**
	 * Extracts the minute component from a SQL TIME string.
	 * Uses substr for optimal performance.
	 * @param string $sqlTime SQL TIME format string (e.g., "14:30:45")
	 * @return int The minute (0-59)
	 */
	public static function getMinute(string $sqlTime): int
	{
		return (int) substr($sqlTime, 3, 2);
	}

	/**
	 * Extracts the second component from a SQL TIME string.
	 * Uses substr for optimal performance.
	 * @param string $sqlTime SQL TIME format string (e.g., "14:30:45")
	 * @return int The second (0-59)
	 */
	public static function getSecond(string $sqlTime): int
	{
		return (int) substr($sqlTime, 6, 2);
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
	public static function create(int $hour, int $minute, int $second = 0): string
	{
		return sprintf('%02d', $hour).':'.sprintf('%02d', $minute).':'.sprintf('%02d', $second);
	}

	/**
	 * Creates a SQL TIME string for the current time.
	 * @return string SQL TIME format string for now
	 */
	public static function now(): string
	{
		return date('H:i:s');
	}

	// ========== Conversion Methods ==========

	/**
	 * Converts a SQL TIME string to a DateTime object.
	 * Uses today's date with the given time.
	 * @param string $sqlTime SQL TIME format string
	 * @return \DateTime|null DateTime object, or null if parsing fails
	 */
	public static function toDateTime(string $sqlTime): ?\DateTime
	{
		return DateTime::parseFromSqlDateTime(date('Y-m-d').' '.$sqlTime);
	}

	/**
	 * Converts a SQL TIME string to seconds since midnight.
	 * @param string $sqlTime SQL TIME format string
	 * @return int Total seconds since midnight (0-86399)
	 */
	public static function toSeconds(string $sqlTime): int
	{
		$hour = self::getHour($sqlTime);
		$minute = self::getMinute($sqlTime);
		$second = self::getSecond($sqlTime);
		return ($hour * 3600) + ($minute * 60) + $second;
	}

	/**
	 * Creates a SQL TIME string from seconds since midnight.
	 * @param int $seconds Total seconds since midnight (0-86399)
	 * @return string SQL TIME format string
	 */
	public static function fromSeconds(int $seconds): string
	{
		$hours = floor($seconds / 3600);
		$minutes = floor(($seconds % 3600) / 60);
		$secs = $seconds % 60;
		return self::create((int) $hours, (int) $minutes, (int) $secs);
	}

	// ========== Calculation Methods ==========

	/**
	 * Adds seconds to a SQL TIME string.
	 * @param string $sqlTime SQL TIME format string
	 * @param int $seconds Number of seconds to add
	 * @return string New SQL TIME format string
	 */
	public static function addSeconds(string $sqlTime, int $seconds): string
	{
		$totalSeconds = self::toSeconds($sqlTime) + $seconds;
		// Wrap around if > 86399 (24 hours)
		$totalSeconds = $totalSeconds % 86400;
		if ($totalSeconds < 0) {
			$totalSeconds += 86400;
		}
		return self::fromSeconds($totalSeconds);
	}

	/**
	 * Subtracts seconds from a SQL TIME string.
	 * @param string $sqlTime SQL TIME format string
	 * @param int $seconds Number of seconds to subtract
	 * @return string New SQL TIME format string
	 */
	public static function subSeconds(string $sqlTime, int $seconds): string
	{
		return self::addSeconds($sqlTime, -$seconds);
	}

	/**
	 * Adds minutes to a SQL TIME string.
	 * @param string $sqlTime SQL TIME format string
	 * @param int $minutes Number of minutes to add
	 * @return string New SQL TIME format string
	 */
	public static function addMinutes(string $sqlTime, int $minutes): string
	{
		return self::addSeconds($sqlTime, $minutes * 60);
	}

	/**
	 * Subtracts minutes from a SQL TIME string.
	 * @param string $sqlTime SQL TIME format string
	 * @param int $minutes Number of minutes to subtract
	 * @return string New SQL TIME format string
	 */
	public static function subMinutes(string $sqlTime, int $minutes): string
	{
		return self::addSeconds($sqlTime, -$minutes * 60);
	}

	/**
	 * Adds hours to a SQL TIME string.
	 * @param string $sqlTime SQL TIME format string
	 * @param int $hours Number of hours to add
	 * @return string New SQL TIME format string
	 */
	public static function addHours(string $sqlTime, int $hours): string
	{
		return self::addSeconds($sqlTime, $hours * 3600);
	}

	/**
	 * Subtracts hours from a SQL TIME string.
	 * @param string $sqlTime SQL TIME format string
	 * @param int $hours Number of hours to subtract
	 * @return string New SQL TIME format string
	 */
	public static function subHours(string $sqlTime, int $hours): string
	{
		return self::addSeconds($sqlTime, -$hours * 3600);
	}

	// ========== Comparison Methods ==========

	/**
	 * Calculates the number of seconds between two SQL TIME strings.
	 * Returns positive if sqlTime1 is after sqlTime2, negative if before.
	 * @param string $sqlTime1 First SQL TIME string (e.g., "15:00:00")
	 * @param string $sqlTime2 Second SQL TIME string (e.g., "14:00:00")
	 * @return int Number of seconds between the two times
	 */
	public static function getSecondsBetween(string $sqlTime1, string $sqlTime2): int
	{
		return self::toSeconds($sqlTime1) - self::toSeconds($sqlTime2);
	}

	/**
	 * Calculates the number of seconds from now until the specified time.
	 * Returns positive if time is in the future, negative if in the past.
	 * Uses today's date with the given time.
	 * @param string $sqlTime SQL TIME format string (e.g., "14:00:00")
	 * @return int Number of seconds from now to the specified time
	 */
	public static function getSecondsFromNow(string $sqlTime): int
	{
		return (int) (strtotime(date('Y-m-d').' '.$sqlTime) - time());
	}

	/**
	 * Checks if the first time is before the second time.
	 * @param string $sqlTime1 First SQL TIME string
	 * @param string $sqlTime2 Second SQL TIME string
	 * @return bool True if sqlTime1 is before sqlTime2, false otherwise
	 */
	public static function isBefore(string $sqlTime1, string $sqlTime2): bool
	{
		return self::getSecondsBetween($sqlTime1, $sqlTime2) < 0;
	}

	/**
	 * Checks if the first time is after the second time.
	 * @param string $sqlTime1 First SQL TIME string
	 * @param string $sqlTime2 Second SQL TIME string
	 * @return bool True if sqlTime1 is after sqlTime2, false otherwise
	 */
	public static function isAfter(string $sqlTime1, string $sqlTime2): bool
	{
		return self::getSecondsBetween($sqlTime1, $sqlTime2) > 0;
	}

	/**
	 * Checks if two times are equal.
	 * @param string $sqlTime1 First SQL TIME string
	 * @param string $sqlTime2 Second SQL TIME string
	 * @return bool True if times are equal
	 */
	public static function isEqual(string $sqlTime1, string $sqlTime2): bool
	{
		return $sqlTime1 === $sqlTime2;
	}

	/**
	 * Checks if the specified time is before the current time.
	 * Uses today's date with the given time.
	 * @param string $sqlTime SQL TIME format string
	 * @return bool True if the time is in the past (today), false otherwise
	 */
	public static function isBeforeNow(string $sqlTime): bool
	{
		return self::getSecondsFromNow($sqlTime) < 0;
	}

	/**
	 * Checks if the specified time is after the current time.
	 * Uses today's date with the given time.
	 * @param string $sqlTime SQL TIME format string
	 * @return bool True if the time is in the future (today), false otherwise
	 */
	public static function isAfterNow(string $sqlTime): bool
	{
		return self::getSecondsFromNow($sqlTime) > 0;
	}

	// ========== Formatting Methods ==========

	/**
	 * Formats a SQL TIME string using IntlDateFormatter.
	 * @param string $sqlTime SQL TIME format string
	 * @param string|null $locale Optional locale code
	 * @param int $timeType IntlDateFormatter time type constant
	 * @return string Formatted time string, or empty string if time is invalid
	 */
	public static function format(string $sqlTime, ?string $locale = null, int $timeType = \IntlDateFormatter::MEDIUM): string
	{
		if (empty($sqlTime) || !self::isValid($sqlTime)) {
			return '';
		}

		$dateTime = self::toDateTime($sqlTime);
		return $dateTime ? DateTime::formatTime($dateTime, $locale, $timeType) : '';
	}

	/**
	 * Formats a SQL TIME string as HH:MM:SS.
	 * This simply returns the input as SQL TIME is already in this format.
	 * @param string $sqlTime SQL TIME format string
	 * @return string Time in HH:MM:SS format, or empty string if time is invalid
	 */
	public static function formatString(string $sqlTime): string
	{
		if (empty($sqlTime) || !self::isValid($sqlTime)) {
			return '';
		}

		return $sqlTime;
	}

	/**
	 * Formats a SQL TIME string as HH:MM (without seconds).
	 * @param string $sqlTime SQL TIME format string
	 * @return string Time in HH:MM format, or empty string if time is invalid
	 */
	public static function formatShort(string $sqlTime): string
	{
		if (empty($sqlTime) || !self::isValid($sqlTime)) {
			return '';
		}

		$dateTime = self::toDateTime($sqlTime);
		return $dateTime ? DateTime::formatTimeShort($dateTime) : '';
	}

	/**
	 * Formats a SQL TIME string as HH:MM:SS or HH:MM.
	 * @param string $sqlTime SQL TIME format string
	 * @param bool $includeSeconds Whether to include seconds (default: true)
	 * @return string Formatted time string, or empty string if time is invalid
	 */
	public static function formatLong(string $sqlTime, bool $includeSeconds = true): string
	{
		if (empty($sqlTime) || !self::isValid($sqlTime)) {
			return '';
		}

		$dateTime = self::toDateTime($sqlTime);
		return $dateTime ? DateTime::formatTimeLong($dateTime, $includeSeconds) : '';
	}

	/**
	 * Formats a SQL TIME string in ISO 8601 format (HH:MM:SS).
	 * Since SQL TIME is already in ISO 8601 format, this simply returns the input.
	 * This method exists for API consistency and explicit ISO 8601 compliance indication.
	 * @param string $sqlTime SQL TIME format string
	 * @return string ISO 8601 formatted time (HH:MM:SS), or empty string if time is invalid
	 */
	public static function formatISO(string $sqlTime): string
	{
		if (empty($sqlTime) || !self::isValid($sqlTime)) {
			return '';
		}

		return $sqlTime;
	}

	// ========== DEPRECATED METHODS ==========
	// Backward compatibility. Will be removed in a future major version. Please update your code to use the new method names.

	/**
	 * @deprecated Use isValid() instead
	 * @param string|null $time SQL TIME string to validate
	 * @return bool True if valid time, false otherwise
	 */
	public static function check(?string $time): bool
	{
		return self::isValid($time);
	}

	/**
	 * @deprecated Use create() instead
	 * @param int $hour The hour (0-23)
	 * @param int $minute The minute (0-59)
	 * @param int $second The second (0-59)
	 * @return string SQL TIME format string
	 */
	public static function get(int $hour, int $minute, int $second = 0): string
	{
		return self::create($hour, $minute, $second);
	}

	/**
	 * @deprecated Use getSecondsBetween() instead
	 * @param string $sqlTime1 First SQL TIME string
	 * @param string $sqlTime2 Second SQL TIME string
	 * @return int Number of seconds between the two times
	 */
	public static function getNbSecondsFromTime(string $sqlTime1, string $sqlTime2): int
	{
		return self::getSecondsBetween($sqlTime1, $sqlTime2);
	}

	/**
	 * @deprecated Use getSecondsFromNow() instead
	 * @param string $sqlTime SQL TIME format string
	 * @return int Number of seconds from now to the specified time
	 */
	public static function getNbSecondsFromNow(string $sqlTime): int
	{
		return self::getSecondsFromNow($sqlTime);
	}

	/**
	 * @deprecated Use isBefore() instead
	 * @param string $sqlTime1 First SQL TIME string
	 * @param string $sqlTime2 Second SQL TIME string
	 * @return bool True if sqlTime1 is before sqlTime2
	 */
	public static function isBeforeTime(string $sqlTime1, string $sqlTime2): bool
	{
		return self::isBefore($sqlTime1, $sqlTime2);
	}

	/**
	 * @deprecated Use isAfter() instead
	 * @param string $sqlTime1 First SQL TIME string
	 * @param string $sqlTime2 Second SQL TIME string
	 * @return bool True if sqlTime1 is after sqlTime2
	 */
	public static function isAfterTime(string $sqlTime1, string $sqlTime2): bool
	{
		return self::isAfter($sqlTime1, $sqlTime2);
	}
}