<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Item_Model
 *
 * Типы товаров:
 * 0 - Товар,
 * 1 - Доставка,
 * 2 - Пополнение лицевого счета,
 * 3 - Списание бонусов в счет оплаты счета.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Item_Model extends Core_Entity
{
	/**
	 * Backend property
	 * @var string
	 */
	public $sum = NULL;

	/**
	 * List of preloaded values
	 * @var array
	 */
	protected $_preloadValues = array(
		'shop_item_digital_id' => 0,
		'rate' => 0,
		'price' => 0,
		'shop_item_id' => 0,
		'quantity' => 0
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_order_item_digital' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_order' => array(),
		'shop_item' => array(),
		'shop_warehouse' => array(),
		'user' => array()
	);


	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'shop_order_items.id' => 'ASC',
	);
	
	/**
	 * Forbidden tags. If list of tags is empty, all tags will show.
	 * @var array
	 */
	protected $_forbiddenTags = array(
		'price',
	);

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id))
		{
			$oUserCurrent = Core_Entity::factory('User', 0)->getCurrent();
			$this->_preloadValues['user_id'] = is_null($oUserCurrent) ? 0 : $oUserCurrent->id;
		}
	}

	/**
	 * Get order sum with currency name
	 * @return string
	 */
	public function sum()
	{
		return htmlspecialchars(
			sprintf("%.2f %s", $this->getAmount(), $this->Shop_Order->Shop_Currency->name)
		);
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function name($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (is_null($this->Shop_Item->id))
		{
			return htmlspecialchars($this->name);
		}
		else
		{
			$sShopItemPath = '/admin/shop/item/index.php';
			$iShopItemId = $this->Shop_Item->id;

			return sprintf(
				'<a href="%s" target="_blank">%s <i class="fa fa-external-link"></i></a>',
				htmlspecialchars($oAdmin_Form_Controller->getAdminActionLoadHref($sShopItemPath, 'edit', NULL, 1, $iShopItemId)),
				htmlspecialchars($this->Shop_Item->name)
			);
		}
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_order_item.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));
		
		$this->Shop_Order_Item_Digitals->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}

	/**
	 * Show order items data in XML
	 * @var boolean
	 */
	protected $_showXmlItem = FALSE;

	/**
	 * Show order's items in XML
	 * @param boolean $showXmlItem
	 * @return self
	 */
	public function showXmlItem($showXmlItem = TRUE)
	{
		$this->_showXmlItem = $showXmlItem;
		return $this;
	}

	/**
	 * Get order's item tax
	 * @return float
	 */
	public function getTax()
	{
		return Shop_Controller::instance()->round($this->price * $this->rate / 100);
	}

	/**
	 * Get order's item price
	 * @return float
	 */
	public function getPrice()
	{
		return Shop_Controller::instance()->round($this->price + $this->getTax());
	}

	/**
	 * Get sum of order's item
	 * @return float
	 */
	public function getAmount()
	{
		return Shop_Controller::instance()->round(
			// Цена каждого товара откругляется
			Shop_Controller::instance()->round($this->price + $this->getTax()) * $this->quantity
		);
	}

	/**
	 * Show properties in XML
	 * @var boolean
	 */
	protected $_showXmlProperties = FALSE;

	/**
	 * Show properties in XML
	 * @param mixed $showXmlProperties array of allowed properties ID or boolean
	 * @return self
	 */
	public function showXmlProperties($showXmlProperties = TRUE)
	{
		$this->_showXmlProperties = is_array($showXmlProperties)
			? array_combine($showXmlProperties, $showXmlProperties)
			: $showXmlProperties;

		return $this;
	}

	/**
	 * Get XML for entity and children entities
	 * @return string
	 * @hostcms-event shop_order_item.onBeforeRedeclaredGetXml
	 */
	public function getXml()
	{
		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredGetXml', $this);

		if ($this->_showXmlItem && $this->Shop_Item->id)
		{
			$oShop_Item = $this->Shop_Item
				->clearEntities()
				->showXmlProperties($this->_showXmlProperties);

			// Parent item for modification
			if ($this->Shop_Item->modification_id)
			{
				$oModification = Core_Entity::factory('Shop_Item')->find($this->Shop_Item->modification_id);
				!is_null($oModification->id) && $oShop_Item->addEntity(
					$oModification->showXmlProperties($this->_showXmlProperties)
				);
			}

			$this->addEntity(
				$oShop_Item
			);
		}

		$this->clearXmlTags()
			->addXmlTag('price', $this->getPrice())
			->addXmlTag('tax', $this->getTax());

		// Заказ оплачен и товар электронный
		if ($this->Shop_Order->paid == 1 && $this->Shop_Item->type == 1)
		{
			// Digital items
			$aShop_Order_Item_Digitals = $this->Shop_Order_Item_Digitals->findAll();
			foreach ($aShop_Order_Item_Digitals as $oShop_Order_Item_Digital)
			{
				$this->addEntity(
					$oShop_Order_Item_Digital->clearEntities()
				);
			}
		}

		return parent::getXml();
	}
}