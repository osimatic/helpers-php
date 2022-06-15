<?php

namespace Osimatic\Helpers\Messaging;

/**
 * Class EmailAddress
 * @package Osimatic\Helpers\Messaging
 */
class EmailAddress
{

	// ========== Vérification ==========

	/**
	 * @param string $email l'adresse email à vérifier
	 * @return bool
	 */
	public static function check(string $email): bool
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);
	}

	// ========== Get element ==========

	/**
	 * Retourne le nom de domaine (fournisseur) contenu dans une adresse email
	 * @param string $email l'adresse email dans laquelle récupérer le nom de domaine (fournisseur)
	 * @return string|null le nom de domaine (fournisseur) contenu dans l'adresse email
	 */
	public static function getHost(string $email): ?string
	{
		// preg_replace('!^[a-z0-9._-]+@(.+)$!', '$1', $email)
		if (!str_contains($email, '@')) {
			return null;
		}
		return substr($email, (strpos($email, '@')+1));
	}

	/**
	 * Retourne le domaine de premier niveau contenu dans une adresse email, avec éventuellement le séparateur "."
	 * @param string $email l'adresse email dans laquelle récupérer le domaine de premier niveau
	 * @param boolean $withPoint true pour ajouter le séparateur "." avant le domaine de premier niveau, false sinon (true par défaut)
	 * @return string le domaine de premier niveau contenu dans l'adresse email
	 */
	public static function getTld(string $email, bool $withPoint=true): string
	{
		$host = self::getHost($email);
		return \Osimatic\Helpers\Network\URL::getTld($host, $withPoint);
	}



}