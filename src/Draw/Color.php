<?php

namespace Osimatic\Draw;

/**
 * Utility class for color manipulation and conversion.
 * Provides comprehensive methods for:
 * - Color validation (hexadecimal and CSS named colors)
 * - Color conversion between RGB, RGBA, hexadecimal, HSL, and HSV formats
 * - Color brightness and contrast analysis (WCAG 2.0 compliant)
 * - Color manipulation (lighten, darken, saturation, hue adjustment, mixing)
 * - Color scheme generation (complementary, triadic, analogous, split-complementary, tetradic)
 * - Random color generation (full color and grayscale)
 * - CSS named colors support (147 standard colors)
 */
class Color
{
	// ========== Constants ==========

	/** ITU-R BT.709 luminance weight for red channel */
	private const float LUMINANCE_RED = 0.2125;

	/** ITU-R BT.709 luminance weight for green channel */
	private const float LUMINANCE_GREEN = 0.7154;

	/** ITU-R BT.709 luminance weight for blue channel */
	private const float LUMINANCE_BLUE = 0.0721;

	/** Brightness threshold for determining if a color is light or dark */
	private const int BRIGHTNESS_THRESHOLD = 128;

	/** Minimum RGB component value */
	private const int RGB_MIN = 0;

	/** Maximum RGB component value */
	private const int RGB_MAX = 255;

	// ========== Validation Methods ==========

