<?php

namespace Osimatic\Messaging;

use Osimatic\FileSystem\File;

/**
 * Class Email
 * @package Osimatic\Messaging
 */
class Email
{
	public const int ATTACHMENT_FILESIZE_MAX = 2000000; // 2 Mo

	/**
	 * The identifier property represents any kind of identifier.
	 * @var string|null
	 */
	private ?string $identifier = null;

	/**
	 * The From email address for the message.
	 * @var string|null
	 */
	private ?string $fromEmailAddress = null;

	/**
	 * The From name of the message.
	 * @var string|null
	 */
	private ?string $fromName = null;

	/**
	 * The envelope sender of the message.
	 * This will usually be turned into a Return-Path header by the receiver, and is the address that bounces will be sent to.
	 * @type string|null
	 */
	private ?string $sender = null;

	/**
	 * The array of reply-to names and addresses.
	 * A reply-to element is an array containing :
	 * [0] => email address
	 * [1] => name (optional)
	 * @var array
	 */
	private array $replyTo = [];

	/**
	 * The array of 'to' names and addresses.
	 * A recipient element is an array containing :
	 * [0] => email address
	 * [1] => name (optional)
	 * @var array
	 */
	private array $listTo = [];

	/**
	 * The array of 'cc' names and addresses.
	 * A recipient element is an array containing :
	 * [0] => email address
	 * [1] => name (optional)
	 * @var array
	 */
	private array $listCc = [];

	/**
	 * The array of 'bcc' names and addresses.
	 * A recipient element is an array containing :
	 * [0] => email address
	 * [1] => name (optional)
	 * @var array
	 */
	private array $listBcc = [];

	/**
	 * The email address that a reading confirmation should be sent to, also known as read receipt.
	 * @var string|null
	 */
	private ?string $confirmReadingTo = null;

	/**
	 * The array of attachments.
	 * An attachment element is an array containing :
	 * [0] => String si la pièce jointe est une string à la place d'un fichier ou
	 * Chemin complet vers le fichier si la pièce jointe est un fichier ou
	 * Chemin complet vers l'image si la pièce jointe est une embedded image
	 * [1] => Filename of attachment
	 * [2] => Filename of attachment displayed in the email
	 * [3] => Filename encoding (default "base64")
	 * [4] => MIME type of attachment (default "application/octet-stream")
	 * [5] => boolean indicating if attachment is a string (true) or a file (false)
	 * [6] => "attachment" if it's an attachment, "inline" if it's an embedded image
	 * [7] => Content ID of the attachment. Use this to reference the content when using an embedded image in HTML.
	 * @var array
	 */
	private array $listAttachments = [];

	/**
	 * The Subject of the message.
	 * @var string
	 */
	private string $subject = '';

	/**
	 * An HTML or plain text message body.
	 * @var string
	 */
	private string $text = '';

	/**
	 * The plain-text message body.
	 * This body can be read by mail clients that do not have HTML email capability such as mutt & Eudora.
	 * Clients that can read HTML will view the normal Body.
	 * @var string
	 */
	private string $altText = '';

	/**
	 * Email priority.
	 * Options: null (default), 1 = High, 3 = Normal, 5 = low.
	 * @var int|null
	 */
	private ?int $priority = null;

	/**
	 * The character set of the message.
	 * @var EmailCharset
	 */
	private EmailCharset $charSet = EmailCharset::UTF8;

	/**
	 * The MIME Content-type of the message.
	 * @var EmailContentType
	 */
	private EmailContentType $contentType = EmailContentType::TEXT_HTML;

	/**
	 * The message encoding.
	 * Options: "8bit", "7bit", "binary", "base64", and "quoted-printable".
	 * @var EmailEncoding
	 */
	private EmailEncoding $encoding = EmailEncoding::_8BIT;

	/**
	 * The complete compiled MIME message body.
	 * @var string
	 */
	private string $MIMEBody = '';

	/**
	 * The complete compiled MIME message headers.
	 * @var string
	 */
	private string $MIMEHeader = '';

	/**
	 * The hostname to use in the Message-ID header and as default HELO string.
	 * @var string
	 */
	private string $hostname = '';

