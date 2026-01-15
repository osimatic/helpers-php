<?php

namespace Osimatic\Messaging;

/**
 * Represents different types of phone numbers.
 * This enum wraps the libphonenumber library's phone number types and provides additional parsing capabilities for various string formats.
 */
enum PhoneNumberType: int
{
	/**
	 * Mobile phone number type.
	 */
	case MOBILE 		= \libphonenumber\PhoneNumberType::MOBILE;

	/**
	 * Fixed line (landline) phone number type.
	 */
	case FIXED_LINE 	= \libphonenumber\PhoneNumberType::FIXED_LINE;

	/**
	 * Premium rate phone number type (e.g., audiotel services).
	 */
	case PREMIUM_RATE 	= \libphonenumber\PhoneNumberType::PREMIUM_RATE;

	/**
	 * Toll-free phone number type.
	 */
	case TOLL_FREE 		= \libphonenumber\PhoneNumberType::TOLL_FREE;

	/**
	 * Shared cost phone number type.
	 */
	case SHARED_COST 	= \libphonenumber\PhoneNumberType::SHARED_COST;

	/**
	 * VoIP phone number type.
	 */
	case VOIP 			= \libphonenumber\PhoneNumberType::VOIP;

	/**
	 * Unknown phone number type.
	 */
	case UNKNOWN 		= \libphonenumber\PhoneNumberType::UNKNOWN;


	/**
	 * Get the string key representation of the phone number type.
	 * @return string The uppercase string key for this phone number type
	 */
	public function getKey(): string
	{
		return match ($this) {
			self::MOBILE => 'MOBILE',
			self::FIXED_LINE => 'FIXED_LINE',
			self::PREMIUM_RATE => 'PREMIUM_RATE',
			self::TOLL_FREE => 'TOLL_FREE',
			self::SHARED_COST => 'SHARED_COST',
			self::VOIP => 'VOIP',
			self::UNKNOWN => 'UNKNOWN',
		};
	}

	/**
	 * Parse a string value into a PhoneNumberType enum case.
	 * This method handles various string formats and aliases for phone number types, including multilingual variations and common alternative names.
	 * @param string|null $type The phone number type string to parse (case-insensitive)
	 * @return PhoneNumberType|null The corresponding PhoneNumberType case, or null if not recognized
	 */
	public static function parse(?string $type): ?PhoneNumberType
	{
		if (null === $type) {
			return null;
		}

		$type = mb_strtoupper($type);
		if (str_ends_with($type, '_NUMBER')) {
			$type = substr($type, 0, -7);
		}

		if (in_array($type, ['FIXE', 'LAND', 'LANDLINE', 'LAND_LINE', 'FIXED', 'FIXED_LINE', 'GEO', 'GEO_LINE'], true)) {
			return self::FIXED_LINE;
		}

		if (in_array($type, ['MOBILE', 'MOBILE_LINE', 'MOBILE_PHONE', 'CELL', 'CELLPHONE', 'CELL_PHONE'], true)) {
			return self::MOBILE;
		}

		if ($type === 'PREMIUM_RATE' || $type === 'PREMIUM_RATE_LINE' || $type === 'PREMIUM' || $type === 'PREMIUM_LINE' || $type === 'AUDIOTEL') {
			return self::PREMIUM_RATE;
		}

		if ($type === 'FREE' || $type === 'FREE_LINE' || $type === 'TOLL_FREE' || $type === 'TOLL_FREE_LINE') {
			return self::TOLL_FREE;
		}

		if ($type === 'SHARED_COST' || $type === 'SHARED_COST_LINE' || $type === 'SHARED' || $type === 'SHARED_LINE') {
			return self::SHARED_COST;
		}

		if ($type === 'UNKNOWN' || $type === 'OTHER' || $type === 'HIDDEN') {
			return self::UNKNOWN;
		}

		return null;
	}
}