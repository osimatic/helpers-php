<?php

namespace Osimatic\Messaging;

enum PhoneNumberType: int
{
	case MOBILE 		= \libphonenumber\PhoneNumberType::MOBILE;
	case FIXED_LINE 	= \libphonenumber\PhoneNumberType::FIXED_LINE;
	case PREMIUM_RATE 	= \libphonenumber\PhoneNumberType::PREMIUM_RATE;
	case TOLL_FREE 		= \libphonenumber\PhoneNumberType::TOLL_FREE;
	case SHARED_COST 	= \libphonenumber\PhoneNumberType::SHARED_COST;
	case VOIP 			= \libphonenumber\PhoneNumberType::VOIP;
	case UNKNOWN 		= \libphonenumber\PhoneNumberType::UNKNOWN;


	/**
	 * @return string
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
	 * @param string|null $type
	 * @return PhoneNumberType|null
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