<?php

namespace Osimatic\Helpers\Messaging;

use Osimatic\Helpers\FileSystem\File;

/**
 * Class Email
 * @package Osimatic\Helpers\Messaging
 */
class Email
{
	public const ATTACHMENT_FILESIZE_MAX = 2000000; // 2 Mo

	public const CONTENT_TYPE_PLAINTEXT = 'text/plain';
	public const CONTENT_TYPE_TEXT_CALENDAR = 'text/calendar';
	public const CONTENT_TYPE_TEXT_HTML = 'text/html';
	public const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
	public const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';
	public const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';

	public const ENCODING_7BIT = '7bit';
	public const ENCODING_8BIT = '8bit';
	public const ENCODING_BASE64 = 'base64';
	public const ENCODING_BINARY = 'binary';
	public const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

	/**
	 * The From email address for the message.
	 * @var string
	 */
	private $fromEmailAddress = '';

	/**
	 * The From name of the message.
	 * @var string
	 */
	private $fromName;

	/**
	 * The envelope sender of the message.
	 * This will usually be turned into a Return-Path header by the receiver, and is the address that bounces will be sent to.
	 * @type string
	 */
	protected $sender = '';

	/**
	 * Tableau contenant la liste des personnes en "répondre à" du mail.
	 * Une personne "répondre à" est elle même un tableau contenant :
	 * [0] => son adresse email
	 * [1] => son nom (non obligatoire)
	 * @var array
	 */
	private $replyTo = [];

	/**
	 * Tableau contenant la liste des destinataires du mail.
	 * Un destinataire est lui même un tableau contenant :
	 * [0] => son adresse email
	 * [1] => son nom (non obligatoire)
	 * @var array
	 */
	private $listTo = [];

	/**
	 * Tableau contenant la liste des destinataires en copie du mail.
	 * Un destinataire en copie est lui même un tableau contenant :
	 * [0] => son adresse email
	 * [1] => son nom (non obligatoire)
	 * @var array
	 */
	private $listCc = [];

	/**
	 * Tableau contenant la liste des destinataires en copie cachée du mail.
	 * Un destinataire en copie cachée est lui même un tableau contenant :
	 * [0] => son adresse email
	 * [1] => son nom (non obligatoire)
	 * @var array
	 */
	private $listBcc = [];

	/**
	 * The email address that a reading confirmation should be sent to, also known as read receipt.
	 * @var string
	 */
	private $confirmReadingTo;

	/**
	 * Tableau contenant la liste des pièces jointes.
	 * Une pièce jointe est elle même un tableau contenant :
	 * [0] => String si la pièce jointe est une string à la place d'un fichier ou
	 * Chemin complet vers le fichier si la pièce jointe est un fichier ou
	 * Chemin complet vers l'image si la pièce jointe est une embedded image
	 * [1] => Nom du fichier correspondant à la pièce jointe
	 * [2] => Nom de la pièce jointe affiché dans le mail
	 * [3] => Encodage du fichier en pièce jointe. Vaut "base64" par défaut.
	 * [4] => Type MIME de la pièce jointe. Vaut "application/octet-stream" par défaut.
	 * [5] => true si la pièce jointe est une string à la place d'un fichier, false sinon
	 * [6] => "attachment" si c'est une pièce jointe à part, "inline" si c'est une embedded image
	 * [7] => 0 si c'est une pièce jointe à part, id unique si c'est une embedded image
	 * @var array
	 */
	private $listAttachments = [];

	/**
	 * Format du mail (true si le mail est au format HTML, false s'il est au format texte).
	 * @var boolean
	 */
	private $isHtml = false;

	/**
	 * The Subject of the message.
	 * @var string
	 */
	private $subject;

	/**
	 * An HTML or plain text message body.
	 * @var string
	 */
	private $text;

	/**
	 * The plain-text message body.
	 * This body can be read by mail clients that do not have HTML email capability such as mutt & Eudora.
	 * Clients that can read HTML will view the normal Body.
	 * @var string
	 */
	private $altText = '';

