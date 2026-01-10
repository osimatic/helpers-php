<?php

declare(strict_types=1);

namespace Tests\Location;

use Osimatic\Location\Polygon;
use PHPUnit\Framework\TestCase;

final class PolygonTest extends TestCase
{
	/* ===================== isPointOnSegment() ===================== */

	public function testIsPointOnSegmentTrue(): void
	{
		$A = [0.0, 0.0];
		$B = [1.0, 1.0];
		$P = [0.5, 0.5]; // Point au milieu du segment
		$this->assertTrue(Polygon::isPointOnSegment($P, $A, $B));
	}

	public function testIsPointOnSegmentAtEndpoints(): void
	{
		$A = [0.0, 0.0];
		$B = [1.0, 1.0];
		$this->assertTrue(Polygon::isPointOnSegment($A, $A, $B));
		$this->assertTrue(Polygon::isPointOnSegment($B, $A, $B));
	}

	public function testIsPointOnSegmentFalseNotCollinear(): void
	{
		$A = [0.0, 0.0];
		$B = [1.0, 1.0];
		$P = [0.5, 0.6]; // Point en dehors de la ligne
		$this->assertFalse(Polygon::isPointOnSegment($P, $A, $B));
	}

	public function testIsPointOnSegmentFalseOutsideBounds(): void
	{
		$A = [0.0, 0.0];
		$B = [1.0, 1.0];
		$P = [2.0, 2.0]; // Point sur la ligne mais en dehors du segment
		$this->assertFalse(Polygon::isPointOnSegment($P, $A, $B));
	}

	public function testIsPointOnSegmentHorizontal(): void
	{
		$A = [0.0, 0.0];
		$B = [0.0, 1.0];
		$P = [0.0, 0.5];
		$this->assertTrue(Polygon::isPointOnSegment($P, $A, $B));
	}

	public function testIsPointOnSegmentVertical(): void
	{
		$A = [0.0, 0.0];
		$B = [1.0, 0.0];
		$P = [0.5, 0.0];
		$this->assertTrue(Polygon::isPointOnSegment($P, $A, $B));
	}

	public function testIsPointOnSegmentNegativeCoordinates(): void
	{
		$A = [-1.0, -1.0];
		$B = [1.0, 1.0];
		$P = [0.0, 0.0];
		$this->assertTrue(Polygon::isPointOnSegment($P, $A, $B));
	}

	/* ===================== isPointInRing() ===================== */

	public function testIsPointInRingInside(): void
	{
		$ring = [[0, 0], [0, 1], [1, 1], [1, 0]]; // carré non fermé
		$this->assertTrue(Polygon::isPointInRing([0.5, 0.5], $ring));
	}

	public function testIsPointInRingOutside(): void
	{
		$ring = [[0, 0], [0, 1], [1, 1], [1, 0]];
		$this->assertFalse(Polygon::isPointInRing([2.0, 2.0], $ring));
	}

	public function testIsPointInRingOnEdge(): void
	{
		$ring = [[0, 0], [0, 1], [1, 1], [1, 0]];
		$this->assertTrue(Polygon::isPointInRing([0, 0.5], $ring)); // sur le bord gauche
	}

	public function testIsPointInRingClosedPolygon(): void
	{
		$ring = [[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]]; // carré fermé
		$this->assertTrue(Polygon::isPointInRing([0.5, 0.5], $ring));
	}

	public function testIsPointInRingTooFewPoints(): void
	{
		$ring = [[0, 0], [0, 1]]; // seulement 2 points
		$this->assertFalse(Polygon::isPointInRing([0.5, 0.5], $ring));
	}

	public function testIsPointInRingOnVertex(): void
	{
		$ring = [[0, 0], [0, 1], [1, 1], [1, 0]];
		$this->assertTrue(Polygon::isPointInRing([0, 0], $ring)); // sur un sommet
	}

