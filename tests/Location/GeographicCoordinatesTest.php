<?php

declare(strict_types=1);

namespace Tests\Location;

use Osimatic\Location\GeographicCoordinates;
use PHPUnit\Framework\TestCase;

final class GeographicCoordinatesTest extends TestCase
{
	/* ===================== check() ===================== */

	public function testCheckValid(): void
	{
		$this->assertTrue(GeographicCoordinates::check('48.8584,2.2945'));
		$this->assertTrue(GeographicCoordinates::check('0,0'));
		$this->assertTrue(GeographicCoordinates::check('90,180'));
		$this->assertTrue(GeographicCoordinates::check('-90,-180'));
		$this->assertTrue(GeographicCoordinates::check('45.5,-73.5'));
		$this->assertTrue(GeographicCoordinates::check('48.8584, 2.2945')); // avec espace
	}

	public function testCheckValidEdgeCases(): void
	{
		$this->assertTrue(GeographicCoordinates::check('90,0'));
		$this->assertTrue(GeographicCoordinates::check('-90,0'));
		$this->assertTrue(GeographicCoordinates::check('0,180'));
		$this->assertTrue(GeographicCoordinates::check('0,-180'));
		$this->assertTrue(GeographicCoordinates::check('90.0,180.0'));
	}

	public function testCheckInvalidLatitude(): void
	{
		$this->assertFalse(GeographicCoordinates::check('91,0')); // latitude > 90
		$this->assertFalse(GeographicCoordinates::check('-91,0')); // latitude < -90
		$this->assertFalse(GeographicCoordinates::check('90.1,0')); // latitude > 90
	}

	public function testCheckInvalidLongitude(): void
	{
		$this->assertFalse(GeographicCoordinates::check('0,181')); // longitude > 180
		$this->assertFalse(GeographicCoordinates::check('0,-181')); // longitude < -180
		$this->assertFalse(GeographicCoordinates::check('0,180.1')); // longitude > 180
	}

	public function testCheckInvalidFormat(): void
	{
		$this->assertFalse(GeographicCoordinates::check('abc,def')); // non numérique
		$this->assertFalse(GeographicCoordinates::check('48.8584')); // incomplet
		$this->assertFalse(GeographicCoordinates::check('')); // vide
		$this->assertFalse(GeographicCoordinates::check('48.8584;2.2945')); // mauvais séparateur
	}

	/* ===================== getCoordinatesFromLatitudeAndLongitude() ===================== */

	public function testGetCoordinatesFromLatitudeAndLongitude(): void
	{
		$result = GeographicCoordinates::getCoordinatesFromLatitudeAndLongitude(48.8584, 2.2945);
		$this->assertSame('48.8584,2.2945', $result);

		$result = GeographicCoordinates::getCoordinatesFromLatitudeAndLongitude(-40.7128, -74.0060);
		$this->assertSame('-40.7128,-74.006', $result);
	}

	public function testGetCoordinatesWithSpecialValues(): void
	{
		$result = GeographicCoordinates::getCoordinatesFromLatitudeAndLongitude(0.0, 0.0);
		$this->assertSame('0,0', $result);

		$result = GeographicCoordinates::getCoordinatesFromLatitudeAndLongitude(90.0, 180.0);
		$this->assertSame('90,180', $result);

		$result = GeographicCoordinates::getCoordinatesFromLatitudeAndLongitude(-90.0, -180.0);
		$this->assertSame('-90,-180', $result);
	}

	public function testGetCoordinatesWithFloatingPoint(): void
	{
		$result = GeographicCoordinates::getCoordinatesFromLatitudeAndLongitude(48.858370, 2.294481);
		$this->assertSame('48.85837,2.294481', $result);
	}

	/* ===================== parse() ===================== */

	public function testParseValid(): void
	{
		$result = GeographicCoordinates::parse('48.8584,2.2945');
		$this->assertSame('48.8584,2.2945', $result);

		$result = GeographicCoordinates::parse(' 48.8584 , 2.2945 ');
		$this->assertSame('48.8584,2.2945', $result);

		$result = GeographicCoordinates::parse('48.8584;2.2945');
		$this->assertSame('48.8584,2.2945', $result);
	}

	public function testParseWithNegativeCoordinates(): void
	{
		$result = GeographicCoordinates::parse('-40.7128,-74.0060');
		$this->assertSame('-40.7128,-74.006', $result);
	}

	public function testParseWithZeroCoordinates(): void
	{
		$result = GeographicCoordinates::parse('0,0');
		$this->assertSame('0,0', $result);
	}

	public function testParseInvalid(): void
	{
		$this->assertNull(GeographicCoordinates::parse(null));
		$this->assertNull(GeographicCoordinates::parse(''));
		$this->assertNull(GeographicCoordinates::parse('invalid'));
		$this->assertNull(GeographicCoordinates::parse('48.8584'));
		$this->assertNull(GeographicCoordinates::parse('NaN,NaN'));
	}

	/* ===================== isCoordinatesInsidePlaces() ===================== */

	public function testIsCoordinatesInsidePlaces(): void
	{
		$places = ['{"type":"Point","coordinates":[2.2945,48.8584]}'];

		$this->assertTrue(GeographicCoordinates::isCoordinatesInsidePlaces('48.8584,2.2945', $places));
		$this->assertFalse(GeographicCoordinates::isCoordinatesInsidePlaces('48.86,2.29', $places));
	}

	public function testIsCoordinatesInsidePlacesWithRadius(): void
	{
		$places = ['{"type":"Point","coordinates":[2.2945,48.8584]}'];

		// Point légèrement décalé mais dans le rayon
		$this->assertTrue(GeographicCoordinates::isCoordinatesInsidePlaces('48.8585,2.2945', $places, 12.0));

		// Point trop éloigné
		$this->assertFalse(GeographicCoordinates::isCoordinatesInsidePlaces('48.8585,2.2945', $places, 5.0));
	}

	public function testIsCoordinatesInsidePlacesWithPolygon(): void
	{
		$places = ['{"type":"Polygon","coordinates":[[[2.31,48.87],[2.314,48.87],[2.314,48.868],[2.31,48.868],[2.31,48.87]]]}'];

		$this->assertTrue(GeographicCoordinates::isCoordinatesInsidePlaces('48.8692,2.312', $places));
		$this->assertFalse(GeographicCoordinates::isCoordinatesInsidePlaces('48.871,2.312', $places));
	}

	public function testIsCoordinatesInsidePlacesMixed(): void
	{
		$places = [
			'{"type":"Point","coordinates":[2.2945,48.8584]}',
			'{"type":"Polygon","coordinates":[[[2.31,48.87],[2.314,48.87],[2.314,48.868],[2.31,48.868],[2.31,48.87]]]}',
		];

		// Match le point avec tolérance
		$this->assertTrue(GeographicCoordinates::isCoordinatesInsidePlaces('48.85845,2.2945', $places, 6.0));

		// Match le polygon
		$this->assertTrue(GeographicCoordinates::isCoordinatesInsidePlaces('48.8692,2.312', $places));

		// Ne match aucun
		$this->assertFalse(GeographicCoordinates::isCoordinatesInsidePlaces('50.0,3.0', $places));
	}

	public function testIsCoordinatesInsidePlacesEmptyList(): void
	{
		$this->assertFalse(GeographicCoordinates::isCoordinatesInsidePlaces('48.8584,2.2945', []));
	}
}