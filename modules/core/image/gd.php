<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Image helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Image_Gd extends Core_Image
{
    /**
     * ICC header size in APP2 segment
     */
    const ICC_HEADER_LEN = 14;

    /**
     * maximum data len of a JPEG marker
     */
    const MAX_BYTES_IN_MARKER = 65533;

    /**
     * ICC header marker
     */
    const ICC_MARKER = "ICC_PROFILE\x00";

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

		// ИЗВЛЕКАЕМ ICC-ПРОФИЛЬ ТОЛЬКО ДЛЯ JPEG
		$icc_profile = NULL;

		if ($iSourceImagetype == IMAGETYPE_JPEG)
		{
			$icc_profile = $this->_extractIccProfile($sourceFile);
		}

		// Change output format
		$iDestImagetype = !is_NULL($outputFormat)
			? self::getImagetypeByFormat($outputFormat)
			: $iSourceImagetype;

		/* Если размеры исходного файла больше максимальных, тогда масштабируем*/
		if (($sourceX > $maxWidth || $sourceY > $maxHeight) /*&& $maxWidth != 0 && $maxHeight != 0*/ || $iSourceImagetype != $iDestImagetype)
		{
			//$ext = Core_File::getExtension($targetFile);
			if ($iSourceImagetype == IMAGETYPE_JPEG)
			{
				$sourceResource = imagecreatefromjpeg($sourceFile);
			}
			elseif ($iSourceImagetype == IMAGETYPE_PNG)
			{
				$sourceResource = imagecreatefrompng($sourceFile);
			}
			elseif ($iSourceImagetype == IMAGETYPE_GIF)
			{
				// Detect animated GIF
				if (!$this->isAnimatedGif($sourceFile))
				{
					$sourceResource = imagecreatefromgif($sourceFile);
				}
				else
				{
					Core_File::copy($sourceFile, $targetFile);
					return TRUE;
				}
			}
			elseif (defined('IMAGETYPE_WEBP') && $iSourceImagetype == IMAGETYPE_WEBP && function_exists('imagecreatefromwebp'))
			{
				$sourceResource = imagecreatefromwebp($sourceFile);
			}
			elseif (defined('IMAGETYPE_AVIF') && $iSourceImagetype == IMAGETYPE_AVIF && function_exists('imagecreatefromavif'))
			{
				$sourceResource = imagecreatefromavif($sourceFile);
			}
			else
			{
				return FALSE;
			}

			if ($sourceResource)
			{
				// Image Rotate
				if ($iSourceImagetype == IMAGETYPE_JPEG)
				{
					if (function_exists('exif_read_data'))
					{
						$aEXIF = @exif_read_data($sourceFile, 'IFD0');

						if (isset($aEXIF['Orientation']))
						{
							switch ($aEXIF['Orientation'])
							{
								case 3: // Поворот на 180 градусов
									$sourceResource = imagerotate($sourceResource, 180, 0);
								break;
								case 6: // Поворот вправо на 90 градусов
									$sourceResource = imagerotate($sourceResource, -90, 0);

									$tmp = $sourceX;
									$sourceX = $sourceY;
									$sourceY = $tmp;
								break;
								case 8: // Поворот влево на 90 градусов
									$sourceResource = imagerotate($sourceResource, 90, 0);

									$tmp = $sourceX;
									$sourceX = $sourceY;
									$sourceY = $tmp;
								break;
							}
						}
					}
					else
					{
						Core_Log::instance()->clear()
							->status(Core_Log::$MESSAGE)
							->write('Required PHP extension EXIF not found!');
					}
				}
			}
			else
			{
				return FALSE;
			}

			if ($preserveAspectRatio)
			{
				$destX = $sourceX;
				$destY = $sourceY;

				// Масштабируем сначала по X
				if ($destX > $maxWidth && $maxWidth != 0)
				{
					$coefficient = $sourceY / $sourceX;
					$destX = $maxWidth;
					$destY = ceil($maxWidth * $coefficient);
				}

				// Масштабируем по Y
				if ($destY > $maxHeight && $maxHeight != 0)
				{
					$coefficient = $sourceX / $sourceY;
					$destX = ceil($maxHeight * $coefficient);
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
						$destY = ceil($maxWidth * $coefficient);
					}
				}
				// division by zero
				elseif ($sourceY != 0)
				{
					// Масштабируем по Y
					if ($destY > $maxHeight && $maxHeight != 0)
					{
						$coefficient = $sourceX / $sourceY;
						$destX = ceil($maxHeight * $coefficient);
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

			$targetResourceStep1 = imagecreateTRUEcolor($destX, $destY);

			if (!$preserveAspectRatio)
			{
				if ($destX_step2 == 0 || $destY_step2 == 0)
				{
					PHP_VERSION_ID < 80500 && imagedestroy($targetResourceStep1);
					unset($targetResourceStep1);

					return FALSE;
				}

				$targetResourceStep2 = imagecreateTRUEcolor($destX_step2, $destY_step2);
			}

			if ($iDestImagetype == IMAGETYPE_JPEG)
			{
				$quality = is_NULL($quality)
					? (defined('JPG_QUALITY') ? JPG_QUALITY : 60)
					: intval($quality);

				// Изменяем размер оригинальной картинки и копируем в созданую картинку
				imagecopyresampled($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);

				if ($preserveAspectRatio)
				{
					imagejpeg($targetResourceStep1, $targetFile, $quality);

					// ВОССТАНАВЛИВАЕМ ICC-ПРОФИЛЬ
					if ($icc_profile)
					{
						$this->_embedIccProfile($targetFile, $icc_profile);
					}
				}
				else
				{
					imagecopy($targetResourceStep2, $targetResourceStep1, 0, 0, $src_x, $src_y, $destX_step2, $destY_step2);

					imagejpeg($targetResourceStep2, $targetFile, $quality);

					// ВОССТАНАВЛИВАЕМ ICC-ПРОФИЛЬ
					if ($icc_profile)
					{
						$this->_embedIccProfile($targetFile, $icc_profile);
					}

					PHP_VERSION_ID < 80500 && imagedestroy($targetResourceStep2);
					unset($targetResourceStep2);
				}

				@chmod($targetFile, CHMOD_FILE);

				PHP_VERSION_ID < 80500 && imagedestroy($sourceResource);
				unset($sourceResource);
			}
			elseif ($iDestImagetype == IMAGETYPE_PNG)
			{
				$quality = is_NULL($quality)
					? (defined('PNG_QUALITY') ? PNG_QUALITY : 6)
					: intval($quality);

				imagealphablending($targetResourceStep1, FALSE);
				imagesavealpha($targetResourceStep1, TRUE);

				//$transparent = imagecolorallocatealpha($targetResourceStep1, 255, 255, 255, 127);
				//imagefilledrectangle($targetResourceStep1, 0, 0, $destX, $destY, $transparent);

				imagecopyresampled($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);

				if ($preserveAspectRatio)
				{
					imagepng($targetResourceStep1, $targetFile, $quality);
				}
				else
				{
					imagealphablending($targetResourceStep2, FALSE);
					imagesavealpha($targetResourceStep2, TRUE);

					//imagecopy($targetResourceStep2, $targetResourceStep1, 0, 0, $src_x, $src_y, $destX_step2, $destY_step2);
					imagecopyresampled($targetResourceStep2, $targetResourceStep1, 0, 0, $src_x, $src_y, $destX_step2, $destY_step2, $destX_step2, $destY_step2);

					imagepng($targetResourceStep2, $targetFile, $quality);

					PHP_VERSION_ID < 80500 && imagedestroy($targetResourceStep2);
					unset($targetResourceStep2);
				}
				@chmod($targetFile, CHMOD_FILE);

				PHP_VERSION_ID < 80500 && imagedestroy($sourceResource);
				unset($sourceResource);
			}
			elseif ($iDestImagetype == IMAGETYPE_GIF)
			{
				$this->setTransparency($targetResourceStep1, $sourceResource);

				imagecopyresampled($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);
				//imagecopyresampled($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);

				if ($preserveAspectRatio)
				{
					imagegif($targetResourceStep1, $targetFile);
				}
				else
				{
					//imagecopy($targetResourceStep2, $targetResourceStep1, 0, 0, $src_x, $src_y, $destX_step2, $destY_step2);
					imagecopyresampled($targetResourceStep2, $targetResourceStep1, 0, 0, $src_x, $src_y, $destX_step2, $destY_step2, $destX_step2, $destY_step2);

					imagegif($targetResourceStep2, $targetFile);

					PHP_VERSION_ID < 80500 && imagedestroy($targetResourceStep2);
					unset($targetResourceStep2);
				}
				@chmod($targetFile, CHMOD_FILE);

				PHP_VERSION_ID < 80500 && imagedestroy($sourceResource);
				unset($sourceResource);
			}
			elseif (defined('IMAGETYPE_WEBP') && $iDestImagetype == IMAGETYPE_WEBP)
			{
				$quality = is_NULL($quality)
					? (defined('WEBP_QUALITY') ? WEBP_QUALITY : 50)
					: intval($quality);

				imagealphablending($targetResourceStep1, FALSE);
				imagesavealpha($targetResourceStep1, TRUE);

				// Изменяем размер оригинальной картинки и копируем в созданую картинку
				imagecopyresampled($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);

				if ($preserveAspectRatio)
				{
					imagewebp($targetResourceStep1, $targetFile, $quality);
				}
				else
				{
					imagealphablending($targetResourceStep2, FALSE);
					imagesavealpha($targetResourceStep2, TRUE);

					// imagecopy($targetResourceStep2, $targetResourceStep1, 0, 0, $src_x, $src_y, $destX_step2, $destY_step2);
					imagecopyresampled($targetResourceStep2, $targetResourceStep1, 0, 0, $src_x, $src_y, $destX_step2, $destY_step2, $destX_step2, $destY_step2);

					imagewebp($targetResourceStep2, $targetFile, $quality);

					PHP_VERSION_ID < 80500 && imagedestroy($targetResourceStep2);
					unset($targetResourceStep2);
				}
				@chmod($targetFile, CHMOD_FILE);

				PHP_VERSION_ID < 80500 && imagedestroy($sourceResource);
				unset($sourceResource);
			}
			elseif (defined('IMAGETYPE_AVIF') && $iDestImagetype == IMAGETYPE_AVIF)
			{
				$quality = is_NULL($quality)
					? (defined('AVIF_QUALITY') ? AVIF_QUALITY : 50)
					: intval($quality);

				imagealphablending($targetResourceStep1, FALSE);
				imagesavealpha($targetResourceStep1, TRUE);

				// Изменяем размер оригинальной картинки и копируем в созданую картинку
				imagecopyresampled($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);

				if ($preserveAspectRatio)
				{
					imageavif($targetResourceStep1, $targetFile, $quality);
				}
				else
				{
					imagealphablending($targetResourceStep2, FALSE);
					imagesavealpha($targetResourceStep2, TRUE);

					// imagecopy($targetResourceStep2, $targetResourceStep1, 0, 0, $src_x, $src_y, $destX_step2, $destY_step2);
					imagecopyresampled($targetResourceStep2, $targetResourceStep1, 0, 0, $src_x, $src_y, $destX_step2, $destY_step2, $destX_step2, $destY_step2);

					imageavif($targetResourceStep2, $targetFile, $quality);

					PHP_VERSION_ID < 80500 && imagedestroy($targetResourceStep2);
					unset($targetResourceStep2);
				}
				@chmod($targetFile, CHMOD_FILE);

				PHP_VERSION_ID < 80500 && imagedestroy($sourceResource);
				unset($sourceResource);
			}
			/*else
			{
				PHP_VERSION_ID < 80500 && imagedestroy($targetResourceStep1);
				unset($targetResourceStep1);

				if (!$preserveAspectRatio)
				{
					PHP_VERSION_ID < 80500 && imagedestroy($targetResourceStep2);
					unset($targetResourceStep2);
				}

				return FALSE;
			}*/

			PHP_VERSION_ID < 80500 && imagedestroy($targetResourceStep1);
			unset($targetResourceStep1);
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
		if (!Core_File::isFile($source))
		{
			throw new Core_Exception("The file '%source' does not exist.",
				array('%source' => Core::cutRootPath($source)));
		}

		$return = FALSE;

		// ИЗВЛЕКАЕМ ICC-ПРОФИЛЬ ТОЛЬКО ДЛЯ JPEG
		$icc_profile = NULL;

		$iSourceImagetype = self::exifImagetype($source);

		if ($iSourceImagetype == IMAGETYPE_JPEG)
		{
			$icc_profile = $this->_extractIccProfile($source);
		}

		if (Core_File::isFile($watermark))
		{
			$watermarkResource = imagecreatefrompng($watermark);

			//$ext = Core_File::getExtension($target);
			$iSourceImagetype = self::exifImagetype($source);

			// Change output format
			$iDestImagetype = !is_NULL($outputFormat)
				? self::getImagetypeByFormat($outputFormat)
				: $iSourceImagetype;

			if ($iSourceImagetype == IMAGETYPE_JPEG)
			{
				$sourceResource = imagecreatefromjpeg($source);

				if ($sourceResource)
				{
					$sourceResource = $this->_addWatermark($sourceResource, $watermarkResource, $watermarkX, $watermarkY);
				}
			}
			elseif ($iSourceImagetype == IMAGETYPE_PNG)
			{
				$sourceResource = imagecreatefrompng($source);

				if ($sourceResource)
				{
					imagealphablending($sourceResource, FALSE);
					imagesavealpha($sourceResource, TRUE);

					$sourceResource = $this->_addWatermark($sourceResource, $watermarkResource, $watermarkX, $watermarkY);
				}
			}
			elseif (defined('IMAGETYPE_WEBP') && $iSourceImagetype == IMAGETYPE_WEBP && function_exists('imagecreatefromwebp'))
			{
				$sourceResource = imagecreatefromwebp($source);

				if ($sourceResource)
				{
					imagealphablending($sourceResource, FALSE);
					imagesavealpha($sourceResource, TRUE);

					$sourceResource = $this->_addWatermark($sourceResource, $watermarkResource, $watermarkX, $watermarkY);
				}
			}
			elseif (defined('IMAGETYPE_AVIF') && $iSourceImagetype == IMAGETYPE_AVIF && function_exists('imagecreatefromavif'))
			{
				$sourceResource = imagecreatefromavif($source);

				if ($sourceResource)
				{
					imagealphablending($sourceResource, FALSE);
					imagesavealpha($sourceResource, TRUE);

					$sourceResource = $this->_addWatermark($sourceResource, $watermarkResource, $watermarkX, $watermarkY);
				}
			}
			elseif ($iSourceImagetype == IMAGETYPE_GIF)
			{
				$sourceResourceTmp = imagecreatefromgif($source);

				if ($sourceResourceTmp)
				{
					$picsize = self::getImageSize($source);
					$width = $picsize['width'];
					$height = $picsize['height'];

					// New Image
					$sourceResource = imagecreateTRUEcolor($width, $height);
					$this->setTransparency($sourceResource, $sourceResourceTmp);

					imagecopyresampled($sourceResource, $sourceResourceTmp, 0, 0, 0, 0, $width, $height, $width, $height);

					PHP_VERSION_ID < 80500 && imagedestroy($sourceResourceTmp);
					unset($sourceResourceTmp);

					$sourceResource = $this->_addWatermark($sourceResource, $watermarkResource, $watermarkX, $watermarkY);
				}
			}
			else
			{
				//$return = FALSE;
				$sourceResource = NULL;
			}

			PHP_VERSION_ID < 80500 && imagedestroy($watermarkResource);
			unset($watermarkResource);

			if ($sourceResource)
			{

				if ($iDestImagetype == IMAGETYPE_JPEG)
				{
					$return = imagejpeg($sourceResource, $target, intval(JPG_QUALITY));

					// ВОССТАНАВЛИВАЕМ ICC-ПРОФИЛЬ
					if ($icc_profile)
					{
						$this->_embedIccProfile($target, $icc_profile);
					}
				}
				elseif ($iDestImagetype == IMAGETYPE_PNG)
				{
					$return = imagepng($sourceResource, $target, intval(PNG_QUALITY));
				}
				elseif (defined('IMAGETYPE_WEBP') && $iDestImagetype == IMAGETYPE_WEBP && function_exists('imagecreatefromwebp'))
				{
					$return = imagewebp($sourceResource, $target, defined('WEBP_QUALITY') ? WEBP_QUALITY : 50);
				}
				elseif (defined('IMAGETYPE_AVIF') && $iDestImagetype == IMAGETYPE_AVIF && function_exists('imagecreatefromavif'))
				{
					$return = imageavif($sourceResource, $target, defined('AVIF_QUALITY') ? AVIF_QUALITY : 50);
				}
				elseif ($iDestImagetype == IMAGETYPE_GIF)
				{
					$return = imagegif($sourceResource, $target);
				}

				@chmod($target, CHMOD_FILE);

				PHP_VERSION_ID < 80500 && imagedestroy($sourceResource);
				unset($sourceResource);
			}
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
	 * Добавление водяного знака
	 *
	 * @param GdImage $sourceResource исходное изображение
	 * @param GdImage $watermarkResource водяной знак
	 * @param int $watermarkX позиция по оси X
	 * @param int $watermarkY позиция по оси Y
	 * @return GdImage
	 */
	protected function _addWatermark($sourceResource, $watermarkResource, $watermarkX = NULL, $watermarkY = NULL)
	{
		// Высота и ширина основной картинки
		$sourceResource_w = imagesx($sourceResource);
		$sourceResource_h = imagesy($sourceResource);

		// Высота и ширина watermark-а
		$watermarkResource_w = imagesx($watermarkResource);
		$watermarkResource_h = imagesy($watermarkResource);

		if (!is_NULL($watermarkX))
		{
			// Если передан атрибут в %-ах
			if (preg_match("/^([0-9]*)%$/", $watermarkX, $regs))
			{
				// Вычисляем позицию в %-х
				$watermarkX = $regs[1] > 0
					? ($sourceResource_w - $watermarkResource_w) * ($regs[1] / 100)
					: 0;
			}
		}

		if (!is_NULL($watermarkY))
		{
			// Если передан атрибут в %-ах
			if (preg_match("/^([0-9]*)%$/", $watermarkY, $regs))
			{
				// Вычисляем позицию в %-х
				$watermarkY = $regs[1] > 0
					? ($sourceResource_h - $watermarkResource_h) * ($regs[1] / 100)
					: 0;
			}
		}

		$watermarkX = intval($watermarkX);
		$watermarkY = intval($watermarkY);

		$watermarkX < 0 && $watermarkX = 0;
		$watermarkY < 0 && $watermarkY = 0;

		imagealphablending($sourceResource, TRUE);

		// Convert source image to TRUE-color image
		if (!imageisTRUEcolor($sourceResource))
		{
			$this->imagepalettetoTRUEcolor($sourceResource);
		}

		imagecopy($sourceResource, $watermarkResource, (int) $watermarkX, (int) $watermarkY, 0, 0, $watermarkResource_w, $watermarkResource_h);

		return $sourceResource;
	}

	/**
	 * Function imagepalettetoTRUEcolor() (PHP 5 >= 5.5.0, PHP 7, PHP 8)
	 * @param GdImage $src
	 * @return bool
	 */
	public function imagepalettetoTRUEcolor(&$src)
	{
		if (function_exists('imagepalettetoTRUEcolor'))
		{
			return imagepalettetoTRUEcolor($src);
		}

		if (imageisTRUEcolor($src))
		{
			return TRUE;
		}

		$dst = imagecreateTRUEcolor(imagesx($src), imagesy($src));

		imagecopy($dst, $src, 0, 0, 0, 0, imagesx($src), imagesy($src));
		PHP_VERSION_ID < 80500 && imagedestroy($src);

		$src = $dst;

		return TRUE;
	}

	/**
	 * Установка прозрачности для $image_target, равной прозрачности $image_source
	 * @param $image_target Ресурс изображения получателя
	 * @param $image_source Ресурс изображения источника
	 */
	public function setTransparency($image_target, $image_source)
	{
		$transparencyIndex = imagecolortransparent($image_source);

		$transparencyColor = $transparencyIndex >= 0 && $transparencyIndex < imagecolorstotal($image_source)
			? imagecolorsforindex($image_source, $transparencyIndex)
			: array('red' => 0, 'green' => 0, 'blue' => 0);

		$transparencyIndex = imagecolorallocate($image_target, $transparencyColor['red'], $transparencyColor['green'], $transparencyColor['blue']);
		imagefill($image_target, 0, 0, $transparencyIndex);
		imagecolortransparent($image_target, $transparencyIndex);

		return TRUE;
	}

	/**
	 * Get image size
	 * @param string $path path
	 * @return array|NULL
     */
	public function getImageSize($path)
	{
		if (Core_File::isFile($path) && is_readable($path) && filesize($path) > 12 && self::exifImagetype($path))
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
	 * Get Image Type: 0 = UNKNOWN, 1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF (orden de bytes intel), 8 = TIFF (orden de bytes motorola),
	 * 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM, 17 = ICO, 18 = WEBP, 19 = AVIF, 20 = COUNT
	 * @param string $path
	 * @return mixed
	 */
	public function getImageType($path)
	{
		if ((list($width, $height, $type, $attr) = @getimagesize($path)) !== FALSE)
		{
			return $type;
		}

		return 0;
	}

	/**
	 * Detect animated GIF
	 * @param string $filePath
	 * @return bool
	 */
	public function isAnimatedGif($filePath)
	{
		$fp = fopen($filePath, "rb");

		if (fread($fp, 3) !== "GIF")
		{
			fclose($fp);
			return FALSE;
		}

		$iFrames = 0;

		while (!feof($fp) && $iFrames < 2)
		{
			if (fread($fp, 1) === "\x00")
			{
				// Animated gif contains multiple "frames", with each frame having a
				// header made up of:
				// * a static 4-byte sequence (\x00\x21\xF9\x04)
				// * 4 variable bytes
				// * a static 2-byte sequence (\x00\x2C)

				// Some of the animated GIFs do not contain graphic control extension (starts with 21 f9)
				$byte = fread($fp, 1);
				if ($byte === "\x2c"
					|| $byte === "\x21" && fread($fp, 1) === "\xf9"
				)
				{
					$iFrames++;
				}
			}
		}

		fclose($fp);

		return $iFrames > 1;
	}

	/**
	 * Извлечение ICC-профиля из JPEG файла
	 * @param string $file путь к файлу
	 * @return string|NULL данные профиля или NULL
	 */
	protected function _extractIccProfile($file)
	{
		if (self::exifImagetype($file) != IMAGETYPE_JPEG)
		{
			return NULL;
		}

		$data = @file_get_contents($file);
		if ($data === FALSE)
		{
			return NULL;
		}

		$len = strlen($data);
		$pos = 0;
		$counter = 0;
		$profile_chunks = array();

		while ($pos < $len && $counter < 1000)
		{
			$pos = strpos($data, "\xff", $pos);
			if ($pos === FALSE) break;

			$type = $this->_getJpegSegmentType($data, $pos);

			if ($type == 0xe2) // APP2
			{
				$size = $this->_getJpegSegmentSize($data, $pos);

				if ($this->_jpegSegmentContainsIcc($data, $pos, $size))
				{
					list($chunk_no, $chunk_cnt) = $this->_getJpegSegmentIccChunkInfo($data, $pos);

					if ($chunk_no <= $chunk_cnt)
					{
						$profile_chunks[$chunk_no] = $this->_getJpegSegmentIccChunk($data, $pos);

						if ($chunk_no == $chunk_cnt)
						{
							ksort($profile_chunks);
							return implode('', $profile_chunks);
						}
					}
				}

				$pos += $size + 2;
			}
			else
			{
				// Пропускаем другие сегменты
				if (in_array($type, array(0xe0, 0xe1, 0xe3, 0xe4, 0xe5, 0xe6, 0xe7, 0xe8, 0xe9, 0xea,
										0xeb, 0xec, 0xed, 0xee, 0xef, 0xc0, 0xc2, 0xc4, 0xdb, 0xda, 0xfe)))
				{
					$size = $this->_getJpegSegmentSize($data, $pos);
					$pos += $size + 2;
				}
				else
				{
					$pos += 2;
				}
			}

			$counter++;
		}

		return NULL;
	}

	/**
	 * Внедрение ICC-профиля в JPEG файл
	 * @param string $file путь к файлу
	 * @param string $icc_profile данные профиля
	 * @return bool успех операции
	 */
	protected function _embedIccProfile($file, $icc_profile)
	{
		if (empty($icc_profile) || !file_exists($file) || !is_readable($file))
		{
			return FALSE;
		}

		$jpeg_data = @file_get_contents($file);
		if ($jpeg_data === FALSE)
		{
			return FALSE;
		}

		// Удаляем существующие ICC-профили
		$this->_removeIccProfile($jpeg_data);

		// Вставляем новый профиль
		if ($this->_insertIccProfile($jpeg_data, $icc_profile))
		{
			return file_put_contents($file, $jpeg_data) !== FALSE;
		}

		return FALSE;
	}

	/**
	 * Удаление ICC-профиля из JPEG данных
	 * @param string &$jpeg_data данные JPEG
	 * @return bool
	 */
	protected function _removeIccProfile(&$jpeg_data)
	{
		$len = strlen($jpeg_data);
		$pos = 0;
		$counter = 0;
		$chunks_to_go = -1;

		while ($pos < $len && $counter < 100)
		{
			$pos = strpos($jpeg_data, "\xff", $pos);
			if ($pos === FALSE) break;

			$type = $this->_getJpegSegmentType($jpeg_data, $pos);

			if ($type == 0xe2) // APP2
			{
				$size = $this->_getJpegSegmentSize($jpeg_data, $pos);

				if ($this->_jpegSegmentContainsIcc($jpeg_data, $pos, $size))
				{
					list($chunk_no, $chunk_cnt) = $this->_getJpegSegmentIccChunkInfo($jpeg_data, $pos);
					if ($chunks_to_go == -1) $chunks_to_go = $chunk_cnt;

					// Удаляем сегмент
					$jpeg_data = substr_replace($jpeg_data, '', $pos, $size + 2);
					$len -= $size + 2;

					if (--$chunks_to_go == 0) return TRUE;

					continue;
				}

				$pos += $size + 2;
			}
			else
			{
				// Пропускаем другие сегменты
				if (in_array($type, array(0xe0, 0xe1, 0xe3, 0xe4, 0xe5, 0xe6, 0xe7, 0xe8, 0xe9, 0xea,
										0xeb, 0xec, 0xed, 0xee, 0xef, 0xc0, 0xc2, 0xc4, 0xdb, 0xda, 0xfe)))
				{
					$size = $this->_getJpegSegmentSize($jpeg_data, $pos);
					$pos += $size + 2;
				}
				else
				{
					$pos += 2;
				}
			}

			$counter++;
		}

		return FALSE;
	}

	/**
	 * Вставка ICC-профиля в JPEG данные
	 * @param string &$jpeg_data данные JPEG
	 * @param string $icc_profile данные профиля
	 * @return bool
	 */
	protected function _insertIccProfile(&$jpeg_data, $icc_profile)
	{
		$len = strlen($jpeg_data);
		$pos = 0;
		$counter = 0;

		while ($pos < $len && $counter < 100)
		{
			$pos = strpos($jpeg_data, "\xff", $pos);
			if ($pos === FALSE) break;

			$type = $this->_getJpegSegmentType($jpeg_data, $pos);

			if ($type == 0xd8) // SOI - Start of Image
			{
				$pos += 2;
				$profile_data = $this->_prepareJpegProfileData($icc_profile);

				if (!empty($profile_data))
				{
					$jpeg_data = substr($jpeg_data, 0, $pos) . $profile_data . substr($jpeg_data, $pos);
					return TRUE;
				}

				return FALSE;
			}
			else
			{
				// Пропускаем другие сегменты
				if (in_array($type, array(0xe0, 0xe1, 0xe2, 0xe3, 0xe4, 0xe5, 0xe6, 0xe7, 0xe8, 0xe9, 0xea,
										0xeb, 0xec, 0xed, 0xee, 0xef, 0xc0, 0xc2, 0xc4, 0xdb, 0xda, 0xfe)))
				{
					$size = $this->_getJpegSegmentSize($jpeg_data, $pos);
					$pos += $size + 2;
				}
				else
				{
					$pos += 2;
				}
			}

			$counter++;
		}

		return FALSE;
	}

	/**
	 * Подготовка данных профиля для вставки в JPEG
	 * @param string $icc_profile данные профиля
	 * @return string
	 */
	protected function _prepareJpegProfileData($icc_profile)
	{
		$icc_size = strlen($icc_profile);
		$icc_chunks = ceil($icc_size / (self::MAX_BYTES_IN_MARKER - self::ICC_HEADER_LEN));
		$result = '';

		for ($i = 1; $i <= $icc_chunks; $i++)
		{
			$max_chunk_size = self::MAX_BYTES_IN_MARKER - self::ICC_HEADER_LEN;
			$from = ($i - 1) * $max_chunk_size;
			$bytes = ($i < $icc_chunks) ? $max_chunk_size : $icc_size % $max_chunk_size;

			$chunk = substr($icc_profile, $from, $bytes);
			$chunk_size = strlen($chunk);

			// APP2 segment marker + size field
			$result .= "\xff\xe2" . pack('n', $chunk_size + 2 + self::ICC_HEADER_LEN);
			// Profile marker + chunk number + total chunks
			$result .= self::ICC_MARKER . pack('CC', $i, $icc_chunks);
			// Chunk data
			$result .= $chunk;
		}

		return $result;
	}

	/**
	 * Получение размера JPEG сегмента
	 * @param string &$data данные JPEG
	 * @param int $pos позиция
	 * @return int
	 */
	protected function _getJpegSegmentSize(&$data, $pos)
	{
		$arr = unpack('nint', substr($data, $pos + 2, 2));
		return $arr['int'];
	}

	/**
	 * Получение типа JPEG сегмента
	 * @param string &$data данные JPEG
	 * @param int $pos позиция
	 * @return int
	 */
	protected function _getJpegSegmentType(&$data, $pos)
	{
		$arr = unpack('Cchar', substr($data, $pos + 1, 1));
		return $arr['char'];
	}

	/**
	 * Проверка содержит ли сегмент ICC профиль
	 * @param string &$data данные JPEG
	 * @param int $pos позиция
	 * @param int $size размер
	 * @return bool
	 */
	protected function _jpegSegmentContainsIcc(&$data, $pos, $size)
	{
		if ($size < self::ICC_HEADER_LEN) return FALSE;
		return substr($data, $pos + 4, self::ICC_HEADER_LEN - 2) == self::ICC_MARKER;
	}

	/**
	 * Получение информации о чанке ICC профиля
	 * @param string &$data данные JPEG
	 * @param int $pos позиция
	 * @return array [chunk_no, chunk_cnt]
	 */
	protected function _getJpegSegmentIccChunkInfo(&$data, $pos)
	{
		$a = unpack('Cchunk_no/Cchunk_count', substr($data, $pos + 16, 2));
		return array_values($a);
	}

	/**
	 * Получение данных чанка ICC профиля
	 * @param string &$data данные JPEG
	 * @param int $pos позиция
	 * @return string
	 */
	protected function _getJpegSegmentIccChunk(&$data, $pos)
	{
		$data_offset = $pos + 4 + self::ICC_HEADER_LEN;
		$size = $this->_getJpegSegmentSize($data, $pos);
		$data_size = $size - self::ICC_HEADER_LEN - 2;
		return substr($data, $data_offset, $data_size);
	}

	/**
	 * Get GD version
	 * @return string
	 */
	public function getVersion()
	{
		if (function_exists('gd_info'))
		{
			$gd_info = @gd_info();
			return preg_replace('/[^0-9\.]/', '', $gd_info['GD Version']);
		}

		return NULL;
	}

	/**
	 * Check GD-Module Availability
	 * @return bool
	 */
	public function isAvailable()
	{
		return function_exists('gd_info');
	}
}