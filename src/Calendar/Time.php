<?php

namespace Osimatic\Calendar;

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
	 * @param string|null $enteredTime
	 * @param string $separator
	 * @param int $hourPos
	 * @param int $minutePos
	 * @param int $secondPos
	 * @return bool
	 */
	public static function checkValue(?string $enteredTime, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): bool
	{
		return null !== self::_parse($enteredTime, $separator, $hourPos, $minutePos, $secondPos);
	}

	// ========== Parse ==========

	/**
	 * @param string|null $enteredTime
	 * @param string $separator
	 * @param int $hourPos
	 * @param int $minutePos
	 * @param int $secondPos
	 * @return string|null
	 */
	public static function parseToSqlTime(?string $enteredTime, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): ?string
	{
		if (null === ($timeArray = self::_parse($enteredTime, $separator, $hourPos, $minutePos, $secondPos))) {
			return null;
		}

		return date('H:i:s', mktime($timeArray[0], $timeArray[1], $timeArray[2]));
	}

	/**
	 * @param string|null $enteredTime
	 * @param string $separator
	 * @param int $hourPos
	 * @param int $minutePos
	 * @param int $secondPos
	 * @return \DateTime|null
	 */
	public static function parse(?string $enteredTime, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): ?\DateTime
	{
		if (null === ($sqlTime = self::parseToSqlTime($enteredTime, $separator, $hourPos, $minutePos, $secondPos))) {
			return null;
		}

		return DateTime::parseFromSqlDateTime(date('Y-m-d') . ' ' . $sqlTime);
	}

	/**
	 * @param string|null $enteredTime
	 * @param string $separator
	 * @param int $hourPos
	 * @param int $minutePos
	 * @param int $secondPos
	 * @return array|null
	 */
	public static function _parse(?string $enteredTime, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): ?array
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

		$hour = (int) $timeArray[$hourPos];
		$minute = (int) $timeArray[$minutePos];
		$second = (int) ($timeArray[$secondPos] ?? 0);

		if (!self::check($hour, $minute, $second)) {
			return null;
		}

		return [$hour, $minute, $second];
	}


}