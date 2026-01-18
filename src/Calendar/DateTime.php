<?php

namespace Osimatic\Calendar;

/**
 * Utility class for DateTime manipulation, formatting, and calculations.
 * Provides methods for:
 * - DateTime creation and parsing from various formats
 * - Localized date and time formatting using IntlDateFormatter
 * - DateTime comparisons (past, future, before, after)
 * - Working day and business day calculations
 * - Week and month boundary calculations
 * - Date manipulation (moving forward/backward by days or months)
 * - Age calculation and weekday finding
 * - UTC conversions
 */
class DateTime
{
	// ========== Basic Methods ==========

	/**
	 * Gets the current date and time.
	 * @return \DateTime A new DateTime object representing now
	 */
	public static function getCurrentDateTime(): \DateTime
	{
		return new \DateTime();
	}

	// ========== Formatting Methods ==========

	/**
	 * Formats a DateTime using IntlDateFormatter with custom date and time format levels.
	 * Uses ICU IntlDateFormatter for internationalization support.
	 * @param \DateTime $dateTime The DateTime object to format
	 * @param int $dateFormatter IntlDateFormatter constant for date format (NONE, SHORT, MEDIUM, LONG, FULL)
	 * @param int $timeFormatter IntlDateFormatter constant for time format (NONE, SHORT, MEDIUM, LONG, FULL)
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string The formatted date/time string, or empty string on error
	 */
	public static function format(\DateTime $dateTime, int $dateFormatter, int $timeFormatter, ?string $locale=null): string
	{
		return \IntlDateFormatter::create($locale, $dateFormatter, $timeFormatter)?->format($dateTime->getTimestamp()) ?? '';
	}

	/**
	 * Formats a DateTime with SHORT format for both date and time.
	 * Uses ICU IntlDateFormatter for internationalization.
	 * @param \DateTime $dateTime The DateTime object to format
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string The formatted date and time string, or empty string on error
	 */
	public static function formatDateTime(\DateTime $dateTime, ?string $locale=null): string
	{
		return \IntlDateFormatter::create($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT)?->format($dateTime->getTimestamp()) ?? '';
	}

	/**
	 * Formats only the date portion of a DateTime.
	 * Uses ICU IntlDateFormatter for internationalization.
	 * @param \DateTime $dateTime The DateTime object to format
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @param int $dateFormatter IntlDateFormatter constant for date format (default: SHORT)
	 * @return string The formatted date string, or empty string on error
	 */
	public static function formatDate(\DateTime $dateTime, ?string $locale=null, int $dateFormatter=\IntlDateFormatter::SHORT): string
	{
		return \IntlDateFormatter::create($locale, $dateFormatter, \IntlDateFormatter::NONE)?->format($dateTime->getTimestamp()) ?? '';
	}

	/**
	 * Formats a date in LONG or FULL format.
	 * FULL format includes the day of the week, LONG format does not.
	 * @param \DateTime $dateTime The DateTime object to format
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @param bool $withWeekDay If true, uses FULL format with weekday; if false, uses LONG format (default: false)
	 * @return string The formatted date string, or empty string on error
	 */
	public static function formatDateInLong(\DateTime $dateTime, ?string $locale=null, bool $withWeekDay=false): string
	{
		return self::formatDate($dateTime, $locale, $withWeekDay ? \IntlDateFormatter::FULL : \IntlDateFormatter::LONG);
	}

	/**
	 * Formats only the time portion of a DateTime.
	 * Uses ICU IntlDateFormatter for internationalization.
	 * @param \DateTime $dateTime The DateTime object to format
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @param int $timeFormatter IntlDateFormatter constant for time format (default: SHORT)
	 * @return string The formatted time string, or empty string on error
	 */
	public static function formatTime(\DateTime $dateTime, ?string $locale=null, int $timeFormatter=\IntlDateFormatter::SHORT): string
	{
		return \IntlDateFormatter::create($locale, \IntlDateFormatter::NONE, $timeFormatter)?->format($dateTime->getTimestamp()) ?? '';
	}

	// ========== Twig Formatting Methods ==========

	/**
	 * Formats a DateTime for use in Twig templates with string format names.
	 * Accepts both string DateTime and DateTime objects.
	 * Converts Twig format strings ('none', 'short', 'medium', 'long', 'full') to IntlDateFormatter constants.
	 * @param string|\DateTime|null $dateTime The DateTime to format (string or object)
	 * @param string $dateFormatter Format name: 'none', 'short', 'medium', 'long', 'full' (default: 'short')
	 * @param string $timeFormatter Format name: 'none', 'short', 'medium', 'long', 'full' (default: 'short')
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string|null The formatted date/time string, or null if input is null
	 * @throws \Exception If string datetime cannot be parsed
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
	 * Formats only the date portion for use in Twig templates with string format name.
	 * Converts Twig format string ('none', 'short', 'medium', 'long', 'full') to IntlDateFormatter constant.
	 * @param \DateTime|null $dateTime The DateTime to format
	 * @param string $dateFormatter Format name: 'none', 'short', 'medium', 'long', 'full' (default: 'short')
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string|null The formatted date string, or null if input is null
	 */
	public static function formatDateFromTwig(?\DateTime $dateTime, string $dateFormatter='short', ?string $locale=null): ?string
	{
		if (null === $dateTime) {
			return null;
		}

		return self::format($dateTime, self::getDateTimeFormatterFromTwig($dateFormatter), \IntlDateFormatter::NONE, $locale);
	}

