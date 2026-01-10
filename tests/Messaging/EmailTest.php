<?php

declare(strict_types=1);

namespace Tests\Messaging;

use Osimatic\Messaging\Email;
use Osimatic\Messaging\EmailCharset;
use Osimatic\Messaging\EmailContentType;
use Osimatic\Messaging\EmailEncoding;
use PHPUnit\Framework\TestCase;

final class EmailTest extends TestCase
{
	/* ===================== Constructor ===================== */

	public function testCanBeInstantiated(): void
	{
		$email = new Email();
		$this->assertInstanceOf(Email::class, $email);
	}

	public function testConstructorSetsSendingDateTime(): void
	{
		$email = new Email();
		$this->assertInstanceOf(\DateTime::class, $email->getSendingDateTime());
	}

	/* ===================== Constants ===================== */

	public function testAttachmentFilesizeMaxConstant(): void
	{
		$this->assertSame(2000000, Email::ATTACHMENT_FILESIZE_MAX);
	}

	/* ===================== Identifier ===================== */

	public function testGetIdentifierDefaultIsNull(): void
	{
		$email = new Email();
		$this->assertNull($email->getIdentifier());
	}

	public function testSetIdentifier(): void
	{
		$email = new Email();
		$email->setIdentifier('test-id-123');
		$this->assertSame('test-id-123', $email->getIdentifier());
	}

	public function testSetIdentifierNull(): void
	{
		$email = new Email();
		$email->setIdentifier('test-id');
		$email->setIdentifier(null);
		$this->assertNull($email->getIdentifier());
	}

	/* ===================== From Address ===================== */

	public function testGetFromEmailAddressDefaultIsNull(): void
	{
		$email = new Email();
		$this->assertNull($email->getFromEmailAddress());
	}

	public function testSetFromEmailAddressWithValidEmail(): void
	{
		$email = new Email();
		$result = $email->setFromEmailAddress('sender@example.com');
		$this->assertInstanceOf(Email::class, $result);
		$this->assertSame('sender@example.com', $email->getFromEmailAddress());
	}

	public function testSetFromEmailAddressWithInvalidEmail(): void
	{
		$email = new Email();
		$email->setFromEmailAddress('invalid-email');
		$this->assertNull($email->getFromEmailAddress());
	}

	public function testSetFromEmailAddressTrimmsWhitespace(): void
	{
		$email = new Email();
		$email->setFromEmailAddress('  sender@example.com  ');
		$this->assertSame('sender@example.com', $email->getFromEmailAddress());
	}

	public function testSetFromEmailAddressNull(): void
	{
		$email = new Email();
		$email->setFromEmailAddress('sender@example.com');
		$email->setFromEmailAddress(null);
		$this->assertNull($email->getFromEmailAddress());
	}

	/* ===================== From Name ===================== */

	public function testGetFromNameDefaultIsNull(): void
	{
		$email = new Email();
		$this->assertNull($email->getFromName());
	}

	public function testSetFromName(): void
	{
		$email = new Email();
		$result = $email->setFromName('John Doe');
		$this->assertInstanceOf(Email::class, $result);
		$this->assertSame('John Doe', $email->getFromName());
	}

	public function testSetFromNameRemovesLineBreaks(): void
	{
		$email = new Email();
		$email->setFromName("John\r\nDoe");
		$this->assertSame('JohnDoe', $email->getFromName());
	}

	public function testSetFromNameTrimmsWhitespace(): void
	{
		$email = new Email();
		$email->setFromName('  John Doe  ');
		$this->assertSame('John Doe', $email->getFromName());
	}

	public function testSetFromNameNull(): void
	{
		$email = new Email();
		$email->setFromName('John Doe');
		$email->setFromName(null);
		$this->assertNull($email->getFromName());
	}

	/* ===================== setFrom() ===================== */

