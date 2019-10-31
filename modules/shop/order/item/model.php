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
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
		'deleted',
		'user_id',
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
	public function nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
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
				htmlspecialchars($this->name)
			);
		}
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function shop_warehouse_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$additionalParams = Core_Str::escapeJavascriptVariable(
			str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
		);

		$aOptions = array('...');

		$aShop_Warehouses = $this->Shop_Order->Shop->Shop_Warehouses->findAll(FALSE);
		foreach ($aShop_Warehouses as $oShop_Warehouse)
		{
			$aOptions[$oShop_Warehouse->id] = htmlspecialchars($oShop_Warehouse->name);
		}

		$oItemsWarehouseSelect = Admin_Form_Entity::factory('Select')
			->divAttr(array('class' => ''))
			->options($aOptions)
			->class('form-control')
			->name('shop_order_item_warehouse_' . $this->id)
			->value($this->shop_warehouse_id)
			->onchange("$.adminLoad({path: '{$oAdmin_Form_Controller->getPath()}', additionalParams: '{$additionalParams}', action: 'apply', post: { 'hostcms[checked][0][{$this->id}]': 0, apply_check_0_{$this->id}_fv_{$oAdmin_Form_Field->id}: $(this).val() }, windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
			->execute();
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

		$this->_prepareData();

		return parent::getXml();
	}

	/**
	 * Get stdObject for entity and children entities
	 * @return stdObject
	 * @hostcms-event shop_order_item.onBeforeRedeclaredGetStdObject
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
		if ($this->_showXmlItem && $this->shop_item_id)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->find($this->shop_item_id);

			if (!is_null($oShop_Item->id) && $oShop_Item->active)
			{
				$oShop_Item
					->clearEntities()
					->showXmlProperties($this->_showXmlProperties);

				// Parent item for modification
				if ($oShop_Item->modification_id)
				{
					$oModification = Core_Entity::factory('Shop_Item')->find($oShop_Item->modification_id);
					!is_null($oModification->id) && $oShop_Item->addEntity(
						$oModification->showXmlProperties($this->_showXmlProperties)
					);
				}

				$this->addEntity($oShop_Item);
			}
		}

		$this->clearXmlTags()
			->addXmlTag('price', $this->getPrice())
			->addXmlTag('tax', $this->getTax());

		// Заказ оплачен и товар электронный
		if ($this->Shop_Order->paid == 1 /*&& $this->Shop_Item->type == 1*/)
		{
			// Digital items
			$aShop_Order_Item_Digitals = $this->Shop_Order_Item_Digitals->findAll(FALSE);
			foreach ($aShop_Order_Item_Digitals as $oShop_Order_Item_Digital)
			{
				$this->addEntity(
					$oShop_Order_Item_Digital->clearEntities()
				);
			}
		}

		return $this;
	}

	public function addDigitalItems(Shop_Item_Model $oShop_Item)
	{
		if ($oShop_Item->type == 1)
		{
			// Получаем все файлы электронного товара
			$aShop_Item_Digitals = $oShop_Item->Shop_Item_Digitals->getBySorting();

			if (count($aShop_Item_Digitals))
			{
				// Указываем, какой именно электронный товар добавляем в заказ
				// $this->shop_item_digital_id = $aShop_Item_Digitals[0]->id;

				$countGoodsNeed = $this->quantity;

				foreach ($aShop_Item_Digitals as $oShop_Item_Digital)
				{
					if ($oShop_Item_Digital->count == -1 || $oShop_Item_Digital->count > 0)
					{
						if ($oShop_Item_Digital->count == -1)
						{
							$iCount = $countGoodsNeed;
						}
						// Списывам файлы, если их количество не равно -1
						else
						{
							$iCount = $oShop_Item_Digital->count < $countGoodsNeed
								? $oShop_Item_Digital->count
								: $countGoodsNeed;
						}

						for ($i = 0; $i < $iCount; $i++)
						{
							$oShop_Order_Item_Digital = Core_Entity::factory('Shop_Order_Item_Digital');
							$oShop_Order_Item_Digital->shop_item_digital_id = $oShop_Item_Digital->id;
							$this->add($oShop_Order_Item_Digital);

							$countGoodsNeed--;
						}

						$mode = $this->Shop_Order->paid == 0 ? -1 : 1;

						// Списываем электронный товар, если он ограничен
						if ($oShop_Item_Digital->count != -1)
						{
							$oShop_Item_Digital->count -= $iCount * $mode;
							$oShop_Item_Digital->save();
						}

						if ($countGoodsNeed == 0)
						{
							break;
						}
					}
				}
			}

			$this->save();
		}

		return $this;
	}
}