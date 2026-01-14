<?php

namespace Osimatic\Number;

class Duration
{
	// ========== Calcul entre 2 plages horaires ==========

	/**
	 * Calculates the duration (in seconds) of the intersection between two time slots.
	 * @param int $timeSlot1StartTimestamp
	 * @param int $timeSlotEnd1Timestamp
	 * @param int $timeSlot2StartTimestamp
	 * @param int $timeSlotEnd2Timestamp
	 * @return int
	 */
	public static function getIntersectionDurationBetweenTimestampSlots(
		int $timeSlot1StartTimestamp,
		int $timeSlotEnd1Timestamp,
		int $timeSlot2StartTimestamp,
		int $timeSlotEnd2Timestamp
	): int
	{
		$timestampCalcStart = ($timeSlot1StartTimestamp > $timeSlot2StartTimestamp ? $timeSlot1StartTimestamp : $timeSlot2StartTimestamp);
		$timestampCalcEnd = ($timeSlotEnd1Timestamp < $timeSlotEnd2Timestamp ? $timeSlotEnd1Timestamp : $timeSlotEnd2Timestamp);
		if ($timestampCalcEnd > $timestampCalcStart) {
			return $timestampCalcEnd - $timestampCalcStart;
		}
		return 0;
	}

	/**
	 * Calculates the duration (in seconds) of the intersection between two time slots.
	 * @param \DateTimeInterface $slot1Start
	 * @param \DateTimeInterface $slot1End
	 * @param \DateTimeInterface $slot2Start
	 * @param \DateTimeInterface $slot2End
	 * @return int Duration of the intersection in seconds
	 */
	public static function getIntersectionDurationBetweenTimeSlots(
		\DateTimeInterface $slot1Start,
		\DateTimeInterface $slot1End,
		\DateTimeInterface $slot2Start,
		\DateTimeInterface $slot2End
	): int
	{
		// Determine the later of the two start times
		$calcStart = $slot1Start > $slot2Start ? $slot1Start : $slot2Start;
		// Determine the earlier of the two end times
		$calcEnd = $slot1End < $slot2End ? $slot1End : $slot2End;

		// If there is an intersection
		if ($calcEnd > $calcStart) {
			return self::getDurationInSeconds($calcStart, $calcEnd);
		}

		return 0;
	}

