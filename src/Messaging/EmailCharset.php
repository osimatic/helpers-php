<?php

namespace Osimatic\Messaging;

/**
 * Represents the available character encoding sets for email messages.
 * This enum defines the character sets that can be used to encode email content, ensuring proper display of text in different languages and scripts.
 */
enum EmailCharset: string {
	/**
	 * US-ASCII character set (7-bit encoding).
	 */
	case ASCII = 'us-ascii';

	/**
	 * ISO-8859-1 (Latin-1) character set, supporting Western European languages.
	 */
	case ISO88591 = 'iso-8859-1';

	/**
	 * UTF-8 character set, supporting all Unicode characters.
	 */
	case UTF8 = 'utf-8';

	/**
	 * Parse a string value into an EmailCharset enum case.
	 * This method handles various string formats and aliases for character sets.
	 * @param string|null $value The character set string to parse (case-insensitive)
	 * @return EmailCharset|null The corresponding EmailCharset case, or null if the value is null or not recognized
	 */
	public static function parse(?string $value): ?self
	{
		if (null === $value) {
			return null;
		}

		$value = mb_strtolower($value);

		if ('utf8' === $value) {
			return self::UTF8;
		}
		if ('ascii' === $value) {
			return self::ASCII;
		}
		if (in_array($value, ['iso8859', 'iso88591'], true)) {
			return self::ISO88591;
		}

		return self::tryFrom($value);
	}
}