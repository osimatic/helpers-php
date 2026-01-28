<?php

declare(strict_types=1);

namespace Tests\Location;

use Osimatic\Location\GeographicCoordinates;
use PHPUnit\Framework\TestCase;

final class GeographicCoordinatesTest extends TestCase
{
	/* ===================== isValid() ===================== */

	public function testIsValidValid(): void
	{
		$this->assertTrue(GeographicCoordinates::isValid('48.8584,2.2945'));
		$this->assertTrue(GeographicCoordinates::isValid('0,0'));
		$this->assertTrue(GeographicCoordinates::isValid('90,180'));
		$this->assertTrue(GeographicCoordinates::isValid('-90,-180'));
		$this->assertTrue(GeographicCoordinates::isValid('45.5,-73.5'));
		$this->assertTrue(GeographicCoordinates::isValid('48.8584, 2.2945')); // avec espace
	}

	public function testIsValidValidEdgeCases(): void
	{
		$this->assertTrue(GeographicCoordinates::isValid('90,0'));
		$this->assertTrue(GeographicCoordinates::isValid('-90,0'));
		$this->assertTrue(GeographicCoordinates::isValid('0,180'));
		$this->assertTrue(GeographicCoordinates::isValid('0,-180'));
		$this->assertTrue(GeographicCoordinates::isValid('90.0,180.0'));
	}

	public function testIsValidInvalidLatitude(): void
	{
		$this->assertFalse(GeographicCoordinates::isValid('91,0')); // latitude > 90
		$this->assertFalse(GeographicCoordinates::isValid('-91,0')); // latitude < -90
		$this->assertFalse(GeographicCoordinates::isValid('90.1,0')); // latitude > 90
	}

	public function testIsValidInvalidLongitude(): void
	{
		$this->assertFalse(GeographicCoordinates::isValid('0,181')); // longitude > 180
		$this->assertFalse(GeographicCoordinates::isValid('0,-181')); // longitude < -180
		$this->assertFalse(GeographicCoordinates::isValid('0,180.1')); // longitude > 180
	}

