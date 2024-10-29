<?php

namespace Osimatic\Messaging;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class EmailSender implements EmailSenderInterface
{
	public function __construct(
		private EmailSendingMethod $sendingMethod = EmailSendingMethod::SMTP,
		private LoggerInterface $logger = new NullLogger(),
		private ?string $host = null,
		private ?int $port = null,
		private bool $smtpAuth = false,
		private ?string $smtpAuthEncryption = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS,
		private ?string $smtpAuthUsername = null,
		private ?string $smtpAuthPassword = null,
	) {}

	public function setLogger(LoggerInterface $logger): void
	{
		$this->logger = $logger;
	}

	public function setSendingMethod(EmailSendingMethod $sendingMethod): void
	{
		$this->sendingMethod = $sendingMethod;
	}

	public function setHost(string $host, int $port=25): void
	{
		$this->host = $host;
		$this->port = $port;
	}

	public function setSmtpAuth(string $username, string $password, string $encryption=\PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS): void
	{
		$this->smtpAuth = true;
		$this->smtpAuthUsername = $username;
		$this->smtpAuthPassword = $password;
		$this->smtpAuthEncryption = $encryption;
	}

	/**
	 * @param Email $email
	 * @return void
	 * @throws \Exception
	 */
	public function send(Email $email): void
	{
		//$mail = new \PHPMailer;
		$mail = new \PHPMailer\PHPMailer\PHPMailer();
		$mail->Debugoutput = $this->logger;

		if (EmailSendingMethod::SMTP === $this->sendingMethod) {
			$mail->isSMTP();
			$mail->SMTPAuth 	= $this->smtpAuth;
			$mail->SMTPSecure 	= $this->smtpAuthEncryption;
			$mail->Username 	= $this->smtpAuthUsername;
			$mail->Password 	= $this->smtpAuthPassword;
		}
		if (EmailSendingMethod::SENDMAIL === $this->sendingMethod) {
			$mail->isSendmail();
		}
		if (EmailSendingMethod::QMAIL === $this->sendingMethod) {
			$mail->isQmail();
		}

		//$mail->SMTPDebug = 4;
		//$mail->Timeout = 10;
		$mail->Host 		= $this->host;
		$mail->Port 		= $this->port;

		// 17/02/2021 : désactivé ces lignes de code, à voir si cela change qqchose pour les mails en spam
		//$mail->SMTPOptions = [
		//	'ssl' => [
		//		'verify_peer' => false,
		//		'verify_peer_name' => false,
		//		'allow_self_signed' => true
		//	]
		//];

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
				$mail->AltBody = 'This is a plain-text message body';
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
			$this->logger->error($e->getMessage());
			throw new \Exception($e->getMessage(), $e->getCode());
		}
	}
}