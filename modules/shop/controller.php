<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Online shop.
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Controller
{
	/**
	 * The singleton instances.
	 * @var mixed
	 */
	static public $instance = NULL;

	/**
	 * Register an existing instance as a singleton.
	 * @return object
	 */
	static public function instance()
	{
		if (is_null(self::$instance))
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Float digits format
	 * @var string
	 */
	protected $_floatFormat = "%.2f";

	/**
	 * Set float format
	 * @param string $floatFormat format
	 * @return self
	 */
	public function floatFormat($floatFormat)
	{
		$this->_floatFormat = $floatFormat;
		return $this;
	}

	/**
	 * Number of decimal digits
	 * @var int
	 */
	protected $_decimalDigits = 2;

	/**
	 * Set number of decimal digits
	 * @param string $floatFormat format
	 * @return self
	 */
	public function decimalDigits($decimalDigits)
	{
		$this->_decimalDigits = $decimalDigits;
		return $this;
	}

	/**
	 * Use Banker's Rounding. Default TRUE
	 * @var boolean
	 */
	protected $_bankersRounding = TRUE;

	/**
	 * Use Banker's Rounding
	 * @param boolean $bankersRounding
	 * @return self
	 */
	public function bankersRounding($bankersRounding = TRUE)
	{
		$this->_bankersRounding = $bankersRounding;
		return $this;
	}

	/**
	 * Banker's Round half to even
	 * TRUE - Round half to even. 23.5 => 24, 24.5 => 24
	 * FALSE - Round half to odd. 23.5 => 23, 24.5 => 25
	 * @var boolean
	 */
	protected $_bankersRoundHalfToEven = TRUE;

	/**
	 * Banker's Round half to even
	 * TRUE - Round half to even. 23.5 => 24, 24.5 => 24
	 * FALSE - Round half to odd. 23.5 => 23, 24.5 => 25
	 * @param boolean $bankersRoundHalfToEven
	 * @return self
	 */
	public function bankersRoundHalfToEven($bankersRoundHalfToEven = TRUE)
	{
		$this->_bankersRoundHalfToEven = $bankersRoundHalfToEven;
		return $this;
	}

	/**
	 * Banker's Round
	 * depends on $this->_bankersRoundHalfToEven
	 *
	 * @param float $value The value to round
	 * @param int $precision The optional number of decimal digits to round to
	 * @return float The rounded value
	 */
	public function bRound($value, $precision = 0)
	{
		$m = pow(10, $precision);
		$n = $precision ? $value * $m : $value;
		$i = floor($n);
		$f = $n - $i;
		$e = 0.00001;

		$r = ($f > 0.5 - $e && $f < 0.5 + $e)
			? ($this->_bankersRoundHalfToEven
				? (($i % 2 == 0) ? $i : $i + 1)
				: (($i % 2 == 0) ? $i + 1 : $i)
			)
			: round($n);

		return $precision
			? $r / $m
			: $r;
	}

	/**
	 * Округление цен к формату, приведенного в $this->_floatFormat
	 *
	 * @param float $value цена
	 * @return string
	 */
	public function round($value)
	{
		return sprintf($this->_floatFormat, $this->_bankersRounding
			? $this->bRound($value, $this->_decimalDigits)
			: round($value, $this->_decimalDigits)
		);
	}

	/**
	 * Convert decimal
	 * @param mixed $value
	 * @return mixed
	 */
	public function convertFloat($value)
	{
		return self::convertDecimal($value);
	}

	/**
	 * Convert decimal
	 * @param mixed $value
	 * @return mixed
	 */
	static public function convertDecimal($value)
	{
		$value = preg_replace('/[^0-9.,\-]/u', '', $value);
		$value = str_replace(array(',', '-'), '.', $value);
		$value === '' && $value = 0;
		return $value;
	}

	/**
	 * Convert price
	 * @param mixed $price price
	 * @param int $decimalDigits e.g. 2, default uses $this->_decimalDigits
	 * @return mixed
	 */
	public function convertPrice($price, $decimalDigits = NULL)
	{
		$price = self::convertDecimal($price);

		$decimalDigits = intval(!is_null($decimalDigits) ? $decimalDigits : $this->_decimalDigits);
		preg_match("/((\d+(\.)\d{0,{$decimalDigits}})|\d+)/u", $price, $array_price);
		return isset($array_price[1]) ? floatval($array_price[1]) : 0;
	}

	/**
	 * Определение коэффициента пересчета валюты $oItem_Currency в валюту $oShop_Currency
	 *
	 * @param Shop_Currency_Model $oItem_Currency исходная валюта
	 * @param Shop_Currency_Model $oShop_Currency требуемая валюта
	 * @return float
	 */
	public function getCurrencyCoefficientInShopCurrency(Shop_Currency_Model $oItem_Currency, Shop_Currency_Model $oShop_Currency)
	{
		// Определяем коэффициент пересчета в базовую валюту
		$fItemExchangeRate = $oItem_Currency->exchange_rate;
		if ($fItemExchangeRate == 0)
		{
			throw new Core_Exception('Method getCurrencyCoefficientInShopCurrency(): Item "%id" currency exchange rate is 0.', array('%id' => $oItem_Currency->id));
		}

		// Определяем коэффициент пересчета в валюту магазина
		$fShopExchangeRate = $oShop_Currency->exchange_rate;
		if ($fShopExchangeRate == 0)
		{
			throw new Core_Exception('Method getCurrencyCoefficientInShopCurrency(): Shop currency %id exchange rate is 0.', array('%id' => $oShop_Currency->id));
		}

		// Без округления
		//return round($fItemExchangeRate / $fShopExchangeRate, $this->_decimalDigits);
		return $fItemExchangeRate / $fShopExchangeRate;
	}

	/**
	 * Конвертирование значения из одной меры размера в другую
	 * @param string $value значение для конвертации
	 * @param int $sourceMeasure исходная мера
	 * @param int $destMeasure целевая мера
	 */
	static public function convertSizeMeasure($value, $sourceMeasure, $destMeasure = 0)
	{
		$sourceMeasure = intval($sourceMeasure);
		$destMeasure = intval($destMeasure);

		if ($sourceMeasure < 0 || $sourceMeasure > 4 || $destMeasure < 0 || $destMeasure > 4)
		{
			throw new Core_Exception('Method convertSizeMeasure(): Measure %id is out of range.', array('%id' => $destMeasure));
		}

		$aTmp = array(
			0 => 1, // мм
			1 => 10, // см
			2 => 1000, // м
			3 => 25.4, // дюйм
			4 => 304.8 // фут
		);

		return $aTmp[$sourceMeasure] * $value / $aTmp[$destMeasure];
	}

	static public function showGroupButton()
	{
		$html = '
			<script>
				var lastFocusedGroup;

				$(function() {

					$("textarea[name^=\'seo_group_\']").on("focus", function() {

						lastFocusedGroup = $(document.activeElement);
					});
				})
			</script>
			<div class="btn-group pull-right">
				<a class="btn btn-sm btn-default"><i class="fa fa-plus"></i></a>
				<a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-angle-down"></i></a>
				<ul class="dropdown-menu dropdown-default" role="menu">
					<li class="disabled">
						<a class="bold">' . Core::_("Shop.seo_template_shop") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{shop.name\}\')">' . Core::_("Shop.seo_template_shop_name") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{this.seoFilter \x22: \x22 \x22, \x22\}\')">' . Core::_("Shop.seo_template_filter") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{this.filterProducer}\')">' . Core::_("Shop.seo_template_filter_producer") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{this.pageNumber \x22, ' . Core::_("Shop.seo_template_group_page") . ' %d\x22\}\')">' . Core::_("Shop.seo_template_group_page_number") . '</a>
					</li>
					<li class="divider"></li>
					<li class="disabled">
						<a class="bold">' . Core::_("Shop.seo_template_group") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{group.name\}\')">' . Core::_("Shop.seo_template_group_name") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{group.description\}\')">' . Core::_("Shop.seo_template_group_description") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{group.propertyValue ID\}\')">' . Core::_("Shop.seo_template_property_value") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedGroup, \'\{group.groupPathWithSeparator \x22 → \x22 1\}\')">' . Core::_("Shop.seo_template_group_path") . '</a>
					</li>
				</ul>
			</div>
		';

		return $html;
	}

	static public function showItemButton()
	{
		$html = '
			<script>
				var lastFocusedItem;

				$(function(){

					$("textarea[name^=\'seo_item_\']").on("focus", function() {

						lastFocusedItem = $(document.activeElement);
					});
				})
			</script>
			<div class="btn-group pull-right">
				<a class="btn btn-sm btn-default"><i class="fa fa-plus"></i></a>
				<a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-angle-down"></i></a>
				<ul class="dropdown-menu dropdown-default" role="menu">
					<li class="disabled">
						<a class="bold">' . Core::_("Shop.seo_template_shop") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{shop.name\}\')">' . Core::_("Shop.seo_template_shop_name") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{this.seoFilter \x22: \x22 \x22, \x22\}\')">' . Core::_("Shop.seo_template_filter") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{this.filterProducer}\')">' . Core::_("Shop.seo_template_filter_producer") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{this.pageNumber \x22, ' . Core::_("Shop.seo_template_group_page") . ' %d\x22\}\')">' . Core::_("Shop.seo_template_group_page_number") . '</a>
					</li>
					<li class="divider"></li>
					<li class="disabled">
						<a class="bold">' . Core::_("Shop.seo_template_group") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{group.name\}\')">' . Core::_("Shop.seo_template_group_name") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{group.description\}\')">' . Core::_("Shop.seo_template_group_description") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{group.propertyValue ID\}\')">' . Core::_("Shop.seo_template_property_value") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{group.groupPathWithSeparator \x22 → \x22 1\}\')">' . Core::_("Shop.seo_template_group_path") . '</a>
					</li>
					<li class="divider"></li>
					<li class="disabled">
						<a class="bold">' . Core::_("Shop.seo_template_item") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{item.name\}\')">' . Core::_("Shop.seo_template_item_name") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{item.description\}\')">' . Core::_("Shop.seo_template_item_description") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{item.text\}\')">' . Core::_("Shop.seo_template_item_text") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{item.propertyValue ID\}\')">' . Core::_("Shop.seo_template_property_value") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{item.priceWithCurrency\}\')">' . Core::_("Shop.seo_template_item_price_with_currency") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedItem, \'\{item.currencyName\}\')">' . Core::_("Shop.seo_template_item_currency") . '</a>
					</li>
				</ul>
			</div>
		';

		return $html;
	}

	static public function showRootButton()
	{
		$html = '
			<script>
				var lastFocusedRoot;

				$(function(){

					$("textarea[name^=\'seo_root_\']").on("focus", function() {

						lastFocusedRoot = $(document.activeElement);
					});
				})
			</script>
			<div class="btn-group pull-right">
				<a class="btn btn-sm btn-default"><i class="fa fa-plus"></i></a>
				<a class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false"><i class="fa fa-angle-down"></i></a>
				<ul class="dropdown-menu dropdown-default" role="menu">
					<li class="disabled">
						<a class="bold">' . Core::_("Shop.seo_template_shop") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedRoot, \'\{shop.name\}\')">' . Core::_("Shop.seo_template_shop_name") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedRoot, \'\{this.seoFilter \x22: \x22 \x22, \x22\}\')">' . Core::_("Shop.seo_template_filter") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedRoot, \'\{this.filterProducer}\')">' . Core::_("Shop.seo_template_filter_producer") . '</a>
					</li>
					<li>
						<a onclick="$.insertSeoTemplate(lastFocusedRoot, \'\{this.pageNumber \x22, ' . Core::_("Shop.seo_template_group_page") . ' %d\x22\}\')">' . Core::_("Shop.seo_template_group_page_number") . '</a>
					</li>
				</ul>
			</div>
		';

		return $html;
	}

	/**
	 * Get currencies for list
	 * @return array
	 */
	static public function fillCurrencies()
	{
		$oShop_Currencies = Core_Entity::factory('Shop_Currency');

		$oShop_Currencies->queryBuilder()
			->orderBy('sorting')
			->orderBy('name');

		//$aReturn = array(' … ');
		$aReturn = array();

		$aShop_Currencies = $oShop_Currencies->findAll();
		foreach ($aShop_Currencies as $oShop_Currency)
		{
			$aReturn[$oShop_Currency->id] = $oShop_Currency->sign;
		}

		return $aReturn;
	}

	/**
	 * Get shops for list
	 * @param int $iSiteId site ID
	 * @return array
	 */
	static public function fillShops($iSiteId)
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
	 * Get measures for list
	 * @return array
	 */
	static public function fillMeasures()
	{
		$aReturn = array(' … ');

		$aShop_Measures = Core_Entity::factory('Shop_Measure')->findAll();
		foreach ($aShop_Measures as $oShop_Measure)
		{
			$aReturn[$oShop_Measure->id] = $oShop_Measure->name;
		}

		return $aReturn;
	}

	/**
	 * Get uniq document ID
	 * @param int $id document ID
	 * @param int $type document type
	 * @return int
	 */
	static public function getDocumentId($id, $type)
	{
		return ($id << 8) | $type;
	}

	/**
	 * Get document type
	 * @return int|NULL
	 */
	static public function getDocumentType($document_id)
	{
		return $document_id
			? Core_Bit::extractBits($document_id, 8, 1)
			: NULL;
	}

	/**
	 * Get document
	 * @return object|NULL
	 */
	static public function getDocument($document_id)
	{
		$type = self::getDocumentType($document_id);

		$id = $document_id >> 8;

		$model = self::getDocumentModel($type);

		return !is_null($model)
			? Core_Entity::factory($model)->getById($id, FALSE)
			: NULL;
	}

	/**
	 * Get Model Name By Type Id
	 * @param int $type
	 *
	 */
	static public function getDocumentModel($type)
	{
		/* Типы документов:
		* 0 - Shop_Warehouse_Inventory_Model
		* 1 - Shop_Warehouse_Incoming_Model
		* 2 - Shop_Warehouse_Writeoff_Model
		* 3 - Shop_Warehouse_Regrade_Model
		* 4 - Shop_Warehouse_Movement_Model
		* 5 - Shop_Order_Model
		* 6 - Shop_Warehouse_Purchaseorder_Model
		* 7 - Shop_Warehouse_Invoice_Model
		* 8 - Shop_Warehouse_Supply
		* 9 - Shop_Warehouse_Purchasereturn
		* 30 - Shop_Warrant_Model
		* 31 - Shop_Warrant_Model
		* 32 - Shop_Warrant_Model
		* 33 - Shop_Warrant_Model
		*/
		switch ($type)
		{
			case 0:
				$model = 'Shop_Warehouse_Inventory';
			break;
			case 1:
				$model = 'Shop_Warehouse_Incoming';
			break;
			case 2:
				$model = 'Shop_Warehouse_Writeoff';
			break;
			case 3:
				$model = 'Shop_Warehouse_Regrade';
			break;
			case 5:
				$model = 'Shop_Order';
			break;
			case 6:
				$model = 'Shop_Warehouse_Purchaseorder';
			break;
			case 7:
				$model = 'Shop_Warehouse_Invoice';
			break;
			case 8:
				$model = 'Shop_Warehouse_Supply';
			break;
			case 9:
				$model = 'Shop_Warehouse_Purchasereturn';
			break;
			case 30:
			case 31:
			case 32:
			case 33:
				$model = 'Shop_Warrant';
			break;
			default:
				$model = NULL;
		}

		return $model;
	}
}