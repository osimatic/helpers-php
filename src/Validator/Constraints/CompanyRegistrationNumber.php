<?php

namespace Osimatic\Validator\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute]
class CompanyRegistrationNumber extends Constraint
{
	public string $companyCountry;
	public string $message;

	#[HasNamedArguments]
	public function __construct(string $companyCountry='FR', ?string $message=null, array $groups = null, mixed $payload = null)
	{
		parent::__construct([], $groups, $payload);

		$this->companyCountry = $companyCountry;
		$this->message = $message ?? 'company_registration_number.invalid';
	}
}