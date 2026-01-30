<?php

namespace Osimatic\Calendar;

/**
 * Utility class for Unix timestamp manipulation and analysis.
 * This class works exclusively with Unix timestamps (integer seconds since 1970-01-01 00:00:00 UTC).
 * For DateTime object manipulation, use the DateTime class instead.
 *
 * Organized categories:
 * - Current Time: Get current timestamp
 * - Creation: Create timestamps from components
 * - Conversion: Convert between timestamp and DateTime
 * - Validation & Comparison: Check past/future, compare timestamps
 * - Calculation: Add/subtract time units
 * - Day of Week: Find next/previous day of week
 * - Formatting: Format timestamps as strings
 */
class Timestamp
{
	// ========== Current Time Methods ==========

	/**
	 * Gets the current Unix timestamp.
	 * @return int Current Unix timestamp (seconds since 1970-01-01 00:00:00 UTC)
	 */
	public static function getCurrentTimestamp(): int
	{
		return time();
	}

	// ========== Creation Methods ==========

	/**
	 * Creates a Unix timestamp from a DateTime object.
	 * @param \DateTime $dateTime The DateTime object to convert
	 * @return int Unix timestamp
	 */
	public static function fromDateTime(\DateTime $dateTime): int
	{
		return $dateTime->getTimestamp();
	}

	/**
	 * Creates a Unix timestamp from date and time components.
	 * @param int $year The year (e.g., 2024)
	 * @param int $month The month (1-12)
	 * @param int $day The day of month (1-31)
	 * @param int $hour The hour (0-23, default: 0)
	 * @param int $minute The minute (0-59, default: 0)
	 * @param int $second The second (0-59, default: 0)
	 * @return int The Unix timestamp
	 */
	public static function create(int $year, int $month, int $day, int $hour = 0, int $minute = 0, int $second = 0): int
	{
		return mktime($hour, $minute, $second, $month, $day, $year);
	}

	/**
	 * Creates a Unix timestamp from date components.
	 * The time is set to midnight (00:00:00).
	 * @param int $year The year (e.g., 2024)
	 * @param int $month The month (1-12)
	 * @param int $day The day of month (1-31)
	 * @return int The Unix timestamp at midnight of the specified date
	 */
	public static function createFromDate(int $year, int $month, int $day): int
	{
		return mktime(0, 0, 0, $month, $day, $year);
	}

	// ========== Conversion Methods ==========

	/**
	 * Converts a Unix timestamp to a DateTime object.
	 * @param int $timestamp The Unix timestamp to convert
	 * @return \DateTime DateTime object
	 */
	public static function toDateTime(int $timestamp): \DateTime
	{
		$dateTime = new \DateTime();
		$dateTime->setTimestamp($timestamp);
		return $dateTime;
	}

	// ========== Validation & Comparison Methods ==========

	/**
	 * Checks if a date (ignoring time) is in the past.
	 * Compares the date portion only (midnight) with today's midnight.
	 * @param int $timestamp The Unix timestamp to check
	 * @return bool True if the date is before today, false otherwise
	 */
	public static function isDateInThePast(int $timestamp): bool
	{
		return $timestamp < mktime(0, 0, 0, (int) date('m'), (int) date('d'), (int) date('Y'));
	}

	/**
	 * Checks if a date (ignoring time) is in the future.
	 * Compares the date portion only (midnight) with tomorrow's midnight.
	 * @param int $timestamp The Unix timestamp to check
	 * @return bool True if the date is after today, false otherwise
	 */
	public static function isDateInTheFuture(int $timestamp): bool
	{
		return $timestamp >= mktime(0, 0, 0, (int) date('m'), (int) date('d') + 1, (int) date('Y'));
	}

	/**
	 * Checks if a timestamp (including time) is in the past.
	 * Compares with the current date and time.
	 * @param int $timestamp The Unix timestamp to check
	 * @return bool True if the timestamp is before now, false otherwise
	 */
	public static function isTimeInThePast(int $timestamp): bool
	{
		return $timestamp < time();
	}

