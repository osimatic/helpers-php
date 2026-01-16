<?php

namespace Osimatic\Location;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Client for the Google Maps Geocoding API.
 * Provides methods for geocoding (address to coordinates), reverse geocoding (coordinates to address),
 * and populating PostalAddress objects from Google Maps API responses.
 */
class GoogleMaps
{
	private HTTPClient $httpClient;

	/**
	 * In-memory cache for API responses to reduce duplicate requests.
	 * @var array<string, array>
	 */
	private array $cache = [];

	/**
	 * Cache TTL in seconds (default: 1 hour).
	 * @var int
	 */
	private int $cacheTtl = 3600;

	/**
	 * Construct a new Google Maps API client.
	 * @param string|null $apiKey The Google Maps API key for authentication
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 * @param int $cacheTtl Cache time-to-live in seconds (default: 3600)
	 */
	public function __construct(
		private ?string $apiKey=null,
		private LoggerInterface $logger=new NullLogger(),
		int $cacheTtl=3600,
	) {
		$this->httpClient = new HTTPClient($logger);
		$this->cacheTtl = $cacheTtl;
	}

	/**
	 * Set the Google Maps API key.
	 * @param string $apiKey The API key for authentication
	 * @return self Returns this instance for method chaining
	 */
	public function setApiKey(string $apiKey): self
	{
		$this->apiKey = $apiKey;

		return $this;
	}

	/**
	 * Sets the logger for error and debugging information.
	 * @param LoggerInterface $logger The PSR-3 logger instance
	 * @return self Returns this instance for method chaining
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;
		$this->httpClient->setLogger($logger);

		return $this;
	}

	/**
	 * Generate a Google Maps URL for the given coordinates.
	 * @param string $coordinates The coordinate string in "lat,lon" format
	 * @return string The Google Maps URL for viewing the location
	 */
	public static function getUrl(string $coordinates): string
	{
		[$latitude, $longitude] = array_values(explode(',', $coordinates));

		// return 'http://maps.googleapis.com/maps/api/staticmap?center='.$latitude.','.$longitude.'&zoom=14&size=400x300&sensor=false';
		return 'https://maps.google.com/?q='.$latitude.','.$longitude.'';
	}

	/**
	 * Convert an address string to geographic coordinates using Google Maps Geocoding API.
	 * Returns an array of possible results, ordered by relevance.
	 * @param string $address The address to geocode (e.g., "10 Downing Street, London, UK")
	 * @return array|null Array of geocoding results from Google Maps API, or null on error
	 */
	public function geocoding(string $address): ?array
	{
		$cacheKey = 'geocode_' . md5($address);

		// Check cache first
		if ($this->isCacheValid($cacheKey)) {
			return $this->cache[$cacheKey]['data'];
		}

		$address = str_replace(['’', '́', '+', ' '], ["'", "'", '%2B', '+'], $address);

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.'&key='.$this->apiKey;

		if (null === ($json = $this->httpClient->jsonRequest(HTTPMethod::GET, $url))) {
			return null;
		}

		$results = $this->getResults($json);

		// Cache the results
		if (null !== $results) {
			$this->cacheSet($cacheKey, $results);
		}

		return $results;
	}

	/**
	 * Convert geographic coordinates to an address using Google Maps Reverse Geocoding API.
	 * Returns an array of possible addresses at or near the given coordinates.
	 * @param string $coordinates The coordinates to reverse geocode in "lat,lon" format (e.g., "48.8566,2.3522")
	 * @return array|null Array of reverse geocoding results from Google Maps API, or null on error
	 */
	public function reverseGeocoding(string $coordinates): ?array
	{
		[$latitude, $longitude] = explode(',', $coordinates);
		return $this->reverseGeocodingFromLatitudeAndLongitude((float) $latitude, (float) $longitude);
	}

