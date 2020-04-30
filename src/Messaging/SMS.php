<?php

namespace Osimatic\Helpers\Messaging;

use Osimatic\Helpers\ContactDetails\PhoneNumber;

/**
 * Class SMS
 * @package Osimatic\Helpers\Messaging
 */
class SMS
{
	// Nombre de caractères maximum dans un SMS.
	public const MESSAGE_NB_CHAR_MAX = 160;

	/**
	 * Tableau contenant la liste des numéros de téléphone des destinataires du SMS.
	 * @var array
	 */
	protected $recipients = [];

	/**
	 * Texte (message) du SMS.
	 * @var string
	 */
	protected $text;

	/**
	 * True pour couper le texte pour qu'il rentre sur un seul SMS, false pour laisser le texte entier même s'il dépasse la taille d'un SMS.
	 * @var boolean
	 */
	protected $truncateText = false;

	/**
	 * Nom de l'expéditeur du SMS.
	 * @var string
	 */
	protected $senderName;

	/**
	 * Date/heure de l'envoi du SMS, au format SQL. Permet d'envoyer le SMS en différé (null = envoi immédiat).
	 * @var \DateTime
	 */
	protected $sendingDateTime;

	/**
	 *
	 * @var boolean
	 */
	protected $adultContent;


	public function __construct()
	{
		$this->sendingDateTime = \Osimatic\Helpers\DateTime\DateTime::getCurrentDateTime();
	}


	// ========== Sender ==========

	/**
	 * @return string|null
	 */
	public function getSenderName(): ?string
	{
		return $this->senderName;
	}

	/**
	 * @param string|null $senderName
	 * @return self
	 */
	public function setSenderName(?string $senderName): self
	{
		$this->senderName = $senderName;

		return $this;
	}

	// ========== Recipient ==========

	/**
	 * Récupère la liste des destinataires du SMS.
	 * @return array un tableau contenant la liste des destinataires du SMS.
	 */
	public function getListRecipients(): array
	{
		return $this->recipients;
	}

	/**
	 * Récupère l'unique destinataires du SMS.
	 * @return string le numéro de téléphone du destinataires du SMS.
	 */
	public function getRecipient(): ?string
	{
		return $this->recipients[0] ?? null;
	}

	/**
	 * @return array
	 */
	public function getListPhoneNumbers(): array
	{
		return $this->getListRecipients();
	}

	/**
	 * @return string|null
	 */
	public function getPhoneNumber(): ?string
	{
		return $this->getRecipient();
	}

	/**
	 * @return int
	 */
	public function getNbRecipients(): int
	{
		return count($this->recipients);
	}

	/**
	 * @return int
	 */
	public function getNbPhoneNumbers(): int
	{
		return $this->getNbRecipients();
	}

	/**
	 * Ajoute un destinataire pour le SMS.
	 * @param string $mobileNumber numéro de téléphone du destinataire.
	 * @return self
	 */
	public function addRecipient(?string $mobileNumber): self
	{
		$this->recipients[] = $mobileNumber;

		return $this;
	}

	/**
	 * Définit un unique destinataire pour le SMS.
	 * @param string $mobileNumber le numéro de téléphone du destinataire.
	 * @return self
	 */
	public function setRecipient(?string $mobileNumber): self
	{
		$this->clearRecipients();
		$this->addRecipient($mobileNumber);

		return $this;
	}

	/**
	 * Ajoute une liste de destinataires pour le SMS.
	 * @param array $listRecipient une liste de numéro de téléphone de plusieurs destinataires.
	 * @return self
	 */
	public function addListRecipient(array $listRecipient): self
	{
		foreach ($listRecipient as $mobileNumber) {
			$this->addRecipient($mobileNumber);
		}

		return $this;
	}

