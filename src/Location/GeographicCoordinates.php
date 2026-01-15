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
		$cleanLatOrLng = static fn(string $latOrLng) => str_replace([' ', ','], ['', '.'], $latOrLng);
		return $cleanLatOrLng((string) $latitude).','.$cleanLatOrLng((string) $longitude);
	}

	/**
	 * @param string|null $coordinates
	 * @return string|null
	 */
	public static function parse(?string $coordinates): ?string
	{
		if (null === $coordinates || null === ($point = Point::parse($coordinates))) {
			return null;
		}
		return implode(',', $point);
	}

	/**
	 * @param string $coordinates coordonnées à tester
	 * @param array $geoJSONList tableau de GeoJSON (GeoJSON Point ou GeoJSON Polygon)
	 * @param float $radius tolérance en mètres pour comparer un point (0 = égalité stricte)
	 * @return bool
	 */
	public static function isCoordinatesInsidePlaces(string $coordinates, array $geoJSONList, float $radius = 0.): bool
	{
		return Point::isPointInsidePlaces(Point::parse($coordinates), $geoJSONList, $radius);
	}

	/**
	 * Convert GPS coordinates from degrees/minutes/seconds format to decimal degrees.
	 * Used for converting EXIF GPS data from photos to standard decimal format.
	 * @param array $coordinate Array of three strings representing degrees, minutes, and seconds (e.g., ["40/1", "26/1", "46/1"])
	 * @param string $hemisphere N, S, E, or W
	 * @return float The coordinate in decimal degrees
	 */
	public static function gpsToDecimal(array $coordinate, string $hemisphere): float
	{
		$degrees = count($coordinate) > 0 ? self::gpsRationalToFloat($coordinate[0]) : 0;
		$minutes = count($coordinate) > 1 ? self::gpsRationalToFloat($coordinate[1]) : 0;
		$seconds = count($coordinate) > 2 ? self::gpsRationalToFloat($coordinate[2]) : 0;

		$decimal = $degrees + ($minutes / 60) + ($seconds / 3600);

		if ($hemisphere === 'S' || $hemisphere === 'W') {
			$decimal *= -1;
		}

		return $decimal;
	}

	/**
	 * Convert a GPS rational number (e.g., "40/1") to a float.
	 * GPS coordinates in EXIF are stored as rational numbers (fractions).
	 * @param string $rational The rational number as a string
	 * @return float The float value
	 */
	public static function gpsRationalToFloat(string $rational): float
	{
		$parts = explode('/', $rational);
		if (count($parts) !== 2 || $parts[1] == 0) {
			return 0;
		}
		return (float)$parts[0] / (float)$parts[1];
	}

}