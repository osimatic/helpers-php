<?php

namespace Osimatic\Helpers\Bank;

class PayBoxResponse
{
	public const _3D_SECURE_VERSION_1 = 1;
	public const _3D_SECURE_VERSION_2 = 2;

	/**
	 * @var string|null
	 */
	private ?string $reference = null;

	/**
	 * @var string|null
	 */
	private ?string $responseCode = null;

	/**
	 * @var string|null
	 */
	private ?string $callNumber = null;

	/**
	 * @var string|null
	 */
	private ?string $transactionNumber = null;

	/**
	 * @var string|null
	 */
	private ?string $authorizationNumber = null;

	/**
	 * @var string|null
	 */
	private ?string $cardNumber = null;

	/**
	 * @var string|null
	 */
	private ?string $cardLastDigits = null;

	/**
	 * @var \DateTime|null
	 */
	private ?\DateTime $cardExpirationDateTime = null;

	/**
	 * @var string|null
	 */
	private ?string $cardHash = null;

	/**
	 * @var bool
	 */
	private bool $_3DSecureEnabled = false;

	/**
	 * Valeurs possibles :
	 * - "Y" : Porteur authentifié
	 * - "A" : Authentification du porteur forcée par la banque de l’acheteur
	 * - "U" : L’authentification du porteur n’a pas pu s’effectuer
	 * - "N" : Porteur non authentifié
	 * @var string|null
	 */
	private ?string $_3DSecureAuthentication = null;

	/**
	 * @var int|null
	 */
	private ?int $_3DSecureVersion = null;

	public static function getFromRequest(array $request): PayBoxResponse {
		$payBoxResponse = new PayBoxResponse();
		$payBoxResponse->setReference(urldecode($request['reference'] ?? null));
		$payBoxResponse->setResponseCode(urldecode($request['response_code'] ?? null));
		$payBoxResponse->setCallNumber(urldecode($request['call_nb'] ?? null));
		$payBoxResponse->setTransactionNumber(urldecode($request['transact_nb'] ?? null));
		$payBoxResponse->setAuthorizationNumber(urldecode($request['authorization_nb'] ?? null));

		if (!empty($request['card_ref'])) {
			// card_ref contient la chaine suivante : "abc123abc12  2206  ---" (la premiere partie correspond au token de la carte, à utiliser pour débiter la carte ultérieurement)
			$payBoxResponse->setCardHash(explode('  ', $request['card_ref'])[0] ?? null);
		}

		if (!empty($request['bin6'])) {
			$payBoxResponse->setCardNumber($request['bin6'].'********'.($request['bc_ldigit'] ?? '**'));
		}

		$payBoxResponse->setCardLastDigits(urldecode($request['bc_ldigit'] ?? null));
		if (!empty($cardExpirationDate = urldecode($request['bc_expdate'] ?? null))) {
			$cardExpirationSqlDate = '20'.substr($cardExpirationDate, 0, 2).'-'.substr($cardExpirationDate, 2, 2).'-01';
			$payBoxResponse->setCardExpirationDateTime(new \DateTime(date('Y-m-t H:i:s', strtotime($cardExpirationSqlDate.' 00:00:00'))));
		}

		$payBoxResponse->set3DSecureEnabled($request['3ds_enable'] === 'O');
		if ($payBoxResponse->is3DSecureEnabled()) {
			$payBoxResponse->set3DSecureAuthentication($request['3ds_auth'] ?? null);
			$payBoxResponse->set3DSecureVersion(!empty($request['3ds_v'])?self::_3D_SECURE_VERSION_2:self::_3D_SECURE_VERSION_1);
		}

		return $payBoxResponse;
	}

	/**
	 * @return bool
	 */
	public function isSuccess(): bool
	{
		return $this->responseCode ===  '00000';
	}


	/**
	 * @return string|null
	 */
	public function getReference(): ?string
	{
		return $this->reference;
	}

	/**
	 * @param string|null $reference
	 */
	public function setReference(?string $reference): void
	{
		$this->reference = $reference;
	}

	/**
	 * @return string|null
	 */
	public function getResponseCode(): ?string
	{
		return $this->responseCode ?? null;
	}

	/**
	 * @param string|null $responseCode
	 */
	public function setResponseCode(?string $responseCode): void
	{
		$this->responseCode = $responseCode;
	}

	/**
	 * @return string|null
	 */
	public function getCallNumber(): ?string
	{
		return $this->callNumber ?? null;
	}

	/**
	 * @param string|null $callNumber
	 */
	public function setCallNumber(?string $callNumber): void
	{
		$this->callNumber = $callNumber;
	}

	/**
	 * @return string|null
	 */
	public function getTransactionNumber(): ?string
	{
		return $this->transactionNumber ?? null;
	}

	/**
	 * @param string|null $transactionNumber
	 */
	public function setTransactionNumber(?string $transactionNumber): void
	{
		$this->transactionNumber = $transactionNumber;
	}

	/**
	 * @return string|null
	 */
	public function getAuthorizationNumber(): ?string
	{
		return $this->authorizationNumber ?? null;
	}

	/**
	 * @param string|null $authorizationNumber
	 */
	public function setAuthorizationNumber(?string $authorizationNumber): void
	{
		$this->authorizationNumber = $authorizationNumber;
	}

	/**
	 * @return string|null
	 */
	public function getCardNumber(): ?string
	{
		return $this->cardNumber;
	}

	/**
	 * @param string|null $cardNumber
	 */
	public function setCardNumber(?string $cardNumber): void
	{
		$this->cardNumber = $cardNumber;
	}

	/**
	 * @return string|null
	 */
	public function getCardLastDigits(): ?string
	{
		return $this->cardLastDigits ?? null;
	}

	/**
	 * @param string|null $cardLastDigits
	 */
	public function setCardLastDigits(?string $cardLastDigits): void
	{
		$this->cardLastDigits = $cardLastDigits;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getCardExpirationDateTime(): ?\DateTime
	{
		return $this->cardExpirationDateTime;
	}

	/**
	 * @param \DateTime|null $cardExpirationDateTime
	 */
	public function setCardExpirationDateTime(?\DateTime $cardExpirationDateTime): void
	{
		$this->cardExpirationDateTime = $cardExpirationDateTime;
	}

	/**
	 * @return string|null
	 */
	public function getCardHash(): ?string
	{
		return $this->cardHash ?? null;
	}

	/**
	 * @param string|null $cardHash
	 */
	public function setCardHash(?string $cardHash): void
	{
		$this->cardHash = $cardHash;
	}

	/**
	 * @return bool
	 */
	public function is3DSecureEnabled(): bool
	{
		return $this->_3DSecureEnabled;
	}

	/**
	 * @param bool $_3DSecureEnabled
	 */
	public function set3DSecureEnabled(bool $_3DSecureEnabled): void
	{
		$this->_3DSecureEnabled = $_3DSecureEnabled;
	}

	/**
	 * @return string|null
	 */
	public function get3DSecureAuthentication(): ?string
	{
		return $this->_3DSecureAuthentication;
	}

	/**
	 * @param string|null $_3DSecureAuthentication
	 */
	public function set3DSecureAuthentication(?string $_3DSecureAuthentication): void
	{
		$this->_3DSecureAuthentication = $_3DSecureAuthentication;
	}

	/**
	 * @return int|null
	 */
	public function getX3DSecureVersion(): ?int
	{
		return $this->_3DSecureVersion;
	}

	/**
	 * @param int|null $_3DSecureVersion
	 */
	public function set3DSecureVersion(?int $_3DSecureVersion): void
	{
		$this->_3DSecureVersion = $_3DSecureVersion;
	}

}