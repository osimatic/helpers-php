<?php

namespace Osimatic\Helpers\Bank;

enum BankCardType: string
{
	case AMERICAN_EXPRESS = 'AMERICAN_EXPRESS';
	case VISA = 'VISA';
	case DINNER_CLUB = 'DINNER_CLUB';
	case DISCOVER_NETWORK = 'DISCOVER_NETWORK';
	case MASTER_CARD = 'MASTER_CARD';
	case GOOGLE_WALLET = 'GOOGLE_WALLET';
	case SKRILL = 'SKRILL';
}