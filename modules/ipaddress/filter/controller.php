<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Filter_Controller.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Ipaddress_Filter_Controller
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Cache name
	 * @var string
	 */
	protected $_cacheName = 'ipaddresses';

	/**
	 * Get user filter conditions
	 * @return array
	 */
	static public function getConditions()
	{
		return array(
			'=' => Core::_('Ipaddress_Filter.condition_='),
			'!=' => Core::_('Ipaddress_Filter.condition_!='),
			'like' => Core::_('Ipaddress_Filter.condition_like'),
			'not-like' => Core::_('Ipaddress_Filter.condition_not-like'),
			'^' => Core::_('Ipaddress_Filter.condition_^'),
			'!^' => Core::_('Ipaddress_Filter.condition_!^'),
			'$' => Core::_('Ipaddress_Filter.condition_$'),
			'!$' => Core::_('Ipaddress_Filter.condition_!$'),
			'reg' => Core::_('Ipaddress_Filter.condition_reg')
		);
	}

	/**
	 * Get user filter types
	 * @return array
	 */
	static public function getTypes()
	{
		return array(
			'referer' => Core::_('Ipaddress_Filter.referer'),
			'user_agent' => Core::_('Ipaddress_Filter.user_agent'),
			'host' => Core::_('Ipaddress_Filter.host'),
			'uri' => Core::_('Ipaddress_Filter.uri'),
			'ip' => Core::_('Ipaddress_Filter.ip'),
			'get' => Core::_('Ipaddress_Filter.get'),
			'lang' => Core::_('Ipaddress_Filter.lang'),
			'header' => Core::_('Ipaddress_Filter.header'),
		);
	}

	/**
	 * Cache for getFilters()
	 * @var array|NULL
	 */
	protected $_filters = NULL;

	/**
	 * Get filters
	 * @return array
	 */
	public function getFilters()
	{
		if (is_null($this->_filters))
		{
			$bCache = Core::moduleIsActive('cache');

			if ($bCache)
			{
				$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
				$this->_filters = $oCore_Cache->get('filter', $this->_cacheName);
			}
			else
			{
				$this->_filters = NULL;
			}

			if (!is_array($this->_filters))
			{
				$this->_filters = Core_QueryBuilder::select('id', 'name', 'json', 'mode', 'block_ip')
					->from('ipaddress_filters')
					->where('active', '=', 1)
					->where('deleted', '=', 0)
					->clearOrderBy()
					->orderBy('sorting', 'ASC')
					->execute()->asAssoc()->result();

				$bCache && count($this->_filters)
					&& $oCore_Cache->set('filter', $this->_filters, $this->_cacheName);
			}
		}

		return $this->_filters;
	}


	/**
	 * Check is request blocked
	 * @return boolean
	 */
	public function isBlocked()
	{
		$bBlocked = FALSE;

		$aFilters = $this->getFilters();

		if (count($aFilters))
		{
			$aHeaders = array_change_key_case(Core::getallheaders());

			foreach ($aFilters as $aFilter)
			{
				$bBlocked = NULL;

				if ($aFilter['json'] != '')
				{
					$aJson = @json_decode($aFilter['json'], TRUE);
					if (is_array($aJson) && count($aJson))
					{
						foreach ($aJson as $aCondition)
						{
							if (isset($aCondition['type']) && isset($aCondition['condition']) && isset($aCondition['value']))
							{
								//var_dump($aCondition);
								$bCaseSensitive = isset($aCondition['case_sensitive']) && $aCondition['case_sensitive'] == 1;

								switch ($aCondition['type'])
								{
									case 'referer':
										$compared = Core_Array::get($_SERVER, 'HTTP_REFERER', '', 'str');
									break;
									case 'user_agent':
										$compared = Core_Array::get($_SERVER, 'HTTP_USER_AGENT', '', 'str');
									break;
									case 'host':
										$compared = Core_Array::get($_SERVER, 'HTTP_HOST', '', 'str');
									break;
									case 'uri':
										$compared = Core_Array::get($_SERVER, 'REQUEST_URI', '', 'str');
									break;
									case 'ip':
										$compared = Core_Array::get($_SERVER, 'REMOTE_ADDR', '', 'str');
									break;
									case 'get':
										$compared = isset($aCondition['get'])
											? Core_Array::getGet($aCondition['get']/*, '', 'str'*/) // Нужен NULL
											: NULL;
									break;
									case 'header':
										$compared = isset($aCondition['header'])
											? Core_Array::get($aHeaders, strtolower($aCondition['header'])/*, '', 'str'*/) // Нужен NULL
											: NULL;
									break;
									case 'lang':
										$compared = strtolower(
											substr(Core_Array::get($_SERVER, 'HTTP_ACCEPT_LANGUAGE', '', 'str'), 0, 2)
										);
									break;
									default:
										$compared = NULL;
								}

								// NULL может проверяться в режимах содержит/не содержит
								if (!is_null($compared) || $aCondition['condition'] == 'like' || $aCondition['condition'] == 'not-like')
								{
									if (!is_null($compared) && $aCondition['condition'] !== 'reg' && !$bCaseSensitive)
									{
										$compared = mb_strtolower($compared);
										$aCondition['value'] = mb_strtolower($aCondition['value']);
									}

									switch ($aCondition['condition'])
									{
										case '=':
											// Не IP или IP не содержит подсеть
											if ($aCondition['type'] != 'ip' || strpos($aCondition['value'], '/') === FALSE)
											{
												$bReturn = $compared == $aCondition['value'];
											}
											else
											{
												$bReturn = Ipaddress_Controller::instance()->ipCheck($compared, $aCondition['value']);
											}
										break;
										case '!=':
											// Не IP или IP не содержит подсеть
											if ($aCondition['type'] != 'ip' || strpos($aCondition['value'], '/') === FALSE)
											{
												$bReturn = $compared != $aCondition['value'];
											}
											else
											{
												$bReturn = !Ipaddress_Controller::instance()->ipCheck($compared, $aCondition['value']);
											}
										break;
										case 'like':
											$bReturn = is_scalar($compared) // NULL not scalar
												? ($aCondition['value'] != ''
													? mb_strpos($compared, $aCondition['value']) !== FALSE
													: TRUE // для пустоты содержит будет TRUE
												)
												: FALSE; // содержит для отсутствующего значения будет FALSE
										break;
										case 'not-like':
											$bReturn = is_scalar($compared) // NULL not scalar
												? ($aCondition['value'] != ''
													? mb_strpos($compared, $aCondition['value']) === FALSE
													: FALSE // для пустоты НЕ содержит будет FALSE
												)
												: TRUE; // не содержит для отсутствующего значения будет TRUE
										break;
										case '^':
											$bReturn = $aCondition['value'] != ''
												? mb_strpos($compared, $aCondition['value']) === 0
												: FALSE;
										break;
										case '!^':
											$bReturn = $aCondition['value'] != ''
												? mb_strpos($compared, $aCondition['value']) !== 0
												: FALSE;
										break;
										case '$':
											$bReturn = $aCondition['value'] != ''
												? mb_strpos($compared, $aCondition['value']) === (mb_strlen($compared) - mb_strlen($aCondition['value']))
												: FALSE;
										break;
										case '!$':
											$bReturn = $aCondition['value'] != ''
												? mb_strpos($compared, $aCondition['value']) !== (mb_strlen($compared) - mb_strlen($aCondition['value']))
												: FALSE;
										break;
										case 'reg':
											//$pattern = '/' . preg_quote($aCondition['value'], '/') . '/' . ($bCaseSensitive ? '' : 'i');
											$pattern = '/' . str_replace('/', '\/', $aCondition['value']) . '/' . ($bCaseSensitive ? '' : 'i');
											$bReturn = preg_match($pattern, $compared, $matches) > 0;
											//var_dump($pattern, $compared, $bReturn);
										break;
										default:
											$bReturn = FALSE;
									}

									if ($bReturn)
									{
										// NULL => TRUE, TRUE => TRUE
										$bBlocked = TRUE;
									}
									// 0 - AND, 1 - OR
									elseif ($aFilter['mode'] == 0)
									{
										// Прерываем, если не совпало хоть одно условие
										$bBlocked = FALSE;
										break;
									}
								}
							}
						}

						if ($bBlocked === TRUE)
						{
							$this->incFilterBanned($aFilter['id']);

							// Блокировать IP, соответствующий фильтру
							if ($aFilter['block_ip'])
							{
								$ip = Core_Array::get($_SERVER, 'REMOTE_ADDR', '', 'str');
								if ($ip != '' && !Ipaddress_Controller::instance()->isBlocked(array($ip)))
								{
									$oIpaddress = Core_Entity::factory('Ipaddress');
									$oIpaddress->ip = $ip;
									$oIpaddress->deny_access = 1;
									$oIpaddress->comment = "Blocked by filter '{$aFilter['name']}'";
									$oIpaddress->save();

									Ipaddress_Controller::instance()->clearCache();
								}
							}
							// прерываем, один из фильтров полностью совпал
							break;
						}
					}
				}
			}
		}

		return $bBlocked === TRUE;
	}

	/**
	 * Update banned fo Ipaddress_Filter
	 * @param int $id
	 */
	public function incFilterBanned($id)
	{
		Core_DataBase::instance()
			->setQueryType(2)
			->query("UPDATE `ipaddress_filters` SET `banned` = `banned` + 1 WHERE `id` = {$id}");
	}

	/**
	 * Clear ipaddresses cache
	 * @return self
	 */
	public function clearCache()
	{
		// Clear cache
		if (Core::moduleIsActive('cache'))
		{
			$oCore_Cache = Core_Cache::instance(Core::$mainConfig['defaultCache']);
			$oCore_Cache->delete('filter', $this->_cacheName);
		}

		return $this;
	}
}