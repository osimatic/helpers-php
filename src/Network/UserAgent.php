<?php

namespace Osimatic\Helpers\Network;

class UserAgent implements \JsonSerializable
{
	public const DEVICE_TYPE_DESKTOP 			= 'desktop';
	public const DEVICE_TYPE_MOBILE 			= 'mobile';
	public const DEVICE_TYPE_PDA 				= 'pda';
	public const DEVICE_TYPE_DECT 				= 'dect';
	public const DEVICE_TYPE_TABLET 			= 'tablet';
	public const DEVICE_TYPE_GAMING 			= 'gaming';
	public const DEVICE_TYPE_EREADER 			= 'ereader';
	public const DEVICE_TYPE_MEDIA 				= 'media';
	public const DEVICE_TYPE_HEADSET 			= 'headset';
	public const DEVICE_TYPE_WATCH 				= 'watch';
	public const DEVICE_TYPE_EMULATOR 			= 'emulator';
	public const DEVICE_TYPE_TELEVISION 		= 'television';
	public const DEVICE_TYPE_MONITOR 			= 'monitor';
	public const DEVICE_TYPE_CAMERA 			= 'camera';
	public const DEVICE_TYPE_PRINTER 			= 'printer';
	public const DEVICE_TYPE_SIGNAGE 			= 'signage';
	public const DEVICE_TYPE_WHITEBOARD 		= 'whiteboard';
	public const DEVICE_TYPE_DEVBOARD 			= 'devboard';
	public const DEVICE_TYPE_INFLIGHT 			= 'inflight';
	public const DEVICE_TYPE_APPLIANCE 			= 'appliance';
	public const DEVICE_TYPE_GPS 				= 'gps';
	public const DEVICE_TYPE_CAR 				= 'car';
	public const DEVICE_TYPE_POS 				= 'pos';
	public const DEVICE_TYPE_BOT 				= 'bot';
	public const DEVICE_TYPE_PROJECTOR 			= 'projector';

	public object $parser;

	public string $readableRepresentation;

	public ?string $browserName;
	public ?string $browserVersion;

	public ?string $osName;
	public ?string $osVersion;

	public bool $deviceIsMobile;
	public ?string $deviceType;
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
		$this->deviceType = $result->device->type;
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