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
		if (null !== ($organizer = $event->getOrganizer())) {
			return \Osimatic\Person\Name::getFormattedName($organizer->getGender(), $organizer->getGivenName(), $organizer->getFamilyName());
		}
		if (null !== $event->getOrganizingOrganization()) {
			return $event->getOrganizingOrganization()->getLegalName();
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
			return $event->getOrganizer()->getEmail();
		}
		if (null !== $event->getOrganizingOrganization()) {
			return $event->getOrganizingOrganization()->getEmail();
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