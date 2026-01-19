<?php

namespace Osimatic\Route;

/**
 * Enumeration of public transit travel modes for route calculations.
 * Used to specify preferred types of public transportation when calculating routes with TRANSIT travel mode.
 */
enum TransitTravelMode: string
{
	/**
	 * Bus transit mode.
	 */
	case BUS = 'BUS';

	/**
	 * Subway or metro transit mode.
	 */
	case SUBWAY = 'SUBWAY';

	/**
	 * Train transit mode.
	 */
	case TRAIN = 'TRAIN';

	/**
	 * Light rail, tram, or streetcar transit mode.
	 */
	case LIGHT_RAIL = 'LIGHT_RAIL';

	/**
	 * Parses a string into a TransitTravelMode enum value with support for common aliases.
	 * Converts alternative names (TRAM, METRO, RAIL, etc.) to their corresponding enum values.
	 * @param string|null $type The transit mode string to parse (case-insensitive)
	 * @return TransitTravelMode|null The corresponding TransitTravelMode enum value, or null if parsing fails
	 */
	public static function parse(?string $type): ?self
	{
		if (null === $type) {
			return null;
		}

		$type = mb_strtoupper($type);

		if ('TRAM' === $type || 'LIGHT_SUBWAY' === $type) {
			return self::LIGHT_RAIL;
		}
		if ('METRO' === $type) {
			return self::SUBWAY;
		}
		if ('RAIL' === $type) {
			return self::TRAIN;
		}
		return self::tryFrom($type);
	}
}