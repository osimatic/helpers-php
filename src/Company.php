<?php

namespace Osimatic\Helpers;

class Company
{
	/**
	 * @param string $companyName
	 * @return bool
	 */
	public static function checkCompanyName(string $companyName): bool
	{
		return preg_match('/^([0-9a-zA-Z\'&àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\.\(\)\s\/-]{3,100})+$/u', $companyName);
	}

	/**
	 * @param string $countryCode
	 * @param string $companyNumber
	 * @return bool
	 */
	public static function checkCompanyNumber(string $countryCode, string $companyNumber): bool
	{
		// ---------- FRANCE ----------
		if ('FR' === $countryCode) {
			// Vérification de la syntaxe du SIREN
			if (!preg_match('#^[0-9]{9}$#', $companyNumber)) {
				return false;
			}
			// Vérification de la validité du SIREN par la clé de contrôle, suivant l'algorithme de Luhn (clé "1-2").
			if (!Number::checkLuhn($companyNumber)) {
				return false;
			}
			return true;
		}

		return true;
	}

}