	public function testSetFrom(): void
	{
		$email = new Email();
		$result = $email->setFrom('sender@example.com', 'John Doe');
		$this->assertInstanceOf(Email::class, $result);
		$this->assertSame('sender@example.com', $email->getFromEmailAddress());
		$this->assertSame('John Doe', $email->getFromName());
	}

	public function testSetFromAutoSetsSender(): void
	{
		$email = new Email();
		$email->setFrom('sender@example.com', 'John Doe', true);
		$this->assertSame('sender@example.com', $email->getSender());
	}

	public function testSetFromDoesNotAutoSetSender(): void
	{
		$email = new Email();
		$email->setFrom('sender@example.com', 'John Doe', false);
		$this->assertNull($email->getSender());
	}

	/* ===================== Sender ===================== */

	public function testGetSenderDefaultIsNull(): void
	{
		$email = new Email();
		$this->assertNull($email->getSender());
	}

	public function testSetSender(): void
	{
		$email = new Email();
		$result = $email->setSender('bounce@example.com');
		$this->assertInstanceOf(Email::class, $result);
		$this->assertSame('bounce@example.com', $email->getSender());
	}

	/* ===================== Confirm Reading To ===================== */

	public function testGetConfirmReadingToDefaultIsNull(): void
	{
		$email = new Email();
		$this->assertNull($email->getConfirmReadingTo());
	}

	public function testSetConfirmReadingToWithValidEmail(): void
	{
		$email = new Email();
		$result = $email->setConfirmReadingTo('confirm@example.com');
		$this->assertInstanceOf(Email::class, $result);
		$this->assertSame('confirm@example.com', $email->getConfirmReadingTo());
	}

	public function testSetConfirmReadingToWithInvalidEmail(): void
	{
		$email = new Email();
		$email->setConfirmReadingTo('invalid-email');
		$this->assertNull($email->getConfirmReadingTo());
	}

	public function testSetConfirmReadingToNull(): void
	{
		$email = new Email();
		$email->setConfirmReadingTo('confirm@example.com');
		$email->setConfirmReadingTo(null);
		$this->assertNull($email->getConfirmReadingTo());
	}

	/* ===================== Clear Sender ===================== */

	public function testClearSender(): void
	{
		$email = new Email();
		$email->setFrom('sender@example.com', 'John Doe');
		$email->clearSender();
		$this->assertNull($email->getFromEmailAddress());
		$this->assertNull($email->getFromName());
		$this->assertNull($email->getSender());
	}

	/* ===================== Reply-To ===================== */

	public function testGetReplyToDefaultIsEmpty(): void
	{
		$email = new Email();
		$this->assertIsArray($email->getReplyTo());
		$this->assertEmpty($email->getReplyTo());
	}

	public function testAddReplyTo(): void
	{
		$email = new Email();
		$result = $email->addReplyTo('reply@example.com', 'Reply Name');
		$this->assertInstanceOf(Email::class, $result);
		$replyTo = $email->getReplyTo();
		$this->assertCount(1, $replyTo);
		$this->assertSame('reply@example.com', $replyTo[0][0]);
		$this->assertSame('Reply Name', $replyTo[0][1]);
	}

	public function testAddMultipleReplyTo(): void
	{
		$email = new Email();
		$email->addReplyTo('reply1@example.com', 'Reply 1');
		$email->addReplyTo('reply2@example.com', 'Reply 2');
		$this->assertCount(2, $email->getReplyTo());
	}

	public function testSetReplyTo(): void
	{
		$email = new Email();
		$email->addReplyTo('old@example.com');
		$email->setReplyTo('new@example.com', 'New Reply');
		$replyTo = $email->getReplyTo();
		$this->assertCount(1, $replyTo);
		$this->assertSame('new@example.com', $replyTo[0][0]);
	}

	public function testGetReplyToEmail(): void
	{
		$email = new Email();
		$email->addReplyTo('reply@example.com', 'Reply Name');
		$this->assertSame('reply@example.com', $email->getReplyToEmail());
	}

	public function testGetReplyToEmailWhenEmpty(): void
	{
		$email = new Email();
		$this->assertSame('', $email->getReplyToEmail());
	}

