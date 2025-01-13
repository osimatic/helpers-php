<?php

namespace Osimatic\Organization;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;

class VatNumber
{
	/**
	 * @param string $vatNumber
	 * @return string
	 */
	public static function format(string $vatNumber): string
	{
		// todo
		return $vatNumber;
	}

	/**
	 * @param string $vatNumber
	 * @param bool $checkValidity
	 * @return bool
	 */
	public static function check(string $vatNumber, bool $checkValidity=true): bool
	{
		if (empty($vatNumber)) {
			return false;
		}

		$originalVatNumber = $vatNumber;
		$countryCode = substr($vatNumber, 0, 2);
		$vatNumber = substr($vatNumber, 2);

		// Vérification de la syntaxe
		if (!preg_match('/^[A-Z]{2}$/', $countryCode) || !preg_match('/^[0-9A-Za-z\+\*\.]{2,12}$/', $vatNumber)) {
			return false;
		}

		// --- Vérification pour la France/Monaco ---
		if ('FR' === $countryCode) {
			// France
			if (strlen($vatNumber) === 11) {
				// Vérification du SIREN
				$siren = substr($vatNumber, 2);
				if (!Company::checkCompanyNumber('FR', $siren)) {
					return false;
				}
			}
			// Monaco
			elseif (strlen($vatNumber) === 9) {
				// Vérification du n°SSEE
				$siren = substr($vatNumber, 2);
			}
			else {
				return false;
			}

			// Vérification de la clef TVA
			$vatKey = (int) substr($vatNumber, 0, 2);
			$theoricalVatKey = ( ( ($siren % 97) * 3 ) + 12 ) % 97;
			return ($vatKey === $theoricalVatKey);
		}

		// Vérification de la validité
		if ($checkValidity) {
			return self::checkValidity($originalVatNumber);
		}

		return true;
	}

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
		catch (\SoapFault $e) {
			return false;
		}
		return true;

		/*
		// Méthode API REST : non activé car pose pb de requete concurrente et renvoie de false au lieu de true 3 fois sur 4
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