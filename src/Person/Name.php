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
	 * @return bool
	 */
	public static function checkFirstName(?string $value): bool
	{
		return preg_match('/^([a-zA-Z\'àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\s-]{3,100})+$/u', $value);
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	public static function checkGivenName(?string $value): bool
	{
		return self::checkFirstName($value);
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	public static function checkLastName(?string $value): bool
	{
		return preg_match('/^([a-zA-Z\'àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\s-]){3,100}+$/u', $value);
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	public static function checkFamilyName(?string $value): bool
	{
		return self::checkLastName($value);
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