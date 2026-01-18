<?php

namespace Osimatic\Security;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class GoogleReCaptcha
 */
class GoogleReCaptcha
{
	private HTTPClient $httpClient;

	/**
	 * @param string|null $siteKey
	 * @param string|null $secret
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging (default: NullLogger)
	 */
	public function __construct(
		private ?string $siteKey=null,
		private ?string $secret=null,
		LoggerInterface $logger=new NullLogger(),
	) {
		$this->httpClient = new HTTPClient($logger);
	}

	/**
	 * Sets the logger for error and debugging information.
	 * @param LoggerInterface $logger The PSR-3 logger instance
	 * @return self Returns this instance for method chaining
	 */
	public function setLogger(LoggerInterface $logger): self
	{
		$this->httpClient->setLogger($logger);

		return $this;
	}

	/**
	 * @param string $siteKey
	 * @return self Returns this instance for method chaining
	 */
	public function setSiteKey(string $siteKey): self
	{
		$this->siteKey = $siteKey;

		return $this;
	}

	/**
	 * @param string $secret
	 * @return self Returns this instance for method chaining
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

		$url = 'https://www.google.com/recaptcha/api/siteverify';
		$queryData = [
			'secret' => $this->secret,
			'response' => $recaptchaResponse,
			//'remoteip' => ($_SERVER['REMOTE_ADDR'] ?? null),
		];

		if (null === ($json = $this->httpClient->jsonRequest(HTTPMethod::GET, $url, queryData: $queryData))) {
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