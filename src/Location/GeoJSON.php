<?php

namespace Osimatic\Location;

class GeoJSON
{
	/**
	 * @param string|array $geojson
	 * @return array|null
	 */
	public static function getData(string|array $geojson): ?array
	{
		if (is_array($geojson)) {
			return $geojson;
		}

		return json_decode($geojson, true);

	}

	/**
	 * Normalise un GeoJSON Point vers un tableau [lat,lon]
	 * @param string|array $geojson
	 * @return float[]|null
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

		[$lon, $lat] = $obj['coordinates'];
		return [(float) $lat, (float) $lon];
	}

	/**
	 * Normalise un GeoJSON Polygon vers un tableau [[[lat,lon], ...], [[lat,lon], ...], ...]
	 * @param string|array $geojson
	 * @return float[][][]|null
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

		// On s’assure que chaque coord est [lon,lat] float
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
				[$lon, $lat] = $pt;
				$ringOut[] = [(float) $lat, (float) $lon];
			}

			if (count($ringOut) >= 3) {
				$poly[] = $ringOut;
			}
		}

		return !empty($poly) ? $poly : null;
	}
}