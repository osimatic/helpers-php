<?php

namespace Osimatic\Messaging;

enum EmailSmtpEncryption: string
{
	case STARTTLS = 'tls';
	case SMTPS = 'ssl';
	case NONE = '';

	/**
	 * Convert to PHPMailer constant value
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
	 * Create from PHPMailer constant
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