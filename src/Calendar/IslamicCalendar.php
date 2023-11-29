<?php

namespace Osimatic\Helpers\Calendar;

/**
 * Class IslamicCalendar
 * @package Osimatic\Helpers\Calendar
 * @link https://www.islamicfinder.org/islamic-date-converter/?language=fr
 * @link https://github.com/khaled-alshamaa/ar-php
 * @link https://www.ar-php.org/ar-example-Mktime-php-arabic.html
 * @link https://github.com/hubaishan/HijriDateLib
 * @link https://fr.wikipedia.org/wiki/Calendrier_h%C3%A9girien
 * @link https://www.phpclasses.org/browse/file/66919.html
 */
class IslamicCalendar
{
	/**
	 * This will return current Unix timestamp for given Hijri date (Islamic calendar)
	 * @param integer $hijriYear  Hijri year  (Islamic calendar)
	 * @param integer $hijriMonth Hijri month (Islamic calendar)
	 * @param integer $hijriDay   Hijri day   (Islamic calendar)
	 * @param integer $hour       Time hour
	 * @param integer $minute     Time minute
	 * @param integer $second     Time second
	 * @return integer Returns the current time measured in the number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	public static function getTimestamp(int $hijriYear, int $hijriMonth, int $hijriDay, int $hour = 0, int $minute = 0, int $second = 0): int
	{
		return self::mktime($hour, $minute, $second, $hijriMonth, $hijriDay, $hijriYear, self::getMktimeCorrection($hijriMonth, $hijriYear));
	}

	// ========== Conversion ==========

	/**
	 * This will convert given Hijri date (Islamic calendar) into Gregorian date
	 * @param integer $year Hijri year (Islamic calendar)
	 * @param integer $month Hijri month (Islamic calendar)
	 * @param integer $day Hijri day (Islamic calendar)
	 * @return array Gregorian date [int Year, int Month, int Day]
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	public static function convertIslamicDateToGregorianDate(int $year, int $month, int $day): array
	{
		$str = jdtogregorian(self::convertIslamicDateToJd($year, $month, $day));

		[$month, $day, $year] = explode('/', $str);

		return [$year, $month, $day];
	}

	/**
	 * Convert given Gregorian date into Hijri date
	 * @param integer $year Year Gregorian year
	 * @param integer $month Month Gregorian month
	 * @param integer $day Day Gregorian day
	 * @return array Hijri date [int Year, int Month, int Day](Islamic calendar)
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	public static function convertGregorianDateToIslamicDate(int $year, int $month, int $day): array
	{
		$jd = gregoriantojd($month, $day, $year);

		[$year, $month, $day] = self::convertJdToIslamicDate($jd);

		return [$year, $month, $day];
	}

	/**
	 * Convert given timestamp into Hijri date
	 * @param integer $timestamp
	 * @return array Hijri date [int Year, int Month, int Day](Islamic calendar)
	 */
	public static function convertTimestampToIslamicDate(int $timestamp): array
	{
		return self::convertGregorianDateToIslamicDate(date('Y', $timestamp), date('m', $timestamp), date('d', $timestamp));
	}

	// ========== Mois ==========

	/**
	 * @param int $month
	 * @return string
	 */
	public static function getMonthName(int $month): string
	{
		try {
			$dateTime = new \DateTime('@'.self::getTimestamp(1400, $month, 1));
		}
		catch(\Exception $e) {
			return '';
		}

		$IntlDateFormatter = new \IntlDateFormatter(
			'en_US@calendar=islamic-civil',
			\IntlDateFormatter::FULL,
			\IntlDateFormatter::FULL,
			date_default_timezone_get(), // 'Asia/Tehran'
			\IntlDateFormatter::TRADITIONAL,
			'MMMM'
		);
		return $IntlDateFormatter->format($dateTime);
	}

