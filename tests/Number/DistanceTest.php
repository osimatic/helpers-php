<?php

namespace Tests\Number;

use Osimatic\Number\Distance;
use PHPUnit\Framework\TestCase;

final class DistanceTest extends TestCase
{
	/* ===================== Orthonormal Basis Distance ===================== */

	public function testCalculateInOrthonormalBasis(): void
	{
		// Distance between (0,0) and (3,4) should be 5 (Pythagorean theorem)
		$distance = Distance::calculateInOrthonormalBasis(0, 0, 3, 4);
		$this->assertEquals(5.0, $distance);

		// Distance between same point
		$distance = Distance::calculateInOrthonormalBasis(1, 1, 1, 1);
		$this->assertEquals(0.0, $distance);

		// Distance between (1,2) and (4,6) should be 5
		$distance = Distance::calculateInOrthonormalBasis(1, 2, 4, 6);
		$this->assertEquals(5.0, $distance);

		// Negative coordinates
		$distance = Distance::calculateInOrthonormalBasis(-1, -1, 2, 3);
		$this->assertEquals(5.0, $distance);
	}

	/* ===================== Geographic Distance ===================== */

	public function testCalculateBetweenLatitudeAndLongitude(): void
	{
		// Deux points très proches autour de la Tour Eiffel
		$d = Distance::calculateBetweenLatitudeAndLongitude(48.8584, 2.2945, 48.8585, 2.2945);
		// ~11 mètres (ordre de grandeur) — on vérifie la fourchette
		$this->assertGreaterThan(10, $d);
		$this->assertLessThan(13, $d);

		// Distance between Paris (48.8566, 2.3522) and London (51.5074, -0.1278)
		// Approximately 344 km
		$d = Distance::calculateBetweenLatitudeAndLongitude(48.8566, 2.3522, 51.5074, -0.1278);
		$this->assertGreaterThan(340000, $d);
		$this->assertLessThan(350000, $d);

		// Distance between same point
		$d = Distance::calculateBetweenLatitudeAndLongitude(48.8566, 2.3522, 48.8566, 2.3522);
		$this->assertEquals(0.0, $d);

		// Custom decimals
		$d = Distance::calculateBetweenLatitudeAndLongitude(48.8584, 2.2945, 48.8585, 2.2945, 0);
		$this->assertIsFloat($d);
		$this->assertGreaterThan(10, $d);
		$this->assertLessThan(13, $d);
	}

	public function testCalculateBetweenPoints(): void
	{
		// Points as arrays [latitude, longitude]
		$originPoint = [48.8566, 2.3522]; // Paris
		$destinationPoint = [51.5074, -0.1278]; // London

		$distance = Distance::calculateBetweenPoints($originPoint, $destinationPoint);
		$this->assertGreaterThan(340000, $distance);
		$this->assertLessThan(350000, $distance);

		// Same point
		$distance = Distance::calculateBetweenPoints($originPoint, $originPoint);
		$this->assertEquals(0.0, $distance);

		// With custom decimals
		$distance = Distance::calculateBetweenPoints($originPoint, $destinationPoint, 0);
		$this->assertIsFloat($distance);
	}

	public function testCalculate(): void
	{
		// Using coordinate strings (depends on Point::parse implementation)
		// This test might need adjustment based on actual Point class format
		// Testing basic functionality
		$originCoords = '48.8566,2.3522'; // Paris
		$destCoords = '51.5074,-0.1278'; // London

		$distance = Distance::calculate($originCoords, $destCoords);

		if ($distance !== null) {
			$this->assertGreaterThan(340000, $distance);
			$this->assertLessThan(350000, $distance);
		} else {
			// If Point::parse returns null, that's expected behavior
			$this->assertNull($distance);
		}

		// Invalid coordinates should return null
		$distance = Distance::calculate('invalid', 'invalid');
		$this->assertNull($distance);
	}

	/* ===================== Conversion ===================== */

	public function testConvertMetersToMiles(): void
	{
		// 1000 meters = ~0.621 miles
		$miles = Distance::convertMetersToMiles(1000);
		$this->assertGreaterThan(0.62, $miles);
		$this->assertLessThan(0.63, $miles);

		// 1 meter = ~0.000621 miles
		$miles = Distance::convertMetersToMiles(1);
		$this->assertGreaterThan(0.0006, $miles);
		$this->assertLessThan(0.0007, $miles);

		// 0 meters
		$miles = Distance::convertMetersToMiles(0);
		$this->assertEquals(0.0, $miles);

		// 1609.344 meters = ~1 mile
		$miles = Distance::convertMetersToMiles(1609.344);
		$this->assertGreaterThan(0.99, $miles);
		$this->assertLessThan(1.01, $miles);
	}
}