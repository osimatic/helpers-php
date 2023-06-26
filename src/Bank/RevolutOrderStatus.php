<?php

namespace Osimatic\Helpers\Bank;

enum RevolutOrderStatus: string
{
    case ORDER_AUTHORISED   = 'ORDER_AUTHORISED';
    case ORDER_COMPLETED    = 'ORDER_COMPLETED';
}