	/**
	 * Checks if a timestamp (including time) is in the future.
	 * Compares with the current date and time.
	 * @param int $timestamp The Unix timestamp to check
	 * @return bool True if the timestamp is after now, false otherwise
	 */
	public static function isTimeInTheFuture(int $timestamp): bool
	{
		return $timestamp > time();
	}

	/**
	 * Checks if a timestamp is between two other timestamps.
	 * @param int $timestamp The timestamp to check
	 * @param int $startTimestamp The start of the range
	 * @param int $endTimestamp The end of the range
	 * @param bool $inclusive Whether to include the boundaries (default: true)
	 * @return bool True if within range, false otherwise
	 */
	public static function isBetween(int $timestamp, int $startTimestamp, int $endTimestamp, bool $inclusive = true): bool
	{
		if ($inclusive) {
			return $timestamp >= $startTimestamp && $timestamp <= $endTimestamp;
		}
		return $timestamp > $startTimestamp && $timestamp < $endTimestamp;
	}

	// ========== Calculation Methods ==========

	/**
	 * Adds seconds to a timestamp.
	 * @param int $timestamp The base timestamp
	 * @param int $seconds Number of seconds to add
	 * @return int New timestamp
	 */
	public static function addSeconds(int $timestamp, int $seconds): int
	{
		return $timestamp + $seconds;
	}

	/**
	 * Subtracts seconds from a timestamp.
	 * @param int $timestamp The base timestamp
	 * @param int $seconds Number of seconds to subtract
	 * @return int New timestamp
	 */
	public static function subSeconds(int $timestamp, int $seconds): int
	{
		return $timestamp - $seconds;
	}

	/**
	 * Adds minutes to a timestamp.
	 * @param int $timestamp The base timestamp
	 * @param int $minutes Number of minutes to add
	 * @return int New timestamp
	 */
	public static function addMinutes(int $timestamp, int $minutes): int
	{
		return $timestamp + ($minutes * 60);
	}

	/**
	 * Subtracts minutes from a timestamp.
	 * @param int $timestamp The base timestamp
	 * @param int $minutes Number of minutes to subtract
	 * @return int New timestamp
	 */
	public static function subMinutes(int $timestamp, int $minutes): int
	{
		return $timestamp - ($minutes * 60);
	}

	/**
	 * Adds hours to a timestamp.
	 * @param int $timestamp The base timestamp
	 * @param int $hours Number of hours to add
	 * @return int New timestamp
	 */
	public static function addHours(int $timestamp, int $hours): int
	{
		return $timestamp + ($hours * 3600);
	}

	/**
	 * Subtracts hours from a timestamp.
	 * @param int $timestamp The base timestamp
	 * @param int $hours Number of hours to subtract
	 * @return int New timestamp
	 */
	public static function subHours(int $timestamp, int $hours): int
	{
		return $timestamp - ($hours * 3600);
	}

	/**
	 * Adds days to a timestamp.
	 * @param int $timestamp The base timestamp
	 * @param int $days Number of days to add
	 * @return int New timestamp
	 */
	public static function addDays(int $timestamp, int $days): int
	{
		return $timestamp + ($days * 86400);
	}

	/**
	 * Subtracts days from a timestamp.
	 * @param int $timestamp The base timestamp
	 * @param int $days Number of days to subtract
	 * @return int New timestamp
	 */
	public static function subDays(int $timestamp, int $days): int
	{
		return $timestamp - ($days * 86400);
	}

	/**
	 * Gets the timestamp at the start of the day (midnight 00:00:00).
	 * @param int $timestamp The base timestamp
	 * @return int Timestamp at midnight
	 */
	public static function getStartOfDay(int $timestamp): int
	{
		return mktime(0, 0, 0, (int) date('m', $timestamp), (int) date('d', $timestamp), (int) date('Y', $timestamp));
	}

	/**
	 * Gets the timestamp at the end of the day (23:59:59).
	 * @param int $timestamp The base timestamp
	 * @return int Timestamp at 23:59:59
	 */
	public static function getEndOfDay(int $timestamp): int
	{
		return mktime(23, 59, 59, (int) date('m', $timestamp), (int) date('d', $timestamp), (int) date('Y', $timestamp));
	}

