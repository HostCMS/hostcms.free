<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Показ списка дисконтных карт.
 *
 * Доступные методы:
 *
 * - showLevels(TRUE|FALSE) выводить список уровней дисконтных карт, по умолчанию TRUE.
 * - showBonuses(60|FALSE) выводить бонусы на указанное количество дней вперед, по умолчанию 60.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Discountcard_Controller_Show extends Core_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'showLevels',
		'showBonuses',
	);

	/**
	 * Constructor.
	 * @param Siteuser_Model $oSiteuser siteuser
	 */
	public function __construct(Siteuser_Model $oSiteuser)
	{
		parent::__construct($oSiteuser->clearEntities());

		$oSiteuser->showXmlProperties(TRUE);

		$this->showLevels = TRUE;
		$this->showBonuses = 60;
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

				// Show bonuses for 2 year
				if ($this->showBonuses > 0 && $this->showBonuses < 730)
				{
					$datetime = Core_Date::timestamp2sql(time());

					$oShop_Discountcard_Bonuses = $oShop_Discountcard->Shop_Discountcard_Bonuses;
					$oShop_Discountcard_Bonuses->queryBuilder()
						//->where('shop_discountcard_bonuses.datetime', '<=', $datetime)
						->where('shop_discountcard_bonuses.expired', '>=', $datetime)
						->where('shop_discountcard_bonuses.written_off', '<', Core_QueryBuilder::expression('shop_discountcard_bonuses.amount'))
						->clearOrderBy()
						->orderBy('shop_discountcard_bonuses.id');

					$aShop_Discountcard_Bonuses = $oShop_Discountcard_Bonuses->findAll();

					if (count($aShop_Discountcard_Bonuses))
					{
						$oShop_Discountcard->addEntity(
							$oBonusesEntity = Core::factory('Core_Xml_Entity')
								->name('bonuses')
						);

						$maxBonus = 0;

						$dateTime = Core_Date::datetime2timestamp(date('Y-m-d 23:59:59'));
						for ($i = 0; $i < $this->showBonuses; $i++)
						{
							$amount = 0;

							foreach ($aShop_Discountcard_Bonuses as $oShop_Discountcard_Bonus)
							{
								if (Core_Date::sql2timestamp($oShop_Discountcard_Bonus->datetime) <= $dateTime
									&& Core_Date::sql2timestamp($oShop_Discountcard_Bonus->expired) >= $dateTime)
								{
									$amount += $oShop_Discountcard_Bonus->amount - $oShop_Discountcard_Bonus->written_off;
								}
							}

							$amount = Shop_Controller::instance()->round($amount);

							$oBonusesEntity->addEntity(
								Core::factory('Core_Xml_Entity')
									->name('day')
									->value($amount)
									->addAttribute('id', $i)
									->addAttribute('date', Core_Date::timestamp2date($dateTime))
							);

							$maxBonus < $amount && $maxBonus = $amount;

							// Next day
							$dateTime += 86400;
						}

						$oBonusesEntity->addAttribute('max', $maxBonus);
					}
				}
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