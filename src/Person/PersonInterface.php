<?php

namespace Osimatic\Person;

use Osimatic\Location\PlaceInterface;
use Osimatic\Location\PostalAddressInterface;
use Osimatic\Organization\OrganizationInterface;

/**
 * Interface for representing a person with various personal, contact, and professional information.
 * This interface defines standard getters and setters for person-related data including
 * names, addresses, contact information, birth/death details, and professional information.
 */
interface PersonInterface
{

	/**
	 * Gets the identifier property which represents any kind of identifier for the person.
	 * This could be a database ID, a UUID, or any other unique identifier.
	 * @return string|null The identifier or null if not set
	 */
	public function getIdentifier(): ?string;

	/**
	 * Sets the identifier property which represents any kind of identifier for the person.
	 * @param string|null $identifier The identifier to set, or null to clear it
	 * @return void
	 */
	public function setIdentifier(?string $identifier): void;

	/**
	 * Gets the given name (first name) of the person.
	 * The given name is typically the first name of a person and can be used along with the family name.
	 * @return string|null The given name or null if not set
	 */
	public function getGivenName(): ?string;

	/**
	 * Sets the given name (first name) of the person.
	 * @param string|null $givenName The given name to set, or null to clear it
	 * @return void
	 */
	public function setGivenName(?string $givenName): void;

	/**
	 * Gets the family name (last name) of the person.
	 * The family name is typically the last name of a person and can be used along with the given name.
	 * @return string|null The family name or null if not set
	 */
	public function getFamilyName(): ?string;

	/**
	 * Sets the family name (last name) of the person.
	 * @param string|null $familyName The family name to set, or null to clear it
	 * @return void
	 */
	public function setFamilyName(?string $familyName): void;

	/**
	 * Gets the additional name of the person.
	 * The additional name can be used for a middle name or any other supplementary name.
	 * @return string|null The additional name or null if not set
	 */
	public function getAdditionalName(): ?string;

	/**
	 * Sets the additional name of the person.
	 * @param string|null $additionalName The additional name to set, or null to clear it
	 * @return void
	 */
	public function setAdditionalName(?string $additionalName): void;

	/**
	 * Gets the gender of the person.
	 * Standard values: 1 for male, 2 for female.
	 * @return int|null The gender code or null if not set
	 */
	public function getGender(): ?int;

	/**
	 * Sets the gender of the person.
	 * Standard values: 1 for male, 2 for female.
	 * @param int|null $gender The gender code to set, or null to clear it
	 * @return void
	 */
	public function setGender(?int $gender): void;

	/**
	 * Gets the physical address of the person.
	 * @return PostalAddressInterface|null The postal address or null if not set
	 */
	public function getAddress(): ?PostalAddressInterface;

	/**
	 * Sets the physical address of the person.
	 * @param PostalAddressInterface|null $address The postal address to set, or null to clear it
	 * @return void
	 */
	public function setAddress(?PostalAddressInterface $address): void;

	/**
	 * Gets the date of birth of the person.
	 * @return \DateTime|null The birth date or null if not set
	 */
	public function getBirthDate(): ?\DateTime;

	/**
	 * Sets the date of birth of the person.
	 * @param \DateTime|null $birthDate The birth date to set, or null to clear it
	 * @return void
	 */
	public function setBirthDate(?\DateTime $birthDate): void;

	/**
	 * Gets the place where the person was born.
	 * @return PlaceInterface|null The birth place or null if not set
	 */
	public function getBirthPlace(): ?PlaceInterface;

	/**
	 * Sets the place where the person was born.
	 * @param PlaceInterface|null $birthPlace The birth place to set, or null to clear it
	 * @return void
	 */
	public function setBirthPlace(?PlaceInterface $birthPlace): void;

	/**
	 * Gets the date of death of the person.
	 * @return \DateTime|null The death date or null if not set
	 */
	public function getDeathDate(): ?\DateTime;

	/**
	 * Sets the date of death of the person.
	 * @param \DateTime|null $deathDate The death date to set, or null to clear it
	 * @return void
	 */
	public function setDeathDate(?\DateTime $deathDate): void;

	/**
	 * Gets the place where the person died.
	 * @return PlaceInterface|null The death place or null if not set
	 */
	public function getDeathPlace(): ?PlaceInterface;

	/**
	 * Sets the place where the person died.
	 * @param PlaceInterface|null $deathPlace The death place to set, or null to clear it
	 * @return void
	 */
	public function setDeathPlace(?PlaceInterface $deathPlace): void;

	/**
	 * Gets the nationality of the person.
	 * Should typically be represented as a country code.
	 * @return string|null The nationality country code or null if not set
	 */
	public function getNationality(): ?string;

