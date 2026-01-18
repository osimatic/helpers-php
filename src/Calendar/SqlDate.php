<?php

namespace Osimatic\Calendar;

/**
 * Utility class for SQL DATE format manipulation and operations.
 * Handles date strings in SQL DATE format (YYYY-MM-DD).
 * Provides methods for:
 * - Parsing and validating SQL DATE strings
 * - Extracting date components (year, month, day)
 * - Creating SQL DATE strings from components
 * - Getting first/last days of weeks and months
 */
class SqlDate
{

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

		//if (false === ($date = date('Y-m-d', strtotime($date.' 00:00:00')))) {
		if (false === ($timestamp = strtotime($date.' 00:00:00')) || empty($parsedDate = date('Y-m-d', $timestamp))) {
			return null;
		}

		// Verify that the parsed date is valid and matches the input
		if (!self::check($parsedDate)) {
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
	public static function check(?string $date): bool
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
	 * @param string $sqlDate SQL DATE format string (e.g., "2024-01-15")
	 * @return int The year (e.g., 2024)
	 * TODO: Extract via substr for better performance
	 */
	public static function getYear(string $sqlDate): int
	{
		return (int) date('Y', strtotime($sqlDate.' 00:00:00'));
	}

	/**
	 * Extracts the month component from a SQL DATE string.
	 * @param string $sqlDate SQL DATE format string (e.g., "2024-01-15")
	 * @return int The month (1-12)
	 */
	public static function getMonth(string $sqlDate): int
	{
		return (int) date('m', strtotime($sqlDate.' 00:00:00'));
	}

	/**
	 * Extracts the day component from a SQL DATE string.
	 * @param string $sqlDate SQL DATE format string (e.g., "2024-01-15")
	 * @return int The day of month (1-31)
	 */
	public static function getDay(string $sqlDate): int
	{
		return (int) date('d', strtotime($sqlDate.' 00:00:00'));
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
	public static function get(int $year, int $month, int $day): string
	{
		return $year.'-'.sprintf('%02d', $month).'-'.sprintf('%02d', $day);
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
		$timeStampPremierJanvier = strtotime($year . '-01-01 00:00:00');
		$jourPremierJanvier = (int) date('w', $timeStampPremierJanvier);

		// Find the week number of January 1st
		$numSemainePremierJanvier = (int) date('W', $timeStampPremierJanvier);

		// Number to add based on the previous number
		$decallage = ($numSemainePremierJanvier === 1) ? $week - 1 : $week;

		// Timestamp of the day in the searched week
		$timeStampDate = strtotime('+' . $decallage . ' weeks', $timeStampPremierJanvier);

		// Find the Monday of the week based on the previous line
		return date('Y-m-d', ($jourPremierJanvier === 1) ? $timeStampDate : strtotime('last monday', $timeStampDate));
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
		return date('Y-m-d', strtotime(self::getFirstDayOfWeek($year, $week).' 00:00:00')+(6*3600*24));
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

}