<?php

namespace Osimatic\Helpers\Calendar;

class PublicHolidays
{
	public static function getEasterDateTime(int $year): \DateTime
	{
		$base = new \DateTime("$year-03-21");
		$days = easter_days($year);
		return $base->add(new \DateInterval("P{$days}D"));
	}

	/**
	 * @param \DateTime $dateTime
	 * @param string $country
	 * @param array $options
	 * @return bool
	 */
	public static function isPublicHoliday(\DateTime $dateTime, string $country='FR', array $options=[]): bool
	{
		$listOfPublicHolidays = self::getList($country, $dateTime->format('Y'), $options);
		foreach ($listOfPublicHolidays as $publicHoliday) {
			if (($publicHoliday['calendar'] ?? null) === 'islamic') {
				[, $hijriMonth, $hijriDay] = IslamicCalendar::convertGregorianDateToIslamicDate($dateTime->format('Y'), $dateTime->format('m'), $dateTime->format('d'));
				if ($publicHoliday['month'] === $hijriMonth && $publicHoliday['day'] === $hijriDay) {
					//if (IslamicCalendar::isGregorianDateTimeEqualToIslamicDay($dateTime, $publicHoliday['month'], $publicHoliday['day'])) {
					return true;
				}
				continue;
			}

			if (date('Y-m-d', $publicHoliday['timestamp']) === $dateTime->format('Y-m-d')) {
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
	public static function getList(string $country, int $year, array $options=[]): array
	{
		$country = mb_strtoupper($country);

		$fillData = static function(array $listOfPublicHolidays) use ($year): array {
			foreach ($listOfPublicHolidays as $key => $publicHolidayData) {
				$publicHolidayData['day'] = (int) $publicHolidayData['day'];
				$publicHolidayData['month'] = (int) $publicHolidayData['month'];

				if (($publicHolidayData['calendar'] ?? null) === 'islamic') {
					$publicHolidayData['timestamp'] = null;
				}
				else {
					//$publicHolidayData['date'] = $year.'-'.sprintf('%02d', $publicHolidayData['month']).'-'.sprintf('%02d', $publicHolidayData['day']); // ce champ est deprecated (remplacé par timestamp)
					if (!isset($publicHolidayData['timestamp'])) {
						$publicHolidayData['timestamp'] = mktime(0, 0, 0, $publicHolidayData['month'], $publicHolidayData['day'], $year);
					}
				}

				$publicHolidayData['key'] ??= date('Y-m-d', $publicHolidayData['timestamp']);

				// ajout jour de l'année dans le label
				if (preg_match('/[1-2][0-9][0-9][0-9]-((0[0-9])|(1[1-2]))-(([0-2][0-9])|(3[0-1]))/', $publicHolidayData['key']) !== 0) {
					if (($publicHolidayData['calendar'] ?? null) === 'islamic') {
						$publicHolidayData['label'] .= ' ('.$publicHolidayData['day'].($publicHolidayData['day']===1?'er':'').' '.\Osimatic\Helpers\Calendar\IslamicCalendar::getMonthName($publicHolidayData['month']).')';
					}
					else {
						$publicHolidayData['label'] .= ' ('.$publicHolidayData['day'].($publicHolidayData['day']===1?'er':'').' '.\Osimatic\Helpers\Calendar\Date::getMonthName($publicHolidayData['month']).')';
					}
				}

				$listOfPublicHolidays[$key] = $publicHolidayData;
			}
			return $listOfPublicHolidays;
		};

		//$easterDateTime = (new \DateTime('@'.easter_date($year)))->setTimezone(new \DateTimeZone($timeZone));
		$easterDateTime = self::getEasterDateTime($year);

		$vendrediSaintDateTime = (clone $easterDateTime)->modify('-2 days');
		$lundiPaquesDateTime = (clone $easterDateTime)->modify('+1 days');
		$jeudiAscensionDateTime = (clone $easterDateTime)->modify('+39 days');
		$pentecoteDateTime = (clone $easterDateTime)->modify('+49 days');
		$lundiPentecoteDateTime = (clone $easterDateTime)->modify('+50 days');

		// ---------- BELGIQUE ----------
		if ('BE' === $country) {
			return $fillData([
				// --- BELGIQUE - Fêtes civiles ---

				// 1er janvier - Jour de l’an
				['day' => 1, 'month' => 1, 'label' => 'Jour de l’an'],

				// 1er mai - Fête du Travail
				['day' => 1, 'month' => 5, 'label' => 'Fête du Travail'],

				// 21 juillet - Fête nationale (Belgique)
				['day' => 21, 'month' => 7, 'label' => 'Fête nationale', 'nom_complet' => 'Fête nationale belge'],

				// 27 septembre - Fête de la communauté française
				['day' => 27, 'month' => 9, 'label' => 'Fête de la communauté française', 'nom_complet' => 'Fête de la communauté française'],

				// 11 novembre - Armistice de la Première Guerre mondiale (11 novembre 1918)
				['day' => 11, 'month' => 11, 'label' => 'Armistice 1918', 'nom_complet' => 'Armistice de la Première Guerre mondiale (11 novembre 1918)'],

				// --- BELGIQUE - Fêtes religieuses ---

				// Pâques
				['key' => 'paques', 'day' => $easterDateTime->format('d'), 'month' => $easterDateTime->format('m'), 'timestamp' => $easterDateTime->getTimestamp(), 'label' => 'Pâques'],

				// Lundi de Pâques (1 jour après Pâques)
				['key' => 'lundi_paques', 'day' => $lundiPaquesDateTime->format('d'), 'month' => $lundiPaquesDateTime->format('m'), 'timestamp' => $lundiPaquesDateTime->getTimestamp(), 'label' => 'Lundi de Pâques'],

				// Jeudi de l’Ascension (39 jours après Pâques)
				['key' => 'ascension', 'day' => $jeudiAscensionDateTime->format('d'), 'month' => $jeudiAscensionDateTime->format('m'), 'timestamp' => $jeudiAscensionDateTime->getTimestamp(), 'label' => 'Ascension', 'nom_complet' => 'Jeudi de l’Ascension'],

				// Pentecôte (49 jours après Pâques)
				['key' => 'pentecote', 'day' => $pentecoteDateTime->format('d'), 'month' => $pentecoteDateTime->format('m'), 'timestamp' => $pentecoteDateTime->getTimestamp(), 'label' => 'Pentecôte'],

				// Lundi de Pentecôte (50 jours après Pâques)
				['key' => 'lundi_pentecote', 'day' => $lundiPentecoteDateTime->format('d'), 'month' => $lundiPentecoteDateTime->format('m'), 'timestamp' => $lundiPentecoteDateTime->getTimestamp(), 'label' => 'Lundi de Pentecôte'],

				// 15 août - Assomption
				['day' => 15, 'month' => 8, 'label' => 'Assomption'],

				// 1er novembre - Toussaint
				['day' => 1, 'month' => 11, 'label' => 'Toussaint', 'nom_complet' => 'Toussaint'],

				// 25 décembre - Noël
				['day' => 25, 'month' => 12, 'label' => 'Noël'],
			]);
		}

		// ---------- LUXEMBOURG ----------
		if ('LU' === $country) {
			return $fillData([
				// --- LUXEMBOURG - Fêtes civiles ---

				// 1er janvier - Jour de l’an
				['day' => 1, 'month' => 1, 'label' => 'Jour de l’an'],

				// 1er mai - Fête du Travail
				['day' => 1, 'month' => 5, 'label' => 'Fête du Travail'],

				// 23 juin - Fête nationale (Luxembourg) (célébration de l’anniversaire de SAR le Grand-Duc)
				['day' => 23, 'month' => 6, 'label' => 'Fête nationale', 'nom_complet' => 'Fête nationale luxembourgeoise'],

				// --- LUXEMBOURG - Fêtes religieuses ---

				// Pâques
				['key' => 'paques', 'day' => $easterDateTime->format('d'), 'month' => $easterDateTime->format('m'), 'timestamp' => $easterDateTime->getTimestamp(), 'label' => 'Pâques'],

				// Lundi de Pâques (1 jour après Pâques)
				['key' => 'lundi_paques', 'day' => $lundiPaquesDateTime->format('d'), 'month' => $lundiPaquesDateTime->format('m'), 'timestamp' => $lundiPaquesDateTime->getTimestamp(), 'label' => 'Lundi de Pâques'],

				// Jeudi de l’Ascension (39 jours après Pâques)
				['key' => 'ascension', 'day' => $jeudiAscensionDateTime->format('d'), 'month' => $jeudiAscensionDateTime->format('m'), 'timestamp' => $jeudiAscensionDateTime->getTimestamp(), 'label' => 'Ascension', 'nom_complet' => 'Jeudi de l’Ascension'],

				// Pentecôte (49 jours après Pâques)
				['key' => 'pentecote', 'day' => $pentecoteDateTime->format('d'), 'month' => $pentecoteDateTime->format('m'), 'timestamp' => $pentecoteDateTime->getTimestamp(), 'label' => 'Pentecôte'],

				// Lundi de Pentecôte (50 jours après Pâques)
				['key' => 'lundi_pentecote', 'day' => $lundiPentecoteDateTime->format('d'), 'month' => $lundiPentecoteDateTime->format('m'), 'timestamp' => $lundiPentecoteDateTime->getTimestamp(), 'label' => 'Lundi de Pentecôte'],

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
		if ('CH' === $country) {
			$feteDieuDateTime = (clone $easterDateTime)->modify('+60 days');
			$timestampJeuneGenevois = strtotime('sunday', mktime(0, 0, 0, 9, 1, $year))+(4*24*3600);
			$timestampLundiJeuneFederal = strtotime('sunday', mktime(0, 0, 0, 9, 1, $year))+(15*24*3600);

			return $fillData([
				// --- SUISSE - Fêtes civiles ---

				// 1er janvier - Jour de l’an
				['day' => 1, 'month' => 1, 'label' => 'Jour de l’an'],

				// 1er mars - Instauration de la République
				['day' => 1, 'month' => 3, 'label' => 'Instauration de la République'],

				// 1er mai - Fête du Travail
				['day' => 1, 'month' => 5, 'label' => 'Fête du Travail'],

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
				['key' => 'vendredi_saint', 'day' => $vendrediSaintDateTime->format('d'), 'month' => $vendrediSaintDateTime->format('m'), 'timestamp' => $vendrediSaintDateTime->getTimestamp(), 'label' => 'Vendredi saint'],

				// Pâques
				['key' => 'paques', 'day' => $easterDateTime->format('d'), 'month' => $easterDateTime->format('m'), 'timestamp' => $easterDateTime->getTimestamp(), 'label' => 'Pâques'],

				// Lundi de Pâques (1 jour après Pâques)
				['key' => 'lundi_paques', 'day' => $lundiPaquesDateTime->format('d'), 'month' => $lundiPaquesDateTime->format('m'), 'timestamp' => $lundiPaquesDateTime->getTimestamp(), 'label' => 'Lundi de Pâques'],

				// Jeudi de l’Ascension (39 jours après Pâques)
				['key' => 'ascension', 'day' => $jeudiAscensionDateTime->format('d'), 'month' => $jeudiAscensionDateTime->format('m'), 'timestamp' => $jeudiAscensionDateTime->getTimestamp(), 'label' => 'Ascension', 'nom_complet' => 'Jeudi de l’Ascension'],

				// Pentecôte (49 jours après Pâques)
				['key' => 'pentecote', 'day' => $pentecoteDateTime->format('d'), 'month' => $pentecoteDateTime->format('m'), 'timestamp' => $pentecoteDateTime->getTimestamp(), 'label' => 'Pentecôte'],

				// Lundi de Pentecôte (50 jours après Pâques)
				['key' => 'lundi_pentecote', 'day' => $lundiPentecoteDateTime->format('d'), 'month' => $lundiPentecoteDateTime->format('m'), 'timestamp' => $lundiPentecoteDateTime->getTimestamp(), 'label' => 'Lundi de Pentecôte'],

				// Fête-Dieu (60 jours après Pâques)
				['key' => 'fete_dieu', 'day' => $feteDieuDateTime->format('d'), 'month' => $feteDieuDateTime->format('m'), 'timestamp' => $feteDieuDateTime->getTimestamp(), 'label' => 'Fête-Dieu'],

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

		// ---------- MAROC ----------
		if ('MA' === $country) {
			return $fillData([
				// --- MAROC - Fêtes civiles ---

				// 1er janvier - Jour de l’an
				['day' => 1, 'month' => 1, 'label' => 'Jour de l’an'],

				// 11 janvier - Manifeste de l’Indépendance du Maroc
				['day' => 11, 'month' => 1, 'label' => 'Manifeste de l’Indépendance'],

				// 1er mai - Fête du Travail
				['day' => 1, 'month' => 5, 'label' => 'Fête du Travail'],

				// 30 juillet - Fête du Trône
				['day' => 30, 'month' => 7, 'label' => 'Fête du Trône'],

				// 14 août - Commémoration de l’allégeance de l’oued Eddahab
				['day' => 14, 'month' => 8, 'label' => 'Allégeance Oued Eddahab'],

				// 20 août - Révolution du roi et du peuple
				['day' => 20, 'month' => 8, 'label' => 'Révolution du roi et du peuple'],

				// 21 août - Fête de la Jeunesse
				['day' => 21, 'month' => 8, 'label' => 'Fête de la Jeunesse'],

				// 6 novembre - La marche verte
				['day' => 6, 'month' => 11, 'label' => 'La marche verte'],

				// 18 novembre - Fête de l’indépendance
				['day' => 18, 'month' => 11, 'label' => 'Fête de l’indépendance'],

				// --- MAROC - Fêtes religieuses ---

				// 1er chawal - Aïd el-Fitr
				['day' => 1, 'month' => 10, 'calendar' => 'islamic', 'key' => 'aid_el_fitr', 'label' => 'Aïd el-Fitr'],

				// 10 dhou al-hijja - Aïd al-Adha
				['day' => 10, 'month' => 12, 'calendar' => 'islamic', 'key' => 'aid_al_adha', 'label' => 'Aïd al-Adha'],

				// 12 rabia al awal - Al-Mawlid
				['day' => 12, 'month' => 3, 'calendar' => 'islamic', 'key' => 'al_mawlid', 'label' => 'Al-Mawlid'],

				// 1er Mouharram - Jour de l’an hégire
				['day' => 1, 'month' => 1, 'calendar' => 'islamic', 'key' => 'jour_an_hegire', 'label' => 'Jour de l’an hégire'],
			]);
		}

		// ---------- FRANCE ----------
		if (in_array($country, ['FR', 'MQ', 'GP', 'RE'], true)) {
			// --- FRANCE - Fêtes civiles ---
			$listOfPublicHolidays = [
				// 1er janvier - Jour de l’an
				['day' => 1, 'month' => 1, 'label' => 'Jour de l’an'],

				// 1er mai - Fête du Travail
				['day' => 1, 'month' => 5, 'label' => 'Fête du Travail'],

				// 8 mai - Victoire des Alliés sur l’Allemagne nazie (8 mai 1945)
				['day' => 8, 'month' => 5, 'label' => 'Victoire des Alliés', 'nom_complet' => 'Victoire des Alliés sur l’Allemagne nazie (8 mai 1945)'],

				// 14 juillet - Fête nationale (France) (Fête de la Fédération 14 juillet 1790)
				['day' => 14, 'month' => 7, 'label' => 'Fête nationale', 'nom_complet' => 'Fête nationale française (Fête de la Fédération 14 juillet 1790)'],

				// 11 novembre - Armistice de la Première Guerre mondiale (11 novembre 1918)
				['day' => 11, 'month' => 11, 'label' => 'Armistice', 'nom_complet' => 'Armistice de la Première Guerre mondiale (11 novembre 1918)'],
			];

			// --- FRANCE - Fêtes religieuses ---

			// Vendredi saint (vendredi précédent Pâques)
			if (!empty($options['alsace']) && $options['alsace']) {
				$listOfPublicHolidays[] = ['key' => 'vendredi_saint', 'day' => $vendrediSaintDateTime->format('d'), 'month' => $vendrediSaintDateTime->format('m'), 'timestamp' => $vendrediSaintDateTime->getTimestamp(), 'label' => 'Vendredi saint'];
			}

			// Pâques
			$listOfPublicHolidays[] = ['key' => 'paques', 'day' => $easterDateTime->format('d'), 'month' => $easterDateTime->format('m'), 'timestamp' => $easterDateTime->getTimestamp(), 'label' => 'Pâques'];

			// Lundi de Pâques (1 jour après Pâques)
			$listOfPublicHolidays[] = ['key' => 'lundi_paques', 'day' => $lundiPaquesDateTime->format('d'), 'month' => $lundiPaquesDateTime->format('m'), 'timestamp' => $lundiPaquesDateTime->getTimestamp(), 'label' => 'Lundi de Pâques'];

			// Jeudi de l’Ascension (39 jours après Pâques)
			$listOfPublicHolidays[] = ['key' => 'ascension', 'day' => $jeudiAscensionDateTime->format('d'), 'month' => $jeudiAscensionDateTime->format('m'), 'timestamp' => $jeudiAscensionDateTime->getTimestamp(), 'label' => 'Ascension', 'nom_complet' => 'Jeudi de l’Ascension'];

			// Pentecôte (49 jours après Pâques)
			$listOfPublicHolidays[] = ['key' => 'pentecote', 'day' => $pentecoteDateTime->format('d'), 'month' => $pentecoteDateTime->format('m'), 'timestamp' => $pentecoteDateTime->getTimestamp(), 'label' => 'Pentecôte'];

			// Lundi de Pentecôte (50 jours après Pâques)
			$listOfPublicHolidays[] = ['key' => 'lundi_pentecote', 'day' => $lundiPentecoteDateTime->format('d'), 'month' => $lundiPentecoteDateTime->format('m'), 'timestamp' => $lundiPentecoteDateTime->getTimestamp(), 'label' => 'Lundi de Pentecôte'];

			// 15 août - Assomption
			$listOfPublicHolidays[] = ['day' => 15, 'month' => 8, 'label' => 'Assomption'];

			// 1er novembre - La Toussaint
			$listOfPublicHolidays[] = ['day' => 1, 'month' => 11, 'label' => 'La Toussaint'];

			// 25 décembre - Noël
			$listOfPublicHolidays[] = ['day' => 25, 'month' => 12, 'label' => 'Noël'];

			// 26 décembre - Saint-Étienne
			if ($options['alsace'] ?? false) {
				$listOfPublicHolidays[] = ['day' => 26, 'month' => 12, 'label' => 'Saint Étienne'];
			}

			// --- MARTINIQUE / GUADELOUPE ---

			if ('MQ' === $country || 'GP' === $country) {
				// Abolition de l’esclavage
				if ('MQ' === $country) {
					$listOfPublicHolidays[] = ['day' => 22, 'month' => 5, 'label' => 'Abolition de l’esclavage']; // Martinique
				}
				else {
					$listOfPublicHolidays[] = ['day' => 27, 'month' => 5, 'label' => 'Abolition de l’esclavage']; // Guadeloupe
				}

				// Fête Victor Schœlcher
				$listOfPublicHolidays[] = ['day' => 21, 'month' => 7, 'label' => 'Fête Victor Schœlcher'];

				// Défunts
				$listOfPublicHolidays[] = ['day' => 2, 'month' => 11, 'label' => 'Défunts'];

				// Mardi gras (47 jours avant Pâques)
				$mardiGrasDateTime = (clone $easterDateTime)->modify('-47 days');
				$listOfPublicHolidays[] = ['key' => 'mardi_gras', 'day' => $mardiGrasDateTime->format('d'), 'month' => $mardiGrasDateTime->format('m'), 'timestamp' => $mardiGrasDateTime->getTimestamp(), 'label' => 'Mardi gras'];

				// Mercredi des Cendres (1er jour du Carême) (46 jours avant Pâques)
				$mercrediDesCendresDateTime = (clone $easterDateTime)->modify('-46 days');
				$listOfPublicHolidays[] = ['key' => 'mercredi_des_cendres', 'day' => $mercrediDesCendresDateTime->format('d'), 'month' => $mercrediDesCendresDateTime->format('m'), 'timestamp' => $mercrediDesCendresDateTime->getTimestamp(), 'label' => 'Mercredi des Cendres'];

				// Mi-carême (24 jours avant Pâques)
				$miCaremeDateTime = (clone $easterDateTime)->modify('-24 days');
				$listOfPublicHolidays[] = ['key' => 'mi_careme', 'day' => $miCaremeDateTime->format('d'), 'month' => $miCaremeDateTime->format('m'), 'timestamp' => $miCaremeDateTime->getTimestamp(), 'label' => 'Mi-carême'];

				// Vendredi saint (2 jours avant Pâques)
				$listOfPublicHolidays[] = ['key' => 'vendredi_saint', 'day' => $vendrediSaintDateTime->format('d'), 'month' => $vendrediSaintDateTime->format('m'), 'timestamp' => $vendrediSaintDateTime->getTimestamp(), 'label' => 'Vendredi saint'];
			}

			// --- REUNION ---

			if ('RE' === $country) {
				// Abolition de l’esclavage
				$listOfPublicHolidays[] = ['day' => 20, 'month' => 12, 'label' => 'Abolition de l’esclavage'];

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