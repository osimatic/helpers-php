<?php

namespace Osimatic\Calendar;

/**
 * Utility class for managing public holidays across different countries.
 * This class provides methods to retrieve public holidays for various countries including civil holidays, religious holidays, and regional celebrations. It supports multiple calendar systems (Gregorian, Islamic Hijri, Indian) and allows customization through options for specific regions (e.g., Alsace-Moselle in France, DOM-TOM territories).
 */
class PublicHolidays
{
	// ========== Easter Calculation ==========

	/**
	 * Calculates the Easter date for a given year.
	 * Uses the PHP easter_days() function to calculate the number of days after March 21st when Easter occurs. Easter is a moveable feast in the Christian calendar, calculated based on the lunar calendar and the spring equinox. This method returns the exact DateTime for Easter Sunday in the Gregorian calendar.
	 * @param int $year The year for which to calculate Easter (must be a valid Gregorian calendar year)
	 * @return \DateTime The DateTime object representing Easter Sunday at midnight for the given year
	 */
	public static function getEasterDateTime(int $year): \DateTime
	{
		$base = new \DateTime("$year-03-21");
		$days = easter_days($year);
		return $base->add(new \DateInterval("P{$days}D"));
	}

	/**
	 * Calculates Good Friday date for a given year (2 days before Easter).
	 * @param int $year The year
	 * @return \DateTime Good Friday DateTime
	 */
	private static function getGoodFridayDateTime(int $year): \DateTime
	{
		return (clone self::getEasterDateTime($year))->modify('-2 days');
	}

	/**
	 * Calculates Easter Monday date for a given year (1 day after Easter).
	 * @param int $year The year
	 * @return \DateTime Easter Monday DateTime
	 */
	private static function getEasterMondayDateTime(int $year): \DateTime
	{
		return (clone self::getEasterDateTime($year))->modify('+1 days');
	}

	/**
	 * Calculates Ascension Thursday date for a given year (39 days after Easter).
	 * @param int $year The year
	 * @return \DateTime Ascension Thursday DateTime
	 */
	private static function getAscensionThursdayDateTime(int $year): \DateTime
	{
		return (clone self::getEasterDateTime($year))->modify('+39 days');
	}

	/**
	 * Calculates Pentecost Sunday date for a given year (49 days after Easter).
	 * @param int $year The year
	 * @return \DateTime Pentecost Sunday DateTime
	 */
	private static function getPentecostSundayDateTime(int $year): \DateTime
	{
		return (clone self::getEasterDateTime($year))->modify('+49 days');
	}

	/**
	 * Calculates Whit Monday date for a given year (50 days after Easter).
	 * @param int $year The year
	 * @return \DateTime Whit Monday DateTime
	 */
	private static function getWhitMondayDateTime(int $year): \DateTime
	{
		return (clone self::getEasterDateTime($year))->modify('+50 days');
	}

	// ========== Public Holiday Checking ==========

