<?php

namespace Osimatic\Helpers\Person;

use Osimatic\Helpers\Location\Place;
use Osimatic\Helpers\Location\PostalAddress;
use Osimatic\Helpers\Organization\Organization;

/**
 * Class Person
 * @package Osimatic\Helpers\Person
 */
class Person
{
	/**
	 * The identifier property represents any kind of identifier.
	 * @var string|null
	 */
	private $identifier;

	/**
	 * Given name, e. g. the first name of a Person. This can be used along with familyName.
	 * @var string|null
	 */
	private $givenName;

	/**
	 * Family name, e. g. the last name of a Person. This can be used along with givenName.
	 * @var string|null
	 */
	private $familyName;

	/**
	 * An additional name for a Person, can be used for a middle name.
	 * @var string|null
	 */
	private $additionalName;

	/**
	 * Gender of the person. 1 for male, 2 for female.
	 * @var int|null
	 */
	private $gender;

	/**
	 * Physical address of the person.
	 * @var PostalAddress|null
	 */
	private $address;

	/**
	 * Date of birth.
	 * @var \DateTime|null
	 */
	private $birthDate;

	/**
	 * The place where the person was born.
	 * @var Place|null
	 */
	private $birthPlace;

	/**
	 * Date of death.
	 * @var \DateTime|null
	 */
	private $deathDate;

	/**
	 * The place where the person died.
	 * @var Place|null
	 */
	private $deathPlace;

	/**
	 * Email address.
	 * @var string|null
	 */
	private $nationality;

	/**
	 * The height of the person.
	 * @var string|null
	 */
	private $height;

	/**
	 * The weight of the person.
	 * @var string|null
	 */
	private $weight;

	/**
	 * Nationality of the person (country code).
	 * @var string|null
	 */
	private $email;

	/**
	 * The fixed-line number of the person.
	 * @var string|null
	 */
	private $fixedLineNumber;

	/**
	 * The mobile number of the person.
	 * @var string|null
	 */
	private $mobileNumber;

	/**
	 * A contact location for a person's residence.
	 * @var Place|null
	 */
	private $homeLocation;

	/**
	 * A contact location for a person's place of work.
	 * @var Place|null
	 */
	private $workLocation;

	/**
	 * Organizations that the person works for.
	 * @var Organization|null
	 */
	private $worksFor;

	/**
	 * The job title of the person (for example, Financial Manager).
	 * @var string|null
	 */
	private $jobTitle;

	/**
	 * The International Standard of Industrial Classification of All Economic Activities (ISIC), Revision 4.
	 * @var string|null
	 */
	private $isicV4;

	/**
	 * The Tax / Fiscal ID of the organization or person, e.g. the TIN in the US or the CIF/NIF in Spain.
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
	 * @return Place|null
	 */
	public function getBirthPlace(): ?Place
	{
		return $this->birthPlace;
	}

	/**
	 * @param Place|null $birthPlace
	 */
	public function setBirthPlace(?Place $birthPlace): void
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
	 * @return Place|null
	 */
	public function getDeathPlace(): ?Place
	{
		return $this->deathPlace;
	}

	/**
	 * @param Place|null $deathPlace
	 */
	public function setDeathPlace(?Place $deathPlace): void
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
	 * @return Place|null
	 */
	public function getHomeLocation(): ?Place
	{
		return $this->homeLocation;
	}

	/**
	 * @param Place|null $homeLocation
	 */
	public function setHomeLocation(?Place $homeLocation): void
	{
		$this->homeLocation = $homeLocation;
	}

	/**
	 * @return Place|null
	 */
	public function getWorkLocation(): ?Place
	{
		return $this->workLocation;
	}

	/**
	 * @param Place|null $workLocation
	 */
	public function setWorkLocation(?Place $workLocation): void
	{
		$this->workLocation = $workLocation;
	}

	/**
	 * @return Organization|null
	 */
	public function getWorksFor(): ?Organization
	{
		return $this->worksFor;
	}

	/**
	 * @param Organization|null $worksFor
	 */
	public function setWorksFor(?Organization $worksFor): void
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