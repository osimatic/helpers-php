<?php

namespace Osimatic\Network;

use Symfony\Component\HttpFoundation\Response;

/**
 * Class IniResponse
 * Generates HTTP responses in INI file format
 */
class IniResponse
{
	public const string LINE_BREAK = "\r\n";

	/**
	 * Creates a Response with INI format containing a result and additional data
	 * @param string $result the result value
	 * @param array $data additional key-value pairs to include in the data section
	 * @return Response
	 */
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

	/**
	 * Creates a Response with INI format containing multiple sections with their data
	 * @param array $data array of sections where each key is the section name and value is an array of key-value pairs
	 * @param string $countFieldName name of the field containing the count of sections
	 * @return Response
	 */
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