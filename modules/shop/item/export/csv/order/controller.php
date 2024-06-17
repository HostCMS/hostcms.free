<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Item_Export_Csv_Order_Controller
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Shop_Item_Export_Csv_Order_Controller extends Shop_Item_Export_Csv_Controller
{
	/**
	 * Constructor.
	 * @param int $iShopId shop ID
	 */
	public function __construct($iShopId)
	{
		$this->_allowedProperties = array_merge($this->_allowedProperties, array(
			'startOrderDate',
			'endOrderDate'
		));

		parent::__construct($iShopId);
	}

	/**
	 * Get Group Titles
	 * @return array
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onGetGroupTitles
	 */
	public function getGroupTitles()
	{
		$return = array(
			Core::_('Shop_Exchange.group_name'),
			Core::_('Shop_Exchange.group_guid'),
			Core::_('Shop_Exchange.parent_group_guid'),
			Core::_('Shop_Exchange.group_seo_title'),
			Core::_('Shop_Exchange.group_seo_description'),
			Core::_('Shop_Exchange.group_seo_keywords'),
			Core::_('Shop_Exchange.group_description'),
			Core::_('Shop_Exchange.group_path'),
			Core::_('Shop_Exchange.group_image_large'),
			Core::_('Shop_Exchange.group_image_small'),
			Core::_('Shop_Exchange.group_sorting'),

			Core::_('Shop_Exchange.group_seo_group_title_template'),
			Core::_('Shop_Exchange.group_seo_group_keywords_template'),
			Core::_('Shop_Exchange.group_seo_group_description_template'),

			Core::_('Shop_Exchange.group_seo_item_title_template'),
			Core::_('Shop_Exchange.group_seo_item_keywords_template'),
			Core::_('Shop_Exchange.group_seo_item_description_template')
		);

		Core_Event::notify(get_class($this) . '.onGetGroupTitles', $this, array($return));

		return !is_null(Core_Event::getLastReturn())
			? Core_Event::getLastReturn()
			: $return;
	}

	/**
	 * Get Item Titles
	 * @return array
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onGetItemTitles
	 */
	public function getItemTitles()
	{
		$return = array(
			Core::_('Shop_Exchange.item_guid'),
			Core::_('Shop_Exchange.item_id'),
			Core::_('Shop_Exchange.item_marking'),
			Core::_('Shop_Exchange.item_parent_marking'),
			Core::_('Shop_Exchange.item_parent_guid'),
			Core::_('Shop_Exchange.item_name'),
			Core::_('Shop_Exchange.item_description'),
			Core::_('Shop_Exchange.item_text'),
			Core::_('Shop_Exchange.item_weight'),
			Core::_('Shop_Exchange.item_length'),
			Core::_('Shop_Exchange.item_width'),
			Core::_('Shop_Exchange.item_height'),
			Core::_('Shop_Exchange.item_min_quantity'),
			Core::_('Shop_Exchange.item_max_quantity'),
			Core::_('Shop_Exchange.item_quantity_step'),
			Core::_('Shop_Exchange.item_type'),
			Core::_('Shop_Exchange.item_tags'),
			Core::_('Shop_Exchange.item_price'),
			Core::_('Shop_Exchange.item_active'),
			Core::_('Shop_Exchange.item_sorting'),
			Core::_('Shop_Exchange.item_path'),
			Core::_('Shop_Exchange.item_full_path'),
			Core::_('Shop_Exchange.tax_id'),
			Core::_('Shop_Exchange.currency_id'),
			Core::_('Shop_Exchange.seller_name'),
			Core::_('Shop_Exchange.producer_name'),
			Core::_('Shop_Exchange.measure_value'),
			Core::_('Shop_Exchange.item_seo_title'),
			Core::_('Shop_Exchange.item_seo_description'),
			Core::_('Shop_Exchange.item_seo_keywords'),
			Core::_('Shop_Exchange.item_indexing'),
			Core::_('Shop_Exchange.item_yandex_market'),
			Core::_('Shop_Exchange.item_yandex_market_bid'),
			Core::_('Shop_Exchange.item_yandex_market_cid'),
			Core::_('Shop_Exchange.item_yandex_vendorcode'),
			Core::_('Shop_Exchange.item_datetime'),
			Core::_('Shop_Exchange.item_start_datetime'),
			Core::_('Shop_Exchange.item_end_datetime'),
			Core::_('Shop_Exchange.item_image_large'),
			Core::_('Shop_Exchange.item_image_small'),
			Core::_('Shop_Exchange.item_additional_group'),
			Core::_('Shop_Exchange.item_barcode'),
			Core::_('Shop_Exchange.item_sets_guid'),
			Core::_('Shop_Exchange.item_tabs'),
			Core::_('Shop_Exchange.siteuser_id'),
			Core::_('Shop_Exchange.item_yandex_market_sales_notes'),
		);
		Core_Event::notify(get_class($this) . '.onGetItemTitles', $this, array($return));

		return !is_null(Core_Event::getLastReturn())
			? Core_Event::getLastReturn()
			: $return;
	}

	/**
	 * Get Item's Special Prices Titles
	 * @return array
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onGetItemSpecialpricesTitles
	 */
	public function getItemSpecialpricesTitles()
	{
		$return = array(
			Core::_('Shop_Exchange.specialprices_min_quantity'),
			Core::_('Shop_Exchange.specialprices_max_quantity'),
			Core::_('Shop_Exchange.specialprices_price'),
			Core::_('Shop_Exchange.specialprices_percent'),
		);

		Core_Event::notify(get_class($this) . '.onGetItemSpecialpricesTitles', $this, array($return));

		return !is_null(Core_Event::getLastReturn())
			? Core_Event::getLastReturn()
			: $return;
	}

	/**
	 * Init
	 * @return self
	 */
	public function init()
	{


		return $this;
	}

	/**
	 * Array of titile line
	 * @var array
	 */
	protected $_aCurrentRow = array();

	/**
	 * Get Current Row
	 * @return array
	 */
	public function getCurrentRow()
	{
		return $this->_aCurrentRow;
	}

	/**
	 * Set Current Row
	 * @param array $array
	 * @return self
	 */
	public function setCurrentRow(array $array)
	{
		$this->_aCurrentRow = $array;
		return $this;
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onBeforeExportOrdersTitleProperties
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onAfterExportOrdersTitleProperties
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onBeforeExportOrderProperties
	 * @hostcms-event Shop_Item_Export_Csv_Controller.onAfterExportOrderProperties
	 */
	public function execute()
	{
		$oUser = Core_Auth::getCurrentUser();
		if (!$oUser->superuser && $oUser->only_access_my_own)
		{
			return FALSE;
		}

		$this->init();

		// Stop buffering
		while (ob_get_level() > 0)
		{
			ob_end_flush();
		}

		header('Pragma: public');
		header('Cache-Control: no-cache, must-revalidate');
		header('Content-Type: text/html; charset=utf-8');
		// Disable Nginx cache
		header('X-Accel-Buffering: no');
		header('Content-Encoding: none');

		// Автоматический сброс буфера при каждом выводе
		ob_implicit_flush(TRUE);

		if (!$this->exportToFile)
		{
			header('Content-Description: File Transfer');
			header('Content-Type: application/force-download');
			header('Content-Transfer-Encoding: binary');
			header("Content-Disposition: attachment; filename = {$this->fileName};");
		}
		else
		{
			$oSkin = Core_Skin::instance()
				->title('')
				->setMode('blank')
				->header();

			?><div class="container ">
				<div class="page-body"><?php

			Core_Message::show(Core::_('Shop_Item_Export.begin_file_export_orders', TMP_DIR . $this->fileName), 'info');
			flush();
		}

		Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('Begin CSV export ' . $this->fileName);

		$oShop = Core_Entity::factory('Shop', $this->shopId);

		$this->_aCurrentRow = array(
			Core::_('Shop_Item_Export.order_guid'),
			Core::_('Shop_Item_Export.order_invoice'),
			Core::_('Shop_Item_Export.order_country'),
			Core::_('Shop_Item_Export.order_location'),
			Core::_('Shop_Item_Export.order_city'),
			Core::_('Shop_Item_Export.order_city_area'),
			Core::_('Shop_Item_Export.order_name'),
			Core::_('Shop_Item_Export.order_surname'),
			Core::_('Shop_Item_Export.order_patronymic'),
			Core::_('Shop_Item_Export.order_email'),
			Core::_('Shop_Item_Export.order_acceptance_report_form'),
			Core::_('Shop_Item_Export.order_acceptance_report_invoice'),
			Core::_('Shop_Item_Export.order_company_name'),
			Core::_('Shop_Item_Export.order_tin'),
			Core::_('Shop_Item_Export.order_phone'),
			Core::_('Shop_Item_Export.order_fax'),
			Core::_('Shop_Item_Export.order_address'),
			Core::_('Shop_Item_Export.order_status'),
			Core::_('Shop_Item_Export.order_currency'),
			Core::_('Shop_Item_Export.order_paymentsystem'),
			Core::_('Shop_Item_Export.order_delivery'),
			Core::_('Shop_Item_Export.order_date'),
			Core::_('Shop_Item_Export.order_paid'),
			Core::_('Shop_Item_Export.order_paid_date'),
			Core::_('Shop_Item_Export.order_description'),
			Core::_('Shop_Item_Export.order_info'),
			Core::_('Shop_Item_Export.order_canceled'),
			Core::_('Shop_Item_Export.order_status_date'),
			Core::_('Shop_Item_Export.order_delivery_info'),
			Core::_('Shop_Item_Export.order_item_marking'),
			Core::_('Shop_Item_Export.order_item_name'),
		);

		$this->_aCurrentRow = array_map(array($this, 'prepareCell'), $this->_aCurrentRow);

		Core_Event::notify(get_class($this) . '.onBeforeExportOrdersTitleProperties', $this, array($oShop));

		$linkedObject = Core_Entity::factory('Shop_Item_Property_List', $this->shopId);
		$aProperties = $linkedObject->Properties->findAll(FALSE);

		$aCheckedProperties = array();

		foreach ($aProperties as $oProperty)
		{
			if (Core_Array::getPost('property_' . $oProperty->id))
			{
				$this->_aCurrentRow[] = $this->prepareCell($oProperty->name);
				$aCheckedProperties[] = $oProperty;
			}
		}

		Core_Event::notify(get_class($this) . '.onAfterExportOrdersTitleProperties', $this, array($oShop));

		$this->_aCurrentRow = array_merge($this->_aCurrentRow, array(
			'"' . Core::_('Shop_Item_Export.order_item_quantity') . '"',
			'"' . Core::_('Shop_Item_Export.order_item_price') . '"',
			'"' . Core::_('Shop_Item_Export.order_item_tax') . '"',
			'"' . Core::_('Shop_Item_Export.order_item_type') . '"'
			)
		);

		$this->_printRow($this->_aCurrentRow);

		$offset = 0;
		$limit = 500;

		if (!is_null($this->startOrderDate) && !is_null($this->endOrderDate))
		{
			$sStartDate = Core_Date::timestamp2sql(Core_Date::datetime2timestamp($this->startOrderDate . " 00:00:00"));
			$sEndDate = Core_Date::timestamp2sql(Core_Date::datetime2timestamp($this->endOrderDate . " 23:59:59"));
		}
		else
		{
			$sStartDate = $sEndDate = NULL;
		}

		do {
			$oShop_Orders = $oShop->Shop_Orders;

			if (!is_null($sStartDate) && !is_null($sEndDate))
			{
				$oShop_Orders
					->queryBuilder()
					->where('datetime', 'BETWEEN', array($sStartDate, $sEndDate));
			}

			$oShop_Orders
				->queryBuilder()
				->orderBy('id', 'ASC')
				->offset($offset)->limit($limit);

			$aShop_Orders = $oShop_Orders->findAll(FALSE);

			foreach ($aShop_Orders as $key => $oShop_Order)
			{
				$this->_printRow(array(
					$this->prepareCell($oShop_Order->guid),
					$this->prepareCell($oShop_Order->invoice),
					$this->prepareCell($oShop_Order->Shop_Country->name),
					$this->prepareCell($oShop_Order->Shop_Country_Location->name),
					$this->prepareCell($oShop_Order->Shop_Country_Location_City->name),
					$this->prepareCell($oShop_Order->Shop_Country_Location_City_Area->name),
					$this->prepareCell($oShop_Order->name),
					$this->prepareCell($oShop_Order->surname),
					$this->prepareCell($oShop_Order->patronymic),
					$this->prepareCell($oShop_Order->email),
					$this->prepareCell($oShop_Order->acceptance_report),
					$this->prepareCell($oShop_Order->vat_invoice),
					$this->prepareCell($oShop_Order->company),
					$this->prepareCell($oShop_Order->tin),
					$this->prepareCell($oShop_Order->phone),
					$this->prepareCell($oShop_Order->fax),
					$this->prepareCell($oShop_Order->address),
					$this->prepareCell($oShop_Order->Shop_Order_Status->name),
					$this->prepareCell($oShop_Order->Shop_Currency->name),
					$this->prepareCell($oShop_Order->Shop_Payment_System->name),
					$this->prepareCell($oShop_Order->Shop_Delivery->name),
					$this->prepareCell($oShop_Order->datetime),
					$this->prepareCell($oShop_Order->paid),
					$this->prepareCell($oShop_Order->payment_datetime),
					$this->prepareCell($oShop_Order->description),
					$this->prepareCell($oShop_Order->system_information),
					$this->prepareCell($oShop_Order->canceled),
					$this->prepareCell($oShop_Order->status_datetime),
					$this->prepareCell($oShop_Order->delivery_information)
				));

				// Получаем все товары заказа
				$aShop_Order_Items = $oShop_Order->Shop_Order_Items->findAll(FALSE);
				foreach ($aShop_Order_Items as $oShop_Order_Item)
				{
					$this->_aCurrentRow = array(
						$this->prepareCell($oShop_Order->guid),
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						'""',
						$this->prepareCell($oShop_Order_Item->marking),
						$this->prepareCell($oShop_Order_Item->name)
					);

					Core_Event::notify(get_class($this) . '.onBeforeExportOrderProperties', $this, array($oShop, $oShop_Order_Item));

					foreach ($aCheckedProperties as $oProperty)
					{
						$oShop_Item = $oShop_Order_Item->Shop_Item;
						$aPropertyValues = $oProperty->getValues($oShop_Item->id, FALSE);

						if (count($aPropertyValues))
						{
							$oProperty_Value = $aPropertyValues[0];

							$this->_aCurrentRow[] = $this->prepareCell($this->_getPropertyValue($oProperty, $oProperty_Value, $oShop_Item));
						}
						else
						{
							$this->_aCurrentRow[] = '""';
						}
					}

					Core_Event::notify(get_class($this) . '.onAfterExportOrderProperties', $this, array($oShop, $oShop_Order_Item));

					$this->_aCurrentRow = array_merge($this->_aCurrentRow, array(
						sprintf('"%s"', $this->prepareFloat($oShop_Order_Item->quantity)),
						sprintf('"%s"', $this->prepareFloat($oShop_Order_Item->price)),
						sprintf('"%s"', $this->prepareFloat($oShop_Order_Item->rate)),
						sprintf('"%s"', $oShop_Order_Item->type)
					));

					$this->_printRow($this->_aCurrentRow);
				}

				if ($this->exportToFile && $key && $key % 100 == 0)
				{
					echo '.';
					flush();
				}
			}
			$offset += $limit;
		}
		while (count($aShop_Orders));

		$this->_finish();

		Core_Log::instance()->clear()
			->status(Core_Log::$MESSAGE)
			->write('End CSV export ' . $this->fileName);

		if ($this->exportToFile)
		{
			@chmod(CMS_FOLDER . TMP_DIR . $this->fileName, CHMOD_FILE);

			Core_Message::show(Core::_('Shop_Item_Export.end_file_export', TMP_DIR . $this->fileName));

			?></div></div><?php

			$oSkin->footer();
		}

		exit();
	}
}