<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Rebuild fast filter
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Filter_Controller_Rebuild extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$oShop = $this->_object;

		if ($oShop->filter)
		{
			$oAdmin_Form_Controller = $this->getController();

			$aShopConfig = Shop_Controller::getConfig();

			$position = Core_Array::getRequest('position', 0, 'int');
			$mode = Core_Array::getRequest('mode', 0, 'int');
			$limit = Core_Array::getRequest('limit', $aShopConfig['fastFilterRebuildLimit'], 'int');
			$iDelay = Core_Array::getRequest('delay', 1, 'int');
			$iMaxTime = Core_Array::getRequest('max_time', 10, 'int');

			$oShop_Filter_Controller = new Shop_Filter_Controller($oShop);

			if ($position == 0 && $mode == 0)
			{
				$oShop_Filter_Controller
					->dropTable()
					->createTable();
			}

			$timeout = Core::getmicrotime();

			if ($mode == 0)
			{
				$Shop_Filter_Group_Controller = new Shop_Filter_Group_Controller($oShop);
				$Shop_Filter_Group_Controller
					->dropTable()
					->createTable()
					->rebuild();

				$mode = 1;

				$bRedirect = TRUE;

				$message = Core_Message::get(Core::_('Shop_Filter.rebuild_groups'), 'info');
			}
			else
			{
				do {
					$oShop_Items = $oShop->Shop_Items;
					$oShop_Items->queryBuilder()
						->where('shop_items.active', '=', 1)
						->limit($limit)
						->offset($position)
						->clearOrderBy()
						->orderBy('shop_items.id');

					$aShop_Items = $oShop_Items->findAll(FALSE);

					foreach ($aShop_Items as $key => $oShop_Item)
					{
						$oShop_Filter_Controller->fill($oShop_Item);

						if (Core::getmicrotime() - $timeout + 3 > $iMaxTime)
						{
							$position += $key + 1;
							break 2;
						}
					}

					$position += $limit;
				}
				while(count($aShop_Items));

				$bRedirect = count($aShop_Items) > 0;

				$message = Core_Message::get(Core::_('Shop_Filter.rebuild_all_items', $position));
			}

			if ($bRedirect)
			{
				$sAdditionalParams = "limit={$limit}&mode={$mode}&delay={$iDelay}&max_time={$iMaxTime}&position=" . $position;

				echo $message;
				?>
				<script type="text/javascript">
				function set_location()
				{
					<?php echo $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'rebuildFilter', NULL, 1, $oShop->id, $sAdditionalParams)?>
				}
				setTimeout('set_location()', <?php echo $iDelay * 1000?>);
				</script><?php
			}
			else
			{
				Core_Message::show(Core::_('Shop_Filter.rebuild_end'));
			}
		}
	}
}