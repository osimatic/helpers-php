<?php

namespace Osimatic\Route;

enum TravelMode: string
{
	case WALK = 'WALK';
	case BICYCLE = 'BICYCLE';
	case DRIVE = 'DRIVE';
	case TWO_WHEELER = 'TWO_WHEELER';
	case TRANSIT = 'TRANSIT';
	case PLANE = 'PLANE';
	case BOAT = 'BOAT';
}