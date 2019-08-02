<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ списка дисконтных карт.
 *
 * Доступные методы:
 *
 * - showLevels(TRUE|FALSE) выводить список уровней дисконтных карт, по умолчанию TRUE.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Discountcard_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'showLevels',
	);

	/**
	 * Constructor.
	 * @param Siteuser_Model $oSiteuser siteuser
	 */
	public function __construct(Siteuser_Model $oSiteuser)
	{
		parent::__construct($oSiteuser->clearEntities());

		$this->showLevels = TRUE;
	}

	/**
	 * Show built data
	 * @return self
	 * @hostcms-event Shop_Discountcard_Controller_Show.onBeforeRedeclaredShow
	 */
	public function show()
	{
		Core_Event::notify(get_class($this) . '.onBeforeRedeclaredShow', $this);

		$oSiteuser = $this->getEntity();

		$aShops = $oSiteuser->Site->Shops->findAll(FALSE);

		foreach ($aShops as $oShop)
		{
			$this->addEntity(
				$oShop->clearEntities()
			);

			$oShop_Discountcards = $oSiteuser->Shop_Discountcards;
			$oShop_Discountcards
				->queryBuilder()
				->where('shop_discountcards.shop_id', '=', $oShop->id);

			$aShop_Discountcards = $oShop_Discountcards->findAll(FALSE);

			foreach ($aShop_Discountcards as $oShop_Discountcard)
			{
				$oShop->addEntity(
					$oShop_Discountcard->clearEntities()
				);
			}

			if ($this->showLevels)
			{
				$aShop_Discountcard_Levels = $oShop->Shop_Discountcard_Levels->findAll(FALSE);

				foreach ($aShop_Discountcard_Levels as $oShop_Discountcard_Level)
				{
					$oShop->addEntity(
						$oShop_Discountcard_Level->clearEntities()
					);
				}
			}
		}

		return parent::show();
	}
}