<?php

namespace Osimatic\Helpers\Name;

class Name
{
	/**
	 * @var int|null
	 */
	private $title = 0;

	/**
	 * @var string|null
	 */
	private $firstName = '';

	/**
	 * @var string|null
	 */
	private $lastName = '';

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

}