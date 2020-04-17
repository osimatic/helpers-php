<?php

namespace Osimatic\Helpers\API;

/**
 * Class GoogleCalendar
 * @package Osimatic\Helpers\API
 */
class GoogleCalendar
{
	/**
	 * @param string $eventTitle
	 * @param \DateTime $eventDateStart
	 * @param \DateTime $eventDateEnd
	 * @param string|null $eventDetails
	 * @param string|null $eventLocation
	 * @return string
	 */
	public static function getAddEventUrl(string $eventTitle, \DateTime $eventDateStart, \DateTime $eventDateEnd, ?string $eventDetails=null, ?string $eventLocation=null): string
	{
		return 'https://calendar.google.com/calendar/r/eventedit?text='.urlencode($eventTitle).'&dates='.$eventDateStart->format('Ymd\THis\Z').'/'.$eventDateEnd->format('Ymd\THis\Z').'&ctz='.$eventDateStart->getTimezone()->getName().'&details='.urlencode($eventDetails).'&location='.urlencode($eventLocation);
		//20131124T010000Z/20131124T020000Z
	}
}