	/**
	 * Email priority.
	 * Options: null (default), 1 = High, 3 = Normal, 5 = low.
	 * @var int|null
	 */
	private $priority;

	/**
	 * Encodage de caractère du mail.
	 * @var string
	 */
	private $charSet;

	/**
	 * The MIME Content-type of the message.
	 * @var string
	 */
	private $contentType = self::CONTENT_TYPE_PLAINTEXT;

	/**
	 * The message encoding.
	 * Options: "8bit", "7bit", "binary", "base64", and "quoted-printable".
	 * @var string
	 */
	private $encoding = self::ENCODING_8BIT;

	/**
	 * The complete compiled MIME message body.
	 * @var string
	 */
	private $MIMEBody = '';

	/**
	 * The complete compiled MIME message headers.
	 * @var string
	 */
	private $MIMEHeader = '';

	/**
	 * The hostname to use in the Message-ID header and as default HELO string.
	 * @var string
	 */
	private $hostname = '';

	/**
	 * An ID to be used in the Message-ID header.
	 * If empty, a unique id will be generated.
	 * You can set your own, but it must be in the format "<id@domain>", as defined in RFC5322 section 3.6.4 or it will be ignored.
	 * @see https://tools.ietf.org/html/rfc5322#section-3.6.4
	 * @var string
	 */
	private $messageID = '';

	/**
	 * The message date to be used in the Date header and the sending date of the message
	 * If empty, the current date will be added.
	 * @var \DateTime
	 */
	private $sendingDateTime;

	/**
	 * private usage
	 * @var array
	 */
	private $allAdresses;


	/**
	 * Email constructor.
	 */
	public function __construct()
	{
		$this->sendingDateTime = \Osimatic\Helpers\DateTime\DateTime::getCurrentDateTime();
	}



	// ========== Vérification ==========

	/**
	 * @param string $email l'adresse email à vérifier
	 * @return bool
	 */
	public static function check(string $email): bool
	{
		return filter_var($email, FILTER_VALIDATE_EMAIL);;
	}

	// ========== Get element ==========

	/**
	 * Retourne le nom de domaine (fournisseur) contenu dans une adresse email
	 * @param string $email l'adresse email dans laquelle récupérer le nom de domaine (fournisseur)
	 * @return string|null le nom de domaine (fournisseur) contenu dans l'adresse email
	 */
	public static function getHost(string $email): ?string
	{
		// preg_replace('!^[a-z0-9._-]+@(.+)$!', '$1', $email)
		if (strstr($email, '@') === false) {
			return null;
		}
		return substr($email, (strpos($email, '@')+1));
	}

	/**
	 * Retourne le domaine de premier niveau contenu dans une adresse email, avec éventuellement le séparateur "."
	 * @param string $email l'adresse email dans laquelle récupérer le domaine de premier niveau
	 * @param boolean $withPoint true pour ajouter le séparateur "." avant le domaine de premier niveau, false sinon (true par défaut)
	 * @return string le domaine de premier niveau contenu dans l'adresse email
	 */
	public static function getTld(string $email, bool $withPoint=true): string
	{
		$host = self::getHost($email);
		return \Osimatic\Helpers\Network\URL::getTld($host, $withPoint);
	}




	// ========== Expéditeur ==========

	/**
	 * Get de l'adresse e-mail de l'expéditeur du mail.
	 * @return string : l'adresse e-mail de l'expéditeur.
	 */
	public function getFromEmailAddress(): ?string
	{
		return $this->fromEmailAddress;
	}

	/**
	 * Set de l'adresse e-mail de l'expéditeur du mail.
	 * @param string $emailAddress : l'adresse e-mail de l'expéditeur.
	 * @return self
	 */
	public function setFromEmailAddress(?string $emailAddress): self
	{
		$emailAddress = trim($emailAddress);
		if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
			//trace('Invalid address : '.$emailAddress);
			return $this;
		}

