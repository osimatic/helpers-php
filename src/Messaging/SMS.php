<?php

namespace Osimatic\Messaging;

/**
 * Represents an SMS message with recipients, text content, and sending options.
 * This class provides a complete API for building and managing SMS messages before sending them through various SMS gateway providers.
 */
class SMS
{
	/**
	 * Maximum number of characters allowed in a single SMS message.
	 */
	public const int MESSAGE_NB_CHAR_MAX = 160;

	/**
	 * The identifier property represents any kind of identifier.
	 * @var string|null
	 */
	private ?string $identifier = null;

	/**
	 * Array containing the list of recipient phone numbers for the SMS.
	 * @var array
	 */
	private array $recipients = [];

	/**
	 * The text message content of the SMS.
	 * @var string
	 */
	private string $text = '';

	/**
	 * Whether to truncate the text to fit in a single SMS message.
	 * True to cut the text to fit within MESSAGE_NB_CHAR_MAX characters, false to keep the full text even if it exceeds one SMS length.
	 * @var boolean
	 */
	private bool $truncateText = false;

	/**
	 * The sender name displayed to SMS recipients.
	 * @var string|null
	 */
	private ?string $senderName = null;

	/**
	 * The date and time when the SMS should be sent.
	 * Allows for scheduled/deferred sending (null = immediate sending).
	 * @var \DateTime|null
	 */
	private ?\DateTime $sendingDateTime = null;

	/**
	 * Whether the SMS contains adult content.
	 * @var boolean
	 */
	private bool $adultContent = false;


	/**
	 * Construct a new SMS instance.
	 * Initializes the sending date time to the current date and time.
	 */
	public function __construct()
	{
		$this->sendingDateTime = \Osimatic\Calendar\DateTime::getCurrentDateTime();
	}

	/**
	 * Get the SMS identifier.
	 * @return string|null The identifier, or null if not set
	 */
	public function getIdentifier(): ?string
	{
		return $this->identifier;
	}

	/**
	 * Set the SMS identifier.
	 * @param string|null $identifier The identifier to set
	 */
	public function setIdentifier(?string $identifier): void
	{
		$this->identifier = $identifier;
	}


	// ========== Sender ==========

	/**
	 * Get the sender name.
	 * @return string|null The sender name, or null if not set
	 */
	public function getSenderName(): ?string
	{
		return $this->senderName;
	}

	/**
	 * Set the sender name.
	 * @param string|null $senderName The sender name to display to recipients
	 * @return self Returns this instance for method chaining
	 */
	public function setSenderName(?string $senderName): self
	{
		$this->senderName = $senderName;

		return $this;
	}

	// ========== Recipients ==========

	/**
	 * Get the list of all SMS recipients.
	 * @return array Array containing the phone numbers of all recipients
	 */
	public function getListRecipients(): array
	{
		return $this->recipients;
	}

	/**
	 * Get the first SMS recipient.
	 * @return string|null The phone number of the first recipient, or null if no recipients
	 */
	public function getRecipient(): ?string
	{
		return $this->recipients[0] ?? null;
	}

	/**
	 * Get the list of all recipient phone numbers (alias for getListRecipients).
	 * @return array Array containing the phone numbers of all recipients
	 */
	public function getListPhoneNumbers(): array
	{
		return $this->getListRecipients();
	}

	/**
	 * Get the first recipient phone number (alias for getRecipient).
	 * @return string|null The phone number of the first recipient, or null if no recipients
	 */
	public function getPhoneNumber(): ?string
	{
		return $this->getRecipient();
	}

	/**
	 * Get the number of recipients.
	 * @return int The total count of recipients
	 */
	public function getNbRecipients(): int
	{
		return count($this->recipients);
	}

	/**
	 * Get the number of phone numbers (alias for getNbRecipients).
	 * @return int The total count of phone numbers
	 */
	public function getNbPhoneNumbers(): int
	{
		return $this->getNbRecipients();
	}

	/**
	 * Add a recipient for the SMS.
	 * @param string|null $mobileNumber The recipient's phone number
	 * @return self Returns this instance for method chaining
	 */
	public function addRecipient(?string $mobileNumber): self
	{
		$this->addRecipientPhoneNumber($mobileNumber);

		return $this;
	}

	/**
	 * Set a single recipient for the SMS (replaces any existing recipients).
	 * @param string|null $mobileNumber The recipient's phone number
	 * @return self Returns this instance for method chaining
	 */
	public function setRecipient(?string $mobileNumber): self
	{
		$this->clearRecipients();
		$this->addRecipientPhoneNumber($mobileNumber);

		return $this;
	}

	/**
	 * Add multiple recipients for the SMS.
	 * @param array $listRecipient Array of phone numbers for multiple recipients
	 * @return self Returns this instance for method chaining
	 */
	public function addListRecipient(array $listRecipient): self
	{
		foreach ($listRecipient as $mobileNumber) {
			$this->addRecipientPhoneNumber($mobileNumber);
		}

		return $this;
	}

	/**
	 * Set multiple recipients for the SMS (replaces any existing recipients).
	 * @param array $listRecipient Array of phone numbers for multiple recipients
	 * @return self Returns this instance for method chaining
	 */
	public function setListRecipient(array $listRecipient): self
	{
		$this->clearRecipients();
		$this->addListRecipient($listRecipient);

		return $this;
	}

	/**
	 * Add a phone number (alias for addRecipient).
	 * @param string|null $mobileNumber The recipient's phone number
	 * @return self Returns this instance for method chaining
	 */
	public function addPhoneNumber(?string $mobileNumber): self
	{
		$this->addRecipientPhoneNumber($mobileNumber);

		return $this;
	}

