<?php

namespace Tests\Location;

use Osimatic\Number\Distance;
use PHPUnit\Framework\TestCase;

final class DistanceTest extends TestCase
{
	/* ===================== Distance ===================== */

	public function testDistanceCalculation(): void
	{
		// Deux points très proches autour de la Tour Eiffel
		$d = Distance::calculateBetweenLatitudeAndLongitude(48.8584,2.2945, 48.8585,2.2945);
		// ~11 mètres (ordre de grandeur) — on vérifie la fourchette
		$this->assertGreaterThan(10, $d);
		$this->assertLessThan(13, $d);
	}
}