<?php

namespace Osimatic\Helpers\Network;

/**
 * Class URL
 * @package Osimatic\Helpers\Network
 */
class URL
{
	// ========== Check ==========

	/**
	 * Vérifie la syntaxe d'une URL
	 * @param string $url l'URL à vérifier
	 * @return boolean true si l'URL est syntaxiquement correcte, false sinon
	 */
	public static function check(string $url): bool
	{
		return filter_var($url, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED);
	}

	// ========== Affichage ==========

	/**
	 * Formate une URL pour l'affichage en mettant en minuscule tous les caractères et en retirant éventuellement le protocole associé
	 * @param string $url l'URL à formater pour l'affichage
	 * @param boolean $withProtocole true pour laisser le protocole dans l'URL, false pour l'enlever (false par défaut)
	 * @return string l'URL formatée prête à être affichée
	 */
	public static function format(string $url, bool $withProtocole=false): string
	{
		if (!$withProtocole) {
			$url = substr($url, strpos($url, '://')+3);
		}
		if (substr($url, -1) == '/') { // todo : tester si contient path (dans ce cas ne pas retirer le dernier slash)
			$url = substr($url, 0, -1);
		}
		return self::toLowerCase($url);
	}

	/**
	 * Met uniquement le nom de domaine en minuscule (pas le chemin ni les arguments)
	 * @param  string $url URL à formater
	 * @return string l'URL formatée
	 */
	public static function toLowerCase(string $url): string
	{
		//Récupération de la position du premier "/" séparant la partie ndd (http://ndd.tld) du reste de l'url
		preg_match('/\w(\/)\w/', $url, $matches, PREG_OFFSET_CAPTURE);

		if (empty($matches)) {
			// i.e http://mon-site.fr[?arg=unArg]
			if (!strrpos($url, '?')) {
				return strtolower($url);
			}
			$tabUrl = explode('?', $url);
			return strtolower($tabUrl[0]) . '?' . $tabUrl[1];
		}

		// i.e http://mon-site.fr/categ1[/categ2?arg=unArg]
		// Reconstitution de l'url
		// Match[1][1] contient la position du "/"
		return strtolower(substr($url, 0, $matches[1][1])) . substr($url, $matches[1][1]);
	}

	// ========== Components of URL ==========

	/**
	 * Retourne le domaine de premier niveau contenu dans une URL, avec éventuellement le séparateur "."
	 * @param string $url l'URL dans laquelle récupérer le domaine de premier niveau
	 * @param boolean $withPoint true pour ajouter le séparateur "." avant le domaine de premier niveau, false sinon (true par défaut)
	 * @return string le domaine de premier niveau contenu dans l'URL
	 */
	public static function getTld(string $url, bool $withPoint=true): string
	{
		$host = self::getHost($url, false, true);

		return DNS::getTld($host, $withPoint);
	}

	/**
	 * Alias de la fonction URL::getTld()
	 * @param string $url
	 * @return string
	 */
	public static function getTopLevelDomain(string $url): string
	{
		return self::getTld($url);
	}

	/**
	 * Retourne le domaine de deuxième niveau contenu dans une URL, avec éventuellement le domaine de premier niveau
	 * @param string $url l'URL dans laquelle récupérer le domaine de deuxième niveau
	 * @param boolean $withTld true pour ajouter le domaine de premier niveau contenu dans l'URL, false sinon (true par défaut)
	 * @return string le domaine de deuxième niveau contenu dans l'URL
	 */
	public static function getSld(string $url, bool $withTld=true): string
	{
		$host = self::getHost($url, false, true);

		return DNS::getSld($host);
	}

	/**
	 * Alias de la fonction URL::getSld()
	 * @param string $url
	 * @param bool $withTld
	 * @return string
	 */
	public static function getSecondLevelDomain(string $url, bool $withTld=true): string
	{
		return self::getSld($url, $withTld);
	}

	/**
	 * Retourne le protocole contenu dans une URL, avec éventuellement l'ajout du séparateur "://"
	 * @param string $url l'URL dans laquelle récupérer le protocole
	 * @param boolean $withSchemeSeparator true pour ajouter le séparateur "://" après le protocole, false sinon (true par défaut)
	 * @param boolean $returnEmptyStringIfNoScheme true pour returner une chaîne vide si le protocole n'est pas présent dans l'URL, false pour retourner le protocole HTTP par défaut si le protocole n'est pas présent (false par défaut)
	 * @return string le protocole contenu dans l'URL
	 */
	public static function getScheme(string $url, bool $withSchemeSeparator=true, bool $returnEmptyStringIfNoScheme=false): string
	{
		$tabInfosUrl = parse_url($url);
		$scheme = $tabInfosUrl['scheme'] ?? '';

		if (empty($scheme)) {
			if ($returnEmptyStringIfNoScheme) {
				return '';
			}
			$scheme = 'http';
		}

		if ($withSchemeSeparator && strstr($scheme, '://') === false) {
			$scheme .= '://';
		}
		return $scheme;
	}


