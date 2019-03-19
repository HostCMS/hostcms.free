<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Document_Status Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Document
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Document_Status_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$title = $this->_object->id
			? Core::_('Document_Status.edit_title', $this->_object->name)
			: Core::_('Document_Status.add_title');

		$this->title($title);
	}

	/**
	 * Fill document statuses list
	 * @param int $iSiteId site ID
	 * @return array
	 */
	public function fillDocumentStatus($iSiteId)
	{
		$iSiteId = intval($iSiteId);

		$aReturn = array();
		$aChildren = Core_Entity::factory('Document_Status')->getBySiteId($iSiteId);

		if (count($aChildren))
		{
			foreach ($aChildren as $oMenu)
			{
				$aReturn[$oMenu->id] = $oMenu->name;
			}
		}

		return $aReturn;
	}
}