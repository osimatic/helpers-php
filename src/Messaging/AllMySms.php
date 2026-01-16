<?php

namespace Osimatic\Messaging;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * AllMySms SMS gateway client for sending SMS messages.
 * This class implements the SmsSenderInterface and provides methods to send SMS messages through the AllMySms API service.
 */
class AllMySms implements SmsSenderInterface
{
	/**
	 * Construct a new AllMySms client instance.
	 * @param string $login The AllMySms account login
	 * @param string $apiKey The AllMySms API key for authentication
	 * @param LoggerInterface $logger The PSR-3 logger instance for debugging (default: NullLogger)
	 */
	public function __construct(
		private string $login,
		private string $apiKey,
		private LoggerInterface $logger=new NullLogger(),
	) {}

	/**
	 * Sets the logger for error and debugging information.
	 * @param LoggerInterface $logger The logger instance
	 * @return self Returns this instance for method chaining
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	/**
	 * Set the AllMySms account login.
	 * @param string $login The account login
	 * @return self Returns this instance for method chaining
	 */
	public function setLogin(string $login): self
	{
		$this->login = $login;

		return $this;
	}

	/**
	 * Set the AllMySms API key.
	 * @param string $apiKey The API key for authentication
	 * @return self Returns this instance for method chaining
	 */
	public function setApiKey(string $apiKey): self
	{
		$this->apiKey = $apiKey;

		return $this;
	}

	/**
	 * Get the base64-encoded authentication token for API requests.
	 * @return string The base64-encoded authentication token (login:apiKey)
	 */
	public function getAuthToken(): string
	{
		return base64_encode($this->login.':'.$this->apiKey);
	}

	/**
	 * Send an SMS message through the AllMySms API.
	 * @param SMS $sms The SMS message to send
	 * @throws \Exception If the SMS fails to send or API returns an error
	 */
	public function send(SMS $sms): void
	{
		/*
		$listSuccess = [
			100 => 'Le message a été envoyé',
			101 => 'Le message a été programmé pour un envoi différé',
		];

		$listError = [
			102 => 'Problème de connexion – Aucun compte ne correspond aux clientcode et passcode spécifiés',
			103 => 'Crédit SMS épuisé. Veuillez re-créditer votre compte sur AllMySMS.com',
			104 => 'Crédit insuffisant pour traiter cet envoi. A utiliser: XX Crédits, Disponibles: YY Crédits. Veuillez re-créditer votre compte sur AllMySMS.com',
			105 => 'Flux XML Vide',
			106 => 'Flux XML invalide ou incomplet après la balise',
			107 => 'Flux XML invalide ou incomplet après la balise',
			108 => 'Le code CLIENT donné dans le flux XML est incorrect, il doit correspondre au clientcode en majuscule',
			109 => 'Flux XML invalide ou incomplet après la balise',
			110 => 'Message non défini (vide) dans le flux XML',
			111 => 'Le message dépasse 640 caractères',
			112 => 'Flux XML invalide ou incomplet après la balise',
			113 => 'Certains numéros de téléphone sont invalides ou non pris en charge',
			114 => 'Aucun numéro de téléphone valide dans le flux. Veuillez-vous référer à la documentation en ligne pour connaitre les formats valides.',
			115 => 'Flux XML invalide ou date mal formatée entre les balises et',
			117 => 'Balise – Lien trop long, dépasse les 80 caractères',
			118 => 'Le compte maître spécifié n’existe pas',
		];
		*/

		foreach ($sms->getListRecipients() as $phoneNumber) {
			$json = json_encode([
				'from' => $sms->getSenderName(),
				'to' => $phoneNumber,
				'text' => $sms->getText(),
			]);

			try {
				$curl = curl_init();

				curl_setopt_array($curl, array(
					CURLOPT_URL => "https://api.allmysms.com/sms/send",
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 5,
					CURLOPT_CONNECTTIMEOUT => 2,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "POST",
					CURLOPT_POSTFIELDS => $json,
					CURLOPT_HTTPHEADER => [
						"Authorization: Basic ".$this->getAuthToken(),
						"Content-Type: application/json",
						"cache-control: no-cache"
					],
				));

				$response = curl_exec($curl);
				$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
				$err = curl_error($curl);

				curl_close($curl);
			}
			catch (\Exception $e) {
				$this->logger->error($e->getMessage());
				throw $e;
			}

			if ($err) {
				$this->logger->error('cURL Error: ' . $err);
				throw new \Exception('AllMySms API cURL error: ' . $err);
			}

			// Validate HTTP response code
			if ($httpCode < 200 || $httpCode >= 300) {
				$errorMsg = 'AllMySms API returned HTTP code ' . $httpCode;
				if ($response) {
					$errorMsg .= ': ' . $response;
				}
				$this->logger->error($errorMsg);
				throw new \Exception($errorMsg);
			}

			// Parse and validate JSON response
			try {
				$responseData = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
				$this->logger->info('AllMySms API response', ['response' => $responseData]);

				// Check for API errors in response
				if (isset($responseData['status']) && $responseData['status'] !== 100 && $responseData['status'] !== 101) {
					$errorMsg = 'AllMySms API error (status ' . $responseData['status'] . ')';
					if (isset($responseData['message'])) {
						$errorMsg .= ': ' . $responseData['message'];
					}
					$this->logger->error($errorMsg);
					throw new \Exception($errorMsg);
				}
			} catch (\JsonException $e) {
				$this->logger->error('Invalid JSON response from AllMySms API: ' . $e->getMessage());
				throw new \Exception('Invalid JSON response from AllMySms API: ' . $e->getMessage());
			}
		}
	}
}