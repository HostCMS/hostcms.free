<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Security helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Security
{
	static public function checkXSS($string)
	{
		if (!is_string($string))
		{
			return FALSE;
		}

		// URL decode
		$decoded = urldecode($string);
		// Hexadecimals => single-byte string, e.g. &#x4f60;, \x4f60;
		$decoded = preg_replace_callback('/(?:&#|\\\)[x]([0-9a-f]+);?/i', 'Core_Security::_chr_hexdec', $decoded);
		// &#00 => &#00;
		$decoded = preg_replace('/(&#0+[0-9]+)/', '$1;', $decoded);
		// Convert HTML entities to their corresponding characters
		$decoded = html_entity_decode($decoded, defined('ENT_HTML5') ? ENT_QUOTES | ENT_HTML5 : ENT_QUOTES, 'UTF-8');
		// Remove all spaces
		$decoded = preg_replace('/\s/', '', $decoded);

		$aPatterns = array(
			// Match attributes starting 'on'/'xmlns'
			'/(<[^>]+[\"\'\/\x00-\x20])(on|xmlns)[^>]*>?/iuU',
			// Match data:|feed:|mocha:, javascript:|livescript:|vbscript:
			'/(data|feed|mocha|(java|live|vb)script):(\w)*/iuU',
			// Match style attributes
			'/(<[^>]+[\"\'\/\x00-\x20])style=[^>]*>?/iuU',
			// Match tags
			'/<\/*(applet|bgsound|blink|base|embed|frame|frameset|iframe|ilayer|layer|link|meta|object|script|style|title|xml)[^>]*>?/i',
			// Match -moz-binding, removed from FF 67
			'/-moz-binding[\x00-\x20]*:/iu'
		);

		foreach ($aPatterns as $pattern)
		{
			if (preg_match($pattern, $string) || preg_match($pattern, $decoded))
			{
				return TRUE;
			}
		}

		return FALSE;
	}

	/**
	 * Callback for hexadecimals
	 * @param array $matches
	 * @return string
	 */
	static protected function _chr_hexdec($matches)
	{
		return chr(
			hexdec($matches[1])
		);
	}
}