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

	/* ===================== isPointInMultiPolygon() ===================== */

	public function testIsPointInMultiPolygonEmptyArray(): void
	{
		$multiPolygon = [];
		$this->assertFalse(Polygon::isPointInMultiPolygon([0.5, 0.5], $multiPolygon));
	}

	public function testIsPointInMultiPolygonSinglePolygon(): void
	{
		// MultiPolygon avec un seul polygone (carré)
		$multiPolygon = [
			[[[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]]]
		];
		$this->assertTrue(Polygon::isPointInMultiPolygon([0.5, 0.5], $multiPolygon));
		$this->assertFalse(Polygon::isPointInMultiPolygon([2.0, 2.0], $multiPolygon));
	}

	public function testIsPointInMultiPolygonMultiplePolygons(): void
	{
		// MultiPolygon avec deux polygones séparés (ex: pays avec îles)
		$multiPolygon = [
			[[[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]]], // Polygone 1
			[[[5, 5], [5, 6], [6, 6], [6, 5], [5, 5]]]  // Polygone 2
		];

		$this->assertTrue(Polygon::isPointInMultiPolygon([0.5, 0.5], $multiPolygon));  // Dans polygone 1
		$this->assertTrue(Polygon::isPointInMultiPolygon([5.5, 5.5], $multiPolygon));  // Dans polygone 2
		$this->assertFalse(Polygon::isPointInMultiPolygon([3.0, 3.0], $multiPolygon)); // Dans aucun
	}

	public function testIsPointInMultiPolygonWithHoles(): void
	{
		// MultiPolygon avec un polygone contenant un trou
		$multiPolygon = [
			[
				[[0, 0], [0, 10], [10, 10], [10, 0], [0, 0]], // outer ring
				[[4, 4], [4, 6], [6, 6], [6, 4], [4, 4]]      // hole
			]
		];

		$this->assertTrue(Polygon::isPointInMultiPolygon([2, 2], $multiPolygon));  // Dans outer, hors trou
		$this->assertFalse(Polygon::isPointInMultiPolygon([5, 5], $multiPolygon)); // Dans le trou
		$this->assertFalse(Polygon::isPointInMultiPolygon([15, 15], $multiPolygon)); // Dehors
	}

	public function testIsPointInMultiPolygonRealWorldExample(): void
	{
		// Exemple réaliste: France métropolitaine + Corse (simplifié)
		$multiPolygon = [
			// France métropolitaine (carré simplifié)
			[[[43.0, -1.0], [43.0, 8.0], [51.0, 8.0], [51.0, -1.0], [43.0, -1.0]]],
			// Corse (carré simplifié)
			[[[41.0, 8.5], [41.0, 9.5], [43.0, 9.5], [43.0, 8.5], [41.0, 8.5]]]
		];

		$this->assertTrue(Polygon::isPointInMultiPolygon([48.8566, 2.3522], $multiPolygon));  // Paris (métropole)
		$this->assertTrue(Polygon::isPointInMultiPolygon([42.0, 9.0], $multiPolygon));        // Ajaccio (Corse)
		$this->assertFalse(Polygon::isPointInMultiPolygon([40.0, 9.0], $multiPolygon));       // Sardaigne (hors France)
	}

	public function testIsPointInMultiPolygonOnBoundary(): void
	{
		$multiPolygon = [
			[[[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]]]
		];
		// Point sur le bord du polygone
		$this->assertTrue(Polygon::isPointInMultiPolygon([0, 0.5], $multiPolygon));
	}

	/* ===================== calculateArea() ===================== */

	public function testCalculateAreaSquare(): void
	{
		// Carré de 1x1
		$ring = [[0, 0], [0, 1], [1, 1], [1, 0]];
		$area = Polygon::calculateArea($ring);
		$this->assertEqualsWithDelta(1.0, $area, 0.0001);
	}

	public function testCalculateAreaSquareClosed(): void
	{
		// Carré de 1x1 fermé (premier point répété)
		$ring = [[0, 0], [0, 1], [1, 1], [1, 0], [0, 0]];
		$area = Polygon::calculateArea($ring);
		$this->assertEqualsWithDelta(1.0, $area, 0.0001);
	}

	public function testCalculateAreaTriangle(): void
	{
		// Triangle rectangle de base 2 et hauteur 2 (aire = 0.5 * 2 * 2 = 2)
		$ring = [[0, 0], [0, 2], [2, 0]];
		$area = Polygon::calculateArea($ring);
		$this->assertEqualsWithDelta(2.0, $area, 0.0001);
	}

	public function testCalculateAreaRectangle(): void
	{
		// Rectangle de 2x3 (aire = 6)
		$ring = [[0, 0], [0, 2], [3, 2], [3, 0]];
		$area = Polygon::calculateArea($ring);
		$this->assertEqualsWithDelta(6.0, $area, 0.0001);
	}

	public function testCalculateAreaIrregularPolygon(): void
	{
		// Polygone irrégulier (pentagone)
		$ring = [[0, 0], [2, 0], [3, 1], [1, 2], [0, 1]];
		$area = Polygon::calculateArea($ring);
		// Calcul avec Shoelace formula: aire = 4.0
		$this->assertGreaterThan(0, $area);
		$this->assertEqualsWithDelta(4.0, $area, 0.0001);
	}

	public function testCalculateAreaTooFewPoints(): void
	{
		// Moins de 3 points = polygone invalide
		$ring = [[0, 0], [1, 1]];
		$area = Polygon::calculateArea($ring);
		$this->assertSame(0.0, $area);
	}

	public function testCalculateAreaEmptyArray(): void
	{
		$ring = [];
		$area = Polygon::calculateArea($ring);
		$this->assertSame(0.0, $area);
	}

	public function testCalculateAreaSinglePoint(): void
	{
		$ring = [[0, 0]];
		$area = Polygon::calculateArea($ring);
		$this->assertSame(0.0, $area);
	}

	public function testCalculateAreaNegativeCoordinates(): void
	{
		// Carré centré sur l'origine
		$ring = [[-1, -1], [-1, 1], [1, 1], [1, -1]];
		$area = Polygon::calculateArea($ring);
		$this->assertEqualsWithDelta(4.0, $area, 0.0001);
	}

	public function testCalculateAreaRealWorldCoordinates(): void
	{
		// Petit carré à Paris (environ 0.01° × 0.01°)
		$ring = [
			[48.85, 2.35],
			[48.85, 2.36],
			[48.86, 2.36],
			[48.86, 2.35]
		];
		$area = Polygon::calculateArea($ring);
		$this->assertEqualsWithDelta(0.0001, $area, 0.00001);
	}

	public function testCalculateAreaCounterClockwise(): void
	{
		// Carré dans le sens antihoraire (devrait donner le même résultat en valeur absolue)
		$ring = [[0, 0], [1, 0], [1, 1], [0, 1]];
		$area = Polygon::calculateArea($ring);
		$this->assertEqualsWithDelta(1.0, $area, 0.0001);
	}

	public function testCalculateAreaComplexPolygon(): void
	{
		// Polygone en forme de L
		$ring = [
			[0, 0], [0, 3], [1, 3], [1, 1], [3, 1], [3, 0]
		];
		$area = Polygon::calculateArea($ring);
		// Aire = 3×1 + 2×1 = 5
		$this->assertEqualsWithDelta(5.0, $area, 0.1);
	}

	/* ===================== calculateAreaWithHoles() ===================== */

	public function testCalculateAreaWithHolesEmptyPolygon(): void
	{
		$polygon = [];
		$area = Polygon::calculateAreaWithHoles($polygon);
		$this->assertSame(0.0, $area);
	}

	public function testCalculateAreaWithHolesNoHoles(): void
	{
		// Carré sans trou (juste le ring extérieur)
		$polygon = [
			[[0, 0], [0, 10], [10, 10], [10, 0]]
		];
		$area = Polygon::calculateAreaWithHoles($polygon);
		$this->assertEqualsWithDelta(100.0, $area, 0.0001);
	}

	public function testCalculateAreaWithHolesSingleHole(): void
	{
		// Carré 10×10 avec un trou 2×2 au centre
		$polygon = [
			[[0, 0], [0, 10], [10, 10], [10, 0]], // outer: aire = 100
			[[4, 4], [4, 6], [6, 6], [6, 4]]       // hole: aire = 4
		];
		$area = Polygon::calculateAreaWithHoles($polygon);
		$this->assertEqualsWithDelta(96.0, $area, 0.0001); // 100 - 4 = 96
	}

	public function testCalculateAreaWithHolesMultipleHoles(): void
	{
		// Carré 10×10 avec deux trous
		$polygon = [
			[[0, 0], [0, 10], [10, 10], [10, 0]], // outer: aire = 100
			[[1, 1], [1, 3], [3, 3], [3, 1]],     // hole1: aire = 4
			[[6, 6], [6, 8], [8, 8], [8, 6]]      // hole2: aire = 4
		];
		$area = Polygon::calculateAreaWithHoles($polygon);
		$this->assertEqualsWithDelta(92.0, $area, 0.0001); // 100 - 4 - 4 = 92
	}

	public function testCalculateAreaWithHolesLargeHole(): void
	{
		// Trou très grand qui prend presque toute la surface
		$polygon = [
			[[0, 0], [0, 10], [10, 10], [10, 0]], // outer: aire = 100
			[[1, 1], [1, 9], [9, 9], [9, 1]]       // hole: aire = 64
		];
		$area = Polygon::calculateAreaWithHoles($polygon);
		$this->assertEqualsWithDelta(36.0, $area, 0.0001); // 100 - 64 = 36
	}

	public function testCalculateAreaWithHolesTooLarge(): void
	{
		// Cas limite: trou plus grand que l'extérieur (géométriquement invalide)
		// La méthode utilise max(0.0, totalArea) pour éviter les valeurs négatives
		$polygon = [
			[[0, 0], [0, 5], [5, 5], [5, 0]],      // outer: aire = 25
			[[0, 0], [0, 10], [10, 10], [10, 0]]   // hole: aire = 100
		];
		$area = Polygon::calculateAreaWithHoles($polygon);
		$this->assertSame(0.0, $area); // max(0.0, 25 - 100) = 0
	}

	public function testCalculateAreaWithHolesRealWorldExample(): void
	{
		// Exemple réaliste: bâtiment avec cour intérieure
		$polygon = [
			[[0, 0], [0, 20], [20, 20], [20, 0]],  // Bâtiment 20×20 = 400
			[[5, 5], [5, 15], [15, 15], [15, 5]]   // Cour 10×10 = 100
		];
		$area = Polygon::calculateAreaWithHoles($polygon);
		$this->assertEqualsWithDelta(300.0, $area, 0.0001); // 400 - 100 = 300
	}

	public function testCalculateAreaWithHolesTriangleWithHole(): void
	{
		// Triangle avec un petit trou triangulaire
		$polygon = [
			[[0, 0], [0, 4], [4, 0]],           // Triangle: aire = 8
			[[1, 1], [1, 2], [2, 1]]            // Petit triangle: aire = 0.5
		];
		$area = Polygon::calculateAreaWithHoles($polygon);
		$this->assertEqualsWithDelta(7.5, $area, 0.1); // 8 - 0.5 = 7.5
	}

	public function testCalculateAreaWithHolesOnlyOuterRing(): void
	{
		// Polygone complexe sans trous
		$polygon = [
			[[0, 0], [2, 0], [3, 1], [1, 2], [0, 1]]
		];
		$area = Polygon::calculateAreaWithHoles($polygon);
		$this->assertGreaterThan(0, $area);
		$this->assertEqualsWithDelta(4.0, $area, 0.0001);
	}
}