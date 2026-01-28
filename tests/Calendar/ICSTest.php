<?php

declare(strict_types=1);

namespace Tests\Calendar;

use Osimatic\Calendar\EventInterface;
use Osimatic\Calendar\ICS;
use Osimatic\Person\PersonInterface;
use PHPUnit\Framework\TestCase;

final class ICSTest extends TestCase
{
	// ========== Constants Tests ==========

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

	// ========== Validation Methods Tests ==========

	public function testCheckFileWithValidIcsExtension(): void
	{
		$tempFile = tempnam(sys_get_temp_dir(), 'test');
		rename($tempFile, $tempFile . '.ics');
		$tempFile .= '.ics';

		$result = ICS::checkFile($tempFile, 'event.ics');

		$this->assertTrue($result);
		unlink($tempFile);
	}

	public function testCheckFileWithValidVcsExtension(): void
	{
		$tempFile = tempnam(sys_get_temp_dir(), 'test');
		rename($tempFile, $tempFile . '.vcs');
		$tempFile .= '.vcs';

		$result = ICS::checkFile($tempFile, 'event.vcs');

		$this->assertTrue($result);
		unlink($tempFile);
	}

	public function testCheckFileWithValidIcalExtension(): void
	{
		$tempFile = tempnam(sys_get_temp_dir(), 'test');
		rename($tempFile, $tempFile . '.ical');
		$tempFile .= '.ical';

		$result = ICS::checkFile($tempFile, 'event.ical');

		$this->assertTrue($result);
		unlink($tempFile);
	}

	public function testCheckFileWithValidIfbExtension(): void
	{
		$tempFile = tempnam(sys_get_temp_dir(), 'test');
		rename($tempFile, $tempFile . '.ifb');
		$tempFile .= '.ifb';

		$result = ICS::checkFile($tempFile, 'event.ifb');

		$this->assertTrue($result);
		unlink($tempFile);
	}

	public function testValidateContentWithValidIcs(): void
	{
		$validContent = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:test\r\nEND:VCALENDAR";

		$result = ICS::validateContent($validContent);

		$this->assertTrue($result);
	}

	public function testValidateContentWithValidIcsAndErrorsArray(): void
	{
		$validContent = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:test\r\nEND:VCALENDAR";
		$errors = [];

		$result = ICS::validateContent($validContent, $errors);

		$this->assertTrue($result);
		$this->assertEmpty($errors);
	}

	public function testValidateContentWithMissingBeginVcalendar(): void
	{
		$invalidContent = "VERSION:2.0\r\nPRODID:test\r\nEND:VCALENDAR";
		$errors = [];

		$result = ICS::validateContent($invalidContent, $errors);

		$this->assertFalse($result);
		$this->assertContains('Missing BEGIN:VCALENDAR', $errors);
	}

	public function testValidateContentWithMissingEndVcalendar(): void
	{
		$invalidContent = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:test";
		$errors = [];

		$result = ICS::validateContent($invalidContent, $errors);

		$this->assertFalse($result);
		$this->assertContains('Missing END:VCALENDAR', $errors);
	}

	public function testValidateContentWithMissingVersion(): void
	{
		$invalidContent = "BEGIN:VCALENDAR\r\nPRODID:test\r\nEND:VCALENDAR";
		$errors = [];

		$result = ICS::validateContent($invalidContent, $errors);

		$this->assertFalse($result);
		$this->assertContains('Missing or invalid VERSION (must be 2.0)', $errors);
	}

	public function testValidateContentWithMissingProdid(): void
	{
		$invalidContent = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nEND:VCALENDAR";
		$errors = [];

		$result = ICS::validateContent($invalidContent, $errors);

		$this->assertFalse($result);
		$this->assertContains('Missing PRODID', $errors);
	}

	public function testValidateContentWithMismatchedVeventBlocks(): void
	{
		$invalidContent = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:test\r\nBEGIN:VEVENT\r\nSUMMARY:Test\r\nEND:VCALENDAR";
		$errors = [];

		$result = ICS::validateContent($invalidContent, $errors);

		$this->assertFalse($result);
		$this->assertCount(1, $errors);
		$this->assertStringContainsString('Mismatched VEVENT blocks', $errors[0]);
	}

	public function testValidateContentWithMultipleErrors(): void
	{
		$invalidContent = "VERSION:2.0";
		$errors = [];

		$result = ICS::validateContent($invalidContent, $errors);

		$this->assertFalse($result);
		$this->assertGreaterThanOrEqual(3, count($errors));
	}

