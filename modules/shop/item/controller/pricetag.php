<?php
defined('HOSTCMS') || exit('HostCMS: access denied.');

require_once(CMS_FOLDER . 'modules/vendor/PHPOffice/autoload.php');
require_once(CMS_FOLDER . 'modules/vendor/Psr/simple-cache/CacheInterface.php');
require_once(CMS_FOLDER . 'modules/vendor/Picqer/autoload.php');

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

/**
 * Shop_Item_Controller_Pricetag.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Controller_Pricetag extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'fullname',
		'date',
		'horizontal',
		'vertical'
	);

	/**
	 * Printlayout model
	 * @var object
	 */
	protected $_oPrintlayout = NULL;

	/**
	 * Core_Meta model
	 * @var object
	 */
	protected $_oCore_Meta = NULL;

	/**
	 * Old worksheet object
	 * @var object
	 */
	protected $_sheet = NULL;

	/**
	 * New worksheet object
	 * @var object
	 */
	protected $_newSheet = NULL;

	/**
	 * Spreadsheet object
	 * @var object
	 */
	protected $_spreadsheet = NULL;

	/**
	 * Merged cells array
	 * @var array
	 */
	protected $_aMergedCells = array();

	/**
	 * Drawing collection array
	 * @var array
	 */
	protected $_aDrawingCollection = array();

	/**
	 * Drawing collection files array
	 * @var array
	 */
	protected $_aDrawingCollectionFiles = array();

	/**
	 * Constructor.
	 * @param Printlayout_Model $oPrintlayout print layout
	 */
	public function __construct(Printlayout_Model $oPrintlayout)
	{
		parent::__construct();

		$this->_oPrintlayout = $oPrintlayout;

		$filepath = $this->_oPrintlayout->getFilePath();

		$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
		$reader->setReadDataOnly(FALSE);
		$this->_spreadsheet = $reader->load($filepath);

		// Old worksheet
		$this->_sheet = $this->_spreadsheet->getActiveSheet();

		// Создаем новый лист
		$this->_spreadsheet->createSheet();
		$this->_newSheet = $this->_spreadsheet->getSheet(1);

		$this->_newSheet->getPageSetup()->setFitToPage(FALSE);

		// Поля
		$this->_newSheet->getPageMargins()
			->setTop($this->_sheet->getPageMargins()->getTop())
			->setBottom($this->_sheet->getPageMargins()->getBottom())
			->setLeft($this->_sheet->getPageMargins()->getLeft())
			->setRight($this->_sheet->getPageMargins()->getRight());

		$this->horizontal = $this->vertical = 1;
		$this->fullname = $this->date = '';

		$this->_oCore_Meta = new Core_Meta();
	}

	/**
	 * Execute business logic.
	 * @param array $aShop_Items shop item objects array
	 */
	public function execute($aShop_Items)
	{
		// string(5) "A1:F9"
		$sourceDimension = $this->_sheet->calculateWorksheetDataDimension();

		$this->_aMergedCells = $this->_sheet->getMergeCells();

		$this->_aDrawingCollection = (array)$this->_sheet->getDrawingCollection();

		foreach ($this->_aDrawingCollection as $drawing)
		{
			$sourceCoordinate = $drawing->getCoordinates();

			$sTempFilePath = tempnam(CMS_FOLDER . TMP_DIR, "XLS");

			$zipReader = fopen($drawing->getPath(), 'r');
			$imageContents = '';
			while (!feof($zipReader)) {
				$imageContents .= fread($zipReader, 1024);
			}
			fclose($zipReader);

			Core_File::write($sTempFilePath, $imageContents);

			$this->_aDrawingCollectionFiles[$sourceCoordinate] = $sTempFilePath;
		}

		$rangeBoundaries = Coordinate::rangeBoundaries($sourceDimension);

		$offsetX = $rangeBoundaries[1][0];
		$offsetY = $rangeBoundaries[1][1];

		$row = $col = 1;

		$rowBlock = $colBlock = 0;

		foreach ($aShop_Items as $oShop_Item)
		{
			// Клонирование
			$this->cloneRange($sourceDimension, Coordinate::stringFromColumnIndex($col) . $row, $oShop_Item);

			$col += $offsetX;
			$colBlock++;

			if ($colBlock >= $this->horizontal)
			{
				$row += $offsetY;
				$rowBlock++;

				if ($rowBlock >= $this->vertical)
				{
					$breakPoint = Coordinate::stringFromColumnIndex($col) . $row;

					// Разрыв страницы
					$this->_newSheet->setBreak($breakPoint, \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);

					$rowBlock = 0;
				}

				$colBlock = 0;
				$col = 1;
			}
		}

		// Удаляем исходный лист
		$this->_spreadsheet->removeSheetByIndex(0);

		$tmpSave = $this->_oPrintlayout->getCopyFilePath();

		$writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($this->_spreadsheet);
		$writer->save($tmpSave);

		$oCore_Templater = new Core_Templater();
		$title = $oCore_Templater
			->addObject('this', $this->_oPrintlayout)
			->setTemplate($this->_oPrintlayout->file_mask)
			->execute();

		$fileName = $title . '.xlsx';

		// Download file
		Core_File::download($tmpSave, $fileName, array('content_disposition' => 'inline'));
		Core_File::delete($tmpSave);

		// Delete TMP files
		foreach ($this->_aDrawingCollectionFiles as $sTempFilePath)
		{
			Core_File::delete($sTempFilePath);
		}

		$this->_aDrawingCollection = $this->_aDrawingCollectionFiles = array();
	}

	/**
	 * Print image
	 * @param string $value
	 */
	public static function printImage($value)
	{
		echo $value;
	}

	/**
	 * Clone range
	 * @param string $sourceDimension source dimension
	 * @param string $destinationPoint destination point
	 * @param Shop_Item_Model $oShop_Item shop item object
	 * @return self
	 * @hostcms-event Shop_Item_Controller_Pricetag.onAfterDrawing
	 */
	public function cloneRange($sourceDimension, $destinationPoint, Shop_Item_Model $oShop_Item)
	{
		$generatorPNG = new Picqer\Barcode\BarcodeGeneratorPNG();

		$oShop_Item_Barcode = $oShop_Item->Shop_Item_Barcodes->getFirst();

		$this->_oCore_Meta
			->clear()
			->addObject('shop_item', $oShop_Item)
			->addObject('shop', $oShop_Item->Shop)
			->addObject('company', $oShop_Item->Shop->Shop_Company)
			->addObject('barcode', !is_null($oShop_Item_Barcode) ? $oShop_Item_Barcode->value : '')
			->addObject('fullname', $this->fullname)
			->addObject('date', $this->date);

		// Данные
		$dataArray = $this->_sheet->rangeToArray(
			$sourceDimension,	// The worksheet range that we want to retrieve
			NULL,        	    // Value that should be returned for empty cells
			TRUE,        		// Should formulas be calculated (the equivalent of getCalculatedValue() for each cell)
			TRUE,        		// Should values be formatted (the equivalent of getFormattedValue() for each cell)
			TRUE         		// Should the array be indexed by cell row and cell column
		);

		$this->_newSheet->fromArray($dataArray, NULL, $destinationPoint);

		// Расчеты точек
		$rangeBoundaries = Coordinate::rangeBoundaries($sourceDimension);

		$aDestinationPoint = Coordinate::coordinateFromString($destinationPoint);

		$destinationCol = Coordinate::columnIndexFromString($aDestinationPoint[0]) - 1;
		$destinationRow = $aDestinationPoint[1] - 1;

		// Объединения
		foreach ($this->_aMergedCells as $sMergeCell)
		{
			$rangeMerge = Coordinate::rangeBoundaries($sMergeCell);

			$rangeMerge[0][0] = Coordinate::stringFromColumnIndex($rangeMerge[0][0] + $destinationCol);
			$rangeMerge[0][1] += $destinationRow;

			$rangeMerge[1][0] = Coordinate::stringFromColumnIndex($rangeMerge[1][0] + $destinationCol);
			$rangeMerge[1][1] += $destinationRow;

			$this->_newSheet->mergeCells(implode($rangeMerge[0]) . ':' . implode($rangeMerge[1]));
		}

		// Стили
		$aColumnsDimension = $this->_sheet->getColumnDimensions();
		$aRowsDimension = $this->_sheet->getRowDimensions();

		for ($col = $rangeBoundaries[0][0]; $col <= $rangeBoundaries[1][0]; $col++)
		{
			$sCol = Coordinate::stringFromColumnIndex($col);

			// Ширины колонок
			$column = $this->_newSheet->getColumnDimensionByColumn($destinationCol + $col);
			$column->setWidth($aColumnsDimension[$sCol]->getWidth());

			for ($row = $rangeBoundaries[0][1]; $row <= $rangeBoundaries[1][1]; $row++)
			{
				// Высоты строк
				$rowDim = $this->_newSheet->getRowDimension($destinationRow + $row);
				$rowDim->setRowHeight($aRowsDimension[$row]->getRowHeight());

				// Копируем стили до данных
				$this->_newSheet->duplicateStyle(
					$this->_sheet->getStyle($sCol . $row),
					Coordinate::stringFromColumnIndex($destinationCol + $col) . ($destinationRow + $row)
				);

				// Подмена значения ячейки
				$sourceValue = $this->_sheet->getCell($sCol . $row);
				if (strlen($sourceValue))
				{
					$destinationCell = $this->_newSheet->getCell(Coordinate::stringFromColumnIndex($destinationCol + $col) . ($destinationRow + $row));

					$destinationCell->setValueExplicit($this->_oCore_Meta->apply($sourceValue), $destinationCell->getDataType());
				}
			}
		}

		// Картинки
		foreach ($this->_aDrawingCollection as $drawing)
		{
			$sourceCoordinate = $drawing->getCoordinates();

			if (isset($this->_aDrawingCollectionFiles[$sourceCoordinate]))
			{
				$aSourceCoordinate = Coordinate::coordinateFromString($sourceCoordinate);

				$newCoordinate = Coordinate::stringFromColumnIndex(Coordinate::columnIndexFromString($aDestinationPoint[0]) + (Coordinate::columnIndexFromString($aSourceCoordinate[0]) - $rangeBoundaries[0][0])) . ($aDestinationPoint[1] + $aSourceCoordinate[1] - $rangeBoundaries[0][1]);

				if ($drawing->getName() != 'Barcode')
				{
					Core_Event::notify('Shop_Item_Controller_Pricetag.onAfterDrawing', $this, array($oShop_Item, $drawing, $newCoordinate, $destinationPoint));

					$newDrawing = Core_Event::getLastReturn();

					if (is_null($newDrawing))
					{
						// Новая картинка
						$newDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
						$newDrawing->setName($destinationPoint . $drawing->getName());
						$newDrawing->setPath($this->_aDrawingCollectionFiles[$sourceCoordinate]);
						$newDrawing->setCoordinates($newCoordinate);
						$newDrawing->setOffsetX($drawing->getOffsetX());
						$newDrawing->setOffsetY($drawing->getOffsetY());
						$newDrawing->setWidthAndHeight($drawing->getWidth(), $drawing->getHeight());
					}

					$newDrawing->setWorksheet($this->_newSheet);
				}
				else
				{
					if (!is_null($oShop_Item_Barcode))
					{
						//  Add the In-Memory image to a worksheet
						$newContentDrawing = new \PhpOffice\PhpSpreadsheet\Worksheet\ContentDrawing();
						$newContentDrawing->setName($destinationPoint . $drawing->getName());
						$newContentDrawing->setCoordinates($newCoordinate);

						// Barcode
						$type = $this->_getBarcodeType($oShop_Item_Barcode, $generatorPNG);
						$barcode = !is_null($type)
							? $generatorPNG->getBarcode($oShop_Item_Barcode->value, $type)
							: '';

						$newContentDrawing->setImageResource($barcode);
						$newContentDrawing->setRenderingFunction(__CLASS__ . '::printImage');
						$newContentDrawing->setMimeType('image/png');
						$newContentDrawing->setOffsetX($drawing->getOffsetX());
						$newContentDrawing->setOffsetY($drawing->getOffsetY());
						$newContentDrawing->setWidthAndHeight($drawing->getWidth(), $drawing->getHeight());
						$newContentDrawing->setWorksheet($this->_newSheet);
					}
				}
			}
		}

		return $this;
	}

	/**
	 * Get barcode type
	 * @param Shop_Item_Barcode_Model $oShop_Item_Barcode shop item barcode object
	 * @param $generatorPNG Picqer\Barcode\BarcodeGeneratorPNG object
	 * @return int
	 */
	protected function _getBarcodeType(Shop_Item_Barcode_Model $oShop_Item_Barcode, $generatorPNG)
	{
		$return = NULL;

		switch ($oShop_Item_Barcode->type)
		{
			case 1:
				$return = $generatorPNG::TYPE_EAN_8;
			break;
			case 2:
			case 3:
				$return = $generatorPNG::TYPE_EAN_13;
			break;
			case 4:
				$return = $generatorPNG::TYPE_CODE_128;
			break;
			case 5:
				$return = $generatorPNG::TYPE_CODE_39;
			break;
		}

		return $return;
	}
}