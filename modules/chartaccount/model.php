<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount_Model
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Chartaccount_Model extends Core_Entity
{
	/**
	 * Callback property_id
	 * @var int
	 */
	public $correct_entries = 1;

	/*array(29) {
		[0]=> int(0)
		[1]=> string(2) "Основные средства"
		[2]=> string(2) "Контрагенты"
		[3]=> string(2) "НМА и расходы на НИОКР"
		[4]=> string(2) "Объекты внеоборотных активов"
		[5]=> string(3) "Статьи затрат"
		[6]=> string(2) "Номенклатура"
		[7]=> string(2) "Договоры"
		[8]=> string(3) "Договоры поставки"
		[9]=> string(3) "Виды расчетов с поставщиками"
		[10]=> string(3) "Виды деятельности"
		[11]=> string(2) "Движение денежных средств"
		[12]=> string(2) "Банковские счета"
		[13]=> string(3) "Ценные бумаги"
		[14]=> string(3) "Виды платежей в бюджет"
		[15]=> string(3) "Сотрудники"
		[16]=> string(3) "Виды начислений"
		[17]=> string(3) "Назначение целевых средств"
		[18]=> string(3) "Источники поступлений"
		[19]=> string(3) "Прочие доходы и расходы"
		[20]=> string(3) "Резервы"
		[21]=> string(3) "Расходы будущих периодов"
		[22]=> string(2) "Доходы будущих периодов"
		[23]=> string(3) "Прибыли и убытки"
		[24]=> string(3) "Виды обеспечения обязательств"
		[25]=> string(3) "ГТД"
		[26]=> string(3) "Места хранения"
		[27]=> string(3) "Даты оплаты"
		[28]=> string(3) "Классы условий труда"
	  }*/

	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'chartaccount';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'user' => array()
	);

	/**
	 * One-to-many or many-to-many relations
	 * @var array
	 */
	protected $_hasMany = array(
		'debit_correct_entry' => array(
			'model' => 'Chartaccount_Correct_Entry',
			'foreign_key' => 'debit'
		),
		'credit_correct_entry' => array(
			'model' => 'Chartaccount_Correct_Entry',
			'foreign_key' => 'credit'
		)
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
		}
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function currencyBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$this->currency && Core_Html_Entity::factory('Span')
			->value('<i class="fa fa-check-circle-o palegreen"></i>')
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function quantitativeBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$this->quantitative && Core_Html_Entity::factory('Span')
			->value('<i class="fa fa-check-circle-o palegreen"></i>')
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function off_balanceBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$this->off_balance && Core_Html_Entity::factory('Span')
			->value('<i class="fa fa-check-circle-o palegreen"></i>')
			->execute();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function sc0Backend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->sc0
			? Core::_('Chartaccount.subcount' . $this->sc0)
			: '';
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function sc1Backend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->sc1
			? Core::_('Chartaccount.subcount' . $this->sc1)
			: '';
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function sc2Backend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return $this->sc2
			? Core::_('Chartaccount.subcount' . $this->sc2)
			: '';
	}

	public function getTypeBadge()
	{
		$color = Core_Str::createColor($this->type);

		return '<span class="badge badge-round badge-max-width margin-left-5" title="' . Core::_('Chartaccount.type' . $this->type) . '" style="border-color: ' . $color . '; color: ' . Core_Str::hex2darker($color, 0.2) . '; background-color: ' . Core_Str::hex2lighter($color, 0.88) . '">'
			. Core::_('Chartaccount.short_type' . $this->type)
			. '</span>';
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function nameBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		echo $this->getTypeBadge();
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function codeBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$class = $this->folder ? 'gray' : '';

		return '<span class="editable ' . $class . '" id="apply_check_0_' . $this->id . '_fv_2111">' . htmlspecialchars($this->code) . '</span>';
	}

	/**
	 * Get correct entries
	 * @return object
	 */
	protected function _getCorrectEntries()
	{
		$oChartaccount_Correct_Entries = Core_Entity::factory('Chartaccount_Correct_Entry');
		$oChartaccount_Correct_Entries->queryBuilder()
			->open()
				->where('chartaccount_correct_entries.debit', '=', $this->id)
				->setOr()
				->where('chartaccount_correct_entries.credit', '=', $this->id)
			->close();

		return $oChartaccount_Correct_Entries;
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function correct_entriesBadge($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		$count = $this->_getCorrectEntries()->getCount(FALSE);
		$count && Core_Html_Entity::factory('Span')
			->class('badge badge-ico badge-palegreen white')
			->value($count < 100 ? $count : '∞')
			->title($count)
			->execute();
	}

	/**
	 * Delete object from database
	 * @param mixed $primaryKey primary key for deleting object
	 * @return Core_Entity
	 * @hostcms-event chartaccount.onBeforeRedeclaredDelete
	 */
	public function delete($primaryKey = NULL)
	{
		if (is_null($primaryKey))
		{
			$primaryKey = $this->getPrimaryKey();
		}
		$this->id = $primaryKey;

		Core_Event::notify($this->_modelName . '.onBeforeRedeclaredDelete', $this, array($primaryKey));

		$this->_getCorrectEntries()->deleteAll(FALSE);

		return parent::delete($primaryKey);
	}
}