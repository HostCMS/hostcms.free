<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Item_Controller_Change_Attribute
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Informationsystem_Item_Controller_Change_Attribute extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'title',
		'Informationsystem',
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
	 * @hostcms-event Informationsystem_Item_Controller_Change_Attribute.onBeforeExecute
	 * @hostcms-event Informationsystem_Item_Controller_Change_Attribute.onBeforeAddButton
	 * @hostcms-event Informationsystem_Item_Controller_Change_Attribute.onAfterExecute
	 */
	public function execute($operation = NULL)
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this, array($operation));

		if (is_null($operation))
		{
			$windowId = $this->_Admin_Form_Controller->getWindowId();

			$newWindowId = 'Change_Attribute_' . time();

			$window_Admin_Form_Controller = clone $this->_Admin_Form_Controller;

			$oCore_Html_Entity_Form = Core_Html_Entity::factory('Form');

			$oCore_Html_Entity_Div = Core_Html_Entity::factory('Div')
				->id($newWindowId)
				->class('tabbable')
				->add(
					$oCore_Html_Entity_Form
					->add(
						Admin_Form_Entity::factory('Code')
							->html('<ul id="changeAttributesTabModal" class="nav nav-tabs">
										<li class="active">
											<a href="#itemTab" data-toggle="tab" aria-expanded="true">' . Core::_('Informationsystem_Item.attribute_item_tab') . '</a>
										</li>
										<li class="tab-red">
											<a href="#groupTab" data-toggle="tab" aria-expanded="false">' . Core::_('Informationsystem_Item.attribute_group_tab') . '</a>
										</li>
									</ul>')
					)
					->add(
						Admin_Form_Entity::factory('Div')
							->class('tab-content')
							->add(
								$oItemTab = Admin_Form_Entity::factory('Div')
									->id('itemTab')
									->class('tabbable tab-pane active')
							)
							->add(
								$oGroupTab = Admin_Form_Entity::factory('Div')
									->id('groupTab')
									->class('tab-pane')
							)
					)
			);

			$oItemTab
				->add(
					Admin_Form_Entity::factory('Code')
						->html('<ul id="changeAttributesItemTabsModal" class="nav nav-tabs">
									<li class="active">
										<a href="#itemMainTab" data-toggle="tab" aria-expanded="true">' . Core::_('Informationsystem_Item.attribute_item_main_tab') . '</a>
									</li>
								</ul>')
				)
				->add(
					Admin_Form_Entity::factory('Div')
						->class('tab-content')
						->add(
							$oItemMainTab = Admin_Form_Entity::factory('Div')
								->id('itemMainTab')
								->class('tab-pane active')
						)
				);

			$oCore_Html_Entity_Form
				->action($this->_Admin_Form_Controller->getPath())
				->method('post');

			// $oInformationsystem = Core_Entity::factory('Informationsystem', Core_Array::getGet('informationsystem_id', 0));

			// Select на всплывающем окне должен быть найден через ID нового окна, а не id_content
			$window_Admin_Form_Controller->window($newWindowId);

			if (Core::moduleIsActive('siteuser'))
			{
				$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
				$aSiteuser_Groups = $oSiteuser_Controller_Edit->fillSiteuserGroups(CURRENT_SITE);
			}
			else
			{
				$aSiteuser_Groups = array();
			}

			$oAdmin_Form_Entity_Select_Siteuser_Groups = Admin_Form_Entity::factory('Select')
				->name('siteuser_group_id')
				->id('siteuserGroupId')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->options(array(' … ') + $aSiteuser_Groups)
				->caption(Core::_('Informationsystem_Item.siteuser_group_id'))
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Select_Active = Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('active')
				->caption(Core::_('Informationsystem_Item.active'))
				->options(
					array(
						'' => ' … ',
						0 => Core::_('Informationsystem_Item.remove'),
						1 => Core::_('Informationsystem_Item.set')
					)
				)
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Select_Indexing = Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('indexing')
				->caption(Core::_('Informationsystem_Item.indexing'))
				->options(
					array(
						'' => ' … ',
						0 => Core::_('Informationsystem_Item.remove'),
						1 => Core::_('Informationsystem_Item.set')
					)
				)
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Select_Closed = Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('closed')
				->caption(Core::_('Informationsystem_Item.closed'))
				->options(
					array(
						'' => ' … ',
						0 => Core::_('Informationsystem_Item.remove'),
						1 => Core::_('Informationsystem_Item.set')
					)
				)
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Input_Datetime = Admin_Form_Entity::factory('Datetime')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('datetime')
				->caption(Core::_('Informationsystem_Item.datetime'))
				->controller($window_Admin_Form_Controller);

			$oItemMainTab
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Select_Active)
						->add($oAdmin_Form_Entity_Select_Indexing)
						->add($oAdmin_Form_Entity_Select_Closed)
				)
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Input_Datetime)
						->add($oAdmin_Form_Entity_Select_Siteuser_Groups)
				);

			if (Core::moduleIsActive('tag'))
			{
				$oAdmin_Form_Entity_Select_Tags = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Informationsystem_Item.tags'))
					->options(array())
					->name('tags[]')
					->class('informationsystem-item-tags')
					->style('width: 100%')
					->multiple('multiple')
					->divAttr(array('class' => 'form-group col-xs-12'));

				$tagsHtml = '
					<script>
						$(function(){
							$("#' . $newWindowId . ' .informationsystem-item-tags").select2({
								language: "' . Core_I18n::instance()->getLng() . '",
								minimumInputLength: 1,
								placeholder: "' . Core::_('Informationsystem_Item.type_tag') . '",
								tags: true,
								allowClear: true,
								multiple: true,
								ajax: {
									url: hostcmsBackend + "/tag/index.php?hostcms[action]=loadTagsList&hostcms[checked][0][0]=1",
									dataType: "json",
									type: "GET",
									processResults: function (data) {
										var aResults = [];
										$.each(data, function (index, item) {
											aResults.push({
												"id": item.id,
												"text": item.text
											});
										});
										return {
											results: aResults
										};
									}
								},
							});
						})</script>
					';

				$oItemMainTab
					->add(
						Admin_Form_Entity::factory('Div')
							->class('row')
							->add($oAdmin_Form_Entity_Select_Tags)
							->add(Admin_Form_Entity::factory('Code')->html($tagsHtml))
					);
			}

			$oAdmin_Form_Entity_Shortcut_Checkbox = Admin_Form_Entity::factory('Checkbox')
				->name("apply_shortcut_parent_item")
				->class('form-control')
				->caption(Core::_('Informationsystem_Item.apply_shortcut_parent_item'))
				->divAttr(array('class' => 'form-group col-xs-12'));

			$oItemMainTab
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Shortcut_Checkbox)
				);

			// Идентификаторы переносимых указываем скрытыми полями в форме, чтобы не превысить лимит GET
			$aChecked = $this->_Admin_Form_Controller->getChecked();

			// Clear checked list
			$this->_Admin_Form_Controller->clearChecked();

			foreach ($aChecked as $datasetKey => $checkedItems)
			{
				$oAdmin_Form_Dataset_Entity = $this->_Admin_Form_Controller->getDataset($datasetKey);

				if ($oAdmin_Form_Dataset_Entity /*&& get_class($oAdmin_Form_Dataset_Entity->getEntity()) == 'Informationsystem_Item_Model'*/)
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

			// Группы
			$oAdmin_Form_Entity_Select_Active_Group = Admin_Form_Entity::factory('Select')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->name('group_active')
					->caption(Core::_('Informationsystem_Group.active'))
					->options(
						array(
							'' => ' … ',
							0 => Core::_('Informationsystem_Item.remove'),
							1 => Core::_('Informationsystem_Item.set')
						)
					)
					->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Select_Indexing_Group = Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->name('group_indexing')
				->caption(Core::_('Informationsystem_Group.indexing'))
				->options(
					array(
						'' => ' … ',
						0 => Core::_('Informationsystem_Item.remove'),
						1 => Core::_('Informationsystem_Item.set')
					)
				)
				->controller($window_Admin_Form_Controller);

			$oAdmin_Form_Entity_Select_Group_Siteuser_Groups = Admin_Form_Entity::factory('Select')
				->name('group_siteuser_group_id')
				->id('siteuserGroupId')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
				->options(array(' … ') + $aSiteuser_Groups)
				->caption(Core::_('Informationsystem_Item.siteuser_group_id'))
				->controller($window_Admin_Form_Controller);

			$oGroupTab
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Select_Active_Group)
						->add($oAdmin_Form_Entity_Select_Indexing_Group)
						->add($oAdmin_Form_Entity_Select_Group_Siteuser_Groups)
				);

			$oAdmin_Form_Entity_Shortcut_Group_Checkbox = Admin_Form_Entity::factory('Checkbox')
				->name("apply_shortcut_parent_group")
				->class('form-control')
				->caption(Core::_('Informationsystem_Group.apply_shortcut_parent_group'))
				->divAttr(array('class' => 'form-group col-xs-12'));

			$oGroupTab
				->add(
					Admin_Form_Entity::factory('Div')
						->class('row')
						->add($oAdmin_Form_Entity_Shortcut_Group_Checkbox)
				);

			Core_Event::notify(get_class($this) . '.onBeforeAddButton', $this, array($oCore_Html_Entity_Form, $oCore_Html_Entity_Div));

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
					$('#{$newWindowId}').HostCMSWindow({ autoOpen: true, destroyOnClose: false, title: '" . Core_Str::escapeJavascriptVariable($this->title) . "', AppendTo: '#{$windowId}', width: 800, height: 480, addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
				->execute();

			$this->addMessage(ob_get_clean());

			// Break execution for other
			return TRUE;
		}
		else
		{
			switch (get_class($this->_object))
			{
				case 'Informationsystem_Item_Model':
					$this->_applyItem($this->_object);
				break;
				case 'Informationsystem_Group_Model':
					$this->_applyGroup($this->_object);
				break;
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this, array($operation));

		return $this;
	}

	/**
	 * Apply attrubites for items in group
	 * @param Informationsystem_Group_Model $oInformationsystem_Group
	 * @return self
	 * @hostcms-event Informationsystem_Item_Controller_Change_Attribute.onBeforeApplyGroup
	 * @hostcms-event Informationsystem_Item_Controller_Change_Attribute.onAfterApplyGroup
	 */
	protected function _applyGroup(Informationsystem_Group_Model $oInformationsystem_Group)
	{
		Core_Event::notify(get_class($this) . '.onBeforeApplyGroup', $this, array($oInformationsystem_Group));

		$this->_applyGroupAttributes($oInformationsystem_Group);

		$aInformationsystem_Items = $oInformationsystem_Group->Informationsystem_Items->findAll(FALSE);
		foreach ($aInformationsystem_Items as $oInformationsystem_Item)
		{
			$this->_applyItem($oInformationsystem_Item);
		}

		$aInformationsystem_Groups = $oInformationsystem_Group->Informationsystem_Groups->findAll(FALSE);
		foreach ($aInformationsystem_Groups as $oTmp_Informationsystem_Group)
		{
			$this->_applyGroup($oTmp_Informationsystem_Group);
		}

		Core_Event::notify(get_class($this) . '.onAfterApplyGroup', $this, array($oInformationsystem_Group));

		return $this;
	}

	/**
	 * Apply attrubites for group
	 * @param Informationsystem_Group_Model $oInformationsystem_Group
	 * @return self
	 */
	protected function _applyGroupAttributes(Informationsystem_Group_Model $oInformationsystem_Group)
	{
		Core_Array::getPost('group_active') !== '' && $oInformationsystem_Group->active = Core_Array::getPost('group_active', 0, 'int');
		$oInformationsystem_Group->save();

		if (!$oInformationsystem_Group->shortcut_id)
		{
			Core_Array::getPost('group_indexing') !== '' && $oInformationsystem_Group->indexing = Core_Array::getPost('group_indexing', 0, 'int');
			Core_Array::getPost('group_siteuser_group_id') && $oInformationsystem_Group->siteuser_group_id = Core_Array::getPost('group_siteuser_group_id', 0, 'int');

			$oInformationsystem_Group->save();
		}
		// Ярлык и применять к основным группам ярлыков
		elseif (!is_null(Core_Array::getPost('apply_shortcut_parent_group')))
		{
			$this->_applyGroupAttributes($oInformationsystem_Group->Shortcut);
		}

		$oInformationsystem_Group->clearCache();

		return $this;
	}

	/**
	 * Apply attrubites for item
	 * @param Informationsystem_Item_Model $oInformationsystem_Item
	 * @return self
	 * @hostcms-event Informationsystem_Item_Controller_Change_Attribute.onBeforeApplyItem
	 * @hostcms-event Informationsystem_Item_Controller_Change_Attribute.onAfterApplyItem
	 */
	protected function _applyItem(Informationsystem_Item_Model $oInformationsystem_Item)
	{
		Core_Event::notify(get_class($this) . '.onBeforeApplyItem', $this, array($oInformationsystem_Item));

		Core_Array::getPost('active') !== '' && $oInformationsystem_Item->active = Core_Array::getPost('active', 0, 'int');
		$oInformationsystem_Item->save();

		if (!$oInformationsystem_Item->shortcut_id)
		{
			Core_Array::getPost('siteuser_group_id') && $oInformationsystem_Item->siteuser_group_id = Core_Array::getPost('siteuser_group_id', 0, 'int');
			Core_Array::getPost('indexing') !== '' && $oInformationsystem_Item->indexing = Core_Array::getPost('indexing', 0, 'int');
			Core_Array::getPost('closed') !== '' && $oInformationsystem_Item->closed = Core_Array::getPost('closed', 0, 'int');
			Core_Array::getPost('datetime') !== '' && $oInformationsystem_Item->datetime = strval(Core_Date::datetime2sql(Core_Array::getPost('datetime')));

			$oInformationsystem_Item->save();

			if (Core::moduleIsActive('tag'))
			{
				$aRecievedTags = Core_Array::getPost('tags', array());
				!is_array($aRecievedTags) && $aRecievedTags = array();

				$aTmp = array();

				$aTags = $oInformationsystem_Item->Tags->findAll(FALSE);
				foreach ($aTags as $oTag)
				{
					$aTmp[] = $oTag->name;
				}

				foreach ($aRecievedTags as $tag_name)
				{
					$tag_name = trim($tag_name);

					if ($tag_name != '' && !in_array($tag_name, $aTmp))
					{
						$oTag = Core_Entity::factory('Tag')->getByName($tag_name, FALSE);

						if (is_null($oTag))
						{
							$oTag = Core_Entity::factory('Tag');
							$oTag->name = $oTag->path = $tag_name;
							$oTag->save();
						}

						$oInformationsystem_Item->add($oTag);
					}
				}
			}
		}

		// Ярлык и применять к основным товарам ярлыков
		elseif (!is_null(Core_Array::getPost('apply_shortcut_parent_item')))
		{
			$this->_applyItem($oInformationsystem_Item->Informationsystem_Item);
		}

		$oInformationsystem_Item->clearCache();

		Core_Event::notify(get_class($this) . '.onAfterApplyItem', $this, array($oInformationsystem_Item));

		return $this;
	}
}