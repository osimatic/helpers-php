<?php

namespace Osimatic\Organization;

use Osimatic\Location\PostalAddressInterface;

/**
 * Interface OrganizationInterface
 * Defines the contract for organization entities with properties following Schema.org standards
 */
interface OrganizationInterface
{

	/**
	 * Gets the identifier property representing any kind of identifier
	 * @return string|null the organization identifier
	 */
	public function getIdentifier(): ?string;

	/**
	 * Sets the organization identifier
	 * @param string|null $identifier the organization identifier
	 */
	public function setIdentifier(?string $identifier): void;

	/**
	 * Gets the name of the organization
	 * @return string|null the organization name
	 */
	public function getName(): ?string;

	/**
	 * Sets the organization name
	 * @param string|null $name the organization name
	 */
	public function setName(?string $name): void;

	/**
	 * Gets the official legal name of the organization (e.g., registered company name)
	 * @return string|null the legal name
	 */
	public function getLegalName(): ?string;

	/**
	 * Sets the legal name of the organization
	 * @param string|null $legalName the legal name
	 */
	public function setLegalName(?string $legalName): void;

	/**
	 * Gets the description of the organization
	 * @return string|null the description
	 */
	public function getDescription(): ?string;

	/**
	 * Sets the description of the organization
	 * @param string|null $description the description
	 */
	public function setDescription(?string $description): void;

	/**
	 * Gets the LEI code (Legal Entity Identifier as defined in ISO 17442)
	 * @return string|null the LEI code
	 */
	public function getLeiCode(): ?string;

	/**
	 * Sets the LEI code
	 * @param string|null $leiCode the LEI code
	 */
	public function setLeiCode(?string $leiCode): void;

	/**
	 * Gets the ISIC v4 code (International Standard Industrial Classification, Revision 4)
	 * @return string|null the ISIC v4 code
	 */
	public function getIsicV4(): ?string;

	/**
	 * Sets the ISIC v4 code
	 * @param string|null $isicV4 the ISIC v4 code
	 */
	public function setIsicV4(?string $isicV4): void;

	/**
	 * Gets a department of this organization (allowing different URLs, logos, opening hours)
	 * @return OrganizationInterface|null the department organization
	 */
	public function getDepartment(): ?OrganizationInterface;

	/**
	 * Sets the department organization
	 * @param OrganizationInterface|null $department the department organization
	 */
	public function setDepartment(?OrganizationInterface $department): void;

	/**
	 * Gets the physical address of the organization
	 * @return PostalAddressInterface|null the postal address
	 */
	public function getAddress(): ?PostalAddressInterface;

	/**
	 * Sets the physical address
	 * @param PostalAddressInterface|null $address the postal address
	 */
	public function setAddress(?PostalAddressInterface $address): void;

	/**
	 * Gets the telephone number of the organization
	 * @return string|null the phone number
	 */
	public function getPhoneNumber(): ?string;

	/**
	 * Sets the telephone number
	 * @param string|null $phoneNumber the phone number
	 */
	public function setPhoneNumber(?string $phoneNumber): void;

	/**
	 * Gets the URL of the organization
	 * @return string|null the URL
	 */
	public function getUrl(): ?string;

	/**
	 * Sets the URL
	 * @param string|null $url the URL
	 */
	public function setUrl(?string $url): void;

	/**
	 * Gets the associated logo URL or path
	 * @return string|null the logo URL or path
	 */
	public function getLogo(): ?string;

	/**
	 * Sets the logo URL or path
	 * @param string|null $logo the logo URL or path
	 */
	public function setLogo(?string $logo): void;

	/**
	 * Gets the email address of the organization
	 * @return string|null the email address
	 */
	public function getEmail(): ?string;

	/**
	 * Sets the email address
	 * @param string|null $email the email address
	 */
	public function setEmail(?string $email): void;

	/**
	 * Gets the number of employees in the organization
	 * @return int|null the number of employees
	 */
	public function getNumberOfEmployees(): ?int;

	/**
	 * Sets the number of employees
	 * @param int|null $numberOfEmployees the number of employees
	 */
	public function setNumberOfEmployees(?int $numberOfEmployees): void;

	/**
	 * Gets the Value-Added Tax ID (VAT ID) of the organization
	 * @return string|null the VAT ID
	 */
	public function getVatID(): ?string;

	/**
	 * Sets the VAT ID
	 * @param string|null $vatID the VAT ID
	 */
	public function setVatID(?string $vatID): void;

	/**
	 * Gets the legal form of the organization (e.g., LLC, Corporation, etc.)
	 * @return string|null the legal form
	 */
	public function getLegalForm(): ?string;

	/**
	 * Sets the legal form
	 * @param string|null $legalForm the legal form
	 */
	public function setLegalForm(?string $legalForm): void;

	/**
	 * Gets the capital amount of the organization
	 * @return float|null the capital amount
	 */
	public function getCapital(): ?float;

	/**
	 * Sets the capital amount
	 * @param float|null $capital the capital amount
	 */
	public function setCapital(?float $capital): void;

	/**
	 * Gets the registration number of the organization
	 * @return string|null the registration number
	 */
	public function getRegistrationNumber(): ?string;

	/**
	 * Sets the registration number
	 * @param string|null $registrationNumber the registration number
	 */
	public function setRegistrationNumber(?string $registrationNumber): void;

	/**
	 * Gets the city where the organization was registered
	 * @return string|null the registration city
	 */
	public function getRegistrationCity(): ?string;

	/**
	 * Sets the registration city
	 * @param string|null $registrationCity the registration city
	 */
	public function setRegistrationCity(?string $registrationCity): void;

	/**
	 * Gets the country where the organization was registered
	 * @return string|null the registration country
	 */
	public function getRegistrationCountry(): ?string;

	/**
	 * Sets the registration country
	 * @param string|null $registrationCountry the registration country
	 */
	public function setRegistrationCountry(?string $registrationCountry): void;

	/**
	 * Gets the date this organization was founded
	 * @return \DateTime|null the founding date
	 */
	public function getFoundingDate(): ?\DateTime;

	/**
	 * Sets the founding date
	 * @param \DateTime|null $foundingDate the founding date
	 */
	public function setFoundingDate(?\DateTime $foundingDate): void;

	/**
	 * Gets the date this organization was dissolved
	 * @return \DateTime|null the dissolution date
	 */
	public function getDissolutionDate(): ?\DateTime;

	/**
	 * Sets the dissolution date
	 * @param \DateTime|null $dissolutionDate the dissolution date
	 */
	public function setDissolutionDate(?\DateTime $dissolutionDate): void;

}