	/**
	 * Returns the duration between two DateTime objects in seconds,
	 * correctly handling daylight saving time transitions.
	 *
	 * @param \DateTimeInterface $start
	 * @param \DateTimeInterface $end
	 * @param bool $inverse
	 * @return int Duration in seconds (always positive)
	 */
	public static function getDurationInSeconds(\DateTimeInterface $start, \DateTimeInterface $end, bool $inverse=false): int
	{
		// Ensure $end is after $start
		if ($end < $start) {
			if (!$inverse) {
				return 0;
			}

			[$start, $end] = [$end, $start];
		}

		return $end->getTimestamp() - $start->getTimestamp();

		/*$interval = $start->diff($end);
		return $interval->days * 86400
			+ $interval->h * 3600
			+ $interval->i * 60
			+ $interval->s;*/
	}

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
	 * Retourne le nombre d'heures entières restantes (après avoir retiré les jours) dans une durée en secondes.
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
		return $durationInSeconds%60;
	}

	// ========== Affichage des durées (format texte) ==========

	public static function formatAsText(int $durationInSeconds, bool $withSeconds=true, bool $withMinutes=true, bool $withMinuteLabel=true, bool $fullLabel=false, bool $hideHourIfZeroHour=false): string
	{
		$durationInSeconds = (int) abs($durationInSeconds);

		$str = '';

		// Heures
		$nbHours = self::getNbHours($durationInSeconds);
		if (!$hideHourIfZeroHour || $nbHours > 0) {
			$str .= $nbHours;
			$str .= $fullLabel ? ' '.\Osimatic\Text\Str::pluralize('{heure|heures}', $nbHours) : 'h';
		}

		// Minutes
		if ($withMinutes) {
			$nbMinutes = self::getNbMinutesRemaining($durationInSeconds);
			$str .= ' ' . str_pad($nbMinutes, 2, '0', STR_PAD_LEFT);
			if ($withMinuteLabel) {
				$str .= $fullLabel ? ' '.\Osimatic\Text\Str::pluralize('{minute|minutes}', $nbMinutes) : 'min';
			}
		}

		// Secondes
		if ($withSeconds) {
			$nbSeconds = self::getNbSecondsRemaining($durationInSeconds);
			$str .= ' ' . str_pad($nbSeconds, 2, '0', STR_PAD_LEFT);
			$str .= $fullLabel ? ' '.\Osimatic\Text\Str::pluralize('{seconde|secondes}', $nbSeconds) : 's';
		}

		return trim($str);
	}

	// ========== Affichage des durées (format chronomètre) ==========

	/**
	 * Formate une durée en heure pour l'affichage sous la forme "10:20.3" ou "10:20'30" (mode chronomètre), à partir d'une durée en seconde passée en paramètre.
	 * @param int $durationInSeconds la durée en seconde à formater
	 * @param DurationDisplayMode $displayMode valeurs possibles : "standard" pour afficher sous la forme "10:20.03", "input_time" pour afficher sous la forme "10:20:03" ou "chrono" pour afficher sous la forme "10:20'03" (mode chronomètre)
	 * @param boolean $withSecondes true pour ajouter les secondes dans la durée formatée, false pour ne pas les ajouter (true par défaut)
	 * @return string la durée formatée pour l'affichage.
	 */
	public static function formatNbHours(int $durationInSeconds, DurationDisplayMode $displayMode=DurationDisplayMode::STANDARD, bool $withSecondes=true): string
	{
		// Heures
		$strHeure = sprintf('%02d', self::getNbHours($durationInSeconds)).':';

		// Minutes
		$strMinute = self::getFormattedNbMinutes(self::getNbMinutesRemaining($durationInSeconds), $displayMode);

		// Secondes
		$strSeconde = '';
		if ($withSecondes) {
			$strSeconde = self::getFormattedNbSeconds(self::getNbSecondsRemaining($durationInSeconds), $displayMode);
		}

		return $strHeure.$strMinute.$strSeconde;
	}

	/**
	 * @param int $durationInSeconds la durée en seconde à formater
	 * @param DurationDisplayMode $displayMode valeurs possibles : "standard" pour afficher sous la forme "10:20.03", "input_time" pour afficher sous la forme "10:20:03" ou "chrono" pour afficher sous la forme "10:20'03" (mode chronomètre)
	 * @return string la durée formatée pour l'affichage.
	 */
	public static function formatNbMinutes(int $durationInSeconds, DurationDisplayMode $displayMode=DurationDisplayMode::STANDARD): string
	{
		// Minutes
		$strMinute = self::getFormattedNbMinutes(self::getNbMinutes($durationInSeconds), $displayMode);

		// Secondes
		$strSeconde = self::getFormattedNbSeconds(self::getNbSecondsRemaining($durationInSeconds), $displayMode);

		return $strMinute.$strSeconde;
	}

	/**
	 * @param int $nbMinutes
	 * @param DurationDisplayMode $displayMode
	 * @return string
	 */
	private static function getFormattedNbMinutes(int $nbMinutes, DurationDisplayMode $displayMode=DurationDisplayMode::STANDARD): string
	{
		return sprintf('%02d', $nbMinutes).($displayMode===DurationDisplayMode::CHRONO?'\'':'');
	}

	/**
	 * @param int $nbSeconds
	 * @param DurationDisplayMode $displayMode
	 * @return string
	 */
	private static function getFormattedNbSeconds(int $nbSeconds, DurationDisplayMode $displayMode=DurationDisplayMode::STANDARD): string
	{
		return ($displayMode===DurationDisplayMode::INPUT_TIME?':':($displayMode!==DurationDisplayMode::CHRONO?'.':'')).sprintf('%02d', $nbSeconds).($displayMode===DurationDisplayMode::CHRONO?'"':'');
	}


	// ========== Check ==========

	/**
	 * Vérifie la validité d'une durée saisie dans un formulaire, via un champ text (saisie de int) ou un champ de type time (saisie de type hh:mm:ss)
	 * Accepte donc des durées sous la forme "10:23:02" ou "1220" (secondes)
	 * @param mixed $enteredDuration
	 * @param string $separator
	 * @param int $hourPos
	 * @param int $minutePos
	 * @param int $secondPos
	 * @return bool
	 */
	public static function check(mixed $enteredDuration, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): bool
	{
		return (null !== self::_parse($enteredDuration, $separator, $hourPos, $minutePos, $secondPos));
	}

	// ========== Parse ==========

	/**
	 * Parse une durée au format "entier" (nombre de secondes) ou format "string" (type hh:mm:ss) et retourne la durée en secondes
	 * @param mixed $enteredDuration
	 * @param string $separator
	 * @param int $hourPos
	 * @param int $minutePos
	 * @param int $secondPos
	 * @return int
	 */
	public static function parse(mixed $enteredDuration, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): int
	{
		if (null !== ($duration = self::_parse($enteredDuration, $separator, $hourPos, $minutePos, $secondPos))) {
			return $duration;
		}
		return 0;
	}

	/**
	 * @param mixed $enteredDuration
	 * @param string $separator
	 * @param int $hourPos
	 * @param int $minutePos
	 * @param int $secondPos
	 * @return int|null
	 */
	private static function _parse(mixed $enteredDuration, string $separator=':', int $hourPos=1, int $minutePos=2, int $secondPos=3): ?int
	{
		if (preg_match("/^-?[0-9]{0,10}$/", $enteredDuration)) {
			return (int) $enteredDuration;
		}

		if (null === ($timeArray = \Osimatic\Calendar\Time::_parse($enteredDuration, $separator, $hourPos, $minutePos, $secondPos))) {
			return null;
		}

		return ($timeArray[0] ?? 0) * 3600 + ($timeArray[1] ?? 0) * 60 + ($timeArray[2] ?? 0);
	}

	// ========== Conversion en durée décimales ==========

	/**
	 * @param int $duration (en secondes)
	 * @return float
	 */
	public static function convertToNbDecimalHours(int $duration): float {
		return round($duration / 3600, 2);
	}

	/**
	 * @param int $duration (en secondes)
	 * @return float
	 */
	public static function convertToNbDecimalMinutes(int $duration): float {
		return round($duration / 60, 2);
	}

	// ========== Arrondissement ==========

	/**
	 * @param int $duration
	 * @param int $precision
	 * @param string|null $mode
	 * @return int
	 */
	public static function round(int $duration, int $precision, ?string $mode=null): int
	{
		if ($precision <= 0) {
			// pas d'arrondissement
			return $duration;
		}

		$mode = mb_strtolower(!empty($mode)?$mode:'close');

		$remainingDuration = $duration;
		$hours = (int) floor($remainingDuration / 3600);
		$remainingDuration -= $hours * 3600;
		$minutes = (int) floor($remainingDuration / 60);
		$remainingDuration -= $minutes * 60;
		$seconds = $remainingDuration % 60;

		$minutesRemaining = $minutes % $precision;
		$minutesRemainingAndSecondsAsCentieme = $minutesRemaining + $seconds/60;
		if ($minutesRemainingAndSecondsAsCentieme === 0) {
			// pas d'arrondissement
			return $duration;
		}

		$halfRoundPrecision = $precision / 2;
		$hoursRounded = $hours;
		$secondsRounded = 0;
		if ($mode === 'up' || ($mode === 'close' && $minutesRemainingAndSecondsAsCentieme > $halfRoundPrecision)) {
			// Arrondissement au-dessus
			if ($minutes > (60-$precision)) {
				$minutesRounded = 0;
				$hoursRounded++;
			}
			else {
				$minutesRounded = ($minutes-$minutesRemaining)+$precision;
			}
		}
		else {
			// Arrondissement au-dessous
			$minutesRounded = ($minutes-$minutesRemaining);
		}
		
		return $hoursRounded * 3600 + $minutesRounded * 60 + $secondsRounded;
	}


	// ========== Vérification min/max ==========

	/**
	 * @param int $duration
	 * @param int $durationMin
	 * @param int $durationMax
	 * @return bool
	 */
	public static function checkMinAndMax(int $duration, int $durationMin, int $durationMax): bool
	{
		if ($durationMin > 0 && $durationMax > 0) {
			return ($duration >= $durationMin && $duration <= $durationMax);
		}
		if ($durationMin > 0) {
			return ($duration >= $durationMin);
		}
		if ($durationMax > 0) {
			return ($duration <= $durationMax);
		}
		return false;
	}








	// ========== DEPRECATED ==========

	/**
	 * @deprecated use formatNbHours instead
	 */
	public static function formatHourChrono(int $durationInSeconds, string $displayMode='standard', bool $withSecondes=true): string
	{
		$enumDisplayMode = DurationDisplayMode::parse($displayMode) ?? DurationDisplayMode::STANDARD;

		// Heures
		$strHeure = sprintf('%02d', self::getNbHours($durationInSeconds)).':';

		// Minutes
		$strMinute = self::getFormattedNbMinutes(self::getNbMinutesRemaining($durationInSeconds), $enumDisplayMode);

		// Secondes
		$strSeconde = '';
		if ($withSecondes) {
			$strSeconde = self::getFormattedNbSeconds(self::getNbSecondsRemaining($durationInSeconds), $enumDisplayMode);
		}

		return $strHeure.$strMinute.$strSeconde;
	}

	/**
	 * @deprecated use formatNbMinutes instead
	 */
	public static function formatMinuteChrono(int $durationInSeconds, string $displayMode='standard'): string
	{
		$enumDisplayMode = DurationDisplayMode::parse($displayMode) ?? DurationDisplayMode::STANDARD;

		// Minutes
		$strMinute = self::getFormattedNbMinutes(self::getNbMinutes($durationInSeconds), $enumDisplayMode);

		// Secondes
		$strSeconde = self::getFormattedNbSeconds(self::getNbSecondsRemaining($durationInSeconds), $enumDisplayMode);

		return $strMinute.$strSeconde;
	}

	/**
	 * @deprecated use getIntersectionDurationBetweenTimestampSlots instead
	 */
	public static function getDurationOfIntersectionBetweenTwoTimeSlot(int $timeSlot1StartTimestamp, int $timeSlotEnd1Timestamp, int $timeSlot2StartTimestamp, int $timeSlotEnd2Timestamp): int
	{
		return self::getIntersectionDurationBetweenTimestampSlots($timeSlot1StartTimestamp, $timeSlotEnd1Timestamp, $timeSlot2StartTimestamp, $timeSlotEnd2Timestamp);
	}

}