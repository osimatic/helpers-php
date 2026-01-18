<?php

namespace Osimatic\Calendar;

/**
 * Utility class for date parsing, formatting, and calendar calculations.
 * Provides methods for:
 * - Parsing date strings in various formats
 * - Getting localized names of days and months
 * - Calculating the number of days in months and years
 * - Leap year detection
 */
class Date
{
	// ========== Parsing Methods ==========

	/**
	 * Parses a date string in various formats and returns a DateTime object.
	 * Supports multiple input formats:
	 * - ISO 8601 with time: "YYYY-MM-DDTHH:II:SS"
	 * - Compact format with time: "YYYYMMDDHHIISS"
	 * - SQL DATE format: "YYYY-MM-DD" (time set to 00:00:00)
	 * - Various other formats supported by SqlDate::parse()
	 * @param string $str The date string to parse
	 * @return \DateTime|null A DateTime object if parsing succeeds, null otherwise
	 */
	public static function parse(string $str): ?\DateTime
	{
		if (empty($str)) {
			return null;
		}

		// Format YYYY-mm-ddTHH:ii:ss
		if (strlen($str) === strlen('YYYY-mm-ddTHH:ii:ss') && null !== ($dateTime = DateTime::parseFromSqlDateTime($str))) {
			return $dateTime;
		}

		// Format YYYYmmddHHiiss
		if (strlen($str) === strlen('yyyymmddhhiiss')) {
			$sqlDate = substr($str, 0, 4).'-'.substr($str, 4, 2).'-'.substr($str, 6, 2);
			$sqlTime = substr($str, 8, 2).':'.substr($str, 10, 2).':'.substr($str, 12, 2);

			if (null !== ($dateTime = DateTime::parseFromSqlDateTime($sqlDate.' '.$sqlTime))) {
				return $dateTime;
			}
		}

		//if (false !== SqlDate::check($sqlDate = SqlDate::parse($str))) {
		if (null !== ($sqlDate = SqlDate::parse($str)) && false !== SqlDate::check($sqlDate)) {
			return DateTime::parseFromSqlDateTime($sqlDate.' 00:00:00');
		}

		return null;
	}


	// ========== Day of Week Methods ==========

	/**
	 * Gets the localized name of a day of the week.
	 * Uses ICU IntlDateFormatter for internationalization support.
	 * Returns the full day name with the first letter capitalized.
	 * @param int $dayOfWeek ISO-8601 numeric representation of the day (1 for Monday through 7 for Sunday)
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string The localized name of the day (e.g., "Monday", "Lundi")
	 * @see http://userguide.icu-project.org/formatparse/datetime
	 */
	public static function getDayName(int $dayOfWeek, ?string $locale=null): string
	{
		$timestamp = strtotime('monday this week')+(($dayOfWeek-1)*3600*24);
		return ucfirst(\IntlDateFormatter::create($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL,
			date_default_timezone_get(), \IntlDateFormatter::GREGORIAN , 'EEEE')?->format($timestamp) ?? '');
		//return ucfirst(strftime('%A', ($timestamp+($dayOfWeek*3600*24))));
	}

	// ========== Day of Month Methods ==========

	// ========== Month Methods ==========

	/**
	 * Gets the localized name of a month.
	 * Uses ICU IntlDateFormatter for internationalization support.
	 * Returns the full month name with the first letter capitalized.
	 * @param int $month Numeric representation of a month (1-12, where 1 is January and 12 is December)
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string The localized name of the month (e.g., "January", "Janvier"), or empty string on error
	 * @see http://userguide.icu-project.org/formatparse/datetime
	 */
	public static function getMonthName(int $month, ?string $locale=null): string
	{
		try {
			return ucfirst(\IntlDateFormatter::create($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL,
				date_default_timezone_get(), \IntlDateFormatter::GREGORIAN, 'MMMM')?->format(new \DateTime('2020-' . sprintf('%02d', $month) . '-15 00:00:00')) ?? '');
		} catch (\Exception) {}
		//return ucfirst(strftime('%B', mktime(0, 0, 0, $month)));
		return '';
	}

	/**
	 * Gets the number of days in a specific month.
	 * Automatically handles months with different numbers of days and leap years.
	 * @param int $year The year (e.g., 2024)
	 * @param int $month The month (1-12, where 1 is January and 12 is December)
	 * @return int The number of days in the month (28-31)
	 */
	public static function getNumberOfDaysInMonth(int $year, int $month): int
	{
		return date('t', mktime(0, 0, 0, $month, 1, $year));
	}

	// ========== Year Methods ==========

	/**
	 * Checks if a year is a leap year.
	 * A leap year is divisible by 4, except for years divisible by 100 (unless also divisible by 400).
	 * Examples: 2020 is a leap year, 1900 is not, 2000 is a leap year.
	 * @param int $year The year to check (e.g., 2024)
	 * @return int Returns 1 if leap year, 0 if not
	 */
	public static function isLeapYear(int $year): int
	{
		return ((($year % 4) === 0) && ((($year % 100) !== 0) || (($year %400) === 0)));
	}

	/**
	 * Gets the number of days in a year.
	 * Returns 366 for leap years, 365 for regular years.
	 * @param int $year The year (e.g., 2024)
	 * @return int The number of days (365 or 366)
	 */
	public static function getNumberOfDaysInYear(int $year): int
	{
		return self::isLeapYear($year) ? 366 : 365;
	}

}