	/**
	 * Sets the nationality of the person.
	 * Should typically be represented as a country code.
	 * @param string|null $nationality The nationality country code to set, or null to clear it
	 * @return void
	 */
	public function setNationality(?string $nationality): void;

	/**
	 * Gets the height of the person.
	 * @return string|null The height or null if not set
	 */
	public function getHeight(): ?string;

	/**
	 * Sets the height of the person.
	 * @param string|null $height The height to set, or null to clear it
	 * @return void
	 */
	public function setHeight(?string $height): void;

	/**
	 * Gets the weight of the person.
	 * @return string|null The weight or null if not set
	 */
	public function getWeight(): ?string;

	/**
	 * Sets the weight of the person.
	 * @param string|null $weight The weight to set, or null to clear it
	 * @return void
	 */
	public function setWeight(?string $weight): void;

	/**
	 * Gets the email address of the person.
	 * @return string|null The email address or null if not set
	 */
	public function getEmail(): ?string;

	/**
	 * Sets the email address of the person.
	 * @param string|null $email The email address to set, or null to clear it
	 * @return void
	 */
	public function setEmail(?string $email): void;

	/**
	 * Gets the fixed-line phone number of the person.
	 * @return string|null The fixed-line number or null if not set
	 */
	public function getFixedLineNumber(): ?string;

	/**
	 * Sets the fixed-line phone number of the person.
	 * @param string|null $fixedLineNumber The fixed-line number to set, or null to clear it
	 * @return void
	 */
	public function setFixedLineNumber(?string $fixedLineNumber): void;

	/**
	 * Gets the mobile phone number of the person.
	 * @return string|null The mobile number or null if not set
	 */
	public function getMobileNumber(): ?string;

	/**
	 * Sets the mobile phone number of the person.
	 * @param string|null $mobileNumber The mobile number to set, or null to clear it
	 * @return void
	 */
	public function setMobileNumber(?string $mobileNumber): void;

	/**
	 * Gets the contact location for the person's residence.
	 * @return PlaceInterface|null The home location or null if not set
	 */
	public function getHomeLocation(): ?PlaceInterface;

	/**
	 * Sets the contact location for the person's residence.
	 * @param PlaceInterface|null $homeLocation The home location to set, or null to clear it
	 * @return void
	 */
	public function setHomeLocation(?PlaceInterface $homeLocation): void;

	/**
	 * Gets the contact location for the person's place of work.
	 * @return PlaceInterface|null The work location or null if not set
	 */
	public function getWorkLocation(): ?PlaceInterface;

	/**
	 * Sets the contact location for the person's place of work.
	 * @param PlaceInterface|null $workLocation The work location to set, or null to clear it
	 * @return void
	 */
	public function setWorkLocation(?PlaceInterface $workLocation): void;

	/**
	 * Gets the organization that the person works for.
	 * @return OrganizationInterface|null The organization or null if not set
	 */
	public function getWorksFor(): ?OrganizationInterface;

	/**
	 * Sets the organization that the person works for.
	 * @param OrganizationInterface|null $worksFor The organization to set, or null to clear it
	 * @return void
	 */
	public function setWorksFor(?OrganizationInterface $worksFor): void;

	/**
	 * Gets the job title of the person.
	 * Examples include: Financial Manager, Software Engineer, Marketing Director, etc.
	 * @return string|null The job title or null if not set
	 */
	public function getJobTitle(): ?string;

	/**
	 * Sets the job title of the person.
	 * @param string|null $jobTitle The job title to set, or null to clear it
	 * @return void
	 */
	public function setJobTitle(?string $jobTitle): void;

	/**
	 * Gets the International Standard of Industrial Classification (ISIC) code, Revision 4.
	 * The ISIC classification is used to categorize economic activities.
	 * @return string|null The ISIC V4 code or null if not set
	 */
	public function getIsicV4(): ?string;

	/**
	 * Sets the International Standard of Industrial Classification (ISIC) code, Revision 4.
	 * @param string|null $isicV4 The ISIC V4 code to set, or null to clear it
	 * @return void
	 */
	public function setIsicV4(?string $isicV4): void;

	/**
	 * Gets the Tax/Fiscal ID of the person.
	 * This could be a TIN (US), CIF/NIF (Spain), or equivalent identifier in other countries.
	 * @return string|null The tax ID or null if not set
	 */
	public function getTaxID(): ?string;

	/**
	 * Sets the Tax/Fiscal ID of the person.
	 * @param string|null $taxID The tax ID to set, or null to clear it
	 * @return void
	 */
	public function setTaxID(?string $taxID): void;

}