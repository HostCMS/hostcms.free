<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Security helper. XSS protection. CSRF token.
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Security
{
	/**
	 * Check XSS
	 * @param String $string
	 * @return boolean
	 */
	static public function checkXSS($string)
	{
		if (!is_string($string))
		{
			return FALSE;
		}

		// Double Encoding (before URL decode): Char '<' (Hex encode %3C), Then encoding '%' - %25, Double encode %253C
		$decoded = preg_replace('/%25([0-9a-f]{2})/i', '%$1', $string);
		// URL decode
		$decoded = rawurldecode($decoded);
		//var_dump($decoded);
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
			@hexdec($matches[1])
		);
	}

	/**
	 * Get CSRF token
	 */
	static public function getCsrfToken()
	{
		!Core_Session::hasSessionId() && Core_Session::start();

		$time = time();

		return $time . hash('sha256', session_id() . $time);
	}

	/**
	 * The last CSRF error
	 * @var NULL|string
	 */
	static protected $_checkCsrfError = NULL;

	/**
	 * Get last CSRF error
	 */
	static public function getCsrfError()
	{
		return self::$_checkCsrfError;
	}

	/**
	 * Throw csrf error
	 */
	static public function throwCsrfError()
	{
		switch (self::getCsrfError())
		{
			case 'wrong-length':
				throw new Core_Exception(Core::_('Core.csrf_wrong_token'), array(), 0, FALSE);
			break;
			case 'wrong-token':
			default:
				throw new Core_Exception(Core::_('Core.csrf_wrong_token'), array(), 0, FALSE);
			break;
			case 'timeout':
				throw new Core_Exception(Core::_('Core.csrf_token_timeout'), array(), 0, FALSE);
			break;
		}
	}

	/**
	 * Check valid CSRF-token
	 * @param string $secret_csrf Token to check
	 * @return boolean
	 */
	static public function checkCsrf($secret_csrf, $lifetime)
	{
		self::$_checkCsrfError = NULL;

		if (strlen($secret_csrf) != 74)
		{
			self::$_checkCsrfError = 'wrong-length';
			return FALSE;
		}

		$csrf_time = substr($secret_csrf, 0, 10);
		$csrf_token = substr($secret_csrf, 10, strlen($secret_csrf));

		$token = hash('sha256', session_id() . $csrf_time);
		if ($token !== $csrf_token)
		{
			self::$_checkCsrfError = 'wrong-token';
			return FALSE;
		}

		if (time() - $csrf_time > $lifetime)
		{
			self::$_checkCsrfError = 'timeout';
			return FALSE;
		}

		return TRUE;
	}
}