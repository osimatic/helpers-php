<?php

namespace Osimatic\Calendar;

class DateTime
{
	/**
	 * @return \DateTime
	 */
	public static function getCurrentDateTime(): \DateTime
	{
		return new \DateTime();
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
		return \IntlDateFormatter::create($locale, $dateFormatter, $timeFormatter)?->format($dateTime->getTimestamp()) ?? '';
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatDateTime(\DateTime $dateTime, ?string $locale=null): string
	{
		return \IntlDateFormatter::create($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT)?->format($dateTime->getTimestamp()) ?? '';
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @param int $dateFormatter
	 * @return string
	 */
	public static function formatDate(\DateTime $dateTime, ?string $locale=null, int $dateFormatter=\IntlDateFormatter::SHORT): string
	{
		return \IntlDateFormatter::create($locale, $dateFormatter, \IntlDateFormatter::NONE)?->format($dateTime->getTimestamp()) ?? '';
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @param bool $withWeekDay
	 * @return string
	 */
	public static function formatDateInLong(\DateTime $dateTime, ?string $locale=null, bool $withWeekDay=false): string
	{
		return self::formatDate($dateTime, $locale, $withWeekDay ? \IntlDateFormatter::FULL : \IntlDateFormatter::LONG);
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @param int $timeFormatter
	 * @return string
	 */
	public static function formatTime(\DateTime $dateTime, ?string $locale=null, int $timeFormatter=\IntlDateFormatter::SHORT): string
	{
		return \IntlDateFormatter::create($locale, \IntlDateFormatter::NONE, $timeFormatter)?->format($dateTime->getTimestamp()) ?? '';
	}


	/**
	 * @param string|\DateTime|null $dateTime
	 * @param string $dateFormatter
	 * @param string $timeFormatter
	 * @param string|null $locale
	 * @return string|null
	 * @throws \Exception
	 */
	public static function formatFromTwig(string|\DateTime|null $dateTime, string $dateFormatter='short', string $timeFormatter='short', ?string $locale=null): ?string
	{
		if (null === $dateTime) {
			return null;
		}

		if (is_string($dateTime)) {
			$dateTime = new \DateTime($dateTime);
		}

		return self::format($dateTime, self::getDateTimeFormatterFromTwig($dateFormatter), self::getDateTimeFormatterFromTwig($timeFormatter), $locale);
	}

	/**
	 * @param \DateTime|null $dateTime
	 * @param string $dateFormatter
	 * @param string|null $locale
	 * @return string|null
	 */
	public static function formatDateFromTwig(?\DateTime $dateTime, string $dateFormatter='short', ?string $locale=null): ?string
	{
		if (null === $dateTime) {
			return null;
		}

		return self::format($dateTime, self::getDateTimeFormatterFromTwig($dateFormatter), \IntlDateFormatter::NONE, $locale);
	}

	/**
	 * @param \DateTime|null $dateTime
	 * @param string $timeFormatter
	 * @param string|null $locale
	 * @return string|null
	 */
	public static function formatTimeFromTwig(?\DateTime $dateTime, string $timeFormatter='short', ?string $locale=null): ?string
	{
		if (null === $dateTime) {
			return null;
		}

		return self::format($dateTime, \IntlDateFormatter::NONE, self::getDateTimeFormatterFromTwig($timeFormatter), $locale);
	}

	/**
	 * @param \DateTime|null $dateTime
	 * @return string|null
	 */
	public static function getUTCSqlDate(?\DateTime $dateTime): ?string
	{
		return null !== $dateTime ? (clone $dateTime)->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d') : null;
	}

	/**
	 * @param \DateTime|null $dateTime
	 * @return string|null
	 */
	public static function getUTCSqlTime(?\DateTime $dateTime): ?string
	{
		return null !== $dateTime ? (clone $dateTime)->setTimezone(new \DateTimeZone('UTC'))->format('H:i:s') : null;
	}

	/**
	 * @param \DateTime|null $dateTime
	 * @return string|null
	 */
	public static function getUTCSqlDateTime(?\DateTime $dateTime): ?string
	{
		return null !== $dateTime ? (clone $dateTime)->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s') : null;
	}

	/**
	 * @param string $formatter
	 * @return int
	 */
	private static function getDateTimeFormatterFromTwig(string $formatter): int
	{
		return match ($formatter) {
			'none' => \IntlDateFormatter::NONE,
			'full' => \IntlDateFormatter::FULL,
			'long' => \IntlDateFormatter::LONG,
			'medium' => \IntlDateFormatter::MEDIUM,
			default => \IntlDateFormatter::SHORT,
		};
	}



	/**
	 * @param string $str
	 * @return null|\DateTime
	 */
	public static function parse(string $str): ?\DateTime
	{
		return Date::parse($str);
	}

	/**
	 * @param string $sqlDateTime
	 * @return \DateTime|null
	 */
	public static function parseFromSqlDateTime(string $sqlDateTime): ?\DateTime
	{
		try {
			return new \DateTime($sqlDateTime);
		}
		catch (\Exception) { }
		return null;
	}

	/**
	 * @param int $timestamp
	 * @return \DateTime|null
	 */
	public static function parseFromTimestamp(int $timestamp): ?\DateTime
	{
		//return new \DateTime('@'.$timestamp);
		return self::parseFromSqlDateTime(date('Y-m-d H:i:s', $timestamp));
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 * @return \DateTime|null
	 */
	public static function parseFromYearMonthDay(int $year, int $month, int $day): ?\DateTime
	{
		try {
			$d = new \DateTime();
			$d->setDate($year, $month, $day);
			return $d;
		} catch (\Exception) {}
		return null;
	}

	// ========== Comparaison ==========

	/**
	 * @param \DateTime $dateTime1
	 * @param \DateTime $dateTime2
	 * @return bool
	 */
	public static function isDateAfter(\DateTime $dateTime1, \DateTime $dateTime2): bool
	{
		return $dateTime1->format('Ymd') > $dateTime2->format('Ymd');
	}

	/**
	 * @param \DateTime $dateTime1
	 * @param \DateTime $dateTime2
	 * @return bool
	 */
	public static function isDateBefore(\DateTime $dateTime1, \DateTime $dateTime2): bool
	{
		return $dateTime1->format('Ymd') < $dateTime2->format('Ymd');
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public static function isInThePast(\DateTime $dateTime): bool
	{
		return $dateTime < self::getCurrentDateTime();
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public static function isInTheFuture(\DateTime $dateTime): bool
	{
		return $dateTime > self::getCurrentDateTime();
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public static function isDateInThePast(\DateTime $dateTime): bool
	{
		return $dateTime->format('Ymd') < self::getCurrentDateTime()->format('Ymd');
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public static function isDateInTheFuture(\DateTime $dateTime): bool
	{
		return $dateTime->format('Ymd') > self::getCurrentDateTime()->format('Ymd');
	}



	// ========== Jour ==========

	// Jours dans une semaine

	/**
	 * Jour ouvré avec jour férié ou non
	 * @param \DateTime $dateTime
	 * @param bool $withPublicHoliday
	 * @return bool
	 */
	public static function isWorkingDay(\DateTime $dateTime, bool $withPublicHoliday=true): bool
	{
		if (self::isWeekend($dateTime)) {
			return false;
		}
		if ($withPublicHoliday && PublicHolidays::isPublicHoliday($dateTime)) {
			return false;
		}
		return true;
	}

	/**
	 * Jour ouvrable avec jour férié ou non
	 * @param \DateTime $dateTime
	 * @param bool $withPublicHoliday
	 * @return bool
	 */
	public static function isBusinessDay(\DateTime $dateTime, bool $withPublicHoliday=true): bool
	{
		$dayOfWeek = (int) $dateTime->format('N');
		if ($dayOfWeek === 7) {
			return false;
		}
		if ($withPublicHoliday && PublicHolidays::isPublicHoliday($dateTime)) {
			return false;
		}
		return true;
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public static function isWeekend(\DateTime $dateTime): bool
	{
		$dayOfWeek = (int) $dateTime->format('N');
		return ($dayOfWeek === 6 || $dayOfWeek === 7);
	}

	/**
	 * @param \DateTime $dateTime
	 * @param int $nbDays
	 * @return \DateTime
	 */
	public static function moveBackOfNbDays(\DateTime $dateTime, int $nbDays): \DateTime
	{
		try {
			$dateTime = new \DateTime($dateTime->format('Y-m-d H:i:s'));
		} catch (\Exception) {}
		return $dateTime->modify('-'.$nbDays.' day');
	}

	/**
	 * @param \DateTime $dateTime
	 * @param int $nbDays
	 * @return \DateTime
	 */
	public static function moveForwardOfNbDays(\DateTime $dateTime, int $nbDays): \DateTime
	{
		try {
			$dateTime = new \DateTime($dateTime->format('Y-m-d H:i:s'));
		} catch (\Exception) {}
		return $dateTime->modify('+'.$nbDays.' day');
	}


	// ========== Semaine ==========

	/**
	 * @param \DateTime $dateTime
	 * @return array
	 */
	public static function getWeekNumber(\DateTime $dateTime): array
	{
		$weekNumber = $dateTime->format('W');
		$year = $dateTime->format('Y');
		// si weekNumber = 1 et que mois de sqlDate = 12, mettre year++
		if (((int)$weekNumber) === 1 && ((int)$dateTime->format('m')) === 12) {
			$year++;
		}
		return [$year, $weekNumber];
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfCurrentWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getFirstDayOfWeek(date('Y'), date('W')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfCurrentWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getLastDayOfWeek(date('Y'), date('W')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfPreviousWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('monday last week')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfPreviousWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('sunday last week')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfNextWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('monday next week')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfNextWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('sunday next week')).' 00:00:00');
	}

	/**
	 * @param int $year
	 * @param int $week
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfWeek(int $year, int $week): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getFirstDayOfWeek($year, $week).' 00:00:00');
	}

	/**
	 * @param int $year
	 * @param int $week
	 * @return \DateTime|null
	 */
	public static function getLastDayOfWeek(int $year, int $week): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getLastDayOfWeek($year, $week).' 00:00:00');
	}

	/**
	 * @param \DateTime $dateTime
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfWeekOfDate(\DateTime $dateTime): ?\DateTime
	{
		return self::getFirstDayOfWeek((int) $dateTime->format('Y'), (int) $dateTime->format('W'));
	}

	/**
	 * @param \DateTime $dateTime
	 * @return \DateTime|null
	 */
	public static function getLastDayOfWeekOfDate(\DateTime $dateTime): ?\DateTime
	{
		return self::getLastDayOfWeek((int) $dateTime->format('Y'), (int) $dateTime->format('W'));
	}

	/**
	 * @param \DateTime $dateTime
	 * @param int $weekDay
	 * @return \DateTime
	 */
	public static function getNextWeekDay(\DateTime $dateTime, int $weekDay): \DateTime
	{
		//$timestampCurrent = $dateTime->getTimestamp();
		//while (((int) date('N', $timestampCurrent)) !== $weekDay) {
		//	$timestampCurrent += 86400;
		//}
		//return new \DateTime(date('Y-m-d H:i:s', $timestampCurrent));
		while (((int) $dateTime->format('N')) !== $weekDay) {
			$dateTime->modify('+1 day');
		}
		return $dateTime;
	}

	// ========== Mois ==========

	/**
	 * @param \DateTime $dateTime
	 * @param int $nbMonths
	 * @return \DateTime
	 */
	public static function moveBackOfNbMonths(\DateTime $dateTime, int $nbMonths): \DateTime
	{
		try {
			$dateTime = new \DateTime($dateTime->format('Y-m-d H:i:s'));
		} catch (\Exception) {}
		return $dateTime->modify('-'.$nbMonths.' month');
	}

	/**
	 * @param \DateTime $dateTime
	 * @param int $nbMonths
	 * @return \DateTime
	 */
	public static function moveForwardOfNbMonths(\DateTime $dateTime, int $nbMonths): \DateTime
	{
		try {
			$dateTime = new \DateTime($dateTime->format('Y-m-d H:i:s'));
		} catch (\Exception) {}
		return $dateTime->modify('+'.$nbMonths.' month');
	}

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

	/**
	 * @param \DateTime $dateTime
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfMonthOfDate(\DateTime $dateTime): ?\DateTime
	{
		return self::getFirstDayOfMonth($dateTime->format('Y'), $dateTime->format('m'));
	}

	/**
	 * @param \DateTime $dateTime
	 * @return \DateTime|null
	 */
	public static function getLastDayOfMonthOfDate(\DateTime $dateTime): ?\DateTime
	{
		return self::getLastDayOfMonth($dateTime->format('Y'), $dateTime->format('m'));
	}

	/**
	 * Renvoi le n-ième jour de la semaine d'un mois donné. Exemple : "2ème mercredi du mois"
	 * @param int $year
	 * @param int $month
	 * @param int $weekDay
	 * @param int $number
	 * @return \DateTime|null
	 */
	public static function getWeekDayOfMonth(int $year, int $month, int $weekDay, int $number): ?\DateTime
	{
		$weekDayName = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'][$weekDay-1] ?? null;
		if (null === $weekDayName) {
			return null;
		}

		$numberName = ['first', 'second', 'third', 'fourth', 'fifth'][$number-1] ?? null;
		if (null === $numberName) {
			return null;
		}

		try {
			$dateTime = new \DateTime($year.'-'.$month.'-01 00:00:00');
			$dateTime->modify($numberName.' '.$weekDayName.' of this month');

			if (((int) $dateTime->format('Y')) !== $year || ((int) $dateTime->format('m')) !== $month) {
				return null;
			}

			return $dateTime;
		}
		catch (\Exception) {}
		return null;
	}

	/**
	 * Renvoi le dernier jour de la semaine d'un mois donné. Exemple : "Dernier mercredi du mois"
	 * @param int $year
	 * @param int $month
	 * @param int $weekDay
	 * @return \DateTime|null
	 */
	public static function getLastWeekDayOfMonth(int $year, int $month, int $weekDay): ?\DateTime
	{
		$weekDayName = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'][$weekDay-1] ?? null;
		if (null === $weekDayName) {
			return null;
		}
		try {
			$dateTime = new \DateTime($year.'-'.$month.'-01 00:00:00');
			$dateTime->modify('+1 month');
			$dateTime->modify('last '.$weekDayName);
			return $dateTime;
		}
		catch (\Exception) {}
		return null;
	}



	// ========== Année ==========

	/**
	 * @param \DateTime $from
	 * @return int
	 */
	public static function calculateAge(\DateTime $from): int
	{
		$to = new \DateTime();
		return (int) $from->diff($to)->y;
	}











	/**
	 * @deprecated replace by DateTime::parse()
	 * @param string $str
	 * @return null|\DateTime
	 */
	public static function parseDate(string $str): ?\DateTime
	{
		if (empty($str)) {
			return null;
		}

		// Format YYYY-mm-ddTHH:ii:ss
		if (strlen($str) === strlen('YYYY-mm-ddTHH:ii:ss') && null !== ($dateTime = self::parseFromSqlDateTime($str))) {
			return $dateTime;
		}

		//if (false !== SqlDate::check($sqlDate = SqlDate::parse($str))) {
		if (null !== ($sqlDate = SqlDate::parse($str)) && false !== SqlDate::check($sqlDate)) {
			return self::parseFromSqlDateTime($sqlDate.' 00:00:00');
		}

		return null;
	}

}