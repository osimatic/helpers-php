<?php

namespace Osimatic\Helpers\Messaging;

/**
 * Class WebPushNotification
 * Represent payload of a web push notification
 * @package Osimatic\Helpers\Messaging
 */
class WebPushNotification
{
	/**
	 * @var \DateTime|null
	 */
	private $dateTime = null;

	/**
	 * @var string
	 */
	private $tag;

	/**
	 * @var boolean
	 */
	private $reNotify = false;

	/**
	 * @var boolean
	 */
	private $requireInteraction = false;

	/**
	 * @var string
	 */
	private $url;

	/**
	 * @var string
	 */
	private $image;

	/**
	 * @var string
	 */
	private $badge;

	/**
	 * @var array
	 */
	private $actions = [];

	/**
	 * @var boolean
	 */
	private $silent = false;

	/**
	 * @var array|null
	 */
	private $vibrate;

	/**
	 * @var string|null
	 */
	private $sound;

	public function getPayload() : string
	{
		return json_encode($this->getPayloadData());
	}

	public function getPayloadData() : array
	{

		$opt = [
			'renotify' => $this->reNotify,
			'requireInteraction' => $this->requireInteraction,
			'silent' => $this->silent,
			'data' => []
		];

		if (!empty($this->tag)) {
			$opt['tag'] = $this->tag;
		}
		if (!empty($this->badge)) {
			$opt['image'] = $this->image;
		}
		if (!empty($this->badge)) {
			$opt['badge'] = $this->badge;
		}

		if (!empty($this->dateTime)) {
			$opt['timestamp'] = $this->dateTime->getTimestamp() * 1000;
		}

		if (!empty($this->url)) {
			$opt['data']['url'] = $this->url;
		}

		if (!empty($this->vibrate)) {
			$opt['vibrate'] = $this->vibrate;
		}
		if (!empty($this->sound)) {
			$opt['sound'] = $this->sound;
		}

		if (!empty($this->actions)) {
			$opt['actions'] = [];
			foreach ($this->actions as $action) {
				$opt['actions'][] = [
					'action' => $action['action'],
					'title' => $action['title'],
					'icon' => $action['icon'],
					'custom' => $action['custom_info']
				];
			}
		}

		return [
			'opt' => $opt
		];
	}


	/**
	 * Note: the URL is no part of the JS showNotification() - Options!
	 * @param string $url URL to open when user click on the notification.
	 */
	public function setURL(string $url) : void
	{
		$this->url = $url;
	}

	/**
	 * An ID for a given notification that allows you to find, replace, or remove the notification using
	 * a script if necessary.
	 * If set, multiple notifications with the same tag will only reappear if $bReNotify is set to true.
	 * Usualy the last notification with same tag is displayed in this case.
	 *
	 * @param string $tag
	 * @param bool $reNotify
	 */
	public function setTag(string $tag, bool $reNotify = false) : void
	{
		$this->tag = $tag;
		$this->reNotify = $reNotify;
	}

	/**
	 * containing the URL of an larger image to be displayed in the notification.
	 * Size, position and cropping vary with the different browsers and platforms
	 * @param string $image
	 */
	public function setImage(string $image) : void
	{
		$this->image = $image;
	}

	/**
	 * containing the URL of an badge assigend to the notification.
	 * The badge is a small monochrome icon that is used to portray a little
	 * more information to the user about where the notification is from.
	 * So far I have only found Chrome for Android that supports the badge...
	 * ... in most cases the browsers icon is displayed.
	 *
	 * @param string $badge
	 */
	public function setBadge(string $badge) : void
	{
		$this->badge = $badge;
	}

	/**
	 * Add action to display in the notification.
	 *
	 * The count of action that can be displayed vary between browser/platform. On
	 * the client it can be detected with javascript: Notification.maxActions
	 *
	 * Appropriate responses have to be implemented within the notificationclick event.
	 * the event.action property contains the $strAction clicked on
	 *
	 * @param string $action     identifying a user action to be displayed on the notification.
	 * @param string $title      containing action text to be shown to the user.
	 * @param string $icon       containing the URL of an icon to display with the action.
	 * @param string $customInfo     custom info - not part of the showNotification()- Options!
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
	 * Set the time when the notification was created.
	 * It can be used to indicate the time at which a notification is actual. For example, this could
	 * be in the past when a notification is used for a message that couldn’t immediately be delivered
	 * because the device was offline, or in the future for a meeting that is about to start.
	 *
	 * @param \DateTime $dateTime
	 */
	public function setDateTime(\DateTime $dateTime) : void
	{
		$this->dateTime = $dateTime;
	}

	/**
	 * Indicates that on devices with sufficiently large screens, a notification should remain active until
	 * the user clicks or dismisses it. If this value is absent or false, the desktop version of Chrome
	 * will auto-minimize notifications after approximately twenty seconds. Implementation depends on
	 * browser and plattform.
	 *
	 * @param bool $requireInteraction
	 */
	public function requireInteraction(bool $requireInteraction = true) : void
	{
		$this->requireInteraction = $requireInteraction;
	}

	/**
	 * Indicates that no sounds or vibrations should be made.
	 * If this 'mute' function is activated, a previously set vibration is reset to prevent a TypeError exception.
	 * @param bool $silent
	 */
	public function setSilent(bool $silent = true) : void
	{
		$this->silent = $silent;
		$this->vibrate = null;
	}

	/**
	 * A vibration pattern to run with the display of the notification.
	 * A vibration pattern can be an array with as few as one member. The values are times in milliseconds
	 * where the even indices (0, 2, 4, etc.) indicate how long to vibrate and the odd indices indicate
	 * how long to pause. For example, [300, 100, 400] would vibrate 300ms, pause 100ms, then vibrate 400ms.
	 *
	 * @param array $pattern
	 */
	public function setVibration(array $pattern) : void
	{
		$this->silent = false;
		$this->vibrate = $pattern;
	}

	/**
	 * containing the URL of an sound - file (mp3 or wav).
	 * currently not found any browser supports sounds
	 * @param string $sound
	 */
	public function setSound(string $sound) : void
	{
		$this->sound = $sound;
	}

}