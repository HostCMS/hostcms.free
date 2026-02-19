<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Xsl_Export_Controller
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Xsl_Export_Controller extends Core_Servant_Properties
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
					$oXsl = Core_Entity::factory('Xsl')->getById($key);

					!is_null($oXsl)
						&& $this->_setObjects($oXsl);
				}
				else
				{
					$oXsl_Dir = Core_Entity::factory('Xsl_Dir')->getById($key);

					!is_null($oXsl_Dir)
						&& $this->_addDirs($oXsl_Dir);
				}
			}
		}

		return $this;
	}

	/**
	 * Add from dirs
	 * @param Xsl_Dir_Model $oXsl_Dir
	 * @return self
	 */
	protected function _addDirs(Xsl_Dir_Model $oXsl_Dir)
	{
		$aXsls = $oXsl_Dir->Xsls->findAll(FALSE);
		foreach ($aXsls as $oXsl)
		{
			$this->_setObjects($oXsl);
		}

		// subgroups
		$aXsl_Dirs = $oXsl_Dir->Xsl_Dirs->findAll(FALSE);
		foreach ($aXsl_Dirs as $oXsl_Dir)
		{
			$this->_addDirs($oXsl_Dir);
		}

		return $this;
	}

	/**
	 * Get dir name
	 * @param Xsl_Model $oXsl
	 * @return string
	 */
	protected function _getDirName(Xsl_Model $oXsl)
	{
		$aReturn = array();

		if ($oXsl->xsl_dir_id)
		{
			$oXsl_Dir = $oXsl->Xsl_Dir;

			do {
				$aReturn[] = $oXsl_Dir->name;
				$oXsl_Dir = $oXsl_Dir->getParent();
			} while ($oXsl_Dir);

			$aReturn = array_reverse($aReturn);
		}

		return implode('/', $aReturn);
	}

    /**
     * Set objects
     * @param Xsl_Model $oXsl xsl
     * @return self
     */
	protected function _setObjects(Xsl_Model $oXsl)
	{
		$this->_aObjects[$oXsl->name] = array(
			'version' => CURRENT_VERSION,
			'dirName' => strval($this->_getDirName($oXsl)),
			'name' => $oXsl->name,
			'xsl' => $oXsl->loadXslFile(),
			'format' => intval($oXsl->format),
			'description' => $oXsl->description,
			'dtds' => array()
		);

		// DTD
		$aLngs = Xsl_Controller::getLngs();
		foreach ($aLngs as $sLng)
		{
			$this->_aObjects[$oXsl->name]['dtds'][$sLng] = $oXsl->loadLngDtdFile($sLng);
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
			: 'xsls';

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