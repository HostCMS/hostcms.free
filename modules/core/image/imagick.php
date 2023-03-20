<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * ImageMagick
 *
 * http://www.php.net/manual/book.imagick.php
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Image_Imagick extends Core_Image
{
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
		$maxWidth = intval($maxWidth);

		$maxHeight = intval($maxHeight);

		$picsize = self::getImageSize($sourceFile);

		if (!$picsize)
		{
			throw new Core_Exception("Get the size of an image error.");
		}

		$sourceX = $picsize['width'];
		$sourceY = $picsize['height'];

		if ($sourceX > $maxWidth || $sourceY > $maxHeight)
		{
			if ($preserveAspectRatio)
			{
				$destX = $sourceX;
				$destY = $sourceY;

				// Масштабируем сначала по X
				if ($destX > $maxWidth && $maxWidth != 0)
				{
					$coefficient = $sourceY / $sourceX;
					$destX = $maxWidth;
					$destY = $maxWidth * $coefficient;
				}

				// Масштабируем по Y
				if ($destY > $maxHeight && $maxHeight != 0)
				{
					$coefficient = $sourceX / $sourceY;
					$destX = $maxHeight * $coefficient;
					$destY = $maxHeight;
				}
			}
			else
			{
				$destX = $sourceX;
				$destY = $sourceY;

				// Через пропорцию высчитываем какое измерение нужно уменьшать
				if ($sourceX != 0 && $sourceY * $maxWidth / $sourceX > $maxHeight)
				{
					// Масштабируем сначала по X
					if ($destX > $maxWidth && $maxWidth != 0)
					{
						$coefficient = $sourceY / $sourceX;
						$destX = $maxWidth;
						$destY = $maxWidth * $coefficient;
					}
				}
				// division by zero
				elseif ($sourceY != 0)
				{
					// Масштабируем по Y
					if ($destY > $maxHeight && $maxHeight != 0)
					{
						$coefficient = $sourceX / $sourceY;
						$destX = $maxHeight * $coefficient;
						$destY = $maxHeight;
					}
				}
			}

			// в $destX и $destY теперь хранятся размеры оригинального изображения после уменьшения
			// от них рассчитываем размеры для обрезания на втором шаге
			$destX_step2 = $maxWidth;
			// Масштабируем сначала по X
			if ($destX > $maxWidth && $maxWidth != 0)
			{
				// Позиции, с которых необходимо вырезать
				$src_x = ceil(($destX - $maxWidth) / 2);
			}
			else
			{
				$src_x = 0;
			}

			// Масштабируем по Y
			if ($destY > $maxHeight && $maxHeight != 0)
			{
				$destY_step2 = $maxHeight;
				$destX_step2 = $destX;

				// Позиции, с которых необходимо вырезать
				$src_y = ceil(($destY - $maxHeight) / 2);
			}
			else
			{
				$destY_step2 = $destY;
				$src_y = 0;
			}

			$ext = Core_File::getExtension($targetFile);
			$oImagick = new Imagick($sourceFile);
			if ($ext == 'jpg' || $ext == 'jpeg')
			{
				$oImagick->setImageCompression(Imagick::COMPRESSION_JPEG);
				$oImagick->setImageCompressionQuality(is_null($quality) ? (defined('JPG_QUALITY') ? JPG_QUALITY : 60) : intval($quality));
			}
			elseif ($ext == 'png')
			{
				$oImagick->setImageCompression(Imagick::COMPRESSION_ZIP);
				$oImagick->setImageCompressionQuality(is_null($quality) ? (defined('PNG_QUALITY') ? PNG_QUALITY : 6) : intval($quality));
			}
			elseif ($ext == 'webp')
			{
				$oImagick->setImageFormat('webp');
				$oImagick->setImageCompressionQuality(is_null($quality) ? (defined('WEBP_QUALITY') ? WEBP_QUALITY : 80) : intval($quality));
			}
			elseif ($ext == 'gif'){}
			else
			{
				$oImagick->clear();
				$oImagick->destroy();
				return FALSE;
			}

			$oImagick->resizeimage($destX, $destY, 0, 1, FALSE);

			if (is_array(self::$_config['imagick']['sharpenImage']))
			{
				self::$_config['imagick']['sharpenImage'] += array('radius' => 0, 'sigma' => 1);

				$oImagick->sharpenImage(floatval(self::$_config['imagick']['sharpenImage']['radius']), floatval(self::$_config['imagick']['sharpenImage']['sigma']));
			}
			elseif (is_array(self::$_config['imagick']['adaptiveSharpenImage']))
			{
				self::$_config['imagick']['adaptiveSharpenImage'] += array('radius' => 0, 'sigma' => 1);

				$oImagick->adaptiveSharpenImage(floatval(self::$_config['imagick']['adaptiveSharpenImage']['radius']), floatval(self::$_config['imagick']['adaptiveSharpenImage']['sigma']));
			}

			// Удаляем метаданные
			$oImagick->stripImage();

			if ($preserveAspectRatio)
			{
				$oImagick->writeImage($targetFile);
				$oImagick->clear();
				$oImagick->destroy();
			}
			else
			{
				if ($destX_step2 == 0 || $destY_step2 == 0)
				{
					return FALSE;
				}

				$oImagick->cropImage($destX_step2, $destY_step2, $src_x, $src_y);
				// Удаляем канвас
				$oImagick->setImagePage(0, 0, 0, 0);
				$oImagick->writeImage($targetFile);
				$oImagick->clear();
				$oImagick->destroy();
			}

			@chmod($targetFile, CHMOD_FILE);
		}
		else
		{
			Core_File::copy($sourceFile, $targetFile);
		}

		return TRUE;
	}

	/**
	 * Добавление watermark на изображение. Если файл watermark не существует, метод скопирует исходное изображение
	 *
	 * @param string $source путь к файлу источнику
	 * @param string $target путь к файлу получателю
	 * @param string $watermark путь к файлу watermark в формате PNG
	 * @param string $watermarkX позиция по оси X (в пикселях или процентах)
	 * @param string $watermarkY позиция по оси Y (в пикселях или процентах)
	 * <code>
	 * <?php
	 * $source = CMS_FOLDER . 'file1.jpg';
	 * $target = CMS_FOLDER . 'file2.jpg';
	 * $watermark = CMS_FOLDER . 'information_system_watermark1.png';
	 *
	 * Core_Image::instance()->addWatermark($source, $target, $watermark);
	 * ?>
	 * </code>
	 * @return bool
	 */
	static public function addWatermark($source, $target, $watermark, $watermarkX = NULL, $watermarkY = NULL)
	{
		$return = FALSE;

		if (Core_File::isFile($watermark))
		{
			$sourceImage = new Imagick($source);
			$watermarkImage = new Imagick($watermark);

			$ext = Core_File::getExtension($target);
			if ($ext == 'jpg' || $ext == 'jpeg')
			{
				$sourceImage->setImageCompression(Imagick::COMPRESSION_JPEG);
				$sourceImage->setImageCompressionQuality(JPG_QUALITY);
			}
			elseif ($ext == 'webp')
			{
				$sourceImage->setImageFormat('webp');
				$sourceImage->setImageCompressionQuality(defined('WEBP_QUALITY') ? WEBP_QUALITY : 80);
			}
			elseif ($ext == 'png')
			{
				$sourceImage->setImageCompression(Imagick::COMPRESSION_ZIP);
				$sourceImage->setImageCompressionQuality(PNG_QUALITY);
			}
			elseif ($ext == 'gif') {}
			else
			{
				$sourceImage->clear();
				$sourceImage->destroy();
				$sourceImage->clear();
				$sourceImage->destroy();
				return FALSE;
			}

			if (!is_null($watermarkX))
			{
				// Если передан атрибут в %-ах
				if (preg_match("/^([0-9]*)%$/", $watermarkX, $regs))
				{
					// Вычисляем позицию в %-х
					$watermarkX = $regs[1] > 0
						? ($sourceImage->getImageWidth() - $watermarkImage->getImageWidth()) * ($regs[1] / 100)
						: 0;
				}
			}

			if (!is_null($watermarkY))
			{
				// Если передан атрибут в %-ах
				if (preg_match("/^([0-9]*)%$/", $watermarkY, $regs))
				{
					// Вычисляем позицию в %-х
					$watermarkY = $regs[1] > 0
						? ($sourceImage->getImageHeight() - $watermarkImage->getImageHeight()) * ($regs[1] / 100)
						: 0;
				}
			}

			$watermarkX < 0 && $watermarkX = 0;
			$watermarkY < 0 && $watermarkY = 0;

			$sourceImage->compositeImage($watermarkImage, imagick::COMPOSITE_OVER, $watermarkX, $watermarkY);
			// Удаляем метаданные
			$sourceImage->stripImage();
			$sourceImage->writeImage($target);
			$sourceImage->clear();
			$sourceImage->destroy();
			$watermarkImage->clear();
			$watermarkImage->destroy();
			@chmod($target, CHMOD_FILE);
			$return = TRUE;
		}
		else
		{
			if ($source != $target)
			{
				Core_File::copy($source, $target);
				$return = TRUE;
			}
		}

		return $return;
	}

	/**
	 * Get image size
	 * @param string $path path
	 * @return mixed
	 */
	static public function getImageSize($path)
	{
		if (Core_File::isFile($path) && is_readable($path) && filesize($path) > 12 && self::exifImagetype($path))
		{
			$oImagick = new Imagick($path);

			return array(
				'width' => $oImagick->getImageWidth(), 'height' => $oImagick->getImageHeight()
			);
		}

		return NULL;
	}

	/**
	 * Supported Image Formats
	 * https://imagemagick.org/script/formats.php
	 * @var array
	 */
	static protected $_aFormats = array(
		'GIF' => 1,
		'JPEG' => 2,
		'PNG' => 3,
		'PNG8' => 3,
		'PNG00' => 3,
		'PNG24' => 3,
		'PNG32' => 3,
		'PNG48' => 3,
		'PNG64' => 3,
		'PSD' => 5,
		'BMP' => 6,
		'BMP2' => 6,
		'BMP3' => 6,
		'TIFF' => 7,
		'JP2' => 10,
		'JPT' => 10,
		'J2C' => 10,
		'J2K' => 10,
		'WBMP' => 15,
		'XBM' => 16,
		'ICO' => 17,
		'WEBP' => 18
	);

	/**
	 * Get Image Type: 0 = UNKNOWN, 1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF (orden de bytes intel), 8 = TIFF (orden de bytes motorola),
	 * 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM, 17 = ICO, 18 = WEBP
	 * @param string $path
	 * @return mixed
	 */
	static public function getImageType($path)
	{
		$oImagick = new Imagick($path);
		$format = $oImagick->getImageFormat();

		return isset(self::$_aFormats[$format])
			? self::$_aFormats[$format]
			: 0;
	}

	/**
	 * Get ImageMagick version
	 * @return string
	 */
	static public function getIMVersion()
	{
		$im = new Imagick();
		return Core_Array::get($im->getVersion(), 'versionString', '');
	}
}