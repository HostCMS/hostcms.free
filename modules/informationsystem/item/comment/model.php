<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Item_Comment_Model
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Item_Comment_Model extends Comment_Model
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
	 * Callback function
	 */
	public function viewBackend()
	{
		ob_start();

		$oInformationsystem_Item = $this->Comment_Informationsystem_Item->Informationsystem_Item;

		$href = $oInformationsystem_Item->Informationsystem->Structure->getPath() . $oInformationsystem_Item->getPath();

		$oSite = $oInformationsystem_Item->Informationsystem->Site;
		$oSite_Alias = $oSite->getCurrentAlias();
		!is_null($oSite_Alias) && $href = 'http://' . $oSite_Alias->name . $href;

		Core_Html_Entity::factory('A')
			->href($href)
			->target('_blank')
			->add(
				Core_Html_Entity::factory('I')->class('fa fa-external-link')
			)
			->execute();

		return ob_get_clean();
	}

	/**
	 * Copy object
	 * @return Core_Entity
	 * @hostcms-event informationsystem_item_comment.onAfterRedeclaredCopy
	 */
	public function copy()
	{
		// save original _nameColumn
		$nameColumn = $this->_nameColumn;
		$this->_nameColumn = 'subject';

		$newObject = parent::copy();

		$aNewComment_Informationsystem_Item = clone $this->Comment_Informationsystem_Item;
		$newObject->add($aNewComment_Informationsystem_Item);

		// restore original _nameColumn
		$this->_nameColumn = $nameColumn;
		
		Core_Event::notify($this->_modelName . '.onAfterRedeclaredCopy', $newObject, array($this));

		return $newObject;
	}
}