<?php

namespace Osimatic\Number;

class Number
{
	// ========== Affichage ==========

	/**
	 * @param float|int $number
	 * @param int $nbDigitMin
	 * @param string|null $thousandsSeparator
	 * @return string
	 */
	public static function addLeadingZero(float|int $number, int $nbDigitMin, ?string $thousandsSeparator=null): string
	{
		$tmp = str_replace($thousandsSeparator ?? '.', '', $number);
		$nbLeadingZero = $nbDigitMin - strlen((string) (int) $tmp);
		return str_pad($number, $nbLeadingZero, '0', STR_PAD_LEFT);
	}

	/**
	 * @param float|int $number
	 * @param int $decimals
	 * @return string
	 */
	public static function format(float|int $number, int $decimals=2): string
	{
		$fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::DECIMAL);
		$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
		return \Osimatic\Text\Str::removeNonBreakingSpaces($fmt->format($number));
	}

	/**
	 * @param float|int $number
	 * @return string
	 */
	public static function formatInt(float|int $number): string
	{
		return self::format($number, 0);
	}

	/**
	 * @param float|int $number
	 * @return string
	 */
	public static function formatOrdinal(float|int $number): string
	{
		$fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::ORDINAL);
		return \Osimatic\Text\Str::removeNonBreakingSpaces($fmt->format($number));
	}

	/**
	 * @param float|int $number
	 * @param int $decimals
	 * @return string
	 */
	public static function formatScientific(float|int $number, int $decimals=2): string
	{
		$fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::SCIENTIFIC);
		$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
		return \Osimatic\Text\Str::removeNonBreakingSpaces($fmt->format($number));
	}

	/**
	 * @param float|int $number
	 * @param int $decimals
	 * @return string
	 */
	public static function formatSpellOut(float|int $number, int $decimals=2): string
	{
		$fmt = new \NumberFormatter(\Locale::getDefault(), \NumberFormatter::SPELLOUT);
		$fmt->setAttribute(\NumberFormatter::FRACTION_DIGITS, $decimals);
		return \Osimatic\Text\Str::removeNonBreakingSpaces($fmt->format($number));
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
		$str = '';
		foreach ($ar as $v) {
			$s = dechex($v);
			if (strlen($s)<2) {
				$s = "0$s";
			}
			$str .= $s.' ';
		}
		return $str;
	}

	// ========== Vérification ==========

	/**
	 * @param string|null $str
	 * @return float
	 */
	public static function parseFloat(?string $str): float
	{
		return (float) self::clean($str);
	}

	/**
	 * @param string|null $str
	 * @return int
	 */
	public static function parseInt(?string $str): int
	{
		return (int) self::clean($str);
	}

	/**
	 * @param float|null $str
	 * @return string
	 */
	public static function floatToString(?float $str): string
	{
		return self::clean($str);
	}

	/**
	 * @param mixed $str
	 * @param bool $negativeAllowed
	 * @param bool $positiveAllowed
	 * @return bool
	 */
	public static function checkFloat(mixed $str, bool $negativeAllowed=true, bool $positiveAllowed=true): bool
	{
		$str = self::clean((string) $str);

		if (false === self::check($str, $negativeAllowed, $positiveAllowed)) {
			return false;
		}

		// mise en commentaire pour autoriser aussi la saisie des entiers
		//if (strpos($str, '.') === false) {
		//	return false;
		//}

		return true;
	}

	/**
	 * @param mixed $str
	 * @param bool $negativeAllowed
	 * @param bool $positiveAllowed
	 * @return bool
	 */
	public static function checkInt(mixed $str, bool $negativeAllowed=true, bool $positiveAllowed=true): bool
	{
		$str = self::clean((string) $str, false);

		if (false === self::check($str, $negativeAllowed, $positiveAllowed)) {
			return false;
		}

		if (str_contains($str, '.')) {
			return false;
		}

		return true;
	}

	/**
	 * @param mixed $str
	 * @param bool $negativeAllowed
	 * @param bool $positiveAllowed
	 * @return bool
	 */
	private static function check(mixed $str, bool $negativeAllowed=true, bool $positiveAllowed=true): bool
	{
		// négatif interdit
		if (false === $negativeAllowed && str_contains($str, '-')) {
			return false;
		}

		// positif interdit
		if (false === $positiveAllowed && !str_contains($str, '-')) {
			return false;
		}

		$str = str_replace('.', '', $str);
		if (str_starts_with($str, '-')) {
			$str = substr($str, 1);
		}

		return ctype_digit($str);
	}

	/**
	 * @param string|null $str
	 * @param bool $addDecimalIfNotPresent
	 * @return string
	 */
	private static function clean(?string $str, bool $addDecimalIfNotPresent=true): string
	{
		if ($str === null || $str === '') {
			return '0';
		}

		$str = trim($str);
		if (str_starts_with($str, '+')) {
			$str = substr($str, 1);
		}
		$str = str_replace(' ', '', $str);

		// formattage virgule
		$str = str_replace(',', '.', $str);
		if ($addDecimalIfNotPresent && substr_count($str, '.') === 0) {
			$str .= '.0';
		}

		return $str;
	}


	// ========== Arrondissement d'un nombre ==========

	/**
	 * Arrondi un flottant au plus petit flottant supérieur
	 * @param float $nombre le flottant à arrondir
	 * @param int $precision le nombre de chiffres après la virgule à garder
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
	 * @param int $precision le nombre de chiffres après la virgule à garder
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
	 * @param float|int $val
	 * @return bool
	 */
	public static function isInteger(float|int $val): bool
	{
		return ($val - round($val) === 0);
	}

	/**
	 * @param float|int $val
	 * @return bool
	 */
	public static function isFloat(float|int $val): bool
	{
		return ($val - round($val) !== 0);
	}

	/**
	 * Calcule le nombre de chiffres d'un entier.
	 * @example cette fonction retourne l'entier 6 pour le nombre 112233
	 * @param int $int l'entier pour lequel son nombre de chiffres est calculé
	 * @return int le nombre de chiffres de l'entier
	 */
	public static function getNbDigitsOfInt(int $int): int
	{
		return strlen((string) (int) $int);
	}

	// ========== Mathématiques ==========

	/**
	 * Récupère la valeur numérique décimale d'un nombre sous forme de flottant
	 * @example cette fonction retourne le flottant 0.3344 pour le nombre 1122.3344
	 * @param float $float le nombre pour lequel la partie décimale est récupérée
	 * @return float la partie décimale du nombre, sous forme de flottant
	 */
	public static function decimal(float $float): float
	{
		if (!self::isFloat($float)) {
			return 0;
		}

		$whole = floor($float);
		return $float - $whole;
	}

	/**
	 * Récupère la valeur numérique décimale d'un nombre sous forme d'entier
	 * @example cette fonction retourne l'entier 3344 pour le nombre 1122.3344
	 * @param float $float le nombre pour lequel la partie décimale est récupérée
	 * @return int la partie décimale du nombre, sous forme d'entier
	 */
	public static function decimalPart(float $float): int
	{
		if (!self::isFloat($float)) {
			return 0;
		}

		$whole = floor($float);
		$decimal = substr($float - $whole,2);

		// cette solution ne fonctione pas car le cast du float en string génère des problèmes en fonction de la locale
		//$floatStr = str_replace(',', '.', (string) $float);
		//[$whole, $decimal] = explode('.', $floatStr);
		//[$whole, $decimal] = sscanf($floatStr, '%d.%d'); // identique à  ligne du dessus

		return (int) $decimal;
	}

	/**
	 * @param float|int $number
	 * @return bool
	 */
	public static function checkLuhn(float|int $number): bool
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