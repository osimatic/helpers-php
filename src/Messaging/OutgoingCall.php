<?php

namespace Osimatic\Helpers\Messaging;

use Osimatic\Helpers\Messaging\PhoneNumber;

/**
 * Class OutgoingCall
 * @package Osimatic\Helpers\Messaging
 */
class OutgoingCall
{
	const CALL_RESULT_OK = 'OK';
	const CALL_RESULT_BUSY = 'BUSY';
	const CALL_RESULT_NO_RESPONSE = 'NO_RESPONSE';
	const CALL_RESULT_FAILED = 'FAILED';

	/**
	 * Numéro de téléphone appelé
	 * @var string
	 */
	protected $calledNumber;

	/**
	 * Numéro de téléphone affiché sur le téléphone de l'appelé
	 * @var string
	 */
	protected $displayedNumber;

	/**
	 * Date/heure de l'appel. Permet d'effectuer l'appel en différé (null = appel immédiat).
	 * @var \DateTime
	 */
	protected $callDateTime;



	public function __construct()
	{
		$this->callDateTime = \Osimatic\Helpers\Calendar\DateTime::getCurrentDateTime();
	}


	// ========== Numéro appelé ==========

	/**
	 * Définit le numéro de téléphone appelé.
	 * @param string $phoneNumber
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
	 * @param string $phoneNumber
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
	 * @param \DateTime $callDateTime
	 * @return self
	 */
	public function setCallDateTime(?\DateTime $callDateTime): self
	{
		$this->callDateTime = $callDateTime;

		return $this;
	}

}