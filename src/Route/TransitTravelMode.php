<?php

namespace Osimatic\Helpers\Route;

enum TransitTravelMode: string
{
	case BUS = 'BUS';
	case SUBWAY = 'SUBWAY';
	case TRAIN = 'TRAIN';
	case LIGHT_RAIL = 'LIGHT_RAIL';
}