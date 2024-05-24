<?php

namespace Osimatic\Text;

/**
 * Array2XML: A class to convert array in PHP to XML
 * It also takes into account attributes names unlike SimpleXML in PHP
 * It returns the XML in form of DOMDocument class for further manipulation.
 * It throws exception if the tag name or attribute name has illegal chars.
 *
 * Author : Lalit Patel
 * Website: http://www.lalit.org/lab/convert-php-array-to-xml-with-attributes
 * License: Apache License 2.0
 *		  http://www.apache.org/licenses/LICENSE-2.0
 * Version: 0.1 (10 July 2011)
 * Version: 0.2 (16 August 2011)
 *		  - replaced htmlentities() with htmlspecialchars() (Thanks to Liel Dulev)
 *		  - fixed a edge case where root node has a false/null/0 value. (Thanks to Liel Dulev)
 * Version: 0.3 (22 August 2011)
 *		  - fixed tag sanitize regex which didn't allow tagnames with single character.
 * Version: 0.4 (18 September 2011)
 *		  - Added support for CDATA section using @cdata instead of @value.
 * Version: 0.5 (07 December 2011)
 *		  - Changed logic to check numeric array indices not starting from 0.
 * Version: 0.6 (04 March 2012)
 *		  - Code now doesn't @cdata to be placed in an empty array
 * Version: 0.7 (24 March 2012)
 *		  - Reverted to version 0.5
 * Version: 0.8 (02 May 2012)
 *		  - Removed htmlspecialchars() before adding to text node or attributes.
 *
 * Usage:
 *	   $xml = Array2XML::createXML('root_node_name', $php_array);
 *	   echo $xml->saveXML();
 */
class Array2XML
{
	/**
	 * Convert an Array to XML
	 * @param string $node_name - name of the root node to be converted
	 * @param array $arr - aray to be converterd
	 * @param string $version
	 * @param string $encoding
	 * @param bool $format_output
	 * @return \DomDocument
	 */
	public static function createXML(string $node_name, array $arr=[], string $version = '1.0', string $encoding = 'UTF-8', bool $format_output = true): \DomDocument
	{
		$xml = new \DomDocument($version, $encoding);
		$xml->formatOutput = $format_output;
		try {
			$xml->appendChild(self::convert($xml, $node_name, $arr));
		} catch (\DOMException $e) {
		}
		return $xml;
	}

	/**
	 * Convert an Array to XML
	 * @param \DomDocument $xml
	 * @param string $node_name - name of the root node to be converted
	 * @param array $arr - aray to be converterd
	 * @return \DOMNode
	 * @throws \DOMException
	 */
	private static function &convert(\DomDocument $xml, string $node_name, array $arr=[]): \DOMNode
	{
		//print_arr($node_name);
		$node = $xml->createElement($node_name);

		if(is_array($arr)){
			// get the attributes first.;
			if(isset($arr['@attributes'])) {
				foreach($arr['@attributes'] as $key => $value) {
					if(!self::isValidTagName($key)) {
						throw new \RuntimeException('[Array2XML] Illegal character in attribute name. attribute: '.$key.' in node: '.$node_name);
					}
					$node->setAttribute($key, self::formatValue($value));
				}
				unset($arr['@attributes']); //remove the key from the array once done.
			}

			// check if it has a value stored in @value, if yes store the value and return
			// else check if its directly stored as string
			if(isset($arr['@value'])) {
				$node->appendChild($xml->createTextNode(self::formatValue($arr['@value'])));
				unset($arr['@value']);	//remove the key from the array once done.
				//return from recursion, as a note with value cannot have child nodes.
				return $node;
			}
			if(isset($arr['@cdata'])) {
				$node->appendChild($xml->createCDATASection(self::formatValue($arr['@cdata'])));
				unset($arr['@cdata']);	//remove the key from the array once done.
				//return from recursion, as a note with cdata cannot have child nodes.
				return $node;
			}
		}

		//create subnodes using recursion
		if(is_array($arr)){
			// recurse to get the node for that key
			foreach($arr as $key=>$value){
				if(!self::isValidTagName($key)) {
					throw new \RuntimeException('[Array2XML] Illegal character in tag name. tag: '.$key.' in node: '.$node_name);
				}
				if(is_array($value) && is_numeric(key($value))) {
					// MORE THAN ONE NODE OF ITS KIND;
					// if the new array is numeric index, means it is array of nodes of the same kind
					// it should follow the parent key name
					foreach($value as $k=>$v){
						$node->appendChild(self::convert($xml, $key, $v));
					}
				} else {
					// ONLY ONE NODE OF ITS KIND
					$node->appendChild(self::convert($xml, $key, $value));
				}
				unset($arr[$key]); //remove the key from the array once done.
			}
		}

		// after we are done with all the keys in the array (if it is one)
		// we check if it has any text value, if yes, append it.
		if(!is_array($arr)) {
			$node->appendChild($xml->createTextNode(self::formatValue($arr)));
		}

		return $node;
	}

	/*
	 * Get string representation of boolean value
	 */
	private static function formatValue($v)
	{
		if (is_bool($v)) {
			return self::bool2str($v);
		}
		return $v;
	}

	/*
	 * Get string representation of boolean value
	 */
	private static function bool2str(bool $v): string
	{
		return $v === true ? 'true' : 'false';
	}

	/*
	 * Check if the tag name or attribute name contains illegal characters
	 * Ref: http://www.w3.org/TR/xml/#sec-common-syn
	 */
	private static function isValidTagName(string $tag): bool
	{
		$pattern = '/^[a-z_]+[a-z0-9\:\-\.\_]*[^:]*$/i';
		return preg_match($pattern, $tag, $matches) && $matches[0] == $tag;
	}
}
