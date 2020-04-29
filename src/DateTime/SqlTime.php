<?php

namespace Osimatic\Helpers\DateTime;

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

}