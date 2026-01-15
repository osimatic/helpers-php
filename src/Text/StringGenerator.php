<?php

namespace Osimatic\Text;

/**
 * Utility class for generating random strings with various character types and patterns.
 * Provides methods for creating pronounceable words, alphabetic, numeric, and alphanumeric strings.
 */
class StringGenerator
{
	private const string VOWELS 				= 'aeiouy';
	private const string CONSONANTS 			= 'bcdfghjklmnpqrstvwxz';
	private const string LETTERS				= 'abcdefghijklmnopqrstuvwxyz';
	private const string DIGITS					= '0123456789';

	/**
	 * Generates a pronounceable string (i.e., alternating consonants and vowels).
	 * @param int $length The number of characters in the string to generate
	 * @param string|null $consonantsList The list of possible consonants for string generation, or null to use all consonants from the alphabet (default: null)
	 * @param string|null $vowelsList The list of possible vowels for string generation, or null to use all vowels from the alphabet (default: null)
	 * @param bool $randomFirstLetter True to randomly choose whether to start with a consonant or vowel, false otherwise (default: false)
	 * @param bool $startWithConsonant True to start with a consonant, false to start with a vowel (default: true). This parameter is only considered if $randomFirstLetter is false
	 * @return string The generated string
	 */
	public static function getRandomPronounceableWord(int $length, ?string $consonantsList=null, ?string $vowelsList=null, bool $randomFirstLetter=false, bool $startWithConsonant=true): string
	{
		if ($randomFirstLetter) {
			$isConsonant = (random_int(0, 1) === 0);
		}
		else {
			$isConsonant = $startWithConsonant;
		}

		if ($consonantsList === null) {
			$consonantsList = self::CONSONANTS;
		}
		if ($vowelsList === null) {
			$vowelsList = self::VOWELS;
		}
		$consonantsCount = strlen($consonantsList);
		$vowelsCount = strlen($vowelsList);

		$pronounceableWord = '';
		for ($i=0; $i<$length; $i++) {
			if ($isConsonant === true) {
				$pronounceableWord .= $consonantsList[random_int(0, $consonantsCount-1)];
			}
			else {
				$pronounceableWord .= $vowelsList[random_int(0, $vowelsCount-1)];
			}

			$isConsonant = !$isConsonant;
		}

		return $pronounceableWord;
	}

	/**
	 * Generates a random string from a list of characters.
	 * @param int $length The number of characters in the string to generate
	 * @param string $charactersList The list of possible characters for string generation
	 * @return string The generated string
	 */
	public static function getRandomString(int $length, string $charactersList): string
	{
		$randomStr = '';
		$charactersCount = strlen($charactersList);
		for ($i=0; $i<$length; $i++) {
			$randomChar = $charactersList[random_int(0, $charactersCount-1)];
			$randomStr .= $randomChar;
		}
		return $randomStr;
	}

	/**
	 * Generates a random alphabetic string.
	 * @param int $length The number of characters in the string to generate
	 * @param bool $uppercaseEnabled True to generate uppercase alphabetic characters, false otherwise (default: false)
	 * @param bool $lowercaseEnabled True to generate lowercase alphabetic characters, false otherwise (default: true)
	 * @return string The generated string
	 */
	public static function getRandomAlphaString(int $length, bool $uppercaseEnabled=false, bool $lowercaseEnabled=true): string
	{
		if (!$lowercaseEnabled && !$uppercaseEnabled) {
			return '';
		}

		$lettersList = self::LETTERS;
		$lettersCount = strlen($lettersList);

		$alphabeticString = '';
		for ($i=0; $i<$length; $i++) {
			$alphabeticChar = $lettersList[random_int(0, $lettersCount-1)];

			if ($lowercaseEnabled && $uppercaseEnabled) {
				if (random_int(0, 1) === 1) {
					$alphabeticChar = strtoupper($alphabeticChar);
				}
			}
			elseif ($uppercaseEnabled) {
				$alphabeticChar = strtoupper($alphabeticChar);
			}

			$alphabeticString .= $alphabeticChar;
		}

		return $alphabeticString;
	}

	/**
	 * Generates a random numeric string.
	 * @param int $length The number of characters in the string to generate
	 * @param bool $startWith0 True to prevent the string from starting with the digit 0, false to start with any digit (default: false)
	 * @return string The generated string
	 */
	public static function getRandomNumericString(int $length, bool $startWith0=false): string
	{
		$digitsList = self::DIGITS;
		$digitsCount = strlen($digitsList);

		$numericString = '';
		for ($i=0; $i<$length; $i++) {
			$numericChar = $digitsList[random_int(0, $digitsCount-1)];

			if (false === $startWith0 && 0 === $i && '0' === $numericChar) {
				$i--;
			}
			else {
				$numericString .= $numericChar;
			}
		}

		return $numericString;
	}

	/**
	 * Generates a random alphanumeric string (containing both letters and digits).
	 * @param int $length The number of characters in the string to generate
	 * @param bool $uppercaseEnabled True to generate uppercase alphabetic characters, false otherwise (default: false)
	 * @param bool $lowercaseEnabled True to generate lowercase alphabetic characters, false otherwise (default: true)
	 * @return string The generated string
	 */
	public static function getRandomAlphanumericString(int $length, bool $uppercaseEnabled=false, bool $lowercaseEnabled=true): string
	{
		$characterTypesCount = 1;
		if ($uppercaseEnabled && $lowercaseEnabled) {
			$characterTypesCount = 3;
		}
		elseif ($uppercaseEnabled || $lowercaseEnabled) {
			$characterTypesCount = 2;
		}

		do {
			$alphanumericString = '';
			for ($i=0; $i<$length; $i++) {
				if (!$uppercaseEnabled && !$lowercaseEnabled) {
					$alphanumericString .= self::getRandomNumericString(1);
					continue;
				}

				$alphanumericChar = null;
				$characterType = random_int(1, $characterTypesCount);
				switch ($characterType) {
					case 1 :
						$alphanumericChar = self::getRandomNumericString(1);
						break;

					case 2 :
						$alphanumericChar = self::getRandomAlphaString(1);
						if ($uppercaseEnabled && !$lowercaseEnabled) {
							$alphanumericChar = strtoupper(self::getRandomAlphaString(1));
						}
						break;

					case 3 :
						$alphanumericChar = strtoupper(self::getRandomAlphaString(1));
						break;
				}

				$alphanumericString .= $alphanumericChar;
			}
		}
			// Keep generating while the string contains only letters or only digits, because we must return a string with both letters and digits
		while ($characterTypesCount > 1 && (ctype_digit($alphanumericString) || strpbrk($alphanumericString, self::DIGITS) === false));

		return $alphanumericString;
	}
}