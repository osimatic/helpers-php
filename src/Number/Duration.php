<?php

namespace Osimatic\Helpers\Number;

class Duration
{
	// ========== Calcul de nombres d'élément ==========

	/**
	 * @param int $durationInSeconds
	 * @return int
	 */
	public static function getNbDays(int $durationInSeconds): int
	{
		return (int) floor($durationInSeconds / 86400);
	}

	/**
	 * @param int $durationInSeconds
	 * @return int
	 */
	public static function getNbHours(int $durationInSeconds): int
	{
		return (int) floor($durationInSeconds / 3600);
	}

	/**
	 * @param int $durationInSeconds
	 * @return int
	 */
	public static function getNbMinutes(int $durationInSeconds): int
	{
		return (int) floor($durationInSeconds / 60);
	}

	/**
	 * Retourne le nombre d'heure entières restantes (après avoir retiré les jours) dans une durée en secondes.
	 * Par exemple, la durée "1 jour 2 heures 3 minutes et 4 secondes" correspond à 86400 + 7200 + 180 + 4 = 93784 secondes
	 * Pour la durée 93784 secondes, cette fonction retournera 2.
	 * @param int $durationInSeconds
	 * @return int
	 */
	public static function getNbHoursRemaining(int $durationInSeconds): int
	{
		$nbSecondesRemaining = $durationInSeconds%86400;
		return self::getNbHours($nbSecondesRemaining);
	}

	/**
	 * Retourne le nombre de minutes entières restantes (après avoir retiré les jours et les heures) dans une durée en secondes.
	 * Par exemple, la durée "1 jour 2 heures et 3 minutes" correspond à 86400 + 7200 + 180 = 93780 secondes
	 * Pour la durée 93784 secondes, cette fonction retournera 3.
	 * @param int $durationInSeconds
	 * @return int
	 */
	public static function getNbMinutesRemaining(int $durationInSeconds): int
	{
		$nbSecondesRemaining = $durationInSeconds%3600;
		return self::getNbMinutes($nbSecondesRemaining);
	}

	/**
	 * Retourne le nombre de secondes restantes (après avoir retiré les jours les heures et les minutes) dans une durée en secondes.
	 * Par exemple, la durée "1 jour 2 heures et 3 minutes" correspond à 86400 + 7200 + 180 = 93780 secondes
	 * Pour la durée 93784 secondes, cette fonction retournera 4.
	 * @param int $durationInSeconds
	 * @return int
	 */
	public static function getNbSecondsRemaining(int $durationInSeconds): int
	{
		$nbSecondesRemaining = $durationInSeconds%60;
		return $nbSecondesRemaining;
	}

	// ========== Affichage des durées (format chronomètre) ==========

	/**
	 * Formate une durée en heure pour l'affichage sous la forme "10:20.3" ou "10:20'30" (mode chronomètre), à partir d'une durée en seconde passée en paramètre.
	 * @param int $durationInSeconds la durée en seconde à formater
	 * @param string $displayMode valeurs possibles : "standard" pour afficher sous la forme "10:20.03", "input_time" pour afficher sous la forme "10:20:03" ou "chrono" pour afficher sous la forme "10:20'03" (mode chronomètre)
	 * @param boolean $withSecondes true pour ajouter les secondes dans la durée formatée, false pour ne pas les ajouter (true par défaut)
	 * @return string la durée formatée pour l'affichage.
	 */
	public static function formatHourChrono(int $durationInSeconds, string $displayMode='standard', bool $withSecondes=true): string
	{
		// Heures
		$strHeure = sprintf('%02d', self::getNbHours($durationInSeconds)).'"';

		// Minutes
		$strMinute = self::getFormattedMinutesInChrono(self::getNbMinutesRemaining($durationInSeconds), $displayMode);

		// Secondes
		$strSeconde = '';
		if ($withSecondes) {
			$strSeconde = self::getFormattedSecondsInChrono(self::getNbSecondsRemaining($durationInSeconds), $displayMode);
		}

		return $strHeure.$strMinute.$strSeconde;
	}

	/**
	 * @param int $durationInSeconds la durée en seconde à formatter
	 * @param string $displayMode valeurs possibles : "standard" pour afficher sous la forme "10:20.03", "input_time" pour afficher sous la forme "10:20:03" ou "chrono" pour afficher sous la forme "10:20'03" (mode chronomètre)
	 * @return string la durée formatée pour l'affichage.
	 */
	public static function formatMinuteChrono(int $durationInSeconds, string $displayMode='standard'): string
	{
		// Minutes
		$strMinute = self::getFormattedMinutesInChrono(self::getNbMinutes($durationInSeconds), $displayMode);

		// Secondes
		$strSeconde = self::getFormattedSecondsInChrono(self::getNbSecondsRemaining($durationInSeconds), $displayMode);

		return $strMinute.$strSeconde;
	}

	private static function getFormattedMinutesInChrono(int $nbMinutes, string $displayMode='standard')
	{
		return sprintf('%02d', $nbMinutes).($displayMode==='chrono'?'\'':'');
	}

	private static function getFormattedSecondsInChrono(int $nbSeconds, string $displayMode='standard')
	{
		return ($displayMode==='input_time'?':':($displayMode!=='chrono'?'.':'')).sprintf('%02d', $nbSeconds).($displayMode==='chrono'?'"':'');
	}


}