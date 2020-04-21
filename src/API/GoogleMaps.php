<?php

namespace Osimatic\Helpers\API;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class GoogleMaps
{
	/**
	 * @var LoggerInterface
	 */
	private $logger;

	/**
	 * @var string
	 */
	private $apiKey;

	public function __construct()
	{
		$this->logger = new NullLogger();
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
	 * @param $address
	 * @return null|array
	 */
	public function geocoding(string $address): ?array
	{
		$address = str_replace('’', "'", $address);
		$address = str_replace(array('+', ' '), array('%2B', '+'), utf8_encode($address));

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&key='.$this->apiKey;

		if (($json = file_get_contents($url)) === false) {
			$this->logger->error('Erreur pendant la requete vers l\'API Google.');
			return null;
		}

		$result = json_decode($json, true);

		if ($result['status'] === 'ZERO_RESULTS' || !isset($result['results'])) {
			$this->logger->info('Aucun résultat trouvé.');
			return null;
		}

		// var_dump($json);
		return $result;
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
		$latitude = str_replace(' ', '', $latitude);
		$longitude = str_replace(' ', '', $longitude);

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$latitude.','.$longitude.'&key='.$this->apiKey;

		if (($json = file_get_contents($url)) === false) {
			$this->logger->error('Erreur pendant la requete vers l\'API Google.');
			return null;
		}

		$result = json_decode($json, true);

		if ($result['status'] === 'ZERO_RESULTS' || !isset($result['results'])) {
			$this->logger->info('Aucun résultat trouvé.');
			return null;
		}

		return $result['results'];
	}

	/**
	 * @param string $address
	 * @return string|null
	 */
	public function getCoordinatesFromAddress(string $address): ?string
	{
		$results = $this->geocoding($address);
		if (null === $results) {
			return null;
		}

		$coordinates = self::getCoordinatesFromResult($results[0]);
		if (null !== $coordinates) {
			return $coordinates;
		}

		return null;
	}

	/**
	 * @param string $coordinates
	 * @return string|null
	 */
	public function getFormattedAddressFromCoordinates(string $coordinates): ?string
	{
		$results = $this->reverseGeocoding($coordinates);
		if (null === $results) {
			return null;
		}

		$address = self::getFormattedAddressFromResult($results[0]);
		if (null !== $address) {
			return $address;
		}

		//trace('Erreur inconnue : '.$result['status'].'.');
		return null;
	}

	/**
	 * Retourne durée en seconde et distance en mètres
	 * @param string $originCoordinates
	 * @param string $destinationCoordinates
	 * @param array $parameters
	 * @return array|null
	 */
	public function getDistanceMatrix(string $originCoordinates, string $destinationCoordinates, array $parameters=[]): ?array
	{
		$originCoordinates = str_replace(' ', '', $originCoordinates);
		$destinationCoordinates = str_replace(' ', '', $destinationCoordinates);

		$params = [
			'origins' => $originCoordinates,
			'destinations' => $destinationCoordinates,
		];

		$mode = $parameters['mode'] ?? null;
		if (!empty($mode)) {
			if (!in_array($mode, ['driving', 'walking', 'bicycling', 'transit'])) {
				return null;
			}
			$params['mode'] = $mode;
		}

		if ('driving' === $mode) {
			$avoid = $parameters['avoid'] ?? null;
			if (!empty($avoid)) {
				if (!in_array($avoid, ['tolls', 'highways', 'ferries', 'indoor'])) {
					return null;
				}
				$params['avoid'] = $avoid;
			}
		}

		if ('transit' === $mode) {
			$transitMode = $parameters['transit_mode'] ?? null;
			$transitMode = (is_array($transitMode)?$transitMode:[$transitMode]);
			if (!empty($transitMode)) {
				foreach ($transitMode as $oneTransitMode) {
					if (!in_array($oneTransitMode, ['bus', 'subway', 'train', 'tram'])) {
						return null;
					}
				}
				$params['transit_mode'] = implode('|', $transitMode);
			}
		}

		$url = 'https://maps.googleapis.com/maps/api/distancematrix/json?'.http_build_query($params).'&key='.$this->apiKey;
		if (($json = file_get_contents($url)) === false) {
			return null;
		}

		$results = json_decode($json, true);
		if (empty($results['status']) || $results['status'] !== 'OK') {
			return null;
		}

		$element = $results['rows'][0]['elements'][0];

		if (empty($element['status']) || $element['status'] !== 'OK') {
			return null;
		}

		return [$element['duration']['value'] ?? 0, $element['distance']['value'] ?? 0];
	}


	private static function getAddressComponentsFromResult($result): array
	{
		$addressComponents = [];
		foreach (($result['address_components'] ?? []) as $resultAddressComponent) {
			if (in_array('street_number', $resultAddressComponent['types'], true)) {
				$addressComponents['street_number'] = $resultAddressComponent['long_name'];
			}
			if (in_array('route', $resultAddressComponent['types'], true)) {
				$addressComponents['route'] = $resultAddressComponent['long_name'];
			}
			if (in_array('locality', $resultAddressComponent['types'], true)) {
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
			}
			if (in_array('postal_code', $resultAddressComponent['types'], true)) {
				$addressComponents['postal_code'] = $resultAddressComponent['long_name'];
			}
		}
		return $addressComponents;
	}

	private static function getCoordinatesFromResult($result): ?string
	{
		$lat = $result['geometry']['location']['lat'] ?? null;
		$lng = $result['geometry']['location']['lng'] ?? null;
		if (empty($lat) || empty($lng)) {
			return null;
		}
		return $lat.','.$lng;
	}

	private static function getFormattedAddressFromResult($result): ?string
	{
		if (empty($formattedAddress = $result['formatted_address'] ?? null)) {
			return null;
		}
		return $formattedAddress;
	}

}