	// ========== Day of Week Methods ==========

	/**
	 * Gets the day of week for a timestamp.
	 * @param int $timestamp The Unix timestamp
	 * @return int Day of week (1=Monday, 7=Sunday, ISO-8601)
	 */
	public static function getDayOfWeek(int $timestamp): int
	{
		return (int) date('N', $timestamp);
	}

	/**
	 * Finds the next occurrence of a specific day of the week from date components.
	 * If the given date is already the desired day, returns that date.
	 * @param int $year The starting year
	 * @param int $month The starting month (1-12)
	 * @param int $day The starting day of month (1-31)
	 * @param int $dayOfWeek The target day of week (1=Monday, 7=Sunday, ISO-8601)
	 * @return int The Unix timestamp of the next occurrence of the specified day
	 */
	public static function getNextDayOfWeek(int $year, int $month, int $day, int $dayOfWeek): int
	{
		$timestamp = mktime(0, 0, 0, $month, $day, $year);
		while (((int) date('N', $timestamp)) !== $dayOfWeek) {
			$timestamp += 86400;
		}
		return $timestamp;
	}

	/**
	 * Finds the previous occurrence of a specific day of the week from date components.
	 * If the given date is already the desired day, returns that date.
	 * @param int $year The starting year
	 * @param int $month The starting month (1-12)
	 * @param int $day The starting day of month (1-31)
	 * @param int $dayOfWeek The target day of week (1=Monday, 7=Sunday, ISO-8601)
	 * @return int The Unix timestamp of the previous occurrence of the specified day
	 */
	public static function getPreviousDayOfWeek(int $year, int $month, int $day, int $dayOfWeek): int
	{
		$timestamp = mktime(0, 0, 0, $month, $day, $year);
		while (((int) date('N', $timestamp)) !== $dayOfWeek) {
			$timestamp -= 86400;
		}
		return $timestamp;
	}

	/**
	 * Finds the next occurrence of a specific day of the week from a timestamp.
	 * If the timestamp is already the desired day, returns that timestamp.
	 * @param int $timestamp The starting Unix timestamp
	 * @param int $dayOfWeek The target day of week (1=Monday, 7=Sunday, ISO-8601)
	 * @return int The Unix timestamp of the next occurrence of the specified day
	 */
	public static function getNextDayOfWeekFromTimestamp(int $timestamp, int $dayOfWeek): int
	{
		return self::getNextDayOfWeek((int) date('Y', $timestamp), (int) date('m', $timestamp), (int) date('d', $timestamp), $dayOfWeek);
	}

	/**
	 * Finds the previous occurrence of a specific day of the week from a timestamp.
	 * If the timestamp is already the desired day, returns that timestamp.
	 * @param int $timestamp The starting Unix timestamp
	 * @param int $dayOfWeek The target day of week (1=Monday, 7=Sunday, ISO-8601)
	 * @return int The Unix timestamp of the previous occurrence of the specified day
	 */
	public static function getPreviousDayOfWeekFromTimestamp(int $timestamp, int $dayOfWeek): int
	{
		return self::getPreviousDayOfWeek((int) date('Y', $timestamp), (int) date('m', $timestamp), (int) date('d', $timestamp), $dayOfWeek);
	}

	// ========== Formatting Methods ==========

	// IntlDateFormatter methods

