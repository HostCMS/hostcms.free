<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Creates a gz archive
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Gz
{
	/**
	 * Сжимает файл в GZIP
	 * @param string $source Путь к исходному файлу
	 * @param string $destination Путь к выходному файлу .gz (если null, добавит .gz к имени)
	 * @param int $level Уровень сжатия (1-9)
	 * @return bool
	 */
	static public function compress($source, $destination = NULL, $level = 6)
	{
		if (!file_exists($source))
		{
			return FALSE;
		}

		if (is_null($destination))
		{
			$destination = $source . '.gz';
		}

		$mode = 'wb' . $level;

		// Открываем файл на чтение
		$fp_in = fopen($source, 'rb');
		if ($fp_in === FALSE)
		{
			return FALSE;
		}

		// Открываем файл на запись (gzopen пишет сразу в gzip формат)
		// Функция gzopen/gzopen64 есть в стандартном расширении zlib
		$func = function_exists('gzopen64') ? 'gzopen64' : 'gzopen';
		$fp_out = $func($destination, $mode);

		if ($fp_out === FALSE)
		{
			fclose($fp_in);
			return FALSE;
		}

		// Копируем блоками по 64KB
		while (!feof($fp_in))
		{
			$buffer = fread($fp_in, 65536);
			gzwrite($fp_out, $buffer);
		}

		fclose($fp_in);
		gzclose($fp_out);

		return TRUE;
	}
}