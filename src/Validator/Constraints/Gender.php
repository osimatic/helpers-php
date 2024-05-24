<?php

namespace Osimatic\Validator\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Gender extends Constraint
{
	public string $message;

	#[HasNamedArguments]
	public function __construct(?string $message=null, array $groups = null, mixed $payload = null)
	{
		parent::__construct([], $groups, $payload);

		$this->message = $message ?? 'gender.invalid';
	}
}