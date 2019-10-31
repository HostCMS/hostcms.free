<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Rebuild fast filter
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Filter_Controller_Rebuild extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$oShop = $this->_object;

		if ($oShop->filter)
		{
			$oAdmin_Form_Controller = $this->getController();

			$position = Core_Array::getRequest('position', 0);
			$limit = intval(Core_Array::getRequest('limit', 500));
			$iDelay = intval(Core_Array::getRequest('delay', 1));
			$iMaxTime = intval(Core_Array::getRequest('max_time', 10));

			$oShop_Filter_Controller = new Shop_Filter_Controller($oShop);

			if ($position == 0)
			{
				$oShop_Filter_Controller
					->dropTable()
					->createTable();
			}

			$timeout = Core::getmicrotime();

			do {
				$oShop_Items = $oShop->Shop_Items;
				$oShop_Items->queryBuilder()
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

			if (count($aShop_Items) == $limit)
			{
				$sAdditionalParams = "limit={$limit}&delay={$iDelay}&max_time={$iMaxTime}&position=" . $position;

				Core_Message::show(Core::_('Shop_Filter.rebuild_all_items', $position));

				?>
				<script type="text/javascript">
				function set_location()
				{
					<?php echo $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'rebuildFilter', NULL, 1, $oShop->id, $sAdditionalParams)?>
				}
				setTimeout ('set_location()', <?php echo $iDelay * 1000?>);
				</script><?php
			}
			else
			{
				Core_Message::show(Core::_('Shop_Filter.rebuild_end'));
			}
		}
	}
}