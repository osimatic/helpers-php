<?php

namespace Osimatic\Validator\Constraints;

use Osimatic\Messaging\PhoneNumberType;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class PhoneNumber extends Constraint
{
	public string $message;
	public ?PhoneNumberType $phoneNumberType;

	#[HasNamedArguments]
	public function __construct(?PhoneNumberType $phoneNumberType=null, ?string $message=null, array $groups = null, mixed $payload = null)
	{
		parent::__construct([], $groups, $payload);

		$this->message = $message ?? 'phone_number.invalid';
		$this->phoneNumberType = $phoneNumberType;
	}
}