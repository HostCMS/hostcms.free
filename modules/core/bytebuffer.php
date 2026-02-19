<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Buffer to work with binary data
 * Modified version of https://github.com/madwizard-thomas/webauthn-server/blob/master/src/Format/ByteBuffer.php
 * Copyright © 2018 Thomas Bleeker - MIT licensed
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Bytebuffer implements JsonSerializable, Serializable
{
	/**
	 * @var bool
	 */
	static public $useBase64UrlEncoding = FALSE;

	/**
	 * @var string
	 */
	protected $_data;

	/**
	 * @var int
	 */
	protected $_length;

	/**
	 * Constructor
	 * @param string $binaryData
	 */
	public function __construct($binaryData)
	{
		$this->_data = strval($binaryData);
		$this->_length = strlen($binaryData);
	}

	/**
	 * Create a ByteBuffer from a base64 url encoded string
	 * @param string $base64url
	 * @return Core_Bytebuffer
	 */
	static public function fromBase64Url($base64url)
	{
		// base64 url decode
		$bin = base64_decode(strtr($base64url, '-_', '+/') . str_repeat('=', 3 - (3 + strlen($base64url)) % 4));

		if ($bin === FALSE)
		{
			throw new Core_Exception('ByteBuffer: Invalid base64 url string');
		}

		return new Core_Bytebuffer($bin);
	}

	/**
	 * Create a ByteBuffer from a base64 url encoded string
	 * @param string $hex
	 * @return Core_Bytebuffer
	 */
	static public function fromHex($hex)
	{
		$bin = hex2bin($hex);

		if ($bin === FALSE)
		{
			throw new Core_Exception('ByteBuffer: Invalid hex string');
		}

		return new Core_Bytebuffer($bin);
	}

	/**
	 * Create a random ByteBuffer
	 * @param string $length
	 * @return Core_Bytebuffer
	 */
	static public function randomBuffer($length)
	{
		// > PHP 7.0
		if (function_exists('random_bytes'))
		{
			return new Core_Bytebuffer(random_bytes($length));
		}
		elseif (function_exists('openssl_random_pseudo_bytes'))
		{
			return new Core_Bytebuffer(openssl_random_pseudo_bytes($length));
		}
		else
		{
			throw new Core_Exception('ByteBuffer: cannot generate random bytes');
		}
	}

	/**
	 * Get bytes
	 * @param int $offset
	 * @param int $length
	 * @return string
	 */
	public function getBytes($offset, $length)
	{
		if ($offset < 0 || $length < 0 || ($offset + $length > $this->_length))
		{
			throw new Core_Exception('ByteBuffer: Invalid offset or length');
		}

		return substr($this->_data, $offset, $length);
	}

	/**
	 * Get byte val
	 * @param int $offset
	 * @return int
	 */
	public function getByteVal($offset)
	{
		if ($offset < 0 || $offset >= $this->_length)
		{
			throw new Core_Exception('ByteBuffer: Invalid offset');
		}

		return ord(substr($this->_data, $offset, 1));
	}

	/**
	 * get JSON
	 * @param int $jsonFlags
	 * @return string
	 */
	public function getJson($jsonFlags = 0)
	{
		$data = json_decode($this->getBinaryString(), NULL, 512, $jsonFlags);

		if (json_last_error() !== JSON_ERROR_NONE)
		{
			throw new Core_Exception(json_last_error_msg());
		}

		return $data;
	}

	/**
	 * Get length
	 * @return int
	 */
	public function getLength()
	{
		return $this->_length;
	}

	/**
	 * get UINT 16
	 * @param int $offset
	 * @return string
	 */
	public function getUint16Val($offset)
	{
		if ($offset < 0 || ($offset + 2) > $this->_length)
		{
			throw new Core_Exception('ByteBuffer: Invalid offset');
		}

		return unpack('n', $this->_data, $offset)[1];
	}

	/**
	 * Get UINT 32
	 * @param int $offset
	 * @return string
	 */
	public function getUint32Val($offset)
	{
		if ($offset < 0 || ($offset + 4) > $this->_length)
		{
			throw new Core_Exception('ByteBuffer: Invalid offset');
		}

		$val = unpack('N', $this->_data, $offset)[1];

		// Signed integer overflow causes signed negative numbers
		if ($val < 0)
		{
			throw new Core_Exception('ByteBuffer: Value out of integer range.');
		}

		return $val;
	}

	/**
	 * Get UINT 64
	 * @param int $offset
	 * @return string
	 */
	public function getUint64Val($offset)
	{
		if (PHP_INT_SIZE < 8)
		{
			throw new Core_Exception('ByteBuffer: 64-bit values not supported by this system');
		}

		if ($offset < 0 || ($offset + 8) > $this->_length)
		{
			throw new Core_Exception('ByteBuffer: Invalid offset');
		}

		$val = unpack('J', $this->_data, $offset)[1];

		// Signed integer overflow causes signed negative numbers
		if ($val < 0)
		{
			throw new Core_Exception('ByteBuffer: Value out of integer range.');
		}

		return $val;
	}

	/**
	 * Get half float
	 * @param int $offset
	 * @return float|int
	 */
	public function getHalfFloatVal($offset)
	{
		// FROM spec pseudo decode_half(unsigned char *halfp)
		$half = $this->getUint16Val($offset);

		$exp = ($half >> 10) & 0x1f;
		$mant = $half & 0x3ff;

		if ($exp === 0)
		{
			$val = $mant * (2 ** -24);
		}
		elseif ($exp !== 31)
		{
			$val = ($mant + 1024) * (2 ** ($exp - 25));
		}
		else
		{
			$val = ($mant === 0) ? INF : NAN;
		}

		return ($half & 0x8000) ? -$val : $val;
	}

	/**
	 * Get float
	 * @param int $offset
	 * @return string
	 */
	public function getFloatVal($offset)
	{
		if ($offset < 0 || ($offset + 4) > $this->_length)
		{
			throw new Core_Exception('ByteBuffer: Invalid offset');
		}

		return unpack('G', $this->_data, $offset)[1];
	}

	/**
	 * Get double
	 * @param int $offset
	 * @return string
	 */
	public function getDoubleVal($offset)
	{
		if ($offset < 0 || ($offset + 8) > $this->_length)
		{
			throw new Core_Exception('ByteBuffer: Invalid offset');
		}

		return unpack('E', $this->_data, $offset)[1];
	}

	/**
	 * @return string
	 */
	public function getBinaryString()
	{
		return $this->_data;
	}

	/**
	 * @param string|Core_Bytebuffer $buffer
	 * @return bool
	 */
	public function equals($buffer)
	{
		if (is_object($buffer) && $buffer instanceof Core_Bytebuffer)
		{
			return $buffer->getBinaryString() === $this->getBinaryString();

		}
		elseif (is_string($buffer))
		{
			return $buffer === $this->getBinaryString();
		}

		return FALSE;
	}

	/**
	 * @return string
	 */
	public function getHex()
	{
		return bin2hex($this->_data);
	}

	/**
	 * @return bool
	 */
	public function isEmpty()
	{
		return $this->_length === 0;
	}

	/**
	 * jsonSerialize interface
	 * return binary data in RFC 1342-Like serialized string
	 * @return string
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		// base64 url encode
		return Core_Bytebuffer::$useBase64UrlEncoding
			? rtrim(strtr(base64_encode($this->_data), '+/', '-_'), '=')
			: '=?BINARY?B?' . base64_encode($this->_data) . '?=';
	}

	/**
	 * Serializable-Interface
	 * @return string
	 */
	#[\ReturnTypeWillChange]
	public function serialize()
	{
		return serialize($this->_data);
	}

	/**
	 * Serializable-Interface
	 * @param string $serialized
	 */
	#[\ReturnTypeWillChange]
	public function unserialize($serialized)
	{
		$this->_data = unserialize($serialized);
		$this->_length = strlen($this->_data);
	}

	/**
	 * (PHP 8 deprecates Serializable-Interface)
	 * @return array
	 */
	public function __serialize()
	{
		return [
			'data' => serialize($this->_data)
		];
	}

	/**
	 * object to string
	 * @return string
	 */
	public function __toString()
	{
		return $this->getHex();
	}

	/**
	 * (PHP 8 deprecates Serializable-Interface)
	 * @param array $data
	 * @return void
	 */
	public function __unserialize($data)
	{
		if ($data && isset($data['data']))
		{
			$this->_data = unserialize($data['data']);
			$this->_length = strlen($this->_data);
		}
	}
}