	/**
	 * Formats a timestamp using IntlDateFormatter with custom date and time format levels.
	 *  Uses ICU IntlDateFormatter for internationalization support.
	 *  Examples with SHORT/SHORT: en_US: "1/15/24, 2:30 PM" ; fr_FR: "15/01/2024 14:30"
	 *  Examples with MEDIUM/MEDIUM: en_US: "Jan 15, 2024, 2:30:45 PM" ; fr_FR: "15 janv. 2024, 14:30:45"
	 *  Examples with LONG/LONG: en_US: "January 15, 2024 at 2:30:45 PM UTC" ; fr_FR: "15 janvier 2024 à 14:30:45 UTC"
	 *  Examples with FULL/FULL: en_US: "Monday, January 15, 2024 at 2:30:45 PM Coordinated Universal Time" ; fr_FR: "lundi 15 janvier 2024 à 14:30:45 temps universel coordonné"
	 * @param int $timestamp The Unix timestamp to format
	 * @param int $dateFormatter IntlDateFormatter constant for date format (NONE, SHORT, MEDIUM, LONG, FULL). Default: SHORT
	 * @param int $timeFormatter IntlDateFormatter constant for time format (NONE, SHORT, MEDIUM, LONG, FULL). Default: SHORT
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string The formatted date/time string
	 */
	public static function format(int $timestamp, int $dateFormatter=\IntlDateFormatter::SHORT, int $timeFormatter=\IntlDateFormatter::SHORT, ?string $locale = null): string
	{
		return DateTime::format(self::toDateTime($timestamp), $dateFormatter, $timeFormatter, $locale);
	}

	/**
	 * Formats a timestamp with SHORT format for both date and time.
	 *  Uses ICU IntlDateFormatter for internationalization.
	 *  See exemples in format method.
	 * @param int $timestamp The Unix timestamp to format
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @param int $dateFormatter IntlDateFormatter constant for date format (NONE, SHORT, MEDIUM, LONG, FULL). Default: SHORT
	 * @param int $timeFormatter IntlDateFormatter constant for time format (NONE, SHORT, MEDIUM, LONG, FULL). Default: SHORT
	 * @return string The formatted date and time string
	 */
	public static function formatDateTime(int $timestamp, ?string $locale = null, int $dateFormatter=\IntlDateFormatter::SHORT, int $timeFormatter=\IntlDateFormatter::SHORT): string
	{
		return DateTime::formatDateTime(self::toDateTime($timestamp), $locale, $dateFormatter, $timeFormatter);
	}

	// Date Formatting Methods

	/**
	 * Formats only the date portion of a timestamp.
	 *  Uses ICU IntlDateFormatter for internationalization.
	 * @param int $timestamp The Unix timestamp to format
	 * @param string|null $locale Optional locale code
	 * @param int $dateFormatter IntlDateFormatter constant for date format (default: SHORT). Available formats:
	 *   - SHORT: Numeric date format. Exemples: en_US: "1/15/24" ; fr_FR: "15/01/2024"
	 *   - MEDIUM: Abbreviated month name with day and year. Exemples: en_US: "Jan 15, 2024" ; fr_FR: "15 janv. 2024"
	 *   - LONG: Full month name with day and year, without weekday. Exemples: en_US: "January 15, 2024" ; fr_FR: "15 janvier 2024"
	 *   - FULL: Full format with weekday. Exemples: en_US: "Monday, January 15, 2024" ; fr_FR: "lundi 15 janvier 2024"
	 * @return string The formatted date string
	 */
	public static function formatDate(int $timestamp, ?string $locale = null, int $dateFormatter = \IntlDateFormatter::SHORT): string
	{
		return DateTime::formatDate(self::toDateTime($timestamp), $locale, $dateFormatter);
	}

	/**
	 * Formats a timestamp's date in short format (numeric date).
	 * Uses ICU IntlDateFormatter for internationalization.
	 * Examples: en_US: "1/15/24" ; fr_FR: "15/01/2024"
	 * Examples with separator='-': en_US: "1-15-24" ; fr_FR: "15-01-2024"
	 * @param int $timestamp The Unix timestamp to format
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @param string|null $separator Optional custom separator to replace the default one (e.g., '-', '.', ' ')
	 * @return string The formatted date string, or empty string on error
	 */
	public static function formatDateShort(int $timestamp, ?string $locale = null, ?string $separator = null): string
	{
		return DateTime::formatDateShort(self::toDateTime($timestamp), $locale, $separator);
	}

