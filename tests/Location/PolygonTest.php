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

	/* ===================== getCentroid() ===================== */

	public function testGetCentroidEmptyArray(): void
	{
		$this->assertNull(Polygon::getCentroid([]));
	}

	public function testGetCentroidInvalidPoints(): void
	{
		// Tableau avec des points invalides (pas de coordonnées)
		$polygon = [null, [], 'invalid'];
		$this->assertNull(Polygon::getCentroid($polygon));
	}

	public function testGetCentroidSinglePoint(): void
	{
		$polygon = [[48.8566, 2.3522]]; // Paris
		$centroid = Polygon::getCentroid($polygon);
		$this->assertNotNull($centroid);
		$this->assertEqualsWithDelta(48.8566, $centroid[0], 0.0001);
		$this->assertEqualsWithDelta(2.3522, $centroid[1], 0.0001);
	}

	public function testGetCentroidTriangle(): void
	{
		// Triangle équilatéral centré sur l'origine
		$polygon = [
			[0.0, 1.0],
			[-0.866, -0.5],
			[0.866, -0.5]
		];
		$centroid = Polygon::getCentroid($polygon);
		$this->assertNotNull($centroid);
		// Le centroïde d'un triangle est la moyenne des sommets
		$this->assertEqualsWithDelta(0.0, $centroid[0], 0.001);
		$this->assertEqualsWithDelta(0.0, $centroid[1], 0.001);
	}

	public function testGetCentroidSquare(): void
	{
		// Carré centré sur (0.5, 0.5)
		$polygon = [
			[0.0, 0.0],
			[0.0, 1.0],
			[1.0, 1.0],
			[1.0, 0.0]
		];
		$centroid = Polygon::getCentroid($polygon);
		$this->assertNotNull($centroid);
		$this->assertEqualsWithDelta(0.5, $centroid[0], 0.0001);
		$this->assertEqualsWithDelta(0.5, $centroid[1], 0.0001);
	}

	public function testGetCentroidSquareClosed(): void
	{
		// Carré fermé (premier point répété à la fin)
		$polygon = [
			[0.0, 0.0],
			[0.0, 1.0],
			[1.0, 1.0],
			[1.0, 0.0],
			[0.0, 0.0] // Point fermant
		];
		$centroid = Polygon::getCentroid($polygon);
		$this->assertNotNull($centroid);
		// La moyenne inclut le point en double, donc légèrement différent
		$this->assertEqualsWithDelta(0.4, $centroid[0], 0.0001);
		$this->assertEqualsWithDelta(0.4, $centroid[1], 0.0001);
	}

	public function testGetCentroidNegativeCoordinates(): void
	{
		// Polygone avec coordonnées négatives
		$polygon = [
			[-1.0, -1.0],
			[-1.0, 1.0],
			[1.0, 1.0],
			[1.0, -1.0]
		];
		$centroid = Polygon::getCentroid($polygon);
		$this->assertNotNull($centroid);
		$this->assertEqualsWithDelta(0.0, $centroid[0], 0.0001);
		$this->assertEqualsWithDelta(0.0, $centroid[1], 0.0001);
	}

	public function testGetCentroidRealWorldCoordinates(): void
	{
		// Polygone autour de Paris
		$polygon = [
			[48.8566, 2.3522], // Centre de Paris
			[48.8766, 2.3522],
			[48.8766, 2.3722],
			[48.8566, 2.3722]
		];
		$centroid = Polygon::getCentroid($polygon);
		$this->assertNotNull($centroid);
		$this->assertEqualsWithDelta(48.8666, $centroid[0], 0.0001);
		$this->assertEqualsWithDelta(2.3622, $centroid[1], 0.0001);
	}

	public function testGetCentroidMixedValidInvalidPoints(): void
	{
		// Mélange de points valides et invalides
		$polygon = [
			[1.0, 2.0],
			null,
			[3.0, 4.0],
			'invalid',
			[5.0, 6.0],
			[]
		];
		$centroid = Polygon::getCentroid($polygon);
		$this->assertNotNull($centroid);
		// Moyenne de (1,2), (3,4), (5,6)
		$this->assertEqualsWithDelta(3.0, $centroid[0], 0.0001);
		$this->assertEqualsWithDelta(4.0, $centroid[1], 0.0001);
	}

	public function testGetCentroidIrregularPolygon(): void
	{
		// Polygone irrégulier
		$polygon = [
			[0.0, 0.0],
			[1.0, 0.0],
			[1.5, 1.0],
			[0.5, 2.0],
			[-0.5, 1.0]
		];
		$centroid = Polygon::getCentroid($polygon);
		$this->assertNotNull($centroid);
		// Moyenne arithmétique simple des coordonnées
		$this->assertEqualsWithDelta(0.5, $centroid[0], 0.0001);
		$this->assertEqualsWithDelta(0.8, $centroid[1], 0.0001);
	}

	public function testGetCentroidWithPolygonFormat(): void
	{
		// Polygone au format [[[lat, lon], ...]] (tableau de rings)
		$polygon = [[
			[0.0, 0.0],
			[0.0, 1.0],
			[1.0, 1.0],
			[1.0, 0.0]
		]];
		$centroid = Polygon::getCentroid($polygon);
		$this->assertNotNull($centroid);
		$this->assertEqualsWithDelta(0.5, $centroid[0], 0.0001);
		$this->assertEqualsWithDelta(0.5, $centroid[1], 0.0001);
	}

	public function testGetCentroidWithPolygonAndHoles(): void
	{
		// Polygone avec trous (le centroïde est calculé sur le ring extérieur uniquement)
		$polygon = [
			[[0, 0], [0, 10], [10, 10], [10, 0], [0, 0]], // outer ring
			[[2, 2], [2, 4], [4, 4], [4, 2], [2, 2]]      // hole (ignoré)
		];
		$centroid = Polygon::getCentroid($polygon);
		$this->assertNotNull($centroid);
		// Centroïde du ring extérieur uniquement
		$this->assertEqualsWithDelta(4.0, $centroid[0], 0.0001);
		$this->assertEqualsWithDelta(4.0, $centroid[1], 0.0001);
	}

	public function testGetCentroidRealWorldPolygonFormat(): void
	{
		// Format polygone complet avec coordonnées GPS réelles
		$polygon = [[
			[48.8566, 2.3522], // Paris
			[48.8766, 2.3522],
			[48.8766, 2.3722],
			[48.8566, 2.3722]
		]];
		$centroid = Polygon::getCentroid($polygon);
		$this->assertNotNull($centroid);
		$this->assertEqualsWithDelta(48.8666, $centroid[0], 0.0001);
		$this->assertEqualsWithDelta(2.3622, $centroid[1], 0.0001);
	}
}