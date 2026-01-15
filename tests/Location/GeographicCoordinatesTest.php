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

	/* ===================== gpsRationalToFloat() ===================== */

	public function testGpsRationalToFloatWithValidRational(): void
	{
		$this->assertSame(40.0, GeographicCoordinates::gpsRationalToFloat('40/1'));
		$this->assertSame(26.0, GeographicCoordinates::gpsRationalToFloat('26/1'));
		$this->assertSame(46.0, GeographicCoordinates::gpsRationalToFloat('46/1'));
	}

	public function testGpsRationalToFloatWithFraction(): void
	{
		$this->assertSame(0.5, GeographicCoordinates::gpsRationalToFloat('1/2'));
		$this->assertSame(0.25, GeographicCoordinates::gpsRationalToFloat('1/4'));
		$this->assertSame(2.5, GeographicCoordinates::gpsRationalToFloat('5/2'));
	}

	public function testGpsRationalToFloatWithZeroDenominator(): void
	{
		$this->assertSame(0.0, GeographicCoordinates::gpsRationalToFloat('40/0'));
		$this->assertSame(0.0, GeographicCoordinates::gpsRationalToFloat('100/0'));
	}

	public function testGpsRationalToFloatWithInvalidFormat(): void
	{
		$this->assertSame(0.0, GeographicCoordinates::gpsRationalToFloat('40'));
		$this->assertSame(0.0, GeographicCoordinates::gpsRationalToFloat('invalid'));
		$this->assertSame(0.0, GeographicCoordinates::gpsRationalToFloat('40/1/2'));
	}

	/* ===================== gpsToDecimal() ===================== */

	public function testGpsToDecimalWithNorthernLatitude(): void
	{
		// 40°26'46" N = 40.446111°
		$coordinate = ['40/1', '26/1', '46/1'];
		$result = GeographicCoordinates::gpsToDecimal($coordinate, 'N');
		$this->assertEqualsWithDelta(40.446111, $result, 0.000001);
	}

	public function testGpsToDecimalWithSouthernLatitude(): void
	{
		// 33°51'35" S = -33.859722°
		$coordinate = ['33/1', '51/1', '35/1'];
		$result = GeographicCoordinates::gpsToDecimal($coordinate, 'S');
		$this->assertEqualsWithDelta(-33.859722, $result, 0.000001);
	}

	public function testGpsToDecimalWithEasternLongitude(): void
	{
		// 151°12'51" E = 151.214167°
		$coordinate = ['151/1', '12/1', '51/1'];
		$result = GeographicCoordinates::gpsToDecimal($coordinate, 'E');
		$this->assertEqualsWithDelta(151.214167, $result, 0.000001);
	}

	public function testGpsToDecimalWithWesternLongitude(): void
	{
		// 74°0'21" W = -74.005833°
		$coordinate = ['74/1', '0/1', '21/1'];
		$result = GeographicCoordinates::gpsToDecimal($coordinate, 'W');
		$this->assertEqualsWithDelta(-74.005833, $result, 0.000001);
	}

	public function testGpsToDecimalWithFractionalValues(): void
	{
		// Test with fractional degrees, minutes, and seconds
		$coordinate = ['40/1', '26/1', '923/20']; // 46.15 seconds
		$result = GeographicCoordinates::gpsToDecimal($coordinate, 'N');
		$this->assertEqualsWithDelta(40.446153, $result, 0.000001);
	}

	public function testGpsToDecimalWithZeroValues(): void
	{
		// 0°0'0" N = 0.0°
		$coordinate = ['0/1', '0/1', '0/1'];
		$result = GeographicCoordinates::gpsToDecimal($coordinate, 'N');
		$this->assertSame(0.0, $result);
	}

	public function testGpsToDecimalWithOnlyDegrees(): void
	{
		// Only degrees provided
		$coordinate = ['48/1'];
		$result = GeographicCoordinates::gpsToDecimal($coordinate, 'N');
		$this->assertSame(48.0, $result);
	}

	public function testGpsToDecimalWithDegreesAndMinutes(): void
	{
		// Degrees and minutes only, no seconds
		$coordinate = ['48/1', '51/1'];
		$result = GeographicCoordinates::gpsToDecimal($coordinate, 'N');
		$this->assertSame(48.85, $result);
	}

	public function testGpsToDecimalWithEmptyArray(): void
	{
		$coordinate = [];
		$result = GeographicCoordinates::gpsToDecimal($coordinate, 'N');
		$this->assertSame(0.0, $result);
	}

	public function testGpsToDecimalRealWorldExample(): void
	{
		// Eiffel Tower GPS coordinates
		// 48°51'29.6"N, 2°17'40.2"E
		$latitude = ['48/1', '51/1', '296/10']; // 29.6 seconds
		$longitude = ['2/1', '17/1', '402/10']; // 40.2 seconds

		$lat = GeographicCoordinates::gpsToDecimal($latitude, 'N');
		$lng = GeographicCoordinates::gpsToDecimal($longitude, 'E');

		$this->assertEqualsWithDelta(48.858222, $lat, 0.000001);
		$this->assertEqualsWithDelta(2.294500, $lng, 0.000001);
	}
}