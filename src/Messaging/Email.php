<?php

namespace Osimatic\Messaging;

use Osimatic\FileSystem\File;

/**
 * Represents an email message with all its components (recipients, attachments, content, etc.).
 * This class provides a comprehensive API for building and managing email messages before sending them through various transport methods.
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
	 * An attachment element is an array containing:
	 * [0] => String if attachment is a string instead of a file, or full path to the file if attachment is a file, or full path to the image if attachment is an embedded image
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
	 * Internal array to track all added recipient addresses to prevent duplicates.
	 * @var array
	 */
	private array $allAddresses = [];


	/**
	 * Construct a new Email instance.
	 * Initializes the sending date time to the current date and time.
	 */
	public function __construct()
	{
		$this->sendingDateTime = \Osimatic\Calendar\DateTime::getCurrentDateTime();
	}



	/**
	 * Get the email identifier.
	 * @return string|null The identifier, or null if not set
	 */
	public function getIdentifier(): ?string
	{
		return $this->identifier;
	}

	/**
	 * Set the email identifier.
	 * @param string|null $identifier The identifier to set
	 */
	public function setIdentifier(?string $identifier): void
	{
		$this->identifier = $identifier;
	}



	// ========== Sender ==========

	/**
	 * Get the From email address for the message.
	 * @return string|null The sender's email address, or null if not set
	 */
	public function getFromEmailAddress(): ?string
	{
		return $this->fromEmailAddress;
	}

	/**
	 * Set the From email address for the message.
	 * @param string|null $emailAddress The sender's email address to set
	 * @return self Returns this instance for method chaining
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
	 * Get the From name of the message.
	 * @return string|null The sender's display name, or null if not set
	 */
	public function getFromName(): ?string
	{
		return $this->fromName;
	}

	/**
	 * Set the From name of the message.
	 * @param string|null $name The sender's display name to set
	 * @return self Returns this instance for method chaining
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
	 * Set both the From email address and From name.
	 * @param string $emailAddress The sender's email address
	 * @param string|null $name The sender's display name (optional)
	 * @param bool $auto Whether to also set the envelope sender address (default: true)
	 * @return self Returns this instance for method chaining
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
	 * Set the envelope sender email address.
	 * @param string|null $emailAddress The envelope sender address (used for bounce handling)
	 * @return self Returns this instance for method chaining
	 */
	public function setSender(?string $emailAddress): self
	{
		$this->sender = $emailAddress;

		return $this;
	}

	/**
	 * Get the envelope sender email address.
	 * @return string|null The envelope sender address, or null if not set
	 */
	public function getSender(): ?string
	{
		return $this->sender ?? null;
	}


	/**
	 * Get the email address that a reading confirmation should be sent to (read receipt).
	 * @return string|null The read receipt email address, or null if not set
	 */
	public function getConfirmReadingTo(): ?string
	{
		return $this->confirmReadingTo;
	}

	/**
	 * Set the email address that a reading confirmation should be sent to (read receipt).
	 * @param string|null $emailAddress The read receipt email address
	 * @return self Returns this instance for method chaining
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



	// ========== Reply-To ==========

	/**
	 * Get all "Reply-To" addresses.
	 * @return array Array of Reply-To addresses, each containing [email, name]
	 */
	public function getReplyTo(): array
	{
		return array_values($this->replyTo);
	}

	/**
	 * Get the first "Reply-To" email address.
	 * @return string The first Reply-To email address, or empty string if none set
	 */
	public function getReplyToEmail(): string
	{
		return $this->getReplyTo()[0][0] ?? '';
	}

	/**
	 * Add a "Reply-To" address.
	 * @param string|null $emailAddress The Reply-To email address
	 * @param string|null $name The Reply-To display name (optional)
	 * @return self Returns this instance for method chaining
	 */
	public function addReplyTo(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress(EmailAddressKind::replyTo, $emailAddress, $name);

		return $this;
	}

	/**
	 * Set the "Reply-To" address (replaces any existing Reply-To addresses).
	 * @param string|null $emailAddress The Reply-To email address
	 * @param string|null $name The Reply-To display name (optional)
	 * @return self Returns this instance for method chaining
	 */
	public function setReplyTo(?string $emailAddress, ?string $name = ''): self
	{
		$this->clearReplyTo();
		$this->addEmailAddress(EmailAddressKind::replyTo, $emailAddress, $name);

		return $this;
	}

	/**
	 * Clear all Reply-To recipients.
	 */
	public function clearReplyTo(): void
	{
		$this->replyTo = [];
	}

	/**
	 * Format all Reply-To addresses as a string with a specified separator.
	 * @param string $separator The separator to use between addresses (default: ' ; ')
	 * @return string The formatted Reply-To addresses string
	 */
	public function formatReplyTo(string $separator=' ; '): string
	{
		return implode($separator, self::formatEmailList($this->getReplyTo()));
	}


	// ========== Recipients ==========

	/**
	 * Get the array of 'to' names and addresses.
	 * @return array Array of To recipients, each containing [email, name]
	 */
	public function getListTo(): array
	{
		return $this->listTo;
	}

	/**
	 * Get all 'to' email addresses only.
	 * @return array Array of To email addresses (without names)
	 */
	public function getListToEmails(): array
	{
		return array_filter(array_map(static fn($email) => $email[0] ?? null, $this->listTo));
	}

	/**
	 * Get the array of 'cc' names and addresses.
	 * @return array Array of Cc recipients, each containing [email, name]
	 */
	public function getListCc(): array
	{
		return $this->listCc;
	}

	/**
	 * Get all 'cc' email addresses only.
	 * @return array Array of Cc email addresses (without names)
	 */
	public function getListCcEmails(): array
	{
		return array_filter(array_map(static fn($email) => $email[0] ?? null, $this->listCc));
	}

	/**
	 * Get the array of 'bcc' names and addresses.
	 * @return array Array of Bcc recipients, each containing [email, name]
	 */
	public function getListBcc(): array
	{
		return $this->listBcc;
	}

	/**
	 * Get all 'bcc' email addresses only.
	 * @return array Array of Bcc email addresses (without names)
	 */
	public function getListBccEmails(): array
	{
		return array_filter(array_map(static fn($email) => $email[0] ?? null, $this->listBcc));
	}

	/**
	 * Get all recipient email addresses combined ('to', 'cc' and 'bcc').
	 * @return array Array of all recipient email addresses
	 */
	public function getAllRecipientAddresses(): array
	{
		return $this->allAddresses;
	}


	/**
	 * Add a "To" recipient.
	 * @param string|null $emailAddress The recipient's email address
	 * @param string|null $name The recipient's display name (optional)
	 * @return self Returns this instance for method chaining
	 */
	public function addTo(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress(EmailAddressKind::to, $emailAddress, $name);

		return $this;
	}

	/**
	 * Add a "To" recipient (alias for addTo).
	 * @param string|null $emailAddress The recipient's email address
	 * @param string|null $name The recipient's display name (optional)
	 * @return self Returns this instance for method chaining
	 */
	public function addRecipient(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress(EmailAddressKind::to, $emailAddress, $name);

		return $this;
	}

	/**
	 * Add a "Cc" recipient.
	 * @param string|null $emailAddress The recipient's email address
	 * @param string|null $name The recipient's display name (optional)
	 * @return self Returns this instance for method chaining
	 */
	public function addCc(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress(EmailAddressKind::cc, $emailAddress, $name);

		return $this;
	}

	/**
	 * Add a "Bcc" recipient.
	 * @param string|null $emailAddress The recipient's email address
	 * @param string|null $name The recipient's display name (optional)
	 * @return self Returns this instance for method chaining
	 */
	public function addBcc(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress(EmailAddressKind::bcc, $emailAddress, $name);

		return $this;
	}

	/**
	 * Add multiple "To" recipients.
	 * @param array $recipientList Array of recipients, each containing [email, name]
	 * @return self Returns this instance for method chaining
	 */
	public function addListTo(array $recipientList): self
	{
		$this->addEmailAddressList(EmailAddressKind::to, $recipientList);

		return $this;
	}

	/**
	 * Add multiple "Cc" recipients.
	 * @param array $recipientList Array of recipients, each containing [email, name]
	 * @return self Returns this instance for method chaining
	 */
	public function addListCc(array $recipientList): self
	{
		$this->addEmailAddressList(EmailAddressKind::cc, $recipientList);

		return $this;
	}

	/**
	 * Add multiple "Bcc" recipients.
	 * @param array $recipientList Array of recipients, each containing [email, name]
	 * @return self Returns this instance for method chaining
	 */
	public function addListBcc(array $recipientList): self
	{
		$this->addEmailAddressList(EmailAddressKind::bcc, $recipientList);

		return $this;
	}


	/**
	 * Set the "To" recipient (replaces any existing To recipients).
	 * @param string|null $emailAddress The recipient's email address
	 * @param string|null $name The recipient's display name (optional)
	 * @return self Returns this instance for method chaining
	 */
	public function setRecipient(?string $emailAddress, ?string $name = ''): self
	{
		$this->clearListTo();
		$this->addEmailAddress(EmailAddressKind::to, $emailAddress, $name);

		return $this;
	}

	/**
	 * Set the "To" recipient (replaces any existing To recipients, alias for setRecipient).
	 * @param string|null $emailAddress The recipient's email address
	 * @param string|null $name The recipient's display name (optional)
	 * @return self Returns this instance for method chaining
	 */
	public function setTo(?string $emailAddress, ?string $name = ''): self
	{
		$this->clearListTo();
		$this->addEmailAddress(EmailAddressKind::to, $emailAddress, $name);

		return $this;
	}

	/**
	 * Set the "Cc" recipient (replaces any existing Cc recipients).
	 * @param string|null $emailAddress The recipient's email address
	 * @param string|null $name The recipient's display name (optional)
	 * @return self Returns this instance for method chaining
	 */
	public function setCc(?string $emailAddress, ?string $name = ''): self
	{
		$this->clearListCc();
		$this->addEmailAddress(EmailAddressKind::cc, $emailAddress, $name);

		return $this;
	}

	/**
	 * Set the "Bcc" recipient (replaces any existing Bcc recipients).
	 * @param string|null $emailAddress The recipient's email address
	 * @param string|null $name The recipient's display name (optional)
	 * @return self Returns this instance for method chaining
	 */
	public function setBcc(?string $emailAddress, ?string $name = ''): self
	{
		$this->clearListBcc();
		$this->addEmailAddress(EmailAddressKind::bcc, $emailAddress, $name);

		return $this;
	}

	/**
	 * Set multiple "To" recipients (replaces any existing To recipients).
	 * @param array $recipientList Array of recipients, each containing [email, name]
	 * @return self Returns this instance for method chaining
	 */
	public function setListRecipients(array $recipientList): self
	{
		$this->clearListTo();
		$this->addEmailAddressList(EmailAddressKind::to, $recipientList);

		return $this;
	}

	/**
	 * Set multiple "To" recipients (replaces any existing To recipients, alias for setListRecipients).
	 * @param array $recipientList Array of recipients, each containing [email, name]
	 * @return self Returns this instance for method chaining
	 */
	public function setListTo(array $recipientList): self
	{
		$this->clearListTo();
		$this->addEmailAddressList(EmailAddressKind::to, $recipientList);

		return $this;
	}

	/**
	 * Set multiple "Cc" recipients (replaces any existing Cc recipients).
	 * @param array $recipientList Array of recipients, each containing [email, name]
	 * @return self Returns this instance for method chaining
	 */
	public function setListCc(array $recipientList): self
	{
		$this->clearListCc();
		$this->addEmailAddressList(EmailAddressKind::cc, $recipientList);

		return $this;
	}

	/**
	 * Set multiple "Bcc" recipients (replaces any existing Bcc recipients).
	 * @param array $recipientList Array of recipients, each containing [email, name]
	 * @return self Returns this instance for method chaining
	 */
	public function setListBcc(array $recipientList): self
	{
		$this->clearListBcc();
		$this->addEmailAddressList(EmailAddressKind::bcc, $recipientList);

		return $this;
	}



	/**
	 * Format all "To" recipients as a string with a specified separator.
	 * @param string $separator The separator to use between addresses (default: ' ; ')
	 * @return string The formatted To recipients string
	 */
	public function formatListTo(string $separator=' ; '): string
	{
		return implode($separator, self::formatEmailList($this->getListTo()));
	}

	/**
	 * Format all "Cc" recipients as a string with a specified separator.
	 * @param string $separator The separator to use between addresses (default: ' ; ')
	 * @return string The formatted Cc recipients string
	 */
	public function formatListCc(string $separator=' ; '): string
	{
		return implode($separator, self::formatEmailList($this->getListCc()));
	}

	/**
	 * Format all "Bcc" recipients as a string with a specified separator.
	 * @param string $separator The separator to use between addresses (default: ' ; ')
	 * @return string The formatted Bcc recipients string
	 */
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

	// ========== Attachments ==========

	/**
	 * Get the array of all attachments.
	 * @return array Array of attachments
	 */
	public function getAttachments(): array
	{
		return $this->listAttachments;
	}

	/**
	 * Get the array of all attachments.
	 * @return array Array of attachments
	 */
	public function getListAttachments(): array
	{
		return $this->listAttachments;
	}

	/**
	 * Check if a regular attachment (non-inline) is present.
	 * @return bool True if at least one attachment exists, false otherwise
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
	 * Check if an inline attachment (embedded image) is present.
	 * @return bool True if at least one inline image exists, false otherwise
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
	 * Add multiple attachments from an array.
	 * @param array $listAttachments Array of attachments to add
	 * @return self Returns this instance for method chaining
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
	 * Add an attachment from a file path on the filesystem.
	 * The attachment will be skipped if the file doesn't exist or exceeds the maximum file size.
	 * @param string $path Full path to the attachment file
	 * @param string|null $name Display name for the attachment (optional, defaults to filename)
	 * @param EmailEncoding $encoding File encoding method (default: base64)
	 * @param string|null $mimeType MIME type of the file (auto-detected if not provided)
	 * @return self Returns this instance for method chaining
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
	 * This method can be used to attach ASCII or binary data, such as a BLOB record from a database.
	 * @param string $string The attachment data as a string
	 * @param string $filename The display name of the attachment
	 * @param EmailEncoding $encoding The encoding method (default: base64)
	 * @param string|null $mimeType MIME type of the attachment (auto-detected if not provided)
	 * @return self Returns this instance for method chaining
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
	 * These differ from regular attachments as they are displayed inline with the message content.
	 * This is commonly used in HTML messages to embed images referenced by the content ID.
	 * @param string $path Full path to the embedded file
	 * @param string $cid Content ID used to reference this resource in HTML (e.g., <img src="cid:value">)
	 * @param string|null $name Display name for the embedded resource (optional, defaults to filename)
	 * @param EmailEncoding $encoding The encoding method (default: base64)
	 * @param string|null $mimeType MIME type of the file (auto-detected if not provided)
	 * @return self Returns this instance for method chaining
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
	 * Add an embedded attachment from a string.
	 * This can include images, sounds, and other document types as binary data.
	 * Be sure to set the correct MIME type (e.g., 'image/jpeg', 'image/gif', 'image/png' for images).
	 * @param string $string The binary data of the embedded resource
	 * @param string $cid Content ID used to reference this resource in HTML (e.g., <img src="cid:value">)
	 * @param string|null $name Display name for the embedded resource (optional)
	 * @param EmailEncoding $encoding The encoding method (default: base64)
	 * @param string|null $mimeType MIME type of the resource (auto-detected if not provided)
	 * @return self Returns this instance for method chaining
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
	 * Clear all attachments (filesystem, string, binary, and inline).
	 */
	public function clearAttachments(): void
	{
		$this->listAttachments = [];
	}


	// ========== Subject and text ==========

	/**
	 * Format text by replacing special characters.
	 * @param string|null $str The text to format
	 * @return string The formatted text
	 */
	private static function formatText(?string $str): string
	{
		if ($str === null) {
			return '';
		}
		$str = str_replace("’", "'", $str);
		return $str;
	}

	/**
	 * Get the subject of the message.
	 * @return string The email subject
	 */
	public function getSubject(): string
	{
		return $this->subject ?? '';
	}

	/**
	 * Set the subject of the message.
	 * @param string|null $subject The email subject to set
	 * @return self Returns this instance for method chaining
	 */
	public function setSubject(?string $subject): self
	{
		$this->subject = self::formatText($subject);

		return $this;
	}

	/**
	 * Check if the message content type is HTML.
	 * @return bool True if HTML format, false if plain text
	 */
	public function isHTML(): bool
	{
		return $this->contentType === EmailContentType::TEXT_HTML;
	}

	/**
	 * Set message content type to HTML format.
	 * @return self Returns this instance for method chaining
	 */
	public function setHtmlFormat(): self
	{
		$this->contentType = EmailContentType::TEXT_HTML;

		return $this;
	}

	/**
	 * Set message content type to plain text format.
	 * @return self Returns this instance for method chaining
	 */
	public function setTextFormat(): self
	{
		$this->contentType = EmailContentType::PLAINTEXT;

		return $this;
	}

	/**
	 * Set the MIME content type of the message.
	 * @param EmailContentType|string $contentType The content type to set
	 * @return self Returns this instance for method chaining
	 */
	public function setContentType(EmailContentType|string $contentType): self
	{
		$this->contentType = is_string($contentType) ? EmailContentType::tryFrom($contentType) ?? EmailContentType::TEXT_HTML : $contentType;

		return $this;
	}

	/**
	 * Set message content type to HTML or plain text based on a boolean flag.
	 * @param bool $isHtml True for HTML format, false for plain text
	 * @return self Returns this instance for method chaining
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
	 * Get the character set of the message.
	 * @return EmailCharset The character encoding set
	 */
	public function getCharSet(): EmailCharset
	{
		return $this->charSet;
	}

	/**
	 * Set the character set of the message.
	 * @param EmailCharset|string $charSet The character encoding to set
	 * @return self Returns this instance for method chaining
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
	 * Get the HTML or plain text message body.
	 * @return string The message body content
	 */
	public function getText(): string
	{
		return $this->text ?? '';
	}

	/**
	 * Set the HTML or plain text message body.
	 * @param string|null $text The message body content to set
	 * @return self Returns this instance for method chaining
	 */
	public function setText(?string $text): self
	{
		$this->text = self::formatText($text);

		return $this;
	}

	// ========== Sending options ==========

	/**
	 * Get the sending date and time of the message.
	 * @return \DateTime|null The sending date and time, or null if not set
	 */
	public function getSendingDateTime(): ?\DateTime
	{
		return $this->sendingDateTime;
	}

	/**
	 * Set the sending date and time of the message.
	 * @param \DateTime|null $sendingDateTime The sending date and time to set
	 * @return self Returns this instance for method chaining
	 */
	public function setSendingDateTime(?\DateTime $sendingDateTime): self
	{
		$this->sendingDateTime = $sendingDateTime;

		return $this;
	}


	// ========== Reset ==========

	/**
	 * Clear all email properties (sender, recipients, attachments, etc.).
	 */
	public function clear(): void
	{
		$this->clearSender();
		$this->clearReplyTo();
		$this->clearRecipients();
		$this->clearAttachments();
	}













	// ========== Format for display ==========

	/**
	 * Format an email address and name for display.
	 * @param string $email The email address
	 * @param string|null $name The display name (optional)
	 * @param bool $formatHtml If true, use HTML entities for angle brackets (default: true)
	 * @return string The formatted email address string
	 */
	public static function formatEmailAndName(string $email, ?string $name=null, bool $formatHtml=true): string
	{
		if (!empty($name)) {
			return $name.' '.($formatHtml?'&lt;':'<').$email.($formatHtml?'&gt;':'>');
		}
		return $email;
	}

	/**
	 * Format an array of email addresses for display.
	 * @param array $emailList Array of email addresses, each containing [email, name]
	 * @return array Array of formatted email address strings
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



	// ========== Private methods ==========

	/**
	 * Add multiple email addresses to a recipient list.
	 * @param EmailAddressKind $kind The type of recipient list to add to
	 * @param array $emailList Array of email addresses to add
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
	 * Add an email address to one of the recipient arrays.
	 * Addresses that have been added already return false, but do not throw exceptions.
	 * @param EmailAddressKind $kind The type of recipient (to, cc, bcc, or replyTo)
	 * @param string $emailAddress The email address to add
	 * @param string $name The display name (optional)
	 * @return bool True on success, false if address is already added or invalid
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
