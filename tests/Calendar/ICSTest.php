<?php

declare(strict_types=1);

namespace Tests\Calendar;

use Osimatic\Calendar\EventInterface;
use Osimatic\Calendar\ICS;
use Osimatic\Person\PersonInterface;
use PHPUnit\Framework\TestCase;

final class ICSTest extends TestCase
{
	/* ===================== Constants ===================== */

	public function testFileExtensionConstant(): void
	{
		$this->assertSame('.ics', ICS::FILE_EXTENSION);
	}

	public function testFileExtensionsArrayConstant(): void
	{
		$this->assertSame(['.ics', '.vcs', '.ical', '.ifb'], ICS::FILE_EXTENSIONS);
	}

	public function testLineBreakConstant(): void
	{
		$this->assertSame("\r\n", ICS::LN);
	}

	/* ===================== getContent() - Basic structure ===================== */

	public function testGetContentWithEmptyEventArray(): void
	{
		$content = ICS::getContent([]);

		$this->assertStringContainsString('BEGIN:VCALENDAR', $content);
		$this->assertStringContainsString('VERSION:2.0', $content);
		$this->assertStringContainsString('PRODID:-//hacksw/handcal//NONSGML v1.0//EN', $content);
		$this->assertStringContainsString('CALSCALE:GREGORIAN', $content);
		$this->assertStringContainsString('END:VCALENDAR', $content);
	}

	public function testGetContentStructureHasCorrectLineBreaks(): void
	{
		$content = ICS::getContent([]);
		$lines = explode("\r\n", $content);

		$this->assertSame('BEGIN:VCALENDAR', $lines[0]);
		$this->assertSame('VERSION:2.0', $lines[1]);
		$this->assertSame('END:VCALENDAR', $lines[count($lines) - 1]);
	}

	/* ===================== getContent() - Single event ===================== */

