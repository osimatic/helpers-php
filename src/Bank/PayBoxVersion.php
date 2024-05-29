<?php

namespace Osimatic\Bank;

enum PayBoxVersion: string
{
	case PAYBOX_DIRECT = '00103';
	case PAYBOX_DIRECT_PLUS = '00104';
}