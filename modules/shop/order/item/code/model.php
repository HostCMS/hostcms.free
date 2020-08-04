<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Item_Code_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 */
	public function getNomenclatureCode()
	{
		$code = NULL;

		if (strlen($this->code))
		{
			/*
			01 - идентификатор применения
			02900000578296 - код товара (0 и GTIN)
			21 - идентификатор применения
			ljWMSdmijz"Y0 - индивидуальный серийный номер единицы товара
			91 - идентификатор применения
			003A - индивидуальный порядковый номер ключа проверки
			92 - идентификатор применения
			UxabXQuJ7gRQEUwJHEIHExxQMc2dXkQ53TkFsxMIbmpTP8QbWNlbMA5UOyxLdAUnLdDSMJBCcZvAkZ5vNt52Ow== - значение кода проверки
			*/
			preg_match('/^(01\d{14}21.*?)91/', $this->code, $matches);

			if (isset($matches[1]))
			{
				$hex = strtoupper(bin2hex($matches[1]));
				$code = wordwrap($hex, 2, ' ', TRUE);
			}
		}

		return $code;
	}
}
