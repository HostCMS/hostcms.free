<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Account_Model
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Company_Account_Model extends Core_Entity
{
	/**
	 * Belongs to relations
	 * @var array
	 */
	protected $_belongsTo = array(
		'company' => array(),
		'user' => array(),
	);

	/**
	 * Default sorting for models
	 * @var array
	 */
	protected $_sorting = array(
		'company_accounts.id' => 'ASC',
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
	 * Switch default status
	 * @return self
	 */
	public function changeDefaultStatus()
	{
		$this->save();

		$oCompany_Accounts = $this->Company->Company_Accounts;
		$oCompany_Accounts
			->queryBuilder()
			->where('company_accounts.default', '=', 1);

		$aCompany_Accounts = $oCompany_Accounts->findAll();

		foreach ($aCompany_Accounts as $oCompany_Account)
		{
			$oCompany_Account->default = 0;
			$oCompany_Account->update();
		}

		$this->default = 1;

		return $this->save();
	}

	/**
	 * Get default cashbox
	 * @param boolean $bCache cache mode
	 * @return self|NULL
	 */
	public function getDefault($bCache = TRUE)
	{
		$this->queryBuilder()
			//->clear()
			->where('company_accounts.default', '=', 1)
			->limit(1);

		$aCompany_Accounts = $this->findAll($bCache);

		return isset($aCompany_Accounts[0])
			? $aCompany_Accounts[0]
			: NULL;
	}
}