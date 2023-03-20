<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Company_Cashbox_Model
 *
 * @package HostCMS
 * @subpackage Company
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Company_Cashbox_Model extends Core_Entity
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

		$oCompany_Cashboxes = $this->Company->Company_Cashboxes;
		$oCompany_Cashboxes
			->queryBuilder()
			->where('company_cashboxes.default', '=', 1);

		$aCompany_Cashboxes = $oCompany_Cashboxes->findAll();

		foreach ($aCompany_Cashboxes as $oCompany_Cashbox)
		{
			$oCompany_Cashbox->default = 0;
			$oCompany_Cashbox->update();
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
			->where('company_cashboxes.default', '=', 1)
			->limit(1);

		$aCompany_Cashboxes = $this->findAll($bCache);

		return isset($aCompany_Cashboxes[0])
			? $aCompany_Cashboxes[0]
			: NULL;
	}
}