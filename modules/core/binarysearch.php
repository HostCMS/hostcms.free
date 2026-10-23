<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Binarysearch
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Binarysearch
{
	/**
	 * String for binary search
	 * @var string
	 */
	protected $_packedString = '';

	/**
	 * Key len
	 * @var int
	 */
	protected $_keyLen;

	/**
	 * Value len
	 * @var int
	 */
	protected $_valueLen;

	/**
	 * Record len: key + value len
	 * @var int
	 */
	protected $_recordLen;

	/**
	 * Key pack method, default 'md5;
	 */
	protected $_pack = 'md5';

	/**
	 * Temporary Buffer
	 * @var array
	 */
	protected $_temporaryBuffer = array();

	/**
	 * String is assembled
	 * @var boolean
	 */
	protected $isAssembled = false;

	/**
	 * Конструктор, длины ключа и значения в байтах
	 * @param $keyLen
	 * @param $valueLen
	 * @param $pack default md5
	 */
	public function __construct($keyLen = 16, $valueLen = 32, $pack = 'md5')
	{
		$this->_keyLen = $keyLen;
		$this->_valueLen = $valueLen;
		$this->_recordLen = $keyLen + $valueLen;
		$this->_pack = $pack;
	}

	/**
	 * Add key:value into array
	 * @param string $key
	 * @param string $value
	 * @return self
	 */
	public function add($key, $value)
	{
		$this->isAssembled = false;

		$key = $this->pack($key);

		// Принудительно приводим ключ к строке, чтобы избежать числовых индексов PHP
		$this->_temporaryBuffer[(string)$key] = $value;

		return $this;
	}

	/**
	 * Set data array
	 * @param array $rawData
	 * @return self
	 */
	public function set(array $rawData)
	{
		$this->isAssembled = false;
		$this->_temporaryBuffer = $rawData;

		return $this;
	}

	/**
	 * Pack key
	 * @param string $key
	 * @return string
	 */
	public function pack($key)
	{
		switch ($this->_pack)
		{
			case 'md5':
				$key = md5($key, true);
			break;
		}

		return $key;
	}

	/**
	 * Convert data to binary string
	 * @return self
	 */
	public function init()
	{
		if ($this->isAssembled || empty($this->_temporaryBuffer))
		{
			return $this;
		}

		// Сортируем по бинарным значениям байт
		uksort($this->_temporaryBuffer, 'strcmp');

		$buffer = array();
		foreach ($this->_temporaryBuffer as $key => &$value)
		{
			$valueStr = is_array($value) ? implode(',', $value) : (string)$value;

			// Безопасно отсекаем избыток байт
			$truncatedKey = substr((string)$key, 0, $this->_keyLen);
			$truncatedValue = substr($valueStr, 0, $this->_valueLen);

			//if (strlen($key) > $this->_keyLen) { echo "\n"; var_dump($key); }
			//if (strlen($valueStr) > $this->_valueLen) { echo "\n"; var_dump($valueStr); }

			// Теперь str_pad гарантированно выдаст ровные блоки, без сдвигов математики
			$paddedKey = str_pad($truncatedKey, $this->_keyLen, "\x00", STR_PAD_RIGHT);
			$paddedValue = str_pad($truncatedValue, $this->_valueLen, "\x00", STR_PAD_RIGHT);

			$buffer[] = $paddedKey . $paddedValue;

			unset($this->_temporaryBuffer[$key]);
		}

		$this->_packedString = implode('', $buffer);

		$this->_temporaryBuffer = array();
		$this->isAssembled = true;
	}

	/**
	 * Find value for key
	 * @param string $key
	 * @return NULL|string
	 */
	public function find($key)
	{
		if (!$this->isAssembled)
		{
			$this->init();
		}

		$totalBytes = strlen($this->_packedString);
		if ($totalBytes === 0)
		{
			return NULL;
		}

		$totalRecords = intval(floor($totalBytes / $this->_recordLen));

		$low = 0;
		$high = $totalRecords - 1;

		$key = $this->pack($key);

		while ($low <= $high)
		{
			$mid = intval(floor(($low + $high) / 2));
			$offset = $mid * $this->_recordLen;

			// Извлекаем сырые байты ключа
			$rawKey = substr($this->_packedString, $offset, $this->_keyLen);
			// Очищаем от нулевых байтов-заполнителей
			$currentKey = rtrim($rawKey, "\x00");

			$cmp = strcmp($key, $currentKey);

			if ($cmp === 0)
			{
				// Ключ найден! Забираем значение и очищаем от нулевых байтов
				$rawValue = substr($this->_packedString, $offset + $this->_keyLen, $this->_valueLen);
				return rtrim($rawValue, "\x00");
			}

			$cmp < 0
				? $high = $mid - 1
				: $low = $mid + 1;
		}

		return NULL;
	}

	/**
	 * Write binary string into cache
	 * @param string $filePath
	 * @return self
	 */
	public function writeCache($filePath)
	{
		if (!$this->isAssembled)
		{
			$this->init();
		}

		Core_File::write($filePath, $this->_packedString);

		return $this;
	}

	/**
	 * Read binary cache
	 * @param string $filePath
	 * @return boolean
	 */
	public function readCache($filePath)
	{
		if (Core_File::isFile($filePath))
		{
			$this->_packedString = Core_File::read($filePath);

			if ($this->_packedString === FALSE)
			{
				$this->_packedString = '';
				return FALSE;
			}

			$this->_temporaryBuffer = array();
			$this->isAssembled = true;

			return TRUE;
		}

		return FALSE;
	}
}
