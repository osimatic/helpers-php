<?php

namespace Osimatic\Messaging;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class EmailSender implements EmailSenderInterface
{
	private ?string $plainTextAltBody = null;

	public function __construct(
		private EmailSendingMethod $sendingMethod = EmailSendingMethod::SMTP,
		private LoggerInterface $logger = new NullLogger(),
		private ?string $host = null,
		private ?int $port = null,
		private bool $smtpAuth = false,
		private EmailSmtpEncryption $smtpAuthEncryption = EmailSmtpEncryption::STARTTLS,
		private ?string $smtpAuthUsername = null,
		private ?string $smtpAuthPassword = null,
	) {}

	public function setLogger(LoggerInterface $logger): self
	{
		$this->logger = $logger;

		return $this;
	}

	public function setSendingMethod(EmailSendingMethod $sendingMethod): self
	{
		$this->sendingMethod = $sendingMethod;

		return $this;
	}

	public function setHost(string $host, int $port=25): self
	{
		$this->host = $host;
		$this->port = $port;

		return $this;
	}

	public function setSmtpAuth(string $username, string $password, EmailSmtpEncryption $encryption = EmailSmtpEncryption::STARTTLS): self
	{
		$this->smtpAuth = true;
		$this->smtpAuthUsername = $username;
		$this->smtpAuthPassword = $password;
		$this->smtpAuthEncryption = $encryption;

		return $this;
	}

	public function setPlainTextAltBody(?string $altBody): self
	{
		$this->plainTextAltBody = $altBody;

		return $this;
	}

	/**
	 * @param Email $email
	 * @return void
	 * @throws \Exception
	 */
	public function send(Email $email): void
	{
		// Validation - Verify from address is set and valid
		$fromAddress = $email->getFromEmailAddress();
		if (empty($fromAddress)) {
			$this->logger->error('From email address is required');
			throw new \Exception('From email address is required');
		}
		if (!EmailAddress::check($fromAddress)) {
			$this->logger->error('From email address is invalid: ' . $fromAddress);
			throw new \Exception('From email address is invalid: ' . $fromAddress);
		}

		// Validation - Verify at least one recipient
		if (!empty($email->getListTo()) || !empty($email->getListCc()) || !empty($email->getListBcc())) {
			$this->logger->error('At least one recipient is required (To, Cc, or Bcc)');
			throw new \Exception('At least one recipient is required (To, Cc, or Bcc)');
		}

		// Validation - Verify SMTP configuration when using SMTP
		if (EmailSendingMethod::SMTP === $this->sendingMethod) {
			if (empty($this->host) || empty($this->port)) {
				$this->logger->error('SMTP host and port are required when using SMTP sending method');
				throw new \Exception('SMTP host and port are required when using SMTP sending method');
			}
		}

		$mail = new \PHPMailer\PHPMailer\PHPMailer();
		$mail->Debugoutput = $this->logger;

		if (EmailSendingMethod::PHP_MAIL === $this->sendingMethod) {
			$mail->isMail();
		}
		elseif (EmailSendingMethod::SMTP === $this->sendingMethod) {
			$mail->isSMTP();
			$mail->SMTPAuth = $this->smtpAuth;
			$mail->SMTPSecure = $this->smtpAuthEncryption->toPhpMailer();
			$mail->Username = $this->smtpAuthUsername;
			$mail->Password = $this->smtpAuthPassword;
			$mail->Host = $this->host;
			$mail->Port = $this->port;
		}
		elseif (EmailSendingMethod::SENDMAIL === $this->sendingMethod) {
			$mail->isSendmail();
		}
		elseif (EmailSendingMethod::QMAIL === $this->sendingMethod) {
			$mail->isQmail();
		}

		try {
			$mail->CharSet = $email->getCharSet()->value;
			$mail->setFrom($email->getFromEmailAddress(), $email->getFromName());

			foreach ($email->getReplyTo() as $replyTo) {
				$replyTo = is_array($replyTo) ? $replyTo : [$replyTo];
				$mail->addReplyTo(mb_strtolower($replyTo[0]), $replyTo[1] ?? '');
			}
			foreach ($email->getListTo() as $to) {
				$to = is_array($to) ? $to : [$to];
				$mail->addAddress(mb_strtolower($to[0]), $to[1] ?? '');
			}
			foreach ($email->getListCc() as $cc) {
				$cc = is_array($cc) ? $cc : [$cc];
				$mail->addCC(mb_strtolower($cc[0]), $cc[1] ?? '');
			}
			foreach ($email->getListBcc() as $bcc) {
				$bcc = is_array($bcc) ? $bcc : [$bcc];
				$mail->addBCC(mb_strtolower($bcc[0]), $bcc[1] ?? '');
			}

			$mail->Subject = $email->getSubject();
			if ($email->isHTML()) {
				$mail->msgHTML($email->getText());
			}
			else {
				$mail->Body = $email->getText();
				$mail->AltBody = $this->plainTextAltBody ?? 'This is a plain-text message body';
			}

			foreach ($email->getListAttachments() as $attachment) {
				$resAttachment = $mail->addAttachment($attachment[0], $attachment[1]);
				$this->logger->debug('Fichier '.$attachment[0].' : '.($resAttachment?'ok':'not ok').'.');
			}

			if (!$mail->send()) {
				$errorMessage = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : 'Error during sending email.';
				$this->logger->error($errorMessage);
				throw new \Exception($errorMessage);
			}
		}
		catch (\Exception $e) {
			// Only log if not already logged
			if (!str_contains($e->getMessage(), 'Error during sending email') && !str_contains($e->getMessage(), 'email address')) {
				$this->logger->error($e->getMessage());
			}
			throw $e;
		}
	}
}