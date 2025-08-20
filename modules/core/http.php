<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Abstract HTTP
 *
 * Доступные методы:
 *
 * - clear() очистить предыдущие данные
 * - instance($name = 'default') получить Core_Http указанного типа, например, curl или socket
 * - config($array) перезаписать конфигурационные данные, загруженные из файла
 * - getConfig() получить массив с конфигурационными данными
 * - additionalHeader($name, $value) установить дополнительный заголовок $name со значением $value. Если $value установлен в NULL, то ранее добавленное значение будет удалено.
 * - addOption($name, $value) установить дополнительную опцию $name со значением $value, используется, например, для задания опций cUrl
 * - method('GET') метод HTTP запроса, по умолчанию GET
 * - timeout(10) таймаут соединения, по умолчанию 10
 * - port(80) порт соединения, по умолчанию 80
 * - contentType('application/x-www-form-urlencoded') Content-type запроса, по умолчанию не задан
 * - rawData($data) все данные, передаваемые в HTTP POST-запросе (передача массива закодирует данные в виде multipart/form-data)
 * - userAgent($userAgent) установить пользовательский агент, по умолчанию 'Mozilla/5.0 (compatible; HostCMS/7.x; +https://www.hostcms.ru)'
 * - url($url) адрес загружаемого ресурса
 * - referer($referer) содержимое заголовка Referer, если NULL, то будет установлен в "{схема}://{запрошенный домен}", если FALSE, то не будет передан
 * - data($key, $value) добавить POST-данные
 * - execute() отправить запрос
 *
 * - getHeaders() получить заголовки ответа
 * - parseHeaders() получить заголовки в виде массива
 * - parseHttpStatusCode('HTTP/1.1 200 OK') получить код ответа по переданному статусу
 * - getBody() получить сырой ответ, может быть сжат и разбит на chunk-и
 * - getDecompressedBody() получить распакованный ответ
 * - getErrno() получить номер ошибки
 * - getError() получить текст ошибки
 *
 * <code>
 * $Core_Http = Core_Http::instance()
 * 	->clear()
 * 	->url('https://www.site.com')
 * 	->additionalHeader('Content-Type', 'text/html')
 * 	->execute();
 *
 * $aHeaders = $Core_Http->parseHeaders();
 * print_r($aHeaders);
 *
 * $sBody = $Core_Http->getDecompressedBody();
 * echo htmlspecialchars($sBody);
 * </code>
 *
 * @package HostCMS
 * @subpackage Core\Http
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
abstract class Core_Http
{
	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return string
	 */
	static protected function _getDriverName($driver)
	{
		return __CLASS__ . '_' . ucfirst($driver);
	}

	/**
	 * Original Config
	 */
	protected $_originalConfig = NULL;

	/**
	 * The singleton instance.
	 * @var array
	 */
	static private $_instance = array();