	/**
	 * Formats only the time portion for use in Twig templates with string format name.
	 * Converts Twig format string ('none', 'short', 'medium', 'long', 'full') to IntlDateFormatter constant.
	 * @param \DateTime|null $dateTime The DateTime to format
	 * @param string $timeFormatter Format name: 'none', 'short', 'medium', 'long', 'full' (default: 'short')
	 * @param string|null $locale Optional locale code (e.g., 'en_US', 'fr_FR'). Uses default if null
	 * @return string|null The formatted time string, or null if input is null
	 */
	public static function formatTimeFromTwig(?\DateTime $dateTime, string $timeFormatter='short', ?string $locale=null): ?string
	{
		if (null === $dateTime) {
			return null;
		}

		return self::format($dateTime, \IntlDateFormatter::NONE, self::getDateTimeFormatterFromTwig($timeFormatter), $locale);
	}

	// ========== UTC Conversion Methods ==========

	/**
	 * Converts a DateTime to UTC timezone and returns SQL DATE format.
	 * Creates a clone to avoid modifying the original DateTime.
	 * @param \DateTime|null $dateTime The DateTime to convert
	 * @return string|null SQL DATE format string (YYYY-MM-DD) in UTC, or null if input is null
	 */
	public static function getUTCSqlDate(?\DateTime $dateTime): ?string
	{
		return null !== $dateTime ? (clone $dateTime)->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d') : null;
	}

	/**
	 * Converts a DateTime to UTC timezone and returns SQL TIME format.
	 * Creates a clone to avoid modifying the original DateTime.
	 * @param \DateTime|null $dateTime The DateTime to convert
	 * @return string|null SQL TIME format string (HH:MM:SS) in UTC, or null if input is null
	 */
	public static function getUTCSqlTime(?\DateTime $dateTime): ?string
	{
		return null !== $dateTime ? (clone $dateTime)->setTimezone(new \DateTimeZone('UTC'))->format('H:i:s') : null;
	}

	/**
	 * Converts a DateTime to UTC timezone and returns SQL DATETIME format.
	 * Creates a clone to avoid modifying the original DateTime.
	 * @param \DateTime|null $dateTime The DateTime to convert
	 * @return string|null SQL DATETIME format string (YYYY-MM-DD HH:MM:SS) in UTC, or null if input is null
	 */
	public static function getUTCSqlDateTime(?\DateTime $dateTime): ?string
	{
		return null !== $dateTime ? (clone $dateTime)->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s') : null;
	}

	/**
	 * Converts Twig format string to IntlDateFormatter constant.
	 * Maps string format names to their corresponding IntlDateFormatter integer constants.
	 * @param string $formatter Format name: 'none', 'full', 'long', 'medium', 'short'
	 * @return int IntlDateFormatter constant
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

	// ========== Parsing Methods ==========

	/**
	 * Parses a date string in various formats and returns a DateTime object.
	 * Delegates to Date::parse() which supports multiple formats.
	 * @param string $str The date string to parse
	 * @return \DateTime|null A DateTime object if parsing succeeds, null otherwise
	 * @see Date::parse()
	 */
	public static function parse(string $str): ?\DateTime
	{
		return Date::parse($str);
	}

	/**
	 * Parses a SQL DATETIME format string and returns a DateTime object.
	 * Accepts format: "YYYY-MM-DD HH:MM:SS"
	 * @param string $sqlDateTime SQL DATETIME format string
	 * @return \DateTime|null A DateTime object if parsing succeeds, null on error
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
	 * Creates a DateTime object from a Unix timestamp.
	 * Converts the timestamp to SQL DATETIME format before parsing.
	 * @param int $timestamp Unix timestamp (seconds since January 1, 1970)
	 * @return \DateTime|null A DateTime object if creation succeeds, null on error
	 */
	public static function parseFromTimestamp(int $timestamp): ?\DateTime
	{
		//return new \DateTime('@'.$timestamp);
		return self::parseFromSqlDateTime(date('Y-m-d H:i:s', $timestamp));
	}

	/**
	 * Creates a DateTime object from year, month, and day components.
	 * Validates the date using checkdate() before creating the DateTime.
	 * Time is set to the current time.
	 * @param int $year The year (e.g., 2024)
	 * @param int $month The month (1-12)
	 * @param int $day The day of month (1-31)
	 * @return \DateTime|null A DateTime object if date is valid, null otherwise
	 */
	public static function parseFromYearMonthDay(int $year, int $month, int $day): ?\DateTime
	{
		// Validate date components before creating DateTime
		if (!checkdate($month, $day, $year)) {
			return null;
		}

		try {
			$d = new \DateTime();
			$d->setDate($year, $month, $day);
			return $d;
		} catch (\Exception) {}
		return null;
	}

	// ========== Comparison Methods ==========

