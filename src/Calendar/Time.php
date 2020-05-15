<?php

namespace Osimatic\Helpers\Calendar;

class Time
{
	// ========== Affichage ==========

	/**
	 * @param int $hour
	 * @return string
	 */
	public static function formatHour(int $hour): string
	{
		return sprintf('%02d', $hour).'h';
	}

	// ========== Vérification ==========

	/**
	 * @param int $hour
	 * @param int $minute
	 * @param int $second
	 * @return bool
	 */
	public static function check(int $hour, int $minute, int $second=0): bool
	{
		return ($hour >= 0 && $hour < 24 && $minute >= 0 && $minute < 60 && $second >= 0 && $second < 60);
	}

	/**
	 * @param mixed $enteredTime
	 * @param string $separator
	 * @param int $hourPos
	 * @param int $minutePos
	 * @param int $secondPos
	 * @return bool
	 */
	public static function checkValue($enteredTime, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): bool
	{
		if (null === ($timeArray = self::_parse($enteredTime, $separator, $hourPos, $minutePos, $secondPos))) {
			return false;
		}

		return self::check($timeArray[0], $timeArray[1], $timeArray[2]);
	}

	// ========== Parse ==========

	/**
	 * @param mixed $enteredTime
	 * @param string $separator
	 * @param int $hourPos
	 * @param int $minutePos
	 * @param int $secondPos
	 * @return string|null
	 */
	public static function parseToSqlTime($enteredTime, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): ?string
	{
		if (null === ($timeArray = self::_parse($enteredTime, $separator, $hourPos, $minutePos, $secondPos))) {
			return null;
		}

		return date('H:i:s', mktime($timeArray[0], $timeArray[1], $timeArray[2]));
	}

	/**
	 * @param mixed $enteredTime
	 * @param string $separator
	 * @param int $hourPos
	 * @param int $minutePos
	 * @param int $secondPos
	 * @return array|null
	 */
	public static function _parse($enteredTime, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): ?array
	{
		$timeArray = explode($separator, $enteredTime);
		--$hourPos;
		--$minutePos;
		--$secondPos;

		if (!isset($timeArray[$hourPos]) || !is_numeric($timeArray[$hourPos])) {
			return null;
		}
		if (!isset($timeArray[$minutePos]) || !is_numeric($timeArray[$minutePos])) {
			return null;
		}
		if (isset($timeArray[$secondPos]) && !is_numeric($timeArray[$secondPos])) {
			return null;
		}

		return [
			(int) $timeArray[$hourPos],
			(int) $timeArray[$minutePos],
			(int) ($timeArray[$secondPos] ?? 0),
		];
	}


}