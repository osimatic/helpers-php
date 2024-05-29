<?php

namespace Osimatic\Location;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class GoogleMaps
{
	private HTTPClient $httpClient;

	public function __construct(
		private ?string $apiKey=null,
		private LoggerInterface $logger=new NullLogger(),
	) {
		$this->httpClient = new HTTPClient($logger);
	}

	/**
	 * @param string $apiKey
	 * @return self
	 */
	public function setApiKey(string $apiKey): self
	{
		$this->apiKey = $apiKey;

		return $this;
	}

	/**
	 * @param LoggerInterface $logger
	 * @return self
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;
		$this->httpClient->setLogger($logger);

		return $this;
	}

	/**
	 * @param string $coordinates
	 * @return string
	 */
	public static function getUrl(string $coordinates): string
	{
		[$latitude, $longitude] = array_values(explode(',', $coordinates));

		// return 'http://maps.googleapis.com/maps/api/staticmap?center='.$latitude.','.$longitude.'&zoom=14&size=400x300&sensor=false';
		return 'https://maps.google.com/?q='.$latitude.','.$longitude.'';
	}

	/**
	 * @param string $address
	 * @return null|array
	 */
	public function geocoding(string $address): ?array
	{
		$address = str_replace(['’', '́', '+', ' '], ["'", "'", '%2B', '+'], $address);

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&key='.$this->apiKey;

		if (null === ($json = $this->httpClient->jsonRequest(HTTPMethod::GET, $url))) {
			return null;
		}

		return $this->getResults($json);
	}

	/**
	 * @param string $coordinates
	 * @return array|null
	 */
	public function reverseGeocoding(string $coordinates): ?array
	{
		[$latitude, $longitude] = explode(',', $coordinates);
		return $this->reverseGeocodingFromLatitudeAndLongitude((float) $latitude, (float) $longitude);
	}

	/**
	 * @param float $latitude
	 * @param float $longitude
	 * @return array|null
	 */
	public function reverseGeocodingFromLatitudeAndLongitude(float $latitude, float $longitude): ?array
	{
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.GeographicCoordinates::getCoordinatesFromLatitudeAndLongitude($latitude, $longitude).'&key='.$this->apiKey;

		if (null === ($json = $this->httpClient->jsonRequest(HTTPMethod::GET, $url))) {
			return null;
		}

		return $this->getResults($json);
	}

	/**
	 * @param PostalAddressInterface $postalAddress
	 * @param string $address
	 * @return bool
	 */
	public function initPostalAddressDataFromAddress(PostalAddressInterface $postalAddress, string $address): bool
	{
		if (null === ($results = $this->geocoding($address))) {
			return false;
		}

		return self::initPostalAddressFromResult($postalAddress, $results[0]);
	}

	/**
	 * @param string $address
	 * @return string|null
	 */
	public function getCoordinatesFromAddress(string $address): ?string
	{
		if (null === ($results = $this->geocoding($address))) {
			return null;
		}

		return self::getCoordinatesFromResult($results[0]);
	}

	/**
	 * @param string $address
	 * @return string|null
	 */
	public function getFormattedAddressFromAddress(string $address): ?string
	{
		if (null === ($results = $this->geocoding($address))) {
			return null;
		}

		return self::getFormattedAddressFromResult($results[0]);
	}


	/**
	 * @param PostalAddressInterface $postalAddress
	 * @param string $defaultCountryCode
	 * @return string|null
	 */
	public function getCoordinatesFromPostalAddress(PostalAddressInterface $postalAddress, string $defaultCountryCode='FR'): ?string
	{
		if (empty($postalAddress->getRoad()) || empty($postalAddress->getCity())) {
			return null;
		}

		$countryName = \Osimatic\Location\Country::getCountryNameFromCountryCode($postalAddress->getCountryCode() ?? $defaultCountryCode);
		if (!empty($postalAddress->getRoad()) && !empty($postalAddress->getAttention())) {
			$address = $postalAddress->getRoad().', '.$postalAddress->getAttention().', '.$postalAddress->getPostcode().' '.$postalAddress->getCity().', '.$countryName;
			if (null !== ($coordinates = $this->getCoordinatesFromAddress($address))) {
				return $coordinates;
			}
		}

		if (!empty($postalAddress->getRoad())) {
			$address = $postalAddress->getRoad().', '.$postalAddress->getPostcode().' '.$postalAddress->getCity().', '.$countryName;
			if (null !== ($coordinates = $this->getCoordinatesFromAddress($address))) {
				return $coordinates;
			}
		}

		if (!empty($postalAddress->getAttention())) {
			$address = $postalAddress->getAttention().', '.$postalAddress->getPostcode().' '.$postalAddress->getCity().', '.$countryName;
			if (null !== ($coordinates = $this->getCoordinatesFromAddress($address))) {
				return $coordinates;
			}
		}

		return null;
	}

