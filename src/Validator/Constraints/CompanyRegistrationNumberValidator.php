<?php

namespace Osimatic\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class CompanyRegistrationNumberValidator extends ConstraintValidator
{
	/**
	 * Checks if the passed value is valid.
	 * @param mixed $value The value that should be validated
	 * @param Constraint $constraint The constraint for the validation
	 */
	public function validate(mixed $value, Constraint $constraint): void
	{
		if (null === $value || '' === $value) {
			return ;
		}

		/** @var CompanyRegistrationNumber $constraint */
		if ($constraint->companyCountry === 'FR' && !\Osimatic\Organization\Company::isValidFranceSiret($value)) {
			$this->context->buildViolation($constraint->message)
				->setParameter('{{value}}', $value)
				->addViolation();
		}

		// todo : autre pays
	}
}