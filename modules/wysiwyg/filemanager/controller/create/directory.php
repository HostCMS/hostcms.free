<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Filemanager.
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Wysiwyg_Filemanager_Controller_Create_Directory extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'cdir',
		'name'
	);

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 */
	public function execute($operation = NULL)
	{
		if (is_null($this->cdir))
		{
			throw new Core_Exception('cdir is NULL.');
		}

		if (is_null($this->name))
		{
			throw new Core_Exception('name is NULL.');
		}

		$dirName = Core_File::pathCorrection(/*Core_File::convertfileNameToLocalEncoding(*/$this->name/*)*/);
		$newDir = CMS_FOLDER . $this->cdir . $dirName;

		if (!file_exists($newDir) && !Core_File::isDir($newDir))
		{
			try
			{
				$dirMode = octdec(Core_Array::getPost('dir_mode'));
				Core_File::mkdir($newDir, $dirMode);
			}
			catch (Exception $e)
			{
				Core_Message::show($e->getMessage(), 'error');
			}
		}
		else
		{
			throw new Core_Exception(Core::_('Wysiwyg_Filemanager.isset_dir'), array(), 0, FALSE);
		}
		return FALSE;
	}
}