<?php

namespace Osimatic\Location;

/**
 * Utility class for working with geographic coordinates (latitude/longitude pairs).
 * Provides validation, formatting, parsing, and EXIF GPS data conversion methods.
 */
class GeographicCoordinates
{
	/**
	 * Validate if a string is a properly formatted coordinate pair.
	 * Checks for valid latitude (-90 to +90) and longitude (-180 to +180) ranges.
	 * @param string $coordinates The coordinate string to validate (e.g., "48.8566, 2.3522")
	 * @return bool True if the coordinates are valid
	 */
	public static function check(string $coordinates): bool
	{
		return preg_match('/^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$/', $coordinates);
	}

	/**
	 * Format latitude and longitude as a coordinate string "lat,lon".
	 * Normalizes decimal separators and removes spaces.
	 * @param float $latitude The latitude value
	 * @param float $longitude The longitude value
	 * @return string The formatted coordinate string (e.g., "48.8566,2.3522")
	 */
	public static function getCoordinatesFromLatitudeAndLongitude(float $latitude, float $longitude): string
	{
		$cleanLatOrLng = static fn(string $latOrLng) => str_replace([' ', ','], ['', '.'], $latOrLng);
		return $cleanLatOrLng((string) $latitude).','.$cleanLatOrLng((string) $longitude);
	}

	/**
	 * Parse and normalize a coordinate string.
	 * Accepts various formats and returns a standardized "lat,lon" string.
	 * @param string|null $coordinates The coordinate string to parse
	 * @return string|null The normalized coordinate string, or null if invalid
	 */
	public static function parse(?string $coordinates): ?string
	{
		if (null === $coordinates || null === ($point = Point::parse($coordinates))) {
			return null;
		}
		return implode(',', $point);
	}

	/**
	 * Check if coordinates are contained within any of the provided geographic areas.
	 * @param string $coordinates The coordinates to test (e.g., "48.8566, 2.3522")
	 * @param array $geoJSONList Array of GeoJSON objects (Point or Polygon geometries)
	 * @param float $radius Tolerance radius in meters for Point comparison (0 = strict equality)
	 * @return bool True if the coordinates are inside any of the provided areas
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