<?php

namespace Osimatic\Number;

use Osimatic\Location\PlaceInterface;

/**
 * Class Distance
 * Provides utilities for distance calculations (orthonormal and geographic)
 */
class Distance
{
	// Earth radius in meters
	private const int EARTH_RADIUS_METERS = 6378137;

	// Conversion coefficient meters to miles (note: treats meters as kilometers for compatibility)
	private const float METERS_TO_MILES = 0.000621371192;

	// ========== Distance in Orthonormal Basis ==========

	/**
	 * Calculates the Euclidean distance between two points in an orthonormal coordinate system
	 * @param float $abscissa1 the x-coordinate of the first point
	 * @param float $ordinate1 the y-coordinate of the first point
	 * @param float $abscissa2 the x-coordinate of the second point
	 * @param float $ordinate2 the y-coordinate of the second point
	 * @return float the distance between the two points
	 */
	public static function calculateInOrthonormalBasis(float $abscissa1, float $ordinate1, float $abscissa2, float $ordinate2): float
	{
		return sqrt(pow(($abscissa2-$abscissa1), 2) + pow(($ordinate2-$ordinate1), 2));
	}


	// ========== Geographic Distance ==========

	/**
	 * Calculates the geographic distance between two coordinate strings
	 * @param string $originCoordinates the origin coordinates as a string
	 * @param string $destinationCoordinates the destination coordinates as a string
	 * @param int $decimals the number of decimal places to round to (default: 2)
	 * @return float|null the distance in meters, null if coordinates are invalid
	 */
	public static function calculate(string $originCoordinates, string $destinationCoordinates, int $decimals=2): ?float
	{
		if (null === ($originPoint = \Osimatic\Location\Point::parse($originCoordinates)) || null === ($destinationPoint = \Osimatic\Location\Point::parse($destinationCoordinates))) {
			return null;
		}
		return self::calculateBetweenPoints($originPoint, $destinationPoint, $decimals);
	}

	/**
	 * Calculates the geographic distance between two point arrays [latitude, longitude]
	 * @param float[] $originPoint the origin point [latitude, longitude]
	 * @param float[] $destinationPoint the destination point [latitude, longitude]
	 * @param int $decimals the number of decimal places to round to (default: 2)
	 * @return float the distance in meters
	 */
	public static function calculateBetweenPoints(array $originPoint, array $destinationPoint, int $decimals=2): float
	{
		[$originLatitude, $originLongitude] = $originPoint;
		[$destinationLatitude, $destinationLongitude] = $destinationPoint;
		return self::calculateBetweenLatitudeAndLongitude((float) $originLatitude, (float) $originLongitude, (float) $destinationLatitude, (float) $destinationLongitude, $decimals);
	}

	/**
	 * Calculates the geographic distance between two latitude/longitude coordinates using the Haversine formula
	 * @param float $originLatitude the origin latitude in degrees
	 * @param float $originLongitude the origin longitude in degrees
	 * @param float $destinationLatitude the destination latitude in degrees
	 * @param float $destinationLongitude the destination longitude in degrees
	 * @param int $decimals the number of decimal places to round to (default: 2)
	 * @return float the distance in meters
	 */
	public static function calculateBetweenLatitudeAndLongitude(float $originLatitude, float $originLongitude, float $destinationLatitude, float $destinationLongitude, int $decimals=2): float
	{
		$radLng1 = deg2rad($originLongitude);
		$radLat1 = deg2rad($originLatitude);
		$radLng2 = deg2rad($destinationLongitude);
		$radLat2 = deg2rad($destinationLatitude);
		$diffLng = ($radLng2 - $radLng1) / 2;
		$diffLat = ($radLat2 - $radLat1) / 2;
		$a = (sin($diffLat) * sin($diffLat)) + cos($radLat1) * cos($radLat2) * (sin($diffLng) * sin($diffLng));
		$d = 2 * atan2(sqrt($a), sqrt(1 - $a));
		$distance = self::EARTH_RADIUS_METERS * $d;

		/*
		// Alternative calculation using degrees
		$degrees = rad2deg(acos((sin(deg2rad($originLatitude))*sin(deg2rad($destinationLatitude))) + (cos(deg2rad($originLatitude))*cos(deg2rad($destinationLatitude))*cos(deg2rad($originLongitude-$destinationLongitude)))));

		// Convert distance in degrees to meters
		$distance = $degrees * 111133.84; // 1 degree = 111,133.84 meters, based on Earth's mean diameter (12735 km)
		*/

		// Round to specified decimal places
		return round($distance, $decimals);
	}

	/**
	 * Calculates the geographic distance between two Place objects
	 * @param PlaceInterface $originPlace the origin place
	 * @param PlaceInterface $destinationPlace the destination place
	 * @return float the distance in meters
	 */
	public static function calculateBetweenPlaces(PlaceInterface $originPlace, PlaceInterface $destinationPlace): float
	{
		return self::calculateBetweenLatitudeAndLongitude($originPlace->getLatitude(), $originPlace->getLongitude(), $destinationPlace->getLatitude(), $destinationPlace->getLongitude());
	}

	/**
	 * Converts a distance from meters to miles
	 * @param float $distance the distance in meters
	 * @return float the distance in miles
	 */
	public static function convertMetersToMiles(float $distance): float
	{
		return $distance * self::METERS_TO_MILES;
	}

}