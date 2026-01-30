<?php

namespace Osimatic\Calendar;

/**
 * Utility class for SQL DATE format manipulation and operations.
 * Handles date strings in SQL DATE format (YYYY-MM-DD).
 * This class works exclusively with SQL DATE strings, not DateTime objects.
 *
 * Organized categories:
 * - Parsing & Validation: Parse and validate SQL DATE strings
 * - Extraction: Extract date components (year, month, day)
 * - Creation: Create SQL DATE strings from components
 * - Conversion: Convert between SQL DATE and other formats
 * - Calculation: Add/subtract time units, calculate differences
 * - Week Methods: Get first/last days of weeks
 * - Month Methods: Get first/last days of months
 * - Year Methods: Year-related calculations
 * - Formatting: Format SQL DATE strings
 */
class SqlDate
{
	// ========== Parsing & Validation Methods ==========

	/**
	 * Parses various date formats and returns a SQL DATE string.
	 * Handles DD/MM/YYYY format, array format from database results, and validates the parsed date.
	 * Prevents unwanted conversions by validating the input matches the output.
	 * @param mixed $date Date value (string in various formats, or array with 'date' key)
	 * @return string|null SQL DATE format string (YYYY-MM-DD), or null if invalid
	 */
	public static function parse($date): ?string
	{
		if (empty($date)) {
			return null;
		}

		if (is_array($date) && !empty($date['date'])) {
			$date = substr($date['date'], 0, 10);
		}

		if (str_contains($date, '/')) {
			$dateArr = explode('/', $date);
			$date = ($dateArr[2]??null).'-'.($dateArr[1]??null).'-'.($dateArr[0]??null);
		}

		if (false === ($timestamp = strtotime($date.' 00:00:00')) || empty($parsedDate = date('Y-m-d', $timestamp))) {
			return null;
		}

		// Verify that the parsed date is valid and matches the input
		if (!self::isValid($parsedDate)) {
			return null;
		}

		// Verify that the parsed date matches the input (avoid unwanted conversions)
		// For example "invalid-date" could be parsed as a relative date
		$originalDate = trim($date);
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $originalDate) && $parsedDate !== $originalDate) {
			// If input is not in ISO format and result doesn't match, it's probably invalid
			if (!str_contains($originalDate, '-') || count(explode('-', $originalDate)) !== 3) {
				return null;
			}
		}

		return $parsedDate;
	}

	/**
	 * Validates if a SQL DATE string represents a valid date.
	 * Uses PHP's checkdate() function to verify the date is valid.
	 * @param string|null $date SQL DATE string to validate (e.g., "2024-01-15")
	 * @return bool True if valid date, false otherwise
	 */
	public static function isValid(?string $date): bool
	{
		if (empty($date)) {
			return false;
		}
		$dateArr = explode('-', $date);
		$year = (int) ($dateArr[0]??0);
		$month = (int) ($dateArr[1]??0);
		$day = (int) ($dateArr[2]??0);
		return checkdate($month, $day, $year);
	}

	// ========== Extraction Methods ==========

	/**
	 * Extracts the year component from a SQL DATE string.
	 * Uses substr for optimal performance.
	 * @param string $sqlDate SQL DATE format string (e.g., "2024-01-15")
	 * @return int The year (e.g., 2024)
	 */
	public static function getYear(string $sqlDate): int
	{
		return (int) substr($sqlDate, 0, 4);
	}

	/**
	 * Extracts the month component from a SQL DATE string.
	 * Uses substr for optimal performance.
	 * @param string $sqlDate SQL DATE format string (e.g., "2024-01-15")
	 * @return int The month (1-12)
	 */
	public static function getMonth(string $sqlDate): int
	{
		return (int) substr($sqlDate, 5, 2);
	}

	/**
	 * Extracts the day component from a SQL DATE string.
	 * Uses substr for optimal performance.
	 * @param string $sqlDate SQL DATE format string (e.g., "2024-01-15")
	 * @return int The day of month (1-31)
	 */
	public static function getDay(string $sqlDate): int
	{
		return (int) substr($sqlDate, 8, 2);
	}

	/**
	 * Gets the day of week for a SQL DATE string.
	 * @param string $sqlDate SQL DATE format string
	 * @return int Day of week (1=Monday, 7=Sunday, ISO-8601)
	 */
	public static function getDayOfWeek(string $sqlDate): int
	{
		return (int) date('N', strtotime($sqlDate.' 00:00:00'));
	}

	// ========== Creation Methods ==========

	/**
	 * Creates a SQL DATE string from year, month, and day components.
	 * Month and day are zero-padded to 2 digits.
	 * @param int $year The year (e.g., 2024)
	 * @param int $month The month (1-12)
	 * @param int $day The day of month (1-31)
	 * @return string SQL DATE format string (YYYY-MM-DD)
	 */
	public static function create(int $year, int $month, int $day): string
	{
		return $year.'-'.sprintf('%02d', $month).'-'.sprintf('%02d', $day);
	}

	/**
	 * Creates a SQL DATE string for today's date.
	 * @return string SQL DATE format string for today
	 */
	public static function today(): string
	{
		return date('Y-m-d');
	}

	/**
	 * Creates a SQL DATE string for yesterday's date.
	 * @return string SQL DATE format string for yesterday
	 */
	public static function yesterday(): string
	{
		return date('Y-m-d', strtotime('-1 day'));
	}

	/**
	 * Creates a SQL DATE string for tomorrow's date.
	 * @return string SQL DATE format string for tomorrow
	 */
	public static function tomorrow(): string
	{
		return date('Y-m-d', strtotime('+1 day'));
	}

	// ========== Conversion Methods ==========

	/**
	 * Converts a SQL DATE string to a DateTime object.
	 * Time is set to midnight (00:00:00).
	 * @param string $sqlDate SQL DATE format string
	 * @return \DateTime|null DateTime object, or null if parsing fails
	 */
	public static function toDateTime(string $sqlDate): ?\DateTime
	{
		return DateTime::parseFromSqlDateTime($sqlDate.' 00:00:00');
	}

	/**
	 * Converts a SQL DATE string to a Unix timestamp.
	 * Time is set to midnight (00:00:00).
	 * @param string $sqlDate SQL DATE format string
	 * @return int Unix timestamp
	 */
	public static function toTimestamp(string $sqlDate): int
	{
		return strtotime($sqlDate.' 00:00:00');
	}

	// ========== Calculation Methods ==========

	/**
	 * Adds days to a SQL DATE string.
	 * @param string $sqlDate SQL DATE format string
	 * @param int $days Number of days to add
	 * @return string New SQL DATE format string
	 */
	public static function addDays(string $sqlDate, int $days): string
	{
		return date('Y-m-d', strtotime($sqlDate.' 00:00:00') + ($days * 86400));
	}

	/**
	 * Subtracts days from a SQL DATE string.
	 * @param string $sqlDate SQL DATE format string
	 * @param int $days Number of days to subtract
	 * @return string New SQL DATE format string
	 */
	public static function subDays(string $sqlDate, int $days): string
	{
		return date('Y-m-d', strtotime($sqlDate.' 00:00:00') - ($days * 86400));
	}

	/**
	 * Adds months to a SQL DATE string.
	 * @param string $sqlDate SQL DATE format string
	 * @param int $months Number of months to add
	 * @return string New SQL DATE format string
	 */
	public static function addMonths(string $sqlDate, int $months): string
	{
		return date('Y-m-d', strtotime($sqlDate.' 00:00:00 +'.$months.' months'));
	}

	/**
	 * Subtracts months from a SQL DATE string.
	 * @param string $sqlDate SQL DATE format string
	 * @param int $months Number of months to subtract
	 * @return string New SQL DATE format string
	 */
	public static function subMonths(string $sqlDate, int $months): string
	{
		return date('Y-m-d', strtotime($sqlDate.' 00:00:00 -'.$months.' months'));
	}

	/**
	 * Adds years to a SQL DATE string.
	 * @param string $sqlDate SQL DATE format string
	 * @param int $years Number of years to add
	 * @return string New SQL DATE format string
	 */
	public static function addYears(string $sqlDate, int $years): string
	{
		return date('Y-m-d', strtotime($sqlDate.' 00:00:00 +'.$years.' years'));
	}

	/**
	 * Subtracts years from a SQL DATE string.
	 * @param string $sqlDate SQL DATE format string
	 * @param int $years Number of years to subtract
	 * @return string New SQL DATE format string
	 */
	public static function subYears(string $sqlDate, int $years): string
	{
		return date('Y-m-d', strtotime($sqlDate.' 00:00:00 -'.$years.' years'));
	}

	/**
	 * Calculates the number of days between two SQL DATE strings.
	 * @param string $sqlDate1 First SQL DATE string
	 * @param string $sqlDate2 Second SQL DATE string
	 * @return int Number of days between the dates (positive if date1 is after date2)
	 */
	public static function getDaysBetween(string $sqlDate1, string $sqlDate2): int
	{
		$timestamp1 = strtotime($sqlDate1.' 00:00:00');
		$timestamp2 = strtotime($sqlDate2.' 00:00:00');
		return (int) (($timestamp1 - $timestamp2) / 86400);
	}

	// ========== Comparison Methods ==========

	/**
	 * Checks if the first date is before the second date.
	 * @param string $sqlDate1 First SQL DATE string
	 * @param string $sqlDate2 Second SQL DATE string
	 * @return bool True if date1 is before date2
	 */
	public static function isBefore(string $sqlDate1, string $sqlDate2): bool
	{
		return $sqlDate1 < $sqlDate2;
	}

	/**
	 * Checks if the first date is after the second date.
	 * @param string $sqlDate1 First SQL DATE string
	 * @param string $sqlDate2 Second SQL DATE string
	 * @return bool True if date1 is after date2
	 */
	public static function isAfter(string $sqlDate1, string $sqlDate2): bool
	{
		return $sqlDate1 > $sqlDate2;
	}

	/**
	 * Checks if two dates are equal.
	 * @param string $sqlDate1 First SQL DATE string
	 * @param string $sqlDate2 Second SQL DATE string
	 * @return bool True if dates are equal
	 */
	public static function isEqual(string $sqlDate1, string $sqlDate2): bool
	{
		return $sqlDate1 === $sqlDate2;
	}

	// ========== Week Methods ==========

	/**
	 * Gets the first day (Monday) of a specific week in a year.
	 * Uses ISO-8601 week numbering (week starts on Monday).
	 * @param int $year The year (e.g., 2024)
	 * @param int $week The ISO week number (1-53)
	 * @return string SQL DATE format string of the first day (Monday) of the week
	 */
	public static function getFirstDayOfWeek(int $year, int $week): string
	{
		$timestampFirstJanuary = strtotime($year . '-01-01 00:00:00');
		$dayFirstJanuary = (int) date('w', $timestampFirstJanuary);

		// Find the week number of January 1st
		$weekNumberFirstJanuary = (int) date('W', $timestampFirstJanuary);

		// Offset to add based on the week number
		$offset = ($weekNumberFirstJanuary === 1) ? $week - 1 : $week;

		// Timestamp of the day in the searched week
		$timestampDate = strtotime('+' . $offset . ' weeks', $timestampFirstJanuary);

		// Find the Monday of the week
		return date('Y-m-d', ($dayFirstJanuary === 1) ? $timestampDate : strtotime('last monday', $timestampDate));
	}

	/**
	 * Gets the last day (Sunday) of a specific week in a year.
	 * Uses ISO-8601 week numbering (week starts on Monday, ends on Sunday).
	 * @param int $year The year (e.g., 2024)
	 * @param int $week The ISO week number (1-53)
	 * @return string SQL DATE format string of the last day (Sunday) of the week
	 */
	public static function getLastDayOfWeek(int $year, int $week): string
	{
		return date('Y-m-d', strtotime(self::getFirstDayOfWeek($year, $week).' 00:00:00')+(6*86400));
	}

	// ========== Month Methods ==========

	/**
	 * Gets the first day of a specific month.
	 * Always returns the 1st day of the month.
	 * @param int $year The year (e.g., 2024)
	 * @param int $month The month (1-12)
	 * @return string SQL DATE format string of the first day of the month
	 */
	public static function getFirstDayOfMonth(int $year, int $month): string
	{
		return date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
	}

	/**
	 * Gets the last day of a specific month.
	 * Automatically handles months with different numbers of days and leap years.
	 * @param int $year The year (e.g., 2024)
	 * @param int $month The month (1-12)
	 * @return string SQL DATE format string of the last day of the month
	 */
	public static function getLastDayOfMonth(int $year, int $month): string
	{
		return date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
	}

	// ========== Year Methods ==========

	/**
	 * Gets the first day of a specific year (January 1st).
	 * @param int $year The year (e.g., 2024)
	 * @return string SQL DATE format string (YYYY-01-01)
	 */
	public static function getFirstDayOfYear(int $year): string
	{
		return $year.'-01-01';
	}

	/**
	 * Gets the last day of a specific year (December 31st).
	 * @param int $year The year (e.g., 2024)
	 * @return string SQL DATE format string (YYYY-12-31)
	 */
	public static function getLastDayOfYear(int $year): string
	{
		return $year.'-12-31';
	}

	// ========== Formatting Methods ==========

	/**
	 * Formats a SQL DATE string using IntlDateFormatter.
	 * @param string $sqlDate SQL DATE format string
	 * @param string|null $locale Optional locale code
	 * @param int $dateType IntlDateFormatter date type constant
	 * @return string Formatted date string
	 */
	public static function format(string $sqlDate, ?string $locale = null, int $dateType = \IntlDateFormatter::MEDIUM): string
	{
		$dateTime = self::toDateTime($sqlDate);
		return $dateTime ? DateTime::formatDate($dateTime, $locale, $dateType) : '';
	}

	/**
	 * Formats a SQL DATE string in LONG or FULL localized format.
	 *  FULL format includes the day of the week, LONG format does not.
	 * @param string $sqlDate SQL DATE format string
	 * @param string|null $locale Optional locale code
	 * @param bool $withWeekDay If true, uses FULL format with weekday; if false, uses LONG format (default: false)
	 * @return string Formatted date string, or empty string if date is invalid
	 */
	public static function formatInLong(string $sqlDate, ?string $locale = null, bool $withWeekDay=false): string
	{
		if (empty($sqlDate) || !self::isValid($sqlDate)) {
			return '';
		}

		$dateTime = self::toDateTime($sqlDate);
		return $dateTime ? DateTime::formatDateInLong($dateTime, $locale, $withWeekDay) : '';
	}

	/**
	 * Formats a SQL DATE string in short format (DD/MM/YYYY or MM/DD/YYYY depending on lang).
	 * @param string $sqlDate SQL DATE format string
	 * @param string $separator Separator between date components (default: '/')
	 * @param string $lang Language code for date order: 'US' (MM/DD/YYYY) or 'EU'/'FR' (DD/MM/YYYY) (default: 'EU')
	 * @return string Formatted date string, or empty string if date is invalid
	 */
	public static function formatShort(string $sqlDate, string $separator = '/', string $lang = 'EU'): string
	{
		if (empty($sqlDate) || !self::isValid($sqlDate)) {
			return '';
		}

		$dateTime = self::toDateTime($sqlDate);
		return $dateTime ? DateTime::formatDateShort($dateTime, $separator, $lang) : '';
	}

	/**
	 * Formats a SQL DATE string in medium format (e.g., "15 Jan 2024").
	 * @param string $sqlDate SQL DATE format string
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR')
	 * @return string Formatted date string, or empty string if date is invalid
	 */
	public static function formatMedium(string $sqlDate, ?string $locale = null): string
	{
		if (empty($sqlDate) || !self::isValid($sqlDate)) {
			return '';
		}

		$dateTime = self::toDateTime($sqlDate);
		return $dateTime ? DateTime::formatDateMedium($dateTime, $locale) : '';
	}

	/**
	 * Formats a SQL DATE string in long format (e.g., "15 January 2024").
	 * @param string $sqlDate SQL DATE format string
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR')
	 * @return string Formatted date string, or empty string if date is invalid
	 */
	public static function formatLong(string $sqlDate, ?string $locale = null): string
	{
		if (empty($sqlDate) || !self::isValid($sqlDate)) {
			return '';
		}

		$dateTime = self::toDateTime($sqlDate);
		return $dateTime ? DateTime::formatDateLong($dateTime, $locale) : '';
	}

	/**
	 * Formats a SQL DATE string in ISO 8601 format (YYYY-MM-DD).
	 * This simply returns the input as SQL DATE is already in ISO format.
	 * @param string $sqlDate SQL DATE format string
	 * @return string Date in ISO format (YYYY-MM-DD), or empty string if date is invalid
	 */
	public static function formatISO(string $sqlDate): string
	{
		if (empty($sqlDate) || !self::isValid($sqlDate)) {
			return '';
		}

		return $sqlDate;
	}

	// ========== DEPRECATED METHODS ==========
	// Backward compatibility. Will be removed in a future major version. Please update your code to use the new method names.

	/**
	 * @deprecated Use isValid() instead
	 * @param string|null $date SQL DATE string to validate
	 * @return bool True if valid date, false otherwise
	 */
	public static function check(?string $date): bool
	{
		return self::isValid($date);
	}

	/**
	 * @deprecated Use create() instead
	 * @param int $year The year
	 * @param int $month The month (1-12)
	 * @param int $day The day of month (1-31)
	 * @return string SQL DATE format string
	 */
	public static function get(int $year, int $month, int $day): string
	{
		return self::create($year, $month, $day);
	}
}
