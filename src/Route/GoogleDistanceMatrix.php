<?php

namespace Osimatic\Helpers\Route;

use Osimatic\Helpers\Network\HTTPRequest;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class GoogleDistanceMatrix
{
	public function __construct(
		private ?string $apiKey=null,
		private LoggerInterface $logger=new NullLogger(),
	) {}

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
	 * Retourne durée en seconde et distance en mètres
	 * @param string $originCoordinates
	 * @param string $destinationCoordinates
	 * @param TravelMode $travelMode
	 * @param GoogleDistanceMatrixParameters $parameters
	 * @return array|null
	 */
	public function getDistanceMatrix(string $originCoordinates, string $destinationCoordinates, TravelMode $travelMode = TravelMode::DRIVE, GoogleDistanceMatrixParameters $parameters = new GoogleDistanceMatrixParameters()): ?array
	{
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
			return null;
		}

		$params['avoid'] = [];

		if (TravelMode::DRIVE === $travelMode) {
			if ($parameters->isAvoidTolls()) {
				$params['avoid'][] = 'tolls';
			}
			if ($parameters->isAvoidHighways()) {
				$params['avoid'][] = 'highways';
			}
			if ($parameters->isAvoidFerries()) {
				$params['avoid'][] = 'ferries';
			}
		}

		if (TravelMode::WALK === $travelMode) {
			if ($parameters->isAvoidIndoor()) {
				$params['avoid'][] = 'indoor';
			}
		}

		if (TravelMode::TRANSIT === $travelMode && !empty($transitModes = $parameters->getTransitModes())) {
			$params['transit_mode'] = implode('|', array_values(array_filter(array_map(fn(TransitTravelMode $transitMode) => match ($travelMode) {
				TransitTravelMode::BUS => 'bus',
				TransitTravelMode::SUBWAY => 'subway',
				TransitTravelMode::TRAIN => 'train',
				TransitTravelMode::LIGHT_RAIL => 'tram',
				default => null
			}, $transitModes))));
		}

		$url = 'https://maps.googleapis.com/maps/api/distancematrix/json?'.http_build_query($params).'&key='.$this->apiKey;

		if (null === ($json = HTTPRequest::getAndDecodeJson($url, [], $this->logger))) {
			return null;
		}

		if (empty($json['status']) || $json['status'] !== 'OK') {
			return null;
		}

		$element = $json['rows'][0]['elements'][0];

		if (empty($element['status']) || $element['status'] !== 'OK') {
			return null;
		}

		return [$element['duration']['value'] ?? 0, $element['distance']['value'] ?? 0];
	}
}