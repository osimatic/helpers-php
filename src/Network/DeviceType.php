<?php

namespace Osimatic\Network;

enum DeviceType: string
{
	case DESKTOP 			= 'desktop';
	case MOBILE 			= 'mobile';
	case PDA 				= 'pda';
	case DECT 				= 'dect';
	case TABLET 			= 'tablet';
	case GAMING 			= 'gaming';
	case EREADER 			= 'ereader';
	case MEDIA 				= 'media';
	case HEADSET 			= 'headset';
	case WATCH 				= 'watch';
	case EMULATOR 			= 'emulator';
	case TELEVISION 		= 'television';
	case MONITOR 			= 'monitor';
	case CAMERA 			= 'camera';
	case PRINTER 			= 'printer';
	case SIGNAGE 			= 'signage';
	case WHITEBOARD 		= 'whiteboard';
	case DEVBOARD 			= 'devboard';
	case INFLIGHT 			= 'inflight';
	case APPLIANCE 			= 'appliance';
	case GPS 				= 'gps';
	case CAR 				= 'car';
	case POS 				= 'pos';
	case BOT 				= 'bot';
	case PROJECTOR 			= 'projector';

}