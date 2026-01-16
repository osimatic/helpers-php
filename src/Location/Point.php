<?php

namespace Osimatic\Location;

/**
 * Utility class for working with geographic points (latitude/longitude coordinates).
 * Provides methods for parsing coordinate strings, converting formats, and testing point containment within geographic areas.
 */
class Point
{
	/**
	 * Parse a coordinate string into a [latitude, longitude] array.
	 * Accepts various formats: "lat,lon" or "lat;lon" with optional spaces.
	 * @param string $coordinates The coordinate string to parse (e.g., "48.8566, 2.3522" or "48.8566;2.3522")
	 * @return float[]|null Array [latitude, longitude] if valid, null if invalid
	 */
	public static function parse(string $coordinates): ?array {
		$coordinates = trim($coordinates);
		if (empty($coordinates) || 'NaN,NaN' === $coordinates) {
			return null;
		}

		// Replace semicolons with commas and remove all whitespace
		$coordinates = str_replace(';', ',', $coordinates);
		$coordinates = preg_replace('/\s+/', '', $coordinates);

		if (!strpos($coordinates, ',')) {
			return null;
		}

		[$lat, $long] = array_map(trim(...), explode(',', $coordinates, 2));
		if (!is_numeric($lat) || !is_numeric($long)) {
			return null;
		}
		return [(float) $lat, (float) $long];
	}

	/**
	 * Convert standard [lat,lon] coordinates to GeoJSON format [lon,lat].
	 * GeoJSON uses longitude-first ordering, while most systems use latitude-first.
	 * @param string $coordinates The coordinate string to convert (e.g., "48.8566, 2.3522")
	 * @return float[]|null Array [longitude, latitude] in GeoJSON format, or null if parsing fails
	 */
	public static function convertToGeoJsonCoordinates(string $coordinates): ?array
	{
		if (null === ([$lat, $long] = self::parse($coordinates))) {
			return null;
		}
		return [$long, $lat];
	}

	/**
	 * Check if a point is contained within any of the provided geographic areas.
	 * Supports both GeoJSON Point (with radius tolerance) and GeoJSON Polygon geometries.
	 * @param float[] $point The point to test as [latitude, longitude]
	 * @param array $geoJSONList Array of GeoJSON objects (Point or Polygon geometries)
	 * @param float $radius Tolerance radius in meters for Point comparison (0 = strict equality)
	 * @return bool True if the point is inside any of the provided areas
	 */
	public static function isPointInsidePlaces(array $point, array $geoJSONList, float $radius = 0.): bool
	{
		[$latitude, $longitude] = $point;

		foreach ($geoJSONList as $geoJSON) {
			if (null !== ($pointData = GeoJSON::getPoint($geoJSON))) {
				[$placeLat, $placeLon] = $pointData;
				if ($radius > 0) {
					if (\Osimatic\Number\Distance::calculateBetweenLatitudeAndLongitude($latitude, $longitude, $placeLat, $placeLon) <= $radius) {
						return true;
					}
					continue;
				}

				// Strict equality (watch out for floating-point rounding in practice)
				if (abs($latitude - $placeLat) < 1e-12 && abs($longitude - $placeLon) < 1e-12) {
					return true;
				}
				continue;
			}

			if (null !== ($polygonData = GeoJSON::getPolygon($geoJSON))) {
				if (Polygon::isPointInPolygon($point, $polygonData)) {
					return true;
				}
				continue;
			}
		}

		return false;
	}
}