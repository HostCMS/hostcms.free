<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Gift_Dir_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Gift_Dir_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'shop_gift_dir';

	/**
	 * Backend property
	 * @var string
	 */
	public $img = 0;

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_gift' => array(),
		'shop_gift_dir' => array('foreign_key' => 'parent_id')
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'shop_gift_dir' => array('foreign_key' => 'parent_id'),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_gift_dirs.sorting' => 'ASC'
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
	 * Get count of items all levels
	 * @return int
	 */
	public function getChildCount()
	{
		$count = $this->Shop_Gifts->getCount();

		$aShop_Gift_Dirs = $this->Shop_Gift_Dirs->findAll(FALSE);
		foreach ($aShop_Gift_Dirs as $oShop_Gift_Dir)
		{
			$count += $oShop_Gift_Dir->getChildCount();
		}

		return $count;
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
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

		$iCount = $this->getChildCount();

		$iCount > 0 && $oCore_Html_Entity_Div
			->add(
				Core_Html_Entity::factory('Span')
					->class('badge badge-hostcms badge-square')
					->value($iCount)
			);

		$oCore_Html_Entity_Div->execute();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
     * @hostcms-event shop_gift_dir.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Gifts->deleteAll(FALSE);
		$this->Shop_Gift_Dirs->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get parent shop discount dir
	 * @return Shop_Gift_Dir_Model|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Shop_Gift_Dir', $this->parent_id)
			: NULL;
	}

	/**
	 * Get dir path with separator
	 * @return string
	 */
	public function groupPathWithSeparator($separator = ' → ', $offset = 0)
	{
		$aParentGroups = array();

		$aTmpGroup = $this;

		// Добавляем все директории от текущей до родителя.
		do {
			$aParentGroups[] = $aTmpGroup->name;
		} while ($aTmpGroup = $aTmpGroup->getParent());

		$offset > 0
			&& $aParentGroups = array_slice($aParentGroups, $offset);

		$sParents = implode($separator, array_reverse($aParentGroups));

		return $sParents;
	}

	/**
	 * Move dir to another
	 * @param int $parent_id dir id
	 * @return self
	 * @hostcms-event shop_discount_dir.onBeforeMove
	 * @hostcms-event shop_discount_dir.onAfterMove
	 */
	public function move($parent_id)
	{
		Core_Event::notify($this->_modelName . '.onBeforeMove', $this, array($parent_id));

		$this->parent_id = $parent_id;
		$this->save();

		Core_Event::notify($this->_modelName . '.onAfterMove', $this);

		return $this;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_gift_dir.onBeforeGetRelatedSite
	 * @hostcms-event shop_gift_dir.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}