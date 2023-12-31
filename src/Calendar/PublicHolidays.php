<?php

namespace Osimatic\Helpers\Calendar;

class PublicHolidays
{
	/**
	 * @param int $year
	 * @return \DateTime
	 */
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
			if (self::isDateCorrespondingToPublicHoliday($publicHoliday, $dateTime)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param PublicHoliday $publicHoliday
	 * @param \DateTime $dateTime
	 * @return bool
	 */
	public static function isDateCorrespondingToPublicHoliday(PublicHoliday $publicHoliday, \DateTime $dateTime): bool
	{
		if ($publicHoliday->getCalendar() === PublicHolidayCalendar::HIJRI) {
			[, $hijriMonth, $hijriDay] = IslamicCalendar::convertGregorianDateToIslamicDate($dateTime->format('Y'), $dateTime->format('m'), $dateTime->format('d'));
			if ($publicHoliday->getMonth() === $hijriMonth && $publicHoliday->getDay() === $hijriDay) {
				return true;
			}
			return false;
		}

		if (date('Y-m-d', $publicHoliday->getTimestamp()) === $dateTime->format('Y-m-d')) {
			return true;
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
	 * @return PublicHoliday[]
	 */
	public static function getList(string $country, int $year, array $options=[]): array
	{
		return \Osimatic\Helpers\ArrayList\Arr::collection_array_unique(self::getListOfCountry($country, $year, $options), fn(PublicHoliday $publicHoliday) => $publicHoliday->getKey());
	}

	/**
	 * @param string $country
	 * @param int $year
	 * @param array $options
	 * @return PublicHoliday[]
	 */
	private static function getListOfCountry(string $country, int $year, array $options=[]): array
	{
		$country = mb_strtoupper($country);

		//$easterDateTime = (new \DateTime('@'.easter_date($year)))->setTimezone(new \DateTimeZone($timeZone));
		$easterDateTime = self::getEasterDateTime($year);

		$vendrediSaintDateTime = (clone $easterDateTime)->modify('-2 days');
		$lundiPaquesDateTime = (clone $easterDateTime)->modify('+1 days');
		$jeudiAscensionDateTime = (clone $easterDateTime)->modify('+39 days');
		$pentecoteDateTime = (clone $easterDateTime)->modify('+49 days');
		$lundiPentecoteDateTime = (clone $easterDateTime)->modify('+50 days');

		// ---------- BELGIQUE ----------
		if ('BE' === $country) {
			return [
				// --- BELGIQUE - Fêtes civiles ---

				// 1er janvier - Jour de l’an
				new PublicHoliday('Jour de l’an', mktime(0, 0, 0, 1, 1, $year)),

				// 1er mai - Fête du Travail
				new PublicHoliday('Fête du Travail', mktime(0, 0, 0, 5, 1, $year)),

				// 21 juillet - Fête nationale (Belgique)
				new PublicHoliday('Fête nationale', mktime(0, 0, 0, 7, 21, $year), fullName: 'Fête nationale belge'),

				// 27 septembre - Fête de la communauté française
				new PublicHoliday('Fête de la communauté française', mktime(0, 0, 0, 9, 27, $year), fullName: 'Fête de la communauté française'),

				// 11 novembre - Armistice de la Première Guerre mondiale (11 novembre 1918)
				new PublicHoliday('Armistice 1918', mktime(0, 0, 0, 11, 11, $year), fullName: 'Armistice de la Première Guerre mondiale (11 novembre 1918)'),

				// --- BELGIQUE - Fêtes religieuses ---

				// Pâques
				new PublicHoliday('Pâques', $easterDateTime->getTimestamp(), key: 'paques'),

				// Lundi de Pâques (1 jour après Pâques)
				new PublicHoliday('Lundi de Pâques', $lundiPaquesDateTime->getTimestamp(), key: 'lundi_paques'),

				// Jeudi de l’Ascension (39 jours après Pâques)
				new PublicHoliday('Ascension', $jeudiAscensionDateTime->getTimestamp(), key: 'ascension', fullName: 'Jeudi de l’Ascension'),

				// Pentecôte (49 jours après Pâques)
				new PublicHoliday('Pentecôte', $pentecoteDateTime->getTimestamp(), key: 'pentecote'),

				// Lundi de Pentecôte (50 jours après Pâques)
				new PublicHoliday('Lundi de Pentecôte', $lundiPentecoteDateTime->getTimestamp(), key: 'lundi_pentecote'),

				// 15 août - Assomption
				new PublicHoliday('Assomption', mktime(0, 0, 0, 8, 15, $year)),

				// 1er novembre - Toussaint
				new PublicHoliday('Toussaint', mktime(0, 0, 0, 11, 1, $year)),

				// 25 décembre - Noël
				new PublicHoliday('Noël', mktime(0, 0, 0, 12, 25, $year)),
			];
		}

		// ---------- LUXEMBOURG ----------
		if ('LU' === $country) {
			return [
				// --- LUXEMBOURG - Fêtes civiles ---

				// 1er janvier - Jour de l’an
				new PublicHoliday('Jour de l’an', mktime(0, 0, 0, 1, 1, $year)),

				// 1er mai - Fête du Travail
				new PublicHoliday('Fête du Travail', mktime(0, 0, 0, 5, 1, $year)),

				// 23 juin - Fête nationale (Luxembourg) (célébration de l’anniversaire de SAR le Grand-Duc)
				new PublicHoliday('Fête nationale', mktime(0, 0, 0, 6, 23, $year), fullName: 'Fête nationale luxembourgeoise'),

				// --- LUXEMBOURG - Fêtes religieuses ---

				// Pâques
				new PublicHoliday('Pâques', $easterDateTime->getTimestamp(), key: 'paques'),

				// Lundi de Pâques (1 jour après Pâques)
				new PublicHoliday('Lundi de Pâques', $lundiPaquesDateTime->getTimestamp(), key: 'lundi_paques'),

				// Jeudi de l’Ascension (39 jours après Pâques)
				new PublicHoliday('Ascension', $jeudiAscensionDateTime->getTimestamp(), key: 'ascension', fullName: 'Jeudi de l’Ascension'),

				// Pentecôte (49 jours après Pâques)
				new PublicHoliday('Pentecôte', $pentecoteDateTime->getTimestamp(), key: 'pentecote'),

				// Lundi de Pentecôte (50 jours après Pâques)
				new PublicHoliday('Lundi de Pentecôte', $lundiPentecoteDateTime->getTimestamp(), key: 'lundi_pentecote'),

				// 15 août - Assomption
				new PublicHoliday('Assomption', mktime(0, 0, 0, 8, 15, $year)),

				// 1er novembre - Toussaint
				new PublicHoliday('Toussaint', mktime(0, 0, 0, 11, 1, $year)),

				// 25 décembre - Noël
				new PublicHoliday('Noël', mktime(0, 0, 0, 12, 25, $year)),
			];
		}

		// ---------- SUISSE ----------
		// https://fr.wikipedia.org/wiki/Jours_f%C3%A9ri%C3%A9s_en_Suisse
		if ('CH' === $country) {
			$feteDieuDateTime = (clone $easterDateTime)->modify('+60 days');
			$timestampJeuneGenevois = strtotime('sunday', mktime(0, 0, 0, 9, 1, $year))+(4*24*3600);
			$timestampLundiJeuneFederal = strtotime('sunday', mktime(0, 0, 0, 9, 1, $year))+(15*24*3600);

			return [
				// --- SUISSE - Fêtes civiles ---

				// 1er janvier - Jour de l’an
				new PublicHoliday('Jour de l’an', mktime(0, 0, 0, 1, 1, $year)),

				// 1er mars - Instauration de la République
				new PublicHoliday('Instauration de la République', mktime(0, 0, 0, 3, 1, $year)),

				// 1er mai - Fête du Travail
				new PublicHoliday('Fête du Travail', mktime(0, 0, 0, 5, 1, $year)),

				// 23 juin - Commémoration du plébiscite
				new PublicHoliday('Commémoration du plébiscite', mktime(0, 0, 0, 6, 23, $year)),

				// 1er août - Fête nationale (Suisse)
				new PublicHoliday('Fête nationale', mktime(0, 0, 0, 8, 1, $year), fullName: 'Fête nationale suisse'),

				// Jeûne genevois (jeudi suivant le 1er dimanche de septembre)
				new PublicHoliday('Jeûne genevois', $timestampJeuneGenevois, key: 'jeune_genevois'),

				// Lundi du Jeûne fédéral (lundi suivant le 3e dimanche de septembre)
				new PublicHoliday('Lundi du Jeûne fédéral', $timestampLundiJeuneFederal, key: 'jeune_federal'),

				// 31 décembre - Restauration de la République
				new PublicHoliday('Restauration de la République', mktime(0, 0, 0, 12, 31, $year)),

				// --- SUISSE - Fêtes religieuses ---

				// 2 janvier - Saint-Berchtold
				new PublicHoliday('Saint-Berchtold', mktime(0, 0, 0, 1, 2, $year)),

				// 6 janvier - Épiphanie
				new PublicHoliday('Épiphanie', mktime(0, 0, 0, 1, 6, $year)),

				// 19 mars - Saint-Joseph
				new PublicHoliday('Saint-Joseph', mktime(0, 0, 0, 3, 19, $year)),

				// 1er jeudi d'avril - Fahrtsfest
				//new PublicHoliday('Fahrtsfest', ), // todo

				// Vendredi saint (2 jours avant Pâques)
				new PublicHoliday('Vendredi saint', $vendrediSaintDateTime->getTimestamp(), key: 'vendredi_saint'),

				// Pâques
				new PublicHoliday('Pâques', $easterDateTime->getTimestamp(), key: 'paques'),

				// Lundi de Pâques (1 jour après Pâques)
				new PublicHoliday('Lundi de Pâques', $lundiPaquesDateTime->getTimestamp(), key: 'lundi_paques'),

				// Jeudi de l’Ascension (39 jours après Pâques)
				new PublicHoliday('Ascension', $jeudiAscensionDateTime->getTimestamp(), key: 'ascension', fullName: 'Jeudi de l’Ascension'),

				// Pentecôte (49 jours après Pâques)
				new PublicHoliday('Pentecôte', $pentecoteDateTime->getTimestamp(), key: 'pentecote'),

				// Lundi de Pentecôte (50 jours après Pâques)
				new PublicHoliday('Lundi de Pentecôte', $lundiPentecoteDateTime->getTimestamp(), key: 'lundi_pentecote'),

				// Fête-Dieu (60 jours après Pâques)
				new PublicHoliday('Fête-Dieu', $feteDieuDateTime->getTimestamp(), key: 'fete_dieu'),

				// 29 juin - Saint-Pierre et Paul
				new PublicHoliday('Saint-Pierre et Paul', mktime(0, 0, 0, 6, 29, $year)),

				// 15 août - Assomption
				new PublicHoliday('Assomption', mktime(0, 0, 0, 8, 15, $year)),

				// 25 septembre - Fête de Saint-Nicolas-de-Flüe
				new PublicHoliday('Fête de Saint-Nicolas-de-Flüe', mktime(0, 0, 0, 9, 25, $year)),

				// 1er novembre - Toussaint
				new PublicHoliday('Toussaint', mktime(0, 0, 0, 11, 1, $year)),

				// 8 décembre - Immaculée Conception
				new PublicHoliday('Immaculée Conception', mktime(0, 0, 0, 12, 9, $year)),

				// 25 décembre - Noël
				new PublicHoliday('Noël', mktime(0, 0, 0, 12, 25, $year)),

				// 26 décembre - Saint-Étienne
				new PublicHoliday('Saint-Étienne', mktime(0, 0, 0, 12, 26, $year)),
			];
		}

		// ---------- MAROC ----------
		if ('MA' === $country) {
			return [
				// --- MAROC - Fêtes civiles ---

				// 1er janvier - Jour de l’an
				new PublicHoliday('Jour de l’an', mktime(0, 0, 0, 1, 1, $year)),

				// 11 janvier - Manifeste de l’Indépendance du Maroc
				new PublicHoliday('Manifeste de l’Indépendance', mktime(0, 0, 0, 1, 11, $year)),

				// 1er mai - Fête du Travail
				new PublicHoliday('Fête du Travail', mktime(0, 0, 0, 5, 1, $year)),

				// 30 juillet - Fête du Trône
				new PublicHoliday('Fête du Trône', mktime(0, 0, 0, 7, 30, $year)),

				// 14 août - Commémoration de l’allégeance de l’oued Eddahab
				new PublicHoliday('Allégeance Oued Eddahab', mktime(0, 0, 0, 8, 14, $year)),

				// 20 août - Révolution du roi et du peuple
				new PublicHoliday('Révolution du roi et du peuple', mktime(0, 0, 0, 8, 20, $year)),

				// 21 août - Fête de la Jeunesse
				new PublicHoliday('Fête de la Jeunesse', mktime(0, 0, 0, 8, 21, $year)),

				// 6 novembre - La marche verte
				new PublicHoliday('La marche verte', mktime(0, 0, 0, 11, 6, $year)),

				// 18 novembre - Fête de l’indépendance
				new PublicHoliday('Fête de l’indépendance', mktime(0, 0, 0, 11, 18, $year)),

				// --- MAROC - Fêtes religieuses ---

				// 1er chawal - Aïd el-Fitr
				new PublicHoliday('Aïd el-Fitr', IslamicCalendar::getTimestamp($year, 10, 1), key: 'aid_el_fitr', calendar: PublicHolidayCalendar::HIJRI),

				// 10 dhou al-hijja - Aïd al-Adha
				new PublicHoliday('Aïd al-Adha', IslamicCalendar::getTimestamp($year, 12, 10), key: 'aid_al_adha', calendar: PublicHolidayCalendar::HIJRI),

				// 12 rabia al awal - Al-Mawlid
				new PublicHoliday('Al-Mawlid', IslamicCalendar::getTimestamp($year, 3, 12), key: 'al_mawlid', calendar: PublicHolidayCalendar::HIJRI),

				// 1er Mouharram - Jour de l’an hégire
				new PublicHoliday('Jour de l’an hégire', IslamicCalendar::getTimestamp($year, 1, 1), key: 'jour_an_hegire', calendar: PublicHolidayCalendar::HIJRI),
			];
		}

		// ---------- FRANCE ----------
		if (in_array($country, ['FR', 'MQ', 'GP', 'RE'], true)) {
			// --- FRANCE - Fêtes civiles ---
			$listOfPublicHolidays = [
				// 1er janvier - Jour de l’an
				new PublicHoliday('Jour de l’an', mktime(0, 0, 0, 1, 1, $year)),

				// 1er mai - Fête du Travail
				new PublicHoliday('Fête du Travail', mktime(0, 0, 0, 5, 1, $year)),

				// 8 mai - Victoire des Alliés sur l’Allemagne nazie (8 mai 1945)
				new PublicHoliday('Victoire des Alliés', mktime(0, 0, 0, 5, 8, $year), fullName: 'Victoire des Alliés sur l’Allemagne nazie (8 mai 1945)'),

				// 14 juillet - Fête nationale (France) (Fête de la Fédération 14 juillet 1790)
				new PublicHoliday('Fête nationale', mktime(0, 0, 0, 7, 14, $year), fullName: 'Fête nationale française (Fête de la Fédération 14 juillet 1790)'),

				// 11 novembre - Armistice de la Première Guerre mondiale (11 novembre 1918)
				new PublicHoliday('Armistice', mktime(0, 0, 0, 11, 11, $year), fullName: 'Armistice de la Première Guerre mondiale (11 novembre 1918)'),
			];

			// --- FRANCE - Fêtes religieuses ---

			// Vendredi saint (vendredi précédent Pâques)
			if (!empty($options['alsace']) && $options['alsace']) {
				$listOfPublicHolidays[] = new PublicHoliday('Vendredi saint', $vendrediSaintDateTime->getTimestamp(), key: 'vendredi_saint');
			}

			// Pâques
			$listOfPublicHolidays[] = new PublicHoliday('Pâques', $easterDateTime->getTimestamp(), key: 'paques');

			// Lundi de Pâques (1 jour après Pâques)
			$listOfPublicHolidays[] = new PublicHoliday('Lundi de Pâques', $lundiPaquesDateTime->getTimestamp(), key: 'lundi_paques');

			// Jeudi de l’Ascension (39 jours après Pâques)
			$listOfPublicHolidays[] = new PublicHoliday('Ascension', $jeudiAscensionDateTime->getTimestamp(), key: 'ascension', fullName: 'Jeudi de l’Ascension');

			// Pentecôte (49 jours après Pâques)
			$listOfPublicHolidays[] = new PublicHoliday('Pentecôte', $pentecoteDateTime->getTimestamp(), key: 'pentecote');

			// Lundi de Pentecôte (50 jours après Pâques)
			$listOfPublicHolidays[] = new PublicHoliday('Lundi de Pentecôte', $lundiPentecoteDateTime->getTimestamp(), key: 'lundi_pentecote');

			// 15 août - Assomption
			$listOfPublicHolidays[] = new PublicHoliday('Assomption', mktime(0, 0, 0, 8, 15, $year));

			// 1er novembre - La Toussaint
			$listOfPublicHolidays[] = new PublicHoliday('Toussaint', mktime(0, 0, 0, 11, 1, $year));

			// 25 décembre - Noël
			$listOfPublicHolidays[] = new PublicHoliday('Noël', mktime(0, 0, 0, 12, 25, $year));

			// 26 décembre - Saint-Étienne
			if ($options['alsace'] ?? false) {
				$listOfPublicHolidays[] = new PublicHoliday('Saint Étienne', mktime(0, 0, 0, 12, 26, $year));
			}

			// --- MARTINIQUE / GUADELOUPE ---

			if ('MQ' === $country || 'GP' === $country) {
				// Abolition de l’esclavage
				if ('MQ' === $country) {
					$listOfPublicHolidays[] = new PublicHoliday('Abolition de l’esclavage', mktime(0, 0, 0, 5, 22, $year)); // Martinique
				}
				else {
					$listOfPublicHolidays[] = new PublicHoliday('Abolition de l’esclavage', mktime(0, 0, 0, 5, 27, $year)); // Guadeloupe
				}

				// Fête Victor Schœlcher
				$listOfPublicHolidays[] = new PublicHoliday('Fête Victor Schœlcher', mktime(0, 0, 0, 7, 21, $year));

				// Défunts
				$listOfPublicHolidays[] = new PublicHoliday('Défunts', mktime(0, 0, 0, 11, 2, $year));

				// Mardi gras (47 jours avant Pâques)
				$mardiGrasDateTime = (clone $easterDateTime)->modify('-47 days');
				$listOfPublicHolidays[] = new PublicHoliday('Mardi gras', $mardiGrasDateTime->getTimestamp(), key: 'mardi_gras');

				// Mercredi des Cendres (1er jour du Carême) (46 jours avant Pâques)
				$mercrediDesCendresDateTime = (clone $easterDateTime)->modify('-46 days');
				$listOfPublicHolidays[] = new PublicHoliday('Mercredi des Cendres', $mercrediDesCendresDateTime->getTimestamp(), key: 'mercredi_des_cendres');

				// Mi-carême (24 jours avant Pâques)
				$miCaremeDateTime = (clone $easterDateTime)->modify('-24 days');
				$listOfPublicHolidays[] = new PublicHoliday('Mi-carême', $miCaremeDateTime->getTimestamp(), key: 'mi_careme');

				// Vendredi saint (2 jours avant Pâques)
				$listOfPublicHolidays[] = new PublicHoliday('Vendredi saint', $vendrediSaintDateTime->getTimestamp(), key: 'vendredi_saint');
			}

			// --- REUNION ---

			if ('RE' === $country) {
				// Abolition de l’esclavage
				$listOfPublicHolidays[] = new PublicHoliday('Abolition de l’esclavage', mktime(0, 0, 0, 12, 20, $year));

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

			return $listOfPublicHolidays;
		}

		return [];
	}
}