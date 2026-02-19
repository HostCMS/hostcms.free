<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Item_Code_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Shop_Order_Item_Code_Model extends Core_Entity
{
	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_order_item' => array(),
		'shop_codetype' => array(),
	);

	/**
	 * Get Nomenclature Code
	 * @return string
	 */
	public function getNomenclatureCode()
	{
		$code = $this->code;

		if ($this->shop_codetype_id && strlen($this->code))
		{
			switch ($this->Shop_Codetype->code)
			{
				case 'shoes':
					/*
					01 - идентификатор применения, 2 символа
					02900000578296 - код товара (0 и GTIN 13 цифр, всего 14)
					21 - идентификатор применения, 2 символа
					ljWMSdmijz"Y0 - индивидуальный серийный номер единицы товара (13 символов)
					91 - идентификатор применения, 2 символа
					003A - индивидуальный порядковый номер ключа проверки
					92 - идентификатор применения, 2 символа
					UxabXQuJ7gRQEUwJHEIHExxQMc2dXkQ53TkFsxMIbmpTP8QbWNlbMA5UOyxLdAUnLdDSMJBCcZvAkZ5vNt52Ow== - значение кода проверки
					*/
					preg_match('/^(01\d{14}21.*?)91/', $this->code, $matches);

					if (isset($matches[1]))
					{
						$hex = strtoupper(bin2hex($matches[1]));
						$code = wordwrap($hex, 2, ' ', TRUE);
					}
				break;
			}
		}

		return $code;
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_order_item_code.onBeforeGetRelatedSite
	 * @hostcms-event shop_order_item_code.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Order_Item->Shop_Order->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}
}
