<?php

namespace Osimatic\Calendar;

/**
 * Islamic Calendar (Hijri Calendar) conversion and utility class.
 * The Islamic calendar is a lunar calendar consisting of 12 lunar months in a year of 354 or 355 days.
 * It is used to determine the proper days of Islamic holidays and rituals.
 * @link https://en.wikipedia.org/wiki/Islamic_calendar
 * @link https://en.wikipedia.org/wiki/Tabular_Islamic_calendar
 * @link https://www.islamicfinder.org/islamic-date-converter/
 * @link https://github.com/khaled-alshamaa/ar-php
 * @link https://www.ar-php.org/ar-example-Mktime-php-arabic.html
 * @link https://github.com/hubaishan/HijriDateLib
 */
class IslamicCalendar
{
	/**
	 * Islamic month names in Arabic
	 */
	public const array ISLAMIC_MONTHS_AR = ["محرم", "صفر", "ربيع الأول", "ربيع الآخر", "جمادى الأولى", "جمادى الآخرة", "رجب", "شعبان", "رمضان", "شوال", "ذو القعدة", "ذو الحجة"];

	/**
	 * Islamic month names in English
	 */
	public const array ISLAMIC_MONTHS_EN = ["Muharram", "Safar", "Rabi' al-awwal", "Rabi' al-thani", "Jumada al-awwal", "Jumada al-thani", "Rajab", "Sha'ban", "Ramadan", "Shawwal", "Dhu al-Qi'dah", "Dhu al-Hijjah"];

	/**
	 * Islamic weekday names in Arabic
	 */
	public const array ISLAMIC_WEEKDAYS_AR = ["الأحد", "الاثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت"];

	/**
	 * Islamic weekday names in English
	 */
	public const array ISLAMIC_WEEKDAYS_EN = ["Al-Ahad", "Al-Ithnayn", "Al-Thulatha", "Al-Arbi'a", "Al-Khamis", "Al-Jumu'ah", "As-Sabt"];

	/* ========== Timestamp Methods ========== */

	/**
	 * Get Unix timestamp for a given Hijri date and time.
	 * @param int $hijriYear  Hijri year (Islamic calendar)
	 * @param int $hijriMonth Hijri month (1-12)
	 * @param int $hijriDay   Hijri day (1-30)
	 * @param int $hour       Hour (0-23)
	 * @param int $minute     Minute (0-59)
	 * @param int $second     Second (0-59)
	 * @return int Unix timestamp (number of seconds since January 1 1970 00:00:00 GMT)
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	public static function getTimestamp(int $hijriYear, int $hijriMonth, int $hijriDay, int $hour = 0, int $minute = 0, int $second = 0): int
	{
		return self::mktime($hour, $minute, $second, $hijriMonth, $hijriDay, $hijriYear, self::getMktimeCorrection($hijriMonth, $hijriYear));
	}

	/* ========== Conversion Methods ========== */

	/**
	 * Convert Hijri date (Islamic calendar) to Gregorian date.
	 * @param int $year  Hijri year (Islamic calendar)
	 * @param int $month Hijri month (1-12)
	 * @param int $day   Hijri day (1-30)
	 * @return int[] Gregorian date array: [year, month, day]
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	public static function convertIslamicDateToGregorianDate(int $year, int $month, int $day): array
	{
		$str = jdtogregorian(self::convertIslamicDateToJd($year, $month, $day));

		[$monthStr, $dayStr, $yearStr] = explode('/', $str);

		return [(int)$yearStr, (int)$monthStr, (int)$dayStr];
	}

	/**
	 * Convert Gregorian date to Hijri date (Islamic calendar).
	 * @param int $year  Gregorian year
	 * @param int $month Gregorian month (1-12)
	 * @param int $day   Gregorian day (1-31)
	 * @return int[] Hijri date array: [year, month, day]
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	public static function convertGregorianDateToIslamicDate(int $year, int $month, int $day): array
	{
		$jd = gregoriantojd($month, $day, $year);

		[$hijriYear, $hijriMonth, $hijriDay] = self::convertJdToIslamicDate($jd);

		return [$hijriYear, $hijriMonth, $hijriDay];
	}

	/**
	 * Convert Unix timestamp to Hijri date (Islamic calendar).
	 * @param int $timestamp Unix timestamp
	 * @return int[] Hijri date array: [year, month, day]
	 */
	public static function convertTimestampToIslamicDate(int $timestamp): array
	{
		return self::convertGregorianDateToIslamicDate((int)date('Y', $timestamp), (int)date('m', $timestamp), (int)date('d', $timestamp));
	}

