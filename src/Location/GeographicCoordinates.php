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
		if (empty($coordinates) || 'NaN,NaN' === $coordinates) {
			return null;
		}

		$coordinatesComponents = array_map(trim(...), explode(',', $coordinates));
		return implode(',', $coordinatesComponents);
	}

}