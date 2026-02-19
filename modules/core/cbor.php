<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Concise Binary Object Representation (CBOR) is a binary data serialization format loosely based on JSON
 * Modified version of https://github.com/madwizard-org/webauthn-server/blob/master/src/Format/CborDecoder.php
 * Copyright © 2018 Thomas Bleeker - MIT licensed
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Cbor
{
	/**
	 * Decode string
	 * @param Core_Bytebuffer|string $bufOrBin
	 * @return mixed
	 */
	public static function decode($bufOrBin)
	{
		$buf = $bufOrBin instanceof Core_Bytebuffer
			? $bufOrBin
			: new Core_Bytebuffer($bufOrBin);

		$offset = 0;

		$result = self::_parseItem($buf, $offset);

		if ($offset !== $buf->getLength())
		{
			throw new Core_Exception('Unused bytes after data item.');
		}

		return $result;
	}

	/**
	 * @param Core_Bytebuffer|string $bufOrBin
	 * @param int $startOffset
	 * @param int|NULL $endOffset
	 * @return mixed
	 */
	public static function decodeInPlace($bufOrBin, $startOffset, &$endOffset = NULL)
	{
		$buf = $bufOrBin instanceof Core_Bytebuffer
			? $bufOrBin
			: new Core_Bytebuffer($bufOrBin);

		$offset = $startOffset;

		$data = self::_parseItem($buf, $offset);

		$endOffset = $offset;

		return $data;
	}

	/**
	 * @param Core_Bytebuffer $buf
	 * @param int $offset
	 * @return mixed
	 */
	static protected function _parseItem(Core_Bytebuffer $buf, &$offset)
	{
		$first = $buf->getByteVal($offset++);
		$type = $first >> 5;
		$val = $first & 0b11111;

		if ($type === 7) // FLOAT SIMPLE
		{
			return self::_parseFloatSimple($val, $buf, $offset);
		}

		$val = self::_parseExtraLength($val, $buf, $offset);

		return self::_parseItemData($type, $val, $buf, $offset);
	}

	/**
	 * Parse float simple
	 * @param string $val
	 * @param Core_Bytebuffer $buf
	 * @param int $offset
	 * @return mixed
	 */
	static protected function _parseFloatSimple($val, Core_Bytebuffer $buf, &$offset)
	{
		switch ($val)
		{
			case 24:
				$val = $buf->getByteVal($offset);
				$offset++;
				return self::_parseSimple($val);

			case 25:
				$floatValue = $buf->getHalfFloatVal($offset);
				$offset += 2;
				return $floatValue;

			case 26:
				$floatValue = $buf->getFloatVal($offset);
				$offset += 4;
				return $floatValue;

			case 27:
				$floatValue = $buf->getDoubleVal($offset);
				$offset += 8;
				return $floatValue;

			case 28:
			case 29:
			case 30:
				throw new Core_Exception('Reserved value used.');
			case 31:
				throw new Core_Exception('Indefinite length is not supported.');
		}

		return self::_parseSimple($val);
	}

    /**
     * Parse simple
     * @param int $val
     * @return bool|null
     * @throws Core_Exception
     */
	static protected function _parseSimple($val)
	{
		if ($val === 20)
		{
			return false;
		}

		if ($val === 21)
		{
			return true;
		}

		if ($val === 22)
		{
			return NULL;
		}

		throw new Core_Exception(sprintf('Unsupported simple value %d.', $val));
	}

    /**
     * Parse extra length
     * @param string $val
     * @param Core_Bytebuffer $buf
     * @param int $offset
     * @return int|string
     * @throws Core_Exception
     */
	static protected function _parseExtraLength($val, Core_Bytebuffer $buf, &$offset)
	{
		switch ($val)
		{
			case 24:
				$val = $buf->getByteVal($offset);
				$offset++;
			break;

			case 25:
				$val = $buf->getUint16Val($offset);
				$offset += 2;
			break;

			case 26:
				$val = $buf->getUint32Val($offset);
				$offset += 4;
			break;

			case 27:
				$val = $buf->getUint64Val($offset);
				$offset += 8;
			break;
			case 28:
			case 29:
			case 30:
				throw new Core_Exception('Reserved value used.');
			case 31:
				throw new Core_Exception('Indefinite length is not supported.');
		}

		return $val;
	}

	/**
	 * Parse item data
	 * @param int $type
	 * @param string $val
	 * @param Core_Bytebuffer $buf
	 * @param int $offset
	 * @return mixed
	 */
	static protected function _parseItemData($type, $val, Core_Bytebuffer $buf, &$offset)
	{
		switch ($type)
		{
			case 0: // UNSIGNED INT
				return $val;

			case 1: // NEGATIVE INT
				return -1 - $val;

			case 2: // BYTE STRING
				$data = $buf->getBytes($offset, $val);
				$offset += $val;
				return new Core_Bytebuffer($data); // bytes

			case 3: // TEXT STRING
				$data = $buf->getBytes($offset, $val);
				$offset += $val;
				return $data; // UTF-8

			case 4: // ARRAY
				return self::_parseArray($buf, $offset, $val);

			case 5: // MAP
				return self::_parseMap($buf, $offset, $val);

			case 6: // TAG
				return self::_parseItem($buf, $offset); // 1 embedded data item
		}

		// This should never be reached
		throw new Core_Exception(sprintf('Unknown major type %d', $type));
	}

	/**
	 * Parse map
	 * @param Core_Bytebuffer $buf
	 * @param int $offset
	 * @param int $count
	 * @return array
	 */
	static protected function _parseMap(Core_Bytebuffer $buf, &$offset, $count)
	{
		$map = array();

		for ($i = 0; $i < $count; $i++)
		{
			$mapKey = self::_parseItem($buf, $offset);
			$mapVal = self::_parseItem($buf, $offset);

			if (!is_int($mapKey) && !is_string($mapKey))
			{
				throw new Core_Exception('Can only use strings or integers as map keys');
			}

			$map[$mapKey] = $mapVal;
		}

		return $map;
	}

	/**
	 * Parse array
	 * @param Core_Bytebuffer $buf
	 * @param int $offset
	 * @param int $count
	 * @return array
	 */
	static protected function _parseArray(Core_Bytebuffer $buf, &$offset, $count)
	{
		$arr = array();

		for ($i = 0; $i < $count; $i++)
		{
			$arr[] = self::_parseItem($buf, $offset);
		}

		return $arr;
	}
}