	/**
	 * Retourne le nom de domaine contenu dans une URL, avec éventuellement l'ajout ou la suppression du caractère "/" à la fin
	 * @param string $url l'URL dans laquelle récupérer le nom de domaine
	 * @param boolean $addSlashAtEnd true pour ajouter (s'il n'est pas présent) le caractère "/" à la fin du nom de domaine, false sinon (true par défaut)
	 * @param boolean $deleteSlashAtEnd true pour enlever (s'il est présent) le caractère "/" à la fin du nom de domaine, false sinon (false par défaut)
	 * @return string le nom de domaine contenu dans l'URL (ou dans la chaîne de caractère)
	 */
	public static function getHost(string $url, bool $addSlashAtEnd=true, bool $deleteSlashAtEnd=false): string
	{
		$tabInfosUrl = parse_url($url);
		$host = $tabInfosUrl['host'] ?? '';
		if ($addSlashAtEnd && substr($host, -1) != '/') {
			$host .= '/';
		}
		if ($deleteSlashAtEnd && substr($host, -1) == '/') {
			$host = substr($host, 0, -1);
		}
		return $host;
	}

	/**
	 * Retourne le port contenu dans une URL, avec éventuellement l'ajout du séparateur ":" à la fin
	 * @param string $url l'URL dans laquelle récupérer le port
	 * @return string|null le port contenu dans l'URL (ou dans la chaîne de caractère)
	 */
	public static function getPort(string $url): ?string
	{
		$tabInfosUrl = parse_url($url);
		$port = $tabInfosUrl['port'] ?? '';
		if (empty($port)) {
			return null;
		}
		return $port;
	}

	/**
	 * Retourne le chemin contenu dans une URL, avec éventuellement l'ajout ou la suppression du caractère "/" au tout début
	 * @param string $url l'URL dans laquelle récupérer le chemin
	 * @param boolean $addSlashAtBeginning true pour ajouter (s'il n'est pas présent) le caractère "/" au début du chemin, false sinon (true par défaut)
	 * @param boolean $deleteSlashAtBeginning true pour enlever (s'il est présent) le caractère "/" au début du chemin, false sinon (false par défaut)
	 * @return string le chemin contenu dans l'URL
	 */
	public static function getPath(string $url, bool $addSlashAtBeginning=true, bool $deleteSlashAtBeginning=false): string
	{
		$tabInfosUrl = parse_url($url);
		$path = $tabInfosUrl['path'] ?? '';

		if ($addSlashAtBeginning && substr($path, 0, 1) != '/') {
			$path = '/'.$path;
		}
		if ($deleteSlashAtBeginning && substr($path, 0, 1) == '/') {
			$path = substr($path, 1);
		}
		return $path;
	}

	/**
	 * Retourne le nom de fichier (sans son chemin) dans une URL, avec éventuellement l'ajout ou la suppression du caractère "/" au tout début
	 * @param string $url l'URL dans laquelle récupérer le nom de fichier
	 * @return string le nom de fichier (sans son chemin) contenu dans l'URL
	 */
	public static function getFile(string $url): string
	{
		$path = self::getPath($url, true);
		if (strrpos($path, '/') !== false) {
			$nomFichier = substr($path, strrpos($path, '/')+1);
		}
		return $nomFichier;
	}


	/**
	 * Retourne les paramètres GET (sous forme de chaîne de caractère ou de tableau) dans une URL, avec éventuellement l'ajout du caractère "?" au tout début
	 * @param string $url l'URL dans laquelle récupérer la chaîne de caractère correspondant aux paramètres GET
	 * @param boolean $withQueryStringSeparator true pour ajout le séparateur "?" au tout début, false sinon (true par défaut)
	 * @param boolean $withQueryStringSeparatorIfEmptyQueryString true pour ajout le séparateur "?" au tout début même s'il y a aucun paramètre GET, false sinon (false par défaut)
	 * @return string la chaîne de caractère correspondant aux paramètres GET contenu dans l'URL ou un  tableau associatif avec en clé le nom du paramètre et en valeur la valeur du paramètre
	 */
	public static function getQueryString(string $url, bool $withQueryStringSeparator=false, bool $withQueryStringSeparatorIfEmptyQueryString=false): string
	{
		$tabInfosUrl = parse_url($url);
		$queryString = $tabInfosUrl['query'] ?? '';

		if ($withQueryStringSeparator) {
			if (empty($queryString)) {
				$queryString = ($withQueryStringSeparatorIfEmptyQueryString?'?':'');
			}
			else {
				$queryString = '?'.$queryString;
			}
		}

		return $queryString;
	}


}