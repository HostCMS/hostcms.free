<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Purchaseorder_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Shop_Warehouse_Purchaseorder_Model extends Core_Entity
{
	public $siteuserCompanyContract = NULL;
	public $siteuserCompanyName = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'shop_warehouse' => array(),
		'siteuser' => array(),
		'siteuser_company' => array(),
		'siteuser_company_contract' => array(),
		'user' => array(),
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'shop_warehouse_purchaseorder_item' => array(),
	);

	/**
	 * Column consist item's name
	 * @var string
	 */
	protected $_nameColumn = 'number';

	/**
	 * Backend property
	 * @var mixed
	 */
	public $rollback = 0;

	/**
	 * TYPE
	 * @var int
	 */
	const TYPE = 6;

	/**
	 * Get Entity Type
	 * @return int
	 */
	public function getEntityType()
	{
		return self::TYPE;
	}

	/**
	 * Constructor.
	 * @param int $id entity ID
	 */
	public function __construct($id = NULL)
	{
		parent::__construct($id);

		if (is_null($id) && !$this->loaded())
		{
			$oUser = Core_Auth::getCurrentUser();
			$this->_preloadValues['user_id'] = is_null($oUser) ? 0 : $oUser->id;
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
			//$this->_preloadValues['posted'] = 0;
		}
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function postedBackend()
	{
		return $this->posted
			? '<i class="fa fa-check-circle-o green">'
			: '<i class="fa fa-times-circle-o red">';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function siteuserCompanyNameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return Core::moduleIsActive('siteuser') && $this->siteuser_company_id
			? '<div class="profile-container tickets-container counterparty-block"><ul class="tickets-list">' . $this->Siteuser_Company->getProfileBlock('') . '</ul></div>'
			: '';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function siteuserCompanyContractBackend()
	{
		return Core::moduleIsActive('siteuser')
			? $this->Siteuser_Company_Contract->name
			: '';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function dataManagerBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$oUser = $this->User;
		return $oUser->id
			? $oUser->showAvatarWithName()
			: '';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function amountBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return '<div class="small">' . $this->Shop_Warehouse->Shop->Shop_Currency->formatWithCurrency($this->getAmount()) . '</div>';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function invoiceBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$document_id = Shop_Controller::getDocumentId($this->id, $this->getEntityType());

		$amount = 0;

		$aShop_Document_Relations = Core_Entity::factory('Shop_Document_Relation')->getAllByRelated_document_id($document_id, FALSE);
		foreach ($aShop_Document_Relations as $oShop_Document_Relation)
		{
			$type = Shop_Controller::getDocumentType($oShop_Document_Relation->document_id);

			// Счет поставщика
			if ($type == 7)
			{
				$oShop_Warehouse_Invoice = Shop_Controller::getDocument($oShop_Document_Relation->document_id);

				if (!is_null($oShop_Warehouse_Invoice) && $oShop_Warehouse_Invoice->posted)
				{
					$amount += $oShop_Warehouse_Invoice->getAmount();
				}
			}
		}

		return '<div class="small">' . $this->Shop_Warehouse->Shop->Shop_Currency->formatWithCurrency($amount) . '</div>';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function paidBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$document_id = Shop_Controller::getDocumentId($this->id, $this->getEntityType());

		$amount = 0;

		$aShop_Document_Relations = Core_Entity::factory('Shop_Document_Relation')->getAllByRelated_document_id($document_id, FALSE);
		foreach ($aShop_Document_Relations as $oShop_Document_Relation)
		{
			$type = Shop_Controller::getDocumentType($oShop_Document_Relation->document_id);

			// Кассы, расходный кассовый ордер или исходящий платеж
			if ($type == 30 || $type == 33)
			{
				$oShop_Warrant = Shop_Controller::getDocument($oShop_Document_Relation->document_id);

				if (!is_null($oShop_Warrant) && $oShop_Warrant->posted)
				{
					$amount += $oShop_Document_Relation->paid;
				}
			}

			// Не прямая связь
			$aShop_Document_Relations_Second = Core_Entity::factory('Shop_Document_Relation')->getAllByRelated_document_id($oShop_Document_Relation->document_id, FALSE);
			foreach ($aShop_Document_Relations_Second as $oShop_Document_Relation_Second)
			{
				$type_second = Shop_Controller::getDocumentType($oShop_Document_Relation_Second->document_id);

				// Кассы, расходный кассовый ордер или исходящий платеж
				if ($type_second == 30 || $type_second == 33)
				{
					$oShop_Warrant = Shop_Controller::getDocument($oShop_Document_Relation_Second->document_id);

					if (!is_null($oShop_Warrant) && $oShop_Warrant->posted)
					{
						$amount += $oShop_Document_Relation_Second->paid;
					}
				}
			}
		}

		$dot = $amount >= $this->getAmount()
			? '<span class="online online-small"></span> '
			: '';

		return $amount
			? '<div class="small">' . $dot . $this->Shop_Warehouse->Shop->Shop_Currency->formatWithCurrency($amount) . '</div>'
			: '';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function shippedBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$document_id = Shop_Controller::getDocumentId($this->id, $this->getEntityType());

		$amount = 0;

		// Заказы поставщику <==> Приемки
		$aShop_Document_Relations = Core_Entity::factory('Shop_Document_Relation')->getAllByRelated_document_id($document_id, FALSE);
		foreach ($aShop_Document_Relations as $oShop_Document_Relation)
		{
			$type = Shop_Controller::getDocumentType($oShop_Document_Relation->document_id);

			// Приемки
			if ($type == 8)
			{
				$oShop_Warehouse_Supply = Shop_Controller::getDocument($oShop_Document_Relation->document_id);

				if (!is_null($oShop_Warehouse_Supply) && $oShop_Warehouse_Supply->posted)
				{
					$amount += $oShop_Warehouse_Supply->getAmount();
				}
			}

			// Счета поставщика на основе Заказа поставщику <==> Приемки
			$aShop_Document_Relations_Second = Core_Entity::factory('Shop_Document_Relation')->getAllByRelated_document_id($oShop_Document_Relation->document_id, FALSE);

			foreach ($aShop_Document_Relations_Second as $oShop_Document_Relation)
			{
				$type = Shop_Controller::getDocumentType($oShop_Document_Relation->document_id);

				// Приемки
				if ($type == 8)
				{
					$oShop_Warehouse_Supply = Shop_Controller::getDocument($oShop_Document_Relation->document_id);

					if ($oShop_Warehouse_Supply->posted)
					{
						$amount += $oShop_Warehouse_Supply->getAmount();
					}
				}
			}
		}

		$dot = $amount >= $this->getAmount()
			? '<span class="online online-small"></span> '
			: '';

		return $amount
			? '<div class="small">' . $dot . $this->Shop_Warehouse->Shop->Shop_Currency->formatWithCurrency($amount) . '</div>'
			: '';
	}

	public function date()
	{
		return Core_Date::sql2date($this->datetime);
	}

	/**
	 * Mark entity as deleted
	 * @return Core_Entity
	 */
	public function markDeleted()
	{
		$this->unpost();

		return parent::markDeleted();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event shop_warehouse.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->Shop_Warehouse_Purchaseorder_Items->deleteAll(FALSE);

		Core_Entity::factory('Shop_Warehouse_Entry')->deleteByDocument($this->id, $this->getEntityType());

		if (Core::moduleIsActive('revision'))
		{
			Revision_Controller::delete($this->getModelName(), $this->id);
		}

		return parent::delete($primaryKey);
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function selectDocumentBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$document_id = Core_Array::getGet('document_id', 0, 'int');
		$parentWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('parentWindowId', '', 'str'));
		$windowId = $parentWindowId ? $parentWindowId : $oAdmin_Form_Controller->getWindowId();

		$modalWindowId = preg_replace('/[^A-Za-z0-9_-]/', '', Core_Array::getGet('modalWindowId', '', 'str'));

		return '<i class="fa fa-check-circle-o green shop-document-related-select" onclick="$.selectShopDocumentRelated(this, \'' . $windowId . '\', \'' . $modalWindowId . '\')" data-id="' . $this->id . '" data-type="' . $this->getEntityType() . '" data-document-id="' . $document_id . '" data-shop-id="' . $this->Shop_Warehouse->shop_id . '" data-amount="' . $this->getAmount() . '"></i>';
	}

	/**
	 * Get amount
	 * @return float
	 */
	public function getAmount()
	{
		$amount = 0;

		$oBaseCurrency = Core_Entity::factory('Shop_Currency')->getDefault();

		$aShop_Warehouse_Purchaseorder_Items = $this->Shop_Warehouse_Purchaseorder_Items->findAll(FALSE);
		foreach ($aShop_Warehouse_Purchaseorder_Items as $oShop_Warehouse_Purchaseorder_Item)
		{
			if ($oShop_Warehouse_Purchaseorder_Item->shop_item_id && $oShop_Warehouse_Purchaseorder_Item->Shop_Item->shop_currency_id)
			{
				$price = Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(Core_Entity::factory('Shop_Currency', $oShop_Warehouse_Purchaseorder_Item->Shop_Item->shop_currency_id), $oBaseCurrency) * $oShop_Warehouse_Purchaseorder_Item->price;

				$amount += $price * $oShop_Warehouse_Purchaseorder_Item->count;
			}
		}

		return number_format(Shop_Controller::instance()->round($amount), 2, '.', '');
	}

	/**
	 * Get title
	 * @return string
	 */
	public function getTitle()
	{
		return Core::_('Shop_Warehouse_Purchaseorder.title');
	}

	/**
	 * Add entries
	 * @return self
	 */
	public function post()
	{
		$this->posted = 1;
		$this->save();

		return $this;
	}

	/**
	 * Remove all shop warehouse entries by document
	 * @return self
	 */
	public function unpost()
	{
		if ($this->posted)
		{
			$this->posted = 0;
			$this->save();
		}

		return $this;
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function printBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		Core::moduleIsActive('printlayout')
			&& Printlayout_Controller::getBackendPrintButton($oAdmin_Form_Controller, $this->id, $this->getEntityType());
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function shop_warehouse_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->Shop_Warehouse->id ? htmlspecialchars((string) $this->Shop_Warehouse->name) : '';
	}

	/**
	 * Backup revision
	 * @return self
	 */
	public function backupRevision()
	{
		if (Core::moduleIsActive('revision'))
		{
			$aBackup = array(
				'shop_warehouse_id' => $this->shop_warehouse_id,
				'number' => $this->number,
				'description' => $this->description,
				'datetime' => $this->datetime,
				'posted' => $this->posted,
				'shop_price_id' => $this->shop_price_id,
				'user_id' => $this->user_id,
				'items' => array()
			);

			$aShop_Warehouse_Purchaseorder_Items = $this->Shop_Warehouse_Purchaseorder_Items->findAll(FALSE);

			foreach ($aShop_Warehouse_Purchaseorder_Items as $oShop_Warehouse_Purchaseorder_Item)
			{
				$aBackup['items'][] = array(
					'shop_item_id' => $oShop_Warehouse_Purchaseorder_Item->shop_item_id,
					'price' => $oShop_Warehouse_Purchaseorder_Item->price,
					'count' => $oShop_Warehouse_Purchaseorder_Item->count,
				);
			}

			Revision_Controller::backup($this, $aBackup);
		}

		return $this;
	}

	/**
	 * Rollback Revision
	 * @param int $revision_id Revision ID
	 * @return self
	 */
	public function rollbackRevision($revision_id)
	{
		if (Core::moduleIsActive('revision'))
		{
			$oRevision = Core_Entity::factory('Revision', $revision_id);

			$aBackup = json_decode($oRevision->value, TRUE);

			if (is_array($aBackup))
			{
				$this->unpost();

				$this->shop_warehouse_id = Core_Array::get($aBackup, 'shop_warehouse_id');
				$this->number = Core_Array::get($aBackup, 'number');
				$this->description = Core_Array::get($aBackup, 'description');
				$this->datetime = Core_Array::get($aBackup, 'datetime');
				$this->posted = 0;
				$this->shop_price_id = Core_Array::get($aBackup, 'shop_price_id');
				$this->user_id = Core_Array::get($aBackup, 'user_id');

				$aAllItems = Core_Array::get($aBackup, 'items');

				if (count($aAllItems))
				{
					// Удаляем все товары
					$this->Shop_Warehouse_Purchaseorder_Items->deleteAll(FALSE);

					// Создаем новые
					foreach ($aAllItems as $aShop_Warehouse_Purchaseorder_Item)
					{
						$oShop_Warehouse_Purchaseorder_Item = Core_Entity::factory('Shop_Warehouse_Purchaseorder_Item');
						$oShop_Warehouse_Purchaseorder_Item->shop_warehouse_purchaseorder_id = $this->id;
						$oShop_Warehouse_Purchaseorder_Item->shop_item_id = Core_Array::get($aShop_Warehouse_Purchaseorder_Item, 'shop_item_id');
						$oShop_Warehouse_Purchaseorder_Item->count = Core_Array::get($aShop_Warehouse_Purchaseorder_Item, 'count');
						$oShop_Warehouse_Purchaseorder_Item->save();
					}
				}

				$this->save();

				Core_Array::get($aBackup, 'posted') && $this->post();
			}
		}

		return $this;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function count_itemsBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->Shop_Warehouse_Purchaseorder_Items->getCount();
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-danger badge-square')
			->value($count)
			->execute();
	}

	/**
	 * Get printlayout replaces
	 * @return array
	 * @hostcms-event shop_warehouse_purchaseorder.onAfterGetPrintlayoutReplaces
	 */
	public function getPrintlayoutReplaces()
	{
		$oShop = $this->Shop_Warehouse->Shop;

		$aReplace = array(
			// Core_Meta
			'this' => $this,
			'company' => $this->Shop_Warehouse->shop_company_id ? $this->Shop_Warehouse->Shop_Company : $this->Shop_Warehouse->Shop->Shop_Company,
			'shop_warehouse' => $this->Shop_Warehouse,
			'shop' => $oShop,
			'user' => $this->User,
			'type' => Core::_('Shop_Warehouse_Purchaseorder.title'),
			'total_count' => 0,
			'date' => Core_Date::sql2date($this->datetime),
			'Items' => array(),
		);

		if (Core::moduleIsActive('siteuser'))
		{
			$oSiteuser_Company = $this->Siteuser_Company;
			$aReplace['siteuser_company'] = !is_null($oSiteuser_Company)
				? $oSiteuser_Company
				: new Core_Meta_Empty();
		}

		$position = 1;

		$total_amount = $total_quantity = 0;

		$Shop_Item_Controller = new Shop_Item_Controller();

		$aShop_Warehouse_Purchaseorder_Items = $this->Shop_Warehouse_Purchaseorder_Items->findAll();
		foreach ($aShop_Warehouse_Purchaseorder_Items as $oShop_Warehouse_Purchaseorder_Item)
		{
			$oShop_Item = $oShop_Warehouse_Purchaseorder_Item->Shop_Item;

			$aPrices = $Shop_Item_Controller->calculatePrice($oShop_Warehouse_Purchaseorder_Item->price, $oShop_Item);

			$aBarcodes = array();

			$aShop_Item_Barcodes = $oShop_Item->Shop_Item_Barcodes->findAll(FALSE);
			foreach ($aShop_Item_Barcodes as $oShop_Item_Barcode)
			{
				$aBarcodes[] = $oShop_Item_Barcode->value;
			}

			$node = new stdClass();

			$node->position = $position++;
			$node->item = $oShop_Item;
			$node->name = htmlspecialchars($oShop_Item->name);
			$node->measure = $oShop_Item->shop_measure_id ? htmlspecialchars((string) $oShop_Item->Shop_Measure->name) : '';
			$node->currency = $oShop_Item->shop_currency_id ? htmlspecialchars((string) $oShop_Item->Shop_Currency->sign) : '';
			$node->price = $oShop_Item->shop_currency_id ? $oShop_Item->Shop_Currency->format($aPrices['price_tax']) : 0;
			$node->quantity = Core_Str::hideZeros($oShop_Warehouse_Purchaseorder_Item->count);
			$node->amount = $oShop_Item->shop_currency_id ? $oShop_Item->Shop_Currency->format(Shop_Controller::instance()->round($node->quantity * $aPrices['price_tax'])) : 0;
			$node->barcodes = implode(', ', $aBarcodes);

			$aReplace['Items'][] = $node;

			$total_quantity += $node->quantity;
			$total_amount += $node->quantity * $aPrices['price_tax'];

			$aReplace['total_count']++;
		}

		$aReplace['quantity'] = $total_quantity;
		$aReplace['amount'] = $oShop->shop_currency_id ? $oShop->Shop_Currency->format(Shop_Controller::instance()->round($total_amount)) : 0;

		$aReplace['amount_in_words'] = '';

		if ($oShop->shop_currency_id)
		{
			$lng = $oShop->Site->lng;

			$aReplace['amount_in_words'] = Core_Inflection::available($lng)
				? Core_Str::ucfirst(Core_Inflection::instance($lng)->currencyInWords($total_amount, $oShop->Shop_Currency->code))
				: $total_amount . ' ' . $oShop->Shop_Currency->sign;
		}

		$aReplace['year'] = date('Y');
		$aReplace['month'] = date('m');
		$aReplace['day'] = date('d');

		Core_Event::notify($this->_modelName . '.onAfterGetPrintlayoutReplaces', $this, array($aReplace));
		$eventResult = Core_Event::getLastReturn();

		return !is_null($eventResult)
			? $eventResult
			: $aReplace;
	}

	/**
	 * Get document full name
	 * @return string
	 */
	public function getDocumentFullName($oAdmin_Form_Controller)
	{
		$color = Core_Str::createColor($this->getEntityType());

		$href = $oAdmin_Form_Controller->getAdminActionLoadHref(array('path' => '/{admin}/shop/warehouse/purchaseorder/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $this->id));

		$onclick = $oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => '/{admin}/shop/warehouse/purchaseorder/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $this->id, 'window' => '', 'width' => '90%'));

		ob_start();

		?><span class="badge badge-round badge-max-width" style="border-color: <?php echo $color?>; background-color: <?php echo Core_Str::hex2lighter($color, 0.88)?>;"><a style="color: <?php echo Core_Str::hex2darker($color, 0.2)?>" href="<?php echo $href?>" onclick="<?php echo $onclick?>"><?php echo Core::_('Shop_Document_Relation.type' . $this->getEntityType())?> № <?php echo htmlspecialchars($this->number)?></a></span><?php

		return ob_get_clean();
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_warehouse_purchaseorder.onBeforeGetRelatedSite
	 * @hostcms-event shop_warehouse_purchaseorder.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Warehouse->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}

	public function createShopWarehouseInvoice()
	{
		$oShop_Warehouse_Invoice = Core_Entity::factory('Shop_Warehouse_Invoice');

		$oShop_Warehouse_Invoice
			->datetime(Core_Date::timestamp2sql(time()))
			->company_id($this->company_id)
			->shop_warehouse_id($this->shop_warehouse_id)
			->siteuser_company_id($this->siteuser_company_id)
			->siteuser_company_contract_id($this->siteuser_company_contract_id)
			->shop_price_id($this->shop_price_id);

		return $oShop_Warehouse_Invoice;
	}

	public function createShopWarehouseSupply()
	{
		$oShop_Warehouse_Supply = Core_Entity::factory('Shop_Warehouse_Supply');

		$oShop_Warehouse_Supply
			->datetime(Core_Date::timestamp2sql(time()))
			->company_id($this->company_id)
			->shop_warehouse_id($this->shop_warehouse_id)
			->siteuser_company_id($this->siteuser_company_id)
			->siteuser_company_contract_id($this->siteuser_company_contract_id)
			->shop_price_id($this->shop_price_id);

		return $oShop_Warehouse_Supply;
	}

	public function getDocumentInfoLink($oAdmin_Form_Controller)
	{
		$oShop = $this->Shop_Warehouse->Shop;

		$color = Core_Str::createColor($this->getEntityType());

		$options = array('path' => '/{admin}/shop/warehouse/purchaseorder/index.php', 'action' => 'edit', 'datasetKey' => 0, 'datasetValue' => $this->id, 'additionalParams' => "shop_id={$oShop->id}", 'window' => '');
		$href = $oAdmin_Form_Controller->getAdminActionLoadHref($options);

		$options['operation'] = 'modal';
		$onclick = $oAdmin_Form_Controller->getAdminActionModalLoad($options);

		return Core::_('Shop_Warehouse_Purchaseorder.based_on_document') . " <span class=\"badge badge-round badge-max-width\" style=\"border-color:{$color}; background-color:" . Core_Str::hex2lighter($color, 0.88) . "\"><a href=\"{$href}\" target=\"_blank\" style=\"color:" . Core_Str::hex2darker($color, 0.2) . "\" onclick=\"{$onclick}\">" . Core::_('Shop_Document_Relation.type' . $this->getEntityType()) . " № " . htmlspecialchars($this->number) . "</a></span>";
	}
}