	/**
	 * Checks if a given date is a public holiday in a specific country.
	 * Retrieves the list of public holidays for the specified country and year, then checks if the provided date matches any of them. Supports multiple calendar systems (Gregorian, Hijri, Indian) and regional options (Alsace-Moselle, DOM-TOM, etc.).
	 * @param \DateTime $dateTime The date to check
	 * @param string $country The ISO 3166-1 alpha-2 country code (default: 'FR' for France)
	 * @param array<string, mixed> $options Optional configuration for regional holidays (supported keys: 'alsace' for Alsace-Moselle holidays, 'dom_tom' for French overseas territories, 'fetes_civiles' for civil celebrations, 'fetes_catholiques' for Catholic holidays, 'fetes_protestantes' for Protestant holidays)
	 * @return bool True if the date is a public holiday, false otherwise
	 */
	public static function isPublicHoliday(\DateTime $dateTime, string $country = 'FR', array $options = []): bool
	{
		$listOfPublicHolidays = self::getList($country, (int) $dateTime->format('Y'), $options);
		foreach ($listOfPublicHolidays as $publicHoliday) {
			if (self::isDateCorrespondingToPublicHoliday($publicHoliday, $dateTime)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Checks if a specific date corresponds to a given public holiday.
	 * Compares a date against a PublicHoliday object, handling different calendar systems (Gregorian, Hijri, Indian). For Hijri and Indian calendars, it converts the Gregorian date to the respective calendar system before comparison.
	 * @param PublicHoliday $publicHoliday The public holiday to check against
	 * @param \DateTime $dateTime The date to verify
	 * @return bool True if the date corresponds to the public holiday, false otherwise
	 */
	public static function isDateCorrespondingToPublicHoliday(PublicHoliday $publicHoliday, \DateTime $dateTime): bool
	{
		if ($publicHoliday->getCalendar() === PublicHolidayCalendar::HIJRI) {
			[, $hijriMonth, $hijriDay] = IslamicCalendar::convertGregorianDateToIslamicDate((int) $dateTime->format('Y'), (int) $dateTime->format('m'), (int) $dateTime->format('d'));
			return $publicHoliday->getMonth() === $hijriMonth && $publicHoliday->getDay() === $hijriDay;
		}

		if ($publicHoliday->getCalendar() === PublicHolidayCalendar::INDIAN) {
			[, $indianMonth, $indianDay] = IndianCalendar::convertGregorianDateToIndianDate((int) $dateTime->format('Y'), (int) $dateTime->format('m'), (int) $dateTime->format('d'));
			return $publicHoliday->getMonth() === $indianMonth && $publicHoliday->getDay() === $indianDay;
		}

		if (date('Y-m-d', $publicHoliday->getTimestamp()) === $dateTime->format('Y-m-d')) {
			return true;
		}
		return false;
	}

	// ========== Public Holiday Retrieval ==========

	/**
	 * Retrieves the complete list of public holidays for a specific country and year.
	 * Returns a deduplicated and chronologically sorted array of PublicHoliday objects for the specified country and year. The list includes civil holidays, religious holidays, and optionally regional celebrations based on the provided options. Duplicates are removed based on the holiday's unique key.
	 * @param string $country The ISO 3166-1 alpha-2 country code (default: 'FR')
	 * @param int $year The year for which to retrieve holidays
	 * @param array<string, mixed> $options Optional configuration array with supported keys: 'alsace' (bool) - includes holidays specific to Alsace-Moselle region, 'dom_tom' (bool) - includes holidays for French overseas territories, 'fetes_civiles' (bool) - includes non-public civil celebrations, 'fetes_catholiques' (bool) - includes non-public Catholic celebrations, 'fetes_protestantes' (bool) - includes non-public Protestant celebrations
	 * @return PublicHoliday[] Array of PublicHoliday objects sorted chronologically by date
	 */
	public static function getList(string $country, int $year, array $options = []): array
	{
		$list = \Osimatic\ArrayList\Arr::uniqueByCallback(
			self::getListOfCountry($country, $year, $options),
			static fn(PublicHoliday $publicHoliday) => $publicHoliday->getKey()
		);
		usort($list, static fn(PublicHoliday $publicHoliday1, PublicHoliday $publicHoliday2) => $publicHoliday1->getTimestamp() <=> $publicHoliday2->getTimestamp());
		return $list;
	}

	/**
	 * Gets the list of supported country codes.
	 * Returns all ISO 3166-1 alpha-2 country codes for which public holiday data is available in this class.
	 * @return string[] Array of supported country codes
	 */
	public static function getSupportedCountries(): array
	{
		return ['FR', 'BE', 'LU', 'CH', 'MU', 'MA', 'MQ', 'GP', 'RE', 'GF'];
	}

	/**
	 * Checks if a country code is supported.
	 * Verifies whether public holiday data is available for the specified country code.
	 * @param string $country The ISO 3166-1 alpha-2 country code to check
	 * @return bool True if the country is supported, false otherwise
	 */
	public static function isSupportedCountry(string $country): bool
	{
		return in_array(mb_strtoupper($country), self::getSupportedCountries(), true);
	}

	// ========== Country-Specific Holiday Lists ==========

	/**
	 * Retrieves the raw list of public holidays for a specific country.
	 * Internal method that returns country-specific public holidays without deduplication or sorting. Each country has its own set of civil holidays (national days, labor day, etc.) and religious holidays (Easter, Christmas, Islamic holidays, etc.). Some countries support regional variations through the options parameter.
	 * @param string $country The ISO 3166-1 alpha-2 country code
	 * @param int $year The year for which to retrieve holidays
	 * @param array<string, mixed> $options Configuration options for regional holidays
	 * @return PublicHoliday[] Unsorted array of PublicHoliday objects for the specified country
	 */
	private static function getListOfCountry(string $country, int $year, array $options = []): array
	{
		$country = mb_strtoupper($country);

		return match ($country) {
			'BE' => self::getBelgiumHolidays($year),
			'LU' => self::getLuxembourgHolidays($year),
			'CH' => self::getSwitzerlandHolidays($year),
			'MU' => self::getMauritiusHolidays($year),
			'MA' => self::getMoroccoHolidays($year),
			'FR', 'MQ', 'GP', 'RE', 'GF' => self::getFranceHolidays($country, $year, $options),
			default => [],
		};
	}

	// ========== Belgium Public Holidays ==========

	/**
	 * Returns the list of public holidays for Belgium.
	 * Includes Belgian national holidays (National Day on July 21, Armistice Day) and Christian holidays (Easter, Ascension, Assumption, All Saints' Day, Christmas).
	 * @param int $year The year
	 * @return PublicHoliday[] Array of Belgian public holidays
	 */
	private static function getBelgiumHolidays(int $year): array
	{
		return [
			// Civil Holidays
			new PublicHoliday('Jour de l’an', mktime(0, 0, 0, 1, 1, $year)), // New Year's Day
			new PublicHoliday('Fête du Travail', mktime(0, 0, 0, 5, 1, $year)), // Labor Day
			new PublicHoliday('Fête nationale', mktime(0, 0, 0, 7, 21, $year), fullName: 'Fête nationale belge'), // Belgian National Day
			new PublicHoliday('Fête de la communauté française', mktime(0, 0, 0, 9, 27, $year), fullName: 'Fête de la communauté française'), // French Community Festival
			new PublicHoliday('Armistice 1918', mktime(0, 0, 0, 11, 11, $year), fullName: 'Armistice de la Première Guerre mondiale (11 novembre 1918)'), // Armistice Day

			// Religious Holidays
			new PublicHoliday('Pâques', self::getEasterDateTime($year)->getTimestamp(), key: 'paques'), // Easter Sunday
			new PublicHoliday('Lundi de Pâques', self::getEasterMondayDateTime($year)->getTimestamp(), key: 'lundi_paques'), // Easter Monday
			new PublicHoliday('Ascension', self::getAscensionThursdayDateTime($year)->getTimestamp(), key: 'ascension', fullName: 'Jeudi de l’Ascension'), // Ascension Day
			new PublicHoliday('Pentecôte', self::getPentecostSundayDateTime($year)->getTimestamp(), key: 'pentecote'), // Pentecost
			new PublicHoliday('Lundi de Pentecôte', self::getWhitMondayDateTime($year)->getTimestamp(), key: 'lundi_pentecote'), // Whit Monday
			new PublicHoliday('Assomption', mktime(0, 0, 0, 8, 15, $year)), // Assumption of Mary
			new PublicHoliday('Toussaint', mktime(0, 0, 0, 11, 1, $year)), // All Saints' Day
			new PublicHoliday('Noël', mktime(0, 0, 0, 12, 25, $year)), // Christmas Day
		];
	}

	// ========== Luxembourg Public Holidays ==========

	/**
	 * Returns the list of public holidays for Luxembourg.
	 * Includes Luxembourg national holidays (Grand Duke's Birthday on June 23) and Christian holidays.
	 * @param int $year The year
	 * @return PublicHoliday[] Array of Luxembourg public holidays
	 */
	private static function getLuxembourgHolidays(int $year): array
	{
		return [
			// Civil Holidays
			new PublicHoliday('Jour de l’an', mktime(0, 0, 0, 1, 1, $year)), // New Year's Day
			new PublicHoliday('Fête du Travail', mktime(0, 0, 0, 5, 1, $year)), // Labor Day
			new PublicHoliday('Fête nationale', mktime(0, 0, 0, 6, 23, $year), fullName: 'Fête nationale luxembourgeoise'), // Luxembourg National Day

			// Religious Holidays
			new PublicHoliday('Pâques', self::getEasterDateTime($year)->getTimestamp(), key: 'paques'), // Easter Sunday
			new PublicHoliday('Lundi de Pâques', self::getEasterMondayDateTime($year)->getTimestamp(), key: 'lundi_paques'), // Easter Monday
			new PublicHoliday('Ascension', self::getAscensionThursdayDateTime($year)->getTimestamp(), key: 'ascension', fullName: 'Jeudi de l’Ascension'), // Ascension Day
			new PublicHoliday('Pentecôte', self::getPentecostSundayDateTime($year)->getTimestamp(), key: 'pentecote'), // Pentecost
			new PublicHoliday('Lundi de Pentecôte', self::getWhitMondayDateTime($year)->getTimestamp(), key: 'lundi_pentecote'), // Whit Monday
			new PublicHoliday('Assomption', mktime(0, 0, 0, 8, 15, $year)), // Assumption of Mary
			new PublicHoliday('Toussaint', mktime(0, 0, 0, 11, 1, $year)), // All Saints' Day
			new PublicHoliday('Noël', mktime(0, 0, 0, 12, 25, $year)), // Christmas Day
		];
	}

	// ========== Switzerland Public Holidays ==========

	/**
	 * Returns the list of public holidays for Switzerland.
	 * Swiss holidays vary by canton, but this method returns the most commonly observed federal and cantonal holidays. Includes Swiss National Day (August 1), various Christian holidays, and regional observances like Jeûne genevois and Jeûne fédéral.
	 * @param int $year The year
	 * @return PublicHoliday[] Array of Swiss public holidays
	 */
	private static function getSwitzerlandHolidays(int $year): array
	{
		$feteDieuDateTime = (clone self::getEasterDateTime($year))->modify('+60 days');
		$timestampJeuneGenevois = strtotime('sunday', mktime(0, 0, 0, 9, 1, $year)) + (4 * 24 * 3600);
		$timestampLundiJeuneFederal = strtotime('sunday', mktime(0, 0, 0, 9, 1, $year)) + (15 * 24 * 3600);

		return [
			// Civil Holidays
			new PublicHoliday('Jour de l’an', mktime(0, 0, 0, 1, 1, $year)), // New Year's Day
			new PublicHoliday('Instauration de la République', mktime(0, 0, 0, 3, 1, $year)), // Republic Day
			new PublicHoliday('Fête du Travail', mktime(0, 0, 0, 5, 1, $year)), // Labor Day
			new PublicHoliday('Commémoration du plébiscite', mktime(0, 0, 0, 6, 23, $year)), // Plebiscite Commemoration
			new PublicHoliday('Fête nationale', mktime(0, 0, 0, 8, 1, $year), fullName: 'Fête nationale suisse'), // Swiss National Day
			new PublicHoliday('Jeûne genevois', $timestampJeuneGenevois, key: 'jeune_genevois'), // Geneva Fast Day
			new PublicHoliday('Lundi du Jeûne fédéral', $timestampLundiJeuneFederal, key: 'jeune_federal'), // Federal Fast Monday
			new PublicHoliday('Restauration de la République', mktime(0, 0, 0, 12, 31, $year)), // Republic Restoration Day

			// Religious Holidays
			new PublicHoliday('Saint-Berchtold', mktime(0, 0, 0, 1, 2, $year)), // Berchtold's Day
			new PublicHoliday('Épiphanie', mktime(0, 0, 0, 1, 6, $year)), // Epiphany
			new PublicHoliday('Saint-Joseph', mktime(0, 0, 0, 3, 19, $year)), // Saint Joseph's Day
			new PublicHoliday('Vendredi saint', self::getGoodFridayDateTime($year)->getTimestamp(), key: 'vendredi_saint'), // Good Friday
			new PublicHoliday('Pâques', self::getEasterDateTime($year)->getTimestamp(), key: 'paques'), // Easter Sunday
			new PublicHoliday('Lundi de Pâques', self::getEasterMondayDateTime($year)->getTimestamp(), key: 'lundi_paques'), // Easter Monday
			new PublicHoliday('Ascension', self::getAscensionThursdayDateTime($year)->getTimestamp(), key: 'ascension', fullName: 'Jeudi de l’Ascension'), // Ascension Day
			new PublicHoliday('Pentecôte', self::getPentecostSundayDateTime($year)->getTimestamp(), key: 'pentecote'), // Pentecost
			new PublicHoliday('Lundi de Pentecôte', self::getWhitMondayDateTime($year)->getTimestamp(), key: 'lundi_pentecote'), // Whit Monday
			new PublicHoliday('Fête-Dieu', $feteDieuDateTime->getTimestamp(), key: 'fete_dieu'), // Corpus Christi
			new PublicHoliday('Saint-Pierre et Paul', mktime(0, 0, 0, 6, 29, $year)), // Saints Peter and Paul
			new PublicHoliday('Assomption', mktime(0, 0, 0, 8, 15, $year)), // Assumption of Mary
			new PublicHoliday('Fête de Saint-Nicolas-de-Flüe', mktime(0, 0, 0, 9, 25, $year)), // Saint Nicholas of Flüe
			new PublicHoliday('Toussaint', mktime(0, 0, 0, 11, 1, $year)), // All Saints' Day
			new PublicHoliday('Immaculée Conception', mktime(0, 0, 0, 12, 9, $year)), // Immaculate Conception
			new PublicHoliday('Noël', mktime(0, 0, 0, 12, 25, $year)), // Christmas Day
			new PublicHoliday('Saint-Étienne', mktime(0, 0, 0, 12, 26, $year)), // Saint Stephen's Day
		];
	}

	// ========== Mauritius Public Holidays ==========

	/**
	 * Returns the list of public holidays for Mauritius.
	 * Mauritius has a diverse religious population, so holidays include Christian holidays, Islamic holidays (using Hijri calendar), Hindu holidays (using Indian calendar), and Chinese holidays (Spring Festival). Also includes civil holidays like Independence Day and Abolition of Slavery Day.
	 * @param int $year The year
	 * @return PublicHoliday[] Array of Mauritian public holidays
	 */
	private static function getMauritiusHolidays(int $year): array
	{
		return [
			// Civil Holidays
			new PublicHoliday('Jour de l’an', mktime(0, 0, 0, 1, 1, $year)), // New Year's Day
			new PublicHoliday('Jour de l’an', mktime(0, 0, 0, 1, 2, $year)), // New Year's Day (2nd day)
			new PublicHoliday('Abolition de l’esclavage', mktime(0, 0, 0, 2, 1, $year)), // Abolition of Slavery
			new PublicHoliday('Fête nationale', mktime(0, 0, 0, 3, 12, $year)), // Independence Day
			new PublicHoliday('Fête du Travail', mktime(0, 0, 0, 5, 1, $year)), // Labor Day
			new PublicHoliday('Toussaint', mktime(0, 0, 0, 11, 1, $year)), // All Saints' Day
			new PublicHoliday('Arrivée des Travailleurs engagés', mktime(0, 0, 0, 11, 2, $year)), // Arrival of Indentured Laborers
			new PublicHoliday('Noël', mktime(0, 0, 0, 12, 25, $year)), // Christmas Day

			// Islamic Holidays (Hijri Calendar)
			new PublicHoliday('Aïd el-Fitr', IslamicCalendar::getTimestamp($year, 10, 1), key: 'aid_el_fitr', calendar: PublicHolidayCalendar::HIJRI), // End of Ramadan

			// Chinese Holidays
			new PublicHoliday('Fête du printemps', 0, key: 'fete_du_printemps'), // Chinese New Year / Spring Festival

			// Hindu Holidays (Indian Calendar)
			new PublicHoliday('Thaipoosam Cavadee', 0, key: 'thaipoosam_cavadee', calendar: PublicHolidayCalendar::INDIAN), // Tamil Festival
			new PublicHoliday('Maha Shivaratree', 0, key: 'maha_shivaratree', calendar: PublicHolidayCalendar::INDIAN), // Great Night of Shiva
			new PublicHoliday('Ugaadi', IndianCalendar::getTimestamp($year, 1, 1), key: 'ugaadi', calendar: PublicHolidayCalendar::INDIAN), // Hindu New Year
			new PublicHoliday('Ganesh Chaturthi', 0, key: 'ganesh_chaturthi', calendar: PublicHolidayCalendar::INDIAN), // Ganesh Festival
			new PublicHoliday('Divali', 0, key: 'divali', calendar: PublicHolidayCalendar::INDIAN), // Festival of Lights
		];
	}

	// ========== Morocco Public Holidays ==========

	/**
	 * Returns the list of public holidays for Morocco.
	 * Includes Moroccan national holidays (Throne Day, Green March, Independence Day) and Islamic religious holidays using the Hijri calendar (Eid al-Fitr, Eid al-Adha, Mawlid, Islamic New Year).
	 * @param int $year The year
	 * @return PublicHoliday[] Array of Moroccan public holidays
	 */
	private static function getMoroccoHolidays(int $year): array
	{
		// Convert Gregorian year to Hijri year for Islamic holidays
		// We check mid-year (July 1) to get the appropriate Hijri year
		[$hijriYear, ,] = IslamicCalendar::convertGregorianDateToIslamicDate($year, 7, 1);

		$listOfPublicHolidays = [
			// Civil Holidays
			new PublicHoliday('Jour de l’an', mktime(0, 0, 0, 1, 1, $year)), // New Year's Day
			new PublicHoliday('Manifeste de l’Indépendance', mktime(0, 0, 0, 1, 11, $year)), // Independence Manifesto
			new PublicHoliday('Fête du Travail', mktime(0, 0, 0, 5, 1, $year)), // Labor Day
			new PublicHoliday('Fête du Trône', mktime(0, 0, 0, 7, 30, $year)), // Throne Day
			new PublicHoliday('Allégeance Oued Eddahab', mktime(0, 0, 0, 8, 14, $year)), // Oued Eddahab Allegiance Day
			new PublicHoliday('Révolution du roi et du peuple', mktime(0, 0, 0, 8, 20, $year)), // Revolution of the King and the People
			new PublicHoliday('Fête de la Jeunesse', mktime(0, 0, 0, 8, 21, $year)), // Youth Day
			new PublicHoliday('La marche verte', mktime(0, 0, 0, 11, 6, $year)), // Green March
			new PublicHoliday('Fête de l’Indépendance', mktime(0, 0, 0, 11, 18, $year)), // Independence Day
		];

		// Islamic Holidays (Hijri Calendar)
		// Islamic holidays can span two Hijri years within a single Gregorian year
		// We calculate holidays for both potential Hijri years and filter those within the requested Gregorian year
		$startOfYear = mktime(0, 0, 0, 1, 1, $year);
		$endOfYear = mktime(23, 59, 59, 12, 31, $year);

		// Calculate Islamic holidays for the current Hijri year and the previous one
		$islamicHolidays = [
			['name' => 'Aïd el-Fitr', 'key' => 'aid_el_fitr', 'month' => 10, 'day' => 1], // End of Ramadan
			['name' => 'Aïd al-Adha', 'key' => 'aid_al_adha', 'month' => 12, 'day' => 10], // Festival of Sacrifice
			['name' => 'Jour de l’an hégire', 'key' => 'jour_an_hegire', 'month' => 1, 'day' => 1], // Islamic New Year
			['name' => 'Al-Mawlid', 'key' => 'al_mawlid', 'month' => 3, 'day' => 12], // Prophet's Birthday
		];

		foreach ($islamicHolidays as $holiday) {
			// Try the previous, current and next Hijri year to cover all possibilities
			foreach ([$hijriYear - 1, $hijriYear, $hijriYear + 1] as $tryYear) {
				$timestamp = IslamicCalendar::getTimestamp($tryYear, $holiday['month'], $holiday['day']);

				// Only add if it falls within the requested Gregorian year
				if ($timestamp >= $startOfYear && $timestamp <= $endOfYear) {
					$listOfPublicHolidays[] = new PublicHoliday(
						$holiday['name'],
						$timestamp,
						key: $holiday['key'],
						calendar: PublicHolidayCalendar::HIJRI
					);
					break; // Only add once per holiday
				}
			}
		}

		return $listOfPublicHolidays;
	}

	// ========== France and French Territories Public Holidays ==========

	/**
	 * Returns the list of public holidays for France and its overseas territories.
	 * France has national holidays like Bastille Day (July 14), Victory Day (May 8), Armistice Day (November 11), and Christian holidays. Supports regional variations: Alsace-Moselle has additional holidays (Good Friday, Saint Stephen's Day), French overseas territories (Martinique, Guadeloupe, Réunion, French Guiana) have specific Abolition of Slavery dates and carnival celebrations.
	 * @param string $country Country code (FR, MQ, GP, RE, GF)
	 * @param int $year The year
	 * @param array<string, mixed> $options Configuration options
	 * @return PublicHoliday[] Array of French public holidays
	 */
	private static function getFranceHolidays(string $country, int $year, array $options): array
	{
		// Civil Holidays (Metropolitan France and all territories)
		$listOfPublicHolidays = [
			new PublicHoliday('Jour de l’an', mktime(0, 0, 0, 1, 1, $year)), // New Year's Day
			new PublicHoliday('Fête du Travail', mktime(0, 0, 0, 5, 1, $year)), // Labor Day
			new PublicHoliday('Victoire des Alliés', mktime(0, 0, 0, 5, 8, $year), fullName: 'Victoire des Alliés sur l’Allemagne nazie (8 mai 1945)'), // Victory in Europe Day
			new PublicHoliday('Fête nationale', mktime(0, 0, 0, 7, 14, $year), fullName: 'Fête nationale française (Fête de la Fédération 14 juillet 1790)'), // Bastille Day
			new PublicHoliday('Armistice', mktime(0, 0, 0, 11, 11, $year), fullName: 'Armistice de la Première Guerre mondiale (11 novembre 1918)'), // Armistice Day
		];

		// Religious Holidays

		// Good Friday (Alsace-Moselle only)
		if ($options['alsace'] ?? false) {
			$listOfPublicHolidays[] = new PublicHoliday('Vendredi saint', self::getGoodFridayDateTime($year)->getTimestamp(), key: 'vendredi_saint'); // Good Friday
		}

		// Easter and Easter-related holidays
		$listOfPublicHolidays[] = new PublicHoliday('Pâques', self::getEasterDateTime($year)->getTimestamp(), key: 'paques'); // Easter Sunday
		$listOfPublicHolidays[] = new PublicHoliday('Lundi de Pâques', self::getEasterMondayDateTime($year)->getTimestamp(), key: 'lundi_paques'); // Easter Monday
		$listOfPublicHolidays[] = new PublicHoliday('Ascension', self::getAscensionThursdayDateTime($year)->getTimestamp(), key: 'ascension', fullName: 'Jeudi de l’Ascension'); // Ascension Day
		$listOfPublicHolidays[] = new PublicHoliday('Pentecôte', self::getPentecostSundayDateTime($year)->getTimestamp(), key: 'pentecote'); // Pentecost
		$listOfPublicHolidays[] = new PublicHoliday('Lundi de Pentecôte', self::getWhitMondayDateTime($year)->getTimestamp(), key: 'lundi_pentecote'); // Whit Monday

		// Other Christian holidays
		$listOfPublicHolidays[] = new PublicHoliday('Assomption', mktime(0, 0, 0, 8, 15, $year)); // Assumption of Mary
		$listOfPublicHolidays[] = new PublicHoliday('Toussaint', mktime(0, 0, 0, 11, 1, $year)); // All Saints' Day
		$listOfPublicHolidays[] = new PublicHoliday('Noël', mktime(0, 0, 0, 12, 25, $year)); // Christmas Day

		// Saint Stephen's Day (Alsace-Moselle only)
		if ($options['alsace'] ?? false) {
			$listOfPublicHolidays[] = new PublicHoliday('Saint Étienne', mktime(0, 0, 0, 12, 26, $year)); // Saint Stephen's Day
		}

		// Martinique / Guadeloupe specific holidays
		if ($country === 'MQ' || $country === 'GP') {
			// Abolition of Slavery (different dates)
			if ($country === 'MQ') {
				$listOfPublicHolidays[] = new PublicHoliday('Abolition de l’esclavage', mktime(0, 0, 0, 5, 22, $year)); // Martinique
			} else {
				$listOfPublicHolidays[] = new PublicHoliday('Abolition de l’esclavage', mktime(0, 0, 0, 5, 27, $year)); // Guadeloupe
			}

			// Common holidays for both territories
			$listOfPublicHolidays[] = new PublicHoliday('Fête Victor Schœlcher', mktime(0, 0, 0, 7, 21, $year)); // Victor Schœlcher Day
			$listOfPublicHolidays[] = new PublicHoliday('Défunts', mktime(0, 0, 0, 11, 2, $year)); // All Souls' Day

			// Carnival season
			$easterDateTime = self::getEasterDateTime($year);
			$mardiGrasDateTime = (clone $easterDateTime)->modify('-47 days');
			$listOfPublicHolidays[] = new PublicHoliday('Mardi gras', $mardiGrasDateTime->getTimestamp(), key: 'mardi_gras'); // Shrove Tuesday

			$mercrediDesCendresDateTime = (clone $easterDateTime)->modify('-46 days');
			$listOfPublicHolidays[] = new PublicHoliday('Mercredi des Cendres', $mercrediDesCendresDateTime->getTimestamp(), key: 'mercredi_des_cendres'); // Ash Wednesday

			$miCaremeDateTime = (clone $easterDateTime)->modify('-24 days');
			$listOfPublicHolidays[] = new PublicHoliday('Mi-carême', $miCaremeDateTime->getTimestamp(), key: 'mi_careme'); // Mid-Lent

			$listOfPublicHolidays[] = new PublicHoliday('Vendredi saint', self::getGoodFridayDateTime($year)->getTimestamp(), key: 'vendredi_saint'); // Good Friday
		}

		// Réunion specific holidays
		if ($country === 'RE') {
			$listOfPublicHolidays[] = new PublicHoliday('Abolition de l’esclavage', mktime(0, 0, 0, 12, 20, $year)); // Abolition of Slavery (Réunion)
		}

		// French Guiana specific holidays
		if ($country === 'GF') {
			$listOfPublicHolidays[] = new PublicHoliday('Abolition de l’esclavage', mktime(0, 0, 0, 6, 10, $year)); // Abolition of Slavery (French Guiana)
		}

		return $listOfPublicHolidays;
	}
}