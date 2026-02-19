<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Item_Controller_Recount.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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

		$quantityPurchaseDiscount = $amountPurchaseDiscount = $quantity = $amount = $weight = 0;

		$oShop = $this->shopOrder->Shop;

		// Есть скидки на N-й товар, доступные для текущей даты
		$bPositionDiscount = $oShop->Shop_Purchase_Discounts->checkAvailableWithPosition();

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

		$oShop_Item_Controller = new Shop_Item_Controller();

		$aShop_Order_Items = $this->shopOrder->Shop_Order_Items->findAll(FALSE);
		foreach ($aShop_Order_Items as $oShop_Order_Item)
		{
			if ($oShop_Order_Item->type == 0)
			{
				$oShop_Item = $oShop_Order_Item->Shop_Item;

				// Товару заказа задана связь с товаров, при этому у товара заказа не задан статус, либо задан статус и он не отмененный
				if ($oShop_Item->id && !$oShop_Order_Item->isCanceled())
				{
					//$bSkipItem = $oShop_Item->type == 4;

					$quantity += $oShop_Order_Item->quantity;

					// Prices
					if (Core::moduleIsActive('siteuser') && $this->shopOrder->siteuser_id)
					{
						$oShop_Item_Controller->siteuser(
							$this->shopOrder->Siteuser
						);
					}

					$oShop_Item_Controller->count($oShop_Order_Item->quantity);
					$aPrices = $oShop_Item_Controller->getPrices($oShop_Item);

					// Количество для скидок от суммы заказа рассчитывается отдельно
					/*$oShop_Item->apply_purchase_discount
						&& $quantityPurchaseDiscount += $oShop_Order_Item->quantity;*/

					// Prices
					$price = $oShop_Order_Item->getPrice();
					$amount += $price * $oShop_Order_Item->quantity;

					$weight += $oShop_Item->weight * $oShop_Order_Item->quantity;

					if ($bPositionDiscount /*&& !$bSkipItem*/)
					{
						// По каждой единице товара добавляем цену в массив, т.к. может быть N единиц одого товара
						for ($i = 0; $i < $oShop_Order_Item->quantity; $i++)
						{
							$aDiscountPrices[] = $price;
						}
					}

					// Сумма для скидок от суммы заказа рассчитывается отдельно
					/*$oShop_Item->apply_purchase_discount
						&& $amountPurchaseDiscount += $price * $oShop_Order_Item->quantity;*/

					if ($oShop_Item->apply_purchase_discount /*&& !$bSkipItem*/)
					{
						$bApplyPurchaseDiscount = TRUE;
						foreach ($aPrices['discounts'] as $oShop_Discount)
						{
							if ($oShop_Discount->not_apply_purchase_discount)
							{
								$bApplyPurchaseDiscount = FALSE;
								break;
							}
						}

						if ($bApplyPurchaseDiscount)
						{
							// Сумма для скидок от суммы заказа рассчитывается отдельно
							$amountPurchaseDiscount += $aPrices['price_discount'] * $oShop_Order_Item->quantity;

							// Количество для скидок от суммы заказа рассчитывается отдельно
							$quantityPurchaseDiscount += $oShop_Order_Item->quantity;
						}
					}
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
				'amount' => $amountPurchaseDiscount,
				'quantity' => $quantityPurchaseDiscount,
				'weight' => $weight,
				'prices' => $aDiscountPrices,
				'applyDiscounts' => TRUE,
				'applyDiscountCards' => TRUE
			)
		);

		return $this;
	}
}