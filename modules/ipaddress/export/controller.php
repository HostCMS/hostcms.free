<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Export_Controller
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Ipaddress_Export_Controller extends Core_Servant_Properties
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
					$oIpaddress = Core_Entity::factory('Ipaddress')->getById($key);

					!is_null($oIpaddress)
						&& $this->_setObjects($oIpaddress);
				}
				else
				{
					$oIpaddress_Dir = Core_Entity::factory('Ipaddress_Dir')->getById($key);

					!is_null($oIpaddress_Dir)
						&& $this->_addFilters($oIpaddress_Dir);
				}
			}
		}

		return $this;
	}

	/**
	 * Add from dirs
	 * @param Ipaddress_Dir_Model $oIpaddress_Dir
	 * @return self
	 */
	protected function _addFilters(Ipaddress_Dir_Model $oIpaddress_Dir)
	{
		$aIpaddresses = $oIpaddress_Dir->Ipaddresses->findAll(FALSE);
		foreach ($aIpaddresses as $oIpaddress)
		{
			$this->_setObjects($oIpaddress);
		}

		// subgroups
		$aIpaddress_Dirs = $oIpaddress_Dir->Ipaddress_Dirs->findAll(FALSE);
		foreach ($aIpaddress_Dirs as $oIpaddress_Dir)
		{
			$this->_addFilters($oIpaddress_Dir);
		}

		return $this;
	}

	/**
	 * Get dir name
	 * @param Ipaddress_Model $oIpaddress
	 * @return string
	 */
	protected function _getDirName(Ipaddress_Model $oIpaddress)
	{
		$aReturn = array();

		if ($oIpaddress->ipaddress_dir_id)
		{
			$oIpaddress_Dir = $oIpaddress->Ipaddress_Dir;

			do {
				$aReturn[] = $oIpaddress_Dir->name;
				$oIpaddress_Dir = $oIpaddress_Dir->getParent();
			} while ($oIpaddress_Dir);

			$aReturn = array_reverse($aReturn);
		}

		return implode('/', $aReturn);
	}

    /**
     * Set objects
     * @param Ipaddress_Model $oIpaddress
     * @return self
     */
	protected function _setObjects(Ipaddress_Model $oIpaddress)
	{
		$this->_aObjects[$oIpaddress->id] = array(
			'version' => CURRENT_VERSION,
			'dirName' => strval($this->_getDirName($oIpaddress)),
			'ip' => $oIpaddress->ip,
			'deny_access' => $oIpaddress->deny_access,
			'deny_backend' => $oIpaddress->deny_backend,
			'no_statistic' => $oIpaddress->no_statistic,
			'comment' => $oIpaddress->comment,
			'banned' => $oIpaddress->banned,
			'datetime' => strval($oIpaddress->datetime)
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
				: 'ipaddresses';

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