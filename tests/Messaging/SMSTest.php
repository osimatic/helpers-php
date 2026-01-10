<?php

declare(strict_types=1);

namespace Tests\Messaging;

use Osimatic\Messaging\SMS;
use PHPUnit\Framework\TestCase;

final class SMSTest extends TestCase
{
	/* ===================== Constructor ===================== */

	public function testCanBeInstantiated(): void
	{
		$sms = new SMS();
		$this->assertInstanceOf(SMS::class, $sms);
	}

	public function testConstructorSetsSendingDateTime(): void
	{
		$sms = new SMS();
		$this->assertInstanceOf(\DateTime::class, $sms->getSendingDateTime());
	}

	/* ===================== Constants ===================== */

	public function testMessageNbCharMaxConstant(): void
	{
		$this->assertSame(160, SMS::MESSAGE_NB_CHAR_MAX);
	}

	/* ===================== Identifier ===================== */

	public function testGetIdentifierDefaultIsNull(): void
	{
		$sms = new SMS();
		$this->assertNull($sms->getIdentifier());
	}

	public function testSetIdentifier(): void
	{
		$sms = new SMS();
		$sms->setIdentifier('sms-id-123');
		$this->assertSame('sms-id-123', $sms->getIdentifier());
	}

	public function testSetIdentifierNull(): void
	{
		$sms = new SMS();
		$sms->setIdentifier('sms-id');
		$sms->setIdentifier(null);
		$this->assertNull($sms->getIdentifier());
	}

	/* ===================== Sender Name ===================== */

	public function testGetSenderNameDefaultIsNull(): void
	{
		$sms = new SMS();
		$this->assertNull($sms->getSenderName());
	}

	public function testSetSenderName(): void
	{
		$sms = new SMS();
		$result = $sms->setSenderName('MySender');
		$this->assertInstanceOf(SMS::class, $result);
		$this->assertSame('MySender', $sms->getSenderName());
	}

	public function testSetSenderNameNull(): void
	{
		$sms = new SMS();
		$sms->setSenderName('MySender');
		$sms->setSenderName(null);
		$this->assertNull($sms->getSenderName());
	}

	/* ===================== Recipients ===================== */

	public function testGetListRecipientsDefaultIsEmpty(): void
	{
		$sms = new SMS();
		$this->assertIsArray($sms->getListRecipients());
		$this->assertEmpty($sms->getListRecipients());
	}

	public function testGetRecipientDefaultIsNull(): void
	{
		$sms = new SMS();
		$this->assertNull($sms->getRecipient());
	}

	public function testGetListPhoneNumbers(): void
	{
		$sms = new SMS();
		$this->assertIsArray($sms->getListPhoneNumbers());
		$this->assertEmpty($sms->getListPhoneNumbers());
	}

	public function testGetPhoneNumber(): void
	{
		$sms = new SMS();
		$this->assertNull($sms->getPhoneNumber());
	}

	public function testGetNbRecipientsDefaultIsZero(): void
	{
		$sms = new SMS();
		$this->assertSame(0, $sms->getNbRecipients());
	}

	public function testGetNbPhoneNumbers(): void
	{
		$sms = new SMS();
		$this->assertSame(0, $sms->getNbPhoneNumbers());
	}

	public function testAddRecipient(): void
	{
		$sms = new SMS();
		$result = $sms->addRecipient('+33612345678');
		$this->assertInstanceOf(SMS::class, $result);
		$recipients = $sms->getListRecipients();
		$this->assertCount(1, $recipients);
		$this->assertSame('+33612345678', $recipients[0]);
	}

	public function testAddRecipientInvalidNumber(): void
	{
		$sms = new SMS();
		$sms->addRecipient('invalid-number');
		$this->assertEmpty($sms->getListRecipients());
	}

	public function testAddMultipleRecipients(): void
	{
		$sms = new SMS();
		$sms->addRecipient('+33612345678');
		$sms->addRecipient('+33687654321');
		$this->assertCount(2, $sms->getListRecipients());
		$this->assertSame(2, $sms->getNbRecipients());
	}

