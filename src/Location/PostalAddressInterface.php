<?php

namespace Osimatic\Helpers\Location;

interface PostalAddressInterface
{

	/**
	 * @return string|null
	 */
	public function getAttention(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setAttention(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getHouseNumber(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setHouseNumber(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getHouse(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setHouse(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getRoad(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setRoad(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getVillage(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setVillage(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getSuburb(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setSuburb(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getCity(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setCity(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getCounty(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setCounty(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getPostcode(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setPostcode(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getStateDistrict(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setStateDistrict(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getState(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setState(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getRegion(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setRegion(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getIsland(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setIsland(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getCountry(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setCountry(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getCountryCode(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setCountryCode(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getContinent(): ?string;

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setContinent(?string $val): self;

	/**
	 * @return string|null
	 */
	public function getCoordinates(): ?string;

	/**
	 * @param string|null $coordinates
	 * @return self
	 */
	public function setCoordinates(?string $coordinates): self;

	/**
	 * @return string|null
	 */
	public function getFormattedAddress(): ?string;

	/**
	 * @param string|null $formattedAddress
	 * @return self
	 */
	public function setFormattedAddress(?string $formattedAddress): self;

}