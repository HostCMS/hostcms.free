<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * HTTP response.
 *
 * <code>
 * $oCore_Response
 * 	->status(200)
 * 	->header('Content-Type', "text/plain; charset={$oSite->coding}")
 * 	->header('Last-Modified', gmdate('D, d M Y H:i:s', time()) . ' GMT')
 * 	->header('X-Powered-By', 'HostCMS')
 * 	->body('Page content')
 * 	->compress()
 * 	->sendHeaders()
 * 	->showBody();
 * </code>
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Response
{
	/**
	 * Response status
	 */
	protected $_status = 200;

	/**
	 * List of HTTP codes
	 * @var array
	 */
	static protected $_httpStatusCode = array(
		// 1xx: Informational (Информационные).
		100 => 'Continue', // (Продолжать).
		101 => 'Switching Protocols', // (Переключение протоколов).
		102 => 'Processing', // (Идет обработка).

		//2xx: Success (Успешно).
		200 => 'OK', // (Хорошо).
		201 => 'Created', // (Создано).
		202 => 'Accepted', // (Принято).
		203 => 'Non-Authoritative Information', // (Информация не авторитетна).
		204 => 'No Content', // (Нет содержимого).
		205 => 'Reset Content', // (Сбросить содержимое).
		206 => 'Partial Content', // (Частичное содержимое).
		207 => 'Multi-Status', // (Многостатусный).
		226 => 'IM Used', // (IM использовано).

		//3xx: Redirection (Перенаправление).
		300 => 'Multiple Choices', // (Множество выборов).
		301 => 'Moved Permanently', // (Перемещено окончательно).
		302 => 'Found', // (Найдено).
		303 => 'See Other', // (Смотреть другое).
		304 => 'Not Modified', // (Не изменялось).
		305 => 'Use Proxy', // (Использовать прокси).
		306	=> '', // (зарезервировано).
		307 => 'Temporary Redirect', // (Временное перенаправление).

		//4xx: Client Error (Ошибка клиента).
		400 => 'Bad Request', // (Плохой запрос).
		401 => 'Unauthorized', // (Неавторизован).
		402 => 'Payment Required', // (Необходима оплата).
		403 => 'Forbidden', // (Запрещено).
		404 => 'Not Found', // (Не найдено).
		405 => 'Method Not Allowed', // (Метод не поддерживается).
		406 => 'Not Acceptable', // (Не приемлемо).
		407 => 'Proxy Authentication Required', // (Необходима аутентификация прокси).
		408 => 'Request Timeout', // (Время ожидания истекло).
		409 => 'Conflict', // (Конфликт).
		410 => 'Gone', // (Удален).
		411 => 'Length Required', // (Необходима длина).
		412 => 'Precondition Failed', // (Условие «ложно»).
		413 => 'Request Entity Too Large', // (Размер запроса слишком велик).
		414 => 'Request-URI Too Long', // (Запрашиваемый URI слишком длинный).
		415 => 'Unsupported Media Type', // (Неподдерживаемый тип данных).
		416 => 'Requested Range Not Satisfiable', // (Запрашиваемый диапазон не достижим).
		417 => 'Expectation Failed', // (Ожидаемое не приемлемо).
		418 => 'I\'m a teapot', // (Я - чайник).
		422 => 'Unprocessable Entity', // (Необрабатываемый экземпляр).
		423 => 'Locked', // (Заблокировано).
		424 => 'Failed Dependency', // (Невыполненная зависимость).
		425 => 'Unordered Collection', // (Неупорядоченный набор).
		426 => 'Upgrade Required', // (Необходимо обновление).
		449 => 'Retry With', // (Повторить с...).
		456 => 'Unrecoverable Error', // (Некорректируемая ошибка...).

		//5xx: Server Error (Ошибка сервера).
		500 => 'Internal Server Error', // (Внутренняя ошибка сервера).
		501 => 'Not Implemented', // (Не реализовано).
		502 => 'Bad Gateway', // (Плохой шлюз).
		503 => 'Service Unavailable', // (Сервис недоступен).
		504 => 'Gateway Timeout', // (Шлюз не отвечает).
		505 => 'HTTP Version Not Supported', // (Версия HTTP не поддерживается).
		506 => 'Variant Also Negotiates', // (Вариант тоже согласован).
		507 => 'Insufficient Storage', // (Переполнение хранилища).
		509 => 'Bandwidth Limit Exceeded', // (Исчерпана пропускная ширина канала).
		510 => 'Not Extended' // (Не расширено).
	);

	/**
	 * Array of headers
	 * @var array
	 */
	protected $_headers = array();

	/**
	 * Body
	 * @var string
	 */
	protected $_body = NULL;

	/**
	 * Set response status
	 *
	 * <code>
	 * $oCore_Response->status(200);
	 * </code>
	 * @param int $code Status code
	 * @return Core_Response
	 */
	public function status($code)
	{
		$this->_status = $code;
		return $this;
	}

	/**
	 * Get response status
	 *
	 * @return int status
	 */
	public function getStatus()
	{
		return $this->_status;
	}

	/**
	 * Add response body
	 *
	 * <code>
	 * $oCore_Response
	 * 	->body('Page content.')
	 * 	->body('Additional page content.');
	 * </code>
	 * @param string $body
	 * @return Core_Response
	 */
	public function body($body)
	{
		$this->_body .= $body;
		return $this;
	}

	/**
	 * Change response body
	 *
	 * <code>
	 * $oCore_Response
	 * 	->body('New body');
	 * </code>
	 * @param string $body
	 * @return Core_Response
	 */
	public function changeBody($body)
	{
		$this->_body = $body;
		return $this;
	}

	/**
	 * Add response header
	 *
	 * <code>
	 * $oCore_Response->header('X-Powered-By', 'HostCMS');
	 * </code>
	 * @param string $name
	 * @param string $value
	 * @param string $replace default TRUE
	 * @return Core_Response
	 */
	public function header($name, $value, $replace = TRUE)
	{
		$this->_headers[] = array($name, $value, $replace);
		return $this;
	}

	/**
	 * Compress response body
	 *
	 * <code>
	 * $oCore_Response->compress();
	 * </code>
	 * @return Core_Response
	 * @hostcms-event Core_Response.onBeforeCompress
	 * @hostcms-event Core_Response.onAfterCompress
	 */
	public function compress()
	{
		Core_Event::notify(get_class($this) . '.onBeforeCompress', $this);

		if (Core::moduleIsActive('compression'))
		{
			$oCompression_Controller = Compression_Controller::instance('http');

			if ($oCompression_Controller->compressionAllowed())
			{
				$encoding = $oCompression_Controller->getAcceptEncoding();

				if ($encoding)
				{
					//$this->header('Content-Encoding', $encoding);
					$this->_body = $oCompression_Controller->compress($this->_body, $encoding);
				}
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterCompress', $this);

		return $this;
	}

	/**
	 * Send HTTP response code
	 * @param int $status Status code
	 *
	 */
	static public function sendHttpStatusCode($status)
	{
		if (isset(self::$_httpStatusCode[$status]))
		{
			$sHttpStatusCode = self::$_httpStatusCode[$status];

			$SERVER_PROTOCOL = Core_Array::get($_SERVER, 'SERVER_PROTOCOL');

			if (substr(php_sapi_name(), 0, 3) == 'cgi')
			{
				header("Status: {$status} {$sHttpStatusCode}", TRUE);
			}
			elseif ($SERVER_PROTOCOL == 'HTTP/1.0')
			{
				header("HTTP/1.0 {$status} {$sHttpStatusCode}", TRUE, $status);
			}
			else
			{
				header("HTTP/1.1 {$status} {$sHttpStatusCode}", TRUE, $status);
			}
			/*Core_Array::get($_SERVER, 'SERVER_PROTOCOL') == 'HTTP/1.1' && function_exists('apache_lookup_uri')
				? header("HTTP/1.1 {$this->_status} {$sHttpStatusCode}")
				: header("Status: {$this->_status} {$sHttpStatusCode}");*/
		}
	}

	/**
	 * Send response headers
	 *
	 * <code>
	 * $oCore_Response->sendHeaders();
	 * </code>
	 * @return Core_Response
	 * @hostcms-event Core_Response.onBeforeSendHeaders
	 * @hostcms-event Core_Response.onAfterSendHeaders
	 */
	public function sendHeaders()
	{
		Core_Event::notify(get_class($this) . '.onBeforeSendHeaders', $this);

		self::sendHttpStatusCode($this->_status);

		foreach ($this->_headers as $value)
		{
			header($value[0] . ': ' . Core_Http::sanitizeHeader($value[1]), $value[2]);
		}

		Core_Event::notify(get_class($this) . '.onAfterSendHeaders', $this);

		return $this;
	}

	/**
	 * Get headers
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->_headers;
	}

	/**
	 * Get body
	 *
	 * @return string
	 */
	public function getBody()
	{
		return $this->_body;
	}

	/**
	 * Show response body
	 *
	 * <code>
	 * $oCore_Response->showBody();
	 * </code>
	 * @return Core_Response
	 * @hostcms-event Core_Response.onBeforeShowBody
	 * @hostcms-event Core_Response.onAfterShowBody
	 */
	public function showBody()
	{
		Core_Event::notify(get_class($this) . '.onBeforeShowBody', $this);

		echo $this->_body;

		Core_Event::notify(get_class($this) . '.onAfterShowBody', $this);

		return $this;
	}
}