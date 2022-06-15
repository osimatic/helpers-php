<?php

namespace Osimatic\Helpers\API;

use Osimatic\Helpers\Network\HTTPRequest;

/**
 * Class GoogleReCaptcha
 * @package Osimatic\Helpers\API
 */
class GoogleReCaptcha
{
	private $siteKey;
	private $secret;

	/**
	 * GoogleReCaptcha constructor.
	 * @param string|null $siteKey
	 * @param string|null $secret
	 */
	public function __construct(?string $siteKey=null, ?string $secret=null)
	{
		$this->siteKey = $siteKey;
		$this->secret = $secret;
	}

	/**
	 * @param string $siteKey
	 * @return self
	 */
	public function setSiteKey(string $siteKey): self
	{
		$this->siteKey = $siteKey;

		return $this;
	}

	/**
	 * @param string $secret
	 * @return self
	 */
	public function setSecret(string $secret): self
	{
		$this->secret = $secret;

		return $this;
	}

	/**
	 * @param string|null $recaptchaResponse
	 * @return bool
	 */
	public function check(?string $recaptchaResponse): bool
	{
		if (empty($recaptchaResponse)) {
			return false;
		}

		$url = 'https://www.google.com/recaptcha/api/siteverify?secret='.$this->secret.'&response='.$recaptchaResponse;
		// '&remoteip='.($_SERVER['REMOTE_ADDR'] ?? null);
		if (null === ($json = HTTPRequest::getAndDecodeJson($url))) {
			return false;
		}

		return isset($json['success']) && $json['success'] == true;
	}

	/**
	 * @return string
	 */
	public function getFormField(): string
	{
		return '<div class="g-recaptcha" data-sitekey="'.$this->siteKey.'"></div>';
	}

	/**
	 * @param string $locale
	 * @return string
	 */
	public function getJavaScriptUrl(string $locale='en'): string
	{
		return 'https://www.google.com/recaptcha/api.js?hl='.$locale;
	}

}