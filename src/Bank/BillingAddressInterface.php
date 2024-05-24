<?php

namespace Osimatic\Bank;

interface BillingAddressInterface
{
	/**
	 * @return string|null
	 */
	public function getFirstName(): ?string;

	/**
	 * @param string|null $firstName
	 * @return self|void
	 */
	public function setFirstName(?string $firstName);

	/**
	 * @return string|null
	 */
	public function getLastName(): ?string;

	/**
	 * @param string|null $lastName
	 * @return self|void
	 */
	public function setLastName(?string $lastName);

	/**
	 * @return string|null
	 */
	public function getCompanyName(): ?string;

	/**
	 * @param string|null $companyName
	 * @return self|void
	 */
	public function setCompanyName(?string $companyName);

	/**
	 * @return string
	 */
	public function getStreet(): string;

	/**
	 * @param string $street
	 * @return self|void
	 */
	public function setStreet(string $street);

	/**
	 * @return string|null
	 */
	public function getStreet2(): ?string;

	/**
	 * @param string|null $street2
	 * @return self|void
	 */
	public function setStreet2(?string $street2);

	/**
	 * @return string|null
	 */
	public function getZipCode(): ?string;

	/**
	 * @param string|null $zipCode
	 * @return self|void
	 */
	public function setZipCode(?string $zipCode);

	/**
	 * @return string
	 */
	public function getCity(): string;

	/**
	 * @param string $city
	 * @return self|void
	 */
	public function setCity(string $city);

	/**
	 * @return string
	 */
	public function getCountryCode(): string;

	/**
	 * @param string $countryCode
	 * @return self|void
	 */
	public function setCountryCode(string $countryCode);
}