<?php

namespace Osimatic\Helpers\Location;

enum Continent: int
{
	case EUROPE = 1;
	case MIDDLE_EAST = 2;
	case AFRICA = 3;
	case NORTH_AMERICA = 4;
	case SOUTH_AMERICA = 5;
	case ASIA = 6;
	case OCEANIA = 7;
	case ANTARCTICA = 8;

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