<?php

namespace Osimatic\Organization;

use Osimatic\Location\PostalAddressInterface;

/**
 * Interface OrganizationInterface
 * @package Osimatic\Organization
 */
interface OrganizationInterface
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
	 * The name of the organization.
	 * @return string|null
	 */
	public function getName(): ?string;

	/**
	 * @param string|null $name
	 */
	public function setName(?string $name): void;

	/**
	 * The official name of the organization, e.g. the registered company name.
	 * @return string|null
	 */
	public function getLegalName(): ?string;

	/**
	 * @param string|null $legalName
	 */
	public function setLegalName(?string $legalName): void;

	/**
	 * A description of the organization.
	 * @return string|null
	 */
	public function getDescription(): ?string;

	/**
	 * @param string|null $description
	 */
	public function setDescription(?string $description): void;

	/**
	 * An organization identifier that uniquely identifies a legal entity as defined in ISO 17442.
	 * @return string|null
	 */
	public function getLeiCode(): ?string;

	/**
	 * @param string|null $leiCode
	 */
	public function setLeiCode(?string $leiCode): void;

	/**
	 * The International Standard of Industrial Classification of All Economic Activities (ISIC), Revision 4 code.
	 * @return string|null
	 */
	public function getIsicV4(): ?string;

	/**
	 * @param string|null $isicV4
	 */
	public function setIsicV4(?string $isicV4): void;

	/**
	 * A relationship between an organization and a department of that organization, also described as an organization (allowing different urls, logos, opening hours).
	 * @return OrganizationInterface|null
	 */
	public function getDepartment(): ?OrganizationInterface;

	/**
	 * @param OrganizationInterface|null $department
	 */
	public function setDepartment(?OrganizationInterface $department): void;

	/**
	 * Physical address of the organization.
	 * @return PostalAddressInterface|null
	 */
	public function getAddress(): ?PostalAddressInterface;

	/**
	 * @param PostalAddressInterface|null $address
	 */
	public function setAddress(?PostalAddressInterface $address): void;

	/**
	 * Telephone number of the organization.
	 * @return string|null
	 */
	public function getPhoneNumber(): ?string;

	/**
	 * @param string|null $phoneNumber
	 */
	public function setPhoneNumber(?string $phoneNumber): void;

	/**
	 * 	URL of the organization.
	 * @return string|null
	 */
	public function getUrl(): ?string;

	/**
	 * @param string|null $url
	 */
	public function setUrl(?string $url): void;

	/**
	 * An associated logo.
	 * @return string|null
	 */
	public function getLogo(): ?string;

	/**
	 * @param string|null $logo
	 */
	public function setLogo(?string $logo): void;

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
	 * The number of employees in an organization e.g. business.
	 * @return int|null
	 */
	public function getNumberOfEmployees(): ?int;

	/**
	 * @param int|null $numberOfEmployees
	 */
	public function setNumberOfEmployees(?int $numberOfEmployees): void;

	/**
	 * The Value-added Tax ID of the organization or person.
	 * @return string|null
	 */
	public function getVatID(): ?string;

	/**
	 * @param string|null $vatID
	 */
	public function setVatID(?string $vatID): void;

	/**
	 * The legal form of the organization.
	 * @return string|null
	 */
	public function getLegalForm(): ?string;

	/**
	 * @param string|null $legalForm
	 */
	public function setLegalForm(?string $legalForm): void;

	/**
	 * The capital amount of the organization.
	 * @return float|null
	 */
	public function getCapital(): ?float;

	/**
	 * @param float|null $capital
	 */
	public function setCapital(?float $capital): void;

	/**
	 * The registration number of the organization.
	 * @return string|null
	 */
	public function getRegistrationNumber(): ?string;

	/**
	 * @param string|null $registrationNumber
	 */
	public function setRegistrationNumber(?string $registrationNumber): void;

	/**
	 * The city where the organization was registered.
	 * @return string|null
	 */
	public function getRegistrationCity(): ?string;

	/**
	 * @param string|null $registrationCity
	 */
	public function setRegistrationCity(?string $registrationCity): void;

	/**
	 * The country where the organization was registered.
	 * @return string|null
	 */
	public function getRegistrationCountry(): ?string;

	/**
	 * @param string|null $registrationCountry
	 */
	public function setRegistrationCountry(?string $registrationCountry): void;

	/**
	 * The date that this organization was founded.
	 * @return \DateTime|null
	 */
	public function getFoundingDate(): ?\DateTime;

	/**
	 * @param \DateTime|null $foundingDate
	 */
	public function setFoundingDate(?\DateTime $foundingDate): void;

	/**
	 * The date that this organization was dissolved.
	 * @return \DateTime|null
	 */
	public function getDissolutionDate(): ?\DateTime;

	/**
	 * @param \DateTime|null $dissolutionDate
	 */
	public function setDissolutionDate(?\DateTime $dissolutionDate): void;

}