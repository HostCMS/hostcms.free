<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 * Типовой контроллер создания ярлыка
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Action_Controller_Type_Shortcut extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'value',
		'title', // Form Title
		'selectCaption', // Select caption, e.g. 'Choose a group'
		'selectOptions', // Array of options
		'buttonName', // Button name, e.g. 'Create shortcut'
		'skipColumns' // Array of skipped columns
	);

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);

		// Set default title
		$this->title(
			$this->_Admin_Form_Action->Admin_Word->getWordByLanguage(
				Core_Entity::factory('Admin_Language')->getCurrent()->id
			)->name
		);

		$this->buttonName(Core::_('admin_form.apply'));
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (is_null($operation))
		{
			// Original windowId
			$windowId = $this->_Admin_Form_Controller->getWindowId();

			$newWindowId = 'Shortcut_' . time();

			$oCore_Html_Entity_Form = Core::factory('Core_Html_Entity_Form')
				->action($this->_Admin_Form_Controller->getPath())
				->method('post');

			$window_Admin_Form_Controller = clone $this->_Admin_Form_Controller;
			// Select на всплывающем окне должен быть найден через ID нового окна, а не id_content
			$window_Admin_Form_Controller->window($newWindowId);

			$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
				->id($newWindowId)
				->add($oCore_Html_Entity_Form);

			$oAdmin_Form_Entity_Select = Admin_Form_Entity::factory('Select')
				->name('destinationId')
				->id('destinationId')
				->filter(TRUE)
				->options($this->selectOptions)
				->caption($this->selectCaption)
				->value($this->value)
				->controller($window_Admin_Form_Controller);

			// Идентификаторы переносимых указываем скрытыми полями в форме, чтобы не превысить лимит GET
			$aChecked = $this->_Admin_Form_Controller->getChecked();

			// Clear checked list
			$this->_Admin_Form_Controller->clearChecked();

			foreach ($aChecked as $datasetKey => $checkedItems)
			{
				foreach ($checkedItems as $key => $value)
				{
					$oCore_Html_Entity_Form->add(
						Core::factory('Core_Html_Entity_Input')
							->name('hostcms[checked][' . $datasetKey . '][' . $key . ']')
							->value(1)
							->type('hidden')
							//->controller($window_Admin_Form_Controller)
					);
				}
			}

			$oAdmin_Form_Entity_Button = Admin_Form_Entity::factory('Button')
				->name('apply')
				->type('submit')
				->class('applyButton btn btn-blue')
				->value($this->buttonName)
				->onclick(
					'$("#' . $newWindowId . '").parents(".modal").remove(); '
					. $this->_Admin_Form_Controller->getAdminSendForm(NULL, 'apply')
				)
				->controller($this->_Admin_Form_Controller);

			$oCore_Html_Entity_Form
				->add($oAdmin_Form_Entity_Select)
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12')
						->add($oAdmin_Form_Entity_Button)
				);

			$oCore_Html_Entity_Div->execute();

			ob_start();

			Core::factory('Core_Html_Entity_Script')
				->type("text/javascript")
				->value("$(function() {
				$('#{$newWindowId}').HostCMSWindow({ autoOpen: true, destroyOnClose: false, title: '" . $this->title . "', AppendTo: '#{$windowId}', width: 750, height: 140, addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
				->execute();

			$this->addMessage(ob_get_clean());

			// Break execution for other
			return TRUE;
		}
		else
		{
			$destinationId = Core_Array::getPost('destinationId');

			if (is_null($destinationId))
			{
				throw new Core_Exception("destinationId is NULL");
			}

			$this->_object->shortcut($destinationId);
		}

		return $this;
	}
}