	/* ========== Validation Methods ========== */

	/**
	 * Check if a given Hijri date is valid.
	 * @param int $year  Hijri year
	 * @param int $month Hijri month (1-12)
	 * @param int $day   Hijri day (1-30)
	 * @return bool True if the date is valid, false otherwise
	 */
	public static function isValidDate(int $year, int $month, int $day): bool
	{
		if ($year < 1) {
			return false;
		}

		if ($month < 1 || $month > 12) {
			return false;
		}

		// In the Islamic calendar, odd months have 30 days and even months have 29 days
		// except for the 12th month which can have 29 or 30 days depending on the year
		$maxDays = ($month % 2 === 1 || ($month === 12 && self::isLeapYear($year))) ? 30 : 29;

		if ($day < 1 || $day > $maxDays) {
			return false;
		}

		return true;
	}

	/**
	 * Check if a given Hijri year is a leap year.
	 * In the Islamic calendar, a leap year has 355 days (12th month has 30 days instead of 29).
	 * In a 30-year cycle, years 2, 5, 7, 10, 13, 16, 18, 21, 24, 26, and 29 are leap years.
	 * @param int $year Hijri year
	 * @return bool True if the year is a leap year, false otherwise
	 */
	public static function isLeapYear(int $year): bool
	{
		// 30-year cycle: leap years are at positions 2, 5, 7, 10, 13, 16, 18, 21, 24, 26, 29
		$leapYearsInCycle = [2, 5, 7, 10, 13, 16, 18, 21, 24, 26, 29];
		$yearInCycle = (($year - 1) % 30) + 1;

		return in_array($yearInCycle, $leapYearsInCycle, true);
	}

	/* ========== Utility Methods ========== */

	/**
	 * Get the name of a Hijri month in the specified language.
	 * @param int    $month    Month number (1-12)
	 * @param string $language Language code ('ar' for Arabic, 'en' for English)
	 * @return string|null Month name, or null if invalid month
	 */
	public static function getMonthName(int $month, string $language = 'en'): ?string
	{
		if ($month < 1 || $month > 12) {
			return null;
		}

		$months = $language === 'ar' ? self::ISLAMIC_MONTHS_AR : self::ISLAMIC_MONTHS_EN;
		return $months[$month - 1];

		/*$IntlDateFormatter = new \IntlDateFormatter(
			'en_US@calendar=islamic-civil',
			\IntlDateFormatter::FULL,
			\IntlDateFormatter::FULL,
			date_default_timezone_get(), // 'Asia/Tehran'
			\IntlDateFormatter::TRADITIONAL,
			'MMMM'
		);
		return $IntlDateFormatter->format($dateTime);*/
	}

	/**
	 * Get the name of a weekday in the specified language.
	 * @param int    $weekday  Weekday number (0=Sunday, 1=Monday, ..., 6=Saturday)
	 * @param string $language Language code ('ar' for Arabic, 'en' for English)
	 * @return string|null Weekday name, or null if invalid weekday
	 */
	public static function getWeekdayName(int $weekday, string $language = 'en'): ?string
	{
		if ($weekday < 0 || $weekday > 6) {
			return null;
		}

		$weekdays = $language === 'ar' ? self::ISLAMIC_WEEKDAYS_AR : self::ISLAMIC_WEEKDAYS_EN;
		return $weekdays[$weekday];
	}