	public function testIsPointInRingTriangle(): void
	{
		$ring = [[0, 0], [1, 0], [0.5, 1]];
		$this->assertTrue(Polygon::isPointInRing([0.5, 0.3], $ring));
		$this->assertFalse(Polygon::isPointInRing([0.5, 1.5], $ring));
	}

	/* ===================== isPointInPolygon() ===================== */

	public function testIsPointInPolygonBasicSquare(): void
	{
		// Carré simple (lat,lon)
		$square = [[
			[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]
		]];

		$this->assertTrue(Polygon::isPointInPolygon([0.5, 0.5], $square)); // centre
		$this->assertTrue(Polygon::isPointInPolygon([0, 0.5], $square)); // bord gauche (inclus)
		$this->assertFalse(Polygon::isPointInPolygon([1.5, 0.5], $square)); // dehors
	}

	public function testIsPointInPolygonWithHole(): void
	{
		// Un anneau extérieur 0..1..1..0..0, et un trou au centre 0.4..0.6
		$poly = [
			[[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]],             // outer
			[[0.4, 0.4], [0.4, 0.6], [0.6, 0.6], [0.6, 0.4], [0.4, 0.4]] // hole
		];

		$this->assertTrue(Polygon::isPointInPolygon([0.2, 0.2], $poly));   // dans outer, hors trou
		$this->assertFalse(Polygon::isPointInPolygon([0.5, 0.5], $poly));  // dans le trou
		$this->assertFalse(Polygon::isPointInPolygon([0.4, 0.5], $poly));  // sur le bord du trou
	}

	public function testIsPointOnEdgeIsInsideOuter(): void
	{
		$square = [[
			[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]
		]];
		// Un point sur le bord est considéré "inside" pour l'outer ring
		$this->assertTrue(Polygon::isPointInPolygon([0, 0.3], $square));
	}

	public function testIsPointInPolygonEmptyPolygon(): void
	{
		$this->assertFalse(Polygon::isPointInPolygon([0.5, 0.5], []));
	}

	public function testIsPointInPolygonTriangle(): void
	{
		$triangle = [[[0, 0], [1, 0], [0.5, 1], [0, 0]]];
		$this->assertTrue(Polygon::isPointInPolygon([0.5, 0.3], $triangle));
		$this->assertFalse(Polygon::isPointInPolygon([0.5, 1.5], $triangle));
	}

	public function testIsPointInPolygonComplex(): void
	{
		// Polygon plus complexe (exemple de Paris)
		$poly = [[
			[48.8534, 2.3488],
			[48.8634, 2.3588],
			[48.8634, 2.3688],
			[48.8534, 2.3688],
			[48.8534, 2.3488]
		]];

		$this->assertTrue(Polygon::isPointInPolygon([48.8584, 2.3588], $poly));
		$this->assertFalse(Polygon::isPointInPolygon([48.8784, 2.3588], $poly));
	}

	public function testIsPointInPolygonNegativeCoordinates(): void
	{
		$poly = [[[- 1, -1], [-1, 1], [1, 1], [1, -1], [-1, -1]]];
		$this->assertTrue(Polygon::isPointInPolygon([0, 0], $poly));
		$this->assertFalse(Polygon::isPointInPolygon([2, 2], $poly));
	}

	public function testIsPointInPolygonWithMultipleHoles(): void
	{
		$poly = [
			[[0, 0], [0, 10], [10, 10], [10, 0], [0, 0]], // outer
			[[1, 1], [1, 3], [3, 3], [3, 1], [1, 1]],     // hole 1
			[[5, 5], [5, 7], [7, 7], [7, 5], [5, 5]]      // hole 2
		];

		$this->assertTrue(Polygon::isPointInPolygon([4, 4], $poly));  // entre les trous
		$this->assertFalse(Polygon::isPointInPolygon([2, 2], $poly)); // dans hole 1
		$this->assertFalse(Polygon::isPointInPolygon([6, 6], $poly)); // dans hole 2
	}
}