	/**
	 * Formats a timestamp's date in medium format (abbreviated month name).
	 * Uses ICU IntlDateFormatter for internationalization.
	 * Examples: en_US: "Jan 15, 2024" ; fr_FR: "15 janv. 2024"
	 * @param int $timestamp The Unix timestamp to format
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string The formatted date string, or empty string on error
	 */
	public static function formatDateMedium(int $timestamp, ?string $locale = null): string
	{
		return DateTime::formatDateMedium(self::toDateTime($timestamp), $locale);
	}

	/**
	 * Formats a timestamp's date in long format (does not include the day of the week) with localized text.
	 * Uses ICU IntlDateFormatter for internationalization.
	 * Examples: en_US: "January 15, 2024" ; fr_FR: "15 janvier 2024"
	 * @param int $timestamp The Unix timestamp to format
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string The formatted date string, or empty string on error
	 */
	public static function formatDateLong(int $timestamp, ?string $locale = null): string
	{
		return DateTime::formatDateLong(self::toDateTime($timestamp), $locale);
	}

	/**
	 * Formats a timestamp's date in full format (includes the day of the week) with localized text.
	 * Uses ICU IntlDateFormatter for internationalization.
	 * Examples: en_US: "Monday, January 15, 2024" ; fr_FR: "lundi 15 janvier 2024"
	 * @param int $timestamp The Unix timestamp to format
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string The formatted date string, or empty string on error
	 */
	public static function formatDateFull(int $timestamp, ?string $locale=null): string
	{
		return DateTime::formatDateFull(self::toDateTime($timestamp), $locale);
	}

	/**
	 * Formats a timestamp's date in ISO 8601 format (YYYY-MM-DD).. Exemple: "2024-01-15"
	 * @param int $timestamp The Unix timestamp to format
	 * @return string The ISO formatted date string
	 */
	public static function formatDateISO(int $timestamp): string
	{
		return DateTime::formatDateISO(self::toDateTime($timestamp));
	}

	// Time Formatting Methods

	/**
	 * Formats only the time portion of a timestamp.
	 * Uses ICU IntlDateFormatter for internationalization.
	 * @param int $timestamp The Unix timestamp to format
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @param int $timeFormatter IntlDateFormatter constant for time format (default: SHORT). Available formats:
	 *   - SHORT: Time without seconds (HH:MM format). Examples: en_US: "2:30 PM" ; fr_FR: "14:30"
	 *   - MEDIUM: Time with seconds (HH:MM:SS format). Examples: en_US: "2:30:45 PM" ; fr_FR: "14:30:45"
	 *   - LONG: Time with seconds and timezone. Examples: en_US: "2:30:45 PM UTC" ; fr_FR: "14:30:45 UTC"
	 *   - FULL: Time with seconds and full timezone name. Examples: en_US: "2:30:45 PM Coordinated Universal Time" ; fr_FR: "14:30:45 temps universel coordonné"
	 * @return string The formatted time string, or empty string on error
	 */
	public static function formatTime(int $timestamp, ?string $locale = null, int $timeFormatter = \IntlDateFormatter::SHORT): string
	{
		return DateTime::formatTime(self::toDateTime($timestamp), $locale, $timeFormatter);
	}

	/**
	 * Formats a timestamp's time in short HH:MM format (without seconds).
	 * Uses ICU IntlDateFormatter for internationalization.
	 * Examples: en_US: "2:30 PM" ; fr_FR: "14:30"
	 * @param int $timestamp The Unix timestamp to format
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string The formatted time string, or empty string on error
	 */
	public static function formatTimeShort(int $timestamp, ?string $locale = null): string
	{
		return DateTime::formatTimeShort(self::toDateTime($timestamp), $locale);
	}

	/**
	 * Formats a timestamp's time in medium format (with seconds).
	 * Uses ICU IntlDateFormatter for internationalization.
	 * Examples: en_US: "2:30:45 PM" ; fr_FR: "14:30:45"
	 * @param int $timestamp The Unix timestamp to format
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string The formatted time string, or empty string on error
	 */
	public static function formatTimeMedium(int $timestamp, ?string $locale=null): string
	{
		return DateTime::formatTimeMedium(self::toDateTime($timestamp), $locale);
	}

