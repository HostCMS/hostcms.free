<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Status_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Status_Model extends Core_Entity
{
	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop' => array(),
		'shop_order_status' => array('foreign_key' => 'parent_id'),
		'shop_order_history' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_order_status' => array('foreign_key' => 'parent_id'),
		'user' => array()
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_order_statuses.sorting' => 'ASC',
		'shop_order_statuses.name' => 'ASC',
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
	 * Backend callback method
	 * @return string
	 */
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$link = $oAdmin_Form_Field->link;
		$onclick = $oAdmin_Form_Field->onclick;

		$link = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $link);
		$onclick = $oAdmin_Form_Controller->doReplaces($oAdmin_Form_Field, $this, $onclick);

		$return = '<i class="fa fa-circle" style="margin-right: 5px; color: ' . ($this->color ? htmlspecialchars($this->color) : '#eee' ) . '"></i> '
				. '<a href="' . $link . '" onclick="' . $onclick . '">' . htmlspecialchars($this->name) . '</a>';

		$count = $this->getChildCount();
		$count
			&& $return .= '<span class="badge badge-hostcms badge-square margin-left-5">' . $count . '</span>';

		return $return;
	}

	/**
	 * Get parent status
	 * @return Shop_Order_Status_Model|NULL
	 */
	public function getParent()
	{
		return $this->parent_id
			? Core_Entity::factory('Shop_Order_Status', $this->parent_id)
			: NULL;
	}

	/**
	 * Get count of items all levels
	 * @return int
	 */
	public function getChildCount()
	{
		$count = $this->Shop_Order_Statuses->getCount();

		$aShop_Order_Statuses = $this->Shop_Order_Statuses->findAll(FALSE);
		foreach ($aShop_Order_Statuses as $oShop_Order_Status)
		{
			$count += $oShop_Order_Status->getChildCount();
		}

		return $count;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (Core::moduleIsActive('bot'))
		{
			$oModule = Core_Entity::factory('Module')->getByPath('shop');

			$aBot_Modules = Bot_Controller::getBotModules($oModule->id, 0, $this->id);

			foreach ($aBot_Modules as $oBot_Module)
			{
				$oBot = $oBot_Module->Bot;
				
				$sParents = $oBot->bot_dir_id
					? $oBot->Bot_Dir->dirPathWithSeparator() . ' → '
					: '';
							
				Core::factory('Core_Html_Entity_Span')
					->class('badge badge-square badge-hostcms')
					->value('<i class="fa fa-android"></i> ' . $sParents . htmlspecialchars($oBot->name))
					->execute();
			}
		}
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_order_status.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Order_Statuses->deleteAll(FALSE);

		Core_QueryBuilder::update('shop_orders')
			->set('shop_order_status_id', 0)
			->where('shop_order_status_id', '=', $this->id)
			->execute();

		return parent::delete($primaryKey);
	}
}