<?php

namespace Osimatic\Location;

class GeographicCoordinates
{
	/**
	 * @param string $coordinates
	 * @return bool
	 */
	public static function check(string $coordinates): bool
	{
		return preg_match('/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/', $coordinates);
	}

	/**
	 * @param float $latitude
	 * @param float $longitude
	 * @return string
	 */
	public static function getCoordinatesFromLatitudeAndLongitude(float $latitude, float $longitude): string
	{
		$cleanLatOrLng = fn(string $latOrLng) => str_replace([' ', ','], ['', '.'], $latOrLng);
		return $cleanLatOrLng((string) $latitude).','.$cleanLatOrLng((string) $longitude);
	}

}