	/**
	 * Définit une liste de destinataires pour le SMS (tous les anciens destinataires précédemment ajoutés seront supprimés).
	 * @param array $listRecipient une liste de numéro de téléphone de plusieurs destinataires.
	 * @return self
	 */
	public function setListRecipient(array $listRecipient): self
	{
		$this->clearRecipients();
		foreach ($listRecipient as $mobileNumber) {
			$this->addRecipient($mobileNumber);
		}

		return $this;
	}

	/**
	 * @param string|null $mobileNumber
	 * @return self
	 */
	public function addPhoneNumber(?string $mobileNumber): self
	{
		$this->addRecipient($mobileNumber);

		return $this;
	}

	/**
	 * @param string|null $mobileNumber
	 * @return self
	 */
	public function setPhoneNumber(?string $mobileNumber): self
	{
		$this->setRecipient($mobileNumber);

		return $this;
	}

	/**
	 * @param array $listRecipient
	 * @return self
	 */
	public function setListPhoneNumber(array $listRecipient): self
	{
		$this->setListRecipient($listRecipient);

		return $this;
	}

	/**
	 * @param array $listRecipient
	 * @return self
	 */
	public function addListPhoneNumber(array $listRecipient): self
	{
		$this->addListRecipient($listRecipient);

		return $this;
	}


	/**
	 * Réinitialise la liste des destinataires.
	 */
	public function clearRecipients(): void
	{
		$this->recipients = [];
	}

	/**
	 * @param string $separator
	 * @return string
	 */
	public function formatRecipients(string $separator=' ; '): string
	{
		return implode($separator, self::formatPhoneNumberList($this->recipients));
	}


	// ========== Message ==========

	/**
	 * Retourne le texte du SMS à envoyer.
	 * @return string le texte du SMS.
	 */
	public function getText(): ?string
	{
		return $this->text;
	}

	/**
	 * Affecte le texte du SMS à envoyer, à partir d'une chaine de caractère.
	 * Si le nombre de caractères du texte est supérieur au nombre de caractères maximum autorisé dans un SMS, le reste du texte est ignoré.
	 * @param string $text : texte du SMS.
	 * @return self
	 */
	public function setText($text): self
	{
		// $text = addslashes($text);
		if ($this->isTruncatedText() && strlen($text) > self::MESSAGE_NB_CHAR_MAX) {
			$this->text = substr($text, 0, self::MESSAGE_NB_CHAR_MAX);
		}
		else {
			$this->text = $text;
		}

		return $this;
	}

	/**
	 * Affecte le texte du SMS à envoyer, à partir d'un fichier texte.
	 * Cela ne fonctionne que si le fichier existe et si c'est un fichier texte avec du texte.
	 * @param string $filePath le chemin complet (chemin absolu) vers le fichier texte contenant le texte du SMS.
	 * @return self
	 */
	public function setTextFromFile(string $filePath): self
	{
		//$fp = fopen($file, 'r');
		//$text = '';
		//while (!feof($fp)) {
		//	$text .= fgets($fp, 2048);
		//}
		//fclose($fp);

		$text = file_get_contents($filePath);

		$this->setText($text);

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isAdultContent(): bool
	{
		return $this->adultContent;
	}

	/**
	 * @param bool $isAdultContent
	 * @return self
	 */
	public function setAdultContent(bool $isAdultContent=true): self
	{
		$this->adultContent = $isAdultContent;

		return $this;
	}

	/**
	 * @return bool
	 */
	public function isTruncatedText(): bool
	{
		return $this->truncateText;
	}

	/**
	 * @param bool $truncateText
	 * @return self
	 */
	public function setTruncateText(bool $truncateText=true): self
	{
		$this->truncateText = $truncateText;

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




	// ========== Format for display ==========

	/**
	 * @param array $phoneNumberList
	 * @return array
	 */
	private static function formatPhoneNumberList(array $phoneNumberList): array
	{
		$formattedList = [];
		foreach ($phoneNumberList as $phoneNumber) {
			if (!empty($phoneNumber)) {
				$formattedList[] = PhoneNumber::formatInternational($phoneNumber);
			}
		}
		return $formattedList;
	}

}