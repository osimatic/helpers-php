<?php

namespace Osimatic\Helpers\Calendar;

/**
 * Class Timezone
 * @package Osimatic\Helpers\Calendar
 */
class Timezone
{
	/**
	 * Vérifie la validité d'un fuseau horaire
	 * @param string $timezone
	 * @param string|null $countryCode
	 * @return bool
	 * @link https://www.php.net/manual/en/timezones.php
	 */
	public static function check(string $timezone, ?string $countryCode=null): bool
	{
		$validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
			->addMethodMapping('loadValidatorMetadata')
			->getValidator();
		$constraint = new \Symfony\Component\Validator\Constraints\Timezone();
		$constraint->countryCode = $countryCode;
		return $validator->validate($timezone, $constraint)->count() === 0;
	}

	/**
	 * @param string $timezone
	 * @param bool $withCountry
	 * @param bool $withCities
	 * @return string
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
	 * @param string $timezoneName
	 * @param string $utc
	 * @param string|null $countryCodeOrCountryName
	 * @param string[] $cities
	 * @param bool $withCountry
	 * @param bool $withCities
	 * @return string
	 */
	public static function formatWithData(string $timezoneName, string $utc, ?string $countryCodeOrCountryName=null, array $cities=[], bool $withCountry=true, bool $withCities=true): string
	{
		$withCities = $withCities && !empty($cities);
		$withCountry = $withCountry && !empty($countryCodeOrCountryName);
		$str = $utc.' - '.$timezoneName;
		if ($withCountry || $withCities) {
			$str .= ' (';
			if ($withCountry) {
				$str .= (\Osimatic\Helpers\Location\Country::getCountryNameByCountryCode($countryCodeOrCountryName) ?? $countryCodeOrCountryName);
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
	 * @param bool $withCountry
	 * @param bool $withCities
	 * @return string[]
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
	 * @return array
	 */
	public static function getListTimeZones(): array
	{
		return parse_ini_file(__DIR__.'/conf/time_zones.ini', true);
	}

}