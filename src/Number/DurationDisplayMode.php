<?php

namespace Osimatic\Number;

/**
 * Enum DurationDisplayMode
 * Represents different display modes for duration formatting
 */
enum DurationDisplayMode: string
{
	case STANDARD = 'standard';
	case CHRONO = 'chrono';
	case INPUT_TIME = 'input_time';
	case DECIMAL = 'decimal';
	case SECONDS = 'seconds';

	/**
	 * Parses a string value to a DurationDisplayMode enum
	 * @param string|null $value the string value to parse
	 * @return self|null the corresponding enum value, or null if invalid
	 */
	public static function parse(?string $value): ?self {
		if (null === $value) {
			return null;
		}

		$value = mb_strtolower($value);
		return self::tryFrom($value);
	}
}