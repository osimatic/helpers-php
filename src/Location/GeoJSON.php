<?php

namespace Osimatic\Location;

/**
 * Utility class for parsing and normalizing GeoJSON geometric data.
 * Provides methods to extract and convert GeoJSON Point and Polygon geometries into PHP arrays.
 * Converts GeoJSON's [longitude, latitude] format to standard [latitude, longitude] format.
 */
class GeoJSON
{
	/**
	 * Parse GeoJSON input (string or array) and return as an array.
	 * @param string|array $geojson The GeoJSON data (JSON string or already-parsed array)
	 * @return array|null The parsed GeoJSON as an associative array, or null if invalid
	 */
	public static function getData(string|array $geojson): ?array
	{
		if (is_array($geojson)) {
			return $geojson;
		}

		return json_decode($geojson, true);

	}

	/**
	 * Extract and normalize a GeoJSON Point geometry to [latitude, longitude] format.
	 * GeoJSON stores coordinates as [longitude, latitude], this method converts to [latitude, longitude].
	 * @param string|array $geojson The GeoJSON Point (JSON string or array)
	 * @return float[]|null Array [latitude, longitude] if valid, null otherwise
	 */
	public static function getPoint(string|array $geojson): ?array
	{
		$obj = self::getData($geojson);
		if (null === $obj || !isset($obj['type']) || strtoupper($obj['type']) !== 'POINT') {
			return null;
		}

		if (!isset($obj['coordinates'][0], $obj['coordinates'][1])) {
			return null;
		}

		[$long, $lat] = $obj['coordinates'];
		return [(float) $lat, (float) $long];
	}

	/**
	 * Extract and normalize a GeoJSON Polygon geometry to [[[lat,lon], ...], [[lat,lon], ...], ...] format.
	 * Converts from GeoJSON's [lon,lat] format to standard [lat,lon] format.
	 * Handles polygons with holes (outer ring + inner rings).
	 * @param string|array $geojson The GeoJSON Polygon (JSON string or array)
	 * @return float[][][]|null Array of rings where each ring is an array of [latitude, longitude] points, or null if invalid
	 */
	public static function getPolygon(string|array $geojson): ?array
	{
		$obj = self::getData($geojson);
		if (null === $obj || !isset($obj['type']) || strtoupper($obj['type']) !== 'POLYGON') {
			return null;
		}

		$rings = $obj['coordinates'] ?? [];
		if (!is_array($rings) || count($rings) === 0) {
			return null;
		}

		// Ensure each coordinate is converted from [lon,lat] to [lat,lon] as float
		$poly = [];
		foreach ($rings as $ring) {
			if (!is_array($ring)) {
				continue;
			}

			$ringOut = [];
			foreach ($ring as $pt) {
				if (!is_array($pt) || count($pt) < 2) {
					continue;
				}
				[$long, $lat] = $pt;
				$ringOut[] = [(float) $lat, (float) $long];
			}

			if (count($ringOut) >= 3) {
				$poly[] = $ringOut;
			}
		}

		return !empty($poly) ? $poly : null;
	}
}