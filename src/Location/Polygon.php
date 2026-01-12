<?php

namespace Osimatic\Location;

class Polygon
{
	/**
	 * Test si P est sur le segment AB (tolérance EPS)
	 * @param float[] $P [lat,lon]
	 * @param float[] $A [lat,lon]
	 * @param float[] $B [lat,lon]
	 * @param float $EPS tolérance
	 * @return bool
	 */
	public static function isPointOnSegment(array $P, array $A, array $B, float $EPS = 1e-10): bool
	{
		[$y, $x] = $P;
		[$y1, $x1] = $A;
		[$y2, $x2] = $B;

		// Colinéarité via l'aire (cross product)
		$cross = ($x - $x1)*($y2 - $y1) - ($y - $y1)*($x2 - $x1);
		if (abs($cross) > $EPS) {
			return false;
		}

		// Projection dans la boîte englobante
		$dot = ($x - $x1)*($x2 - $x1) + ($y - $y1)*($y2 - $y1);
		if ($dot < -$EPS) {
			return false;
		}

		$len2 = ($x2 - $x1)**2 + ($y2 - $y1)**2;
		if ($dot - $len2 > $EPS) {
			return false;
		}

		return true;
	}

	/**
	 * Point-In-Polygon (ray casting). Retourne true si à l'intérieur ou sur le bord.
	 * @param float[] $point [lat,lon]
	 * @param float[][][] $polygon [[[lat,lon], ...], [[lat,lon], ...], ...]
	 * @return bool
	 */
	public static function isPointInPolygon(array $point, array $polygon): bool
	{
		if (empty($polygon)) {
			return false;
		}

		// doit être dans l’outer
		if (!self::isPointInRing($point, $polygon[0])) {
			return false;
		}

		// pas dans un trou
		for ($i = 1; $i < count($polygon); $i++) {
			if (self::isPointInRing($point, $polygon[$i])) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Point-In-Polygon (ray casting). Retourne true si à l'intérieur ou sur le bord.
	 * @param float[] $point [lat,lon]
	 * @param float[][] $ring [[lat,lon], ...]
	 * @return bool
	 */
	public static function isPointInRing(array $point, array $ring): bool
	{
		$n = count($ring);
		if ($n < 3) {
			return false;
		}

		// S'assurer que le polygone est "fermé" pour les tests de bord
		if ($ring[0] !== $ring[$n - 1]) {
			$ring[] = $ring[0];
			$n++;
		}

		// Bord ?
		for ($i = 0; $i < $n - 1; $i++) {
			if (self::isPointOnSegment($point, $ring[$i], $ring[$i+1])) {
				return true; // Sur le bord = inside
			}
		}

		// Ray casting
		[$y, $x] = $point;
		$inside = false;
		for ($i = 0, $j = $n - 1; $i < $n; $j = $i++) {
			[$yi, $xi] = $ring[$i];
			[$yj, $xj] = $ring[$j];
			$intersect = (($xi > $x) !== ($xj > $x)) &&
				($y < ($yj - $yi) * ($x - $xi) / (($xj - $xi) ?: 1e-20) + $yi);
			if ($intersect) {
				$inside = !$inside;
			}
		}
		return $inside;
	}

	/**
	 * Calcule le centroid (centre géométrique) d'un polygone
	 * @param array $polygonData Tableau de points du polygone [[lat1, lng1], [lat2, lng2], ...]
	 * @return array|null [latitude, longitude] du centroid ou null si le calcul échoue
	 */
	public static function getCentroid(array $polygonData): ?array
	{
		if (empty($polygonData)) {
			return null;
		}

		$latSum = 0;
		$lngSum = 0;
		$count = 0;

		foreach ($polygonData as $point) {
			if (is_array($point) && count($point) >= 2) {
				$latSum += $point[0];
				$lngSum += $point[1];
				$count++;
			}
		}

		if ($count === 0) {
			return null;
		}

		return [$latSum / $count, $lngSum / $count];
	}
}