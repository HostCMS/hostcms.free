<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Informationsystem_Item and Informationsystem_Group Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Informationsystem
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Informationsystem_Item_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$modelName = $object->getModelName();

		$informationsystem_id = Core_Array::getGet('informationsystem_id');
		$informationsystem_group_id = Core_Array::getGet('informationsystem_group_id');

		switch ($modelName)
		{
			case 'informationsystem_item':
				$this
					->addSkipColumn('shortcut_id')
					->addSkipColumn('image_large')
					->addSkipColumn('image_small')
					->addSkipColumn('image_large_width')
					->addSkipColumn('image_large_height')
					->addSkipColumn('image_small_width')
					->addSkipColumn('image_small_height');

				if ($object->shortcut_id != 0)
				{
					$object = $object->Informationsystem_Item;
				}

				if (!$object->id)
				{
					$object->informationsystem_id = $informationsystem_id;
					$object->informationsystem_group_id = $informationsystem_group_id;
				}
			break;
			case 'informationsystem_group':
				$this
					->addSkipColumn('shortcut_id')
					->addSkipColumn('image_large')
					->addSkipColumn('image_small')
					->addSkipColumn('subgroups_count')
					->addSkipColumn('subgroups_total_count')
					->addSkipColumn('items_count')
					->addSkipColumn('items_total_count')
					->addSkipColumn('sns_type_id');

				if ($object->shortcut_id != 0)
				{
					$object = $object->Shortcut;
				}

				// Значения директории для добавляемого объекта
				if (!$object->id)
				{
					$object->informationsystem_id = $informationsystem_id;
					$object->parent_id = $informationsystem_group_id;
				}
			break;
		}

		return parent::setObject($object);
	}

	/**
	 * Prepare backend item's edit form
	 *
	 * @return self
	 */
	protected function _prepareForm()
	{
		parent::_prepareForm();

		$object = $this->_object;

		$modelName = $object->getModelName();

		$oInformationsystem = is_null($object->id)
			? Core_Entity::factory('Informationsystem', $object->informationsystem_id)
			: $object->Informationsystem;

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		switch ($modelName)
		{
			case 'informationsystem_item':

				if ($object->shortcut_id != 0)
				{
					$object = $object->Informationsystem_Item;
				}

				$title = $object->id
					? Core::_('Informationsystem_Item.information_items_edit_form_title', $object->name)
					: Core::_('Informationsystem_Item.information_items_add_form_title');

				$template_id = $this->_object->Informationsystem->Structure->template_id
					? $this->_object->Informationsystem->Structure->template_id
					: 0;

				$oPropertyTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Admin_Form.tabProperties'))
					->name('Property');

				$this->addTabBefore($oPropertyTab, $oAdditionalTab);

				// Properties
				Property_Controller_Tab::factory($this->_Admin_Form_Controller)
					->setObject($this->_object)
					->setDatasetId($this->getDatasetId())
					->linkedObject(Core_Entity::factory('Informationsystem_Item_Property_List', $oInformationsystem->id))
					->setTab($oPropertyTab)
					->template_id($template_id)
					->fillTab();

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow9 = Admin_Form_Entity::factory('Div')->class('row'));

				$oAdditionalTab->delete($this->getField('informationsystem_group_id'));

				$oMainTab->delete($this->getField('name'));

				$oName = Admin_Form_Entity::factory('Input')
					->name('name')
					->value($this->_object->name)
					->caption(Core::_('Informationsystem_Item.name'))
					->class('form-control input-lg');

				$oMainRow1->add($oName);

				// Добавляем группу товаров
				$aResult = $this->informationsystemGroupShow('informationsystem_group_id');
				foreach ($aResult as $resultItem)
				{
					$oMainRow2->add($resultItem);
				}

				// Группы ярлыков
				$oAdditionalGroupsSelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Informationsystem_Item.shortcut_group_tags'))
					->options($this->_fillShortcutGroupList($this->_object))
					->name('shortcut_group_id[]')
					->class('shortcut-group-tags')
					->style('width: 100%')
					->multiple('multiple')
					->divAttr(array('class' => 'form-group col-xs-12'));

				$this->addField($oAdditionalGroupsSelect);

				$oMainRow3->add($oAdditionalGroupsSelect);

				$html2 = '
					<script>
						$(function(){
							$(".shortcut-group-tags").select2({
								language: "' . Core_i18n::instance()->getLng() . '",
								minimumInputLength: 1,
								placeholder: "' . Core::_('Informationsystem_Item.select_group') . '",
								tags: true,
								allowClear: true,
								multiple: true,
								ajax: {
									url: "/admin/informationsystem/item/index.php?shortcuts&informationsystem_id=' . $this->_object->informationsystem_id .'",
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

				$oMainRow3->add(Admin_Form_Entity::factory('Code')->html($html2));

				$this->getField('datetime')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));
				$this->getField('start_datetime')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));
				$this->getField('end_datetime')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$this->_object->start_datetime == '0000-00-00 00:00:00'
					&& $this->getField('start_datetime')->value('');

				$this->_object->end_datetime == '0000-00-00 00:00:00'
					&& $this->getField('end_datetime')->value('');

				$oMainTab
					->move($this->getField('datetime'), $oMainRow4)
					->move($this->getField('start_datetime'), $oMainRow4)
					->move($this->getField('end_datetime'), $oMainRow4);

				$this->getField('active')
					->divAttr(array('class' => 'form-group col-xs-6 col-sm-4'));
				$this->getField('indexing')
					->divAttr(array('class' => 'form-group col-xs-6 col-sm-4'));

				$oMainTab->move($this->getField('active'), $oMainRow5);
				$oMainTab->move($this->getField('indexing'), $oMainRow5);

				$this->getField('sorting')
					->divAttr(array('class' => 'form-group col-xs-6 col-sm-3'));
				$this->getField('ip')
					->divAttr(array('class' => 'form-group col-xs-6 col-sm-3'));
				$this->getField('showed')
					->divAttr(array('class' => 'form-group col-xs-6 col-sm-3'));

				$oMainTab
					->move($this->getField('sorting'), $oMainRow6)
					->move($this->getField('ip'), $oMainRow6)
					->move($this->getField('showed'), $oMainRow6);

				$oAdditionalTab->delete($this->getField('siteuser_id'));

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser = $this->_object->Siteuser;

					$options = !is_null($oSiteuser->id)
						? array($oSiteuser->id => $oSiteuser->login . ' [' . $oSiteuser->id . ']')
						: array(0);

					$oSiteuserSelect = Admin_Form_Entity::factory('Select')
						->caption(Core::_('Informationsystem_Group.siteuser_id'))
						->id('object_siteuser_id')
						->options($options)
						->name('siteuser_id')
						->class('siteuser-tag')
						->style('width: 100%')
						->divAttr(array('class' => 'form-group col-xs-12'));

					$oMainRow6
						->add(
							Admin_Form_Entity::factory('Div')
								->class('form-group col-xs-12 col-sm-3 no-padding')
								->add($oSiteuserSelect)
						);

					// Show button
					Siteuser_Controller_Edit::addSiteuserSelect2($oSiteuserSelect, $oSiteuser, $this->_Admin_Form_Controller);
				}

				// Добавляем новое поле типа файл
				$oImageField = Admin_Form_Entity::factory('File');

				$oLargeFilePath = is_file($this->_object->getLargeFilePath())
					? $this->_object->getLargeFileHref()
					: '';

				$oSmallFilePath = is_file($this->_object->getSmallFilePath())
					? $this->_object->getSmallFileHref()
					: '';

				$sFormPath = $this->_Admin_Form_Controller->getPath();
				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$oImageField
					//->caption(Core::_('Informationsystem_Group.image_large'))
					->name("image")
					->id("image")
					->largeImage(array(
							// image_big_max_width - значение максимальной ширины большого изображения;
							'max_width' => $oInformationsystem->image_large_max_width,

							// image_big_max_height - значение максимальной высоты большого изображения;
							'max_height' => $oInformationsystem->image_large_max_height,

							// big_image_path - адрес большого загруженного изображения
							'path' => $oLargeFilePath,

							// show_big_image_params - параметр, определяющий отображать ли настройки большого изображения
							'show_params' => TRUE,

							// watermark_position_x - значение поля ввода с подписью "По оси X"
							'watermark_position_x' => $oInformationsystem->watermark_default_position_x,

							// watermark_position_y - значение поля ввода с подписью "По оси Y"
							'watermark_position_y' => $oInformationsystem->watermark_default_position_y,

							// large_image_watermark_checked - вид ображения checkbox'а с подписью "Наложить водяной знак на большое изображение" (1 - отображать выбранным (по умолчанию), 0 - невыбранным);
							'place_watermark_checkbox_checked' => $oInformationsystem->watermark_default_use_large_image,

							// onclick_delete_big_image - значение onclick для удаления большой картинки
							'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteLargeImage', windowId: '{$windowId}'}); return false",

							'caption' => Core::_('Informationsystem_Item.image_large'),

							// used_big_image_preserve_aspect_ratio_checked - вид ображения checkbox'а с подписью "Сохранять пропорции изображения" (1 - отображать выбранным (по умолчанию), 0 - невыбранным);
							'preserve_aspect_ratio_checkbox_checked' => $oInformationsystem->preserve_aspect_ratio
						)
					)
					->smallImage(array(			// image_small_max_width - значение максимальной ширины малого изображения;
							'max_width' => $oInformationsystem->image_small_max_width,

							// image_small_max_height - значение максимальной высоты малого изображения;
							'max_height' => $oInformationsystem->image_small_max_height,

							// small_image_path - адрес малого загруженного изображения
							'path' => $oSmallFilePath,

							// make_small_image_from_big_checked - вид ображения checkbox'а с подписью "Создать малое изображение из большого" выбранным (1 - отображать выбранным (по умолчанию), 0 - невыбранным);
							'create_small_image_from_large_checked' => $oInformationsystem->create_small_image && $this->_object->image_small == '',

							// small_image_watermark_checked - вид ображения checkbox'а с подписью "Наложить водяной знак на малое изображение" (1 - отображать выбранным (по умолчанию), 0 - невыбранным);
							'place_watermark_checkbox_checked' => $oInformationsystem->watermark_default_use_small_image,

							// onclick_delete_small_image - значение onclick для удаления малой картинки
							'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteSmallImage', windowId: '{$windowId}'}); return false",

							// load_small_image_caption - заголовок поля загрузки малого изображения
							'caption' => Core::_('Informationsystem_Item.image_small'),

							'show_params' => TRUE,

							'preserve_aspect_ratio_checkbox_checked' => $oInformationsystem->preserve_aspect_ratio_small
						)
					)
					->crop(TRUE);

				$oMainRow7->add($oImageField);

				$oSiteAlias = $oInformationsystem->Site->getCurrentAlias();
				if ($oSiteAlias)
				{
					$sItemUrl = ($oInformationsystem->Structure->https ? 'https://' : 'http://')
						. $oSiteAlias->name
						. $oInformationsystem->Structure->getPath()
						. $this->_object->getPath();

					$this->getField('path')
						->add(
							Admin_Form_Entity::factory('A')
								->id('path')
								->target('_blank')
								->href($sItemUrl)
								->class('input-group-addon bg-blue bordered-blue')
								->value('<i class="fa fa-external-link"></i>')
						);
				}

				$this->getField('path')
					->format(array('maxlen' => array('value' => 255)));

				$oMainTab->move($this->getField('path'), $oMainRow8);

				if (Core::moduleIsActive('maillist'))
				{
					$oMaillist_Controller_Edit = new Maillist_Controller_Edit($this->_Admin_Form_Action);

					$oSelect_Maillist = Admin_Form_Entity::factory('Select');

					$oSelect_Maillist->options(array(Core::_('Informationsystem_Item.maillist_default_value'))
						+ $oMaillist_Controller_Edit->fillMaillist()
					)
					->name('maillist_id')
					->value(0)
					->caption(Core::_('Informationsystem_Item.maillist'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

					$oMainRow9->add($oSelect_Maillist);
				}

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
					$aSiteuser_Groups = $oSiteuser_Controller_Edit->fillSiteuserGroups($this->_object->Informationsystem->site_id);
				}
				else
				{
					$aSiteuser_Groups = array();
				}

				// Список групп пользователей
				$oSelect_SiteuserGroups = Admin_Form_Entity::factory('Select')
					->options(
						array(
							0 => Core::_('Informationsystem.information_all'),
							-1 => Core::_('Informationsystem_Group.information_parent')
						) + $aSiteuser_Groups
					)
					->name('siteuser_group_id')
					->value($this->_object->siteuser_group_id)
					->caption(Core::_('Informationsystem_Item.siteuser_group_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

				$oMainRow9->add($oSelect_SiteuserGroups);

				$oAdditionalTab = $this->getTab('additional');
				$oAdditionalTab->delete($this->getField('siteuser_group_id'));

				$this->getField('informationsystem_id')->divAttr(array('style' => 'display: none'));

				// Description
				$oInformationsystemTabDescription = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem_Item.tab_1'))
					->name('Description');
				$this->addTabAfter($oInformationsystemTabDescription, $oMainTab);

				$oInformationsystemTabDescription
					->add($oDescriptionRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oDescriptionRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oDescriptionRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oDescriptionRow4 = Admin_Form_Entity::factory('Div')->class('row'));

				$this->getField('description')
					->rows(8)
					->wysiwyg(Core::moduleIsActive('wysiwyg'))
					->template_id($template_id);

				$oMainTab->move($this->getField('description'), $oDescriptionRow1);

				if (Core::moduleIsActive('typograph'))
				{
					$this->getField('description')->value(
						Typograph_Controller::instance()->eraseOpticalAlignment($this->getField('description')->value)
					);

					$oUseTypograph = Admin_Form_Entity::factory('Checkbox');
					$oUseTypograph
						->name("use_typograph_description")
						->caption(Core::_('Informationsystem_Item.exec_typograph_description'))
						->value(1)
						->checked($oInformationsystem->typograph_default_items == 1)
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-5 col-lg-4'));

					$oUseTrailingPunctuation = Admin_Form_Entity::factory('Checkbox');
					$oUseTrailingPunctuation
						->name("use_trailing_punctuation_description")
						->caption(Core::_('Informationsystem_Item.use_trailing_punctuation'))
						->value(1)
						->checked($oInformationsystem->typograph_default_items == 1)
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-5 col-lg-4'));

					$oDescriptionRow2
						->add($oUseTypograph)
						->add($oUseTrailingPunctuation);
				}

				// Text
				$this->getField('text')
					->rows(15)
					->wysiwyg(Core::moduleIsActive('wysiwyg'))
					->template_id($template_id);

				$oMainTab->move($this->getField('text'), $oDescriptionRow3);

				//$oMainTab->move($this->getField('text'), $oInformationsystemTabDescription);
				if (Core::moduleIsActive('typograph'))
				{
					$this->getField('text')->value(
						Typograph_Controller::instance()->eraseOpticalAlignment($this->getField('text')->value)
					);

					$oUseTypograph = Admin_Form_Entity::factory('Checkbox');
					$oUseTypograph
						->name("use_typograph_text")
						->caption(Core::_('Informationsystem_Item.exec_typograph_for_text'))
						->value(1)
						->checked($oInformationsystem->typograph_default_items == 1)
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-5 col-lg-4'));

					$oUseTrailingPunctuation = Admin_Form_Entity::factory('Checkbox');
					$oUseTrailingPunctuation
						->name("use_trailing_punctuation_text")
						->caption(Core::_('Informationsystem_Item.use_trailing_punctuation_for_text'))
						->value(1)
						->checked($oInformationsystem->typograph_default_items == 1)
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-5 col-lg-4'));

					$oDescriptionRow4
						->add($oUseTypograph)
						->add($oUseTrailingPunctuation);
				}

				// Export
				$oInformationsystemItemTabExportImport = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem_Item.tab_export'))
					->name('ExportImport');

				$this->addTabAfter($oInformationsystemItemTabExportImport, $oInformationsystemTabDescription);

				$oInformationsystemItemTabExportImport
					->add($oExportRow1 = Admin_Form_Entity::factory('Div')->class('row'));

				$oMainTab
					->move($this->getField('guid'), $oExportRow1);

				// SEO
				$oInformationsystemTabSeo = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem_Item.tab_2'))
					->name('Seo');

				$this->addTabAfter($oInformationsystemTabSeo, $oInformationsystemTabDescription);

				$oInformationsystemTabSeo
					->add($oSeoRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oSeoRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oSeoRow3 = Admin_Form_Entity::factory('Div')->class('row'));

				$oMainTab
					->move($this->getField('seo_title'), $oSeoRow1)
					->move($this->getField('seo_description'), $oSeoRow2)
					->move($this->getField('seo_keywords'), $oSeoRow3);

				if (Core::moduleIsActive('tag'))
				{
					/*$oTagsTab = Admin_Form_Entity::factory('Tab')
						->caption(Core::_('Informationsystem_Item.tab_3'))
						->name('Tags');
					$this->addTabAfter($oTagsTab, $oInformationsystemTabSeo);

					$oTagsTab
						->add($oTagRow1 = Admin_Form_Entity::factory('Div')->class('row'));*/

					$oAdditionalGroupsSelect = Admin_Form_Entity::factory('Select')
						->caption(Core::_('Informationsystem_Item.tags'))
						->options($this->_fillTagsList($this->_object))
						->name('tags[]')
						->class('informationsystem-item-tags')
						->style('width: 100%')
						->multiple('multiple')
						->divAttr(array('class' => 'form-group col-xs-12'));

					$oMainRow5->add($oAdditionalGroupsSelect);

					$html = '
						<script>
							$(function(){
								$(".informationsystem-item-tags").select2({
									language: "' . Core_i18n::instance()->getLng() . '",
									minimumInputLength: 2,
									placeholder: "' . Core::_('Informationsystem_Item.type_tag') . '",
									tags: true,
									allowClear: true,
									multiple: true,
									ajax: {
										url: "/admin/tag/index.php?hostcms[action]=loadTagsList&hostcms[checked][0][0]=1",
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

					$oMainRow5->add(Admin_Form_Entity::factory('Code')->html($html));
				}

			break;
			case 'informationsystem_group':
			default:
				if ($object->shortcut_id != 0)
				{
					$object = $object->Shortcut;
				}

				$template_id = $this->_object->Informationsystem->Structure->template_id
					? $this->_object->Informationsystem->Structure->template_id
					: 0;

				$oPropertyTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Admin_Form.tabProperties'))
					->name('Property');

				$this->addTabBefore($oPropertyTab, $oAdditionalTab);

				// Properties
				Property_Controller_Tab::factory($this->_Admin_Form_Controller)
					->setObject($this->_object)
					->setDatasetId($this->getDatasetId())
					->linkedObject(Core_Entity::factory('Informationsystem_Group_Property_List', $oInformationsystem->id))
					->setTab($oPropertyTab)
					->template_id($template_id)
					->fillTab();

				$title = $object->id
					? Core::_('Informationsystem_Group.information_groups_edit_form_title', $object->name)
					: Core::_('Informationsystem_Group.information_groups_add_form_title');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					//->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'));

				// Добавляем новые вкладки
				$this->addTabAfter($oInformationsystemGroupDescriptionTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem_Group.tab_1'))
					->name('Description'), $oMainTab);

				// Добавляем новые вкладки
				$this->addTabAfter($oInformationsystemGroupExportTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem_Group.tab_export'))
					->name('Export'), $oInformationsystemGroupDescriptionTab);

				$this->addTabAfter($oInformationsystemTabSeo = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem_Group.tab_2'))
					->name('Seo'), $oInformationsystemGroupDescriptionTab);

				$oInformationsystemGroupDescriptionTab
					->add($oInformationsystemGroupDescriptionTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemGroupDescriptionTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oInformationsystemGroupExportTab
					->add($oInformationsystemGroupExportTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				// Name
				$oMainTab
					->move($this->getField('guid'), $oInformationsystemGroupExportTabRow1);

				$oInformationsystemTabSeo
					->add($oSeoRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oSeoRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oSeoRow3 = Admin_Form_Entity::factory('Div')->class('row'));

				$this->addTabAfter($oInformationsystemTabSeoTemplates = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Informationsystem_Group.tab_seo_templates'))
					->name('Seo_Templates'), $oInformationsystemTabSeo);

				$oInformationsystemTabSeoTemplates
					->add($oInformationsystemGroupBlock = Admin_Form_Entity::factory('Div')->class('well with-header'))
					->add($oInformationsystemItemBlock = Admin_Form_Entity::factory('Div')->class('well with-header'));

				$oInformationsystemGroupBlock
					->add($oInformationsystemGroupHeaderDiv = Admin_Form_Entity::factory('Div')
						->class('header bordered-darkorange')
						->value(Core::_("Informationsystem_Group.seo_group_header"))
					)
					->add($oInformationsystemGroupBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemGroupBlockRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemGroupBlockRow3 = Admin_Form_Entity::factory('Div')->class('row'));

				$oInformationsystemGroupHeaderDiv
					->add(Admin_Form_Entity::factory('Code')->html(
						Informationsystem_Controller::showGroupButton()
					));

				$oInformationsystemItemBlock
					->add($oInformationsystemItemHeaderDiv = Admin_Form_Entity::factory('Div')
						->class('header bordered-palegreen')
						->value(Core::_("Informationsystem_Group.seo_item_header"))
					)
					->add($oInformationsystemItemBlockRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemItemBlockRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oInformationsystemItemBlockRow3 = Admin_Form_Entity::factory('Div')->class('row'));

				$oInformationsystemItemHeaderDiv
					->add(Admin_Form_Entity::factory('Code')->html(
						Informationsystem_Controller::showItemButton()
					));

				// Seo templates
				$oMainTab
					->move($this->getField('seo_group_title_template')->divAttr(array('class' => 'form-group col-xs-12')), $oInformationsystemGroupBlockRow1)
					->move($this->getField('seo_group_description_template')->divAttr(array('class' => 'form-group col-xs-12')), $oInformationsystemGroupBlockRow2)
					->move($this->getField('seo_group_keywords_template')->divAttr(array('class' => 'form-group col-xs-12')), $oInformationsystemGroupBlockRow3)
					->move($this->getField('seo_item_title_template')->divAttr(array('class' => 'form-group col-xs-12')), $oInformationsystemItemBlockRow1)
					->move($this->getField('seo_item_description_template')->divAttr(array('class' => 'form-group col-xs-12')), $oInformationsystemItemBlockRow2)
					->move($this->getField('seo_item_keywords_template')->divAttr(array('class' => 'form-group col-xs-12')), $oInformationsystemItemBlockRow3);

				// Name
				$oMainTab
					->move($this->getField('name'), $oMainRow1);

				// parent_id
				$oAdditionalTab->delete($this->getField('parent_id'));

				// Добавляем группу товаров
				$aResult = $this->informationsystemGroupShow('parent_id');
				foreach ($aResult as $resultItem)
				{
					$oMainRow2->add($resultItem);
				}

				$oMainTab
					->move($this->getField('description'), $oInformationsystemGroupDescriptionTabRow1)
				;

				// Description
				//$oMainTab->move($this->getField('description'), $oMainRow3);

				$this->getField('description')
					->rows(15)
					->wysiwyg(Core::moduleIsActive('wysiwyg'))
					->template_id($template_id);

				if (Core::moduleIsActive('typograph'))
				{
					$this->getField('description')->value(
						Typograph_Controller::instance()->eraseOpticalAlignment($this->getField('description')->value)
					);

					$oUseTypograph = Admin_Form_Entity::factory('Checkbox');
					$oUseTypograph
						->name("use_typograph_description")
						->caption(Core::_('Informationsystem_Item.exec_typograph_description'))
						->value(1)
						->checked($oInformationsystem->typograph_default_items == 1)
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

					$oUseTrailingPunctuation = Admin_Form_Entity::factory('Checkbox');
					$oUseTrailingPunctuation
						->name("use_trailing_punctuation_description")
						->caption(Core::_('Informationsystem_Item.use_trailing_punctuation'))
						->value(1)
						->checked($oInformationsystem->typograph_default_items == 1)
						->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'));

					$oInformationsystemGroupDescriptionTabRow2
						->add($oUseTypograph)
						->add($oUseTrailingPunctuation);
				}

				// Добавляем новое поле типа файл
				$oImageField = Admin_Form_Entity::factory('File');

				$oLargeFilePath = is_file($this->_object->getLargeFilePath())
					? $this->_object->getLargeFileHref()
					: '';

				$oSmallFilePath = is_file($this->_object->getSmallFilePath())
					? $this->_object->getSmallFileHref()
					: '';

				$sFormPath = $this->_Admin_Form_Controller->getPath();
				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$oImageField
					//->caption(Core::_('Informationsystem_Group.image_large'))
					->style("width: 400px;")
					->name("image")
					->id("image")
					->largeImage(
					array(
						// image_big_max_width - значение максимальной ширины большого изображения;
						'max_width' => $oInformationsystem->group_image_large_max_width,

						// image_big_max_height - значение максимальной высоты большого изображения;
						'max_height' => $oInformationsystem->group_image_large_max_height,

						// big_image_path - адрес большого загруженного изображения
						'path' => $oLargeFilePath,

						// show_big_image_params - параметр, определяющий отображать ли настройки большого изображения
						'show_params' => TRUE,

						// watermark_position_x - значение поля ввода с подписью "По оси X"
						'watermark_position_x' => $oInformationsystem->watermark_default_position_x,

						// watermark_position_y - значение поля ввода с подписью "По оси Y"
						'watermark_position_y' => $oInformationsystem->watermark_default_position_y,

						// large_image_watermark_checked - вид ображения checkbox'а с подписью "Наложить водяной знак на большое изображение" (1 - отображать выбранным (по умолчанию), 0 - невыбранным);
						'place_watermark_checkbox_checked' => $oInformationsystem->watermark_default_use_large_image,

						// onclick_delete_big_image - значение onclick для удаления большой картинки
						'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteLargeImage', windowId: '{$windowId}'}); return false",

						'caption' => Core::_('Informationsystem_Group.image_large'),

						// used_big_image_preserve_aspect_ratio_checked - вид ображения checkbox'а с подписью "Сохранять пропорции изображения" (1 - отображать выбранным (по умолчанию), 0 - невыбранным);
						'preserve_aspect_ratio_checkbox_checked' => $oInformationsystem->preserve_aspect_ratio_group
						)
					)
					->smallImage(array(
						// image_small_max_width - значение максимальной ширины малого изображения;
						'max_width' => $oInformationsystem->group_image_small_max_width,

						// image_small_max_height - значение максимальной высоты малого изображения;
						'max_height' => $oInformationsystem->group_image_small_max_height,

						// small_image_path - адрес малого загруженного изображения
						'path' => $oSmallFilePath,

						// make_small_image_from_big_checked - вид ображения checkbox'а с подписью "Создать малое изображение из большого" выбранным (1 - отображать выбранным (по умолчанию), 0 - невыбранным);
						'create_small_image_from_large_checked' => $oInformationsystem->create_small_image && $this->_object->image_small == '',

						// small_image_watermark_checked - вид ображения checkbox'а с подписью "Наложить водяной знак на малое изображение" (1 - отображать выбранным (по умолчанию), 0 - невыбранным);
						'place_watermark_checkbox_checked' => $oInformationsystem->watermark_default_use_small_image,

						// onclick_delete_small_image - значение onclick для удаления малой картинки
						'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteSmallImage', windowId: '{$windowId}'}); return false",

						// load_small_image_caption - заголовок поля загрузки малого изображения
						'caption' => Core::_('Informationsystem_Group.image_small'),

						'show_params' => TRUE,

						'preserve_aspect_ratio_checkbox_checked' => $oInformationsystem->preserve_aspect_ratio_group_small
						)
					)
					->crop(TRUE);

				$oMainRow4->add($oImageField);

				$oSiteAlias = $oInformationsystem->Site->getCurrentAlias();
				if ($oSiteAlias)
				{
					$sGroupUrl = ($oInformationsystem->Structure->https ? 'https://' : 'http://')
						. $oSiteAlias->name
						. $oInformationsystem->Structure->getPath()
						. $this->_object->getPath();
				}

				$this->getField('path')
					->add(
						Admin_Form_Entity::factory('A')
							->id('path')
							->target('_blank')
							->href($sGroupUrl)
							->class('input-group-addon bg-blue bordered-blue')
							->value('<i class="fa fa-external-link"></i>')
				);

				// Path
				$this->getField('path')
					->format(array('maxlen' => array('value' => 255)));

				$oMainTab->move($this->getField('path'), $oMainRow5);

				// Sorting
				$this->getField('sorting')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$oMainTab->move($this->getField('sorting'), $oMainRow6);

				// Siteuser
				$oAdditionalTab->delete($this->getField('siteuser_group_id'));

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
					$aSiteuser_Groups = $oSiteuser_Controller_Edit->fillSiteuserGroups($this->_object->Informationsystem->site_id);
				}
				else
				{
					$aSiteuser_Groups = array();
				}

				// Список групп пользователей
				$oSelect_SiteuserGroups = Admin_Form_Entity::factory('Select')
					->options(array(
						0 => Core::_('Informationsystem.information_all'),
						-1 => Core::_('Informationsystem_Group.information_parent')
						) + $aSiteuser_Groups
					)
					->name('siteuser_group_id')
					->value($this->_object->siteuser_group_id)
					->caption(Core::_('Informationsystem_Group.siteuser_group_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'));

				$oMainRow6->add($oSelect_SiteuserGroups);

				$oAdditionalTab->delete($this->getField('siteuser_id'));

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser = $this->_object->Siteuser;

					$options = !is_null($oSiteuser->id)
						? array($oSiteuser->id => $oSiteuser->login . ' [' . $oSiteuser->id . ']')
						: array(0);

					$oSiteuserSelect = Admin_Form_Entity::factory('Select')
						->caption(Core::_('Informationsystem_Group.siteuser_id'))
						->id('object_siteuser_id')
						->options($options)
						->name('siteuser_id')
						->class('siteuser-tag')
						->style('width: 100%')
						->divAttr(array('class' => 'form-group col-xs-12'));

					$oMainRow6
						->add(
							Admin_Form_Entity::factory('Div')
								->class('form-group col-xs-12 col-sm-4 no-padding')
								->add($oSiteuserSelect)
						);

					// Show button
					Siteuser_Controller_Edit::addSiteuserSelect2($oSiteuserSelect, $oSiteuser, $this->_Admin_Form_Controller);
				}

				// Active
				$this->getField('active')
					->divAttr(array('class' => 'form-group col-xs-6 col-sm-4'));
				$this->getField('indexing')
					->divAttr(array('class' => 'form-group col-xs-6 col-sm-4'));

				$oMainTab->move($this->getField('active'), $oMainRow7);
				$oMainTab->move($this->getField('indexing'), $oMainRow7);

				// SEO
				$oMainTab
					->move($this->getField('seo_title'), $oSeoRow1)
					->move($this->getField('seo_description'), $oSeoRow2)
					->move($this->getField('seo_keywords'), $oSeoRow3);

			break;
		}

		$this->title($title);

		return $this;
	}

	/**
	 * Показ списка групп или поле ввода с autocomplete для большого количества групп
	 * @param string $fieldName имя поля группы
	 * @return array  массив элементов, для доабвления в строку
	 */
	public function informationsystemGroupShow($fieldName)
	{
		$return = array();

		$iCountGroups = $this->_object->Informationsystem->Informationsystem_Groups->getCount();

		switch (get_class($this->_object))
		{
			case 'Informationsystem_Item_Model':
				$i18n = 'Informationsystem_Item';
				$aExclude = array();
			break;
			case 'Informationsystem_Group_Model':
			default:
				$i18n = 'Informationsystem_Group';
				$aExclude = array($this->_object->id);
		}

		if ($iCountGroups < Core::$mainConfig['switchSelectToAutocomplete'])
		{
			$oInformationsystemGroupSelect = Admin_Form_Entity::factory('Select');
			$oInformationsystemGroupSelect
				->caption(Core::_($i18n . '.' . $fieldName))
				->options(array(' … ') + self::fillInformationsystemGroup($this->_object->informationsystem_id, 0, $aExclude))
				->name($fieldName)
				->value($this->_object->$fieldName)
				->divAttr(array('class' => 'form-group col-xs-12'))
				->filter(TRUE);

			$return = array($oInformationsystemGroupSelect);
		}
		else
		{
			$oInformationsystem_Group = Core_Entity::factory('Informationsystem_Group', $this->_object->$fieldName);

			$oInformationsystemGroupInput = Admin_Form_Entity::factory('Input')
				->caption(Core::_($i18n . '.' . $fieldName))
				->divAttr(array('class' => 'form-group col-xs-12'))
				->name('informationsystem_group_name');

			$this->_object->$fieldName
				&& $oInformationsystemGroupInput->value($oInformationsystem_Group->name . ' [' . $oInformationsystem_Group->id . ']');

			$oInformationsystemGroupInputHidden = Admin_Form_Entity::factory('Input')
				->divAttr(array('class' => 'form-group col-xs-12 hidden'))
				->name($fieldName)
				->value($this->_object->$fieldName)
				->type('hidden');

			$oCore_Html_Entity_Script = Core::factory('Core_Html_Entity_Script')
			->value("
				$('[name = informationsystem_group_name]').autocomplete({
					  source: function(request, response) {

						$.ajax({
						  url: '/admin/informationsystem/item/index.php?autocomplete=1&show_group=1&informationsystem_id={$this->_object->informationsystem_id}',
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
						$('[name = {$fieldName}]').val(ui.item.id);
					  },
					  open: function() {
						$(this).removeClass('ui-corner-all').addClass('ui-corner-top');
					  },
					  close: function() {
						$(this).removeClass('ui-corner-top').addClass('ui-corner-all');
					  }
				});
			");

			$return = array($oInformationsystemGroupInput, $oInformationsystemGroupInputHidden, $oCore_Html_Entity_Script);
		}

		return $return;
	}

	/**
	 * Information system groups tree
	 * @var array
	 */
	static protected $_aGroupTree = array();

	/**
	 * Build visual representation of group tree
	 * @param int $iInformationsystemId information system ID
	 * @param int $iInformationsystemGroupParentId parent ID
	 * @param int $aExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	static public function fillInformationsystemGroup($iInformationsystemId, $iInformationsystemGroupParentId = 0, $aExclude = array(), $iLevel = 0)
	{
		$iInformationsystemId = intval($iInformationsystemId);
		$iInformationsystemGroupParentId = intval($iInformationsystemGroupParentId);
		$iLevel = intval($iLevel);

		if ($iLevel == 0)
		{
			$aTmp = Core_QueryBuilder::select('id', 'parent_id', 'name')
				->from('informationsystem_groups')
				->where('informationsystem_id', '=', $iInformationsystemId)
				->where('shortcut_id', '=', 0)
				->where('deleted', '=', 0)
				->orderBy('sorting')
				->orderBy('name')
				->execute()->asAssoc()->result();

			foreach ($aTmp as $aGroup)
			{
				self::$_aGroupTree[$aGroup['parent_id']][] = $aGroup;
			}
		}

		$aReturn = array();

		if (isset(self::$_aGroupTree[$iInformationsystemGroupParentId]))
		{
			$countExclude = count($aExclude);
			foreach (self::$_aGroupTree[$iInformationsystemGroupParentId] as $childrenGroup)
			{
				if ($countExclude == 0 || !in_array($childrenGroup['id'], $aExclude))
				{
					$aReturn[$childrenGroup['id']] = str_repeat('  ', $iLevel) . $childrenGroup['name'] . ' [' . $childrenGroup['id'] . ']';
					$aReturn += self::fillInformationsystemGroup($iInformationsystemId, $childrenGroup['id'], $aExclude, $iLevel + 1);
				}
			}
		}

		$iLevel == 0 && self::$_aGroupTree = array();

		return $aReturn;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Informationsystem_Item_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$bNewObject = is_null($this->_object->id) && is_null(Core_Array::getPost('id'));

		$this->_formValues['siteuser_id'] = intval(Core_Array::get($this->_formValues, 'siteuser_id'));

		// Backup revision
		if (Core::moduleIsActive('revision') && $this->_object->id)
		{
			$this->_object->backupRevision();
		}

		parent::_applyObjectProperty();

		// UnIndex item
		!$bNewObject && $this->_object->unindex();

		$informationsystem_id = Core_Array::getGet('informationsystem_id');

		$oInformationsystem = /*is_null($this->_object->id)
			? Core_Entity::factory('Informationsystem', $informationsystem_id)
			: */$this->_object->Informationsystem;

		$modelName = $this->_object->getModelName();

		// Обработка ключевых слов группы
		if (Core::moduleIsActive('tag') && $modelName == 'informationsystem_item')
		{
			$aRecievedTags = Core_Array::getPost('tags', array());
			!is_array($aRecievedTags) && $aRecievedTags = array();

			//$item_tags = trim(Core_Array::getPost('tags'));

			if (count($aRecievedTags) == 0
				&& $oInformationsystem->apply_tags_automatically
				|| $oInformationsystem->apply_keywords_automatically && $this->_object->seo_keywords == '')
			{
				// Получаем хэш названия, описания и текста инфоэлемента
				$array_text = Core_Str::getHashes(
					Core_Array::getPost('name') . Core_Array::getPost('description') . ' ' . Core_Array::getPost('text', ''), array('hash_function' => 'crc32')
				);
				$array_text = array_unique($array_text);

				$coeff_intersect = array();

				$offset = 0;
				$limit = 100;

				do {
					$oTags = Core_Entity::factory('Tag');

					$oTags->queryBuilder()
						->offset($offset)
						->limit($limit);

					// Получаем список меток
					$aTags = $oTags->findAll(FALSE);

					foreach ($aTags as $oTag)
					{
						// Получаем хэш тэга
						$array_tags = Core_Str::getHashes($oTag->name, array('hash_function' => 'crc32'));

						// Получаем коэффициент схожести текста элемента с тэгом
						$array_tags = array_unique($array_tags);

						// Текст метки меньше текста инфоэлемента, т.к. должна входить метка в текст инфоэлемента, а не наоборот
						if (count($array_text) >= count($array_tags))
						{
							// Расчитываем пересечение
							$intersect = count(array_intersect($array_text, $array_tags));

							$coefficient = count($array_tags) != 0
								? $intersect / count($array_tags)
								: 0;

							// Найдено полное вхождение
							if ($coefficient == 1 && !in_array($oTag->id, $coeff_intersect))
							{
								$coeff_intersect[] = $oTag->id;
							}
						}
					}
					$offset += $limit;
				}
				while (count($aTags));
			}

			// Автоматическое применение ключевых слов
			if ($oInformationsystem->apply_keywords_automatically && $this->_object->seo_keywords == '')
			{
				// Найдено соответствие с тэгами
				if (count($coeff_intersect))
				{
					$aTmp = array();
					foreach ($coeff_intersect as $tag_id)
					{
						$oTag = Core_Entity::factory('Tag', $tag_id);
						$aTmp[] = $oTag->name;
					}

					$this->_object->seo_keywords = implode(', ', $aTmp);
				}
			}

			if (count($aRecievedTags) == 0
				&& $oInformationsystem->apply_tags_automatically && count($coeff_intersect)
			)
			{
				// Удаляем связь с метками
				$this->_object->Tag_Informationsystem_Items->deleteAll();

				foreach ($coeff_intersect as $tag_id)
				{
					$oTag = Core_Entity::factory('Tag', $tag_id);
					$this->_object->add($oTag);
				}
			}
			else
			{
				$this->_object->applyTagsArray($aRecievedTags);
			}
		}

		$aConfig = Core_Config::instance()->get('informationsystem_config', array()) + array(
			'smallImagePrefix' => 'small_',
			'itemLargeImage' => 'item_%d.%s',
			'itemSmallImage' => 'small_item_%d.%s',
			'groupLargeImage' => 'group_%d.%s',
			'groupSmallImage' => 'small_group_%d.%s',
		);

		switch ($modelName)
		{
			case 'informationsystem_item':
				// Проверяем подключен ли модуль типографики.
				if (Core::moduleIsActive('typograph'))
				{
					// Проверяем, нужно ли применять типографику к описанию информационного элемента.
					if (Core_Array::getPost('use_typograph_description', 0))
					{
						$this->_object->description = Typograph_Controller::instance()->process($this->_object->description, Core_Array::getPost('use_trailing_punctuation_description', 0));
					}

					// Проверяем, нужно ли применять типографику к информационного элемента тексту.
					if (Core_Array::getPost('use_typograph_text', 0))
					{
						$this->_object->text = Typograph_Controller::instance()->process($this->_object->text, Core_Array::getPost('use_trailing_punctuation_text', 0));
					}
				}

				if ($this->_object->start_datetime == '')
				{
					$this->_object->start_datetime = '0000-00-00 00:00:00';
				}

				if ($this->_object->end_datetime == '')
				{
					$this->_object->end_datetime = '0000-00-00 00:00:00';
				}

				// Properties
				Property_Controller_Tab::factory($this->_Admin_Form_Controller)
					->setObject($this->_object)
					->linkedObject(Core_Entity::factory('Informationsystem_Item_Property_List', $oInformationsystem->id))
					->applyObjectProperty();

				$aShortcutGroupIds = Core_Array::getPost('shortcut_group_id', array());
				!is_array($aShortcutGroupIds) && $aShortcutGroupIds = array();

				$aTmp = array();

				// Выбранные группы
				$aShortcuts = $oInformationsystem->Informationsystem_Items->getAllByShortcut_id($this->_object->id, FALSE);
				foreach ($aShortcuts as $oShortcut)
				{
					!in_array($oShortcut->informationsystem_group_id, $aShortcutGroupIds)
						? $oShortcut->markDeleted()
						: $aTmp[] = $oShortcut->informationsystem_group_id;
				}

				$aNewShortcutGroupIDs = array_diff($aShortcutGroupIds, $aTmp);
				foreach ($aNewShortcutGroupIDs as $iShortcutGroupId)
				{
					$oInformationsystem_Group = $oInformationsystem->Informationsystem_Groups->getById($iShortcutGroupId);
					if (!is_null($oInformationsystem_Group))
					{
						$oInformationsystem_ItemShortcut = Core_Entity::factory('Informationsystem_Item');

						$oInformationsystem_ItemShortcut->informationsystem_id = $this->_object->informationsystem_id;
						$oInformationsystem_ItemShortcut->shortcut_id = $this->_object->id;
						$oInformationsystem_ItemShortcut->informationsystem_group_id = $iShortcutGroupId;
						$oInformationsystem_ItemShortcut->datetime = $this->_object->datetime;
						$oInformationsystem_ItemShortcut->name = '';
						$oInformationsystem_ItemShortcut->path = '';
						$oInformationsystem_ItemShortcut->indexing = 0;

						$oInformationsystem_ItemShortcut->save()->clearCache();
					}
				}

				break;
			case 'informationsystem_group':
			default:
				// Проверяем подключен ли модуль типографики.
				if (Core::moduleIsActive('typograph'))
				{
					// Проверяем, нужно ли применять типографику к описанию информационной группы.
					if (Core_Array::getPost('use_typograph', 0))
					{
						$this->_object->description = Typograph_Controller::instance()->process($this->_object->description, Core_Array::getPost('use_trailing_punctuation', 0));
					}
				}

				// Properties
				Property_Controller_Tab::factory($this->_Admin_Form_Controller)
					->setObject($this->_object)
					->linkedObject(Core_Entity::factory('Informationsystem_Group_Property_List', $oInformationsystem->id))
					->applyObjectProperty();
		}

		// Clear tagged cache
		$this->_object->clearCache();

		$param = array();

		$large_image = '';
		$small_image = '';

		$aCore_Config = Core::$mainConfig;

		$create_small_image_from_large = Core_Array::getPost('create_small_image_from_large_small_image');

		$bLargeImageIsCorrect =
			// Поле файла большого изображения существует
			!is_null($aFileData = Core_Array::getFiles('image', NULL))
			// и передан файл
			&& intval($aFileData['size']) > 0;

		if ($bLargeImageIsCorrect)
		{
			// Проверка на допустимый тип файла
			if (Core_File::isValidExtension($aFileData['name'], $aCore_Config['availableExtension']))
			{
				// Удаление файла большого изображения
				if ($this->_object->image_large)
				{
					$this->_object->deleteLargeImage();
				}

				$file_name = $aFileData['name'];

				// Не преобразовываем название загружаемого файла
				if (!$oInformationsystem->change_filename)
				{
					$large_image = $file_name;
				}
				else
				{
					// Определяем расширение файла
					$ext = Core_File::getExtension($aFileData['name']);

					$large_image = $modelName == 'informationsystem_item'
						? sprintf($aConfig['itemLargeImage'], $this->_object->id, $ext)
						: sprintf($aConfig['groupLargeImage'], $this->_object->id, $ext);
				}
			}
			else
			{
				$this->addMessage(	Core_Message::get(
					Core::_('Core.extension_does_not_allow', Core_File::getExtension($aFileData['name'])),
						'error'
					)
				);
			}
		}

		$aSmallFileData = Core_Array::getFiles('small_image', NULL);
		$bSmallImageIsCorrect =
			// Поле файла малого изображения существует
			!is_null($aSmallFileData)
			&& $aSmallFileData['size'];

		// Задано малое изображение и при этом не задано создание малого изображения
		// из большого или задано создание малого изображения из большого и
		// при этом не задано большое изображение.

		if ($bSmallImageIsCorrect || $create_small_image_from_large && $bLargeImageIsCorrect)
		{
			// Удаление файла малого изображения
			if ($this->_object->image_small)
			{
				$this->_object->deleteSmallImage();
			}

			// Явно указано малое изображение
			if ($bSmallImageIsCorrect
				&& Core_File::isValidExtension($aSmallFileData['name'], $aCore_Config['availableExtension']))
			{
				// Для инфогруппы ранее задано изображение
				if ($this->_object->image_large != '')
				{
					// Существует ли большое изображение
					$param['large_image_isset'] = true;
					$create_large_image = false;
				}
				else // Для информационной группы ранее не задано большое изображение
				{
					$create_large_image = empty($large_image);
				}

				$file_name = $aSmallFileData['name'];

				// Не преобразовываем название загружаемого файла
				if (!$oInformationsystem->change_filename)
				{
					if ($create_large_image)
					{
						$large_image = $file_name;
						$small_image = $aConfig['smallImagePrefix'] . $large_image;
					}
					else
					{
						$small_image = $file_name;
					}
				}
				else
				{
					// Определяем расширение файла
					$ext = Core_File::getExtension($file_name);

					$small_image = $modelName == 'informationsystem_item'
						? sprintf($aConfig['itemSmallImage'], $this->_object->id, $ext)
						: sprintf($aConfig['groupSmallImage'], $this->_object->id, $ext);
				}
			}
			elseif ($create_small_image_from_large && $bLargeImageIsCorrect)
			{
				$small_image = $aConfig['smallImagePrefix'] . $large_image;
			}
			// Тип загружаемого файла является недопустимым для загрузки файла
			else
			{
				$this->addMessage(
					Core_Message::get(Core::_('Core.extension_does_not_allow', Core_File::getExtension($aSmallFileData['name'])), 'error')
				);
			}
		}

		if ($bLargeImageIsCorrect || $bSmallImageIsCorrect)
		{
			if ($bLargeImageIsCorrect)
			{
				// Путь к файлу-источнику большого изображения;
				$param['large_image_source'] = $aFileData['tmp_name'];
				// Оригинальное имя файла большого изображения
				$param['large_image_name'] = $aFileData['name'];
			}

			if ($bSmallImageIsCorrect)
			{
				// Путь к файлу-источнику малого изображения;
				$param['small_image_source'] = $aSmallFileData['tmp_name'];
				// Оригинальное имя файла малого изображения
				$param['small_image_name'] = $aSmallFileData['name'];
			}

			if ($modelName == 'informationsystem_group')
			{
				// Путь к создаваемому файлу большого изображения;
				$param['large_image_target'] = !empty($large_image)
					? $this->_object->getGroupPath() . $large_image
					: '';

				// Путь к создаваемому файлу малого изображения;
				$param['small_image_target'] = !empty($small_image)
					? $this->_object->getGroupPath() . $small_image
					: '' ;
			}
			else
			{
				// Путь к создаваемому файлу большого изображения;
				$param['large_image_target'] = !empty($large_image)
					? $this->_object->getItemPath() . $large_image
					: '';

				// Путь к создаваемому файлу малого изображения;
				$param['small_image_target'] = !empty($small_image)
					? $this->_object->getItemPath() . $small_image
					: '' ;
			}

			// Использовать большое изображение для создания малого
			$param['create_small_image_from_large'] = !is_null(Core_Array::getPost('create_small_image_from_large_small_image'));

			// Значение максимальной ширины большого изображения
			$param['large_image_max_width'] = Core_Array::getPost('large_max_width_image', 0);

			// Значение максимальной высоты большого изображения
			$param['large_image_max_height'] = Core_Array::getPost('large_max_height_image', 0);

			// Значение максимальной ширины малого изображения;
			$param['small_image_max_width'] = Core_Array::getPost('small_max_width_small_image');

			// Значение максимальной высоты малого изображения;
			$param['small_image_max_height'] = Core_Array::getPost('small_max_height_small_image');

			// Путь к файлу с "водяным знаком"
			$param['watermark_file_path'] = $oInformationsystem->getWatermarkFilePath();

			// Позиция "водяного знака" по оси X
			$param['watermark_position_x'] = Core_Array::getPost('watermark_position_x_image');

			// Позиция "водяного знака" по оси Y
			$param['watermark_position_y'] = Core_Array::getPost('watermark_position_y_image');

			// Наложить "водяной знак" на большое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['large_image_watermark'] = !is_null(Core_Array::getPost('large_place_watermark_checkbox_image'));

			// Наложить "водяной знак" на малое изображение (true - наложить (по умолчанию), false - не наложить);
			$param['small_image_watermark'] = !is_null(Core_Array::getPost('small_place_watermark_checkbox_small_image'));

			// Сохранять пропорции изображения для большого изображения
			$param['large_image_preserve_aspect_ratio'] = !is_null(Core_Array::getPost('large_preserve_aspect_ratio_image'));

			// Сохранять пропорции изображения для малого изображения
			$param['small_image_preserve_aspect_ratio'] = !is_null(Core_Array::getPost('small_preserve_aspect_ratio_small_image'));

			$this->_object->createDir();

			$result = Core_File::adminUpload($param);

			if ($result['large_image'])
			{
				$this->_object->image_large = $large_image;

				if ($modelName == 'informationsystem_item')
				{
					$this->_object->setLargeImageSizes();
				}
			}

			if ($result['small_image'])
			{
				$this->_object->image_small = $small_image;

				if ($modelName == 'informationsystem_item')
				{
					$this->_object->setSmallImageSizes();
				}
			}

			//$this->_object->save();
		}

		$this->_object->save();

		// Index item
		$this->_object->index();

		if ($modelName == 'informationsystem_item')
		{
			// Index item by schedule
			if (Core::moduleIsActive('schedule')
				&& $this->_object->start_datetime != '0000-00-00 00:00:00'
				&& Core_Date::sql2timestamp($this->_object->start_datetime) > time())
			{
				$oModule = Core_Entity::factory('Module')->getByPath('informationsystem');

				if (!is_null($oModule->id))
				{
					$oSchedule = Core_Entity::factory('Schedule');
					$oSchedule->module_id = $oModule->id;
					$oSchedule->site_id = CURRENT_SITE;
					$oSchedule->entity_id = $this->_object->id;
					$oSchedule->action = 0;
					$oSchedule->start_datetime = $this->_object->start_datetime;
					$oSchedule->save();
				}
			}

			// Unindex item by schedule
			if (Core::moduleIsActive('schedule')
				&& $this->_object->end_datetime != '0000-00-00 00:00:00'
				&& Core_Date::sql2timestamp($this->_object->end_datetime) > time())
			{
				$oModule = Core_Entity::factory('Module')->getByPath('informationsystem');

				if (!is_null($oModule->id))
				{
					$oSchedule = Core_Entity::factory('Schedule');
					$oSchedule->module_id = $oModule->id;
					$oSchedule->site_id = CURRENT_SITE;
					$oSchedule->entity_id = $this->_object->id;
					$oSchedule->action = 2;
					$oSchedule->start_datetime = $this->_object->end_datetime;
					$oSchedule->save();
				}
			}
		}

		if (Core::moduleIsActive('maillist') && Core_Array::getPost('maillist_id'))
		{
			$oMaillist = Core_Entity::factory('Maillist', Core_Array::getPost('maillist_id'));
			$oMaillist_Fascicle = Core_Entity::factory('Maillist_Fascicle');

			$html = str_replace("%TEXT", $this->_object->text, $oMaillist->template);

			$oCurrentAlias = $this->_object->Informationsystem->Site->getCurrentAlias();

			if ($oCurrentAlias)
			{
				$href = ($this->_object->Informationsystem->Structure->https ? 'https://' : 'http://')
					. $oCurrentAlias->name;

				$html = preg_replace('~(href|src)=(["\'])(?!#)(?!https?://)([^\2]*?)\2~i','$1="' . $href . '$3"', $html);
			}

			$oMaillist_Fascicle->subject = $this->_object->name;
			$oMaillist_Fascicle->html = $html;
			$oMaillist_Fascicle->createTextFromHtml();
			$oMaillist_Fascicle->datetime = Core_Date::timestamp2sql(time());
			$oMaillist_Fascicle->sent_datetime = '0000-00-00 00:00:00';
			$oMaillist_Fascicle->changed = 0;

			$oMaillist_Fascicle->save();
			$oMaillist->add($oMaillist_Fascicle);
		}

		$oSiteAlias = $oInformationsystem->Site->getCurrentAlias();
		if ($oSiteAlias)
		{
			$windowId = $this->_Admin_Form_Controller->getWindowId();

			$sUrl = ($oInformationsystem->Structure->https ? 'https://' : 'http://')
				. $oSiteAlias->name
				. $oInformationsystem->Structure->getPath()
				. $this->_object->getPath();

			$this->_Admin_Form_Controller->addMessage(
				Core::factory('Core_Html_Entity_Script')
					->value("$('#{$windowId} a#path').attr('href', '" . Core_Str::escapeJavascriptVariable($sUrl) . "')")
				->execute()
			);
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Fill shortcut groups list
	 * @param Informationsystem_Item_Model $oInformationsystem_Item item
	 * @return array
	 */
	protected function _fillShortcutGroupList($oInformationsystem_Item)
	{
		$aReturnArray = array();

		$oInformationsystem = $oInformationsystem_Item->Informationsystem;

		$aShortcuts = $oInformationsystem->Informationsystem_Items->getAllByShortcut_id($oInformationsystem_Item->id, FALSE);
		foreach ($aShortcuts as $oShortcut)
		{
			$oInformationsystem_Group = $oShortcut->Informationsystem_Group;

			$aParentGroups = array();

			$aTmpGroup = $oInformationsystem_Group;

			// Добавляем все директории от текущей до родителя.
			do {
				$aParentGroups[] = $aTmpGroup->name;
			} while ($aTmpGroup = $aTmpGroup->getParent());

			$sParents = implode(' → ', array_reverse($aParentGroups));

			if (!is_null($oInformationsystem_Group->id))
			{
				$aReturnArray[$oInformationsystem_Group->id] = array(
					'value' => $sParents . ' [' . $oInformationsystem_Group->id . ']',
					'attr' => array('selected' => 'selected')
				);
			}
			else
			{
				$aReturnArray[0] = array(
					'value' => Core::_('Informationsystem_Item.root') . ' [0]',
					'attr' => array('selected' => 'selected')
				);
			}
		}

		return $aReturnArray;
	}

	/**
	 * Fill tags list
	 * @param Informationsystem_Item_Model $oInformationsystem_Item item
	 * @return array
	 */
	protected function _fillTagsList($oInformationsystem_Item)
	{
		$aReturnArray = array();

		$aTags = $oInformationsystem_Item->Tags->findAll(FALSE);

		foreach ($aTags as $oTag)
		{
			$aReturnArray[$oTag->name] = array(
				'value' => $oTag->name,
				'attr' => array('selected' => 'selected')
			);
		}

		return $aReturnArray;
	}

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (!is_null($operation) && $operation != '')
		{
			$informationsystem_id = Core_Array::getPost('informationsystem_id');
			$path = Core_Array::getPost('path');

			/*if ($path == '')
			{
				$this->_object->name = Core_Array::getPost('name');
				$this->_object->path = Core_Array::getPost('path');
				// id еще не определен, поэтому makePath() не может работать корректно
				//$this->_object->makePath();
				$path = $this->_object->path;

				$this->addSkipColumn('path');
			}*/

			if (strlen($path))
			{
				$modelName = $this->_object->getModelName();

				switch ($modelName)
				{
					case 'informationsystem_item':
						$informationsystem_group_id = Core_Array::getPost('informationsystem_group_id');

						$oSameInformationsystemItem = Core_Entity::factory('Informationsystem', $informationsystem_id)->Informationsystem_Items->getByGroupIdAndPath($informationsystem_group_id, $path);

						if (!is_null($oSameInformationsystemItem) && $oSameInformationsystemItem->id != Core_Array::getPost('id'))
						{
							$this->addMessage(
								Core_Message::get(Core::_('Informationsystem_Item.error_information_group_URL_item'), 'error')
							);
							return TRUE;
						}

						$oSameInformationsystemGroup = Core_Entity::factory('Informationsystem', $informationsystem_id)->Informationsystem_Groups->getByParentIdAndPath($informationsystem_group_id, $path);

						if (!is_null($oSameInformationsystemGroup))
						{
							$this->addMessage(
								Core_Message::get(Core::_('Informationsystem_Item.error_information_group_URL_item_URL') , 'error')
							);
							return TRUE;
						}
					break;
					case 'informationsystem_group':
						$parent_id = Core_Array::getPost('parent_id');

						$oSameInformationsystemGroup = Core_Entity::factory('Informationsystem', $informationsystem_id)->Informationsystem_Groups->getByParentIdAndPath($parent_id, $path);

						if (!is_null($oSameInformationsystemGroup) && $oSameInformationsystemGroup->id != Core_Array::getPost('id'))
						{
							$this->addMessage(
								Core_Message::get(Core::_('Informationsystem_Group.error_URL_information_group'), 'error')
							);
							return TRUE;
						}

						$oSameInformationsystemItem = Core_Entity::factory('Informationsystem', $informationsystem_id)->Informationsystem_Items->getByGroupIdAndPath($parent_id, $path);

						if (!is_null($oSameInformationsystemItem))
						{
							$this->addMessage(
								Core_Message::get(Core::_('Informationsystem_Group.error_information_group_URL_add_edit_URL'), 'error')
							);
							return TRUE;
						}
					break;
				}
			}
		}

		return parent::execute($operation);
	}
}