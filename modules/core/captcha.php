<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Completely Automated Public Turing test to tell Computers and Humans Apart.
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @author Kruglov Sergei
 * @copyright © 2006, 2007, 2008, 2011 Kruglov Sergei, http://www.captcha.ru
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Captcha
{
	/**
	 * Путь к шрифтам
	 */
	//protected $_fontsDir = 'hostcmsfiles/captcha/fonts';

	/**
	 * Порядок символов в шрифтах
	 */
	protected $_alphabet = '0123456789abcdefghijklmnopqrstuvwxyz';

	/**
	 * амплитуда искажения
	 */
	protected $_amplitudeDistortion = 10;

	/**
	 * Флаг, указывающий, слитно писать символы CAPTCHA или нет
	 */
	protected $_spaces = TRUE;

	/**
	 * Расширение рисунка CAPTCHA
	 */
	protected $_type = 'PNG';

	/**
	 * Минимальное значение радиуса многоугольника на фоне CAPTCHA
	 */
	protected $_polygonMinRadius = 10;

	/**
	 * Максимальное значение радиуса многоугольника на фоне CAPTCHA
	 */
	protected $_polygonMaxRadius = 15;

	/**
	 * Минимальное значение количества углов многоугольника на фоне CAPTCHA
	 */
	protected $_polygonMinCorners = 3;

	/**
	 * Максимальное значение количества углов многоугольника на фоне CAPTCHA
	 */
	protected $_polygonMaxCorners = 6;

	/**
	 * CAPTCHA config
	 * @var array
	 */
	static protected $_config = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		if (is_null(self::$_config))
		{
			self::$_config = Core::$config->get('core_captcha', array()) + array(
				'allowedCharacters' => '23456789abcdeghkmnpqsuvxyz',
				//'color' => array(mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100)),
				'backgroundColor' => array(mt_rand(230, 255), mt_rand(230, 255), mt_rand(230, 255)),
				'noise' => 5,
				'width' => 88,
				'height' => 31,
				// Минимальная длина строки
				'minLenght' => 4,
				// Максимальная длина строки
				'maxLenght' => 4,
				'fillBackground' => TRUE,
				'fonts' => array(
					'/hostcmsfiles/captcha/fonts/antiqua.png',
					// '/hostcmsfiles/captcha/fonts/baskerville.png',//
					// '/hostcmsfiles/captcha/fonts/batang.png',//
					// '/hostcmsfiles/captcha/fonts/bodoni.png', //?
					'/hostcmsfiles/captcha/fonts/bookman.png',
					'/hostcmsfiles/captcha/fonts/cambria.png',
					'/hostcmsfiles/captcha/fonts/centaur.png',
					'/hostcmsfiles/captcha/fonts/century.png',
					'/hostcmsfiles/captcha/fonts/constantia.png',
					// '/hostcmsfiles/captcha/fonts/elizabeth.png', //
					// '/hostcmsfiles/captcha/fonts/footlight.png', //?
					// '/hostcmsfiles/captcha/fonts/garamond.png',//
					'/hostcmsfiles/captcha/fonts/goudy_old.png',
					'/hostcmsfiles/captcha/fonts/high_tower.png',
					'/hostcmsfiles/captcha/fonts/lucida.png',
					// '/hostcmsfiles/captcha/fonts/modern_20.png', //?
					'/hostcmsfiles/captcha/fonts/palatino.png',
					'/hostcmsfiles/captcha/fonts/palatino_linotype_bold.png',
					'/hostcmsfiles/captcha/fonts/perpetua.png',
					'/hostcmsfiles/captcha/fonts/perpetua_bold.png',
					'/hostcmsfiles/captcha/fonts/rockwell.png',
					'/hostcmsfiles/captcha/fonts/times.png',
					'/hostcmsfiles/captcha/fonts/times_bold.png',
				)
			);
		}
	}

	/**
	 * Set config
	 * @param string $name name
	 * @param string $value value
	 * @return self
	 */
	public function setConfig($name, $value)
	{
		self::$_config[$name] = $value;
		return $this;
	}

	/**
	 * Получить уникальный индекс для CAPTCHA
	 * @return string
     */
	static public function getCaptchaId()
	{
		/*$max = 99999;

		$maxValue = isset($_SESSION)
			? count($_SESSION) + $max
			: $max;

		$captchaId = mt_rand(0, $maxValue);
		$i = 0;
		while (isset($_SESSION['captcha_' . $captchaId]) && $i < $max)
		{
			$captchaId = mt_rand(0, $maxValue);
			$i++;
		}

		return $captchaId;*/
		return Core::generateUniqueId();
	}

	/**
	 * Check if CAPTCHA is valid
	 * @param int $captchaId ID of CAPTCHA
	 * @param string $value value
	 * @return boolean
	 */
	static public function valid($captchaId, $value)
	{
		if (!is_array($captchaId) && !is_array($value))
		{
			$captchaId = strval($captchaId);
			$value = strval($value);

			Core_Session::start();

			if (isset($_SESSION['captcha_' . $captchaId]))
			{
				$return = $value == $_SESSION['captcha_' . $captchaId];

				unset($_SESSION['captcha_' . $captchaId]);
				return $return;
			}
		}

		return FALSE;
	}

	/**
	 * Create value for CAPTCHA
	 * @return string
	 */
	static public function createValue()
	{
		$length = mt_rand(self::$_config['minLenght'], self::$_config['maxLenght']);

		$allowedCharacters = strval(self::$_config['allowedCharacters']);
		$allowedCharactersLength = strlen($allowedCharacters);

		while (TRUE)
		{
			$value = '';
			for ($i = 0; $i < $length; $i++)
			{
				$value .= $allowedCharacters[mt_rand(0, $allowedCharactersLength - 1)];
			}

			// Исключаем сочетания символов, сложные для распознавания пользователем
			if (!preg_match('/cp|cb|ck|c6|c9|rn|rm|mm|co|do|cl|db|qp|qb|dp|ww/u', $value))
			{
				break;
			}
		}

		return $value;
	}

	/**
	 * Построения изображения CAPTCHA и помещения его текста в сессию
	 *
	 * @param int $captchaId - уникальный номер CAPTCHA
	 */
	public function build($captchaId)
	{
		$ind = 0;

		$width = 120;
		$height = 50;

		// Цвет текста
		$foreground_color = isset(self::$_config['color'])
			? self::$_config['color']
			: array(mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100));

		// Цвет фона
		$backgroundColor = self::$_config['backgroundColor'];

		// ШРИФТЫ
		/*$fonts = array();
		$_fontsDir = CMS_FOLDER . $this->_fontsDir;

		if (Core_File::isDir($_fontsDir) && !Core_File::isLink($_fontsDir))
		{
			// Открываем директорию со шрифтами
			if ($handle = opendir($_fontsDir))
			{
				// Составляем массив шрифтов
				while (FALSE !== ($file = readdir($handle)))
				{
					if (preg_match('/\.png$/iu', $file))
					{
						$fonts[] = $_fontsDir . '/' . $file;
					}
				}

				closedir($handle);
			}
		}*/

		// Случайный шрифт
		$randKey = array_rand(self::$_config['fonts'], 1);
		$font = imagecreatefrompng(CMS_FOLDER . self::$_config['fonts'][$randKey]);

		// Устанавливает режим смешивания для изображения
		imageAlphaBlending($font, FALSE);

		// Применяется для установки прозрачности изображений в формате PNG
		imageSaveAlpha($font, TRUE);

		$fontfile_width = imagesx($font);
		$fontfile_height = imagesy($font) - 1;

		// Формируем массив с указанием, с какого места начинается написание символа и в какой позиции оно заканчивается
		$font_metrics = array();
		$symbol = 0;

		// Флаг, указывающий, прочитан ли символ
		$characterRead = FALSE;

		// Загрузка шрифта
		$alphabetLength = strlen($this->_alphabet);
		for ($i = 0; $i < $fontfile_width && $symbol < $alphabetLength; $i++)
		{
			$transparent = (imagecolorat($font, $i, 0) >> 24) == 127;

			$smbl = $this->_alphabet[$symbol];

			// Символ еще не прочитан и позиция курсора не на пустом пространстве
			if (!$characterRead && !$transparent)
			{
				// Начало символа
				$font_metrics[$smbl]['start'] = $i;

				$characterRead = TRUE;
				continue;
			}

			// Символ прочитан и курсор оказался на пустом пространстве
			if ($characterRead && $transparent)
			{
				// Конец символа
				$font_metrics[$smbl]['end'] = $i;
				$characterRead = FALSE;

				// Следующий символ
				$symbol++;
				continue;
			}
		}

		$img = imagecreatetruecolor($width, $height);

		imagealphablending($img, TRUE);

		$white = imagecolorallocate($img, 255, 255, 255);
		imagefilledrectangle($img, 0, 0, $width - 1, $height - 1, $white);

		Core_Session::start();

		// Записываем в сессию строку
		$value = $_SESSION['captcha_' . $captchaId] = $this->createValue();

		Core_Session::close();

		$length = strlen($value);

		// Рисуем текст CAPTCHA
		$x = 1;

		for ($i = 0; $i < $length; $i++)
		{
			if (!isset($font_metrics[$value[$i]]))
			{
				throw new Core_Exception('Wrong Font Metric, check "hostcmsfiles/captcha/fonts"!');
			}

			$m = $font_metrics[$value[$i]];

			/*if (!$this->_spaces)
			{
				$y = mt_rand(- $this->_amplitudeDistortion, $this->_amplitudeDistortion) + ($height - $fontfile_height) / 2 + 2;

				$shift = 0;

				if ($i > 0)
				{
					$shift = 10000;

					for ($sy = 7; $sy < $fontfile_height -20; $sy += 1)
					{
						for ($sx = $m['start'] - 1; $sx < $m['end']; $sx += 1)
						{
							$rgb = imagecolorat($font, $sx, $sy);
							$opacity = $rgb >> 24;
							$opacity = $rgb;

							if ($opacity < 127)
							{
								$left = $sx - $m['start'] + $x;
								$py = $sy + $y;

								if ($py > $height)
								{
									break;
								}

								for ($px = min($left, $width - 1); $px > $left -12 && $px >= 0; $px -= 1)
								{
									$color = imagecolorat($img, $px, $py) & 0xff;

									if ($color + $opacity < 170)
									{
										if ($shift > $left - $px)
										{
											$shift = $left - $px;
										}

										break;
									}
								}

								break;
							}
						}
					}

					if ($shift == 10000)
					{
						$shift = mt_rand(4, 6);
					}
				}
			}
			else
			{*/
				$shift = 1;
			/*}*/

			// Наносим символ на рисунок CAPTCHA
			imagecopy($img, $font, $x, mt_rand(3, 5), $m['start'], 1, $m['end'] - $m['start'], $fontfile_height);

			// Курсор после последнего нанесенного символа
			$x += $m['end'] - $m['start'] - $shift;
		}

		$center = $x / 2;

		// Итоговое изображение CAPTCHA
		$img2 = imagecreatetruecolor($width, $height);

		imagefilledrectangle($img2, 0, 0, $width - 1, $height - 1, imagecolorallocate($img2, $backgroundColor[0], $backgroundColor[1], $backgroundColor[2]));

		// Использовать генерацию фонов
		if (self::$_config['fillBackground'])
		{
			// Радиус
			$radius = mt_rand($this->_polygonMinRadius, $this->_polygonMaxRadius);

			// Количество углов многоугольника
			$corners = mt_rand($this->_polygonMinCorners, $this->_polygonMaxCorners);

			$t = 0;

			// Двигаемся сверху вниз по изображению
			for ($y = 0; $y < $height + 3 * $radius; $y += sqrt(3) * $radius / 2)
			{
				$t++;

				// Смещение многоугольника вправо на каждой второй строке на полтора радиуса
				$x = fmod($t, 2) == 0 ? 0 : 1.5 * $radius;

				// Двигаемся слева направа по изображению
				for (; $x < $width + 2 * $radius; $x += 3 * $radius)
				{
					// Наносим многоугольники
					$this->_dawPolygon($img2, $x, $y, $radius, $corners, $ind);
				}
			}
		}

		// Частоты
		$rand1 = mt_rand(700000, 1000000) / 14000000;
		$rand2 = mt_rand(700000, 1000000) / 14000000;
		$rand3 = mt_rand(700000, 1000000) / 14000000;
		$rand4 = mt_rand(700000, 1000000) / 14000000;

		// Фазы
		$rand5 = mt_rand(0, 3141592) / 1000000;
		$rand6 = mt_rand(0, 3141592) / 1000000;
		$rand7 = mt_rand(0, 3141592) / 1000000;
		$rand8 = mt_rand(0, 3141592) / 1000000;

		// Амплитуды
		$rand9 = mt_rand(400, 600) / 100;
		$rand10 = mt_rand(400, 600) / 100;

		// Искажение
		for ($x = 0; $x < $width; $x++)
		{
			for ($y = 0; $y < $height; $y++)
			{
				$sx = intval($x + (sin($x * $rand1 + $rand5) + sin($y * $rand3 + $rand6)) * $rand9 - $width / 2 + $center + 1);
				$sy = intval($y + (sin($x * $rand2 + $rand7) + sin($y * $rand4 + $rand8)) * $rand10);

				if ($sx < 0 || $sy < 0 || $sx >= $width - 1 || $sy >= $height - 1)
				{
					continue;
				}
				else
				{
					$color = (imagecolorat($img, $sx, $sy) >> 16) & 0xFF;
					$color_x = (imagecolorat($img, $sx + 1, $sy) >> 16) & 0xFF;
					$color_y = (imagecolorat($img, $sx, $sy + 1) >> 16) & 0xFF;
					$color_xy = (imagecolorat($img, $sx + 1, $sy + 1) >> 16) & 0xFF;
				}

				if ($color == 255 && $color_x == 255 && $color_y == 255 && $color_xy == 255)
				{
					continue;
				}
				elseif ($color == 0 && $color_x == 0 && $color_y == 0 && $color_xy == 0)
				{
					$newred = $foreground_color[0];
					$newgreen = $foreground_color[1];
					$newblue = $foreground_color[2];
				}
				else
				{
					$frsx = $sx -floor($sx);
					$frsy = $sy -floor($sy);
					$frsx1 = 1 - $frsx;
					$frsy1 = 1 - $frsy;

					$newcolor = ($color * $frsx1 * $frsy1 + $color_x * $frsx * $frsy1 + $color_y * $frsx1 * $frsy + $color_xy * $frsx * $frsy);

					$newcolor > 255 && $newcolor = 255;
					$newcolor = $newcolor / 255;
					$newcolor0 = 1 - $newcolor;

					$newred = $newcolor0 * $foreground_color[0] + $newcolor * $backgroundColor[0];
					$newgreen = $newcolor0 * $foreground_color[1] + $newcolor * $backgroundColor[1];
					$newblue = $newcolor0 * $foreground_color[2] + $newcolor * $backgroundColor[2];
				}

				imagesetpixel($img2, intval($x), intval($y),
					intval(imagecolorallocate($img2, intval($newred), intval($newgreen), intval($newblue)))
				);
			}
		}

		// Шум
		for ($i = 0; $i < $height * self::$_config['noise']; $i++)
		{
			$pX = mt_rand(0, $width);
			$pY = mt_rand(0, $height);

			// Цвета текста
			imagesetpixel($img2, $pX, $pY,
				imagecolorallocate($img2, $foreground_color[0], $foreground_color[1], $foreground_color[2])
			);
			$pX > 0 && imagesetpixel($img2, $pX - 1, $pY,
				imagecolorallocate($img2, $foreground_color[0], $foreground_color[1], $foreground_color[2])
			);
			$pX < $width && imagesetpixel($img2, $pX + 1, $pY,
				imagecolorallocate($img2, $foreground_color[0], $foreground_color[1], $foreground_color[2])
			);
			$pY > 0 && imagesetpixel($img2, $pX, $pY - 1,
				imagecolorallocate($img2, $foreground_color[0], $foreground_color[1], $foreground_color[2])
			);
			$pY < $height && imagesetpixel($img2, $pX, $pY + 1,
				imagecolorallocate($img2, $foreground_color[0], $foreground_color[1], $foreground_color[2])
			);

			// Случайного цвета
			imagesetpixel($img2, mt_rand(0, $width), mt_rand(0, $height),
				imagecolorallocate($img2, mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100))
			);
		}

		header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
		header('X-Robots-Tag: none');
		header('Cache-Control: no-store, no-cache, must-revalidate');
		header('Cache-Control: post-check=0, pre-check=0', FALSE);
		header('Pragma: no-cache');
		header('X-Powered-By: HostCMS');

		// Масштабируем базовое изображение до необходимого
		$img3 = imagecreatetruecolor(self::$_config['width'], self::$_config['height']);
		imagecopyresampled($img3, $img2, 0, 0, 0, 0, self::$_config['width'], self::$_config['height'], $width, $height);

		PHP_VERSION_ID < 80500 && imagedestroy($img2);
		unset($img2);

		ob_start(array($this, 'contentLength'));

		$type = strtoupper($this->_type);
		if ($type == 'GIF')
		{
			header('Content-Type: image/gif');
			imagegif ($img3);
		}
		elseif ($type == 'PNG')
		{
			header('Content-Type: image/png');
			imagepng($img3, NULL, defined('PNG_QUALITY') ? PNG_QUALITY : 9);
		}
		else
		{
			header('Content-Type: image/jpeg');
			imagejpeg($img3, NULL, defined('JPG_QUALITY') ? JPG_QUALITY : 100);
		}

		PHP_VERSION_ID < 80500 && imagedestroy($img3);
		unset($img3);
		
		ob_end_flush();
	}

	/**
	 * Set Content-Length header value
	 * @param string $content content
	 * @return string
	 */
	public static function contentLength($content)
	{
		$func_overload = intval(ini_get('mbstring.func_overload'));

		header('Content-Length: ' . (
			$func_overload && ($func_overload & 2)
				? mb_strlen($content, 'latin1')
				: strlen($content)
		));

		return $content;
	}

	/**
	 * Рисование многоугольника на фоне для CAPTCHA
	 * @param GDImage $image
	 * @param int $center_x координата х центра многоугольника
	 * @param int $center_y координата у центра многоугольника
	 * @param int $radius радиус
	 * @param int $corners количество углов многоугольника
	 * @param int $ind номер массива цветов (0 - светлый / 1 - темный)
	 *
	 * @return bool
	 * @access private
	 */
	public function _dawPolygon($image, $center_x, $center_y, $radius, $corners, $ind)
	{
		// Количество углов
		$corners = $corners < 3 ? 3 : intval($corners);
		$corners = $corners > 10 ? 10 : intval($corners);

		$ind = intval($ind) > 1 ? 1 : 0;

		$radius = intval($radius);
		$center_x = intval($center_x);
		$center_y = intval($center_y);

		// Массив координат углов многоугольника
		$point = array();

		for ($f = 1; $f <= $corners; $f++)
		{
			// Координаты следующего угла многоугольника
			$point[] = $center_x + $radius * cos(2 * pi() * $f / $corners);
			$point[] = $center_y + $radius * sin(2 * pi() * $f / $corners);
		}

		// Случайный цвет
		$colors = array(array(mt_rand(200, 255), mt_rand(200, 255), mt_rand(200, 255)), array(mt_rand(0, 100), mt_rand(0, 100), mt_rand(0, 100)));

		$backgroundColor = $colors[$ind];
		$color = $colors[1 - $ind];

		$background = imagecolorallocate($image, $backgroundColor[0], $backgroundColor[1], $backgroundColor[2]);
		$foreground = imagecolorallocate($image, $color[0], $color[1], $color[2]);

		PHP_VERSION_ID < 80100
			? imagefilledpolygon($image, $point, $corners, $background)
			: imagefilledpolygon($image, $point, $background);

		PHP_VERSION_ID < 80100
			? imagepolygon($image, $point, $corners, $foreground)
			: imagepolygon($image, $point, $foreground);

		return TRUE;
	}
}