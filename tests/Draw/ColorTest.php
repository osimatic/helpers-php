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
		$this->assertSame('ff0000', Color::rgbToHex(255, 0, 0, false));
		$this->assertSame('00ff00', Color::rgbToHex(0, 255, 0, false));
		$this->assertSame('0000ff', Color::rgbToHex(0, 0, 255, false));
		$this->assertSame('ffffff', Color::rgbToHex(255, 255, 255, false));
		$this->assertSame('000000', Color::rgbToHex(0, 0, 0, false));
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
		$this->assertSame(0, Color::getGreenFromHexa('#FF0000'));
		$this->assertSame(255, Color::getGreenFromHexa('#00FF00'));
		$this->assertSame(205, Color::getGreenFromHexa('#ABCDEF'));
		$this->assertSame(255, Color::getGreenFromHexa('#FFF'));
		$this->assertSame(0, Color::getGreenFromHexa('#000'));
	}

	public function testGetGreenFromHexaWithInvalidColor(): void
	{
		$this->assertNull(Color::getGreenFromHexa('invalid'));
		$this->assertNull(Color::getGreenFromHexa('#GG0000'));
	}

	/* ===================== getBlueFromHexa() ===================== */

	public function testGetBlueFromHexa(): void
	{
		$this->assertSame(0, Color::getBlueFromHexa('#FF0000'));
		$this->assertSame(0, Color::getBlueFromHexa('#00FF00'));
		$this->assertSame(255, Color::getBlueFromHexa('#0000FF'));
		$this->assertSame(239, Color::getBlueFromHexa('#ABCDEF'));
		$this->assertSame(255, Color::getBlueFromHexa('#FFF'));
		$this->assertSame(0, Color::getBlueFromHexa('#000'));
	}

	public function testGetBlueFromHexaWithInvalidColor(): void
	{
		$this->assertNull(Color::getBlueFromHexa('invalid'));
		$this->assertNull(Color::getBlueFromHexa('#GG0000'));
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
}