		$this->fromEmailAddress = $emailAddress;

		return $this;
	}

	/**
	 * Get du nom de l'expéditeur du mail.
	 * @return string : le nom de l'expéditeur.
	 */
	public function getFromName(): ?string
	{
		return $this->fromName;
	}

	/**
	 * Set du nom de l'expéditeur du mail.
	 * @param string $name : le nom de l'expéditeur.
	 * @return self
	 */
	public function setFromName(?string $name): self
	{
		$name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
		$this->fromName = $name;

		return $this;
	}

	/**
	 * Set the fromEmailAddress and fromName properties.
	 * @param string $emailAddress
	 * @param string $name
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
	 * @param string $emailAddress
	 * @return self
	 */
	public function setSender(?string $emailAddress): self
	{
		$this->sender = $emailAddress;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getSender(): ?string
	{
		return $this->sender;
	}


	/**
	 * @return string
	 */
	public function getConfirmReadingTo(): ?string
	{
		return $this->confirmReadingTo;
	}

	/**
	 * @param string $emailAddress
	 * @return self
	 */
	public function setConfirmReadingTo(?string $emailAddress): self
	{
		$emailAddress = trim($emailAddress);
		if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
			//trace('Invalid address : '.$emailAddress);
			return $this;
		}

		$this->confirmReadingTo = $emailAddress;

		return $this;
	}

	/**
	 * Réinitialise le nom et l'adresse e-mail de l'expéditeur.
	 */
	public function clearSender(): void
	{
		$this->fromEmailAddress = '';
		$this->fromName = '';
		$this->sender = '';
	}



	// ========== Répondre à ==========

	/**
	 * Get du nom et de l'adresse e-mail de la personne qui peut recevoir la réponse au mail.
	 * @return array : un tableau contenant le nom et l'adresse e-mail de la personne pour la réponse.
	 */
	public function getReplyTo(): array
	{
		return array_values($this->replyTo);
	}

	/**
	 * @return string|null
	 */
	public function getReplyToEmail(): ?string
	{
		return $this->getReplyTo()[0] ?? '';
	}

	/**
	 * Ajoute une personne qui peut recevoir la réponse au mail.
	 * @param string $emailAddress : l'adresse e-mail pour la réponse.
	 * @param string $name : le nom de la personne pour la réponse.
	 * @return self
	 */
	public function addReplyTo(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress('replyTo', $emailAddress, $name);

		return $this;
	}

	/**
	 * Set du nom et de l'adresse e-mail de la personne qui peut recevoir la réponse au mail.
	 * @param string $emailAddress : adresse e-mail pour la réponse.
	 * @param string $name : nom de la personne pour la réponse.
	 * @return self
	 */
	public function setReplyTo(?string $emailAddress, ?string $name = ''): self
	{
		$this->clearReplyTo();
		$this->addEmailAddress('replyTo', $emailAddress, $name);
		// $this->replyTo = array(array($replyToEmail, $replyToName));

		return $this;
	}

	/**
	 * Réinitialise le nom et l'adresse e-mail de la personne pour la réponse.
	 */
	public function clearReplyTo(): void
	{
		$this->replyTo = [];
	}

	/**
	 * @param string $separator
	 * @return string|null
	 */
	public function formatReplyTo(string $separator=' ; '): ?string
	{
		return implode($separator, self::formatEmailList($this->getReplyTo()));
	}


	// ========== Destinataires ==========

	/**
	 * Récupère la liste des destinataires du mail.
	 * @return array : un tableau contenant la liste des destinataires du mail.
	 */
	public function getListTo(): array
	{
		return $this->listTo;
	}

	/**
	 * @return array
	 */
	public function getListToEmails(): array
	{
		$listEmails = [];
		foreach ($this->listTo as $email) {
			if (!empty($email[0])) {
				$listEmails[] = $email[0];
			}
		}
		return $listEmails;
	}

	/**
	 * Récupère la liste des destinataires en copie du mail.
	 * @return array : un tableau contenant la liste des destinataires en copie du mail.
	 */
	public function getListCc(): array
	{
		return $this->listCc;
	}

	/**
	 * @return array
	 */
	public function getListCcEmails(): array
	{
		$listEmails = array();
		foreach ($this->listCc as $email) {
			if (!empty($email[0])) {
				$listEmails[] = $email[0];
			}
		}
		return $listEmails;
	}

	/**
	 * Récupère la liste des destinataires en copie cachée du mail.
	 * @return array : un tableau contenant la liste des destinataires en copie cachée du mail.
	 */
	public function getListBcc(): array
	{
		return $this->listBcc;
	}

	public function getListBccEmails(): array
	{
		$listEmails = array();
		foreach ($this->listBcc as $email) {
			if (!empty($email[0])) {
				$listEmails[] = $email[0];
			}
		}
		return $listEmails;
	}

	/**
	 * Allows for public read access to 'all_recipients' property.
	 * @return array
	 */
	public function getAllRecipientAddresses(): array
	{
		return $this->allAdresses;
	}


	/**
	 * Ajoute un destinataire pour le mail.
	 * @param string $emailAddress : l'adresse e-mail du destinataire.
	 * @param string $name : le nom du destinataire.
	 * @return self
	 */
	public function addTo(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress('listTo', $emailAddress, $name);

		return $this;
	}

	/**
	 * @param string|null $emailAddress
	 * @param string|null $name
	 * @return self
	 */
	public function addRecipient(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress('listTo', $emailAddress, $name);

		return $this;
	}

	/**
	 * Ajoute un destinataire en copie pour le mail.
	 * Attention, les destinataires en copie ne sont pas pris en compte pour certains mailer (fonction mail() de PHP, MailByFile, etc.).
	 * @param string $emailAddress : l'adresse e-mail du destinataire en copie.
	 * @param string $name : le nom du destinataire en copie.
	 * @return self
	 */
	public function addCc(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress('listCc', $emailAddress, $name);

		return $this;
	}

	/**
	 * Ajoute un destinataire en copie cachée pour le mail.
	 * Attention, les destinataires en copie cachée ne sont pas pris en compte pour certains mailer (fonction mail() de PHP, MailByFile, etc.).
	 * @param string $emailAddress : l'adresse e-mail du destinataire en copie cachée.
	 * @param string $name : le nom du destinataire en copie cachée.
	 * @return self
	 */
	public function addBcc(?string $emailAddress, ?string $name = ''): self
	{
		$this->addEmailAddress('listBcc', $emailAddress, $name);

		return $this;
	}

	/**
	 * Ajoute une liste de destinataires pour le mail.
	 * @param array $listTo
	 * @return self
	 */
	public function addListTo(array $listTo): self
	{
		$this->addEmailAddressList('listTo', $listTo);

		return $this;
	}

	/**
	 * Ajoute une liste de destinataires en copie pour le mail.
	 * @param array $listCc
	 * @return self
	 */
	public function addListCc(array $listCc): self
	{
		$this->addEmailAddressList('listCc', $listCc);

		return $this;
	}

	/**
	 * Ajoute une liste de destinataires en copie cachée pour le mail.
	 * @param array $listBcc
	 * @return self
	 */
	public function addListBcc(array $listBcc): self
	{
		$this->addEmailAddressList('listBcc', $listBcc);

		return $this;
	}


	/**
	 * @param string|null $emailAddress
	 * @param string|null $name
	 * @return self
	 */
	public function setRecipient(?string $emailAddress, ?string $name = ''): self
	{
		$this->clearListTo();
		$this->addEmailAddress('listTo', $emailAddress, $name);

		return $this;
	}

	/**
	 * @param string|null $emailAddress
	 * @param string|null $name
	 * @return self
	 */
	public function setTo(?string $emailAddress, ?string $name = ''): self
	{
		$this->clearListTo();
		$this->addEmailAddress('listTo', $emailAddress, $name);

		return $this;
	}

	/**
	 * Définie une liste de destinataires pour le mail.
	 * @param array $listRecipients
	 * @return self
	 */
	public function setListRecipients(array $listRecipients): self
	{
		$this->clearListTo();
		$this->addEmailAddressList('listTo', $listRecipients);

		return $this;
	}

	/**
	 * Définie une liste de destinataires pour le mail.
	 * @param array $listTo
	 * @return self
	 */
	public function setListTo(array $listTo): self
	{
		$this->clearListTo();
		$this->addEmailAddressList('listTo', $listTo);

		return $this;
	}

	/**
	 * Définie une liste de destinataires en copie pour le mail.
	 * @param array $listCc
	 * @return self
	 */
	public function setListCc(array $listCc): self
	{
		$this->clearListCc();
		$this->addEmailAddressList('listCc', $listCc);

		return $this;
	}

	/**
	 * Définie une liste de destinataires en copie cachée pour le mail.
	 * @param array $listBcc
	 * @return self
	 */
	public function setListBcc(array $listBcc): self
	{
		$this->clearListBcc();
		$this->addEmailAddressList('listBcc', $listBcc);

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
	 * Réinitialise la liste des destinataires (supprime tous ceux actuellement présents).
	 */
	public function clearListTo(): void
	{
		foreach ($this->listTo as $to) {
			if (!empty($to[0]) && isset($this->allAdresses[strtolower($to[0])])) {
				unset($this->allAdresses[strtolower($to[0])]);
			}
		}
		$this->listTo = [];
	}

	/**
	 * Réinitialise la liste des destinataires en copie (supprime tous ceux actuellement présents).
	 */
	public function clearListCc(): void
	{
		foreach ($this->listCc as $cc) {
			if (!empty($cc[0]) && isset($this->allAdresses[strtolower($cc[0])])) {
				unset($this->allAdresses[strtolower($cc[0])]);
			}
		}
		$this->listCc = [];
	}

	/**
	 * Réinitialise la liste des destinataires en copie cachée (supprime tous ceux actuellement présents).
	 */
	public function clearListBcc(): void
	{
		foreach ($this->listBcc as $bcc) {
			if (!empty($bcc[0]) && isset($this->allAdresses[strtolower($bcc[0])])) {
				unset($this->allAdresses[strtolower($bcc[0])]);
			}
		}
		$this->listBcc = [];
	}

	/**
	 * Réinitialise la liste des destinataires, ainsi que ceux en copie et en copie cachée.
	 */
	public function clearRecipients(): void
	{
		$this->listTo = [];
		$this->listCc = [];
		$this->listBcc = [];
		$this->allAdresses = [];
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
			$attachmentPath = '';
			$attachmentName = '';
			if (is_array($attachment)) {
				if (isset($attachment[0])) {
					$attachmentPath = $attachment[0];
					if (isset($attachment[1])) {
						$attachmentName = $attachment[1];
					}
				}
			}
			else {
				$attachmentPath = $attachment;
			}
			if (!empty($attachmentPath)) {
				$this->addAttachment($attachmentPath, $attachmentName);
			}
		}

		return $this;
	}

	/**
	 * Add an attachment from a path on the filesystem.
	 * Returns false if the file could not be found or read.
	 * @param string $path Path to the attachment.
	 * @param string $name Overrides the attachment name.
	 * @param string $encoding File encoding (see $Encoding).
	 * @param string $type File extension (MIME) type.
	 * @param string $disposition Disposition to use
	 * @return self
	 */
	public function addAttachment(string $path, string $name = '', string $encoding = 'base64', string $type = '', string $disposition = 'attachment'): self
	{
		if (!@is_file($path)) {
			//error('Could not access file: '.$path);
			return $this;
		}

		if (filesize($path) > self::ATTACHMENT_FILESIZE_MAX) {
			//error('Ce fichier est trop gros pour être mis en pièce jointe du mail: '.$path);
			return $this;
		}

		//If a MIME type is not specified, try to work it out from the file name
		if (empty($type)) {
			$type = File::getMimeTypesForFile($path);
		}

		$filename = basename($path);
		if (empty($name)) {
			$name = $filename;
		}

		$this->listAttachments[] = [
			0 => $path,
			1 => $filename,
			2 => $name,
			3 => $encoding,
			4 => $type,
			5 => false, // isStringAttachment
			6 => $disposition,
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
	 * @param string $encoding File encoding (see $Encoding).
	 * @param string $type File extension (MIME) type.
	 * @param string $disposition Disposition to use
	 * @return self
	 */
	public function addStringAttachment(string $string, string $filename, string $encoding = 'base64', string $type = '', string $disposition = 'attachment'): self
	{
		//If a MIME type is not specified, try to work it out from the file name
		if (empty($type)) {
			$type = File::getMimeTypesForFile($filename);
		}
		// Append to $attachment array
		$this->listAttachments[] = array(
			0 => $string,
			1 => $filename,
			2 => basename($filename),
			3 => $encoding,
			4 => $type,
			5 => true, // isStringAttachment
			6 => $disposition,
			7 => 0
		);

		return $this;
	}

	/**
	 * Add an embedded (inline) attachment from a file.
	 * This can include images, sounds, and just about any other document type.
	 * These differ from 'regular' attachmants in that they are intended to be
	 * displayed inline with the message, not just attached for download.
	 * This is used in HTML messages that embed the images
	 * the HTML refers to using the $cid value.
	 * @param string $path Path to the attachment.
	 * @param string $cid Content ID of the attachment; Use this to reference the content when using an embedded image in HTML.
	 * @param string $name Overrides the attachment name.
	 * @param string $encoding File encoding (see $Encoding).
	 * @param string $type File MIME type.
	 * @param string $disposition Disposition to use
	 * @return self
	 */
	public function addEmbeddedImage(string $path, string $cid, string $name = '', string $encoding = 'base64', string $type = '', string $disposition = 'inline'): self
	{
		if (!@is_file($path)) {
			//error('Could not access file: '.$path);
			return $this;
		}

		if (filesize($path) > self::ATTACHMENT_FILESIZE_MAX) {
			//error('Ce fichier est trop gros pour être mis en pièce jointe du mail: '.$path);
			return $this;
		}

		//If a MIME type is not specified, try to work it out from the file name
		if (empty($type)) {
			$type = File::getMimeTypesForFile($path);
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
			3 => $encoding,
			4 => $type,
			5 => false, // isStringAttachment
			6 => $disposition,
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
	 * @param string $name
	 * @param string $encoding File encoding (see $Encoding).
	 * @param string $type MIME type.
	 * @param string $disposition Disposition to use
	 * @return self
	 */
	public function addStringEmbeddedImage(string $string, string $cid, string $name = '', string $encoding = 'base64', string $type = '', string $disposition = 'inline'): self
	{
		//If a MIME type is not specified, try to work it out from the name
		if (empty($type)) {
			$type = File::getMimeTypesForFile($name);
		}

		// Append to $attachment array
		$this->listAttachments[] = [
			0 => $string,
			1 => $name,
			2 => $name,
			3 => $encoding,
			4 => $type,
			5 => true, // isStringAttachment
			6 => $disposition,
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

	/**
	 * Get du sujet du mail.
	 * @return string sujet du mail.
	 */
	public function getSubject(): ?string
	{
		return $this->subject;
	}

	/**
	 * Set du sujet du mail.
	 * @param string $subject sujet du mail.
	 * @param bool $encode
	 * @return self
	 */
	public function setSubject(?string $subject, bool $encode=false): self
	{
		if ($encode && strtolower($this->charSet) === 'utf-8') {
			$subject = utf8_encode($subject);
		}
		$this->subject = $subject;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isHTML(): bool
	{
		return $this->isHtml;
	}

	/**
	 * Définit le format du mail au format HTML.
	 * @return self
	 */
	public function setHtmlFormat(): self
	{
		$this->isHtml = true;

		return $this;
	}

	/**
	 * Définit le format du mail au format texte.
	 * @return self
	 */
	public function setTextFormat(): self
	{
		$this->isHtml = false;

		return $this;
	}

	/**
	 * Sets message type to HTML or plain.
	 * @param bool $isHtml
	 * @return self
	 */
	public function setIsHTML(bool $isHtml): self
	{
		$this->isHtml = $isHtml;

		return $this;
	}

	/**
	 * @return string
	 */
	public function getCharSet(): string
	{
		return $this->charSet;
	}

	/**
	 * Définit l'encodage de caractère du mail.
	 * @param string $charSet
	 * @return self
	 */
	public function setCharSet(string $charSet): self
	{
		$this->charSet = $charSet;

		return $this;
	}

	/**
	 * Get du texte du mail.
	 * @return string texte du mail.
	 */
	public function getText(): ?string
	{
		return $this->text;
	}

	/**
	 * Set du texte du mail.
	 * @param string $text texte du mail.
	 * @param bool $encode
	 * @return self
	 */
	public function setText(?string $text, bool $encode=false): self
	{
		if ($encode && strtolower($this->charSet) === 'utf-8') {
			$text = utf8_encode($text);
		}
		$this->text = $text;

		return $this;
	}

	// ========== Sending options ==========

	/**
	 * Get de la date et heure de l'envoi du mail.
	 */
	public function getSendingDateTime(): ?\DateTime
	{
		return $this->sendingDateTime;
	}

	/**
	 * Set de la date et heure de l'envoi du mail.
	 * @param \DateTime $sendingDateTime
	 * @return self
	 */
	public function setSendingDateTime(?\DateTime $sendingDateTime): self
	{
		$this->sendingDateTime = $sendingDateTime;

		return $this;
	}


	// ==========  ==========

	/**
	 * Réinitialise tout.
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
	 * @param string $kind
	 * @param array $emailList
	 */
	private function addEmailAddressList(string $kind, array $emailList): void
	{
		foreach ($emailList as $emailData) {
			$email = '';
			$name = '';
			if (is_array($emailData)) {
				if (isset($emailData[0])) {
					$email = $emailData[0];
					if (isset($emailData[1])) {
						$name = $emailData[1];
					}
				}
			}
			else {
				$email = $emailData;
			}
			if (!empty($email)) {
				$this->addEmailAddress($kind, $email, $name);
			}
		}
	}

	/**
	 * Adds an address to one of the recipient arrays
	 * Addresses that have been added already return false, but do not throw exceptions
	 * @param string $kind One of 'listTo', 'listCc', 'listBcc', 'replyTo'
	 * @param string $emailAddress The email address to send to
	 * @param string $name
	 * @return boolean true on success, false if address already used or invalid in some way
	 * @access private
	 */
	private function addEmailAddress(string $kind, string $emailAddress, string $name = ''): bool
	{
		if (!preg_match('/^(listTo|listCc|listBcc|replyTo)$/', $kind)) {
			//trace('Invalid recipient array : '.$kind);
			return false;
		}

		$emailAddress = trim($emailAddress);
		$name = trim(preg_replace('/[\r\n]+/', '', $name)); //Strip breaks and trim
		if (!filter_var($emailAddress, FILTER_VALIDATE_EMAIL)) {
			//trace('Invalid address : '.$emailAddress);
			return false;
		}

		if ($kind !== 'replyTo') {
			if (!array_key_exists(strtolower($emailAddress), $this->allAdresses)) {
				$this->$kind[] = [$emailAddress, $name];
				$this->allAdresses[strtolower($emailAddress)] = true;
				return true;
			}

			return false;
		}

		if (!array_key_exists(strtolower($emailAddress), $this->replyTo)) {
			$this->replyTo[strtolower($emailAddress)] = [$emailAddress, $name];
			return true;
		}

		return false;
	}

}