	/**
	 * Register an existing instance as a singleton.
	 * @param string $name
	 * @return object
	 */
	static public function instance($name = 'default')
	{
		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$_instance[$name]))
		{
			$aConfig = Core::$config->get('core_http', array());

			if (!isset($aConfig[$name]) || !isset($aConfig[$name]['driver']))
			{
				throw new Core_Exception('Core_Http "%name" configuration doesn\'t defined', array('%name' => $name));
			}

			// Base config of the $name + driver's default config
			$aConfigDriver = $aConfig[$name]
				+ Core_Array::get($aConfig, $aConfig[$name]['driver'], array())
				+ array('options' => array());

			$driver = self::_getDriverName($aConfigDriver['driver']);

			self::$_instance[$name] = new $driver($aConfigDriver);
			self::$_instance[$name]->config($aConfigDriver);
		}

		return self::$_instance[$name];
	}

	/**
	 * Constructor.
	 */
	public function __construct($config)
	{
		$this->_originalConfig = $config;

		$this->_init();
	}

	/**
	 * Additional parameters
	 * @var array
	 */
	protected $_config = array();

	/**
	 * Set additional parameters
	 * @param array $array params
	 * @return self
	 */
	public function config($array)
	{
		$this->_config = $array;
		return $this;
	}

	/**
	 * Get config
	 * @return array
	 */
	public function getConfig()
	{
		return $this->_config;
	}

	/**
	 * Additional headers
	 * @var array
	 */
	protected $_additionalHeaders = array();

	/**
	 * Add additional headers
	 * @param string $name Header Name
	 * @param string $value Value
	 * @return self
	 */
	public function additionalHeader($name, $value)
	{
		if (!is_null($value))
		{
			$this->_additionalHeaders[$name] = $value;
		}
		elseif (isset($this->_additionalHeaders[$name]))
		{
			unset($this->_additionalHeaders[$name]);
		}

		return $this;
	}

	/**
	 * Add option
	 * @param string $name Header name
	 * @param string $value Value
	 * @return self
	 */
	public function addOption($name, $value)
	{
		$this->_config['options'][$name] = $value;
		return $this;
	}

	/**
	 * Send request
	 * @param string $host host
	 * @param string $path path
	 * @param string $query query
	 * @param mixed $user user
	 * @param mixed $password password
	 * @return mixed
	 */
	abstract protected function _execute($host, $path, $query, $scheme = 'http', $user = NULL, $password = NULL);

	/**
	 * Clear object
	 *
	 * @return self
	 */
	public function clear()
	{
		$this->_additionalHeaders = $this->_data = array();
		$this->_rawData = $this->_userAgent = $this->_url = $this->_referer = $this->_headers = $this->_body = NULL;

		$this->_method = 'GET';

		return $this->config($this->_originalConfig)->_init();
	}

	/**
	 * Initialize object
	 * @return self
	 */
	protected function _init()
	{
		$this
			->userAgent('Mozilla/5.0 (compatible; HostCMS/7.x; +https://www.hostcms.ru)')
			->method('GET')
			->timeout(10)
			->port(80);

		$this
			->additionalHeader('Accept-Charset', 'utf-8,*;q=0.9')
			->additionalHeader('Accept-Language', 'ru-RU,ru;q=0.8,en-US;q=0.5,en;q=0.3')
			->additionalHeader('Accept', 'text/html,application/xml,application/xhtml+xml;q=0.9,text/plain;q=0.8,*/*;q=0.7');

		return $this;
	}

	/**
	 * Request Raw Data
	 * @var string
	 */
	protected $_rawData = NULL;

	/**
	 * Set Raw request data
	 * @param string $data Raw data
	 * @return self
	 */
	public function rawData($data)
	{
		$this->_rawData = $data;
		return $this;
	}

	/**
	 * Request User-Agent
	 * @var string
	 */
	protected $_userAgent = NULL;

	/**
	 * Set HTTP User-Agent
	 * @param string $userAgent User-Agent
	 * @return self
	 */
	public function userAgent($userAgent)
	{
		$this->_userAgent = $userAgent;
		return $this;
	}

	/**
	 * Request URL
	 * @var string
	 */
	protected $_url = NULL;

	/**
	 * Set URL
	 * @param string $url URL
	 * @return self
	 */
	public function url($url)
	{
		$this->_url = $url;
		return $this;
	}

	/**
	 * Get URL
	 * @return string URL
	 */
	public function getUrl()
	{
		return $this->_url;
	}

	/**
	 * Request referer
	 * @var string
	 */
	protected $_referer = NULL;

	/**
	 * Set HTTP referer
	 * @param string $referer referer
	 * @return self
	 */
	public function referer($referer)
	{
		$this->_referer = $referer;
		return $this;
	}

	/**
	 * Request method
	 * @var string
	 */
	protected $_method = NULL;

	/**
	 * Set HTTP method
	 * @param string $method method
	 * @return self
	 */
	public function method($method)
	{
		$this->_method = $method;
		return $this;
	}

	/**
	 * Request time out
	 * @var string
	 */
	protected $_timeout = NULL;

	/**
	 * Set timeout
	 * @param string $timeout timeout
	 * @return self
	 */
	public function timeout($timeout)
	{
		$this->_timeout = $timeout;
		return $this;
	}

	/**
	 * Request port
	 * @var int
	 */
	protected $_port = NULL;

	/**
	 * Set port
	 * @param string $port port
	 * @return self
	 */
	public function port($port)
	{
		$this->_port = $port;
		return $this;
	}

	/**
	 * Request Content-type
	 * @var string
	 */
	protected $_contentType = NULL;

	/**
	 * Set Content-type
	 * @param string $contentType content type
	 * @return self
	 */
	public function contentType($contentType)
	{
		$this->_contentType = $contentType;
		return $this;
	}

	/**
	 * Additional data of the request
	 * @var array
	 */
	protected $_data = array();

	/**
	 * Additional POST data
	 * @param string $key key
	 * @param string $value value
	 * @return self
	 */
	public function data($key, $value)
	{
		$this->_data[$key] = $value;
		return $this;
	}

	/**
	 * Get POST-data
	 * @return array
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Headers of the request
	 * @var string
	 */
	protected $_headers = NULL;

	/**
	 * Get headers
	 * @return string
	 */
	public function getHeaders()
	{
		return $this->_headers;
	}

	/**
	 * Body of the request
	 * @var string
	 */
	protected $_body = NULL;

	/**
	 * Get body
	 * @return string
	 */
	public function getBody()
	{
		return $this->_body;
	}

	/**
	 * Get decompressed body
	 * @return string
	 */
	public function getDecompressedBody()
	{
		$aHeaders = array_change_key_case($this->parseHeaders(), CASE_LOWER);

		if (isset($aHeaders['content-encoding']))
		{
			$encoding = is_array($aHeaders['content-encoding'])
				? end($aHeaders['content-encoding'])
				: $aHeaders['content-encoding'];

			switch ($encoding)
			{
				case 'gzip':
					// First 2 bytes: 1F 8B
					if (substr($this->_body, 0, 2) === chr(0x1F) . chr(0x8b))
					{
						$content = substr($this->_body, 10);
						return $content != '' ? gzinflate($content) : '';
					}
				break;
				case 'none':
					// nothing to do
				break;
				default:
					throw new Core_Exception('Core_Http unsupported compression method "%name"', array('%name' => $encoding));
			}
		}

		return $this->_body;
	}

	/**
	 * Error number
	 * @var int
	 */
	protected $_errno = 0;

	/**
	 * Get Error number
	 * @return int
	 */
	public function getErrno()
	{
		return $this->_errno;
	}

	/**
	 * Error message
	 * @var string|NULL
	 */
	protected $_error = NULL;

	/**
	 * Get Error message
	 * @return string|NULL
	 */
	public function getError()
	{
		return $this->_error;
	}

	/**
	 * Executes the business logic.
	 * @return self
	 */
	public function execute()
	{
		if (!is_null($this->_url))
		{
			$aUrl = @parse_url(trim($this->_url));

			$scheme = strtolower(Core_Array::get($aUrl, 'scheme', 'http'));

			// Change https port
			$scheme == 'https' && $this->_port == 80
				&& $this->_port = 443;

			$path = isset($aUrl['host']) && isset($aUrl['path'])
				? $aUrl['path']
				: '/';

			isset($aUrl['port']) && $this->_port = $aUrl['port'];

			$host = Core_Array::get($aUrl, 'host', '');

			$query = isset($aUrl['query']) ?
				'?' . $aUrl['query']
				: '';

			$this->_referer = is_null($this->_referer)
				? "{$scheme}://{$host}/"
				: $this->_referer;

			$this->_execute($host, $path, $query, $scheme, Core_Array::get($aUrl, 'user'), Core_Array::get($aUrl, 'pass'));
		}
		
		return $this;
	}

	/**
	 * Parse header
	 * @return array
	 */
	public function parseHeaders()
	{
		return $this->_parseHeaders(
			$this->getHeaders()
		);
	}

	/**
	 * Parse HTTP status code
	 * @param string $status status code, e.g. 'HTTP/1.1 200 OK'
	 * @return int|NULL e.g. '200'
	 */
	public function parseHttpStatusCode($status)
	{
		if (is_string($status))
		{
			preg_match('|HTTP/\d(?:\.\d)?\s+(\d+)|', $status, $match);
			return Core_Array::get($match, 1);
		}

		return NULL;
	}

	/**
	 * Parse header callback
	 * @param array $matches
	 * @return string
	 */
	protected function _parseHeadersCallback($matches)
	{
		return strtoupper($matches[0]);
	}

	/**
	 * Parse header
	 * @param string $header
	 * @return array
	 */
	protected function _parseHeaders($header)
	{
		$aReturn = array();

		if (!is_null($header))
		{
			$fields = explode("\r\n", preg_replace('/\x0D\x0A[\x09\x20]+/', ' ', $header));

			foreach ($fields as $field)
			{
				if (preg_match('/([^:]+): (.*)/m', $field, $match))
				{
					$match[1] = preg_replace_callback(
						'/(?<=^|[\x09\x20\x2D])./',
						array($this, '_parseHeadersCallback'),
						strtolower(trim($match[1]))
					);

					if ( isset($aReturn[$match[1]]))
					{
						if (is_array($aReturn[$match[1]]))
						{
							$aReturn[$match[1]][] = trim($match[2]);
						}
						else
						{
							$aReturn[$match[1]] = array($aReturn[$match[1]], $match[2]);
						}
					}
					else
					{
						$aReturn[$match[1]] = trim($match[2]);
					}
				}
				// get last status
				//elseif (!isset($aReturn['status']))
				elseif (strpos($field, 'HTTP') === 0)
				{
					$aReturn['statuses'][] = $aReturn['status'] = trim($field);
				}
			}
		}

		return $aReturn;
	}

	/**
	 * Sanitize Header Value
	 * @param string $value
	 * @return string
	 */
	static public function sanitizeHeader($value)
	{
		return str_replace(array("\r", "\n", "\0"), '', (string) $value);
	}

	/**
	 * Get random OS for $browser and $version
	 * @param string $browser
	 * @param string $version
	 * @return string
	 */
	static public function getOs($browser, $version = NULL)
	{
		is_null($version) && $browser == 'firefox'
			&& $version = rand(110, 128) . '.' . rand(0, 9);

		switch (rand(0, 2))
		{
			// Windows
			case 0:
				$os = 'Windows NT 10.0';

				$os .= $browser == 'firefox'
					? "; Win64; x64; rv:{$version}"
					: Core_Array::randomValue(array('', '; Win64; x64', '; WOW64'));
			break;
			// MacOS
			case 1:
				$os = 'Macintosh; Intel Mac OS X ';

				$os .= $browser == 'firefox'
					// Mozilla/5.0 (Macintosh; Intel Mac OS X 14.5; rv:128.0)
					? rand(10, 14) . '.' . rand(0, 5) . "; rv:{$version}"
					// Mozilla/5.0 (Macintosh; Intel Mac OS X 14_5)
					: rand(10, 14) . '_' . rand(0, 5);
			break;
			// Linux
			case 2:
				$os = 'X11';

				$os .= $browser == 'chrome'
					? '; Linux x86_64'
					: Core_Array::randomValue(array('; Ubuntu; Linux i686', '; Ubuntu; Linux x86_64', '; Fedora; Linux x86_64')) . "; rv:{$version}";
			break;
		}

		return $os;
	}

	/**
	 * Get random User Agent
	 * @param string|NULL $browser 'chrome' or 'firefox', random if NULL
	 * @return string
	 */
	static public function generateUserAgent($browser = NULL)
	{
		is_null($browser)
			&& $browser = rand(0, 1) ? 'chrome' : 'firefox';

		if ($browser == 'chrome')
		{
			$version = rand(110, 128) . '.0.' . rand(1000, 5000) . '.' . rand(10, 400);
			$os = self::getOs($browser, $version);
			$appleVersion = (rand(0, 1) ? rand(533, 537) : rand(600, 603)) . '.' . rand(30, 50);
			$userAgent = "Mozilla/5.0 ({$os}) AppleWebKit/{$appleVersion} (KHTML, like Gecko) Chrome/{$version} Safari/{$appleVersion}";
		}
		elseif ($browser == 'firefox')
		{
			$version = rand(110, 128) . '.' . rand(0, 9);
			$os = self::getOs($browser, $version);
			$userAgent = "Mozilla/5.0 ({$os}) Gecko/20100101 Firefox/{$version}";
		}
		else
		{
			$userAgent = '';
		}

		return $userAgent;
	}

	/**
	 * POST of requestParseBody()
	 * @var array
	 */
	static protected $_requestParseBodyPost = array();

	/**
	 * FILE of requestParseBody()
	 * @var array
	 */
	static protected $_requestParseBodyFile = array();

	/**
	 * Parse and consume php://input and return the values for $_POST and $_FILES variables.
	 *
	 * @param string $boundary
	 * @return array Array with key 0 being the post data (similar to $_POST), and key 1 being the files ($_FILES).
	 */
	static public function requestParseBody($boundary)
	{
		self::$_requestParseBodyPost = self::$_requestParseBodyFile = array();

		if ($fp = @fopen("php://input", "r"))
		{
			$prevTail = '';

			// Read the data 4KB
			while ($chunk = fread($fp, 4096))
			{
				$prevTail = self::_parseChunk($prevTail . $chunk, $boundary);
			}

			fclose($fp);
		}

		$return = array(self::$_requestParseBodyPost, self::$_requestParseBodyFile);

		self::$_requestParseBodyPost = self::$_requestParseBodyFile = array();

		return $return;
	}

	/**
	 * Pointer for _parseChunk()
	 * @var NULL|string|resource
	 */
	static protected $_parseFieldLink = NULL;

	/**
	 * Parse chunk for requestParseBody()
	 * @param string $chunk
	 * @param string $boundary
	 * @return rest of chunk
	 */
	static protected function _parseChunk($chunk, $boundary)
	{
		$eol = "\r\n";
		$boundaryLine = "--" . $boundary /*. $eol*/;

		do {
			$boundaryPos = strpos($chunk, $boundaryLine);

			if (!is_null(self::$_parseFieldLink))
			{
				$content = $boundaryPos !== FALSE
					? substr($chunk, 0, $boundaryPos - 2)
					: $chunk;

				if (is_resource(self::$_parseFieldLink))
				{
					@fwrite(self::$_parseFieldLink, $content);
				}
				else
				{
					self::$_parseFieldLink .= $content;
				}
			}

			if ($boundaryPos !== FALSE)
			{
				// Reset pointer and close file
				is_resource(self::$_parseFieldLink) && fclose(self::$_parseFieldLink);

				$value = NULL;
				self::$_parseFieldLink = & $value;

				$dataPos = strpos($chunk, $eol . $eol, $boundaryPos + strlen($boundaryLine) + 2);
				if ($dataPos !== FALSE)
				{
					$chunk = substr($chunk, $boundaryPos + strlen($boundaryLine));

					// $dataPos была расчитана относительно смещения $boundaryPos + strlen($boundaryLine)
					$dataPos -= ($boundaryPos + strlen($boundaryLine));

					$headerData = substr($chunk, 2, $dataPos);

					$chunk = substr($chunk, $dataPos + 4);

					$fieldFilename = $fieldIndex = NULL;
					preg_match('/^(.+); *name="([^"]+)"(; *filename="([^"]+)")?/', $headerData, $matches);

					if (isset($matches[2]))
					{
						list(, $fieldType, $fieldName) = $matches;
						isset($matches[4]) && $fieldFilename = $matches[4];

						if (is_null($fieldFilename))
						{
							// aaaa[xxx] только для POST
							preg_match('/^(.*?)\[(.*?)\]$/u', $fieldName, $match);
							if (isset($match[2]))
							{
								$fieldName = $match[1];
								$fieldIndex = $match[2];
							}

							if (is_null($fieldIndex))
							{
								self::$_requestParseBodyPost[$fieldName] = '';
								self::$_parseFieldLink = & self::$_requestParseBodyPost[$fieldName];
							}
							else
							{
								$fieldIndex = max(array_keys(
									isset(self::$_requestParseBodyPost[$fieldName]) && count(self::$_requestParseBodyPost[$fieldName])
										? self::$_requestParseBodyPost[$fieldName]
										: array(-1 => '')
								)) + 1;

								self::$_requestParseBodyPost[$fieldName][$fieldIndex] = '';
								self::$_parseFieldLink = & self::$_requestParseBodyPost[$fieldName][$fieldIndex];
							}
						}
						else
						{
							$temp_file = tempnam(sys_get_temp_dir(), 'restapi');

							self::$_parseFieldLink = NULL;

							$aTmp = array(
								'name' => $fieldFilename,
								'tmp_name' => $temp_file
							);
							self::$_requestParseBodyFile[$fieldName] = $aTmp;

							self::$_parseFieldLink = @fopen($temp_file, 'w');
						}
					}
				}
				else
				{
					break;
				}
			}
			else
			{
				$chunk = '';
			}
		}
		while ($boundaryPos !== FALSE);

		return $chunk;
	}
}