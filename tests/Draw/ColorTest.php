<?php

declare(strict_types=1);

namespace Tests\Draw;

use Osimatic\Draw\Color;
use PHPUnit\Framework\TestCase;

final class ColorTest extends TestCase
{
	/* ===================== checkHexaColor() ===================== */

	public function testCheckHexaColorWithValidSixCharacterColor(): void
	{
		$this->assertTrue(Color::checkHexaColor('#FF0000'));
		$this->assertTrue(Color::checkHexaColor('#00FF00'));
		$this->assertTrue(Color::checkHexaColor('#0000FF'));
		$this->assertTrue(Color::checkHexaColor('#FFFFFF'));
		$this->assertTrue(Color::checkHexaColor('#000000'));
		$this->assertTrue(Color::checkHexaColor('#123456'));
		$this->assertTrue(Color::checkHexaColor('#AbCdEf'));
	}

	public function testCheckHexaColorWithValidThreeCharacterColor(): void
	{
		$this->assertTrue(Color::checkHexaColor('#F00'));
		$this->assertTrue(Color::checkHexaColor('#0F0'));
		$this->assertTrue(Color::checkHexaColor('#00F'));
		$this->assertTrue(Color::checkHexaColor('#FFF'));
		$this->assertTrue(Color::checkHexaColor('#000'));
		$this->assertTrue(Color::checkHexaColor('#Abc'));
	}

	public function testCheckHexaColorWithInvalidColor(): void
	{
		$this->assertFalse(Color::checkHexaColor('FF0000'));
		$this->assertFalse(Color::checkHexaColor('#GG0000'));
		$this->assertFalse(Color::checkHexaColor('#12345'));
		$this->assertFalse(Color::checkHexaColor('#1234567'));
		$this->assertFalse(Color::checkHexaColor('#FF'));
		$this->assertFalse(Color::checkHexaColor('invalid'));
		$this->assertFalse(Color::checkHexaColor(''));
	}

	/* ===================== rgbToHex() ===================== */

	public function testRgbToHexWithDiese(): void
	{
		$this->assertSame('#ff0000', Color::rgbToHex(255, 0, 0));
		$this->assertSame('#00ff00', Color::rgbToHex(0, 255, 0));
		$this->assertSame('#0000ff', Color::rgbToHex(0, 0, 255));
		$this->assertSame('#ffffff', Color::rgbToHex(255, 255, 255));
		$this->assertSame('#000000', Color::rgbToHex(0, 0, 0));
	}

	public function testRgbToHexWithoutDiese(): void
	{
		$this->assertSame('ff0000', Color::rgbToHex(255, 0, 0, null, false));
		$this->assertSame('00ff00', Color::rgbToHex(0, 255, 0, null, false));
		$this->assertSame('0000ff', Color::rgbToHex(0, 0, 255, null, false));
		$this->assertSame('ffffff', Color::rgbToHex(255, 255, 255, null, false));
		$this->assertSame('000000', Color::rgbToHex(0, 0, 0, null, false));
	}

	public function testRgbToHexWithPaddedValues(): void
	{
		$this->assertSame('#010203', Color::rgbToHex(1, 2, 3));
		$this->assertSame('#0a0b0c', Color::rgbToHex(10, 11, 12));
	}

	/* ===================== hexColorToRgb() ===================== */

	public function testHexColorToRgbWithSixCharacters(): void
	{
		$this->assertSame([255, 0, 0], Color::hexColorToRgb('#FF0000'));
		$this->assertSame([0, 255, 0], Color::hexColorToRgb('#00FF00'));
		$this->assertSame([0, 0, 255], Color::hexColorToRgb('#0000FF'));
		$this->assertSame([255, 255, 255], Color::hexColorToRgb('#FFFFFF'));
		$this->assertSame([0, 0, 0], Color::hexColorToRgb('#000000'));
	}

	public function testHexColorToRgbWithThreeCharacters(): void
	{
		$this->assertSame([255, 255, 255], Color::hexColorToRgb('#FFF'));
		$this->assertSame([0, 0, 0], Color::hexColorToRgb('#000'));
		$this->assertSame([17, 34, 51], Color::hexColorToRgb('#123'));
	}

