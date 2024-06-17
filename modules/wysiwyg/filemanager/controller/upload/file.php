<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Filemanager.
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Wysiwyg_Filemanager_Controller_Upload_File extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'cdir',
		'file'
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
			$secret_csrf = Core_Array::getGet('secret_csrf', '', 'trim');
			$this->_checkCsrf($secret_csrf);

			if (is_null($this->cdir))
			{
				throw new Core_Exception('cdir is NULL.');
			}

			if (is_null($this->file))
			{
				throw new Core_Exception('file is NULL.');
			}

			if (isset($this->file['name']))
			{
				if (is_array($this->file['name']))
				{
					foreach ($this->file['name'] as $key => $fileName)
					{
						$this->_uploadFile($this->file['tmp_name'][$key], $fileName);
					}
				}
				else
				{
					$this->_uploadFile($this->file['tmp_name'], $this->file['name']);
				}

				if (function_exists('opcache_reset'))
				{
					opcache_reset();
				}
			}
		}
		catch (Exception $e)
		{
			$this->addMessage(Core_Message::get($e->getMessage(), 'error'));
			return TRUE;
		}

		return FALSE;
	}

	/**
	 * Upload file
	 * @param string $tmpFile
	 * @param string $fileName
	 */
	protected function _uploadFile($tmpFile, $fileName)
	{
		$target = CMS_FOLDER . $this->cdir . Core_File::filenameCorrection(
			/*Core_File::convertfileNameToLocalEncoding(*/$fileName/*)*/
		);

		Core_File::moveUploadedFile($tmpFile, $target);
	}
}