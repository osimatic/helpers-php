<?php

namespace Osimatic\Messaging;

/**
 * Represents the available SMTP encryption methods for sending emails.
 * This enum defines the encryption protocols that can be used to secure SMTP connections when sending email messages.
 */
enum EmailSmtpEncryption: string
{
	/**
	 * STARTTLS encryption, which upgrades a plain text connection to encrypted using TLS.
	 */
	case STARTTLS = 'tls';

	/**
	 * SMTPS encryption, which uses SSL/TLS from the start of the connection.
	 */
	case SMTPS = 'ssl';

	/**
	 * No encryption, plain text connection.
	 */
	case NONE = '';

	/**
	 * Convert to PHPMailer constant value.
	 * @return string The PHPMailer encryption constant corresponding to this enum case
	 */
	public function toPhpMailer(): string
	{
		return match($this) {
			self::STARTTLS => \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
			self::SMTPS => \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS,
			self::NONE => '',
		};
	}

	/**
	 * Create from PHPMailer constant.
	 * @param string|null $encryption The PHPMailer encryption constant or string value
	 * @return EmailSmtpEncryption The corresponding EmailSmtpEncryption case
	 */
	public static function fromPhpMailer(?string $encryption): self
	{
		return match($encryption) {
			\PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS, 'tls' => self::STARTTLS,
			\PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS, 'ssl' => self::SMTPS,
			default => self::NONE,
		};
	}
}