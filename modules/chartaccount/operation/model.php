<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount_Operation_Model
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Chartaccount_Operation_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'chartaccount_operation';

	/**
	 * Document type
	 * @var int
	 */
	const TYPE = 70;

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
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'chartaccount_operation_item' => array(),
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
			$this->_preloadValues['datetime'] = Core_Date::timestamp2sql(time());
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
	 * Add entries
	 * @return self
	 */
	public function post()
	{
		if ($this->company_id)
		{
			$document_id = Chartaccount_Controller::getDocumentId($this->id, $this->getEntityType());

			$aChartaccount_Operation_Items = $this->Chartaccount_Operation_Items->findAll(FALSE);
			foreach ($aChartaccount_Operation_Items as $oChartaccount_Operation_Item)
			{
				if ($oChartaccount_Operation_Item->dchartaccount_id && $oChartaccount_Operation_Item->cchartaccount_id)
				{
					$oDChartaccount = $oChartaccount_Operation_Item->Chartaccount_Debit;
					$oCChartaccount = $oChartaccount_Operation_Item->Chartaccount_Credit;

					$aEntry = array(
						'debit' => $oDChartaccount->code,
						'debit_sc' => array(
							$oDChartaccount->sc0 => $oChartaccount_Operation_Item->dsc0,
							$oDChartaccount->sc1 => $oChartaccount_Operation_Item->dsc1,
							$oDChartaccount->sc2 => $oChartaccount_Operation_Item->dsc2
						),
						'credit' => $oCChartaccount->code,
						'credit_sc' => array(
							$oCChartaccount->sc0 => $oChartaccount_Operation_Item->csc0,
							$oCChartaccount->sc1 => $oChartaccount_Operation_Item->csc1,
							$oCChartaccount->sc2 => $oChartaccount_Operation_Item->csc2
						),
						'amount' => $oChartaccount_Operation_Item->amount,
						'datetime' => $this->datetime
					);

					Chartaccount_Entry_Controller::insertEntries($document_id, $this->company_id, array($aEntry));
				}
			}

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
	 * Get document full name
	 * @return string
	 */
	public function getDocumentFullName($oAdmin_Form_Controller)
	{
		$color = Core_Str::createColor($this->getEntityType());

		$href = $oAdmin_Form_Controller->getAdminActionLoadHref(array('path' => '/admin/chartaccount/operation/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $this->id));

		$onclick = $oAdmin_Form_Controller->getAdminActionModalLoad(array('path' => '/admin/chartaccount/operation/index.php', 'action' => 'edit', 'operation' => 'modal', 'datasetKey' => 0, 'datasetValue' => $this->id, 'window' => '', 'width' => '90%'));

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

		$this->Chartaccount_Operation_Items->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}