<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$modelName = $object->getModelName();

		switch ($modelName)
		{
			case 'shop':
				$this->addSkipColumn('watermark_file');

				if (!$object->id)
				{
					$object->shop_dir_id = intval(Core_Array::getGet('shop_dir_id', 0));

					$object->order_admin_subject = Core::_('Shop_Order.shop_order_admin_subject');
					$object->order_user_subject = Core::_('Shop_Order.shop_order_admin_subject');
					$object->confirm_admin_subject = Core::_('Shop_Order.confirm_admin_subject');
					$object->confirm_user_subject = Core::_('Shop_Order.confirm_user_subject');
					$object->cancel_admin_subject = Core::_('Shop_Order.cancel_admin_subject');
					$object->cancel_user_subject = Core::_('Shop_Order.cancel_user_subject');
				}

			break;
			case 'shop_dir':
			default:
				if (!$object->id)
				{
					$object->parent_id = intval(Core_Array::getGet('shop_dir_id', 0));
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

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		switch ($modelName)
		{
			case 'shop_dir':
				$title = $object->id
					? Core::_('Shop_Dir.edit_title')
					: Core::_('Shop_Dir.add_title');

				$oAdditionalTab->delete($this->getField('parent_id'));

				$oAdminFormEntitySelect = Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Dir.parent_id'))
					->options(
						array(' … ') + $this->_fillShopDir(0, $object->id)
					)
					->name('parent_id')
					->value($this->_object->parent_id);

				$oMainTab->addAfter(
					$oAdminFormEntitySelect, $this->getField('description')
				);

			break;

			case 'shop':
				$title = $object->id
					? Core::_('Shop.edit_title')
					: Core::_('Shop.add_title');

				$oShopTabFormats = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop.tab_formats'))
					->name('Formats');
				$oShopTabExport = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop.tab_export'))
					->name('Export');
				$oShopTabWatermark = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop.tab_watermark'))
					->name('Watermark');
				$oShopTabOrders = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop.tab_sort'))
					->name('Orders');
				$oShopTabMailSubjects = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop.tab_mail_subject'))
					->name('Mail_Subjects');

				$oMainTab
					->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRowNotification = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow9 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow10 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow11 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow12 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow13 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow14 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow15 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow16 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oMainRow17 = Admin_Form_Entity::factory('Div')->class('row'));

				$oShopTabFormats
					->add($oShopTabFormatsRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabFormatsRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabFormatsRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabFormatsRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabFormatsRow5 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabFormatsRow6 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabFormatsRow7 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabFormatsRow8 = Admin_Form_Entity::factory('Div')->class('row'));

				$oShopTabExport
					->add($oGuidRow = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oYandexMarketBlock = Admin_Form_Entity::factory('Div')->class('well with-header'));

				$oYandexMarketBlock
					->add(Admin_Form_Entity::factory('Div')
						->class('header bordered-yellow')
						->value(Core::_("Shop_Item.yandex_market_header"))
					)
					->add($oShopTabExportRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabExportRow2 = Admin_Form_Entity::factory('Div')->class('row'))
				;

				$oShopTabWatermark
					->add($oShopTabWatermarkRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabWatermarkRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabWatermarkRow3 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabWatermarkRow4 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabWatermarkRow5 = Admin_Form_Entity::factory('Div')->class('row'));

				$oShopTabOrders
					->add($oShopTabOrdersRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabOrdersRow2 = Admin_Form_Entity::factory('Div')->class('row'));

				$oShopTabMailSubjects
					->add($oShopTabMailSubjectsRow1 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabMailSubjectsRow2 = Admin_Form_Entity::factory('Div')->class('row'))
					->add($oShopTabMailSubjectsRow3 = Admin_Form_Entity::factory('Div')->class('row'));

				$this
					->addTabAfter($oShopTabFormats, $oMainTab)
					->addTabAfter($oShopTabMailSubjects, $oShopTabFormats)
					->addTabAfter($oShopTabExport, $oShopTabMailSubjects)
					->addTabAfter($oShopTabWatermark, $oShopTabExport)
					->addTabAfter($oShopTabOrders, $oShopTabWatermark);

				// Перемещаем поля на их вкладки
				$oMainTab
					// Formats
					->move($this->getField('image_small_max_width'), $oShopTabFormats)
					->move($this->getField('image_small_max_height'), $oShopTabFormats)
					->move($this->getField('image_large_max_width'), $oShopTabFormats)
					->move($this->getField('image_large_max_height'), $oShopTabFormats)
					->move($this->getField('group_image_small_max_width'), $oShopTabFormats)
					->move($this->getField('group_image_small_max_height'), $oShopTabFormats)
					->move($this->getField('group_image_large_max_width'), $oShopTabFormats)
					->move($this->getField('group_image_large_max_height'), $oShopTabFormats)
					->move($this->getField('producer_image_small_max_width'), $oShopTabFormats)
					->move($this->getField('producer_image_small_max_height'), $oShopTabFormats)
					->move($this->getField('producer_image_large_max_width'), $oShopTabFormats)
					->move($this->getField('producer_image_large_max_height'), $oShopTabFormats)
					->move($this->getField('format_date'), $oShopTabFormats)
					->move($this->getField('format_datetime'), $oShopTabFormats)
					->move($this->getField('typograph_default_items'), $oShopTabFormats)
					->move($this->getField('typograph_default_groups'), $oShopTabFormats)
					// Export
					->move($this->getField('yandex_market_name'), $oShopTabExport)
					->move($this->getField('guid'), $oShopTabExport)
					->move($this->getField('yandex_market_sales_notes_default'), $oShopTabExport)
					->move($this->getField('adult'), $oShopTabExport)
					// Watermark
					->move($this->getField('preserve_aspect_ratio'), $oShopTabWatermark)
					->move($this->getField('preserve_aspect_ratio_small'), $oShopTabWatermark)
					->move($this->getField('preserve_aspect_ratio_group'), $oShopTabWatermark)
					->move($this->getField('preserve_aspect_ratio_group_small'), $oShopTabWatermark)
					->move($this->getField('watermark_default_use_large_image'), $oShopTabWatermark)
					->move($this->getField('watermark_default_use_small_image'), $oShopTabWatermark)
					->move($this->getField('watermark_default_position_x'), $oShopTabWatermark)
					->move($this->getField('watermark_default_position_y'), $oShopTabWatermark)
					// Orders
					->move($this->getField('items_sorting_field'), $oShopTabOrders)
					->move($this->getField('items_sorting_direction'), $oShopTabOrders)
					->move($this->getField('groups_sorting_field'), $oShopTabOrders)
					->move($this->getField('groups_sorting_direction'), $oShopTabOrders)
					//Mail subjects
					->move($this->getField('order_admin_subject')->divAttr(array('class' => 'form-group col-xs-12 col-lg-6')), $oShopTabMailSubjectsRow1)
					->move($this->getField('order_user_subject')->divAttr(array('class' => 'form-group col-xs-12 col-lg-6')), $oShopTabMailSubjectsRow1)
					->move($this->getField('confirm_admin_subject')->divAttr(array('class' => 'form-group col-xs-12 col-lg-6')), $oShopTabMailSubjectsRow2)
					->move($this->getField('confirm_user_subject')->divAttr(array('class' => 'form-group col-xs-12 col-lg-6')), $oShopTabMailSubjectsRow2)
					->move($this->getField('cancel_admin_subject')->divAttr(array('class' => 'form-group col-xs-12 col-lg-6')), $oShopTabMailSubjectsRow3)
					->move($this->getField('cancel_user_subject')->divAttr(array('class' => 'form-group col-xs-12 col-lg-6')), $oShopTabMailSubjectsRow3)
					;

				// Переопределяем стандартные поля на нужный нам вид

				// Удаляем группу магазинов
				$oAdditionalTab
					->delete($this->getField('shop_dir_id'))
					// Удаляем структуру
					->delete($this->getField('structure_id'))
					// Удаляем страну
					->delete($this->getField('shop_country_id'))
					// Удаляем группу пользователей сайта
					->delete($this->getField('siteuser_group_id'))
					// Удаляем единицы измерения
					->delete($this->getField('shop_measure_id'))
					// Удаляем валюты
					->delete($this->getField('shop_currency_id'))
					// Удаляем статусы заказов
					->delete($this->getField('shop_order_status_id'))
					// Удаляем компании
					->delete($this->getField('shop_company_id'));

				// Удаляем тип URL
				$oMainTab->delete(
					$this->getField('url_type')
				);

				// Удаляем поле сортировки товара
				$oShopTabOrders->delete(
					$this->getField('items_sorting_field')
				);

				// Удаляем направление сортировки товара
				$oShopTabOrders->delete(
					$this->getField('items_sorting_direction')
				);

				// Удаляем поле сортировки групп товаров
				$oShopTabOrders->delete(
					$this->getField('groups_sorting_field')
				);

				// Удаляем направление сортировки групп товаров
				$oShopTabOrders->delete(
					$this->getField('groups_sorting_direction')
				);

				// Добавляем группу магазинов
				$oMainRow1->add(Admin_Form_Entity::factory('Select')
					->name('shop_dir_id')
					->caption(Core::_('Shop.shop_dir_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-lg-6'))
					//->style("width: 320px")
					->options(
						array(' … ') + $this->_fillShopDir()
					)
					->value($this->_object->shop_dir_id));

				// Переопределяем тип поля описания на WYSIWYG
				$this->getField('description')
					->wysiwyg(TRUE)
					->template_id($this->_object->Structure->template_id
						? $this->_object->Structure->template_id
						: 0);

				$oMainTab->move($this->getField('description'), $oMainRow2);

				$Structure_Controller_Edit = new Structure_Controller_Edit($this->_Admin_Form_Action);

				// Добавляем структуру
				$oStructureSelectField = Admin_Form_Entity::factory('Select')
					->name('structure_id')
					->caption(Core::_('Shop.structure_id'))
					->options(
						array(' … ') + $Structure_Controller_Edit->fillStructureList($this->_object->site_id)
					)
					->value($this->_object->structure_id);

				$oMainRow3->add($oStructureSelectField);

				if (Core::moduleIsActive('siteuser'))
				{
					$oSiteuser_Controller_Edit = new Siteuser_Controller_Edit($this->_Admin_Form_Action);
					$aSiteuser_Groups = $oSiteuser_Controller_Edit->fillSiteuserGroups($this->_object->site_id);
				}
				else
				{
					$aSiteuser_Groups = array();
				}

				// Добавляем группы пользователей сайта
				$oShopUserGroupSelect = Admin_Form_Entity::factory('Select')
					->name('siteuser_group_id')
					->caption(Core::_('Shop.siteuser_group_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->options(array(Core::_('Shop.allgroupsaccess')) + $aSiteuser_Groups)
					->value($this->_object->siteuser_group_id);

				$oMainRow4->add($oShopUserGroupSelect);

				// Добавляем компании
				$oCompaniesField = Admin_Form_Entity::factory('Select')
					->name('shop_company_id')
					->caption(Core::_('Shop.shop_company_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->options(
						$this->_fillCompanies()
					)
					->value($this->_object->shop_company_id);

				$oMainRow4->add($oCompaniesField);

				// Добавляем валюты
				$oCurrencyField = Admin_Form_Entity::factory('Select')
					->name('shop_currency_id')
					->caption(Core::_('Shop.shop_currency_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->options(
						$this->fillCurrencies()
					)
					->value($this->_object->shop_currency_id);

				$oMainRow4->add($oCurrencyField);

				// Добавляем страны
				$oCountriesField = Admin_Form_Entity::factory('Select')
					->name('shop_country_id')
					->caption(Core::_('Shop.shop_country_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->options(
						$this->fillCountries()
					)
					->value($this->_object->shop_country_id);

				$oMainRow5->add($oCountriesField);

				// Добавляем статусы заказов
				$oOrderStatusField = Admin_Form_Entity::factory('Select')
					->name('shop_order_status_id')
					->caption(Core::_('Shop.shop_order_status_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->options(
						$this->fillOrderStatuses()
					)
					->value($this->_object->shop_order_status_id);

				$oMainRow5->add($oOrderStatusField);

				// Добавляем единицы измерения
				$oMeasuresField = Admin_Form_Entity::factory('Select')
					->name('shop_measure_id')
					->caption(Core::_('Shop.shop_measure_id'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->options(
						$this->fillMeasures()
					)
					->value($this->_object->shop_measure_id);

				$oMainRow5->add($oMeasuresField);


				$oMainTab->move($this->getField('email')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					// clear standart url pattern
					->format(array('lib' => array())), $oMainRow6);

				$oMainTab->move($this->getField('items_on_page')
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oMainRow6);

				// Добавляем тип URL
				$oUrlTypeField = Admin_Form_Entity::factory('Select')
					->name('url_type')
					->caption(Core::_('Shop.url_type'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->options(
						array(
							Core::_('Shop.shop_shops_url_type_element_0'),
							Core::_('Shop.shop_shops_url_type_element_1'))
					)
					->value($this->_object->url_type);

				$oMainRow6->add($oUrlTypeField);

				$oMainTab->delete($this->getField('size_measure'));

				$oMainRow7->add(Admin_Form_Entity::factory('Select')
					->name('size_measure')
					->caption(Core::_('Shop.size_measure'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-4'))
					->options(array(Core::_('Shop.size_measure_0'),
						Core::_('Shop.size_measure_1'),
						Core::_('Shop.size_measure_2'),
						Core::_('Shop.size_measure_3'),
						Core::_('Shop.size_measure_4')))
					->value($this->_object->size_measure), $oUrlTypeField);

				$oMainTab->delete($this->getField('reserve_hours'));

				$oMainRow7->add($this->getField('reserve_hours')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')));

				// Notification subscribers
				if (Core::moduleIsActive('notification'))
				{
					$oNotificationSubscribersSelect = Admin_Form_Entity::factory('Select')
						->caption(Core::_('Shop.notification_subscribers'))
						->options($this->_fillNotificationSubscribersList())
						->name('notification_subscribers[]')
						->class('shop-notification-subscribers')
						->style('width: 100%')
						->multiple('multiple')
						->divAttr(array('class' => 'form-group col-xs-12'));

					$oMainRowNotification->add($oNotificationSubscribersSelect);

					$html = '
						<script type="text/javascript">
							$(function(){
								$(".shop-notification-subscribers").select2({
									language: "' . Core_i18n::instance()->getLng() . '",
									minimumInputLength: 1,
									placeholder: "' . Core::_('Shop.type_subscriber') . '",
									tags: true,
									allowClear: true,
									multiple: true,
									ajax: {
										url: "/admin/user/index.php?loadSubscribersList",
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

					$oMainRowNotification->add(Admin_Form_Entity::factory('Code')->html($html));
				}

				$oMainTab->move($this->getField('reserve'), $oMainRow8);
				$oMainTab->move($this->getField('send_order_email_admin'), $oMainRow9);
				$oMainTab->move($this->getField('send_order_email_user'), $oMainRow10);
				$oMainTab->move($this->getField('comment_active'), $oMainRow11);
				$oMainTab->move($this->getField('apply_tags_automatically'), $oMainRow12);
				$oMainTab->move($this->getField('write_off_paid_items'), $oMainRow13);
				$oMainTab->move($this->getField('apply_keywords_automatically'), $oMainRow14);
				$oMainTab->move($this->getField('change_filename'), $oMainRow15);
				$oMainTab->move($this->getField('attach_digital_items'), $oMainRow16);
				$oMainTab->move($this->getField('use_captcha'), $oMainRow17);

				$oShopTabFormats->move($this->getField('image_small_max_width')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow1);
				$oShopTabFormats->move($this->getField('image_small_max_height')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow1);

				$oShopTabFormats->move($this->getField('image_large_max_width')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow2);
				$oShopTabFormats->move($this->getField('image_large_max_height')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow2);

				$oShopTabFormats->move($this->getField('group_image_small_max_width')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow3);
				$oShopTabFormats->move($this->getField('group_image_small_max_height')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow3);

				$oShopTabFormats->move($this->getField('group_image_large_max_width')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow4);
				$oShopTabFormats->move($this->getField('group_image_large_max_height')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow4);

				$oShopTabFormats->move($this->getField('producer_image_small_max_width')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow5);
				$oShopTabFormats->move($this->getField('producer_image_small_max_height')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow5);

				$oShopTabFormats->move($this->getField('producer_image_large_max_width')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow6);
				$oShopTabFormats->move($this->getField('producer_image_large_max_height')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow6);

				$oShopTabFormats->move($this->getField('format_date')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow7);
				$oShopTabFormats->move($this->getField('format_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow7);

				$oShopTabFormats->move($this->getField('typograph_default_items')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow8);
				$oShopTabFormats->move($this->getField('typograph_default_groups')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabFormatsRow8);

				$oShopTabExport->move($this->getField('guid')->divAttr(array('class' => 'form-group col-xs-12')),$oGuidRow);
				$oShopTabExport->move($this->getField('yandex_market_name')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-6')),$oShopTabExportRow1);
				$oShopTabExport->move($this->getField('yandex_market_sales_notes_default')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-6')),$oShopTabExportRow1);
				$oShopTabExport->move($this->getField('adult')->divAttr(array('class' => 'form-group col-xs-12')),$oShopTabExportRow2);

				$oShop_Item_Delivery_Option_Controller_Tab = new Shop_Item_Delivery_Option_Controller_Tab($this->_Admin_Form_Controller);

				$oDeliveryOption = $oShop_Item_Delivery_Option_Controller_Tab
					->shop_id($this->_object->id)
					->execute();

				$oYandexMarketBlock->add($oDeliveryOption);

				$watermarkPath =
					is_file($this->_object->getWatermarkFilePath())
					? $this->_object->getWatermarkFileHref()
					: '';

				$sFormPath = $this->_Admin_Form_Controller->getPath();
				$windowId = $this->_Admin_Form_Controller->getWindowId();

				$oShopTabWatermarkRow1->add(Admin_Form_Entity::factory('File')
					->type("file")
					->caption(Core::_('Shop.watermark_file'))
					->divAttr(array('class' => 'form-group col-xs-12'))
					->name("watermark_file")
					->id("watermark_file")
					->largeImage(
						array(
							'path' => $watermarkPath,
							'show_params' => FALSE,
							'delete_onclick' => "$.adminLoad({path: '{$sFormPath}', additionalParams: 'hostcms[checked][{$this->_datasetId}][{$this->_object->id}]=1', action: 'deleteWatermarkFile', windowId: '{$windowId}'}); return false",
						)
					)
					->smallImage(
						array(
							'show' => FALSE
						)
					));

				$oShopTabWatermark->move($this->getField('preserve_aspect_ratio')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabWatermarkRow2);
				$oShopTabWatermark->move($this->getField('preserve_aspect_ratio_small')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabWatermarkRow2);

				$oShopTabWatermark->move($this->getField('preserve_aspect_ratio_group')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabWatermarkRow3);
				$oShopTabWatermark->move($this->getField('preserve_aspect_ratio_group_small')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabWatermarkRow3);

				$oShopTabWatermark->move($this->getField('watermark_default_use_large_image')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabWatermarkRow4);
				$oShopTabWatermark->move($this->getField('watermark_default_use_small_image')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabWatermarkRow4);

				$oShopTabWatermark->move($this->getField('watermark_default_position_x')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabWatermarkRow5);
				$oShopTabWatermark->move($this->getField('watermark_default_position_y')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6')),$oShopTabWatermarkRow5);

				// Добавляем поле сортировки товара
				$oShopTabOrdersRow1->add(Admin_Form_Entity::factory('Select')
					->name('items_sorting_field')
					->caption(Core::_('Shop.items_sorting_field'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					->options(
						array(
							Core::_('Shop.sort_by_date'),
							Core::_('Shop.sort_by_name'),
							Core::_('Shop.sort_by_order')
						)
					)
					->value($this->_object->items_sorting_field));


				// Добавляем направление сортировки товара
				$oShopTabOrdersRow1->add(Admin_Form_Entity::factory('Select')
					->name('items_sorting_direction')
					->caption(Core::_('Shop.items_sorting_direction'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					->options(
						array
						(
							Core::_('Shop.sort_to_increase'),
							Core::_('Shop.sort_to_decrease')
						)
					)
					->value($this->_object->items_sorting_direction));

				// Добавляем поле сортировки групп
				$oShopTabOrdersRow2->add(Admin_Form_Entity::factory('Select')
					->name('groups_sorting_field')
					->caption(Core::_('Shop.groups_sorting_field'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					->options(
						array
						(
							Core::_('Shop.sort_by_name'),
							Core::_('Shop.sort_by_order'),
						)
					)
					->value($this->_object->groups_sorting_field));

				// Добавляем направление сортировки групп
				$oShopTabOrdersRow2->add(Admin_Form_Entity::factory('Select')
					->name('groups_sorting_direction')
					->caption(Core::_('Shop.groups_sorting_direction'))
					->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
					->options(
						array(
							Core::_('Shop.sort_to_increase'),
							Core::_('Shop.sort_to_decrease')
						)
					)
					->value($this->_object->groups_sorting_direction));

			break;
		}

		$this->title($title);

		return $this;
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
			$modelName = $this->_object->getModelName();

			if ($modelName == 'shop')
			{
				$oShop = Core_Entity::factory('Shop');

				$iStructureId = intval(Core_Array::get($this->_formValues, 'structure_id'));

				$oShop->queryBuilder()
					->where('shops.structure_id', '=', $iStructureId);

				$aShop = $oShop->findAll();

				$iCount = count($aShop);

				if ($iStructureId
					&& $iCount
					&& (!$this->_object->id || $iCount > 1 || $aShop[0]->id != $this->_object->id)
				)
				{
					$oStructure = Core_Entity::factory('Structure', $iStructureId);

					$this->addMessage(
						Core_Message::get(
							Core::_('Shop.structureIsExist', $oStructure->name),
							'error'
						)
					);

					return TRUE;
				}
			}
		}

		return parent::execute($operation);
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @hostcms-event Shop_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		parent::_applyObjectProperty();

		if (Core::moduleIsActive('notification'))
		{
			$oModule = Core::$modulesList['shop'];

			$aRecievedNotificationSubscribers = Core_Array::getPost('notification_subscribers', array());
			!is_array($aRecievedNotificationSubscribers) && $aRecievedNotificationSubscribers = array();

			$aTmp = array();

			// Выбранные сотрудники
			$oNotification_Subscribers = Core_Entity::factory('Notification_Subscriber');
			$oNotification_Subscribers->queryBuilder()
				->where('notification_subscribers.module_id', '=', $oModule->id)
				->where('notification_subscribers.type', '=', 0)
				->where('notification_subscribers.entity_id', '=', $this->_object->id)
				;

			$aNotification_Subscribers = $oNotification_Subscribers->findAll(FALSE);

			foreach ($aNotification_Subscribers as $oNotification_Subscriber)
			{
				!in_array($oNotification_Subscriber->user_id, $aRecievedNotificationSubscribers)
					? $oNotification_Subscriber->delete()
					: $aTmp[] = $oNotification_Subscriber->user_id;
			}

			$aNewRecievedNotificationSubscribers = array_diff($aRecievedNotificationSubscribers, $aTmp);

			foreach ($aRecievedNotificationSubscribers as $user_id)
			{
				$oNotification_Subscribers = Core_Entity::factory('Notification_Subscriber');
				$oNotification_Subscribers->queryBuilder()
					->where('notification_subscribers.module_id', '=', $oModule->id)
					->where('notification_subscribers.user_id', '=', intval($user_id))
					->where('notification_subscribers.entity_id', '=', $this->_object->id)
					;

				$iCount = $oNotification_Subscribers->getCount();

				if (!$iCount)
				{
					$oNotification_Subscriber = Core_Entity::factory('Notification_Subscriber');
					$oNotification_Subscriber
						->module_id($oModule->id)
						->type(0)
						->entity_id($this->_object->id)
						->user_id($user_id)
						->save();
				}
			}
		}

		if(
			// Поле файла существует
			!is_null($aFileData = Core_Array::getFiles('watermark_file', NULL))
			// и передан файл
			&& intval($aFileData['size']) > 0)
		{
			if (Core_File::isValidExtension($aFileData['name'], array('png')))
			{
				$this->_object->saveWatermarkFile($aFileData['tmp_name']);
			}
			else
			{
				$this->addMessage(
					Core_Message::get(
						Core::_('Core.extension_does_not_allow', Core_File::getExtension($aFileData['name'])),
						'error'
					)
				);
			}
		}

		//Яндекс.Маркет доставка
		$oShop_Item_Delivery_Option_Controller_Tab = new Shop_Item_Delivery_Option_Controller_Tab($this->_Admin_Form_Controller);
		$oShop_Item_Delivery_Option_Controller_Tab
			->shop_id($this->_object->id)
			->applyObjectProperty();

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));
	}

	/**
	 * Get currency array
	 * @return array
	 */
	public function fillCurrencies()
	{
		$oCurrency = Core_Entity::factory('Shop_Currency');

		$oCurrency->queryBuilder()
			->orderBy('sorting')
			->orderBy('name');

		//$aCurrencyArray = array(' … ');
		$aCurrencyArray = array();

		$aCurrencies = $oCurrency->findAll();
		foreach($aCurrencies as $oCurrency)
		{
			$aCurrencyArray[$oCurrency->id] = $oCurrency->name;
		}

		return $aCurrencyArray;
	}

	/**
	 * Get order statuses array
	 * @return array
	 */
	public function fillOrderStatuses()
	{
		$oOrderStatus = Core_Entity::factory('Shop_Order_Status');

		$oOrderStatus->queryBuilder()
			->orderBy('name');

		$aOrderStatusArray = array(' … ');

		$aOrderStatuses = $oOrderStatus->findAll();
		foreach($aOrderStatuses as $oOrderStatus)
		{
			$aOrderStatusArray[$oOrderStatus->id] = $oOrderStatus->name;
		}

		return $aOrderStatusArray;
	}

	/**
	 * Get measures array
	 * @return array
	 */
	public function fillMeasures()
	{
		$oMeasure = Core_Entity::factory('Shop_Measure');

		$oMeasure->queryBuilder()
			->orderBy('name');

		$aMeasures = $oMeasure->findAll();

		$aMeasureArray = array(' … ');

		foreach($aMeasures as $oMeasure)
		{
			$aMeasureArray[$oMeasure->id] = $oMeasure->name;
		}

		return $aMeasureArray;
	}

	/**
	 * Get countries array
	 * @return array
	 */
	public function fillCountries()
	{
		$oCountry = Core_Entity::factory('Shop_Country');

		$oCountry->queryBuilder()
			->orderBy('sorting')
			->orderBy('name');

		$aCountries = $oCountry->findAll();

		$aCountryArray = array(' … ');

		foreach($aCountries as $oCountry)
		{
			$aCountryArray[$oCountry->id] = $oCountry->name;
		}

		return $aCountryArray;
	}

	/**
	 * Get country locations
	 * @param int $iCountryId country ID
	 * @return array
	 */
	public function fillCountryLocations($iCountryId)
	{
		$iCountryId = intval($iCountryId);

		$oCountryLocation = Core_Entity::factory('Shop_Country_Location');

		$oCountryLocation->queryBuilder()
			->where('shop_country_id', '=', $iCountryId)
			->orderBy('sorting')
			->orderBy('name');

		$oCountryLocations = $oCountryLocation->findAll();

		$aCountryLocationArray = array(' … ');

		foreach($oCountryLocations as $oCountryLocation)
		{
			$aCountryLocationArray[$oCountryLocation->id] = $oCountryLocation->name;
		}

		return $aCountryLocationArray;
	}

	/**
	 * Get location cities
	 * @param int $iCountryLocationId location ID
	 * @return array
	 */
	public function fillCountryLocationCities($iCountryLocationId)
	{
		$iCountryLocationId = intval($iCountryLocationId);

		$oCountryLocationCity = Core_Entity::factory('Shop_Country_Location_City');

		$oCountryLocationCity->queryBuilder()
			->where('shop_country_location_id', '=', $iCountryLocationId)
			->orderBy('sorting')
			->orderBy('name');

		$oCountryLocationCities = $oCountryLocationCity->findAll();

		$aCountryLocationCityArray = array(' … ');

		foreach($oCountryLocationCities as $oCountryLocationCity)
		{
			$aCountryLocationCityArray[$oCountryLocationCity->id] = $oCountryLocationCity->name;
		}

		return $aCountryLocationCityArray;
	}

	/**
	 * Get city areas
	 * @param int $iCountryLocationCityId city ID
	 * @return array
	 */
	public function fillCountryLocationCityAreas($iCountryLocationCityId)
	{
		$iCountryLocationCityId = intval($iCountryLocationCityId);

		$oCountryLocationCityArea = Core_Entity::factory('Shop_Country_Location_City_Area');

		$oCountryLocationCityArea->queryBuilder()
			->where('shop_country_location_city_id', '=', $iCountryLocationCityId)
			->orderBy('sorting')
			->orderBy('name');

		$oCountryLocationCityAreas = $oCountryLocationCityArea->findAll();

		$aCountryLocationCityAreaArray = array(' … ');

		foreach($oCountryLocationCityAreas as $oCountryLocationCityArea)
		{
			$aCountryLocationCityAreaArray[$oCountryLocationCityArea->id] = $oCountryLocationCityArea->name;
		}

		return $aCountryLocationCityAreaArray;
	}

	/**
	 * Get companies array
	 * @return array
	 */
	protected function _fillCompanies()
	{
		$oCompany = Core_Entity::factory('Shop_Company');

		$oCompany->queryBuilder()
			->orderBy('name');

		$aCompanies = $oCompany->findAll();

		$aCompanyArray = array(' … ');
		foreach($aCompanies as $oCompany)
		{
			$aCompanyArray[$oCompany->id] = $oCompany->name;
		}

		return $aCompanyArray;
	}

	/**
	 * Create visual tree of the directories
	 * @param int $iShopDirParentId parent directory ID
	 * @param boolean $bExclude exclude group ID
	 * @param int $iLevel current nesting level
	 * @return array
	 */
	protected function _fillShopDir($iShopDirParentId = 0, $bExclude = FALSE, $iLevel = 0)
	{
		$iShopDirParentId = intval($iShopDirParentId);

		$iLevel = intval($iLevel);

		$oShopDir = Core_Entity::factory('Shop_Dir', $iShopDirParentId);

		$aResult = array();

		$aChildrenDirs = $oShopDir->Shop_Dirs;
		$aChildrenDirs->queryBuilder()
			->where('site_id', '=', CURRENT_SITE);

		$aChildrenDirs = $aChildrenDirs->findAll();

		foreach ($aChildrenDirs as $oChildrenDir)
		{
			if ($bExclude != $oChildrenDir->id)
			{
				$aResult[$oChildrenDir->id] = str_repeat('  ', $iLevel) . $oChildrenDir->name;

				$aResult += $this->_fillShopDir($oChildrenDir->id, $bExclude, $iLevel+1);
			}
		}

		return $aResult;
	}

	/**
	 * Fill list of shops for site
	 * @param int $iSiteId site ID
	 * @return array
	 */
	public function fillShops($iSiteId)
	{
		$iSiteId = intval($iSiteId);

		$aReturn = array();

		$aObjects = Core_Entity::factory('Site', $iSiteId)->Shops->findAll();
		foreach ($aObjects as $oObject)
		{
			$aReturn[$oObject->id] = $oObject->name;
		}

		return $aReturn;
	}

	/**
	 * Fill notification subscribers list
	 * @param Shop_Model $oShop shop
	 * @return array
	 */
	protected function _fillNotificationSubscribersList()
	{
		$aReturnArray = array();

		$oModule = Core::$modulesList['shop'];

		$oNotification_Subscribers = Core_Entity::factory('Notification_Subscriber');
		$oNotification_Subscribers->queryBuilder()
			->where('notification_subscribers.module_id', '=', $oModule->id)
			->where('notification_subscribers.type', '=', 0)
			->where('notification_subscribers.entity_id', '=', $this->_object->id)
			;

		$aNotification_Subscribers = $oNotification_Subscribers->findAll(FALSE);

		foreach ($aNotification_Subscribers as $oNotification_Subscriber)
		{
			$oUser = $oNotification_Subscriber->User;

			$aReturnArray[$oUser->id] = array(
				'value' => $oUser->login . ' [' . $oUser->id . ']',
				'attr' => array('selected' => 'selected')
			);
		}

		return $aReturnArray;
	}
}