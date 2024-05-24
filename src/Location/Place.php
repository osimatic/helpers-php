<?php

namespace Osimatic\Location;

/**
 * @deprecated use PlaceInterface instead
 */
class Place implements PlaceInterface
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
	private $description;

	/**
	 * @var PostalAddressInterface|null
	 */
	private $address;

	/**
	 * @var string|null
	 */
	private $phoneNumber;

	/**
	 * @var float|null
	 */
	private $latitude;

	/**
	 * @var float|null
	 */
	private $longitude;

	/**
	 * @var string|null
	 */
	private $building;

	/**
	 * @var string|null
	 */
	private $floorLevel;

	/**
	 * @var string|null
	 */
	private $openingHours;

	/**
	 * @var string|null
	 */
	private $logo;

	/**
	 * @var string|null
	 */
	private $photo;

	/**
	 * @var boolean
	 */
	private $isAccessibleForFree = true;

	/**
	 * @var boolean
	 */
	private $publicAccess = true;




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
	 * @return PostalAddressInterface|null
	 */
	public function getAddress(): ?PostalAddressInterface
	{
		return $this->address;
	}

	/**
	 * @param PostalAddressInterface|null $address
	 */
	public function setAddress(?PostalAddressInterface $address): void
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
	 * @return float|null
	 */
	public function getLatitude(): ?float
	{
		return $this->latitude;
	}

	/**
	 * @param float|null $latitude
	 */
	public function setLatitude(?float $latitude): void
	{
		$this->latitude = $latitude;
	}

	/**
	 * @return float|null
	 */
	public function getLongitude(): ?float
	{
		return $this->longitude;
	}

	/**
	 * @param float|null $longitude
	 */
	public function setLongitude(?float $longitude): void
	{
		$this->longitude = $longitude;
	}

	/**
	 * @return string|null
	 */
	public function getBuilding(): ?string
	{
		return $this->building;
	}

	/**
	 * @param string|null $building
	 */
	public function setBuilding(?string $building): void
	{
		$this->building = $building;
	}

	/**
	 * @return string|null
	 */
	public function getFloorLevel(): ?string
	{
		return $this->floorLevel;
	}

	/**
	 * @param string|null $floorLevel
	 */
	public function setFloorLevel(?string $floorLevel): void
	{
		$this->floorLevel = $floorLevel;
	}

	/**
	 * @return string|null
	 */
	public function getOpeningHours(): ?string
	{
		return $this->openingHours;
	}

	/**
	 * @param string|null $openingHours
	 */
	public function setOpeningHours(?string $openingHours): void
	{
		$this->openingHours = $openingHours;
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
	public function getPhoto(): ?string
	{
		return $this->photo;
	}

	/**
	 * @param string|null $photo
	 */
	public function setPhoto(?string $photo): void
	{
		$this->photo = $photo;
	}

	/**
	 * @return bool
	 */
	public function isAccessibleForFree(): bool
	{
		return $this->isAccessibleForFree;
	}

	/**
	 * @param bool $isAccessibleForFree
	 */
	public function setIsAccessibleForFree(bool $isAccessibleForFree): void
	{
		$this->isAccessibleForFree = $isAccessibleForFree;
	}

	/**
	 * @return bool
	 */
	public function isPublicAccess(): bool
	{
		return $this->publicAccess;
	}

	/**
	 * @param bool $publicAccess
	 */
	public function setPublicAccess(bool $publicAccess): void
	{
		$this->publicAccess = $publicAccess;
	}

}