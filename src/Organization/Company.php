<?php

namespace Osimatic\Helpers\Organization;

class Company
{
	/**
	 * @param string $companyName
	 * @return bool
	 */
	public static function checkCompanyName(string $companyName): bool
	{
		return preg_match('/^([0-9a-zA-Z\'&àâäéèêëìîïòôöùûüçÀÂÄÉÈÊËÌÎÏÒÔÖÙÛÜÇ\.\(\)\s\/-]{3,100})$/u', $companyName);
	}

	/**
	 * @param string $countryCode
	 * @param string $companyNumber
	 * @return bool
	 */
	public static function checkCompanyNumber(string $countryCode, string $companyNumber): bool
	{
		// France
		if ('FR' === $countryCode) {
			return self::checkFranceSiren($companyNumber);
		}

		return true;
	}

	// ========== FRANCE ==========

	/**
	 * Vérifie la validité d'un SIREN (Système d’Identification du Répertoire des ENtreprises).
	 * Le numéro SIREN est composé de huit chiffres, plus un chiffre de contrôle qui permet de vérifier la validité du numéro.
	 * @link http://fr.wikipedia.org/wiki/SIREN
	 * @param string $siren
	 * @return bool
	 */
	public static function checkFranceSiren(string $siren): bool
	{
		// Vérification de la syntaxe du SIREN
		if (!preg_match('#^[0-9]{9}$#', $siren)) {
			return false;
		}
		// Vérification de la validité du SIREN par la clé de contrôle, suivant l'algorithme de Luhn (clé "1-2").
		return \Osimatic\Helpers\Number\Number::checkLuhn((int) $siren);
	}

	/**
	 * Vérifie la validité d'un SIRET (Système d’identification du répertoire des établissements) attribué pour les entreprises en France.
	 * Cet identifiant numérique de 14 chiffres est articulé en deux parties :
	 * - la première est le numéro SIREN de l'entreprise (ou unité légale ou personne juridique) à laquelle appartient l'unité SIRET ;
	 * - la seconde, appelée NIC (Numéro Interne de Classement), se compose d'un numéro d'ordre séquentiel à quatre chiffres attribué à l'établissement et d'un chiffre de contrôle (clé de contrôle), qui permet de vérifier la validité de l'ensemble du numéro SIRET.
	 * @link http://fr.wikipedia.org/wiki/SIRET
	 * @param string $siret
	 * @return bool
	 */
	public static function checkFranceSiret(string $siret): bool
	{
		// Vérification de la syntaxe du SIRET
		if (!preg_match('#^[0-9]{14}$#', $siret)) {
			return false;
		}
		// Vérification de la validité du SIREN
		$siren = substr($siret, 0, 9);
		if (!self::checkFranceSiren($siren)) {
			return false;
		}
		// Vérification de la validité du SIRET par la clé de contrôle, suivant l'algorithme de Luhn (clé "1-2").
		return \Osimatic\Helpers\Number\Number::checkLuhn((int) $siret);
	}

	/**
	 * Retourne la liste des code NAF selon la NAF 2008 (732 postes).
	 * @link http://fr.wikipedia.org/wiki/Code_NAF
	 * @return array la liste des codes NAF
	 */
	public static function getFranceApeCodeList(): array
	{
		return parse_ini_file(__DIR__.'/conf/france_code_naf.ini', true);
	}

	/**
	 * Vérifie la validité d'un code NAF ou APE
	 * @link http://fr.wikipedia.org/wiki/Code_APE
	 * @link https://fr.wikipedia.org/wiki/Activit%C3%A9_principale_exerc%C3%A9e
	 * @param string $codeApe
	 * @return bool
	 */
	public static function checkFranceCodeApe(string $codeApe): bool
	{
		return self::checkFranceCodeNaf($codeApe);
	}

	/**
	 * Vérifie la validité d'un code NAF
	 * @param string $codeNaf
	 * @return bool
	 */
	public static function checkFranceCodeNaf(string $codeNaf): bool
	{
		if (!preg_match('#^[0-9A-Za-z]{5}$#', $codeNaf)) {
			return false;
		}

		return array_key_exists($codeNaf, self::getFranceApeCodeList());
	}

	/**
	 * Retourne le libellé du code APE
	 * @param string $ape
	 * @return string
	 */
	public static function getFranceApeLabel(string $ape): string
	{
		return self::getFranceApeCodeList()[$ape] ?? '';
	}

	/**
	 * Formate un numéro SIRET pour être affiché en tant que RCS
	 * @link https://fr.wikipedia.org/wiki/Registre_du_commerce_et_des_soci%C3%A9t%C3%A9s_(France)
	 * @param string $siret
	 * @return string
	 */
	public static function formatFranceRcs(string $siret): string
	{
		$siren = substr($siret, 0, -5);
		return 'B '.chunk_split($siren, 3, ' ');
	}

	// ========== MONACO ==========

	/**
	 * Vérifie la validité d'un Numéro d’Identification Statistique (N.I.S.) attribué pour les entreprises à Monaco.
	 * @param string $nis
	 * @return bool
	 */
	public static function checkMonacoNis(string $nis): bool
	{
		return preg_match('#^[0-9A-Z]{5,10}$#', $nis);
	}

}