	/**
	 * Calculate how many days in a given Hijri month
	 * @param integer $year      Hijri year  (Islamic calendar), valid range[1320-1459]
	 * @param integer $month     Hijri month (Islamic calendar)
	 * @param boolean $umAlqoura Should we implement Um-Al-Qura calendar correction in this calculation (default value is true)
	 * @return integer Days in a given Hijri month
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	public static function getNbDaysOfMonth(int $year, int $month, bool $umAlqoura = true): int
	{
		if ($year < 1320 || $year >= 1460) {
			return 0;
		}

		$begin = self::mktime(0, 0, 0, $month, 1, $year);

		if ($month === 12) {
			$month2 = 1;
			$year2 = $year + 1;
		}
		else {
			$month2 = $month + 1;
			$year2 = $year;
		}

		$end = self::mktime(0, 0, 0, $month2, 1, $year2);

		$days = ($end - $begin) / (3600 * 24);

		if ($umAlqoura === false) {
			return $days;
		}

		$c1 = self::getMktimeCorrection($month, $year);
		$c2 = self::getMktimeCorrection($month2, $year2);

		return $days - $c1 + $c2;
	}



	// ========== private ==========

	/**
	 * @var string|null
	 */
	private static ?string $umAlqoura = null;

	/**
	 * Convert given Hijri date into Julian day
	 * @param integer $y Year Hijri year
	 * @param integer $m Month Hijri month
	 * @param integer $d Day Hijri day
	 * @return integer Julian day
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	private static function convertIslamicDateToJd(int $y, int $m, int $d): int
	{
		return (int)((11 * $y + 3) / 30) + (int)(354 * $y) + (int)(30 * $m) - (int)(($m - 1) / 2) + $d + 1948440 - 385;
	}

	/**
	 * Convert given Julian day into Hijri date
	 * @param integer $jd Julian day
	 * @return array Hijri date [int Year, int Month, int Day](Islamic calendar)
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
	 * @return string
	 */
	private static function getUmAlqouraData(): string
	{
		if (null === self::$umAlqoura) {
			self::$umAlqoura = file_get_contents(__DIR__.'/conf/um_alqoura.txt');
		}
		return self::$umAlqoura;
	}

	/**
	 * This will return current Unix timestamp for given Hijri date (Islamic calendar)
	 * @param integer $hour       Time hour
	 * @param integer $minute     Time minute
	 * @param integer $second     Time second
	 * @param integer $hj_month   Hijri month (Islamic calendar)
	 * @param integer $hj_day     Hijri day   (Islamic calendar)
	 * @param integer $hj_year    Hijri year  (Islamic calendar)
	 * @param integer $correction To apply correction factor (+/- 1-2) to standard Hijri calendar
	 * @return integer Returns the current time measured in the number of seconds since the Unix Epoch (January 1 1970 00:00:00 GMT)
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
	 * Calculate Hijri calendar correction using Um-Al-Qura calendar information
	 * @param integer $m Hijri month (Islamic calendar)
	 * @param integer $y Hijri year  (Islamic calendar), valid range [1420-1459]
	 * @return integer Correction factor to fix Hijri calendar calculation using Um-Al-Qura calendar information
	 * @author Khaled Al-Sham'aa <khaled@ar-php.org>
	 */
	private static function getMktimeCorrection(int $m, int $y): int
	{
		if ($y < 1420 || $y >= 1460) {
			return 0;
		}

		$calc   = self::mktime(0, 0, 0, $m, 1, $y);
		$offset = (($y - 1420) * 12 + $m) * 11;

		$d = (int) substr(self::getUmAlqouraData(), $offset, 2);
		$m = (int) substr(self::getUmAlqouraData(), $offset + 3, 2);
		$y = (int) substr(self::getUmAlqouraData(), $offset + 6, 4);

		$real = mktime(0, 0, 0, $m, $d, $y);
		$diff = (int)(($real - $calc) / (3600 * 24));

		return $diff;
	}

}