<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Exceptions
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Exception extends Exception
{
	/**
	 * Constructor.
	 * Exception $previous = NULL just for PHP 5.3.0+
	 * @param string $message message text
	 * @param array $values values for exchange
	 * @param int $code error code
	 * @param boolean $bShowDebugTrace debug trace info mode
	 * @param int $status error status
	 * @param boolean $log write to log
	 */
	public function __construct($message = NULL, array $values = array(), $code = 0, $bShowDebugTrace = TRUE, $status = 0, $log = TRUE)
	{
		if (!is_null($message) && !empty($values))
		{
			$values = array_map('htmlspecialchars', $values);
			$message = str_replace(array_keys($values), array_values($values), $message);
		}

		if ($bShowDebugTrace)
		{
			$aDebugTrace = $this->getDebugTrace();

			foreach ($aDebugTrace as $aTrace)
			{
				$message .= "\n<br />{$aTrace['line']} {$aTrace['file']}";
			}
		}

		$log && Core_Log::instance()->clear()->status($status)->write(strip_tags($message));

		// Fix bug with PDO text codes, e.g. '42S21'. Code should be integer
		$bIsNumeric = is_numeric($code);

		parent::__construct($message, $bIsNumeric ? $code : 0);

		!$bIsNumeric && $this->code = $code;
	}

	/**
	 * Get debug trace
	 * @return array
	 */
	public function getDebugTrace()
	{
		$debug_backtrace = debug_backtrace();

		$aDebugTrace = array();

		foreach ($debug_backtrace as $history)
		{
			if (isset($history['file']) && isset($history['line']))
			{
				$history['file'] = self::cutRootPath($history['file']);

				$aDebugTrace[] = array('file' => $history['file'], 'line' => $history['line']);
			}
		}

		return $aDebugTrace;
	}

	/**
	 * Cut CMS_FOLDER from $path
	 * @param string $path path
	 * @return string
	 */
	static public function cutRootPath($path)
	{
		if (strpos($path, dirname(CMS_FOLDER)) === 0)
		{
			$path = substr($path, strlen(CMS_FOLDER));
		}

		return $path;
	}
}