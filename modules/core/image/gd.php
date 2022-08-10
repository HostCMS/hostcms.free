<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Image helper
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Image_Gd extends Core_Image
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

		/* Если размеры исходного файла больше максимальных, тогда масштабируем*/
		if (($sourceX > $maxWidth || $sourceY > $maxHeight) /*&& $maxWidth != 0 && $maxHeight != 0*/)
		{
			//$ext = Core_File::getExtension($targetFile);
			$iImagetype = self::exifImagetype($sourceFile);

			if ($iImagetype == IMAGETYPE_JPEG)
			{
				$sourceResource = imagecreatefromjpeg($sourceFile);
			}
			elseif ($iImagetype == IMAGETYPE_PNG)
			{
				$sourceResource = imagecreatefrompng($sourceFile);
			}
			elseif ($iImagetype == IMAGETYPE_GIF)
			{
				$sourceResource = imagecreatefromgif($sourceFile);
			}
			elseif (defined('IMAGETYPE_WEBP') && $iImagetype == IMAGETYPE_WEBP && function_exists('imagecreatefromwebp'))
			{
				$sourceResource = imagecreatefromwebp($sourceFile);
			}
			else
			{
				return FALSE;
			}

			if ($sourceResource)
			{
				// Image Rotate
				if ($iImagetype == IMAGETYPE_JPEG && function_exists('exif_read_data'))
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

			$targetResourceStep1 = imagecreatetruecolor($destX, $destY);

			if (!$preserveAspectRatio)
			{
				if ($destX_step2 == 0 || $destY_step2 == 0)
				{
					imagedestroy($targetResourceStep1);
					return FALSE;
				}
				$targetResourceStep2 = imagecreatetruecolor($destX_step2, $destY_step2);
			}

			if ($iImagetype == IMAGETYPE_JPEG)
			{
				$quality = is_null($quality)
					? (defined('JPG_QUALITY') ? JPG_QUALITY : 60)
					: intval($quality);

				// Изменяем размер оригинальной картинки и копируем в созданую картинку
				imagecopyresampled($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);

				if ($preserveAspectRatio)
				{
					imagejpeg($targetResourceStep1, $targetFile, $quality);
				}
				else
				{
					imagecopy($targetResourceStep2, $targetResourceStep1, 0, 0, $src_x, $src_y, $destX_step2, $destY_step2);

					imagejpeg($targetResourceStep2, $targetFile, $quality);
					imagedestroy($targetResourceStep2);
				}
				@chmod($targetFile, CHMOD_FILE);

				imagedestroy($sourceResource);
			}
			elseif ($iImagetype == IMAGETYPE_PNG)
			{
				$quality = is_null($quality)
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
					imagedestroy($targetResourceStep2);
				}
				@chmod($targetFile, CHMOD_FILE);

				imagedestroy($sourceResource);
			}
			elseif ($iImagetype == IMAGETYPE_GIF)
			{
				self::setTransparency($targetResourceStep1, $sourceResource);

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
					imagedestroy($targetResourceStep2);
				}
				@chmod($targetFile, CHMOD_FILE);

				imagedestroy($sourceResource);
			}
			elseif (defined('IMAGETYPE_WEBP') && $iImagetype == IMAGETYPE_WEBP)
			{
				$quality = is_null($quality)
					? (defined('WEBP_QUALITY') ? WEBP_QUALITY : 80)
					: intval($quality);

				// Изменяем размер оригинальной картинки и копируем в созданую картинку
				imagecopyresampled($targetResourceStep1, $sourceResource, 0, 0, 0, 0, $destX, $destY, $sourceX, $sourceY);

				if ($preserveAspectRatio)
				{
					imagewebp($targetResourceStep1, $targetFile, $quality);
				}
				else
				{
					imagecopy($targetResourceStep2, $targetResourceStep1, 0, 0, $src_x, $src_y, $destX_step2, $destY_step2);

					imagewebp($targetResourceStep2, $targetFile, $quality);
					imagedestroy($targetResourceStep2);
				}
				@chmod($targetFile, CHMOD_FILE);

				imagedestroy($sourceResource);
			}
			/*else
			{
				imagedestroy($targetResourceStep1);

				if (!$preserveAspectRatio)
				{
					imagedestroy($targetResourceStep2);
				}

				return FALSE;
			}*/

			imagedestroy($targetResourceStep1);
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
		if (!is_file($source))
		{
			throw new Core_Exception("The file '%source' does not exist.",
				array('%source' => Core::cutRootPath($source)));
		}

		$return = FALSE;

		if (is_file($watermark))
		{
			$watermarkResource = imagecreatefrompng($watermark);

			$ext = Core_File::getExtension($target);

			if ($ext == 'jpg' || $ext == 'jpeg')
			{
				$sourceResource = imageCreateFromJPEG($source);

				if ($sourceResource)
				{
					$sourceResource = self::_addWatermark($sourceResource, $watermarkResource, $watermarkX, $watermarkY);
					$return = imagejpeg($sourceResource, $target, intval(JPG_QUALITY));
					@chmod($target, CHMOD_FILE);

					imagedestroy($sourceResource);
				}
			}
			elseif ($ext == 'png')
			{
				$sourceResource = imagecreatefrompng($source);

				if ($sourceResource)
				{
					imagealphablending($sourceResource, FALSE);
					imagesavealpha($sourceResource, TRUE);

					$sourceResource = self::_addWatermark($sourceResource, $watermarkResource, $watermarkX, $watermarkY);
					$return = imagepng($sourceResource, $target, intval(PNG_QUALITY));
					@chmod($target, CHMOD_FILE);

					imagedestroy($sourceResource);
				}
			}
			elseif ($ext == 'webp' && function_exists('imagecreatefromwebp'))
			{
				$sourceResource = imagecreatefromwebp($source);

				if ($sourceResource)
				{
					imagealphablending($sourceResource, FALSE);
					imagesavealpha($sourceResource, TRUE);

					$sourceResource = self::_addWatermark($sourceResource, $watermarkResource, $watermarkX, $watermarkY);
					$return = imagewebp($sourceResource, $target, defined('WEBP_QUALITY') ? WEBP_QUALITY : 80);
					@chmod($target, CHMOD_FILE);

					imagedestroy($sourceResource);
				}
			}
			elseif ($ext == 'gif')
			{
				$sourceResource = imagecreatefromgif($source);

				if ($sourceResource)
				{
					$picsize = self::getImageSize($source);
					$width = $picsize['width'];
					$height = $picsize['height'];

					$new_image = imagecreatetruecolor($width, $height);
					self::setTransparency($new_image, $sourceResource);

					imagecopyresampled($new_image, $sourceResource, 0, 0, 0, 0, $width, $height, $width, $height);

					$new_image = self::_addWatermark($new_image, $watermarkResource, $watermarkX, $watermarkY);
					$return = imagegif($new_image, $target);
					@chmod($target, CHMOD_FILE);

					imagedestroy($new_image);
					imagedestroy($sourceResource);
				}
			}
			else
			{
				$return = FALSE;
			}

			imagedestroy($watermarkResource);
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
	static protected function _addWatermark($sourceResource, $watermarkResource, $watermarkX = NULL, $watermarkY = NULL)
	{
		// Высота и ширина основной картинки
		$sourceResource_w = imagesx($sourceResource);
		$sourceResource_h = imagesy($sourceResource);

		// Высота и ширина watermark-а
		$watermarkResource_w = imagesx($watermarkResource);
		$watermarkResource_h = imagesy($watermarkResource);

		if (!is_null($watermarkX))
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

		if (!is_null($watermarkY))
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

		$watermarkX < 0 && $watermarkX = 0;
		$watermarkY < 0 && $watermarkY = 0;

		imagealphablending($sourceResource, TRUE);
		imagecopy($sourceResource, $watermarkResource, $watermarkX, $watermarkY, 0, 0, $watermarkResource_w, $watermarkResource_h);

		return $sourceResource;
	}

	/**
	 * Установка прозрачности для $image_target, равной прозрачности $image_source
	 * @param $image_target Ресурс изображения получателя
	 * @param $image_source Ресурс изображения источника
	 */
	static public function setTransparency($image_target, $image_source)
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
	 * Get Image Type: 0 = UNKNOWN, 1 = GIF, 2 = JPG, 3 = PNG, 4 = SWF, 5 = PSD, 6 = BMP, 7 = TIFF (orden de bytes intel), 8 = TIFF (orden de bytes motorola),
	 * 9 = JPC, 10 = JP2, 11 = JPX, 12 = JB2, 13 = SWC, 14 = IFF, 15 = WBMP, 16 = XBM, 17 = ICO, 18 = WEBP
	 * @param string $path
	 * @return mixed
	 */
	static public function getImageType($path)
	{
		if ((list($width, $height, $type, $attr) = @getimagesize($path)) !== FALSE)
		{
			return $type;
		}

		return 0;
	}

	/**
	 * Get GD version
	 * @return string
	 */
	static public function getVersion()
	{
		if (function_exists('gd_info'))
		{
			$gd_info = @gd_info();
			return preg_replace('/[^0-9\.]/', '', $gd_info['GD Version']);
		}

		return NULL;
	}
}