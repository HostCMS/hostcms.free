<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Visitor_Filter_Export_Controller
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
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
			}
		}

		return $this;
	}

	/**
	 * Set objects
	 * @param object Ipaddress_Visitor_Filter_Model $oIpaddress_Visitor_Filter filter
	 * @return self
	 */
	protected function _setObjects(Ipaddress_Visitor_Filter_Model $oIpaddress_Visitor_Filter)
	{
		$this->_aObjects[$oIpaddress_Visitor_Filter->name] = array(
			'version' => CURRENT_VERSION,
			'name' => $oIpaddress_Visitor_Filter->name,
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
			);

			exit();
		}
	}
}