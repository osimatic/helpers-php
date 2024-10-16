<?php

namespace Osimatic\Messaging;

enum EmailContentType: string
{
	case PLAINTEXT = 'text/plain';
	case TEXT_CALENDAR = 'text/calendar';
	case TEXT_HTML = 'text/html';
	case MULTIPART_ALTERNATIVE = 'multipart/alternative';
	case MULTIPART_MIXED = 'multipart/mixed';
	case MULTIPART_RELATED = 'multipart/related';
}