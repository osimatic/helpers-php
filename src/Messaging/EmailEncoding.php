<?php

namespace Osimatic\Messaging;

enum EmailEncoding: string {
	case _7BIT = '7bit';
	case _8BIT = '8bit';
	case BASE64 = 'base64';
	case BINARY = 'binary';
	case QUOTED_PRINTABLE = 'quoted-printable';

	public static function parse(?string $value): ?self
	{
		if (null === $value) {
			return null;
		}

		$value = mb_strtolower($value);
		return self::tryFrom($value);
	}
}