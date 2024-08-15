<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Sql_Table_Controller_Structure
 *
 * @package HostCMS
 * @subpackage Backup
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Sql_Table_Controller_Structure extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		if (!defined('DENY_INI_SET') || !DENY_INI_SET)
		{
			@set_time_limit(21600);
			ini_set('max_execution_time', '21600');
		}

		$sTablesName = $this->_object->name;

		$oCore_Out_Std = new Core_Out_Std();

		$aConfig = Core_DataBase::instance()->getConfig();

		header("Pragma: public");
		header("Content-Description: File Transfer");
		header("Content-Type: application/force-download");
		header("Content-Disposition: attachment; filename = " . $sTablesName . '_structure_' . date("Y_m_d_H_i_s") . '.sql' . ";");
		header("Content-Transfer-Encoding: binary");

		$oCore_Out_Std->open();

		$oCore_Out_Std->write(
			"-- HostCMS dump\r\n"
			. "-- https://www.hostcms.ru\r\n"
			. "-- Host: " . Core_DataBase::instance()->quote($aConfig['host']) . "\r\n"
			. "-- Database: " . Core_DataBase::instance()->quote($aConfig['database']) . "\r\n"
			. "-- Версия сервера: " . Core_DataBase::instance()->getVersion() . "\r\n\r\n"
			. 'SET NAMES utf8;' . "\r\n"
			. 'SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";' . "\r\n"
			. 'SET SQL_NOTES=0;'
		);

		Core_DataBase::instance()->dumpStructure($sTablesName, $oCore_Out_Std);

		$oCore_Out_Std->close();

		exit();
	}
}