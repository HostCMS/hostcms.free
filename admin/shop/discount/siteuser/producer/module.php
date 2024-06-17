<?php

$tmpDir = Market_Controller::instance()->tmpDir . DIRECTORY_SEPARATOR;

$aOptions = Market_Controller::instance()->options;

$aReplace = array();

$sLng = 'ru';
$aPossibleLanguages = array('en', 'ru');
$sSitePostfix = '';

foreach ($aOptions as $optionName => $optionValue)
{
	$aReplace['%' . $optionName . '%'] = $optionValue;
}

$Install_Controller = Install_Controller::instance();
$Install_Controller->setTemplatePath($tmpDir);

// Массив XSL-шаблонов
$aXsli18n = array(
	'245.xsl' => array('name' => 'SMSКодПодтверждения', 'dirName' => 'CMC'),
);

//Xsls
foreach ($aXsli18n as $sFileName => $aXsl)
{
	$oXsl = Core_Entity::factory('Xsl')->getByName($aXsl['name'], FALSE);

	if (is_null($oXsl))
	{
		$iParent_Id = 0;

		if ($aXsl['dirName'] != '')
		{
			$aExplodeDir = explode('/', $aXsl['dirName']);
			// array_unshift($aExplodeDir);

			foreach ($aExplodeDir as $sDirName)
			{
				$oXsl_Dir = Core_Entity::factory('Xsl_Dir');
				$oXsl_Dir
					->queryBuilder()
					->where('xsl_dirs.parent_id', '=', $iParent_Id);

				$oXsl_Dir = $oXsl_Dir->getByName($sDirName, FALSE);

				if (is_null($oXsl_Dir))
				{
					$oXsl_Dir = Core_Entity::factory('Xsl_Dir');
					$oXsl_Dir
						->parent_id($iParent_Id)
						->name($sDirName)
						->save();
				}

				$iParent_Id = $oXsl_Dir->id;
			}
		}

		$oXsl = Core_Entity::factory('Xsl');
		$oXsl
			->name($aXsl['name'])
			->xsl_dir_id($iParent_Id)
			->save();

		$aReplace["'{$aXsl['name']}'"] = "'" . $oXsl->name . "'";
		$aReplace['"lang://' . $aXsl['id'] . '"'] = '"lang://' . $oXsl->id . '"';

		$oXsl->saveXslFile($Install_Controller->loadFile($tmpDir . "tmp/hostcmsfiles/xsl/" . $sFileName, $aReplace));
	}
	else
	{
		$aReplace["'{$aXsl['name']}'"] = "'" . $oXsl->name . "'";
	}
}