<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Image helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			self::$_config = $aConfig = Core::$config->get('core_image', array());

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
	 * @param string $path
	 * @return mixed
	 */
	static public function exifImagetype($path)
	{
		// Uploaded file doesn't have extension
		if (is_uploaded_file($path)
			|| Core_File::isValidExtension($path, Core_File::getResizeExtensions())
			|| strpos($path, CMS_FOLDER . TMP_DIR) === 0
			|| in_array(Core_File::getExtension($path), array('tmp', 'dat'))
		)
		{
			if (function_exists('exif_imagetype'))
			{
				return @exif_imagetype($path);
			}

			$type = Core_Image::instance()->getImageType($path);

			return $type ? $type : FALSE;
		}

		return FALSE;
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

	/**
	 * Create avatar
	 * @param string $initials
	 * @param string $bgColor
	 * @param integer $width
	 * @param integer $height
	 */
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

		if (strlen((string) $initials))
		{
			$bbox = imagettfbbox($fontSize, 0, $font, $initials);

			// Text
			$textColor = imagecolorallocate($image, 255, 255, 255);

			imagettftext($image, $fontSize, 0, intval(($width - $bbox[4]) / 2), intval(($height - $bbox[5]) / 2), $textColor, $font, $initials);
		}

		// Output the image.
		header('Pragma: public');
		header('Cache-Control: public, max-age=86400');
		header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 86400) . ' GMT');

		$etag = sha1($initials . ' ' . $bgColor . ' ' . $width . ' ' . $height . ' ');

		if (
			isset($_SERVER['HTTP_IF_NONE_MATCH']) &&
			trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') === $etag
		) {
			header("HTTP/1.1 304 Not Modified");
			exit;
		}

		header("Content-type: image/png");
		header('ETag: "' . $etag . '"');

		imagepng($image);
		imagedestroy($image);

		exit();
	}
}