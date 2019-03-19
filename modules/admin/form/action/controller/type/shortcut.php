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
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Admin_Form_Action_Controller_Type_Shortcut extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'autocomplete',
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

		$this->autocomplete = FALSE;
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

			if (!$this->autocomplete)
			{
				$oAdmin_Form_Entity_Select = Admin_Form_Entity::factory('Select')
					->name('destinationId')
					->id('destinationId')
					->filter(TRUE)
					->options($this->selectOptions)
					->caption($this->selectCaption)
					->value($this->value)
					->controller($window_Admin_Form_Controller);

				$oCore_Html_Entity_Form->add($oAdmin_Form_Entity_Select);
			}
			else
			{
				$oAdmin_Form_Entity_Input = Admin_Form_Entity::factory('Input')
					->caption($this->selectCaption)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-8'))
					->class('form-control')
					->name('destinationName')
					->controller($window_Admin_Form_Controller);
					
				$oAdmin_Form_Entity_Autocomplete_Select = Admin_Form_Entity::factory('Select')
					->name('inputMode')
					->id('inputMode')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->options(array(
						0 => Core::_('Admin_Form.autocomplete_mode0'),
						1 => Core::_('Admin_Form.autocomplete_mode1'),
						2 => Core::_('Admin_Form.autocomplete_mode2'),
						3 => Core::_('Admin_Form.autocomplete_mode3')
					))
					->caption(Core::_('Admin_Form.autocomplete_mode'));					

				$oInputHidden = Admin_Form_Entity::factory('Input')
					->divAttr(array('class' => 'form-group col-xs-12 hidden'))
					->name('destinationId')
					->type('hidden')
					->controller($window_Admin_Form_Controller);

				$entity_id = 0;

				if (Core_Array::getGet('shop_id'))
				{
					$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
					$entity_id = $oShop->id;
					$path = '/admin/shop/item/index.php?autocomplete=1&show_shortcut_groups=1';
				}
				elseif(Core_Array::getGet('informationsystem_id'))
				{
					$oInformationsystem = Core_Entity::factory('Informationsystem', Core_Array::getGet('informationsystem_id', 0));
					$entity_id = $oInformationsystem->id;
					$path = '/admin/informationsystem/item/index.php?autocomplete=1&show_shortcut_groups=1';
				}

				if ($entity_id)
				{
					$oCore_Html_Entity_Script = Core::factory('Core_Html_Entity_Script')
					->value("
						$('[name = destinationName]').autocomplete({
							  source: function(request, response) {

								$.ajax({
								  url: '{$path}&entity_id={$entity_id}&mode=' + $('select#inputMode').val(),
								  dataType: 'json',
								  data: {
									queryString: request.term
								  },
								  success: function( data ) {
									response( data );
								  }
								});
							  },
							  minLength: 1,
							  create: function() {
								$(this).data('ui-autocomplete')._renderItem = function( ul, item ) {
									return $('<li></li>')
										.data('item.autocomplete', item)
										.append($('<a>').text(item.label))
										.appendTo(ul);
								}

								 $(this).prev('.ui-helper-hidden-accessible').remove();
							  },
							  select: function( event, ui ) {
								$('[name = destinationId]').val(ui.item.id);
							  },
							  open: function() {
								$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
							  },
							  close: function() {
								$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
							  }
						});
					");

					$oCore_Html_Entity_Form
						->add($oAdmin_Form_Entity_Input)
						->add($oAdmin_Form_Entity_Autocomplete_Select)
						->add($oInputHidden)
						->add($oCore_Html_Entity_Script);
				}
			}

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
				// ->add($oAdmin_Form_Entity_Select)
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12')
						->add($oAdmin_Form_Entity_Button)
				);

			$oCore_Html_Entity_Div->execute();

			ob_start();

			Core::factory('Core_Html_Entity_Script')
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