	public function testAddDuplicateRecipientDoesNotAddTwice(): void
	{
		$sms = new SMS();
		$sms->addRecipient('+33612345678');
		$sms->addRecipient('+33612345678');
		$this->assertCount(1, $sms->getListRecipients());
	}

	public function testSetRecipient(): void
	{
		$sms = new SMS();
		$sms->addRecipient('+33612345678');
		$result = $sms->setRecipient('+33687654321');
		$this->assertInstanceOf(SMS::class, $result);
		$recipients = $sms->getListRecipients();
		$this->assertCount(1, $recipients);
		$this->assertSame('+33687654321', $recipients[0]);
	}

	public function testGetRecipient(): void
	{
		$sms = new SMS();
		$sms->addRecipient('+33612345678');
		$this->assertSame('+33612345678', $sms->getRecipient());
	}

	public function testAddListRecipient(): void
	{
		$sms = new SMS();
		$recipients = ['+33612345678', '+33687654321', '+33698765432'];
		$result = $sms->addListRecipient($recipients);
		$this->assertInstanceOf(SMS::class, $result);
		$this->assertCount(3, $sms->getListRecipients());
	}

	public function testSetListRecipient(): void
	{
		$sms = new SMS();
		$sms->addRecipient('+33600000000');
		$recipients = ['+33612345678', '+33687654321'];
		$result = $sms->setListRecipient($recipients);
		$this->assertInstanceOf(SMS::class, $result);
		$this->assertCount(2, $sms->getListRecipients());
	}

	public function testAddPhoneNumber(): void
	{
		$sms = new SMS();
		$result = $sms->addPhoneNumber('+33612345678');
		$this->assertInstanceOf(SMS::class, $result);
		$this->assertCount(1, $sms->getListPhoneNumbers());
	}

	public function testSetPhoneNumber(): void
	{
		$sms = new SMS();
		$result = $sms->setPhoneNumber('+33612345678');
		$this->assertInstanceOf(SMS::class, $result);
		$this->assertSame('+33612345678', $sms->getPhoneNumber());
	}

	public function testSetListPhoneNumber(): void
	{
		$sms = new SMS();
		$recipients = ['+33612345678', '+33687654321'];
		$result = $sms->setListPhoneNumber($recipients);
		$this->assertInstanceOf(SMS::class, $result);
		$this->assertCount(2, $sms->getListPhoneNumbers());
	}

	public function testAddListPhoneNumber(): void
	{
		$sms = new SMS();
		$recipients = ['+33612345678', '+33687654321'];
		$result = $sms->addListPhoneNumber($recipients);
		$this->assertInstanceOf(SMS::class, $result);
		$this->assertCount(2, $sms->getListPhoneNumbers());
	}

	public function testClearRecipients(): void
	{
		$sms = new SMS();
		$sms->addRecipient('+33612345678');
		$sms->addRecipient('+33687654321');
		$sms->clearRecipients();
		$this->assertEmpty($sms->getListRecipients());
		$this->assertSame(0, $sms->getNbRecipients());
	}

	public function testFormatRecipients(): void
	{
		$sms = new SMS();
		$sms->addRecipient('+33612345678');
		$sms->addRecipient('+33687654321');
		$formatted = $sms->formatRecipients();
		$this->assertIsString($formatted);
		$this->assertStringContainsString(';', $formatted);
	}

	public function testFormatRecipientsWithCustomSeparator(): void
	{
		$sms = new SMS();
		$sms->addRecipient('+33612345678');
		$sms->addRecipient('+33687654321');
		$formatted = $sms->formatRecipients(', ');
		$this->assertStringContainsString(',', $formatted);
	}

	/* ===================== Text ===================== */

	public function testGetTextDefaultIsEmpty(): void
	{
		$sms = new SMS();
		$this->assertSame('', $sms->getText());
	}

	public function testSetText(): void
	{
		$sms = new SMS();
		$result = $sms->setText('Hello World');
		$this->assertInstanceOf(SMS::class, $result);
		$this->assertSame('Hello World', $sms->getText());
	}

	public function testSetTextFormatsApostrophe(): void
	{
		$sms = new SMS();
		$sms->setText("It's a test");
		$this->assertSame("It's a test", $sms->getText());
	}