	public function testClearReplyTo(): void
	{
		$email = new Email();
		$email->addReplyTo('reply@example.com');
		$email->clearReplyTo();
		$this->assertEmpty($email->getReplyTo());
	}

	public function testFormatReplyTo(): void
	{
		$email = new Email();
		$email->addReplyTo('reply@example.com', 'Reply Name');
		$formatted = $email->formatReplyTo();
		$this->assertStringContainsString('Reply Name', $formatted);
		$this->assertStringContainsString('reply@example.com', $formatted);
	}

	/* ===================== To Recipients ===================== */

	public function testGetListToDefaultIsEmpty(): void
	{
		$email = new Email();
		$this->assertIsArray($email->getListTo());
		$this->assertEmpty($email->getListTo());
	}

	public function testAddTo(): void
	{
		$email = new Email();
		$result = $email->addTo('recipient@example.com', 'Recipient Name');
		$this->assertInstanceOf(Email::class, $result);
		$to = $email->getListTo();
		$this->assertCount(1, $to);
		$this->assertSame('recipient@example.com', $to[0][0]);
		$this->assertSame('Recipient Name', $to[0][1]);
	}

	public function testAddRecipient(): void
	{
		$email = new Email();
		$email->addRecipient('recipient@example.com', 'Recipient Name');
		$this->assertCount(1, $email->getListTo());
	}

	public function testSetTo(): void
	{
		$email = new Email();
		$email->addTo('old@example.com');
		$email->setTo('new@example.com', 'New Recipient');
		$to = $email->getListTo();
		$this->assertCount(1, $to);
		$this->assertSame('new@example.com', $to[0][0]);
	}

	public function testSetRecipient(): void
	{
		$email = new Email();
		$email->setRecipient('recipient@example.com', 'Recipient');
		$this->assertCount(1, $email->getListTo());
	}

	public function testAddListTo(): void
	{
		$email = new Email();
		$recipients = [
			['to1@example.com', 'To 1'],
			['to2@example.com', 'To 2'],
		];
		$email->addListTo($recipients);
		$this->assertCount(2, $email->getListTo());
	}

	public function testSetListTo(): void
	{
		$email = new Email();
		$email->addTo('old@example.com');
		$recipients = [
			['new1@example.com', 'New 1'],
			['new2@example.com', 'New 2'],
		];
		$email->setListTo($recipients);
		$this->assertCount(2, $email->getListTo());
	}

	public function testSetListRecipients(): void
	{
		$email = new Email();
		$recipients = [['recipient@example.com', 'Recipient']];
		$email->setListRecipients($recipients);
		$this->assertCount(1, $email->getListTo());
	}

	public function testGetListToEmails(): void
	{
		$email = new Email();
		$email->addTo('to1@example.com', 'To 1');
		$email->addTo('to2@example.com', 'To 2');
		$emails = $email->getListToEmails();
		$this->assertCount(2, $emails);
		$this->assertContains('to1@example.com', $emails);
		$this->assertContains('to2@example.com', $emails);
	}

	public function testClearListTo(): void
	{
		$email = new Email();
		$email->addTo('to@example.com');
		$email->clearListTo();
		$this->assertEmpty($email->getListTo());
	}

	public function testFormatListTo(): void
	{
		$email = new Email();
		$email->addTo('to@example.com', 'To Name');
		$formatted = $email->formatListTo();
		$this->assertStringContainsString('To Name', $formatted);
		$this->assertStringContainsString('to@example.com', $formatted);
	}

	/* ===================== Cc Recipients ===================== */

	public function testGetListCcDefaultIsEmpty(): void
	{
		$email = new Email();
		$this->assertIsArray($email->getListCc());
		$this->assertEmpty($email->getListCc());
	}

	public function testAddCc(): void
	{
		$email = new Email();
		$result = $email->addCc('cc@example.com', 'CC Name');
		$this->assertInstanceOf(Email::class, $result);
		$cc = $email->getListCc();
		$this->assertCount(1, $cc);
		$this->assertSame('cc@example.com', $cc[0][0]);
	}

