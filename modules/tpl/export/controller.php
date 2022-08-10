<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Tpls.
 *
 * @package HostCMS
 * @subpackage Tpl
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Tpl_Export_Controller extends Core_Servant_Properties
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
					$oTpl = Core_Entity::factory('Tpl')->getById($key);

					!is_null($oTpl)
						&& $this->_setObjects($oTpl);
				}
			}
		}

		return $this;
	}

	/**
	 * Set objects
	 * @param object Tpl_Model $oTpl xsl
	 * @return self
	 */
	protected function _setObjects(Tpl_Model $oTpl)
	{
		$this->_aObjects[$oTpl->name] = array(
			'version' => CURRENT_VERSION,
			'name' => $oTpl->name,
			'tpl' => $oTpl->loadTplFile(),
			'description' => $oTpl->description,
			'configs' => array()
		);

		// Configs
		$aLngs = Tpl_Controller::getLngs();
		foreach ($aLngs as $sLng)
		{
			$this->_aObjects[$oTpl->name]['configs'][$sLng] = $oTpl->loadLngConfigFile($sLng);
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
			: 'tpls';

		$fileName = $prefix . '_' . date("Y_m_d_H_i_s") . '.json';
		$fileName = str_replace(array("\r", "\n", "\0"), '', $fileName);

		header("Pragma: public");
		header("Content-Description: File Transfer");
		header("Content-Type: " . Core_Mime::getFileMime($fileName));
		header("Content-Disposition: attachment; filename = \"" . rawurlencode($fileName) . "\";");
		header("Content-Transfer-Encoding: binary");

		echo json_encode(
			count($this->_aObjects) == 1
				? reset($this->_aObjects)
				: $this->_aObjects
		);

		exit();
	}
}