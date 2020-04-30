<?php

namespace Osimatic\Helpers\Messaging;

use Osimatic\Helpers\ContactDetails\PhoneNumber;

/**
 * Class OutgoingCall
 * @package Osimatic\Helpers\Messaging
 */
class OutgoingCall
{
	/**
	 * Numéro de téléphone appelé
	 * @var string
	 */
	protected $calledNumber;

	/**
	 * Date/heure de l'appel. Permet d'effectuer l'appel en différé (null = appel immédiat).
	 * @var \DateTime
	 */
	protected $callDateTime;


	public function __construct()
	{
		$this->callDateTime = \Osimatic\Helpers\DateTime\DateTime::getCurrentDateTime();
	}


	// ========== Numéro appelé ==========

	/**
	 * Définit le numéro de téléphone appelé.
	 * @param string $phoneNumber le numéro de téléphone appelé.
	 * @return self
	 */
	public function setCalledNumber(?string $phoneNumber): self
	{
		$phoneNumber = PhoneNumber::parse(trim($phoneNumber));
		if (!PhoneNumber::isValid($phoneNumber)) {
			//trace('Invalid number : '.$mobileNumber);
			return $this;
		}

		$this->calledNumber = $phoneNumber;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getCalledNumber(): ?string
	{
		return $this->calledNumber;
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