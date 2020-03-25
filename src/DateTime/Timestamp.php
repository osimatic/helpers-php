<?php

namespace Osimatic\Helpers\DateTime;

class Timestamp
{
	public static function isDateInThePast(int $timestamp): bool
	{
		return $timestamp < mktime(0, 0, 0, date('m'), date('d'), date('Y'));
	}

	public static function isTimeInThePast(int $timestamp): bool
	{
		return $timestamp <  mktime(date('H'), date('i'), date('s'), date('m'), date('d'), date('Y'));
	}

	public static function getByYearMonthDay($year, $month, $day): int
	{
		return mktime(0, 0, 0, (int) $month, (int) $day, (int) $year);
	}

	public static function getTimestampNextDayOfWeekByYearMonthDay(int $dayOfWeekInNumeric, $year, $month, $day): int
	{
		$timestampCurrent = mktime(0, 0, 0, $month, $day, $year);
		while (date('N', $timestampCurrent) != $dayOfWeekInNumeric) {
			$timestampCurrent += 86400;
		}
		return $timestampCurrent;
	}

	public static function getTimestampPreviousDayOfWeekByYearMonthDay(int $dayOfWeekInNumeric, $year, $month, $day): int
	{
		$timestampCurrent = mktime(0, 0, 0, $month, $day, $year);
		while (date('N', $timestampCurrent) != $dayOfWeekInNumeric) {
			$timestampCurrent -= 86400;
		}
		return $timestampCurrent;
	}

	public static function getNextDayOfWeekOfWeek(int $dayOfWeekInNumeric, $timestamp): int
	{
		return self::getTimestampNextDayOfWeekByYearMonthDay($dayOfWeekInNumeric, date('Y', $timestamp), date('m', $timestamp), date('d', $timestamp));
	}

	public static function getPreviousDayOfWeekOfWeek(int $dayOfWeekInNumeric, $timestamp): int
	{
		return self::getTimestampPreviousDayOfWeekByYearMonthDay($dayOfWeekInNumeric, date('Y', $timestamp), date('m', $timestamp), date('d', $timestamp));
	}
}