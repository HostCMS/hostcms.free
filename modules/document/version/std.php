<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Document_Version_Std
 * Temporary class
 *
 * @package HostCMS
 * @subpackage Document
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Document_Version_Std extends stdClass
{
	public $document_id = NULL;
	public $Template = NULL;
	
	public function getCurrent()
	{
		return $this;
	}
	
	/**
	 * Load document file
	 * @return string|NULL
	 */
	public function loadFile()
	{
		if ($this->document_id)
		{
			return Core_Entity::factory('Document', $this->document_id)->text;
		}
		
		throw new Core_Exception("document_id IS NULL!");
	}
	
	/**
	 * Show document version.
	 *
	 * @hostcms-event document_version.onBeforeExecute
	 * @hostcms-event document_version.onAfterExecute
	 * <code>
	 * Core_Entity::factory('Document', 123)->Document_Versions->getCurrent()->execute();
	 * </code>
	 */
	public function execute()
	{
		if ($this->document_id)
		{
			return Core_Entity::factory('Document', $this->document_id)->execute();
		}
		
		throw new Core_Exception("document_id IS NULL!");
	}
}