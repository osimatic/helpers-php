<?php

namespace Osimatic\Media;

/**
 * Class Book
 * Provides utilities for validating book and publication identifiers including ISBN and ISSN numbers.
 * Uses Symfony Validator component for validation.
 */
class Book
{
	/**
	 * Normalize an ISBN by removing hyphens, spaces, and converting to uppercase.
	 * @param string $isbn The ISBN number to normalize
	 * @return string The normalized ISBN number
	 */
	public static function normalizeIsbn(string $isbn): string
	{
		return strtoupper(str_replace(['-', ' '], '', trim($isbn)));
	}

	/**
	 * Convert an ISBN-10 to ISBN-13 format.
	 * @param string $isbn10 The ISBN-10 number to convert
	 * @return string|null The ISBN-13 number, or null if the input is not a valid ISBN-10
	 */
	public static function convertIsbn10ToIsbn13(string $isbn10): ?string
	{
		$isbn10 = self::normalizeIsbn($isbn10);

		if (!self::checkIsbn10($isbn10)) {
			return null;
		}

		// Remove the check digit from ISBN-10
		$isbn10Base = substr($isbn10, 0, 9);

		// Add 978 prefix
		$isbn13Base = '978' . $isbn10Base;

		// Calculate ISBN-13 check digit
		$checksum = 0;
		for ($i = 0; $i < 12; $i++) {
			$checksum += (int)$isbn13Base[$i] * (($i % 2 === 0) ? 1 : 3);
		}
		$checkDigit = (10 - ($checksum % 10)) % 10;

		return $isbn13Base . $checkDigit;
	}

	/**
	 * Check if an ISBN number is valid. Accepts both ISBN-10 and ISBN-13 formats.
	 * @param string $isbn The ISBN number to validate
	 * @return bool True if the ISBN is valid, false otherwise
	 * @link https://en.wikipedia.org/wiki/International_Standard_Book_Number
	 */
	public static function checkIsbn(string $isbn): bool
	{
		return self::_checkIsbn($isbn);
	}

	/**
	 * Check if an ISBN-10 number is valid. ISBN-10 format uses 10 digits.
	 * @param string $isbn The ISBN-10 number to validate
	 * @return bool True if the ISBN-10 is valid, false otherwise
	 */
	public static function checkIsbn10(string $isbn): bool
	{
		return self::_checkIsbn($isbn, 'isbn10');
	}

	/**
	 * Check if an ISBN-13 number is valid. ISBN-13 format uses 13 digits.
	 * @param string $isbn The ISBN-13 number to validate
	 * @return bool True if the ISBN-13 is valid, false otherwise
	 */
	public static function checkIsbn13(string $isbn): bool
	{
		return self::_checkIsbn($isbn, 'isbn13');
	}

	/**
	 * Check if an ISBN has an invalid pattern (all identical digits, sequential, etc.).
	 * @param string $isbn The normalized ISBN number to check
	 * @return bool True if the pattern is valid, false if it's an invalid pattern
	 */
	private static function isValidIsbnPattern(string $isbn): bool
	{
		// Remove any non-digit characters except X
		$digits = preg_replace('/[^0-9X]/', '', $isbn);

		if (empty($digits)) {
			return false;
		}

		// Check for all identical digits (0000000000, 1111111111, etc.)
		if (preg_match('/^(.)\1+$/', $digits)) {
			return false;
		}

		// Check for ascending sequences (0123456789, 1234567890)
		$isAscending = true;
		$isDescending = true;
		for ($i = 1; $i < strlen($digits) - 1; $i++) { // -1 to skip check digit
			if (!is_numeric($digits[$i]) || !is_numeric($digits[$i - 1])) {
				continue;
			}
			$current = (int)$digits[$i];
			$previous = (int)$digits[$i - 1];

			if ($current !== ($previous + 1) % 10) {
				$isAscending = false;
			}
			if ($current !== ($previous - 1 + 10) % 10) {
				$isDescending = false;
			}
		}

		if ($isAscending || $isDescending) {
			return false;
		}

		return true;
	}

	/**
	 * Internal method to validate ISBN numbers using Symfony Validator.
	 * @param string $isbn The ISBN number to validate
	 * @param string|null $type The ISBN type ('isbn10' or 'isbn13'), null for both
	 * @return bool True if the ISBN is valid, false otherwise
	 */
	private static function _checkIsbn(string $isbn, ?string $type=null): bool
	{
		if (empty($isbn)) {
			return false;
		}

		$normalizedIsbn = self::normalizeIsbn($isbn);

		// Check for invalid patterns
		if (!self::isValidIsbnPattern($normalizedIsbn)) {
			return false;
		}

		$validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
			->addMethodMapping('loadValidatorMetadata')
			->getValidator();
		$constraint = new \Symfony\Component\Validator\Constraints\Isbn();
		$constraint->type = $type;
		return $validator->validate($isbn, $constraint)->count() === 0;
	}

	/**
	 * Get the ISBN prefix (EAN prefix for ISBN-13).
	 * For ISBN-13, returns '978' or '979'. For ISBN-10, returns null.
	 * @param string $isbn The ISBN number
	 * @return string|null The ISBN prefix, or null if ISBN-10 or invalid
	 */
	public static function getIsbnPrefix(string $isbn): ?string
	{
		$normalizedIsbn = self::normalizeIsbn($isbn);

		if (strlen($normalizedIsbn) === 13) {
			return substr($normalizedIsbn, 0, 3);
		}

		return null;
	}

