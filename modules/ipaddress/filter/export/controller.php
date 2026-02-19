<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Filter_Export_Controller
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Ipaddress_Filter_Export_Controller extends Core_Servant_Properties
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
					$oIpaddress_Filter = Core_Entity::factory('Ipaddress_Filter')->getById($key);

					!is_null($oIpaddress_Filter)
						&& $this->_setObjects($oIpaddress_Filter);
				}
				else
				{
					$oIpaddress_Filter_Dir = Core_Entity::factory('Ipaddress_Filter_Dir')->getById($key);

					!is_null($oIpaddress_Filter_Dir)
						&& $this->_addFilters($oIpaddress_Filter_Dir);
				}
			}
		}

		return $this;
	}

	/**
	 * Add filters from dirs
	 * @param Ipaddress_Filter_Dir_Model $oIpaddress_Filter_Dir
	 * @return self
	 */
	protected function _addFilters(Ipaddress_Filter_Dir_Model $oIpaddress_Filter_Dir)
	{
		$aIpaddress_Filters = $oIpaddress_Filter_Dir->Ipaddress_Filters->findAll(FALSE);
		foreach ($aIpaddress_Filters as $oIpaddress_Filter)
		{
			$this->_setObjects($oIpaddress_Filter);
		}

		// subgroups
		$aIpaddress_Filter_Dirs = $oIpaddress_Filter_Dir->Ipaddress_Filter_Dirs->findAll(FALSE);
		foreach ($aIpaddress_Filter_Dirs as $oIpaddress_Filter_Dir)
		{
			$this->_addFilters($oIpaddress_Filter_Dir);
		}

		return $this;
	}

	/**
	 * Get dir name
	 * @param Ipaddress_Filter_Model $oIpaddress_Filter
	 * @return string
	 */
	protected function _getDirName(Ipaddress_Filter_Model $oIpaddress_Filter)
	{
		$aReturn = array();

		if ($oIpaddress_Filter->ipaddress_filter_dir_id)
		{
			$oIpaddress_Filter_Dir = $oIpaddress_Filter->Ipaddress_Filter_Dir;

			do {
				$aReturn[] = $oIpaddress_Filter_Dir->name;
				$oIpaddress_Filter_Dir = $oIpaddress_Filter_Dir->getParent();
			} while ($oIpaddress_Filter_Dir);

			$aReturn = array_reverse($aReturn);
		}

		return implode('/', $aReturn);
	}

    /**
     * Set objects
     * @param Ipaddress_Filter_Model $oIpaddress_Filter filter
     * @return self
     */
	protected function _setObjects(Ipaddress_Filter_Model $oIpaddress_Filter)
	{
		$this->_aObjects[$oIpaddress_Filter->name] = array(
			'version' => CURRENT_VERSION,
			'dirName' => strval($this->_getDirName($oIpaddress_Filter)),
			'name' => $oIpaddress_Filter->name,
			'description' => $oIpaddress_Filter->description,
			'json' => $oIpaddress_Filter->json,
			'active' => $oIpaddress_Filter->active,
			'mode' => $oIpaddress_Filter->mode,
			'banned' => $oIpaddress_Filter->banned,
			'datetime' => strval($oIpaddress_Filter->datetime),
			'block_ip' => $oIpaddress_Filter->block_ip,
			'sorting' => $oIpaddress_Filter->sorting
		);

		return $this;
	}

	/**
	 * Export
	 */
	public function export()
	{
		$this->_init();

		if (count($this->_aObjects))
		{
			$prefix = count($this->_aObjects) == 1
				? key($this->_aObjects)
				: 'ipaddress_filters';

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
}