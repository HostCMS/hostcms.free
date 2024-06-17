<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order_Comment_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Order_Comment_Model extends Comment_Model
{
	/**
	 * Name of the table
	 * @var string
	 */
	protected $_tableName = 'comments';

	/**
	 * Name of the model
	 * @var string
	 */
	protected $_modelName = 'comment';

	/**
	 * Backend callback method
	 * @return string
	 */
	/*public function viewBackend()
	{
		ob_start();

		$oShop_Order = $this->Comment_Shop_Order->Shop_Order;

		$href = $oShop_Order->Shop->Structure->getPath() . $oShop_Order->getPath();

		$oSite = $oShop_Order->Shop->Site;
		$oSite_Alias = $oSite->getCurrentAlias();
		!is_null($oSite_Alias) && $href = 'http://' . $oSite_Alias->name . $href;

		Core_Html_Entity::factory('A')
			->href($href)
			->target('_blank')
			->add(
				Core_Html_Entity::factory('I')
					->class('fa fa-external-link')
			)
			->execute();

		return ob_get_clean();
	}*/

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event shop_order_comment.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		// save original _nameColumn
		$nameColumn = $this->_nameColumn;
		$this->_nameColumn = 'subject';

		$newObject = parent::copy();

		$aNewComment_Shop_Order = clone $this->Comment_Shop_Order;
		$newObject->add($aNewComment_Shop_Order);

		// restore original _nameColumn
		$this->_nameColumn = $nameColumn;

		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}
}