	/**
	 * @param PostalAddressInterface $postalAddress
	 * @param string $coordinates
	 * @return bool
	 */
	public function initPostalAddressDataFromCoordinates(PostalAddressInterface $postalAddress, string $coordinates): bool
	{
		if (null === ($results = $this->reverseGeocoding($coordinates))) {
			return false;
		}

		if (null === ($formattedAddress = self::getFormattedAddressFromResult($results[0]))) {
			return false;
		}

		if (false === self::initPostalAddressFromResult($postalAddress, $results[0])) {
			return false;
		}

		$postalAddress->setCoordinates($coordinates);
		return true;
	}

	/**
	 * @param string $coordinates
	 * @return string|null
	 */
	public function getFormattedAddressFromCoordinates(string $coordinates): ?string
	{
		if (null === ($results = $this->reverseGeocoding($coordinates))) {
			return null;
		}

		return self::getFormattedAddressFromResult($results[0]);
	}

	/**
	 * @param array|null $result
	 * @return array|null
	 */
	public static function getAddressComponentsFromResult(?array $result): ?array
	{
		if (empty($result['address_components'] ?? null)) {
			return null;
		}

		$addressComponents = [];
		foreach (($result['address_components'] ?? []) as $resultAddressComponent) {
			if (in_array('street_number', $resultAddressComponent['types'], true)) {
				$addressComponents['street_number'] = $resultAddressComponent['long_name'];
			}
			if (in_array('route', $resultAddressComponent['types'], true)) {
				$addressComponents['route'] = $resultAddressComponent['long_name'];
			}
			if (in_array('sublocality_level_1', $resultAddressComponent['types'], true)) {
				$addressComponents['suburb'] = $resultAddressComponent['long_name'];
			}
			if (in_array('locality', $resultAddressComponent['types'], true)) {
				$addressComponents['locality'] = $resultAddressComponent['long_name'];
			}
			if (in_array('postal_town', $resultAddressComponent['types'], true)) {
				$addressComponents['locality'] = $resultAddressComponent['long_name'];
			}
			if (in_array('administrative_area_level_3', $resultAddressComponent['types'], true)) {
				$addressComponents['locality'] = $resultAddressComponent['long_name'];
			}
			if (in_array('administrative_area_level_2', $resultAddressComponent['types'], true)) {
				$addressComponents['administrative_area_level_2'] = $resultAddressComponent['long_name'];
			}
			if (in_array('administrative_area_level_1', $resultAddressComponent['types'], true)) {
				$addressComponents['administrative_area_level_1'] = $resultAddressComponent['long_name'];
			}
			if (in_array('country', $resultAddressComponent['types'], true)) {
				$addressComponents['country'] = $resultAddressComponent['long_name'];
				$addressComponents['country_code'] = $resultAddressComponent['short_name'];
			}
			if (in_array('postal_code', $resultAddressComponent['types'], true)) {
				$addressComponents['postal_code'] = $resultAddressComponent['long_name'];
			}
		}

		$addressComponents['street_number'] ??= '';
		$addressComponents['route'] ??= '';

		// on génère la ligne numéro/rue depuis le champs "formatted_address", car la ligne numéro/rue n'est pas disponible dans le champs "address_components"
		//$result['formatted_address'] = '131, Avenue Charles de Gaulle, 92200 Neuilly-sur-Seine, France';
		$streetAddress = $result['formatted_address'] ?? '';
		if (!empty($addressComponents['route']) && false !== ($pos = strpos($streetAddress, ',', strpos($streetAddress, $addressComponents['route'])))) {
			$streetAddress = substr($streetAddress, 0, $pos);
		}
		//if (false !== ($pos = strrpos($streetAddress, ','))) {
		//	$streetAddress = trim(substr($streetAddress, $pos + 1));
		//}
		if (!empty($addressComponents['route']) && !empty($streetAddress) && strstr($streetAddress, $addressComponents['route'])) {
			$addressComponents['street'] = $streetAddress;
		}
		else {
			// si on arrive pas à la récupérer depuis le champs "formatted_address", on concatene simplement le numéro et rue récupéré dans le champs "address_components"
			// on ne fais pas ceci directement car selon les pays le numéro de la rue se trouve à la fin, au début, précédé de "No.", etc.
			$addressComponents['street'] = trim($addressComponents['street_number'].' '.$addressComponents['route']);
		}

		return $addressComponents;
	}