	public function testHexColorToRgbWithoutDiese(): void
	{
		$this->assertSame([255, 0, 0], Color::hexColorToRgb('FF0000'));
		$this->assertSame([0, 255, 0], Color::hexColorToRgb('00FF00'));
		$this->assertSame([255, 255, 255], Color::hexColorToRgb('FFF'));
	}

	public function testHexColorToRgbWithLowerCase(): void
	{
		$this->assertSame([255, 0, 0], Color::hexColorToRgb('#ff0000'));
		$this->assertSame([171, 205, 239], Color::hexColorToRgb('#abcdef'));
	}

	public function testHexColorToRgbWithInvalidColor(): void
	{
		$this->assertNull(Color::hexColorToRgb('#GG0000'));
		$this->assertNull(Color::hexColorToRgb('#12345'));
		$this->assertNull(Color::hexColorToRgb('#1234567'));
		$this->assertNull(Color::hexColorToRgb('invalid'));
		$this->assertNull(Color::hexColorToRgb(''));
	}

	/* ===================== getRedFromHex() ===================== */

	public function testGetRedFromHex(): void
	{
		$this->assertSame(255, Color::getRedFromHex('#FF0000'));
		$this->assertSame(0, Color::getRedFromHex('#00FF00'));
		$this->assertSame(171, Color::getRedFromHex('#ABCDEF'));
		$this->assertSame(255, Color::getRedFromHex('#FFF'));
		$this->assertSame(0, Color::getRedFromHex('#000'));
	}

	public function testGetRedFromHexWithInvalidColor(): void
	{
		$this->assertNull(Color::getRedFromHex('invalid'));
		$this->assertNull(Color::getRedFromHex('#GG0000'));
	}

	/* ===================== getGreenFromHexa() ===================== */

	public function testGetGreenFromHexa(): void
	{
		$this->assertSame(0, Color::getGreenFromHex('#FF0000'));
		$this->assertSame(255, Color::getGreenFromHex('#00FF00'));
		$this->assertSame(205, Color::getGreenFromHex('#ABCDEF'));
		$this->assertSame(255, Color::getGreenFromHex('#FFF'));
		$this->assertSame(0, Color::getGreenFromHex('#000'));
	}

	public function testGetGreenFromHexaWithInvalidColor(): void
	{
		$this->assertNull(Color::getGreenFromHex('invalid'));
		$this->assertNull(Color::getGreenFromHex('#GG0000'));
	}

	/* ===================== getBlueFromHexa() ===================== */

	public function testGetBlueFromHexa(): void
	{
		$this->assertSame(0, Color::getBlueFromHex('#FF0000'));
		$this->assertSame(0, Color::getBlueFromHex('#00FF00'));
		$this->assertSame(255, Color::getBlueFromHex('#0000FF'));
		$this->assertSame(239, Color::getBlueFromHex('#ABCDEF'));
		$this->assertSame(255, Color::getBlueFromHex('#FFF'));
		$this->assertSame(0, Color::getBlueFromHex('#000'));
	}

	public function testGetBlueFromHexaWithInvalidColor(): void
	{
		$this->assertNull(Color::getBlueFromHex('invalid'));
		$this->assertNull(Color::getBlueFromHex('#GG0000'));
	}

	/* ===================== isLightColor() ===================== */

	public function testIsLightColorWithLightColors(): void
	{
		$this->assertTrue(Color::isLightColor(255, 255, 255)); // White
		$this->assertTrue(Color::isLightColor(200, 200, 200)); // Light gray
		$this->assertTrue(Color::isLightColor(255, 255, 0));   // Yellow
		$this->assertTrue(Color::isLightColor(0, 255, 0));     // Green
	}

	public function testIsLightColorWithDarkColors(): void
	{
		$this->assertFalse(Color::isLightColor(0, 0, 0));       // Black
		$this->assertFalse(Color::isLightColor(50, 50, 50));    // Dark gray
		$this->assertFalse(Color::isLightColor(255, 0, 0));     // Red
		$this->assertFalse(Color::isLightColor(0, 0, 255));     // Blue
		$this->assertFalse(Color::isLightColor(128, 0, 128));   // Purple
	}

	/* ===================== isLightHexColor() ===================== */

