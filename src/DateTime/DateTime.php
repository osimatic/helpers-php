<?php

namespace Osimatic\Helpers\DateTime;

class DateTime
{
	/**
	 * @return \DateTime|null
	 */
	public static function getCurrentDateTime(): ?\DateTime
	{
		try {
			return new \DateTime('now');
		} catch (\Exception $e) {}
		return null;
	}

	/**
	 * @param \DateTime $dateTime
	 * @param int $dateFormatter
	 * @param int $timeFormatter
	 * @param string|null $locale
	 * @return string
	 */
	public static function format(\DateTime $dateTime, int $dateFormatter, int $timeFormatter, ?string $locale=null): string
	{
		return \IntlDateFormatter::create($locale, $dateFormatter, $timeFormatter)->format($dateTime->getTimestamp());
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatDateTime(\DateTime $dateTime, ?string $locale=null): string
	{
		return \IntlDateFormatter::create($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT)->format($dateTime->getTimestamp());
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatDate(\DateTime $dateTime, ?string $locale=null): string
	{
		return \IntlDateFormatter::create($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::NONE)->format($dateTime->getTimestamp());
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatTime(\DateTime $dateTime, ?string $locale=null): string
	{
		return \IntlDateFormatter::create($locale, \IntlDateFormatter::NONE, \IntlDateFormatter::SHORT)->format($dateTime->getTimestamp());
	}

	/**
	 * @param string $str
	 * @return null|\DateTime
	 */
	public static function parse(string $str): ?\DateTime
	{
		try {
			return new \DateTime($str);
		}
		catch (\Exception $e) { }
		return null;
	}

	/**
	 * @param string $str
	 * @return null|\DateTime
	 */
	public static function parseDate(string $str): ?\DateTime
	{
		if (empty($str) || false === SqlDate::check($sqlDate = SqlDate::parse($str))) {
			return null;
		}
		return self::parseFromSqlDateTime($sqlDate.' 00:00:00');
	}

	/**
	 * @param string $sqlDateTime
	 * @return \DateTime|null
	 */
	public static function parseFromSqlDateTime(string $sqlDateTime): ?\DateTime
	{
		try {
			return new \DateTime($sqlDateTime);
		} catch (\Exception $e) {}
		return null;
	}

	// ========== Semaine ==========

	/**
	 * @param $year
	 * @param $week
	 * @return string
	 */
	public static function getFirstDayOfWeek($year, $week): string
	{
		return self::parseFromSqlDateTime(SqlDate::getFirstDayOfWeek($year, $week).' 00:00:00');
	}

	/**
	 * @param $year
	 * @param $week
	 * @return string
	 */
	public static function getLastDayOfWeek($year, $week): string
	{
		return self::parseFromSqlDateTime(SqlDate::getLastDayOfWeek($year, $week).' 00:00:00');
	}

	// ========== Mois ==========

	/**
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfCurrentMonth(): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getFirstDayOfMonth(date('Y'), date('m')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfCurrentMonth(): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getLastDayOfMonth(date('Y'), date('m')).' 00:00:00');
	}

	/**
	 * @param $year
	 * @param $month
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfMonth($year, $month): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getFirstDayOfMonth($year, $month).' 00:00:00');
	}

	/**
	 * @param $year
	 * @param $month
	 * @return \DateTime|null
	 */
	public static function getLastDayOfMonth($year, $month): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getLastDayOfMonth($year, $month).' 00:00:00');
	}

}