<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * ImageMagick
 *
 * http://www.php.net/manual/book.imagick.php
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * @return
	 */
	static public function addWatermark($source, $target, $watermark, $watermarkX = NULL, $watermarkY = NULL)
	{
		$return = FALSE;

		if (is_file($watermark))
		{
			$sourceImage = new Imagick($source);
			$watermarkImage = new Imagick($watermark);

			$ext = Core_File::getExtension($target);
			if ($ext == 'jpg' || $ext == 'jpeg')
			{
				$sourceImage->setImageCompression(Imagick::COMPRESSION_JPEG);
				$sourceImage->setImageCompressionQuality(JPG_QUALITY);
			}
			elseif ($ext == 'png')
			{
				$sourceImage->setImageCompression(Imagick::COMPRESSION_ZIP);
				$sourceImage->setImageCompressionQuality(PNG_QUALITY);
			}
			elseif ($ext == 'gif')
			{}
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
				if (preg_match("/^([0-9]*)%$/", $watermarkX, $regs) && $regs[1] > 0)
				{
					// Вычисляем позицию в %-х
					$watermarkX = ($sourceImage->getImageWidth() - $watermarkImage->getImageWidth()) * ($regs[1] / 100);
				}
			}

			if (!is_null($watermarkY))
			{
				// Если передан атрибут в %-ах
				if (preg_match("/^([0-9]*)%$/", $watermarkY, $regs) && $regs[1] > 0)
				{
					// Вычисляем позицию в %-х
					$watermarkY = ($sourceImage->getImageHeight() - $watermarkImage->getImageHeight()) * ($regs[1] / 100);
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
	 * Get ImageMagick version
	 * @return string
	 */
	static public function getIMVersion()
	{
		$im = new Imagick();
		return Core_Array::get($im->getVersion(), 'versionString', '');
	}
}