	/**
	 * Validates if a string is a valid hexadecimal color code.
	 * Accepts both 3-digit (#RGB) and 6-digit (#RRGGBB) formats with leading hash.
	 * @param string $hexColor The hexadecimal color string to validate (e.g., '#FF0000' or '#F00')
	 * @return bool True if valid hexadecimal color, false otherwise
	 */
	public static function checkHexColor(string $hexColor): bool
	{
		return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $hexColor) === 1;
	}

	// ========== Conversion Methods ==========

	/**
	 * Converts an RGB or RGBA color to hexadecimal format.
	 * Each RGB component must be in the range 0-255.
	 * Alpha channel is optional and ranges from 0.0 (transparent) to 1.0 (opaque).
	 * @param int $red The red component (0-255)
	 * @param int $green The green component (0-255)
	 * @param int $blue The blue component (0-255)
	 * @param float|null $alpha Optional alpha channel (0.0-1.0). If provided, returns 8-character hex
	 * @param bool $withHash Whether to include the "#" prefix (default: true)
	 * @return string The hexadecimal color string (e.g., '#FF0000' or '#FF0000FF' with alpha)
	 * @throws \InvalidArgumentException If any RGB value is outside the 0-255 range or alpha is outside 0.0-1.0
	 */
	public static function rgbToHex(int $red, int $green, int $blue, ?float $alpha=null, bool $withHash=true): string
	{
		// Validate RGB values
		if ($red < self::RGB_MIN || $red > self::RGB_MAX || $green < self::RGB_MIN || $green > self::RGB_MAX || $blue < self::RGB_MIN || $blue > self::RGB_MAX) {
			throw new \InvalidArgumentException("RGB values must be between " . self::RGB_MIN . " and " . self::RGB_MAX . ". Got: R=$red, G=$green, B=$blue");
		}

		// Validate alpha if provided
		if ($alpha !== null && ($alpha < 0.0 || $alpha > 1.0)) {
			throw new \InvalidArgumentException("Alpha value must be between 0.0 and 1.0. Got: $alpha");
		}

		// Convert each component to hex with padding
		$hexRgb = ''
			. str_pad(dechex($red), 2, '0', STR_PAD_LEFT)
			. str_pad(dechex($green), 2, '0', STR_PAD_LEFT)
			. str_pad(dechex($blue), 2, '0', STR_PAD_LEFT);

		// Add alpha channel if provided
		if ($alpha !== null) {
			$alphaInt = (int) round($alpha * 255);
			$hexRgb .= str_pad(dechex($alphaInt), 2, '0', STR_PAD_LEFT);
		}

		return $withHash ? '#' . $hexRgb : $hexRgb;
	}

	/**
	 * Converts a hexadecimal color to RGB or RGBA format.
	 * Automatically detects the presence of alpha channel based on string length.
	 * Returns an array containing three elements for RGB or four elements for RGBA.
	 * The "#" prefix is optional and will be automatically ignored.
	 * Supports 3-character (#RGB), 6-character (#RRGGBB), and 8-character (#RRGGBBAA) formats.
	 * @param string $hex The hexadecimal color string (e.g., '#FF0000', 'FF0000', '#F00', '#FF0000FF')
	 * @return array{0: int, 1: int, 2: int, 3?: float}|null Array of [red, green, blue] or [red, green, blue, alpha] values, or null if invalid format
	 */
	public static function hexToRgb(string $hex): ?array
	{
		if (str_starts_with($hex, '#')) {
			$hex = substr($hex, 1);
		}

		$length = strlen($hex);

		// Handle 3-character shorthand (#RGB -> #RRGGBB)
		if ($length === 3) {
			$hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
			$length = 6;
		}

		// Validate format
		if ($length === 6) {
			// RGB format
			if (!preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
				return null;
			}

			$hexRed = substr($hex, 0, 2);
			$hexGreen = substr($hex, 2, 2);
			$hexBlue = substr($hex, 4, 2);

			return [hexdec($hexRed), hexdec($hexGreen), hexdec($hexBlue)];
		}
		
		if ($length === 8) {
			// RGBA format
			if (!preg_match('/^[0-9a-fA-F]{8}$/', $hex)) {
				return null;
			}

			$hexRed = substr($hex, 0, 2);
			$hexGreen = substr($hex, 2, 2);
			$hexBlue = substr($hex, 4, 2);
			$hexAlpha = substr($hex, 6, 2);

			// Convert alpha from 0-255 to 0.0-1.0
			$alpha = round(hexdec($hexAlpha) / 255, 2);

			return [hexdec($hexRed), hexdec($hexGreen), hexdec($hexBlue), $alpha];
		}

		return null;
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
	public static function getGreenFromHex(string $hex): ?int
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
	public static function getBlueFromHex(string $hex): ?int
	{
		if (null === ($rgb = self::hexColorToRgb($hex))) {
			return null;
		}

		return $rgb[2];
	}

	/**
	 * Extracts the alpha component from a hexadecimal color with alpha channel.
	 * The "#" prefix is optional and will be automatically ignored.
	 * @param string $hex The hexadecimal color string with alpha (e.g., '#FF0000FF')
	 * @return float|null The alpha value (0.0-1.0), or null if invalid format
	 */
	public static function getAlphaFromHex(string $hex): ?float
	{
		$rgba = self::hexToRgb($hex);

		// Return alpha if present (4th element), otherwise null
		return isset($rgba[3]) ? $rgba[3] : null;
	}

	/**
	 * Converts RGB to HSL (Hue, Saturation, Lightness) color space.
	 * @param int $red The red component (0-255)
	 * @param int $green The green component (0-255)
	 * @param int $blue The blue component (0-255)
	 * @return array{0: float, 1: float, 2: float} Array of [hue (0-360), saturation (0-100), lightness (0-100)]
	 */
	public static function rgbToHsl(int $red, int $green, int $blue): array
	{
		$r = $red / 255;
		$g = $green / 255;
		$b = $blue / 255;

		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		$delta = $max - $min;

		$l = ($max + $min) / 2;

		if ($delta == 0) {
			$h = 0;
			$s = 0;
		} else {
			$s = $l > 0.5 ? $delta / (2 - $max - $min) : $delta / ($max + $min);

			if ($max == $r) {
				$h = (($g - $b) / $delta) + ($g < $b ? 6 : 0);
			} elseif ($max == $g) {
				$h = (($b - $r) / $delta) + 2;
			} else {
				$h = (($r - $g) / $delta) + 4;
			}

			$h /= 6;
		}

		return [round($h * 360, 2), round($s * 100, 2), round($l * 100, 2)];
	}

	/**
	 * Converts HSL (Hue, Saturation, Lightness) to RGB color space.
	 * @param float $hue The hue (0-360 degrees)
	 * @param float $saturation The saturation (0-100%)
	 * @param float $lightness The lightness (0-100%)
	 * @return array{0: int, 1: int, 2: int} Array of [red (0-255), green (0-255), blue (0-255)]
	 */
	public static function hslToRgb(float $hue, float $saturation, float $lightness): array
	{
		$h = $hue / 360;
		$s = $saturation / 100;
		$l = $lightness / 100;

		if ($s == 0) {
			$r = $g = $b = $l;
		} else {
			$hue2rgb = function($p, $q, $t) {
				if ($t < 0) $t += 1;
				if ($t > 1) $t -= 1;
				if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
				if ($t < 1/2) return $q;
				if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
				return $p;
			};

			$q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
			$p = 2 * $l - $q;

			$r = $hue2rgb($p, $q, $h + 1/3);
			$g = $hue2rgb($p, $q, $h);
			$b = $hue2rgb($p, $q, $h - 1/3);
		}

		return [(int) round($r * 255), (int) round($g * 255), (int) round($b * 255)];
	}

	/**
	 * Converts RGB to HSV (Hue, Saturation, Value) color space.
	 * @param int $red The red component (0-255)
	 * @param int $green The green component (0-255)
	 * @param int $blue The blue component (0-255)
	 * @return array{0: float, 1: float, 2: float} Array of [hue (0-360), saturation (0-100), value (0-100)]
	 */
	public static function rgbToHsv(int $red, int $green, int $blue): array
	{
		$r = $red / 255;
		$g = $green / 255;
		$b = $blue / 255;

		$max = max($r, $g, $b);
		$min = min($r, $g, $b);
		$delta = $max - $min;

		$v = $max;
		$s = $max == 0 ? 0 : $delta / $max;

		if ($delta == 0) {
			$h = 0;
		} else {
			if ($max == $r) {
				$h = (($g - $b) / $delta) + ($g < $b ? 6 : 0);
			} elseif ($max == $g) {
				$h = (($b - $r) / $delta) + 2;
			} else {
				$h = (($r - $g) / $delta) + 4;
			}
			$h /= 6;
		}

		return [round($h * 360, 2), round($s * 100, 2), round($v * 100, 2)];
	}

	/**
	 * Converts HSV (Hue, Saturation, Value) to RGB color space.
	 * @param float $hue The hue (0-360 degrees)
	 * @param float $saturation The saturation (0-100%)
	 * @param float $value The value/brightness (0-100%)
	 * @return array{0: int, 1: int, 2: int} Array of [red (0-255), green (0-255), blue (0-255)]
	 */
	public static function hsvToRgb(float $hue, float $saturation, float $value): array
	{
		$h = $hue / 60;
		$s = $saturation / 100;
		$v = $value / 100;

		$i = floor($h);
		$f = $h - $i;
		$p = $v * (1 - $s);
		$q = $v * (1 - $f * $s);
		$t = $v * (1 - (1 - $f) * $s);

		$i = $i % 6;

		switch ($i) {
			case 0: $r = $v; $g = $t; $b = $p; break;
			case 1: $r = $q; $g = $v; $b = $p; break;
			case 2: $r = $p; $g = $v; $b = $t; break;
			case 3: $r = $p; $g = $q; $b = $v; break;
			case 4: $r = $t; $g = $p; $b = $v; break;
			default: $r = $v; $g = $p; $b = $q; break;
		}

		return [(int) round($r * 255), (int) round($g * 255), (int) round($b * 255)];
	}

	// ========== Color Manipulation Methods ==========

	/**
	 * Lightens a color by a percentage.
	 * @param string $hex The hexadecimal color string
	 * @param float $percentage The percentage to lighten (0-100)
	 * @return string|null The lightened color in hex format, or null if invalid input
	 */
	public static function lighten(string $hex, float $percentage): ?string
	{
		if (null === ($rgb = self::hexColorToRgb($hex))) {
			return null;
		}

		[$h, $s, $l] = self::rgbToHsl(...$rgb);
		$l = min(100, $l + $percentage);

		return self::rgbToHex(...self::hslToRgb($h, $s, $l));
	}

	/**
	 * Darkens a color by a percentage.
	 * @param string $hex The hexadecimal color string
	 * @param float $percentage The percentage to darken (0-100)
	 * @return string|null The darkened color in hex format, or null if invalid input
	 */
	public static function darken(string $hex, float $percentage): ?string
	{
		if (null === ($rgb = self::hexColorToRgb($hex))) {
			return null;
		}

		[$h, $s, $l] = self::rgbToHsl(...$rgb);
		$l = max(0, $l - $percentage);

		return self::rgbToHex(...self::hslToRgb($h, $s, $l));
	}

	/**
	 * Adjusts the saturation of a color.
	 * @param string $hex The hexadecimal color string
	 * @param float $amount The amount to adjust saturation (-100 to 100)
	 * @return string|null The adjusted color in hex format, or null if invalid input
	 */
	public static function adjustSaturation(string $hex, float $amount): ?string
	{
		if (null === ($rgb = self::hexColorToRgb($hex))) {
			return null;
		}

		[$h, $s, $l] = self::rgbToHsl(...$rgb);
		$s = max(0, min(100, $s + $amount));

		return self::rgbToHex(...self::hslToRgb($h, $s, $l));
	}

	/**
	 * Adjusts the hue of a color by rotating it around the color wheel.
	 * @param string $hex The hexadecimal color string
	 * @param int $degrees The degrees to rotate (can be negative, wraps around 360)
	 * @return string|null The adjusted color in hex format, or null if invalid input
	 */
	public static function adjustHue(string $hex, int $degrees): ?string
	{
		if (null === ($rgb = self::hexColorToRgb($hex))) {
			return null;
		}

		[$h, $s, $l] = self::rgbToHsl(...$rgb);
		$h = fmod($h + $degrees + 360, 360);

		return self::rgbToHex(...self::hslToRgb($h, $s, $l));
	}

	/**
	 * Mixes two colors together.
	 * @param string $hex1 The first hexadecimal color
	 * @param string $hex2 The second hexadecimal color
	 * @param float $weight The weight of the first color (0.0-1.0, default 0.5 for equal mix)
	 * @return string|null The mixed color in hex format, or null if invalid input
	 */
	public static function mix(string $hex1, string $hex2, float $weight = 0.5): ?string
	{
		if (null === ($rgb1 = self::hexColorToRgb($hex1)) || null === ($rgb2 = self::hexColorToRgb($hex2))) {
			return null;
		}

		$weight = max(0, min(1, $weight));
		$w = $weight * 2 - 1;
		$w1 = ($w + 1) / 2;
		$w2 = 1 - $w1;

		$r = (int) round($rgb1[0] * $w1 + $rgb2[0] * $w2);
		$g = (int) round($rgb1[1] * $w1 + $rgb2[1] * $w2);
		$b = (int) round($rgb1[2] * $w1 + $rgb2[2] * $w2);

		return self::rgbToHex($r, $g, $b);
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
		$coeff = (self::LUMINANCE_RED * $red) + (self::LUMINANCE_GREEN * $green) + (self::LUMINANCE_BLUE * $blue);
		return $coeff > self::BRIGHTNESS_THRESHOLD;
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
		$red = random_int(self::RGB_MIN, self::RGB_MAX);
		$green = random_int(self::RGB_MIN, self::RGB_MAX);
		$blue = random_int(self::RGB_MIN, self::RGB_MAX);
		return [$red, $green, $blue];
	}

	/**
	 * Generates a random grayscale RGB color.
	 * All components have the same value, creating a shade of gray from black to white.
	 * @return int[] Array of [red, green, blue] values (all identical, 0-255)
	 */
	public static function getRandomBlackAndWhiteColor(): array
	{
		$red = random_int(self::RGB_MIN, self::RGB_MAX);
		$green = $red;
		$blue = $red;
		return [$red, $green, $blue];
	}

	// ========== Named CSS Colors ==========

	/** CSS named colors (147 standard colors) */
	private const array CSS_COLORS = [
		'aliceblue' => '#F0F8FF',
		'antiquewhite' => '#FAEBD7',
		'aqua' => '#00FFFF',
		'aquamarine' => '#7FFFD4',
		'azure' => '#F0FFFF',
		'beige' => '#F5F5DC',
		'bisque' => '#FFE4C4',
		'black' => '#000000',
		'blanchedalmond' => '#FFEBCD',
		'blue' => '#0000FF',
		'blueviolet' => '#8A2BE2',
		'brown' => '#A52A2A',
		'burlywood' => '#DEB887',
		'cadetblue' => '#5F9EA0',
		'chartreuse' => '#7FFF00',
		'chocolate' => '#D2691E',
		'coral' => '#FF7F50',
		'cornflowerblue' => '#6495ED',
		'cornsilk' => '#FFF8DC',
		'crimson' => '#DC143C',
		'cyan' => '#00FFFF',
		'darkblue' => '#00008B',
		'darkcyan' => '#008B8B',
		'darkgoldenrod' => '#B8860B',
		'darkgray' => '#A9A9A9',
		'darkgrey' => '#A9A9A9',
		'darkgreen' => '#006400',
		'darkkhaki' => '#BDB76B',
		'darkmagenta' => '#8B008B',
		'darkolivegreen' => '#556B2F',
		'darkorange' => '#FF8C00',
		'darkorchid' => '#9932CC',
		'darkred' => '#8B0000',
		'darksalmon' => '#E9967A',
		'darkseagreen' => '#8FBC8F',
		'darkslateblue' => '#483D8B',
		'darkslategray' => '#2F4F4F',
		'darkslategrey' => '#2F4F4F',
		'darkturquoise' => '#00CED1',
		'darkviolet' => '#9400D3',
		'deeppink' => '#FF1493',
		'deepskyblue' => '#00BFFF',
		'dimgray' => '#696969',
		'dimgrey' => '#696969',
		'dodgerblue' => '#1E90FF',
		'firebrick' => '#B22222',
		'floralwhite' => '#FFFAF0',
		'forestgreen' => '#228B22',
		'fuchsia' => '#FF00FF',
		'gainsboro' => '#DCDCDC',
		'ghostwhite' => '#F8F8FF',
		'gold' => '#FFD700',
		'goldenrod' => '#DAA520',
		'gray' => '#808080',
		'grey' => '#808080',
		'green' => '#008000',
		'greenyellow' => '#ADFF2F',
		'honeydew' => '#F0FFF0',
		'hotpink' => '#FF69B4',
		'indianred' => '#CD5C5C',
		'indigo' => '#4B0082',
		'ivory' => '#FFFFF0',
		'khaki' => '#F0E68C',
		'lavender' => '#E6E6FA',
		'lavenderblush' => '#FFF0F5',
		'lawngreen' => '#7CFC00',
		'lemonchiffon' => '#FFFACD',
		'lightblue' => '#ADD8E6',
		'lightcoral' => '#F08080',
		'lightcyan' => '#E0FFFF',
		'lightgoldenrodyellow' => '#FAFAD2',
		'lightgray' => '#D3D3D3',
		'lightgrey' => '#D3D3D3',
		'lightgreen' => '#90EE90',
		'lightpink' => '#FFB6C1',
		'lightsalmon' => '#FFA07A',
		'lightseagreen' => '#20B2AA',
		'lightskyblue' => '#87CEFA',
		'lightslategray' => '#778899',
		'lightslategrey' => '#778899',
		'lightsteelblue' => '#B0C4DE',
		'lightyellow' => '#FFFFE0',
		'lime' => '#00FF00',
		'limegreen' => '#32CD32',
		'linen' => '#FAF0E6',
		'magenta' => '#FF00FF',
		'maroon' => '#800000',
		'mediumaquamarine' => '#66CDAA',
		'mediumblue' => '#0000CD',
		'mediumorchid' => '#BA55D3',
		'mediumpurple' => '#9370DB',
		'mediumseagreen' => '#3CB371',
		'mediumslateblue' => '#7B68EE',
		'mediumspringgreen' => '#00FA9A',
		'mediumturquoise' => '#48D1CC',
		'mediumvioletred' => '#C71585',
		'midnightblue' => '#191970',
		'mintcream' => '#F5FFFA',
		'mistyrose' => '#FFE4E1',
		'moccasin' => '#FFE4B5',
		'navajowhite' => '#FFDEAD',
		'navy' => '#000080',
		'oldlace' => '#FDF5E6',
		'olive' => '#808000',
		'olivedrab' => '#6B8E23',
		'orange' => '#FFA500',
		'orangered' => '#FF4500',
		'orchid' => '#DA70D6',
		'palegoldenrod' => '#EEE8AA',
		'palegreen' => '#98FB98',
		'paleturquoise' => '#AFEEEE',
		'palevioletred' => '#DB7093',
		'papayawhip' => '#FFEFD5',
		'peachpuff' => '#FFDAB9',
		'peru' => '#CD853F',
		'pink' => '#FFC0CB',
		'plum' => '#DDA0DD',
		'powderblue' => '#B0E0E6',
		'purple' => '#800080',
		'rebeccapurple' => '#663399',
		'red' => '#FF0000',
		'rosybrown' => '#BC8F8F',
		'royalblue' => '#4169E1',
		'saddlebrown' => '#8B4513',
		'salmon' => '#FA8072',
		'sandybrown' => '#F4A460',
		'seagreen' => '#2E8B57',
		'seashell' => '#FFF5EE',
		'sienna' => '#A0522D',
		'silver' => '#C0C0C0',
		'skyblue' => '#87CEEB',
		'slateblue' => '#6A5ACD',
		'slategray' => '#708090',
		'slategrey' => '#708090',
		'snow' => '#FFFAFA',
		'springgreen' => '#00FF7F',
		'steelblue' => '#4682B4',
		'tan' => '#D2B48C',
		'teal' => '#008080',
		'thistle' => '#D8BFD8',
		'tomato' => '#FF6347',
		'turquoise' => '#40E0D0',
		'violet' => '#EE82EE',
		'wheat' => '#F5DEB3',
		'white' => '#FFFFFF',
		'whitesmoke' => '#F5F5F5',
		'yellow' => '#FFFF00',
		'yellowgreen' => '#9ACD32',
	];

	/**
	 * Gets the hexadecimal color code for a CSS named color.
	 * @param string $name The CSS color name (case-insensitive)
	 * @return string|null The hexadecimal color string, or null if name not found
	 */
	public static function getColorByName(string $name): ?string
	{
		$name = strtolower($name);
		return self::CSS_COLORS[$name] ?? null;
	}

	/**
	 * Validates if a string is a valid CSS color name.
	 * @param string $name The color name to validate (case-insensitive)
	 * @return bool True if valid CSS color name, false otherwise
	 */
	public static function isValidColorName(string $name): bool
	{
		return isset(self::CSS_COLORS[strtolower($name)]);
	}

	// ========== Advanced Color Analysis Methods ==========

	/**
	 * Calculates the contrast ratio between two colors according to WCAG 2.0.
	 * @param string $hex1 The first hexadecimal color
	 * @param string $hex2 The second hexadecimal color
	 * @return float|null The contrast ratio (1-21), or null if invalid input
	 */
	public static function getContrast(string $hex1, string $hex2): ?float
	{
		if (null === ($rgb1 = self::hexColorToRgb($hex1)) || null === ($rgb2 = self::hexColorToRgb($hex2))) {
			return null;
		}

		$l1 = self::getRelativeLuminance(...$rgb1);
		$l2 = self::getRelativeLuminance(...$rgb2);

		$lighter = max($l1, $l2);
		$darker = min($l1, $l2);

		return round(($lighter + 0.05) / ($darker + 0.05), 2);
	}

	/**
	 * Calculates relative luminance for WCAG contrast calculations.
	 * @param int $red The red component (0-255)
	 * @param int $green The green component (0-255)
	 * @param int $blue The blue component (0-255)
	 * @return float The relative luminance (0-1)
	 */
	private static function getRelativeLuminance(int $red, int $green, int $blue): float
	{
		$convert = function($val) {
			$val = $val / 255;
			return $val <= 0.03928 ? $val / 12.92 : pow(($val + 0.055) / 1.055, 2.4);
		};

		$r = $convert($red);
		$g = $convert($green);
		$b = $convert($blue);

		return 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
	}

	/**
	 * Gets the complementary color (opposite on the color wheel).
	 * @param string $hex The hexadecimal color string
	 * @return string|null The complementary color in hex format, or null if invalid input
	 */
	public static function getComplementaryColor(string $hex): ?string
	{
		return self::adjustHue($hex, 180);
	}

	/**
	 * Gets a triadic color scheme (colors evenly spaced around the color wheel).
	 * @param string $hex The base hexadecimal color string
	 * @return array{0: string, 1: string, 2: string}|null Array of three colors, or null if invalid input
	 */
	public static function getTriadicColors(string $hex): ?array
	{
		if (null === self::hexColorToRgb($hex)) {
			return null;
		}

		$color1 = $hex;
		$color2 = self::adjustHue($hex, 120);
		$color3 = self::adjustHue($hex, 240);

		if ($color2 === null || $color3 === null) {
			return null;
		}

		return [$color1, $color2, $color3];
	}

	/**
	 * Gets an analogous color scheme (adjacent colors on the color wheel).
	 * @param string $hex The base hexadecimal color string
	 * @param int $angle The angle between colors (default: 30 degrees)
	 * @return array{0: string, 1: string, 2: string}|null Array of three colors, or null if invalid input
	 */
	public static function getAnalogousColors(string $hex, int $angle = 30): ?array
	{
		if (null === self::hexColorToRgb($hex)) {
			return null;
		}

		$color1 = self::adjustHue($hex, -$angle);
		$color2 = $hex;
		$color3 = self::adjustHue($hex, $angle);

		if ($color1 === null || $color3 === null) {
			return null;
		}

		return [$color1, $color2, $color3];
	}

	/**
	 * Gets a split-complementary color scheme.
	 * @param string $hex The base hexadecimal color string
	 * @return array{0: string, 1: string, 2: string}|null Array of three colors, or null if invalid input
	 */
	public static function getSplitComplementaryColors(string $hex): ?array
	{
		if (null === self::hexColorToRgb($hex)) {
			return null;
		}

		$color1 = $hex;
		$color2 = self::adjustHue($hex, 150);
		$color3 = self::adjustHue($hex, 210);

		if ($color2 === null || $color3 === null) {
			return null;
		}

		return [$color1, $color2, $color3];
	}

	/**
	 * Gets a tetradic (rectangular) color scheme.
	 * @param string $hex The base hexadecimal color string
	 * @return array{0: string, 1: string, 2: string, 3: string}|null Array of four colors, or null if invalid input
	 */
	public static function getTetradicColors(string $hex): ?array
	{
		if (null === self::hexColorToRgb($hex)) {
			return null;
		}

		$color1 = $hex;
		$color2 = self::adjustHue($hex, 90);
		$color3 = self::adjustHue($hex, 180);
		$color4 = self::adjustHue($hex, 270);

		if ($color2 === null || $color3 === null || $color4 === null) {
			return null;
		}

		return [$color1, $color2, $color3, $color4];
	}




	/**
	 * @deprecated Use rgbToHex() with $alpha parameter instead
	 */
	public static function rgbaToHex(int $red, int $green, int $blue, float $alpha, bool $withHash=true): string
	{
		return self::rgbToHex($red, $green, $blue, $alpha, $withHash);
	}

	/**
	 * @deprecated Use hexToRgb() instead - it now automatically detects alpha channel
	 */
	public static function hexToRgba(string $hex): ?array
	{
		$result = self::hexToRgb($hex);

		// Only return if it has alpha channel, otherwise return null to maintain original behavior
		return (isset($result[3])) ? $result : null;
	}

	/**
	 * @deprecated Use hexToRgb() instead
	 */
	public static function hexColorToRgb(string $hex): ?array
	{
		return self::hexToRgb($hex);
	}

	/**
	 * @deprecated Use checkHexColor() instead
	 */
	public static function checkHexaColor(string $hexaColor): bool
	{
		return self::checkHexColor($hexaColor);
	}

}