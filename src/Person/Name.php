<?php

namespace Osimatic\Helpers\Person;

/**
 * Class Name
 * @package Osimatic\Helpers\Person
 */
class Name
{
	/**
	 * @var int|null
	 */
	private $title = 0;

	/**
	 * @var string|null
	 */
	private $firstName;

	/**
	 * @var string|null
	 */
	private $lastName;


	// ========== Vérification ==========

	/**
	 * @param string $value
	 * @return bool
	 */
	public static function checkCivility($value): bool
	{
		return preg_match('/[0-2]/', $value);
	}

	/**
	 * @param string $value
	 * @param bool $numbersAllowed
	 * @return bool
	 */
	public static function checkFirstName(?string $value, bool $numbersAllowed=false): bool
	{
		return preg_match('/^(['.($numbersAllowed?'0-9':'').'a-zA-Z\'àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\s-]{3,120})$/u', $value);
	}

	/**
	 * @param string $value
	 * @param bool $numbersAllowed
	 * @return bool
	 */
	public static function checkGivenName(?string $value, bool $numbersAllowed=false): bool
	{
		return self::checkFirstName($value, $numbersAllowed);
	}

	/**
	 * @param string $value
	 * @param bool $numbersAllowed
	 * @return bool
	 */
	public static function checkLastName(?string $value, bool $numbersAllowed=false): bool
	{
		return preg_match('/^(['.($numbersAllowed?'0-9':'').'a-zA-Z\'àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\s-]{2,120})$/u', $value);
	}

	/**
	 * @param string $value
	 * @param bool $numbersAllowed
	 * @return bool
	 */
	public static function checkFamilyName(?string $value, bool $numbersAllowed=false): bool
	{
		return self::checkLastName($value, $numbersAllowed);
	}


	// ========== Affichage ==========

	/**
	 * @param int $civility
	 * @param string $firstName
	 * @param string $lastName
	 * @return string
	 */
	public static function getFormattedName(?int $civility, ?string $firstName, ?string $lastName): ?string
	{
		return (new NameFormatter())->format(
			(new self())
				->setTitle($civility)
				->setFirstName($firstName)
				->setLastName($lastName)
		);
	}

	/**
	 * @param Name $name
	 * @return string
	 */
	public static function formatFromTwig(Name $name): ?string
	{
		return (new NameFormatter())->format($name);
	}

	/**
	 * @return string
	 */
	public function format(): ?string
	{
		return (new NameFormatter())->format($this);
	}

	public function __toString()
	{
		return $this->format() ?? '';
	}


	// ========== Get / Set ==========

	/**
	 * @return int|null
	 */
	public function getTitle(): ?int
	{
		return $this->title;
	}

	/**
	 * @param int|null $title
	 * @return Name
	 */
	public function setTitle(?int $title): self
	{
		$this->title = $title;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getFirstName(): ?string
	{
		return $this->firstName;
	}

	/**
	 * @param string|null $firstName
	 * @return Name
	 */
	public function setFirstName(?string $firstName): self
	{
		$this->firstName = $firstName;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getLastName(): ?string
	{
		return $this->lastName;
	}

	/**
	 * @param string|null $lastName
	 * @return Name
	 */
	public function setLastName(?string $lastName): self
	{
		$this->lastName = $lastName;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getGivenName(): ?string
	{
		return $this->firstName;
	}

	/**
	 * @param string|null $firstName
	 * @return Name
	 */
	public function setGivenName(?string $firstName): self
	{
		$this->firstName = $firstName;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getFamilyName(): ?string
	{
		return $this->lastName;
	}

	/**
	 * @param string|null $lastName
	 * @return Name
	 */
	public function setFamilyName(?string $lastName): self
	{
		$this->lastName = $lastName;

		return $this;
	}

}