	public function testSetCc(): void
	{
		$email = new Email();
		$email->addCc('old@example.com');
		$email->setCc('new@example.com', 'New CC');
		$cc = $email->getListCc();
		$this->assertCount(1, $cc);
		$this->assertSame('new@example.com', $cc[0][0]);
	}

	public function testAddListCc(): void
	{
		$email = new Email();
		$recipients = [
			['cc1@example.com', 'CC 1'],
			['cc2@example.com', 'CC 2'],
		];
		$email->addListCc($recipients);
		$this->assertCount(2, $email->getListCc());
	}

	public function testSetListCc(): void
	{
		$email = new Email();
		$recipients = [['cc@example.com', 'CC']];
		$email->setListCc($recipients);
		$this->assertCount(1, $email->getListCc());
	}

	public function testGetListCcEmails(): void
	{
		$email = new Email();
		$email->addCc('cc1@example.com');
		$email->addCc('cc2@example.com');
		$emails = $email->getListCcEmails();
		$this->assertCount(2, $emails);
		$this->assertContains('cc1@example.com', $emails);
	}

	public function testClearListCc(): void
	{
		$email = new Email();
		$email->addCc('cc@example.com');
		$email->clearListCc();
		$this->assertEmpty($email->getListCc());
	}

	public function testFormatListCc(): void
	{
		$email = new Email();
		$email->addCc('cc@example.com', 'CC Name');
		$formatted = $email->formatListCc();
		$this->assertStringContainsString('CC Name', $formatted);
	}

	/* ===================== Bcc Recipients ===================== */

	public function testGetListBccDefaultIsEmpty(): void
	{
		$email = new Email();
		$this->assertIsArray($email->getListBcc());
		$this->assertEmpty($email->getListBcc());
	}

	public function testAddBcc(): void
	{
		$email = new Email();
		$result = $email->addBcc('bcc@example.com', 'BCC Name');
		$this->assertInstanceOf(Email::class, $result);
		$bcc = $email->getListBcc();
		$this->assertCount(1, $bcc);
		$this->assertSame('bcc@example.com', $bcc[0][0]);
	}

	public function testSetBcc(): void
	{
		$email = new Email();
		$email->addBcc('old@example.com');
		$email->setBcc('new@example.com', 'New BCC');
		$bcc = $email->getListBcc();
		$this->assertCount(1, $bcc);
		$this->assertSame('new@example.com', $bcc[0][0]);
	}

	public function testAddListBcc(): void
	{
		$email = new Email();
		$recipients = [
			['bcc1@example.com', 'BCC 1'],
			['bcc2@example.com', 'BCC 2'],
		];
		$email->addListBcc($recipients);
		$this->assertCount(2, $email->getListBcc());
	}

	public function testSetListBcc(): void
	{
		$email = new Email();
		$recipients = [['bcc@example.com', 'BCC']];
		$email->setListBcc($recipients);
		$this->assertCount(1, $email->getListBcc());
	}

	public function testGetListBccEmails(): void
	{
		$email = new Email();
		$email->addBcc('bcc1@example.com');
		$email->addBcc('bcc2@example.com');
		$emails = $email->getListBccEmails();
		$this->assertCount(2, $emails);
		$this->assertContains('bcc1@example.com', $emails);
	}

	public function testClearListBcc(): void
	{
		$email = new Email();
		$email->addBcc('bcc@example.com');
		$email->clearListBcc();
		$this->assertEmpty($email->getListBcc());
	}

	public function testFormatListBcc(): void
	{
		$email = new Email();
		$email->addBcc('bcc@example.com', 'BCC Name');
		$formatted = $email->formatListBcc();
		$this->assertStringContainsString('BCC Name', $formatted);
	}

	/* ===================== All Recipients ===================== */

