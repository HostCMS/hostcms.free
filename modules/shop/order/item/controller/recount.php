<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Item_Controller_Recount.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Item_Controller_Recount extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'shopOrder',
	);

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$round = TRUE;

		$quantityPurchaseDiscount = $amountPurchaseDiscount = $quantity = $amount = 0;

		// Удаляем существующие скидки
		$oShop_Order_Items = $this->shopOrder->Shop_Order_Items;
		$oShop_Order_Items->queryBuilder()
			->where('shop_order_items.type', '=', 3) // 3 - Скидка от суммы заказа
			->where('shop_order_items.quantity', '=', 1)
			->where('shop_order_items.price', '<', 0);

		$aShop_Order_Items = $oShop_Order_Items->findAll(FALSE);
		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			$oShop_Order_Item->delete();
		}

		$aDiscountPrices = array();

		$aShop_Order_Items = $this->shopOrder->Shop_Order_Items->getAllByType(0, FALSE);
		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			if ($oShop_Order_Item->Shop_Item->id)
			{
				$oShop_Item = $oShop_Order_Item->Shop_Item;

				$quantity += $oShop_Order_Item->quantity;

				// Количество для скидок от суммы заказа рассчитывается отдельно
				$oShop_Item->apply_purchase_discount
					&& $quantityPurchaseDiscount += $oShop_Order_Item->quantity;

				// Prices
				$oShop_Item_Controller = new Shop_Item_Controller();

				Core::moduleIsActive('siteuser') && $this->shopOrder->siteuser_id
					&& $oShop_Item_Controller->siteuser($this->shopOrder->Siteuser);

				$aPrices = $oShop_Item_Controller->getPrices($oShop_Item, $round);

				$amount += $aPrices['price_discount'] * $oShop_Order_Item->quantity;

				// По каждой единице товара добавляем цену в массив, т.к. может быть N единиц одого товара
				for ($i = 0; $i < $oShop_Order_Item->quantity; $i++)
				{
					$aDiscountPrices[] = $aPrices['price_discount'];
				}

				// Сумма для скидок от суммы заказа рассчитывается отдельно
				$oShop_Item->apply_purchase_discount
					&& $amountPurchaseDiscount += $aPrices['price_discount'] * $oShop_Order_Item->quantity;
			}
		}

		$this->shopOrder->addPurchaseDiscount(
			array(
				'amount' => $amount,
				'quantity' => $quantity,
				'prices' => $aDiscountPrices,
				'applyDiscounts' => TRUE,
				'applyDiscountCards' => TRUE
			)
		);

		return $this;
	}
}