	public function testIsLightHexColorWithLightColors(): void
	{
		$this->assertTrue(Color::isLightHexColor('#FFFFFF'));  // White
		$this->assertTrue(Color::isLightHexColor('#FFFF00'));  // Yellow
		$this->assertTrue(Color::isLightHexColor('#00FF00'));  // Green
		$this->assertTrue(Color::isLightHexColor('#FFF'));     // White (short)
	}

	public function testIsLightHexColorWithDarkColors(): void
	{
		$this->assertFalse(Color::isLightHexColor('#000000')); // Black
		$this->assertFalse(Color::isLightHexColor('#FF0000')); // Red
		$this->assertFalse(Color::isLightHexColor('#0000FF')); // Blue
		$this->assertFalse(Color::isLightHexColor('#000'));    // Black (short)
	}

	public function testIsLightHexColorWithInvalidColor(): void
	{
		$this->assertFalse(Color::isLightHexColor('invalid'));
		$this->assertFalse(Color::isLightHexColor('#GG0000'));
	}

	/* ===================== getRandomColor() ===================== */

	public function testGetRandomColorReturnsArray(): void
	{
		$color = Color::getRandomColor();

		$this->assertIsArray($color);
		$this->assertCount(3, $color);
	}

	public function testGetRandomColorReturnsValidRgbValues(): void
	{
		$color = Color::getRandomColor();

		$this->assertIsInt($color[0]);
		$this->assertIsInt($color[1]);
		$this->assertIsInt($color[2]);
		$this->assertGreaterThanOrEqual(0, $color[0]);
		$this->assertLessThanOrEqual(255, $color[0]);
		$this->assertGreaterThanOrEqual(0, $color[1]);
		$this->assertLessThanOrEqual(255, $color[1]);
		$this->assertGreaterThanOrEqual(0, $color[2]);
		$this->assertLessThanOrEqual(255, $color[2]);
	}

	/* ===================== getRandomBlackAndWhiteColor() ===================== */

	public function testGetRandomBlackAndWhiteColorReturnsArray(): void
	{
		$color = Color::getRandomBlackAndWhiteColor();

		$this->assertIsArray($color);
		$this->assertCount(3, $color);
	}

	public function testGetRandomBlackAndWhiteColorReturnsGrayscale(): void
	{
		$color = Color::getRandomBlackAndWhiteColor();

		// All three values should be the same for grayscale
		$this->assertSame($color[0], $color[1]);
		$this->assertSame($color[1], $color[2]);
		$this->assertGreaterThanOrEqual(0, $color[0]);
		$this->assertLessThanOrEqual(255, $color[0]);
	}

	/* ===================== getRandomHexColor() ===================== */

	public function testGetRandomHexColorReturnsValidFormat(): void
	{
		$color = Color::getRandomHexColor();

		$this->assertIsString($color);
		$this->assertStringStartsWith('#', $color);
		$this->assertSame(7, strlen($color));
		$this->assertTrue(Color::checkHexaColor($color));
	}

	/* ===================== getRandomBlackAndWhiteHexColor() ===================== */

	public function testGetRandomBlackAndWhiteHexColorReturnsValidFormat(): void
	{
		$color = Color::getRandomBlackAndWhiteHexColor();

		$this->assertIsString($color);
		$this->assertStringStartsWith('#', $color);
		$this->assertSame(7, strlen($color));
		$this->assertTrue(Color::checkHexaColor($color));
	}

	public function testGetRandomBlackAndWhiteHexColorReturnsGrayscale(): void
	{
		$color = Color::getRandomBlackAndWhiteHexColor();
		$rgb = Color::hexColorToRgb($color);

		// All three RGB values should be the same for grayscale
		$this->assertSame($rgb[0], $rgb[1]);
		$this->assertSame($rgb[1], $rgb[2]);
	}

	/* ===================== Round-trip conversion ===================== */

	public function testRoundTripConversionRgbToHexToRgb(): void
	{
		$originalRed = 123;
		$originalGreen = 45;
		$originalBlue = 67;

		$hex = Color::rgbToHex($originalRed, $originalGreen, $originalBlue);
		$rgb = Color::hexColorToRgb($hex);

		$this->assertSame($originalRed, $rgb[0]);
		$this->assertSame($originalGreen, $rgb[1]);
		$this->assertSame($originalBlue, $rgb[2]);
	}

