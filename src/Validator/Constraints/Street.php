<?php

namespace Osimatic\Helpers\Validator\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class Street extends Constraint
{
	public string $message;

	#[HasNamedArguments]
	public function __construct(?string $message=null, array $groups = null, mixed $payload = null)
	{
		parent::__construct([], $groups, $payload);

		$this->message = $message ?? 'street.invalid';
	}
}