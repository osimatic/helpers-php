<?php

namespace Osimatic\Helpers\Organization;

use Osimatic\Helpers\Location\PostalAddress;

/**
 * @deprecated use OrganizationInterface instead
 */
class Organization implements OrganizationInterface
{
	/**
	 * @var string|null
	 */
	private $identifier;

	/**
	 * @var string|null
	 */
	private $name;

	/**
	 * @var string|null
	 */
	private $legalName;

	/**
	 * @var string|null
	 */
	private $description;

	/**
	 * @var string|null
	 */
	private $leiCode;

	/**
	 * @var string|null
	 */
	private $isicV4;

	/**
	 * @var Organization|null
	 */
	private $department;

	/**
	 * @var PostalAddress|null
	 */
	private $address;

	/**
	 * @var string|null
	 */
	private $phoneNumber;

	/**
	 * @var string|null
	 */
	private $url;

	/**
	 * @var string|null
	 */
	private $logo;

	/**
	 * @var string|null
	 */
	private $email;

	/**
	 * @var int|null
	 */
	private $numberOfEmployees;

	/**
	 * @var string|null
	 */
	private $vatID;

	/**
	 * @var string|null
	 */
	private $legalForm;

	/**
	 * @var float|null
	 */
	private $capital;

	/**
	 * @var string|null
	 */
	private $registrationNumber;

	/**
	 * @var string|null
	 */
	private $registrationCity;

	/**
	 * @var string|null
	 */
	private $registrationCountry;

	/**
	 * @var \DateTime|null
	 */
	private $foundingDate;

	/**
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
	 * @return string|null
	 */
	public function getLegalForm(): ?string
	{
		return $this->legalForm;
	}

	/**
	 * @param string|null $legalForm
	 */
	public function setLegalForm(?string $legalForm): void
	{
		$this->legalForm = $legalForm;
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
	 * @return string|null
	 */
	public function getRegistrationNumber(): ?string
	{
		return $this->registrationNumber;
	}

	/**
	 * @param string|null $registrationNumber
	 */
	public function setRegistrationNumber(?string $registrationNumber): void
	{
		$this->registrationNumber = $registrationNumber;
	}

	/**
	 * @return string|null
	 */
	public function getRegistrationCity(): ?string
	{
		return $this->registrationCity;
	}

	/**
	 * @param string|null $registrationCity
	 */
	public function setRegistrationCity(?string $registrationCity): void
	{
		$this->registrationCity = $registrationCity;
	}

	/**
	 * @return string|null
	 */
	public function getRegistrationCountry(): ?string
	{
		return $this->registrationCountry;
	}

	/**
	 * @param string|null $registrationCountry
	 */
	public function setRegistrationCountry(?string $registrationCountry): void
	{
		$this->registrationCountry = $registrationCountry;
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