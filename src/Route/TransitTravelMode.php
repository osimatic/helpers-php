<?php

namespace Osimatic\Route;

enum TransitTravelMode: string
{
	case BUS = 'BUS';
	case SUBWAY = 'SUBWAY';
	case TRAIN = 'TRAIN';
	case LIGHT_RAIL = 'LIGHT_RAIL';

	/**
	 * @param string|null $type
	 * @return TransitTravelMode|null
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