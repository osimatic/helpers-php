<?php

declare(strict_types=1);

namespace Tests\Location;

use GuzzleHttp\Psr7\Response;
use Osimatic\Location\GoogleMaps;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;

final class GoogleMapsTest extends TestCase
{
	private const string TEST_API_KEY = 'test-google-maps-api-key';
	private const string TEST_ADDRESS = '10 Downing Street, London, UK';
	private const string TEST_COORDINATES = '51.5033635,-0.1276248';
	private const float TEST_LATITUDE = 51.5033635;
	private const float TEST_LONGITUDE = -0.1276248;

	/**
	 * Helper method to create a PSR-7 Response with JSON body
	 * @param array $data Data to encode as JSON
	 * @param int $statusCode HTTP status code
	 * @return Response PSR-7 Response instance
	 */
	private function createJsonResponse(array $data, int $statusCode = 200): Response
	{
		return new Response($statusCode, ['Content-Type' => 'application/json'], json_encode($data));
	}

	/**
	 * Helper method to create a successful geocoding response
	 * @param string $address The address
	 * @param float $lat Latitude
	 * @param float $lng Longitude
	 * @return array The geocoding response array
	 */
	private function createGeocodingResponse(string $address, float $lat, float $lng): array
	{
		return [
			'status' => 'OK',
			'results' => [
				[
					'formatted_address' => $address,
					'geometry' => [
						'location' => [
							'lat' => $lat,
							'lng' => $lng
						]
					],
					'address_components' => [
						[
							'long_name' => '10',
							'short_name' => '10',
							'types' => ['street_number']
						],
						[
							'long_name' => 'Downing Street',
							'short_name' => 'Downing St',
							'types' => ['route']
						],
						[
							'long_name' => 'London',
							'short_name' => 'London',
							'types' => ['locality', 'political']
						],
						[
							'long_name' => 'SW1A 2AA',
							'short_name' => 'SW1A 2AA',
							'types' => ['postal_code']
						],
						[
							'long_name' => 'United Kingdom',
							'short_name' => 'GB',
							'types' => ['country', 'political']
						]
					]
				]
			]
		];
	}

	/* ===================== Constants ===================== */

	public function testApiUrlConstant(): void
	{
		self::assertSame('https://maps.googleapis.com/maps/api/', GoogleMaps::API_URL);
	}

	/* ===================== Constructor ===================== */

	public function testConstructorWithAllParameters(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$httpClient = $this->createMock(ClientInterface::class);
		$googleMaps = new GoogleMaps(self::TEST_API_KEY, logger: $logger, httpClient: $httpClient);

		self::assertInstanceOf(GoogleMaps::class, $googleMaps);
	}

	public function testConstructorWithMinimalParameters(): void
	{
		$googleMaps = new GoogleMaps();

		self::assertInstanceOf(GoogleMaps::class, $googleMaps);
	}

	public function testConstructorWithCustomCacheTtl(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$googleMaps = new GoogleMaps(self::TEST_API_KEY, httpClient: $httpClient, cacheTtl: 7200);

		self::assertInstanceOf(GoogleMaps::class, $googleMaps);
	}

	/* ===================== Setters ===================== */

	public function testSetApiKey(): void
	{
		$googleMaps = new GoogleMaps();

		$result = $googleMaps->setApiKey('new-api-key');

		self::assertSame($googleMaps, $result);
	}

	public function testFluentInterface(): void
	{
		$googleMaps = new GoogleMaps();

		$result = $googleMaps->setApiKey(self::TEST_API_KEY);

		self::assertSame($googleMaps, $result);
	}

	/* ===================== Static method: getUrl() ===================== */

	public function testGetUrl(): void
	{
		$url = GoogleMaps::getUrl(self::TEST_COORDINATES);

		self::assertStringContainsString('maps.google.com', $url);
		self::assertStringContainsString('51.5033635', $url);
		self::assertStringContainsString('-0.1276248', $url);
	}

	/* ===================== geocoding() ===================== */

