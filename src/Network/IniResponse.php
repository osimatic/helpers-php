<?php

namespace Osimatic\Network;

use Symfony\Component\HttpFoundation\Response;

class IniResponse
{
	public const string LINE_BREAK = "\r\n";

	public static function get(string $result, array $data=[]): Response
	{
		$ini = ''
			.'[data]' .self::LINE_BREAK
			.'result='.$result.self::LINE_BREAK
		;

		foreach ($data as $key => $value) {
			$ini .= $key.'='.$value.self::LINE_BREAK;
		}

		return new Response($ini);
	}

	public static function getList(array $data=[], string $countFieldName='count'): Response
	{
		$ini = ''
			.'[data]' .self::LINE_BREAK
			.$countFieldName.'='.count($data).self::LINE_BREAK
		;

		foreach ($data as $sectionName => $list) {
			$ini .= '['.$sectionName.']' .self::LINE_BREAK;
			foreach ($list as $key => $value) {
				$ini .= $key.'='.$value.self::LINE_BREAK;
			}
		}

		return new Response($ini);
	}
}