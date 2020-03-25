<?php

namespace Osimatic\Helpers\Number;

class Distance
{
	/**
	 * @param string $originCoordinates
	 * @param string $destinationCoordinates
	 * @return float
	 */
	public static function calculate(string $originCoordinates, string $destinationCoordinates): float
	{
		[$originLatitude, $originLongitude] = explode(',', $originCoordinates);
		[$destinationLatitude, $destinationLongitude] = explode(',', $destinationCoordinates);
		return self::calculateBetweenLatitudeAndLongitudeData($originLatitude, $originLongitude, $destinationLatitude, $destinationLongitude);
	}

	/**
	 * Retourne distance en mètre
	 * @param float $originLatitude
	 * @param float $originLongitude
	 * @param float $destinationLatitude
	 * @param float $destinationLongitude
	 * @return float
	 */
	public static function calculateBetweenLatitudeAndLongitudeData(float $originLatitude, float $originLongitude, float $destinationLatitude, float $destinationLongitude): float
	{
		return sqrt(pow(($destinationLatitude- $originLatitude), 2) + pow(($destinationLongitude-$originLongitude), 2)) * 100000;
	}

}