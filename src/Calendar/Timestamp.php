<?php

namespace Osimatic\Calendar;

/**
 * Utility class for Unix timestamp manipulation and analysis.
 * Provides methods for:
 * - Checking if timestamps are in the past
 * - Creating timestamps from date components
 * - Finding next/previous day of week from a given date
 */
class Timestamp
{
	/**
	 * Checks if a date (ignoring time) is in the past.
	 * Compares the date portion only (midnight) with today's midnight.
	 * @param int $timestamp The Unix timestamp to check
	 * @return bool True if the date is before today, false otherwise
	 */
	public static function isDateInThePast(int $timestamp): bool
	{
		return $timestamp < mktime(0, 0, 0, date('m'), date('d'), date('Y'));
	}

	/**
	 * Checks if a timestamp (including time) is in the past.
	 * Compares with the current date and time.
	 * @param int $timestamp The Unix timestamp to check
	 * @return bool True if the timestamp is before now, false otherwise
	 */
	public static function isTimeInThePast(int $timestamp): bool
	{
		return $timestamp <  mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
	}

	/**
	 * Creates a Unix timestamp from year, month, and day components.
	 * The time is set to midnight (00:00:00).
	 * @param int $year The year (e.g., 2024)
	 * @param int $month The month (1-12)
	 * @param int $day The day of month (1-31)
	 * @return int The Unix timestamp at midnight of the specified date
	 */
	public static function getByYearMonthDay(int $year, int $month, int $day): int
	{
		return mktime(0, 0, 0, $month, $day, $year);
	}

	/**
	 * Finds the next occurrence of a specific day of the week from a given date.
	 * If the given date is already the desired day, returns that date.
	 * @param int $dayOfWeekInNumeric The target day of week (1=Monday, 7=Sunday, ISO-8601)
	 * @param int $year The starting year
	 * @param int $month The starting month (1-12)
	 * @param int $day The starting day of month (1-31)
	 * @return int The Unix timestamp of the next occurrence of the specified day
	 */
	public static function getTimestampNextDayOfWeekByYearMonthDay(int $dayOfWeekInNumeric, int $year, int $month, int $day): int
	{
		$timestampCurrent = mktime(0, 0, 0, $month, $day, $year);
		while (((int) date('N', $timestampCurrent)) !== $dayOfWeekInNumeric) {
			$timestampCurrent += 86400;
		}
		return $timestampCurrent;
	}

	/**
	 * Finds the previous occurrence of a specific day of the week from a given date.
	 * If the given date is already the desired day, returns that date.
	 * @param int $dayOfWeekInNumeric The target day of week (1=Monday, 7=Sunday, ISO-8601)
	 * @param int $year The starting year
	 * @param int $month The starting month (1-12)
	 * @param int $day The starting day of month (1-31)
	 * @return int The Unix timestamp of the previous occurrence of the specified day
	 */
	public static function getTimestampPreviousDayOfWeekByYearMonthDay(int $dayOfWeekInNumeric, int $year, int $month, int $day): int
	{
		$timestampCurrent = mktime(0, 0, 0, $month, $day, $year);
		while (((int) date('N', $timestampCurrent)) !== $dayOfWeekInNumeric) {
			$timestampCurrent -= 86400;
		}
		return $timestampCurrent;
	}

	/**
	 * Finds the next occurrence of a specific day of the week from a timestamp.
	 * If the timestamp is already the desired day, returns that timestamp.
	 * @param int $dayOfWeekInNumeric The target day of week (1=Monday, 7=Sunday, ISO-8601)
	 * @param int $timestamp The starting Unix timestamp
	 * @return int The Unix timestamp of the next occurrence of the specified day
	 */
	public static function getNextDayOfWeekOfWeek(int $dayOfWeekInNumeric, int $timestamp): int
	{
		return self::getTimestampNextDayOfWeekByYearMonthDay($dayOfWeekInNumeric, date('Y', $timestamp), date('m', $timestamp), date('d', $timestamp));
	}

	/**
	 * Finds the previous occurrence of a specific day of the week from a timestamp.
	 * If the timestamp is already the desired day, returns that timestamp.
	 * @param int $dayOfWeekInNumeric The target day of week (1=Monday, 7=Sunday, ISO-8601)
	 * @param int $timestamp The starting Unix timestamp
	 * @return int The Unix timestamp of the previous occurrence of the specified day
	 */
	public static function getPreviousDayOfWeekOfWeek(int $dayOfWeekInNumeric, int $timestamp): int
	{
		return self::getTimestampPreviousDayOfWeekByYearMonthDay($dayOfWeekInNumeric, date('Y', $timestamp), date('m', $timestamp), date('d', $timestamp));
	}
}