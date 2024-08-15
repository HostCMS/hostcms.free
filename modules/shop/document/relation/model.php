<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Document_Relation_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Document_Relation_Model extends Core_Entity
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

	);

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'id';

	protected $_object = NULL;

	protected function _getObject()
	{
		if (is_null($this->_object))
		{
			$this->_object = Shop_Controller::getDocument($this->related_document_id);
		}

		return $this->_object;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function numberBackend()
	{
		$oObject = $this->_getObject();

		return !is_null($oObject)
			? htmlspecialchars((string) $oObject->number)
			: '';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function datetimeBackend()
	{
		$oObject = $this->_getObject();

		return !is_null($oObject) && $oObject->datetime != '0000-00-00 00:00:00' && !is_null($oObject->datetime)
			? htmlspecialchars(Core_Date::sql2datetime($oObject->datetime))
			: '';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function siteuserCompanyNameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$oObject = $this->_getObject();

		return Core::moduleIsActive('siteuser') && !is_null($oObject) && $oObject->siteuser_company_id
			? '<div class="profile-container tickets-container counterparty-block"><ul class="tickets-list">' . $oObject->Siteuser_Company->getProfileBlock('') . '</ul></div>'
			: '';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function siteuserCompanyContractBackend()
	{
		$oObject = $this->_getObject();

		return Core::moduleIsActive('siteuser') && !is_null($oObject)
			? $oObject->Siteuser_Company_Contract->name
			: '';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function typeBackend()
	{
		$type = Shop_Controller::getDocumentType($this->related_document_id);

		$color = Core_Str::createColor($type);

		return '<span class="badge badge-round badge-max-width" style="border-color: ' . $color . '; color: ' . Core_Str::hex2darker($color, 0.2) . '; background-color: ' . Core_Str::hex2lighter($color, 0.88) . '">'
			. Core::_('Shop_Document_Relation.type' . $type)
			. '</span>';
	}

	public function getObject()
	{
		return $this->_getObject();
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function amountBackend()
	{
		$oObject = $this->_getObject();

		$oShop_Currency = NULL;

		if (!is_null($oObject) && method_exists($oObject, 'getAmount'))
		{
			if (isset($oObject->shop_id))
			{
				$oShop_Currency = $oObject->Shop->Shop_Currency;
			}
			else
			{
				$oShop_Currency = $oObject->Shop_Warehouse->Shop->Shop_Currency;
			}

			if (!is_null($oShop_Currency))
			{
				return $oShop_Currency->formatWithCurrency($oObject->getAmount());
			}
		}
	}
}