	/**
	 * Get the registration group (country/language code) from an ISBN-13.
	 * Note: This is a simplified extraction. Full parsing would require the ISBN range database.
	 * @param string $isbn The ISBN-13 number
	 * @return string|null The registration group (1-5 digits after prefix), or null if not ISBN-13
	 */
	public static function getIsbnRegistrationGroup(string $isbn): ?string
	{
		$normalizedIsbn = self::normalizeIsbn($isbn);

		if (strlen($normalizedIsbn) !== 13) {
			return null;
		}

		// Extract potential registration group (usually 1-5 digits after the 3-digit prefix)
		// This is a simplification - actual parsing requires the ISBN range database
		// Common groups: 0 or 1 (English), 2 (French), 3 (German), 4 (Japan), etc.
		$afterPrefix = substr($normalizedIsbn, 3);

		// Try to identify single-digit groups (most common)
		$firstDigit = $afterPrefix[0];
		if (in_array($firstDigit, ['0', '1', '2', '3', '4', '5', '7'])) {
			return $firstDigit;
		}

		// Could be a multi-digit group (80-99, 600-649, etc.)
		// Return first 2 digits as a reasonable approximation
		return substr($afterPrefix, 0, 2);
	}

	/**
	 * Get the check digit from an ISBN.
	 * @param string $isbn The ISBN number
	 * @return string|null The check digit (last character), or null if invalid
	 */
	public static function getIsbnCheckDigit(string $isbn): ?string
	{
		$normalizedIsbn = self::normalizeIsbn($isbn);

		$length = strlen($normalizedIsbn);
		if ($length !== 10 && $length !== 13) {
			return null;
		}

		return substr($normalizedIsbn, -1);
	}

	/**
	 * Get basic information extracted from an ISBN.
	 * @param string $isbn The ISBN number
	 * @return array Associative array with keys: type (isbn10/isbn13), prefix, registration_group, check_digit
	 */
	public static function getIsbnInfo(string $isbn): array
	{
		$normalizedIsbn = self::normalizeIsbn($isbn);
		$length = strlen($normalizedIsbn);

		$info = [
			'type' => null,
			'prefix' => null,
			'registration_group' => null,
			'check_digit' => null,
			'is_valid' => false,
		];

		if ($length === 10) {
			$info['type'] = 'isbn10';
			$info['check_digit'] = substr($normalizedIsbn, -1);
			$info['is_valid'] = self::checkIsbn10($isbn);
		} elseif ($length === 13) {
			$info['type'] = 'isbn13';
			$info['prefix'] = substr($normalizedIsbn, 0, 3);
			$info['registration_group'] = self::getIsbnRegistrationGroup($isbn);
			$info['check_digit'] = substr($normalizedIsbn, -1);
			$info['is_valid'] = self::checkIsbn13($isbn);
		}

		return $info;
	}

	/**
	 * Get the registration group name from an ISBN registration group code.
	 * Returns common country/language names for well-known codes.
	 * @param string $groupCode The registration group code (e.g., '0', '1', '2')
	 * @return string|null The group name, or null if unknown
	 */
	public static function getRegistrationGroupName(string $groupCode): ?string
	{
		$groups = [
			'0' => 'English language',
			'1' => 'English language',
			'2' => 'French language',
			'3' => 'German language',
			'4' => 'Japan',
			'5' => 'Russian Federation',
			'7' => 'China',
			'80' => 'Czech Republic',
			'81' => 'India',
			'82' => 'Norway',
			'83' => 'Poland',
			'84' => 'Spain',
			'85' => 'Brazil',
			'86' => 'Serbia',
			'87' => 'Denmark',
			'88' => 'Italy',
			'89' => 'Korea',
			'90' => 'Netherlands',
			'91' => 'Sweden',
			'92' => 'UNESCO',
			'93' => 'India',
			'94' => 'Netherlands',
			'950' => 'Argentina',
			'951' => 'Finland',
			'952' => 'Finland',
			'953' => 'Croatia',
			'954' => 'Bulgaria',
			'955' => 'Sri Lanka',
			'956' => 'Chile',
			'957' => 'Taiwan',
			'958' => 'Colombia',
			'959' => 'Cuba',
			'960' => 'Greece',
			'961' => 'Slovenia',
			'962' => 'Hong Kong',
			'963' => 'Hungary',
			'964' => 'Iran',
			'965' => 'Israel',
			'966' => 'Ukraine',
			'967' => 'Malaysia',
			'968' => 'Mexico',
			'969' => 'Pakistan',
			'970' => 'Mexico',
			'971' => 'Philippines',
			'972' => 'Portugal',
			'973' => 'Romania',
			'974' => 'Thailand',
			'975' => 'Turkey',
			'976' => 'Caribbean',
			'977' => 'Egypt',
			'978' => 'Nigeria',
			'979' => 'Indonesia',
			'980' => 'Venezuela',
			'981' => 'Singapore',
			'982' => 'South Pacific',
			'983' => 'Malaysia',
			'984' => 'Bangladesh',
			'985' => 'Belarus',
			'986' => 'Taiwan',
			'987' => 'Argentina',
			'988' => 'Hong Kong',
			'989' => 'Portugal',
		];

		return $groups[$groupCode] ?? null;
	}

	/**
	 * Check if an ISSN number is valid. ISSN is used for periodicals, magazines, and journals.
	 * @param string $issn The ISSN number to validate (format: XXXX-XXXX)
	 * @return bool True if the ISSN is valid, false otherwise
	 * @link https://en.wikipedia.org/wiki/International_Standard_Serial_Number
	 */
	public static function checkIssn(string $issn): bool
	{
		if (empty($issn) || $issn === '00000000' || $issn === '0000-0000') {
			return false;
		}

		$validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
			->addMethodMapping('loadValidatorMetadata')
			->getValidator();
		return $validator->validate($issn, new \Symfony\Component\Validator\Constraints\Issn())->count() === 0;
	}

}