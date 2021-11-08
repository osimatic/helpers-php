<?php

namespace Osimatic\Helpers\Location;

use Symfony\Component\Yaml\Yaml;

/**
 * Class PostalAddress
 * @package Osimatic\Helpers\Location
 */
class PostalAddress
{
	// ========== Vérification ==========

	/**
	 * @param string|null $value
	 * @return bool
	 */
	public static function checkStreet(?string $value): bool
	{
		//return preg_match('/(([0-9]+ )?[a-zA-Z ]){1,200}$/', $value);
		return preg_match('/^(.){1,200}$/u', $value);
	}

	/**
	 * @param string|null $value
	 * @param string|null $country
	 * @return bool
	 */
	public static function checkPostalCode(?string $value, ?string $country=null): bool
	{
		// Si le pays est fourni, on vérifie le code postal spécifique à ce pays
		if (null !== $country) {
			$regEx = Yaml::parse(file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'conf'.DIRECTORY_SEPARATOR.'postal_codes.yaml'));
			if (!empty($regEx[$country])) {
				return preg_match('/^'.$regEx[$country].'$/u', $value);
			}
		}

		return preg_match('/^([\-\.\s\w]){3,15}$/u', $value);
	}

	/**
	 * @param string|null $value
	 * @return bool
	 */
	public static function checkZipCode(?string $value): bool
	{
		return self::checkPostalCode($value);
	}

	/**
	 * @param string|null $value
	 * @return bool
	 */
	public static function checkCity(?string $value): bool
	{
		// /^([a-zA-Z'àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\s-]){2-100}$/
		//return preg_match('/^[a-zA-Z\'àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\s-]+$/u', $value);
		return preg_match('/^(.){1,100}$/u', $value);
	}


	// ========== Affichage ==========

	/**
	 * @param PostalAddressInterface $postalAddress
	 * @param string|null $separator
	 * @return string
	 */
	public static function formatFromTwig(PostalAddressInterface $postalAddress, ?string $separator='<br/>'): ?string
	{
		return (new PostalAddressFormatter())->format($postalAddress, [], $separator);
	}

	/**
	 * @param PostalAddressInterface $postalAddress
	 * @param string|null $separator
	 * @return string
	 */
	public static function formatInlineFromTwig(PostalAddressInterface $postalAddress, ?string $separator=', '): ?string
	{
		return (new PostalAddressFormatter())->format($postalAddress, [], $separator);
	}

	/**
	 * @param PostalAddressInterface $postalAddress
	 * @param string|null $separator
	 * @return string
	 */
	public static function format(PostalAddressInterface $postalAddress, ?string $separator=null): ?string
	{
		return (new PostalAddressFormatter())->format($postalAddress, [], $separator);
	}






	// ========== DEPRECATED ==========

	/**
	 * @var string|null
	 */
	private ?string $attention;

	/**
	 * @var string|null
	 */
	private ?string $houseNumber;

	/**
	 * @var string|null
	 */
	private ?string $house;

	/**
	 * @var string|null
	 */
	private ?string $road;

	/**
	 * @var string|null
	 */
	private ?string $village;

	/**
	 * @var string|null
	 */
	private ?string $suburb;

	/**
	 * @var string|null
	 */
	private ?string $city;

	/**
	 * @var string|null
	 */
	private ?string $county;

	/**
	 * @var string|null
	 */
	private ?string $postcode;

	/**
	 * @var string|null
	 */
	private ?string $stateDistrict;

	/**
	 * @var string|null
	 */
	private ?string $state;

	/**
	 * @var string|null
	 */
	private ?string $region;

	/**
	 * @var string|null
	 */
	private ?string $island;

	/**
	 * @var string|null
	 */
	private ?string $country;

	/**
	 * @var string|null
	 */
	private ?string $countryCode;

	/**
	 * @var string|null
	 */
	private ?string $continent;

	/**
	 * @var string|null
	 */
	private ?string $coordinates;

	/**
	 * @var string|null
	 */
	private ?string $formattedAddress;

	/**
	 * @return string|null
	 */
	public function getAttention(): ?string
	{
		return $this->attention;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setAttention(?string $val): self
	{
		$this->attention = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getHouseNumber(): ?string
	{
		return $this->houseNumber;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setHouseNumber(?string $val): self
	{
		$this->houseNumber = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getHouse(): ?string
	{
		return $this->house;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setHouse(?string $val): self
	{
		$this->house = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getRoad(): ?string
	{
		return $this->road;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setRoad(?string $val): self
	{
		$this->road = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getVillage(): ?string
	{
		return $this->village;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setVillage(?string $val): self
	{
		$this->village = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getSuburb(): ?string
	{
		return $this->suburb;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setSuburb(?string $val): self
	{
		$this->suburb = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getCity(): ?string
	{
		return $this->city;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setCity(?string $val): self
	{
		$this->city = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getCounty(): ?string
	{
		return $this->county;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setCounty(?string $val): self
	{
		$this->county = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getPostcode(): ?string
	{
		return $this->postcode;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setPostcode(?string $val): self
	{
		$this->postcode = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getStateDistrict(): ?string
	{
		return $this->stateDistrict;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setStateDistrict(?string $val): self
	{
		$this->stateDistrict = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getState(): ?string
	{
		return $this->state;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setState(?string $val): self
	{
		$this->state = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getRegion(): ?string
	{
		return $this->region;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setRegion(?string $val): self
	{
		$this->region = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getIsland(): ?string
	{
		return $this->island;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setIsland(?string $val): self
	{
		$this->island = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getCountry(): ?string
	{
		return $this->country;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setCountry(?string $val): self
	{
		$this->country = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getCountryCode(): ?string
	{
		return $this->countryCode;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setCountryCode(?string $val): self
	{
		$this->countryCode = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getContinent(): ?string
	{
		return $this->continent;
	}

	/**
	 * @param string|null $val
	 * @return self
	 */
	public function setContinent(?string $val): self
	{
		$this->continent = $val;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getCoordinates(): ?string
	{
		return $this->coordinates;
	}

	/**
	 * @param string|null $coordinates
	 * @return self
	 */
	public function setCoordinates(?string $coordinates): self
	{
		$this->coordinates = $coordinates;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getFormattedAddress(): ?string
	{
		return $this->formattedAddress;
	}

	/**
	 * @param string|null $formattedAddress
	 * @return self
	 */
	public function setFormattedAddress(?string $formattedAddress): self
	{
		$this->formattedAddress = $formattedAddress;

		return $this;
	}

}