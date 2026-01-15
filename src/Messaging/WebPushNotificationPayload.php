<?php

namespace Osimatic\Messaging;

/**
 * Represents the payload data for a web push notification.
 * This class encapsulates all the configuration options and content for web push notifications sent to browsers.
 */
class WebPushNotificationPayload
{
	/**
	 * The date and time when the notification was created or should be displayed.
	 * @var \DateTime|null
	 */
	private ?\DateTime $dateTime = null;

	/**
	 * A unique identifier tag for the notification, allowing notification replacement or removal.
	 * @var string
	 */
	private string $tag;

	/**
	 * Whether to re-display notifications with the same tag.
	 * @var boolean
	 */
	private bool $reNotify = false;

	/**
	 * Whether the notification should remain active until user interaction on large screens.
	 * @var boolean
	 */
	private bool $requireInteraction = false;

	/**
	 * The URL to open when the user clicks on the notification.
	 * @var string
	 */
	private string $url;

	/**
	 * The URL of a larger image to display in the notification.
	 * @var string
	 */
	private string $image;

	/**
	 * The URL of a monochrome badge icon for the notification.
	 * @var string
	 */
	private string $badge;

	/**
	 * Array of action buttons to display in the notification.
	 * @var array
	 */
	private array $actions = [];

	/**
	 * Whether the notification should be silent (no sounds or vibrations).
	 * @var boolean
	 */
	private bool $silent = false;

	/**
	 * Vibration pattern array in milliseconds.
	 * @var array|null
	 */
	private ?array $vibrate;

	/**
	 * The URL of a sound file to play with the notification.
	 * @var string|null
	 */
	private ?string $sound;


	/**
	 * Set the URL to open when the user clicks on the notification.
	 * Note: This is custom data, not part of the standard JS showNotification() options.
	 * @param string $url The URL to open on notification click
	 */
	public function setURL(string $url) : void
	{
		$this->url = $url;
	}

	/**
	 * Get the URL to open when the user clicks on the notification.
	 * @return string The notification click URL
	 */
	public function getUrl(): string
	{
		return $this->url;
	}

	/**
	 * Set a unique tag identifier for the notification.
	 * The tag allows you to find, replace, or remove notifications using scripts.
	 * Multiple notifications with the same tag will only reappear if reNotify is set to true.
	 * @param string $tag The unique identifier tag for this notification
	 * @param bool $reNotify Whether to re-display notifications with the same tag (default: false)
	 */
	public function setTag(string $tag, bool $reNotify = false) : void
	{
		$this->tag = $tag;
		$this->reNotify = $reNotify;
	}

	/**
	 * Get the notification tag.
	 * @return string The notification tag identifier
	 */
	public function getTag(): string
	{
		return $this->tag;
	}

	/**
	 * Check if re-notification is enabled for this tag.
	 * @return bool True if notifications with the same tag should be re-displayed
	 */
	public function isReNotify(): bool
	{
		return $this->reNotify;
	}



	/**
	 * Set the URL of a larger image to display in the notification.
	 * Size, position, and cropping vary across different browsers and platforms.
	 * @param string $image The URL of the image to display
	 */
	public function setImage(string $image) : void
	{
		$this->image = $image;
	}

	/**
	 * Get the notification image URL.
	 * @return string The image URL
	 */
	public function getImage(): string
	{
		return $this->image;
	}

	/**
	 * Set the URL of a badge icon for the notification.
	 * The badge is a small monochrome icon that indicates the notification source.
	 * Browser support varies; some browsers display their own icon instead.
	 * @param string $badge The URL of the badge icon
	 */
	public function setBadge(string $badge) : void
	{
		$this->badge = $badge;
	}

	/**
	 * Get the notification badge icon URL.
	 * @return string The badge icon URL
	 */
	public function getBadge(): string
	{
		return $this->badge;
	}

	/**
	 * Add an action button to display in the notification.
	 * The number of actions that can be displayed varies by browser/platform (check Notification.maxActions).
	 * The notificationclick event handler should implement appropriate responses based on event.action.
	 * @param string $action The action identifier (used in the notificationclick event)
	 * @param string $title The action button text shown to the user
	 * @param string|null $icon The URL of an icon to display with the action
	 * @param string $customInfo Custom information (not part of standard showNotification() options)
	 */
	public function addAction(string $action, string $title, ?string $icon = null, string $customInfo = '') : void
	{
		$this->actions[] = [
			'action' => $action,
			'title' => $title,
			'icon' => $icon,
			'custom' => $customInfo
		];
	}

	/**
	 * Get all notification actions.
	 * @return array Array of action definitions
	 */
	public function getActions(): array
	{
		return $this->actions;
	}

	/**
	 * Set the date and time when the notification was created or should be displayed.
	 * This can indicate when a notification is relevant, such as for delayed messages or upcoming meetings.
	 * @param \DateTime $dateTime The notification timestamp
	 */
	public function setDateTime(\DateTime $dateTime) : void
	{
		$this->dateTime = $dateTime;
	}

	/**
	 * Get the notification date and time.
	 * @return \DateTime|null The notification timestamp, or null if not set
	 */
	public function getDateTime(): ?\DateTime
	{
		return $this->dateTime;
	}

	/**
	 * Set whether the notification requires user interaction to dismiss.
	 * On large screens, notifications will remain active until the user interacts with them.
	 * Implementation varies by browser and platform. Chrome desktop auto-minimizes after ~20 seconds by default.
	 * @param bool $requireInteraction Whether user interaction is required (default: true)
	 */
	public function requireInteraction(bool $requireInteraction = true) : void
	{
		$this->requireInteraction = $requireInteraction;
	}

	/**
	 * Check if user interaction is required to dismiss the notification.
	 * @return bool True if user interaction is required
	 */
	public function isRequireInteraction(): bool
	{
		return $this->requireInteraction;
	}

	/**
	 * Set whether the notification should be silent (no sounds or vibrations).
	 * Setting silent to true will reset any previously configured vibration pattern.
	 * @param bool $silent Whether the notification should be silent (default: true)
	 */
	public function setSilent(bool $silent = true) : void
	{
		$this->silent = $silent;
		$this->vibrate = null;
	}

	/**
	 * Check if the notification is silent.
	 * @return bool True if silent mode is enabled
	 */
	public function isSilent(): bool
	{
		return $this->silent;
	}

	/**
	 * Set a vibration pattern for the notification.
	 * The pattern is an array of millisecond values where even indices represent vibration duration and odd indices represent pause duration.
	 * Example: [300, 100, 400] vibrates 300ms, pauses 100ms, then vibrates 400ms.
	 * @param array $pattern The vibration pattern array
	 */
	public function setVibration(array $pattern) : void
	{
		$this->silent = false;
		$this->vibrate = $pattern;
	}

	/**
	 * Get the vibration pattern.
	 * @return array|null The vibration pattern array, or null if not set
	 */
	public function getVibrate(): ?array
	{
		return $this->vibrate;
	}



	/**
	 * Set the URL of a sound file to play with the notification.
	 * Supports MP3 or WAV formats. Note: Browser support for notification sounds is currently limited.
	 * @param string $sound The URL of the sound file
	 */
	public function setSound(string $sound) : void
	{
		$this->sound = $sound;
	}

	/**
	 * Get the notification sound URL.
	 * @return string|null The sound file URL, or null if not set
	 */
	public function getSound(): ?string
	{
		return $this->sound;
	}

}