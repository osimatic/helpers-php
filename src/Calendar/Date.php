<?php

namespace Osimatic\Calendar;

class Date
{
	// ========== Parse ==========

	/**
	 * @param string $str
	 * @return null|\DateTime
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


	// ========== Jour de la semaine ==========

	/**
	 * @param int $dayOfWeek ISO-8601 numeric representation of the day of the week : 1 (for Monday) through 7 (for Sunday)
	 * @param string|null $locale
	 * @see http://userguide.icu-project.org/formatparse/datetime
	 * @return string
	 */
	public static function getDayName(int $dayOfWeek, ?string $locale=null): string
	{
		$timestamp = strtotime('monday this week')+(($dayOfWeek-1)*3600*24);
		return ucfirst(\IntlDateFormatter::create($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL,
			date_default_timezone_get(), \IntlDateFormatter::GREGORIAN , 'EEEE')->format($timestamp));
		//return ucfirst(strftime('%A', ($timestamp+($dayOfWeek*3600*24))));
	}

	// ========== Jour du mois ==========

	// ========== Mois ==========

	/**
	 * @param int $month Numeric representation of a month
	 * @param string|null $locale
	 * @see http://userguide.icu-project.org/formatparse/datetime
	 * @return string
	 */
	public static function getMonthName(int $month, ?string $locale=null): string
	{
		return ucfirst(\IntlDateFormatter::create($locale, \IntlDateFormatter::FULL, \IntlDateFormatter::FULL,
			date_default_timezone_get(), \IntlDateFormatter::GREGORIAN , 'MMMM')->format(new \DateTime('2020-'.sprintf('%02d', $month).'-15 00:00:00')));
		//return ucfirst(strftime('%B', mktime(0, 0, 0, $month)));
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @return int
	 */
	public static function getNumberOfDaysInMonth(int $year, int $month): int
	{
		return date('t', mktime(0, 0, 0, $month, 1, $year));
	}

	// ========== Année ==========

	/**
	 * @param int $year
	 * @return int
	 */
	public static function isLeapYear(int $year): int
	{
		return ((($year % 4) == 0) && ((($year % 100) != 0) || (($year %400) == 0)));
	}

	/**
	 * @param int $year
	 * @return int
	 */
	public static function getNumberOfDaysInYear(int $year): int
	{
		return self::isLeapYear($year) ? 366 : 365;
	}

}