	/**
	 * An ID to be used in the Message-ID header.
	 * If empty, a unique id will be generated.
	 * You can set your own, but it must be in the format "<id@domain>", as defined in RFC5322 section 3.6.4 or it will be ignored.
	 * @see https://tools.ietf.org/html/rfc5322#section-3.6.4
	 * @var string
	 */
	private string $messageID = '';

	/**
	 * The message date to be used in the Date header and the sending date of the message
	 * If empty, the current date will be added.
	 * @var \DateTime|null
	 */
	private ?\DateTime $sendingDateTime = null;

	/**
	 * private usage
	 * @var array
	 */
	private array $allAddresses = [];


	/**
	 * Email constructor.
	 */
	public function __construct()
	{
		$this->sendingDateTime = \Osimatic\Calendar\DateTime::getCurrentDateTime();
	}



	/**
	 * @return string|null
	 */
	public function getIdentifier(): ?string
	{
		return $this->identifier;
	}

	/**
	 * @param string|null $identifier
	 */
	public function setIdentifier(?string $identifier): void
	{
		$this->identifier = $identifier;
	}



	// ========== Expéditeur ==========

	/**
	 * Return the From email address for the message.
	 * @return string|null
	 */
	public function getFromEmailAddress(): ?string
	{
		return $this->fromEmailAddress;
	}

	/**
	 * Sets the From email address for the message.
	 * @param string|null $emailAddress
	 * @return self
	 */
	public function setFromEmailAddress(?string $emailAddress): self
	{
		if (null === $emailAddress) {
			$this->fromEmailAddress = null;
			return $this;
		}

		$emailAddress = trim($emailAddress);
		if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
			//trace('Invalid address : '.$emailAddress);
			return $this;
		}

		$this->fromEmailAddress = $emailAddress;

