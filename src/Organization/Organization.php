<?php

namespace Osimatic\Helpers\Organization;

use Osimatic\Helpers\Location\PostalAddress;

/**
 * Class Organization
 * @package Osimatic\Helpers\Organization
 */
class Organization
{
	/**
	 * The identifier property represents any kind of identifier.
	 * @var string|null
	 */
	private $identifier;

	/**
	 * The name of the organization.
	 * @var string|null
	 */
	private $name;

	/**
	 * The official name of the organization, e.g. the registered company name.
	 * @var string|null
	 */
	private $legalName;

	/**
	 * A description of the organization.
	 * @var string|null
	 */
	private $description;

	/**
	 * An organization identifier that uniquely identifies a legal entity as defined in ISO 17442.
	 * @var string|null
	 */
	private $leiCode;

	/**
	 * The International Standard of Industrial Classification of All Economic Activities (ISIC), Revision 4 code.
	 * @var string|null
	 */
	private $isicV4;

	/**
	 * A relationship between an organization and a department of that organization, also described as an organization (allowing different urls, logos, opening hours).
	 * @var Organization|null
	 */
	private $department;

	/**
	 * Physical address of the organization.
	 * @var PostalAddress|null
	 */
	private $address;

	/**
	 * Telephone number of the organization.
	 * @var string|null
	 */
	private $phoneNumber;

	/**
	 * 	URL of the organization.
	 * @var string|null
	 */
	private $url;

	/**
	 * An associated logo.
	 * @var string|null
	 */
	private $logo;

	/**
	 * Email address.
	 * @var string|null
	 */
	private $email;

	/**
	 * The number of employees in an organization e.g. business.
	 * @var int|null
	 */
	private $numberOfEmployees;

	/**
	 * The Value-added Tax ID of the organization or person.
	 * @var string|null
	 */
	private $vatID;

	/**
	 * The date that this organization was founded.
	 * @var float|null
	 */
	private $capital;

	/**
	 * The date that this organization was founded.
	 * @var \DateTime|null
	 */
	private $foundingDate;

	/**
	 * The date that this organization was dissolved.
	 * @var \DateTime|null
	 */
	private $dissolutionDate;








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
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @param string|null $name
	 */
	public function setName(?string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @return string|null
	 */
	public function getLegalName(): ?string
	{
		return $this->legalName;
	}

	/**
	 * @param string|null $legalName
	 */
	public function setLegalName(?string $legalName): void
	{
		$this->legalName = $legalName;
	}

	/**
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @param string|null $description
	 */
	public function setDescription(?string $description): void
	{
		$this->description = $description;
	}

	/**
	 * @return string|null
	 */
	public function getLeiCode(): ?string
	{
		return $this->leiCode;
	}

	/**
	 * @param string|null $leiCode
	 */
	public function setLeiCode(?string $leiCode): void
	{
		$this->leiCode = $leiCode;
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
	 * @return Organization|null
	 */
	public function getDepartment(): ?Organization
	{
		return $this->department;
	}

	/**
	 * @param Organization|null $department
	 */
	public function setDepartment(?Organization $department): void
	{
		$this->department = $department;
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
	 * @return string|null
	 */
	public function getPhoneNumber(): ?string
	{
		return $this->phoneNumber;
	}

	/**
	 * @param string|null $phoneNumber
	 */
	public function setPhoneNumber(?string $phoneNumber): void
	{
		$this->phoneNumber = $phoneNumber;
	}

	/**
	 * @return string|null
	 */
	public function getUrl(): ?string
	{
		return $this->url;
	}

	/**
	 * @param string|null $url
	 */
	public function setUrl(?string $url): void
	{
		$this->url = $url;
	}

	/**
	 * @return string|null
	 */
	public function getLogo(): ?string
	{
		return $this->logo;
	}

	/**
	 * @param string|null $logo
	 */
	public function setLogo(?string $logo): void
	{
		$this->logo = $logo;
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
	 * @return int|null
	 */
	public function getNumberOfEmployees(): ?int
	{
		return $this->numberOfEmployees;
	}

	/**
	 * @param int|null $numberOfEmployees
	 */
	public function setNumberOfEmployees(?int $numberOfEmployees): void
	{
		$this->numberOfEmployees = $numberOfEmployees;
	}

	/**
	 * @return string|null
	 */
	public function getVatID(): ?string
	{
		return $this->vatID;
	}

	/**
	 * @param string|null $vatID
	 */
	public function setVatID(?string $vatID): void
	{
		$this->vatID = $vatID;
	}

	/**
	 * @return float|null
	 */
	public function getCapital(): ?float
	{
		return $this->capital;
	}

	/**
	 * @param float|null $capital
	 */
	public function setCapital(?float $capital): void
	{
		$this->capital = $capital;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getFoundingDate(): ?\DateTime
	{
		return $this->foundingDate;
	}

	/**
	 * @param \DateTime|null $foundingDate
	 */
	public function setFoundingDate(?\DateTime $foundingDate): void
	{
		$this->foundingDate = $foundingDate;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getDissolutionDate(): ?\DateTime
	{
		return $this->dissolutionDate;
	}

	/**
	 * @param \DateTime|null $dissolutionDate
	 */
	public function setDissolutionDate(?\DateTime $dissolutionDate): void
	{
		$this->dissolutionDate = $dissolutionDate;
	}

}