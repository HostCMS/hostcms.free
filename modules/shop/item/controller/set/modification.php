<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Item_Controller_Set_Modification extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'title',
		'Shop',
		'buttonName',
		'skipColumns'
	);

	/**
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);

		$this->buttonName(Core::_('Admin_Form.apply'));
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 * @hostcms-event Shop_Item_Controller_Set_Modification.onBeforeExecute
	 * @hostcms-event Shop_Item_Controller_Set_Modification.onBeforeAddButton
	 * @hostcms-event Shop_Item_Controller_Set_Modification.onAfterExecute
	 */
	public function execute($operation = NULL)
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this, array($operation));

		if (is_null($operation))
		{
			// Original windowId
			$windowId = $this->_Admin_Form_Controller->getWindowId();

			$newWindowId = 'Set_Modification_' . time();

			$oCore_Html_Entity_Form = Core_Html_Entity::factory('Form');

			$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')
				->id($newWindowId)
				->add($oCore_Html_Entity_Form);

			$oCore_Html_Entity_Form->action($this->_Admin_Form_Controller->getPath())
				->method('post');

			$window_Admin_Form_Controller = clone $this->_Admin_Form_Controller;
			// Select на всплывающем окне должен быть найден через ID нового окна, а не id_content
			$window_Admin_Form_Controller->window($newWindowId);

			// Идентификаторы переносимых указываем скрытыми полями в форме, чтобы не превысить лимит GET
			$aChecked = $this->_Admin_Form_Controller->getChecked();
			// Clear checked list
			$this->_Admin_Form_Controller->clearChecked();

			$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));

			$oModificationInput = Admin_Form_Entity::factory('Input')
				->caption(Core::_('Shop_Item.shop_item_catalog_modification_flag'))
				->divAttr(array('class' => 'form-group col-xs-12'))
				->name('modification_name');

			$oModificationInputHidden = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12 hidden'))
				->name('shop_item_id')
				// ->value($this->_object->modification_id)
				->type('hidden');

			$oCore_Html_Entity_Script_Modification = Core_Html_Entity::factory('Script')
				->value("
					$('#{$newWindowId} [name = modification_name]').autocomplete({
						source: function(request, response) {
							$.ajax({
								url: '/admin/shop/item/index.php?items=1&shop_id={$oShop->id}',
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
									.append($('<div class=\"name\">').text(item.label))
									.appendTo(ul);
							}

							$(this).prev('.ui-helper-hidden-accessible').remove();
						},
						select: function(event, ui) {
							$('#{$newWindowId} [name = shop_item_id]').val(ui.item.id);
						},
						change: function(event, ui) {
							if (ui.item === null)
							{
								$('#{$newWindowId} [name = shop_item_id]').val(0);
							}
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
				->add($oModificationInput)
				->add($oModificationInputHidden)
				->add($oCore_Html_Entity_Script_Modification);

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
			$shop_item_id = Core_Array::getPost('shop_item_id', 0, 'int');

			if (is_null($shop_item_id))
			{
				throw new Core_Exception("shop_item_id is NULL");
			}

			if ($shop_item_id)
			{
				$this->_object->modification_id = $shop_item_id;
				$this->_object->save();
			}
		}

		return $this;
	}
}