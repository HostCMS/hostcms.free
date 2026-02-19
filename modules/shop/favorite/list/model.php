<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Favorite_List_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Favorite_List_Model extends Core_Entity
{
	/**
	 * Callback property_id
	 * @var int
	 */
	public $img = 0;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'siteuser' => array(),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_favorite' => array()
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
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 */
	public function dataNameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$link = $oAdmin_Form_Field->link;
		$onclick = $oAdmin_Form_Field->onclick;

		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $onclick);

		$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div');

		$oCore_Html_Entity_Div
			->add(
				Core_Html_Entity::factory('A')
					->href($link)
					->onclick($onclick)
					->value(htmlspecialchars($this->name))
			);

		$count = $this->Shop_Favorites->getCount();
		$count > 0 && $oCore_Html_Entity_Div
			->add(
				Core_Html_Entity::factory('Span')
					->class('badge badge-hostcms badge-square')
					->value($count)
			);

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_favorite_list.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Favorites->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_favorite_list.onBeforeGetRelatedSite
	 * @hostcms-event shop_favorite_list.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}