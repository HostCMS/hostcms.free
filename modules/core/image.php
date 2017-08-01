<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Image helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
abstract class Core_Image
{
	/**
	 * Driver's configuration
	 */
	static protected $_config = NULL;

	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = array();

	/**
	 * Get full driver name
	 * @param string $driver driver name
	 * @return srting
	 */
	static protected function _getDriverName($driver)
	{
		return __CLASS__ . '_' . ucfirst($driver);
	}

	/**
	 * Register an existing instance as a singleton.
	 * @param string $name driver's name
	 * @return object
	 */
	static public function instance($name = 'default')
	{
		if (!is_string($name))
		{
			throw new Core_Exception('Wrong argument type (expected String)');
		}

		if (!isset(self::$instance[$name]))
		{
			self::$_config = $aConfig = Core::$config->get('core_image');

			if (!isset($aConfig[$name]) || !isset($aConfig[$name]['driver']))
			{
				throw new Core_Exception('Image configuration doesn\'t defined');
			}

			$driver = self::_getDriverName($aConfig[$name]['driver']);
			self::$instance[$name] = new $driver();
		}

		return self::$instance[$name];
	}

	/**
	 * Implement exifImagetype function
	 * @param string $filename file name
	 * @return mixed
	 */
	static public function exifImagetype($filename)
	{
		if (function_exists('exif_imagetype'))
		{
			return @exif_imagetype($filename);
		}

		if ((list($width, $height, $type, $attr) = @getimagesize($filename)) !== FALSE)
		{
			return $type;
		}

		return FALSE;
	}

	/**
	 * Get image size
	 * @param string $path path
	 * @return mixed
	 */
	static public function getImageSize($path)
	{
		if (is_file($path) && is_readable($path) && filesize($path) > 12 && self::exifImagetype($path))
		{
			$picsize = @getimagesize($path);
			if ($picsize)
			{
				return array(
					'width' => $picsize[0], 'height' => $picsize[1]
				);
			}
		}

		return NULL;
	}

	/**
	 * Пропорциональное масштабирование изображения
	 *
	 * @param string $sourceFile путь к исходному файлу
	 * @param int $maxWidth максимальная ширина картинки
	 * @param int $maxHeight максимальная высота картинки
	 * @param string $targetFile путь к результирующему файлу
	 * @param int $quality качество JPEG/PNG файла, если не передано, то берется из констант
	 * @param int $preserveAspectRatio сохранять пропорции изображения
	 * <code>
	 * <?php
	 * $sourceFile = CMS_FOLDER . 'file1.jpg';
	 * $targetFile = CMS_FOLDER . 'file2.jpg';
	 *
	 * Core_Image::instance()->resizeImage($sourceFile, 100, 50, $targetFile);
	 * ?>
	 * </code>
	 * @return bool
	 */
	static public function resizeImage($sourceFile, $maxWidth, $maxHeight, $targetFile, $quality = NULL, $preserveAspectRatio = TRUE)
	{
		return Core_Image::instance()->resizeImage($sourceFile, $maxWidth, $maxHeight, $targetFile, $quality, $preserveAspectRatio);
	}
}