<?php

namespace Osimatic\Organization;

/**
 * Class VatNumber
 * Provides utilities for VAT number validation and formatting
 */
class VatNumber
{
	/**
	 * Formats a VAT number for display
	 * @param string $vatNumber the VAT number to format
	 * @return string the formatted VAT number
	 * @todo implement VAT number formatting logic
	 */
	public static function format(string $vatNumber): string
	{
		// todo
		return $vatNumber;
	}

	/**
	 * Validates a VAT number syntax and optionally checks validity via VIES
	 * @param string $vatNumber the VAT number to check (including country code)
	 * @param bool $checkValidity whether to verify with VIES service (default: true)
	 * @return bool true if valid, false otherwise
	 */
	public static function check(string $vatNumber, bool $checkValidity=true): bool
	{
		if (empty($vatNumber)) {
			return false;
		}

		$originalVatNumber = $vatNumber;
		$countryCode = substr($vatNumber, 0, 2);
		$vatNumber = substr($vatNumber, 2);

		// Syntax validation
		if (!preg_match('/^[A-Z]{2}$/', $countryCode) || !preg_match('/^[0-9A-Za-z\+\*\.]{2,12}$/', $vatNumber)) {
			return false;
		}

		// --- France/Monaco specific validation ---
		if ('FR' === $countryCode) {
			// France
			if (strlen($vatNumber) === 11) {
				// SIREN validation
				$siren = substr($vatNumber, 2);
				if (!Company::checkCompanyNumber('FR', $siren)) {
					return false;
				}
			}
			// Monaco
			elseif (strlen($vatNumber) === 9) {
				// SSEE number validation
				$siren = substr($vatNumber, 2);
			}
			else {
				return false;
			}

			// VAT key validation
			$vatKey = (int) substr($vatNumber, 0, 2);
			$theoricalVatKey = ( ( ($siren % 97) * 3 ) + 12 ) % 97;
			return ($vatKey === $theoricalVatKey);
		}

		// Validity check via VIES service
		if ($checkValidity) {
			return self::checkValidity($originalVatNumber);
		}

		return true;
	}

	/**
	 * Validates a VAT number using the EU VIES (VAT Information Exchange System) service
	 * @param string $vatNumber the VAT number to validate (including country code)
	 * @return bool true if valid according to VIES, false otherwise
	 */
	public static function checkValidity(string $vatNumber): bool
	{
		$countryCode = substr($vatNumber, 0, 2);
		$vatNumber = substr($vatNumber, 2);

		try {
			$client = new \SoapClient('http://ec.europa.eu/taxation_customs/vies/checkVatService.wsdl');
			$response = $client->checkVat(['countryCode' => $countryCode, 'vatNumber' => $vatNumber]);
			if (!$response->valid) {
				return false;
			}
		}
		catch (\SoapFault) {
			return false;
		}
		return true;

		/*
		// REST API method: disabled due to concurrent request issues and false negatives
		$url = 'https://ec.europa.eu/taxation_customs/vies/rest-api/check-vat-number';
		$body = [
			'countryCode' => $countryCode,
			'vatNumber' => $vatNumber,
		];

		$httpClient = new HTTPClient();
		$json = $httpClient->jsonRequest(HTTPMethod::POST, $url, $body, [], true);
		if (null === $json) {
			return false;
		}

		return $json['valid'] ?? false;
		*/
	}


}