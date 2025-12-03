<?php

namespace Osimatic\Security;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

readonly class GoogleReCaptchaValidator
{
	public function __construct(
		private ?string $siteKey=null,
		private ?string $secret=null,
		private LoggerInterface $logger=new NullLogger(),
	) {}

	public function __invoke(?string $recaptchaResponse): bool
	{
		$googleReCaptcha = new GoogleReCaptcha($this->siteKey, $this->secret, $this->logger);
		return $googleReCaptcha->check($recaptchaResponse);
	}
}