	// ========== Basic Generation Methods Tests ==========

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

		$this->assertStringContainsString('Event 1', $content);
		$this->assertStringContainsString('Event 2', $content);
		$this->assertStringContainsString('Event 3', $content);

		$eventCount = substr_count($content, 'BEGIN:VEVENT');
		$this->assertSame(3, $eventCount);
	}

	public function testGetContentWithTimezoneSupport(): void
	{
		$event = $this->createMockEvent(
			'UTC Event',
			'Test UTC format',
			new \DateTime('2024-03-15 10:00:00', new \DateTimeZone('Europe/Paris')),
			new \DateTime('2024-03-15 11:00:00', new \DateTimeZone('Europe/Paris')),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event], true);

		// With timezone=true, dates should be converted to UTC and have Z suffix
		$this->assertStringContainsString('DTSTART:20240315T090000Z', $content);
		$this->assertStringContainsString('DTEND:20240315T100000Z', $content);
		$this->assertStringContainsString('DTSTAMP:', $content);
		$this->assertMatchesRegularExpression('/DTSTAMP:\d{8}T\d{6}Z/', $content);
	}

	public function testGetContentWithoutTimezoneSupport(): void
	{
		$event = $this->createMockEvent(
			'Local Event',
			'Test local format',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event], false);

		// Without timezone, should not have Z suffix
		$this->assertStringContainsString('DTSTART:20240315T100000', $content);
		$this->assertStringContainsString('DTEND:20240315T110000', $content);
		$this->assertStringNotContainsString('DTSTART:20240315T100000Z', $content);
	}

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

		preg_match('/DTSTAMP:(\d{8}T\d{6})/', $content, $matches);
		$this->assertNotEmpty($matches, 'DTSTAMP should be present');

		$dtstampStr = $matches[1];
		$dtstamp = \DateTime::createFromFormat('Ymd\THis', $dtstampStr);

		$this->assertGreaterThanOrEqual($beforeTime->format('Ymd\THis'), $dtstamp->format('Ymd\THis'));
		$this->assertLessThanOrEqual($afterTime->format('Ymd\THis'), $dtstamp->format('Ymd\THis'));
	}

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

	public function testGetContentEscapesBackslash(): void
	{
		$event = $this->createMockEvent(
			'Test',
			'Path: C:\\Users\\Documents\\file.txt',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('C:\\\\Users\\\\Documents\\\\file.txt', $content);
	}

	public function testGetContentEscapesNewlines(): void
	{
		$event = $this->createMockEvent(
			'Test',
			"Line 1\nLine 2\nLine 3",
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('Line 1\\nLine 2\\nLine 3', $content);
	}

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

		preg_match_all('/UID:([^\r\n]+)/', $content, $matches);
		$uids = $matches[1];

		$this->assertCount(2, $uids);
		$this->assertNotEquals($uids[0], $uids[1], 'UIDs should be unique');
	}

	public function testGetContentHandlesNullValues(): void
	{
		$event = $this->createMockEvent(
			'Test',
			null,
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			null,
			'',
			'',
			null
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('DESCRIPTION:', $content);
		$this->assertStringContainsString('LOCATION:', $content);
	}

	public function testGenerateFileCreatesValidIcsFile(): void
	{
		$event = $this->createMockEvent(
			'Test Event',
			'Test Description',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'Test Location',
			'test@example.com',
			'Test Organizer',
			'https://example.com'
		);

		$tempFile = sys_get_temp_dir() . '/test_' . uniqid() . '.ics';

		ICS::generateFile([$event], $tempFile);

		$this->assertFileExists($tempFile);

		$content = file_get_contents($tempFile);
		$this->assertStringContainsString('BEGIN:VCALENDAR', $content);
		$this->assertStringContainsString('Test Event', $content);
		$this->assertStringContainsString('END:VCALENDAR', $content);

		unlink($tempFile);
	}

	public function testGenerateFileWithTimezone(): void
	{
		$event = $this->createMockEvent(
			'UTC Event',
			'',
			new \DateTime('2024-03-15 10:00:00', new \DateTimeZone('Europe/Paris')),
			new \DateTime('2024-03-15 11:00:00', new \DateTimeZone('Europe/Paris')),
			'',
			'',
			'',
			''
		);

		$tempFile = sys_get_temp_dir() . '/test_' . uniqid() . '.ics';

		ICS::generateFile([$event], $tempFile, true);

		$this->assertFileExists($tempFile);

		$content = file_get_contents($tempFile);
		$this->assertStringContainsString('20240315T090000Z', $content);

		unlink($tempFile);
	}

	// ========== Extended Generation Methods Tests ==========

	public function testGetExtendedContentWithBasicOptions(): void
	{
		$event = $this->createMockEvent(
			'Extended Event',
			'Test extended features',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getExtendedContent([$event]);

		$this->assertStringContainsString('BEGIN:VCALENDAR', $content);
		$this->assertStringContainsString('METHOD:REQUEST', $content);
		$this->assertStringContainsString('Extended Event', $content);
		$this->assertStringContainsString('END:VCALENDAR', $content);
	}

	public function testGetExtendedContentWithAlarms(): void
	{
		$event = $this->createMockEvent(
			'Event with Alarm',
			'',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getExtendedContent([$event], [
			'include_alarms' => true,
			'alarm_minutes' => 30
		]);

		$this->assertStringContainsString('BEGIN:VALARM', $content);
		$this->assertStringContainsString('ACTION:DISPLAY', $content);
		$this->assertStringContainsString('TRIGGER:-PT30M', $content);
		$this->assertStringContainsString('END:VALARM', $content);
	}

	public function testGetExtendedContentWithDefaultAlarmMinutes(): void
	{
		$event = $this->createMockEvent(
			'Event',
			'',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getExtendedContent([$event], ['include_alarms' => true]);

		$this->assertStringContainsString('TRIGGER:-PT15M', $content);
	}

	public function testGetExtendedContentWithRecurrenceRule(): void
	{
		$event = $this->createMockEvent(
			'Recurring Event',
			'',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getExtendedContent([$event], [
			'recurrence_rule' => 'FREQ=WEEKLY;BYDAY=MO,WE,FR;COUNT=10'
		]);

		$this->assertStringContainsString('RRULE:FREQ=WEEKLY;BYDAY=MO,WE,FR;COUNT=10', $content);
	}

	public function testGetExtendedContentWithTimezone(): void
	{
		$event = $this->createMockEvent(
			'Event',
			'',
			new \DateTime('2024-03-15 10:00:00', new \DateTimeZone('Europe/Paris')),
			new \DateTime('2024-03-15 11:00:00', new \DateTimeZone('Europe/Paris')),
			'',
			'',
			'',
			''
		);

		$content = ICS::getExtendedContent([$event], ['include_timezone' => true]);

		$this->assertStringContainsString('20240315T090000Z', $content);
	}

	public function testGetExtendedContentWithAllOptions(): void
	{
		$event = $this->createMockEvent(
			'Full Event',
			'Event with all options',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getExtendedContent([$event], [
			'include_alarms' => true,
			'alarm_minutes' => 45,
			'include_timezone' => true,
			'recurrence_rule' => 'FREQ=DAILY;COUNT=5'
		]);

		$this->assertStringContainsString('BEGIN:VALARM', $content);
		$this->assertStringContainsString('TRIGGER:-PT45M', $content);
		$this->assertStringContainsString('RRULE:FREQ=DAILY;COUNT=5', $content);
		$this->assertMatchesRegularExpression('/\d{8}T\d{6}Z/', $content);
	}

	public function testAddEventToContentAddsEventToExistingContent(): void
	{
		$event1 = $this->createMockEvent(
			'Existing Event',
			'',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'',
			'',
			'',
			''
		);

		$existingContent = ICS::getContent([$event1]);

		$event2 = $this->createMockEvent(
			'New Event',
			'',
			new \DateTime('2024-03-16 10:00:00'),
			new \DateTime('2024-03-16 11:00:00'),
			'',
			'',
			'',
			''
		);

		$updatedContent = ICS::addEventToContent($existingContent, $event2);

		$this->assertStringContainsString('Existing Event', $updatedContent);
		$this->assertStringContainsString('New Event', $updatedContent);

		$eventCount = substr_count($updatedContent, 'BEGIN:VEVENT');
		$this->assertSame(2, $eventCount);
	}

	public function testAddEventToContentPreservesStructure(): void
	{
		$existingContent = ICS::getContent([]);

		$event = $this->createMockEvent(
			'Added Event',
			'',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'',
			'',
			'',
			''
		);

		$updatedContent = ICS::addEventToContent($existingContent, $event);

		$this->assertStringContainsString('BEGIN:VCALENDAR', $updatedContent);
		$this->assertStringContainsString('VERSION:2.0', $updatedContent);
		$this->assertStringContainsString('Added Event', $updatedContent);
		$this->assertStringContainsString('END:VCALENDAR', $updatedContent);
	}

	// ========== Parsing Methods Tests ==========

	public function testParseFileWithNonExistentFile(): void
	{
		$nonExistentFile = sys_get_temp_dir() . '/non_existent_' . uniqid() . '.ics';
		$baseEvent = $this->createMockEvent('', '', new \DateTime(), new \DateTime(), '', '', '', '');
		$errors = [];

		$events = ICS::parseFile($nonExistentFile, $baseEvent, 'UTC', $errors);

		$this->assertEmpty($events);
		$this->assertNotEmpty($errors);
		$this->assertStringContainsString('not found', $errors[0]);
	}

	public function testParseFileWithInvalidContent(): void
	{
		$tempFile = sys_get_temp_dir() . '/test_' . uniqid() . '.ics';
		file_put_contents($tempFile, "INVALID CONTENT");

		$baseEvent = $this->createMockEvent('', '', new \DateTime(), new \DateTime(), '', '', '', '');
		$errors = [];

		$events = ICS::parseFile($tempFile, $baseEvent, 'UTC', $errors);

		$this->assertEmpty($events);
		$this->assertNotEmpty($errors);

		unlink($tempFile);
	}

	public function testParseFileWithValidIcsFile(): void
	{
		// Create a valid ICS file
		$event = $this->createMockEvent(
			'Test Event',
			'Test Description',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'Test Location',
			'test@example.com',
			'Test Organizer',
			'https://example.com'
		);

		$tempFile = sys_get_temp_dir() . '/test_' . uniqid() . '.ics';
		ICS::generateFile([$event], $tempFile);

		$baseEvent = $this->createMockEvent('', '', new \DateTime(), new \DateTime(), '', '', '', '');
		$errors = [];

		$parsedEvents = ICS::parseFile($tempFile, $baseEvent, 'UTC', $errors);

		$this->assertNotEmpty($parsedEvents);
		$this->assertEmpty($errors);

		unlink($tempFile);
	}

	public function testParseFileWithoutErrorsParameter(): void
	{
		$nonExistentFile = sys_get_temp_dir() . '/non_existent_' . uniqid() . '.ics';
		$baseEvent = $this->createMockEvent('', '', new \DateTime(), new \DateTime(), '', '', '', '');

		// Should not throw exception when errors parameter is not provided
		$events = ICS::parseFile($nonExistentFile, $baseEvent);

		$this->assertEmpty($events);
	}

	// ========== Utility Methods Tests ==========

	public function testGetEventCountWithEmptyContent(): void
	{
		$content = "BEGIN:VCALENDAR\r\nVERSION:2.0\r\nPRODID:test\r\nEND:VCALENDAR";

		$count = ICS::getEventCount($content);

		$this->assertSame(0, $count);
	}

	public function testGetEventCountWithSingleEvent(): void
	{
		$event = $this->createMockEvent(
			'Event',
			'',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);
		$count = ICS::getEventCount($content);

		$this->assertSame(1, $count);
	}

	public function testGetEventCountWithMultipleEvents(): void
	{
		$events = [];
		for ($i = 0; $i < 5; $i++) {
			$events[] = $this->createMockEvent(
				"Event $i",
				'',
				new \DateTime('2024-03-15 10:00:00'),
				new \DateTime('2024-03-15 11:00:00'),
				'',
				'',
				'',
				''
			);
		}

		$content = ICS::getContent($events);
		$count = ICS::getEventCount($content);

		$this->assertSame(5, $count);
	}

	public function testGetSingleEventContentGeneratesValidIcs(): void
	{
		$event = $this->createMockEvent(
			'Single Event',
			'Test single event',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getSingleEventContent($event);

		$this->assertStringContainsString('BEGIN:VCALENDAR', $content);
		$this->assertStringContainsString('Single Event', $content);
		$this->assertStringContainsString('END:VCALENDAR', $content);

		$eventCount = substr_count($content, 'BEGIN:VEVENT');
		$this->assertSame(1, $eventCount);
	}

	public function testGetSingleEventContentWithTimezone(): void
	{
		$event = $this->createMockEvent(
			'Single Event',
			'',
			new \DateTime('2024-03-15 10:00:00', new \DateTimeZone('Europe/Paris')),
			new \DateTime('2024-03-15 11:00:00', new \DateTimeZone('Europe/Paris')),
			'',
			'',
			'',
			''
		);

		$content = ICS::getSingleEventContent($event, true);

		$this->assertStringContainsString('20240315T090000Z', $content);
	}

	public function testGetSingleEventContentEquivalentToGetContentWithOneEvent(): void
	{
		$event = $this->createMockEvent(
			'Test Event',
			'Test',
			new \DateTime('2024-03-15 10:00:00'),
			new \DateTime('2024-03-15 11:00:00'),
			'',
			'',
			'',
			''
		);

		$singleContent = ICS::getSingleEventContent($event);
		$arrayContent = ICS::getContent([$event]);

		// UIDs will be different, so compare structure
		$this->assertStringContainsString('Test Event', $singleContent);
		$this->assertStringContainsString('Test Event', $arrayContent);
		$this->assertSame(substr_count($singleContent, 'BEGIN:VEVENT'), substr_count($arrayContent, 'BEGIN:VEVENT'));
	}

	// ========== Real-world Examples Tests ==========

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
		$this->assertStringContainsString('END:VEVENT', $content);
		$this->assertStringContainsString('END:VCALENDAR', $content);
	}

	public function testGetContentWithRecurringMeeting(): void
	{
		$event = $this->createMockEvent(
			'Weekly Team Standup',
			'15-minute sync meeting',
			new \DateTime('2024-03-18 09:00:00'),
			new \DateTime('2024-03-18 09:15:00'),
			'Video Conference',
			'manager@example.com',
			'Team Manager',
			'https://meet.example.com/standup'
		);

		$content = ICS::getExtendedContent([$event], [
			'recurrence_rule' => 'FREQ=WEEKLY;BYDAY=MO;COUNT=52',
			'include_alarms' => true,
			'alarm_minutes' => 5
		]);

		$this->assertStringContainsString('Weekly Team Standup', $content);
		$this->assertStringContainsString('RRULE:FREQ=WEEKLY;BYDAY=MO;COUNT=52', $content);
		$this->assertStringContainsString('TRIGGER:-PT5M', $content);
	}

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

	// ========== Edge Cases Tests ==========

	public function testGetContentWithComplexSpecialCharacters(): void
	{
		$event = $this->createMockEvent(
			'Test "Quotes" & \'Apostrophes\'',
			'Special chars: @#$%^&*()_+-=[]{}|<>?/',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('BEGIN:VEVENT', $content);
		$this->assertStringContainsString('END:VEVENT', $content);
	}

	public function testGetContentWithEventSpanningMultipleDays(): void
	{
		$event = $this->createMockEvent(
			'Multi-day Conference',
			'3-day event',
			new \DateTime('2024-06-15 09:00:00'),
			new \DateTime('2024-06-17 18:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('DTSTART:20240615T090000', $content);
		$this->assertStringContainsString('DTEND:20240617T180000', $content);
	}

	public function testGetContentWithMidnightTimes(): void
	{
		$event = $this->createMockEvent(
			'Midnight Event',
			'',
			new \DateTime('2024-01-01 00:00:00'),
			new \DateTime('2024-01-01 00:30:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('DTSTART:20240101T000000', $content);
		$this->assertStringContainsString('DTEND:20240101T003000', $content);
	}

	public function testGetContentWithLeapYearDate(): void
	{
		$event = $this->createMockEvent(
			'Leap Year Event',
			'',
			new \DateTime('2024-02-29 12:00:00'),
			new \DateTime('2024-02-29 13:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);

		$this->assertStringContainsString('DTSTART:20240229T120000', $content);
		$this->assertStringContainsString('DTEND:20240229T130000', $content);
	}

	public function testValidateContentIsCalledDuringGeneration(): void
	{
		$event = $this->createMockEvent(
			'Test Event',
			'',
			new \DateTime('2024-01-01 12:00:00'),
			new \DateTime('2024-01-01 13:00:00'),
			'',
			'',
			'',
			''
		);

		$content = ICS::getContent([$event]);
		$errors = [];

		$isValid = ICS::validateContent($content, $errors);

		$this->assertTrue($isValid);
		$this->assertEmpty($errors);
	}

	// ========== Helper Methods ==========

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