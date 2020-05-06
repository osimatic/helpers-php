<?php

namespace Osimatic\Helpers\Location;

/**
 * Class Place
 * @package Osimatic\Helpers\Location
 */
class Place
{
	/**
	 * The identifier property represents any kind of identifier.
	 * @var string|null
	 */
	private $identifier;

	/**
	 * The name of the place.
	 * @var string|null
	 */
	private $name;

	/**
	 * A description of the place.
	 * @var string|null
	 */
	private $description;

	/**
	 * Physical address of the place.
	 * @var PostalAddress|null
	 */
	private $address;

	/**
	 * The telephone number.
	 * @var string|null
	 */
	private $phoneNumber;

	/**
	 * The latitude of a location.
	 * @var float|null
	 */
	private $latitude;

	/**
	 * The longitude of a location.
	 * @var float|null
	 */
	private $longitude;

	/**
	 * The building an Accommodation if the address contains many buildings.
	 * @var string|null
	 */
	private $building;

	/**
	 * The floor level for an Accommodation in a multi-storey building. Since counting systems vary internationally, the local system should be used where possible.
	 * @var string|null
	 */
	private $floorLevel;

	/**
	 * The general opening hours for a business. Opening hours can be specified as a weekly time range, starting with days, then times per day. Multiple days can be listed with commas ',' separating each day. Day or time ranges are specified using a hyphen '-'.
	 * @var string|null
	 */
	private $openingHours;

	/**
	 * An associated logo URL.
	 * @var string|null
	 */
	private $logo;

	/**
	 * A photograph URL of this place.
	 * @var string|null
	 */
	private $photo;

	/**
	 * A flag to signal that the item, event, or place is accessible for free.
	 * @var boolean
	 */
	private $isAccessibleForFree = true;

	/**
	 * A flag to signal that the Place is open to public visitors.
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