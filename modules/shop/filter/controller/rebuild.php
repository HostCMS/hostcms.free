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

			$iLimit = intval(Core_Array::getRequest('limit', 5));
			$iDelay = intval(Core_Array::getRequest('delay', 1));
			$iMaxTime = intval(Core_Array::getRequest('max_time', 20));

			$oShop_Filter_Controller = new Shop_Filter_Controller($oShop);

			$bCompleted = $oShop_Filter_Controller
				->limit($iLimit)
				->max_time($iMaxTime)
				->position(Core_Array::getRequest('position', 0))
				->rebuild();

			// echo "<pre>";
			// var_dump($bCompleted);
			// echo "</pre>";

			if ($bCompleted === FALSE)
			{
				$sAdditionalParams = "limit={$iLimit}&delay={$iDelay}&max_time={$iMaxTime}&position=" . $oShop_Filter_Controller->position;

				Core_Message::show(Core::_('Shop_Filter.rebuild_all_items', $oShop_Filter_Controller->position));

				?>
				<script type="text/javascript">
				function set_location()
				{
					<?php echo $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'rebuildFilter', NULL, 1, $oShop->id, $sAdditionalParams)?>
				}
				setTimeout ('set_location()', <?php echo $iDelay * 1000?>);
				</script><?php
			}
		}

		// return $this;
	}
}