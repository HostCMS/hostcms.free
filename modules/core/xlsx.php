<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * HostCMS XLSX generator
 * Легковесный генератор XLSX файлов с поддержкой нескольких листов,
 * форматов (в т.ч. дат), стилей, фильтров и настройки ширины колонок.
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Xlsx
{
	/**
	 * Массив всех листов и их данных
	 * @var array
	 */
	protected $_sheets = array();

	/**
	 * Индекс текущего активного листа (начиная с 0)
	 * @var integer
	 */
	protected $_activeSheetIndex = -1;

	/**
	 * Конструктор
	 */
	public function __construct()
	{
		$this->addSheet('Sheet1');
	}

	/**
	 * Добавить новый лист в книгу и сделать его активным.
	 * Очищает название от запрещенных символов и обрезает до 31 символа.
	 *
	 * @param string $name Название нового листа
	 * @return self
	 */
	public function addSheet($name)
	{
		$name = str_replace(array('\\', '/', '?', '*', ':', '[', ']'), '', $name);
		$name = mb_substr($name, 0, 31);

		$this->_sheets[] = array(
			'name' => $name,
			'rows' => array(),
			'cellOverrides' => array(),
			'columnWidths' => array(),
			'autoFilter' => FALSE,
			'zoomScale' => 100
		);

		$this->_activeSheetIndex = count($this->_sheets) - 1;

		return $this;
	}

	/**
	 * Переключиться на существующий лист по его индексу для дальнейшей работы с ним.
	 *
	 * @param int $index Индекс листа (0 - первый лист, 1 - второй и т.д.)
	 * @return self
	 */
	public function setActiveSheet($index)
	{
		$index = (int)$index;
		if (isset($this->_sheets[$index]))
		{
			$this->_activeSheetIndex = $index;
		}
		return $this;
	}

	/**
	 * Изменить имя текущего активного листа.
	 *
	 * @param string $name Новое имя листа
	 * @return self
	 */
	public function setSheetName($name)
	{
		$name = str_replace(array('\\', '/', '?', '*', ':', '[', ']'), '', $name);
		$this->_sheets[$this->_activeSheetIndex]['name'] = mb_substr($name, 0, 31);
		return $this;
	}

	/**
	 * Добавить одну строку данных на текущий лист.
	 *
	 * @param array $data Массив значений для строки (например: ['ID', 'Название'])
	 * @return self
	 */
	public function addRow(array $data)
	{
		$this->_sheets[$this->_activeSheetIndex]['rows'][] = array_values($data);
		return $this;
	}

	/**
	 * Добавить сразу несколько строк данных на текущий лист.
	 *
	 * @param array $data Двумерный массив строк
	 * @return self
	 */
	public function addRows(array $data)
	{
		foreach ($data as $row)
		{
			$this->addRow($row);
		}
		return $this;
	}

	/**
	 * Включить или выключить автофильтр для текущего листа (применится ко всей области данных).
	 *
	 * @param boolean $enable TRUE для включения, FALSE для выключения
	 * @return self
	 */
	public function enableAutoFilter($enable = true)
	{
		$this->_sheets[$this->_activeSheetIndex]['autoFilter'] = (bool)$enable;
		return $this;
	}

	/**
	 * Установить масштаб отображения (зум) для текущего листа при открытии в Excel.
	 *
	 * @param int $scale Значение масштаба в процентах (от 10 до 400)
	 * @return self
	 */
	public function setZoomScale($scale)
	{
		$scale = (int)$scale;
		if ($scale >= 10 && $scale <= 400)
		{
			$this->_sheets[$this->_activeSheetIndex]['zoomScale'] = $scale;
		}
		return $this;
	}

	/**
	 * Установить фиксированную ширину для указанной колонки.
	 *
	 * @param string $colLetter Буква колонки (например 'A', 'B', 'AA')
	 * @param float $width Ширина колонки в символах стандартного шрифта Excel
	 * @return self
	 */
	public function setColumnWidth($colLetter, $width)
	{
		$this->_sheets[$this->_activeSheetIndex]['columnWidths'][strtoupper($colLetter)] = (float)$width;
		return $this;
	}

	/**
	 * Преобразовать стандартную дату или Unix Timestamp в формат даты Excel (Serial Date).
	 *
	 * @param string|int $date Строка с датой или Unix Timestamp
	 * @return float Дробное число, представляющее дату в Excel
	 */
	public function dateToExcel($date)
	{
		$timestamp = is_numeric($date) ? $date : strtotime($date);
		// Корректировка на часовой пояс, чтобы Excel не исказил время
		$timestamp += date('Z', $timestamp);
		// 25569 - количество дней между 01.01.1900 (начало эпохи Excel) и 01.01.1970 (начало эпохи Unix)
		return 25569 + ($timestamp / 86400);
	}

	/**
	 * Принудительно установить или заменить значение/тип конкретной ячейки.
	 *
	 * @param string $address Адрес ячейки (например 'A1', 'B2')
	 * @param mixed $value Новое значение ячейки
	 * @param string|null $type Тип данных ('n' - число, 'inlineStr' - строка). Оставив NULL, тип определится автоматически.
	 * @return self
	 */
	public function setCell($address, $value, $type = NULL)
	{
		$address = strtoupper($address);
		if (!isset($this->_sheets[$this->_activeSheetIndex]['cellOverrides'][$address]))
		{
			$this->_sheets[$this->_activeSheetIndex]['cellOverrides'][$address] = array();
		}

		if (is_null($type))
		{
			$type = is_numeric($value) ? 'n' : 'inlineStr';
		}

		$this->_sheets[$this->_activeSheetIndex]['cellOverrides'][$address]['value'] = $value;
		$this->_sheets[$this->_activeSheetIndex]['cellOverrides'][$address]['type'] = $type;

		return $this;
	}

	/**
	 * Комплексная установка визуальных стилей для ячейки (формат, заливка, шрифт, границы).
	 *
	 * @param string $address Адрес ячейки (например 'A1')
	 * @param array $options Массив настроек стиля: ['format' => '...', 'fill' => '...', 'font' => [...], 'border' => [...]]
	 * @return self
	 */
	public function setStyle($address, array $options)
	{
		$address = strtoupper($address);
		if (!isset($this->_sheets[$this->_activeSheetIndex]['cellOverrides'][$address]))
		{
			$this->_sheets[$this->_activeSheetIndex]['cellOverrides'][$address] = array();
		}

		if (isset($options['format']))
		{
			$this->_sheets[$this->_activeSheetIndex]['cellOverrides'][$address]['format'] = (string)$options['format'];
		}

		if (isset($options['fill']))
		{
			$this->_sheets[$this->_activeSheetIndex]['cellOverrides'][$address]['color'] = $this->_formatColor($options['fill']);
		}

		if (isset($options['font']) && is_array($options['font']))
		{
			$this->_sheets[$this->_activeSheetIndex]['cellOverrides'][$address]['font'] = $options['font'];
		}

		if (isset($options['border']) && is_array($options['border']))
		{
			$this->_sheets[$this->_activeSheetIndex]['cellOverrides'][$address]['border'] = $options['border'];
		}

		return $this;
	}

	/**
	 * Преобразовать HEX-код цвета в формат ARGB, используемый в Excel.
	 *
	 * @param string $color Цвет в HEX (например 'FF0000' или '#FF0000')
	 * @return string Цвет в формате ARGB
	 */
	protected function _formatColor($color)
	{
		$color = ltrim($color, '#');
		if (strlen($color) === 6)
		{
			$color = 'FF' . $color; // Добавляем FF для 100% непрозрачности
		}
		return strtoupper($color);
	}

	/**
	 * Преобразовать числовой индекс колонки (начиная с 0) в буквенный формат (A, B, Z, AA).
	 *
	 * @param int $index Индекс колонки (0, 1, 2...)
	 * @return string Буквенное обозначение ('A', 'B', 'C'...)
	 */
	protected function _getColumnLetter($index)
	{
		$letter = '';
		while ($index >= 0)
		{
			$letter = chr($index % 26 + 65) . $letter;
			$index = floor($index / 26) - 1;
		}
		return $letter;
	}

	/**
	 * Преобразовать буквенный формат колонки (A, B, AA) в числовой индекс (1-based, используется для XML).
	 *
	 * @param string $letter Буквенное обозначение ('A', 'B'...)
	 * @return int Числовой индекс (1, 2, 27...)
	 */
	protected function _letterToIndex($letter)
	{
		$letter = strtoupper($letter);
		$len = strlen($letter);
		$index = 0;
		for ($i = 0; $i < $len; $i++)
		{
			$index = $index * 26 + (ord($letter[$i]) - 64);
		}
		return $index;
	}

	/**
	 * Вычислить диапазон данных для указанного листа (например 'A1:D10').
	 * Используется для автофильтров и границ области просмотра листа.
	 *
	 * @param int $sheetIndex Индекс листа
	 * @return string Диапазон ячеек
	 */
	protected function _getDimension($sheetIndex)
	{
		$rows = $this->_sheets[$sheetIndex]['rows'];
		$rowCount = count($rows);
		$maxColCount = 0;

		foreach ($rows as $row)
		{
			$cols = count($row);
			if ($cols > $maxColCount)
			{
				$maxColCount = $cols;
			}
		}

		$lastColLetter = $maxColCount > 0 ? $this->_getColumnLetter($maxColCount - 1) : 'A';
		$lastRowNum = max(1, $rowCount);

		return 'A1:' . $lastColLetter . $lastRowNum;
	}

	/**
	 * Сгенерировать XLSX архив и сохранить его по указанному пути.
	 *
	 * @param string $filePath Абсолютный путь для сохранения архива
	 * @return bool TRUE в случае успешного сохранения
	 */
	public function save($filePath)
	{
		// Собираем и дедуплицируем стили со ВСЕХ листов
		$styleData = $this->_buildStylesData();

		// Используем Core_Zip для сборки архива
		$oZip = new Core_Zip($filePath);

		// Базовые файлы структуры Office Open XML
		$oZip->addFile($this->_getContentTypesXml(), '[Content_Types].xml');
		$oZip->addFile($this->_getRelsXml(), '_rels/.rels');
		$oZip->addFile($this->_getWorkbookXml(), 'xl/workbook.xml');
		$oZip->addFile($this->_getWorkbookRelsXml(), 'xl/_rels/workbook.xml.rels');
		$oZip->addFile($this->_getStylesXml($styleData), 'xl/styles.xml');

		// Генерируем XML для каждого отдельного листа
		foreach ($this->_sheets as $index => $sheet)
		{
			$sheetId = $index + 1;
			$dimension = $this->_getDimension($index);
			$oZip->addFile($this->_getSheetXml($index, $dimension), 'xl/worksheets/sheet' . $sheetId . '.xml');
		}

		$oZip->close();

		return TRUE;
	}

	/**
	 * Собрать, сгруппировать и дедуплицировать все стили (шрифты, заливки, границы, форматы) из всех листов.
	 * Генерирует уникальные ID для каждой комбинации стилей.
	 *
	 * @return array Массив сгруппированных данных стилей
	 */
	protected function _buildStylesData()
	{
		$numFmts = array();
		$fonts = array(md5(serialize(array())) => array('id' => 0, 'settings' => array()));
		$fills = array('none' => array('id' => 0, 'color' => NULL), 'gray125' => array('id' => 1, 'color' => NULL));
		$borders = array(md5(serialize([])) => array('id' => 0, 'settings' => array()));
		$xfs = array('0_0_0_0' => array('id' => 0, 'fontId' => 0, 'fillId' => 0, 'borderId' => 0, 'numFmtId' => 0));

		$numFmtCounter = 164; // Кастомные форматы данных в Excel начинаются со 164
		$fontCounter = 1;
		$fillCounter = 2; // 0 - none, 1 - gray125 (зарезервировано Excel)
		$borderCounter = 1;
		$xfCounter = 1;

		// Проходимся по всем листам и их переопределениям ячеек
		foreach ($this->_sheets as $sheetIndex => &$sheet)
		{
			foreach ($sheet['cellOverrides'] as $address => &$override)
			{
				$formatStr = isset($override['format']) ? $override['format'] : NULL;
				$fontSettings = isset($override['font']) ? $override['font'] : array();
				$fillColor = isset($override['color']) ? $override['color'] : NULL;
				$borderSettings = isset($override['border']) ? $override['border'] : array();

				// 1. Обработка формата данных
				if ($formatStr)
				{
					if (!isset($numFmts[$formatStr]))
					{
						$numFmts[$formatStr] = array('id' => $numFmtCounter++, 'formatCode' => $formatStr);
					}
					$numFmtId = $numFmts[$formatStr]['id'];
				}
				else
				{
					$numFmtId = 0;
				}

				// 2. Обработка шрифтов
				$fontHash = md5(serialize($fontSettings));
				if (!isset($fonts[$fontHash]))
				{
					$fonts[$fontHash] = array('id' => $fontCounter++, 'settings' => $fontSettings);
				}
				$fontId = $fonts[$fontHash]['id'];

				// 3. Обработка фоновой заливки
				$fillId = 0;
				if ($fillColor)
				{
					if (!isset($fills[$fillColor]))
					{
						$fills[$fillColor] = array('id' => $fillCounter++, 'color' => $fillColor);
					}
					$fillId = $fills[$fillColor]['id'];
				}

				// 4. Обработка границ ячеек
				if (isset($borderSettings['all']))
				{
					$bAll = $borderSettings['all'];
					$borderSettings = array('left' => $bAll, 'right' => $bAll, 'top' => $bAll, 'bottom' => $bAll);
				}
				$borderHash = md5(serialize($borderSettings));
				if (!isset($borders[$borderHash]))
				{
					$borders[$borderHash] = array('id' => $borderCounter++, 'settings' => $borderSettings);
				}
				$borderId = $borders[$borderHash]['id'];

				// 5. Создание уникального комбинированного стиля (XF)
				$xfKey = $fontId . '_' . $fillId . '_' . $borderId . '_' . $numFmtId;
				if (!isset($xfs[$xfKey]))
				{
					$xfs[$xfKey] = array('id' => $xfCounter++, 'fontId' => $fontId, 'fillId' => $fillId, 'borderId' => $borderId, 'numFmtId' => $numFmtId);
				}

				// Присваиваем ячейке итоговый ID стиля
				$override['styleId'] = $xfs[$xfKey]['id'];
			}
		}

		return array('numFmts' => $numFmts, 'fonts' => $fonts, 'fills' => $fills, 'borders' => $borders, 'xfs' => $xfs);
	}

	/**
	 * Сгенерировать XML-разметку для конкретного рабочего листа.
	 *
	 * @param int $sheetIndex Индекс генерируемого листа
	 * @param string $dimension Диапазон ячеек листа
	 * @return string XML-код рабочего листа
	 */
	protected function _getSheetXml($sheetIndex, $dimension)
	{
		$sheet = $this->_sheets[$sheetIndex];

		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
		$xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';
		$xml .= '<dimension ref="' . $dimension . '"/>';

		$xml .= '<sheetViews>';
		// Делаем активным (открытым по умолчанию) только первый лист (индекс 0)
		$tabSelected = ($sheetIndex === 0) ? '1' : '0';
		$xml .= '<sheetView tabSelected="' . $tabSelected . '" workbookViewId="0" zoomScale="' . $sheet['zoomScale'] . '"/>';
		$xml .= '</sheetViews>';

		$xml .= '<sheetFormatPr defaultRowHeight="15"/>';

		// Генерация ширины колонок
		if (!empty($sheet['columnWidths']))
		{
			$xml .= '<cols>';
			foreach ($sheet['columnWidths'] as $col => $width)
			{
				$index = $this->_letterToIndex($col);
				$xml .= '<col min="' . $index . '" max="' . $index . '" width="' . $width . '" customWidth="1"/>';
			}
			$xml .= '</cols>';
		}

		$xml .= '<sheetData>';

		// Генерация строк и ячеек
		foreach ($sheet['rows'] as $rowIndex => $rowData)
		{
			$rowNum = $rowIndex + 1;
			$xml .= '<row r="' . $rowNum . '">';

			foreach ($rowData as $colIndex => $originalValue)
			{
				$colLetter = $this->_getColumnLetter($colIndex);
				$address = $colLetter . $rowNum;

				// Подключение привязанного стиля
				$styleAttr = '';
				if (isset($sheet['cellOverrides'][$address]['styleId']) && $sheet['cellOverrides'][$address]['styleId'] > 0)
				{
					$styleAttr = ' s="' . $sheet['cellOverrides'][$address]['styleId'] . '"';
				}

				// Проверка переопределения значений и типов
				if (isset($sheet['cellOverrides'][$address]) && array_key_exists('value', $sheet['cellOverrides'][$address]))
				{
					$cellValue = $sheet['cellOverrides'][$address]['value'];
					$type = $sheet['cellOverrides'][$address]['type'];
				}
				else
				{
					$cellValue = $originalValue;
					$type = is_numeric($cellValue) ? 'n' : 'inlineStr';
				}

				$xml .= '<c r="' . $address . '" t="' . $type . '"' . $styleAttr . '>';

				if ($type === 'n')
				{
					$xml .= '<v>' . htmlspecialchars((string)$cellValue) . '</v>';
				}
				else
				{
					$xml .= '<is><t>' . htmlspecialchars((string)$cellValue, ENT_XML1, 'UTF-8') . '</t></is>';
				}

				$xml .= '</c>';
			}

			$xml .= '</row>';
		}

		$xml .= '</sheetData>';

		// Добавление автофильтра в конец XML-структуры
		if ($sheet['autoFilter'])
		{
			$xml .= '<autoFilter ref="' . $dimension . '"/>';
		}

		$xml .= '</worksheet>';

		return $xml;
	}

	/**
	 * Сгенерировать XML-файл со стилями (styles.xml), общий для всей книги.
	 *
	 * @param array $styleData Массив подготовленных и дедуплицированных стилей
	 * @return string XML-код таблицы стилей
	 */
	protected function _getStylesXml($styleData)
	{
		$numFmts = $styleData['numFmts'];
		$fonts = $styleData['fonts'];
		$fills = $styleData['fills'];
		$borders = $styleData['borders'];
		$xfs = $styleData['xfs'];

		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . "\n";
		$xml .= '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">';

		if (!empty($numFmts))
		{
			$xml .= '<numFmts count="' . count($numFmts) . '">';
			foreach ($numFmts as $fmt)
			{
				$xml .= '<numFmt numFmtId="' . $fmt['id'] . '" formatCode="' . htmlspecialchars($fmt['formatCode']) . '"/>';
			}
			$xml .= '</numFmts>';
		}

		$xml .= '<fonts count="' . count($fonts) . '">';
		$sortedFonts = array();
		foreach ($fonts as $f) $sortedFonts[$f['id']] = $f['settings'];
		ksort($sortedFonts);
		foreach ($sortedFonts as $id => $settings)
		{
			$xml .= '<font>';
			if (!empty($settings['bold'])) $xml .= '<b/>';
			if (!empty($settings['italic'])) $xml .= '<i/>';
			if (!empty($settings['underline'])) $xml .= '<u/>';
			if (!empty($settings['strike'])) $xml .= '<strike/>';
			$size = !empty($settings['size']) ? (int)$settings['size'] : 11;
			$xml .= '<sz val="' . $size . '"/>';
			if (!empty($settings['color'])) $xml .= '<color rgb="' . $this->_formatColor($settings['color']) . '"/>';
			$name = !empty($settings['name']) ? htmlspecialchars($settings['name']) : 'Calibri';
			$xml .= '<name val="' . $name . '"/>';
			$xml .= '</font>';
		}
		$xml .= '</fonts>';

		$xml .= '<fills count="' . count($fills) . '">';
		$sortedFills = array();
		foreach ($fills as $f) $sortedFills[$f['id']] = $f['color'];
		ksort($sortedFills);
		foreach ($sortedFills as $id => $color)
		{
			if ($id === 0) $xml .= '<fill><patternFill patternType="none"/></fill>';
			elseif ($id === 1) $xml .= '<fill><patternFill patternType="gray125"/></fill>';
			else $xml .= '<fill><patternFill patternType="solid"><fgColor rgb="' . $color . '"/><bgColor indexed="64"/></patternFill></fill>';
		}
		$xml .= '</fills>';

		$xml .= '<borders count="' . count($borders) . '">';
		$sortedBorders = array();
		foreach ($borders as $b) $sortedBorders[$b['id']] = $b['settings'];
		ksort($sortedBorders);
		foreach ($sortedBorders as $id => $settings)
		{
			if ($id === 0)
			{
				$xml .= '<border><left/><right/><top/><bottom/><diagonal/></border>';
				continue;
			}
			$xml .= '<border>';
			foreach (array('left', 'right', 'top', 'bottom') as $side)
			{
				if (!empty($settings[$side]))
				{
					$style = !empty($settings[$side]['style']) ? $settings[$side]['style'] : 'thin';
					$xml .= '<' . $side . ' style="' . htmlspecialchars($style) . '">';
					if (!empty($settings[$side]['color'])) $xml .= '<color rgb="' . $this->_formatColor($settings[$side]['color']) . '"/>';
					else $xml .= '<color auto="1"/>';
					$xml .= '</' . $side . '>';
				}
				else $xml .= '<' . $side . '/>';
			}
			$xml .= '<diagonal/></border>';
		}
		$xml .= '</borders>';

		$xml .= '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>';

		$xml .= '<cellXfs count="' . count($xfs) . '">';
		$sortedXfs = array();
		foreach ($xfs as $xf) $sortedXfs[$xf['id']] = $xf;
		ksort($sortedXfs);
		foreach ($sortedXfs as $id => $xf)
		{
			$applyNumberFormat = $xf['numFmtId'] > 0 ? ' applyNumberFormat="1"' : '';
			$applyFont = $xf['fontId'] > 0 ? ' applyFont="1"' : '';
			$applyFill = $xf['fillId'] > 0 ? ' applyFill="1"' : '';
			$applyBorder = $xf['borderId'] > 0 ? ' applyBorder="1"' : '';
			$xml .= '<xf numFmtId="' . $xf['numFmtId'] . '" fontId="' . $xf['fontId'] . '" fillId="' . $xf['fillId'] . '" borderId="' . $xf['borderId'] . '" xfId="0"' . $applyNumberFormat . $applyFont . $applyFill . $applyBorder . '/>';
		}
		$xml .= '</cellXfs>';

		$xml .= '<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>';
		$xml .= '</styleSheet>';

		return $xml;
	}

	/**
	 * Сгенерировать XML-разметку для типов контента архива (MIME-типы и связи расширений).
	 * Включает регистрацию всех созданных листов.
	 *
	 * @return string XML-код [Content_Types].xml
	 */
	protected function _getContentTypesXml()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
	<Default ContentType="application/vnd.openxmlformats-package.relationships+xml" Extension="rels"/>
	<Default ContentType="application/xml" Extension="xml"/>
	<Override ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml" PartName="/xl/workbook.xml"/>';

		foreach ($this->_sheets as $index => $sheet)
		{
			$sheetId = $index + 1;
			$xml .= '<Override ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml" PartName="/xl/worksheets/sheet' . $sheetId . '.xml"/>';
		}

		$xml .= '<Override ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml" PartName="/xl/styles.xml"/>
</Types>';

		return $xml;
	}

	/**
	 * Сгенерировать корневой XML-файл связей (_rels/.rels).
	 * Указывает точку входа в основной файл книги.
	 *
	 * @return string XML-код связей
	 */
	protected function _getRelsXml()
	{
		return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
	<Relationship Id="rId1" Target="xl/workbook.xml" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument"/>
</Relationships>';
	}

	/**
	 * Сгенерировать главный XML-файл книги (workbook.xml).
	 * Содержит список всех листов, их имена, а также регистрирует скрытые диапазоны для автофильтров.
	 *
	 * @return string XML-код книги
	 */
	protected function _getWorkbookXml()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
	<sheets>';

		// Регистрация листов
		foreach ($this->_sheets as $index => $sheet)
		{
			$sheetId = $index + 1;
			$rId = 'rId' . $sheetId;
			$safeName = htmlspecialchars($sheet['name'], ENT_XML1, 'UTF-8');
			$xml .= '<sheet name="' . $safeName . '" sheetId="' . $sheetId . '" r:id="' . $rId . '"/>';
		}

		$xml .= '</sheets>';

		// Проверка на наличие включенных фильтров в книге
		$hasFilters = false;
		foreach ($this->_sheets as $sheet)
		{
			if ($sheet['autoFilter']) {
				$hasFilters = true;
				break;
			}
		}

		// Регистрация Defined Names для правильной работы автофильтров
		if ($hasFilters)
		{
			$xml .= '<definedNames>';
			foreach ($this->_sheets as $index => $sheet)
			{
				if ($sheet['autoFilter'])
				{
					$dimension = $this->_getDimension($index);
					$absDimension = preg_replace('/([A-Z]+)([0-9]+)/', '\$$1\$$2', $dimension);
					// Экранирование для имен листов со спецсимволами
					$safeSheetName = "'" . str_replace("'", "''", $sheet['name']) . "'";
					$xml .= '<definedName name="_xlnm._FilterDatabase" localSheetId="' . $index . '" hidden="1">' . htmlspecialchars($safeSheetName, ENT_XML1, 'UTF-8') . '!' . $absDimension . '</definedName>';
				}
			}
			$xml .= '</definedNames>';
		}

		$xml .= '</workbook>';
		return $xml;
	}

	/**
	 * Сгенерировать внутренний XML-файл связей книги (xl/_rels/workbook.xml.rels).
	 * Описывает связи между главным файлом, листами и таблицей стилей.
	 *
	 * @return string XML-код внутренних связей
	 */
	protected function _getWorkbookRelsXml()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';

		foreach ($this->_sheets as $index => $sheet)
		{
			$sheetId = $index + 1;
			$rId = 'rId' . $sheetId;
			$xml .= '<Relationship Id="' . $rId . '" Target="worksheets/sheet' . $sheetId . '.xml" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"/>';
		}

		$rIdStyles = 'rId' . (count($this->_sheets) + 1);
		$xml .= '<Relationship Id="' . $rIdStyles . '" Target="styles.xml" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"/>';
		$xml .= '</Relationships>';

		return $xml;
	}

	/**
	 * Сгенерировать файл и инициировать его скачивание клиентом через браузер.
	 * Скрипт сохраняет файл во временную директорию сервера, отправляет HTTP-заголовки,
	 * отдает контент файла, удаляет временный файл и прерывает дальнейшее выполнение.
	 *
	 * @param string $fileName Имя скачиваемого файла, которое увидит пользователь
	 * @return void (прерывает выполнение через exit)
	 */
	public function download($fileName = 'export.xlsx')
	{
		$tempFilePath = tempnam(CMS_FOLDER . TMP_DIR, 'xlsx_');

		$this->save($tempFilePath);

		// Очистка буфера перед отдачей файла, чтобы не повредить архив лишним выводом
		if (ob_get_level())
		{
			ob_end_clean();
		}

		header('Content-Description: File Transfer');
		header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
		header('Content-Disposition: attachment; filename="' . basename($fileName) . '"');
		header('Content-Transfer-Encoding: binary');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($tempFilePath));

		readfile($tempFilePath);
		@unlink($tempFilePath);

		exit;
	}
}