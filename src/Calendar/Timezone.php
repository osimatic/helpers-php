<?php

namespace Osimatic\Helpers\Calendar;

/**
 * Class Timezone
 * @package Osimatic\Helpers\Calendar
 */
class Timezone
{
	/**
	 * Vérifie la validité d'un fuseau horaire
	 * @param string $timezone
	 * @param string|null $countryCode
	 * @return bool
	 * @link https://www.php.net/manual/en/timezones.php
	 */
	public static function check(string $timezone, ?string $countryCode=null): bool
	{
		$validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
			->addMethodMapping('loadValidatorMetadata')
			->getValidator();
		$constraint = new \Symfony\Component\Validator\Constraints\Timezone();
		$constraint->countryCode = $countryCode;
		return $validator->validate($timezone, $constraint)->count() === 0;
	}

}