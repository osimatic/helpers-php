<?php

namespace Osimatic\Helpers\DateTime;

class SqlDate
{

	/**
	 * @param $date
	 * @return string|null
	 */
	public static function parse($date): ?string
	{
		if (is_array($date) && !empty($date['date'])) {
			$date = substr($date['date'], 0, 10);
		}

		if (strpos($date, '/') !== false) {
			$dateArr = explode('/', $date);
			$date = ($dateArr[2]??null).'-'.($dateArr[1]??null).'-'.($dateArr[0]??null);
		}

		if (false === ($date = date('Y-m-d', strtotime($date.' 00:00:00')))) {
			return null;
		}
		return $date;
	}

	/**
	 * @param string|null $date
	 * @return bool
	 */
	public static function check(?string $date): bool
	{
		$dateArr = explode('-', $date);
		$year = ($dateArr[0]??null);
		$month = ($dateArr[1]??null);
		$day = ($dateArr[2]??null);
		return checkdate($month, $day, $year);
	}

}