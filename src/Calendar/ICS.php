<?php

namespace Osimatic\Calendar;

use Symfony\Component\HttpFoundation\Response;

/**
 * Cette classe contient des fonctions relatives au fichier ICS.
 * @package Osimatic\Helpers\Calendar
 * @author Benoit Guiraudou <guiraudou@osimatic.com>
 * @link https://en.wikipedia.org/wiki/ICalendar
 */
class ICS
{
	public const FILE_EXTENSION = '.ics';
	public const FILE_EXTENSIONS = ['.ics', '.vcs', '.ical', '.ifb'];
	const LN = "\r\n";


	// ---------- check ----------

	/**
	 * @param string $filePath
	 * @param string $clientOriginalName
	 * @return bool
	 */
	public static function checkFile(string $filePath, string $clientOriginalName): bool
	{
		return \Osimatic\FileSystem\File::check($filePath, $clientOriginalName, self::FILE_EXTENSIONS);
	}

	// ---------- output ----------

	/**
	 * Download a ICS file to the browser.
	 * Aucun affichage ne doit être effectué avant ou après l'appel à cette fonction.
	 * @param string $filePath
	 * @param string|null $fileName
	 */
	public static function output(string $filePath, ?string $fileName=null): void
	{
		\Osimatic\FileSystem\File::output($filePath, $fileName, 'text/calendar');
		// header('Connection: close'); // sert à qqchose ?
	}

	/**
	 * @param string $filePath
	 * @param string|null $fileName
	 * @return Response
	 */
	public static function getHttpResponse(string $filePath, ?string $fileName=null): Response
	{
		return \Osimatic\FileSystem\File::getHttpResponse($filePath, $fileName, false, 'text/calendar');
	}

	// ---------- generate ----------

	/**
	 * Get output as string
	 * @param EventInterface[] $events
	 * @return string
	 */
	public static function getContent(array $events): string
	{
		// Build ICS properties - add header
		$props = [
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'PRODID:-//hacksw/handcal//NONSGML v1.0//EN',
			'CALSCALE:GREGORIAN',
		];

		// Build ICS properties - add values
		foreach ($events as $event) {
			$props[] = 'BEGIN:VEVENT';
			$props[] = 'DESCRIPTION:' . self::escapeString($event->getDescription());
			$props[] = 'DTSTART:' . self::getFormattedDateTime($event->getStartDate());
			$props[] = 'DTEND:' . self::getFormattedDateTime($event->getEndDate());
			$props[] = 'DTSTAMP:' . self::getFormattedDateTime(new \DateTime());
			$props[] = 'LOCATION:' . self::escapeString(Event::getLocationName($event));
			$props[] = 'SUMMARY:' . self::escapeString($event->getSummary());
			$props[] = 'ORGANIZER;RSVP=TRUE;CN=' . self::escapeString(Event::getOrganizerName($event)) . ';PARTSTAT=ACCEPTED;ROLE=CHAIR:mailto:' . self::escapeString(Event::getOrganizerEmail($event)) . '';
			$props[] = 'URL;VALUE=URI:' . self::escapeString($event->getUrl());
			$props[] = 'UID:' . uniqid('', true);
			$props[] = 'END:VEVENT';
		}

		// Build ICS properties - add footer
		$props[] = 'END:VCALENDAR';

		return implode(self::LN, $props);
	}

	/**
	 * Save to a file
	 * @param EventInterface[] $events
	 * @param string $filePath
	 */
	public static function generateFile(array $events, string $filePath): void
	{
		\Osimatic\FileSystem\FileSystem::initializeFile($filePath);

		file_put_contents($filePath, self::getContent($events));
	}

	private static function escapeString(?string $str): ?string
	{
		return preg_replace('/([\,;])/', '\\\$1', $str);
	}

	private static function getFormattedDateTime(\DateTime $dateTime): string
	{
		// todo : add fuseau horaire
		//return $dateTime->format('Ymd\THis\Z');
		return $dateTime->format('Ymd\THis');
	}

	// ---------- parse ----------

	/**
	 * @param string $filePath
	 * @param EventInterface $baseEvent
	 * @param string $timezone
	 * @return EventInterface[]
	 */
	public static function parseFile(string $filePath, EventInterface $baseEvent, string $timezone='UTC'): array
	{
		$eventList = [];
		try {
			$ical = new \ICal\ICal($filePath, array(
				'defaultSpan'                 => 2,     // Default value
				'defaultTimeZone'             => $timezone,
				'defaultWeekStart'            => 'MO',  // Default value
				'disableCharacterReplacement' => false, // Default value
				'filterDaysAfter'             => null,  // Default value
				'filterDaysBefore'            => null,  // Default value
				'httpUserAgent'               => null,  // Default value
				'skipRecurrence'              => false, // Default value
			));

			foreach ($ical->events() as $iCalEvent) {
				/** @var \ICal\Event $iCalEvent */
				$event = clone $baseEvent;
				$event->setSummary($iCalEvent->summary);
				$event->setDescription($iCalEvent->description);
				$event->setStartDate($ical->iCalDateToDateTime($iCalEvent->dtstart_tz));
				$event->setEndDate($ical->iCalDateToDateTime($iCalEvent->dtend_tz));
				$event->getOrganizer()?->setFamilyName($iCalEvent->organizer);
				$event->setLocation($iCalEvent->location);
				$eventList[] = $event;
			}
		} catch (\Exception $e) {
			die($e);
		}
		return $eventList;
	}
}

/*
EXEMPLE DE ICS :
BEGIN:VCALENDAR
PRODID:-//Mozilla.org/NONSGML Mozilla Calendar V1.1//EN
VERSION:2.0
METHOD:REQUEST
BEGIN:VTIMEZONE
TZID:Europe/Paris
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
TZNAME:CEST
DTSTART:19700329T020000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
TZNAME:CET
DTSTART:19701025T030000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
CREATED:20181228T111045Z
LAST-MODIFIED:20181228T111131Z
DTSTAMP:20181228T111131Z
UID:f19c98c7-b409-465c-b296-a5837d3b3a6e
SUMMARY:myConf Conference
ORGANIZER;RSVP=TRUE;CN=Benoit Guiraudou;PARTSTAT=ACCEPTED;ROLE=CHAIR:mailt
o:benoit.guiraudou@free.fr
ATTENDEE;RSVP=TRUE;PARTSTAT=NEEDS-ACTION;ROLE=REQ-PARTICIPANT:mailto:guira
udou@osimatic.com
DTSTART;TZID=Europe/Paris:20181123T130000
DTEND;TZID=Europe/Paris:20181123T140000
TRANSP:OPAQUE
END:VEVENT
END:VCALENDAR
*/