<?php

namespace Osimatic\Helpers\API;

use Osimatic\Helpers\Location\GeographicCoordinates;
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
		$address = str_replace(['’', '+', ' '], ["'", '%2B', '+'], $address);

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&key='.$this->apiKey;

		if (null === ($results = $this->request($url))) {
			return null;
		}

		return $this->getResults($results);
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

		if (null === ($results = $this->request($url))) {
			return null;
		}

		return $this->getResults($results);
	}



	/**
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

		if (null === ($results = $this->request($url))) {
			return null;
		}

		if (empty($results['status']) || $results['status'] !== 'OK') {
			return null;
		}

		$element = $results['rows'][0]['elements'][0];

		if (empty($element['status']) || $element['status'] !== 'OK') {
			return null;
		}

		return [$element['duration']['value'] ?? 0, $element['distance']['value'] ?? 0];
	}

	/**
	 * @param array|null $result
	 * @return array
	 */
	public static function getAddressComponentsFromResult(?array $result): ?array
	{
		if (empty($formattedAddress = $result['address_components'] ?? null)) {
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

		// caractère parfois utilisé pour séparer la rue de la ville (exemple pour une adresse de la Tunisie, coordonnées 36.7691557,10.2432981)
		$formattedAddress = str_replace('،', ',', $formattedAddress);

		return $formattedAddress;
	}

	// private

	/**
	 * @param string $url
	 * @return null|mixed
	 */
	private function request(string $url)
	{
		/*
		if (($json = file_get_contents($url)) === false) {
			$this->logger->error('Erreur pendant la requete vers l\'API Google.');
			return null;
		}
		return $json;
		*/

		$client = new \GuzzleHttp\Client();
		try {
			$options = [
				'http_errors' => false,
			];
			$res = $client->request('GET', $url, $options);
		}
		catch (\Exception $e) {
			$this->logger->error('Erreur pendant la requete vers l\'API Google.');
			return null;
		}

		try {
			return \GuzzleHttp\json_decode((string) $res->getBody(), true);
		}
		catch (\Exception $e) {
			$this->logger->error('Erreur pendant le décodage du résultat.');
		}
		return null;
	}

	/**
	 * @param array $result
	 * @return array|null
	 */
	private function getResults(array $result): ?array
	{
		if ($result['status'] === 'REQUEST_DENIED') {
			$this->logger->info('Accès refusé : '.($result['error_message'] ?? ''));
			return null;
		}

		if ($result['status'] === 'OVER_QUERY_LIMIT') {
			$this->logger->info('Quota atteint : '.($result['error_message'] ?? ''));
			return null;
		}

		if ($result['status'] === 'INVALID_REQUEST') {
			$this->logger->info('Requête invalide : '.($result['error_message'] ?? ''));
			return null;
		}

		if ($result['status'] === 'UNKNOWN_ERROR' || $result['status'] === 'ERROR') {
			$this->logger->info('Erreur pendant la requete vers l\'API Google : '.($result['error_message'] ?? ''));
			return null;
		}

		if ($result['status'] === 'ZERO_RESULTS' || !isset($result['results'])) {
			$this->logger->info('Aucun résultat trouvé.');
			return null;
		}

		// var_dump($json);
		return $result['results'];
	}

}