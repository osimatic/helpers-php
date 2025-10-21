<?php

namespace Osimatic\Network;

class UserAgent implements \JsonSerializable
{
	public object $parser;

	public string $readableRepresentation;

	public ?string $browserName;
	public ?string $browserVersion;

	public ?string $osName;
	public ?string $osVersion;

	public bool $deviceIsMobile;
	public ?DeviceType $deviceType;
	public ?string $deviceManufacturer;
	public ?string $deviceModel;

	public function __construct(string $userAgent)
	{
		$result = new \WhichBrowser\Parser($userAgent);
		$this->parser = $result;

		$this->readableRepresentation = $result->toString();

		$this->browserName = $result->browser->getName();
		$this->browserName = !empty($this->browserName) ? $this->browserName : null;
		$this->browserVersion = $result->browser->getVersion();
		$this->browserVersion = !empty($this->browserVersion) ? $this->browserVersion : null;

		$this->osName = $result->os->getName();
		$this->osName = !empty($this->osName) ? $this->osName : null;
		$this->osVersion = $result->os->getVersion();
		$this->osVersion = !empty($this->osVersion) ? $this->osVersion : null;

		$this->deviceIsMobile = $result->isMobile();
		$this->deviceType = DeviceType::tryFrom($result->device->type);
		$this->deviceType = !empty($this->deviceType) ? $this->deviceType : null;
		$this->deviceManufacturer = $result->device->getManufacturer();
		$this->deviceManufacturer = !empty($this->deviceManufacturer) ? $this->deviceManufacturer : null;
		$this->deviceModel = $result->device->getModel();
		$this->deviceModel = !empty($this->deviceModel) ? $this->deviceModel : null;
	}

	public static function parse(string $userAgent): UserAgent
	{
		return new UserAgent($userAgent);
	}

	public function getInfosDisplay($separator=' — '): string
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
			'os' => !empty($os ?? null) ? $os : null,
			'browser' => !empty($browser) ? $browser : null,
			'device' => !empty($device) ? $device : null,
		];
	}

	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(): array
	{
		return [
			'user_agent_desc' => $this->readableRepresentation,
			'browser_name' => $this->browserName,
			'os_name' => $this->osName,
			'device_type' => $this->deviceType,
			'device_is_mobile' => $this->deviceIsMobile,
			'device_manufacturer' => $this->deviceManufacturer,
			'device_model' => $this->deviceModel,
		];
	}

	public function __toString(): string
	{
		return $this->readableRepresentation;
	}
}