	/**
	 * Formats a timestamp's time in long format (with timezone).
	 * Uses ICU IntlDateFormatter for internationalization.
	 * Examples: en_US: "2:30:45 PM UTC" ; fr_FR: "14:30:45 UTC"
	 * @param int $timestamp The Unix timestamp to format
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string The formatted time string, or empty string on error
	 */
	public static function formatTimeLong(int $timestamp, ?string $locale = null): string
	{
		return DateTime::formatTimeLong(self::toDateTime($timestamp), $locale);
	}

	/**
	 * Formats a timestamp's time in ISO 8601 format (HH:MM:SS). Exemple: "14:30:45"
	 * @param int $timestamp The Unix timestamp to format
	 * @return string The ISO 8601 formatted time
	 */
	public static function formatTimeISO(int $timestamp): string
	{
		return DateTime::formatTimeISO(self::toDateTime($timestamp));
	}

	// ========== DEPRECATED METHODS ==========
	// Backward compatibility. Will be removed in a future major version. Please update your code to use the new method names.

	/**
	 * @deprecated Use createFromDate() instead
	 * @param int $year The year
	 * @param int $month The month (1-12)
	 * @param int $day The day of month (1-31)
	 * @return int Unix timestamp
	 */
	public static function getByYearMonthDay(int $year, int $month, int $day): int
	{
		return self::createFromDate($year, $month, $day);
	}

	/**
	 * @deprecated Use getNextDayOfWeek() instead (parameter order changed: year, month, day, dayOfWeek)
	 * @param int $dayOfWeekInNumeric Target day of week (1-7)
	 * @param int $year Starting year
	 * @param int $month Starting month (1-12)
	 * @param int $day Starting day of month (1-31)
	 * @return int Unix timestamp
	 */
	public static function getTimestampNextDayOfWeekByYearMonthDay(int $dayOfWeekInNumeric, int $year, int $month, int $day): int
	{
		return self::getNextDayOfWeek($year, $month, $day, $dayOfWeekInNumeric);
	}

	/**
	 * @deprecated Use getPreviousDayOfWeek() instead (parameter order changed: year, month, day, dayOfWeek)
	 * @param int $dayOfWeekInNumeric Target day of week (1-7)
	 * @param int $year Starting year
	 * @param int $month Starting month (1-12)
	 * @param int $day Starting day of month (1-31)
	 * @return int Unix timestamp
	 */
	public static function getTimestampPreviousDayOfWeekByYearMonthDay(int $dayOfWeekInNumeric, int $year, int $month, int $day): int
	{
		return self::getPreviousDayOfWeek($year, $month, $day, $dayOfWeekInNumeric);
	}

	/**
	 * @deprecated Use getNextDayOfWeekFromTimestamp() instead (parameter order changed: timestamp, dayOfWeek)
	 * @param int $dayOfWeekInNumeric Target day of week (1-7)
	 * @param int $timestamp Starting timestamp
	 * @return int Unix timestamp
	 */
	public static function getNextDayOfWeekOfWeek(int $dayOfWeekInNumeric, int $timestamp): int
	{
		return self::getNextDayOfWeekFromTimestamp($timestamp, $dayOfWeekInNumeric);
	}

	/**
	 * @deprecated Use getPreviousDayOfWeekFromTimestamp() instead (parameter order changed: timestamp, dayOfWeek)
	 * @param int $dayOfWeekInNumeric Target day of week (1-7)
	 * @param int $timestamp Starting timestamp
	 * @return int Unix timestamp
	 */
	public static function getPreviousDayOfWeekOfWeek(int $dayOfWeekInNumeric, int $timestamp): int
	{
		return self::getPreviousDayOfWeekFromTimestamp($timestamp, $dayOfWeekInNumeric);
	}

	/**
	 * @deprecated use formatDateLong if $withWeekDay = false or formatDateFull if $withWeekDay = true instead
	 */
	public static function formatDateInLong(int $timestamp, ?string $locale = null, bool $withWeekDay = false): string
	{
		return DateTime::formatDateInLong(self::toDateTime($timestamp), $locale, $withWeekDay);
	}

}