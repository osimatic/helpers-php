<?php

namespace Osimatic\Draw;

/**
 * Utility class for color manipulation and conversion.
 * Provides methods for:
 * - Color validation (hexadecimal format)
 * - Color conversion between RGB and hexadecimal formats
 * - Color brightness analysis
 * - Random color generation
 */
class Color
{
	// ========== Validation Methods ==========

	/**
	 * Validates if a string is a valid hexadecimal color code.
	 * Accepts both 3-digit (#RGB) and 6-digit (#RRGGBB) formats with leading hash.
	 * @param string $hexaColor The hexadecimal color string to validate (e.g., '#FF0000' or '#F00')
	 * @return bool True if valid hexadecimal color, false otherwise
	 */
	public static function checkHexaColor(string $hexaColor): bool
	{
		return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $hexaColor) === 1;
	}

	// ========== Conversion Methods ==========

	/*
	public static function rgbToHexa($value) {
		return str_pad(dechex($value), 2, '0', STR_PAD_LEFT);
	}
	*/

	/**
	 * Converts an RGB color to hexadecimal format.
	 * Each RGB component must be in the range 0-255.
	 * @param int $red The red component (0-255)
	 * @param int $green The green component (0-255)
	 * @param int $blue The blue component (0-255)
	 * @param bool $withDiese Whether to include the "#" prefix (default: true)
	 * @return string The hexadecimal color string (e.g., '#FF0000' or 'FF0000')
	 */
	public static function rgbToHex(int $red, int $green, int $blue, bool $withDiese=true): string
	{
		$listeVal = array($red, $green, $blue);
		$hexRgb = '';
		foreach ($listeVal as $value) {
			$hexValue = dechex($value);
			if (strlen($hexValue) < 2) {
				$hexValue = '0'.$hexValue;
			}
			$hexRgb .= $hexValue;
		}

		if ($withDiese) {
			$hexRgb = '#' .$hexRgb;
		}

		return $hexRgb;
	}

	/**
	 * Converts a hexadecimal color to RGB format.
	 * Returns an array containing three elements: red (0-255), green (0-255), and blue (0-255).
	 * The "#" prefix is optional and will be automatically ignored.
	 * Both 3-character (#RGB) and 6-character (#RRGGBB) formats are supported.
	 * @param string $hex The hexadecimal color string (e.g., '#FF0000', 'FF0000', '#F00', or 'F00')
	 * @return int[]|null Array of [red, green, blue] values (0-255), or null if invalid format
	 */
	public static function hexColorToRgb(string $hex): ?array
	{
		if (str_starts_with($hex, '#')) {
			$hex = substr($hex, 1);
		}

		if (strlen($hex) === 3) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
		}

		if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
			return null;
		}

		$hexRed = substr($hex,0,2);
		$hexGreen = substr($hex,2,2);
		$hexBlue = substr($hex,4,2);

		return [hexdec($hexRed), hexdec($hexGreen), hexdec($hexBlue)];
	}

	/**
	 * Extracts the red component (RGB) from a hexadecimal color.
	 * The "#" prefix is optional and will be automatically ignored.
	 * Both 3-character (#RGB) and 6-character (#RRGGBB) formats are supported.
	 * @param string $hex The hexadecimal color string (e.g., '#FF0000' or 'F00')
	 * @return int|null The red component value (0-255), or null if invalid format
	 */
	public static function getRedFromHex(string $hex): ?int
	{
		if (null === ($rgb = self::hexColorToRgb($hex))) {
			return null;
		}

		return $rgb[0];
	}

	/**
	 * Extracts the green component (RGB) from a hexadecimal color.
	 * The "#" prefix is optional and will be automatically ignored.
	 * Both 3-character (#RGB) and 6-character (#RRGGBB) formats are supported.
	 * @param string $hex The hexadecimal color string (e.g., '#00FF00' or '0F0')
	 * @return int|null The green component value (0-255), or null if invalid format
	 */
	public static function getGreenFromHexa(string $hex): ?int
	{
		if (null === ($rgb = self::hexColorToRgb($hex))) {
			return null;
		}

		return $rgb[1];
	}

	/**
	 * Extracts the blue component (RGB) from a hexadecimal color.
	 * The "#" prefix is optional and will be automatically ignored.
	 * Both 3-character (#RGB) and 6-character (#RRGGBB) formats are supported.
	 * @param string $hex The hexadecimal color string (e.g., '#0000FF' or '00F')
	 * @return int|null The blue component value (0-255), or null if invalid format
	 */
	public static function getBlueFromHexa(string $hex): ?int
	{
		if (null === ($rgb = self::hexColorToRgb($hex))) {
			return null;
		}

		return $rgb[2];
	}

	// ========== Brightness Analysis Methods ==========

	/**
	 * Determines if a hexadecimal color is considered light or dark.
	 * Uses weighted luminance calculation based on human eye sensitivity.
	 * Returns false if the color format is invalid.
	 * @param string $hex The hexadecimal color string (e.g., '#FF0000' or 'F00')
	 * @return bool True if the color is light (bright), false if dark or invalid
	 */
	public static function isLightHexColor(string $hex): bool
	{
		if (null === ($rgb = self::hexColorToRgb($hex))) {
			return false;
		}
		return self::isLightColor(...$rgb);
	}

	/**
	 * Determines if an RGB color is considered light or dark.
	 * Uses the ITU-R BT.709 weighted luminance formula that accounts for human eye sensitivity.
	 * Green contributes most to perceived brightness (71.54%), red moderate (21.25%), and blue least (7.21%).
	 * @param int $red The red component (0-255)
	 * @param int $green The green component (0-255)
	 * @param int $blue The blue component (0-255)
	 * @return bool True if the color is light (brightness > 128), false if dark
	 */
	public static function isLightColor(int $red, int $green, int $blue): bool
	{
		$percentageRed 		= 0.2125;
		$percentageGreen 	= 0.7154;
		$percentageBlue 	= 0.0721;
		// $percentageRed 		= 0.3;
		// $percentageGreen 	= 0.59;
		// $percentageBlue 		= 0.11;

		$coeff = ($percentageRed*$red) + ($percentageGreen*$green) + ($percentageBlue*$blue);
		return $coeff > 128;
	}


	// ========== Color Generation Methods ==========

	/**
	 * Generates a random hexadecimal color.
	 * Each RGB component is randomly selected from 0-255.
	 * @return string A random hexadecimal color with "#" prefix (e.g., '#A3C4F2')
	 */
	public static function getRandomHexColor(): string
	{
		return self::rgbToHex(...self::getRandomColor());
	}

	/**
	 * Generates a random grayscale hexadecimal color.
	 * All RGB components have the same value, creating a shade of gray from black to white.
	 * @return string A random grayscale hexadecimal color with "#" prefix (e.g., '#7F7F7F')
	 */
	public static function getRandomBlackAndWhiteHexColor(): string
	{
		return self::rgbToHex(...self::getRandomBlackAndWhiteColor());
	}

	/**
	 * Generates a random RGB color.
	 * Each component (red, green, blue) is randomly selected from 0-255.
	 * @return int[] Array of [red, green, blue] values (0-255)
	 */
	public static function getRandomColor(): array
	{
		$red = random_int(0, 255);
		$green = random_int(0, 255);
		$blue = random_int(0, 255);
		return [$red, $green, $blue];
	}

	/**
	 * Generates a random grayscale RGB color.
	 * All components have the same value, creating a shade of gray from black to white.
	 * @return int[] Array of [red, green, blue] values (all identical, 0-255)
	 */
	public static function getRandomBlackAndWhiteColor(): array
	{
		$red = random_int(0, 255);
		$green = $red;
		$blue = $red;
		return [$red, $green, $blue];
	}

}