<?php

namespace Osimatic\Network;

use DeviceDetector\DeviceDetector;

/**
 * UserAgent Parser
 *
 * Parses and analyzes HTTP User-Agent strings to extract detailed information about:
 * - Browser (name, version)
 * - Operating System (name, version)
 * - Device (type, manufacturer, model)
 *
 * This class uses the Matomo DeviceDetector library for robust and maintained
 * user agent parsing capabilities.
 *
 * @example
 * ```php
 * $ua = UserAgent::parse($_SERVER['HTTP_USER_AGENT']);
 * echo $ua->browserName;  // "Chrome"
 * echo $ua->osName;       // "Windows"
 * echo $ua->isMobile() ? 'Mobile' : 'Desktop';
 * ```
 *
 * @see https://github.com/matomo-org/device-detector
 */
readonly class UserAgent implements \JsonSerializable
{
	/** @var DeviceDetector DeviceDetector parser instance */
	public DeviceDetector $parser;

	/** @var string|null Browser name (e.g., "Chrome", "Firefox", "Safari") */
	public ?string $browserName;

	/** @var string|null Browser version (e.g., "120.0.0.0") */
	public ?string $browserVersion;

	/** @var string|null Operating system name (e.g., "Windows", "iOS", "Android") */
	public ?string $osName;

	/** @var string|null Operating system version (e.g., "10", "17.0") */
	public ?string $osVersion;

	/** @var DeviceType|null Device type classification */
	public ?DeviceType $deviceType;

	/** @var string|null Device manufacturer/brand (e.g., "Apple", "Samsung", "Google") */
	public ?string $deviceManufacturer;

	/** @var string|null Device model (e.g., "iPhone", "Pixel 7", "Galaxy S23") */
	public ?string $deviceModel;

	/**
	 * Creates a new UserAgent parser instance
	 * @param string $userAgent the User-Agent string to parse
	 */
	public function __construct(string $userAgent)
	{
		$this->parser = new DeviceDetector($userAgent);
		$this->parser->parse();

		// Extract browser information
		$client = $this->parser->getClient();
		$this->browserName = $client['name'] ?? null;
		$this->browserVersion = $client['version'] ?? null;

		// Extract OS information
		$os = $this->parser->getOs();
		$osName = $os['name'] ?? null;
		if ($osName === 'Mac') { // Map OS names for backward compatibility
			$osName = 'macOS';
		}
		$this->osName = $osName;


		$this->osVersion = $os['version'] ?? null;

		// Extract device information
		$deviceName = $this->parser->getDeviceName();
		$this->deviceType = !empty($deviceName) ? DeviceType::tryFrom($deviceName) : null;
		$this->deviceManufacturer = $this->parser->getBrandName() ?: null;
		$this->deviceModel = $this->parser->getModel() ?: null;
	}

	/**
	 * Parses a User-Agent string (static factory method)
	 * @param string $userAgent the User-Agent string to parse
	 * @return UserAgent the parsed UserAgent object
	 */
	public static function parse(string $userAgent): UserAgent
	{
		return new UserAgent($userAgent);
	}

	/**
	 * Checks if the device is a mobile device (smartphone or tablet)
	 *
	 * @return bool true if smartphone or tablet, false otherwise
	 */
	public function isMobile(): bool
	{
		return $this->parser->isSmartphone() || $this->parser->isTablet();
	}

	/**
	 * Checks if the device is a smartphone
	 *
	 * @return bool true if smartphone, false otherwise
	 */
	public function isSmartphone(): bool
	{
		return $this->parser->isSmartphone();
	}

	/**
	 * Checks if the device is a tablet
	 *
	 * @return bool true if tablet, false otherwise
	 */
	public function isTablet(): bool
	{
		return $this->parser->isTablet();
	}

	/**
	 * Adds the appropriate article (a/an) before a word
	 *
	 * @param string $word the word to prefix with an article
	 * @return string the word prefixed with "a" or "an"
	 */
	private function withArticle(string $word): string
	{
		return (preg_match('/^[aeiou]/i', $word) ? 'an ' : 'a ') . $word;
	}

	/**
	 * Returns a formatted string representation of the user agent information
	 * @param string $separator separator between components (default: ' — ')
	 * @return string formatted string with OS, browser, and device information
	 */
	public function getInfosDisplay(string $separator=' — '): string
	{
		$userAgentData = $this->getData();

		$components = [];
		if (null !== $userAgentData['os']) {
			$components[] = $userAgentData['os'];
		}
		if (null !== $userAgentData['browser']) {
			$components[] = $userAgentData['browser'];
		}
		if (null !== $userAgentData['device']) {
			$components[] = $userAgentData['device'];
		}
		return implode($separator, $components);
	}

	/**
	 * Returns user agent data as an associative array
	 * @return array array with 'os', 'browser', and 'device' keys containing formatted strings
	 */
	public function getData(): array
	{
		$os = null;
		if (null !== $this->osName) {
			$os = $this->osName;
			if (null !== $this->osVersion) {
				$os .= ' ' . $this->osVersion;
			}

			$os = trim($os);
		}

		$browser = null;
		if (isset($this->browserName)) {
			$browser = $this->browserName;

			if (isset($this->browserVersion)) {
				$browser .= ' ' . $this->browserVersion;
			}

			$browser = trim($browser);
		}

		$device = null;
		if (isset($this->deviceType)) {
			$device = $this->deviceType->getLabel();

			if (isset($this->deviceManufacturer)) {
				$device .= ' ' . $this->deviceManufacturer;
			}

			if (isset($this->deviceModel)) {
				$device .= ' ' . $this->deviceModel;
			}

			$device = trim($device);
		}

		return [
			'os' => !empty($os) ? $os : null,
			'browser' => !empty($browser) ? $browser : null,
			'device' => !empty($device) ? $device : null,
		];
	}

	/**
	 * Builds a human-readable string representation of the user agent
	 * Based on WhichBrowser's toString() logic
	 * @return string formatted string describing the user agent
	 */
	private function getReadableRepresentation(): string
	{
		$browser = $this->browserName;
		if ($this->browserVersion) {
			$browser .= ' ' . $this->browserVersion;
		}

		$os = $this->osName;
		if ($this->osVersion) {
			$os .= ' ' . $this->osVersion;
		}

		$device = null;
		if ($this->deviceModel) {
			$device = $this->deviceModel;
		} elseif ($this->deviceManufacturer) {
			$device = $this->deviceManufacturer;
		} elseif ($this->deviceType) {
			$device = $this->deviceType->value;
		}

		// Build readable string based on available information
		if ($browser && $os && $device) {
			return $browser . ' on ' . $this->withArticle($device) . ' running ' . $os;
		}

		if ($browser && !$os && $device) {
			return $browser . ' on ' . $this->withArticle($device);
		}

		if ($browser && $os && !$device) {
			return $browser . ' on ' . $os;
		}

		if (!$browser && $os && $device) {
			return $this->withArticle($device) . ' running ' . $os;
		}

		if ($browser && !$os && !$device) {
			return $browser;
		}

		if (!$browser && !$os && $device) {
			return $this->withArticle($device);
		}

		if (!$browser && $os && !$device) {
			return $os;
		}

		return 'unknown';
	}

	public function format(): string
	{
		return $this->getReadableRepresentation();
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(): array
	{
		return [
			'user_agent_desc' => $this->getReadableRepresentation(),
			'browser_name' => $this->browserName,
			'os_name' => $this->osName,
			'device_type' => $this->deviceType,
			'device_is_mobile' => $this->isMobile(),
			'device_manufacturer' => $this->deviceManufacturer,
			'device_model' => $this->deviceModel,
		];
	}

	public function __toString(): string
	{
		return $this->getReadableRepresentation();
	}
}