	public function testRoundTripConversionHexToRgbToHex(): void
	{
		$originalHex = '#1A2B3C';

		$rgb = Color::hexColorToRgb($originalHex);
		$hex = Color::rgbToHex($rgb[0], $rgb[1], $rgb[2]);

		$this->assertSame(strtolower($originalHex), $hex);
	}

	/* ===================== RGBA Support ===================== */

	public function testRgbToHexWithAlpha(): void
	{
		$this->assertSame('#ff0000ff', Color::rgbToHex(255, 0, 0, 1.0));
		$this->assertSame('#00ff0080', Color::rgbToHex(0, 255, 0, 0.5));
		$this->assertSame('#0000ff00', Color::rgbToHex(0, 0, 255, 0.0));
		$this->assertSame('#ffffff7f', Color::rgbToHex(255, 255, 255, 0.498)); // ~0.498 rounds to 127 (0x7f)
	}

	public function testRgbToHexWithAlphaWithoutHash(): void
	{
		$this->assertSame('ff0000ff', Color::rgbToHex(255, 0, 0, 1.0, false));
		$this->assertSame('00ff0080', Color::rgbToHex(0, 255, 0, 0.5, false));
	}

	public function testRgbToHexWithInvalidAlpha(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		Color::rgbToHex(255, 0, 0, 1.5);
	}

	public function testRgbToHexWithNegativeAlpha(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		Color::rgbToHex(255, 0, 0, -0.1);
	}

	public function testRgbToHexWithInvalidRgbValues(): void
	{
		$this->expectException(\InvalidArgumentException::class);
		Color::rgbToHex(256, 0, 0);
	}

	public function testHexToRgbWithEightCharacters(): void
	{
		$this->assertSame([255, 0, 0, 1.0], Color::hexToRgb('#FF0000FF'));
		$this->assertSame([0, 255, 0, 0.5], Color::hexToRgb('#00FF0080'));
		$this->assertSame([0, 0, 255, 0.0], Color::hexToRgb('#0000FF00'));
		$this->assertSame([255, 255, 255, 1.0], Color::hexToRgb('#FFFFFFFF'));
	}

	public function testHexToRgbWithEightCharactersWithoutHash(): void
	{
		$this->assertSame([255, 0, 0, 1.0], Color::hexToRgb('FF0000FF'));
		$this->assertSame([0, 255, 0, 0.5], Color::hexToRgb('00FF0080'));
	}

	/* ===================== getAlphaFromHex() ===================== */

	public function testGetAlphaFromHex(): void
	{
		$this->assertSame(1.0, Color::getAlphaFromHex('#FF0000FF'));
		$this->assertSame(0.5, Color::getAlphaFromHex('#00FF0080'));
		$this->assertSame(0.0, Color::getAlphaFromHex('#0000FF00'));
	}

	public function testGetAlphaFromHexWithoutAlphaChannel(): void
	{
		$this->assertNull(Color::getAlphaFromHex('#FF0000'));
		$this->assertNull(Color::getAlphaFromHex('#F00'));
	}

	public function testGetAlphaFromHexWithInvalidColor(): void
	{
		$this->assertNull(Color::getAlphaFromHex('invalid'));
		$this->assertNull(Color::getAlphaFromHex('#GG0000'));
	}

	/* ===================== HSL Conversions ===================== */

	public function testRgbToHsl(): void
	{
		$this->assertSame([0.0, 100.0, 50.0], Color::rgbToHsl(255, 0, 0));     // Red
		$this->assertSame([120.0, 100.0, 50.0], Color::rgbToHsl(0, 255, 0));   // Green
		$this->assertSame([240.0, 100.0, 50.0], Color::rgbToHsl(0, 0, 255));   // Blue
		$this->assertSame([0.0, 0.0, 100.0], Color::rgbToHsl(255, 255, 255));  // White
		$this->assertSame([0.0, 0.0, 0.0], Color::rgbToHsl(0, 0, 0));          // Black
		$this->assertSame([0.0, 0.0, 50.2], Color::rgbToHsl(128, 128, 128));   // Gray
	}

