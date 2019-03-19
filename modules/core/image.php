<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Image helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		// Uploaded file doesn't have extension
		if (is_uploaded_file($filename)
			|| Core_File::isValidExtension($filename, Core_File::getResizeExtensions())
			|| strpos($filename, CMS_FOLDER . TMP_DIR) === 0
			|| in_array(Core_File::getExtension($filename), array('tmp', 'dat'))
		)
		{
			if (function_exists('exif_imagetype'))
			{
				return @exif_imagetype($filename);
			}

			if ((list($width, $height, $type, $attr) = @getimagesize($filename)) !== FALSE)
			{
				return $type;
			}
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
	
	static public function avatar($initials, $bgColor = '#f44336', $width = 130, $height = 130)
	{
		// Create image
		$font = CMS_FOLDER . 'modules/skin/default/fonts/roboto-regular.ttf';
		$fontSize = 40;

		$image = imagecreatetruecolor($width, $height);

		$transparent = imagecolorallocatealpha($image, 0, 0, 0, 127);
		imagefill($image, 0, 0, $transparent);
		imagesavealpha($image, TRUE); // save alphablending setting (important);

		list($r, $g, $b) = sscanf($bgColor, "#%02x%02x%02x");

		// Draw the circle
		imagefilledellipse($image, $width / 2, $height / 2, $width, $height, imagecolorallocate($image, $r, $g, $b));

		$bbox = imagettfbbox($fontSize, 0, $font, $initials);

		// Text
		$textColor = imagecolorallocate($image, 255, 255, 255);
		imagettftext($image, $fontSize, 0, ($width - $bbox[4]) / 2, ($height - $bbox[5]) / 2, $textColor, $font, $initials);

		// Output the image.
		header("Content-type: image/png");
		imagepng($image);
		imagedestroy($image);
		
		exit();
	}
}