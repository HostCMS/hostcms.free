<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site_Alias_Model
 *
 * @package HostCMS
 * @subpackage Site
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Site_Alias_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var string
	 */
	public $key = 1;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'site' => array(),
		'user' => array()
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}

	/**
	 * Remove mask from $str
	 * @param string $str string
	 * @return string
	 */
	public function replaceMask($str)
	{
		return str_replace("*.", "", $str);
	}

	/**
	 * Utilized for reading data from inaccessible properties
	 * @param string $property property name
	 * @return mixed
	 */
	public function __get($property)
	{
		if ($property == 'alias_name_without_mask')
		{
			return !$this->isEmptyPrimaryKey()
				? $this->replaceMask($this->name)
				: NULL;
		}

		return parent::__get($property);
	}

	/**
	 * Set alias as current
	 * @return self
	 */
	public function setCurrent()
	{
		$this->save();

		$siteAliases = $this->Site->Site_Aliases;
		$siteAliases
			->queryBuilder()
			->where('current', '=', 1);

		$aSiteAliases = $siteAliases->findAll();
		foreach ($aSiteAliases as $oSiteAlias)
		{
			$oSiteAlias->current = 0;
			$oSiteAlias->update();
		}

		$this->redirect = 0;
		$this->current = 1;
		$this->save();
		return $this;
	}

	/**
	 * Set redirect
	 * @return self
	 */
	public function setRedirect()
	{
		if (!$this->current)
		{
			$this->redirect = 1 - $this->redirect;
			$this->save();
		}

		return $this;
	}

	/**
	 * Get key for alias
	 * @return self
	 */
	public function getKey()
	{
		// Проверяем наличие трех констант
		if (!defined('HOSTCMS_USER_LOGIN')
		|| !defined('HOSTCMS_CONTRACT_NUMBER')
		|| !defined('HOSTCMS_PIN_CODE')
		|| !defined('HOSTCMS_UPDATE_NUMBER'))
		{
			throw new Core_Exception(Core::_('Site_Alias.constant_check_error'), array(), 0, FALSE);
		}
		// Если не объявлена константа HOSTCMS_UPDATE_SERVER
		elseif (defined('HOSTCMS_UPDATE_SERVER'))
		{
			$oSite = $this->site;

			$login = HOSTCMS_USER_LOGIN;
			$contract = md5(HOSTCMS_CONTRACT_NUMBER);
			$pin = md5(HOSTCMS_PIN_CODE);
			$cms_folder = CMS_FOLDER;
			$php_version = phpversion();
			$mysql_version = Core_DataBase::instance()->getVersion();
			$update_id = HOSTCMS_UPDATE_NUMBER;

			// Формируем строку запроса
			$url = 'http://' . HOSTCMS_UPDATE_SERVER . "/hostcmsupdate/key/?domain=".rawurlencode($this->alias_name_without_mask) .
				'&protocol=' . (Core::httpsUses() ? 'https' : 'http') .
				"&login=" . rawurlencode($login) .
				"&contract=" . rawurlencode($contract) .
				"&pin=" . rawurlencode($pin) .
				"&cms_folder=" . rawurlencode($cms_folder) .
				"&php_version=" . rawurlencode($php_version) .
				"&mysql_version=" . rawurlencode($mysql_version) .
				"&update_id=" . rawurlencode($update_id);

			$Core_Http = Core_Http::instance()
				->url($url)
				->port(80)
				->timeout(5)
				->execute();

			$xml = $Core_Http->getBody();
			//echo htmlspecialchars($xml);
			$oXml = simplexml_load_string($xml);

			if (is_object($oXml))
			{
				$keyValue = (string)$oXml->value;
				$error = (string)$oXml->error;
			}
			else
			{
				$keyValue = NULL;
				$error = 0;
			}

			// Была передана ошибка
			switch ($error)
			{
				case 0: break;
				case 1:
					throw new Core_Exception(Core::_('Site_Alias.server_error_respond_1'), array(), 0, FALSE);
				break;
				case 2:
					throw new Core_Exception(Core::_('Site_Alias.server_error_respond_2'), array(), 0, FALSE);
				break;
				case 3:
					throw new Core_Exception(Core::_('Site_Alias.server_error_respond_3'), array(), 0, FALSE);
				break;
				case 4:
					throw new Core_Exception(Core::_('Site_Alias.server_error_respond_4'), array(), 0, FALSE);
				break;
				case 5:
					throw new Core_Exception(Core::_('Site_Alias.server_error_respond_5'), array(), 0, FALSE);
				break;
				case 6:
					throw new Core_Exception(Core::_('Site_Alias.server_error_respond_6'), array(), 0, FALSE);
				break;
				default:
					throw new Core_Exception(Core::_('Site_Alias.server_error_respond_0'), array(), 0, FALSE);
				break;
			}

			$aKeys = explode("\n", $oSite->key);

			// Ключа в массиве нет
			if (!in_array($keyValue, $aKeys))
			{
				// Дописываем его в массив ключей
				$aKeys[] = $keyValue;
			}

			$oSite->key = implode("\n", $aKeys);
			$oSite->save();
		}
		/*else
		{
			throw new Core_Exception(Core::_('Site_Alias.server_error_respond_data'));
		}*/
		return $this;
	}

	/**
	 * Get alias by name
	 * @param string $name
	 * @return self|NULL
	 */
	public function getByName($name)
	{
		$this->queryBuilder()
			//->clear()
			->where('name', '=', $name)
			->limit(1);

		$aSiteAlias = $this->findAll();

		if (count($aSiteAlias) > 0)
		{
			$aSiteAlias[0]->name = $aSiteAlias[0]->alias_name_without_mask;
			return $aSiteAlias[0];
		}

		return NULL;
	}

	/**
	 * Find alias by name with mask
	 * @param string $name Alias, e.g. '*.site.ru'
	 * @return self|NULL
	 */
	public function findAlias($aliasName)
	{
		$this->queryBuilder()
			->clear()
			->select('site_aliases.*')
			->join('sites', 'sites.id', '=', 'site_aliases.site_id')
			->where('site_aliases.name', 'LIKE', Core_DataBase::instance()->escapeLike($aliasName))
			->where('sites.deleted', '=', 0)
			->limit(1);

		$aSiteAlias = $this->findAll();

		if (isset($aSiteAlias[0]))
		{
			return $aSiteAlias[0];
		}

		// Удаляем все переданные *. если они были
		$newAliasName = $this->ReplaceMask($aliasName);

		// Если в переданном алиасе небыло *.
		if (strpos($aliasName, '*.') === FALSE)
		{
			$newAliasName = "*." . $aliasName;
			return $this->findAlias($newAliasName);
		}
		// Если в пути осталась хоть одна точка
		elseif (mb_strpos($newAliasName, '.') !== FALSE)
		{
			$newAliasName = "*." . mb_substr($newAliasName, mb_strpos($newAliasName, '.') + 1);
			return $this->findAlias($newAliasName);
		}

		return NULL;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event site_alias.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event site_alias.onBeforeRedeclaredGetStdObject
	 */
	public function getStdObject($attributePrefix = '_')
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetStdObject', $this);

		$this->_prepareData();

		return parent::getStdObject($attributePrefix);
	}

	/**
	 * Prepare entity and children entities
	 * @return self
	 */
	protected function _prepareData()
	{
		$this->clearXmlTags()
			->addXmlTag('alias_name_without_mask', $this->alias_name_without_mask);

		return $this;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$this->name != '' && Core::factory('Core_Html_Entity_A')
			->href('http://' . $this->alias_name_without_mask)
			->target('_blank')
			->add(
				Core::factory('Core_Html_Entity_I')
					->class('fa fa-external-link')
			)
			->execute();
	}
}