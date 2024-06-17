<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Cell_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Warehouse_Cell_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_warehouse' => array(),
		'shop_warehouse_cell' => array('foreign_key' => 'parent_id'),
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_warehouse_cell' => array('foreign_key' => 'parent_id'),
		'shop_warehouse_cell_item' => array(),
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
	 * Get parent shop warehouse cell
	 * @return Shop_Warehouse_Cell|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Shop_Warehouse_Cell', $this->parent_id)
			: NULL;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Shop_Warehouse_Cells->getCount();
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-hostcms badge-square')
			->value('<i class="fa fa-folder-o"></i> ' . $count)
			->title(Core::_('Shop_Warehouse_Cell.all_cells_count', $count))
			->execute();

		$iCountShopItems = $this->getChildCount();
		$iCountShopItems > 0 && Core_Html_Entity::factory('Span')
			->class('badge badge-hostcms badge-square')
			->value('<i class="fa fa-file-o"></i> ' . $iCountShopItems)
			->title(Core::_('Shop_Warehouse_Cell.all_items_count', $iCountShopItems))
			->execute();
	}

	/**
	 * Get cell name with separator
	 * @return string
	 */
	public function nameWithSeparator($offset = 0)
	{
		$aParentCells = array();

		$aTmpShopWarehouseCell = $this;

		// Добавляем все ячейки от текущей до родителя.
		do {
			$aParentCells[] = $aTmpShopWarehouseCell->name;
		} while ($aTmpShopWarehouseCell = $aTmpShopWarehouseCell->getParent());

		$offset > 0
			&& $aParentCells = array_slice($aParentCells, $offset);

		$sParents = implode($this->Shop_Warehouse->separator, array_reverse($aParentCells));

		return $sParents;
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_warehouse_cell.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Warehouse_Cells->deleteAll(FALSE);
		$this->Shop_Warehouse_Cell_Items->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Get count of items all levels
	 * @return int
	 */
	public function getChildCount()
	{
		$count = $this->Shop_Warehouse_Cell_Items->getCount();

		$aShop_Warehouse_Cells = $this->Shop_Warehouse_Cells->findAll(FALSE);
		foreach ($aShop_Warehouse_Cells as $oShop_Warehouse_Cell)
		{
			$count += $oShop_Warehouse_Cell->getChildCount();
		}

		return $count;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_warehouse_cell.onBeforeGetRelatedSite
	 * @hostcms-event shop_warehouse_cell.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Warehouse->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}