<?php

declare(strict_types=1);

namespace Tests\Location;

use Osimatic\Location\GeoJSON;
use PHPUnit\Framework\TestCase;

final class GeoJSONTest extends TestCase
{
	/* ===================== getData() ===================== */

	public function testGetDataWithString(): void
	{
		$json = '{"type":"Point","coordinates":[2.2945,48.8584]}';
		$data = GeoJSON::getData($json);
		$this->assertIsArray($data);
		$this->assertSame('Point', $data['type']);
		$this->assertSame([2.2945, 48.8584], $data['coordinates']);
	}

	public function testGetDataWithArray(): void
	{
		$array = ['type' => 'Point', 'coordinates' => [2.2945, 48.8584]];
		$data = GeoJSON::getData($array);
		$this->assertIsArray($data);
		$this->assertSame($array, $data);
	}

	public function testGetDataInvalidJson(): void
	{
		$data = GeoJSON::getData('invalid json');
		$this->assertNull($data);
	}

	public function testGetDataEmptyString(): void
	{
		$data = GeoJSON::getData('');
		$this->assertNull($data);
	}

	/* ===================== getPoint() ===================== */

	public function testGetPointFromString(): void
	{
		// GeoJSON -> [lon,lat] doit devenir interne [lat,lon]
		$g = GeoJSON::getPoint('{"type":"Point","coordinates":[2.2945,48.8584]}');
		$this->assertNotNull($g);
		$this->assertEquals([48.8584, 2.2945], $g);
	}

	public function testGetPointFromArray(): void
	{
		$array = ['type' => 'Point', 'coordinates' => [2.2945, 48.8584]];
		$point = GeoJSON::getPoint($array);
		$this->assertSame([48.8584, 2.2945], $point);
	}

	public function testGetPointCaseInsensitive(): void
	{
		$json = '{"type":"point","coordinates":[2.2945,48.8584]}';
		$point = GeoJSON::getPoint($json);
		$this->assertSame([48.8584, 2.2945], $point);

		$json = '{"type":"POINT","coordinates":[2.2945,48.8584]}';
		$point = GeoJSON::getPoint($json);
		$this->assertSame([48.8584, 2.2945], $point);
	}

	public function testGetPointWithNegativeCoordinates(): void
	{
		$json = '{"type":"Point","coordinates":[-74.0060,40.7128]}';
		$point = GeoJSON::getPoint($json);
		$this->assertSame([40.7128, -74.0060], $point);
	}

	public function testGetPointEmptyObject(): void
	{
		$this->assertNull(GeoJSON::getPoint('{}'));
	}

	public function testGetPointMissingType(): void
	{
		$json = '{"coordinates":[2.2945,48.8584]}';
		$this->assertNull(GeoJSON::getPoint($json));
	}

	public function testGetPointMissingCoordinates(): void
	{
		$this->assertNull(GeoJSON::getPoint('{"type":"Point"}'));
	}

	public function testGetPointIncompleteCoordinates(): void
	{
		$json = '{"type":"Point","coordinates":[2.2945]}';
		$this->assertNull(GeoJSON::getPoint($json));
	}

	public function testGetPointInvalidType(): void
	{
		$json = '{"type":"Polygon","coordinates":[2.2945,48.8584]}';
		$this->assertNull(GeoJSON::getPoint($json));
	}

	/* ===================== getPolygon() ===================== */

	public function testGetPolygonFromString(): void
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

	public function testGetPolygonFromArray(): void
	{
		$array = [
			'type' => 'Polygon',
			'coordinates' => [
				[[2.31, 48.87], [2.314, 48.87], [2.314, 48.868], [2.31, 48.868], [2.31, 48.87]]
			]
		];
		$polygon = GeoJSON::getPolygon($array);
		$this->assertNotNull($polygon);
		$this->assertCount(1, $polygon);
	}

	public function testGetPolygonCaseInsensitive(): void
	{
		$json = '{"type":"polygon","coordinates":[[[2.31,48.87],[2.314,48.87],[2.314,48.868],[2.31,48.868],[2.31,48.87]]]}';
		$polygon = GeoJSON::getPolygon($json);
		$this->assertNotNull($polygon);

		$json = '{"type":"POLYGON","coordinates":[[[2.31,48.87],[2.314,48.87],[2.314,48.868],[2.31,48.868],[2.31,48.87]]]}';
		$polygon = GeoJSON::getPolygon($json);
		$this->assertNotNull($polygon);
	}

	public function testGetPolygonWithMultipleRings(): void
	{
		// Polygon avec un trou
		$json = '{"type":"Polygon","coordinates":[' .
			'[[0,0],[0,1],[1,1],[1,0],[0,0]],' .
			'[[0.4,0.4],[0.4,0.6],[0.6,0.6],[0.6,0.4],[0.4,0.4]]' .
			']}';
		$polygon = GeoJSON::getPolygon($json);
		$this->assertNotNull($polygon);
		$this->assertCount(2, $polygon); // outer ring + hole
	}

	public function testGetPolygonEmptyObject(): void
	{
		$this->assertNull(GeoJSON::getPolygon('{}'));
	}

	public function testGetPolygonInvalidType(): void
	{
		$json = '{"type":"Point","coordinates":[[[2.31,48.87]]]}';
		$this->assertNull(GeoJSON::getPolygon($json));
	}

	public function testGetPolygonEmptyCoordinates(): void
	{
		$json = '{"type":"Polygon","coordinates":[]}';
		$this->assertNull(GeoJSON::getPolygon($json));
	}

	public function testGetPolygonMissingCoordinates(): void
	{
		$json = '{"type":"Polygon"}';
		$this->assertNull(GeoJSON::getPolygon($json));
	}

	public function testGetPolygonInvalidRingTooFewPoints(): void
	{
		// Ring avec moins de 3 points
		$json = '{"type":"Polygon","coordinates":[[[2.31,48.87],[2.314,48.87]]]}';
		$this->assertNull(GeoJSON::getPolygon($json));
	}

	public function testGetPolygonInvalidRingNotArray(): void
	{
		$json = '{"type":"Polygon","coordinates":["invalid"]}';
		$polygon = GeoJSON::getPolygon($json);
		$this->assertNull($polygon);
	}

	public function testGetPolygonInvalidPointNotArray(): void
	{
		$json = '{"type":"Polygon","coordinates":[[2.31,48.87,"invalid"]]}';
		$polygon = GeoJSON::getPolygon($json);
		$this->assertNull($polygon);
	}

	public function testGetPolygonWithNegativeCoordinates(): void
	{
		$json = '{"type":"Polygon","coordinates":[[[-1,-1],[-1,1],[1,1],[1,-1],[-1,-1]]]}';
		$polygon = GeoJSON::getPolygon($json);
		$this->assertNotNull($polygon);
		$this->assertEquals(
			[[[-1.0, -1.0], [1.0, -1.0], [1.0, 1.0], [-1.0, 1.0], [-1.0, -1.0]]],
			$polygon
		);
	}
}