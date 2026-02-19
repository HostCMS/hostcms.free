<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Visitor_Controller.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Ipaddress_Visitor_Controller
{
	/**
	 * Cleaning old data frequency
	 * @var int
	 */
	static protected $_cleaningFrequency = 500;
	
	/**
	 * Get current Ipaddress_Visitor by _h_tag cookie
	 * @return Ipaddress_Visitor_Model
	 */
	static public function getCurrentIpaddressVisitor()
	{
		$oIpaddress_Visitor = isset($_COOKIE['_h_tag']) && strlen($_COOKIE['_h_tag']) == 22
			? Core_Entity::factory('Ipaddress_Visitor')->getById($_COOKIE['_h_tag'], FALSE)
			: NULL;

		if (!$oIpaddress_Visitor)
		{
			do {
				// cut 24-char string version of $md5 eg "16C056Dl/oStNftflbnO6seQ==" to 22-char "16C056Dl/oStNftflbnO6seQ"
				$tag = substr(base64_encode(md5(Core_Guid::get())), 0, 22);
			} while (is_object(Core_Entity::factory('Ipaddress_Visitor')->getCountByid($tag, FALSE)));

			$oIpaddress_Visitor = Core_Entity::factory('Ipaddress_Visitor');
			$oIpaddress_Visitor->id = $tag;
			$oIpaddress_Visitor->ip = Core::getClientIp();
			$oIpaddress_Visitor->useragent = Core_Array::get($_SERVER, 'HTTP_USER_AGENT', '', 'str');
			$oIpaddress_Visitor->datetime = Core_Date::timestamp2sql(time());
			$oIpaddress_Visitor->site_id = CURRENT_SITE;
			$oIpaddress_Visitor->lng = strtolower(substr(Core_Array::get($_SERVER, 'HTTP_ACCEPT_LANGUAGE', '', 'str'), 0, 2));
			$oIpaddress_Visitor->visits = 1;
			$oIpaddress_Visitor->result = 1;
			
			$aHeaders = array('URI' => Core_Array::get($_SERVER, 'REQUEST_URI', '', 'str')) + Core::getallheaders();
			$oIpaddress_Visitor->headers = json_encode($aHeaders, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
			
			$oIpaddress_Visitor->create();
			
			if (rand(0, self::$_cleaningFrequency) == 0)
			{
				self::_clearOldData();
			}
		}
		else
		{
			$oCore_QueryBuilder_Update = Core_QueryBuilder::update('ipaddress_visitors')
				->set('visits', Core_QueryBuilder::raw('`visits` + 1'))
				->set('datetime', Core_Date::timestamp2sql(time()))
				->where('id', '=', $oIpaddress_Visitor->id)
				->execute();
		}

		return $oIpaddress_Visitor;
	}

    /**
     * Clear Old Data
     * @throws Core_Exception
     */
	static protected function _clearOldData()
	{
		$cleaningDate = date('Y-m-d', strtotime("-10 day"));

		$iLimit = intval(self::$_cleaningFrequency * 1.2);

		Core_DataBase::instance()->setQueryType(3)
			->query("DELETE LOW_PRIORITY QUICK FROM `ipaddress_visitors` WHERE `datetime` < '{$cleaningDate} 00:00:00' LIMIT {$iLimit}");
	}
}