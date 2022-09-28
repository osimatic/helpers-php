<?php

namespace Osimatic\Helpers\Network;

class UserAgent
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

	public ?string $browserName;
	public ?string $browserVersion;

	public ?string $osName;
	public ?string $osVersion;

	public ?string $deviceType;
	public ?string $deviceManufacturer;
	public ?string $deviceModel;
}