	/**
	 * Calculate how many days in a given Hijri month.
	 * @param int  $year      Hijri year (Islamic calendar)
	 * @param int  $month     Hijri month (1-12)
	 * @param bool $umAlqoura Should we implement Um-Al-Qura calendar correction (default: false). Note: Um-Al-Qura is only valid for years 1320-1459 (approximately 1902-2038 CE)
	 * @return int|null Days in a given Hijri month, or null if invalid month. Returns 0 if year is out of Um-Al-Qura range when $umAlqoura is true.
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	public static function getNbDaysOfMonth(int $year, int $month, bool $umAlqoura = false): ?int
	{
		if ($month < 1 || $month > 12) {
			return null;
		}

		// Standard tabular Islamic calendar (no Um-Al-Qura correction)
		if ($umAlqoura === false) {
			// Odd months have 30 days, even months have 29 days
			// Exception: 12th month has 30 days in leap years
			if ($month % 2 === 1) {
				return 30;
			}

			if ($month === 12 && self::isLeapYear($year)) {
				return 30;
			}

			return 29;
		}

		// Um-Al-Qura calendar correction (limited to years 1320-1459)
		if ($year < 1320 || $year >= 1460) {
			return 0;
		}

		$begin = self::mktime(0, 0, 0, $month, 1, $year);

		if ($month === 12) {
			$month2 = 1;
			$year2 = $year + 1;
		} else {
			$month2 = $month + 1;
			$year2 = $year;
		}

		$end = self::mktime(0, 0, 0, $month2, 1, $year2);

		$days = (int)(($end - $begin) / (3600 * 24));

		$c1 = self::getMktimeCorrection($month, $year);
		$c2 = self::getMktimeCorrection($month2, $year2);

		return $days - $c1 + $c2;
	}

	/**
	 * Format a Hijri date as a string.
	 * @param int    $year     Hijri year
	 * @param int    $month    Hijri month (1-12)
	 * @param int    $day      Hijri day (1-30)
	 * @param string $format   Format string: 'full' (e.g., "Al-Jumu'ah 15 Ramadan 1445"), 'long' (e.g., "15 Ramadan 1445"), 'medium' (e.g., "15 Ram 1445"), 'short' (e.g., "15/9/1445"), 'iso' (e.g., "1445-09-15")
	 * @param string $language Language code ('ar' for Arabic, 'en' for English, default: 'en')
	 * @return string|null Formatted date string, or null if invalid date or format
	 */
	public static function format(int $year, int $month, int $day, string $format = 'long', string $language = 'en'): ?string
	{
		if (!self::isValidDate($year, $month, $day)) {
			return null;
		}

		// Get weekday name for full format
		if ($format === 'full') {
			[$gregYear, $gregMonth, $gregDay] = self::convertIslamicDateToGregorianDate($year, $month, $day);
			$weekday = (int)date('w', mktime(0, 0, 0, $gregMonth, $gregDay, $gregYear));
			$weekdayName = self::getWeekdayName($weekday, $language);
			$monthName = self::getMonthName($month, $language);
			return sprintf('%s %d %s %d', $weekdayName, $day, $monthName, $year);
		}

		return match($format) {
			'long' => sprintf('%d %s %d', $day, self::getMonthName($month, $language), $year),
			'medium' => sprintf('%d %s %d', $day, substr(self::getMonthName($month, $language), 0, 3), $year),
			'short' => sprintf('%d/%d/%d', $day, $month, $year),
			'iso' => sprintf('%04d-%02d-%02d', $year, $month, $day),
			default => null,
		};
	}

	/* ========== Private Methods ========== */

	/**
	 * @var string|null
	 */
	private static ?string $umAlqoura = null;

