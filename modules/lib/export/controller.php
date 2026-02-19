<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Libs.
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Lib_Export_Controller extends Core_Servant_Properties
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'controller',
	);

	/**
	 * Objects array
	 * @var array
	 */
	protected $_aObjects = array();

	/**
	 * Init
	 * @return self
	 */
	protected function _init()
	{
		$aChecked = $this->controller->getChecked();

		// Clear checked list
		$this->controller->clearChecked();

		foreach ($aChecked as $datasetKey => $checkedItems)
		{
			foreach ($checkedItems as $key => $value)
			{
				if ($datasetKey)
				{
					$oLib = Core_Entity::factory('Lib')->getById($key);

					!is_null($oLib)
						&& $this->_setObjects($oLib);
				}
				else
				{
					$oLib_Dir = Core_Entity::factory('Lib_Dir')->getById($key);

					!is_null($oLib_Dir)
						&& $this->_addDirs($oLib_Dir);
				}
			}
		}

		return $this;
	}

	/**
	 * Add from dirs
	 * @param Lib_Dir_Model $oLib_Dir
	 * @return self
	 */
	protected function _addDirs(Lib_Dir_Model $oLib_Dir)
	{
		$aLibs = $oLib_Dir->Libs->findAll(FALSE);
		foreach ($aLibs as $oLib)
		{
			$this->_setObjects($oLib);
		}

		// subgroups
		$aLib_Dirs = $oLib_Dir->Lib_Dirs->findAll(FALSE);
		foreach ($aLib_Dirs as $oLib_Dir)
		{
			$this->_addDirs($oLib_Dir);
		}

		return $this;
	}

	/**
	 * Get dir name
	 * @param Lib_Model $oLib
	 * @return string
	 */
	protected function _getDirName(Lib_Model $oLib)
	{
		$aReturn = array();

		if ($oLib->lib_dir_id)
		{
			$oLib_Dir = $oLib->Lib_Dir;

			do {
				$aReturn[] = $oLib_Dir->name;
				$oLib_Dir = $oLib_Dir->getParent();
			} while ($oLib_Dir);

			$aReturn = array_reverse($aReturn);
		}

		return implode('/', $aReturn);
	}

    /**
     * Set objects
     * @param Lib_Model $oLib lib
     * @return self
     */
	protected function _setObjects(Lib_Model $oLib)
	{
		$this->_aObjects[$oLib->name] = array(
			'version' => CURRENT_VERSION,
			'dirName' => strval($this->_getDirName($oLib)),
			'name' => $oLib->name,
			'description' => $oLib->description,
			'type' => intval($oLib->type),
			'class' => $oLib->class,
			'style' => $oLib->style,
			'lib' => $oLib->loadLibFile(),
			'lib_config' => $oLib->loadLibConfigFile(),
			'options' => array()
		);

		// Изображение
		if ($oLib->file != '')
		{
			$this->_aObjects[$oLib->name]['file'] = $oLib->file;

			$filepath = $oLib->getFilePath();

			if (Core_File::isFile($filepath))
			{
				$imageData = file_get_contents($filepath);
				$base64Image = base64_encode($imageData);
				$imageInfo = getimagesize($filepath);
				$mimeType = $imageInfo['mime'];

				$this->_aObjects[$oLib->name]['file_data'] = 'data:' . $mimeType . ';base64,' . $base64Image;
			}
		}

		// Параметры ТДС
		$aLib_Properties = $oLib->Lib_Properties->findAll(FALSE);
		foreach ($aLib_Properties as $oLib_Property)
		{
			$aLibProperty = array(
				'id' => $oLib_Property->id,
				'parent_id' => $oLib_Property->parent_id,
				'name' => $oLib_Property->name,
				'varible_name' => $oLib_Property->varible_name,
				'type' => $oLib_Property->type,
				'default_value' => $oLib_Property->default_value,
				'multivalue' => $oLib_Property->multivalue,
				'sorting' => $oLib_Property->sorting
			);

			if ($oLib_Property->type == 4)
			{
				$aLibProperty['sql_request'] = $oLib_Property->sql_request;
				$aLibProperty['sql_caption_field'] = $oLib_Property->sql_caption_field;
				$aLibProperty['sql_value_field'] = $oLib_Property->sql_value_field;
			}

			// Значения параметров ТДС
			$aLib_Property_List_Values = $oLib_Property->Lib_Property_List_Values->findAll(FALSE);
			foreach ($aLib_Property_List_Values as $oLib_Property_List_Value)
			{
				$aLibProperty['values'][] = array(
					'name' => $oLib_Property_List_Value->name,
					'value' => $oLib_Property_List_Value->value
				);
			}

			$this->_aObjects[$oLib->name]['options'][] = $aLibProperty;
		}

		return $this;
	}

	/**
	 * Export
	 */
	public function export()
	{
		$this->_init();

		$prefix = count($this->_aObjects) == 1
			? key($this->_aObjects)
			: 'libs';

		$fileName = $prefix . '_' . date("Y_m_d_H_i_s") . '.json';

		header("Pragma: public");
		header("Content-Description: File Transfer");
		header("Content-Type: " . Core_Mime::getFileMime($fileName));
		header("Content-Disposition: attachment; filename = \"" . rawurlencode(Core_Http::sanitizeHeader($fileName)) . "\";");
		header("Content-Transfer-Encoding: binary");

		echo json_encode(
			count($this->_aObjects) == 1
				? reset($this->_aObjects)
				: $this->_aObjects
			, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0
		);

		exit();
	}
}