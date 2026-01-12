<?php

namespace Osimatic\Draw;

class Color
{
	// ========== Méthodes de vérification ==========

	/**
	 * @param string $hexaColor
	 * @return bool
	 */
	public static function checkHexaColor(string $hexaColor): bool
	{
		return preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $hexaColor) === 1;
	}

	// ========== Méthodes de conversion ==========

	/*
	public static function rgbToHexa($value) {
		return str_pad(dechex($value), 2, '0', STR_PAD_LEFT);
	}
	*/

	/**
	 * Convertie une couleur au format RGB en une couleur au format hexadécimale
	 * @param int $red le rouge (0 à 255) de la couleur au format RGB
	 * @param int $green le vert (0 à 255) de la couleur au format RGB
	 * @param int $blue le bleu (0 à 255) de la couleur au format RGB
	 * @param bool $withDiese true pour ajouter un "#" au début de la couleur au format HTML retournée, false sinon (true par défaut)
	 * @return string
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
	 * Convertie une couleur au format hexadécimale en une couleur au format RGB, sous forme de tableau contenant 3 entrées : le rouge (0 à 255), le vert (0 à 255) et le bleu (0 à 255)
	 * Si la couleur au format HTML contient un "#", il sera automatiquement ignoré. Les couleurs au format HTML à 3 caractères (#000 par exemple) sont acceptées.
	 * @param string $hex
	 * @return int[]|null
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
	 * Retourne le rouge (du format RGB) à partir d'une couleur au format hexadécimale
	 * Si la couleur au format HTML contient un "#", il sera automatiquement ignoré. Les couleurs au format HTML à 3 caractères (#000 par exemple) sont acceptées.
	 * @param string $hex
	 * @return int|null
	 */
	public static function getRedFromHex(string $hex): ?int
	{
		if (null === ($rgb = self::hexColorToRgb($hex))) {
			return null;
		}

		return $rgb[0];
	}

	/**
	 * Retourne le vert (du format RGB) à partir d'une couleur au format HTML
	 * Si la couleur au format HTML contient un "#", il sera automatiquement ignoré. Les couleurs au format HTML à 3 caractères (#000 par exemple) sont acceptées.
	 * @param string $hex
	 * @return int|null
	 */
	public static function getGreenFromHexa(string $hex): ?int
	{
		if (null === ($rgb = self::hexColorToRgb($hex))) {
			return null;
		}

		return $rgb[1];
	}

	/**
	 * Retourne le bleu (du format RGB) à partir d'une couleur au format HTML
	 * Si la couleur au format HTML contient un "#", il sera automatiquement ignoré. Les couleurs au format HTML à 3 caractères (#000 par exemple) sont acceptées.
	 * @param string $hex
	 * @return int|null
	 */
	public static function getBlueFromHexa(string $hex): ?int
	{
		if (null === ($rgb = self::hexColorToRgb($hex))) {
			return null;
		}

		return $rgb[2];
	}

	// ========== Méthodes sur les teintes ==========

	/**
	 * @param string $hex
	 * @return bool
	 */
	public static function isLightHexColor(string $hex): bool
	{
		if (null === ($rgb = self::hexColorToRgb($hex))) {
			return false;
		}
		return self::isLightColor(...$rgb);
	}

	/**
	 * @param int $red
	 * @param int $green
	 * @param int $blue
	 * @return bool
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


	// ========== Méthodes de génération ==========

	/**
	 * @return string
	 */
	public static function getRandomHexColor(): string
	{
		return self::rgbToHex(...self::getRandomColor());
	}

	/**
	 * @return string
	 */
	public static function getRandomBlackAndWhiteHexColor(): string
	{
		return self::rgbToHex(...self::getRandomBlackAndWhiteColor());
	}

	/**
	 * @return array
	 */
	public static function getRandomColor(): array
	{
		$red = random_int(0, 255);
		$green = random_int(0, 255);
		$blue = random_int(0, 255);
		return [$red, $green, $blue];
	}

	/**
	 * @return array
	 */
	public static function getRandomBlackAndWhiteColor(): array
	{
		$red = random_int(0, 255);
		$green = $red;
		$blue = $red;
		return [$red, $green, $blue];
	}

}