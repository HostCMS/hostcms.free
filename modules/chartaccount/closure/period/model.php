<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount_Closure_Period_Model
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Chartaccount_Closure_Period_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'chartaccount_closure_period';

	/**
	 * Document type
	 * @var int
	 */
	const TYPE = 71;

	/**
	 * Get Entity Type
	 * @return int
	 */
	public function getEntityType()
	{
		return self::TYPE;
	}

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'company' => array(),
		'user' => array()
	);

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
			// $this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
			$this->_preloadValues['datetime'] = date('Y-m-d', strtotime('last day of previous month')) . ' 23:59:59';
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
	 * @return string
	 */
	public function сlosure_cost_accountingBackend()
	{
		return $this->сlosure_cost_accounting
			? '<i class="fa fa-check-circle-o green">'
			: '<i class="fa fa-times-circle-o red">';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function financial_resultBackend()
	{
		return $this->financial_result
			? '<i class="fa fa-check-circle-o green">'
			: '<i class="fa fa-times-circle-o red">';
	}

	/**
	 * Backend callback method
	 * @return string
	 */
	public function balance_reformationBackend()
	{
		return $this->balance_reformation
			? '<i class="fa fa-check-circle-o green">'
			: '<i class="fa fa-times-circle-o red">';
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function company_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		// return htmlspecialchars((string) $this->Company->name);
		return $this->company_id
			? '<div class="profile-container tickets-container counterparty-block"><ul class="tickets-list">' . $this->Company->getProfileBlock() . '</ul></div>'
			: '';
	}

	protected function _insertEntries($aEntries)
	{
		$document_id = Chartaccount_Controller::getDocumentId($this->id, $this->getEntityType());

		$date_from = date('Y-m-01', Core_Date::datetime2timestamp($this->datetime));
		$date_to = date('Y-m-d', Core_Date::datetime2timestamp($this->datetime));

		$aTmp = array();

		foreach ($aEntries as $aEntry)
		{
			$oChartaccount = Core_Entity::factory('Chartaccount')->getByCode($aEntry['credit']);

			if (!is_null($oChartaccount))
			{
				// Обороты за период для $credit_code
				$aOptions = array('company_id' => $this->company_id, 'dchartaccount' => $aEntry['credit'], 'date_from' => $date_from, 'date_to' => $date_to);
				$dAmountPeriod = Chartaccount_Entry_Controller::getEntriesAmount($aOptions);

				$aOptions = array('company_id' => $this->company_id, 'cchartaccount' => $aEntry['credit'], 'date_from' => $date_from, 'date_to' => $date_to);
				$cAmountPeriod = Chartaccount_Entry_Controller::getEntriesAmount($aOptions);

				$amount = 0;
				if ($oChartaccount->type == 0)
				{
					$amount = $dAmountPeriod - $cAmountPeriod;
				}
				elseif ($oChartaccount->type == 2)
				{
					if ($dAmountPeriod == $cAmountPeriod)
					{
						$amount = 0;
					}
					elseif ($dAmountPeriod > $cAmountPeriod)
					{
						$amount = $dAmountPeriod - $cAmountPeriod;
					}
				}

				if ($amount != 0)
				{
					$aTmp[] = array(
						'debit' => $aEntry['debit'],
						'debit_sc' => 0,
						'credit' => $aEntry['credit'],
						'credit_sc' => 0,
						'amount' => $amount,
						'datetime' => $date_to . ' 23:59:59'
					);
				}
			}
		}

		count($aTmp) && Chartaccount_Entry_Controller::insertEntries($document_id, $this->company_id, $aTmp);

		return $this;
	}

	/**
	 * Add entries
	 * @return self
	 */
	public function post()
	{
		if ($this->company_id)
		{
			$aEntries = array();

			// Закрытие затратных счетов (Ежемесячно)
			if ($this->сlosure_cost_accounting)
			{
				/*
				Дебет | Кредит
				--------------
				90-07 | 44
				20    | 25
				90-08 | 26
				90-02 | 20
				*/
				$aEntries[] = array('credit' => '44', 'debit' => '90.7');
				$aEntries[] = array('credit' => '25', 'debit' => '20');
				$aEntries[] = array('credit' => '26', 'debit' => '90.8');
				$aEntries[] = array('credit' => '20', 'debit' => '90.2');

				//  $this->_insertEntries($aEntries);
			}

			// Определение финансового результата (Ежемесячно)
			if ($this->financial_result)
			{
				/*
				Дебет | Кредит
				--------------
				99-01 | 90-09 // отражается прибыль от обычных видов деятельности
				99-01 | 91-09 // отражается убыток от прочих видов деятельности
				*/

				/*Ежемесячно по результатам своей деятельности организация определяет финансовый результат:
				Дт 90.9 Кт 99 – отражается прибыль от обычных видов деятельности;
				Дт 99 Кт 90.9 – отражается убыток от обычных видов деятельности.

				По прочим доходам и расходам финансовый результат отражается следующим образом:
				Дт 91.9 Кт 99 – отражается прибыль от прочих видов деятельности;
				Дт 99 Кт 91.9 – отражается убыток от прочих видов деятельности.
				*/
				$aEntries[] = array('credit' => '90.9', 'debit' => '99'); // отражается убыток от обычных видов деятельности
				$aEntries[] = array('credit' => '91.9', 'debit' => '99'); // отражается убыток от прочих видов деятельности

				// $this->_insertEntries($aEntries);
			}

			// Реформация баланса (В конце года)
			if ($this->balance_reformation)
			{
				/*
				Дебет | Кредит
				--------------
				90-01 | 90-09 // закрывается субсчет «Выручка»
				90-09 | 90-02 // закрывается субсчет «Себестоимость продаж»
				90-09 | 90-03 // закрывается субсчет «НДС»
				90-09 | 90-04
				90-09 | 90-07
				90-09 | 90-08
				91-01 | 91-09 // закрывается субсчет «Прочие доходы»
				91-09 | 91-02
				91-09 | 91-03
				///
				84-02 | 99-01
				99-01 | 84-01
				*/
				$aEntries[] = array('credit' => '90.9', 'debit'  => '90.1'); // закрывается субсчет «Выручка»
				$aEntries[] = array('credit' => '90.2', 'debit'  => '90.9'); //  закрывается субсчет «Себестоимость продаж»
				$aEntries[] = array('credit' => '90.3', 'debit'  => '90.9');
				$aEntries[] = array('credit' => '90.4', 'debit'  => '90.9');
				$aEntries[] = array('credit' => '90.7', 'debit'  => '90.9');
				$aEntries[] = array('credit' => '90.8', 'debit'  => '90.9');
				$aEntries[] = array('credit' => '91.9', 'debit'  => '91.1'); // закрывается субсчет «Прочие доходы»
				$aEntries[] = array('credit' => '91.2', 'debit'  => '91.9'); // закрывается субсчет «Прочие расходы»
				$aEntries[] = array('credit' => '91.3', 'debit'  => '91.9');
			}

			$this->_insertEntries($aEntries);

			$this->posted = 1;
			$this->save();
		}
		else
		{
			$this->unpost();
		}

		return $this;
	}

	/**
	 * Remove all  entries by document
	 * @return self
	 */
	public function unpost()
	{
		if ($this->posted)
		{
			$document_id = Chartaccount_Controller::getDocumentId($this->id, $this->getEntityType());

			Chartaccount_Entry_Controller::deleteEntriesByDocumentId($document_id);

			$this->posted = 0;
			$this->save();
		}

		return $this;
	}

	/**
	 * Get document full name
	 * @return string
	 */
	public function getDocumentFullName($oAdmin_Form_Controller)
	{
		$color = Core_Str::createColor($this->getEntityType());

		$href = $oAdmin_Form_Controller->getAdminActionLoadHref(array('path' => '/admin/chartaccount/closure/period/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $this->id));

		$onclick = $oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => '/admin/chartaccount/closure/period/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $this->id, 'window' => '', 'width' => '90%'));

		ob_start();

		?><span class="badge badge-round badge-max-width" style="border-color: <?php echo $color?>; background-color: <?php echo Core_Str::hex2lighter($color, 0.88)?>;"><a style="color: <?php echo Core_Str::hex2darker($color, 0.2)?>" href="<?php echo $href?>" onclick="<?php echo $onclick?>"><?php echo Core::_('Shop_Document_Relation.type' . $this->getEntityType())?> № <?php echo htmlspecialchars($this->number)?></a></span><?php

		return ob_get_clean();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event chartaccount_operation.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}

		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->unpost();

		return parent::delete($primaryKey);
	}
}