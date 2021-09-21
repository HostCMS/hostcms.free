<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Printlayout_Controller
 *
 * @package HostCMS
 * @subpackage Printlayout
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Printlayout_Controller extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'driver',
		'replace',
		'entity'
	);

	/**
	 * Printlayout model
	 * @var object
	 */
	protected $_oPrintlayout = NULL;

	/**
	 * Document xml path
	 * @var string
	 */
	protected $_documentPath = 'word/document.xml';

	/**
	 * Document xml
	 * @var string
	 */
	protected $_documentXml = NULL;

	protected $_arrayReplace = array();
	protected $_scalarReplace = array();
	protected $_trToReplace = array();

	protected $_filePath = NULL;
	protected $_fileName = NULL;

	/**
	 * Constructor.
	 * @param Printlayout_Model $oPrintlayout print layout
	 */
	public function __construct(Printlayout_Model $oPrintlayout)
	{
		parent::__construct($oPrintlayout->clearEntities());

		$this->_oPrintlayout = $oPrintlayout;

		$this->replace = array();
		$this->entity = NULL;
	}

	/**
	 * Generate docx file
	 * @return string Generated File Path
	 * @hostcms-event Printlayout_Controller.onBeforeGenerateDocx
	 * @hostcms-event Printlayout_Controller.onAfterExecute
	 */
	protected function _generateDocx()
	{
		Core_Event::notify('Printlayout_Controller.onBeforeGenerateDocx', $this);

		$copyFilePath = $this->_oPrintlayout->getCopyFilePath();

		if (is_file($copyFilePath))
		{
			// Удаляем файл в /tmp/
			Core_File::delete($copyFilePath);
		}

		if (Core_File::copy($this->_oPrintlayout->getFilePath(), $copyFilePath))
		{
			// $zip = new ZipArchive();
			$zip = Core_Zip::getZipClass();

			$resultCode = $zip->open($copyFilePath);
			if ($resultCode !== TRUE)
			{
				$error = $this->_getError($resultCode);
				throw new Core_Exception($error);
			}

			$this->_documentXml = $zip->getFromName($this->_documentPath);

			// Удаляем файл, создаем с новым содержимым
			$zip->deleteName($this->_documentPath);

			foreach ($this->replace as $replaceSearch => $replaceValue)
			{
				is_array($replaceValue)
					? $this->_arrayReplace[$replaceSearch] = $replaceValue
					: $this->_scalarReplace[$replaceSearch] = $replaceValue;
			}

			$oXml = $this->_getXml();
			$this->_unionNodes($oXml);

			if (count($this->_trToReplace))
			{
				foreach ($this->_trToReplace as $replaceSearch => $aTR)
				{
					foreach ($aTR as $oSimpleLoadXmlTr)
					{
						if ($table = $oSimpleLoadXmlTr->xpath('ancestor::w:tbl'))
						{
							$oTable = dom_import_simplexml($table[0]);
							$oTR = dom_import_simplexml($oSimpleLoadXmlTr);

							foreach ($this->_arrayReplace[$replaceSearch] as $aItem)
							{
								// Клонируем строку таблицы w:tr
								$cloneNode = $oTR->parentNode->insertBefore($oTR->cloneNode(TRUE), $oTR);

								$oCore_Meta = new Core_Meta();
								$oCore_Meta->addObject($replaceSearch, $aItem);

								// Делаем замену для каждого w:t в клонированной ноде w:tr
								$tNodes = $cloneNode->getElementsByTagName('t');

								if ($tNodes->length)
								{
									foreach ($tNodes as $tNode)
									{
										// Может быть w:t-нода с пустым значением (или пробелом) в nodeValue
										if (strlen(trim($tNode->nodeValue)))
										{
											// Заменяем все найденные переменные на значения
											$tNode->nodeValue = $oCore_Meta->apply($tNode->nodeValue);
										}
									}
								}
							}

							// Удаляем первый TR
							$oTable->removeChild($oTR);
						}
					}
				}
			}

			$this->_documentXml = $oXml->asXml();

			$oCore_Meta = new Core_Meta();

			foreach ($this->_scalarReplace as $replaceSearch => $replaceValue)
			{
				$oCore_Meta->addObject($replaceSearch, $replaceValue);
			}

			foreach ($this->_arrayReplace as $replaceSearch => $replaceValue)
			{
				$oCore_Meta->addObject($replaceSearch, $replaceValue);
			}

			$this->_documentXml = $oCore_Meta->apply($this->_documentXml);

			// Перезаписываем word/document.xml
			$zip->addFromString($this->_documentPath, $this->_documentXml);

			// Закрываем архив
			$zip->close();

			Core_Event::notify('Printlayout_Controller.onAfterGenerateDocx', $this, array($copyFilePath));
		}

		return $copyFilePath;
	}

	protected function _getArrayByRpr($object)
	{
		$aReturn = array();
		foreach ($object->children('w', TRUE) as $rPrValue)
		{
			if ($rPrValue->getName() != 'lang')
			{
				$aReturn[] = array($rPrValue->getName(), (array)$rPrValue->attributes());
			}
		}

		return $aReturn;
	}

	protected function _unionNodes($oXml)
	{
		$aRPR = $aPrevRPR = $prevNode = NULL;

		foreach ($oXml->children('w', TRUE) as $key => $child)
		{
			if ($child->getName() == 'r')
			{
				if (isset($child->rPr))
				{
					$aRPR = $this->_getArrayByRpr($child->rPr);
				}

				if (!isset($child->br))
				{
					if ($aPrevRPR == $aRPR)
					{
						if (isset($prevNode->t) && isset($child->t))
						{
							$child->t = $prevNode->t . $child->t;
							$prevNode->t = '';
						}
					}

					$aPrevRPR = $aRPR;
					$prevNode = $child;
				}
				else
				{
					$aPrevRPR = $prevNode = NULL;
				}

				foreach ($this->_arrayReplace as $search => $replace)
				{
					if (strpos($child->t, '{' . $search . '.') !== FALSE)
					{
						$aTR = $child->xpath('ancestor::w:tr[1]');

						if (isset($aTR[0])
							&& (!isset($this->_trToReplace[$search]) || !in_array($aTR[0], $this->_trToReplace[$search]))
						)
						{
							$this->_trToReplace[$search][] = $aTR[0];
						}
					}
				}
			}

			$this->_unionNodes($child);
		}
	}

	/*
	 * Execute business logic
	 * @hostcms-event Printlayout_Controller.onBeforeExecute
	 * @hostcms-event Printlayout_Controller.onAfterExecute
	 */
	public function execute()
	{
		//$ext = Core_File::getExtension($this->_oPrintlayout->file_name);

		$docxSourcePath = $this->_generateDocx();

		Core_Event::notify('Printlayout_Controller.onBeforeExecute', $this, array($docxSourcePath));

		if ($this->driver instanceof Printlayout_Driver_Model)
		{
			$oPrintlayout_Driver_Controller = Printlayout_Driver_Controller::factory($this->driver->driver);

			$title = $this->_oPrintlayout->id;

			if (!is_null($this->entity))
			{
				$oCore_Templater = new Core_Templater();
				$title = $oCore_Templater
					->addObject('this', $this->entity)
					->setTemplate($this->_oPrintlayout->file_mask)
					->execute();
			}

			$this->_fileName = $title . '.' . $oPrintlayout_Driver_Controller->getExtension();

			$oPrintlayout_Driver_Controller
				->setFile($docxSourcePath)
				->setTitle($title)
				->execute();

			$this->_filePath = $oPrintlayout_Driver_Controller->getFile();
		}
		else
		{
			throw new Core_Exception('Printlayout: Wrong driver');
		}

		Core_Event::notify('Printlayout_Controller.onAfterExecute', $this);

		return $this;
	}

	public function getFilePath()
	{
		return $this->_filePath;
	}

	public function getFileName()
	{
		return $this->_fileName;
	}

	public function downloadFile()
	{
		if (!is_null($this->_filePath))
		{
			Core_File::download($this->getFilePath(), $this->getFileName(), array('content_disposition' => 'attachment'));

			$this->deleteFile();
		}
		else
		{
			echo "Download: Unknown ERROR";
		}
	}

	public function printFile()
	{
		if (!is_null($this->_filePath))
		{
			echo Core_File::read($this->_filePath);
		}
		else
		{
			echo "Print: Unknown ERROR";
		}
	}

	public function deleteFile()
	{
		if (is_file($this->getFilePath()))
		{
			Core_File::delete($this->getFilePath());
		}

		return $this;
	}

	/* Get simplexml object from xml
	 * @return object
	 */
	protected function _getXml()
	{
		$oXml = @simplexml_load_string($this->_documentXml);

		// Register namespaces
		$aNamespaces = $oXml->getNamespaces(TRUE);
		foreach ($aNamespaces as $key => $namespace)
		{
			$oXml->registerXPathNamespace($key, $namespace);
		}

		return $oXml;
	}

	/*
	 * Get zip error
	 * @param int $code error code
	 * @return string
	 */
	protected function _getError($code)
	{
		switch ($code)
		{
			case 0:
				return 'No error';
			case 1:
				return 'Multi-disk zip archives not supported';
			case 2:
				return 'Renaming temporary file failed';
			case 3:
				return 'Closing zip archive failed';
			case 4:
				return 'Seek error';
			case 5:
				return 'Read error';
			case 6:
				return 'Write error';
			case 7:
				return 'CRC error';
			case 8:
				return 'Containing zip archive was closed';
			case 9:
				return 'No such file';
			case 10:
				return 'File already exists';
			case 11:
				return 'Can\'t open file';
			case 12:
				return 'Failure to create temporary file';
			case 13:
				return 'Zlib error';
			case 14:
				return 'Malloc failure';
			case 15:
				return 'Entry has been changed';
			case 16:
				return 'Compression method not supported';
			case 17:
				return 'Premature EOF';
			case 18:
				return 'Invalid argument';
			case 19:
				return 'Not a zip archive';
			case 20:
				return 'Internal error';
			case 21:
				return 'Zip archive inconsistent';
			case 22:
				return 'Can\'t remove file';
			case 23:
				return 'Entry has been deleted';
			default:
				return 'An unknown error has occurred(' . intval($code) . ')';
		}
	}

	static public function getPrintButtonHtml($Admin_Form_Controller, $module_id, $type, $additionalParam, $divider = FALSE)
	{
		$printlayoutsButton = '';

		$aPrintlayouts = Core_Entity::factory('Printlayout')->getAvailable($module_id, $type);

		if (count($aPrintlayouts))
		{
			// Есть разделитель
			$divider
				&& $printlayoutsButton .= '<li class="divider"></li>';

			foreach ($aPrintlayouts as $oPrintlayout)
			{
				$onclick = $Admin_Form_Controller->getAdminLoadAjax($Admin_Form_Controller->getPath(), 'print', NULL, $additionalParam . '&type=' . $type . '&printlayout_id=' . $oPrintlayout->id);

				$printlayoutsButton .= '<li>
					<a onclick="' . $onclick . '">' . htmlspecialchars($oPrintlayout->name) . '</a>
				</li>';
			}
		}

		return $printlayoutsButton;
	}

	static public function getBackendPrintButton($oAdmin_Form_Controller, $id, $type)
	{
		$printlayoutsButton = '';

		$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
		$oShop_Group = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));

		$additionalParam = '&shop_id=' . $oShop->id . '&shop_group_id=' . $oShop_Group->id;

		// Установка цен
		if ($type == 10)
		{
			$additionalParam .= '&shop_price_id=0';
		}

		$moduleName = $oAdmin_Form_Controller->module->getModuleName();

		$oModule = Core_Entity::factory('Module')->getByPath($moduleName);

		if (!is_null($oModule))
		{
			// Печать
			$printlayoutsButton = '
				<div class="btn-group">
					<a class="btn btn-default" href="javascript:void(0);"><i class="fa fa-print"></i></a>
					<a class="btn btn-default dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" aria-expanded="false"><i class="fa fa-angle-down"></i></a>
					<ul class="dropdown-menu dropdown-default">
			';

			$printlayoutsButton .= self::getPrintButtonHtml($oAdmin_Form_Controller, $oModule->id, $type, 'hostcms[checked][0][' . $id . ']=1' . $additionalParam);

			$printlayoutsButton .= '
					</ul>
				</div>
			';
		}

		return Admin_Form_Entity::factory('Code')
			->html($printlayoutsButton)
			->execute();
	}
}