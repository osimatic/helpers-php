<?php

namespace Osimatic\Location;

/**
 * Utility class for working with geographic polygons.
 * Provides methods for point-in-polygon testing, centroid calculation, and geometric operations.
 */
class Polygon
{
	/**
	 * Test if point P lies on line segment AB (within tolerance EPS).
	 * Uses cross product for collinearity and dot product for bounding box projection.
	 * @param float[] $P The point to test [latitude, longitude]
	 * @param float[] $A First endpoint of the segment [latitude, longitude]
	 * @param float[] $B Second endpoint of the segment [latitude, longitude]
	 * @param float $EPS Tolerance for floating-point comparison (default: 1e-10)
	 * @return bool True if P is on segment AB within tolerance
	 */
	public static function isPointOnSegment(array $P, array $A, array $B, float $EPS = 1e-10): bool
	{
		[$y, $x] = $P;
		[$y1, $x1] = $A;
		[$y2, $x2] = $B;

		// Check collinearity using cross product
		$cross = ($x - $x1)*($y2 - $y1) - ($y - $y1)*($x2 - $x1);
		if (abs($cross) > $EPS) {
			return false;
		}

		// Check if point is within bounding box using dot product
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
	 * Check if a point is inside a polygon using ray casting algorithm.
	 * Returns true if the point is inside or on the boundary.
	 * Handles polygons with holes (outer ring and inner rings).
	 * @param float[] $point The point to test [latitude, longitude]
	 * @param float[][][] $polygon Array of rings: [outer ring, hole1, hole2, ...] where each ring is [[lat,lon], ...]
	 * @return bool True if the point is inside the polygon or on its boundary
	 */
	public static function isPointInPolygon(array $point, array $polygon): bool
	{
		if (empty($polygon)) {
			return false;
		}

		// Point must be inside the outer ring
		if (!self::isPointInRing($point, $polygon[0])) {
			return false;
		}

		// Point must not be inside any holes
		for ($i = 1, $iMax = count($polygon); $i < $iMax; $i++) {
			if (self::isPointInRing($point, $polygon[$i])) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Check if a point is inside any polygon in a MultiPolygon geometry.
	 * A MultiPolygon is a collection of multiple polygons (e.g., countries with islands).
	 * Returns true if the point is inside at least one of the polygons.
	 * @param float[] $point The point to test [latitude, longitude]
	 * @param float[][][][] $multiPolygon Array of polygons, where each polygon is an array of rings
	 * @return bool True if the point is inside any of the polygons
	 */
	public static function isPointInMultiPolygon(array $point, array $multiPolygon): bool
	{
		foreach ($multiPolygon as $polygon) {
			if (self::isPointInPolygon($point, $polygon)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if a point is inside a single polygon ring using ray casting algorithm.
	 * Returns true if the point is inside or on the boundary.
	 * @param float[] $point The point to test [latitude, longitude]
	 * @param float[][] $ring Array of coordinates forming a closed ring [[lat,lon], ...]
	 * @return bool True if the point is inside the ring or on its boundary
	 */
	public static function isPointInRing(array $point, array $ring): bool
	{
		$n = count($ring);
		if ($n < 3) {
			return false;
		}

		// Ensure the polygon is "closed" for edge testing
		if ($ring[0] !== $ring[$n - 1]) {
			$ring[] = $ring[0];
			$n++;
		}

		// Check if point is on any edge
		for ($i = 0; $i < $n - 1; $i++) {
			if (self::isPointOnSegment($point, $ring[$i], $ring[$i+1])) {
				return true; // On the boundary = inside
			}
		}

		// Ray casting algorithm
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
	 * Calculate the centroid (geometric center) of a polygon.
	 * Uses the average of all points in the outer ring.
	 * @param array $polygon Array of rings [[[lat, lon], ...], [[lat, lon], ...]] or single ring [[lat, lon], ...]
	 * @return array|null [latitude, longitude] of the centroid, or null if calculation fails
	 */
	public static function getCentroid(array $polygon): ?array
	{
		if (empty($polygon)) {
			return null;
		}

		// Determine if it's a polygon (array of rings) or a single ring (array of points)
		$ring = $polygon;
		if (isset($polygon[0]) && is_array($polygon[0])) {
			// If the first element is an array
			if (isset($polygon[0][0]) && is_array($polygon[0][0])) {
				// It's a polygon (array of rings), take the outer ring
				$ring = $polygon[0];
			}
			// Otherwise it's already a ring (array of points)
		}

		$latSum = 0;
		$lngSum = 0;
		$count = 0;

		foreach ($ring as $point) {
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

	/**
	 * Calculate the area of a polygon using the Shoelace formula (also known as surveyor's formula).
	 * Returns the area in square degrees. For area in square meters or kilometers, use additional conversion.
	 * Note: This method works with the outer ring only. For polygons with holes, subtract the hole areas.
	 * @param array $ring Array of [lat, lon] points forming a closed ring
	 * @return float Area in square degrees (not square meters). Returns 0 for invalid polygons.
	 */
	public static function calculateArea(array $ring): float
	{
		$n = count($ring);
		if ($n < 3) {
			return 0.0;
		}

		// Ensure the ring is closed (first point = last point)
		$isClosed = ($ring[0] === $ring[$n - 1]);
		if (!$isClosed) {
			$ring[] = $ring[0];
			$n++;
		}

		$area = 0.0;
		for ($i = 0; $i < $n - 1; $i++) {
			$j = $i + 1;
			// Using the shoelace formula: A = 0.5 * |Σ(x_i * y_(i+1) - x_(i+1) * y_i)|
			// Here we use [lat, lon] so lat is y and lon is x
			$area += $ring[$i][1] * $ring[$j][0]; // lon_i * lat_(i+1)
			$area -= $ring[$j][1] * $ring[$i][0]; // lon_(i+1) * lat_i
		}

		return abs($area / 2.0);
	}

	/**
	 * Calculate the area of a polygon with holes using the Shoelace formula.
	 * The total area is the outer ring area minus the sum of all hole areas.
	 * @param array $polygon Array of rings: [outer ring, hole1, hole2, ...] where each ring is [[lat, lon], ...]
	 * @return float Total area in square degrees
	 */
	public static function calculateAreaWithHoles(array $polygon): float
	{
		if (empty($polygon)) {
			return 0.0;
		}

		// Calculate outer ring area
		$totalArea = self::calculateArea($polygon[0]);

		// Subtract hole areas
		for ($i = 1, $iMax = count($polygon); $i < $iMax; $i++) {
			$totalArea -= self::calculateArea($polygon[$i]);
		}

		return max(0.0, $totalArea);
	}
}