<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Site_Alias_Controller_Move.
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Site_Alias_Controller_Move extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'value',
		'title', // Form Title
		'buttonName', // Button name, e.g. 'Move'
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

		$this->buttonName(Core::_('Admin_Form.apply'));
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

			$newWindowId = 'Move_' . time();

			$oCore_Html_Entity_Form = Core::factory('Core_Html_Entity_Form');

			$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
				->id($newWindowId)
				->add($oCore_Html_Entity_Form);

			$oCore_Html_Entity_Form->action($this->_Admin_Form_Controller->getPath())
				->method('post');

			$window_Admin_Form_Controller = clone $this->_Admin_Form_Controller;

			// Select на всплывающем окне должен быть найден через ID нового окна, а не id_content
			$window_Admin_Form_Controller->window($newWindowId);

			$aOptions = array();

			$oSites = Core_Entity::factory('Site');
			$oSites->queryBuilder()
				->where('sites.id', '!=', CURRENT_SITE);

			$aSites = $oSites->findAll(FALSE);

			foreach ($aSites as $oSite)
			{
				$aOptions[$oSite->id] = $oSite->name;
			}

			$oAdmin_Form_Entity_Select = Admin_Form_Entity::factory('Select')
				->name('destinationSiteId')
				->id('destinationSiteId')
				// ->filter(TRUE)
				->options($aOptions)
				->caption(Core::_('Site_Alias.select_site'))
				->value($this->value)
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Checkbox = Admin_Form_Entity::factory('Checkbox')
				->name('get_key')
				->divAttr(array('class' => 'form-group col-xs-12'))
				->caption(Core::_('Site_Alias.get_key'))
				->value(1)
				->checked(FALSE);

			$oCore_Html_Entity_Form
				->add($oAdmin_Form_Entity_Select)
				->add($oAdmin_Form_Entity_Checkbox);

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
					//'$("#' . $newWindowId . '").parents(".modal").remove(); '
					'bootbox.hideAll(); '
					. $this->_Admin_Form_Controller->getAdminSendForm(array('operation' => 'apply'))
				)
				->controller($this->_Admin_Form_Controller);

			$oCore_Html_Entity_Form
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12')
						->add($oAdmin_Form_Entity_Button)
				);

			$oCore_Html_Entity_Div->execute();

			ob_start();

			Core::factory('Core_Html_Entity_Script')
				->value("$(function() {
				$('#{$newWindowId}').HostCMSWindow({ autoOpen: true, destroyOnClose: false, title: '" . Core_Str::escapeJavascriptVariable($this->title) . "', AppendTo: '#{$windowId}', width: 750, height: 160, addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
				->execute();

			$this->addMessage(ob_get_clean());

			// Break execution for other
			return TRUE;
		}
		else
		{
			$destinationSiteId = intval(Core_Array::getPost('destinationSiteId'));

			if (is_null($destinationSiteId))
			{
				throw new Core_Exception("destinationSiteId is NULL");
			}

			$this->_object->move($destinationSiteId);

			if (!is_null(Core_Array::getPost('get_key')))
			{
				$this->_object->getKey();
			}
		}

		return $this;
	}
}