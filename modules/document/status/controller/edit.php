<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Document_Status Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Document
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Document_Status_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Prepare backend item's edit form
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$this->title($this->_object->id
			? Core::_('Document_Status.edit_title', $this->_object->name, FALSE)
			: Core::_('Document_Status.add_title')
		);
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