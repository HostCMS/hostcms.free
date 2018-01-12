<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * XML helper.
 *
 * @package HostCMS
 * @subpackage Core\Xml
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Xml
{
	/**
	 * XML to array
	 * @param SimpleXMLElement $xml XML
	 * @return array
	 */
	static public function xml2array($xml)
	{
		libxml_use_internal_errors(FALSE);
		return self::_xml2array(simplexml_load_string($xml));
	}
	
	/**
	 * XML to array
	 * @param SimpleXMLElement $oXml XML
	 * @return array
	 */
	static protected function _xml2array($oXml)
	{
		$array = array();
		
		if (is_object($oXml))
		{
			$array['name'] = $oXml->getName();
			$array['value'] = trim((string)$oXml);

			if (count($oXml->attributes()) > 0)
			{
				$array['attr'] = array();

				foreach ($oXml->attributes() as $key => $attr_name)
				{
					$array['attr'][$key] = (string)$attr_name;
				}
			}

			foreach ($oXml->children() as $oChildren)
			{
				$array['children'][] = self::_xml2array($oChildren);
			}
		}

		return $array ;
	}
	
	/**
	 * Array to XML
	 * @param array $array Array
	 * @return string
	 */
	static public function array2xml(array $array)
	{
		$sReturn = '';
		
		foreach ($array as $key => $value)
		{
			$key = Core_Str::xml($key);
			$sReturn .= '<' . $key . '>' . (
				is_array($value)
					? "\n" . self::array2xml($value)
					: Core_Str::xml($value)
			) . '</' . $key . ">\n";
		}
		
		return $sReturn;
	}
}