	/**
	 * Convert Hijri date to Julian Day number.
	 * The Julian Day Count is a continuous count of days starting from January 1, 4713 BC (proleptic Julian calendar).
	 * @param int $y Hijri year
	 * @param int $m Hijri month (1-12)
	 * @param int $d Hijri day (1-30)
	 * @return int Julian Day number
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	private static function convertIslamicDateToJd(int $y, int $m, int $d): int
	{
		return (int)((11 * $y + 3) / 30) + (int)(354 * $y) + (int)(30 * $m) - (int)(($m - 1) / 2) + $d + 1948440 - 385;
	}

	/**
	 * Convert Julian Day number to Hijri date.
	 * The Julian Day Count is a continuous count of days starting from January 1, 4713 BC (proleptic Julian calendar).
	 * @param int $jd Julian Day number
	 * @return int[] Hijri date array: [year, month, day]
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	private static function convertJdToIslamicDate(int $jd): array
	{
		$l = $jd - 1948440 + 10632;
		$n = (int)(($l - 1) / 10631);

		$l = $l - 10631 * $n + 354;
		$j = (int)((10985 - $l) / 5316) * (int)((50 * $l) / 17719) + (int)($l / 5670) * (int)((43 * $l) / 15238);

		$l = $l - (int)((30 - $j) / 15) * (int)((17719 * $j) / 50) - (int)($j / 16) * (int)((15238 * $j) / 43) + 29;
		$m = (int)((24 * $l) / 709);
		$d = $l - (int)((709 * $m) / 24);
		$y = (int)(30 * $n + $j - 30);

		return [$y, $m, $d];
	}

	/**
	 * Get Um-Al-Qura calendar data from file.
	 * @return string Um-Al-Qura calendar data
	 * @throws \RuntimeException If the data file cannot be read
	 */
	private static function getUmAlqouraData(): string
	{
		if (null === self::$umAlqoura) {
			$filePath = __DIR__ . '/conf/um_alqoura.txt';
			if (!file_exists($filePath) || !is_readable($filePath)) {
				throw new \RuntimeException('Um-Al-Qura data file not found or not readable: ' . $filePath);
			}
			if (false === ($data = file_get_contents($filePath))) {
				throw new \RuntimeException('Failed to read Um-Al-Qura data file: ' . $filePath);
			}
			self::$umAlqoura = $data;
		}
		return self::$umAlqoura;
	}

	/**
	 * Get Unix timestamp for a given Hijri date with optional correction.
	 * @param int $hour       Hour (0-23)
	 * @param int $minute     Minute (0-59)
	 * @param int $second     Second (0-59)
	 * @param int $hj_month   Hijri month (1-12)
	 * @param int $hj_day     Hijri day (1-30)
	 * @param int $hj_year    Hijri year
	 * @param int $correction Correction factor (+/- 1-2 days) to apply to standard Hijri calendar
	 * @return int Unix timestamp (number of seconds since January 1 1970 00:00:00 GMT)
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	private static function mktime(int $hour, int $minute, int $second, int $hj_month, int $hj_day, int $hj_year, int $correction = 0): int
	{
		[$year, $month, $day] = self::convertIslamicDateToGregorianDate($hj_year, $hj_month, $hj_day);

		$unixTimestamp = mktime($hour, $minute, $second, $month, $day, $year);
		$unixTimestamp = $unixTimestamp + 3600 * 24 * $correction;

		return $unixTimestamp;
	}

	/**
	 * Calculate Hijri calendar correction using Um-Al-Qura calendar information.
	 * Note: Valid only for years 1420-1459 (approximately 1999-2038 CE).
	 * @param int $m Hijri month (1-12)
	 * @param int $y Hijri year (valid range: 1420-1459)
	 * @return int Correction factor (number of days) to fix Hijri calendar calculation
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	private static function getMktimeCorrection(int $m, int $y): int
	{
		if ($y < 1420 || $y >= 1460) {
			return 0;
		}

		$calc = self::mktime(0, 0, 0, $m, 1, $y);
		$offset = (($y - 1420) * 12 + $m) * 11;

		try {
			$data = self::getUmAlqouraData();
			$d = (int)substr($data, $offset, 2);
			$m = (int)substr($data, $offset + 3, 2);
			$y = (int)substr($data, $offset + 6, 4);
		} catch (\RuntimeException) {
			return 0;
		}

		$real = mktime(0, 0, 0, $m, $d, $y);
		$diff = (int)(($real - $calc) / (3600 * 24));

		return $diff;
	}
}