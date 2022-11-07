<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Document_Version_Std
 * Temporary class
 *
 * @package HostCMS
 * @subpackage Document
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Document_Version_Std extends stdClass
{
	/**
	 * Document id
	 * @var mixed
	 */
	public $document_id = NULL;

	/**
	 * Template
	 * @var mixed
	 */
	public $Template = NULL;

	/**
	 * Get current version
	 * @return self
	 */
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