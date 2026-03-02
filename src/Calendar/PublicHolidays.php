<?php

namespace Osimatic\Calendar;

/**
 * Utility class for managing public holidays across different countries.
 * This class provides methods to retrieve public holidays for various countries including civil holidays, religious holidays, and regional celebrations. It supports multiple calendar systems (Gregorian, Islamic Hijri, Indian) and allows customization through options for specific regions (e.g., Alsace-Moselle in France, DOM-TOM territories). Holiday data is loaded from YAML files located in conf/holidays/.
 * @link https://en.wikipedia.org/wiki/Public_holiday
 */
class PublicHolidays
{
	// ========== Cache ==========

	/**
	 * Static cache for loaded YAML holiday files to avoid re-parsing on each call.
	 * @var array<string, array<mixed>>
	 */
	private static array $yamlCache = [];

	/**
	 * Static cache for the list of supported country codes, derived from available YAML files.
	 * @var string[]|null
	 */
	private static ?array $supportedCountriesCache = null;

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
	 * Auto-detects supported countries from the available YAML files in conf/holidays/. The result is cached for subsequent calls.
	 * @return string[] Array of supported country codes in uppercase
	 */
	public static function getSupportedCountries(): array
	{
		if (self::$supportedCountriesCache !== null) {
			return self::$supportedCountriesCache;
		}

		$countries = [];
		$dir = __DIR__ . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'holidays';
		foreach (glob($dir . DIRECTORY_SEPARATOR . '*.yaml') as $filePath) {
			$data = self::loadYamlFile(basename($filePath, '.yaml'));
			foreach ($data['territories'] ?? [] as $territory) {
				$countries[] = mb_strtoupper($territory);
			}
		}

		self::$supportedCountriesCache = array_values(array_unique($countries));
		return self::$supportedCountriesCache;
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
	 * Internal method that returns country-specific public holidays without deduplication or sorting. Loads holiday definitions from the appropriate YAML file and resolves them according to the year and options.
	 * @param string $country The ISO 3166-1 alpha-2 country code
	 * @param int $year The year for which to retrieve holidays
	 * @param array<string, mixed> $options Configuration options for regional holidays
	 * @return PublicHoliday[] Unsorted array of PublicHoliday objects for the specified country
	 */
	private static function getListOfCountry(string $country, int $year, array $options = []): array
	{
		$country = mb_strtoupper($country);

		$fileName = self::findYamlFileForCountry($country);
		if ($fileName === null) {
			return [];
		}

		$data = self::loadYamlFile($fileName);
		return self::resolveHolidays($data['holidays'] ?? [], $country, $year, $options);
	}

	// ========== YAML Loading ==========

	/**
	 * Loads and caches a YAML holiday file.
	 * Parses the YAML file on first access and returns cached data on subsequent calls.
	 * @param string $fileName The file name without extension (e.g., 'fr', 'be')
	 * @return array<mixed> Parsed YAML data
	 */
	private static function loadYamlFile(string $fileName): array
	{
		if (!isset(self::$yamlCache[$fileName])) {
			$filePath = __DIR__ . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'holidays' . DIRECTORY_SEPARATOR . $fileName . '.yaml';
			self::$yamlCache[$fileName] = \Symfony\Component\Yaml\Yaml::parse(file_get_contents($filePath));
		}
		return self::$yamlCache[$fileName];
	}

	/**
	 * Finds the YAML file name that covers a given country code.
	 * Iterates all YAML files and checks their territories list to find a match.
	 * @param string $country The ISO 3166-1 alpha-2 country code in uppercase
	 * @return string|null The file name (without extension) if found, null otherwise
	 */
	private static function findYamlFileForCountry(string $country): ?string
	{
		$dir = __DIR__ . DIRECTORY_SEPARATOR . 'conf' . DIRECTORY_SEPARATOR . 'holidays';
		foreach (glob($dir . DIRECTORY_SEPARATOR . '*.yaml') as $filePath) {
			$fileName = basename($filePath, '.yaml');
			$data = self::loadYamlFile($fileName);
			$territories = array_map(mb_strtoupper(...), $data['territories'] ?? []);
			if (in_array($country, $territories, true)) {
				return $fileName;
			}
		}
		return null;
	}

	// ========== Holiday Resolvers ==========

	/**
	 * Resolves a list of holiday definitions into PublicHoliday objects.
	 * Iterates through YAML-defined holiday definitions, filters by inclusion rules, and resolves each to a PublicHoliday instance.
	 * @param array<mixed> $definitions The holiday definitions from the YAML file
	 * @param string $country The ISO 3166-1 alpha-2 country code in uppercase
	 * @param int $year The year for which to resolve holidays
	 * @param array<string, mixed> $options Configuration options (e.g., ['alsace' => true])
	 * @return PublicHoliday[] Array of resolved public holidays
	 */
	private static function resolveHolidays(array $definitions, string $country, int $year, array $options): array
	{
		$holidays = [];
		foreach ($definitions as $def) {
			if (!self::isHolidayIncluded($def, $country, $options)) {
				continue;
			}
			$holiday = self::resolveHolidayDefinition($def, $country, $year);
			if ($holiday !== null) {
				$holidays[] = $holiday;
			}
		}
		return $holidays;
	}

	/**
	 * Determines whether a holiday definition should be included for the given country and options.
	 * Applies filtering rules based on 'condition', 'territory', and 'territories' fields in the definition.
	 * @param array<mixed> $def The holiday definition
	 * @param string $country The ISO 3166-1 alpha-2 country code in uppercase
	 * @param array<string, mixed> $options Configuration options
	 * @return bool True if the holiday should be included
	 */
	private static function isHolidayIncluded(array $def, string $country, array $options): bool
	{
		if (isset($def['condition'])) {
			return ($options[$def['condition']] ?? false) === true;
		}

		if (isset($def['territory'])) {
			return $country === mb_strtoupper($def['territory']);
		}

		if (isset($def['territories'])) {
			$territories = array_map('mb_strtoupper', $def['territories']);
			return in_array($country, $territories, true);
		}

		return true;
	}

	/**
	 * Dispatches a single holiday definition to the appropriate type resolver.
	 * Supports types: fixed, easter, relative, hijri, indian, unimplemented.
	 * @param array<mixed> $def The holiday definition
	 * @param string $country The ISO 3166-1 alpha-2 country code in uppercase
	 * @param int $year The year
	 * @return PublicHoliday|null The resolved holiday, or null if it cannot be resolved
	 */
	private static function resolveHolidayDefinition(array $def, string $country, int $year): ?PublicHoliday
	{
		return match ($def['type'] ?? '') {
			'fixed' => self::resolveFixedHoliday($def, $year),
			'easter' => self::resolveEasterHoliday($def, $year),
			'relative' => self::resolveRelativeHoliday($def, $year),
			'hijri' => self::resolveHijriHoliday($def, $year),
			'indian' => self::resolveIndianHoliday($def, $year),
			'unimplemented' => new PublicHoliday($def['name'], 0, key: $def['key'] ?? null),
			default => null,
		};
	}

	/**
	 * Resolves a fixed-date holiday (type: fixed).
	 * The date field must be in MM-DD format. The key defaults to MM-DD if not specified.
	 * @param array<mixed> $def The holiday definition
	 * @param int $year The year
	 * @return PublicHoliday|null The resolved holiday, or null if the date is invalid
	 */
	private static function resolveFixedHoliday(array $def, int $year): ?PublicHoliday
	{
		if (!isset($def['date'])) {
			return null;
		}
		[$month, $day] = explode('-', $def['date']);
		$timestamp = mktime(0, 0, 0, (int)$month, (int)$day, $year);
		return new PublicHoliday(
			$def['name'],
			$timestamp,
			key: $def['key'] ?? null,
			fullName: $def['full_name'] ?? null
		);
	}

	/**
	 * Resolves an Easter-relative holiday (type: easter).
	 * The offset field specifies the number of days relative to Easter Sunday (negative for before, positive for after).
	 * @param array<mixed> $def The holiday definition
	 * @param int $year The year
	 * @return PublicHoliday The resolved holiday
	 */
	private static function resolveEasterHoliday(array $def, int $year): PublicHoliday
	{
		$offset = (int)($def['offset'] ?? 0);
		$easterDateTime = clone self::getEasterDateTime($year);
		if ($offset !== 0) {
			$modifier = ($offset > 0 ? '+' : '') . $offset . ' days';
			$easterDateTime->modify($modifier);
		}
		return new PublicHoliday(
			$def['name'],
			$easterDateTime->getTimestamp(),
			key: $def['key'] ?? null,
			fullName: $def['full_name'] ?? null
		);
	}

	/**
	 * Resolves a relative holiday using a strtotime expression (type: relative).
	 * The expression field supports a {year} placeholder. An optional offset_days field adds days to the result.
	 * @param array<mixed> $def The holiday definition
	 * @param int $year The year
	 * @return PublicHoliday|null The resolved holiday, or null if the expression is invalid
	 */
	private static function resolveRelativeHoliday(array $def, int $year): ?PublicHoliday
	{
		if (!isset($def['expression'])) {
			return null;
		}
		$expression = str_replace('{year}', (string)$year, $def['expression']);
		$timestamp = strtotime($expression);
		if ($timestamp === false) {
			return null;
		}
		$offsetDays = (int)($def['offset_days'] ?? 0);
		if ($offsetDays !== 0) {
			$timestamp += $offsetDays * 86400;
		}
		return new PublicHoliday(
			$def['name'],
			$timestamp,
			key: $def['key'] ?? null,
			fullName: $def['full_name'] ?? null
		);
	}

	/**
	 * Resolves a Hijri (Islamic) calendar holiday (type: hijri).
	 * Iterates the previous, current, and next Hijri year to find the occurrence that falls within the requested Gregorian year.
	 * @param array<mixed> $def The holiday definition
	 * @param int $year The Gregorian year
	 * @return PublicHoliday|null The resolved holiday if it falls within the year, null otherwise
	 */
	private static function resolveHijriHoliday(array $def, int $year): ?PublicHoliday
	{
		[$hijriYear] = IslamicCalendar::convertGregorianDateToIslamicDate($year, 7, 1);
		$startOfYear = mktime(0, 0, 0, 1, 1, $year);
		$endOfYear = mktime(23, 59, 59, 12, 31, $year);

		foreach ([$hijriYear - 1, $hijriYear, $hijriYear + 1] as $tryYear) {
			$timestamp = IslamicCalendar::getTimestamp($tryYear, (int)$def['month'], (int)$def['day']);
			if ($timestamp >= $startOfYear && $timestamp <= $endOfYear) {
				return new PublicHoliday(
					$def['name'],
					$timestamp,
					key: $def['key'] ?? null,
					fullName: $def['full_name'] ?? null,
					calendar: PublicHolidayCalendar::HIJRI
				);
			}
		}
		return null;
	}

	/**
	 * Resolves an Indian (Hindu) calendar holiday (type: indian).
	 * Uses IndianCalendar::getTimestamp() when both month and day are specified. Returns timestamp 0 for unimplemented holidays (month or day is null).
	 * @param array<mixed> $def The holiday definition
	 * @param int $year The Gregorian year
	 * @return PublicHoliday The resolved holiday
	 */
	private static function resolveIndianHoliday(array $def, int $year): PublicHoliday
	{
		$month = $def['month'] ?? null;
		$day = $def['day'] ?? null;

		if ($month !== null && $day !== null) {
			$timestamp = IndianCalendar::getTimestamp($year, (int)$month, (int)$day);
		} else {
			$timestamp = 0;
		}

		return new PublicHoliday(
			$def['name'],
			$timestamp,
			key: $def['key'] ?? null,
			fullName: $def['full_name'] ?? null,
			calendar: PublicHolidayCalendar::INDIAN
		);
	}
}