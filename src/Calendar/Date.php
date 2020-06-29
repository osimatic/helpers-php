<?php

namespace Osimatic\Helpers\Calendar;

class Date
{
	// ========== Jour de la semaine ==========

	/**
	 * @param int $dayOfWeek
	 * @return string
	 */
	public static function getDayName(int $dayOfWeek): string
	{
		$timestamp = strtotime('monday this week');
		return ucfirst(strftime('%A', ($timestamp+($dayOfWeek*3600*24))));
	}

	// ========== Jour du mois ==========

	// ========== Mois ==========

	/**
	 * @param int $month
	 * @return string
	 */
	public static function getMonthName(int $month): string
	{
		return ucfirst(strftime('%B', mktime(0, 0, 0, $month)));
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @return int
	 */
	public static function getNumberOfDaysInMonth(int $year, int $month): int
	{
		return date('t', mktime(0, 0, 0, $month, 1, $year));
	}

	// ========== Année ==========

}