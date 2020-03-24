<?php

namespace Osimatic\Helpers\Location;

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
	 * @param string $originCoordinates
	 * @param string $destinationCoordinates
	 * @return float
	 */
	public static function getDistance(string $originCoordinates, string $destinationCoordinates): float
	{
		[$originLatitude, $originLongitude] = explode(',', $originCoordinates);
		[$destinationLatitude, $destinationLongitude] = explode(',', $destinationCoordinates);
		return self::getDistanceBetweenLatitudeAndLongitudeData($originLatitude, $originLongitude, $destinationLatitude, $destinationLongitude);
	}

	/**
	 * Retourne distance en mètre
	 * @param float $originLatitude
	 * @param float $originLongitude
	 * @param float $destinationLatitude
	 * @param float $destinationLongitude
	 * @return float
	 */
	public static function getDistanceBetweenLatitudeAndLongitudeData(float $originLatitude, float $originLongitude, float $destinationLatitude, float $destinationLongitude): float
	{
		return sqrt(pow(($destinationLatitude- $originLatitude), 2) + pow(($destinationLongitude-$originLongitude), 2)) * 100000;
	}

}