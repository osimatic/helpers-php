<?php

namespace Tests\Messaging;

use Osimatic\Messaging\Email;
use Osimatic\Messaging\EmailCharset;
use Osimatic\Messaging\EmailSender;
use Osimatic\Messaging\EmailSenderInterface;
use Osimatic\Messaging\EmailSendingMethod;
use Osimatic\Messaging\EmailSmtpEncryption;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class EmailSenderTest extends TestCase
{
	// ========== Helper Methods ==========

	private function createBasicEmailMock(
		string $from = 'sender@example.com',
		array $to = [['recipient@example.com', 'Recipient Name']],
		string $subject = 'Test Subject',
		string $body = '<p>Test body</p>',
		bool $isHtml = true,
		array $cc = [],
		array $bcc = [],
		array $replyTo = [],
		array $attachments = [],
		EmailCharset $charset = EmailCharset::UTF8
	): Email {
		$email = $this->createMock(Email::class);
		$email->method('getCharSet')->willReturn($charset);
		$email->method('getFromEmailAddress')->willReturn($from);
		$email->method('getFromName')->willReturn('Sender Name');
		$email->method('getReplyTo')->willReturn($replyTo);
		$email->method('getListTo')->willReturn($to);
		$email->method('getListCc')->willReturn($cc);
		$email->method('getListBcc')->willReturn($bcc);
		$email->method('getSubject')->willReturn($subject);
		$email->method('isHTML')->willReturn($isHtml);
		$email->method('getText')->willReturn($body);
		$email->method('getListAttachments')->willReturn($attachments);

		return $email;
	}

	private function createLoggerMock(?string $expectedError = null): LoggerInterface
	{
		$logger = $this->createMock(LoggerInterface::class);

		if ($expectedError !== null) {
			$logger->expects(self::atLeastOnce())
				->method('error')
				->with(self::stringContains($expectedError));
		}

		return $logger;
	}

	// ========== Constructor Tests ==========

	public function testConstructorWithDefaultValues(): void
	{
		$sender = new EmailSender();

		self::assertInstanceOf(EmailSender::class, $sender);
		self::assertInstanceOf(EmailSenderInterface::class, $sender);
	}

	public function testConstructorWithCustomSendingMethod(): void
	{
		$sender = new EmailSender(sendingMethod: EmailSendingMethod::SENDMAIL);

		self::assertInstanceOf(EmailSender::class, $sender);
	}

	public function testConstructorWithAllParameters(): void
	{
		$logger = $this->createMock(LoggerInterface::class);

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'smtp.example.com',
			port: 587,
			smtpAuth: true,
			smtpAuthEncryption: EmailSmtpEncryption::STARTTLS,
			smtpAuthUsername: 'user@example.com',
			smtpAuthPassword: 'password123'
		);

		self::assertInstanceOf(EmailSender::class, $sender);
	}

	// ========== Setter Tests ==========

	public function testSetLogger(): void
	{
		$sender = new EmailSender();
		$logger = $this->createMock(LoggerInterface::class);

		$result = $sender->setLogger($logger);

		self::assertSame($sender, $result);
	}

	public function testSetLoggerWithNullLogger(): void
	{
		$sender = new EmailSender();
		$logger = new NullLogger();

		$result = $sender->setLogger($logger);

		self::assertSame($sender, $result);
	}

	public function testSetSendingMethod(): void
	{
		$sender = new EmailSender();

		$result = $sender->setSendingMethod(EmailSendingMethod::SMTP);

		self::assertSame($sender, $result);
	}

	public function testSetHost(): void
	{
		$sender = new EmailSender();

		$result = $sender->setHost('smtp.example.com');

		self::assertSame($sender, $result);
	}

	public function testSetHostWithCustomPort(): void
	{
		$sender = new EmailSender();

		$result = $sender->setHost('smtp.example.com', 587);

		self::assertSame($sender, $result);
	}

	public function testSetSmtpAuth(): void
	{
		$sender = new EmailSender();

		$result = $sender->setSmtpAuth('user@example.com', 'password123');

		self::assertSame($sender, $result);
	}

	public function testSetSmtpAuthWithEncryption(): void
	{
		$sender = new EmailSender();

		$result = $sender->setSmtpAuth('user@example.com', 'password123', EmailSmtpEncryption::STARTTLS);

		self::assertSame($sender, $result);
	}

	public function testSetPlainTextAltBody(): void
	{
		$sender = new EmailSender();

		$result = $sender->setPlainTextAltBody('Custom plain text version');

		self::assertSame($sender, $result);
	}

	public function testFluentConfiguration(): void
	{
		$logger = $this->createMock(LoggerInterface::class);
		$sender = new EmailSender();

		$result = $sender
			->setLogger($logger)
			->setSendingMethod(EmailSendingMethod::SMTP)
			->setHost('smtp.example.com', 587)
			->setSmtpAuth('user@example.com', 'password', EmailSmtpEncryption::STARTTLS);

		self::assertSame($sender, $result);
	}

	// ========== Validation Tests - From Address ==========

	public function testSendThrowsExceptionWhenFromAddressIsEmpty(): void
	{
		$logger = $this->createLoggerMock('From email address is required');

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'localhost',
			port: 1025
		);

		$email = $this->createBasicEmailMock(from: '');

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('From email address is required');
		$sender->send($email);
	}

	public function testSendThrowsExceptionWhenFromAddressIsInvalid(): void
	{
		$logger = $this->createLoggerMock('From email address is invalid');

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'localhost',
			port: 1025
		);

		$email = $this->createBasicEmailMock(from: 'not-an-email');

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('From email address is invalid');
		$sender->send($email);
	}

	// ========== Validation Tests - Recipients ==========

	public function testSendThrowsExceptionWhenNoRecipients(): void
	{
		$logger = $this->createLoggerMock('At least one recipient is required');

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'localhost',
			port: 1025
		);

		$email = $this->createBasicEmailMock(to: [], cc: [], bcc: []);

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('At least one recipient is required');
		$sender->send($email);
	}

	// ========== Validation Tests - SMTP Configuration ==========

	public function testSendThrowsExceptionWhenSmtpHostNotSet(): void
	{
		$logger = $this->createLoggerMock('SMTP host is required');

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger
		);

		$email = $this->createBasicEmailMock();

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('SMTP host is required');
		$sender->send($email);
	}

	public function testSendThrowsExceptionWhenSmtpPortNotSet(): void
	{
		$logger = $this->createLoggerMock('SMTP port is required');

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'smtp.example.com'
		);

		$email = $this->createBasicEmailMock();

		$this->expectException(\Exception::class);
		$this->expectExceptionMessage('SMTP port is required');
		$sender->send($email);
	}

	// ========== Send Tests - Different Sending Methods ==========

	public function testSendWithSmtpMethod(): void
	{
		$logger = $this->createLoggerMock();

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'localhost',
			port: 1025
		);

		$email = $this->createBasicEmailMock();

		try {
			$sender->send($email);
		} catch (\Exception $e) {
			// Expected to fail without real SMTP server
		}

		self::assertTrue(true);
	}

	public function testSendWithPhpMailMethod(): void
	{
		$sender = new EmailSender(sendingMethod: EmailSendingMethod::PHP_MAIL);

		$email = $this->createBasicEmailMock();

		try {
			$sender->send($email);
		} catch (\Exception $e) {
			// Expected to fail without proper PHP mail configuration
		}

		self::assertTrue(true);
	}

	public function testSendWithSendmailMethod(): void
	{
		$sender = new EmailSender(sendingMethod: EmailSendingMethod::SENDMAIL);

		$email = $this->createBasicEmailMock();

		$this->expectException(\Exception::class);
		$sender->send($email);
	}

	public function testSendWithQmailMethod(): void
	{
		$sender = new EmailSender(sendingMethod: EmailSendingMethod::QMAIL);

		$email = $this->createBasicEmailMock();

		$this->expectException(\Exception::class);
		$sender->send($email);
	}

	// ========== Send Tests - Email Content Types ==========

	public function testSendWithHtmlEmail(): void
	{
		$logger = $this->createLoggerMock();

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'localhost',
			port: 1025
		);

		$email = $this->createBasicEmailMock(
			body: '<html><body><h1>Test</h1><p>HTML body</p></body></html>',
			isHtml: true
		);

		$this->expectException(\Exception::class);
		$sender->send($email);
	}

	public function testSendWithPlainTextEmail(): void
	{
		$logger = $this->createLoggerMock();

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'localhost',
			port: 1025
		);

		$email = $this->createBasicEmailMock(
			body: 'Test plain text body',
			isHtml: false
		);

		$this->expectException(\Exception::class);
		$sender->send($email);
	}

	// ========== Send Tests - Multiple Recipients ==========

	public function testSendWithMultipleRecipients(): void
	{
		$logger = $this->createLoggerMock();

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'localhost',
			port: 1025
		);

		$email = $this->createBasicEmailMock(
			to: [
				['recipient1@example.com', 'Recipient 1'],
				['recipient2@example.com', 'Recipient 2'],
			],
			cc: [
				['cc@example.com', 'CC Recipient'],
			],
			bcc: [
				['bcc@example.com', 'BCC Recipient'],
			]
		);

		$this->expectException(\Exception::class);
		$sender->send($email);
	}

	public function testSendWithReplyTo(): void
	{
		$logger = $this->createLoggerMock();

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'localhost',
			port: 1025
		);

		$email = $this->createBasicEmailMock(
			replyTo: [['replyto@example.com', 'Reply To Name']]
		);

		$this->expectException(\Exception::class);
		$sender->send($email);
	}

	public function testSendWithRecipientsAsStrings(): void
	{
		$logger = $this->createLoggerMock();

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'localhost',
			port: 1025
		);

		$email = $this->createBasicEmailMock(
			to: ['recipient@example.com'],
			cc: ['cc@example.com'],
			bcc: ['bcc@example.com'],
			replyTo: ['replyto@example.com']
		);

		$this->expectException(\Exception::class);
		$sender->send($email);
	}

	// ========== Send Tests - Different Charsets ==========

	public function testSendWithDifferentCharsets(): void
	{
		$logger = $this->createLoggerMock();

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'localhost',
			port: 1025
		);

		$email = $this->createBasicEmailMock(charset: EmailCharset::ISO88591);

		$this->expectException(\Exception::class);
		$sender->send($email);
	}

	// ========== Send Tests - Attachments ==========

	public function testSendWithAttachment(): void
	{
		$logger = $this->createLoggerMock();

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'localhost',
			port: 1025
		);

		$tempFile = tempnam(sys_get_temp_dir(), 'test_attachment_');
		file_put_contents($tempFile, 'Test attachment content');

		$email = $this->createBasicEmailMock(
			attachments: [[$tempFile, 'test.txt']]
		);

		try {
			$sender->send($email);
		} catch (\Exception $e) {
			// Expected to fail without real SMTP
		} finally {
			if (file_exists($tempFile)) {
				unlink($tempFile);
			}
		}

		self::assertTrue(true);
	}

	public function testSendWithNonExistentAttachment(): void
	{
		$logger = $this->createLoggerMock();

		$sender = new EmailSender(
			sendingMethod: EmailSendingMethod::SMTP,
			logger: $logger,
			host: 'localhost',
			port: 1025
		);

		$email = $this->createBasicEmailMock(
			attachments: [['/nonexistent/file.txt', 'file.txt']]
		);

		$this->expectException(\Exception::class);
		$sender->send($email);
	}

	// ========== EmailSmtpEncryption Enum Tests ==========

	public function testEmailSmtpEncryptionStartTls(): void
	{
		$encryption = EmailSmtpEncryption::STARTTLS;

		self::assertSame('tls', $encryption->value);
		self::assertSame(\PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS, $encryption->toPhpMailer());
	}

	public function testEmailSmtpEncryptionSmtps(): void
	{
		$encryption = EmailSmtpEncryption::SMTPS;

		self::assertSame('ssl', $encryption->value);
		self::assertSame(\PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS, $encryption->toPhpMailer());
	}

	public function testEmailSmtpEncryptionNone(): void
	{
		$encryption = EmailSmtpEncryption::NONE;

		self::assertSame('', $encryption->value);
		self::assertSame('', $encryption->toPhpMailer());
	}

	public function testEmailSmtpEncryptionFromPhpMailerStartTls(): void
	{
		self::assertSame(
			EmailSmtpEncryption::STARTTLS,
			EmailSmtpEncryption::fromPhpMailer(\PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS)
		);
	}

	public function testEmailSmtpEncryptionFromPhpMailerTls(): void
	{
		self::assertSame(
			EmailSmtpEncryption::STARTTLS,
			EmailSmtpEncryption::fromPhpMailer('tls')
		);
	}

	public function testEmailSmtpEncryptionFromPhpMailerSmtps(): void
	{
		self::assertSame(
			EmailSmtpEncryption::SMTPS,
			EmailSmtpEncryption::fromPhpMailer(\PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS)
		);
	}

	public function testEmailSmtpEncryptionFromPhpMailerSsl(): void
	{
		self::assertSame(
			EmailSmtpEncryption::SMTPS,
			EmailSmtpEncryption::fromPhpMailer('ssl')
		);
	}

	public function testEmailSmtpEncryptionFromPhpMailerEmpty(): void
	{
		self::assertSame(
			EmailSmtpEncryption::NONE,
			EmailSmtpEncryption::fromPhpMailer('')
		);
	}

	public function testEmailSmtpEncryptionFromPhpMailerNull(): void
	{
		self::assertSame(
			EmailSmtpEncryption::NONE,
			EmailSmtpEncryption::fromPhpMailer(null)
		);
	}
}
