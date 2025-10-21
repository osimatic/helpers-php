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

	public function getLabel(): string
	{
		return match ($this) {
			self::DESKTOP => 'Ordinateur',
			self::MOBILE => 'Mobile',
			self::PDA => 'PDA',
			self::DECT => 'Dect',
			self::TABLET => 'Tablette',
			self::GAMING => 'Console',
			self::EREADER => 'Liseuse',
			self::MEDIA => 'Média',
			self::HEADSET => 'Casque',
			self::WATCH => 'Montre',
			self::EMULATOR => 'Émulateur',
			self::TELEVISION => 'TV',
			self::MONITOR => 'Écran',
			self::CAMERA => 'Appareil photo',
			self::PRINTER => 'Imprimante',
			self::SIGNAGE => 'Dispositif d’affichage digital',
			self::WHITEBOARD => 'Tableau interactif',
			self::DEVBOARD => 'Module de développement',
			self::INFLIGHT => 'Divertissement en vol',
			self::APPLIANCE => 'Application matérielle',
			self::GPS => 'GPS',
			self::CAR => 'Véhicule',
			self::POS => 'Point de vente',
			self::BOT => 'Bot',
			self::PROJECTOR => 'Vidéo projecteur',
		};
	}
}