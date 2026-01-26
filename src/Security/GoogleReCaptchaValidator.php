<?php

namespace Osimatic\Security;

use Osimatic\Network\HTTPClient;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Readonly validator class for Google reCAPTCHA v2 verification that provides a simplified interface for validating reCAPTCHA responses.
 */
readonly class GoogleReCaptchaValidator
{
	/**
	 * Initializes the Google reCAPTCHA validator with configuration parameters.
	 * @param string|null $siteKey The Google reCAPTCHA site key (public key) used to render the reCAPTCHA widget
	 * @param string|null $secret The Google reCAPTCHA secret key (private key) used to verify responses with Google's API
	 * @param LoggerInterface $logger The PSR-3 logger instance for error and debugging information (default: NullLogger)
	 * @param ClientInterface $httpClient The PSR-18 HTTP client instance used for making API requests (default: HTTPClient)
	 */
	public function __construct(
		private ?string $siteKey=null,
		private ?string $secret=null,
		private LoggerInterface $logger=new NullLogger(),
		private ClientInterface $httpClient=new HTTPClient(),
	) {}

	/**
	 * Validates a Google reCAPTCHA response by verifying it with Google's API. This method creates a GoogleReCaptcha instance and delegates the verification to it.
	 * @param string|null $recaptchaResponse The reCAPTCHA response token received from the client-side reCAPTCHA widget
	 * @return bool Returns true if the reCAPTCHA response is valid, false otherwise
	 */
	public function __invoke(?string $recaptchaResponse): bool
	{
		$googleReCaptcha = new GoogleReCaptcha($this->siteKey, $this->secret, $this->logger, $this->httpClient);
		return $googleReCaptcha->check($recaptchaResponse);
	}
}