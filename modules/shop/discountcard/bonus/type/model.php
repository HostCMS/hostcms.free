<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discountcard_Bonus_Type_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Discountcard_Bonus_Type_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_discountcard_bonus' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop' => array(),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_discountcard_bonus_types.sorting' => 'ASC',
		'shop_discountcard_bonus_types.name' => 'ASC',
	);

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'sorting' => 0,
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
	 * Switch default status
	 * @return self
	 */
	public function changeDefaultStatus()
	{
		$this->save();

		$oShop_Discountcard_Bonus_Types = $this->Shop->Shop_Discountcard_Bonus_Types;
		$oShop_Discountcard_Bonus_Types
			->queryBuilder()
			->where('shop_discountcard_bonus_types.default', '=', 1);

		$aShop_Discountcard_Bonus_Types = $oShop_Discountcard_Bonus_Types->findAll();

		foreach ($aShop_Discountcard_Bonus_Types as $oShop_Discountcard_Bonus_Type)
		{
			$oShop_Discountcard_Bonus_Type->default = 0;
			$oShop_Discountcard_Bonus_Type->update();
		}

		$this->default = 1;
		return $this->save();
	}

	/**
	 * Get default discountcard bonus type
	 * @param boolean $bCache cache mode
	 * @return self|NULL
	 */
	public function getDefault($bCache = TRUE)
	{
		$this->queryBuilder()
			->where('shop_discountcard_bonus_types.default', '=', 1)
			->limit(1);

		$aShop_Discountcard_Bonus_Types = $this->findAll($bCache);

		return isset($aShop_Discountcard_Bonus_Types[0])
			? $aShop_Discountcard_Bonus_Types[0]
			: NULL;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$link = $oAdmin_Form_Field->link;
		$onclick = $oAdmin_Form_Field->onclick;

		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $onclick);

		return '<i class="fa fa-circle" style="margin-right: 5px; color: ' . ($this->color ? htmlspecialchars($this->color) : '#aebec4') . '"></i> '
			. '<span>' . htmlspecialchars($this->name) . '</span>';
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_discountcard_bonus_type.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		Core_QueryBuilder::update('shop_discountcard_bonuses')
			->set('shop_discountcard_bonus_type_id', 0)
			->where('shop_discountcard_bonus_type_id', '=', $this->id)
			->execute();

		return parent::delete($primaryKey);
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_discountcard_bonus_type.onBeforeGetRelatedSite
	 * @hostcms-event shop_discountcard_bonus_type.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}