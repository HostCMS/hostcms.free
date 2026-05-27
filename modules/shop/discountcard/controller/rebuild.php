<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discountcard_Controller_Rebuild
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Discountcard_Controller_Rebuild extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'Shop',
		'shop_group_id',
		'max_time',
	);

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 */
	public function execute($operation = NULL)
	{
		$this->max_time = ini_get("max_execution_time");
		$this->max_time > 30 && $this->max_time = 30;

		$iDelay = 1;

		$limit = 100;
		$position = Core_Array::getRequest('position', 0);

		if ($this->Shop->id)
		{
			$timeout = Core::getmicrotime();

			do {
				$oShop_Discountcards = $this->Shop->Shop_Discountcards;
				$oShop_Discountcards->queryBuilder()
					->clearOrderBy()
					->orderBy('shop_discountcards.id', 'ASC')
					->offset($position)
					->limit($limit);

				$aShop_Discountcards = $oShop_Discountcards->findAll(FALSE);

				foreach ($aShop_Discountcards as $oShop_Discountcard)
				{
					$oShop_Discountcard->setSiteuserAmount()->save();
					$oShop_Discountcard->checkLevel();
				}

				$position += $limit;
			}
			while(count($aShop_Discountcards) == $limit && (Core::getmicrotime() - $timeout + 3 < $this->max_time));

			if (count($aShop_Discountcards) < $limit)
			{
				return $this;
			}
			else
			{
				$sAdditionalParams = "shop_id={$this->Shop->id}&shop_group_id={$this->shop_group_id}&position=" . $position;

				Core_Message::show(Core::_('Shop_Discountcard.update_levels', $position));

				?>
				<script type="text/javascript">
				function set_location()
				{
					<?php echo $this->_Admin_Form_Controller->getAdminLoadAjax($this->_Admin_Form_Controller->getPath(), 'rebuildLevels', NULL, $sAdditionalParams)?>
				}
				setTimeout ('set_location()', <?php echo $iDelay * 1000?>);
				</script><?php
			}
		}

		return TRUE;
	}
}