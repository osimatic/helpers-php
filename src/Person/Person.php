<?php

namespace Osimatic\Helpers\Person;

use Osimatic\Helpers\Location\PlaceInterface;
use Osimatic\Helpers\Location\PostalAddress;
use Osimatic\Helpers\Organization\OrganizationInterface;

/**
 * @deprecated use PersonInterface instead
 */
class Person implements PersonInterface
{
	/**
	 * @var string|null
	 */
	private $identifier;

	/**
	 * @var string|null
	 */
	private $givenName;

	/**
	 * @var string|null
	 */
	private $familyName;

	/**
	 * @var string|null
	 */
	private $additionalName;

	/**
	 * @var int|null
	 */
	private $gender;

	/**
	 * @var PostalAddress|null
	 */
	private $address;

	/**
	 * @var \DateTime|null
	 */
	private $birthDate;

	/**
	 * @var PlaceInterface|null
	 */
	private $birthPlace;

	/**
	 * @var \DateTime|null
	 */
	private $deathDate;

	/**
	 * @var PlaceInterface|null
	 */
	private $deathPlace;

	/**
	 * @var string|null
	 */
	private $nationality;

	/**
	 * @var string|null
	 */
	private $height;

	/**
	 * @var string|null
	 */
	private $weight;

	/**
	 * @var string|null
	 */
	private $email;

	/**
	 * @var string|null
	 */
	private $fixedLineNumber;

	/**
	 * @var string|null
	 */
	private $mobileNumber;

	/**
	 * @var PlaceInterface|null
	 */
	private $homeLocation;

	/**
	 * @var PlaceInterface|null
	 */
	private $workLocation;

	/**
	 * @var OrganizationInterface|null
	 */
	private $worksFor;

	/**
	 * @var string|null
	 */
	private $jobTitle;

	/**
	 * @var string|null
	 */
	private $isicV4;

	/**
	 * @var string|null
	 */
	private $taxID;



	/**
	 * @return string
	 */
	public function getFormattedName(): ?string
	{
		return Name::getFormattedName($this->gender, $this->givenName, $this->familyName);
	}

	public function __toString()
	{
		return $this->getFormattedName() ?? '';
	}







	// ========== Get / Set ==========

	/**
	 * @return string|null
	 */
	public function getIdentifier(): ?string
	{
		return $this->identifier;
	}

	/**
	 * @param string|null $identifier
	 */
	public function setIdentifier(?string $identifier): void
	{
		$this->identifier = $identifier;
	}

	/**
	 * @return string|null
	 */
	public function getGivenName(): ?string
	{
		return $this->givenName;
	}

	/**
	 * @param string|null $givenName
	 */
	public function setGivenName(?string $givenName): void
	{
		$this->givenName = $givenName;
	}

	/**
	 * @return string|null
	 */
	public function getFamilyName(): ?string
	{
		return $this->familyName;
	}

	/**
	 * @param string|null $familyName
	 */
	public function setFamilyName(?string $familyName): void
	{
		$this->familyName = $familyName;
	}

	/**
	 * @return string|null
	 */
	public function getAdditionalName(): ?string
	{
		return $this->additionalName;
	}

	/**
	 * @param string|null $additionalName
	 */
	public function setAdditionalName(?string $additionalName): void
	{
		$this->additionalName = $additionalName;
	}

	/**
	 * @return int|null
	 */
	public function getGender(): ?int
	{
		return $this->gender;
	}

	/**
	 * @param int|null $gender
	 */
	public function setGender(?int $gender): void
	{
		$this->gender = $gender;
	}

	/**
	 * @return PostalAddress|null
	 */
	public function getAddress(): ?PostalAddress
	{
		return $this->address;
	}

