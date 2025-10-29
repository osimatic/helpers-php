<?php

namespace Osimatic\Location;

class Point
{
	/**
	 * Parse "lat,lon" (ou avec espaces) -> [lat,lon] ; null si invalide
	 * @param string $coordinates
	 * @return float[]|null
	 */
	public static function parse(string $coordinates): ?array {
		$coordinates = trim($coordinates);
		if (empty($coordinates) || 'NaN,NaN' === $coordinates) {
			return null;
		}

		// remplace ; par , et espaces multiples par un seul
		$coordinates = str_replace(';', ',', $coordinates);
		$coordinates = preg_replace('/\s+/', '', $coordinates);

		if (!strpos($coordinates, ',')) {
			return null;
		}

		[$lat, $lon] = array_map(trim(...), explode(',', $coordinates, 2));
		if (!is_numeric($lat) || !is_numeric($lon)) {
			return null;
		}
		return [(float) $lat, (float) $lon];
	}

	/**
	 * @param float[] $point point [lat,lon] à tester
	 * @param array $geoJSONList tableau de GeoJSON (GeoJSON Point ou GeoJSON Polygon)
	 * @param float $radius tolérance en mètres pour comparer un point (0 = égalité stricte)
	 * @return bool
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

				// égalité stricte (attention aux arrondis en pratique)
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