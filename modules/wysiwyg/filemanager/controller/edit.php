<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Wysiwyg_Filemanager Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Wysiwyg
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Wysiwyg_Filemanager_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Get file path
	 * @return string
	 */
	protected function _getFilePath()
	{
		return CMS_FOLDER . Core_File::pathCorrection(
			Core_Array::getRequest('cdir') . (!is_null(Core_Array::getRequest('dir')) ? Core_Array::getRequest('dir') . DIRECTORY_SEPARATOR : '')
			. /*Core_File::convertfileNameToLocalEncoding(*/$this->_object->name/*)*/);
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		//parent::_prepareForm();

		$this->_prepeared = TRUE;

		$oMainTab = Admin_Form_Entity::factory('Tab')
			->caption('main')
			->name('main');

		$this->addTab($oMainTab);

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$filePath = $this->_getFilePath();

		if (!Core_File::isFile($filePath))
		{
			throw new Core_Exception('File %file not found', array('%file' => $this->_object->name));
		}

		switch (Core_File::getExtension($filePath))
		{
			case 'php':
			case 'html':
			case 'htm':
				$mode = 'ace/mode/php';
			break;
			case 'css':
				$mode = 'ace/mode/css';
			break;
			case 'less':
				$mode = 'ace/mode/less';
			break;
			case 'scss':
				$mode = 'ace/mode/scss';
			break;
			case 'xml':
			case 'xsl':
				$mode = 'ace/mode/scss';
			break;
			case 'sql':
				$mode = 'ace/mode/sql';
			break;
			case 'tpl':
				$mode = 'ace/mode/smarty';
			break;
			case 'js':
				$mode = 'ace/mode/javascript';
			break;
			case 'json':
				$mode = 'ace/mode/json';
			break;
			default:
				$mode = 'ace/mode/text';
		}

		$oFile_Content = Admin_Form_Entity::factory('Textarea');

		$oTmpOptions = $oFile_Content->syntaxHighlighterOptions;
		$oTmpOptions['mode'] = $mode;

		$oFile_Content
			->value(
				Core_File::read($filePath)
			)
			->caption(Core::_('Wysiwyg_Filemanager.edit_file_text'))
			->name('text')
			->rows(40)
			->syntaxHighlighter(defined('SYNTAX_HIGHLIGHTING') ? SYNTAX_HIGHLIGHTING : TRUE)
			->syntaxHighlighterOptions($oTmpOptions);

		$oMainRow1->add($oFile_Content);

		$this->_addCsrfToken();

		$this->title(
			Core::_('Wysiwyg_Filemanager.edit_file', $this->_object->name, FALSE)
		);

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Wysiwyg_Filemanager_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$secret_csrf = Core_Array::getPost('secret_csrf', '', 'trim');
		$this->_checkCsrf($secret_csrf);

		$filePath = $this->_getFilePath();

		$content = Core_Array::getPost('text');
		if (!is_null($content))
		{
			Core_File::write($filePath, $content);

			if (function_exists('opcache_reset'))
			{
				opcache_reset();
			}
		}

		//parent::_applyObjectProperty();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}
}