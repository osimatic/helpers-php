<?php

namespace Osimatic\Helpers\Calendar;

class DateTime
{
	/**
	 * @return \DateTime
	 */
	public static function getCurrentDateTime(): \DateTime
	{
		return new \DateTime();
	}

	/**
	 * @param \DateTime $dateTime
	 * @param int $dateFormatter
	 * @param int $timeFormatter
	 * @param string|null $locale
	 * @return string
	 */
	public static function format(\DateTime $dateTime, int $dateFormatter, int $timeFormatter, ?string $locale=null): string
	{
		return \IntlDateFormatter::create($locale, $dateFormatter, $timeFormatter)->format($dateTime->getTimestamp());
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatDateTime(\DateTime $dateTime, ?string $locale=null): string
	{
		return \IntlDateFormatter::create($locale, \IntlDateFormatter::SHORT, \IntlDateFormatter::SHORT)->format($dateTime->getTimestamp());
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @param int $dateFormatter
	 * @return string
	 */
	public static function formatDate(\DateTime $dateTime, ?string $locale=null, int $dateFormatter=\IntlDateFormatter::SHORT): string
	{
		return \IntlDateFormatter::create($locale, $dateFormatter, \IntlDateFormatter::NONE)->format($dateTime->getTimestamp());
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatDateInLong(\DateTime $dateTime, ?string $locale=null): string
	{
		return self::formatDate($dateTime, $locale, \IntlDateFormatter::LONG);
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string|null $locale
	 * @param int $timeFormatter
	 * @return string
	 */
	public static function formatTime(\DateTime $dateTime, ?string $locale=null, int $timeFormatter=\IntlDateFormatter::SHORT): string
	{
		return \IntlDateFormatter::create($locale, \IntlDateFormatter::NONE, $timeFormatter)->format($dateTime->getTimestamp());
	}


	/**
	 * @param \DateTime $dateTime
	 * @param string $dateFormatter
	 * @param string $timeFormatter
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatFromTwig(?\DateTime $dateTime, string $dateFormatter='short', string $timeFormatter='short', ?string $locale=null): ?string
	{
		if (null === $dateTime) {
			return null;
		}

		return self::format($dateTime, self::getDateTimeFormatterFromTwig($dateFormatter), self::getDateTimeFormatterFromTwig($timeFormatter), $locale);
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string $dateFormatter
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatDateFromTwig(?\DateTime $dateTime, string $dateFormatter='short', ?string $locale=null): ?string
	{
		if (null === $dateTime) {
			return null;
		}

		return self::format($dateTime, self::getDateTimeFormatterFromTwig($dateFormatter), \IntlDateFormatter::NONE, $locale);
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string $timeFormatter
	 * @param string|null $locale
	 * @return string
	 */
	public static function formatTimeFromTwig(?\DateTime $dateTime, string $timeFormatter='short', ?string $locale=null): ?string
	{
		if (null === $dateTime) {
			return null;
		}

		return self::format($dateTime, \IntlDateFormatter::NONE, self::getDateTimeFormatterFromTwig($timeFormatter), $locale);
	}

	/**
	 * @param string $formatter
	 * @return int
	 */
	private static function getDateTimeFormatterFromTwig(string $formatter): int
	{
		switch ($formatter) {
			case 'none': return \IntlDateFormatter::NONE;
			case 'full': return \IntlDateFormatter::FULL;
			case 'long': return \IntlDateFormatter::LONG;
			case 'medium': return \IntlDateFormatter::MEDIUM;
		}
		return \IntlDateFormatter::SHORT;
	}



	/**
	 * @param string $str
	 * @return null|\DateTime
	 */
	public static function parse(string $str): ?\DateTime
	{
		try {
			return new \DateTime($str);
		}
		catch (\Exception $e) { }
		return null;
	}

	/**
	 * @param string $str
	 * @return null|\DateTime
	 */
	public static function parseDate(string $str): ?\DateTime
	{
		if (empty($str)) {
			return null;
		}

		// Format YYYY-mm-ddTHH:ii:ss
		if (strlen($str) === strlen('YYYY-mm-ddTHH:ii:ss') && null !== ($dateTime = self::parseFromSqlDateTime($str))) {
			return $dateTime;
		}

		if (false !== SqlDate::check($sqlDate = SqlDate::parse($str))) {
			return self::parseFromSqlDateTime($sqlDate.' 00:00:00');
		}

		return null;
	}

	/**
	 * @param string $sqlDateTime
	 * @return \DateTime|null
	 */
	public static function parseFromSqlDateTime(string $sqlDateTime): ?\DateTime
	{
		try {
			return new \DateTime($sqlDateTime);
		} catch (\Exception $e) {}
		return null;
	}

	// ========== Comparaison ==========

	/**
	 * @param \DateTime $dateTime1
	 * @param \DateTime $dateTime2
	 * @return bool
	 */
	public static function isDateAfter(\DateTime $dateTime1, \DateTime $dateTime2): bool
	{
		return $dateTime1->format('Ymd') > $dateTime2->format('Ymd');
	}

	/**
	 * @param \DateTime $dateTime1
	 * @param \DateTime $dateTime2
	 * @return bool
	 */
	public static function isDateBefore(\DateTime $dateTime1, \DateTime $dateTime2): bool
	{
		return $dateTime1->format('Ymd') < $dateTime2->format('Ymd');
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public static function isInThePast(\DateTime $dateTime): bool
	{
		return $dateTime < self::getCurrentDateTime();
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public static function isInTheFuture(\DateTime $dateTime): bool
	{
		return $dateTime > self::getCurrentDateTime();
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public static function isDateInThePast(\DateTime $dateTime): bool
	{
		return $dateTime->format('Ymd') < self::getCurrentDateTime()->format('Ymd');
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public static function isDateInTheFuture(\DateTime $dateTime): bool
	{
		return $dateTime->format('Ymd') > self::getCurrentDateTime()->format('Ymd');
	}



	// ========== Jour ==========

	// Jours dans une semaine

	/**
	 * Jour ouvré avec jour férié ou non
	 * @param \DateTime $dateTime
	 * @param bool $withPublicHoliday
	 * @return bool
	 */
	public static function isWorkingDay(\DateTime $dateTime, bool $withPublicHoliday=true): bool
	{
		if (self::isWeekend($dateTime)) {
			return false;
		}
		if ($withPublicHoliday && self::isPublicHoliday($dateTime)) {
			return false;
		}
		return true;
	}

	/**
	 * Jour ouvrable avec jour férié ou non
	 * @param \DateTime $dateTime
	 * @param bool $withPublicHoliday
	 * @return bool
	 */
	public static function isBusinessDay(\DateTime $dateTime, bool $withPublicHoliday=true): bool
	{
		$dayOfWeek = (int) $dateTime->format('N');
		if ($dayOfWeek === 7) {
			return false;
		}
		if ($withPublicHoliday && self::isPublicHoliday($dateTime)) {
			return false;
		}
		return true;
	}

	/**
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public static function isWeekend(\DateTime $dateTime): bool
	{
		$dayOfWeek = (int) $dateTime->format('N');
		return ($dayOfWeek === 6 || $dayOfWeek === 7);
	}

	/**
	 * @param \DateTime $dateTime
	 * @param int $nbDays
	 * @return \DateTime
	 */
	public static function moveBackOfNbDays(\DateTime $dateTime, int $nbDays): \DateTime
	{
		try {
			$dateTime = new \DateTime($dateTime->format('Y-m-d H:i:s'));
		} catch (\Exception $e) {
		}
		return $dateTime->modify('-'.$nbDays.' day');
	}

	/**
	 * @param \DateTime $dateTime
	 * @param int $nbDays
	 * @return \DateTime
	 */
	public static function moveForwardOfNbDays(\DateTime $dateTime, int $nbDays): \DateTime
	{
		try {
			$dateTime = new \DateTime($dateTime->format('Y-m-d H:i:s'));
		} catch (\Exception $e) {
		}
		return $dateTime->modify('+'.$nbDays.' day');
	}


	// ========== Semaine ==========

	/**
	 * @param \DateTime $dateTime
	 * @return array
	 */
	public static function getWeekNumber(\DateTime $dateTime): array
	{
		$weekNumber = $dateTime->format('W');
		$year = $dateTime->format('Y');
		// si weekNumber = 1 et que mois de sqlDate = 12, mettre year++
		if (((int)$weekNumber) === 1 && ((int)$dateTime->format('m')) === 12) {
			$year++;
		}
		return [$year, $weekNumber];
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfCurrentWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getFirstDayOfWeek(date('Y'), date('W')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfCurrentWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getLastDayOfWeek(date('Y'), date('W')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfPreviousWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('monday last week')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfPreviousWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('sunday last week')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfNextWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('monday next week')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfNextWeek(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('sunday next week')).' 00:00:00');
	}

	/**
	 * @param int $year
	 * @param int $week
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfWeek(int $year, int $week): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getFirstDayOfWeek($year, $week).' 00:00:00');
	}

	/**
	 * @param int $year
	 * @param int $week
	 * @return \DateTime|null
	 */
	public static function getLastDayOfWeek(int $year, int $week): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getLastDayOfWeek($year, $week).' 00:00:00');
	}

	/**
	 * @param \DateTime $dateTime
	 * @param int $weekDay
	 * @return \DateTime
	 */
	public static function getNextWeekDay(\DateTime $dateTime, int $weekDay): \DateTime
	{
		//$timestampCurrent = $dateTime->getTimestamp();
		//while (((int) date('N', $timestampCurrent)) !== $weekDay) {
		//	$timestampCurrent += 86400;
		//}
		//return new \DateTime(date('Y-m-d H:i:s', $timestampCurrent));
		while (((int) $dateTime->format('N')) !== $weekDay) {
			$dateTime->modify('+1 day');
		}
		return $dateTime;
	}

	// ========== Mois ==========

	/**
	 * @param \DateTime $dateTime
	 * @param int $nbMonths
	 * @return \DateTime
	 */
	public static function moveBackOfNbMonths(\DateTime $dateTime, int $nbMonths): \DateTime
	{
		try {
			$dateTime = new \DateTime($dateTime->format('Y-m-d H:i:s'));
		} catch (\Exception $e) {
		}
		return $dateTime->modify('-'.$nbMonths.' month');
	}

	/**
	 * @param \DateTime $dateTime
	 * @param int $nbMonths
	 * @return \DateTime
	 */
	public static function moveForwardOfNbMonths(\DateTime $dateTime, int $nbMonths): \DateTime
	{
		try {
			$dateTime = new \DateTime($dateTime->format('Y-m-d H:i:s'));
		} catch (\Exception $e) {
		}
		return $dateTime->modify('+'.$nbMonths.' month');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfCurrentMonth(): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getFirstDayOfMonth(date('Y'), date('m')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfCurrentMonth(): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getLastDayOfMonth(date('Y'), date('m')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfPreviousMonth(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('first day of previous month')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfPreviousMonth(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('last day of previous month')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfNextMonth(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('first day of next month')).' 00:00:00');
	}

	/**
	 * @return \DateTime|null
	 */
	public static function getLastDayOfNextMonth(): ?\DateTime
	{
		return self::parseFromSqlDateTime(date('Y-m-d', strtotime('last day of next month')).' 00:00:00');
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @return \DateTime|null
	 */
	public static function getFirstDayOfMonth(int $year, int $month): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getFirstDayOfMonth($year, $month).' 00:00:00');
	}

	/**
	 * @param int $year
	 * @param int $month
	 * @return \DateTime|null
	 */
	public static function getLastDayOfMonth(int $year, int $month): ?\DateTime
	{
		return self::parseFromSqlDateTime(SqlDate::getLastDayOfMonth($year, $month).' 00:00:00');
	}

	/**
	 * Renvoi le n-ième jour de la semaine d'un mois donné. Exemple : "2ème mercredi du mois"
	 * @param int $year
	 * @param int $month
	 * @param int $weekDay
	 * @param int $number
	 * @return \DateTime|null
	 */
	public static function getWeekDayOfMonth(int $year, int $month, int $weekDay, int $number): ?\DateTime
	{
		$weekDayName = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'][$weekDay-1] ?? null;
		if (null === $weekDayName) {
			return null;
		}

		$numberName = ['first', 'second', 'third', 'fourth', 'fifth'][$number-1] ?? null;
		if (null === $numberName) {
			return null;
		}

		try {
			$dateTime = new \DateTime($year.'-'.$month.'-01 00:00:00');
			$dateTime->modify($numberName.' '.$weekDayName.' of this month');

			if (((int) $dateTime->format('Y')) !== $year || ((int) $dateTime->format('m')) !== $month) {
				return null;
			}

			return $dateTime;
		}
		catch (\Exception $e) { }
		return null;
	}

	/**
	 * Renvoi le dernier jour de la semaine d'un mois donné. Exemple : "Dernier mercredi du mois"
	 * @param int $year
	 * @param int $month
	 * @param int $weekDay
	 * @return \DateTime|null
	 */
	public static function getLastWeekDayOfMonth(int $year, int $month, int $weekDay): ?\DateTime
	{
		$weekDayName = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'][$weekDay-1] ?? null;
		if (null === $weekDayName) {
			return null;
		}
		try {
			$dateTime = new \DateTime($year.'-'.$month.'-01 00:00:00');
			$dateTime->modify('+1 month');
			$dateTime->modify('last '.$weekDayName);
			return $dateTime;
		}
		catch (\Exception $e) { }
		return null;
	}



	// ========== Année ==========

	/**
	 * @param \DateTime $from
	 * @return int
	 */
	public static function calculateAge(\DateTime $from): int
	{
		$to = new \DateTime();
		return (int) $from->diff($to)->y;
	}



	// ========== Jours fériés ==========

	/**
	 * @param \DateTime $dateTime
	 * @param string $country
	 * @param array $options
	 * @return bool
	 */
	public static function isPublicHoliday(\DateTime $dateTime, string $country='FR', array $options=[]): bool
	{
		$listOfPublicHolidays = self::getListOfPublicHolidays($country, $dateTime->format('Y'), $options);
		foreach ($listOfPublicHolidays as $publicHoliday) {
			if ($publicHoliday['date'] === $dateTime->format('Y-m-d')) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Retourne sous forme d'un tableau la liste des jours fériés correspondant à des fêtes civiles, religieuses ou régionales.
	 * @param string $country pays correspondant aux jours fériés à récupérer
	 * @param int $year
	 * @param array $options : tableau d'options :
	 * 	- 'alsace' = true pour ajouter les jours fériés uniquement en Alsace et Moselle
	 * 	- 'dom_tom' = true pour ajouter les jours fériés uniquement dans les DOM-TOM
	 * 	- 'fetes_civiles' = true pour ajouter les jours non fériés mais qui correspondent à des fêtes civiles
	 * 	- 'fetes_catholiques' = true pour ajouter les jours non fériés mais qui correspondent à des fêtes catholiques
	 * 	- 'fetes_protestantes' = true pour ajouter les jours non fériés mais qui correspondent à des fêtes protestantes
	 * @return array
	 */
	public static function getListOfPublicHolidays(string $country, int $year, array $options=[]): array
	{
		$country = mb_strtoupper($country);

		$fillData = static function(array $listOfPublicHolidays) use ($year): array {
			foreach ($listOfPublicHolidays as $key => $tabJourFerie) {
				$listOfPublicHolidays[$key]['date'] = $year.'-'.sprintf('%02d', $tabJourFerie['month']).'-'.sprintf('%02d', $tabJourFerie['day']);
				$listOfPublicHolidays[$key]['key'] ??= $listOfPublicHolidays[$key]['date'];
			}
			return $listOfPublicHolidays;
		};

		$timePaques = easter_date($year);

		$timeLundiPentecote = $timePaques+(50*24*3600);
		$timePentecote = $timePaques+(49*24*3600);
		$timeJeudiAscension = $timePaques+(39*24*3600);
		$timeVendrediSaint = $timePaques-(2*24*3600);
		$timeLundiPaques = $timePaques+(1*24*3600);

		// ---------- BELGIQUE ----------
		if ($country === 'BE') {
			return $fillData([
				// --- BELGIQUE - Fêtes civiles ---

				// 1er janvier - Jour de l'an
				['day' => 1, 'month' => 1, 'label' => 'Jour de l\'an'],

				// 1er mai - Fête du Travail
				['day' => 1, 'month' => 5,'label' => 'Fête du Travail'],

				// 21 juillet - Fête nationale (Belgique)
				['day' => 21, 'month' => 7, 'label' => 'Fête nationale', 'nom_complet' => 'Fête nationale belge'],

				// 27 septembre - Fête de la communauté française
				['day' => 27, 'month' => 9, 'label' => 'Fête de la communauté française', 'nom_complet' => 'Fête de la communauté française'],

				// 11 novembre - Armistice de la Première Guerre mondiale (11 novembre 1918)
				['day' => 11, 'month' => 11, 'label' => 'Armistice 1918', 'nom_complet' => 'Armistice de la Première Guerre mondiale (11 novembre 1918)'],

				// --- BELGIQUE - Fêtes religieuses ---

				// Pâques
				['key' => 'paques', 'day' => date('d', $timePaques), 'month' => date('m', $timePaques), 'label' => 'Pâques'],

				// Lundi de Pâques (1 jour après Pâques)
				['key' => 'lundi_paques', 'day' => date('d', $timeLundiPaques), 'month' => date('m', $timeLundiPaques), 'label' => 'Lundi de Pâques'],

				// Jeudi de l'Ascension (39 jours après Pâques)
				['key' => 'ascension', 'day' => date('d', $timeJeudiAscension), 'month' => date('m', $timeJeudiAscension), 'label' => 'Ascension', 'nom_complet' => 'Jeudi de l\'Ascension'],

				// Pentecôte (49 jours après Pâques)
				['key' => 'pentecote', 'day' => date('d', $timePentecote), 'month' => date('m', $timePentecote), 'label' => 'Pentecôte'],

				// Lundi de Pentecôte (50 jours après Pâques)
				['key' => 'lundi_pentecote', 'day' => date('d', $timeLundiPentecote), 'month' => date('m', $timeLundiPentecote), 'label' => 'Lundi de Pentecôte'],

				// 15 août - Assomption
				['day' => 15, 'month' => 8, 'label' => 'Assomption'],

				// 1er novembre - Toussaint
				['day' => 1, 'month' => 11, 'label' => 'Toussaint', 'nom_complet' => 'Toussaint'],

				// 25 décembre - Noël
				['day' => 25, 'month' => 12, 'label' => 'Noël'],
			]);
		}

		// ---------- LUXEMBOURG ----------
		if ($country === 'LU') {
			return $fillData([
				// --- LUXEMBOURG - Fêtes civiles ---

				// 1er janvier - Jour de l'an
				['day' => 1, 'month' => 1, 'label' => 'Jour de l\'an'],

				// 1er mai - Fête du Travail
				['day' => 1, 'month' => 5,'label' => 'Fête du Travail'],

				// 23 juin - Fête nationale (Luxembourg) (célébration de l’anniversaire de SAR le Grand-Duc)
				['day' => 23, 'month' => 6, 'label' => 'Fête nationale', 'nom_complet' => 'Fête nationale luxembourgeoise'],

				// --- LUXEMBOURG - Fêtes religieuses ---

				// Pâques
				['key' => 'paques', 'day' => date('d', $timePaques), 'month' => date('m', $timePaques), 'label' => 'Pâques'],

				// Lundi de Pâques (1 jour après Pâques)
				['key' => 'lundi_paques', 'day' => date('d', $timeLundiPaques), 'month' => date('m', $timeLundiPaques), 'label' => 'Lundi de Pâques'],

				// Jeudi de l'Ascension (39 jours après Pâques)
				['key' => 'ascension', 'day' => date('d', $timeJeudiAscension), 'month' => date('m', $timeJeudiAscension), 'label' => 'Ascension', 'nom_complet' => 'Jeudi de l\'Ascension'],

				// Pentecôte (49 jours après Pâques)
				['key' => 'pentecote', 'day' => date('d', $timePentecote), 'month' => date('m', $timePentecote), 'label' => 'Pentecôte'],

				// Lundi de Pentecôte (50 jours après Pâques)
				['key' => 'lundi_pentecote', 'day' => date('d', $timeLundiPentecote), 'month' => date('m', $timeLundiPentecote), 'label' => 'Lundi de Pentecôte'],

				// 15 août - Assomption
				['day' => 15, 'month' => 8, 'label' => 'Assomption'],

				// 1er novembre - Toussaint
				['day' => 1, 'month' => 11, 'label' => 'Toussaint', 'nom_complet' => 'Toussaint'],

				// 25 décembre - Noël
				['day' => 25, 'month' => 12, 'label' => 'Noël'],
			]);
		}

		// ---------- SUISSE ----------
		// https://fr.wikipedia.org/wiki/Jours_f%C3%A9ri%C3%A9s_en_Suisse
		if ($country === 'CH') {
			$timestampFeteDieu = $timePaques+(60*24*3600);
			$timestampJeuneGenevois = strtotime('sunday', mktime(0, 0, 0, 9, 1, $year))+(4*24*3600);
			$timestampLundiJeuneFederal = strtotime('sunday', mktime(0, 0, 0, 9, 1, $year))+(15*24*3600);

			return $fillData([
				// --- SUISSE - Fêtes civiles ---

				// 1er janvier - Jour de l'an
				['day' => 1, 'month' => 1, 'label' => 'Jour de l\'an'],

				// 1er mars - Instauration de la République
				['day' => 1, 'month' => 3, 'label' => 'Instauration de la République'],

				// 1er mai - Fête du Travail
				['day' => 1, 'month' => 5,'label' => 'Fête du Travail'],

				// 23 juin - Commémoration du plébiscite
				['day' => 23, 'month' => 6, 'label' => 'Commémoration du plébiscite'],

				// 1er août - Fête nationale (Suisse)
				['day' => 1, 'month' => 8, 'label' => 'Fête nationale', 'nom_complet' => 'Fête nationale suisse'],

				// Jeûne genevois (jeudi suivant le 1er dimanche de septembre)
				['key' => 'jeune_genevois', 'day' => date('d', $timestampJeuneGenevois), 'month' => date('m', $timestampJeuneGenevois), 'label' => 'Jeûne genevois'],

				// Lundi du Jeûne fédéral (lundi suivant le 3e dimanche de septembre)
				['key' => 'jeune_federal', 'day' => date('d', $timestampLundiJeuneFederal), 'month' => date('m', $timestampLundiJeuneFederal), 'label' => 'Lundi du Jeûne fédéral'],

				// 31 décembre - Restauration de la République
				['day' => 31, 'month' => 12, 'label' => 'Restauration de la République'],

				// --- SUISSE - Fêtes religieuses ---

				// 2 janvier - Saint-Berchtold
				['day' => 2, 'month' => 1, 'label' => 'Saint-Berchtold'],

				// 6 janvier - Épiphanie
				['day' => 6, 'month' => 1, 'label' => 'Épiphanie'],

				// 19 mars - Saint-Joseph
				['day' => 19, 'month' => 3, 'label' => 'Saint-Joseph'],

				// 1er jeudi d'avril - Fahrtsfest
				// ['day' => 19, 'month' => 3, 'label' => 'Fahrtsfest'], // todo

				// Vendredi saint (2 jours avant Pâques)
				['key' => 'paques', 'day' => date('d', $timeVendrediSaint), 'month' => date('m', $timeVendrediSaint), 'label' => 'Vendredi saint'],

				// Pâques
				['key' => 'paques', 'day' => date('d', $timePaques), 'month' => date('m', $timePaques), 'label' => 'Pâques'],

				// Lundi de Pâques (1 jour après Pâques)
				['key' => 'lundi_paques', 'day' => date('d', $timeLundiPaques), 'month' => date('m', $timeLundiPaques), 'label' => 'Lundi de Pâques'],

				// Jeudi de l'Ascension (39 jours après Pâques)
				['key' => 'ascension', 'day' => date('d', $timeJeudiAscension), 'month' => date('m', $timeJeudiAscension), 'label' => 'Ascension', 'nom_complet' => 'Jeudi de l\'Ascension'],

				// Pentecôte (49 jours après Pâques)
				['key' => 'pentecote', 'day' => date('d', $timePentecote), 'month' => date('m', $timePentecote), 'label' => 'Pentecôte'],

				// Lundi de Pentecôte (50 jours après Pâques)
				['key' => 'lundi_pentecote', 'day' => date('d', $timeLundiPentecote), 'month' => date('m', $timeLundiPentecote), 'label' => 'Lundi de Pentecôte'],

				// Fête-Dieu (60 jours après Pâques)
				['key' => 'fete_dieu', 'day' => date('d', $timestampFeteDieu), 'month' => date('m', $timestampFeteDieu), 'label' => 'Fête-Dieu'],

				// 29 juin - Saint-Pierre et Paul
				['day' => 29, 'month' => 6, 'label' => 'Saint-Pierre et Paul'],

				// 15 août - Assomption
				['day' => 15, 'month' => 8, 'label' => 'Assomption'],

				// 25 septembre - Fête de Saint-Nicolas-de-Flüe
				['day' => 25, 'month' => 9, 'label' => 'Fête de Saint-Nicolas-de-Flüe'],

				// 1er novembre - Toussaint
				['day' => 1, 'month' => 11, 'label' => 'Toussaint'],

				// 8 décembre - Immaculée Conception
				['day' => 8, 'month' => 12, 'label' => 'Immaculée Conception'],

				// 25 décembre - Noël
				['day' => 25, 'month' => 12, 'label' => 'Noël'],

				// 26 décembre - Saint-Étienne
				['day' => 26, 'month' => 12, 'label' => 'Saint-Étienne'],
			]);
		}

		// ---------- FRANCE ----------
		if ($country === 'FR') {
			// --- FRANCE - Fêtes civiles ---
			$listOfPublicHolidays = [
				// 1er janvier - Jour de l'an
				['day' => 1, 'month' => 1, 'label' => 'Jour de l\'an'],

				// 1er mai - Fête du Travail
				['day' => 1, 'month' => 5,'label' => 'Fête du Travail'],

				// 8 mai - Victoire des Alliés sur l'Allemagne nazie (8 mai 1945)
				['day' => 8, 'month' => 5,'label' => 'Victoire des Alliés', 'nom_complet' => 'Victoire des Alliés sur l\'Allemagne nazie (8 mai 1945)'],

				// 14 juillet - Fête nationale (France) (Fête de la Fédération 14 juillet 1790)
				['day' => 14, 'month' => 7, 'label' => 'Fête nationale', 'nom_complet' => 'Fête nationale française (Fête de la Fédération 14 juillet 1790)'],

				// 11 novembre - Armistice de la Première Guerre mondiale (11 novembre 1918)
				['day' => 11, 'month' => 11, 'label' => 'Armistice', 'nom_complet' => 'Armistice de la Première Guerre mondiale (11 novembre 1918)'],
			];

			// --- FRANCE - Fêtes religieuses ---

			// Vendredi saint (vendredi précédent Pâques)
			if (!empty($options['alsace']) && $options['alsace']) {
				$timeVendrediSaint = $timePaques+(-2*24*3600);
				$listOfPublicHolidays[] = ['key' => 'vendredi_saint', 'day' => date('d', $timeVendrediSaint), 'month' => date('m', $timeVendrediSaint), 'label' => 'Vendredi saint'];
			}

			// Pâques
			$listOfPublicHolidays[] = ['key' => 'paques', 'day' => date('d', $timePaques), 'month' => date('m', $timePaques), 'label' => 'Pâques'];

			// Lundi de Pâques (1 jour après Pâques)
			$timeLundiPaques = $timePaques+(1*24*3600);
			$listOfPublicHolidays[] = ['key' => 'lundi_paques', 'day' => date('d', $timeLundiPaques), 'month' => date('m', $timeLundiPaques), 'label' => 'Lundi de Pâques'];

			// Jeudi de l'Ascension (39 jours après Pâques)
			$timeJeudiAscension = $timePaques+(39*24*3600);
			$listOfPublicHolidays[] = ['key' => 'ascension', 'day' => date('d', $timeJeudiAscension), 'month' => date('m', $timeJeudiAscension), 'label' => 'Ascension', 'nom_complet' => 'Jeudi de l\'Ascension'];

			// Pentecôte (49 jours après Pâques)
			$timePentecote = $timePaques+(49*24*3600);
			$listOfPublicHolidays[] = ['key' => 'pentecote', 'day' => date('d', $timePentecote), 'month' => date('m', $timePentecote), 'label' => 'Pentecôte'];

			// Lundi de Pentecôte (50 jours après Pâques)
			$timeLundiPentecote = $timePaques+(50*24*3600);
			$listOfPublicHolidays[] = ['key' => 'lundi_pentecote', 'day' => date('d', $timeLundiPentecote), 'month' => date('m', $timeLundiPentecote), 'label' => 'Lundi de Pentecôte'];

			// 15 août - Assomption
			$listOfPublicHolidays[] = ['day' => 15, 'month' => 8, 'label' => 'Assomption'];

			// 1er novembre - La Toussaint
			$listOfPublicHolidays[] = ['day' => 1, 'month' => 11, 'label' => 'La Toussaint'];

			// 25 décembre - Noël
			$listOfPublicHolidays[] = ['day' => 25, 'month' => 12, 'label' => 'Noël'];

			// 26 décembre - Saint Étienne
			if ($options['alsace'] ?? false) {
				$listOfPublicHolidays[] = ['day' => 26, 'month' => 12, 'label' => 'Saint Étienne'];
			}

			// --- FRANCE - Jours fériés des DOM-TOM ---
			if ($options['dom_tom'] ?? false) {
				// todo
			}

			// --- FRANCE - Jours non fériés mais qui correspondent à des fêtes civiles ---
			if ($options['fetes_civiles'] ?? false) {
				// todo
			}

			// --- FRANCE - Jours non fériés mais qui correspondent à des fêtes catholiques ---
			if ($options['fetes_catholiques'] ?? false) {
				// todo
			}

			// --- FRANCE - Jours non fériés mais qui correspondent à des fêtes protestantes ---
			if ($options['fetes_protestantes'] ?? false) {
				// todo
			}
			
			return $fillData($listOfPublicHolidays);
		}

		return [];
	}


}