	/**
	 * Set a single phone number (alias for setRecipient).
	 * @param string|null $mobileNumber The recipient's phone number
	 * @return self Returns this instance for method chaining
	 */
	public function setPhoneNumber(?string $mobileNumber): self
	{
		$this->setRecipient($mobileNumber);

		return $this;
	}

	/**
	 * Set multiple phone numbers (alias for setListRecipient).
	 * @param array $listRecipient Array of phone numbers
	 * @return self Returns this instance for method chaining
	 */
	public function setListPhoneNumber(array $listRecipient): self
	{
		$this->setListRecipient($listRecipient);

		return $this;
	}

	/**
	 * Add multiple phone numbers (alias for addListRecipient).
	 * @param array $listRecipient Array of phone numbers
	 * @return self Returns this instance for method chaining
	 */
	public function addListPhoneNumber(array $listRecipient): self
	{
		$this->addListRecipient($listRecipient);

		return $this;
	}


	/**
	 * Clear the list of all recipients.
	 */
	public function clearRecipients(): void
	{
		$this->recipients = [];
	}

	/**
	 * Format all recipients as a string with a specified separator.
	 * @param string $separator The separator to use between phone numbers (default: ' ; ')
	 * @return string The formatted recipients string
	 */
	public function formatRecipients(string $separator=' ; '): string
	{
		return implode($separator, self::formatPhoneNumberList($this->recipients));
	}


	// ========== Message ==========

	/**
	 * Get the SMS text message to send.
	 * @return string The SMS text content
	 */
	public function getText(): string
	{
		return $this->text ?? '';
	}

	/**
	 * Set the SMS text message to send from a string.
	 * If the text length exceeds MESSAGE_NB_CHAR_MAX and truncation is enabled, the text will be truncated.
	 * @param string $text The SMS text content
	 * @return self Returns this instance for method chaining
	 */
	public function setText(string $text): self
	{
		$text = str_replace("'", "'", $text);

		// $text = addslashes($text);
		if ($this->isTruncatedText() && mb_strlen($text) > self::MESSAGE_NB_CHAR_MAX) {
			$this->text = mb_substr($text, 0, self::MESSAGE_NB_CHAR_MAX);
		}
		else {
			$this->text = $text;
		}

		return $this;
	}

	/**
	 * Set the SMS text message from a text file.
	 * This only works if the file exists and contains text.
	 * @param string $filePath The absolute path to the text file containing the SMS text
	 * @return self Returns this instance for method chaining
	 */
	public function setTextFromFile(string $filePath): self
	{
		if (!is_file($filePath)) {
			return $this;
		}

		if (false === ($text = file_get_contents($filePath))) {
			return $this;
		}

		$this->setText($text);

		return $this;
	}

	/**
	 * Check if the SMS contains adult content.
	 * @return bool True if contains adult content, false otherwise
	 */
	public function isAdultContent(): bool
	{
		return $this->adultContent;
	}

	/**
	 * Set whether the SMS contains adult content.
	 * @param bool $isAdultContent True if adult content, false otherwise
	 * @return self Returns this instance for method chaining
	 */
	public function setAdultContent(bool $isAdultContent=true): self
	{
		$this->adultContent = $isAdultContent;

		return $this;
	}

	/**
	 * Check if text truncation is enabled.
	 * @return bool True if text will be truncated to fit in one SMS, false otherwise
	 */
	public function isTruncatedText(): bool
	{
		return $this->truncateText;
	}

	/**
	 * Set whether to truncate the text to fit in a single SMS message.
	 * @param bool $truncateText True to enable truncation, false to allow multi-part SMS
	 * @return self Returns this instance for method chaining
	 */
	public function setTruncateText(bool $truncateText=true): self
	{
		$this->truncateText = $truncateText;

		return $this;
	}

	// ========== Sending options ==========

	/**
	 * Get the sending date and time of the SMS.
	 * @return \DateTime|null The scheduled sending date and time, or null for immediate sending
	 */
	public function getSendingDateTime(): ?\DateTime
	{
		return $this->sendingDateTime;
	}

	/**
	 * Set the sending date and time of the SMS.
	 * @param \DateTime|null $sendingDateTime The scheduled sending date and time, or null for immediate sending
	 * @return self Returns this instance for method chaining
	 */
	public function setSendingDateTime(?\DateTime $sendingDateTime): self
	{
		$this->sendingDateTime = $sendingDateTime;

		return $this;
	}




	// ========== Format for display ==========

	/**
	 * Format an array of phone numbers for display in international format.
	 * @param array $phoneNumberList Array of phone numbers to format
	 * @return array Array of formatted phone numbers
	 */
	public static function formatPhoneNumberList(array $phoneNumberList): array
	{
		$formattedList = [];
		foreach ($phoneNumberList as $phoneNumber) {
			if (!empty($phoneNumber)) {
				$formattedList[] = PhoneNumber::formatInternational($phoneNumber);
			}
		}
		return $formattedList;
	}



	// ========== Private methods ==========

	/**
	 * Add a recipient phone number to the recipients list.
	 * The phone number is validated and parsed before being added, and duplicates are prevented.
	 * @param string|null $mobileNumber The recipient's phone number
	 * @return bool True if the phone number was added successfully, false if invalid or already exists
	 */
	private function addRecipientPhoneNumber(?string $mobileNumber): bool
	{
		if (empty($mobileNumber)) {
			return false;
		}

		$mobileNumber = PhoneNumber::parse(trim($mobileNumber));
		if (!PhoneNumber::isValid($mobileNumber)) {
			return false;
		}

		if (!in_array($mobileNumber, $this->recipients, true)) {
			$this->recipients[] = $mobileNumber;
			return true;
		}

		return false;
	}

}