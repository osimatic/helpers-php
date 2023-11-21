<?php

namespace Osimatic\Helpers\Validator\Constraints;

use Symfony\Component\Intl\Countries;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CountryValidator extends ConstraintValidator
{
	public function validate(mixed $value, Constraint $constraint): void
	{
		$value = (string) $value;

		if ('UK' === $value) {
			return;
		}

		/** @var Country $constraint */
		if (empty($value) || !Countries::exists($value)) {
			$this->context->buildViolation($constraint->message)
				->setParameter('{{ string }}', $value)
				->addViolation();
		}
	}
}