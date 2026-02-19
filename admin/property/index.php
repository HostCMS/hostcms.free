<?php
/**
 * Properties.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

if (Core_Auth::logged())
{
	if (!is_null(Core_Array::getGet('autocomplete'))
		&& !is_null(Core_Array::getGet('show_dir'))
		&& !is_null(Core_Array::getGet('queryString'))
	)
	{
		$sQuery = trim(Core_DataBase::instance()->escapeLike(Core_Str::stripTags(strval(Core_Array::getGet('queryString')))));
		$modelName = Core_Array::getGet('linkedObjectName', '', 'trim');
		$id = Core_Array::getGet('linkedObjectId', 0, 'int');
		$linkedObject = Core_Entity::factory($modelName, $id);

		$aJSON = array();

		$aJSON[] = array(
			'id' => 0,
			'label' => Core::_('Property_Dir.root'),
		);

		// Ends on _Property_List
		if (preg_match('/_property_list$/', $modelName))
		{
			if (strlen($sQuery))
			{
				$sQueryLike = '%' . str_replace(' ', '%', $sQuery) . '%';

				$oProperty_Dirs = $linkedObject->Property_Dirs;
				$oProperty_Dirs->queryBuilder()
					->where('property_dirs.name', 'LIKE', $sQueryLike)
					->limit(Core::$mainConfig['autocompleteItems']);

				$aProperty_Dirs = $oProperty_Dirs->findAll(FALSE);

				foreach ($aProperty_Dirs as $oProperty_Dir)
				{
					$aParentDirs = array();

					$aTmpDir = $oProperty_Dir;

					// Добавляем все директории от текущей до родителя.
					do {
						$aParentDirs[] = $aTmpDir->name;
					} while ($aTmpDir = $aTmpDir->getParent());

					$sParents = implode(' → ', array_reverse($aParentDirs));

					$aJSON[] = array(
						'id' => $oProperty_Dir->id,
						'label' => $sParents
					);
				}
			}
		}
		else
		{
			$aJSON['error'] = 'Wrong linkedObjectName';
		}

		Core::showJson($aJSON);
	}
}