	public function testHslToRgb(): void
	{
		$this->assertSame([255, 0, 0], Color::hslToRgb(0, 100, 50));     // Red
		$this->assertSame([0, 255, 0], Color::hslToRgb(120, 100, 50));   // Green
		$this->assertSame([0, 0, 255], Color::hslToRgb(240, 100, 50));   // Blue
		$this->assertSame([255, 255, 255], Color::hslToRgb(0, 0, 100));  // White
		$this->assertSame([0, 0, 0], Color::hslToRgb(0, 0, 0));          // Black
		$this->assertSame([128, 128, 128], Color::hslToRgb(0, 0, 50));   // Gray
	}

	public function testRoundTripRgbToHslToRgb(): void
	{
		$originalRgb = [123, 45, 67];
		$hsl = Color::rgbToHsl(...$originalRgb);
		$rgb = Color::hslToRgb(...$hsl);

		$this->assertSame($originalRgb, $rgb);
	}

	/* ===================== HSV Conversions ===================== */

	public function testRgbToHsv(): void
	{
		$this->assertSame([0.0, 100.0, 100.0], Color::rgbToHsv(255, 0, 0));    // Red
		$this->assertSame([120.0, 100.0, 100.0], Color::rgbToHsv(0, 255, 0));  // Green
		$this->assertSame([240.0, 100.0, 100.0], Color::rgbToHsv(0, 0, 255));  // Blue
		$this->assertSame([0.0, 0.0, 100.0], Color::rgbToHsv(255, 255, 255));  // White
		$this->assertSame([0.0, 0.0, 0.0], Color::rgbToHsv(0, 0, 0));          // Black
		$this->assertSame([0.0, 0.0, 50.2], Color::rgbToHsv(128, 128, 128));   // Gray
	}

	public function testHsvToRgb(): void
	{
		$this->assertSame([255, 0, 0], Color::hsvToRgb(0, 100, 100));    // Red
		$this->assertSame([0, 255, 0], Color::hsvToRgb(120, 100, 100));  // Green
		$this->assertSame([0, 0, 255], Color::hsvToRgb(240, 100, 100));  // Blue
		$this->assertSame([255, 255, 255], Color::hsvToRgb(0, 0, 100));  // White
		$this->assertSame([0, 0, 0], Color::hsvToRgb(0, 0, 0));          // Black
		$this->assertSame([128, 128, 128], Color::hsvToRgb(0, 0, 50));   // Gray
	}

	public function testRoundTripRgbToHsvToRgb(): void
	{
		$originalRgb = [123, 45, 67];
		$hsv = Color::rgbToHsv(...$originalRgb);
		$rgb = Color::hsvToRgb(...$hsv);

		$this->assertSame($originalRgb, $rgb);
	}

	/* ===================== Color Manipulation ===================== */

	public function testLighten(): void
	{
		$red = '#FF0000';
		$lightened = Color::lighten($red, 20);

		$this->assertNotNull($lightened);
		$this->assertTrue(Color::checkHexColor($lightened));

		// The lightened color should have higher lightness
		$originalHsl = Color::rgbToHsl(...Color::hexToRgb($red));
		$lightenedHsl = Color::rgbToHsl(...Color::hexToRgb($lightened));
		$this->assertGreaterThan($originalHsl[2], $lightenedHsl[2]);
	}

	public function testLightenWithInvalidColor(): void
	{
		$this->assertNull(Color::lighten('invalid', 20));
	}

	public function testDarken(): void
	{
		$red = '#FF0000';
		$darkened = Color::darken($red, 20);

		$this->assertNotNull($darkened);
		$this->assertTrue(Color::checkHexColor($darkened));

		// The darkened color should have lower lightness
		$originalHsl = Color::rgbToHsl(...Color::hexToRgb($red));
		$darkenedHsl = Color::rgbToHsl(...Color::hexToRgb($darkened));
		$this->assertLessThan($originalHsl[2], $darkenedHsl[2]);
	}

	public function testDarkenWithInvalidColor(): void
	{
		$this->assertNull(Color::darken('invalid', 20));
	}

