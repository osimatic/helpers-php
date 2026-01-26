<?php

namespace Osimatic\Security;

use Osimatic\Network\HTTPClient;
use Osimatic\Network\HTTPMethod;
use Osimatic\Network\HTTPRequestExecutor;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Google reCAPTCHA v2 integration class that handles verification of reCAPTCHA responses with Google's API, provides methods to generate HTML form fields and JavaScript includes for reCAPTCHA widgets.
 */
class GoogleReCaptcha
{
	private const string VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';
	private const string API_JS_URL = 'https://www.google.com/recaptcha/api.js';
	private const string DEFAULT_LOCALE = 'en';

	/** HTTP request executor for making API calls */
	private HTTPRequestExecutor $requestExecutor;

	/**
	 * Initializes the Google reCAPTCHA handler with configuration parameters and sets up the HTTP client for API communication.
	 * @param string|null $siteKey The Google reCAPTCHA site key (public key) used to render the reCAPTCHA widget on the client side
	 * @param string|null $secret The Google reCAPTCHA secret key (private key) used to verify responses with Google's API server
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging information (default: NullLogger)
	 * @param ClientInterface $httpClient The PSR-18 HTTP client instance used for making API requests (default: HTTPClient)
	 */
	public function __construct(
		private ?string $siteKey=null,
		private ?string $secret=null,
		private readonly LoggerInterface $logger=new NullLogger(),
		ClientInterface $httpClient = new HTTPClient(),
	)
	{
		$this->requestExecutor = new HTTPRequestExecutor($httpClient, $logger);
	}


	/**
	 * Sets the Google reCAPTCHA site key (public key) used to render the reCAPTCHA widget.
	 * @param string $siteKey The site key obtained from Google reCAPTCHA admin console
	 * @return self Returns this instance for method chaining
	 */
	public function setSiteKey(string $siteKey): self
	{
		$this->siteKey = $siteKey;

		return $this;
	}

	/**
	 * Sets the Google reCAPTCHA secret key (private key) used to verify responses with Google's API.
	 * @param string $secret The secret key obtained from Google reCAPTCHA admin console
	 * @return self Returns this instance for method chaining
	 */
	public function setSecret(string $secret): self
	{
		$this->secret = $secret;

		return $this;
	}

	/**
	 * Verifies a reCAPTCHA response token by sending it to Google's verification API. The method makes an HTTP request to Google's siteverify endpoint with the secret key and response token, then returns whether the verification was successful.
	 * @param string|null $recaptchaResponse The reCAPTCHA response token received from the client-side reCAPTCHA widget after user interaction
	 * @return bool Returns true if the reCAPTCHA response is valid and verified by Google, false otherwise (including on empty response or API errors)
	 */
	public function check(?string $recaptchaResponse): bool
	{
		if (empty($this->secret)) {
			$this->logger->error('Google reCAPTCHA secret key (private key) is not configured. Please set secret key.');
			return false;
		}

		if (empty($recaptchaResponse)) {
			return false;
		}

		$queryData = [
			'secret' => $this->secret,
			'response' => $recaptchaResponse,
			//'remoteip' => ($_SERVER['REMOTE_ADDR'] ?? null),
		];

		if (null === ($json = $this->requestExecutor->execute(HTTPMethod::GET, self::VERIFY_URL, $queryData, decodeJson: true))) {
			return false;
		}

		return isset($json['success']) && $json['success'] === true;
	}

	/**
	 * Generates the HTML div element required to display the Google reCAPTCHA widget in a web form. This method returns a div with the class "g-recaptcha" and the data-sitekey attribute set to the configured site key.
	 * @return string The HTML markup for the reCAPTCHA widget container
	 */
	public function getFormField(): string
	{
		return '<div class="g-recaptcha" data-sitekey="'.$this->siteKey.'"></div>';
	}

	/**
	 * Generates the URL to the Google reCAPTCHA JavaScript API with the specified language locale. This URL should be included in the HTML page to load the reCAPTCHA widget functionality.
	 * @param string $locale The language code for the reCAPTCHA widget interface (e.g., 'en' for English, 'fr' for French, 'es' for Spanish) (default: 'en')
	 * @return string The complete URL to the Google reCAPTCHA JavaScript API with the specified language parameter
	 */
	public function getJavaScriptUrl(string $locale=self::DEFAULT_LOCALE): string
	{
		return self::API_JS_URL.'?hl='.$locale;
	}
}