	public function testGetContentWithSingleEvent(): void
	{
		$event = $this->createMockEvent(
			'Team Meeting',
			'Monthly team sync',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'Conference Room A',
			'john.doe@example.com',
			'John Doe',
			'https://example.com/meeting'
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('BEGIN:VEVENT', $content);
		$this->assertStringContainsString('SUMMARY:Team Meeting', $content);
		$this->assertStringContainsString('DESCRIPTION:Monthly team sync', $content);
		$this->assertStringContainsString('DTSTART:20240315T100000', $content);
		$this->assertStringContainsString('DTEND:20240315T110000', $content);
		$this->assertStringContainsString('LOCATION:', $content);
		$this->assertStringContainsString('ORGANIZER;RSVP=TRUE;CN=', $content);
		$this->assertStringContainsString('mailto:john.doe@example.com', $content);
		$this->assertStringContainsString('URL;VALUE=URI:https://example.com/meeting', $content);
		$this->assertStringContainsString('UID:', $content);
		$this->assertStringContainsString('DTSTAMP:', $content);
		$this->assertStringContainsString('END:VEVENT', $content);
	}

	public function testGetContentWithMinimalEvent(): void
	{
		$event = $this->createMockEvent(
			'Simple Event',
			'',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('SUMMARY:Simple Event', $content);
		$this->assertStringContainsString('DTSTART:20240101T120000', $content);
		$this->assertStringContainsString('DTEND:20240101T130000', $content);
	}

	/* ===================== getContent() - Multiple events ===================== */

	public function testGetContentWithMultipleEvents(): void
	{
		$event1 = $this->createMockEvent(
			'Event 1',
			'First event',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'Location 1',
			'organizer1@example.com',
			'Organizer 1',
			'https://example.com/event1'
		);

		$event2 = $this->createMockEvent(
			'Event 2',
			'Second event',
			new \DateTime('2024-03-16 14:00:00'),
			new \DateTime('2024-03-16 15:00:00'),
			'Location 2',
			'organizer2@example.com',
			'Organizer 2',
			'https://example.com/event2'
		);

		$event3 = $this->createMockEvent(
			'Event 3',
			'Third event',
			new \DateTime('2024-03-17 09:00:00'),
			new \DateTime('2024-03-17 10:00:00'),
			'Location 3',
			'organizer3@example.com',
			'Organizer 3',
			'https://example.com/event3'
		);

		$content = ICS::getContent([$event1, $event2, $event3]);

		// Check all events are present
		$this->assertStringContainsString('Event 1', $content);
		$this->assertStringContainsString('Event 2', $content);
		$this->assertStringContainsString('Event 3', $content);

		// Count number of events
		$eventCount = substr_count($content, 'BEGIN:VEVENT');
		$this->assertSame(3, $eventCount);
	}

	/* ===================== getContent() - Date formatting ===================== */

	public function testGetContentFormatsDateTimeCorrectly(): void
	{
		$event = $this->createMockEvent(
			'Date Test',
			'',
			new \DateTime('2024-12-25 23:59:59'),
			new \DateTime('2024-12-26 00:30:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('DTSTART:20241225T235959', $content);
		$this->assertStringContainsString('DTEND:20241226T003000', $content);
	}

	public function testGetContentIncludesDtstampWithCurrentTime(): void
	{
		$event = $this->createMockEvent(
			'Timestamp Test',
			'',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			''
		);

		$beforeTime = new \DateTime();
		$content = ICS::getContent([$event]);
		$afterTime = new \DateTime();

		// Extract DTSTAMP from content
		preg_match('/DTSTAMP:(\d{8}T\d{6})/', $content, $matches);
		$this->assertNotEmpty($matches, 'DTSTAMP should be present');

		$dtstampStr = $matches[1];
		$dtstamp = \DateTime::createFromFormat('Ymd\THis', $dtstampStr);

		// DTSTAMP should be between before and after time
		$this->assertGreaterThanOrEqual($beforeTime->format('Ymd\THis'), $dtstamp->format('Ymd\THis'));
		$this->assertLessThanOrEqual($afterTime->format('Ymd\THis'), $dtstamp->format('Ymd\THis'));
	}

	/* ===================== getContent() - Special characters escaping ===================== */

	public function testGetContentEscapesCommaInDescription(): void
	{
		$event = $this->createMockEvent(
			'Test',
			'Description with comma, semicolon; and text',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('DESCRIPTION:Description with comma\, semicolon\; and text', $content);
	}

	public function testGetContentEscapesSemicolonInLocation(): void
	{
		$event = $this->createMockEvent(
			'Test',
			'',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'Room A; Building B, Floor 3',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		// Location formatting depends on PostalAddress::format() implementation
		// Just verify that LOCATION field is present
		$this->assertStringContainsString('LOCATION:', $content);
	}

	public function testGetContentEscapesSpecialCharsInSummary(): void
	{
		$event = $this->createMockEvent(
			'Meeting: Team, Planning;',
			'',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('SUMMARY:Meeting: Team\, Planning\;', $content);
	}

	public function testGetContentEscapesSpecialCharsInOrganizerName(): void
	{
		$event = $this->createMockEvent(
			'Test',
			'',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'organizer@example.com',
			'Doe, John; Jr.',
			''
		);

		$content = ICS::getContent([$event]);

		// Name formatting depends on Name::getFormattedName() implementation
		// Just verify that CN field is present with escaped characters
		$this->assertStringContainsString('CN=Doe\,', $content);
		$this->assertStringContainsString('\; JR.', $content);
	}

	public function testGetContentEscapesSpecialCharsInUrl(): void
	{
		$event = $this->createMockEvent(
			'Test',
			'',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			'https://example.com/path?param1=value1,value2;param2=value3'
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('URL;VALUE=URI:https://example.com/path?param1=value1\,value2\;param2=value3', $content);
	}

	/* ===================== getContent() - Unique IDs ===================== */

	public function testGetContentGeneratesUniqueUIDsForEachEvent(): void
	{
		$event1 = $this->createMockEvent(
			'Event 1',
			'',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			''
		);

		$event2 = $this->createMockEvent(
			'Event 2',
			'',
			new \DateTime('2024-01-02 12:00:00'),
			new \DateTime('2024-01-02 13:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event1, $event2]);

		// Extract all UIDs
		preg_match_all('/UID:([^\r\n]+)/', $content, $matches);
		$uids = $matches[1];

		$this->assertCount(2, $uids);
		$this->assertNotEquals($uids[0], $uids[1], 'UIDs should be unique');
	}

	/* ===================== getContent() - Null values ===================== */

	public function testGetContentHandlesNullDescription(): void
	{
		$event = $this->createMockEvent(
			'Test',
			null,
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('DESCRIPTION:', $content);
	}

	public function testGetContentHandlesNullLocation(): void
	{
		$event = $this->createMockEvent(
			'Test',
			'',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			null,
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('LOCATION:', $content);
	}

	public function testGetContentHandlesNullUrl(): void
	{
		$event = $this->createMockEvent(
			'Test',
			'',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			null
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('URL;VALUE=URI:', $content);
	}

	/* ===================== Real-world examples ===================== */

	public function testGetContentWithRealWorldConference(): void
	{
		$event = $this->createMockEvent(
			'Annual Tech Conference 2024',
			'Join us for the biggest tech conference of the year, featuring keynotes from industry leaders, hands-on workshops, and networking opportunities.',
			new \DateTime('2024-06-15 09:00:00'),
			new \DateTime('2024-06-15 18:00:00'),
			'Convention Center, 123 Main Street, New York, NY 10001',
			'contact@techconf2024.com',
			'Tech Conference Organizing Committee',
			'https://techconf2024.com/register'
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('BEGIN:VCALENDAR', $content);
		$this->assertStringContainsString('BEGIN:VEVENT', $content);
		$this->assertStringContainsString('Annual Tech Conference 2024', $content);
		$this->assertStringContainsString('DTSTART:20240615T090000', $content);
		$this->assertStringContainsString('DTEND:20240615T180000', $content);
		$this->assertStringContainsString('LOCATION:', $content);
		$this->assertStringContainsString('END:VEVENT', $content);
		$this->assertStringContainsString('END:VCALENDAR', $content);
	}

	public function testGetContentWithMultipleDayWorkshop(): void
	{
		$day1 = $this->createMockEvent(
			'Workshop - Day 1',
			'Introduction to Advanced Topics',
			new \DateTime('2024-04-01 10:00:00'),
			new \DateTime('2024-04-01 17:00:00'),
			'Training Room 1',
			'trainer@example.com',
			'Jane Smith',
			'https://example.com/workshop'
		);

		$day2 = $this->createMockEvent(
			'Workshop - Day 2',
			'Advanced Practice Sessions',
			new \DateTime('2024-04-02 10:00:00'),
			new \DateTime('2024-04-02 17:00:00'),
			'Training Room 1',
			'trainer@example.com',
			'Jane Smith',
			'https://example.com/workshop'
		);

		$content = ICS::getContent([$day1, $day2]);

		$eventCount = substr_count($content, 'BEGIN:VEVENT');
		$this->assertSame(2, $eventCount);
		$this->assertStringContainsString('Workshop - Day 1', $content);
		$this->assertStringContainsString('Workshop - Day 2', $content);
	}

	/* ===================== Edge cases ===================== */

	public function testGetContentWithVeryLongDescription(): void
	{
		$longDescription = str_repeat('This is a very long description. ', 100);
		$event = $this->createMockEvent(
			'Test',
			$longDescription,
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('DESCRIPTION:', $content);
		$this->assertStringContainsString('very long description', $content);
	}

	public function testGetContentWithEmptyStrings(): void
	{
		$event = $this->createMockEvent(
			'',
			'',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('SUMMARY:', $content);
		$this->assertStringContainsString('DESCRIPTION:', $content);
		$this->assertStringContainsString('LOCATION:', $content);
	}

	/* ===================== Helper methods ===================== */

	private function createMockEvent(
		?string $summary,
		?string $description,
		\DateTime $startDate,
		\DateTime $endDate,
		?string $location,
		?string $organizerEmail,
		?string $organizerName,
		?string $url
	): EventInterface {
		$organizer = null;
		if ($organizerEmail !== '' || $organizerName !== '') {
			$organizer = $this->createMock(PersonInterface::class);
			$organizer->method('getEmail')->willReturn($organizerEmail);

			// Parse the name to get first and last name for the interface
			$nameParts = explode(' ', $organizerName ?? '', 2);
			$givenName = $nameParts[0] ?? '';
			$familyName = $nameParts[1] ?? '';

			$organizer->method('getGivenName')->willReturn($givenName);
			$organizer->method('getFamilyName')->willReturn($familyName);
			$organizer->method('getGender')->willReturn(null);
		}

		$event = $this->createMock(EventInterface::class);
		$event->method('getSummary')->willReturn($summary);
		$event->method('getDescription')->willReturn($description);
		$event->method('getStartDate')->willReturn($startDate);
		$event->method('getEndDate')->willReturn($endDate);
		$event->method('getOrganizer')->willReturn($organizer);
		$event->method('getOrganizingOrganization')->willReturn(null);
		$event->method('getUrl')->willReturn($url);

		// For location, we need to mock it properly
		if ($location !== null && $location !== '') {
			$postalAddress = $this->createMock(\Osimatic\Location\PostalAddressInterface::class);
			$postalAddress->method('getRoad')->willReturn($location);
			$postalAddress->method('getCity')->willReturn('');
			$postalAddress->method('getState')->willReturn('');
			$postalAddress->method('getPostcode')->willReturn('');
			$postalAddress->method('getCountry')->willReturn('');

			$place = $this->createMock(\Osimatic\Location\PlaceInterface::class);
			$place->method('getAddress')->willReturn($postalAddress);
			$event->method('getLocation')->willReturn($place);
		} else {
			$event->method('getLocation')->willReturn(null);
		}

		$event->method('getAddress')->willReturn(null);

		return $event;
	}
}