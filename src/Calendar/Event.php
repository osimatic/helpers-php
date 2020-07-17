<?php

namespace Osimatic\Helpers\Calendar;

use Osimatic\Helpers\Location\Place;
use Osimatic\Helpers\Location\PostalAddress;
use Osimatic\Helpers\Organization\OrganizationInterface;
use Osimatic\Helpers\Person\PersonInterface;

/**
 * Class Event
 * @package Osimatic\Helpers\Calendar
 */
class Event
{
	/**
	 * The identifier property represents any kind of identifier.
	 * @var string|null
	 */
	private $identifier;

	/**
	 * The name of the event.
	 * @var string|null
	 */
	private $name;

	/**
	 * A description of the event.
	 * @var string|null
	 */
	private $description;

	/**
	 * An organizer of an Event.
	 * @var PersonInterface|null
	 */
	private $organizer;

	/**
	 * An organizer of an Event.
	 * @var OrganizationInterface|null
	 */
	private $organizingOrganization;

	/**
	 * The start date and time of the event.
	 * @var \DateTime|null
	 */
	private $startDate;

	/**
	 * The end date and time of the event.
	 * @var \DateTime|null
	 */
	private $endDate;

	/**
	 * The location of for example where the event is happening, an organization is located, or where an action takes place.
	 * @var Place|null
	 */
	private $location;

	/**
	 * The location of for example where the event is happening, an organization is located, or where an action takes place.
	 * @var PostalAddress|null
	 */
	private $address;

	/**
	 * The total number of individuals that may attend an event or venue.
	 * @var int|null
	 */
	private $maximumAttendeeCapacity;

	/**
	 * The language of the content or performance or used in an action.
	 * @var string|null
	 */
	private $inLanguage;

	/**
	 * URL of the event.
	 * @var string
	 */
	private $url;

	/**
	 * An eventStatus of an event represents its status; particularly useful when an event is cancelled or rescheduled.
	 * @var string|null
	 */
	private $eventStatus;

	/**
	 * A flag to signal that the item, event, or place is accessible for free.
	 * @var boolean
	 */
	private $isAccessibleForFree=false;






	/**
	 * @return string|null
	 */
	public function getOrganizerName(): ?string
	{
		if (null !== $this->getOrganizer()) {
			$this->getOrganizer()->getFormattedName();
		}
		if (null !== $this->getOrganizingOrganization()) {
			$this->getOrganizingOrganization()->getLegalName();
		}
		return null;
	}

	/**
	 * @return string|null
	 */
	public function getOrganizerEmail(): ?string
	{
		if (null !== $this->getOrganizer()) {
			$this->getOrganizer()->getEmail();
		}
		if (null !== $this->getOrganizingOrganization()) {
			$this->getOrganizingOrganization()->getEmail();
		}
		return null;
	}

	/**
	 * @return string|null
	 */
	public function getLocationName(): ?string
	{
		$postalAddress = null;
		if (null !== $this->getLocation()) {
			$postalAddress = $this->getLocation()->getAddress();
		}
		elseif (null !== $this->getAddress()) {
			$postalAddress = $this->getAddress();
		}
		if (null !== $postalAddress) {
			return $postalAddress->format();
		}
		return null;
	}

	public function __toString()
	{
		return $this->name ?? '';
	}




	// ========== Get / Set ==========

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

	/**
	 * @return string|null
	 */
	public function getName(): ?string
	{
		return $this->name;
	}

	/**
	 * @param string|null $name
	 */
	public function setName(?string $name): void
	{
		$this->name = $name;
	}

	/**
	 * @return string|null
	 */
	public function getDescription(): ?string
	{
		return $this->description;
	}

	/**
	 * @param string|null $description
	 */
	public function setDescription(?string $description): void
	{
		$this->description = $description;
	}

	/**
	 * @return PersonInterface|null
	 */
	public function getOrganizer(): ?PersonInterface
	{
		return $this->organizer;
	}

	/**
	 * @param PersonInterface|null $organizer
	 */
	public function setOrganizer(?PersonInterface $organizer): void
	{
		$this->organizer = $organizer;
	}

	/**
	 * @return OrganizationInterface|null
	 */
	public function getOrganizingOrganization(): ?OrganizationInterface
	{
		return $this->organizingOrganization;
	}

	/**
	 * @param OrganizationInterface|null $organizingOrganization
	 */
	public function setOrganizingOrganization(?OrganizationInterface $organizingOrganization): void
	{
		$this->organizingOrganization = $organizingOrganization;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getStartDate(): ?\DateTime
	{
		return $this->startDate;
	}

	/**
	 * @param \DateTime|null $startDate
	 */
	public function setStartDate(?\DateTime $startDate): void
	{
		$this->startDate = $startDate;
	}

	/**
	 * @return \DateTime|null
	 */
	public function getEndDate(): ?\DateTime
	{
		return $this->endDate;
	}

	/**
	 * @param \DateTime|null $endDate
	 */
	public function setEndDate(?\DateTime $endDate): void
	{
		$this->endDate = $endDate;
	}

	/**
	 * @return Place|null
	 */
	public function getLocation(): ?Place
	{
		return $this->location;
	}

	/**
	 * @param Place|null $location
	 */
	public function setLocation(?Place $location): void
	{
		$this->location = $location;
	}

	/**
	 * @return PostalAddress|null
	 */
	public function getAddress(): ?PostalAddress
	{
		return $this->address;
	}

	/**
	 * @param PostalAddress|null $address
	 */
	public function setAddress(?PostalAddress $address): void
	{
		$this->address = $address;
	}

	/**
	 * @return int|null
	 */
	public function getMaximumAttendeeCapacity(): ?int
	{
		return $this->maximumAttendeeCapacity;
	}

	/**
	 * @param int|null $maximumAttendeeCapacity
	 */
	public function setMaximumAttendeeCapacity(?int $maximumAttendeeCapacity): void
	{
		$this->maximumAttendeeCapacity = $maximumAttendeeCapacity;
	}

	/**
	 * @return string|null
	 */
	public function getInLanguage(): ?string
	{
		return $this->inLanguage;
	}

	/**
	 * @param string|null $inLanguage
	 */
	public function setInLanguage(?string $inLanguage): void
	{
		$this->inLanguage = $inLanguage;
	}

	/**
	 * @return string
	 */
	public function getUrl(): string
	{
		return $this->url;
	}

	/**
	 * @param string $url
	 */
	public function setUrl(string $url): void
	{
		$this->url = $url;
	}

	/**
	 * @return string|null
	 */
	public function getEventStatus(): ?string
	{
		return $this->eventStatus;
	}

	/**
	 * @param string|null $eventStatus
	 */
	public function setEventStatus(?string $eventStatus): void
	{
		$this->eventStatus = $eventStatus;
	}

	/**
	 * @return bool
	 */
	public function isAccessibleForFree(): bool
	{
		return $this->isAccessibleForFree;
	}

	/**
	 * @param bool $isAccessibleForFree
	 */
	public function setIsAccessibleForFree(bool $isAccessibleForFree): void
	{
		$this->isAccessibleForFree = $isAccessibleForFree;
	}

}