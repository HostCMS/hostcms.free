<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Xsl import controller
 *
 * @package HostCMS
 * @subpackage Xsl
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Xsl_Import_Controller extends Admin_Form_Action_Controller
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'content',
		'xsl_dir_id'
	);

	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$oUser = Core_Auth::getCurrentUser();
		if (!$oUser->superuser && $oUser->only_access_my_own)
		{
			return FALSE;
		}

		$aContent = json_decode($this->content, TRUE);

		if (is_array($aContent))
		{
			if (isset($aContent['name']))
			{
				$this->_import($aContent);
			}
			else
			{
				foreach ($aContent as $aXsl)
				{
					$this->_import($aXsl);
				}
			}
		}

		return $this;
	}

	protected function _import(array $aContent = array())
	{
		$oXsl = Core_Entity::factory('Xsl');
		$oXsl->name = $aContent['name'];
		$oXsl->xsl_dir_id = $this->xsl_dir_id;
		$oXsl->format = intval($aContent['format']);
		$oXsl->description = $aContent['description'];
		$oXsl->save();

		$oXsl->name = $oXsl->name . " [{$oXsl->id}]";
		$oXsl->save();

		isset($aContent['xsl'])
			&& $oXsl->saveXslFile($aContent['xsl']);

		// DTD
		$aLngs = Xsl_Controller::getLngs();

		if (isset($aContent['dtds']))
		{
			foreach ($aContent['dtds'] as $lng => $sDtd)
			{
				if (in_array($lng, $aLngs) && $sDtd != '')
				{
					$oXsl->saveLngDtdFile($lng, $sDtd);
				}
			}
		}

		return $this;
	}
}