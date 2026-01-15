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
	 * Set the PSR-3 logger for debugging SMS sending operations.
	 * @param LoggerInterface $logger The logger instance
	 */
	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}

	/**
	 * Set the AllMySms account login.
	 * @param string $login The account login
	 */
	public function setLogin(string $login): void
	{
		$this->login = $login;
	}

	/**
	 * Set the AllMySms API key.
	 * @param string $apiKey The API key for authentication
	 */
	public function setApiKey(string $apiKey): void
	{
		$this->apiKey = $apiKey;
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
					CURLOPT_TIMEOUT => 2,
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
				$err = curl_error($curl);

				curl_close($curl);
			}
			catch (\Exception $e) {
				$this->logger->error($e->getMessage());
				throw $e;
			}

			if ($err) {
				$this->logger->error('cURL Error:' . $err);
				throw new \Exception($err);
			}

			$this->logger->info($response);
		}
	}
}