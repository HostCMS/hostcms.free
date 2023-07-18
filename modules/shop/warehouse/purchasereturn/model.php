<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Shop_Warehouse_Purchasereturn_Model
 *
 * @package HostCMS
 * @subpackage Shop
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Shop_Warehouse_Purchasereturn_Model extends Core_Entity
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
		'shop_warehouse_purchasereturn_item' => array(),
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
	const TYPE = 9;

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
		return $this->Siteuser_Company_Contract->name;
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

		$this->Shop_Warehouse_Purchasereturn_Items->deleteAll(FALSE);

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

		$aShop_Warehouse_Purchasereturn_Items = $this->Shop_Warehouse_Purchasereturn_Items->findAll(FALSE);
		foreach ($aShop_Warehouse_Purchasereturn_Items as $oShop_Warehouse_Purchasereturn_Item)
		{
			if ($oShop_Warehouse_Purchasereturn_Item->shop_item_id && $oShop_Warehouse_Purchasereturn_Item->Shop_Item->shop_currency_id)
			{
				$price = Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(Core_Entity::factory('Shop_Currency', $oShop_Warehouse_Purchasereturn_Item->Shop_Item->shop_currency_id), $oBaseCurrency) * $oShop_Warehouse_Purchasereturn_Item->price;

				$amount += $price * $oShop_Warehouse_Purchasereturn_Item->count;
			}
		}

		return $amount;
	}

	/**
	 * Get title
	 * @return string
	 */
	public function getTitle()
	{
		return Core::_('Shop_Warehouse_Purchasereturn.title');
	}

	/**
	 * Add entries
	 * @return self
	 */
	public function post()
	{
		// if (!$this->posted)
		// {
			$oShop_Warehouse = $this->Shop_Warehouse;

			$aShop_Warehouse_Entries = $oShop_Warehouse->Shop_Warehouse_Entries->getByDocument($this->id, $this->getEntityType());

			$oBaseCurrency = Core_Entity::factory('Shop_Currency')->getDefault();

			$aEntries = array();

			$aTmp = array();
			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				$aTmp[$oShop_Warehouse_Entry->shop_item_id][] = $oShop_Warehouse_Entry;
			}

			unset($aShop_Warehouse_Entries);

			$limit = 500;
			$offset = 0;

			do {
				$oShop_Warehouse_Purchasereturn_Items = $this->Shop_Warehouse_Purchasereturn_Items;
				$oShop_Warehouse_Purchasereturn_Items->queryBuilder()
					->limit($limit)
					->offset($offset)
					->clearOrderBy()
					->orderBy('id', 'ASC');

				$aShop_Warehouse_Purchasereturn_Items = $oShop_Warehouse_Purchasereturn_Items->findAll(FALSE);
				foreach ($aShop_Warehouse_Purchasereturn_Items as $oShop_Warehouse_Purchasereturn_Item)
				{
					// Удаляем все накопительные значения с датой больше, чем дата документа
					Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($oShop_Warehouse_Purchasereturn_Item->shop_item_id, $oShop_Warehouse->id, $this->datetime);

					if (isset($aTmp[$oShop_Warehouse_Purchasereturn_Item->shop_item_id]) && count($aTmp[$oShop_Warehouse_Purchasereturn_Item->shop_item_id]))
					{
						$oShop_Warehouse_Entry = array_shift($aTmp[$oShop_Warehouse_Purchasereturn_Item->shop_item_id]);
					}
					else
					{
						$oShop_Warehouse_Entry = Core_Entity::factory('Shop_Warehouse_Entry');
						$oShop_Warehouse_Entry->setDocument($this->id, $this->getEntityType());
						$oShop_Warehouse_Entry->shop_item_id = $oShop_Warehouse_Purchasereturn_Item->shop_item_id;
					}

					$oShop_Warehouse_Entry->shop_warehouse_id = $oShop_Warehouse->id;
					$oShop_Warehouse_Entry->datetime = $this->datetime;
					$oShop_Warehouse_Entry->value = $oShop_Warehouse_Purchasereturn_Item->count;
					$oShop_Warehouse_Entry->save();

					$rest = $oShop_Warehouse->getRest($oShop_Warehouse_Purchasereturn_Item->shop_item_id);

					if (!is_null($rest))
					{
						// Recount
						$oShop_Warehouse->setRest($oShop_Warehouse_Purchasereturn_Item->shop_item_id, $rest);
					}

					if (Core::moduleIsActive('chartaccount') && $oShop_Warehouse_Purchasereturn_Item->shop_item_id)
					{
						$oShop_Item = $oShop_Warehouse_Purchasereturn_Item->Shop_Item;

						if ($oShop_Item->shop_item_type_id)
						{
							$oShop_Item_Type = $oShop_Item->Shop_Item_Type;

							if ($oShop_Item_Type->account)
							{
								$price = Shop_Controller::instance()->getCurrencyCoefficientInShopCurrency(Core_Entity::factory('Shop_Currency', $oShop_Warehouse_Purchasereturn_Item->Shop_Item->shop_currency_id), $oBaseCurrency) * $oShop_Warehouse_Purchasereturn_Item->price;

								$amount = $price * $oShop_Warehouse_Purchasereturn_Item->count;

								$aEntries[$oShop_Item_Type->Account_Chartaccount->code][] = $amount;
							}
						}
					}
				}

				$offset += $limit;
			}
			while (count($aShop_Warehouse_Purchasereturn_Items));

			if (Core::moduleIsActive('chartaccount'))
			{
				$oDChartaccount = Core_Entity::factory('Chartaccount')->getByCode('76.2');
				if (!is_null($oDChartaccount))
				{
					foreach ($aEntries as $account => $aAmounts)
					{
						$document_id = Shop_Controller::getDocumentId($this->id, $this->getEntityType());

						$total_amount = array_sum($aAmounts);
						$total_amount = number_format(Shop_Controller::instance()->round($total_amount), 2, '.', '');

						$aEntry['debit'] = $oDChartaccount->code;
						$aEntry['debit_sc'] = array(2 => $this->siteuser_company_id, 7 => $this->siteuser_company_contract_id);

						$aEntry['credit'] = $account;
						$aEntry['credit_sc'] = array();

						$aEntry['amount'] = $total_amount;

						Chartaccount_Entry_Controller::insertEntry($document_id, $this->company_id, $aEntry);
					}
				}
			}

			$this->posted = 1;
			$this->save();
		// }

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
			$aShop_Warehouse_Entries = Core_Entity::factory('Shop_Warehouse_Entry')->getByDocument($this->id, $this->getEntityType());

			foreach ($aShop_Warehouse_Entries as $oShop_Warehouse_Entry)
			{
				// Удаляем все накопительные значения с датой больше, чем дата документа
				Shop_Warehouse_Entry_Accumulate_Controller::deleteEntries($oShop_Warehouse_Entry->shop_item_id, $oShop_Warehouse_Entry->shop_warehouse_id, $this->datetime);

				$shop_item_id = $oShop_Warehouse_Entry->shop_item_id;
				$oShop_Warehouse = $oShop_Warehouse_Entry->Shop_Warehouse;

				// Delete Entry
				$oShop_Warehouse_Entry->delete();

				$rest = $oShop_Warehouse->getRest($shop_item_id);

				if (!is_null($rest))
				{
					// Recount
					$oShop_Warehouse->setRest($shop_item_id, $rest);
				}
			}

			if (Core::moduleIsActive('chartaccount'))
			{
				$document_id = Shop_Controller::getDocumentId($this->id, $this->getEntityType());
				Chartaccount_Entry_Controller::deleteEntriesByDocumentId($document_id);
			}

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
		return htmlspecialchars($this->Shop_Warehouse->name);
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

			$aShop_Warehouse_Purchasereturn_Items = $this->Shop_Warehouse_Purchasereturn_Items->findAll(FALSE);

			foreach ($aShop_Warehouse_Purchasereturn_Items as $oShop_Warehouse_Purchasereturn_Item)
			{
				$aBackup['items'][] = array(
					'shop_item_id' => $oShop_Warehouse_Purchasereturn_Item->shop_item_id,
					'price' => $oShop_Warehouse_Purchasereturn_Item->price,
					'count' => $oShop_Warehouse_Purchasereturn_Item->count,
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
					$this->Shop_Warehouse_Purchasereturn_Items->deleteAll(FALSE);

					// Создаем новые
					foreach ($aAllItems as $aShop_Warehouse_Purchasereturn_Item)
					{
						$oShop_Warehouse_Purchasereturn_Item = Core_Entity::factory('Shop_Warehouse_Purchasereturn_Item');
						$oShop_Warehouse_Purchasereturn_Item->shop_warehouse_purchasereturn_id = $this->id;
						$oShop_Warehouse_Purchasereturn_Item->shop_item_id = Core_Array::get($aShop_Warehouse_Purchasereturn_Item, 'shop_item_id');
						$oShop_Warehouse_Purchasereturn_Item->count = Core_Array::get($aShop_Warehouse_Purchasereturn_Item, 'count');
						$oShop_Warehouse_Purchasereturn_Item->save();
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
		$count = $this->Shop_Warehouse_Purchasereturn_Items->getCount();
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-danger badge-square')
			->value($count)
			->execute();
	}

	/**
	 * Get printlayout replaces
	 * @return array
	 * @hostcms-event shop_warehouse_purchasereturn.onAfterGetPrintlayoutReplaces
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
			'type' => Core::_('Shop_Warehouse_Purchasereturn.title'),
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

		$total_amount = $total_quantity = $total_tax = 0;

		$Shop_Item_Controller = new Shop_Item_Controller();

		$aShop_Warehouse_Purchasereturn_Items = $this->Shop_Warehouse_Purchasereturn_Items->findAll();

		foreach ($aShop_Warehouse_Purchasereturn_Items as $oShop_Warehouse_Purchasereturn_Item)
		{
			$oShop_Item = $oShop_Warehouse_Purchasereturn_Item->Shop_Item;

			// $amount = Shop_Controller::instance()->round($oShop_Warehouse_Purchasereturn_Item->count * $oShop_Warehouse_Purchasereturn_Item->price);

			$aPrices = $Shop_Item_Controller->calculatePrice($oShop_Warehouse_Purchasereturn_Item->price, $oShop_Item);

			$amount = Shop_Controller::instance()->round($oShop_Warehouse_Purchasereturn_Item->count * $aPrices['price_tax']);

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
			$node->marking = htmlspecialchars((string) $oShop_Item->marking);
			$node->measure = htmlspecialchars((string) $oShop_Item->Shop_Measure->name);
			$node->currency = htmlspecialchars($oShop_Item->Shop_Currency->sign);
			$node->okei = htmlspecialchars((string) $oShop_Item->Shop_Measure->okei);
			$node->price = $oShop_Item->Shop_Currency->format($aPrices['price_tax']);
			$node->quantity = Core_Str::hideZeros($oShop_Warehouse_Purchasereturn_Item->count);
			$node->amount = $oShop_Item->Shop_Currency->format($amount);
			$node->tax = $oShop_Item->shop_tax_id && $oShop_Item->Shop_Tax->rate ? Shop_Controller::instance()->round($amount * $oShop_Item->Shop_Tax->rate / 100) : 0;
			$node->amount_tax_included = $oShop_Item->Shop_Currency->format(Shop_Controller::instance()->round($amount + $node->tax));
			$node->barcodes = implode(', ', $aBarcodes);

			$aReplace['Items'][] = $node;

			$total_quantity += $node->quantity;
			$total_tax += $node->tax;
			$total_amount += $node->quantity * $aPrices['price_tax'];

			$aReplace['total_count']++;
		}

		$aReplace['quantity'] = $total_quantity;
		$aReplace['tax'] = Shop_Controller::instance()->round($total_tax);
		$aReplace['amount'] = $oShop_Item->Shop_Currency->format(Shop_Controller::instance()->round($total_amount));
		$aReplace['amount_tax_included'] = $oShop_Item->Shop_Currency->format(Shop_Controller::instance()->round($this->getAmount()));

		$lng = $oShop->Site->lng;

		$aReplace['amount_in_words'] = Core_Inflection::available($lng)
			? Core_Str::ucfirst(Core_Inflection::instance($lng)->currencyInWords($total_amount, $oShop->Shop_Currency->code))
			: $total_amount . ' ' . $oShop->Shop_Currency->sign;

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

		$href = $oAdmin_Form_Controller->getAdminActionLoadHref(array('path' => '/admin/shop/warehouse/purchasereturn/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $this->id));

		$onclick = $oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => '/admin/shop/warehouse/purchasereturn/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $this->id, 'window' => '', 'width' => '90%'));

		ob_start();

		?><span class="badge badge-round badge-max-width" style="border-color: <?php echo $color?>; background-color: <?php echo Core_Str::hex2lighter($color, 0.88)?>;"><a style="color: <?php echo Core_Str::hex2darker($color, 0.2)?>" href="<?php echo $href?>" onclick="<?php echo $onclick?>"><?php echo Core::_('Shop_Document_Relation.type' . $this->getEntityType())?> № <?php echo htmlspecialchars($this->number)?></a></span><?php

		return ob_get_clean();
	}

	/**
	 * Get Related Site
	 * @return Site_Model|NULL
	 * @hostcms-event shop_warehouse_purchasereturn.onBeforeGetRelatedSite
	 * @hostcms-event shop_warehouse_purchasereturn.onAfterGetRelatedSite
	 */
	public function getRelatedSite()
	{
		Core_Event::notify($this->_modelName . '.onBeforeGetRelatedSite', $this);

		$oSite = $this->Shop_Warehouse->Shop->Site;

		Core_Event::notify($this->_modelName . '.onAfterGetRelatedSite', $this, array($oSite));

		return $oSite;
	}

	public function getBasedOnDocument()
	{
		$document_id = Shop_Controller::getDocumentId($this->id, $this->getEntityType());

		$oShop_Document_Relation = Core_Entity::factory('Shop_Document_Relation')->getByDocument_id($document_id);

		return !is_null($oShop_Document_Relation)
			? Shop_Controller::getDocument($oShop_Document_Relation->related_document_id)
			: NULL;
	}
}