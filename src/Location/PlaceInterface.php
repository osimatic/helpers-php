<?php

namespace Osimatic\Location;

/**
 * Interface PlaceInterface
 * @package Osimatic\Location
 */
interface PlaceInterface
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
	 * The name of the place.
	 * @return string|null
	 */
	public function getName(): ?string;

	/**
	 * @param string|null $name
	 */
	public function setName(?string $name): void;

	/**
	 * A description of the place.
	 * @return string|null
	 */
	public function getDescription(): ?string;

	/**
	 * @param string|null $description
	 */
	public function setDescription(?string $description): void;

	/**
	 * Physical address of the place.
	 * @return PostalAddressInterface|null
	 */
	public function getAddress(): ?PostalAddressInterface;

	/**
	 * @param PostalAddressInterface|null $address
	 */
	public function setAddress(?PostalAddressInterface $address): void;

	/**
	 * The telephone number.
	 * @return string|null
	 */
	public function getPhoneNumber(): ?string;

	/**
	 * @param string|null $phoneNumber
	 */
	public function setPhoneNumber(?string $phoneNumber): void;

	/**
	 * The latitude of a location.
	 * @return float|null
	 */
	public function getLatitude(): ?float;

	/**
	 * @param float|null $latitude
	 */
	public function setLatitude(?float $latitude): void;

	/**
	 * The longitude of a location.
	 * @return float|null
	 */
	public function getLongitude(): ?float;

	/**
	 * @param float|null $longitude
	 */
	public function setLongitude(?float $longitude): void;

	/**
	 * The building an Accommodation if the address contains many buildings.
	 * @return string|null
	 */
	public function getBuilding(): ?string;

	/**
	 * @param string|null $building
	 */
	public function setBuilding(?string $building): void;

	/**
	 * The floor level for an Accommodation in a multi-storey building. Since counting systems vary internationally, the local system should be used where possible.
	 * @return string|null
	 */
	public function getFloorLevel(): ?string;

	/**
	 * @param string|null $floorLevel
	 */
	public function setFloorLevel(?string $floorLevel): void;

	/**
	 * The general opening hours for a business. Opening hours can be specified as a weekly time range, starting with days, then times per day. Multiple days can be listed with commas ',' separating each day. Day or time ranges are specified using a hyphen '-'.
	 * @return string|null
	 */
	public function getOpeningHours(): ?string;

	/**
	 * @param string|null $openingHours
	 */
	public function setOpeningHours(?string $openingHours): void;

	/**
	 * An associated logo URL.
	 * @return string|null
	 */
	public function getLogo(): ?string;

	/**
	 * @param string|null $logo
	 */
	public function setLogo(?string $logo): void;

	/**
	 * A photograph URL of this place.
	 * @return string|null
	 */
	public function getPhoto(): ?string;

	/**
	 * @param string|null $photo
	 */
	public function setPhoto(?string $photo): void;

	/**
	 * A flag to signal that the item, event, or place is accessible for free.
	 * @return bool
	 */
	public function isAccessibleForFree(): bool;

	/**
	 * @param bool $isAccessibleForFree
	 */
	public function setIsAccessibleForFree(bool $isAccessibleForFree): void;

	/**
	 * A flag to signal that the Place is open to public visitors.
	 * @return bool
	 */
	public function isPublicAccess(): bool;

	/**
	 * @param bool $publicAccess
	 */
	public function setPublicAccess(bool $publicAccess): void;
}