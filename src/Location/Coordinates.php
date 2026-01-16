<?php

namespace Osimatic\Location;

/**
 * Immutable value object representing geographic coordinates (latitude and longitude).
 * Ensures coordinates are always valid and provides useful conversion and distance calculation methods.
 */
readonly class Coordinates
{
	/**
	 * Create a new Coordinates instance with validation.
	 * @param float $latitude The latitude value (must be between -90 and +90)
	 * @param float $longitude The longitude value (must be between -180 and +180)
	 * @throws \InvalidArgumentException If latitude or longitude is out of valid range
	 */
	public function __construct(
		public float $latitude,
		public float $longitude
	) {
		if ($latitude < -90 || $latitude > 90) {
			throw new \InvalidArgumentException(
				sprintf('Latitude must be between -90 and +90, got %f', $latitude)
			);
		}
		if ($longitude < -180 || $longitude > 180) {
			throw new \InvalidArgumentException(
				sprintf('Longitude must be between -180 and +180, got %f', $longitude)
			);
		}
	}

	/**
	 * Create Coordinates from a string in "lat,lon" format.
	 * @param string $coordinates The coordinate string (e.g., "48.8566,2.3522")
	 * @return self
	 * @throws \InvalidArgumentException If the string format is invalid or coordinates are out of range
	 */
	public static function fromString(string $coordinates): self
	{
		$parsed = Point::parse($coordinates);
		if (null === $parsed) {
			throw new \InvalidArgumentException(
				sprintf('Invalid coordinate string format: %s', $coordinates)
			);
		}

		[$lat, $lon] = $parsed;
		return new self($lat, $lon);
	}

	/**
	 * Convert coordinates to string in "lat,lon" format.
	 * @param int $precision Number of decimal places (default: 6)
	 * @return string The formatted coordinate string (e.g., "48.856600,2.352200")
	 */
	public function toString(int $precision = 6): string
	{
		return GeographicCoordinates::format($this->latitude, $this->longitude, $precision);
	}

	/**
	 * Get string representation using default precision.
	 * @return string The formatted coordinate string
	 */
	public function __toString(): string
	{
		return $this->toString();
	}

	/**
	 * Convert to GeoJSON coordinate format [longitude, latitude].
	 * Note: GeoJSON uses [lon, lat] order, opposite of the standard [lat, lon].
	 * @return float[] Array [longitude, latitude]
	 */
	public function toGeoJSON(): array
	{
		return [$this->longitude, $this->latitude];
	}

	/**
	 * Calculate the distance to another coordinate using Haversine formula.
	 * @param Coordinates $other The other coordinates
	 * @return float Distance in meters
	 */
	public function distanceTo(Coordinates $other): float
	{
		return \Osimatic\Number\Distance::calculateBetweenLatitudeAndLongitude(
			$this->latitude,
			$this->longitude,
			$other->latitude,
			$other->longitude
		);
	}

	/**
	 * Check if this coordinate is equal to another (within tolerance).
	 * @param Coordinates $other The other coordinates
	 * @param float $tolerance Tolerance in meters (default: 0.01m = 1cm)
	 * @return bool True if coordinates are equal within tolerance
	 */
	public function equals(Coordinates $other, float $tolerance = 0.01): bool
	{
		if ($tolerance <= 0) {
			return $this->latitude === $other->latitude
				&& $this->longitude === $other->longitude;
		}

		return $this->distanceTo($other) <= $tolerance;
	}

	/**
	 * Check if this coordinate is inside a polygon.
	 * @param float[][][] $polygon Polygon as array of rings: [outer ring, hole1, hole2, ...]
	 * @return bool True if inside the polygon
	 */
	public function isInsidePolygon(array $polygon): bool
	{
		return Polygon::isPointInPolygon([$this->latitude, $this->longitude], $polygon);
	}

	/**
	 * Check if this coordinate is within any of the provided geographic areas.
	 * @param array $geoJSONList Array of GeoJSON objects (Point or Polygon geometries)
	 * @param float $radius Tolerance radius in meters for Point comparison
	 * @return bool True if inside any area
	 */
	public function isInsidePlaces(array $geoJSONList, float $radius = 0.): bool
	{
		return Point::isPointInsidePlaces([$this->latitude, $this->longitude], $geoJSONList, $radius);
	}

	/**
	 * Get a Google Maps URL for viewing this location.
	 * @return string The Google Maps URL
	 */
	public function toGoogleMapsUrl(): string
	{
		return 'https://maps.google.com/?q=' . $this->latitude . ',' . $this->longitude;
	}
}