		return $this;
	}

	/**
	 * Return the From name of the message.
	 * @return string|null
	 */
	public function getFromName(): ?string
	{
		return $this->fromName;
	}

	/**
	 * Sets the From name of the message.
	 * @param string|null $name
	 * @return self
	 */
	public function setFromName(?string $name): self
	{
		if (null === $name) {
			$this->fromName = null;
			return $this;
		}

		$name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
		$this->fromName = $name;

		return $this;
	}

	/**
	 * Set the fromEmailAddress and fromName properties.
	 * @param string $emailAddress
	 * @param string|null $name
	 * @param bool $auto Whether to also set the Sender address, defaults to true
	 * @return self
	 */
	public function setFrom(string $emailAddress, ?string $name = '', bool $auto = true): self
	{
		$this->setFromEmailAddress($emailAddress);
		$this->setFromName($name);

		if ($auto && empty($this->sender)) {
			$this->sender = $emailAddress;
		}

		return $this;
	}

	/**
	 * @param string|null $emailAddress
	 * @return self
	 */
	public function setSender(?string $emailAddress): self
	{
		$this->sender = $emailAddress;

		return $this;
	}

	/**
	 * @return string|null
	 */
	public function getSender(): ?string
	{
		return $this->sender ?? null;
	}


	/**
	 * Return The email address that a reading confirmation should be sent to, also known as read receipt.
	 * @return string|null
	 */
	public function getConfirmReadingTo(): ?string
	{
		return $this->confirmReadingTo;
	}

	/**
	 * Sets the email address that a reading confirmation should be sent to, also known as read receipt.
	 * @param string|null $emailAddress
	 * @return self
	 */
	public function setConfirmReadingTo(?string $emailAddress): self
	{
		if (null === $emailAddress) {
			$this->confirmReadingTo = null;
			return $this;
		}

		$emailAddress = trim($emailAddress);
		if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
			//trace('Invalid address : '.$emailAddress);
			return $this;
		}

		$this->confirmReadingTo = $emailAddress;

		return $this;
	}

	/**
	 * Clear sender.
	 */
	public function clearSender(): void
	{
		$this->fromEmailAddress = null;
		$this->fromName = null;
		$this->sender = null;
	}



	// ========== Répondre à ==========

	/**
	 * Return all "Reply-To" addresses.
	 * @return array
	 */
	public function getReplyTo(): array
	{
		return array_values($this->replyTo);
	}

	/**
	 * Return the first "Reply-To" email address.
	 * @return string
	 */
	public function getReplyToEmail(): string
	{
		return $this->getReplyTo()[0][0] ?? '';
	}

	/**
	 * Add a "Reply-To" address.
	 * @param string|null $emailAddress
	 * @param string|null $name
	 * @return self
	 */
	public function addReplyTo(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress(EmailAddressKind::replyTo, $emailAddress, $name);

		return $this;
	}

	/**
	 * Sets the "Reply-To" address.
	 * @param string|null $emailAddress
	 * @param string|null $name
	 * @return self
	 */
	public function setReplyTo(?string $emailAddress, ?string $name = ''): self
	{
		$this->clearReplyTo();
		$this->addEmailAddress(EmailAddressKind::replyTo, $emailAddress, $name);

		return $this;
	}

	/**
	 * Clear all ReplyTo recipients.
	 */
	public function clearReplyTo(): void
	{
		$this->replyTo = [];
	}

	/**
	 * @param string $separator
	 * @return string
	 */
	public function formatReplyTo(string $separator=' ; '): string
	{
		return implode($separator, self::formatEmailList($this->getReplyTo()));
	}


	// ========== Destinataires ==========

	/**
	 * Return the array of 'to' names and addresses.
	 * @return array
	 */
	public function getListTo(): array
	{
		return $this->listTo;
	}

	/**
	 * Return all 'to' email addresses.
	 * @return array
	 */
	public function getListToEmails(): array
	{
		return array_filter(array_map(static fn($email) => $email[0] ?? null, $this->listTo));
	}

	/**
	 * The array of 'cc' names and addresses.
	 * @return array
	 */
	public function getListCc(): array
	{
		return $this->listCc;
	}

	/**
	 * Return all 'cc' email addresses.
	 * @return array
	 */
	public function getListCcEmails(): array
	{
		return array_filter(array_map(static fn($email) => $email[0] ?? null, $this->listCc));
	}

	/**
	 * The array of 'bcc' names and addresses.
	 * @return array
	 */
	public function getListBcc(): array
	{
		return $this->listBcc;
	}

	/**
	 * Return all 'bcc' email addresses.
	 * @return array
	 */
	public function getListBccEmails(): array
	{
		return array_filter(array_map(static fn($email) => $email[0] ?? null, $this->listBcc));
	}

	/**
	 * Return all recipient email addresses ('to', 'cc' and 'bcc').
	 * @return array
	 */
	public function getAllRecipientAddresses(): array
	{
		return $this->allAddresses;
	}


	/**
	 * Add a "To" address.
	 * @param string|null $emailAddress The email address to send to
	 * @param string|null $name
	 * @return self
	 */
	public function addTo(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress(EmailAddressKind::to, $emailAddress, $name);

		return $this;
	}

	/**
	 * Add a "To" address.
	 * @param string|null $emailAddress The email address to send to
	 * @param string|null $name
	 * @return self
	 */
	public function addRecipient(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress(EmailAddressKind::to, $emailAddress, $name);

		return $this;
	}

	/**
	 * Add a "CC" address.
	 * @param string|null $emailAddress The email address to send to
	 * @param string|null $name
	 * @return self
	 */
	public function addCc(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress(EmailAddressKind::cc, $emailAddress, $name);

		return $this;
	}

	/**
	 * Add a "BCC" address.
	 * @param string|null $emailAddress The email address to send to
	 * @param string|null $name
	 * @return self
	 */
	public function addBcc(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress(EmailAddressKind::bcc, $emailAddress, $name);

		return $this;
	}

	/**
	 * Add "To" addresses.
	 * @param array $recipientList
	 * @return self
	 */
	public function addListTo(array $recipientList): self
	{
		$this->addEmailAddressList(EmailAddressKind::to, $recipientList);

		return $this;
	}

	/**
	 * Add "CC" addresses.
	 * @param array $recipientList
	 * @return self
	 */
	public function addListCc(array $recipientList): self
	{
		$this->addEmailAddressList(EmailAddressKind::cc, $recipientList);

		return $this;
	}

	/**
	 * Add "BCC" addresses.
	 * @param array $recipientList
	 * @return self
	 */
	public function addListBcc(array $recipientList): self
	{
		$this->addEmailAddressList(EmailAddressKind::bcc, $recipientList);

		return $this;
	}


	/**
	 * Sets the "To" address.
	 * @param string|null $emailAddress
	 * @param string|null $name
	 * @return self
	 */
	public function setRecipient(?string $emailAddress, ?string $name = ''): self
	{
		$this->clearListTo();
		$this->addEmailAddress(EmailAddressKind::to, $emailAddress, $name);

		return $this;
	}

	/**
	 * Sets the "To" address.
	 * @param string|null $emailAddress
	 * @param string|null $name
	 * @return self
	 */
	public function setTo(?string $emailAddress, ?string $name = ''): self
	{
		$this->clearListTo();
		$this->addEmailAddress(EmailAddressKind::to, $emailAddress, $name);

		return $this;
	}

	/**
	 * Sets the "CC" address.
	 * @param string|null $emailAddress
	 * @param string|null $name
	 * @return self
	 */
	public function setCc(?string $emailAddress, ?string $name = ''): self
	{
		$this->clearListCc();
		$this->addEmailAddress(EmailAddressKind::cc, $emailAddress, $name);

		return $this;
	}

	/**
	 * Sets the "BCC" address.
	 * @param string|null $emailAddress
	 * @param string|null $name
	 * @return self
	 */
	public function setBcc(?string $emailAddress, ?string $name = ''): self
	{
		$this->clearListBcc();
		$this->addEmailAddress(EmailAddressKind::bcc, $emailAddress, $name);

		return $this;
	}

	/**
	 * Sets "To" addresses.
	 * @param array $recipientList
	 * @return self
	 */
	public function setListRecipients(array $recipientList): self
	{
		$this->clearListTo();
		$this->addEmailAddressList(EmailAddressKind::to, $recipientList);

		return $this;
	}

	/**
	 * Sets "To" addresses.
	 * @param array $recipientList
	 * @return self
	 */
	public function setListTo(array $recipientList): self
	{
		$this->clearListTo();
		$this->addEmailAddressList(EmailAddressKind::to, $recipientList);

		return $this;
	}

	/**
	 * Sets "CC" addresses.
	 * @param array $recipientList
	 * @return self
	 */
	public function setListCc(array $recipientList): self
	{
		$this->clearListCc();
		$this->addEmailAddressList(EmailAddressKind::cc, $recipientList);

		return $this;
	}

	/**
	 * Sets "BCC" addresses.
	 * @param array $recipientList
	 * @return self
	 */
	public function setListBcc(array $recipientList): self
	{
		$this->clearListBcc();
		$this->addEmailAddressList(EmailAddressKind::bcc, $recipientList);

		return $this;
	}



	public function formatListTo(string $separator=' ; '): string
	{
		return implode($separator, self::formatEmailList($this->getListTo()));
	}

	public function formatListCc(string $separator=' ; '): string
	{
		return implode($separator, self::formatEmailList($this->getListCc()));
	}

	public function formatListBcc(string $separator=' ; '): string
	{
		return implode($separator, self::formatEmailList($this->getListBcc()));
	}

	/**
	 * Clear all To recipients.
	 */
	public function clearListTo(): void
	{
		foreach ($this->listTo as $to) {
			if (!empty($to[0]) && isset($this->allAddresses[mb_strtolower($to[0])])) {
				unset($this->allAddresses[mb_strtolower($to[0])]);
			}
		}
		$this->listTo = [];
	}

	/**
	 * Clear all CC recipients.
	 */
	public function clearListCc(): void
	{
		foreach ($this->listCc as $cc) {
			if (!empty($cc[0]) && isset($this->allAddresses[mb_strtolower($cc[0])])) {
				unset($this->allAddresses[mb_strtolower($cc[0])]);
			}
		}
		$this->listCc = [];
	}

	/**
	 * Clear all BCC recipients.
	 */
	public function clearListBcc(): void
	{
		foreach ($this->listBcc as $bcc) {
			if (!empty($bcc[0]) && isset($this->allAddresses[mb_strtolower($bcc[0])])) {
				unset($this->allAddresses[mb_strtolower($bcc[0])]);
			}
		}
		$this->listBcc = [];
	}

	/**
	 * Clear all recipient types.
	 */
	public function clearRecipients(): void
	{
		$this->listTo = [];
		$this->listCc = [];
		$this->listBcc = [];
		$this->allAddresses = [];
	}

	/**
	 * Clear all recipient types.
	 */
	public function clearAllRecipients(): void
	{
		$this->clearRecipients();
	}

	// ========== Attachement ==========

	/**
	 * Return the array of attachments.
	 * @return array
	 */
	public function getAttachments(): array
	{
		return $this->listAttachments;
	}

	/**
	 * @return array
	 */
	public function getListAttachments(): array
	{
		return $this->listAttachments;
	}

	/**
	 * Check if an attachment (non-inline) is present.
	 * @return bool
	 */
	public function attachmentExists(): bool
	{
		foreach ($this->listAttachments as $attachment) {
			if ($attachment[6] === 'attachment') {
				return true;
			}
		}
		return false;
	}

	/**
	 * Check if an inline attachment is present.
	 * @return bool
	 */
	public function inlineImageExists(): bool
	{
		foreach ($this->listAttachments as $attachment) {
			if ($attachment[6] === 'inline') {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param array $listAttachments
	 * @return self
	 */
	public function addListAttachment(array $listAttachments): self
	{
		foreach ($listAttachments as $attachment) {
			$attachmentPath = is_array($attachment) ? $attachment[0] ?? null : $attachment;
			if (!empty($attachmentPath)) {
				$this->addAttachment($attachmentPath, is_array($attachment) ? $attachment[1] ?? null : null);
			}
		}

		return $this;
	}

	/**
	 * Add an attachment from a path on the filesystem.
	 * Returns false if the file could not be found or read.
	 * @param string $path Path to the attachment.
	 * @param string|null $name Overrides the attachment name.
	 * @param EmailEncoding $encoding File encoding.
	 * @param string|null $mimeType File extension (MIME) type.
	 * @return self
	 */
	public function addAttachment(string $path, ?string $name = null, EmailEncoding $encoding = EmailEncoding::BASE64, ?string $mimeType = null): self
	{
		if (!is_file($path)) {
			return $this;
		}

		if (filesize($path) > self::ATTACHMENT_FILESIZE_MAX) {
			//error('Ce fichier est trop gros pour être mis en pièce jointe du mail: '.$path);
			return $this;
		}

		//If a MIME type is not specified, try to work it out from the file name
		if (empty($mimeType)) {
			$mimeType = File::getMimeTypeForFile($path);
		}

		$filename = basename($path);
		if (empty($name)) {
			$name = $filename;
		}

		$this->listAttachments[] = [
			0 => $path,
			1 => $filename,
			2 => $name,
			3 => $encoding->value,
			4 => $mimeType,
			5 => false, // isStringAttachment
			6 => 'attachment', // disposition
			7 => 0
		];

		return $this;
	}

	/**
	 * Add a string or binary attachment (non-filesystem).
	 * This method can be used to attach ascii or binary data,
	 * such as a BLOB record from a database.
	 * @param string $string String attachment data.
	 * @param string $filename Name of the attachment.
	 * @param EmailEncoding $encoding File encoding.
	 * @param string|null $mimeType File extension (MIME) type.
	 * @return self
	 */
	public function addStringAttachment(string $string, string $filename, EmailEncoding $encoding = EmailEncoding::BASE64, ?string $mimeType = null): self
	{
		//If a MIME type is not specified, try to work it out from the file name
		if (empty($mimeType)) {
			$mimeType = File::getMimeTypeForFile($filename);
		}
		// Append to $attachment array
		$this->listAttachments[] = [
			0 => $string,
			1 => $filename,
			2 => basename($filename),
			3 => $encoding->value,
			4 => $mimeType,
			5 => true, // isStringAttachment
			6 => 'attachment', // disposition
			7 => 0
		];

		return $this;
	}

	/**
	 * Add an embedded (inline) attachment from a file.
	 * This can include images, sounds, and just about any other document type.
	 * These differ from 'regular' attachments in that they are intended to be
	 * displayed inline with the message, not just attached for download.
	 * This is used in HTML messages that embed the images
	 * the HTML refers to using the $cid value.
	 * @param string $path Path to the attachment.
	 * @param string $cid Content ID of the attachment; Use this to reference the content when using an embedded image in HTML.
	 * @param string|null $name Overrides the attachment name.
	 * @param EmailEncoding $encoding File encoding.
	 * @param string|null $mimeType File MIME type.
	 * @return self
	 */
	public function addEmbeddedImage(string $path, string $cid, ?string $name = null, EmailEncoding $encoding = EmailEncoding::BASE64, ?string $mimeType = null): self
	{
		if (!is_file($path)) {
			return $this;
		}

		if (filesize($path) > self::ATTACHMENT_FILESIZE_MAX) {
			//error('Ce fichier est trop gros pour être mis en pièce jointe du mail: '.$path);
			return $this;
		}

		//If a MIME type is not specified, try to work it out from the file name
		if (empty($mimeType)) {
			$mimeType = File::getMimeTypeForFile($path);
		}

		$filename = basename($path);
		if (empty($name)) {
			$name = $filename;
		}

		// Append to $attachment array
		$this->listAttachments[] = [
			0 => $path,
			1 => $filename,
			2 => $name,
			3 => $encoding->value,
			4 => $mimeType,
			5 => false, // isStringAttachment
			6 => 'inline', // disposition
			7 => $cid
		];

		return $this;
	}

	/**
	 * Add an embedded stringified attachment.
	 * This can include images, sounds, and just about any other document type.
	 * Be sure to set the $type to an image type for images:
	 * JPEG images use 'image/jpeg', GIF uses 'image/gif', PNG uses 'image/png'.
	 * @param string $string The attachment binary data.
	 * @param string $cid Content ID of the attachment; Use this to reference the content when using an embedded image in HTML.
	 * @param string|null $name
	 * @param EmailEncoding $encoding File encoding.
	 * @param string|null $mimeType MIME type.
	 * @return self
	 */
	public function addStringEmbeddedImage(string $string, string $cid, ?string $name = null, EmailEncoding $encoding = EmailEncoding::BASE64, ?string $mimeType = null): self
	{
		//If a MIME type is not specified, try to work it out from the name
		if (empty($mimeType)) {
			$mimeType = File::getMimeTypeForFile($name);
		}

		// Append to $attachment array
		$this->listAttachments[] = [
			0 => $string,
			1 => $name,
			2 => $name,
			3 => $encoding->value,
			4 => $mimeType,
			5 => true, // isStringAttachment
			6 => 'inline', // disposition
			7 => $cid
		];

		return $this;
	}

	/**
	 * Clear all filesystem, string, and binary attachments.
	 * @return void
	 */
	public function clearAttachments(): void
	{
		$this->listAttachments = [];
	}


	// ========== Subject and text ==========

	private static function formatText(?string $str): string
	{
		if ($str === null) {
			return '';
		}
		$str = str_replace("’", "'", $str);
		return $str;
	}

	/**
	 * Return the subject of the message.
	 * @return string
	 */
	public function getSubject(): string
	{
		return $this->subject ?? '';
	}

	/**
	 * Sets the subject of the message.
	 * @param string|null $subject
	 * @return self
	 */
	public function setSubject(?string $subject): self
	{
		$this->subject = self::formatText($subject);

		return $this;
	}

	/**
	 * Return true if the message type is HTML, false if plain.
	 * @return bool
	 */
	public function isHTML(): bool
	{
		return $this->contentType === EmailContentType::TEXT_HTML;
	}

	/**
	 * Sets message type to HTML or plain.
	 * @return self
	 */
	public function setHtmlFormat(): self
	{
		$this->contentType = EmailContentType::TEXT_HTML;

		return $this;
	}

	/**
	 * Sets message type to plain.
	 * @return self
	 */
	public function setTextFormat(): self
	{
		$this->contentType = EmailContentType::PLAINTEXT;

		return $this;
	}

	/**
	 * Sets MIME type of the message.
	 * @param EmailContentType|string $contentType
	 * @return self
	 */
	public function setContentType(EmailContentType|string $contentType): self
	{
		$this->contentType = is_string($contentType) ? EmailContentType::tryFrom($contentType) ?? EmailContentType::TEXT_HTML : $contentType;

		return $this;
	}

	/**
	 * Sets message type to HTML or plain.
	 * @param bool $isHtml
	 * @return self
	 */
	public function setIsHTML(bool $isHtml): self
	{
		if ($isHtml) {
			$this->setHtmlFormat();
		}
		else {
			$this->setTextFormat();
		}

		return $this;
	}

	/**
	 * Return the character set of the message.
	 * @return EmailCharset
	 */
	public function getCharSet(): EmailCharset
	{
		return $this->charSet;
	}

	/**
	 * Sets the character set of the message.
	 * @param EmailCharset|string $charSet
	 * @return self
	 */
	public function setCharSet(EmailCharset|string $charSet): self
	{
		if (is_string($charSet)) {
			$charSet = EmailCharset::parse($charSet);
		}

		$this->charSet = $charSet;

		return $this;
	}

	/**
	 * Return the HTML or plain text message body.
	 * @return string texte du mail.
	 */
	public function getText(): string
	{
		return $this->text ?? '';
	}

	/**
	 * Sets the HTML or plain text message body.
	 * @param string|null $text
	 * @return self
	 */
	public function setText(?string $text): self
	{
		$this->text = self::formatText($text);

		return $this;
	}

	// ========== Sending options ==========

	/**
	 * Return the sending date time of the message.
	 */
	public function getSendingDateTime(): ?\DateTime
	{
		return $this->sendingDateTime;
	}

	/**
	 * Sets the sending date time of the message.
	 * @param \DateTime|null $sendingDateTime
	 * @return self
	 */
	public function setSendingDateTime(?\DateTime $sendingDateTime): self
	{
		$this->sendingDateTime = $sendingDateTime;

		return $this;
	}


	// ========== Réinitialiser ==========

	/**
	 * Clear everything.
	 */
	public function clear(): void
	{
		$this->clearSender();
		$this->clearReplyTo();
		$this->clearRecipients();
		$this->clearAttachments();
	}













	// ========== format for display ==========

	/**
	 * @param string $email
	 * @param string|null $name
	 * @param bool $formatHtml
	 * @return string
	 */
	public static function formatEmailAndName(string $email, ?string $name=null, bool $formatHtml=true): string
	{
		if (!empty($name)) {
			return $name.' '.($formatHtml?'&lt;':'<').$email.($formatHtml?'&gt;':'>');
		}
		return $email;
	}

	/**
	 * @param array $emailList
	 * @return array
	 */
	private static function formatEmailList(array $emailList): array
	{
		$formattedList = [];
		foreach ($emailList as $emailData) {
			if (!empty($emailData)) {
				$formattedList[] = self::formatEmailAndName($emailData[0], $emailData[1] ?? null);
			}
		}
		return $formattedList;
	}



	// ========== private ==========

	/**
	 * @param EmailAddressKind $kind
	 * @param array $emailList
	 */
	private function addEmailAddressList(EmailAddressKind $kind, array $emailList): void
	{
		foreach ($emailList as $emailData) {
			if (is_array($emailData)) {
				$email = $emailData[0] ?? '';
				$name = $emailData[1] ?? '';
			}
			else {
				$email = $emailData;
				$name = '';
			}
			if (!empty($email)) {
				$this->addEmailAddress($kind, $email, $name);
			}
		}
	}

	/**
	 * Adds an address to one of the recipient arrays
	 * Addresses that have been added already return false, but do not throw exceptions
	 * @param EmailAddressKind $kind One of 'listTo', 'listCc', 'listBcc', 'replyTo'
	 * @param string $emailAddress The email address to send to
	 * @param string $name
	 * @return boolean true on success, false if address already used or invalid in some way
	 * @access private
	 */
	private function addEmailAddress(EmailAddressKind $kind, string $emailAddress, string $name = ''): bool
	{
		$emailAddress = trim($emailAddress);
		$name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
		if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
			//trace('Invalid address : '.$emailAddress);
			return false;
		}

		if (EmailAddressKind::replyTo === $kind) {
			if (!array_key_exists(mb_strtolower($emailAddress), $this->replyTo)) {
				$this->replyTo[mb_strtolower($emailAddress)] = [$emailAddress, $name];
				return true;
			}

			return false;
		}

		if (!array_key_exists(mb_strtolower($emailAddress), $this->allAddresses)) {
			$key = match($kind) {
				EmailAddressKind::to => 'listTo',
				EmailAddressKind::cc => 'listCc',
				EmailAddressKind::bcc => 'listBcc',
				default => $kind->value,
			};
			$this->$key[] = [$emailAddress, $name];
			$this->allAddresses[mb_strtolower($emailAddress)] = true;
			return true;
		}

		return false;
	}

}
