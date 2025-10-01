<?php

namespace Osimatic\Calendar;

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
	 * @param bool $withTimes
	 * @return int
	 */
	public static function getNbDays(\DateTime $periodStart, \DateTime $periodEnd, bool $withTimes=false): int
	{
		try {
			if (!$withTimes) {
				$periodStart = new \DateTime($periodStart->format('Y-m-d').' 00:00:00');
				$periodEnd = new \DateTime($periodEnd->format('Y-m-d').' 00:00:00');
			}
			return (int) $periodStart->diff($periodEnd)->format('%r%a');
		} catch (\Exception) {}
		return 0;
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return int
	 */
	public static function getNbRemainingDays(\DateTime $periodStart, \DateTime $periodEnd): int
	{
		try {
			$periodStart = new \DateTime($periodStart->format('Y-m-d').' 00:00:00');
			$periodEnd = new \DateTime($periodEnd->format('Y-m-d').' 00:00:00');
			return (int) $periodStart->diff($periodEnd)->d;
		} catch (\Exception) {}
		return 0;
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
		catch (\Exception) {
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
		return array_map(fn(\DateTime $date) => $date->format($dateFormat), self::getListOfDateDaysOfTheMonth($periodStart, $periodEnd, $weekDays));
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
	 * @return \DateTime[]
	 */
	public static function getListOfDateWeeks(\DateTime $periodStart, \DateTime $periodEnd): array
	{
		$startIntervalDate = (clone $periodStart)->modify('Monday this week');
		$endIntervalDate = (clone $periodEnd)->modify('this Sunday');

		try {
			$dateRange = new \DatePeriod($startIntervalDate, new \DateInterval('P1W'), $endIntervalDate);

			$periodList = [];
			foreach ($dateRange as $date) {
				$periodList[] = $date;
			}
			return $periodList;
		}
		catch (\Exception) {
			return [];
		}
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @param string $dateFormat
	 * @return string[]
	 */
	public static function getListOfWeeks(\DateTime $periodStart, \DateTime $periodEnd, string $dateFormat='Y-W'): array
	{
		return array_map(fn(\DateTime $date) => $date->format($dateFormat), self::getListOfDateWeeks($periodStart, $periodEnd));
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return bool
	 */
	public static function isFullWeek(\DateTime $periodStart, \DateTime $periodEnd): bool
	{
		return self::getNbDays($periodStart, $periodEnd, true) === 6 && ((int) $periodStart->format('N')) === 1 && ((int) $periodEnd->format('N')) === 7;
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return bool
	 */
	public static function isFullWeeks(\DateTime $periodStart, \DateTime $periodEnd): bool
	{
		return ((int) $periodStart->format('N')) === 1 && ((int) $periodEnd->format('N')) === 7;
	}

	// ========== Mois ==========

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return int
	 */
	public static function getNbFullMonths(\DateTime $periodStart, \DateTime $periodEnd): int
	{
		try {
			$startDate = new \DateTime($periodStart->format('Y-m-d').' 00:00:00');
			$endDate = new \DateTime($periodEnd->format('Y-m-d').' 00:00:00');
			$dateInterval = $startDate->diff($endDate);
			return (($dateInterval->y) * 12) + ($dateInterval->m);
		} catch (\Exception $e) { }
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
		return array_map(fn(\DateTime $date) => $date->format($dateFormat), self::getListOfDateMonths($periodStart, $periodEnd));
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return \DateTime[]
	 */
	public static function getListOfDateMonths(\DateTime $periodStart, \DateTime $periodEnd): array
	{
		$startIntervalDate = (clone $periodStart)->modify('first day of this month');
		$endIntervalDate = (clone $periodEnd)->modify('last day of this month');

		try {
			$dateRange = new \DatePeriod($startIntervalDate, new \DateInterval('P1M'), $endIntervalDate);

			$periodList = [];
			foreach ($dateRange as $date) {
				$periodList[] = $date;
			}
			return $periodList;
		}
		catch (\Exception) {
			return [];
		}
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return bool
	 */
	public static function isFullMonth(\DateTime $periodStart, \DateTime $periodEnd): bool
	{
		return $periodStart->format('Ym') === $periodEnd->format('Ym') && ((int) $periodStart->format('d')) === 1 && null !== ($lastDayOfMonth = DateTime::getLastDayOfMonth($periodEnd->format('Y'), $periodEnd->format('m'))) && $lastDayOfMonth->format('d') === $periodEnd->format('d');
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return bool
	 */
	public static function isFullMonths(\DateTime $periodStart, \DateTime $periodEnd): bool
	{
		return ((int) $periodStart->format('d')) === 1 && null !== ($lastDayOfMonth = DateTime::getLastDayOfMonth($periodEnd->format('Y'), $periodEnd->format('m'))) && $lastDayOfMonth->format('d') === $periodEnd->format('d');
	}

	// ========== Année ==========

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return bool
	 */
	public static function isFullYear(\DateTime $periodStart, \DateTime $periodEnd): bool
	{
		return ((int) $periodStart->format('m')) === 1 && ((int) $periodStart->format('d')) === 1 && ((int) $periodEnd->format('m')) === 12 && ((int) $periodEnd->format('d')) === 31 && ((int) $periodStart->format('Y')) === ((int) $periodEnd->format('Y'));
	}

	/**
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return int
	 */
	public static function getNbYears(\DateTime $periodStart, \DateTime $periodEnd): int
	{
		$nbSeconds = $periodEnd->setTime(0, 0, 0)->getTimestamp() - $periodStart->setTime(0, 0, 0)->getTimestamp();
		return (int) floor($nbSeconds / (3600*24*365.25));
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
	 * @param PeriodType $periodType
	 * @param \DateTime $periodStart
	 * @param \DateTime $periodEnd
	 * @return array|null
	 */
	public static function getListOfPeriod(PeriodType $periodType, \DateTime $periodStart, \DateTime $periodEnd): ?array
	{
		if (PeriodType::HOUR === $periodType) {
			return ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
		}
		if (PeriodType::DAY_OF_MONTH === $periodType) {
			return self::getListOfDaysOfTheMonth($periodStart, $periodEnd, null, 'Y-m-d');
		}
		if (PeriodType::WEEK === $periodType) {
			return self::getListOfWeeks($periodStart, $periodEnd, 'Y-W');
		}
		if (PeriodType::MONTH === $periodType) {
			return self::getListOfMonths($periodStart, $periodEnd, 'Y-n');
		}
		if (PeriodType::DAY_OF_WEEK === $periodType) {
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

	/**
	 * @deprecated
	 * @param \DateTime $startDateTime
	 * @param \DateTime $endDateTime
	 * @return int
	 */
	public static function getNbDaysBetweenDatesAndTimes(\DateTime $startDateTime, \DateTime $endDateTime): int
	{
		return self::getNbDays($startDateTime, $endDateTime, true);
	}

}