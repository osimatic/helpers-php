<?php

namespace Osimatic\Helpers\Location;

class Address
{
	private $attention = '';
	private $houseNumber = '';
	private $house = '';
	private $road = '';
	private $village = '';
	private $suburb = '';
	private $city = '';
	private $county = '';
	private $postcode = '';
	private $stateDistrict = '';
	private $state = '';
	private $region = '';
	private $island = '';
	private $country = '';
	private $countryCode = '';
	private $continent = '';

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

	public function setAttention($val)
	{
		$this->attention = $val;

		return $this;
	}

	public function setHouseNumber($val)
	{
		$this->houseNumber = $val;

		return $this;
	}

	public function setHouse($val)
	{
		$this->house = $val;

		return $this;
	}

	public function setRoad($val)
	{
		$this->road = $val;

		return $this;
	}

	public function setVillage($val)
	{
		$this->village = $val;

		return $this;
	}

	public function setSuburb($val)
	{
		$this->suburb = $val;

		return $this;
	}

	public function setCity($val)
	{
		$this->city = $val;

		return $this;
	}

	public function setCounty($val)
	{
		$this->county = $val;

		return $this;
	}

	public function setPostcode($val)
	{
		$this->postcode = $val;

		return $this;
	}

	public function setStateDistrict($val)
	{
		$this->stateDistrict = $val;

		return $this;
	}

	public function setState($val)
	{
		$this->state = $val;

		return $this;
	}

	public function setRegion($val)
	{
		$this->region = $val;

		return $this;
	}

	public function setIsland($val)
	{
		$this->island = $val;

		return $this;
	}

	public function setCountry($val)
	{
		$this->country = $val;

		return $this;
	}

	public function setCountryCode($val)
	{
		$this->countryCode = $val;

		return $this;
	}

	public function setContinent($val)
	{
		$this->continent = $val;

		return $this;
	}

	public function getAttention()
	{
		return $this->attention;
	}

	public function getHouseNumber()
	{
		return $this->houseNumber;
	}

	public function getHouse()
	{
		return $this->house;
	}

	public function getRoad()
	{
		return $this->road;
	}

	public function getVillage()
	{
		return $this->village;
	}

	public function getSuburb()
	{
		return $this->suburb;
	}

	public function getCity()
	{
		return $this->city;
	}

	public function getCounty()
	{
		return $this->county;
	}

	public function getPostcode()
	{
		return $this->postcode;
	}

	public function getStateDistrict()
	{
		return $this->stateDistrict;
	}

	public function getState()
	{
		return $this->state;
	}

	public function getRegion()
	{
		return $this->region;
	}

	public function getIsland()
	{
		return $this->island;
	}

	public function getCountry()
	{
		return $this->country;
	}

	public function getCountryCode()
	{
		return $this->countryCode;
	}

	public function getContinent()
	{
		return $this->continent;
	}

}