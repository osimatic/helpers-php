<?php

namespace Osimatic\Messaging;

/**
 * Represents an outgoing phone call with recipient numbers and call scheduling options.
 * This class provides an API for building and managing outgoing calls before initiating them through a telephony service.
 */
class OutgoingCall
{
	/**
	 * The identifier property represents any kind of identifier.
	 * @var string|null
	 */
	private ?string $identifier = null;

	/**
	 * The phone number to be called.
	 * @var string|null
	 */
	private ?string $calledNumber = null;

	/**
	 * The phone number displayed on the recipient's phone (caller ID).
	 * @var string|null
	 */
	private ?string $displayedNumber = null;

	/**
	 * The date and time when the call should be made.
	 * Allows for scheduled/deferred calling (defaults to current time for immediate calling).
	 * @var \DateTime
	 */
	private \DateTime $callDateTime;



	/**
	 * Construct a new OutgoingCall instance.
	 * Initializes the call date time to the current date and time.
	 */
	public function __construct()
	{
		$this->callDateTime = \Osimatic\Calendar\DateTime::getCurrentDateTime();
	}

	/**
	 * Get the call identifier.
	 * @return string|null The identifier, or null if not set
	 */
	public function getIdentifier(): ?string
	{
		return $this->identifier;
	}

	/**
	 * Set the call identifier.
	 * @param string|null $identifier The identifier to set
	 */
	public function setIdentifier(?string $identifier): void
	{
		$this->identifier = $identifier;
	}


	// ========== Called number ==========

	/**
	 * Set the phone number to be called.
	 * The phone number is validated and parsed before being set.
	 * @param string|null $phoneNumber The phone number to call
	 * @return self Returns this instance for method chaining
	 */
	public function setCalledNumber(?string $phoneNumber): self
	{
		$phoneNumber = PhoneNumber::parse(trim($phoneNumber));
		if (PhoneNumber::isValid($phoneNumber)) {
			$this->calledNumber = $phoneNumber;
		}

		return $this;
	}

	/**
	 * Get the phone number to be called.
	 * @return string|null The phone number to call, or null if not set
	 */
	public function getCalledNumber(): ?string
	{
		return $this->calledNumber;
	}

	/**
	 * Set the phone number displayed on the recipient's phone (caller ID).
	 * The phone number is validated and parsed before being set.
	 * @param string|null $phoneNumber The phone number to display as caller ID
	 * @return self Returns this instance for method chaining
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
	 * Get the phone number displayed on the recipient's phone (caller ID).
	 * @return string|null The displayed phone number, or null if not set
	 */
	public function getDisplayedNumber(): ?string
	{
		return $this->displayedNumber;
	}


	// ========== Call options ==========

	/**
	 * Get the date and time when the call should be made.
	 * @return \DateTime|null The scheduled call date and time
	 */
	public function getCallDateTime(): ?\DateTime
	{
		return $this->callDateTime;
	}

	/**
	 * Set the date and time when the call should be made.
	 * @param \DateTime|null $callDateTime The scheduled call date and time
	 * @return self Returns this instance for method chaining
	 */
	public function setCallDateTime(?\DateTime $callDateTime): self
	{
		$this->callDateTime = $callDateTime;

		return $this;
	}

}