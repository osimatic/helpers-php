<?php

namespace Osimatic\Messaging;

enum EmailCharset: string {
	case ASCII = 'us-ascii';
	case ISO88591 = 'iso-8859-1';
	case UTF8 = 'utf-8';

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