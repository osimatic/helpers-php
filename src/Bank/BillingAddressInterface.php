<?php

namespace Osimatic\Helpers\Bank;

interface BillingAddressInterface 
{
   /**
     * @return string|null
     */
    public function getFirstName(): ?string;

    /**
     * @param string|null $firstName 
     */
    public function setFirstName(?string $firstName): void;

   /**
    * @return string|null
    */
   public function getLastName(): ?string;

   /**
    * @param string|null $lastName 
    */
   public function setLastName(?string $lastName): void;

  /**
    * @return string|null
    */
   public function getCompanyName(): ?string;

   /**
    * @param string|null $companyname 
    */
   public function setCompanyName(?string $companyName): void;

	/**
	 * @return string
	 */
	public function getStreet(): string;

	/**
	 * @param string $street 
	 */
	public function setStreet(string $street): void;

	/**
	 * @return string|null
	 */
	public function getStreet2(): ?string;

	/**
	 * @param string|null $street2 
	 */
	public function setStreet2(?string $street2): void;

	/**
	 * @return string|null
	 */
	public function getZipCode(): ?string;

	/**
	 * @param string|null $zipCode 
	 */
	public function setZipCode(?string $zipCode): void;
	
	/**
	 * @return string
	 */
	public function getCity(): string;

	/**
	 * @param string $city 
	 */
	public function setCity(string $city): void;

	/**
	 * @return string
	 */
	public function getCountryCode(): string;

	/**
	 * @param string $countryCode 
	 */
	public function setCountryCode(string $countryCode): void;
}