<?php

namespace Osimatic\Helpers\Number;

class Number
{
	/**
	 * @param mixed $number
	 * @param int $nbDigitMin
	 * @param string|null $thousandsSeparator
	 * @return string
	 */
	public static function addLeadingZero($number, int $nbDigitMin, ?string $thousandsSeparator=null): string
	{
		$tmp = str_replace((is_null($thousandsSeparator)?'.':$thousandsSeparator), '', $number);
		$nbLeadingZero = $nbDigitMin-(strlen((string) (int) $tmp));
		return str_pad($number, $nbLeadingZero, '0', STR_PAD_LEFT);
	}

	/**
	 * Format a string for display the binary string as hex bytes.
	 * @param $hex
	 * @return string
	 * @author paladin
	 */
	public static function formatHex($hex): string
	{
		$ar = unpack('C*', $hex);
		$str = "";
		foreach ($ar as $v) {
			$s = dechex($v);
			if (strlen($s)<2) {
				$s = "0$s";
			}
			$str .= $s.' ';
		}
		return $str;
	}

	// ========== Arrondissement d'un nombre ==========

	/**
	 * Arrondi un flottant au plus petit flottant supérieur
	 * @param float $nombre le flottant à arrondir
	 * @param int $precision le nombre de chiffre après la virgule à garder
	 * @return float le flottant arrondi au flottant supérieur
	 * @link http://fr2.php.net/manual/fr/function.pow.php
	 * @link http://fr2.php.net/manual/fr/function.ceil.php
	 */
	public static function floatRoundUp(float $nombre, int $precision=2): float
	{
		if (self::getNbDigitsOfInt(self::decimalPart($nombre)) === $precision) {
			return $nombre;
		}

		$e = pow(10, $precision);
		return ceil($e * $nombre)/$e;
	}

	/**
	 * Arrondi un flottant au plus grand flottant inférieur
	 * @param float $nombre le flottant à arrondir
	 * @param int $precision le nombre de chiffre après la virgule à garder
	 * @return float le flottant arrondi au flottant inférieur
	 * @link http://fr2.php.net/manual/fr/function.pow.php
	 * @link http://fr2.php.net/manual/fr/function.floor.php
	 */
	public static function floatRoundDown(float $nombre, int $precision=2): float
	{
		if (self::getNbDigitsOfInt(self::decimalPart($nombre)) === $precision) {
			return $nombre;
		}

		$e = pow(10, $precision);
		return floor($e * $nombre)/$e;
	}

	// ========== Type / Composition d'un nombre ==========

	/**
	 * @param $val
	 * @return bool
	 */
	public static function isInteger($val): bool
	{
		return ($val - round($val) === 0);
	}

	/**
	 * @param $val
	 * @return bool
	 */
	public static function isFloat($val): bool
	{
		return ($val - round($val) !== 0);
	}

	/**
	 * Calcule le nombre de chiffres d'un entier.
	 * @example cette fonction retourne l'entier 6 pour le nombre 112233
	 * @param int|float $int l'entier pour lequel son nombre de chiffres est calculé
	 * @return int le nombre de chiffres de l'entier
	 */
	public static function getNbDigitsOfInt($int) {
		return strlen((string) (int) $int);
	}

	// ========== Mathématiques ==========

	/**
	 * Récupère la valeur numérique décimale d'un nombre sous forme d'entier ou de flottant
	 * @example cette fonction retourne l'entier 3344 pour le nombre 1122.3344 (ou le flottant 0.3344 si le paramètre $asFloat vaut true
	 * @param int|float $float le nombre pour lequel la partie décimale est récupérée
	 * @param boolean $asFloat true pour retourner la valeur numérique décimale du nombre sous forme d'entier, false pour la retourner sous forme de flottant
	 * @return int|float la partie décimale du nombre, sous forme d'entier
	 */
	public static function decimalPart($float, bool $asFloat=false)
	{
		if (!self::isFloat($float)) {
			return 0;
		}
		$tabFloat = explode('.', $float);

		if ($asFloat) {
			return (float) ('0.' . $tabFloat[1]);
		}
		return (int) $tabFloat[1];
	}

	/**
	 * @param $number
	 * @return bool
	 */
	public static function checkLuhn($number): bool
	{
		$somme = 0;
		$strNumber = (string) $number;
		$nbDigits = strlen($strNumber);
		$parity = $nbDigits%2;

		for ($i=($nbDigits-1); $i>=0; $i--) {
			$digit = $strNumber[$i];

			if ($i%2 == $parity) {
				$digit = $digit * 2;
			}
			if ($digit > 9) {
				$digit -= 9;
			}
			$somme += $digit;
		}
		return ($somme % 10) === 0;
	}

	// ========== Random ==========

	/**
	 * Génère un entier
	 * @param int $min
	 * @param int $max
	 * @return int l'entier généré
	 */
	public static function getRandomInt(int $min, int $max): int
	{
		try {
			return random_int($min, $max);
		} catch (\Exception $e) {
		}
		return 0;
	}

	/**
	 * @param float $min
	 * @param float $max
	 * @param int $round
	 * @return float
	 */
	public static function getRandomFloat(float $min, float $max, int $round=0): float
	{
		if ($min > $max) {
			return false;
		}

		$randomFloat = $min + mt_rand() / mt_getrandmax() * ($max - $min);
		if ($round > 0) {
			$randomFloat = round($randomFloat, $round);
		}
		return $randomFloat;
	}

}