	/**
	 * Convert latitude and longitude to an address using Google Maps Reverse Geocoding API.
	 * Returns an array of possible addresses at or near the given coordinates.
	 * @param float $latitude The latitude value
	 * @param float $longitude The longitude value
	 * @return array|null Array of reverse geocoding results from Google Maps API, or null on error
	 */
	public function reverseGeocodingFromLatitudeAndLongitude(float $latitude, float $longitude): ?array
	{
		$coordinates = GeographicCoordinates::getCoordinatesFromLatitudeAndLongitude($latitude, $longitude);
		$cacheKey = 'reverse_' . md5($coordinates);

		// Check cache first
		if ($this->isCacheValid($cacheKey)) {
			return $this->cache[$cacheKey]['data'];
		}

		$url = 'https://maps.googleapis.com/maps/api/geocode/json?latlng='.$coordinates.'&key='.$this->apiKey;

		if (null === ($json = $this->httpClient->jsonRequest(HTTPMethod::GET, $url))) {
			return null;
		}

		$results = $this->getResults($json);

		// Cache the results
		if (null !== $results) {
			$this->cacheSet($cacheKey, $results);
		}

		return $results;
	}

	/**
	 * Populate a PostalAddress object with data from geocoding an address string.
	 * Sets road, postcode, city, country code, coordinates, and formatted address on the object.
	 * @param PostalAddressInterface $postalAddress The postal address object to populate
	 * @param string $address The address string to geocode
	 * @return bool True if successful, false on error
	 */
	public function initPostalAddressDataFromAddress(PostalAddressInterface $postalAddress, string $address): bool
	{
		if (null === ($results = $this->geocoding($address))) {
			return false;
		}

		return self::initPostalAddressFromResult($postalAddress, $results[0]);
	}

	/**
	 * Get geographic coordinates from an address string.
	 * @param string $address The address to geocode
	 * @return string|null The coordinates in "lat,lon" format, or null if not found
	 */
	public function getCoordinatesFromAddress(string $address): ?string
	{
		if (null === ($results = $this->geocoding($address))) {
			return null;
		}

		return self::getCoordinatesFromResult($results[0]);
	}

	/**
	 * Get a formatted address string from an address query.
	 * Returns Google's standardized formatted address.
	 * @param string $address The address to geocode
	 * @return string|null The formatted address string, or null if not found
	 */
	public function getFormattedAddressFromAddress(string $address): ?string
	{
		if (null === ($results = $this->geocoding($address))) {
			return null;
		}

		return self::getFormattedAddressFromResult($results[0]);
	}


