<?php

namespace Osimatic\Helpers\Messaging;

use Osimatic\Helpers\Messaging\PhoneNumber;

/**
 * Class OutgoingCall
 * @package Osimatic\Helpers\Messaging
 */
class OutgoingCall
{
	public const CALL_RESULT_OK = 'OK';
	public const CALL_RESULT_BUSY = 'BUSY';
	public const CALL_RESULT_NO_RESPONSE = 'NO_RESPONSE';
	public const CALL_RESULT_FAILED = 'FAILED';

	/**
	 * The identifier property represents any kind of identifier.
	 * @var string|null
	 */
	private ?string $identifier = null;

	/**
	 * Numéro de téléphone appelé
	 * @var string|null
	 */
	private ?string $calledNumber = null;

	/**
	 * Numéro de téléphone affiché sur le téléphone de l'appelé
	 * @var string|null
	 */
	private ?string $displayedNumber = null;

	/**
	 * Date/heure de l'appel. Permet d'effectuer l'appel en différé (null = appel immédiat).
	 * @var \DateTime
	 */
	private \DateTime $callDateTime;



	public function __construct()
	{
		$this->callDateTime = \Osimatic\Helpers\Calendar\DateTime::getCurrentDateTime();
	}

	/**
	 * @return string|null
	 */
	public function getIdentifier(): ?string
	{
		return $this->identifier;
	}

	/**
	 * @param string|null $identifier
	 */
	public function setIdentifier(?string $identifier): void
	{
		$this->identifier = $identifier;
	}


	// ========== Numéro appelé ==========

	/**
	 * Définit le numéro de téléphone appelé.
	 * @param string|null $phoneNumber
	 * @return self
	 */
	public function setCalledNumber(?string $phoneNumber): self
	{
		$phoneNumber = PhoneNumber::parse(trim($phoneNumber));
		if (PhoneNumber::isValid($phoneNumber)) {
			//trace('Invalid number : '.$mobileNumber);
			$this->calledNumber = $phoneNumber;
		}

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getCalledNumber(): ?string
	{
		return $this->calledNumber;
	}

	/**
	 * Définit le numéro de téléphone affiché.
	 * @param string|null $phoneNumber
	 * @return self
	 */
	public function setDisplayedNumber(?string $phoneNumber): self
	{
		$phoneNumber = PhoneNumber::parse(trim($phoneNumber));
		if (PhoneNumber::isValid($phoneNumber)) {
			$this->displayedNumber = $phoneNumber;
		}

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getDisplayedNumber(): ?string
	{
		return $this->displayedNumber;
	}


	// ========== Call options ==========

	/**
	 * Get de la date et heure de l'appel.
	 */
	public function getCallDateTime(): ?\DateTime
	{
		return $this->callDateTime;
	}

	/**
	 * Set de la date et heure de l'appel.
	 * @param \DateTime|null $callDateTime
	 * @return self
	 */
	public function setCallDateTime(?\DateTime $callDateTime): self
	{
		$this->callDateTime = $callDateTime;

		return $this;
	}

}