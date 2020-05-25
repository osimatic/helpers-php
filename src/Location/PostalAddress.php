<?php

namespace Osimatic\Helpers\Location;

/**
 * Class PostalAddress
 * @package Osimatic\Helpers\Location
 */
class PostalAddress
{

	/**
	 * @var string|null
	 */
	private $attention;

	/**
	 * @var string|null
	 */
	private $houseNumber;

	/**
	 * @var string|null
	 */
	private $house;

	/**
	 * @var string|null
	 */
	private $road;

	/**
	 * @var string|null
	 */
	private $village;

	/**
	 * @var string|null
	 */
	private $suburb;

	/**
	 * @var string|null
	 */
	private $city;

	/**
	 * @var string|null
	 */
	private $county;

	/**
	 * @var string|null
	 */
	private $postcode;

	/**
	 * @var string|null
	 */
	private $stateDistrict;

	/**
	 * @var string|null
	 */
	private $state;

	/**
	 * @var string|null
	 */
	private $region;

	/**
	 * @var string|null
	 */
	private $island;

	/**
	 * @var string|null
	 */
	private $country;

	/**
	 * @var string|null
	 */
	private $countryCode;

	/**
	 * @var string|null
	 */
	private $continent;


	// ========== Vérification ==========

	/**
	 * @param string $value
	 * @return bool
	 */
	public static function checkStreet(?string $value): bool
	{
		return preg_match('/(([0-9]+ )?[a-zA-Z ]){1,200}$/', $value);
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	public static function checkZipCode(?string $value): bool
	{
		return preg_match('/^[\s0-9a-zA-Z]{3,15}$/', $value);
	}

	/**
	 * @param string $value
	 * @return bool
	 */
	public static function checkCity(?string $value): bool
	{
		// /^([a-zA-Z'àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\s-]){2-100}$/
		return preg_match('/^[a-zA-Z\'àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\s-]+$/u', $value);
	}


	// ========== Affichage ==========

	/**
	 * @param string|null $separator
	 * @return string
	 */
	public function format(?string $separator=null): ?string
	{
		return (new PostalAddressFormatter())->format($this, [], $separator);
	}

	public function __toString()
	{
		return $this->format() ?? '';
	}



	// ========== Get / Set ==========

	public function getAttention(): ?string
	{
		return $this->attention;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setAttention(string $val): self
	{
		$this->attention = $val;

		return $this;
	}

	public function getHouseNumber(): ?string
	{
		return $this->houseNumber;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setHouseNumber($val): self
	{
		$this->houseNumber = $val;

		return $this;
	}

	public function getHouse(): ?string
	{
		return $this->house;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setHouse($val): self
	{
		$this->house = $val;

		return $this;
	}

	public function getRoad(): ?string
	{
		return $this->road;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setRoad($val): self
	{
		$this->road = $val;

		return $this;
	}

	public function getVillage(): ?string
	{
		return $this->village;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setVillage($val): self
	{
		$this->village = $val;

		return $this;
	}

	public function getSuburb(): ?string
	{
		return $this->suburb;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setSuburb($val): self
	{
		$this->suburb = $val;

		return $this;
	}

	public function getCity(): ?string
	{
		return $this->city;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setCity($val): self
	{
		$this->city = $val;

		return $this;
	}

	public function getCounty(): ?string
	{
		return $this->county;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setCounty($val): self
	{
		$this->county = $val;

		return $this;
	}

	public function getPostcode(): ?string
	{
		return $this->postcode;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setPostcode($val): self
	{
		$this->postcode = $val;

		return $this;
	}

	public function getStateDistrict(): ?string
	{
		return $this->stateDistrict;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setStateDistrict($val): self
	{
		$this->stateDistrict = $val;

		return $this;
	}

	public function getState(): ?string
	{
		return $this->state;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setState($val): self
	{
		$this->state = $val;

		return $this;
	}

	public function getRegion(): ?string
	{
		return $this->region;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setRegion($val): self
	{
		$this->region = $val;

		return $this;
	}

	public function getIsland(): ?string
	{
		return $this->island;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setIsland($val): self
	{
		$this->island = $val;

		return $this;
	}

	public function getCountry(): ?string
	{
		return $this->country;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setCountry($val): self
	{
		$this->country = $val;

		return $this;
	}

	public function getCountryCode(): ?string
	{
		return $this->countryCode;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setCountryCode($val): self
	{
		$this->countryCode = $val;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getContinent(): ?string
	{
		return $this->continent;
	}

	/**
	 * @param string $val
	 * @return self
	 */
	public function setContinent($val): self
	{
		$this->continent = $val;

		return $this;
	}

}