	/**
	 * @param PostalAddress|null $address
	 */
	public function setAddress(?PostalAddress $address): void
	{
		$this->address = $address;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getBirthDate(): ?\DateTime
	{
		return $this->birthDate;
	}

	/**
	 * @param \DateTime|null $birthDate
	 */
	public function setBirthDate(?\DateTime $birthDate): void
	{
		$this->birthDate = $birthDate;
	}

	/**
	 * @return PlaceInterface|null
	 */
	public function getBirthPlace(): ?PlaceInterface
	{
		return $this->birthPlace;
	}

	/**
	 * @param PlaceInterface|null $birthPlace
	 */
	public function setBirthPlace(?PlaceInterface $birthPlace): void
	{
		$this->birthPlace = $birthPlace;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getDeathDate(): ?\DateTime
	{
		return $this->deathDate;
	}

	/**
	 * @param \DateTime|null $deathDate
	 */
	public function setDeathDate(?\DateTime $deathDate): void
	{
		$this->deathDate = $deathDate;
	}

	/**
	 * @return PlaceInterface|null
	 */
	public function getDeathPlace(): ?PlaceInterface
	{
		return $this->deathPlace;
	}

	/**
	 * @param PlaceInterface|null $deathPlace
	 */
	public function setDeathPlace(?PlaceInterface $deathPlace): void
	{
		$this->deathPlace = $deathPlace;
	}

	/**
	 * @return string|null
	 */
	public function getNationality(): ?string
	{
		return $this->nationality;
	}

	/**
	 * @param string|null $nationality
	 */
	public function setNationality(?string $nationality): void
	{
		$this->nationality = $nationality;
	}

	/**
	 * @return string|null
	 */
	public function getHeight(): ?string
	{
		return $this->height;
	}

	/**
	 * @param string|null $height
	 */
	public function setHeight(?string $height): void
	{
		$this->height = $height;
	}

	/**
	 * @return string|null
	 */
	public function getWeight(): ?string
	{
		return $this->weight;
	}

	/**
	 * @param string|null $weight
	 */
	public function setWeight(?string $weight): void
	{
		$this->weight = $weight;
	}

	/**
	 * @return string|null
	 */
	public function getEmail(): ?string
	{
		return $this->email;
	}

	/**
	 * @param string|null $email
	 */
	public function setEmail(?string $email): void
	{
		$this->email = $email;
	}

	/**
	 * @return string|null
	 */
	public function getFixedLineNumber(): ?string
	{
		return $this->fixedLineNumber;
	}

	/**
	 * @param string|null $fixedLineNumber
	 */
	public function setFixedLineNumber(?string $fixedLineNumber): void
	{
		$this->fixedLineNumber = $fixedLineNumber;
	}

	/**
	 * @return string|null
	 */
	public function getMobileNumber(): ?string
	{
		return $this->mobileNumber;
	}

	/**
	 * @param string|null $mobileNumber
	 */
	public function setMobileNumber(?string $mobileNumber): void
	{
		$this->mobileNumber = $mobileNumber;
	}

	/**
	 * @return PlaceInterface|null
	 */
	public function getHomeLocation(): ?PlaceInterface
	{
		return $this->homeLocation;
	}

	/**
	 * @param PlaceInterface|null $homeLocation
	 */
	public function setHomeLocation(?PlaceInterface $homeLocation): void
	{
		$this->homeLocation = $homeLocation;
	}

	/**
	 * @return PlaceInterface|null
	 */
	public function getWorkLocation(): ?PlaceInterface
	{
		return $this->workLocation;
	}

	/**
	 * @param PlaceInterface|null $workLocation
	 */
	public function setWorkLocation(?PlaceInterface $workLocation): void
	{
		$this->workLocation = $workLocation;
	}

	/**
	 * @return OrganizationInterface|null
	 */
	public function getWorksFor(): ?OrganizationInterface
	{
		return $this->worksFor;
	}

	/**
	 * @param OrganizationInterface|null $worksFor
	 */
	public function setWorksFor(?OrganizationInterface $worksFor): void
	{
		$this->worksFor = $worksFor;
	}

	/**
	 * @return string|null
	 */
	public function getJobTitle(): ?string
	{
		return $this->jobTitle;
	}

	/**
	 * @param string|null $jobTitle
	 */
	public function setJobTitle(?string $jobTitle): void
	{
		$this->jobTitle = $jobTitle;
	}

	/**
	 * @return string|null
	 */
	public function getIsicV4(): ?string
	{
		return $this->isicV4;
	}

	/**
	 * @param string|null $isicV4
	 */
	public function setIsicV4(?string $isicV4): void
	{
		$this->isicV4 = $isicV4;
	}

	/**
	 * @return string|null
	 */
	public function getTaxID(): ?string
	{
		return $this->taxID;
	}

	/**
	 * @param string|null $taxID
	 */
	public function setTaxID(?string $taxID): void
	{
		$this->taxID = $taxID;
	}

}