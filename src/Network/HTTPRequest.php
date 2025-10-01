<?php

namespace Osimatic\Network;

use Psr\Log\LoggerInterface;

/**
 * Class HTTPRequest
 * @package Osimatic\Helpers\Network
 */
class HTTPRequest
{
	/**
	 * Exécute une requête HTTP avec l'extension cURL de PHP.
	 * @param HTTPMethod $method méthod utilisée pour exécuter la requête
	 * @param string $url l'URL de la requête HTTP à exécuter
	 * @param array $queryParameters query parameter of request (key-value array)
	 * @param array $headers list of HTTP header fields
	 * @param array $options options :
	 *                         - time_out : Temps (en secondes) maximum autorisé pour l'exécution de la requête
	 *                         - user_agent : Chaîne de caractère "User-Agent" envoyée au serveur
	 *                         - user_password :
	 *                         - response_file :
	 * @param LoggerInterface|null $logger
	 * @return string|bool la réponse renvoyée par la requête après son exécution
	 * @link http://en.wikipedia.org/wiki/List_of_HTTP_header_fields
	 */
	public static function execute(HTTPMethod $method, string $url, array $queryParameters=[], array $headers=[], array $options=[], ?LoggerInterface $logger=null): string|bool
	{
		//trace('URL : '.$url);
		$ch = curl_init();

		// Configuration de l'URL
		if (HTTPMethod::GET === $method) {
			$url .= (!str_contains($url, '?') ? '?' : '') . '&' . http_build_query($queryParameters);
		}
		curl_setopt($ch, CURLOPT_URL, $url);

		// Configuration du protocole
		$ssl = str_starts_with($url, 'https://');
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
		if (HTTPMethod::GET !== $method) {
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
			curl_setopt($ch, CURLOPT_FILE, $responseFile);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
		}

		// Exécution de la requête
		$data = curl_exec($ch);

		// Récupération du code HTTP
		$httpResponseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		// Récupération de l'éventuelle erreur
		$requestError = curl_error($ch);

		// Fermeture de la connexion
		curl_close($ch);

		$logger?->info('HTTP response code: '.$httpResponseCode.' ; Response size: '.(null === $responseFile ? ($data !== false ? strlen($data) : '<null>') : filesize($responseFile)));

		// Récupération de l'éventuelle erreur
		if ($data === false) {
			$logger?->error('Erreur cURL request : '.$requestError);
			return false;
		}

		if (null === $responseFile) {
			return $data;
		}
		return true;
	}

	/**
	 * Parse raw HTTP request data
	 * Pass in $data as an array. This is done by reference to avoid copying the data around too much.
	 * Any files found in the request will be added by their field name to the $data['files'] array.
	 * @link http://stackoverflow.com/questions/5483851/manually-parse-raw-http-data-with-php/5488449#5488449
	 * @link http://www.chlab.ch/blog/archives/webdevelopment/manually-parse-raw-http-data-php
	 * @param array $data Empty array to fill with data
	 * @return array Associative array of request data
	 */
	public static function parseRawHttpRequestData(array $data = []): array
	{
		// read incoming data
		$input = file_get_contents('php://input');

		// grab multipart boundary from content type header
		preg_match('/boundary=(.*)$/', $_SERVER['CONTENT_TYPE'] ?? '', $matches);

		// content type is probably regular form-encoded
		if (!count($matches)) {
			// we expect regular puts to containt a query string containing data
			parse_str(urldecode($input), $data);
			return $data;
		}

		$boundary = $matches[1];

		// split content by boundary and get rid of last -- element
		$a_blocks = preg_split("/-+$boundary/", $input);
		array_pop($a_blocks);

		$keyValueStr = '';
		// loop data blocks
		foreach ($a_blocks as $block) {
			if (empty($block)) {
				continue;
			}

			// you'll have to var_dump $block to understand this and maybe replace \n or \r with a visible char

			// parse uploaded files
			if (str_contains($block, 'application/octet-stream')) {
				// match "name", then everything after "stream" (optional) except for prepending newlines
				preg_match('/name=\"([^\"]*)\".*stream[\n|\r]+([^\n\r].*)?$/s', $block, $matches);
				$data['files'][$matches[1]] = $matches[2];
			}
			// parse all other fields
			else {
				// match "name" and optional value in between newline sequences
				preg_match('/name=\"([^\"]*)\"[\n|\r]+([^\n\r].*)?\r$/s', $block, $matches);
				if (count($matches)) {
					$keyValueStr .= $matches[1]."=".$matches[2]."&";
				}
			}
		}
		$keyValueArr = [];
		parse_str($keyValueStr, $keyValueArr);
		return array_merge($data, $keyValueArr);
	}
}