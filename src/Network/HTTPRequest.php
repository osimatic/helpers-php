<?php

namespace Osimatic\Helpers\Network;

/**
 * Class HTTPRequest
 * @package Osimatic\Helpers\Network
 */
class HTTPRequest
{
	/**
	 * Exécute une requête HTTP avec l'extension cURL de PHP.
	 * @param string $url l'URL de la requête HTTP à exécuter
	 * @param string $method méthod utilisée pour exécuter la requête
	 * @param array $queryParameters query parameter of request (key-value array)
	 * @param array $headers list of HTTP header fields
	 * @param array $options options :
	 * 						 - time_out : Temps (en secondes) maximum autorisé pour l'exécution de la requête
	 * 						 - user_agent : Chaîne de caractère "User-Agent" envoyée au serveur
	 * 						 - user_password :
	 * 						 - response_file :
	 * @link http://en.wikipedia.org/wiki/List_of_HTTP_header_fields
	 * @return mixed la réponse renvoyée par la requête après son exécution
	 */
	public static function execute($url, string $method='GET', array $queryParameters=[], array $headers=[], array $options=[])
	{
		//trace('URL : '.$url);

		$ch = curl_init();

		// Configuration de l'URL
		if ($method === 'GET') {
			$url .= (strstr($url, '?') === false ? '?' : '') . '&' . http_build_query($queryParameters);
		}
		curl_setopt($ch, CURLOPT_URL, $url);

		// Configuration du protocole
		$ssl = strpos($url, 'https://') === 0;
		if ($ssl) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
			curl_setopt($ch, CURLOPT_PORT, 443);
		}

		// Configuration de l'authentification HTTP
		if (!empty($options['user_password'])) {
			curl_setopt($ch, CURLOPT_USERPWD, $options['user_password']);
		}

		// Configuration de l'user-agent
		if (!empty($options['user_agent'])) {
			curl_setopt($ch, CURLOPT_USERAGENT, $options['user_agent']);
		}

		// Configuration du timeout
		if (null !== ($options['time_out'] ?? null)) {
			curl_setopt($ch, CURLOPT_TIMEOUT, $options['time_out']);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options['time_out']);
		}

		// Configuration des variables POST
		if ($method === 'POST') {
			curl_setopt($ch, CURLOPT_POST, TRUE);
			if ($ssl) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($queryParameters));
			}
			else {
				curl_setopt($ch, CURLOPT_POSTFIELDS, $queryParameters);
			}
		}

		// Configuration des variables HEADER
		if (!empty($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		// Configuration des redirections
		// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		// curl_setopt($ch, CURLOPT_MAXREDIRS, 20);

		//if ($withCookie) {
		//	curl_setopt($ch, CURLOPT_COOKIESESSION, true);
		//	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		//	curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		//	curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		//}

		// Configuration du corps de la requête
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		// Configuration du fichier pour l'enregistrement de la réponse
		if (null !== ($responseFile = $options['response_file'] ?? null)) {
			//trace('Le résultat sera placé dans le fichier "'.$cheminFichierReponse.'"');
			curl_setopt($ch, CURLOPT_FILE, $responseFile);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
		}

		// Configuration du mode de transmission de la réponse
		if (true === ($options['binary_transfer'] ?? false)) {
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		}

		// Exécution de la requête
		$data = curl_exec($ch);

		// Récupération de l'éventuelle erreur
		if ($data === false) {
			$requestError = curl_error($ch);
			//trace('Erreur request : '.$requestError);
			return false;
		}

		// Récupération du code HTTP
		$httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// Fermeture de la connexion
		curl_close($ch);

		//trace('Exec ok - Code HTTP : '.$httpResponseCode.' ; Temps exec : '.$tempsExecutionRequete.' secondes');
		//if ($responseFile === null) {
		//	trace('Longueur resultat : '.strlen($data));
		//}
		//else {
		//	trace('Taille du fichier : '.filesize($responseFile));
		//}

		if ($responseFile === null) {
			return $data;
		}
		return true;
	}

	/**
	 * Test la réponse d'une requête HTTP avec l'extension cURL de PHP.
	 * @param string $url l'URL de la requête HTTP à tester
	 * @param array $headers list of HTTP header fields
	 * @param array $options options
	 * @return boolean
	 */
	public static function check($url, array $headers=[], array $options=[]): bool
	{
		//trace('URL : '.$url);

		// Configuration de l'URL
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);

		// Configuration du protocole
		if (strpos($url, 'https://') === 0) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
		}

		// Configuration de l'authentification HTTP
		if (!empty($options['user_password'])) {
			curl_setopt($ch, CURLOPT_USERPWD, $options['user_password']);
		}

		// Configuration de l'user-agent
		if (!empty($options['user_agent'])) {
			curl_setopt($ch, CURLOPT_USERAGENT, $options['user_agent']);
		}

		// Configuration du timeout
		if (null !== ($options['time_out'] ?? null)) {
			curl_setopt($ch, CURLOPT_TIMEOUT, $options['time_out']);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $options['time_out']);
		}

		// Configuration des variables HEADER
		if (!empty($headers)) {
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		}

		// Configuration des redirections
		// curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		// curl_setopt($ch, CURLOPT_MAXREDIRS, 20);

		// Configuration du corps de la requête
		curl_setopt($ch, CURLOPT_NOBODY, TRUE);

		// Exécution de la requête
		$data = curl_exec($ch);

		// Récupération du code HTTP
		$codeHttp = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// Fermeture de la connexion
		curl_close($ch);

		if ($data === false) {
			return false;
		}
		return in_array($codeHttp, [200, 301, 302], true);
	}

}