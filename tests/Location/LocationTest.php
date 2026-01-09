<?php

declare(strict_types=1);

namespace Tests\Location;

use Osimatic\Location\GeoJSON;
use Osimatic\Location\Point;
use Osimatic\Location\Polygon;
use PHPUnit\Framework\TestCase;

final class LocationTest extends TestCase
{
	/* ===================== Point ===================== ./vendor/bin/phpunit --colors=always*/

	public function testGeoTextParseLatLonOk(): void
	{
		$this->assertSame([48.8584, 2.2945], \Osimatic\Location\Point::parse('48.8584,2.2945'));
		$this->assertSame([48.8584, 2.2945], \Osimatic\Location\Point::parse(' 48.8584 , 2.2945 '));
		$this->assertSame([48.8584, 2.2945], \Osimatic\Location\Point::parse('48.8584;2.2945'));
	}

	public function testGeoTextParseLatLonInvalid(): void
	{
		$this->assertNull(\Osimatic\Location\Point::parse(''));
		$this->assertNull(\Osimatic\Location\Point::parse('48.8584'));          // incomplet
		$this->assertNull(\Osimatic\Location\Point::parse('lat,lon'));          // non numérique
		$this->assertNull(\Osimatic\Location\Point::parse('48.8584|2.2945'));   // mauvais séparateur
	}

	/* ===================== GeoJSON ===================== */

	public function testGeoJSONNormalizePoint(): void
	{
		// GeoJSON -> [lon,lat] doit devenir interne [lat,lon]
		$g = GeoJSON::getPoint('{"type":"Point","coordinates":[2.2945,48.8584]}');
		$this->assertNotNull($g);
		$this->assertEquals([48.8584, 2.2945], $g);
	}

	public function testGeoJSONNormalizePolygon(): void
	{
		$json = <<<JSON
{"type":"Polygon","coordinates":[
  [[2.31,48.87],[2.314,48.87],[2.314,48.868],[2.31,48.868],[2.31,48.87]]
]}
JSON;
		$g = GeoJSON::getPolygon($json);
		$this->assertNotNull($g);
		// On vérifie bien la conversion [lon,lat] -> [lat,lon]
		$this->assertEquals(
			[[[48.87, 2.31], [48.87, 2.314], [48.868, 2.314], [48.868, 2.31], [48.87, 2.31]]],
			$g
		);
	}

	public function testGeoJSONNormalizeInvalid(): void
	{
		$this->assertNull(GeoJSON::getPoint('{}'));
		$this->assertNull(GeoJSON::getPoint('{"type":"Point"}')); // pas de coordinates
	}


	/* ===================== Polygon ===================== */

	public function testPointInPolygonBasicSquare(): void
	{
		// Carré simple (lat,lon)
		$square = [[
			[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]
		]];

		$this->assertTrue(Polygon::isPointInPolygon([0.5, 0.5], $square)); // centre
		$this->assertTrue(Polygon::isPointInPolygon([0, 0.5], $square)); // bord gauche (inclus)
		$this->assertFalse(Polygon::isPointInPolygon([1.5, 0.5], $square)); // dehors
	}

	public function testPointInPolygonWithHole(): void
	{
		// Un anneau extérieur 0..1..1..0..0, et un trou au centre 0.4..0.6
		$poly = [
			[[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]],             // outer
			[[0.4, 0.4], [0.4, 0.6], [0.6, 0.6], [0.6, 0.4], [0.4, 0.4]] // hole
		];

		$this->assertTrue(Polygon::isPointInPolygon([0.2, 0.2], $poly));   // dans outer, hors trou
		$this->assertFalse(Polygon::isPointInPolygon([0.5, 0.5], $poly));  // dans le trou
		//$this->assertTrue(Polygon::isPointInPolygon([0.4,0.5], $poly));   // sur le bord du trou (inclus -> false ?)
		// NB: Notre implémentation considère "sur le bord" comme inside au niveau anneau.
		// Mais comme c'est un trou, "sur le bord du trou" => pointInRing retourne true,
		// donc Polygon::pointInPolygon le considère "dans le trou" -> donc FALSE.
		// Testons ce comportement explicitement :
		$this->assertFalse(Polygon::isPointInPolygon([0.4, 0.5], $poly));
	}

	public function testPointOnEdgeIsInsideOuter(): void
	{
		$square = [[
			[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]
		]];
		// Un point sur le bord est considéré "inside" pour l'outer ring
		$this->assertTrue(Polygon::isPointInPolygon([0, 0.3], $square));
	}

	/* ===================== Authorizer ===================== */

	public function testAuthorizerWithGeoJSONPoint(): void
	{
		$authorized = ['{"type":"Point","coordinates":[2.2945,48.8584]}'];
		// ~11 m plus au nord -> ok avec tolérance 12m
		$this->assertTrue(Point::isPointInsidePlaces([48.8585, 2.2945], $authorized, radius: 12.0));
		// tolérance insuffisante
		$this->assertFalse(Point::isPointInsidePlaces([48.8585, 2.2945], $authorized, radius: 5.0));
		$this->assertTrue(Point::isPointInsidePlaces([48.8584, 2.2945], $authorized));
		$this->assertFalse(Point::isPointInsidePlaces([48.86, 2.29], $authorized));
	}

	public function testAuthorizerWithGeoJSONPolygon(): void
	{
		$poly = '{"type":"Polygon","coordinates":[[[2.31,48.87],[2.314,48.87],[2.314,48.868],[2.31,48.868],[2.31,48.87]]]}';
		$authorized = [$poly];

		// Un point très proche du centre du polygone
		$this->assertTrue(Point::isPointInsidePlaces([48.8692, 2.312], $authorized));
		// Un point dehors
		$this->assertFalse(Point::isPointInsidePlaces([48.871, 2.312], $authorized));
	}

	public function testAuthorizerMixedEntries(): void
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
}