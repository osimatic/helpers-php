<?php

namespace Osimatic\Helpers\Calendar;

class SqlTime
{
	/**
	 * @param $time
	 * @return string|null
	 */
	public static function parse($time): ?string
	{
		if (is_array($time) && !empty($time['date'])) {
			$time = substr($time['date'], 11, 8);
		}

		// si time sans secondes
		if (strlen($time) === 5) {
			$time .= ':00';
		}

		return $time;
	}

	/**
	 * @param string|null $time
	 * @return bool
	 */
	public static function check(?string $time): bool
	{
		$timeArr = explode(':', $time);
		$hour = ($timeArr[0]??-1);
		$minute = ($timeArr[1]??-1);

		return ($hour >= 0 && $hour < 24 && $minute >= 0 && $minute < 60);
	}


	// ========== Extraction ==========

	/**
	 * Retourne l'heure, à partir d'une heure au format SQL.
	 * @param string $sqlTime
	 * @return int l'heure
	 * TODO : extraire via substr pour une meilleure performance
	 */
	public static function getHour(string $sqlTime): int
	{
		return (int) date('H', strtotime('1970-01-01 '.$sqlTime));
	}

	/**
	 * Retourne la minute, à partir d'une heure au format SQL.
	 * @param string $sqlTime
	 * @return int la minute
	 */
	public static function getMinute(string $sqlTime): int
	{
		return (int) date('i', strtotime('1970-01-01 '.$sqlTime));
	}

	/**
	 * Retourne la seconde, à partir d'une heure au format SQL.
	 * @param string $sqlTime
	 * @return int la seconde
	 */
	public static function getSecond(string $sqlTime): int
	{
		return (int) date('s', strtotime('1970-01-01 '.$sqlTime));
	}


	// ========== Fabrication ==========

	/**
	 * @param int $hour
	 * @param int $minute
	 * @param int $second
	 * @return string
	 */
	public static function get(int $hour, int $minute, int $second=0): string
	{
		return sprintf('%02d', ((int) $hour)).':'.sprintf('%02d', ((int) $minute)).':'.sprintf('%02d', ((int) $second));
	}


	// ========== Comparaison ==========

	/**
	 * @param string $sqlTime1
	 * @param string $sqlTime2
	 * @return int
	 */
	public static function getNbSecondsFromTime(string $sqlTime1, string $sqlTime2): int
	{
		return (int) (strtotime(date('Y-m-d').' '.$sqlTime1) - strtotime(date('Y-m-d').' '.$sqlTime2));
	}

	/**
	 * @param string $sqlTime
	 * @return int
	 */
	public static function getNbSecondsFromNow(string $sqlTime): int
	{
		return (int) (strtotime(date('Y-m-d').' '.$sqlTime) - time());
	}

	/**
	 * @param string $sqlTime1
	 * @param string $sqlTime2
	 * @return bool
	 */
	public static function isBeforeTime(string $sqlTime1, string $sqlTime2): bool
	{
		return self::getNbSecondsFromTime($sqlTime1, $sqlTime2) < 0;
	}

	/**
	 * @param string $sqlTime1
	 * @param string $sqlTime2
	 * @return bool
	 */
	public static function isAfterTime(string $sqlTime1, string $sqlTime2): bool
	{
		return self::getNbSecondsFromTime($sqlTime1, $sqlTime2) > 0;
	}

	/**
	 * @param string $sqlTime
	 * @return bool
	 */
	public static function isBeforeNow(string $sqlTime): bool
	{
		return self::getNbSecondsFromNow($sqlTime) < 0;
	}

	/**
	 * @param string $sqlTime
	 * @return bool
	 */
	public static function isAfterNow(string $sqlTime): bool
	{
		return self::getNbSecondsFromNow($sqlTime) > 0;
	}

}