	public function testGeocodingWithoutApiKey(): void
	{
		$googleMaps = new GoogleMaps();

		$result = $googleMaps->geocoding(self::TEST_ADDRESS);

		self::assertNull($result);
	}

	public function testGeocodingWithValidApiKey(): void
	{
		$responseData = $this->createGeocodingResponse(self::TEST_ADDRESS, self::TEST_LATITUDE, self::TEST_LONGITUDE);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $googleMaps->geocoding(self::TEST_ADDRESS);

		self::assertNotNull($result);
		self::assertIsArray($result);
		self::assertCount(1, $result);
	}

	public function testGeocodingVerifiesRequestContainsAddress(): void
	{
		$responseData = $this->createGeocodingResponse(self::TEST_ADDRESS, self::TEST_LATITUDE, self::TEST_LONGITUDE);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) {
				$uri = (string) $request->getUri();
				return str_contains($uri, 'geocode/json')
					&& str_contains($uri, 'key=' . self::TEST_API_KEY)
					&& str_contains($uri, 'address=');
			}))
			->willReturn($this->createJsonResponse($responseData));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $googleMaps->geocoding(self::TEST_ADDRESS);

		self::assertNotNull($result);
	}

	public function testGeocodingWithNetworkException(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willThrowException(new class('Network error') extends \RuntimeException implements ClientExceptionInterface {});

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $googleMaps->geocoding(self::TEST_ADDRESS);

		self::assertNull($result);
	}

	public function testGeocodingWithRequestDenied(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with(self::stringContains('Access denied'));

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'status' => 'REQUEST_DENIED',
				'error_message' => 'The provided API key is invalid.'
			]));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, logger: $logger, httpClient: $httpClient);

		$result = $googleMaps->geocoding(self::TEST_ADDRESS);

		self::assertNull($result);
	}

	public function testGeocodingWithOverQueryLimit(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with(self::stringContains('Quota exceeded'));

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'status' => 'OVER_QUERY_LIMIT',
				'error_message' => 'You have exceeded your daily request quota.'
			]));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, logger: $logger, httpClient: $httpClient);

		$result = $googleMaps->geocoding(self::TEST_ADDRESS);

		self::assertNull($result);
	}

	public function testGeocodingWithInvalidRequest(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with(self::stringContains('Invalid request'));

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'status' => 'INVALID_REQUEST',
				'error_message' => 'Invalid request'
			]));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, logger: $logger, httpClient: $httpClient);

		$result = $googleMaps->geocoding(self::TEST_ADDRESS);

		self::assertNull($result);
	}

	public function testGeocodingWithZeroResults(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('info')
			->with('No results found.');

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'status' => 'ZERO_RESULTS'
			]));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, logger: $logger, httpClient: $httpClient);

		$result = $googleMaps->geocoding('NonexistentPlace123');

		self::assertNull($result);
	}

	/* ===================== Cache tests ===================== */

	public function testGeocodingUsesCache(): void
	{
		$responseData = $this->createGeocodingResponse(self::TEST_ADDRESS, self::TEST_LATITUDE, self::TEST_LONGITUDE);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once()) // Only called once, second call uses cache
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, httpClient: $httpClient);

		// First call - hits API
		$result1 = $googleMaps->geocoding(self::TEST_ADDRESS);
		// Second call - uses cache
		$result2 = $googleMaps->geocoding(self::TEST_ADDRESS);

		self::assertNotNull($result1);
		self::assertNotNull($result2);
		self::assertSame($result1, $result2);
	}

	public function testClearCache(): void
	{
		$responseData = $this->createGeocodingResponse(self::TEST_ADDRESS, self::TEST_LATITUDE, self::TEST_LONGITUDE);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::exactly(2)) // Called twice after cache clear
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, httpClient: $httpClient, cacheTtl: 3600);

		// First call - hits API
		$result1 = $googleMaps->geocoding(self::TEST_ADDRESS);

		// Clear cache
		$googleMaps->clearCache();

		// Second call - hits API again since cache was cleared
		$result2 = $googleMaps->geocoding(self::TEST_ADDRESS);

		self::assertNotNull($result1);
		self::assertNotNull($result2);
	}

	/* ===================== reverseGeocoding() ===================== */

	public function testReverseGeocodingWithoutApiKey(): void
	{
		$googleMaps = new GoogleMaps();

		$result = $googleMaps->reverseGeocoding(self::TEST_COORDINATES);

		self::assertNull($result);
	}

	public function testReverseGeocodingWithValidCoordinates(): void
	{
		$responseData = $this->createGeocodingResponse(self::TEST_ADDRESS, self::TEST_LATITUDE, self::TEST_LONGITUDE);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $googleMaps->reverseGeocoding(self::TEST_COORDINATES);

		self::assertNotNull($result);
		self::assertIsArray($result);
	}

	public function testReverseGeocodingFromLatitudeAndLongitude(): void
	{
		$responseData = $this->createGeocodingResponse(self::TEST_ADDRESS, self::TEST_LATITUDE, self::TEST_LONGITUDE);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->with(self::callback(function ($request) {
				$uri = (string) $request->getUri();
				return str_contains($uri, 'latlng=');
			}))
			->willReturn($this->createJsonResponse($responseData));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $googleMaps->reverseGeocodingFromLatitudeAndLongitude(self::TEST_LATITUDE, self::TEST_LONGITUDE);

		self::assertNotNull($result);
	}

	/* ===================== getCoordinatesFromAddress() ===================== */

	public function testGetCoordinatesFromAddress(): void
	{
		$responseData = $this->createGeocodingResponse(self::TEST_ADDRESS, self::TEST_LATITUDE, self::TEST_LONGITUDE);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $googleMaps->getCoordinatesFromAddress(self::TEST_ADDRESS);

		self::assertNotNull($result);
		self::assertIsString($result);
		self::assertStringContainsString('51.5033635', $result);
		self::assertStringContainsString('-0.1276248', $result);
	}

	public function testGetCoordinatesFromAddressReturnsNullOnError(): void
	{
		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse([
				'status' => 'ZERO_RESULTS'
			]));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $googleMaps->getCoordinatesFromAddress('Invalid Address');

		self::assertNull($result);
	}

	/* ===================== getFormattedAddressFromAddress() ===================== */

	public function testGetFormattedAddressFromAddress(): void
	{
		$responseData = $this->createGeocodingResponse(self::TEST_ADDRESS, self::TEST_LATITUDE, self::TEST_LONGITUDE);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $googleMaps->getFormattedAddressFromAddress(self::TEST_ADDRESS);

		self::assertNotNull($result);
		self::assertIsString($result);
	}

	/* ===================== getFormattedAddressFromCoordinates() ===================== */

	public function testGetFormattedAddressFromCoordinates(): void
	{
		$responseData = $this->createGeocodingResponse(self::TEST_ADDRESS, self::TEST_LATITUDE, self::TEST_LONGITUDE);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $googleMaps->getFormattedAddressFromCoordinates(self::TEST_COORDINATES);

		self::assertNotNull($result);
		self::assertIsString($result);
	}

	/* ===================== Static methods ===================== */

	public function testGetCoordinatesFromResult(): void
	{
		$result = [
			'geometry' => [
				'location' => [
					'lat' => 48.8566,
					'lng' => 2.3522
				]
			]
		];

		$coordinates = GoogleMaps::getCoordinatesFromResult($result);

		self::assertNotNull($coordinates);
		self::assertStringContainsString('48.8566', $coordinates);
		self::assertStringContainsString('2.3522', $coordinates);
	}

	public function testGetCoordinatesFromResultReturnsNullWithMissingData(): void
	{
		$result = ['geometry' => []];

		$coordinates = GoogleMaps::getCoordinatesFromResult($result);

		self::assertNull($coordinates);
	}

	public function testGetFormattedAddressFromResult(): void
	{
		$result = [
			'formatted_address' => '10 Downing Street, London SW1A 2AA, UK'
		];

		$formattedAddress = GoogleMaps::getFormattedAddressFromResult($result);

		self::assertNotNull($formattedAddress);
		self::assertIsString($formattedAddress);
	}

	public function testGetFormattedAddressFromResultReturnsNullWithMissingData(): void
	{
		$result = [];

		$formattedAddress = GoogleMaps::getFormattedAddressFromResult($result);

		self::assertNull($formattedAddress);
	}

	public function testGetAddressComponentsFromResult(): void
	{
		$result = [
			'formatted_address' => '10 Downing Street, London',
			'address_components' => [
				[
					'long_name' => '10',
					'short_name' => '10',
					'types' => ['street_number']
				],
				[
					'long_name' => 'Downing Street',
					'short_name' => 'Downing St',
					'types' => ['route']
				],
				[
					'long_name' => 'London',
					'short_name' => 'London',
					'types' => ['locality']
				],
				[
					'long_name' => 'SW1A 2AA',
					'short_name' => 'SW1A 2AA',
					'types' => ['postal_code']
				],
				[
					'long_name' => 'United Kingdom',
					'short_name' => 'GB',
					'types' => ['country']
				]
			]
		];

		$addressComponents = GoogleMaps::getAddressComponentsFromResult($result);

		self::assertNotNull($addressComponents);
		self::assertIsArray($addressComponents);
		self::assertArrayHasKey('street', $addressComponents);
		self::assertArrayHasKey('locality', $addressComponents);
		self::assertArrayHasKey('postal_code', $addressComponents);
		self::assertArrayHasKey('country', $addressComponents);
		self::assertArrayHasKey('country_code', $addressComponents);
		self::assertSame('London', $addressComponents['locality']);
		self::assertSame('SW1A 2AA', $addressComponents['postal_code']);
		self::assertSame('GB', $addressComponents['country_code']);
	}

	public function testGetAddressComponentsFromResultReturnsNullWithMissingData(): void
	{
		$result = [];

		$addressComponents = GoogleMaps::getAddressComponentsFromResult($result);

		self::assertNull($addressComponents);
	}

	/* ===================== Credentials Validation ===================== */

	public function testGeocodingLogsErrorWithoutApiKey(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$logger->expects(self::once())
			->method('error')
			->with('Google Maps API key is not configured. Please set API key.');

		$googleMaps = new GoogleMaps(logger: $logger);

		$result = $googleMaps->geocoding(self::TEST_ADDRESS);

		self::assertNull($result);
	}

	/* ===================== Special characters handling ===================== */

	public function testGeocodingWithSpecialCharacters(): void
	{
		$addressWithSpecialChars = "Rue de l'Église, Paris";
		$responseData = $this->createGeocodingResponse($addressWithSpecialChars, 48.8566, 2.3522);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$googleMaps = new GoogleMaps(self::TEST_API_KEY, httpClient: $httpClient);

		$result = $googleMaps->geocoding($addressWithSpecialChars);

		self::assertNotNull($result);
	}

	/* ===================== Workflow tests ===================== */

	public function testCompleteGeocodingWorkflow(): void
	{
		$responseData = $this->createGeocodingResponse(self::TEST_ADDRESS, self::TEST_LATITUDE, self::TEST_LONGITUDE);

		$httpClient = $this->createMock(ClientInterface::class);
		$httpClient->expects(self::once())
			->method('sendRequest')
			->willReturn($this->createJsonResponse($responseData));

		$googleMaps = new GoogleMaps(httpClient: $httpClient);
		$googleMaps->setApiKey(self::TEST_API_KEY);

		$results = $googleMaps->geocoding(self::TEST_ADDRESS);
		$coordinates = GoogleMaps::getCoordinatesFromResult($results[0]);
		$formattedAddress = GoogleMaps::getFormattedAddressFromResult($results[0]);

		self::assertNotNull($results);
		self::assertNotNull($coordinates);
		self::assertNotNull($formattedAddress);
	}
}