<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Document_Relation_Controller_Delete
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Document_Relation_Controller_Delete extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 */
	public function execute($operation = NULL)
	{
		$oShop_Document_Relation = $this->_object;

		$sJSUpdate = '';

		if (!is_null($oShop_Document_Relation) && $oShop_Document_Relation->document_id)
		{
			$oObject = Shop_Controller::getDocument($oShop_Document_Relation->document_id);

			if (!is_null($oObject) && isset($oObject->amount))
			{
				$currentAmount = $oObject->amount;
				$newAmount = $currentAmount - $oShop_Document_Relation->paid;

				$oObject->amount = $newAmount >= 0
					? $newAmount
					: 0;

				$oObject->save();

				$oShop_Document_Relation->delete();

				$sJSUpdate = "$('input[name = amount]').val($.mathRound({$oObject->amount}, 2))";
			}
		}

		if (strlen($sJSUpdate))
		{
			$this->_Admin_Form_Controller->addMessage(
				"<script>$('.shop-document-relation tr#row_0_{$this->_object->id}').remove();" . $sJSUpdate . "</script>"
			);
		}

		return TRUE;
	}
}