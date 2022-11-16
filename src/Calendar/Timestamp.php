<?php

namespace Osimatic\Helpers\Calendar;

class Timestamp
{
	/**
	 * @param int $timestamp
	 * @return bool
	 */
	public static function isDateInThePast(int $timestamp): bool
	{
		return $timestamp < mktime(0, 0, 0, date('m'), date('d'), date('Y'));
	}

	/**
	 * @param int $timestamp
	 * @return bool
	 */
	public static function isTimeInThePast(int $timestamp): bool
	{
		return $timestamp <  mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 * @return int
	 */
	public static function getByYearMonthDay(int $year, int $month, int $day): int
	{
		return mktime(0, 0, 0, $month, $day, $year);
	}

	/**
	 * @param int $dayOfWeekInNumeric
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 * @return int
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
	 * @param int $dayOfWeekInNumeric
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 * @return int
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
	 * @param int $dayOfWeekInNumeric
	 * @param int $timestamp
	 * @return int
	 */
	public static function getNextDayOfWeekOfWeek(int $dayOfWeekInNumeric, int $timestamp): int
	{
		return self::getTimestampNextDayOfWeekByYearMonthDay($dayOfWeekInNumeric, date('Y', $timestamp), date('m', $timestamp), date('d', $timestamp));
	}

	/**
	 * @param int $dayOfWeekInNumeric
	 * @param int $timestamp
	 * @return int
	 */
	public static function getPreviousDayOfWeekOfWeek(int $dayOfWeekInNumeric, int $timestamp): int
	{
		return self::getTimestampPreviousDayOfWeekByYearMonthDay($dayOfWeekInNumeric, date('Y', $timestamp), date('m', $timestamp), date('d', $timestamp));
	}
}