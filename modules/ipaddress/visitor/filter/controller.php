<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Ipaddress_Visitor_Filter_Controller.
 *
 * @package HostCMS
 * @subpackage Ipaddress
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Ipaddress_Visitor_Filter_Controller
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
			'=' => Core::_('Ipaddress_Visitor_Filter.condition_='),
			'!=' => Core::_('Ipaddress_Visitor_Filter.condition_!='),
			'like' => Core::_('Ipaddress_Visitor_Filter.condition_like'),
			'not-like' => Core::_('Ipaddress_Visitor_Filter.condition_not-like'),
			'^' => Core::_('Ipaddress_Visitor_Filter.condition_^'),
			'!^' => Core::_('Ipaddress_Visitor_Filter.condition_!^'),
			'$' => Core::_('Ipaddress_Visitor_Filter.condition_$'),
			'!$' => Core::_('Ipaddress_Visitor_Filter.condition_!$'),
			'reg' => Core::_('Ipaddress_Visitor_Filter.condition_reg'),
			'!reg' => Core::_('Ipaddress_Visitor_Filter.condition_!reg')
		);
	}

	/**
	 * Get user filter types
	 * @return array
	 */
	static public function getTypes()
	{
		return array(
			'referer' => Core::_('Ipaddress_Visitor_Filter.referer'),
			'user_agent' => Core::_('Ipaddress_Visitor_Filter.user_agent'),
			'host' => Core::_('Ipaddress_Visitor_Filter.host'),
			'uri' => Core::_('Ipaddress_Visitor_Filter.uri'),
			'ip' => Core::_('Ipaddress_Visitor_Filter.ip'),
			'ptr' => Core::_('Ipaddress_Visitor_Filter.ptr'),
			'get' => Core::_('Ipaddress_Visitor_Filter.get'),
			'lang' => Core::_('Ipaddress_Visitor_Filter.lang'),
			'header' => Core::_('Ipaddress_Visitor_Filter.header'),
			//'delta_mobile_resolution' => Core::_('Ipaddress_Filter.delta_mobile_resolution'),
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
				$this->_filters = $oCore_Cache->get('filter_visitors', $this->_cacheName);
			}
			else
			{
				$this->_filters = NULL;
			}

			if (!is_array($this->_filters))
			{
				$this->_filters = Core_QueryBuilder::select('id', 'name', 'json', 'mode', 'ban_hours',  'block_mode')
					->from('ipaddress_visitor_filters')
					->where('active', '=', 1)
					->where('deleted', '=', 0)
					->clearOrderBy()
					->orderBy('sorting', 'ASC')
					->execute()->asAssoc()->result();

				$bCache && count($this->_filters)
					&& $oCore_Cache->set('filter_visitors', $this->_filters, $this->_cacheName);
			}
		}

		return $this->_filters;
	}

	/**
	 * Cache for _getCounterData($hours)
	 * @var array
	 */
	protected $_cacheGetCounterData = array();

	/**
	 * Get Counter_Visit for $hours
	 * @param int $hours
	 * @return array
	 */
	protected function _getCounterData($hours)
	{
		if (!isset($this->_cacheGetCounterData[$hours]))
		{
			$ip = Core::getClientIp();

			$oCounter_Visits = Core_Entity::factory('Counter_Visit');
			$oCounter_Visits->queryBuilder()
				->where('ip', '=', Core_Ip::ip2hex($ip))
				->where('site_id', '=', CURRENT_SITE)
				->where('datetime', '>', Core_Date::timestamp2sql(time() - $hours * 3600))
				->clearOrderBy()
				->orderBy('datetime', 'DESC')
				->limit(50);

			$tag = isset($_COOKIE['_h_tag']) && strlen($_COOKIE['_h_tag']) == 22
				? $_COOKIE['_h_tag']
				: NULL;

			if (!is_null($tag))
			{
				$oCounter_Visits->queryBuilder()
					->union(
						Core_QueryBuilder::select('counter_visits.*')
							->from('counter_visits')
							->join('counter_sessions', 'counter_visits.counter_session_id', '=', 'counter_sessions.id')
							->where('counter_sessions.tag', '=', $tag)
							->clearOrderBy()
							->orderBy('counter_visits.datetime', 'DESC')
							->limit(50)
					);
			}

			$this->_cacheGetCounterData[$hours] = $oCounter_Visits->findAll(FALSE);
		}

		return $this->_cacheGetCounterData[$hours];
	}

	/**
	 * Clear $this->_cacheGetCounterData
	 * @return self
	 */
	protected function clearCacheGetCounterData()
	{
		$this->_cacheGetCounterData = array();

		return $this;
	}

	/**
	 * Get number of hours to block
	 * @var NULL|int
	 */
	protected $_blockHours = NULL;

	public function getHoursToBlock()
	{
		return $this->_blockHours;
	}

	/**
	 * Get block mode
	 * @var int
	 */
	protected $_blockMode = 0;

	public function getBlockMode()
	{
		return $this->_blockMode;
	}

	/**
	 * Get filter ID
	 * @var int
	 */
	protected $_filterId = 0;

	public function getFilterId()
	{
		return $this->_filterId;
	}

	/**
	 * Check is request blocked
	 * @return boolean
	 */
	public function isBlocked()
	{
		$this->_blockMode = $this->_filterId = 0;
		$this->_blockHours = NULL;

		if (!Core::moduleIsActive('counter'))
		{
			return FALSE;
		}

		// IP проверяется по _h_tag из $aCounter_Visits
		//$ip = Core::getClientIp();

		$bBlocked = FALSE;

		$aFilters = $this->getFilters();

		if (count($aFilters))
		{
			$aHeaders = Core::getallheaders() + $_SERVER;
			$aHeadersLowercased = array_change_key_case($aHeaders);

			foreach ($aFilters as $aFilter)
			{
				$bBlocked = NULL;

				if ($aFilter['json'] != '')
				{
					$aJson = @json_decode($aFilter['json'], TRUE);
					if (is_array($aJson) && count($aJson))
					{
						// Расчет N Дней по данным в JSON
						$hours = 0;
						foreach ($aJson as $conditionId => $aCondition)
						{
							$aCondition['hours'] > $hours
								&& $hours = $aCondition['hours'];
						}

						$aCounter_Visits = $this->_getCounterData($hours);

						// Массив счетчиков совпадений
						$aMatches = array();

						foreach ($aJson as $conditionId => $aCondition)
						{
							if (isset($aCondition['type']) && isset($aCondition['condition']) && isset($aCondition['value'])
								&& isset($aCondition['hours']) && isset($aCondition['times'])
							)
							{
								$bCaseSensitive = isset($aCondition['case_sensitive']) && $aCondition['case_sensitive'] == 1;

								// Для каждого условия $aCondition цикл по всем $oCounter_Visit
								foreach ($aCounter_Visits as $oCounter_Visit)
								{
									// Дата визита входит в ограниченный для правила диапазон
									if (Core_Date::sql2timestamp($oCounter_Visit->datetime) > time() - $aCondition['hours'] * 3600)
									{
										$aParseUrl = array();
										if (in_array($aCondition['type'], array('host', 'uri', 'get')))
										{
											$oCounter_Visit->Counter_Page->page != ''
												&& $aParseUrl = @parse_url($oCounter_Visit->Counter_Page->page);
										}

										$compared = NULL;
										
										switch ($aCondition['type'])
										{
											case 'referer':
												$compared = ''; // Default empty referer
												$oCounter_Visit->counter_referrer_id
													&& $compared = $oCounter_Visit->Counter_Referrer->referrer;
											break;
											case 'user_agent':
												$Counter_Session = $oCounter_Visit->Counter_Session;
												if ($Counter_Session->counter_useragent_id)
												{
													$compared = $Counter_Session->Counter_Useragent->useragent != ''
														? $Counter_Session->Counter_Useragent->useragent
														: NULL;
												}
											break;
											case 'host':
												$compared = isset($aParseUrl['host']) ? $aParseUrl['host'] : NULL;
											break;
											case 'uri':
												$compared = isset($aParseUrl['path']) ? $aParseUrl['path'] : NULL;
											break;
											case 'ip':
												$compared = Core_Ip::hex2ip($oCounter_Visit->ip);
											break;
											case 'ptr':
												$compared = Ipaddress_Controller::instance()->gethostbyaddr(Core_Ip::hex2ip($oCounter_Visit->ip));
											break;
											case 'get':
												isset($aParseUrl['query']) && $aParseUrl['query'] !== ''
													? @parse_str($aParseUrl['query'], $aVariables)
													: $aVariables = array();

												$compared = isset($aCondition['get'])
													? Core_Array::get($aVariables, $aCondition['get']/*, '', 'str'*/) // Нужен NULL
													: NULL;
											break;
											case 'header':
												$compared = isset($aCondition['header'])
													? (
														// 'header_case_sensitive' since 7.1.5
														!isset($aCondition['header_case_sensitive']) || !$aCondition['header_case_sensitive']
															? Core_Array::get($aHeadersLowercased, strtolower($aCondition['header'])/*, '', 'str'*/) // Нужен NULL
															: Core_Array::get($aHeaders, $aCondition['header']/*, '', 'str'*/) // Нужен NULL
													)
													: NULL;
											break;
											case 'lang':
												$compared = $oCounter_Visit->lng;
											break;
											default:
												$compared = NULL;
										}

										// NULL может проверяться в режимах содержит/не содержит
										//if (!is_null($compared) || $aCondition['condition'] == 'like' || $aCondition['condition'] == 'not-like')
										//{
											if (!is_null($compared) && !in_array($aCondition['condition'], array('reg', '!reg')) && !$bCaseSensitive)
											{
												$compared = mb_strtolower($compared);
												$aCondition['value'] = mb_strtolower($aCondition['value']);
											}

											switch ($aCondition['condition'])
											{
												case '=':
													if (!is_null($compared))
													{
														// Не IP или IP не содержит подсеть
														if ($aCondition['type'] != 'ip' || strpos($aCondition['value'], '/') === FALSE)
														{
															$bReturn = $compared == $aCondition['value'];
														}
														else
														{
															$bReturn = Ipaddress_Controller::instance()->ipCheck($compared, $aCondition['value']);
														}
													}
													else
													{
														$bReturn = FALSE;
													}
												break;
												case '!=':
													if (!is_null($compared))
													{
														// Не IP или IP не содержит подсеть
														if ($aCondition['type'] != 'ip' || strpos($aCondition['value'], '/') === FALSE)
														{
															$bReturn = $compared != $aCondition['value'];
														}
														else
														{
															$bReturn = !Ipaddress_Controller::instance()->ipCheck($compared, $aCondition['value']);
														}
													}
													else
													{
														$bReturn = TRUE;
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
													$bReturn = is_scalar($compared) && $aCondition['value'] != ''
														? mb_strpos($compared, $aCondition['value']) === 0
														: FALSE;
												break;
												case '!^':
													$bReturn = is_scalar($compared) && $aCondition['value'] != ''
														? mb_strpos($compared, $aCondition['value']) !== 0
														: FALSE;
												break;
												case '$':
													$bReturn = is_scalar($compared) && $aCondition['value'] != ''
														? mb_strrpos($compared, $aCondition['value']) === (mb_strlen($compared) - mb_strlen($aCondition['value']))
														: FALSE;
												break;
												case '!$':
													$bReturn = is_scalar($compared) && $aCondition['value'] != ''
														? mb_strrpos($compared, $aCondition['value']) !== (mb_strlen($compared) - mb_strlen($aCondition['value']))
														: FALSE;
												break;
												case 'reg':
													//$pattern = '/' . preg_quote($aCondition['value'], '/') . '/' . ($bCaseSensitive ? '' : 'i');
													$pattern = '/' . str_replace('/', '\/', $aCondition['value']) . '/' . ($bCaseSensitive ? '' : 'i');
													$bReturn = is_scalar($compared)
														? preg_match($pattern, $compared, $matches) > 0
														: FALSE;
												break;
												case '!reg':
													$pattern = '/' . str_replace('/', '\/', $aCondition['value']) . '/' . ($bCaseSensitive ? '' : 'i');
													$bReturn = is_scalar($compared)
														? preg_match($pattern, $compared, $matches) == 0
														: FALSE;
												break;
												default:
													$bReturn = FALSE;
											}

											if ($bReturn)
											{
												// NULL => TRUE, TRUE => TRUE
												isset($aMatches[$conditionId])
													? $aMatches[$conditionId]++
													: $aMatches[$conditionId] = 1;
											}
										//}
									}
								}
							}
						} // /foreach $aJson

						if (count($aMatches))
						{
							// 0 - AND, 1 - OR
							$bBlocked = $aFilter['mode'] == 0;

							foreach ($aJson as $conditionId => $aCondition)
							{
								// 0 - AND, 1 - OR
								if ($aFilter['mode'] == 0)
								{
									// Режим AND, совпадения для условия не были найдены или их количество меньше требуемого количеству
									if (!isset($aMatches[$conditionId]) || $aMatches[$conditionId] < $aCondition['times'])
									{
										$bBlocked = FALSE;
										break;
									}
								}
								// Совпало хотя бы одно условие и режим OR
								else
								{
									// режим OR и количество совпадений больше или равно требуемого
									if (isset($aMatches[$conditionId]) && $aMatches[$conditionId] >= $aCondition['times'])
									{
										$bBlocked = TRUE;
										break;
									}
								}
							}
						}

						// Совпало правило
						if ($bBlocked === TRUE)
						{
							$this->incVisitorFilterBanned($aFilter['id']);

							$this->_filterId = $aFilter['id'];
							$this->_blockMode = $aFilter['block_mode'];

							// 2 - Разрешать
							if ($this->_blockMode == 2)
							{
								$bBlocked = FALSE;
							}
							else
							{
								// 0 - блокировать, 1 - Captcha
								$this->_blockHours = $this->_blockMode == 0
									? $aFilter['ban_hours']
									: 0;
							}

							// Прерываем, один из фильтров полностью совпал
							break;
						}
					}
				}
			} // /foreach
		}

		$this->clearCacheGetCounterData();

		return $bBlocked === TRUE;
	}

	/**
	 * Update banned fo Ipaddress_Filter
	 * @param int $id
	 */
	public function incVisitorFilterBanned($id)
	{
		Core_DataBase::instance()
			->setQueryType(2)
			->query("UPDATE `ipaddress_visitor_filters` SET `banned` = `banned` + 1 WHERE `id` = {$id}");
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
			$oCore_Cache->delete('filter_visitors', $this->_cacheName);
		}

		return $this;
	}
}