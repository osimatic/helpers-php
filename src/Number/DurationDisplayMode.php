<?php

namespace Osimatic\Number;

enum DurationDisplayMode: string
{
	case STANDARD = 'standard';
	case CHRONO = 'chrono';
	case INPUT_TIME = 'input_time';

	public static function parse(?string $value): ?self {
		if (null === $value) {
			return null;
		}

		$value = mb_strtolower($value);
		return self::tryFrom($value);
	}
}