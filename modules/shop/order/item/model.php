<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Item_Model
 *
 * Типы товаров:
 * 0 - Товар
 * 1 - Доставка
 * 2 - Пополнение лицевого счета
 * 3 - Скидка от суммы заказа
 * 4 - Скидка по дисконтной карте
 * 5 - Списание бонусов
 * 6 - Частичная оплата с лицевого счета
 * 7 - Услуга
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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
		'shop_order_item_code' => array(),
	);

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_order' => array(),
		'shop_item' => array(),
		'shop_measure' => array(),
		'shop_warehouse' => array(),
		'shop_order_item_status' => array(),
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
			$this->_preloadValues['quantity'] = 1;
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
		}
	}

	/**
	 * Check shop_order_item was canceled
	 */
	public function isCanceled()
	{
		return $this->shop_order_item_status_id && $this->Shop_Order_Item_Status->canceled;
	}

	/**
	 * Backend, get order sum with currency name
	 * @return string
	 */
	public function sumBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if (!$this->shop_order_item_status_id || !$this->Shop_Order_Item_Status->canceled)
		{
			return htmlspecialchars(
				(string) $this->Shop_Order->Shop_Currency->formatWithCurrency($this->getAmount())
			);
		}
	}

	/**
	 * Backend, get quantity
	 * @return string
	 */
	public function quantityBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$quantity = Core_Str::hideZeros($this->quantity);

		return $oAdmin_Form_Field->editable
			? '<span id="apply_check_0_' . $this->id . '_fv_' . $oAdmin_Form_Field->id .'" class="editable">' . $quantity . '</span>'
			: $quantity;
	}

	/**
	 * Backend, get price
	 * @return string
	 */
	public function priceBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$price = htmlspecialchars((string) $this->Shop_Order->Shop_Currency->format($this->price));

		return $oAdmin_Form_Field->editable
			? '<span id="apply_check_0_' . $this->id . '_fv_' . $oAdmin_Form_Field->id .'" class="editable">' . $price . '</span>'
			: $price;
	}

	/**
	 * Backend, get price
	 * @return string
	 */
	public function rateBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->rate ? $this->rate . '%' : '';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function shop_measure_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->shop_measure_id)
		{
			return htmlspecialchars((string) $this->Shop_Measure->name);
		}
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
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
			$sShopItemPath = '/{admin}/shop/item/index.php';
			$iShopItemId = $this->Shop_Item->id;

			echo sprintf(
				'<a href="%s" target="_blank">%s <i class="fa fa-external-link"></i></a>',
				htmlspecialchars($oAdmin_Form_Controller->getAdminActionLoadHref($sShopItemPath, 'edit', NULL, 1, $iShopItemId)),
				htmlspecialchars($this->name)
			);

			$this->Shop_Item->showSetBadges();
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function imgBackend()
	{
		if ($this->shop_item_id)
		{
			$oShop_Item = Core_Entity::factory('Shop_Item')->find($this->shop_item_id);

			if (!is_null($oShop_Item->id))
			{
				if ($oShop_Item->shortcut_id)
				{
					return '<i class="fa-solid fa-link"></i>';
				}
				elseif (strlen($oShop_Item->image_small) || strlen($oShop_Item->image_large))
				{
					$srcImg = htmlspecialchars(strlen($oShop_Item->image_small)
						? $oShop_Item->getSmallFileHref()
						: $oShop_Item->getLargeFileHref()
					);

					$dataContent = '<img class="backend-preview" src="' . $srcImg . '" />';

					return '<img data-toggle="popover" data-trigger="hover" data-html="true" data-placement="top" data-content="' . htmlspecialchars($dataContent) . '" class="backend-thumbnail" src="' . $srcImg . '" />';
				}
				else
				{
					return '<i class="fa fa-file-text-o"></i>';
				}
			}
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function shop_order_item_status_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		// Только у типа "Товар"
		if ($this->type == 0)
		{
			ob_start();

			$path = $oAdmin_Form_Controller->getPath();

			$oCore_Html_Entity_Dropdownlist = new Core_Html_Entity_Dropdownlist();

			$additionalParams = Core_Str::escapeJavascriptVariable(
				str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
			);

			Core_Html_Entity::factory('Span')
				->class('padding-left-10')
				->add(
					$oCore_Html_Entity_Dropdownlist
						->value($this->shop_order_item_status_id)
						->options(Shop_Order_Item_Status_Controller_Edit::getDropdownlistOptions($this->Shop_Order->shop_id))
						->onchange("$.adminLoad({path: '{$path}', additionalParams: '{$additionalParams}', action: 'apply', post: { 'hostcms[checked][0][{$this->id}]': 0, apply_check_0_{$this->id}_fv_{$oAdmin_Form_Field->id}: $(this).find('li[selected]').prop('id') }, windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
						->data('change-context', 'true')
					)
				->execute();

			return ob_get_clean();
		}
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field_Model $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 */
	public function shop_warehouse_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->type == 0)
		{
			$additionalParams = Core_Str::escapeJavascriptVariable(
				str_replace(array('"'), array('&quot;'), $oAdmin_Form_Controller->additionalParams)
			);

			$aOptions = array('...');

			$aShop_Warehouses = $this->Shop_Order->Shop->Shop_Warehouses->findAll(FALSE);
			foreach ($aShop_Warehouses as $oShop_Warehouse)
			{
				$name = $oShop_Warehouse->name;

				if ($this->shop_item_id)
				{
					$oShop_Warehouse_Cell_Items = $this->Shop_Item->Shop_Warehouse_Cell_Items->getByshop_warehouse_id($oShop_Warehouse->id);

					if ($oShop_Warehouse_Cell_Items)
					{
						$name .= ' (' . $oShop_Warehouse_Cell_Items->Shop_Warehouse_Cell->nameWithSeparator() . ')';
					}
				}

				$aOptions[$oShop_Warehouse->id] = htmlspecialchars($name);
			}

			Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => ''))
				->options($aOptions)
				->class('form-control')
				->name('shop_order_item_warehouse_' . $this->id)
				->value($this->shop_warehouse_id)
				->onchange("$.adminLoad({path: '{$oAdmin_Form_Controller->getPath()}', additionalParams: '{$additionalParams}', action: 'apply', post: { 'hostcms[checked][0][{$this->id}]': 0, apply_check_0_{$this->id}_fv_{$oAdmin_Form_Field->id}: $(this).val() }, windowId: '{$oAdmin_Form_Controller->getWindowId()}'});")
				->execute();
		}
	}

	/**
	 * Mark entity as deleted
	 * @return Core_Entity
	 */
	public function markDeleted()
	{
		$oShop_Order = $this->Shop_Order;

		$this->deleteReservedItems();

		$return = parent::markDeleted();

		if ($oShop_Order->posted)
		{
			$oShop_Order->posted = 0;
			$oShop_Order->post();
		}

		return $return;
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
		$this->Shop_Order_Item_Codes->deleteAll(FALSE);

		$this->deleteReservedItems();

		return parent::delete($primaryKey);
	}

	/**
	 * Delete reserved items for order
	 * @return self
	 */
	public function deleteReservedItems()
	{
		if ($this->shop_item_id)
		{
			$aShop_Item_Reserveds = $this->Shop_Order->Shop_Item_Reserveds->getAllByshop_item_id($this->shop_item_id);
			foreach ($aShop_Item_Reserveds as $oShop_Item_Reserved)
			{
				$oShop_Item_Reserved->delete();
			}
		}

		return $this;
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
		return Shop_Controller::instance()->round($this->getPrice(FALSE) * $this->rate / (100 + $this->rate));
	}

	/**
	 * Get order's item price
	 * @param boolean $round
	 * @return float
	 * @hostcms-event shop_order_item.onAfterGetPrice
	 */
	public function getPrice($round = TRUE)
	{
		$price = $this->price;

		Core_Event::notify($this->_modelName . '.onAfterGetPrice', $this, array($price));
		$eventResult = Core_Event::getLastReturn();

		if (!is_null($eventResult) && is_numeric($eventResult))
		{
			$price = $eventResult;
		}

		return $round
			? Shop_Controller::instance()->round($price)
			: $price;
	}

	/**
	 * Get amount of order's item
	 * @return float
	 */
	public function getAmount()
	{
		return Shop_Controller::instance()->round(
			// Цена каждого товара откругляется
			$this->getPrice() * $this->quantity
		);
	}

	/**
	 * Show properties in XML
	 * @var boolean
	 */
	protected $_showXmlProperties = FALSE;

	/**
	 * Sort properties values in XML
	 * @var mixed
	 */
	protected $_xmlSortPropertiesValues = TRUE;

	/**
	 * Show properties in XML
	 * @param mixed $showXmlProperties array of allowed properties ID or boolean
	 * @return self
	 */
	public function showXmlProperties($showXmlProperties = TRUE, $xmlSortPropertiesValues = TRUE)
	{
		$this->_showXmlProperties = is_array($showXmlProperties)
			? array_combine($showXmlProperties, $showXmlProperties)
			: $showXmlProperties;

		$this->_xmlSortPropertiesValues = $xmlSortPropertiesValues;

		return $this;
	}

	/**
	 * Show media in XML
	 * @var boolean
	 */
	protected $_showXmlMedia = FALSE;

    /**
     * Show properties in XML
     * @param bool $showXmlMedia
     * @return self
     */
	public function showXmlMedia($showXmlMedia = TRUE)
	{
		$this->_showXmlMedia = $showXmlMedia;

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
					->cartQuantity($this->quantity)
					->showXmlProperties($this->_showXmlProperties, $this->_xmlSortPropertiesValues)
					->showXmlMedia($this->_showXmlMedia);

				// Parent item for modification
				if ($oShop_Item->modification_id)
				{
					$oModification = Core_Entity::factory('Shop_Item')->find($oShop_Item->modification_id);
					!is_null($oModification->id) && $oShop_Item->addEntity(
						$oModification
							->showXmlProperties($this->_showXmlProperties, $this->_xmlSortPropertiesValues)
							->showXmlMedia($this->_showXmlMedia)
					);
				}

				$this->addEntity($oShop_Item);
			}
		}

		$price = $this->getPrice();
		$tax = $this->getTax();
		$amount = $price * $this->quantity;

		$oShop_Currency = $this->Shop_Order->Shop_Currency;

		$this->clearXmlTags()
			->addForbiddenTag('quantity')
			->addXmlTag('quantity', $this->quantity, array(
				'formatted' => $oShop_Currency->format($this->quantity))
			)
			->addXmlTag('price', $price, array(
				'formatted' => $oShop_Currency->format($price),
				'formattedWithCurrency' => $oShop_Currency->formatWithCurrency($price))
			)
			->addXmlTag('tax', $tax, array(
				'formatted' => $oShop_Currency->format($tax),
				'formattedWithCurrency' => $oShop_Currency->formatWithCurrency($tax))
			)
			->addXmlTag('amount', $amount, array(
				'formatted' => $oShop_Currency->format($amount),
				'formattedWithCurrency' => $oShop_Currency->formatWithCurrency($amount))
			);

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

		$this->shop_order_item_status_id && $this->addEntity($this->Shop_Order_Item_Status->clearEntities());

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

						// Оплаченный заказ будет значение 1.
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

	/**
	 * Push change item status history
	 * @return self
	 */
	public function historyPushChangeItemStatus()
	{
		$oShop_Order_Item_Status = $this->shop_order_item_status_id
			? $this->Shop_Order_Item_Status
			: NULL;

		$oShop_Order_History = Core_Entity::factory('Shop_Order_History');
		$oShop_Order_History->shop_order_id = $this->shop_order_id;
		$oShop_Order_History->shop_order_status_id = $this->Shop_Order->shop_order_status_id;
		$oShop_Order_History->text = Core::_('Shop_Order_Item.change_item_status', $this->name, $oShop_Order_Item_Status ? $oShop_Order_Item_Status->name : Core::_('Shop_Order.notStatus'), FALSE);
		$oShop_Order_History->color = $oShop_Order_Item_Status ? $this->Shop_Order_Item_Status->color : '#aebec4';
		$oShop_Order_History->save();

		return $this;
	}

	/*
	 * Get shop warehouse cell item
	 * @return Shop_Warehouse_Cell_Item|NULL
	 */
	public function getShopWarehouseCellItem()
	{
		return $this->shop_item_id && $this->shop_warehouse_id
			? $this->Shop_Item->Shop_Warehouse_Cell_Items->getByshop_warehouse_id($this->shop_warehouse_id)
			: NULL;
	}

	/*
	 * Get shop warehouse cell name
	 * @return Shop_Warehouse_Cell|NULL
	 */
	public function getCellName()
	{
		$oShop_Warehouse_Cell_Item = $this->getShopWarehouseCellItem();
		if ($oShop_Warehouse_Cell_Item)
		{
			return $oShop_Warehouse_Cell_Item->Shop_Warehouse_Cell->nameWithSeparator();
		}

		return NULL;
	}

	public function showCodesBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->type == 0)
		{
			$count = $this->Shop_Order_Item_Codes->getCount(FALSE);

			Core_Html_Entity::factory('Span')
				->id('code-badge' . $this->id)
				->class('badge badge-ico badge-darkorange white')
				->value($count < 100 ? $count : '∞')
				->execute();
		}
	}

	public function showCodesBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		// Если тип "Товар"
		if ($this->type == 0)
		{
			ob_start();
			?>
			<i class="fa fa-code shop-order-item-codes codes-<?php echo $this->id?>"></i>
			<script>
				$(function() {
					$('.codes-<?php echo $this->id?>').on('click', function(){
						$.ajax({
							url: hostcmsBackend + '/shop/order/item/index.php',
							type: "POST",
							data: {'load_modal': 1, 'shop_order_item_id': <?php echo $this->id?>, 'shop_order_id': <?php echo $this->shop_order_id?>},
							dataType: 'json',
							error: function(){},
							success: function (result) {
								$('body').append(result.html);

								var jModal = $('#codes-<?php echo $this->id?>');

								jModal.modal('show');

								jModal.on('shown.bs.modal', function() {
									var jInputs = $(this).find('input.form-control');

									jInputs.eq(0)
										.focus()
										.select();

									jInputs.bind('paste', function() {
										$(this).parents('.row').next().find('input.form-control')
											.focus()
											.select();
									})
									.on('keyup', function (e) {
										if (e.keyCode == 13) {
											$(this).parents('.row').next().find('input.form-control')
												.focus()
												.select();
										}
									})
									.on('focus', function () {
										$(this).select();
									});


									$(this).on('keyup change paste', ':input', function(e) { mainFormLocker.lock(e) });

								});

								jModal.on('hide.bs.modal', function (e) {

									var triggerReturn = $('body').triggerHandler('beforeHideModal');

									if (triggerReturn == 'break')
									{
										e.preventDefault();
									}
									else
									{
										jModal.remove();
									}
								});
							}
						});
					});
				});
			</script>
			<?php

			return ob_get_clean();
		}
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_order_item.onBeforeGetRelatedSite
	 * @hostcms-event shop_order_item.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Order->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}