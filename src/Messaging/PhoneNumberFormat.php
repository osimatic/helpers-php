<?php

namespace Osimatic\Messaging;

/**
 * Represents the available formats for phone number output.
 * This enum wraps the libphonenumber library's phone number formats.
 *
 * @link https://www.itu.int/rec/T-REC-E.123 ITU-T E.123 recommendation (INTERNATIONAL and NATIONAL formats)
 * @link https://www.rfc-editor.org/rfc/rfc3966 RFC 3966 (RFC3966 format)
 */
enum PhoneNumberFormat: int
{
	/**
	 * E.164 format: international format with no formatting applied (e.g., "+41446681800").
	 *
	 * @link https://www.itu.int/rec/T-REC-E.164 ITU-T E.164 recommendation
	 */
	case E164 = \libphonenumber\PhoneNumberFormat::E164->value;

	/**
	 * International format as per ITU-T E.123 (e.g., "+41 44 668 1800").
	 */
	case INTERNATIONAL = \libphonenumber\PhoneNumberFormat::INTERNATIONAL->value;

	/**
	 * National format as per ITU-T E.123 (e.g., "044 668 1800").
	 */
	case NATIONAL = \libphonenumber\PhoneNumberFormat::NATIONAL->value;

	/**
	 * RFC 3966 format: international format with hyphens and "tel:" prefix (e.g., "tel:+41-44-668-1800").
	 */
	case RFC3966 = \libphonenumber\PhoneNumberFormat::RFC3966->value;

	/**
	 * Parse a string or libphonenumber PhoneNumberFormat into a PhoneNumberFormat enum case.
	 * @param string|\libphonenumber\PhoneNumberFormat|null $format The phone number format to parse (string is case-insensitive)
	 * @return PhoneNumberFormat|null The corresponding PhoneNumberFormat case, or null if not recognized
	 */
	public static function parse(string|\libphonenumber\PhoneNumberFormat|null $format): ?PhoneNumberFormat
	{
		if (null === $format) {
			return null;
		}

		if ($format instanceof \libphonenumber\PhoneNumberFormat) {
			return self::tryFrom($format->value);
		}

		return match (mb_strtoupper($format)) {
			'E164'                          => self::E164,
			'INTERNATIONAL', 'INTL'        => self::INTERNATIONAL,
			'NATIONAL', 'NAT'              => self::NATIONAL,
			'RFC3966', 'RFC', 'TEL'        => self::RFC3966,
			default                        => null,
		};
	}
}