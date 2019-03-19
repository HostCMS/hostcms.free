<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Order Backend Editing Controller.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2019 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Order_Controller_Edit extends Admin_Form_Action_Controller_Type_Edit
{
	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		$this
			->addSkipColumn('unloaded');

		if (!$object->id)
		{
			$object->shop_id = Core_Array::getGet('shop_id');
		}

		parent::setObject($object);

		$objectId = intval($this->_object->id);
		$windowId = $this->_Admin_Form_Controller->getWindowId();
		$Shop_Controller_Edit = new Shop_Controller_Edit($this->_Admin_Form_Action);

		$oMainTab = $this->getTab('main');
		$oAdditionalTab = $this->getTab('additional');

		$this
			->addTabAfter(
					$oDescriptionTab = Admin_Form_Entity::factory('Tab')
						->caption(Core::_('Shop_Order.tab3'))
						->name('Description'), $oMainTab
				)
			->addTabAfter(
					$oContactsTab = Admin_Form_Entity::factory('Tab')
						->caption(Core::_('Shop_Order.tab2'))
						->name('Contacts'), $oMainTab
				)
			->addTabAfter(
					$oDocumentsTab = Admin_Form_Entity::factory('Tab')
						->caption(Core::_('Shop_Order.tab4'))
						->name('Documents'), $oContactsTab
				);

		// Order tags
		if ($this->_object->source_id)
		{
			$this->addTabAfter(
				$oTagTab = Admin_Form_Entity::factory('Tab')
					->caption(Core::_('Shop_Order.tab5'))
					->name('Tags'), $oContactsTab
			);

			$oSource = $this->_object->Source;

			$oTagTab
				->add($oTagRow1 = Admin_Form_Entity::factory('Div')->class('row'));

			$aSourceFields = array('service', 'campaign', 'ad', 'source', 'medium', 'content', 'term');

			foreach ($aSourceFields as $sFieldName)
			{
				$oAdmin_Form_Entity_Input = Admin_Form_Entity::factory('Input')
					->name('source_' . $sFieldName)
					->divAttr(array('class' => 'form-group col-xs-4'))
					->caption(Core::_('Source.' . $sFieldName))
					->class('form-control input-group-input')
					->disabled('disabled')
					->value($oSource->$sFieldName);

				$oTagRow1->add($oAdmin_Form_Entity_Input);
			}
		}

		$Shop_Delivery_Condition_Controller_Edit = new Shop_Delivery_Condition_Controller_Edit($this->_Admin_Form_Action);

		$Shop_Delivery_Condition_Controller_Edit->controller($this->_Admin_Form_Controller);

		$oMainTab
			->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow6 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow7 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oMainRow8 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oContactsTab
			->add($oContactsTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oContactsTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oContactsTabRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oContactsTabRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oContactsTabRow5 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oContactsTabRow6 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oDocumentsTab
			->add($oDocumentsTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oDocumentsTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oDocumentsTabRow3 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oDocumentsTabRow4 = Admin_Form_Entity::factory('Div')->class('row'))
			// ->add($oDocumentsTabRow5 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oDescriptionTab
			->add($oDescriptionTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oDescriptionTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oDescriptionTabRow3 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oAdditionalTab
			->add($oAdditionalTabRow1 = Admin_Form_Entity::factory('Div')->class('row'))
			->add($oAdditionalTabRow2 = Admin_Form_Entity::factory('Div')->class('row'))
		;

		$oMainTab->move($this->getField('postcode')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oContactsTabRow3);
		$oMainTab->move($this->getField('address')->divAttr(array('class' => 'form-group col-xs-12 col-sm-5')), $oContactsTabRow3);
		$oMainTab->move($this->getField('house')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oContactsTabRow3);
		$oMainTab->move($this->getField('flat')->divAttr(array('class' => 'form-group col-xs-12 col-sm-2')), $oContactsTabRow3);

		$oMainTab->move($this->getField('surname')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oContactsTabRow4);
		$oMainTab->move($this->getField('name')->class('form-control')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oContactsTabRow4);
		$oMainTab->move($this->getField('patronymic')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oContactsTabRow4);
		$oMainTab->move($this->getField('company')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oContactsTabRow4);

		$oMainTab->move($this->getField('phone')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4')), $oContactsTabRow5);
		$oMainTab->move($this->getField('fax')->divAttr(array('class' => 'form-group col-xs-6 col-sm-4')), $oContactsTabRow5);
		$oMainTab->move($this->getField('email')->divAttr(array('class' => 'form-group col-xs-12 col-sm-4')), $oContactsTabRow5);

		$oMainTab->move($this->getField('tin')->divAttr(array('class' => 'form-group col-xs-6')), $oContactsTabRow6);
		$oMainTab->move($this->getField('kpp')->divAttr(array('class' => 'form-group col-xs-6')), $oContactsTabRow6);

		$oMainTab->move($this->getField('guid'), $oAdditionalTab);
		$oAdditionalTab->move($this->getField('shop_id')->divAttr(array('class' => 'form-group col-xs-12')), $oAdditionalTabRow1);
		$oAdditionalTab->move($this->getField('guid')->divAttr(array('class' => 'form-group col-xs-12')), $oAdditionalTabRow2);

		$oMainTab->move($this->getField('invoice')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow1);
		$oMainTab->move($this->getField('datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow1);

		$oAdditionalTab->delete($this->getField('siteuser_id'));

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser = $this->_object->Siteuser;

			$options = !is_null($oSiteuser->id)
				? array($oSiteuser->id => $oSiteuser->login . ' [' . $oSiteuser->id . ']')
				: array(0);

			$oSiteuserSelect = Admin_Form_Entity::factory('Select')
				->caption(Core::_('Shop_Order.siteuser_id'))
				->id('object_siteuser_id')
				->options($options)
				->name('siteuser_id')
				->class('siteuser-tag')
				->style('width: 100%')
				// ->divAttr(array('class' => 'form-group col-xs-12'));
				->divAttr(array('class' => 'col-xs-12'));

			$oMainRow1
				->add(
					Admin_Form_Entity::factory('Div')
						->class('form-group col-xs-12 col-sm-6 col-md-3 no-padding')
						->add($oSiteuserSelect)
				);

			// Show button
			Siteuser_Controller_Edit::addSiteuserSelect2($oSiteuserSelect, $oSiteuser, $this->_Admin_Form_Controller);
		}

		/*$oMainRow2
			->add(
				Admin_Form_Entity::factory('Input')
					->name('sum')
					->id('sum')
					->value($this->_object->getAmount())
					->readonly('readonly')
					->caption(Core::_("Shop_Order.cond_of_delivery_add_form_price_order"))
					->divAttr(array('class' => 'form-group col-xs-6 col-sm-3'))
			)
			->add(
				Admin_Form_Entity::factory('Select')
					->caption(Core::_('Shop_Order.order_currency'))
					->divAttr(array('class' => 'form-group col-xs-6 col-sm-3'))
					->options(
						$Shop_Controller_Edit->fillCurrencies()
					)
					->name('shop_currency_id')
					->value($this->_object->shop_currency_id)
			);*/

		$oDiv_Amount = Admin_Form_Entity::factory('Div')
			->class('form-group col-xs-12 col-sm-6 col-md-3 deal-amount')
			->add(Admin_Form_Entity::factory('Input')
				->name('sum')
				->id('sum')
				->value($this->_object->getAmount())
				->readonly('readonly')
				->caption(Core::_("Shop_Order.cond_of_delivery_add_form_price_order"))
				->divAttr(array('class' => ''))
			)
			->add(
				Admin_Form_Entity::factory('Select')
					->class('form-control no-padding-left no-padding-right')
					// ->caption(Core::_('Shop_Order.order_currency'))
					->divAttr(array('class' => ''))
					->options(
						$Shop_Controller_Edit->fillCurrencies()
					)
					->name('shop_currency_id')
					->value($this->_object->shop_currency_id)
			);

		$oMainRow2->add($oDiv_Amount);

		$shop_group_id = Core_Array::getGet('shop_group_id', 0);
		$shop_dir_id = Core_Array::getGet('shop_dir_id', 0);
		$shop_order_id = intval($this->_object->id);
		$shop_id = Core_Array::getGet('shop_id', 0);

		$sShopOrderItemsPath = '/admin/shop/order/item/index.php';
		$sAdditionalParams = "shop_id={$shop_id}&shop_group_id={$shop_group_id}&shop_dir_id={$shop_dir_id}&shop_order_id={$shop_order_id}";

		$oItemsLink = Admin_Form_Entity::factory('Link');
		$oItemsLink
			->divAttr(array('class' => 'large-link margin-top-21 form-group col-xs-12 col-sm-3'))
			->a
				->class('btn btn-labeled btn-success')
				->href($this->_Admin_Form_Controller->getAdminLoadHref(
						$sShopOrderItemsPath, NULL, NULL, $sAdditionalParams
					)
				)
				->onclick($this->_Admin_Form_Controller->getAdminLoadAjax(
						$sShopOrderItemsPath, NULL, NULL, $sAdditionalParams
					))
				->value(Core::_('Shop_Order.order_items_link'));
		$oItemsLink
			->icon
				->class('btn-label fa fa-list');

		$oMainRow2->add($oItemsLink);

		$oMainTab->move($this->getField('coupon')->divAttr(array('class' => 'form-group col-xs-12 col-sm-3')), $oMainRow2);

		// Add checkbox
		$oSendMailField = Admin_Form_Entity::factory('Checkbox')
			->caption(Core::_('Shop_Order.send_mail'))
			->value(0)
			->name('send_mail')
			->divAttr(array('class' => 'form-group col-xs-12 col-md-3 margin-top-21'));

		$oMainRow2->add($oSendMailField);

		$oMainTab->move($this->getField('paid')->divAttr(
				array('class' => 'form-group col-xs-12 col-sm-3 margin-top-21')
			), $oMainRow3);
		$oMainTab->move($this->getField('canceled')->divAttr(
				array('class' => 'form-group col-xs-12 col-sm-3 margin-top-21')
			), $oMainRow3);

		$oMainRow3->add(
			Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3'))
				->caption(Core::_('Shop_Order.system_of_pay'))
				->options(
					$this->_fillPaymentSystems(Core_Array::getGet('shop_id', 0))
				)
				->name('shop_payment_system_id')
				->value($this->_object->shop_payment_system_id)
		);

		$oMainTab->move($this->getField('payment_datetime')->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow3);

		$oMainRow4->add(
			Admin_Form_Entity::factory('Select')
				->caption(Core::_('Shop_Order.show_order_status'))
				->options(
					$Shop_Controller_Edit->fillOrderStatuses(Core_Array::getGet('shop_id', 0))
				)
				->name('shop_order_status_id')
				->value($this->_object->shop_order_status_id)
				->onchange("$.changeOrderStatus('{$windowId}')")
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3'))
		);

		$oMainTab->move($this->getField('status_datetime')
			->id('status_datetime')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow4);


		$aTmpCompanies = array(" … ");
		$aCompanies = $object->Shop->Site->Companies->findAll();
		foreach ($aCompanies as $oCompany)
		{
			$aTmpCompanies[$oCompany->id] = $oCompany->name;
		}

		$oMainRow4->add(
			Admin_Form_Entity::factory('Select')
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3'))
				->caption(Core::_('Shop_Order.company_id'))
				->options($aTmpCompanies)
				->name('company_id')
				->value($this->_object->company_id)
		);

		$oMainTab->move($this->getField('ip')
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6 col-md-3')), $oMainRow4);

		$this->getField('status_datetime');

		$oMainTab->move($this->getField('description')->divAttr(array('class' => 'form-group col-xs-12')), $oDescriptionTabRow1);
		$oMainTab->move($this->getField('system_information')->divAttr(array('class' => 'form-group col-xs-12')), $oDescriptionTabRow2);
		$oMainTab->move($this->getField('delivery_information')->divAttr(array('class' => 'form-group col-xs-12')), $oDescriptionTabRow3);

		$oAdditionalTab->delete($this->getField('shop_currency_id'));

		$oAdditionalTab->delete($this->getField('shop_payment_system_id'))
			->delete($this->getField('shop_country_id'))
			->delete($this->getField('company_id'));

		// Создаем поле стран как выпадающий список
		$CountriesSelectField = Admin_Form_Entity::factory('Select')
			->name('shop_country_id')
			->caption(Core::_('Shop_Delivery_Condition.shop_country_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->options(
				$Shop_Controller_Edit->fillCountries()
			)
			->value($this->_object->shop_country_id)
			->onchange("$('#{$windowId} #list4').clearSelect();$('#{$windowId} #list3').clearSelect();$.ajaxRequest({path: '". $this->_Admin_Form_Controller->getPath() ."',context: 'list2', callBack: $.loadSelectOptionsCallback, objectId: {$objectId}, action: 'loadList2',additionalParams: 'list_id=' + this.value,windowId: '{$windowId}'}); return false");
		$oContactsTabRow1->add($CountriesSelectField);

		// Удаляем местоположения
		$oAdditionalTab->delete(
			$this->getField('shop_country_location_id')
		);

		// Создаем поле местоположений как выпадающий список
		$CountryLocationsSelectField = Admin_Form_Entity::factory('Select')
			->name('shop_country_location_id')
			->id('list2')
			->caption(Core::_('Shop_Delivery_Condition.shop_country_location_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->options(
					$Shop_Controller_Edit->fillCountryLocations($this->_object->shop_country_id)
				)
			->value($this->_object->shop_country_location_id)
			->onchange("$('#{$windowId} #list4').clearSelect();$.ajaxRequest({path: '". $this->_Admin_Form_Controller->getPath() ."',context: 'list3', callBack: $.loadSelectOptionsCallback, objectId: {$objectId}, action: 'loadList3',additionalParams: 'list_id=' + this.value,windowId: '{$windowId}'}); return false");
		$oContactsTabRow1->add($CountryLocationsSelectField);

		// Удаляем города
		$oAdditionalTab->delete(
			$this->getField('shop_country_location_city_id')
		);

		// Создаем поле городов как выпадающий список
		$CountryLocationCitiesSelectField = Admin_Form_Entity::factory('Select')
			->name('shop_country_location_city_id')
			->id('list3')
			->caption(Core::_('Shop_Delivery_Condition.shop_country_location_city_id'))
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
			->options(
					$Shop_Controller_Edit->fillCountryLocationCities($this->_object->shop_country_location_id)
				)
			->value($this->_object->shop_country_location_city_id)
			->onchange("$.ajaxRequest({path: '". $this->_Admin_Form_Controller->getPath() ."',context: 'list4', callBack: $.loadSelectOptionsCallback, objectId: {$objectId}, action: 'loadList4',additionalParams: 'list_id=' + this.value,windowId: '{$windowId}'}); return false");
		$oContactsTabRow2->add($CountryLocationCitiesSelectField);

		// Удаляем районы
		$oAdditionalTab->delete(
			$this->getField('shop_country_location_city_area_id')
		);

		// Создаем поле районов как выпадающий список
		$oContactsTabRow2->add(
			Admin_Form_Entity::factory('Select')
				->name('shop_country_location_city_area_id')
				->id('list4')
				->caption(Core::_('Shop_Delivery_Condition.shop_country_location_city_area_id'))
				->divAttr(array('class' => 'form-group col-xs-12 col-sm-6'))
				->options(
					$Shop_Controller_Edit->fillCountryLocationCityAreas($this->_object->shop_country_location_city_id)
				)
				->value($this->_object->shop_country_location_city_area_id)
		);

		$Shop_Delivery_Controller_Edit = new Shop_Delivery_Controller_Edit($this->_Admin_Form_Action);

		$oShopDelivery = Core_Entity::factory('Shop_Delivery_Condition', $this->_object->shop_delivery_condition_id)->Shop_Delivery;

		$oAdditionalTab->delete($this->getField('shop_delivery_id'));

		$oShopDeliveryTypeSelect = Admin_Form_Entity::factory('Select')
			->caption(Core::_('Shop_Order.type_of_delivery'))
			->options(
				$Shop_Delivery_Controller_Edit->fillDeliveries(Core_Array::getGet('shop_id', 0))
			)
			->name('shop_delivery_id')
			->id('shop_delivery_id')
			->value($this->_object->shop_delivery_id)
			->onchange("$.ajaxRequest({path: '/admin/shop/order/index.php',context: 'shop_delivery_condition_id', callBack: $.loadSelectOptionsCallback, objectId: {$objectId}, action: 'loadDeliveryConditionsList',additionalParams: 'delivery_id=' + this.value,windowId: '{$windowId}'}); return false")
			->divAttr(array('class' => 'form-group col-xs-6 col-sm-6 col-md-3'));

		$oMainRow6->add($oShopDeliveryTypeSelect);

		$oAdditionalTab->delete(
			$this->getField('shop_delivery_condition_id')
		);

		$oMainRow6->add(
			Admin_Form_Entity::factory('Select')
				->caption(Core::_('Shop_Order.shop_delivery_condition_id'))
				->id('shop_delivery_condition_id')
				->options(
					$oShopDelivery->Shop_Delivery_Conditions->getCount() <= 250
						? $this->_fillDeliveryConditions($oShopDelivery->id)
						: array($this->_object->shop_delivery_condition_id => $this->_object->Shop_Delivery_Condition->name)
				)
				->name('shop_delivery_condition_id')
				->value($this->_object->shop_delivery_condition_id)
				->divAttr(array('class' => 'form-group col-xs-6 col-sm-6 col-md-3'))
		);

		$iOrderId = intval($this->_object->id);
		$sOrderPath = '/admin/shop/order/index.php';

		$oRecalcDeliveryPriceLink = Admin_Form_Entity::factory('Link');
		$oRecalcDeliveryPriceLink
			->divAttr(array('class' => 'form-group col-xs-12 col-sm-3  margin-top-21'))
			->a
				->class('btn btn-default')
				->onclick(
					$this->_Admin_Form_Controller->getAdminSendForm('recalcDelivery')
				)
				->value(Core::_('Shop_Order.recalc_order_delivery_sum'));
		$oRecalcDeliveryPriceLink
			->icon
				->class('fa fa-truck');

		$oMainRow6->add($oRecalcDeliveryPriceLink);

		// Печать
		$printButton = '
			<div class="btn-group">
				<a class="btn btn-labeled btn-success" href="javascript:void(0);"><i class="btn-label fa fa-print"></i>' . Core::_('Printlayout.print') . '</a>
				<a class="btn btn-palegreen dropdown-toggle" data-toggle="dropdown" href="javascript:void(0);" aria-expanded="false"><i class="fa fa-angle-down"></i></a>
				<ul class="dropdown-menu dropdown-palegreen">
		';

		// Печать заказа
		$printLink = $this->_Admin_Form_Controller->getAdminLoadHref("/admin/shop/order/print/index.php", NULL, NULL, "shop_order_id=" . intval($this->_object->id));
		$printButton .= '<li>
			<a target="_blank" href="' . $printLink . '">' . Core::_('Shop_Order.print') . '</a>
		</li>';

		// Карточка заказка
		$orderCardLink = $this->_Admin_Form_Controller->getAdminLoadHref("/admin/shop/order/card/index.php", NULL, NULL, "shop_order_id=" . intval($this->_object->id));
		$printButton .= '<li>
			<a target="_blank" href="' . $orderCardLink . '">' . Core::_('Shop_Order.order_card') . '</a>
		</li>';

		$moduleName = $this->_Admin_Form_Controller->module->getModuleName();

		$oModule = Core_Entity::factory('Module')->getByPath($moduleName);

		if (!is_null($oModule))
		{
			$oShop = Core_Entity::factory('Shop', Core_Array::getGet('shop_id', 0));
			$oShop_Group = Core_Entity::factory('Shop_Group', Core_Array::getGet('shop_group_id', 0));

			$printButton .= Printlayout_Controller::getPrintButtonHtml($this->_Admin_Form_Controller, $oModule->id, 0, 'hostcms[checked][0][' . $this->_object->id . ']=1&shop_id=' . $oShop->id . '&shop_group_id=' . $oShop_Group->id);
		}

		$printButton .= '
				</ul>
			</div>
		';

		$oDocumentsTabRow1
			->add(Admin_Form_Entity::factory('Div')
				->class('form-group col-xs-12 col-sm-3')
				->add(
					Admin_Form_Entity::factory('Code')->html($printButton)
				)
		);

		// Печать
		/*$oPrintLink = Admin_Form_Entity::factory('Link');
		$oPrintLink
			->divAttr(array('class' => 'large-link form-group col-lg-12 col-md-12 col-sm-12'))
			->a
				->class('btn btn-labeled btn-danger')
				->href($this->_Admin_Form_Controller->getAdminLoadHref(
						"/admin/shop/order/print/index.php",
						NULL, NULL, "shop_order_id=" . intval($this->_object->id
					)))
				->value(Core::_('Shop_Order.print'));
		$oPrintLink
			->icon
				->class('btn-label fa fa-print');
		$oDocumentsTabRow1->add($oPrintLink);

		// Карточка заказка
		$oOrderCardLink = Admin_Form_Entity::factory('Link');
		$oOrderCardLink
			->divAttr(array('class' => 'large-link form-group col-lg-12 col-md-12 col-sm-12'))
			->a
				->class('btn btn-labeled btn-warning')
				->href(
					$this->_Admin_Form_Controller->getAdminLoadHref(
						"/admin/shop/order/card/index.php",
						NULL, NULL, "shop_order_id=" . intval($this->_object->id
					)))
				->value(Core::_('Shop_Order.order_card'))
				->target('_blank');
		$oOrderCardLink
			->icon
				->class('btn-label fa fa-print');

		$oDocumentsTabRow2->add($oOrderCardLink);*/

		$oMainTab->delete($this->getField('acceptance_report'));
		$oMainTab->delete($this->getField('acceptance_report_datetime'));

		$oAdmin_Form_Entity_Input = Admin_Form_Entity::factory('Input')
			->name('acceptance_report')
			->divAttr(array('class' => 'form-group col-lg-2 col-md-4 col-sm-6 col-xs-6'))
			->caption(Core::_('Shop_Order.document_number'))
			//->class('form-control input-group-input')
			->value($this->_object->acceptance_report);

		$oAdmin_Form_Entity_Datetime = Admin_Form_Entity::factory('Datetime')
			->name('acceptance_report_datetime')
			->divAttr(array('class' => 'form-group col-lg-4 col-md-4 col-sm-6 col-xs-6'))
			->caption(Core::_('Shop_Order.document_datetime'))
			->value($this->_object->acceptance_report_datetime);

		$oDocumentsTabRow2
			->add($oAdmin_Form_Entity_Input)
			->add($oAdmin_Form_Entity_Datetime);

		$oMainTab->delete($this->getField('vat_invoice'));
		$oMainTab->delete($this->getField('vat_invoice_datetime'));

		$oAdmin_Form_Entity_Input = Admin_Form_Entity::factory('Input');
		$oAdmin_Form_Entity_Input
			->name('vat_invoice')
			->divAttr(array('class' => 'form-group col-lg-2 col-md-4 col-sm-6 col-xs-6'))
			->caption(Core::_('Shop_Order.vat_number'))
			//->class('form-control input-group-input')
			->value($this->_object->vat_invoice);

		$oAdmin_Form_Entity_Vat_Datetime = Admin_Form_Entity::factory('Datetime')
			->name('vat_invoice_datetime')
			->divAttr(array('class' => 'form-group col-lg-4 col-md-4 col-sm-6 col-xs-6'))
			->caption(Core::_('Shop_Order.vat_datetime'))
			->value($this->_object->vat_invoice_datetime);

		$oDocumentsTabRow3
			->add($oAdmin_Form_Entity_Input)
			->add($oAdmin_Form_Entity_Vat_Datetime);

		// $oAdmin_Form_Controller = $this->_Admin_Form_Controller

		$oAdmin_Form_Entity_Div_Print_Form_Buttons = Admin_Form_Entity::factory('Div')
			->class('form-group col-xs-12 backend-print-forms')
			// ->add($oAdmin_Form_Entity_Menus)
		;

		if ($object->id)
		{
			$aColors = array(
				'btn-success',
				'btn-info',
				'btn-danger',
				'btn-warning',
				'btn-maroon',
			);

			$countColors = count($aColors);

			$oShop_Print_Forms = Core_Entity::factory('Shop_Print_Form');
			$oShop_Print_Forms->queryBuilder()
				->where('shop_print_forms.shop_id', '=', $shop_id)
				->where('shop_print_forms.active', '=', 1)
				->clearOrderBy()
				->orderBy('shop_print_forms.sorting', 'ASC');

			$aShop_Print_Forms = $oShop_Print_Forms->findAll(FALSE);

			foreach ($aShop_Print_Forms as $key => $oShop_Print_Form)
			{
				$index = $key % $countColors;

				$additionalParams = "shop_print_form_id={$oShop_Print_Form->id}&shop_order_id={$shop_order_id}";

				$oAdmin_Form_Entity_Div_Print_Form_Buttons
					->add(
						Core::factory('Core_Html_Entity_A')
							->class('btn btn-labeled ' . $aColors[$index])
							->href($this->_Admin_Form_Controller->getAdminLoadHref("/admin/shop/order/index.php", NULL, NULL, $additionalParams))
							->target('_blank')
							->add(
								Core::factory('Core_Html_Entity_I')
									->class("btn-label fa fa-print")
							)
							->add(
								Core::factory('Core_Html_Entity_Code')
									->value(htmlspecialchars($oShop_Print_Form->name))
							)
					);
			}

			$oDocumentsTabRow4->add($oAdmin_Form_Entity_Div_Print_Form_Buttons);
		}

		$oAdditionalTab->delete(
			$this->getField('shop_order_status_id')
		);

		$oPropertyTab = Admin_Form_Entity::factory('Tab')
			->caption(Core::_("Shop_Order.tab_properties"))
			->name('Property');

		$this->addTabBefore($oPropertyTab, $oAdditionalTab);

		// Properties
		Property_Controller_Tab::factory($this->_Admin_Form_Controller)
			->setObject($this->_object)
			->setDatasetId($this->getDatasetId())
			->linkedObject(Core_Entity::factory('Shop_Order_Property_List', $shop_id))
			->setTab($oPropertyTab)
			->template_id($this->_object->Shop->Structure->template_id
					? $this->_object->Shop->Structure->template_id
					: 0)
			->fillTab();

		$title = $this->_object->id
			? Core::_('Shop_Order.order_edit_form_title', $this->_object->invoice)
			: Core::_('Shop_Order.order_add_form_title');

		$this->title($title);

		//Core_Event::notify(get_class($this) . '.onAfterRedeclaredSetObject', $this, array());

		return $this;
	}

	/**
	 * Processing of the form. Apply object fields.
	 * @return self
	 * @hostcms-event Shop_Order_Controller_Edit.onAfterRedeclaredApplyObjectProperty
	 */
	protected function _applyObjectProperty()
	{
		$this->_formValues['siteuser_id'] = intval(Core_Array::get($this->_formValues, 'siteuser_id'));

		// Может измениться в parent::_applyObjectProperty()
		$bShop_payment_system_id = $this->_object->shop_payment_system_id;

		if ($bShop_payment_system_id)
		{
			$oShop_Payment_System_Handler = Shop_Payment_System_Handler::factory(
				Core_Entity::factory('Shop_Payment_System', $this->_object->shop_payment_system_id)
			);

			if ($oShop_Payment_System_Handler)
			{
				$oShop_Payment_System_Handler->shopOrder($this->_object)
					->shopOrderBeforeAction(clone $this->_object);
			}
			// HostCMS v. 5
			elseif (defined('USE_HOSTCMS_5') && USE_HOSTCMS_5)
			{
				$shop = new shop();
				$order_row = $shop->GetOrder($this->_object->id);
			}
		}

		// Payment datetime
		Core_Array::get($this->_formValues, 'paid')
			&& Core_Array::get($this->_formValues, 'payment_datetime') == ''
			&& $this->_formValues['payment_datetime'] = Core_Date::timestamp2sql(time());

		if ($this->_object->id)
		{
			// Change paid status
			if ($this->_object->paid != Core_Array::get($this->_formValues, 'paid', 0))
			{
				$this->_object->paid == 0
					? $this->_object->paid()
					: $this->_object->cancelPaid();
			}

			// Change canceled status
			if ($this->_object->canceled != Core_Array::get($this->_formValues, 'canceled', 0))
			{
				$this->_object->deleteReservedItems();
			}
		}

		//$this->_formValues['unloaded'] = 0;

		parent::_applyObjectProperty();

		$this->_object
			->unloaded(0)
			->save();

		// Properties
		Property_Controller_Tab::factory($this->_Admin_Form_Controller)
			->setObject($this->_object)
			->linkedObject(Core_Entity::factory('Shop_Order_Property_List', $this->_object->Shop->id))
			->applyObjectProperty();

		if ($this->_object->invoice == '')
		{
			// $this->_object->invoice = $this->_object->id;

			if (strlen($this->_object->Shop->invoice_template))
			{
				$oCore_Templater = new Core_Templater();
				$invoice = $oCore_Templater
					->addObject('shop', $this->_object->Shop)
					->addObject('this', $this->_object)
					->addFunction('ordersToday', array($this->_object, 'ordersToday'))
					->setTemplate($this->_object->Shop->invoice_template)
					->execute();
			}
			else
			{
				$invoice = $this->_object->id;
			}

			$this->_object->invoice = $invoice;
			$this->_object->save();
		}

		if ($bShop_payment_system_id)
		{
			if ($oShop_Payment_System_Handler)
			{
				$oShop_Payment_System_Handler->changedOrder('edit');
			}
			// HostCMS v. 5
			elseif (defined('USE_HOSTCMS_5') && USE_HOSTCMS_5)
			{
				// Вызываем обработчик платежной системы для события смены статуса HostCMS v. 5
				$shop->ExecSystemsOfPayChangeStatus($order_row['shop_system_of_pay_id'], array(
					'shop_order_id' => $this->_object->id,
					'action' => 'edit',
					// Предыдущие данные о заказе до редактирования
					'prev_order_row' => $order_row
				));
			}
		}

		if (Core_Array::get($this->_formValues, 'send_mail'))
		{
			try {
				// Send mail about order
				$this->_object->sendMail();
			} catch (Exception $e) {
				Core_Message::show($e->getMessage(), 'error');
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterRedeclaredApplyObjectProperty', $this, array($this->_Admin_Form_Controller));

		return $this;
	}

	/**
	 * Fill delivery conditions list
	 * @param int $iDeliveryId delivery ID
	 * @return array
	 */
	protected function _fillDeliveryConditions($iDeliveryId)
	{
		$oObject = Core_Entity::factory('Shop_Delivery_Condition');

		$iDeliveryId = intval($iDeliveryId);

		$oObject->queryBuilder()
			->where("shop_delivery_id", "=", $iDeliveryId)
			->orderBy("id");

		$aObjects = $oObject->findAll();

		$aReturn = array(" … ");

		foreach ($aObjects as $oObject)
		{
			$aReturn[$oObject->id] = $oObject->name;
		}

		return $aReturn;
	}

	/**
	 * Fill payment systems list
	 * @param int $iShopId shop ID
	 * @return array
	 */
	protected function _fillPaymentSystems($iShopId)
	{
		$iShopId = intval($iShopId);

		$oObject = Core_Entity::factory('Shop_Payment_System');
		$oObject->queryBuilder()
			->where('shop_id', '=', $iShopId)
			->orderBy('sorting');

		$aReturn = array(" … ");
		$aObjects = $oObject->findAll();
		foreach ($aObjects as $oObject)
		{
			$aReturn[$oObject->id] = array('value' => $oObject->name);
			!$oObject->active && $aReturn[$oObject->id]['attr'] = array(
				'style' => 'text-decoration: line-through'
			);
		}

		return $aReturn;
	}
}