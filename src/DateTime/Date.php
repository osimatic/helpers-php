<?php

namespace Osimatic\Helpers\DateTime;

class Date
{
	// ========== Jour de la semaine ==========

	public static function getDayName($dayOfWeek)
	{
		$timestamp = strtotime('monday this week');
		return ucfirst(strftime('%A', ($timestamp+($dayOfWeek*3600*24))));
	}

	// ========== Jour du mois ==========

	// ========== Mois ==========

	public static function getMonthName($month) {
		return ucfirst(strftime('%B', mktime(0, 0, 0, $month)));
	}

	// ========== Année ==========

}