	public function testSetTextWithoutTruncation(): void
	{
		$sms = new SMS();
		$longText = str_repeat('A', 200);
		$sms->setText($longText);
		$this->assertSame(200, strlen($sms->getText()));
		$this->assertSame($longText, $sms->getText());
	}

	public function testSetTextWithTruncation(): void
	{
		$sms = new SMS();
		$sms->setTruncateText(true);
		$longText = str_repeat('A', 200);
		$sms->setText($longText);
		$this->assertSame(160, strlen($sms->getText()));
	}

	public function testSetTextFromFile(): void
	{
		$tempFile = tempnam(sys_get_temp_dir(), 'sms_test_');
		file_put_contents($tempFile, 'SMS content from file');

		$sms = new SMS();
		$result = $sms->setTextFromFile($tempFile);
		$this->assertInstanceOf(SMS::class, $result);
		$this->assertSame('SMS content from file', $sms->getText());

		unlink($tempFile);
	}

	/* ===================== Adult Content ===================== */

	public function testIsAdultContentDefaultIsFalse(): void
	{
		$sms = new SMS();
		$this->assertFalse($sms->isAdultContent());
	}

	public function testSetAdultContentTrue(): void
	{
		$sms = new SMS();
		$result = $sms->setAdultContent(true);
		$this->assertInstanceOf(SMS::class, $result);
		$this->assertTrue($sms->isAdultContent());
	}

	public function testSetAdultContentDefaultIsTrue(): void
	{
		$sms = new SMS();
		$sms->setAdultContent();
		$this->assertTrue($sms->isAdultContent());
	}

	public function testSetAdultContentFalse(): void
	{
		$sms = new SMS();
		$sms->setAdultContent(true);
		$sms->setAdultContent(false);
		$this->assertFalse($sms->isAdultContent());
	}

	/* ===================== Truncate Text ===================== */

	public function testIsTruncatedTextDefaultIsFalse(): void
	{
		$sms = new SMS();
		$this->assertFalse($sms->isTruncatedText());
	}

	public function testSetTruncateTextTrue(): void
	{
		$sms = new SMS();
		$result = $sms->setTruncateText(true);
		$this->assertInstanceOf(SMS::class, $result);
		$this->assertTrue($sms->isTruncatedText());
	}

	public function testSetTruncateTextDefaultIsTrue(): void
	{
		$sms = new SMS();
		$sms->setTruncateText();
		$this->assertTrue($sms->isTruncatedText());
	}

	public function testSetTruncateTextFalse(): void
	{
		$sms = new SMS();
		$sms->setTruncateText(true);
		$sms->setTruncateText(false);
		$this->assertFalse($sms->isTruncatedText());
	}

	/* ===================== Sending Date Time ===================== */

	public function testGetSendingDateTime(): void
	{
		$sms = new SMS();
		$this->assertInstanceOf(\DateTime::class, $sms->getSendingDateTime());
	}

	public function testSetSendingDateTime(): void
	{
		$sms = new SMS();
		$date = new \DateTime('2024-01-01 12:00:00');
		$result = $sms->setSendingDateTime($date);
		$this->assertInstanceOf(SMS::class, $result);
		$this->assertSame($date, $sms->getSendingDateTime());
	}

	public function testSetSendingDateTimeNull(): void
	{
		$sms = new SMS();
		$sms->setSendingDateTime(null);
		$this->assertNull($sms->getSendingDateTime());
	}

	/* ===================== Static Format Methods ===================== */

	public function testFormatPhoneNumberList(): void
	{
		$phoneNumbers = ['+33612345678', '+33687654321'];
		$formatted = SMS::formatPhoneNumberList($phoneNumbers);
		$this->assertIsArray($formatted);
		$this->assertCount(2, $formatted);
	}

	public function testFormatPhoneNumberListWithEmptyNumbers(): void
	{
		$phoneNumbers = ['+33612345678', '', '+33687654321', null];
		$formatted = SMS::formatPhoneNumberList($phoneNumbers);
		$this->assertCount(2, $formatted);
	}

	public function testFormatPhoneNumberListEmpty(): void
	{
		$formatted = SMS::formatPhoneNumberList([]);
		$this->assertIsArray($formatted);
		$this->assertEmpty($formatted);
	}
}