	public function testIsValidInvalidFormat(): void
	{
		$this->assertFalse(GeographicCoordinates::isValid('abc,def')); // non numérique
		$this->assertFalse(GeographicCoordinates::isValid('48.8584')); // incomplet
		$this->assertFalse(GeographicCoordinates::isValid('')); // vide
		$this->assertFalse(GeographicCoordinates::isValid('48.8584;2.2945')); // mauvais séparateur
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

	/* ===================== format() ===================== */

	public function testFormatWithDefaultPrecision(): void
	{
		// Default precision is 6 decimal places
		$result = GeographicCoordinates::format(48.8584, 2.2945);
		$this->assertSame('48.858400,2.294500', $result);

		$result = GeographicCoordinates::format(40.7128, -74.0060);
		$this->assertSame('40.712800,-74.006000', $result);
	}

	public function testFormatWithZeroPrecision(): void
	{
		// Precision 0: ~111 km accuracy (country/region level)
		$result = GeographicCoordinates::format(48.8584, 2.2945, 0);
		$this->assertSame('49,2', $result);

		$result = GeographicCoordinates::format(48.4584, 2.7945, 0);
		$this->assertSame('48,3', $result);

		$result = GeographicCoordinates::format(-33.8688, 151.2093, 0);
		$this->assertSame('-34,151', $result);
	}

	public function testFormatWithOnePrecision(): void
	{
		// Precision 1: ~11 km accuracy (large city level)
		$result = GeographicCoordinates::format(48.8584, 2.2945, 1);
		$this->assertSame('48.9,2.3', $result);

		$result = GeographicCoordinates::format(40.7128, -74.0060, 1);
		$this->assertSame('40.7,-74.0', $result);
	}

	public function testFormatWithTwoPrecision(): void
	{
		// Precision 2: ~1.1 km accuracy (village level)
		$result = GeographicCoordinates::format(48.8584, 2.2945, 2);
		$this->assertSame('48.86,2.29', $result);

		$result = GeographicCoordinates::format(-33.8688, 151.2093, 2);
		$this->assertSame('-33.87,151.21', $result);
	}

	public function testFormatWithThreePrecision(): void
	{
		// Precision 3: ~110 m accuracy (neighborhood level)
		$result = GeographicCoordinates::format(48.8584, 2.2945, 3);
		$this->assertSame('48.858,2.295', $result);

		$result = GeographicCoordinates::format(51.5074, -0.1278, 3);
		$this->assertSame('51.507,-0.128', $result);
	}

	public function testFormatWithFourPrecision(): void
	{
		// Precision 4: ~11 m accuracy (individual street level)
		$result = GeographicCoordinates::format(48.8584, 2.2945, 4);
		$this->assertSame('48.8584,2.2945', $result);

		$result = GeographicCoordinates::format(35.6762, 139.6503, 4);
		$this->assertSame('35.6762,139.6503', $result);
	}

	public function testFormatWithFivePrecision(): void
	{
		// Precision 5: ~1.1 m accuracy (individual tree level)
		$result = GeographicCoordinates::format(48.858370, 2.294481, 5);
		$this->assertSame('48.85837,2.29448', $result);

		$result = GeographicCoordinates::format(-22.906847, -43.172896, 5);
		$this->assertSame('-22.90685,-43.17290', $result);
	}

	public function testFormatWithSevenPrecision(): void
	{
		// Precision 7: centimeter-level precision (surveying)
		$result = GeographicCoordinates::format(48.85837012, 2.29448123, 7);
		$this->assertSame('48.8583701,2.2944812', $result);

		$result = GeographicCoordinates::format(1.352083, 103.819839, 7);
		$this->assertSame('1.3520830,103.8198390', $result);
	}

	public function testFormatWithEightPrecision(): void
	{
		// Precision 8: millimeter-level precision
		$result = GeographicCoordinates::format(48.858370123, 2.294481234, 8);
		$this->assertSame('48.85837012,2.29448123', $result);
	}

	public function testFormatWithZeroCoordinates(): void
	{
		$result = GeographicCoordinates::format(0.0, 0.0);
		$this->assertSame('0.000000,0.000000', $result);

		$result = GeographicCoordinates::format(0.0, 0.0, 2);
		$this->assertSame('0.00,0.00', $result);

		$result = GeographicCoordinates::format(0.0, 0.0, 0);
		$this->assertSame('0,0', $result);
	}

	public function testFormatWithNegativeCoordinates(): void
	{
		// Southern hemisphere and western hemisphere
		$result = GeographicCoordinates::format(-33.8688, -151.2093, 4);
		$this->assertSame('-33.8688,-151.2093', $result);

		$result = GeographicCoordinates::format(-90.0, -180.0, 2);
		$this->assertSame('-90.00,-180.00', $result);
	}

	public function testFormatWithMixedSignCoordinates(): void
	{
		// Northern hemisphere, western hemisphere
		$result = GeographicCoordinates::format(40.7128, -74.0060, 4);
		$this->assertSame('40.7128,-74.0060', $result);

		// Southern hemisphere, eastern hemisphere
		$result = GeographicCoordinates::format(-33.8688, 151.2093, 4);
		$this->assertSame('-33.8688,151.2093', $result);
	}

	public function testFormatWithBoundaryCoordinates(): void
	{
		// Maximum latitude north
		$result = GeographicCoordinates::format(90.0, 0.0, 4);
		$this->assertSame('90.0000,0.0000', $result);

		// Maximum latitude south
		$result = GeographicCoordinates::format(-90.0, 0.0, 4);
		$this->assertSame('-90.0000,0.0000', $result);

		// Maximum longitude east
		$result = GeographicCoordinates::format(0.0, 180.0, 4);
		$this->assertSame('0.0000,180.0000', $result);

		// Maximum longitude west
		$result = GeographicCoordinates::format(0.0, -180.0, 4);
		$this->assertSame('0.0000,-180.0000', $result);

		// All corners
		$result = GeographicCoordinates::format(90.0, 180.0, 2);
		$this->assertSame('90.00,180.00', $result);

		$result = GeographicCoordinates::format(-90.0, -180.0, 2);
		$this->assertSame('-90.00,-180.00', $result);
	}

	public function testFormatRoundsCorrectly(): void
	{
		// Test rounding down
		$result = GeographicCoordinates::format(48.8584449, 2.2945449, 4);
		$this->assertSame('48.8584,2.2945', $result);

		// Test rounding up
		$result = GeographicCoordinates::format(48.8584551, 2.2945551, 4);
		$this->assertSame('48.8585,2.2946', $result);

		// Test exact midpoint (rounds to nearest even by default in number_format)
		$result = GeographicCoordinates::format(48.85845, 2.29455, 4);
		$this->assertSame('48.8585,2.2946', $result);
	}

	public function testFormatWithVeryHighPrecisionInput(): void
	{
		// Input with many decimal places, formatted to lower precision
		$result = GeographicCoordinates::format(48.858370123456789, 2.294481234567890, 6);
		$this->assertSame('48.858370,2.294481', $result);

		$result = GeographicCoordinates::format(48.858370123456789, 2.294481234567890, 3);
		$this->assertSame('48.858,2.294', $result);
	}

	public function testFormatRealWorldLocations(): void
	{
		// Paris - Eiffel Tower
		$result = GeographicCoordinates::format(48.8584, 2.2945, 4);
		$this->assertSame('48.8584,2.2945', $result);

		// New York - Times Square
		$result = GeographicCoordinates::format(40.758896, -73.985130, 5);
		$this->assertSame('40.75890,-73.98513', $result);

		// Tokyo - Tokyo Tower
		$result = GeographicCoordinates::format(35.6586, 139.7454, 4);
		$this->assertSame('35.6586,139.7454', $result);

		// Sydney - Opera House
		$result = GeographicCoordinates::format(-33.8568, 151.2153, 4);
		$this->assertSame('-33.8568,151.2153', $result);

		// London - Big Ben
		$result = GeographicCoordinates::format(51.5007, -0.1246, 4);
		$this->assertSame('51.5007,-0.1246', $result);

		// Rio de Janeiro - Christ the Redeemer
		$result = GeographicCoordinates::format(-22.9519, -43.2105, 4);
		$this->assertSame('-22.9519,-43.2105', $result);
	}

	public function testFormatConsistentOutput(): void
	{
		// Same input should always produce same output
		$result1 = GeographicCoordinates::format(48.8584, 2.2945, 6);
		$result2 = GeographicCoordinates::format(48.8584, 2.2945, 6);
		$this->assertSame($result1, $result2);

		// Different precision should produce different output
		$result3 = GeographicCoordinates::format(48.8584, 2.2945, 4);
		$this->assertNotSame($result1, $result3);
	}

	public function testFormatNoSpacesInOutput(): void
	{
		// Ensure no spaces in the formatted output
		$result = GeographicCoordinates::format(48.8584, 2.2945, 6);
		$this->assertStringNotContainsString(' ', $result);
		$this->assertStringContainsString(',', $result);
	}

	public function testFormatDecimalSeparatorIsAlwaysDot(): void
	{
		// Ensure decimal separator is always a dot, not comma
		$result = GeographicCoordinates::format(48.8584, 2.2945, 6);
		$this->assertMatchesRegularExpression('/^\-?\d+\.\d+,\-?\d+\.\d+$/', $result);

		// With zero precision, no decimal separator
		$result = GeographicCoordinates::format(48.8584, 2.2945, 0);
		$this->assertMatchesRegularExpression('/^\-?\d+,\-?\d+$/', $result);
	}

	public function testFormatPrecisionZeroNoDecimalPoint(): void
	{
		$result = GeographicCoordinates::format(48.8584, 2.2945, 0);
		$this->assertStringNotContainsString('.', $result);
		$this->assertSame('49,2', $result);
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