	public function testGetAllRecipientAddresses(): void
	{
		$email = new Email();
		$email->addTo('to@example.com');
		$email->addCc('cc@example.com');
		$email->addBcc('bcc@example.com');
		$allAddresses = $email->getAllRecipientAddresses();
		$this->assertIsArray($allAddresses);
		$this->assertCount(3, $allAddresses);
	}

	public function testClearRecipients(): void
	{
		$email = new Email();
		$email->addTo('to@example.com');
		$email->addCc('cc@example.com');
		$email->addBcc('bcc@example.com');
		$email->clearRecipients();
		$this->assertEmpty($email->getListTo());
		$this->assertEmpty($email->getListCc());
		$this->assertEmpty($email->getListBcc());
		$this->assertEmpty($email->getAllRecipientAddresses());
	}

	public function testClearAllRecipients(): void
	{
		$email = new Email();
		$email->addTo('to@example.com');
		$email->clearAllRecipients();
		$this->assertEmpty($email->getListTo());
	}

	/* ===================== Attachments ===================== */

	public function testGetAttachmentsDefaultIsEmpty(): void
	{
		$email = new Email();
		$this->assertIsArray($email->getAttachments());
		$this->assertEmpty($email->getAttachments());
	}

	public function testGetListAttachments(): void
	{
		$email = new Email();
		$this->assertIsArray($email->getListAttachments());
		$this->assertEmpty($email->getListAttachments());
	}

	public function testAttachmentExistsDefaultIsFalse(): void
	{
		$email = new Email();
		$this->assertFalse($email->attachmentExists());
	}

	public function testInlineImageExistsDefaultIsFalse(): void
	{
		$email = new Email();
		$this->assertFalse($email->inlineImageExists());
	}

	public function testClearAttachments(): void
	{
		$email = new Email();
		// Assuming we had attachments, clear them
		$email->clearAttachments();
		$this->assertEmpty($email->getAttachments());
	}

	/* ===================== Subject and Text ===================== */

	public function testGetSubjectDefaultIsEmpty(): void
	{
		$email = new Email();
		$this->assertSame('', $email->getSubject());
	}

	public function testSetSubject(): void
	{
		$email = new Email();
		$result = $email->setSubject('Test Subject');
		$this->assertInstanceOf(Email::class, $result);
		$this->assertSame('Test Subject', $email->getSubject());
	}

	public function testSetSubjectFormatsText(): void
	{
		$email = new Email();
		$email->setSubject("Test's Subject");
		$this->assertSame("Test's Subject", $email->getSubject());
	}

	public function testGetTextDefaultIsEmpty(): void
	{
		$email = new Email();
		$this->assertSame('', $email->getText());
	}

	public function testSetText(): void
	{
		$email = new Email();
		$result = $email->setText('Email body text');
		$this->assertInstanceOf(Email::class, $result);
		$this->assertSame('Email body text', $email->getText());
	}

	public function testSetTextFormatsText(): void
	{
		$email = new Email();
		$email->setText("It's a test");
		$this->assertSame("It's a test", $email->getText());
	}

	/* ===================== Content Type and Format ===================== */

	public function testIsHTMLDefaultIsTrue(): void
	{
		$email = new Email();
		$this->assertTrue($email->isHTML());
	}

	public function testSetHtmlFormat(): void
	{
		$email = new Email();
		$result = $email->setHtmlFormat();
		$this->assertInstanceOf(Email::class, $result);
		$this->assertTrue($email->isHTML());
	}

	public function testSetTextFormat(): void
	{
		$email = new Email();
		$result = $email->setTextFormat();
		$this->assertInstanceOf(Email::class, $result);
		$this->assertFalse($email->isHTML());
	}

	public function testSetIsHTMLTrue(): void
	{
		$email = new Email();
		$email->setTextFormat();
		$email->setIsHTML(true);
		$this->assertTrue($email->isHTML());
	}

	public function testSetIsHTMLFalse(): void
	{
		$email = new Email();
		$email->setIsHTML(false);
		$this->assertFalse($email->isHTML());
	}

