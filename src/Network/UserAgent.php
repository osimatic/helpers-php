<?php

namespace Osimatic\Helpers\Network;

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