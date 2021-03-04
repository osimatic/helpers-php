<?php

namespace Osimatic\Helpers\Calendar;

/**
 * Class DatePeriod
 * @package Osimatic\Helpers\Calendar
 */
class DatePeriod
{
	// ========== Jours ==========

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return int
	 */
	public static function getNbDays(\DateTime $periodStart, \DateTime $periodEnd): int
	{
		try {
			$startDate = new \DateTime($periodStart->format('Y-m-d').' 00:00:00');
			$endDate = new \DateTime($periodEnd->format('Y-m-d').' 00:00:00');
			return (int) $startDate->diff($endDate)->format('%r%a');
		} catch (\Exception $e) { }
		return 0;
	}

	/**
	 * @param \DateTime $startDateTime
	 * @param \DateTime $endDateTime
	 * @return int
	 */
	public static function getNbDaysBetweenDatesAndTimes(\DateTime $startDateTime, \DateTime $endDateTime): int
	{
		return (int) $startDateTime->diff($endDateTime)->format('%r%a');
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @param int[]|null $weekDays
	 * @return \DateTime[]
	 */
	public static function getListOfDateDaysOfTheMonth(\DateTime $periodStart, \DateTime $periodEnd, ?array $weekDays=null): array
	{
		$startIntervalDate = (clone $periodStart)->setTime(0, 0, 0);
		$endIntervalDate = (clone $periodEnd)->setTime(0, 0, 0)->modify('+1 day');

		try {
			$list = [];
			$dateRange = new \DatePeriod($startIntervalDate, new \DateInterval('P1D'), $endIntervalDate);
			foreach ($dateRange as $date) {
				if (null === $weekDays || in_array((int) $date->format('N'), $weekDays, true)) {
					$list[] = $date;
				}
			}
			return $list;
		}
		catch (\Exception $e) {
			return [];
		}

		/*
		for ($timestamp=$periodStart->getTimestamp(); $timestamp<=$periodEnd->getTimestamp(); $timestamp+=86400) {
			if (null === $weekDays || in_array((int) date('N', $timestamp), $weekDays, true)) {
				try {
					$list[] = new \DateTime(date('Y-m-d H:i:s', $timestamp));
					//$list[] = new \DateTime('@' . $timestamp); // pb de timezone
				} catch (\Exception $e) { }
			}
		}
		*/
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @param int[]|null $weekDays
	 * @param string $dateFormat
	 * @return string[]
	 */
	public static function getListOfDaysOfTheMonth(\DateTime $periodStart, \DateTime $periodEnd, ?array $weekDays=null, string $dateFormat='Y-m-d'): array
	{
		$startIntervalDate = (clone $periodStart)->setTime(0, 0, 0);
		$endIntervalDate = (clone $periodEnd)->setTime(0, 0, 0)->modify('+1 day');

		try {
			$list = [];
			$dateRange = new \DatePeriod($startIntervalDate, new \DateInterval('P1D'), $endIntervalDate);
			foreach ($dateRange as $date) {
				if (null === $weekDays || in_array((int) $date->format('N'), $weekDays, true)) {
					$list[] = $date->format($dateFormat);
				}
			}
			return $list;
		}
		catch (\Exception $e) {
			return [];
		}
	}

	// ========== Semaines ==========

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return int
	 */
	public static function getNbFullWeeks(\DateTime $periodStart, \DateTime $periodEnd): int
	{
		return floor(self::getNbDays($periodStart, $periodEnd) / 7);
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return int
	 */
	public static function getNbWeeks(\DateTime $periodStart, \DateTime $periodEnd): int
	{
		return count(self::getListOfWeeks($periodStart, $periodEnd));
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return string[]
	 */
	public static function getListOfWeeks(\DateTime $periodStart, \DateTime $periodEnd, string $dateFormat='Y-W'): array
	{
		$startIntervalDate = (clone $periodStart)->modify('Monday this week');
		$endIntervalDate = (clone $periodEnd)->modify('this Sunday');

		try {
			$dateRange = new \DatePeriod($startIntervalDate, new \DateInterval('P1W'), $endIntervalDate);

			$periodList = [];
			foreach ($dateRange as $date) {
				$periodList[] = $date->format($dateFormat);
			}
			return $periodList;
		}
		catch (\Exception $e) {
			return [];
		}
	}

	// ========== Mois ==========

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return int
	 */
	public static function getNbFullMonths(\DateTime $periodStart, \DateTime $periodEnd): int
	{
		// todo
		return 0;
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return int
	 */
	public static function getNbMonths(\DateTime $periodStart, \DateTime $periodEnd): int
	{
		return count(self::getListOfMonths($periodStart, $periodEnd));
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @param string $dateFormat
	 * @return string[]
	 */
	public static function getListOfMonths(\DateTime $periodStart, \DateTime $periodEnd, string $dateFormat='Y-n'): array
	{
		$startIntervalDate = (clone $periodStart)->modify('first day of this month');
		$endIntervalDate = (clone $periodEnd)->modify('last day of this month');

		try {
			$dateRange = new \DatePeriod($startIntervalDate, new \DateInterval('P1M'), $endIntervalDate);

			$periodList = [];
			foreach ($dateRange as $date) {
				$periodList[] = $date->format($dateFormat);
			}
			return $periodList;
		}
		catch (\Exception $e) {
			return [];
		}
	}


	// ========== Interval ==========

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return string
	 */
	public static function getLabel(\DateTime $periodStart, \DateTime $periodEnd): string
	{
		if ($periodStart->format('Y-m-d') === $periodEnd->format('Y-m-d')) {
			return 'le '.((int) $periodStart->format('d')).' '.Date::getMonthName((int) $periodStart->format('m')).' '.$periodStart->format('Y');
		}

		$periodStartYear = (int) $periodStart->format('Y');
		$periodEndYear = (int) $periodEnd->format('Y');

		if ($periodStartYear === $periodEndYear) {
			$periodStartDay = (int) $periodStart->format('d');
			$periodEndDay = (int) $periodEnd->format('d');
			$periodStartMonth = (int) $periodStart->format('m');
			$periodEndMonth = (int) $periodEnd->format('m');

			if ($periodStartMonth === $periodEndMonth && $periodStartDay === 1 && $periodEndDay === Date::getNumberOfDaysInMonth($periodEndMonth, $periodEndYear)) {
				return 'en '.Date::getMonthName($periodStartMonth).' '.$periodStartYear;
			}
			if ($periodStartDay === 1 && $periodStartMonth === 1 && $periodEndMonth === 12 && $periodEndDay === 31) {
				return 'en '.$periodStartYear;
			}
		}

		return 'du '.DateTime::formatDate($periodStart).' au '.DateTime::formatDate($periodEnd);
	}

	/**
	 * @param string $groupBy
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return array
	 */
	public static function getListOfPeriod(string $groupBy, \DateTime $periodStart, \DateTime $periodEnd): ?array
	{
		if ($groupBy === 'hour') {
			return ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
		}
		if ($groupBy === 'day_of_month') {
			return self::getListOfDaysOfTheMonth($periodStart, $periodEnd, null, 'Y-m-d');
		}
		if ($groupBy === 'week') {
			return self::getListOfWeeks($periodStart, $periodEnd, 'Y-W');
		}
		if ($groupBy === 'month') {
			return self::getListOfMonths($periodStart, $periodEnd, 'Y-n');
		}
		if ($groupBy === 'day_of_week') {
			return ['1', '2', '3', '4', '5', '6', '7'];
		}

		return null;
	}

	/*
	private static function getPeriod(\DateTime $startDate, \DateTime $endDate, $dateInterval, $dateFormat): ?array
	{
		if ($dateInterval === 'P1D') {
			$startIntervalDate = $startDate;
			$endIntervalDate = (clone $endDate)->modify('+1 day');
		}
		elseif ($dateInterval === 'P1W') {
			$startIntervalDate = (clone $startDate)->modify('Monday this week');
			$endIntervalDate = (clone $endDate)->modify('this Sunday');
		}
		elseif ($dateInterval === 'P1M') {
			$startIntervalDate = (clone $startDate)->modify('first day of this month');
			$endIntervalDate = (clone $endDate)->modify('last day of this month');
		}
		else {
			return null;
		}

		try {
			$interval = new \DateInterval($dateInterval);
		} catch (\Exception $e) {
			return null;
		}

		$dateRange = new \DatePeriod($startIntervalDate, $interval, $endIntervalDate);
		$periodList = [];
		foreach ($dateRange as $date) {
			$periodList[] = $date->format($dateFormat);
		}

		return $periodList;
	}
	*/


	// deprecated

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @param array|null $weekDays
	 * @return \DateTime[]
	 * @deprecated
	 */
	public static function getListDaysOfMonths(\DateTime $periodStart, \DateTime $periodEnd, ?array $weekDays=null): array
	{
		return self::getListOfDateDaysOfTheMonth($periodStart, $periodEnd, $weekDays);
	}

}