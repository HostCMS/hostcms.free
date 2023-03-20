<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount_Entry_Model
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Chartaccount_Entry_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'chartaccount_entry';

	/**
	 * Disable markDeleted()
	 * @var mixed
	 */
	protected $_marksDeleted = NULL;

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'company' => array(),
		'chartaccount_debit' => array('model' => 'Chartaccount', 'foreign_key' => 'dchartaccount_id'),
		'chartaccount_credit' => array('model' => 'Chartaccount', 'foreign_key' => 'cchartaccount_id'),
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'chartaccount_entry_subcount' => array()
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
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
		}
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function document_idBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$oObject = Chartaccount_Controller::getDocument($this->document_id);

		return !is_null($oObject)
			? (method_exists($oObject, 'getDocumentFullName')
				? $oObject->getDocumentFullName($oAdmin_Form_Controller)
				: Core::_('Shop_Document_Relation.type' . $oObject->getEntityType()) . ' №' . htmlspecialchars($oObject->number)
			)
			: 'undefined';
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

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function amountBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return Chartaccount_Trialbalance_Controller::printAmount($this->amount);
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function debitBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->dchartaccount_id)
		{
			$oChartaccount_Debit = $this->Chartaccount_Debit;

			Chartaccount_Controller::setAdminFormController($oAdmin_Form_Controller);
			$aSubcounts = Chartaccount_Controller::getSubcounts($this);

			ob_start();

			/*?><div class="semi-bold"><?php echo htmlspecialchars((string) $oChartaccount_Debit->code)?></div><?php*/
			?><div><?php echo Chartaccount_Controller::getCode($oChartaccount_Debit)?></div><?php

			if (isset($aSubcounts['debit']))
			{
				foreach ($aSubcounts['debit'] as $aDSubcount)
				{
					if (is_array($aDSubcount))
					{
						$color = 'style="color: ' . (isset($aDSubcount['color'])
							?  $aDSubcount['color']
							: '#0092d6') . '"';

						?><div class="small" <?php echo $color?>><?php
						if (isset($aDSubcount['onclick']) && $aDSubcount['onclick'] != '')
						{
							?><a style="color: inherit" href="<?php echo $aDSubcount['href']?>" onclick="<?php echo $aDSubcount['onclick']?>"><?php echo htmlspecialchars((string) $aDSubcount['name']);?></a><?php
						}
						else
						{
							echo htmlspecialchars((string) $aDSubcount['name']);
						}
						?></div><?php
					}
				}
			}

			return ob_get_clean();
		}
	}

	/**
	 * Backend callback method
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function creditBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		if ($this->cchartaccount_id)
		{
			$oChartaccount_Credit = $this->Chartaccount_Credit;

			Chartaccount_Controller::setAdminFormController($oAdmin_Form_Controller);
			$aSubcounts = Chartaccount_Controller::getSubcounts($this);

			ob_start();

			/*?><div class="semi-bold"><?php echo htmlspecialchars((string) $oChartaccount_Credit->code)?></div><?php*/
			?><div><?php echo Chartaccount_Controller::getCode($oChartaccount_Credit)?></div><?php

			if (isset($aSubcounts['credit']))
			{
				foreach ($aSubcounts['credit'] as $aCSubcount)
				{
					if (is_array($aCSubcount))
					{
						$color = 'style="color: ' . (isset($aCSubcount['color'])
							?  $aCSubcount['color']
							: '#0092d6') . '"';

						?><div class="small" <?php echo $color?>><?php
						if (isset($aCSubcount['onclick']) && $aCSubcount['onclick'] != '')
						{
							?><a style="color: inherit" href="<?php echo $aCSubcount['href']?>" onclick="<?php echo $aCSubcount['onclick']?>"><?php echo htmlspecialchars((string) $aCSubcount['name']);?></a><?php
						}
						else
						{
							echo htmlspecialchars((string) $aCSubcount['name']);
						}
						?></div><?php
					}
				}
			}

			return ob_get_clean();
		}
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

		$this->Chartaccount_Entry_Subcounts->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}