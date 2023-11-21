<?php

namespace Osimatic\Helpers\Validator\Constraints;

use Osimatic\Helpers\Messaging\PhoneNumberType;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class PhoneNumberValidator extends ConstraintValidator
{
	/**
	 * Checks if the passed value is valid.
	 * @param mixed $value The value that should be validated
	 * @param Constraint $constraint The constraint for the validation
	 */
	public function validate(mixed $value, Constraint $constraint): void
	{
		if (null === $value || '' === $value) {
			return;
		}

		/** @var PhoneNumber $constraint */

		$phoneNumber = \Osimatic\Helpers\Messaging\PhoneNumber::parse($value);

		// vérification syntaxe numéro téléphone saisi
		if (false === \Osimatic\Helpers\Messaging\PhoneNumber::isValid($phoneNumber)) {
			$this->context->buildViolation($constraint->message)
				->setParameter('{{string}}', $value)
				->addViolation();
		}

		// si aucun type de numéro attendu, c'est ok.
		if (null === $constraint->phoneNumberType) {
			return;
		}

		// si le type du numéro attendu est bien le type correspondant au numéro, c'est ok.
		if (($phoneNumberType = \Osimatic\Helpers\Messaging\PhoneNumber::getType($phoneNumber)) === $constraint->phoneNumberType) {
			return;
		}

		// Si le type du numéro attendu est un fixe et que le numéro saisi est un numéro de type Voip ou box, c'est ok.
		if (PhoneNumberType::FIXED_LINE === $constraint->phoneNumberType && PhoneNumberType::VOIP === $phoneNumberType) {
			return;
		}

		// type numéro saisi non conforme.
		$this->context->buildViolation($constraint->message)
			->setParameter('{{string}}', $value)
			->addViolation();
	}
}