<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Filemanager.
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
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
		try {
			$secret_csrf = Core_Array::getPost('secret_csrf', '', 'trim');
			$this->_checkCsrf($secret_csrf);

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
				/*try
				{*/
					$dirMode = octdec(Core_Array::getPost('dir_mode'));
					Core_File::mkdir($newDir, $dirMode);
				/*}
				catch (Exception $e)
				{
					Core_Message::show($e->getMessage(), 'error');
					return TRUE;
				}*/
			}
			else
			{
				throw new Core_Exception(Core::_('Wysiwyg_Filemanager.isset_dir'), array(), 0, FALSE);
			}
		}
		catch (Exception $e)
		{
			$this->addMessage(Core_Message::get($e->getMessage(), 'error'));
			return TRUE;
		}

		return FALSE;
	}
}