	public function testAdjustSaturation(): void
	{
		$red = '#FF0000';

		// Increase saturation (should stay at 100 for pure red)
		$moreSaturated = Color::adjustSaturation($red, 10);
		$this->assertNotNull($moreSaturated);

		// Decrease saturation
		$lessSaturated = Color::adjustSaturation($red, -50);
		$this->assertNotNull($lessSaturated);

		$originalHsl = Color::rgbToHsl(...Color::hexToRgb($red));
		$lessSaturatedHsl = Color::rgbToHsl(...Color::hexToRgb($lessSaturated));
		$this->assertLessThan($originalHsl[1], $lessSaturatedHsl[1]);
	}

	public function testAdjustSaturationWithInvalidColor(): void
	{
		$this->assertNull(Color::adjustSaturation('invalid', 20));
	}

	public function testAdjustHue(): void
	{
		$red = '#FF0000';

		// Rotate hue by 120 degrees should give green-ish color
		$rotated = Color::adjustHue($red, 120);
		$this->assertNotNull($rotated);
		$this->assertTrue(Color::checkHexColor($rotated));

		$originalHsl = Color::rgbToHsl(...Color::hexToRgb($red));
		$rotatedHsl = Color::rgbToHsl(...Color::hexToRgb($rotated));

		// Hue should be different
		$this->assertNotEquals($originalHsl[0], $rotatedHsl[0]);
	}

	public function testAdjustHueWithNegativeDegrees(): void
	{
		$red = '#FF0000';
		$rotated = Color::adjustHue($red, -60);

		$this->assertNotNull($rotated);
		$this->assertTrue(Color::checkHexColor($rotated));
	}

	public function testAdjustHueWithInvalidColor(): void
	{
		$this->assertNull(Color::adjustHue('invalid', 120));
	}

	public function testMix(): void
	{
		$red = '#FF0000';
		$blue = '#0000FF';

		// Equal mix should give purple
		$mixed = Color::mix($red, $blue, 0.5);
		$this->assertNotNull($mixed);
		$this->assertTrue(Color::checkHexColor($mixed));

		// More red
		$mixedRed = Color::mix($red, $blue, 0.75);
		$this->assertNotNull($mixedRed);

		// More blue
		$mixedBlue = Color::mix($red, $blue, 0.25);
		$this->assertNotNull($mixedBlue);
	}

	public function testMixWithInvalidColor(): void
	{
		$this->assertNull(Color::mix('invalid', '#FF0000', 0.5));
		$this->assertNull(Color::mix('#FF0000', 'invalid', 0.5));
	}

	/* ===================== CSS Named Colors ===================== */

	public function testGetColorByName(): void
	{
		$this->assertSame('#FF0000', Color::getColorByName('red'));
		$this->assertSame('#0000FF', Color::getColorByName('blue'));
		$this->assertSame('#008000', Color::getColorByName('green'));
		$this->assertSame('#FFFFFF', Color::getColorByName('white'));
		$this->assertSame('#000000', Color::getColorByName('black'));
	}

	public function testGetColorByNameCaseInsensitive(): void
	{
		$this->assertSame('#FF0000', Color::getColorByName('RED'));
		$this->assertSame('#FF0000', Color::getColorByName('Red'));
		$this->assertSame('#FF0000', Color::getColorByName('rEd'));
	}

	public function testGetColorByNameWithInvalidName(): void
	{
		$this->assertNull(Color::getColorByName('notacolor'));
		$this->assertNull(Color::getColorByName(''));
	}

	public function testIsValidColorName(): void
	{
		$this->assertTrue(Color::isValidColorName('red'));
		$this->assertTrue(Color::isValidColorName('blue'));
		$this->assertTrue(Color::isValidColorName('aliceblue'));
		$this->assertTrue(Color::isValidColorName('rebeccapurple'));
	}

	public function testIsValidColorNameCaseInsensitive(): void
	{
		$this->assertTrue(Color::isValidColorName('RED'));
		$this->assertTrue(Color::isValidColorName('Red'));
		$this->assertTrue(Color::isValidColorName('AliceBlue'));
	}

	public function testIsValidColorNameWithInvalidName(): void
	{
		$this->assertFalse(Color::isValidColorName('notacolor'));
		$this->assertFalse(Color::isValidColorName(''));
	}

