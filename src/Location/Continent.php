<?php

namespace Osimatic\Location;

/**
 * Enum representing the world's continents.
 * Each continent is assigned a unique integer value for database storage and comparison.
 */
enum Continent: int
{
	/** Europe continent */
	case EUROPE = 1;

	/** Middle East region */
	case MIDDLE_EAST = 2;

	/** Africa continent */
	case AFRICA = 3;

	/** North America continent */
	case NORTH_AMERICA = 4;

	/** South America continent */
	case SOUTH_AMERICA = 5;

	/** Asia continent */
	case ASIA = 6;

	/** Oceania continent (Australia, Pacific Islands) */
	case OCEANIA = 7;

	/** Antarctica continent */
	case ANTARCTICA = 8;

	/**
	 * Get the human-readable name of the continent in English.
	 * @return string The continent name
	 */
	public function getName(): string {
		return match($this) {
			self::EUROPE => "Europe",
			self::MIDDLE_EAST => "Moyen-Orient",
			self::AFRICA => "Afrique",
			self::NORTH_AMERICA => "Amérique du Nord",
			self::SOUTH_AMERICA => "Amérique du Sud",
			self::ASIA => "Asie",
			self::OCEANIA => "Océanie",
			self::ANTARCTICA => "Antarctique",
		};
	}
}