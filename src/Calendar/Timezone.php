<?php

namespace Osimatic\Calendar;

/**
 * Utility class for timezone validation, formatting, and lookup.
 * Provides methods for:
 * - Timezone validation using Symfony Validator
 * - Timezone formatting with UTC offset, country, and cities
 * - Listing timezones by country
 * - Accessing timezone configuration data
 */
class Timezone
{
	/**
	 * Validates if a timezone identifier is valid.
	 * Uses Symfony Validator component for validation.
	 * Optionally validates timezone against a specific country code.
	 * @param string $timezone The timezone identifier (e.g., 'Europe/Paris', 'America/New_York')
	 * @param string|null $countryCode Optional ISO 3166-1 alpha-2 country code (e.g., 'FR', 'US')
	 * @return bool True if timezone is valid (and matches country if specified), false otherwise
	 * @link https://www.php.net/manual/en/timezones.php
	 */
	public static function check(string $timezone, ?string $countryCode=null): bool
	{
		if (empty($timezone)) {
			return false;
		}

		$constraint = new \Symfony\Component\Validator\Constraints\Timezone();
		$constraint->countryCode = $countryCode;
		$constraint->zone = null !== $countryCode ? \DateTimeZone::PER_COUNTRY : \DateTimeZone::ALL;
		return \Osimatic\Validator\Validator::getInstance()->validate($timezone, $constraint)->count() === 0;
	}

	/**
	 * Formats a timezone identifier with UTC offset and optionally country and cities.
	 * Looks up timezone data from internal configuration.
	 * @param string $timezone The timezone identifier (e.g., 'Europe/Paris')
	 * @param bool $withCountry Whether to include country name in output (default: true)
	 * @param bool $withCities Whether to include city names in output (default: true)
	 * @return string Formatted timezone string (e.g., "UTC+01:00 - Europe/Paris (France : Paris)")
	 */
	public static function format(string $timezone, bool $withCountry=true, bool $withCities=true): string
	{
		$timezone = mb_strtolower($timezone);
		$listTimeZones = self::getListTimeZones();

		foreach ($listTimeZones as $timezoneName => $timezoneData) {
			if (mb_strtolower($timezoneName) !== $timezone) {
				continue;
			}

			return self::formatWithData($timezoneName, $timezoneData['utc'], $timezoneData['country'], $timezoneData['cities'] ?? [], $withCountry, $withCities);
		}

		return '';
	}

	/**
	 * Formats timezone information with provided data.
	 * Creates a formatted string with UTC offset, timezone name, and optionally country and cities.
	 * @param string $timezoneName The timezone identifier (e.g., 'Europe/Paris')
	 * @param string $utc The UTC offset (e.g., 'UTC+01:00')
	 * @param string|null $countryCodeOrCountryName Country code or full country name
	 * @param string[] $cities Array of city names in this timezone
	 * @param bool $withCountry Whether to include country in output (default: true)
	 * @param bool $withCities Whether to include cities in output (default: true)
	 * @return string Formatted timezone string (e.g., "UTC+01:00 - Europe/Paris (France : Paris, Lyon)")
	 */
	public static function formatWithData(string $timezoneName, string $utc, ?string $countryCodeOrCountryName=null, array $cities=[], bool $withCountry=true, bool $withCities=true): string
	{
		$withCities = $withCities && !empty($cities);
		$withCountry = $withCountry && !empty($countryCodeOrCountryName);
		$str = $utc.' - '.$timezoneName;
		if ($withCountry || $withCities) {
			$str .= ' (';
			if ($withCountry) {
				$str .= (\Osimatic\Location\Country::getCountryNameFromCountryCode($countryCodeOrCountryName) ?? $countryCodeOrCountryName);
			}
			if ($withCountry && $withCities) {
				$str .= ' : ';
			}
			if ($withCities) {
				$str .= implode(', ', $cities);
			}
			$str .= ')';
		}
		return $str;
	}

	/**
	 * Gets all timezone identifiers for a specific country.
	 * Uses PHP's DateTimeZone::listIdentifiers() for country-specific timezones.
	 * @param string $countryCode ISO 3166-1 alpha-2 country code (e.g., 'FR', 'US')
	 * @return array Array of timezone identifiers for the country, empty array if invalid
	 */
	public static function getListTimeZonesOfCountry(string $countryCode): array
	{
		if (empty($countryCode) || strlen($countryCode) !== 2) {
			return [];
		}

		try {
			return \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $countryCode);
		} catch (\ValueError) {
			return [];
		}
	}

	/**
	 * Gets the primary timezone identifier for a country.
	 * Returns the first timezone in the country's timezone list.
	 * @param string $countryCode ISO 3166-1 alpha-2 country code (e.g., 'FR', 'US')
	 * @return string|null The primary timezone identifier, or null if country has no timezones
	 */
	public static function getTimeZoneOfCountry(string $countryCode): ?string
	{
		return self::getListTimeZonesOfCountry($countryCode)[0] ?? null;
	}

	/**
	 * Gets formatted labels for all timezones.
	 * Returns an associative array mapping timezone identifiers to formatted strings.
	 * @param bool $withCountry Whether to include country names in labels (default: true)
	 * @param bool $withCities Whether to include city names in labels (default: true)
	 * @return string[] Array of [timezone => formatted_label]
	 */
	public static function getListTimeZonesLabel(bool $withCountry=true, bool $withCities=true): array
	{
		$listTimeZones = self::getListTimeZones();

		$listTimeZonesLabel = [];
		foreach ($listTimeZones as $timezoneName => $timezoneData) {
			$listTimeZonesLabel[$timezoneName] = self::formatWithData($timezoneName, $timezoneData['utc'], $timezoneData['country'], $timezoneData['cities'] ?? [], $withCountry, $withCities);
		}
		return $listTimeZonesLabel;
	}

	/**
	 * Gets all timezone data from configuration file.
	 * Loads timezone information including UTC offsets, countries, and cities.
	 * @return array Array of timezone data from time_zones.ini configuration file
	 */
	public static function getListTimeZones(): array
	{
		return parse_ini_file(__DIR__.'/conf/time_zones.ini', true);
	}

}