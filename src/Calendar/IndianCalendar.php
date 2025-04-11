<?php

namespace Osimatic\Calendar;

/**
 * @link https://www.fourmilab.ch/documents/calendar/calendar.js
 * @link https://stackoverflow.com/questions/24060864/converting-a-date-from-a-calendar-type-to-another-type
 * @link https://en.wikipedia.org/wiki/Hindu_calendar
 * @link https://stackoverflow.com/questions/8645956/converting-to-and-from-hindu-calendar
 * @link https://www.decordier-immobilier.mu/fr/actualites/details/1537/jours-feries-2025-ile-maurice/
 * @link https://www.cg972.fr/guide-ile-maurice/informations-pratiques/fetes-jours-feries
 */
class IndianCalendar
{
	public const array INDIAN_CIVIL_WEEKDAYS = ["ravivara", "somavara", "mangalavara", "budhavara", "brahaspativara", "sukravara", "sanivara"];

	/**
	 * This will return current Unix timestamp for given Indian date
	 * @param int $indianYear  Indian year
	 * @param int $indianMonth Indian month
	 * @param int $indianDay   Indian day
	 * @param int $hour       Time hour
	 * @param int $minute     Time minute
	 * @param int $second     Time second
	 * @return int Returns the current time measured in the number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
	 */
	public static function getTimestamp(int $indianYear, int $indianMonth, int $indianDay, int $hour = 0, int $minute = 0, int $second = 0): int
	{
		[$year, $month, $day] = self::convertIndianDateToGregorianDate($indianYear, $indianMonth, $indianDay);

		return mktime($hour, $minute, $second, $month, $day, $year);
	}

	// ========== Conversion ==========

	/**
	 * Obtain Gregorian date for Indian Civil date
	 * @param int $year Indian year
	 * @param int $month Indian month
	 * @param int $day Indian day
	 * @return int[] Gregorian date [int Year, int Month, int Day]
	 */
	public static function convertIndianDateToGregorianDate(int $year, int $month, int $day): array
	{
		$str = jdtogregorian(self::convertIndianDateToJd($year, $month, $day));

		[$month, $day, $year] = explode('/', $str);

		return [$year, $month, $day];
	}

	/**
	 * Convert given Gregorian date into Indian Civil date
	 * @param int $year Year Gregorian year
	 * @param int $month Month Gregorian month
	 * @param int $day Day Gregorian day
	 * @return int[] Indian date [int Year, int Month, int Day]
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	public static function convertGregorianDateToIndianDate(int $year, int $month, int $day): array
	{
		$jd = gregoriantojd($month, $day, $year);

		[$year, $month, $day] = self::convertJdToIndianDate($jd);

		return [$year, $month, $day];
	}

	/**
	 * Convert given timestamp into Indian date
	 * @param int $timestamp
	 * @return array Indian date [int Year, int Month, int Day]
	 */
	public static function convertTimestampToIndianDate(int $timestamp): array
	{
		return self::convertGregorianDateToIndianDate(date('Y', $timestamp), date('m', $timestamp), date('d', $timestamp));
	}

	// ========== private ==========

	/**
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 * @return int
	 */
	private static function convertIndianDateToJd(int $year, int $month, int $day): int
	{
		$gregorianYear = $year + 78;
		$isLeapYear = Date::isLeapYear($gregorianYear);
		$start = cal_to_jd(CAL_GREGORIAN, 3, $isLeapYear ? 21 : 22, $gregorianYear);
		$caitra = $isLeapYear ? 31 : 30;

		if ($month === 1) {
			return $start + ($day - 1);
		}

		$jd = $start + $caitra;
		$m = $month - 2;
		$m = min($m, 5);
		$jd += $m * 31;
		if ($month >= 8) {
			$m = $month - 7;
			$jd += $m * 30;
		}
		$jd += $day - 1;

		return $jd;
	}

	//  JD_TO_INDIAN_CIVIL  --

	/**
	 * @param int $jd
	 * @return int[]
	 */
	public static function convertJdToIndianDate(int $jd): array
	{
		$saka = 79 - 1;                    // Offset in years from Saka era to Gregorian epoch
		$start = 80;                       // Day offset between Saka and Gregorian

		$jd = floor($jd) + 0.5;
		$greg = cal_from_jd($jd, CAL_GREGORIAN);       // Gregorian date for Julian day
		$gregorianYear = $greg['year'];
		$isLeapYear = Date::isLeapYear($gregorianYear);   // Is this a leap year?
		$year = $gregorianYear - $saka;            // Tentative year in Saka era
		$greg0 = cal_to_jd(CAL_GREGORIAN, 1, 1, $gregorianYear); // JD at start of Gregorian year
		$yday = $jd - $greg0;                // Day number (0 based) in Gregorian year
		$caitra = $isLeapYear ? 31 : 30;          // Days in Caitra this year

		if ($yday < $start) {
			//  Day is at the end of the preceding Saka year
			$year--;
			$yday += $caitra + (31 * 5) + (30 * 3) + 10 + $start;
		}

		$yday -= $start;
		if ($yday < $caitra) {
			$month = 1;
			$day = $yday + 1;
		} else {
			$mday = $yday - $caitra;
			if ($mday < (31 * 5)) {
				$month = floor($mday / 31) + 2;
				$day = ($mday % 31) + 1;
			} else {
				$mday -= 31 * 5;
				$month = floor($mday / 30) + 7;
				$day = ($mday % 30) + 1;
			}
		}

		return [$year, $month, $day];
	}
}