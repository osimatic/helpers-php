<?php

declare(strict_types=1);

namespace Tests\Location;

use Osimatic\Location\Point;
use PHPUnit\Framework\TestCase;

final class PointTest extends TestCase
{
	/* ===================== parse() ===================== */

	public function testParseValidCoordinates(): void
	{
		$this->assertSame([48.8584, 2.2945], Point::parse('48.8584,2.2945'));
		$this->assertSame([48.8584, 2.2945], Point::parse(' 48.8584 , 2.2945 '));
		$this->assertSame([48.8584, 2.2945], Point::parse('48.8584;2.2945'));
	}

	public function testParseWithNegativeCoordinates(): void
	{
		$this->assertSame([-40.7128, -74.0060], Point::parse('-40.7128,-74.0060'));
		$this->assertSame([-40.7128, -74.0060], Point::parse(' -40.7128 ; -74.0060 '));
	}

	public function testParseWithZeroCoordinates(): void
	{
		$this->assertSame([0.0, 0.0], Point::parse('0,0'));
		$this->assertSame([0.0, 0.0], Point::parse('0.0, 0.0'));
	}

	public function testParseEmptyString(): void
	{
		$this->assertNull(Point::parse(''));
	}

	public function testParseNaN(): void
	{
		$this->assertNull(Point::parse('NaN,NaN'));
	}

	public function testParseIncompleteCoordinates(): void
	{
		$this->assertNull(Point::parse('48.8584'));
	}

	public function testParseNonNumericCoordinates(): void
	{
		$this->assertNull(Point::parse('lat,lon'));
		$this->assertNull(Point::parse('abc,def'));
	}

	public function testParseInvalidSeparator(): void
	{
		$this->assertNull(Point::parse('48.8584|2.2945'));
	}

	/* ===================== convertToGeoJsonCoordinates() ===================== */

	public function testConvertToGeoJsonCoordinates(): void
	{
		// GeoJSON utilise [lon, lat] au lieu de [lat, lon]
		$result = Point::convertToGeoJsonCoordinates('48.8584,2.2945');
		$this->assertSame([2.2945, 48.8584], $result);
	}

	public function testConvertToGeoJsonCoordinatesWithNegative(): void
	{
		$result = Point::convertToGeoJsonCoordinates(' 40.7128 , -74.0060 ');
		$this->assertSame([-74.0060, 40.7128], $result);
	}

	public function testConvertToGeoJsonCoordinatesWithSemicolon(): void
	{
		$result = Point::convertToGeoJsonCoordinates('48.8584;2.2945');
		$this->assertSame([2.2945, 48.8584], $result);
	}

	public function testConvertToGeoJsonCoordinatesInvalid(): void
	{
		$this->assertNull(Point::convertToGeoJsonCoordinates(''));
		$this->assertNull(Point::convertToGeoJsonCoordinates('invalid'));
		$this->assertNull(Point::convertToGeoJsonCoordinates('48.8584'));
	}

	/* ===================== isPointInsidePlaces() ===================== */

	public function testIsPointInsidePlacesWithGeoJSONPoint(): void
	{
		$authorized = ['{"type":"Point","coordinates":[2.2945,48.8584]}'];

		// Point exact
		$this->assertTrue(Point::isPointInsidePlaces([48.8584, 2.2945], $authorized));

		// Point très éloigné
		$this->assertFalse(Point::isPointInsidePlaces([48.86, 2.29], $authorized));
	}

	public function testIsPointInsidePlacesWithRadius(): void
	{
		$authorized = ['{"type":"Point","coordinates":[2.2945,48.8584]}'];

		// ~11 m plus au nord -> ok avec tolérance 12m
		$this->assertTrue(Point::isPointInsidePlaces([48.8585, 2.2945], $authorized, radius: 12.0));

		// tolérance insuffisante
		$this->assertFalse(Point::isPointInsidePlaces([48.8585, 2.2945], $authorized, radius: 5.0));
	}

	public function testIsPointInsidePlacesWithGeoJSONPolygon(): void
	{
		$poly = '{"type":"Polygon","coordinates":[[[2.31,48.87],[2.314,48.87],[2.314,48.868],[2.31,48.868],[2.31,48.87]]]}';
		$authorized = [$poly];

		// Un point très proche du centre du polygone
		$this->assertTrue(Point::isPointInsidePlaces([48.8692, 2.312], $authorized));

		// Un point dehors
		$this->assertFalse(Point::isPointInsidePlaces([48.871, 2.312], $authorized));
	}

	public function testIsPointInsidePlacesMixedEntries(): void
	{
		$authorized = [
			'{"type":"Point","coordinates":[2.2945,48.8584]}',
			'{"type":"Polygon","coordinates":[[[2.31,48.87],[2.314,48.87],[2.314,48.868],[2.31,48.868],[2.31,48.87]]]}',
			'{"type":"Polygon","coordinates":[[[2.293707,48.871934],[2.294903,48.871384],[2.296411,48.871557],[2.29603,48.872273],[2.294447,48.872375],[2.293707,48.871934]]]}',
		];

		// match geojson point (avec petite tolérance)
		$this->assertTrue(Point::isPointInsidePlaces([48.85845, 2.2945], $authorized, radius: 6.0));

		// match polygon
		$this->assertTrue(Point::isPointInsidePlaces([48.8692, 2.312], $authorized));
	}

	public function testIsPointInsidePlacesEmptyList(): void
	{
		$this->assertFalse(Point::isPointInsidePlaces([48.8584, 2.2945], []));
	}

	public function testIsPointInsidePlacesInvalidGeoJSON(): void
	{
		$authorized = ['{"type":"InvalidType","coordinates":[2.2945,48.8584]}'];
		$this->assertFalse(Point::isPointInsidePlaces([48.8584, 2.2945], $authorized));
	}
}