<?php

namespace Osimatic\Helpers\Person;

enum Gender: int
{
	case UNKNOWN = 0;
	case MALE = 1;
	case FEMALE = 2;


	public static function parse($gender): Gender
	{
		if (null === $gender) {
			return self::UNKNOWN;
		}
		$gender = mb_strtoupper($gender);
		if (in_array($gender, ['MR', 'M', 1, 'H', 'HOMME', 'MALE'])) {
			return self::MALE;
		}
		if (in_array($gender, ['MME', 'MELLE', 'MSELLE', 2, 'F', 'FEMME', 'FEMALE'])) {
			return self::FEMALE;
		}
		return self::UNKNOWN;
	}

}