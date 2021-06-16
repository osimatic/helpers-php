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

	public static function format(string $timezone, bool $withCountry=true, bool $withCities=true): string
	{
		$timezone = mb_strtolower($timezone);
		$listTimeZones = self::getListTimeZones();

		foreach ($listTimeZones as $timezoneName => $timezoneData) {
			if (mb_strtolower($timezoneName) !== $timezone) {
				continue;
			}

			$displayCities = $withCities && !empty($timezoneData['cities']);
			$countryName = \Osimatic\Helpers\Location\Country::getCountryNameByCountryCode($timezoneData['country']);

			$str = $timezoneData['utc'].' - '.$timezoneName;
			if ($withCountry || $displayCities) {
				$str .= ' (';
				if ($withCountry) {
					$str .= ($countryName ?? $timezoneData['country']);
				}
				if ($withCountry && $displayCities) {
					$str .= ' : ';
				}
				if ($displayCities) {
					$str .= implode(', ', $timezoneData['cities']);
				}
				$str .= ')';
			}
			return $str;
		}

		return '';
	}

	public static function getListTimeZones(): array
	{
		return parse_ini_file(__DIR__.'/conf/time_zones.ini', true);
	}

}