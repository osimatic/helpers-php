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
	 * @return self|void
	 */
	public function setAttention(?string $val);

	/**
	 * @return string|null
	 */
	public function getHouseNumber(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setHouseNumber(?string $val);

	/**
	 * @return string|null
	 */
	public function getHouse(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setHouse(?string $val);

	/**
	 * @return string|null
	 */
	public function getRoad(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setRoad(?string $val);

	/**
	 * @return string|null
	 */
	public function getVillage(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setVillage(?string $val);

	/**
	 * @return string|null
	 */
	public function getSuburb(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setSuburb(?string $val);

	/**
	 * @return string|null
	 */
	public function getCity(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setCity(?string $val);

	/**
	 * @return string|null
	 */
	public function getCounty(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setCounty(?string $val);

	/**
	 * @return string|null
	 */
	public function getPostcode(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setPostcode(?string $val);

	/**
	 * @return string|null
	 */
	public function getStateDistrict(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setStateDistrict(?string $val);

	/**
	 * @return string|null
	 */
	public function getState(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setState(?string $val);

	/**
	 * @return string|null
	 */
	public function getRegion(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setRegion(?string $val);

	/**
	 * @return string|null
	 */
	public function getIsland(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setIsland(?string $val);

	/**
	 * @return string|null
	 */
	public function getCountry(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setCountry(?string $val);

	/**
	 * @return string|null
	 */
	public function getCountryCode(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setCountryCode(?string $val);

	/**
	 * @return string|null
	 */
	public function getContinent(): ?string;

	/**
	 * @param string|null $val
	 * @return self|void
	 */
	public function setContinent(?string $val);

	/**
	 * @return string|null
	 */
	public function getCoordinates(): ?string;

	/**
	 * @param string|null $coordinates
	 * @return self|void
	 */
	public function setCoordinates(?string $coordinates);

	/**
	 * @return string|null
	 */
	public function getFormattedAddress(): ?string;

	/**
	 * @param string|null $formattedAddress
	 * @return self|void
	 */
	public function setFormattedAddress(?string $formattedAddress);

}