	/**
	 * @param array|null $result
	 * @return string|null
	 */
	public static function getCoordinatesFromResult(?array $result): ?string
	{
		$lat = $result['geometry']['location']['lat'] ?? null;
		$lng = $result['geometry']['location']['lng'] ?? null;
		if (empty($lat) || empty($lng)) {
			return null;
		}
		return GeographicCoordinates::getCoordinatesFromLatitudeAndLongitude($lat, $lng);
	}

	/**
	 * @param array|null $result
	 * @return string|null
	 */
	public static function getFormattedAddressFromResult(?array $result): ?string
	{
		if (empty($formattedAddress = $result['formatted_address'] ?? null)) {
			return null;
		}

		return PostalAddress::replaceSpecialChar($formattedAddress);
	}

	/**
	 * @param PostalAddressInterface $postalAddress
	 * @param array|null $result
	 * @return bool
	 */
	public static function initPostalAddressFromResult(PostalAddressInterface $postalAddress, ?array $result): bool
	{
		if (null === ($coordinates = self::getCoordinatesFromResult($result))) {
			return false;
		}

		$formattedAddress = self::getFormattedAddressFromResult($result);
		$addressComponents = self::getAddressComponentsFromResult($result);

		$postalAddress->setRoad($addressComponents['street'] ?? null);
		$postalAddress->setPostcode($addressComponents['postal_code'] ?? null);
		$postalAddress->setCity($addressComponents['locality'] ?? null);
		$postalAddress->setCountryCode($addressComponents['country_code'] ?? null);
		$postalAddress->setCoordinates($coordinates);
		$postalAddress->setFormattedAddress($formattedAddress);

		return true;
	}

	// private

	/**
	 * @param array $result
	 * @return array|null
	 */
	private function getResults(array $result): ?array
	{
		if ($result['status'] === 'REQUEST_DENIED') {
			$this->logger->error('Accès refusé : '.($result['error_message'] ?? ''));
			return null;
		}

		if ($result['status'] === 'OVER_QUERY_LIMIT') {
			$this->logger->error('Quota atteint : '.($result['error_message'] ?? ''));
			return null;
		}

		if ($result['status'] === 'INVALID_REQUEST') {
			$this->logger->error('Requête invalide : '.($result['error_message'] ?? ''));
			return null;
		}

		if ($result['status'] === 'UNKNOWN_ERROR' || $result['status'] === 'ERROR') {
			$this->logger->error('Erreur pendant la requete vers l\'API Google : '.($result['error_message'] ?? ''));
			return null;
		}

		if ($result['status'] === 'ZERO_RESULTS' || !isset($result['results'])) {
			$this->logger->info('Aucun résultat trouvé.');
			return null;
		}

		// var_dump($json);
		return $result['results'];
	}




	// deprecated

	/**
	 * @deprecated Use getPostalAddressDataFromAddress instead
	 * @param string $address
	 * @return array|null
	 */
	public function getAddressDataFromAddress(string $address): ?array
	{
		if (null === ($results = $this->geocoding($address))) {
			return null;
		}

		if (null === ($coordinates = self::getCoordinatesFromResult($results[0]))) {
			return null;
		}

		return [
			'coordinates' => $coordinates,
			'formatted_address' => self::getFormattedAddressFromResult($results[0]),
			'address_components' => self::getAddressComponentsFromResult($results[0]),
		];
	}

	/**
	 * @deprecated Use getPostalAddressDataFromCoordinates instead
	 * @param string $coordinates
	 * @return array|null
	 */
	public function getAddressDataFromCoordinates(string $coordinates): ?array
	{
		if (null === ($results = $this->reverseGeocoding($coordinates))) {
			return null;
		}

		if (null === ($formattedAddress = self::getFormattedAddressFromResult($results[0]))) {
			return null;
		}

		return [
			'coordinates' => $coordinates,
			'formatted_address' => $formattedAddress,
			'address_components' => self::getAddressComponentsFromResult($results[0]),
		];
	}

	/**
	 * @deprecated Use getPostalAddressDataFromAddress instead
	 * @param string $address
	 * @return array|null
	 */
	public function getAddressComponentsFromAddress(string $address): ?array
	{
		if (null === ($results = $this->geocoding($address))) {
			return null;
		}

		return self::getAddressComponentsFromResult($results[0]);
	}

	/**
	 * @deprecated Use getPostalAddressDataFromCoordinates instead
	 * @param string $coordinates
	 * @return array|null
	 */
	public function getAddressComponentsFromCoordinates(string $coordinates): ?array
	{
		if (null === ($results = $this->reverseGeocoding($coordinates))) {
			return null;
		}

		return self::getAddressComponentsFromResult($results[0]);
	}


}