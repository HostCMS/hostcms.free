<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Discountcard_Export_Controller
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Discountcard_Export_Controller
{
	/**
	 * Shop object
	 * @var Shop_Model
	 */
	private $_oShop = NULL;

	/**
	 * CSV data
	 * @var array
	 */
	private $_aCurrentData;

	/**
	 * Constructor.
	 */
	public function __construct(Shop_Model $oShop)
	{
		$this->_oShop = $oShop;

		$iCurrentDataPosition = 0;

		$this->_aCurrentData[$iCurrentDataPosition] = array(
			'"' . Core::_('Shop_Discountcard_Export.number') . '"',
			'"' . Core::_('Shop_Discountcard_Export.datetime') . '"',
			'"' . Core::_('Shop_Discountcard_Export.login') . '"',
			'"' . Core::_('Shop_Discountcard_Export.active') . '"',
			'"' . Core::_('Shop_Discountcard_Export.amount') . '"',
			'"' . Core::_('Shop_Discountcard_Export.level') . '"',
			'"' . Core::_('Shop_Discountcard_Export.discount') . '"',
		);
	}

	/**
	 * Prepare string
	 * @param string $string
	 * @return string
	 */
	protected function _prepareString($string)
	{
		return str_replace('"', '""', trim($string));
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$oUser = Core_Auth::getCurrentUser();
		if (!$oUser->superuser && $oUser->only_access_my_own)
		{
			return FALSE;
		}

		header("Pragma: public");
		header("Content-Description: File Transfer");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename = " . 'shop_discountcards_' . date("Y_m_d_H_i_s") . '.csv' . ";");
		header("Content-Transfer-Encoding: binary");

		if (!defined('DENY_INI_SET') || !DENY_INI_SET)
		{
			@set_time_limit(1200);
			ini_set('max_execution_time', '1200');
		}

		foreach ($this->_aCurrentData as $aData)
		{
			$this->_printRow($aData);
		}

		$offset = 0;
		$limit = 100;

		do {
			$oShop_Discountcards = $this->_oShop->Shop_Discountcards;
			$oShop_Discountcards->queryBuilder()
				->clearOrderBy()
				->orderBy('shop_discountcards.id')
				->offset($offset)
				->limit($limit);

			$aShop_Discountcards = $oShop_Discountcards->findAll(FALSE);

			foreach ($aShop_Discountcards as $oShop_Discountcard)
			{
				$siteuserLogin = Core::moduleIsActive('siteuser')
					? $oShop_Discountcard->Siteuser->login
					: '';

				$levelName = $discount = '';

				if ($oShop_Discountcard->shop_discountcard_level_id)
				{
					$levelName = $oShop_Discountcard->Shop_Discountcard_Level->name;
					$discount = $oShop_Discountcard->Shop_Discountcard_Level->discount . '%';
				}

				$aRows = array(
					sprintf('"%s"', $this->_prepareString($oShop_Discountcard->number)),
					sprintf('"%s"', $this->_prepareString(Core_Date::sql2datetime($oShop_Discountcard->datetime))),
					sprintf('"%s"', $this->_prepareString($siteuserLogin)),
					sprintf('"%s"', $this->_prepareString($oShop_Discountcard->active)),
					sprintf('"%s"', $this->_prepareString($oShop_Discountcard->amount)),
					sprintf('"%s"', $this->_prepareString($levelName)),
					sprintf('"%s"', $this->_prepareString($discount)),
				);

				$this->_printRow($aRows);
			}

			$offset += $limit;
		}
		while (count($aShop_Discountcards));

		exit();
	}

	/**
	 * Print array
	 * @param array $aData
	 * @return self
	 */
	protected function _printRow($aData)
	{
		echo Core_Str::iconv('UTF-8', 'Windows-1251', implode(';', $aData) . "\n");
		return $this;
	}
}