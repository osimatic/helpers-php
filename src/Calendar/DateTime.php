<?php

namespace Osimatic\Helpers\Calendar;

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
	 * @param int $dateFormatter
	 * @return string
	 */
	public static function formatDate(\DateTime $dateTime, ?string $locale=null, int $dateFormatter=\IntlDateFormatter::SHORT): string
	{
		return \IntlDateFormatter::create($locale, $dateFormatter, \IntlDateFormatter::NONE)->format($dateTime->getTimestamp());
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatDateInLong(\DateTime $dateTime, ?string $locale=null): string
	{
		return self::formatDate($dateTime, $locale, \IntlDateFormatter::LONG);
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @param int $timeFormatter
	 * @return string
	 */
	public static function formatTime(\DateTime $dateTime, ?string $locale=null, int $timeFormatter=\IntlDateFormatter::SHORT): string
	{
		return \IntlDateFormatter::create($locale, \IntlDateFormatter::NONE, $timeFormatter)->format($dateTime->getTimestamp());
	}


	/**
	 * @param \DateTime $dateTime
	 * @param string $dateFormatter
	 * @param string $timeFormatter
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatFromTwig(?\DateTime $dateTime, string $dateFormatter, string $timeFormatter, ?string $locale=null): ?string
	{
		if (null === $dateTime) {
			return null;
		}

		return self::format($dateTime, self::getDateTimeFormatterFromTwig($dateFormatter), self::getDateTimeFormatterFromTwig($timeFormatter), $locale);
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string $dateFormatter
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatDateFromTwig(?\DateTime $dateTime, string $dateFormatter, ?string $locale=null): ?string
	{
		if (null === $dateTime) {
			return null;
		}

		return self::format($dateTime, self::getDateTimeFormatterFromTwig($dateFormatter), \IntlDateFormatter::NONE, $locale);
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string $timeFormatter
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatTimeFromTwig(?\DateTime $dateTime, string $timeFormatter, ?string $locale=null): ?string
	{
		if (null === $dateTime) {
			return null;
		}

		return self::format($dateTime, \IntlDateFormatter::NONE, self::getDateTimeFormatterFromTwig($timeFormatter), $locale);
	}

	/**
	 * @param string $formatter
	 * @return int
	 */
	private static function getDateTimeFormatterFromTwig(string $formatter): int
	{
		switch ($formatter) {
			case 'none': return \IntlDateFormatter::NONE;
			case 'full': return \IntlDateFormatter::FULL;
			case 'long': return \IntlDateFormatter::LONG;
			case 'medium': return \IntlDateFormatter::MEDIUM;
		}
		return \IntlDateFormatter::SHORT;
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
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfCurrentWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getFirstDayOfWeek(date('Y'), date('m')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfCurrentWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getLastDayOfWeek(date('Y'), date('m')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfPreviousWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('first day of previous week')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfPreviousWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('last day of previous week')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfNextWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('first day of next week')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfNextWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('last day of next week')).' 00:00:00');
	}

	/**
	 * @param int $year
	 * @param int $week
	 * @return string
	 */
	public static function getFirstDayOfWeek(int $year, int $week): string
	{
		return self::parseFromSqlDateTime(SqlDate::getFirstDayOfWeek($year, $week).' 00:00:00');
	}

	/**
	 * @param int $year
	 * @param int $week
	 * @return string
	 */
	public static function getLastDayOfWeek(int $year, int $week): string
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
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfPreviousMonth(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('first day of previous month')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfPreviousMonth(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('last day of previous month')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfNextMonth(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('first day of next month')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfNextMonth(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('last day of next month')).' 00:00:00');
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfMonth(int $year, int $month): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getFirstDayOfMonth($year, $month).' 00:00:00');
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @return \DateTime|null
	 */
	public static function getLastDayOfMonth(int $year, int $month): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getLastDayOfMonth($year, $month).' 00:00:00');
	}

}