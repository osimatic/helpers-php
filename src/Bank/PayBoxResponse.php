<?php

namespace Osimatic\Bank;

class PayBoxResponse implements BankCardOperationResponseInterface
{
	public const int _3D_SECURE_VERSION_1 = 1;
	public const int _3D_SECURE_VERSION_2 = 2;

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
	private ?string $cardType = null;

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

	public static function getFromHttpRequest(\Symfony\Component\HttpFoundation\Request $request): PayBoxResponse
	{
		return self::getFromRequest([
			'ref' => $request->get('ref'),
			'response_code' => $request->get('response_code'),
			'call_nb' => $request->get('call_nb'),
			'transact_nb' => $request->get('transact_nb'),
			'authorizt_nb' => $request->get('authorizt_nb'),
			'card_ref' => $request->get('card_ref'),
			'bc_type' => $request->get('bc_type'),
			'bc_ldigit' => $request->get('bc_ldigit'),
			'bin6' => $request->get('bin6'),
			'bc_expdate' => $request->get('bc_expdate'),
			'3ds' => $request->get('3ds'),
			'3ds_auth' => $request->get('3ds_auth'),
			'3ds_v' => $request->get('3ds_v'),
		]);
	}

	public static function getFromRequest(array $request): PayBoxResponse
	{
		$payBoxResponse = new PayBoxResponse();
		$payBoxResponse->setReference(!empty($request['ref']) ? urldecode($request['ref']) : null);
		$payBoxResponse->setResponseCode(!empty($request['response_code']) ? urldecode($request['response_code']) : null);
		$payBoxResponse->setCallNumber(!empty($request['call_nb']) ? urldecode($request['call_nb']) : null);
		$payBoxResponse->setTransactionNumber(!empty($request['transact_nb']) ? urldecode($request['transact_nb']) : null);
		$payBoxResponse->setAuthorisationNumber(!empty($request['authorizt_nb']) ? urldecode($request['authorizt_nb']) : null);

		if (!empty($request['card_ref'])) {
			// card_ref contient la chaine suivante : "abc123abc12  2206  ---" (la premiere partie correspond au token de la carte, à utiliser pour débiter la carte ultérieurement)
			$payBoxResponse->setCardHash(explode('  ', $request['card_ref'])[0] ?? null);
		}

		$payBoxResponse->setCardType(urldecode($request['bc_type'] ?? null));
		$payBoxResponse->setCardLastDigits(urldecode($request['bc_ldigit'] ?? null));
		if (!empty($request['bin6'])) {
			$payBoxResponse->setCardNumber($request['bin6'].'********'.($request['bc_ldigit'] ?? '**'));
		}

		if (!empty($cardExpirationDate = urldecode($request['bc_expdate'] ?? null))) {
			$payBoxResponse->setCardExpirationDateTime(BankCard::getExpirationDateFromYearAndMonth((int) ('20'.substr($cardExpirationDate, 0, 2)), (int) (substr($cardExpirationDate, 2, 2))));
		}

		$payBoxResponse->set3DSecureEnabled(($request['3ds'] ?? null) === 'O');
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

	public function getOrderReference(): ?string
	{
		return $this->reference;
	}

	public function getCardReference(): ?string
	{
		return $this->cardHash;
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
	public function getAuthorisationNumber(): ?string
	{
		return $this->authorizationNumber ?? null;
	}

	/**
	 * @param string|null $authorizationNumber
	 */
	public function setAuthorisationNumber(?string $authorizationNumber): void
	{
		$this->authorizationNumber = $authorizationNumber;
	}

	/**
	 * @return string|null
	 */
	public function getCardType(): ?string
	{
		return $this->cardType;
	}

	/**
	 * @param string|null $cardType
	 */
	public function setCardType(?string $cardType): void
	{
		$this->cardType = $cardType;
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
	public function get3DSecureVersion(): ?int
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


	// DEPRECATED

	/**
	 * @deprecated
	 */
	public function getAuthorizationNumber(): ?string
	{
		return $this->authorizationNumber ?? null;
	}

	/**
	 * @deprecated
	 */
	public function setAuthorizationNumber(?string $authorizationNumber): void
	{
		$this->authorizationNumber = $authorizationNumber;
	}

}