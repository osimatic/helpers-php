<?php

namespace Osimatic\Number;

use Osimatic\Location\PlaceInterface;

class Distance
{
	// ========== Distance sur une base orthonormée ==========

	/**
	 * @param float $abscissa1
	 * @param float $ordinate1
	 * @param float $abscissa2
	 * @param float $ordinate2
	 * @return float
	 */
	public static function calculateInOrthonormalBasis(float $abscissa1, float $ordinate1, float $abscissa2, float $ordinate2): float
	{
		return sqrt(pow(($abscissa2-$abscissa1), 2) + pow(($ordinate2-$ordinate1), 2));
	}


	// ========== Distance géographique ==========

	/**
	 * @param string $originCoordinates
	 * @param string $destinationCoordinates
	 * @param int $decimals
	 * @return float|null
	 */
	public static function calculate(string $originCoordinates, string $destinationCoordinates, int $decimals=2): ?float
	{
		if (null === ($originPoint = \Osimatic\Location\GeographicCoordinates::parseToPoint($originCoordinates)) || null === ($destinationPoint = \Osimatic\Location\GeographicCoordinates::parseToPoint($originCoordinates))) {
			return null;
		}
		return self::calculateBetweenPoints($originPoint, $destinationPoint, $decimals);
	}

	/**
	 * @param float[] $originPoint
	 * @param float[] $destinationPoint
	 * @param int $decimals
	 * @return float
	 */
	public static function calculateBetweenPoints(array $originPoint, array $destinationPoint, int $decimals=2): float
	{
		[$originLatitude, $originLongitude] = $originPoint;
		[$destinationLatitude, $destinationLongitude] = $destinationPoint;
		return self::calculateBetweenLatitudeAndLongitude((float) $originLatitude, (float) $originLongitude, (float) $destinationLatitude, (float) $destinationLongitude, $decimals);
	}

	/**
	 * Retourne distance en mètre
	 * @param float $originLatitude
	 * @param float $originLongitude
	 * @param float $destinationLatitude
	 * @param float $destinationLongitude
	 * @param int $decimals
	 * @return float
	 */
	public static function calculateBetweenLatitudeAndLongitude(float $originLatitude, float $originLongitude, float $destinationLatitude, float $destinationLongitude, int $decimals=2): float
	{
		$earth_radius = 6378137; // Terre = sphère de 6378km de rayon
		$radLng1 = deg2rad($originLongitude);
		$radLat1 = deg2rad($originLatitude);
		$radLng2 = deg2rad($destinationLongitude);
		$radLat2 = deg2rad($destinationLatitude);
		$diffLng = ($radLng2 - $radLng1) / 2;
		$diffLat = ($radLat2 - $radLat1) / 2;
		$a = (sin($diffLat) * sin($diffLat)) + cos($radLat1) * cos($radLat2) * (sin($diffLng) * sin($diffLng));
		$d = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$distance = $earth_radius * $d;

		/*
		// Calcul de la distance en degrés
		$degrees = rad2deg(acos((sin(deg2rad($originLatitude))*sin(deg2rad($destinationLatitude))) + (cos(deg2rad($originLatitude))*cos(deg2rad($destinationLatitude))*cos(deg2rad($originLongitude-$destinationLongitude)))));

		// Conversion de la distance en degrés à l'unité mètre
		$distance = $degrees * 111133.84; // 1 degré = 111.133,84 mètres, sur base du diamètre moyen de la Terre (12735 km)
		*/

		// Arrondissement
		return round($distance, $decimals);
	}

	/**
	 * Retourne distance en mètre
	 * @param PlaceInterface $originPlace
	 * @param PlaceInterface $destinationPlace
	 * @return float
	 */
	public static function calculateBetweenPlaces(PlaceInterface $originPlace, PlaceInterface $destinationPlace): float
	{
		return self::calculateBetweenLatitudeAndLongitude($originPlace->getLatitude(), $originPlace->getLongitude(), $destinationPlace->getLatitude(), $destinationPlace->getLongitude() );
	}

	/**
	 * Retourne distance en miles
	 * @param float $distance
	 * @return float
	 */
	public static function convertMetersToMiles(float $distance): float
	{
		return (float) ($distance * 0.621371192);
	}

}