	/* ===================== WCAG Contrast ===================== */

	public function testGetContrast(): void
	{
		// Black and white should have maximum contrast (21:1)
		$contrast = Color::getContrast('#000000', '#FFFFFF');
		$this->assertSame(21.0, $contrast);

		// Same colors should have minimum contrast (1:1)
		$contrast = Color::getContrast('#FF0000', '#FF0000');
		$this->assertSame(1.0, $contrast);
	}

	public function testGetContrastSymmetric(): void
	{
		// Contrast should be the same regardless of order
		$contrast1 = Color::getContrast('#FF0000', '#0000FF');
		$contrast2 = Color::getContrast('#0000FF', '#FF0000');
		$this->assertSame($contrast1, $contrast2);
	}

	public function testGetContrastWithInvalidColor(): void
	{
		$this->assertNull(Color::getContrast('invalid', '#FF0000'));
		$this->assertNull(Color::getContrast('#FF0000', 'invalid'));
	}

	/* ===================== Color Schemes ===================== */

	public function testGetComplementaryColor(): void
	{
		$red = '#FF0000';
		$complementary = Color::getComplementaryColor($red);

		$this->assertNotNull($complementary);
		$this->assertTrue(Color::checkHexColor($complementary));

		// Complementary of red should be cyan-ish
		$rgb = Color::hexToRgb($complementary);
		$this->assertNotNull($rgb);
	}

	public function testGetComplementaryColorWithInvalidColor(): void
	{
		$this->assertNull(Color::getComplementaryColor('invalid'));
	}

	public function testGetTriadicColors(): void
	{
		$red = '#FF0000';
		$triad = Color::getTriadicColors($red);

		$this->assertNotNull($triad);
		$this->assertCount(3, $triad);
		$this->assertSame($red, $triad[0]);
		$this->assertTrue(Color::checkHexColor($triad[1]));
		$this->assertTrue(Color::checkHexColor($triad[2]));
	}

	public function testGetTriadicColorsWithInvalidColor(): void
	{
		$this->assertNull(Color::getTriadicColors('invalid'));
	}

	public function testGetAnalogousColors(): void
	{
		$red = '#FF0000';
		$analogous = Color::getAnalogousColors($red);

		$this->assertNotNull($analogous);
		$this->assertCount(3, $analogous);
		$this->assertSame($red, $analogous[1]); // Middle color should be the original
		$this->assertTrue(Color::checkHexColor($analogous[0]));
		$this->assertTrue(Color::checkHexColor($analogous[2]));
	}

	public function testGetAnalogousColorsWithCustomAngle(): void
	{
		$red = '#FF0000';
		$analogous = Color::getAnalogousColors($red, 45);

		$this->assertNotNull($analogous);
		$this->assertCount(3, $analogous);
	}

	public function testGetAnalogousColorsWithInvalidColor(): void
	{
		$this->assertNull(Color::getAnalogousColors('invalid'));
	}

	public function testGetSplitComplementaryColors(): void
	{
		$red = '#FF0000';
		$splitComp = Color::getSplitComplementaryColors($red);

		$this->assertNotNull($splitComp);
		$this->assertCount(3, $splitComp);
		$this->assertSame($red, $splitComp[0]);
		$this->assertTrue(Color::checkHexColor($splitComp[1]));
		$this->assertTrue(Color::checkHexColor($splitComp[2]));
	}

	public function testGetSplitComplementaryColorsWithInvalidColor(): void
	{
		$this->assertNull(Color::getSplitComplementaryColors('invalid'));
	}

	public function testGetTetradicColors(): void
	{
		$red = '#FF0000';
		$tetradic = Color::getTetradicColors($red);

		$this->assertNotNull($tetradic);
		$this->assertCount(4, $tetradic);
		$this->assertSame($red, $tetradic[0]);
		$this->assertTrue(Color::checkHexColor($tetradic[1]));
		$this->assertTrue(Color::checkHexColor($tetradic[2]));
		$this->assertTrue(Color::checkHexColor($tetradic[3]));
	}

	public function testGetTetradicColorsWithInvalidColor(): void
	{
		$this->assertNull(Color::getTetradicColors('invalid'));
	}
}