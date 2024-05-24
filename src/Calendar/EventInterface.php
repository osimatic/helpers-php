<?php

namespace Osimatic\Calendar;

use Osimatic\Location\PlaceInterface;
use Osimatic\Location\PostalAddressInterface;
use Osimatic\Organization\OrganizationInterface;
use Osimatic\Person\PersonInterface;

/**
 * Interface EventInterface
 * @package Osimatic\Helpers\Calendar
 */
interface EventInterface
{
	/**
	 * The identifier property represents any kind of identifier.
	 * @return string|null
	 */
	public function getIdentifier(): ?string;

	/**
	 * @param string|null $identifier
	 */
	public function setIdentifier(?string $identifier): void;

	/**
	 * The name of the event.
	 * @return string|null
	 */
	public function getName(): ?string;

	/**
	 * @param string|null $name
	 */
	public function setName(?string $name): void;

	/**
	 * A summary of the event.
	 * @return string|null
	 */
	public function getSummary(): ?string;

	/**
	 * @param string|null $summary
	 */
	public function setSummary(?string $summary): void;

	/**
	 * A description of the event.
	 * @return string|null
	 */
	public function getDescription(): ?string;

	/**
	 * @param string|null $description
	 */
	public function setDescription(?string $description): void;

	/**
	 * An organizer of an Event.
	 * @return PersonInterface|null
	 */
	public function getOrganizer(): ?PersonInterface;

	/**
	 * @param PersonInterface|null $organizer
	 */
	public function setOrganizer(?PersonInterface $organizer): void;

	/**
	 * An organizer of an Event.
	 * @return OrganizationInterface|null
	 */
	public function getOrganizingOrganization(): ?OrganizationInterface;

	/**
	 * @param OrganizationInterface|null $organizingOrganization
	 */
	public function setOrganizingOrganization(?OrganizationInterface $organizingOrganization): void;

	/**
	 * The start date and time of the event.
	 * @return \DateTime|null
	 */
	public function getStartDate(): ?\DateTime;

	/**
	 * @param \DateTime|null $startDate
	 */
	public function setStartDate(?\DateTime $startDate): void;

	/**
	 * The end date and time of the event.
	 * @return \DateTime|null
	 */
	public function getEndDate(): ?\DateTime;

	/**
	 * @param \DateTime|null $endDate
	 */
	public function setEndDate(?\DateTime $endDate): void;

	/**
	 * The location of for example where the event is happening, an organization is located, or where an action takes place.
	 * @return PlaceInterface|null
	 */
	public function getLocation(): ?PlaceInterface;

	/**
	 * @param PlaceInterface|null $location
	 */
	public function setLocation(?PlaceInterface $location): void;

	/**
	 * The location of for example where the event is happening, an organization is located, or where an action takes place.
	 * @return PostalAddressInterface|null
	 */
	public function getAddress(): ?PostalAddressInterface;

	/**
	 * @param PostalAddressInterface|null $address
	 */
	public function setAddress(?PostalAddressInterface $address): void;

	/**
	 * The total number of individuals that may attend an event or venue.
	 * @return int|null
	 */
	public function getMaximumAttendeeCapacity(): ?int;

	/**
	 * @param int|null $maximumAttendeeCapacity
	 */
	public function setMaximumAttendeeCapacity(?int $maximumAttendeeCapacity): void;

	/**
	 * The language of the content or performance or used in an action.
	 * @return string|null
	 */
	public function getInLanguage(): ?string;

	/**
	 * @param string|null $inLanguage
	 */
	public function setInLanguage(?string $inLanguage): void;

	/**
	 * URL of the event.
	 * @return string|null
	 */
	public function getUrl(): ?string;

	/**
	 * @param string|null $url
	 */
	public function setUrl(?string $url): void;

	/**
	 * An eventStatus of an event represents its status; particularly useful when an event is cancelled or rescheduled.
	 * @return string|null
	 */
	public function getEventStatus(): ?string;

	/**
	 * @param string|null $eventStatus
	 */
	public function setEventStatus(?string $eventStatus): void;

	/**
	 * A flag to signal that the item, event, or place is accessible for free.
	 * @return bool
	 */
	public function isAccessibleForFree(): bool;

	/**
	 * @param bool $isAccessibleForFree
	 */
	public function setIsAccessibleForFree(bool $isAccessibleForFree): void;
}