	/**
	 * Get coordinates from a PostalAddress object by geocoding its address components.
	 * Tries multiple address combinations in order of specificity until one succeeds.
	 * @param PostalAddressInterface $postalAddress The postal address object
	 * @param string $defaultCountryCode The default country code if not set in the address (default: 'FR')
	 * @return string|null The coordinates in "lat,lon" format, or null if geocoding fails
	 */
	public function getCoordinatesFromPostalAddress(PostalAddressInterface $postalAddress, string $defaultCountryCode='FR'): ?string
	{
		if (empty($postalAddress->getRoad()) || empty($postalAddress->getCity())) {
			return null;
		}

		$countryName = Country::getCountryNameFromCountryCode($postalAddress->getCountryCode() ?? $defaultCountryCode);
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
	 * Populate a PostalAddress object with data from reverse geocoding coordinates.
	 * Sets road, postcode, city, country code, coordinates, and formatted address on the object.
	 * @param PostalAddressInterface $postalAddress The postal address object to populate
	 * @param string $coordinates The coordinates in "lat,lon" format
	 * @return bool True if successful, false on error
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
	 * Get a formatted address string from geographic coordinates using reverse geocoding.
	 * Returns Google's standardized formatted address.
	 * @param string $coordinates The coordinates to reverse geocode in "lat,lon" format
	 * @return string|null The formatted address string, or null if not found
	 */
	public function getFormattedAddressFromCoordinates(string $coordinates): ?string
	{
		if (null === ($results = $this->reverseGeocoding($coordinates))) {
			return null;
		}

		return self::getFormattedAddressFromResult($results[0]);
	}

	/**
	 * Extract and normalize address components from a Google Maps API result.
	 * Converts Google's address_components structure into a simplified array with keys like 'street', 'locality', 'postal_code', etc.
	 * @param array|null $result A single result from the Google Maps Geocoding API response
	 * @return array|null Associative array of address components, or null if parsing fails
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

		// Generate the street address line from the "formatted_address" field, since it's not available in the "address_components" field
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
			// If we can't extract it from the "formatted_address" field, simply concatenate the street number and route from "address_components"
			// We don't do this directly because in some countries the street number appears at the end, at the beginning, preceded by "No.", etc.
			$addressComponents['street'] = trim($addressComponents['street_number'].' '.$addressComponents['route']);
		}

		return $addressComponents;
	}

	/**
	 * Extract coordinates from a Google Maps API result.
	 * @param array|null $result A single result from the Google Maps Geocoding API response
	 * @return string|null The coordinates in "lat,lon" format, or null if not found
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
	 * Extract the formatted address string from a Google Maps API result.
	 * @param array|null $result A single result from the Google Maps Geocoding API response
	 * @return string|null The formatted address with special characters normalized, or null if not found
	 */
	public static function getFormattedAddressFromResult(?array $result): ?string
	{
		if (empty($formattedAddress = $result['formatted_address'] ?? null)) {
			return null;
		}

		return PostalAddress::replaceSpecialChar($formattedAddress);
	}

	/**
	 * Populate a PostalAddress object from a Google Maps API result.
	 * Sets road, postcode, city, country code, coordinates, and formatted address on the object.
	 * @param PostalAddressInterface $postalAddress The postal address object to populate
	 * @param array|null $result A single result from the Google Maps Geocoding API response
	 * @return bool True if successful, false if result is missing coordinates
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
	 * Check if a cache entry is valid (exists and not expired).
	 * @param string $key The cache key
	 * @return bool True if cache is valid
	 */
	private function isCacheValid(string $key): bool
	{
		if (!isset($this->cache[$key])) {
			return false;
		}

		$expiresAt = $this->cache[$key]['expires_at'];
		return time() < $expiresAt;
	}

	/**
	 * Store data in the cache with TTL.
	 * @param string $key The cache key
	 * @param array $data The data to cache
	 * @return void
	 */
	private function cacheSet(string $key, array $data): void
	{
		$this->cache[$key] = [
			'data' => $data,
			'expires_at' => time() + $this->cacheTtl,
		];
	}

	/**
	 * Clear the entire cache.
	 * @return void
	 */
	public function clearCache(): void
	{
		$this->cache = [];
	}

	/**
	 * Process Google Maps API response and handle errors.
	 * Logs errors via the configured logger and returns null on failure.
	 * @param array $result The raw JSON response from Google Maps API
	 * @return array|null Array of results on success, null on error or no results
	 */
	private function getResults(array $result): ?array
	{
		if ($result['status'] === 'REQUEST_DENIED') {
			$this->logger->error('Access denied: '.($result['error_message'] ?? ''));
			return null;
		}

		if ($result['status'] === 'OVER_QUERY_LIMIT') {
			$this->logger->error('Quota exceeded: '.($result['error_message'] ?? ''));
			return null;
		}

		if ($result['status'] === 'INVALID_REQUEST') {
			$this->logger->error('Invalid request: '.($result['error_message'] ?? ''));
			return null;
		}

		if ($result['status'] === 'UNKNOWN_ERROR' || $result['status'] === 'ERROR') {
			$this->logger->error('Error during Google API request: '.($result['error_message'] ?? ''));
			return null;
		}

		if ($result['status'] === 'ZERO_RESULTS' || !isset($result['results'])) {
			$this->logger->info('No results found.');
			return null;
		}

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