<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Incoming Http Request helper.
 * Вспомогательный класс для работы с данными и заголовками входящего http-запроса
 *
 * @package HostCMS
 * @subpackage Core
 * @author James V. Kotov
 * @copyright 2014
 * @version 1.0a
 * @access public commercial
 * @required HostCMS v6.1.1+
 */
class Core_Request
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Кеш raw data
	 * @var mixed
	 */
	protected $_rawPost = NULL;

	/**
	 * Кеш заголовков запроса
	 * @var mixed
	 */
	protected $_requestHeaders = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Core_Request::getRawPost()
	 * Возвращает raw post data из пришедшего запроса
	 *
	 * @return string
	 */
	public function getRawPost()
	{
		if (is_null($this->_rawPost))
		{
			$this->_rawPost = strcasecmp(Core_Array::get($_SERVER, 'REQUEST_METHOD'), 'POST') == 0
				? trim(isset($GLOBALS['HTTP_RAW_POST_DATA'])
					? $GLOBALS['HTTP_RAW_POST_DATA']
					: @file_get_contents("php://input")
				)
				: '';
		}

		return $this->_rawPost;
	}

	/**
	 * Core_Request::getRequestHeaders()
	 * Возвращает массив с заголовками пришедшего запроса
	 *
	 * @return array
	 */
	public function getRequestHeaders()
	{
		if (is_null($this->_requestHeaders))
		{
			$this->_requestHeaders = array();

			foreach ($_SERVER as $name => $value)
			{
				if (substr($name, 0, 5) == 'HTTP_')
				{
					$name = strtolower(
						str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))
					);
					$this->_requestHeaders[$name] = $value;
				}
				elseif (strtoupper($name) == "CONTENT_TYPE")
				{
					$this->_requestHeaders['content-type'] = $value;
				}
				elseif (strtoupper($name) == 'CONTENT_LENGTH')
				{
					$this->_requestHeaders['content-length'] = $value;
				}
			}
		}

		return $this->_requestHeaders;
	}

	/**
	 * Core_Request::getRequestHeader()
	 * Возвращает значение требуемого заголовка от пришедшего запроса или null, если требуемый заголовок не был передан
	 * @param string $sHeaderName
	 * @return string|null
	 */
	public function getRequestHeader($sHeaderName)
	{
		$sHeaderName = strtolower($sHeaderName);
		$aHeaders = $this->getRequestHeaders();

		return isset($aHeaders[$sHeaderName])
			? $aHeaders[$sHeaderName]
			: NULL;
	}
}