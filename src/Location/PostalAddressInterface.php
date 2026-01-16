<?php

namespace Osimatic\Location;

/**
 * Interface for postal address objects.
 * Defines getters and setters for all address components including street details, locality, region, and coordinates.
 */
interface PostalAddressInterface
{

	/**
	 * Get the attention line (recipient name or care of).
	 * @return string|null The attention line
	 */
	public function getAttention(): ?string;

	/**
	 * Set the attention line (recipient name or care of).
	 * @param string|null $val The attention line
	 * @return self|void
	 */
	public function setAttention(?string $val);

	/**
	 * Get the house number (street number).
	 * @return string|null The house number
	 */
	public function getHouseNumber(): ?string;

	/**
	 * Set the house number (street number).
	 * @param string|null $val The house number
	 * @return self|void
	 */
	public function setHouseNumber(?string $val);

	/**
	 * Get the house name or building name.
	 * @return string|null The house name
	 */
	public function getHouse(): ?string;

	/**
	 * Set the house name or building name.
	 * @param string|null $val The house name
	 * @return self|void
	 */
	public function setHouse(?string $val);

	/**
	 * Get the street address (road name and number).
	 * @return string|null The street address
	 */
	public function getRoad(): ?string;

	/**
	 * Set the street address (road name and number).
	 * @param string|null $val The street address
	 * @return self|void
	 */
	public function setRoad(?string $val);

	/**
	 * Get the village name.
	 * @return string|null The village name
	 */
	public function getVillage(): ?string;

	/**
	 * Set the village name.
	 * @param string|null $val The village name
	 * @return self|void
	 */
	public function setVillage(?string $val);

	/**
	 * Get the suburb or district name.
	 * @return string|null The suburb name
	 */
	public function getSuburb(): ?string;

	/**
	 * Set the suburb or district name.
	 * @param string|null $val The suburb name
	 * @return self|void
	 */
	public function setSuburb(?string $val);

	/**
	 * Get the city or town name.
	 * @return string|null The city name
	 */
	public function getCity(): ?string;

	/**
	 * Set the city or town name.
	 * @param string|null $val The city name
	 * @return self|void
	 */
	public function setCity(?string $val);

	/**
	 * Get the county name.
	 * @return string|null The county name
	 */
	public function getCounty(): ?string;

	/**
	 * Set the county name.
	 * @param string|null $val The county name
	 * @return self|void
	 */
	public function setCounty(?string $val);

	/**
	 * Get the postal code or ZIP code.
	 * @return string|null The postal code
	 */
	public function getPostcode(): ?string;

	/**
	 * Set the postal code or ZIP code.
	 * @param string|null $val The postal code
	 * @return self|void
	 */
	public function setPostcode(?string $val);

	/**
	 * Get the state district.
	 * @return string|null The state district
	 */
	public function getStateDistrict(): ?string;

	/**
	 * Set the state district.
	 * @param string|null $val The state district
	 * @return self|void
	 */
	public function setStateDistrict(?string $val);

	/**
	 * Get the state or province name.
	 * @return string|null The state name
	 */
	public function getState(): ?string;

	/**
	 * Set the state or province name.
	 * @param string|null $val The state name
	 * @return self|void
	 */
	public function setState(?string $val);

	/**
	 * Get the region name.
	 * @return string|null The region name
	 */
	public function getRegion(): ?string;

	/**
	 * Set the region name.
	 * @param string|null $val The region name
	 * @return self|void
	 */
	public function setRegion(?string $val);

	/**
	 * Get the island name.
	 * @return string|null The island name
	 */
	public function getIsland(): ?string;

	/**
	 * Set the island name.
	 * @param string|null $val The island name
	 * @return self|void
	 */
	public function setIsland(?string $val);

	/**
	 * Get the country name.
	 * @return string|null The country name
	 */
	public function getCountry(): ?string;

	/**
	 * Set the country name.
	 * @param string|null $val The country name
	 * @return self|void
	 */
	public function setCountry(?string $val);

	/**
	 * Get the ISO 3166-1 alpha-2 country code.
	 * @return string|null The country code (e.g., 'US', 'FR', 'GB')
	 */
	public function getCountryCode(): ?string;

	/**
	 * Set the ISO 3166-1 alpha-2 country code.
	 * @param string|null $val The country code (e.g., 'US', 'FR', 'GB')
	 * @return self|void
	 */
	public function setCountryCode(?string $val);

	/**
	 * Get the continent name.
	 * @return string|null The continent name
	 */
	public function getContinent(): ?string;

	/**
	 * Set the continent name.
	 * @param string|null $val The continent name
	 * @return self|void
	 */
	public function setContinent(?string $val);

	/**
	 * Get the geographic coordinates in "latitude,longitude" format.
	 * @return string|null The coordinates (e.g., "48.8566,2.3522")
	 */
	public function getCoordinates(): ?string;

	/**
	 * Set the geographic coordinates in "latitude,longitude" format.
	 * @param string|null $coordinates The coordinates (e.g., "48.8566,2.3522")
	 * @return self|void
	 */
	public function setCoordinates(?string $coordinates);

	/**
	 * Get the full formatted address as a single string.
	 * @return string|null The formatted address
	 */
	public function getFormattedAddress(): ?string;

	/**
	 * Set the full formatted address as a single string.
	 * @param string|null $formattedAddress The formatted address
	 * @return self|void
	 */
	public function setFormattedAddress(?string $formattedAddress);

}