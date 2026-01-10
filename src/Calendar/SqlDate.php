<?php

namespace Osimatic\Calendar;

class SqlDate
{

	/**
	 * @param $date
	 * @return string|null
	 */
	public static function parse($date): ?string
	{
		if (empty($date)) {
			return null;
		}

		if (is_array($date) && !empty($date['date'])) {
			$date = substr($date['date'], 0, 10);
		}

		if (str_contains($date, '/')) {
			$dateArr = explode('/', $date);
			$date = ($dateArr[2]??null).'-'.($dateArr[1]??null).'-'.($dateArr[0]??null);
		}

		//if (false === ($date = date('Y-m-d', strtotime($date.' 00:00:00')))) {
		if (false === ($timestamp = strtotime($date.' 00:00:00')) || empty($parsedDate = date('Y-m-d', $timestamp))) {
			return null;
		}

		// Vérifier que la date parsée est valide et correspond à l'entrée
		if (!self::check($parsedDate)) {
			return null;
		}

		// Vérifier que la date parsée correspond à l'entrée (éviter les conversions non voulues)
		// Par exemple "invalid-date" pourrait être parsé comme une date relative
		$originalDate = trim($date);
		if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $originalDate) && $parsedDate !== $originalDate) {
			// Si l'entrée n'est pas au format ISO et que le résultat ne correspond pas, c'est probablement invalide
			if (!str_contains($originalDate, '-') || count(explode('-', $originalDate)) !== 3) {
				return null;
			}
		}

		return $parsedDate;
	}

	/**
	 * @param string|null $date
	 * @return bool
	 */
	public static function check(?string $date): bool
	{
		if (empty($date)) {
			return false;
		}
		$dateArr = explode('-', $date);
		$year = (int) ($dateArr[0]??0);
		$month = (int) ($dateArr[1]??0);
		$day = (int) ($dateArr[2]??0);
		return checkdate($month, $day, $year);
	}

	// ========== Extraction ==========

	/**
	 * Retourne le jour, à partir d'une date au format SQL.
	 * @param string $sqlDate
	 * @return int le jour, au format numérique
	 * TODO : extraire via substr pour une meilleure performance
	 */
	public static function getYear(string $sqlDate): int
	{
		return (int) date('Y', strtotime($sqlDate.' 00:00:00'));
	}

	/**
	 * Retourne le mois, à partir d'une date au format SQL.
	 * @param string $sqlDate
	 * @return int le mois, au format numérique
	 */
	public static function getMonth(string $sqlDate): int
	{
		return (int) date('m', strtotime($sqlDate.' 00:00:00'));
	}

	/**
	 * Retourne l'année, à partir d'une date au format SQL.
	 * @param string $sqlDate
	 * @return int l'année
	 */
	public static function getDay(string $sqlDate): int
	{
		return (int) date('d', strtotime($sqlDate.' 00:00:00'));
	}

	// ========== Fabrication ==========

	/**
	 * @param int $year
	 * @param int $month
	 * @param int $day
	 * @return string
	 */
	public static function get(int $year, int $month, int $day): string
	{
		return $year.'-'.sprintf('%02d', $month).'-'.sprintf('%02d', $day);
	}


	// ========== Semaine ==========

	/**
	 * @param int $year
	 * @param int $week
	 * @return string
	 */
	public static function getFirstDayOfWeek(int $year, int $week): string
	{
		$timeStampPremierJanvier = strtotime($year . '-01-01 00:00:00');
		$jourPremierJanvier = (int) date('w', $timeStampPremierJanvier);

		// recherche du N° de semaine du 1er janvier
		$numSemainePremierJanvier = (int) date('W', $timeStampPremierJanvier);

		// nombre à ajouter en fonction du numéro précédent
		$decallage = ($numSemainePremierJanvier === 1) ? $week - 1 : $week;

		// timestamp du jour dans la semaine recherchée
		$timeStampDate = strtotime('+' . $decallage . ' weeks', $timeStampPremierJanvier);

		// recherche du lundi de la semaine en fonction de la ligne précédente
		return date('Y-m-d', ($jourPremierJanvier === 1) ? $timeStampDate : strtotime('last monday', $timeStampDate));
	}

	/**
	 * @param int $year
	 * @param int $week
	 * @return string
	 */
	public static function getLastDayOfWeek(int $year, int $week): string
	{
		return date('Y-m-d', strtotime(self::getFirstDayOfWeek($year, $week).' 00:00:00')+(6*3600*24));
	}

	// ========== Mois ==========

	/**
	 * @param int $year
	 * @param int $month
	 * @return string
	 */
	public static function getFirstDayOfMonth(int $year, int $month): string
	{
		return date('Y-m-d', mktime(0, 0, 0, $month, 1, $year));
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @return string
	 */
	public static function getLastDayOfMonth(int $year, int $month): string
	{
		return date('Y-m-t', mktime(0, 0, 0, $month, 1, $year));
	}

}