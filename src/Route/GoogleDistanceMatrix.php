<?php

namespace Osimatic\Route;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Google Distance Matrix API client for calculating travel distance and time between locations.
 * This class provides methods to interact with the Google Distance Matrix API to retrieve
 * distance and duration information for different travel modes and routing preferences.
 * @see https://developers.google.com/maps/documentation/distance-matrix
 */
class GoogleDistanceMatrix
{
	/**
	 * The HTTP client used for making API requests.
	 * @var HTTPClient
	 */
	private HTTPClient $httpClient;

	/**
	 * Constructs a new Google Distance Matrix API client.
	 * @param string|null $apiKey The Google API key for authentication (can be set later via setApiKey())
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 */
	public function __construct(
		private ?string $apiKey=null,
		private LoggerInterface $logger=new NullLogger(),
	) {
		$this->httpClient = new HTTPClient($logger);
	}

	/**
	 * Sets the Google API key for authenticating requests.
	 * @param string $apiKey The Google API key to use for Distance Matrix API requests
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
	 * Retrieves distance and duration information between two locations using Google Distance Matrix API.
	 * Returns an array containing duration in seconds and distance in meters.
	 * @param string $originCoordinates Origin coordinates in format "latitude,longitude" (e.g., "48.8566,2.3522")
	 * @param string $destinationCoordinates Destination coordinates in format "latitude,longitude" (e.g., "51.5074,0.1278")
	 * @param TravelMode $travelMode The travel mode to use (DRIVE, TRANSIT, WALK, or BICYCLE). Default: TravelMode::DRIVE
	 * @param GoogleDistanceMatrixParameters $parameters Additional parameters for route calculation (avoid tolls/highways/ferries/indoor, transit modes)
	 * @return array|null Returns array [duration_in_seconds, distance_in_meters] on success, null on failure or invalid travel mode
	 */
	public function getDistanceMatrix(string $originCoordinates, string $destinationCoordinates, TravelMode $travelMode = TravelMode::DRIVE, GoogleDistanceMatrixParameters $parameters = new GoogleDistanceMatrixParameters()): ?array
	{
		if (empty($this->apiKey)) {
			$this->logger->error('Google Distance Matrix API key is missing');
			return null;
		}

		$originCoordinates = str_replace(' ', '', $originCoordinates);
		$destinationCoordinates = str_replace(' ', '', $destinationCoordinates);

		$params = [
			'origins' => $originCoordinates,
			'destinations' => $destinationCoordinates,
		];

		$params['mode'] = match ($travelMode) {
			TravelMode::DRIVE => 'driving',
			TravelMode::TRANSIT => 'transit',
			TravelMode::WALK => 'walking',
			TravelMode::BICYCLE => 'bicycling',
			default => null
		};
		if (null === $params['mode']) {
			$this->logger->error('Invalid travel mode provided', ['travelMode' => $travelMode->value]);
			return null;
		}

		$avoid = [];

		if (TravelMode::DRIVE === $travelMode) {
			if ($parameters->isAvoidTolls()) {
				$avoid[] = 'tolls';
			}
			if ($parameters->isAvoidHighways()) {
				$avoid[] = 'highways';
			}
			if ($parameters->isAvoidFerries()) {
				$avoid[] = 'ferries';
			}
		}

		if (TravelMode::WALK === $travelMode && $parameters->isAvoidIndoor()) {
			$avoid[] = 'indoor';
		}

		if (!empty($avoid)) {
			$params['avoid'] = implode('|', $avoid);
		}

		if (TravelMode::TRANSIT === $travelMode && !empty($transitModes = $parameters->getTransitModes())) {
			$transitModesStrings = array_values(array_filter(array_map(static fn(TransitTravelMode $transitMode) => match ($transitMode) {
				TransitTravelMode::BUS => 'bus',
				TransitTravelMode::SUBWAY => 'subway',
				TransitTravelMode::TRAIN => 'train',
				TransitTravelMode::LIGHT_RAIL => 'tram',
			}, $transitModes)));
			if (!empty($transitModesStrings)) {
				$params['transit_mode'] = implode('|', $transitModesStrings);
			}
		}

		$params['key'] = $this->apiKey;
		$url = 'https://maps.googleapis.com/maps/api/distancematrix/json?'.http_build_query($params);

		if (null === ($json = $this->httpClient->jsonRequest(HTTPMethod::GET, $url))) {
			$this->logger->error('Failed to fetch data from Google Distance Matrix API');
			return null;
		}

		if (empty($json['status']) || $json['status'] !== 'OK') {
			$this->logger->error('Google Distance Matrix API returned error status', [
				'status' => $json['status'] ?? 'unknown',
				'error_message' => $json['error_message'] ?? null,
			]);
			return null;
		}

		if (!isset($json['rows'][0]['elements'][0])) {
			$this->logger->error('Invalid response structure from Google Distance Matrix API');
			return null;
		}

		$element = $json['rows'][0]['elements'][0];

		if (empty($element['status']) || $element['status'] !== 'OK') {
			$this->logger->warning('Google Distance Matrix API could not calculate route', [
				'status' => $element['status'] ?? 'unknown',
				'origin' => $originCoordinates,
				'destination' => $destinationCoordinates,
			]);
			return null;
		}

		return [$element['duration']['value'] ?? 0, $element['distance']['value'] ?? 0];
	}
}