	public function testSetContentType(): void
	{
		$email = new Email();
		$result = $email->setContentType(EmailContentType::PLAINTEXT);
		$this->assertInstanceOf(Email::class, $result);
		$this->assertFalse($email->isHTML());
	}

	public function testSetContentTypeWithString(): void
	{
		$email = new Email();
		$email->setContentType('text/plain');
		$this->assertFalse($email->isHTML());
	}

	/* ===================== Character Set ===================== */

	public function testGetCharSetDefaultIsUTF8(): void
	{
		$email = new Email();
		$this->assertSame(EmailCharset::UTF8, $email->getCharSet());
	}

	public function testSetCharSet(): void
	{
		$email = new Email();
		$result = $email->setCharSet(EmailCharset::ISO88591);
		$this->assertInstanceOf(Email::class, $result);
		$this->assertSame(EmailCharset::ISO88591, $email->getCharSet());
	}

	public function testSetCharSetWithString(): void
	{
		$email = new Email();
		$email->setCharSet('ISO-8859-1');
		$this->assertSame(EmailCharset::ISO88591, $email->getCharSet());
	}

	/* ===================== Sending Date Time ===================== */

	public function testGetSendingDateTime(): void
	{
		$email = new Email();
		$this->assertInstanceOf(\DateTime::class, $email->getSendingDateTime());
	}

	public function testSetSendingDateTime(): void
	{
		$email = new Email();
		$date = new \DateTime('2024-01-01 12:00:00');
		$result = $email->setSendingDateTime($date);
		$this->assertInstanceOf(Email::class, $result);
		$this->assertSame($date, $email->getSendingDateTime());
	}

	public function testSetSendingDateTimeNull(): void
	{
		$email = new Email();
		$email->setSendingDateTime(null);
		$this->assertNull($email->getSendingDateTime());
	}

	/* ===================== Clear ===================== */

	public function testClear(): void
	{
		$email = new Email();
		$email->setFrom('sender@example.com', 'Sender');
		$email->addReplyTo('reply@example.com');
		$email->addTo('to@example.com');
		$email->clear();

		$this->assertNull($email->getFromEmailAddress());
		$this->assertNull($email->getFromName());
		$this->assertEmpty($email->getReplyTo());
		$this->assertEmpty($email->getListTo());
		$this->assertEmpty($email->getAttachments());
	}

	/* ===================== Static Format Methods ===================== */

	public function testFormatEmailAndNameWithName(): void
	{
		$formatted = Email::formatEmailAndName('test@example.com', 'Test Name');
		$this->assertStringContainsString('Test Name', $formatted);
		$this->assertStringContainsString('test@example.com', $formatted);
		$this->assertStringContainsString('&lt;', $formatted);
		$this->assertStringContainsString('&gt;', $formatted);
	}

	public function testFormatEmailAndNameWithoutName(): void
	{
		$formatted = Email::formatEmailAndName('test@example.com');
		$this->assertSame('test@example.com', $formatted);
	}

	public function testFormatEmailAndNameWithoutHtmlFormat(): void
	{
		$formatted = Email::formatEmailAndName('test@example.com', 'Test Name', false);
		$this->assertStringContainsString('<', $formatted);
		$this->assertStringContainsString('>', $formatted);
		$this->assertStringNotContainsString('&lt;', $formatted);
	}

	/* ===================== Duplicate Email Prevention ===================== */

	public function testAddingDuplicateToEmailDoesNotAddTwice(): void
	{
		$email = new Email();
		$email->addTo('duplicate@example.com', 'Name 1');
		$email->addTo('duplicate@example.com', 'Name 2');
		$this->assertCount(1, $email->getListTo());
	}

	public function testAddingDuplicateAcrossDifferentRecipientTypes(): void
	{
		$email = new Email();
		$email->addTo('same@example.com');
		$email->addCc('same@example.com');
		$allAddresses = $email->getAllRecipientAddresses();
		$this->assertCount(1, $allAddresses);
	}
}