	/**
	 * Checks if the first date is after the second date (ignoring time).
	 * Only compares the date portion (year, month, day), not the time.
	 * @param \DateTime $dateTime1 First datetime to compare
	 * @param \DateTime $dateTime2 Second datetime to compare
	 * @return bool True if dateTime1's date is after dateTime2's date
	 */
	public static function isDateAfter(\DateTime $dateTime1, \DateTime $dateTime2): bool
	{
		return $dateTime1->format('Ymd') > $dateTime2->format('Ymd');
	}

	/**
	 * Checks if the first date is before the second date (ignoring time).
	 * Only compares the date portion (year, month, day), not the time.
	 * @param \DateTime $dateTime1 First datetime to compare
	 * @param \DateTime $dateTime2 Second datetime to compare
	 * @return bool True if dateTime1's date is before dateTime2's date
	 */
	public static function isDateBefore(\DateTime $dateTime1, \DateTime $dateTime2): bool
	{
		return $dateTime1->format('Ymd') < $dateTime2->format('Ymd');
	}

	/**
	 * Checks if a DateTime is in the past (including both date and time).
	 * Compares full datetime including hours, minutes, and seconds.
	 * @param \DateTime $dateTime The DateTime to check
	 * @return bool True if the datetime is before now
	 */
	public static function isInThePast(\DateTime $dateTime): bool
	{
		return $dateTime < self::getCurrentDateTime();
	}

	/**
	 * Checks if a DateTime is in the future (including both date and time).
	 * Compares full datetime including hours, minutes, and seconds.
	 * @param \DateTime $dateTime The DateTime to check
	 * @return bool True if the datetime is after now
	 */
	public static function isInTheFuture(\DateTime $dateTime): bool
	{
		return $dateTime > self::getCurrentDateTime();
	}

	/**
	 * Checks if a date is in the past (ignoring time).
	 * Only compares the date portion (year, month, day).
	 * @param \DateTime $dateTime The DateTime to check
	 * @return bool True if the date (not time) is before today
	 */
	public static function isDateInThePast(\DateTime $dateTime): bool
	{
		return $dateTime->format('Ymd') < self::getCurrentDateTime()->format('Ymd');
	}

	/**
	 * Checks if a date is in the future (ignoring time).
	 * Only compares the date portion (year, month, day).
	 * @param \DateTime $dateTime The DateTime to check
	 * @return bool True if the date (not time) is after today
	 */
	public static function isDateInTheFuture(\DateTime $dateTime): bool
	{
		return $dateTime->format('Ymd') > self::getCurrentDateTime()->format('Ymd');
	}

	// ========== Day Methods ==========

	/**
	 * Checks if a date is a working day (Monday-Friday, excluding weekends).
	 * Working day: Monday through Friday (excludes both Saturday and Sunday).
	 * Optionally excludes public holidays.
	 * @param \DateTime $dateTime The date to check
	 * @param bool $withPublicHoliday If true, also excludes public holidays (default: true)
	 * @return bool True if the date is a working day
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
	 * Checks if a date is a business day (Monday-Saturday, excluding Sundays only).
	 * Business day: Monday through Saturday (excludes only Sunday).
	 * Optionally excludes public holidays.
	 * @param \DateTime $dateTime The date to check
	 * @param bool $withPublicHoliday If true, also excludes public holidays (default: true)
	 * @return bool True if the date is a business day
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
	 * Checks if a date is a weekend day (Saturday or Sunday).
	 * Uses ISO-8601 day numbering (6 = Saturday, 7 = Sunday).
	 * @param \DateTime $dateTime The date to check
	 * @return bool True if the date is Saturday or Sunday
	 */
	public static function isWeekend(\DateTime $dateTime): bool
	{
		$dayOfWeek = (int) $dateTime->format('N');
		return ($dayOfWeek === 6 || $dayOfWeek === 7);
	}

	/**
	 * Moves a DateTime backward by a specified number of days.
	 * Creates a new DateTime object to avoid modifying the original.
	 * @param \DateTime $dateTime The datetime to move
	 * @param int $nbDays Number of days to move backward
	 * @return \DateTime A new DateTime moved backward by the specified days
	 */
	public static function moveBackOfNbDays(\DateTime $dateTime, int $nbDays): \DateTime
	{
		try {
			$dateTime = new \DateTime($dateTime->format('Y-m-d H:i:s'));
		} catch (\Exception) {}
		return $dateTime->modify('-'.$nbDays.' day');
	}

	/**
	 * Moves a DateTime forward by a specified number of days.
	 * Creates a new DateTime object to avoid modifying the original.
	 * @param \DateTime $dateTime The datetime to move
	 * @param int $nbDays Number of days to move forward
	 * @return \DateTime A new DateTime moved forward by the specified days
	 */
	public static function moveForwardOfNbDays(\DateTime $dateTime, int $nbDays): \DateTime
	{
		try {
			$dateTime = new \DateTime($dateTime->format('Y-m-d H:i:s'));
		} catch (\Exception) {}
		return $dateTime->modify('+'.$nbDays.' day');
	}

	// ========== Week Methods ==========

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