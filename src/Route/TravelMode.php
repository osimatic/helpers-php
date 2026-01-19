<?php

namespace Osimatic\Route;

/**
 * Enumeration of travel modes for route calculations.
 * Defines the primary method of transportation to use when calculating distance, duration, and directions.
 */
enum TravelMode: string
{
	/**
	 * Walking travel mode for pedestrian routes.
	 */
	case WALK = 'WALK';

	/**
	 * Bicycle travel mode for cycling routes.
	 */
	case BICYCLE = 'BICYCLE';

	/**
	 * Driving travel mode for car routes.
	 */
	case DRIVE = 'DRIVE';

	/**
	 * Two-wheeler travel mode for motorcycle or scooter routes.
	 */
	case TWO_WHEELER = 'TWO_WHEELER';

	/**
	 * Public transit travel mode for routes using buses, trains, subways, and other public transportation.
	 */
	case TRANSIT = 'TRANSIT';

	/**
	 * Airplane travel mode for air travel routes.
	 */
	case PLANE = 'PLANE';

	/**
	 * Boat travel mode for water-based routes.
	 */
	case BOAT = 'BOAT';
}