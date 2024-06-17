<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Chartaccount_Correct_Entry_Model
 *
 * @package HostCMS
 * @subpackage Chartaccount
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Chartaccount_Correct_Entry_Model extends Core_Entity
{
	/**
	 * Model name
	 * @var mixed
	 */
	protected $_modelName = 'chartaccount_correct_entry';

	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'debit_chartaccount' => array('model' => 'Chartaccount', 'foreign_key' => 'debit'),
		'credit_chartaccount' => array('model' => 'Chartaccount', 'foreign_key' => 'credit'),
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
		}
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function debitBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return '<span class="semi-bold margin-right-5">' . $this->Debit_Chartaccount->code . '</span>';
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function debit_nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return htmlspecialchars($this->Debit_Chartaccount->name);
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function creditBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return '<span class="semi-bold margin-right-5">' . $this->Credit_Chartaccount->code . '</span>';
	}

	/**
	 * Backend badge
	 * @param Admin_Form_Field $oAdmin_Form_Field
	 * @param Admin_Form_Controller $oAdmin_Form_Controller
	 * @return string
	 */
	public function credit_nameBackend($oAdmin_Form_Field, $oAdmin_Form_Controller)
	{
		return htmlspecialchars($this->Credit_Chartaccount->name);
	}
}