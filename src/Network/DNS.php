<?php

namespace Osimatic\Helpers\Network;

class DNS
{

	// ========== Check ==========

	/**
	 * Vérifie la syntaxe d'un nom de domaine
	 * @param string $dns le nom de domaine à vérifier
	 * @return boolean true si le nom de domaine est syntaxiquement correcte, false sinon
	 */
	public static function check(string $dns): bool
	{
		//return filter_var($url, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME);

		return preg_match('/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/', $dns) === 1;

		/*
		$validator = \Symfony\Component\Validator\Validation::createValidatorBuilder()
			->addMethodMapping('loadValidatorMetadata')
			->getValidator();
		return $validator->validate($dns, new \Symfony\Component\Validator\Constraints\Hostname())->count() === 0;
		*/
	}

	// ========== Components of DNS ==========

	/**
	 * Retourne le domaine de premier niveau contenu dans un nom de domaine, avec éventuellement le séparateur "."
	 * @param string $dns le nom de domaine dans laquelle récupérer le domaine de premier niveau
	 * @param boolean $withPoint true pour ajouter le séparateur "." avant le domaine de premier niveau, false sinon (true par défaut)
	 * @return string le domaine de premier niveau contenu dans le nom de domaine
	 * @link https://en.wikipedia.org/wiki/List_of_Internet_top-level_domains
	 */
	public static function getTld(string $dns, bool $withPoint=true): string
	{
		$tld = substr($dns, strrpos($dns, '.'));

		if (!$withPoint) {
			$tld = substr($tld, 1);
		}

		return $tld;
		//return substr($host, strrpos($host, '.')+1);
	}

	/**
	 * Alias de la fonction DNS::getTld()
	 * @param string $dns
	 * @return string
	 */
	public static function getTopLevelDomain(string $dns): string
	{
		return self::getTld($dns);
	}

	/**
	 * Retourne le domaine de deuxième niveau contenu dans un nom de domaine, avec éventuellement le domaine de premier niveau
	 * @param string $dns le nom de domaine dans laquelle récupérer le domaine de deuxième niveau
	 * @param boolean $withTld true pour ajouter le domaine de premier niveau contenu dans le nom de domaine, false sinon (true par défaut)
	 * @return string le domaine de deuxième niveau contenu dans le nom de domaine
	 */
	public static function getSld(string $dns, bool $withTld=true): string
	{
		$hostWithoutTld = substr($dns, 0, strrpos($dns, '.'));
		if (strrpos($hostWithoutTld, '.')) {
			$sld = substr($hostWithoutTld, strrpos($hostWithoutTld, '.')+1);
		}
		else {
			$sld = $hostWithoutTld;
		}

		if ($withTld) {
			$sld .= self::getTld($dns);
		}

		return $sld;
	}

	/**
	 * Alias de la fonction DNS::getSld()
	 * @param string $dns
	 * @param bool $withTld
	 * @return string
	 */
	public static function getSecondLevelDomain(string $dns, bool $withTld=true): string
	{
		return self::getSld($dns, $withTld);
	}

}