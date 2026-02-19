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
 * @copyright © 2005-2026, https://www.hostcms.ru
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
	 * @param string|NULL $outputFormat формат, в котором сохранять изображение, по умолчанию NULL равен формату исходного
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
	public function resizeImage($sourceFile, $maxWidth, $maxHeight, $targetFile, $quality = NULL, $preserveAspectRatio = TRUE, $outputFormat = NULL)
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

		$iSourceImagetype = self::exifImagetype($sourceFile);

		// Change output format
		$iDestImagetype = !is_null($outputFormat)
			? self::getImagetypeByFormat($outputFormat)
			: $iSourceImagetype;

		if ($sourceX > $maxWidth || $sourceY > $maxHeight)
		{
			$aConfig = self::$_config['imagick'] + array(
				'sharpenImage' => array(
					'radius' => 0,
					'sigma' => 1
				),
				'adaptiveSharpenImage' => array(
					'radius' => 0,
					'sigma' => 1
				)
			);

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

			$destX = intval($destX);
			$destY = intval($destY);

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

			$oImagick = new Imagick($sourceFile);

			// PNG => another types
			$iSourceImagetype == IMAGETYPE_PNG && $iSourceImagetype != $iDestImagetype
				&& $oImagick->setBackgroundColor(new ImagickPixel('#FFFFFF'));

			if ($iDestImagetype == IMAGETYPE_JPEG)
			{
				$oImagick->setImageFormat('jpg');
				$oImagick->setImageCompression(Imagick::COMPRESSION_JPEG);
				$oImagick->setImageCompressionQuality(is_null($quality) ? (defined('JPG_QUALITY') ? JPG_QUALITY : 60) : intval($quality));
			}
			elseif ($iDestImagetype == IMAGETYPE_PNG)
			{
				$oImagick->setImageFormat('png');
				$oImagick->setImageCompression(Imagick::COMPRESSION_ZIP);
				$oImagick->setImageCompressionQuality(is_null($quality) ? (defined('PNG_QUALITY') ? PNG_QUALITY : 6) : intval($quality));
			}
			elseif (defined('IMAGETYPE_WEBP') && $iDestImagetype == IMAGETYPE_WEBP)
			{
				$oImagick->setImageFormat('webp');
				$oImagick->setImageCompressionQuality(is_null($quality) ? (defined('WEBP_QUALITY') ? WEBP_QUALITY : 80) : intval($quality));
			}
			elseif ($iDestImagetype == IMAGETYPE_GIF)
			{
				// Detect animated GIF
				if ($oImagick->getNumberImages() < 2)
				{
					$oImagick->setImageFormat('gif');
				}
				else
				{
					Core_File::copy($sourceFile, $targetFile);
					return TRUE;
				}
			}
			else
			{
				$oImagick->clear();
				$oImagick->destroy();
				return FALSE;
			}

			$oImagick->resizeimage($destX, $destY, 0, 1, FALSE);

			if (is_array($aConfig['sharpenImage']))
			{
				$aConfig['sharpenImage'] += array('radius' => 0, 'sigma' => 1);

				$oImagick->sharpenImage(floatval($aConfig['sharpenImage']['radius']), floatval($aConfig['sharpenImage']['sigma']));
			}
			elseif (is_array($aConfig['adaptiveSharpenImage']))
			{
				$aConfig['adaptiveSharpenImage'] += array('radius' => 0, 'sigma' => 1);

				$oImagick->adaptiveSharpenImage(floatval($aConfig['adaptiveSharpenImage']['radius']), floatval($aConfig['adaptiveSharpenImage']['sigma']));
			}

			// Save ICC-profile
			$aProfiles = $oImagick->getImageProfiles("*", true);
			$iccProfile = isset($aProfiles['icc']) ? $aProfiles['icc'] : NULL;

			// Удаляем метаданные
			$oImagick->stripImage();

			if ($iccProfile)
			{
				// restore ICC
				$oImagick->profileImage("icc", $iccProfile);
			}

			if (!$preserveAspectRatio)
			{
				if ($destX_step2 == 0 || $destY_step2 == 0)
				{
					return FALSE;
				}

				$oImagick->cropImage($destX_step2, $destY_step2, $src_x, $src_y);
				// Удаляем канвас
				$oImagick->setImagePage(0, 0, 0, 0);
			}

			$oImagick->writeImage($targetFile);
			$oImagick->clear();
			$oImagick->destroy();

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
	 * @param string|NULL $outputFormat формат, в котором сохранять изображение, по умолчанию NULL равен формату исходного
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
	public function addWatermark($source, $target, $watermark, $watermarkX = NULL, $watermarkY = NULL, $outputFormat = NULL)
	{
		$return = FALSE;

		if (Core_File::isFile($watermark))
		{
			$oImagick = new Imagick($source);
			$watermarkImage = new Imagick($watermark);

			$iSourceImagetype = self::exifImagetype($source);

			// Change output format
			$iDestImagetype = !is_null($outputFormat)
				? self::getImagetypeByFormat($outputFormat)
				: $iSourceImagetype;

			if (!is_null($watermarkX))
			{
				// Если передан атрибут в %-ах
				if (preg_match("/^([0-9]*)%$/", $watermarkX, $regs))
				{
					// Вычисляем позицию в %-х
					$watermarkX = $regs[1] > 0
						? ($oImagick->getImageWidth() - $watermarkImage->getImageWidth()) * ($regs[1] / 100)
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
						? ($oImagick->getImageHeight() - $watermarkImage->getImageHeight()) * ($regs[1] / 100)
						: 0;
				}
			}

			$watermarkX = intval($watermarkX);
			$watermarkY = intval($watermarkY);

			$watermarkX < 0 && $watermarkX = 0;
			$watermarkY < 0 && $watermarkY = 0;

			$oImagick->compositeImage($watermarkImage, Imagick::COMPOSITE_OVER, $watermarkX, $watermarkY);

			$watermarkImage->clear();
			$watermarkImage->destroy();

			// PNG => another types
			$iSourceImagetype == IMAGETYPE_PNG && $iSourceImagetype != $iDestImagetype
				&& $oImagick->setBackgroundColor(new ImagickPixel('#FFFFFF'));

			if ($iDestImagetype == IMAGETYPE_JPEG)
			{
				$oImagick->setImageFormat('jpg');
				$oImagick->setImageCompression(Imagick::COMPRESSION_JPEG);
				$oImagick->setImageCompressionQuality(defined('JPG_QUALITY') ? JPG_QUALITY : 60);
			}
			elseif ($iDestImagetype == IMAGETYPE_PNG)
			{
				$oImagick->setImageFormat('png');
				$oImagick->setImageCompression(Imagick::COMPRESSION_ZIP);
				$oImagick->setImageCompressionQuality(defined('PNG_QUALITY') ? PNG_QUALITY : 6);
			}
			elseif (defined('IMAGETYPE_WEBP') && $iDestImagetype == IMAGETYPE_WEBP)
			{
				$oImagick->setImageFormat('webp');
				$oImagick->setImageCompressionQuality(defined('WEBP_QUALITY') ? WEBP_QUALITY : 80);
			}
			elseif ($iDestImagetype == IMAGETYPE_GIF)
			{
				$oImagick->setImageFormat('gif');
			}
			else
			{
				$oImagick->clear();
				$oImagick->destroy();
				return FALSE;
			}

			// Удаляем метаданные
			$oImagick->stripImage();

			$oImagick->writeImage($target);

			$oImagick->clear();
			$oImagick->destroy();

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
	 * @return array|null
     */
	public function getImageSize($path)
	{
		if (Core_File::isFile($path) && is_readable($path) && filesize($path) > 12 && self::exifImagetype($path))
		{
			$oImagick = new Imagick($path);

			$result = [
				'width' => $oImagick->getImageWidth(),
				'height' => $oImagick->getImageHeight()
			];

			$oImagick->clear();
			$oImagick->destroy();

			return $result;
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
	public function getImageType($path)
	{
		$oImagick = new Imagick($path);
		$format = $oImagick->getImageFormat();

		$oImagick->clear();
		$oImagick->destroy();

		return isset(self::$_aFormats[$format])
			? self::$_aFormats[$format]
			: 0;
	}

	/**
	 * Get ImageMagick version
	 * @return string
	 */
	public function getIMVersion()
	{
		$im = new Imagick();
		return Core_Array::get($im->getVersion(), 'versionString', '');
	}

	/**
	 * Check Imagick-Module Availability
	 * @return bool
	 */
	public function isAvailable()
	{
		return class_exists('Imagick');
	}
}