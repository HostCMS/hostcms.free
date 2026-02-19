<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Visitor_Filter_Export_Controller
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Ipaddress_Visitor_Filter_Export_Controller extends Core_Servant_Properties
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
					$oIpaddress_Visitor_Filter = Core_Entity::factory('Ipaddress_Visitor_Filter')->getById($key);

					!is_null($oIpaddress_Visitor_Filter)
						&& $this->_setObjects($oIpaddress_Visitor_Filter);
				}
				else
				{
					$oIpaddress_Visitor_Filter_Dir = Core_Entity::factory('Ipaddress_Visitor_Filter_Dir')->getById($key);

					!is_null($oIpaddress_Visitor_Filter_Dir)
						&& $this->_addFilters($oIpaddress_Visitor_Filter_Dir);
				}
			}
		}

		return $this;
	}

	/**
	 * Add filters from dirs
	 * @param Ipaddress_Visitor_Filter_Dir_Model $oIpaddress_Visitor_Filter_Dir
	 * @return self
	 */
	protected function _addFilters(Ipaddress_Visitor_Filter_Dir_Model $oIpaddress_Visitor_Filter_Dir)
	{
		$aIpaddress_Visitor_Filters = $oIpaddress_Visitor_Filter_Dir->Ipaddress_Visitor_Filters->findAll(FALSE);
		foreach ($aIpaddress_Visitor_Filters as $oIpaddress_Visitor_Filter)
		{
			$this->_setObjects($oIpaddress_Visitor_Filter);
		}

		// subgroups
		$aIpaddress_Visitor_Filter_Dirs = $oIpaddress_Visitor_Filter_Dir->Ipaddress_Visitor_Filter_Dirs->findAll(FALSE);
		foreach ($aIpaddress_Visitor_Filter_Dirs as $oIpaddress_Visitor_Filter_Dir)
		{
			$this->_addFilters($oIpaddress_Visitor_Filter_Dir);
		}

		return $this;
	}

	/**
	 * Get dir name
	 * @param Ipaddress_Visitor_Filter_Model $oIpaddress_Visitor_Filter
	 * @return string
	 */
	protected function _getDirName(Ipaddress_Visitor_Filter_Model $oIpaddress_Visitor_Filter)
	{
		$aReturn = array();

		if ($oIpaddress_Visitor_Filter->ipaddress_visitor_filter_dir_id)
		{
			$oIpaddress_Visitor_Filter_Dir = $oIpaddress_Visitor_Filter->Ipaddress_Visitor_Filter_Dir;

			do {
				$aReturn[] = $oIpaddress_Visitor_Filter_Dir->name;
				$oIpaddress_Visitor_Filter_Dir = $oIpaddress_Visitor_Filter_Dir->getParent();
			} while ($oIpaddress_Visitor_Filter_Dir);

			$aReturn = array_reverse($aReturn);
		}

		return implode('/', $aReturn);
	}

    /**
     * Set objects
     * @param Ipaddress_Visitor_Filter_Model $oIpaddress_Visitor_Filter filter
     * @return self
     */
	protected function _setObjects(Ipaddress_Visitor_Filter_Model $oIpaddress_Visitor_Filter)
	{
		$this->_aObjects[$oIpaddress_Visitor_Filter->name] = array(
			'version' => CURRENT_VERSION,
			'dirName' => strval($this->_getDirName($oIpaddress_Visitor_Filter)),
			'name' => $oIpaddress_Visitor_Filter->name,
			'description' => $oIpaddress_Visitor_Filter->description,
			'json' => $oIpaddress_Visitor_Filter->json,
			'active' => $oIpaddress_Visitor_Filter->active,
			'mode' => $oIpaddress_Visitor_Filter->mode,
			'banned' => $oIpaddress_Visitor_Filter->banned,
			'ban_hours' => $oIpaddress_Visitor_Filter->ban_hours,
			'datetime' => strval($oIpaddress_Visitor_Filter->datetime),
			'block_mode' => $oIpaddress_Visitor_Filter->block_mode,
			'sorting' => $oIpaddress_Visitor_Filter->sorting
		);

		return $this;
	}

	/**
	 * Export
	 */
	public function export()
	{
		$this->_init();

// var_dump(count($this->_aObjects));

		if (count($this->_aObjects))
		{
			$prefix = count($this->_aObjects) == 1
				? key($this->_aObjects)
				: 'ipaddress_visitor_filters';

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