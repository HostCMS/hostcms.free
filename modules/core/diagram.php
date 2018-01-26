<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Diagrams
 *
 * @package HostCMS
 * @subpackage Core
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Diagram extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'legend',
		'values',
		'abscissa',
		'point',
		'inversion',
		'showPoints',
		'showOrigin',
		'scaleDivision',
		'fontWidth',
		'fontSize',
		'fontName',
		'horizontalOrientation',
	);

	/**
	 * Path to fonts
	 * @var string
	 */
	protected $_fontPath = NULL;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		$this
			->legend(array())
			->abscissa(array())
			->values(array())
			->inversion(FALSE)
			->showPoints(TRUE)
			->showOrigin(TRUE) // 0,0
			->scaleDivision(NULL)
			->fontWidth(8) // Ширина одного символа в легенде
			->fontSize(8) // Размер шрифта
			->fontName('didactgothic.ttf') // Шрифт
			->horizontalOrientation(0)
			;
		$this->_fontPath = CMS_FOLDER . 'modules/core/fonts/';
	}

	/**
	 * Массив основных цветов
	 * @var array
	 */
	protected $color = array(
		// основные цвета, используемые для рисования диаграммы
		'0' => array('r' => 154, 'g' => 205, 'b' => 50),
		'1' => array('r' => 255, 'g' => 99, 'b' => 71),
		'2' => array('r' => 251, 'g' => 195, 'b' => 0),
		'3' => array('r' => 100, 'g' => 149, 'b' => 237),
		'4' => array('r' => 138, 'g' => 43, 'b' => 226),
		'5' => array('r' => 58, 'g' => 166, 'b' => 0),
		'6' => array('r' => 210, 'g' => 180, 'b' => 140), // 72, 20, 245
		'7' => array('r' => 0, 'g' => 206, 'b' => 209),
		'8' => array('r' => 255, 'g' => 0, 'b' => 255),
		'9' => array('r' => 255, 'g' => 140, 'b' => 0),
		'10' => array('r' => 95, 'g' => 158, 'b' => 160),
		'11' => array('r' => 244, 'g' => 164, 'b' => 96),
		'12' => array('r' => 218, 'g' => 3, 'b' => 18),
		'13' => array('r' => 153, 'g' => 50, 'b' => 204),
		'14' => array('r' => 72, 'g' => 20, 'b' => 245),
		'15' => array('r' => 220, 'g' => 20, 'b' => 60),
		'16' => array('r' => 189, 'g' => 183, 'b' => 107),
		'17' => array('r' => 218, 'g' => 165, 'b' => 32),
		// системные цвета, используемые для рисования текста, фона
		'18' => array('r' => 250, 'g' => 250, 'b' => 250),
		'19' => array('r' => 220, 'g' => 220, 'b' => 220),
		'20' => array('r' => 250, 'g' => 250, 'b' => 250),
		'21' => array('r' => 0, 'g' => 0, 'b' => 0), // Черный
		'22' => array('r' => 255, 'g' => 255, 'b' => 255), // Белый
		'23'=> array('r'=> 230, 'g'=> 230, 'b'=> 230)
	);

	/**
	 * <code>
	 * $oCore_Diagram
	 *		->abscissa($abscissa)
	 *		->legend($legend)
	 *		->values($data)
	 *		->lineChart();
	 * </code>
	 */
	public function lineChart()
	{
		header('Content-Type: image/png');

		$im = imagecreatetruecolor(700, 280);
		imagefill($im, 0, 0, imagecolorallocate($im, 255, 255, 255));

		$scaleDivision = $this->scaleDivision;

		// Минимальное расстояние между делениями по оси х
		$min_division_x = 17;

		// Максимальное значение
		$max_val = $this->values[0][0];

		// Максимальная длина подписи по оси у
		$max_leght_y = 0;

		// Минимальное значение
		$min_val = $this->values[0][0];

		// Максимальная длина подписи по оси x
		$max_leght_x = 0;

		// Ориентация подписей по оси х
		$orientation_x = 1; // 0 - вертикальная, снизу вверх; 1 - горизонтальная, слева направо

		// Количество наборов данных
		$countValues = 0;

		// Максимальное число точек
		$count_value = 0;

		// Рассчитываем минимальные и максимальные значения
		foreach ($this->values as $key => $arr_val)
		{
			if ($count_value < count($this->values[$key]))
			{
				$count_value = count($this->values[$key]);
			}

			$values[$key] = $arr_val;

			$countValues++ ;

			foreach ($arr_val as $key2 => $val)
			{
				if ($val > $max_val)
				{
					// максимальный элемент массива значений
					$max_val = $val;
				}

				if ($max_leght_y < mb_strlen($val))
				{
					// максимальная длина подписей по оси у
					$max_leght_y = mb_strlen($val);
				}

				if ($min_val > $val)
				{
					// минимальный элемент массива значений
					$min_val = $val;
				}
			}
		}
		$this->values = $values;

		$abscissa = $this->abscissa;
		foreach ($abscissa as $key2 => $val)
		{
			if ($max_leght_x < mb_strlen($val))
			{
				// максимальная длина подписей по оси х
				$max_leght_x = mb_strlen($val);

				if ($max_leght_x > $min_division_x)
				{
					// Обрезаем длинные подписи по оси х
					$abscissa[$key2] = mb_substr($abscissa[$key2], 0, 11) . "…";
				}
			}
		}
		$this->abscissa = $abscissa;

		$lenght_max_val = mb_strlen(abs(trim($max_val)));
		$lenght_min_val = mb_strlen(abs(trim($min_val)));

		// Явно указано число делений
		if (!is_null($scaleDivision))
		{
			$current_step = ceil($max_val / $scaleDivision);
			$lenght_step = mb_strlen(abs(trim($current_step)));

			$power = pow(10, $lenght_step - 1);
			$step = ceil($current_step / $power) * $power;

			$max_val_y = $step * $scaleDivision;
		}
		else
		{
			$lenght_max_val = mb_strlen(abs(trim($max_val)));

			// Рассчитывается автоматически
			$power = pow(10, $lenght_max_val - 1);

			$current_step = ceil($max_val / $power);
			$max_val_y = $current_step * $power;

			$scaleDivision = $max_val_y / $power;

			// Минимум 5 делений
			if ($scaleDivision < 5)
			{
				$scaleDivision = 5;
			}
		}

		if ($lenght_min_val > 0)
		{
			$min_val_y = 0;
			//$min_val_y = $min_val;
			//$min_val_y = floor($min_val /(pow(10, $lenght_min_val - 1))) * pow(10, $lenght_min_val - 1);
		}
		else
		{
			// $min_val_y = $min_val;
			$min_val_y = floor($min_val /(pow(10, $lenght_min_val - 2))) * pow(10, $lenght_min_val - 2);
		}

		// Если все значения одинаковы или разница между максимальным и минимальным не больше 1, то количество делений по оси у ставим равным 2

		$delta = $max_val - $min_val;
		if ($delta < 5)
		{
			switch ($delta)
			{
				case 0:
					$scaleDivision = 2;
					$min_val_y = $max_val - ceil($max_val * 0.5);
					$max_val_y = $max_val + ceil($max_val * 0.5);
				break;
				case 1:
					$scaleDivision = 2;
					if ($min_val_y == 0 && $max_val_y == 1)
					{
						$min_val_y = 0;
						$max_val_y = 2;
					}
				break;
				default:
					$scaleDivision = $delta;
				break;
			}
		}

		if ($max_val == $min_val && $min_val == 0)
		{
			$scaleDivision = 2;
			$min_val_y = 1;
			$max_val_y = -1;
		}

		// Учитываем длины максимального и минимального значений по оси у
		if ($max_leght_x < mb_strlen($max_val_y))
		{
			$max_leght_x = mb_strlen($max_val_y);
		}

		if ($max_leght_x < mb_strlen($min_val_y))
		{
			$max_leght_x = mb_strlen($min_val_y);
		}

		// Легенда
		$image_width = ImageSX($im) - 2;
		$image_height = ImageSY($im) - 2;

		// Цвет текста - черный
		$text_color = imagecolorallocate($im, 0, 0, 0);

		// Граница столбцов легенды
		$separator =($image_width - 2) / 2;

		// Максимально возможная длина надписи легенды
		// - 35 Отступ линии от левого края легенды; - 10 Размер линии в легенде; - 10 Отступ надписи от линии в легенде
		$max_length_legend = $separator - 35 - 10 - $this->fontWidth * 3 - 10;

		// Заполняем недостающие значения нулями
		for ($i = 0; $i < $count_value; $i++)
		{
			$values = $this->values;
			foreach ($values as $key => $arr_val)
			{
				if (!isset($values[$key][$i]))
				{
					$values[$key][$i] = 0;
				}
			}
			$this->values = $values;
		}

		$legend = $this->legend;
		foreach ($legend as $key => $name)
		{
			// Обрезаем длинные надписи легенды
			if (mb_strlen($name) > $max_length_legend)
			{
				$legend[$key] = mb_substr($name, 0, $max_length_legend - 3) . "…";
			}
		}
		$this->legend = $legend;

		// Сравниваем раcстояние между чекпойнтами и максимальную длину подписи по оси х
		// Если не помещается в ширину $min_division_x, то выводим вертикально
		if ($max_leght_x * $this->fontWidth >= $min_division_x)// min_division_x - Расстояние между чекпойнтами
		{
			// вертикальная
			$orientation_x = 0;
		}

		// Задаём размер шрифта, которым мы будем выводить легенду
		$this->fontSize = 8;

		// Высота шрифта
		$font_height = $this->fontSize + 9;

		// Высота легенды
		// $font_height * 2 Отступы текста легенды от верхнего и нижнего краев легенды
		$legend_height =($this->fontSize + 6) * ceil($countValues / 2) + $this->fontSize * 2;

		// координаты верхнего угла легенды
		$x_pos_1_leg = 2;
		$y_pos_1_leg = $image_height - $legend_height;

		// координаты нижнего угла легенды
		$XPos2_leg = $image_width - 2;
		$YPos2_leg = $image_height - 2;

		// Границы легенды
		$this->_drawFilledRoundedRectangle($im, $x_pos_1_leg + 2, $y_pos_1_leg + 2, $XPos2_leg + 2, $YPos2_leg + 2, 5, $this->colors($im, 19));
		$this->_drawFilledRoundedRectangle($im, $x_pos_1_leg + 1, $y_pos_1_leg + 1, $XPos2_leg + 1, $YPos2_leg + 1, 5, $this->colors($im, 18));

		// Вывод текста легенды и цветных линий
		// Координата х1 текста
		$text_x = 35 + 10 + 10; // + 35 Отступ линии от левого края легенды; + 10 Размер линии в легенде; + 10 Отступ надписи от линии в легенде

		// Координата х1 линии
		$line_x = 35; // + 35 Отступ линии от левого края легенды;

		// Координата у1
		$y = $y_pos_1_leg + $font_height + 2;

		$j = 0;
		$dy = 0;

		for ($i = 0; $i < $countValues; $i++)
		{
			if ($i % 2 == 0)
			{
				$dx = $x_pos_1_leg;
				// Приращение y
				$dy = $i * $this->fontSize;
			}
			else
			{
				$dx = $separator;
			}

			// вывод текста
			imagettftext($im, $this->fontSize, 0 , $text_x + $dx, $y + $dy, $text_color, $this->_fontPath . $this->fontName, $this->legend[$i]);

			// вывод линий
			$this->_drawFilledRectangle($im, $line_x + $dx, $y + $dy - $this->fontSize / 2 - 2, $line_x + $dx + 10, $y + 4 + $dy - $this->fontSize / 2 - 2, $this->colors($im, $i), true);

			imagefilledellipse($im, $line_x + $dx + 5, $y + $dy - $this->fontSize / 2 , 6, 6, $this->colors($im, $i));
			imagefilledellipse($im, $line_x + $dx + 5, $y + $dy - $this->fontSize / 2 , 2, 2, $this->colors($im, 22));

			// Расстояние между строками
			$j += 5;
		}
		// /Легенда

		$x1 = $max_leght_x * 5.5 + 3 + 2; // * 4 Ширина символа, + + Расстояние между подписями по оси у и левым краем рисунка, + 3 Расстояние между подписями по оси у и рисками
		$y1 = 10;
		$x2 = $image_width - 10; // - 10 Отступ графика от правого края рисунка

		// Если вертикальная ориентация
		if (!$orientation_x)
		{
			//$max_leght_x * $this->fontSize заменено 70
			// - 10 Отступ графика от легенды; - 5 Отступ графика от подписей по оси х
			$y2 = $y_pos_1_leg - 70 - 10 - 5;
		}
		else
		{
			// - 10 Отступ графика от легенды; - 5 Отступ графика от подписей по оси х
			$y2 = $y_pos_1_leg - $this->fontSize - 10 - 5;
		}

		// Фон
		// Шаг делений по осям
		$point_x = $count_value > 1
			? ($x2 - $x1) / ($count_value - 1)
			: 0;
		$point_y = ($y2 - $y1) / $scaleDivision;

		// Светлый фон
		$this->_drawFilledRectangle($im,$x1,$y1,$x2, $y2,$this->colors($im, 18), false);

		// Темный фон
		$color = $this->_getShade($im, 18, - 10);

		// Темные полосы фона
		for ($i = 1; $i <= $scaleDivision; $i++)
		{
			$this->_drawFilledRectangle($im, $x1, $y2 - $point_y *($i - 1), $x2, $y2 - $point_y * $i, $color, false);
			$i++ ;
		}

		// Сетка
		// Цвет пунктира 1
		$color1 = $this->colors($im, 23);

		// Цвет пунктира 2
		$color2 = $this->colors($im, 18);

		// Стиль пунктира
		$style = array($color1, $color1, $color1, $color1, $color1, $color2, $color2, $color2, $color2, $color2);
		imagesetstyle($im, $style);

		// горизонтальные
		for ($i = 0; $i <= $scaleDivision; $i++)
		{
			imageline($im, $x1, $y1 + $point_y * $i, $x2, $y1 + $point_y * $i, IMG_COLOR_STYLED);
		}

		// Максимальное количество делений по оси х
		$max_division_x = floor(($x2 - $x1) /($min_division_x));

		// вертикальные пунктирные линии

		// Цвет пунктира 1
		$color1 = $this->colors($im, 23);

		// Цвет пунктира 2
		$color2 = $this->colors($im, 18);

		// Стиль пунктира
		$style = array($color1, $color1, $color1, $color1, $color1, $color2, $color2, $color2, $color2, $color2);
		imagesetstyle($im, $style);

		if (count($this->abscissa) > $max_division_x)
		{
			// Расстояние между делениями
			$lenght = 0;

			for ($i = 0; $i < $count_value; $i++)
			{
				//
				if ($i == 0)
				{
					$lenght = 0;
				}
				else
				{
					$lenght += $point_x;

					if ($lenght > $min_division_x)
					{
						// Рисуем пунктир
						imageline($im, $x1 + $point_x * $i, $y1, $x1 + $point_x * $i, $y2, IMG_COLOR_STYLED);

						// Присваиваем длине начальное значение, равное расстоянию между двумя делениями
						$lenght = 0;
					}
				}
			}
		}
		// Значений меньше, чем максимально возможное
		else
		{
			for ($i = 0; $i < $count_value; $i++)
			{
				// Рисуем пунктир
				imageline($im, $x1 + $point_x * $i, $y1, $x1 + $point_x * $i, $y2, IMG_COLOR_STYLED);
			}
		}

		// Контур графика
		imagerectangle($im,$x1,$y1,$x2,$y2,$this->_getShade($im, 18, -40));

		// Оси
		// Цвет осей и рисок
		$color = $this->_getShade($im, 19, - 50);

		// Цвет подписей
		$text_color = $this->colors($im, 21);

		// y
		imageline($im, $x1, $y1, $x1, $y2 + 5, $color);
		// x
		imageline($im, $x1 - 5, $y2, $x2, $y2, $color);

		// Величина одного деления
		$val_division =($max_val_y - $min_val_y) / $scaleDivision;

		// y
		for ($i = 0; $i <= $scaleDivision; $i++)
		{
			// вывод подписей
			$str = $min_val_y + $val_division * $i;

			// ADD 17.10.2008//

			// Приводим к целому значению
			$str = round($str);

			// END OF ADD 17.10.2008//

			// деления
			imageline($im, $x1 - 3, $y1 + $point_y * $i, $x1 + 3, $y1 + $point_y * $i, $color);

			// Инвертирование графика
			if ($this->inversion)
			{
				imagettftext($im, $this->fontSize, 0, $x1 - 6 - mb_strlen($str) * 5.5 - 2, $y1 + $point_y * $i + $this->fontSize / 2, $text_color, $this->_fontPath . $this->fontName, $str);

			}
			else
			{
				imagettftext($im, $this->fontSize, 0, $x1 - 6 - mb_strlen($str) * 5.5 - 2, $y2 - $point_y * $i + $this->fontSize / 2, $text_color, $this->_fontPath . $this->fontName, $str);
			}
		}

		// В одном пикселе значений
		$koef_y =($y2 - $y1) /($max_val_y - $min_val_y);

		// Отмечаем ноль на оси у
		if ($this->showOrigin)
		{
			if ($min_val_y < 0)
			{
				$Oy =(0 - $min_val_y) * $koef_y;

				// Цвет пунктира 2
				$color2 = $this->_getShade($im, 23, - 40);
				// Стиль пунктира
				$style = array($color1, $color1, $color1, $color1, $color1, $color2, $color2, $color2, $color2, $color2);
				imagesetstyle($im, $style);

				// Нулевая линия
				imageline($im, $x1 - 3, $y2 - $Oy, $x2, $y2 - $Oy, IMG_COLOR_STYLED);
			}
		}

		// Находим нулевую точку оси у
		if ($min_val_y < 0)
		{
			// Инверсия графика
			$y0 = $this->inversion
				? $y1 + abs($min_val_y) * $koef_y
				: $y2 - abs($min_val_y) * $koef_y;
		}
		else
		{
			// Инверсия графика
			$y0 = $this->inversion
				? $y1 - abs($min_val_y) * $koef_y
				: $y2 + abs($min_val_y) * $koef_y;
		}

		// Ось x и построение графика
		// Максимальное количество делений по оси х
		$max_division_x = floor(($x2 - $x1) /($min_division_x));

		// Значений больше, чем максимально возможное число делений
		if (count($this->abscissa) > $max_division_x)
		{
			//$n =($x2 - $x1) / $max_division_x /($x2 - $x1) / count($this->abscissa);
			// Количество элементов массива, приходящихся на одно деление оси х
			$count_in_division = count($this->abscissa) / $max_division_x;

			// Максимальное количество элементов массива, которые можно разместить на оси х
			//$max_place = $count_value / $count_in_division;

			// Коэффициент делений
			$m =($x2 - $x1) / $max_division_x;

			// Построение графика
			foreach ($this->values as $key => $arr_val)
			{
				// Расстояние между делениями
				$lenght = 0;

				if ($key !== 'x')
				{
					for ($i = 0; $i < $count_value; $i++)
					{
						//Вывод Чекпойнта для первого значения
						if ($i == 0)
						{
							$lenght = 0;
							if ($this->showPoints)
							{
								// Инверсия графика
								if ($this->inversion)
								{
									if ($arr_val[0] >= 0)
									{
										imagefilledellipse($im, $x1, $y0 + $arr_val[0] * $koef_y, 6, 6, $this->colors($im, $key));
										imagefilledellipse($im, $x1, $y0 + $arr_val[0] * $koef_y, 2, 2, $this->colors($im, 22));
									}
								}
								else
								{
									imagefilledellipse($im, $x1, $y0 - $arr_val[0] * $koef_y, 6, 6, $this->colors($im, $key));
									imagefilledellipse($im, $x1, $y0 - $arr_val[0] * $koef_y, 2, 2, $this->colors($im, 22));
								}
							}
						}
						else
						{
							$lenght += $point_x;
							if ($lenght > $min_division_x)
							{
								if ($this->showPoints)
								{
									// Инверсия графика
									if ($this->inversion)
									{
										if ($arr_val[0] >= 0)
										{
											imagefilledellipse($im, $x1 + $point_x * $i, $y0 + $arr_val[$i] * $koef_y, 6, 6, $this->colors($im, $key));
											imagefilledellipse($im, $x1 + $point_x * $i, $y0 + $arr_val[$i] * $koef_y, 2, 2, $this->colors($im, 22));
										}
									}
									else
									{
										imagefilledellipse($im, $x1 + $point_x * $i, $y0 - $arr_val[$i] * $koef_y, 6, 6, $this->colors($im, $key));
										imagefilledellipse($im, $x1 + $point_x * $i, $y0 - $arr_val[$i] * $koef_y, 2, 2, $this->colors($im, 22));
									}
								}
								// Присваиваем длине начальное значение, равное расстоянию между двумя делениями
								$lenght = 0;
							}
						}
						// Индекс следующего элемента
						$j = $i + 1;

						if ($j > $count_value - 1)
						{
							$j = $i;
						}

						// График
						// Инверсия графика
						if ($this->inversion)
						{
							if ($arr_val[$i] >= 0 && $arr_val[$j] >= 0)
							{
								$this->_drawLine($im, $x1 + $point_x * $i, $y0 + $arr_val[$i] * $koef_y, $x1 + $point_x * $j, $y0 + $arr_val[$j] * $koef_y, $key, 0);
							}
						}
						else
						{
							$this->_drawLine($im, $x1 + $point_x * $i, $y0 - $arr_val[$i] * $koef_y, $x1 + $point_x * $j, $y0 - $arr_val[$j] * $koef_y, $key, 0);
						}

					}
				}
			}

			$lenght = 0;

			// Подписи по оси х
			for ($i = 0; $i < $count_value; $i++)
			{
				// Подпись под первым значением
				if ($i == 0)
				{
					$lenght = 0;
					$str = $this->abscissa[0];

					// Провееряем ориентацию подписей
					if ($orientation_x)
					{
						imagettftext($im, $this->fontSize, 0, $x1 - mb_strlen($this->values["x"][0]) * $this->fontWidth / 2, $y2 + 5 + $this->fontSize + 3, $text_color, $this->_fontPath . $this->fontName, $str);
					}
					else
					{
						//$y2 + 5 + mb_strlen($str) * $this->fontWidth + 3
						imagettftext($im, $this->fontSize, 90, $x1 - $this->fontSize / 2 + $this->fontSize, $y2 + 70, $text_color, $this->_fontPath . $this->fontName, $str);
					}
					imageline($im, $x1, $y2 + 3 , $x1, $y2 - 3, $color);
				}
				else
				{
					$lenght += $point_x;
					// Выводим остальные подписи
					if ($lenght > $min_division_x)
					{
						$str = $this->abscissa[$i];

						// Провееряем ориентацию подписей
						if ($orientation_x)// горизонтальная
						{
							imagettftext($im, $this->fontSize, 0, $x1 + $point_x * $i - mb_strlen($this->values["x"][$i]) * $this->fontWidth / 2, $y2 + 5 + $this->fontSize + 3, $text_color, $this->_fontPath . $this->fontName, $str);
						}
						else // вертикальная
						{
							//$y2 + 5 + mb_strlen($str) * $this->fontWidth + 3
							imagettftext($im, $this->fontSize, 90, $x1 + $point_x * $i - $this->fontSize / 2 + $this->fontSize, $y2 + 70, $text_color, $this->_fontPath . $this->fontName, $str);
						}

						// Деления
						imageline($im, $x1 + $point_x * $i, $y2 + 3 , $x1 + $point_x * $i, $y2 - 3, $color);
						$lenght = 0;
					}
				}
			}
		}
		// Значений меньше, чем максимально возможное
		else
		{
			foreach ($this->values as $key => $arr_val)
			{
				// Расстояние между делениями
				$lenght = 0;

				if ($key !== 'x')
				{
					//$n = 1;
					//$m =($x2 - $x1) /(count($this->abscissa) - 1);

					for ($i = 0; $i < $count_value; $i++)
					{
						// График
						// Индекс следующего элемента
						$j = $i + 1;
						if ($j > $count_value - 1)
						{
							$j = $i;
						}

						// График
						if ($this->inversion)
						{
							if ($arr_val[$i] != 0 && $arr_val[$j] != 0)
							{
								$this->_drawLine($im, $x1 + $point_x * $i, $y0 + $arr_val[$i] * $koef_y, $x1 + $point_x * $j, $y0 + $arr_val[$j] * $koef_y, $key, 0);
							}
						}
						else
						{
							$this->_drawLine($im, $x1 + $point_x * $i, $y0 - $arr_val[$i] * $koef_y, $x1 + $point_x * $j, $y0 - $arr_val[$j] * $koef_y, $key, 0);
						}

						if ($this->showPoints)
						{
							if ($this->inversion)
							{
								if ($arr_val[$i] != 0)
								{
									imagefilledellipse($im, $x1 + $point_x * $i, $y0 + $arr_val[$i] * $koef_y, 6, 6, $this->colors($im, $key));
									imagefilledellipse($im, $x1 + $point_x * $i, $y0 + $arr_val[$i] * $koef_y, 2, 2, $this->colors($im, 22));
								}
							}
							else
							{
								imagefilledellipse($im, $x1 + $point_x * $i, $y0 - $arr_val[$i] * $koef_y, 6, 6, $this->colors($im, $key));
								imagefilledellipse($im, $x1 + $point_x * $i, $y0 - $arr_val[$i] * $koef_y, 2, 2, $this->colors($im, 22));
							}

						}
					}
				}
			}

			for ($i = 0; $i < $count_value; $i++)
			{
				// Выводим подписи
				$str = $this->abscissa[$i];

				// Провееряем ориентацию подписей
				if ($orientation_x)// горизонтальная
				{
					imagettftext($im, $this->fontSize, 0, $x1 + $point_x * $i - mb_strlen($this->values["x"][$i]) * $this->fontWidth / 2, $y2 + 5 + $this->fontSize + 3, $text_color, $this->_fontPath . $this->fontName, $str);
				}
				else // вертикальная
				{
					//$y2 + 5 + mb_strlen($str) * $this->fontWidth + 3
					imagettftext($im, $this->fontSize, 90, $x1 + $point_x * $i - $this->fontSize / 2 + $this->fontSize, $y2 + 70, $text_color, $this->_fontPath . $this->fontName, $str);
				}

				// Деления
				imageline($im, $x1 + $point_x * $i, $y2 + 3 , $x1 + $point_x * $i, $y2 - 3, $color);
			}
		}

		imagepng($im);
		imagedestroy($im);

		return $this;
	}

	/**
	 * Метод возвращает идентификатор основного цвета
	 *
	 * @param int $im Ресурс
	 * @param int $color_id Порядковый номер цвета
	 * @return int $return Идентификатор основного цвета
	 */
	protected function colors($im, $color_id)
	{
		switch ($color_id)
		{
			// Основные цвета, используемые для рисования диаграммы
			default:
			case 0:
				$return = imagecolorallocate($im, 154, 205, 50);
			break;
			case 1:
				$return = imagecolorallocate($im, 255, 99, 71);
			break;
			case 2:
				$return = imagecolorallocate($im, 251, 195, 0);
			break;
			case 3:
				$return = imagecolorallocate($im, 100, 149, 237);
			break;
			case 4:
				$return = imagecolorallocate($im, 138, 43, 226);
			break;
			case 5:
				$return = imagecolorallocate($im, 58, 166, 0);
				break;
			case 6:
				$return = imagecolorallocate($im, 210, 180, 140);
				break;
			case 7:
				$return = imagecolorallocate($im, 0, 206, 209);
				break;
			case 8:
				$return = imagecolorallocate($im, 255, 0, 255);
				break;
			case 9:
				$return = imagecolorallocate($im, 255, 140, 0);
				break;
			case 10:
				$return = imagecolorallocate($im, 95, 158, 160);
				break;
			case 11:
				$return = imagecolorallocate($im, 244, 164, 96);
				break;
			case 12:
				$return = imagecolorallocate($im, 218, 3, 18);
				break; //
			case 13:
				$return = imagecolorallocate($im, 153, 50, 204);
				break;
			case 14:
				$return = imagecolorallocate($im, 72, 20, 245);
				break;
			case 15:
				$return = imagecolorallocate($im, 220, 20, 60);
				break;
			case 16:
				$return = imagecolorallocate($im, 189, 183, 107);
				break;
			case 17:
				$return = imagecolorallocate($im, 218, 165, 32);
				break;
				// Системные цвета, используются для рисования фона
			case 18:
				$return = imagecolorallocate($im, 250, 250, 250);
				break;
			case 19:
				$return = imagecolorallocate($im, 220, 220, 220);
				break;
			case 20:
				$return = imagecolorallocate($im, 250, 250, 250);
				break;
				// Черный
			case 21:
				$return = imagecolorallocate($im, 0, 0, 0);
				break;
				// Белый
			case 22:
				$return = imagecolorallocate($im, 255, 255, 255);
				break;
				// Серый
			case 23:
				$return = imagecolorallocate($im, 230, 230, 230);
				break;
		}

		return $return;
	}

	/**
	 * Метод для рисования заполненного округленного прямоугольника
	 *
	 * @param int $im Ресурс
	 * @param int $X1 Координата х верхнего левого угла прямоугольника
	 * @param int $Y1 Координата у верхнего левого угла прямоугольника
	 * @param int $X2 Координата х правого нижнего угла прямоугольника
	 * @param int $Y2 Координата у правого нижнего угла прямоугольника
	 * @param int $radius Радиус округления углов
	 * @param int $bg_color Цвет заливки
	 */
	protected function _drawFilledRoundedRectangle($im, $X1, $Y1, $X2, $Y2, $radius, $bg_color)
	{
		// Прямоугльник
		imagefilledpolygon($im, array(
			$X1 + $radius, $Y1,
			$X1 + $radius, $Y2,
			$X2 - $radius, $Y2,
			$X2 - $radius, $Y1),
			4, $bg_color);

		// Прямоугольник
		imagefilledpolygon($im, array(
			$X1, $Y1 + $radius,
			$X1, $Y2 - $radius,
			$X2, $Y2 - $radius,
			$X2, $Y1 + $radius),
			4, $bg_color);

		// Эллипсы между соответствующих углов прямоугольников
		imagefilledellipse($im, $X1 + $radius, $Y1 + $radius, $radius * 2, $radius * 2, $bg_color);
		imagefilledellipse($im, $X1 + $radius, $Y2 - $radius, $radius * 2, $radius * 2, $bg_color);
		imagefilledellipse($im, $X2 - $radius, $Y1 + $radius, $radius * 2, $radius * 2, $bg_color);
		imagefilledellipse($im, $X2 - $radius, $Y2 - $radius, $radius * 2, $radius * 2, $bg_color);
	}

	/**
	 * Метод для рисования незаполненного округленного прямоугольника.
	 *
	 * @param int $im Ресурс
	 * @param int $X1 Координата х верхнего левого угла прямоугольника
	 * @param int $Y1 Координата у верхнего левого угла прямоугольника
	 * @param int $X2 Координата х правого нижнего угла прямоугольника
	 * @param int $Y2 Координата у правого нижнего угла прямоугольника
	 * @param int $radius Радиус округления углов
	 * @param int $bg_color Цвет заливки
	 */
	protected function _drawRoundedRectangle($im, $X1, $Y1, $X2, $Y2, $radius, $bg_color)
	{
		// Стороны прямоугольника
		imageline($im, $X1 + $radius, $Y1, $X2 - $radius, $Y1, $bg_color);
		imageline($im, $X1, $Y1 + $radius, $X1, $Y2 - $radius, $bg_color);
		imageline($im, $X2, $Y1 + $radius, $X2, $Y2 - $radius, $bg_color);
		imageline($im, $X1 + $radius, $Y2, $X2 - $radius, $Y2, $bg_color);
		// Эллипсы по углам
		imagearc($im, $X2 - $radius, $Y2 - $radius, $radius * 2, $radius * 2, 0, 90, $bg_color);
		imagearc($im, $X1 + $radius, $Y2 - $radius, $radius * 2, $radius * 2, 90, 180, $bg_color);
		imagearc($im, $X1 + $radius, $Y1 + $radius, $radius * 2, $radius * 2, 180, 270, $bg_color);
		imagearc($im, $X2 - $radius, $Y1 + $radius, $radius * 2, $radius * 2, 270, 360, $bg_color);
	}

	/**
	 * Метод для рисования заполненного прямоугольника
	 *
	 * @param int $im Ресурс
	 * @param int $X1 Координата х верхнего левого угла прямоугольника
	 * @param int $Y1 Координата у верхнего левого угла прямоугольника
	 * @param int $X2 Координата х правого нижнего угла прямоугольника
	 * @param int $Y2 Координата у правого нижнего угла прямоугольника
	 * @param int $color Цвет заливки
	 * @param boolean $DrawBorder рисовать обводку прямоугольника, по умолчанию TRUE
	 */
	protected function _drawFilledRectangle($im, $X1, $Y1, $X2, $Y2, $color, $DrawBorder = TRUE)
	{
		// Заполненный прямоугольник
		imagefilledrectangle($im, $X1, $Y1, $X2, $Y2, $color);
		// Обводка прямоугольника
		$DrawBorder && imagerectangle($im, $X1, $Y1, $X2, $Y2, $this->colors($im, 20));
	}

	/**
	 * Метод для рисования линий поточечно
	 *
	 * @param int $im Ресурс
	 * @param int $X1 Х - координата начала линии
	 * @param int $Y1 У - координата начала линии
	 * @param int $X2 Х - координата конца линии
	 * @param int $Y2 У - координата конца линии
	 * @param int $color_id Идентификатор цвета
	 * @param int $color_factor Коэффициент изменения цвета
	 * @return boolean true в случае успешного выполнения, false иначе
	 */
	protected function _drawLine($im, $X1, $Y1, $X2, $Y2, $color_id, $color_factor = 0)
	{
		// Получаем массив цвета
		$color = $this->color[$color_id];

		// Получаем нужный оттенок
		$color['r'] = $color['r'] + $color_factor;
		$color['g'] = $color['g'] + $color_factor;
		$color['b'] = $color['b'] + $color_factor;

		if ($color['r'] < 0) { $color['r'] = 0; } if ($color['r'] > 255) { $color['r'] = 255; }
		if ($color['g'] < 0) { $color['g'] = 0; } if ($color['g'] > 255) { $color['g'] = 255; }
		if ($color['b'] < 0) { $color['b'] = 0; } if ($color['b'] > 255) { $color['b'] = 255; }

		// Длина линии
		$length = sqrt(($X2 - $X1) *($X2 - $X1) +($Y2 - $Y1) *($Y2 - $Y1));

		if ($length == 0)
		{
			return FALSE;
		}

		// Шаг
		$XStep =($X2 - $X1) / $length;
		$YStep =($Y2 - $Y1) / $length;

		$line_width = 1.2;

		for ($i = 0; $i <= $length; $i++)
		{
			$X = $i * $XStep + $X1;
			$Y = $i * $YStep + $Y1;

			// Ширина линии 1 пиксел
			if ($line_width == 1)
			{
				$this->_drawPixel($im, $X, $Y, $color["r"], $color["g"], $color["b"]);
			}
			else
			{
				$start_point = -($line_width / 3);
				$end_point =($line_width / 3);

				for ($j = $start_point; $j <= $end_point; $j++)
				{
					$this->_drawPixel($im, $X + $j, $Y + $j, $color["r"], $color["g"], $color["b"], $color_factor);
				}
			}
		}

		return TRUE;
	}

	/**
	 * Рисование точки
	 *
	 * @param int $im Ресурс
	 * @param int $X Координата х
	 * @param int $Y Координата у
	 * @param int $R Значение красного цвета
	 * @param int $G Значение зеленого цвета
	 * @param int $B Значение голубого цвета
	 * @param int $color_factor Коэффициент смещения оттенка
	 * @return boolean true в случае успешного выполнения, false иначе
	 */
	protected function _drawPixel($im, $X, $Y, $R, $G, $B, $color_factor = 0)
	{
		$R = $R + $color_factor;
		$G = $G + $color_factor;
		$B = $B + $color_factor;

		// Проверяем, не превышают ли значения цветов допустимые пределы
		$R = $this->_correctColorIndex($R);
		$G = $this->_correctColorIndex($G);
		$B = $this->_correctColorIndex($B);

		$Xi = floor($X);
		$Yi = floor($Y);

		if ($Xi == $X && $Yi == $Y)
		{
			$color_pixel = imagecolorallocate($im, $R, $G, $B);
			imagesetpixel($im, $X, $Y, $color_pixel);
		}
		else
		{
			$quality = 10;
			$alpha_1 =(1 -($X - floor($X))) *(1 -($Y - floor($Y))) * 100;
			if ($alpha_1 > $quality)
			{
				$this->_drawAlphaPixel($im, $Xi, $Yi, $alpha_1, $R, $G, $B);
			}

			$alpha_2 =($X - floor($X)) *(1 -($Y - floor($Y))) * 100;
			if ($alpha_2 > $quality)
			{
				$this->_drawAlphaPixel($im, $Xi + 1, $Yi, $alpha_2, $R, $G, $B);
			}

			$alpha_3 =(1 -($X - floor($X))) *($Y - floor($Y)) * 100;
			if ($alpha_3 > $quality)
			{
				$this->_drawAlphaPixel($im, $Xi, $Yi + 1, $alpha_3, $R, $G, $B);
			}

			$alpha_4 =($X - floor($X)) *($Y - floor($Y)) * 100;
			if ($alpha_4 > $quality)
			{
				$this->_drawAlphaPixel($im, $Xi + 1, $Yi + 1, $alpha_4, $R, $G, $B);
			}
		}

		return TRUE;
	}

	/**
	 * Рисования точки с оттенком
	 *
	 * @param int $im Ресурс
	 * @param int $X Координата х
	 * @param int $Y Координата у
	 * @param int $alpha Коэффициент изменения цвета
	 * @param int $R Значение красного цвета
	 * @param int $G Значение зеленого цвета
	 * @param int $B Значение голубого цвета
	 * @return boolean true в случае успешного выполнения, false иначе
	 */
	protected function _drawAlphaPixel($im, $X, $Y, $alpha, $R, $G, $B)
	{
		$R = $this->_correctColorIndex($R);
		$G = $this->_correctColorIndex($G);
		$B = $this->_correctColorIndex($B);

		$X = $X < 0 ? 0 : $X;
		$Y = $Y < 0 ? 0 : $Y;

		$RGB2 = imagecolorat($im, $X, $Y);
		$R2 =($RGB2 >> 16) & 0xFF;
		$G2 =($RGB2 >> 8) & 0xFF;
		$B2 = $RGB2 & 0xFF;

		$iAlpha = (100 - $alpha) / 100;
		$alpha = $alpha / 100;

		$Ra = floor($R * $alpha + $R2 * $iAlpha);
		$Ga = floor($G * $alpha + $G2 * $iAlpha);
		$Ba = floor($B * $alpha + $B2 * $iAlpha);

		imagesetpixel($im, $X, $Y, imagecolorallocate($im, $Ra, $Ga, $Ba));
		return TRUE;
	}

	/**
	 * Оттенок цвета
	 *
	 * @param int $im Ресурс
	 * @param int $color_id Идентификатор исходного цвета
	 * @param int $color_factor Коэффициент смещения оттенка
	 * @return int $color Идентификатор оттенка
	 */
	protected function _getShade($im, $color_id, $color_factor)
	{
		$r = $this->color[$color_id]["r"] + $color_factor;
		$g = $this->color[$color_id]["g"] + $color_factor;
		$b = $this->color[$color_id]["b"] + $color_factor;

		$r = $this->_correctColorIndex($r);
		$g = $this->_correctColorIndex($g);
		$b = $this->_correctColorIndex($b);

		return imagecolorallocate($im, $r, $g, $b);
	}

	/**
	 * Checks $colorIndex range
	 * @param int $colorIndex index
	 * @return int
	 */
	protected function _correctColorIndex($colorIndex)
	{
		$colorIndex = $colorIndex < 0 ? 0 : $colorIndex;
		$colorIndex = $colorIndex > 255 ? 255 : $colorIndex;
		return $colorIndex;
	}

	/**
	 * Show empty PNG image
	 * @return self
	 */
	public function emptyImage()
	{
		header('Content-Type: image/png');

		$im = imagecreate(1, 1);
		$white = imagecolorallocate($im, 255, 255, 255);
		imagefill($im, 0, 0, $white);

		imagepng($im);
		imagedestroy($im);

		return $this;
	}

	/**
	 * Круговая диаграмма
	 *
	 * @param int $width ширина изображения
	 * @param int $maxLegendLength ширина легенды
	 */
	public function pieChart($width, $maxLegendLength = 15)
	{
		header('Content-Type: image/png');

		$maxLegendLength = intval($maxLegendLength);

		$height = 18 * count($this->legend);
		$height = $height < 130 ? 130 : $height;

		$im = imagecreate(intval($width), intval($height));

		// Не учитываем отрицательные элементы, 0% и все нечисловые значения диаграммы
		$diagramm_array_numeric = array();
		$value_sum = 0;
		$j = 0;

		for ($i = 0; $i < count($this->values); $i++)
		{
			if (isset($this->values[$i]))
			{
				// Сумма значений
				$value_sum += $this->values[$i];

				if (is_numeric($this->values[$i]) && $this->values[$i] > 0)
				{
					$diagramm_array_numeric[$j++] = $this->values[$i];
				}
			}
		}

		// Минимальный процент, отображаемый на диаграмме
		$min_percent_diagr = 0.9;

		$diagramm_array = array();
		$j = 0;
		for ($i = 0; $i < count($diagramm_array_numeric); $i++)
		{
			if (isset($diagramm_array_numeric[$i]))
			{
				if ($diagramm_array_numeric[$i] / $value_sum * 100 >= $min_percent_diagr)
				{
					$diagramm_array[$j] = $diagramm_array_numeric[$i];
					$j++ ;
				}
			}
		}

		// Высчитываем длину максимальной подписи и в соответствии с ней изменяем ширину диаграммы.
		/*$max_legth = 0;
		foreach ($this->legend as $key => $val)
		{
			if ($max_legth < mb_strlen($val))
			{
				$max_legth = mb_strlen($val);
			}
		}
		$diagr_width = 3 * $max_legth * $this->fontWidth;*/

		$diagr_width = $width;
		$diagr_height = floor($diagr_width / 2.5);

		$im = imagecreatetruecolor($diagr_width, $diagr_height);
		$white = imagecolorallocate($im, 255, 255, 255);
		imagefill($im, 0, 0, $white);

		// Получим размеры изображения
		$image_width = ImageSX($im) - 2;
		$image_height = ImageSY($im) - 2;

		// Фон
		// 13 Отступ от правого края
		$this->_drawFilledRoundedRectangle($im, 4, 4, $image_width - 13, $image_height - 13, 10, $this->colors($im, 20));
		$this->_drawRoundedRectangle($im, 2, 2, $image_width - 11, $image_height - 11, 10, $this->colors($im, 20));

		// Цвет текста. Черный
		$text_color = $this->colors($im, 21);

		// Массив значений элементов диаграммы
		//$val = array();
		// Массив со значениями и легендой
		$diagramm_array = array();

		// Число значений элементов диаграммы
		$countValues = 0;

		foreach ($this->values as $key => $value)
		{
			if (isset($this->legend[$key]))
			{
				$diagramm_array [$key]['name'] = $this->legend[$key];
				$diagramm_array [$key]['value'] = $value;
			}
			else
			{
				$diagramm_array [$key]['name'] = '';
				$diagramm_array [$key]['value'] = $value;
			}
		}
		// Не учитываем отрицательные элементы, 0% и все нечисловые значения диаграммы
		$diagramm_array_numeric = array();
		$value_sum = 0;
		$j = 0;

		for ($i = 0; $i < count($this->values); $i++)
		{
			if (isset($diagramm_array[$i]['value']))
			{
				// Сумма значений
				$value_sum += $diagramm_array[$i]['value'];
				if (is_numeric($diagramm_array[$i]['value']) && $diagramm_array[$i]['value'] > 0)
				{
					$diagramm_array_numeric[$j] = $diagramm_array[$i];
					$j++ ;
				}
			}
		}

		$diagramm_array = array();
		$j = 0;
		for ($i = 0; $i < count($diagramm_array_numeric); $i++)
		{
			if (isset($diagramm_array_numeric[$i]['value']))
			{
				if ($diagramm_array_numeric[$i]['value'] / $value_sum * 100 >= $min_percent_diagr)
				{
					$diagramm_array[$j] = $diagramm_array_numeric[$i];
					$j++ ;
				}
			}
		}

		$countValues = count($diagramm_array);

		if ($countValues > 18)
		{
			$countValues = 18;
		}
		$diagramm_array = array_slice($diagramm_array, 0, $countValues);

		// Рисование легенды
		// Получим размеры изображения
		$image_width = ImageSX($im) - 2;
		$image_height = ImageSY($im) - 2;

		// Цвет текста - черный
		$text_color = $this->colors($im, 21);

		// координаты нижнего угла легенды
		$XPos2 = $image_width - 30;
		$YPos2 = $image_height - 30;

		// Посчитаем количество пунктов, от этого зависит высота легенды
		$legend_count = $countValues;

		// Посчитаем максимальную длину пункта, от этого зависит ширина легенды
		$max_length = 0;
		for ($i = 0; $i < $legend_count; $i++)
		{
			if (mb_strlen($diagramm_array[$i]['name']) > $maxLegendLength)
			{
				$diagramm_array[$i]['name'] = Core_Str::cut($diagramm_array[$i]['name'], $maxLegendLength);
			}

			if ($max_length < mb_strlen($diagramm_array[$i]['name']))
			{
				$max_length = mb_strlen($diagramm_array[$i]['name']);
			}
		}

		// Высота шрифта
		$font_height = $this->fontSize + 9;
		// Ширина легенды
		$legend_width = ($this->fontWidth * $max_length) + 5 + 5 + 10; // + 5 - отступ справа легенды от текста +5 ширина квадратика + 10 отступ слева и справа от квадратика
		// Высота легенды
		$legend_height = ($font_height * $legend_count) + 7;

		// координаты верхнего угла легенды
		$XPos1 = $XPos2 - $legend_width;
		$YPos1 = $YPos2 - $legend_height;

		// Границы легенды
		$this->_drawFilledRoundedRectangle($im, $XPos1 + 1, $YPos1 + 1, $XPos2 + 1, $YPos2 + 1, 5, $this->colors($im, 19));
		$this->_drawFilledRoundedRectangle($im, $XPos1, $YPos1, $XPos2, $YPos2, 5, $this->colors($im, 18));

		// Вывод текста легенды и цветных квадратиков
		// + 10 - отступ слева и справа от квадратика
		// +5 ширина квадратика +5 отступ текста от квадратиков
		$text_x = $XPos1 + $this->fontWidth + 10 + 5;
		$square_x = $XPos1;
		$y = $YPos1 + $this->fontSize; //
		$j = 0;

		for ($i = 0; $i < $legend_count; $i++)
		{
			$dy = $y + $i * 13;
			// вывод текста
			imagettftext($im, $this->fontSize, 0 ,$text_x, $dy + $j + $this->fontWidth, $text_color, $this->_fontPath . $this->fontName, $diagramm_array[$i]['name']);
			// вывод квадратиков
			$this->_drawFilledRectangle($im, $square_x + $this->fontWidth, $dy + $j, $square_x + 2 * $this->fontWidth, $dy + $j + $this->fontWidth, $this->colors($im, $i), true);
			$j += 4;
		}
		// /Legend

		// Сумма значений диаграммы
		$PieSum = 0;
		$ivalues = array();

		for ($i = 0; $i < $countValues; $i++)
		{
			if (isset($diagramm_array[$i]['value']))
			{
				$PieSum += $diagramm_array[$i]['value'];
				$ivalues[] = $diagramm_array[$i]['value'];
			}
		}

		// Число значений элементов диаграммы
		$countValues = 0;

		// Определяем радиус диаграммы
		if (($image_width - $legend_width) >= $image_height)
		{
			$radius = $image_height / 2 - 25;
		}
		else
		{
			$radius =($image_width - $legend_width - 100) / 2;
		}

		// Массив с координатами верхней части сегментов диаграммы
		$TopParts = array();
		// Массив с координатами нижней части сегментов диаграммы
		$BotParts = array();

		// высота сегментов диаграммы
		$tiltHeight =($radius * 60) / 100;

		// Расстояние между сегментами
		$joint_space_connection = 5;

		if ($PieSum)
		{
			// Нахлест сегментов диаграммы
			$JointConnection =(360 - $joint_space_connection * count($ivalues)) / $PieSum;
		}
		else
		{
			$JointConnection = 1;
		}

		// Процент сегмента от всей диаграммы
		$JointPercent = $value_sum ? 100 / $value_sum : 1;

		$BotParts = "";

		// Центр диаграммы
		$center_x =($image_width - $legend_width - 25) / 2;
		$center_y = $image_height / 2 - 10;

		// Высота соединяемых частей
		$joint_height = 15;

		// Вращение диаграммы вокруг оси
		$angle = 1;

		// Отклонение углов сегментов
		$c_dev = 0;

		// Расстояние между углами сегментов
		$joint_space = 5;

		// Рисование сегментов и надписей диаграммы
		foreach ($diagramm_array as $key => $array)
		{
			// Расчет координат для точек сегментов
			// Х - координата верхней части сегмента
			$XcenterPos = cos(($angle - $c_dev +($array['value'] * $JointConnection + $joint_space_connection) / 2) * pi() / 180) * $joint_space + $center_x;

			// У - координата верхней части сегмента
			$YcenterPos = sin(($angle - $c_dev +($array['value'] * $JointConnection + $joint_space_connection) / 2) * pi() / 180) * $joint_space + $center_y;

			// Х - координата нижней части сегмента
			$XcenterPos2 = cos(($angle + $c_dev +($array['value'] * $JointConnection + $joint_space_connection) / 2) * pi() / 180) * $joint_space + $center_x;

			// У - координата нижней части сегмента
			$YcenterPos2 = sin(($angle + $c_dev +($array['value'] * $JointConnection + $joint_space_connection) / 2) * pi() / 180) * $joint_space + $center_y;

			// Массивы с координатами
			$TopParts[$key][] = $XcenterPos;
			$BotParts[$key][] = $XcenterPos;
			$TopParts[$key][] = $YcenterPos;
			$BotParts[$key][] = $YcenterPos + $joint_height;

			// Надпись
			// Положение надписи
			$text_angle = $angle +($array['value'] * $JointConnection / 2);
			$TX = cos(($text_angle) * pi() / 180) *($radius + 10) + $center_x;

			// Надпись
			$capt = $array['value'] * $JointPercent;
			$caption =(round($capt, 2))."%";

			if ($text_angle > 0 && $text_angle < 180)
			{
				$TY = sin(($text_angle) * pi() / 180) * ($tiltHeight + 10) + $center_y + $joint_height + 4;
			}
			else
			{
				$TY = sin(($text_angle) * pi() / 180) * ($tiltHeight + 10) + $center_y + 4;
			}

			if ($text_angle > 90 && $text_angle < 270)
			{
				// Область расположения надписи
				$Position = imageftbbox(8, 0, $this->_fontPath . $this->fontName, $caption);

				// Ширина надписи
				$TextWidth = $Position[2] - $Position[0];
				$TX = $TX - $TextWidth;
			}
			// Рисование надписи
			imagettftext($im, $this->fontSize, 0 ,$TX, $TY, $text_color, $this->_fontPath . $this->fontName, $caption);

			// Координаты сегментов
			for ($iangle = $angle; $iangle <= $angle + $array['value'] * $JointConnection; $iangle += 0.5)
			{
				$TopX = cos($iangle * pi() / 180) * $radius + $center_x;
				$TopY = sin($iangle * pi() / 180) * $tiltHeight + $center_y;
				$TopParts[$key][] = $TopX;
				$BotParts[$key][] = $TopX;
				$TopParts[$key][] = $TopY;
				$BotParts[$key][] = $TopY + $joint_height;
			}
			// Массивы с координатами
			$TopParts[$key][] = $XcenterPos;
			$BotParts[$key][] = $XcenterPos2;
			$TopParts[$key][] = $YcenterPos;
			$BotParts[$key][] = $YcenterPos2 + $joint_height;

			$angle = $iangle + $joint_space_connection;
		}

		// Рисование сегментов
		// Низ диаграммы
		foreach ($diagramm_array as $key => $array)
		{
			// Рисование многоугольника
			imagefilledpolygon($im, $BotParts[$key],(count($BotParts[$key])) / 2, $this->_getShade($im, $key, - 20));

			// Рисование линий вокруг сегмента
			for ($j = 0; $j <= count($BotParts[$key]) - 4; $j = $j + 2)
			{
				imageline($im, $BotParts[$key][$j], $BotParts[$key][$j + 1], $BotParts[$key][$j + 2], $BotParts[$key][$j + 3], $this->_getShade($im, $key, - 20));
			}
		}

		// Слои диаграммы
		// Коэффициент смещения слоя
		$colorConnection = 30 / $joint_height;

		for ($i = $joint_height - 1; $i >= 1; $i--) // По всей высоте диаграммы
		{
			foreach ($diagramm_array as $key => $array)
			{
				$Parts = array();
				$Part = 0;

				// Смещение четных вершин слоев сегмента
				foreach ($TopParts[$key] as $val2)
				{
					$Part++ ;
					if ($Part % 2 == 1)
					{
						$Parts[] = $val2;
					}
					else
					{
						$Parts[] = $val2 + $i;
					}
				}
				// Коэффициент изменения цвета
				$colorFactor = - 20 +($joint_height - $i) * $colorConnection;

				// Цвет очередного слоя
				$color = $this->_getShade($im, $key, $colorFactor);

				// Рисование слоя
				imagefilledpolygon($im,$Parts,(count($Parts) + 1) / 2,$color);

				// Границы сегментов
				$Index = count($Parts);
				imagesetpixel($im,$Parts[0],$Parts[1],$this->_getShade($im, $key, 10));
				imagesetpixel($im,$Parts[2],$Parts[3],$this->_getShade($im, $key, 10));
				imagesetpixel($im,$Parts[$Index - 4],$Parts[$Index - 3],$this->_getShade($im, $key, 10));
			}
		}
		// Верх диаграммы
		for ($key = count($diagramm_array) - 1; $key >= 0; $key--)
		{
			// Рисование сегментов
			imagefilledpolygon($im, $TopParts[$key],(count($TopParts[$key]) + 1) / 2, $this->_getShade($im, $key, 20));
			// Линии вокруг сегментов
			for ($j = 0; $j <= count($BotParts[$key]) - 4; $j = $j + 2)
			{
				$this->_drawLine($im, $TopParts[$key][$j], $TopParts[$key][$j + 1], $TopParts[$key][$j + 2], $TopParts[$key][$j + 3], $key, 20);
			}
		}

		imagepng($im);
		imagedestroy($im);
	}

	/**
	 * Вывод гистограммы
	 *
	 * @param int $width ширина гистограммы
	 * @param int $height высота гистограммы
	 */
	public function histogram($width, $height)
	{
		$width = intval($width);
		$height = intval($height);

		if (!isset($this->values[0]))
		{
			return FALSE;
		}

		// Задаем изменяемые значения
		// Размер изображения
		$H = $height;

		// Псевдо-глубина графика
		$DX = 30;
		$DY = 20;

		// Отступы
		$MB = 20; // Нижний
		$ML = 10; // Левый
		$M = 5; // Верхний и правый отступы

		// Ширина одного символа
		$LW = imagefontwidth(2);

		// Высота одного символа
		$LH = imagefontheight(2);

		// Подсчитаем количество элементов(столбиков) на графике
		$count = count($this->values[0]);

		for ($j = 0; $j < count($this->values)-1; $j++)
		{
			$dataCount = count(Core_Array::get($this->values, $j));
			if ($dataCount > $count)
			{
				$count = $dataCount;
			}
		}

		if (!$count)
		{
			return FALSE;
		}

		$W = $count > 10 ? $width :($count * 50) + 20;
		$W = $count > 100 ? 600 : $W;
		$W = $W < 100 ? 100 : $W;

		// Подсчитаем максимальное значение
		$max = 0;
		for ($j = 0; $j < count($this->values)-1; $j++)
		{
			for ($i = 0; $i < $count; $i++)
			{
				if (isset($this->values[$j][$i]))
				{
					$max = $max < $this->values[$j][$i] ? $this->values[$j][$i] : $max;
				}
			}
		}

		$max = intval($max);
		$H1 = count($this->values) > 2 ? $H + 60 : $H;

		$im = imagecreate($W,$H1);
		// Цвет фона(белый)
		$bg[0] = imagecolorallocate($im, 255, 255, 255);
		// Цвет задней грани графика(серый)
		$bg[1] = imagecolorallocate($im, 250, 250, 250);
		// Цвет левой грани графика(серый)
		$bg[2] = imagecolorallocate($im, 250, 250, 250);
		// Цвет сетки
		$c = imagecolorallocate($im, 184, 184, 184);
		// Цвет текста
		$text = imagecolorallocate($im, 136, 136, 136);
		// Цвет для легенды
		$black = imagecolorallocate($im, 0, 0, 0);

		// Цвета для столбиков
		$bar[0][0] = imagecolorallocate($im,255,128,234);
		$bar[0][1] = imagecolorallocate($im,222,95,201);
		$bar[0][2] = imagecolorallocate($im,191,65,170);
		$bar[2][0] = imagecolorallocate($im,222,214,0);
		$bar[2][1] = imagecolorallocate($im,181,187,65);
		$bar[2][2] = imagecolorallocate($im,161,155,0);
		$bar[1][0] = imagecolorallocate($im,128,234,255);
		$bar[1][1] = imagecolorallocate($im,95,201,222);
		$bar[1][2] = imagecolorallocate($im,65,170,191);

		// Количество подписей и горизонтальных линий
		// сетки по оси Y.
		$county = count($this->values[0]);

		// Подравняем левую границу с учетом ширины подписей по оси Y
		$text_width = mb_strlen($max) * $LW;
		$ML += $text_width;

		// Вывод фона графика
		imageline($im, $ML, $M + $DY, $ML, $H - $MB, $c);
		imageline($im, $ML, $M + $DY, $ML + $DX, $M, $c);
		imageline($im, $ML, $H - $MB, $ML + $DX, $H - $MB - $DY, $c);
		imageline($im, $ML, $H - $MB, $W - $M - $DX, $H - $MB, $c);
		imageline($im, $W - $M - $DX, $H - $MB, $W - $M, $H - $MB - $DY, $c);

		imagefilledrectangle($im, $ML + $DX, $M, $W - $M, $H - $MB - $DY, $bg[1]);
		imagerectangle($im, $ML + $DX, $M, $W - $M, $H - $MB - $DY, $c);

		imagefill($im, $ML + 1, $H / 2, $bg[2]);

		// Вывод неизменяемой сетки(горизонтальные линии на нижней грани и вертикальные линии сетки на левой грани
		for ($i=1; $i < (count($this->values)-1); $i++)
		{
			imageline($im, $ML + $i * intval($DX /(count($this->values) - 1)),
				$M + $DY - $i * intval($DY /(count($this->values) - 1)),
				$ML + $i * intval($DX /(count($this->values) - 1)),
				$H - $MB - $i * intval($DY /(count($this->values) - 1)), $c);
			imageline($im, $ML + $i * intval($DX /(count($this->values) - 1)),
				$H - $MB - $i * intval($DY /(count($this->values) - 1)),
				$W - $M - $DX + $i * intval($DX /(count($this->values) - 1)),
				$H - $MB - $i * intval($DY /(count($this->values) - 1)), $c);
		}

		// Пересчитаем размеры графика с учетом подписей и отступов
		$RW = $W - $ML - $M - $DX;
		$RH = $H - $MB - $M - $DY;

		// Координаты нулевой точки графика
		$X0 = $ML + $DX;
		$Y0 = $H - $MB - $DY;

		// Вывод изменяемой сетки(вертикальные линии сетки на нижней грани графика и вертикальные линии на задней грани графика)
		for ($i = 0; $i < $count; $i++)
		{
			imageline($im,$X0 + $i *($RW / $count),$Y0,$X0 + $i *($RW / $count) - $DX,$Y0 + $DY,$c);
			imageline($im,$X0 + $i *($RW / $count),$Y0,$X0 + $i *($RW / $count),$Y0 - $RH,$c);
		}

		// Горизонтальные линии сетки задней и левой граней.
		$step = $max ? $RH / $max : 1;

		$lenght = mb_strlen($max);
		if ($lenght > 1)
		{
			$n = ceil($max / (pow(10, $lenght - 1)));
			$step = $RH / $n;
		}
		else
		{
			$n = $max;
		}

		$k = pow(10, $lenght - 1);
		// Вывод кубов для всех трех рядов
		for ($j = 0; $j < count($this->values) - 1; $j++)
		{
			for ($i = 0; $i < $count; $i++)
			{
				if (isset($this->values[$j][$i]))
				{
					$this->_imagebar($im, $X0 + $i *($RW / $count) + 4 -($j + 1) * intval($DX / 3), $Y0 +($j + 1) * intval($DY / 3), intval($RW / $count) - 4, $step / $k * $this->values[$j][$i], intval($DX / 3) - 5, intval($DY / 3) - 3, $bar[$j][0], $bar[$j][1], $bar[$j][2]);
				}
			}
		}

		// Вывод подписей по оси Y
		for ($i = 0; $i <= $n; $i++)
		{
			$lenght = mb_strlen($max) - 1;
			$str = $lenght > 0 ? $i * pow(10,$lenght) : $i;

			// Вертекальная градация
			imagestring($im, 2, $X0 - $DX - mb_strlen($str) * $LW - $ML / 4 - 2, $Y0 + $DY - $step * $i - imagefontheight(2) / 2, $str, $text);
		}

		// Вывод подписей по оси X
		$max_strlen = 0;

		if (Core_Type_Conversion::toInt($param['horizontal_orientation']) == 0)
		{
			for ($i = 0; $i < count($this->values["x"]); $i++)
			{
				$max_strlen = $max_strlen < mb_strlen($this->values["x"][$i]) ? mb_strlen($this->values["x"][$i]) : $max_strlen;
			}
			$twidth = $LW * $max_strlen + 6;
		}
		else
		{
			$max_strlen = $LH;
			$twidth = $LH;
		}

		$prev = 100000;
		// $twidth = $LW * $max_strlen + 6;
		$i = $X0 + $RW - $DX;
		while ($i > $X0 - $DX)
		{
			if ($prev - $twidth > $i)
			{
				$drawx = $i + 1 -($RW / $count) / 2;
				if ($drawx > $X0 - $DX)
				{
					$k = round(($i - $X0 + $DX) /($RW / $count)) - 1;
					if (isset($this->values["x"][$k]))
					{
						/* Горизонтальная градация */
						$str = $this->values["x"][$k];
						imageline($im, $drawx, $Y0 + $DY, $i + 1 -($RW / $count) / 2, $Y0 + $DY + 5, $text);

						/* Определяем тип вывода текста для оси Ox */
						if (Core_Type_Conversion::toInt($param['horizontal_orientation']) == 0)
						{
							// Тест отображается горизонтально
							imagestring($im, 2, $drawx + 1 -(mb_strlen($str) * $LW) / 2, $Y0 + $DY + 7, $str, $text);
						}
						else
						{
							// Текст отображается вертикально слева на право
							imagestringup($im, 2, $drawx - $LH / 2, $Y0 + $DY + 7 + mb_strlen($str) * $LW, $str, $text);
						}
					}
				}
				$prev = $i;
			}
			$i -= $RW / $count;
		}

		/* Вывод легенды, для вывода статистики по хостам, хитам, сессиям */
		if (count($this->values) > 2 && count($this->legend) > 0)
		{
			// Посчитаем количество пунктов, от этого зависит высота легенды
			$legend_count = count($this->values);

			// Посчитаем максимальную длину пункта, от этого зависит ширина легенды
			$max_length = 8;

			// Задаём размер шрифта
			$fontSize = $this->fontSize;
			$font_h = $fontSize + 1;

			// Вывод прямоугольника - границы легенды
			$l_width = 80;
			$l_height = 10 * count($this->legend) + 5;

			// Получим координаты верхнего левого угла прямоугольника - границы легенды
			$l_x1 = $X0 - 40;
			$l_y1 = $Y0 + 45;

			/* Вывод прямоугольника - границы легенды */
			ImageRectangle($im, $l_x1, $l_y1, $l_x1 + $l_width, $l_y1 + $l_height, $black);

			/* Вывод текста легенды и цветных квадратиков */
			$text_x = $l_x1 + 5 + 3 + $font_h;
			$square_x = $l_x1 + 3;
			$y = $l_y1 + 3;
			$i = 0;

			foreach ($this->legend as $v)
			{
				$dy = $y +($i * $font_h);
				imagettftext($im,$fontSize,0,$text_x, $dy + $font_h, $black, $this->_fontPath . $this->fontName, $v);
				ImageFilledRectangle($im, $square_x + 2,$dy + 2,$square_x + $font_h,$dy + $font_h, $bar[$i][1]);
				ImageRectangle($im, $square_x + 2,$dy + 2,$square_x + $font_h,$dy + $font_h, $black);
				$i++;
			}
		}

		header("Content-Type: image/png");
		imagepng($im);
		imagedestroy($im);
		return TRUE;
	}

	/**
	 * Рисование BAR
	 *
	 * @param int $im идентификатор изображения
	 * @param int $x координата x верхнего левого угла куба
	 * @param int $y координата y верхнего левого угла куба
	 * @param int $w ширина куба
	 * @param int $h высота куба
	 * @param int $dx смещение задней грани куба по оси X
	 * @param int $dy смещение задней грани куба по оси Y
	 * @param string $c1 цвет видимой граней куба
	 * @param string $c2 цвет видимой граней куба
	 * @param string $c3 цвет видимой граней куба
	 */
	protected function _imagebar($im, $x, $y, $w, $h, $dx, $dy, $c1, $c2, $c3)
	{
		if ($dx > 0)
		{
			imagefilledpolygon($im, array(
				$x, $y - $h,
				$x + $w, $y - $h,
				$x + $w + $dx, $y - $h - $dy,
				$x + $dx, $y - $dy - $h
				), 4, $c1);

			imagefilledpolygon($im, array(
			$x + $w, $y - $h,
			$x + $w, $y,
			$x + $w + $dx, $y - $dy,
			$x + $w + $dx, $y - $dy - $h
			), 4, $c3);
		}
		imagefilledrectangle($im, $x, $y - $h, $x + $w, $y, $c2);

		return $this;
	}
}