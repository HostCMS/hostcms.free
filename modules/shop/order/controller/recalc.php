<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Controller_Recalc extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 */
	public function execute($operation = NULL)
	{
		// Replace shop_delivery_id
		$this->_object->shop_delivery_id = Core_Array::getPost('shop_delivery_id');
		$this->_object->shop_country_id = Core_Array::getPost('shop_country_id');
		$this->_object->shop_country_location_id = Core_Array::getPost('shop_country_location_id');
		$this->_object->shop_country_location_city_id = Core_Array::getPost('shop_country_location_city_id');
		$this->_object->shop_country_location_city_area_id = Core_Array::getPost('shop_country_location_city_area_id');
		$this->_object->save();

		$oShop_Delivery = Core_Entity::factory('Shop_Delivery')->getById($this->_object->shop_delivery_id);

		if (!is_null($oShop_Delivery))
		{
			$windowId = $this->_Admin_Form_Controller->getWindowId();

			switch ($oShop_Delivery->type)
			{
				case 0:
				default:
					$this->_object->recalcDelivery();

					Core::factory('Admin_Form_Entity_Code')
						->html('
							<script>
								$("#' . $windowId . ' #sum").val(' . $this->_object->getAmount() . ');
								$("#' . $windowId . ' #shop_delivery_condition_id").val(' . $this->_object->shop_delivery_condition_id . ');
							</script>
						')
						->execute();

					Core_Message::show(Core::_('Shop_Order.recalc_delivery_success'));
				break;
				case 1:
					?>
					<div class="modal fade" id="conditionsModal<?php echo $oShop_Delivery->id?>" tabindex="-1" role="dialog" aria-labelledby="conditionsModalLabel">
						<div class="modal-dialog modal-lg" role="document">
							<div class="modal-content">
								<div class="modal-header">
									<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
									<h4 class="modal-title"><?php echo Core::_('Shop_Delivery.conditions_for_delivery', $oShop_Delivery->name)?></h4>
								</div>
								<div class="modal-body">
									<?php
									$aShop_Delivery_Conditions = array();

									try
									{
										$aPrice = Shop_Delivery_Handler::factory($oShop_Delivery)
											->country($this->_object->shop_country_id)
											->location($this->_object->shop_country_location_id)
											->city($this->_object->shop_country_location_city_id)
											->weight($this->_object->getWeight())
											->amount($this->_object->getAmount())
											->postcode($this->_object->postcode)
											->volume(NULL)
											->execute();

										if (!is_null($aPrice))
										{
											!is_array($aPrice) && $aPrice = array($aPrice);

											foreach ($aPrice as $key => $oShop_Delivery_Condition)
											{
												if (!is_object($oShop_Delivery_Condition))
												{
													$tmp = $oShop_Delivery_Condition;
													$oShop_Delivery_Condition = new StdClass();
													$oShop_Delivery_Condition->price = $tmp;
													$oShop_Delivery_Condition->rate = 0;
													$oShop_Delivery_Condition->description = NULL;
												}

												$aShop_Delivery_Conditions[] = array(
													'value' => $oShop_Delivery_Condition->price,
													'name' => $oShop_Delivery_Condition->description
												);
											}
										}
									}
									catch (Exception $e)
									{
										Core_Message::show($e->getMessage(), 'error');

										$aShop_Delivery_Conditions = array();
									}

									if (count($aShop_Delivery_Conditions))
									{
										$oShop_Currency = $this->_object->Shop->Shop_Currency;

										foreach ($aShop_Delivery_Conditions as $key => $aData)
										{
											$checked = $key == 0
												? 'checked="checked"'
												: '';
											?>
											<div class="radio">
												<label>
													<input name="shop_delivery_condition_price" type="radio" data-name="<?php echo htmlspecialchars($aData['name'])?>" value="<?php echo htmlspecialchars($aData['value'])?>" <?php echo $checked?>/>
													<span class="text">
														<b><?php echo htmlspecialchars($aData['name'])?></b>
														<small class="margin-left-20"><?php echo htmlspecialchars($aData['value']) . ' ' . htmlspecialchars($oShop_Currency->name)?></small>
													</span>
												</label>
											</div>
											<?php
										}
									}
									else
									{
										Core_Message::show(Core::_('Shop_Delivery.empty_conditions_for_delivery'), 'error');
									}
									?>
								</div>
								<div class="modal-footer">
									<button type="button" class="btn btn-success conditions-button"><?php echo Core::_('Shop_Delivery.apply_button')?></button>
								</div>
							</div>
						</div>
					</div>
					<script>
						$(function(){
							$('#conditionsModal<?php echo $oShop_Delivery->id?>').modal('show');

							$('.conditions-button').on('click', function(){
								var $input = $('input[name=shop_delivery_condition_price]:checked');

								$.ajax({
									url: '/admin/shop/order/index.php',
									data: { 'recalcFormula': 1, 'shop_order_id': <?php echo $this->_object->id?>, 'shop_delivery_condition_name': $input.data('name'), 'shop_delivery_condition_price': $input.val() },
									dataType: 'json',
									type: 'POST',
									success: function(answer){
										if (answer.status == 'success')
										{
											$('#conditionsModal<?php echo $oShop_Delivery->id?>').modal('hide');
											$('#<?php echo $windowId?> #id_message').append('<div class="alert alert-success fade in"><button type="button" class="close" data-dismiss="alert">&times;</button>' + answer.message + '</div>');

											var $deliveryTr = $('#<?php echo $windowId?> .shop-item-table.shop-order-items tr#' + answer.shop_order_item_id);

											$deliveryTr.find('input[name=shop_order_item_name_' + answer.shop_order_item_id + ']').val($input.data('name'));
											$deliveryTr.find('input[name=shop_order_item_quantity_' + answer.shop_order_item_id + ']').val('1.00');
											$deliveryTr.find('input[name=shop_order_item_price_' + answer.shop_order_item_id + ']').val($input.val());

											$.recountTotal();
										}
										else
										{
											console.log(answer.status);
										}
									}
								});
							});

							$('#conditionsModal<?php echo $oShop_Delivery->id?> :input').on('click', function() { mainFormLocker.unlock() });
						});
					</script>
					<?php
				break;
			}
		}

		return TRUE;
	}
}