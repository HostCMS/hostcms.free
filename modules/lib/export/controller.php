<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Libs.
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Lib_Export_Controller extends Admin_Form_Action_Controller
{
	/**
	 * Executes the business logic.
	 * @param mixed $operation Operation name
	 * @return self
	 */
	public function execute($operation = NULL)
	{
		$oLib = $this->_object;

		$sFilename = $oLib->name . '_' . date("Y_m_d_H_i_s") . '.json';

		header("Pragma: public");
		header("Content-Description: File Transfer");
		header("Content-Type: " . Core_Mime::getFileMime($sFilename));
		header("Content-Disposition: attachment; filename = \"" . str_replace(array("\r", "\n", "\0"), '', $sFilename) . "\";");
		header("Content-Transfer-Encoding: binary");

		$aReturn = array(
			'version' => CURRENT_VERSION,
			'name' => $oLib->name,
			'lib' => $oLib->loadLibFile(),
			'lib_config' => $oLib->loadLibConfigFile(),
			'options' => array()
		);

		// Параметры ТДС
		$aLib_Properties = $oLib->Lib_Properties->findAll(FALSE);
		foreach ($aLib_Properties as $oLib_Property)
		{
			$aLibProperty = array(
				'name' => $oLib_Property->name,
				'varible_name' => $oLib_Property->varible_name,
				'type' => $oLib_Property->type,
				'default_value' => $oLib_Property->default_value,
				'multivalue' => $oLib_Property->multivalue,
				'sorting' => $oLib_Property->sorting
			);

			if ($oLib_Property->type == 4)
			{
				$aLibProperty['sql_request'] = $oLib_Property->sql_request;
				$aLibProperty['sql_caption_field'] = $oLib_Property->sql_caption_field;
				$aLibProperty['sql_value_field'] = $oLib_Property->sql_value_field;
			}

			// Значения параметров ТДС
			$aLib_Property_List_Values = $oLib_Property->Lib_Property_List_Values->findAll(FALSE);
			foreach ($aLib_Property_List_Values as $oLib_Property_List_Value)
			{
				$aLibProperty['values'][] = array(
					'name' => $oLib_Property_List_Value->name,
					'value' => $oLib_Property_List_Value->value
				);
			}

			$aReturn['options'][] = $aLibProperty;
		}

		echo json_encode($aReturn);

		exit();
	}
}