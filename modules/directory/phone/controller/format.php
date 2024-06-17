<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Directory_Phone_Controller_Format
 *
 * @package HostCMS
 * @subpackage Directory
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Directory_Phone_Controller_Format extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 */
	public function execute($operation = NULL)
	{
		if (!defined('DENY_INI_SET') || !DENY_INI_SET)
		{
			set_time_limit(90000);
			ini_set("max_execution_time", "90000");
			ini_set("memory_limit", "512M");
		}

		$offset = 0;
		$limit = 500;

		do {
			$oDirectory_Phones = Core_Entity::factory('Directory_Phone');
			$oDirectory_Phones->queryBuilder()
				->clearOrderBy()
				->orderBy('directory_phones.id')
				->offset($offset)
				->limit($limit);

			$aDirectory_Phones = $oDirectory_Phones->findAll(FALSE);

			foreach ($aDirectory_Phones as $oDirectory_Phone)
			{
				if ($oDirectory_Phone->value != '')
				{
					if (strpos($oDirectory_Phone->value, ',') !== FALSE)
					{
						$aExplode = explode(',', $oDirectory_Phone->value);
						$aExplode = array_filter(array_map('trim', $aExplode), 'strlen');
						$aExplode = array_unique($aExplode);

						if (isset($aExplode[0]))
						{
							// Change first value
							$oDirectory_Phone->value = array_shift($aExplode);

							foreach ($aExplode as $sExplode)
							{
								$oDirectory_Phone_New = clone $oDirectory_Phone;
								$oDirectory_Phone_New->value = Directory_Phone_Controller::format($sExplode);
								$oDirectory_Phone_New->save();
							}
						}
					}

					$oDirectory_Phone->value = Directory_Phone_Controller::format($oDirectory_Phone->value);
					$oDirectory_Phone->save();
				}
			}

			$offset += $limit;
		}
		while (count($aDirectory_Phones));

		return $this;
	}
}