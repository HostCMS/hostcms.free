<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Delivery_Condition_Controller_Import extends Admin_Form_Action_Controller_Type_Edit
{
	// execute operation may be NULL or doImport
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return boolean
	 */
	public function execute($operation = NULL)
	{
		switch ($operation)
		{
			default:
			case NULL:

			$this->_Admin_Form_Controller->title(
				$this->title
			);

			return $this->_showEditForm();

			case 'doImport':

			$aFileData = Core_Array::getFiles('csvfile', NULL);

			if(is_null($aFileData) || intval($aFileData['size']) == 0)
			{
				$this->addMessage(
					Core_Message::get(Core::_("Shop_Delivery_Condition.msg_import_file"), 'error')
				);

				return TRUE;
			}

			$oShopDelivery = Core_Entity::factory('Shop_Delivery', Core_Array::getGet('delivery_id', 0));

			$this->addMessage(
				Core_Message::get(Core::_("Shop_Delivery_Condition.import_delivery_result", $this->import($aFileData['tmp_name'], $oShopDelivery)))
			);

			return TRUE;
		}
	}

	/**
	 * Import
	 * @param string sCSVFilePath file path
	 * @param Shop_Delivery_Model $oShopDelivery delivery
	 * @return mixed
	 */
	public function import($sCSVFilePath, $oShopDelivery)
	{
		$counter = 0;

		$fp = fopen($sCSVFilePath, "rb");

		if(!$fp)
		{
			return true;
		}

		// Цикл по строкам CSV-файла
		while(!feof($fp))
		{
			$current_csv_line_array = fgetcsv($fp, 10000, ';', '"');

			// Если пустая строка - пропускаем
			if (!is_array($current_csv_line_array) || (count($current_csv_line_array) == 1 && empty ($current_csv_line_array[0])))
			{
				continue;
			}

			if ($current_csv_line_array)
			{
				$oShopDeliveryCondition = Core_Entity::factory('Shop_Delivery_Condition');

				foreach($current_csv_line_array as $code => $data)
				{
					switch ($code)
					{
						case 0:
							$oShopDeliveryCondition->name = $data;
						break;
						case 1:
							$oShopDeliveryCondition->min_weight = $data;
						break;
						case 2:
							$oShopDeliveryCondition->max_weight = $data;
						break;
						case 3:
							$oShopDeliveryCondition->min_price = $data;
						break;
						case 4:
							$oShopDeliveryCondition->max_price = $data;
						break;
						case 5:
							$oShopDeliveryCondition->price = $data;
						break;
						case 6:
							$oShop_Currency = Core_Entity::factory('Shop_Currency')->getByName($data);
							$oShopDeliveryCondition->shop_currency_id = is_null($oShop_Currency) ? 0 : $oShop_Currency->id;
						break;
						case 7:
							$oShop_Country = Core_Entity::factory('Shop_Country')->getByName($data);
							$oShopDeliveryCondition->shop_country_id = is_null($oShop_Country) ? 0 : $oShop_Country->id;

						break;
						case 8:
							$oShop_Country_Location = $oShopDeliveryCondition
								->Shop_Country
								->Shop_Country_Locations->getByName($data);
							$oShopDeliveryCondition->shop_country_location_id = is_null($oShop_Country_Location) ? 0 : $oShop_Country_Location->id;
						break;
						case 9:
							$oShop_Country_Location_City = $oShopDeliveryCondition
								->Shop_Country_Location
								->Shop_Country_Location_Cities->getByName($data);
							$oShopDeliveryCondition->shop_country_location_city_id = is_null($oShop_Country_Location_City) ? 0 : $oShop_Country_Location_City->id;

						break;
						case 10:
							$oShop_Country_Location_City_Area = $oShopDeliveryCondition
								->Shop_Country_Location_City
								->Shop_Country_Location_City_Areas->getByName($data);

							$oShopDeliveryCondition->shop_country_location_city_area_id = is_null($oShop_Country_Location_City_Area) ? 0 : $oShop_Country_Location_City_Area->id;

						break;
						case 11:
							$oShopDeliveryCondition->shop_tax_id = $data;
						break;
						case 12:
							$oShopDeliveryCondition->description = $data;
						break;
						case 13:
							$oShopDeliveryCondition->marking = $data;
						break;
						default:
					}
				}

				$oShopDelivery->add($oShopDeliveryCondition);

				$counter++;
			}
		}

		// Закрываем файл
		@ fclose($fp);

		return $counter;
	}

	/**
	 * Set object
	 * @param object $object object
	 * @return self
	 */
	public function setObject($object)
	{
		parent::setObject($object);

		// Очищаем основную вкладку
		$oMainTab = Admin_Form_Entity::factory('Tab')
			->caption('Main')
			->name('main');

		$oMainTab->add($oMainRow1 = Admin_Form_Entity::factory('Div')->class('row'));

		$this->addTab($oMainTab);

		// Очищаем дополнительную вкладку
		$oAdditionalTab = Admin_Form_Entity::factory('Tab')
			->caption('Additional')
			->name('additional');

		$this->addTab($oAdditionalTab);

		// Добавляем поле типа "Файл"
		$oCSVFileField = Admin_Form_Entity::factory('File')
			->caption(Core::_("Shop_Delivery_Condition.import_price_list_file_type"))
			->divAttr(array('class' => 'form-group col-xs-12'))
			->name("csvfile")
			->largeImage(array(
					'show_params' => FALSE
				))
			->smallImage(array('show' => FALSE));

		$oMainRow1->add($oCSVFileField);

		return $this;
	}

	/**
	 * Add form buttons
	 * @return Admin_Form_Entity_Buttons
	 */
	protected function _addButtons()
	{
		// Кнопки
		$oAdmin_Form_Entity_Buttons = Admin_Form_Entity::factory('Buttons');

		// Кнопка "Отправить"
		$oAdmin_Form_Entity_Button_Send = Admin_Form_Entity::factory('Button')
			->name('doImport')
			->class('applyButton btn btn-blue')
			->value(Core::_('Shop_Delivery_Condition.import_button'))
			->onclick(
				$this->_Admin_Form_Controller->getAdminSendForm('import', 'doImport')
			);

		$oAdmin_Form_Entity_Buttons
			->add($oAdmin_Form_Entity_Button_Send);

		return $oAdmin_Form_Entity_Buttons;
	}
}