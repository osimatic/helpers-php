<?php

namespace Osimatic\Helpers;

/**
 * Cette classes contient des fonctions relatives au fichier ICS
 * @author Benoit Guiraudou <guiraudou@osimatic.com>
 * @link https://en.wikipedia.org/wiki/ICalendar
 */
class ICS
{
	const FILE_EXTENSION = '.ics';
	const LN = "\r\n";

	/**
	 * @var string
	 */
	private $summary;

	/**
	 * @var string
	 */
	private $description;

	/**
	 * @var \DateTime
	 */
	private $dateStart;

	/**
	 * @var \DateTime
	 */
	private $dateEnd;

	/**
	 * @var string
	 */
	private $organizerName;

	/**
	 * @var string
	 */
	private $organizerEmail;

	/**
	 * @var string
	 */
	private $location;

	/**
	 * @var string
	 */
	private $url;

	public function __construct()
	{
	}

	public function build($path)
	{
		file_put_contents($path, $this->getContent());
	}

	public function getContent()
	{
		$createdDate = null;
		try {
			$createdDate = new \DateTime('now');
		}
		catch (\Exception $e) {}

		// Build ICS properties - add header
		$ics_props = [
			'BEGIN:VCALENDAR',
			'VERSION:2.0',
			'PRODID:-//hacksw/handcal//NONSGML v1.0//EN',
			'CALSCALE:GREGORIAN',
			'BEGIN:VEVENT'
		];

		// Build ICS properties - add values
		$ics_props[] = 'DESCRIPTION:' . $this->escapeString($this->description);
		$ics_props[] = 'DTSTART:' . $this->getFormattedDateTime($this->dateStart);
		$ics_props[] = 'DTEND:' . $this->getFormattedDateTime($this->dateEnd);
		$ics_props[] = 'DTSTAMP:' . (null !== $createdDate ? $this->getFormattedDateTime($createdDate) : '');
		$ics_props[] = 'LOCATION:' . $this->escapeString($this->location);
		$ics_props[] = 'SUMMARY:' . $this->escapeString($this->summary);
		$ics_props[] = 'ORGANIZER;RSVP=TRUE;CN=' . $this->escapeString($this->organizerName) . ';PARTSTAT=ACCEPTED;ROLE=CHAIR:mailto:' . $this->escapeString($this->organizerEmail) . '';
		$ics_props[] = 'URL;VALUE=URI:' . $this->escapeString($this->url);
		$ics_props[] = 'UID:' . uniqid('', true);

		// Build ICS properties - add footer
		$ics_props[] = 'END:VEVENT';
		$ics_props[] = 'END:VCALENDAR';

		return implode(self::LN, $ics_props);
	}

	private function escapeString(?string $str): ?string
	{
		return preg_replace('/([\,;])/', '\\\$1', $str);
	}

	private function getFormattedDateTime(\DateTime $dateTime): string
	{
		// todo : add fuseau horaire
		//return $dateTime->format('Ymd\THis\Z');
		return $dateTime->format('Ymd\THis');
	}

	/**
	 * @return string
	 */
	public function getSummary(): string
	{
		return $this->summary;
	}

	/**
	 * @param string $summary
	 */
	public function setSummary(string $summary): void
	{
		$this->summary = $summary;
	}

	/**
	 * @return string
	 */
	public function getDescription(): string
	{
		return $this->description;
	}

	/**
	 * @param string $description
	 */
	public function setDescription(string $description): void
	{
		$this->description = $description;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateStart(): \DateTime
	{
		return $this->dateStart;
	}

	/**
	 * @param \DateTime $dateStart
	 */
	public function setDateStart(\DateTime $dateStart): void
	{
		$this->dateStart = $dateStart;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateEnd(): \DateTime
	{
		return $this->dateEnd;
	}

	/**
	 * @param \DateTime $dateEnd
	 */
	public function setDateEnd(\DateTime $dateEnd): void
	{
		$this->dateEnd = $dateEnd;
	}

	/**
	 * @return string
	 */
	public function getOrganizerName(): string
	{
		return $this->organizerName;
	}

	/**
	 * @param string $organizerName
	 */
	public function setOrganizerName(string $organizerName): void
	{
		$this->organizerName = $organizerName;
	}

	/**
	 * @return string
	 */
	public function getOrganizerEmail(): string
	{
		return $this->organizerEmail;
	}

	/**
	 * @param string $organizerEmail
	 */
	public function setOrganizerEmail(string $organizerEmail): void
	{
		$this->organizerEmail = $organizerEmail;
	}

	/**
	 * @return string
	 */
	public function getLocation(): string
	{
		return $this->location;
	}

	/**
	 * @param string $location
	 */
	public function setLocation(string $location): void
	{
		$this->location = $location;
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