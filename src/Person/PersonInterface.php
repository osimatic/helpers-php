<?php

namespace Osimatic\Helpers\Person;

use Osimatic\Helpers\Location\PlaceInterface;
use Osimatic\Helpers\Location\PostalAddressInterface;
use Osimatic\Helpers\Organization\OrganizationInterface;

/**
 * Interface PersonInterface
 * @package Osimatic\Helpers\Person
 */
interface PersonInterface
{

	/**
	 * The identifier property represents any kind of identifier.
	 * @return string|null
	 */
	public function getIdentifier(): ?string;

	/**
	 * @param string|null $identifier
	 */
	public function setIdentifier(?string $identifier): void;

	/**
	 * Given name, e. g. the first name of a Person. This can be used along with familyName.
	 * @return string|null
	 */
	public function getGivenName(): ?string;

	/**
	 * @param string|null $givenName
	 */
	public function setGivenName(?string $givenName): void;

	/**
	 * Family name, e. g. the last name of a Person. This can be used along with givenName.
	 * @return string|null
	 */
	public function getFamilyName(): ?string;

	/**
	 * @param string|null $familyName
	 */
	public function setFamilyName(?string $familyName): void;

	/**
	 * An additional name for a Person, can be used for a middle name.
	 * @return string|null
	 */
	public function getAdditionalName(): ?string;

	/**
	 * @param string|null $additionalName
	 */
	public function setAdditionalName(?string $additionalName): void;

	/**
	 * Gender of the person. 1 for male, 2 for female.
	 * @return int|null
	 */
	public function getGender(): ?int;

	/**
	 * @param int|null $gender
	 */
	public function setGender(?int $gender): void;

	/**
	 * Physical address of the person.
	 * @return PostalAddressInterface|null
	 */
	public function getAddress(): ?PostalAddressInterface;

	/**
	 * @param PostalAddressInterface|null $address
	 */
	public function setAddress(?PostalAddressInterface $address): void;

	/**
	 * Date of birth.
	 * @return \DateTime|null
	 */
	public function getBirthDate(): ?\DateTime;

	/**
	 * @param \DateTime|null $birthDate
	 */
	public function setBirthDate(?\DateTime $birthDate): void;

	/**
	 * The place where the person was born.
	 * @return PlaceInterface|null
	 */
	public function getBirthPlace(): ?PlaceInterface;

	/**
	 * @param PlaceInterface|null $birthPlace
	 */
	public function setBirthPlace(?PlaceInterface $birthPlace): void;

	/**
	 * Date of death.
	 * @return \DateTime|null
	 */
	public function getDeathDate(): ?\DateTime;

	/**
	 * @param \DateTime|null $deathDate
	 */
	public function setDeathDate(?\DateTime $deathDate): void;

	/**
	 * The place where the person died.
	 * @return PlaceInterface|null
	 */
	public function getDeathPlace(): ?PlaceInterface;

	/**
	 * @param PlaceInterface|null $deathPlace
	 */
	public function setDeathPlace(?PlaceInterface $deathPlace): void;

	/**
	 * Nationality of the person (country code).
	 * @return string|null
	 */
	public function getNationality(): ?string;

	/**
	 * @param string|null $nationality
	 */
	public function setNationality(?string $nationality): void;

	/**
	 * The height of the person.
	 * @return string|null
	 */
	public function getHeight(): ?string;

	/**
	 * @param string|null $height
	 */
	public function setHeight(?string $height): void;

	/**
	 * The weight of the person.
	 * @return string|null
	 */
	public function getWeight(): ?string;

	/**
	 * @param string|null $weight
	 */
	public function setWeight(?string $weight): void;

	/**
	 * Email address.
	 * @return string|null
	 */
	public function getEmail(): ?string;

	/**
	 * @param string|null $email
	 */
	public function setEmail(?string $email): void;

	/**
	 * The fixed-line number of the person.
	 * @return string|null
	 */
	public function getFixedLineNumber(): ?string;

	/**
	 * @param string|null $fixedLineNumber
	 */
	public function setFixedLineNumber(?string $fixedLineNumber): void;

	/**
	 * The mobile number of the person.
	 * @return string|null
	 */
	public function getMobileNumber(): ?string;

	/**
	 * @param string|null $mobileNumber
	 */
	public function setMobileNumber(?string $mobileNumber): void;

	/**
	 * A contact location for a person's residence.
	 * @return PlaceInterface|null
	 */
	public function getHomeLocation(): ?PlaceInterface;

	/**
	 * @param PlaceInterface|null $homeLocation
	 */
	public function setHomeLocation(?PlaceInterface $homeLocation): void;

	/**
	 * A contact location for a person's place of work.
	 * @return PlaceInterface|null
	 */
	public function getWorkLocation(): ?PlaceInterface;

	/**
	 * @param PlaceInterface|null $workLocation
	 */
	public function setWorkLocation(?PlaceInterface $workLocation): void;

	/**
	 * Organizations that the person works for.
	 * @return OrganizationInterface|null
	 */
	public function getWorksFor(): ?OrganizationInterface;

	/**
	 * @param OrganizationInterface|null $worksFor
	 */
	public function setWorksFor(?OrganizationInterface $worksFor): void;

	/**
	 * The job title of the person (for example, Financial Manager).
	 * @return string|null
	 */
	public function getJobTitle(): ?string;

	/**
	 * @param string|null $jobTitle
	 */
	public function setJobTitle(?string $jobTitle): void;

	/**
	 * The International Standard of Industrial Classification of All Economic Activities (ISIC), Revision 4.
	 * @return string|null
	 */
	public function getIsicV4(): ?string;

	/**
	 * @param string|null $isicV4
	 */
	public function setIsicV4(?string $isicV4): void;

	/**
	 * The Tax / Fiscal ID of the organization or person, e.g. the TIN in the US or the CIF/NIF in Spain.
	 * @return string|null
	 */
	public function getTaxID(): ?string;

	/**
	 * @param string|null $taxID
	 */
	public function setTaxID(?string $taxID): void;

}