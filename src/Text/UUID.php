<?php

namespace Osimatic\Text;

/**
 * Utility class for working with UUIDs (Universally Unique Identifiers).
 * Provides methods for generating and validating UUIDs in RFC 4122 format.
 */
class UUID
{
	/**
	 * Validates a UUID string according to RFC 4122 format.
	 * @param string $uuid The UUID string to validate
	 * @return bool True if the UUID is valid, false otherwise
	 */
	public static function check(string $uuid): bool
	{
		$validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
			->addMethodMapping('loadValidatorMetadata')
			->getValidator();
		return $validator->validate($uuid, new \Symfony\Component\Validator\Constraints\Uuid())->count() === 0;
	}

	/**
	 * Generates a random UUID v4 (version 4) according to RFC 4122.
	 * UUID v4 is randomly generated and has no inherent structure.
	 * @return string A UUID v4 string in the format xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx
	 * @throws \Exception If random_bytes fails to generate random data
	 */
	public static function generate(): string
	{
		$data = random_bytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);    // Set version to 0100 (UUID v4)
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);    // Set bits 6-7 to 10 (RFC 4122 variant)
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));

		/*
		try {
			return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
				// 32 bits for "time_low"
				random_int( 0, 0xffff ), random_int( 0, 0xffff ),

				// 16 bits for "time_mid"
				random_int( 0, 0xffff ),

				// 16 bits for "time_hi_and_version",
				// four most significant bits holds version number 4
				random_int( 0, 0x0fff ) | 0x4000,

				// 16 bits, 8 bits for "clk_seq_hi_res",
				// 8 bits for "clk_seq_low",
				// two most significant bits holds zero and one for variant DCE1.1
				random_int( 0, 0x3fff ) | 0x8000,

				// 48 bits for "node"
				random_int( 0, 0xffff ), random_int( 0, 0xffff ), random_int( 0, 0xffff )
			);
		}
		catch (\Exception $e) {
		}
		*/
	}

	/**
	 * Extracts the version number from a UUID.
	 * @param string $uuid The UUID string
	 * @return int|null The version number (1-5), or null if invalid UUID
	 */
	public static function getVersion(string $uuid): ?int
	{
		if (!self::check($uuid)) {
			return null;
		}

		$hex = str_replace('-', '', $uuid);
		$versionHex = $hex[12];
		return (int)hexdec($versionHex);
	}

	/**
	 * Extracts the variant from a UUID.
	 * @param string $uuid The UUID string
	 * @return string|null The variant identifier (e.g., 'RFC4122'), or null if invalid UUID
	 */
	public static function getVariant(string $uuid): ?string
	{
		if (!self::check($uuid)) {
			return null;
		}

		$hex = str_replace('-', '', $uuid);
		$variantByte = hexdec($hex[16]);

		if (($variantByte & 0x80) === 0x00) {
			return 'NCS';
		}
		if (($variantByte & 0xC0) === 0x80) {
			return 'RFC4122';
		}
		if (($variantByte & 0xE0) === 0xC0) {
			return 'Microsoft';
		}
		return 'Reserved';
	}

}