<?php

namespace Osimatic\Helpers\API;

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
	 */
	public function setSiteKey(string $siteKey): void
	{
		$this->siteKey = $siteKey;
	}

	/**
	 * @param string $secret
	 */
	public function setSecret(string $secret): void
	{
		$this->secret = $secret;
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

		// '&remoteip='.($_SERVER['REMOTE_ADDR'] ?? null);
		$response = file_get_contents('https://www.google.com/recaptcha/api/siteverify?secret='.$this->secret.'&response='.$recaptchaResponse);
		$response = json_decode($response, true);

		return null !== $response && isset($response['success']) && $response['success'] == true;
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