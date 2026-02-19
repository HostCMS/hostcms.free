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
class Wysiwyg_Filemanager_Controller_Rename extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'cdir',
		'title',
	);

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);

		$this->title = Core::_('Admin_Form.rename');
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 */
	public function execute($operation = NULL)
	{
		if (is_null($operation))
		{
			$windowId = $this->_Admin_Form_Controller->getWindowId();

			$newWindowId = 'Rename_' . time();

			$window_Admin_Form_Controller = clone $this->_Admin_Form_Controller;

			$oCore_Html_Entity_Form = Core_Html_Entity::factory('Form');

			$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')
				->id($newWindowId)
				->add($oCore_Html_Entity_Form);

			$oCore_Html_Entity_Form
				->action($this->_Admin_Form_Controller->getPath())
				->method('post');

			$window_Admin_Form_Controller->window($newWindowId);

			$oCore_Html_Entity_Form->add(
				Admin_Form_Entity::factory('Div')
					->class('row')
					->add(
						Admin_Form_Entity::factory('Input')
							->divAttr(array('class' => 'form-group col-xs-12'))
							->name('new_name')
							->caption(Core::_('Wysiwyg_Filemanager.new_name'))
							->value($this->_object->name)
							->controller($window_Admin_Form_Controller)
					)
			);

			// Идентификаторы переносимых указываем скрытыми полями в форме, чтобы не превысить лимит GET
			$aChecked = $this->_Admin_Form_Controller->getChecked();

			// Clear checked list
			$this->_Admin_Form_Controller->clearChecked();

			foreach ($aChecked as $datasetKey => $checkedItems)
			{
				$oAdmin_Form_Dataset_Entity = $this->_Admin_Form_Controller->getDataset($datasetKey);

				if ($oAdmin_Form_Dataset_Entity)
				{
					foreach ($checkedItems as $key => $value)
					{
						$oCore_Html_Entity_Form->add(
							 Core_Html_Entity::factory('Input')
								->name('hostcms[checked][' . $datasetKey . '][' . $key . ']')
								->value(1)
								->type('hidden')
						);
					}
				}
			}

			$oAdmin_Form_Entity_Button = Admin_Form_Entity::factory('Button')
				->name('rename')
				->type('submit')
				->class('applyButton btn btn-blue')
				->value(Core::_('Admin_Form.rename'))
				->onclick(
					'bootbox.hideAll(); '
					. $this->_Admin_Form_Controller->getAdminSendForm(array('operation' => 'rename'))
				)
				->controller($this->_Admin_Form_Controller);

			$oCore_Html_Entity_Form
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add(
							Admin_Form_Entity::factory('Div')
								->class('form-group col-xs-12 margin-top-10')
								->add($oAdmin_Form_Entity_Button)
						)
				);

			$oCore_Html_Entity_Div->execute();

			ob_start();

			Core_Html_Entity::factory('Script')
				->value("$(function() {
					$('#{$newWindowId}').HostCMSWindow({ autoOpen: true, destroyOnClose: false, title: '" . Core_Str::escapeJavascriptVariable($this->title) . "', AppendTo: '#{$windowId}', width: 800, height: 150, addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
				->execute();

			$this->addMessage(ob_get_clean());

			// Break execution for other
			return TRUE;
		}
		else
		{
			$new_name = Core_Array::getPost('new_name', '', 'trim');
			if ($new_name != '')
			{
				Core_File::rename($this->_getFilePath($this->_object->name), $this->_getFilePath($new_name), TRUE);
			}
		}
	}

	/**
	 * Get file path
	 * @return string
	 */
	protected function _getFilePath($name)
	{
		return CMS_FOLDER . Core_File::pathCorrection(ltrim($this->cdir, '/\\') . $name);
	}
}