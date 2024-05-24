<?php

namespace Osimatic\Calendar;

/**
 * Class Event
 * @package Osimatic\Helpers\Calendar
 */
class Event
{
	/**
	 * @param EventInterface $event
	 * @return string|null
	 */
	public static function getOrganizerName(EventInterface $event): ?string
	{
		if (null !== $event->getOrganizer()) {
			$event->getOrganizer()->getFormattedName();
		}
		if (null !== $event->getOrganizingOrganization()) {
			$event->getOrganizingOrganization()->getLegalName();
		}
		return null;
	}

	/**
	 * @param EventInterface $event
	 * @return string|null
	 */
	public static function getOrganizerEmail(EventInterface $event): ?string
	{
		if (null !== $event->getOrganizer()) {
			$event->getOrganizer()->getEmail();
		}
		if (null !== $event->getOrganizingOrganization()) {
			$event->getOrganizingOrganization()->getEmail();
		}
		return null;
	}

	/**
	 * @param EventInterface $event
	 * @return string|null
	 */
	public static function getLocationName(EventInterface $event): ?string
	{
		$postalAddress = null;
		if (null !== $event->getLocation()) {
			$postalAddress = $event->getLocation()->getAddress();
		}
		elseif (null !== $event->getAddress()) {
			$postalAddress = $event->getAddress();
		}
		if (null !== $postalAddress) {
			return \Osimatic\Location\PostalAddress::format($postalAddress);
		}
		return null;
	}
}