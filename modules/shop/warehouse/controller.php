<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Warehouse_Controller
{
	static public function createDocumentButton($oAdmin_Form_Controller, $oEntity, array $aEntities = array())
	{
		$aTmp = array();

		$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
		$oShop_Group = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));

		switch (get_class($oEntity))
		{
			case 'Shop_Warehouse_Purchaseorder_Model':
				$from = 'purchaseorder';
			break;
			case 'Shop_Warehouse_Invoice_Model':
				$from = 'invoice';
			break;
			case 'Shop_Warehouse_Supply_Model':
				$from = 'supply';
			break;
			default:
				$from = '';
		}

		foreach ($aEntities as $entity)
		{
			$additionalParams = "shop_id={$oShop->id}&shop_group_id=$oShop_Group->id";
			$from != ''
				&& $additionalParams .= "&createFrom={$from}&createFromId={$oEntity->id}";

			$path = '/admin/shop/warehouse/' . $entity . '/index.php';

			switch ($entity)
			{
				case 'invoice':
					$caption = Core::_('Shop_Warehouse_Purchaseorder.create_invoice');
					$icon = 'fa-solid fa-fw fa-file-invoice-dollar';
				break;
				case 'supply':
					$caption = Core::_('Shop_Warehouse_Purchaseorder.create_supply');
					$icon = 'fa-solid fa-fw fa-cart-flatbed';
				break;
				case 'purchasereturn':
					$caption = Core::_('Shop_Warehouse_Supply.create_purchasereturn');
					$icon = 'fa-solid fa-fw fa-cart-flatbed';
				break;
				case 'warrant_order':
					$caption = Core::_('Shop_Warehouse_Purchaseorder.create_warrant_order');
					$icon = 'fa-solid fa-fw fa-cash-register';
					$path = '/admin/shop/warrant/index.php';
					$additionalParams .= '&type=0';
				break;
				case 'warrant_pay':
					$caption = Core::_('Shop_Warehouse_Purchaseorder.create_warrant_pay');
					$icon = 'fa-solid fa-fw fa-building-columns';
					$path = '/admin/shop/warrant/index.php';
					$additionalParams .= '&type=3';
				break;
			}

			$options = array('path' => $path, 'action' => 'edit', 'datasetKey' => 0, 'datasetValue' => 0, 'additionalParams' => $additionalParams);

			$href = $oAdmin_Form_Controller->getAdminActionLoadHref($options);

			$options['operation'] = 'modal';
			$onclick = $oAdmin_Form_Controller->getAdminActionModalLoad($options);

			$aTmp[] = array(
				'entity' => $entity,
				'caption' => $caption,
				'href' => $href,
				'onclick' => $onclick,
				'icon' => $icon
			);
		}

		ob_start();

		if (count($aTmp))
		{
			?><div id="create-document-button" class="btn-group btn-group-short <?php echo (!$oEntity->id ? ' hidden' : '')?>">
				<a class="btn btn-labeled btn-info" data-toggle="dropdown" href="javascript:void(0);"><i class="btn-label fa-solid fa-file-import"></i><span><?php echo Core::_('Shop_Warehouse_Purchaseorder.create_document')?><span></a>
				<a class="btn btn-azure dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" aria-expanded="false"><i class="fa fa-angle-down"></i></a>
				<ul class="dropdown-menu dropdown-default">
					<?php
					foreach ($aTmp as $aLink)
					{
						?><li id="create-<?php echo $aLink['entity']?>">
							<a href="<?php echo $aLink['href']?>" onclick="<?php echo $aLink['onclick']?>"><i class="<?php echo $aLink['icon']?>"></i><?php echo $aLink['caption']?></a>
						</li><?php
					}
					?>
				</ul>
			</div><?php
		}

		return ob_get_clean();
	}

	static public function getJsRefresh($oAdmin_Form_Controller, $oEntity, array $aEntities = array())
	{
		$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));

		$windowId = $modalWindowId
			? preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'))
			: $oAdmin_Form_Controller->getWindowId();

		$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
		$oShop_Group = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));

		$sJsRefresh = "<script>
		var jCreateDocumentButton = $('#{$windowId} #create-document-button');
		";
		foreach ($aEntities as $entity)
		{
			$additionalParams = "shop_id={$oShop->id}&shop_group_id=$oShop_Group->id&createFrom={$entity}&createFromId={$oEntity->id}";

			$path = '/admin/shop/warehouse/' . $entity . '/index.php';

			$options = array('path' => $path, 'action' => 'edit', 'datasetKey' => 0, 'datasetValue' => 0, 'additionalParams' => $additionalParams);

			$href = $oAdmin_Form_Controller->getAdminActionLoadHref($options);

			$options['operation'] = 'modal';
			$onclick = $oAdmin_Form_Controller->getAdminActionModalLoad($options);

			$sJsRefresh .= "
				jCreateDocumentButton
					.find('#create-" . $entity . " a')
					.attr({
						href: \"{$href}\",
						onclick: \"{$onclick}\"
					});
			";
		}
		$sJsRefresh .= "
			jCreateDocumentButton.removeClass('hidden');
		</script>
		";

		return $sJsRefresh;
	}
}