<?php

namespace Osimatic\Helpers\Bank;

class BillingTax
{
	/**
	 * @param string|null $country
	 * @param string|null $zipCode
	 * @param string|null $vatNumber
	 * @param string $billingCountry
	 * @return float
	 */
	public static function getBillingTaxRate(?string $country, ?string $zipCode, ?string $vatNumber, string $billingCountry='FR'): float
	{
		if (empty($country)) {
			$country = $billingCountry;
		}

		if (\Osimatic\Helpers\Location\Country::isCountryInFranceOverseas($country)) {
			$country = 'FR';
		}

		// TVA étranger
		if ($country !== $billingCountry) {
			if (false === \Osimatic\Helpers\Location\Country::isCountryInEuropeanUnion($billingCountry)) {
				// Pas de TVA car l'entité qui facture est hors UE.
				return 0.0;
			}

			// Le pays qui facture est dans l'UE.

			if (false === \Osimatic\Helpers\Location\Country::isCountryInEuropeanUnion($country)) {
				// Pas de TVA car le client est hors UE.
				return 0.0;
			}

			if (!empty($vatNumber)) {
				// Pas de TVA car le client est dans l'UE et numéro de TVA intracommunautaire renseigné.
				return 0.0;
			}

			// Le client est dans l'UE mais n'a pas renseigné son numéro de TVA. On applique donc la TVA du pays de l'entité qui facture.
			$country = $billingCountry;
		}

		// TVA France
		if ($country === 'FR') {
			// DOM-TOM
			if (!empty($zipCode)) {
				// Guyane / Mayotte
				if (in_array(substr($zipCode, 0, 3), ['973', '976'], true)) {
					// TVA de 0 car département de Guyane / Mayotte
					return 0.0;
				}
				// Guadeloupe / Martinique / La Réunion
				if (in_array(substr($zipCode, 0, 3), ['971', '972', '974'], true)) {
					// TVA de 8,5 car département de Guadeloupe / Martinique / La Réunion
					return 8.5;
				}
			}

			return 20.0;
		}

		// todo : autres pays

		return 0;
	}
}