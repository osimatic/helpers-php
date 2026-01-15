<?php

namespace Osimatic\Person;

/**
 * Enum Gender
 * Represents a person's gender
 */
enum Gender: int
{
	case UNKNOWN = 0;
	case MALE = 1;
	case FEMALE = 2;

	/**
	 * Parses various gender representations into a Gender enum
	 * @param mixed $gender the gender value to parse (string, int, or null)
	 * @return Gender the parsed Gender enum
	 */
	public static function parse($gender): Gender
	{
		if (null === $gender) {
			return self::UNKNOWN;
		}
		$gender = mb_strtoupper($gender);
		if (in_array($gender, ['MR', 'M', 1, '1', 'H', 'HOMME', 'MALE'], true)) {
			return self::MALE;
		}
		if (in_array($gender, ['MME', 'MELLE', 'MSELLE', 2, '2', 'F', 'FEMME', 'FEMALE'], true)) {
			return self::FEMALE;
		}
		return self::UNKNOWN;
	}

}