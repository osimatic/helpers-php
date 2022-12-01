<?php

namespace Osimatic\Helpers\Messaging;

enum PhoneNumberType: int
{
	case MOBILE 		= \libphonenumber\PhoneNumberType::MOBILE;
	case FIXED_LINE 	= \libphonenumber\PhoneNumberType::FIXED_LINE;
	case PREMIUM_RATE 	= \libphonenumber\PhoneNumberType::PREMIUM_RATE;
	case TOLL_FREE 		= \libphonenumber\PhoneNumberType::TOLL_FREE;
	case SHARED_COST 	= \libphonenumber\PhoneNumberType::SHARED_COST;
	case UNKNOWN 		= \libphonenumber\PhoneNumberType::UNKNOWN;
}