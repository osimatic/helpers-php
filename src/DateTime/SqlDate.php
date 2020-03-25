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

	// ========== Semaine ==========

	/**
	 * @param $year
	 * @param $week
	 * @return string
	 */
	public static function getFirstDayOfWeek($year, $week): string
	{
		$timeStampPremierJanvier = strtotime($year . '-01-01 00:00:00');
		$jourPremierJanvier = date('w', $timeStampPremierJanvier);

		// recherche du N° de semaine du 1er janvier
		$numSemainePremierJanvier = date('W', $timeStampPremierJanvier);

		// nombre à ajouter en fonction du numéro précédent
		$decallage = ($numSemainePremierJanvier == 1) ? $week - 1 : $week;

		// timestamp du jour dans la semaine recherchée
		$timeStampDate = strtotime('+' . $decallage . ' weeks', $timeStampPremierJanvier);

		// recherche du lundi de la semaine en fonction de la ligne précédente
		return date('Y-m-d', ($jourPremierJanvier == 1) ? $timeStampDate : strtotime('last monday', $timeStampDate));
	}

	/**
	 * @param $year
	 * @param $week
	 * @return string
	 */
	public static function getLastDayOfWeek($year, $week): string
	{
		return date('Y-m-d', strtotime(self::getFirstDayOfWeek($year, $week).' 00:00:00')+(6*3600*24));
	}

	// ========== Mois ==========

	/**
	 * @param $year
	 * @param $month
	 * @return string
	 */
	public static function getFirstDayOfMonth($year, $month): string
	{
		return date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
	}

	/**
	 * @param $year
	 * @param $month
	 * @return string
	 */
	public static function getLastDayOfMonth($year, $month): string
	{
		$nbDaysInMonth = date("t", mktime( 0, 0, 0, $month, 1, $year));
		return date('Y-m-d', mktime(0, 0, 0, $month, $nbDaysInMonth, $year));
	}

}