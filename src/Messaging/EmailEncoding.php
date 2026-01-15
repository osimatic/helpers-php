<?php

namespace Osimatic\Messaging;

/**
 * Represents the available encoding methods for email content transfer.
 * This enum defines the transfer encoding mechanisms used to encode email message bodies for transmission over SMTP.
 */
enum EmailEncoding: string {
	/**
	 * 7-bit encoding, suitable for ASCII text.
	 */
	case _7BIT = '7bit';

	/**
	 * 8-bit encoding, suitable for extended ASCII text.
	 */
	case _8BIT = '8bit';

	/**
	 * Base64 encoding, suitable for binary data and attachments.
	 */
	case BASE64 = 'base64';

	/**
	 * Binary encoding, for unencoded 8-bit data.
	 */
	case BINARY = 'binary';

	/**
	 * Quoted-printable encoding, suitable for text with occasional non-ASCII characters.
	 */
	case QUOTED_PRINTABLE = 'quoted-printable';

	/**
	 * Parse a string value into an EmailEncoding enum case.
	 * @param string|null $value The encoding string to parse (case-insensitive)
	 * @return EmailEncoding|null The corresponding EmailEncoding case, or null if the value is null or not recognized
	 */
	public static function parse(?string $value): ?self
	{
		if (null === $value) {
			return null;
		}

		$value = mb_strtolower($value);
		return self::tryFrom($value);
	}
}