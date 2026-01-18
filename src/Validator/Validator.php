<?php

namespace Osimatic\Validator;

use Symfony\Component\Validator\Validator\ValidatorInterface;

class Validator
{
	private static ?ValidatorInterface $instance = null;

	private function __construct()
	{
	}

	public static function getInstance(): ValidatorInterface
	{
		if (self::$instance === null) {
			self::$instance = \Symfony\Component\Validator\Validation::createValidatorBuilder()
				->addMethodMapping('loadValidatorMetadata')
				->getValidator();
		}

		return self::$instance;
	}
}