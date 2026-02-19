<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 * Типовой контроллер переноса сущности в списке сущностей
 *
 * @package HostCMS
 * @subpackage Admin
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Admin_Form_Action_Controller_Type_Move extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'autocomplete',
		'autocompletePath',
		'autocompleteEntityId',
		'value',
		'title', // Form Title
		'selectCaption', // Select caption, e.g. 'Choose a group'
		'selectOptions', // Array of options
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

			$newWindowId = 'Move_' . time();

			$oCore_Html_Entity_Form = Core_Html_Entity::factory('Form');

			$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')
				->id($newWindowId)
				->add($oCore_Html_Entity_Form);

			$oCore_Html_Entity_Form->action($this->_Admin_Form_Controller->getPath())
				->method('post');

			$window_Admin_Form_Controller = clone $this->_Admin_Form_Controller;
			// Select на всплывающем окне должен быть найден через ID нового окна, а не id_content
			$window_Admin_Form_Controller->window($newWindowId);

			// $className = get_class($window_Admin_Form_Controller->getDataset(0)->getEntity());
			// die();

			if (!$this->autocomplete)
			{
				$oAdmin_Form_Entity_Select = Admin_Form_Entity::factory('Select')
					->name('destinationId')
					->id('destinationId')
					//->style('width: 280px; float: left')
					->filter(TRUE)
					->options($this->selectOptions)
					->caption($this->selectCaption)
					->value($this->value)
					->controller($window_Admin_Form_Controller);

				$oCore_Html_Entity_Form->add($oAdmin_Form_Entity_Select);
			}
			else
			{
				$aExclude = array();

				$aChecked = $window_Admin_Form_Controller->getChecked();

				foreach ($aChecked as $datasetKey => $checkedItems)
				{
					// Exclude just dirs
					if ($datasetKey == 0)
					{
						foreach ($checkedItems as $key => $value)
						{
							$aExclude[] = $key;
						}
					}
				}

				$exclude = count($aExclude)
					? json_encode($aExclude)
					: '';

				$oAdmin_Form_Entity_Input = Admin_Form_Entity::factory('Input')
					->caption($this->selectCaption)
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-8'))
					->class('form-control')
					->name('destinationName')
					->placeholder(Core::_('Admin.autocomplete_placeholder'))
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

				if ($this->autocompleteEntityId)
				{
					$oCore_Html_Entity_Script = Core_Html_Entity::factory('Script')
					->value("
						$('[name = destinationName]').autocomplete({
							source: function(request, response) {
								$.ajax({
									url: '{$this->autocompletePath}&entity_id={$this->autocompleteEntityId}&exclude={$exclude}&mode=' + $('select#inputMode').val(),
									dataType: 'json',
									data: {
										queryString: request.term
									},
									success: function(data) {
										response(data);
									}
								});
							},
							minLength: 1,
							create: function() {
								$(this).data('ui-autocomplete')._renderItem = function(ul, item) {
									return $('<li class=\"autocomplete-suggestion\"></li>')
										.data('item.autocomplete', item)
										.append($('<div class=\"name\">').html($.escapeHtml(item.label)))
										.append($('<div class=\"id\">').html('[' + $.escapeHtml(item.id) + ']'))
										.appendTo(ul);
								}

								$(this).prev('.ui-helper-hidden-accessible').remove();
							},
							select: function(event, ui) {
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
						Core_Html_Entity::factory('Input')
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

			Core_Html_Entity::factory('Script')
				->value("$(function() {
				$('#{$newWindowId}').HostCMSWindow({ autoOpen: true, destroyOnClose: false, title: '" . Core_Str::escapeJavascriptVariable($this->title) . "', AppendTo: '#{$windowId}', width: 750, height: 140, addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
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

			$this->_object->move(intval($destinationId));
		}

		return $this;
	}
}