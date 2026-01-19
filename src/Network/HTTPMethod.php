<?php

namespace Osimatic\Network;

/**
 * Enum HTTPMethod
 * Represents HTTP request methods
 */
enum HTTPMethod: string
{
	case GET = 'GET';
    case POST = 'POST';
    case PATCH = 'PATCH';
    case PUT = 'PUT';
    case DELETE = 'DELETE';
}