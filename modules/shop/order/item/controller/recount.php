<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Item_Controller_Recount.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

		$aShop_Order_Items = $this->shopOrder->Shop_Order_Items->findAll(FALSE);
		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			if ($oShop_Order_Item->type == 0)
			{
				$oShop_Item = $oShop_Order_Item->Shop_Item;

				// Товару заказа задана связь с товаров, при этому у товара заказа не задан статус, либо задан статус и он не отмененный
				if ($oShop_Item->id && !$oShop_Order_Item->isCanceled())
				{
					$quantity += $oShop_Order_Item->quantity;

					// Количество для скидок от суммы заказа рассчитывается отдельно
					$oShop_Item->apply_purchase_discount
						&& $quantityPurchaseDiscount += $oShop_Order_Item->quantity;

					// Prices
					$price = $oShop_Order_Item->getPrice();
					$amount += $price * $oShop_Order_Item->quantity;

					// По каждой единице товара добавляем цену в массив, т.к. может быть N единиц одого товара
					for ($i = 0; $i < $oShop_Order_Item->quantity; $i++)
					{
						$aDiscountPrices[] = $price;
					}

					// Сумма для скидок от суммы заказа рассчитывается отдельно
					$oShop_Item->apply_purchase_discount
						&& $amountPurchaseDiscount += $price * $oShop_Order_Item->quantity;
				}
			}
			// 3 - Скидка от суммы заказа
			// 4 - Скидка по дисконтной карте
			elseif ($oShop_Order_Item->type == 3 || $oShop_Order_Item->type == 4)
			{
				$oShop_Order_Item->markDeleted();
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