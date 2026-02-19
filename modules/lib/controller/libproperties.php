<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib.
 * Типовой контроллер загрузки свойст типовой дин. страницы для структуры
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
abstract class Lib_Controller_Libproperties extends Admin_Form_Action_Controller
{
	/**
	 * Lib ID
	 * @var int
	 */
	protected $_libId = NULL;

	/**
	 * Set lib ID
	 * @param int $libId
	 * @return self
	 */
	public function libId($libId)
	{
		$this->_libId = $libId;
		return $this;
	}

	static protected $_aSortingTree = array();

    /**
     * Get JSON
     * @param Core_Entity $oObject
     * @param array $aLib_Properties
     * @param array $aOptions
     * @param string $prefix
     * @return array
     */
	static protected function _getJson(Core_Entity $oObject, $aLib_Properties, $aOptions, $prefix = 'lib_property_')
	{
		$LA = array();

		$iPrefixLen = strlen($prefix);
		foreach ($_POST as $key => $value)
		{
			if (strpos($key, $prefix) === 0)
			{
				// lib_property_107_0
				// lib_property_107[]
				// lib_property_0_	107_0
				// lib_property_0_	109_0
				// lib_property_1_	107_1
				// lib_property_1_	109_1
				$aTmp = explode('_', substr($key, $iPrefixLen));
				if (count($aTmp) == 2)
				{
					!isset(self::$_aSortingTree[$aTmp[0]]) && self::$_aSortingTree[$aTmp[0]] = array();

					!in_array($aTmp[1], self::$_aSortingTree[$aTmp[0]])
						&& self::$_aSortingTree[$aTmp[0]][] = $aTmp[1];
				}
			}
		}

		// echo "<pre>";
		// var_dump($_POST);
		// echo "</pre>";

		foreach ($aLib_Properties as $oLib_Property)
		{
			$propertyName = $prefix . $oLib_Property->id;

			$propertyValue = $oLib_Property->multivalue
				? array()
				: NULL;

			// Существующие значения, не файл
			if ($oLib_Property->type != 8)
			{
				//$propertyValue = Core_Array::getPost($propertyName);
				//$propertyValue = Core_Array::get(self::$_aSortingTree, $oLib_Property->id);

				/*$propertyValue = $oLib_Property->multivalue
					? array()
					: NULL;*/

				if ($oLib_Property->multivalue)
				{
					if (isset(self::$_aSortingTree[$oLib_Property->id]))
					{
						foreach (self::$_aSortingTree[$oLib_Property->id] as $sortingKey)
						{
							$existsValue = Core_Array::getPost($propertyName . '_' . $sortingKey);

							!is_null($existsValue) && $propertyValue[] = $existsValue;
						}
					}
				}
				elseif (isset(self::$_aSortingTree[$oLib_Property->id]))
				{
					$propertyValue = Core_Array::getPost($propertyName . '_' . self::$_aSortingTree[$oLib_Property->id][0]);
				}
			}
			// Файлы и есть значения ранее загруженные
			elseif (isset($aOptions[$oLib_Property->varible_name]))
			{
				if ($oLib_Property->multivalue)
				{
					if (isset(self::$_aSortingTree[$oLib_Property->id]))
					{
						foreach (self::$_aSortingTree[$oLib_Property->id] as $sortingKey)
						{
							if (!is_null(Core_Array::getPost($propertyName . '_' . $sortingKey)) && isset($aOptions[$oLib_Property->varible_name][$sortingKey]))
							{
								$propertyValue[] = $aOptions[$oLib_Property->varible_name][$sortingKey];
							}
							// Старое значение множественного файла, которое не пришло
							elseif (isset($aOptions[$oLib_Property->varible_name][$sortingKey]))
							{
								$oldValue = $aOptions[$oLib_Property->varible_name][$sortingKey];

								$oldValue = ltrim($oldValue, '/');
								if (strpos($oldValue, $oObject->getLibFileHref()) === 0)
								{
									try
									{
										Core_File::delete(CMS_FOLDER . $oldValue);
									}
									catch (Exception $e)
									{
										Core_Message::show($e->getMessage(), 'error');
									}
								}
							}
						}
					}
				}
				else
				{
					if (!is_null(Core_Array::getPost($propertyName . '_0')))
					{
						$propertyValue = $aOptions[$oLib_Property->varible_name];
					}
					// Старое значение одиночного файла, которое не пришло
					elseif (isset($aOptions[$oLib_Property->varible_name][0]))
					{
						$oldValue = $aOptions[$oLib_Property->varible_name][0];

						$oldValue = ltrim($oldValue, '/');
						if (strpos($oldValue, $oObject->getLibFileHref()) === 0)
						{
							try
							{
								Core_File::delete(CMS_FOLDER . $oldValue);
							}
							catch (Exception $e)
							{
								Core_Message::show($e->getMessage(), 'error');
							}
						}
						$propertyValue = NULL;
					}
				}
			}
			else
			{
				$propertyValue = NULL;
			}

			if ($oLib_Property->type == 8)
			{
				$aTmp = Core_Array::getFiles($propertyName);

				// echo "<pre>";
				// var_dump($propertyName);
				// var_dump($aTmp);
				// echo "</pre>";

				if (isset($aTmp['name']))
				{
					if ($oLib_Property->multivalue)
					{
						foreach ($aTmp['name'] as $key => $sName)
						{
							$tmpValue = array(
								'name' => $sName,
								'tmp_name' => $aTmp['tmp_name'][$key],
								'size' => $aTmp['size'][$key]
							);

							$propertyValue[] = self::_correctPropertyValue($oLib_Property, $oObject, $tmpValue);
						}
					}
					else
					{
						$propertyValue = self::_correctPropertyValue($oLib_Property, $oObject, $aTmp);
					}
				}
			}
			// Составное свойство
			elseif ($oLib_Property->type == 10)
			{
				$aNewValues = array();

				$oLib = $oObject->Lib;
				$aSub_Lib_Properties = $oLib->Lib_Properties->getAllByParent_id($oLib_Property->id, FALSE);

				$aBlocks = array();
				foreach ($aSub_Lib_Properties as $oSub_Lib_Property)
				{
					foreach ($_POST as $key => $value)
					{
						if (preg_match('/' . preg_quote($prefix) . '(\d+)_' . $oSub_Lib_Property->id . '(_\d+)?/', $key, $matches))
						{
							!in_array($matches[1], $aBlocks) && $aBlocks[] = $matches[1];
						}
					}
				}

				foreach ($aBlocks as $iBlockId)
				{
					$complexPrefix = "lib_property_{$iBlockId}_";

					$aBlockValue = isset($aOptions[$oLib_Property->varible_name][$iBlockId])
						? $aOptions[$oLib_Property->varible_name][$iBlockId]
						: array();

					$aBlockValues = self::_getJson($oObject, $aSub_Lib_Properties, $aBlockValue, $complexPrefix);

					$propertyValue[] = $aBlockValues;
				}
			}
			else
			{
				$aNewValues = Core_Array::getPost($propertyName);

				if (!is_array($aNewValues))
				{
					$aNewValues = $oLib_Property->type == 1 || !is_null($aNewValues)
						? array($aNewValues)
						: array();
				}

				foreach ($aNewValues as $newValue)
				{
					$tmpValue = self::_correctPropertyValue($oLib_Property, $oObject, $newValue);

					if ($oLib_Property->multivalue)
					{
						$propertyValue[] = $tmpValue;
					}
					else
					{
						$propertyValue = $tmpValue;
					}
				}
			}

			// Множественные значения или файл
			if ($oLib_Property->multivalue/* || $oLib_Property->type == 8*/)
			{
				$propertyValue = is_array($propertyValue)
					? $propertyValue
					: array();
			}
			else
			{
				$propertyValue = is_array($propertyValue)
					? array() // Delete wrong value
					: $propertyValue;
			}

			$LA[$oLib_Property->varible_name] = $propertyValue;
		}

		return $LA;
	}

	/**
	 * Correct property value
	 * @param Lib_Property_Value $oLib_Property
	 * @param object $oObject
	 * @param string $propertyValue
	 * @return string
	 */
	static public function _correctPropertyValue($oLib_Property, $oObject, $propertyValue)
	{
		switch ($oLib_Property->type)
		{
			case 1: // Флажок
				$propertyValue = !is_null($propertyValue);
			break;
			case 2: // XSL шаблон
				$propertyValue = Core_Entity::factory('Xsl', $propertyValue)->name;
			break;
			case 7: // TPL шаблон
				$propertyValue = Core_Entity::factory('Tpl', $propertyValue)->name;
			break;
			case 0: // Поле ввода
			case 3: // Список
			case 4: // SQL-запрос
			case 5: // Большое текстовое поле
			case 9: // Визуальный редактор
			default:
				$propertyValue = strval($propertyValue);
			break;
			/*case 6: // Множественные значения
				$propertyValue = is_array($propertyValue)
					? $propertyValue
					: array();
			break;*/
			case 8:
				if (is_array($propertyValue) && isset($propertyValue['name']))
				{
					$aFile = $propertyValue;

					$propertyValue = NULL;

					if (intval($aFile['size']) > 0 && strlen($aFile['name']))
					{
						if (Core_File::isValidExtension($aFile['name'], Core::$mainConfig['availableExtension']))
						{
							$ext = Core_File::getExtension($aFile['name']);

							$imageName = $oLib_Property->change_filename
								? strtolower(Core_Guid::get()) . '.' . $ext
								: Core_File::filenameCorrection($aFile['name']);

							Core_File::moveUploadedFile($aFile['tmp_name'], $oObject->getLibFilePath() . $imageName);

							$propertyValue = '/' . $oObject->getLibFileHref() . $imageName;
						}
					}
				}
				/*else
				{
					$propertyValue = NULL;
				}*/
			break;
		}

		return $propertyValue;
	}

	/**
	 * Get lib properties JSON
	 * @param Core_Entity $oObject object
	 * @return string
	 */
	static public function getJson(Core_Entity $oObject)
	{
		$aOptions = !is_null($oObject->options)
			? json_decode($oObject->options, TRUE)
			: array();

		$oLib = $oObject->Lib;

		$aLib_Properties = $oLib->Lib_Properties->getAllByparent_id(0, FALSE);

		$LA = self::_getJson($oObject, $aLib_Properties, $aOptions);

		// echo "<pre>"; var_dump($LA); echo "</pre>";

		return json_encode($LA, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
	}

    /**
     * Get options list
     * @param array $LA
     * @param Core_Entity $oObject
     * @hostcms-event Lib_Controller_Libproperties.onGetOptionsList
     */
	public function getOptionsList(array $LA, Core_Entity $oObject)
	{
		$oLib = Core_Entity::factory('Lib', $this->_libId);
		$aLib_Properties = $oLib->Lib_Properties->getAllByparent_id(0, FALSE);

		$this->_showLevelOptionsList($aLib_Properties, $LA, $oObject);
	}

	/**
	 * Show level item
	 * @param Lib_Property_Model $oLib_Property
	 * @param object $oObject
	 * @param mixed $value
	 * @return self
	 */
	protected function _showLevelOptionItem($oLib_Property, $oObject, $value, $entityId)
	{
		$oDivOpen = Core_Html_Entity::factory('Code')->value('<div class="input-group margin-bottom-10 multiple_value item_div clear">');
		$oDivClose = Core_Html_Entity::factory('Code')->value('</div>');

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		!is_array($value)
			&& $value = !is_null($value) ? array($value) : array();

		// Было удалено последнее значение
		$oLib_Property->multivalue && !count($value) && $oLib_Property->type != 8
			&& $value = array(NULL);

		count($value) > 1 && !$oLib_Property->multivalue && $oLib_Property->type != 10
			&& $value = array_slice($value, 0, 1);

		$acronym = $oLib_Property->description == ''
			? htmlspecialchars($oLib_Property->name)
			: '<acronym title="' . htmlspecialchars($oLib_Property->description) . '">'
				. htmlspecialchars($oLib_Property->name) . '</acronym>';

		$oDivSection = Core_Html_Entity::factory('Div')
			->class('section-' . $oLib_Property->id);

		switch ($oLib_Property->type)
		{
			case 0: // Поле ввода
				$oValue = Core_Html_Entity::factory('Input')
					->class('form-control');

				foreach ($value as $key => $valueItem)
				{
					$oDivRow = Core_Html_Entity::factory('Div')
						->id('lib_property_' . $oLib_Property->id)
						->class('row form-group');

					$oDivInputs = Core_Html_Entity::factory('Div')
						->add(
							Core_Html_Entity::factory('Span')
								->class('caption')
								->value($acronym)
						)
						->class('col-xs-12');

					if ($oLib_Property->multivalue)
					{
						$oValue = clone $oValue;

						$oDivInputs
							->add($oDivOpen)
							->add($oValue->value($valueItem))
							->add($this->imgBox($oLib_Property))
							->add($oDivClose);
					}
					else
					{
						$oDivInputs->add(
							$oValue->value($valueItem)
						);
					}

					$oValue->name = "{$entityId}_{$key}";
					$oValue->id = "id_{$entityId}_{$key}";

					$oDivSection->add(
						$oDivRow->add($oDivInputs)
					);
				}
			break;
			case 1: // Флажок
				$oValue = Core_Html_Entity::factory('Input')
					->type('checkbox')
					->name("{$entityId}")
					->id("id_{$entityId}_0");

				$oDivRow = Core_Html_Entity::factory('Div')
					->id('lib_property_' . $oLib_Property->id)
					->class('row form-group');

				$oDivInputs = Core_Html_Entity::factory('Div')
					->add(
						Core_Html_Entity::factory('Span')
							->class('caption')
							->value($acronym)
					)
					->class('col-xs-12');

				if (strtolower($value[0]) == 'true')
				{
					$oValue->checked('checked');
				}

				$oDivInputs->add(
					Core_Html_Entity::factory('Td')
						->add(
							Core_Html_Entity::factory('Label')
								->for("id_{$entityId}_0")
								->add($oValue)
								->add(
									Core_Html_Entity::factory('Span')
										->class('text')
										->value('&nbsp;' . Core::_('Admin_Form.yes'))
								)
						)
				);

				$oDivSection->add(
					$oDivRow->add($oDivInputs)
				);
			break;
			case 2: // XSL шаблон
				$oXsl_Controller_Edit = new Xsl_Controller_Edit($this->_Admin_Form_Action);
				$aXslDirs = $oXsl_Controller_Edit->fillXslDir(0);

				foreach ($value as $valueItem)
				{
					$oDivRow = Core_Html_Entity::factory('Div')
						->id('lib_property_' . $oLib_Property->id)
						->class('row form-group');

					$oDivInputs = Core_Html_Entity::factory('Div')
						->add(
							Core_Html_Entity::factory('Span')
								->class('caption')
								->value($acronym)
						)
						->class('col-xs-12');

					$xsl_id = $xsl_dir_id = 0;

					$oXsl = Core_Entity::factory('Xsl')->getByName($valueItem);
					if ($oXsl)
					{
						$xsl_id = $oXsl->id;
						$xsl_dir_id = $oXsl->xsl_dir_id;
					}

					$oDivInputs->add(
						Core_Html_Entity::factory('Div')
							->class('row')
							->add(
								Core_Html_Entity::factory('Div')
									->class('col-xs-12 col-sm-6')
									->add(
										Core_Html_Entity::factory('Select')
											->name("xsl_dir_id_{$oLib_Property->id}")
											->id("xsl_dir_id_{$oLib_Property->id}")
											->class('form-control')
											->options(
												array(' … ') + $aXslDirs
											)
											->value($xsl_dir_id)
											->onchange("$.ajaxRequest({path: hostcmsBackend + '/structure/index.php', context: 'id_{$entityId}', callBack: [$.loadSelectOptionsCallback, function(){var xsl_id = \$('#{$windowId} #id_{$entityId} [value=\'{$xsl_id}\']').get(0) ? {$xsl_id} : 0; \$('#{$windowId} #id_{$entityId}').val(xsl_id)}], action: 'loadXslList',additionalParams: 'xsl_dir_id=' + this.value + '&lib_property_id={$oLib_Property->id}',windowId: '{$windowId}'}); return false")
									)
							)
							->add(
								Core_Html_Entity::factory('Script')->value("$('#{$windowId} #xsl_dir_id_{$oLib_Property->id}').change();")
							)
							->add(
								Core_Html_Entity::factory('Div')
									->class('col-xs-12 col-sm-6')
									->add(
										Core_Html_Entity::factory('Div')
											->class('input-group')
											->add(
												Core_Html_Entity::factory('Select')
													->id("id_{$entityId}")
													->name("{$entityId}")
													->class('form-control')
													->value($xsl_id)
											)
											->add(
												Core_Html_Entity::factory('A')
													->href(Admin_Form_Controller::correctBackendPath("/{admin}/xsl/index.php?xsl_dir_id={$xsl_dir_id}&hostcms[checked][1][{$xsl_id}]=1&hostcms[action]=edit"))
													->target('_blank')
													->class('input-group-addon blue')
													->value('<i class="fa fa-pencil"></i>')
											)
									)
							)
					);

					$oDivSection->add(
						$oDivRow->add($oDivInputs)
					);
				}
			break;
			case 3: // Список
				$aLib_Property_List_Values = $oLib_Property->Lib_Property_List_Values->findAll(FALSE);

				$aOptions = array();

				foreach ($aLib_Property_List_Values as $oLib_Property_List_Value)
				{
					$aOptions[$oLib_Property_List_Value->value] = $oLib_Property_List_Value->name;
				}

				$oValue = Core_Html_Entity::factory('Select')
					->class('form-control')
					->options($aOptions);

				foreach ($value as $key => $valueItem)
				{
					$oDivRow = Core_Html_Entity::factory('Div')
						->id('lib_property_' . $oLib_Property->id)
						->class('row form-group');

					$oDivInputs = Core_Html_Entity::factory('Div')
						->add(
							Core_Html_Entity::factory('Span')
								->class('caption')
								->value($acronym)
						)
						->class('col-xs-12');

					if ($oLib_Property->multivalue)
					{
						$oValue = clone $oValue;

						$oDivInputs
							->add($oDivOpen)
							->add($oValue->value($valueItem))
							->add($this->imgBox($oLib_Property))
							->add($oDivClose);
					}
					else
					{
						$oDivInputs->add(
							$oValue->value($valueItem)
						);
					}

					$oValue->name = "{$entityId}_{$key}";
					$oValue->id = "id_{$entityId}_{$key}";

					$oDivSection->add(
						$oDivRow->add($oDivInputs)
					);
				}
			break;
			case 4: // SQL-запрос
				// Выполняем запрос
				$query = $oLib_Property->sql_request;
				$query = str_replace('{SITE_ID}', CURRENT_SITE, $query);

				$aOptions = array();

				if (trim($query) != '')
				{
					try
					{
						$Core_DataBase = Core_DataBase::instance();
						$aRows = $Core_DataBase
							->setQueryType(0)
							->query($query)
							->asAssoc()
							->result();

						foreach ($aRows as $sql_row)
						{
							$aOptions[$sql_row[$oLib_Property->sql_value_field]]
								= htmlspecialchars(Core_Type_Conversion::toStr($sql_row[$oLib_Property->sql_caption_field])) . ' [' . htmlspecialchars(Core_Type_Conversion::toStr($sql_row[$oLib_Property->sql_value_field])) . ']';
						}
					}
					catch (Exception $e)
					{
						Core_Message::show(
							Core::_('Structure.query_error', htmlspecialchars($query)), 'error'
						);
						Core_Message::show($e->getMessage(), 'error');
					}
				}

				if (count($aOptions))
				{
					$oValue = Core_Html_Entity::factory('Select')
						->class('form-control')
						->options($aOptions);

					foreach ($value as $key => $valueItem)
					{
						$oDivRow = Core_Html_Entity::factory('Div')
							->id('lib_property_' . $oLib_Property->id)
							->class('row form-group');

						$oDivInputs = Core_Html_Entity::factory('Div')
							->add(
								Core_Html_Entity::factory('Span')
									->class('caption')
									->value($acronym)
							)
							->class('col-xs-12');

						if ($oLib_Property->multivalue)
						{
							$oValue = clone $oValue;

							$oDivInputs
								->add($oDivOpen)
								->add($oValue->value($valueItem))
								->add($this->imgBox($oLib_Property))
								->add($oDivClose);
						}
						else
						{
							$oDivInputs->add(
								$oValue->value($valueItem)
							);
						}

						$oValue->name = "{$entityId}_{$key}";
						$oValue->id = "id_{$entityId}_{$key}";

						$oDivSection->add(
							$oDivRow->add($oDivInputs)
						);
					}
				}
			break;
			case 5: // Большое текстовое поле
				$oValue = Core_Html_Entity::factory('Textarea')
					->class('form-control');

				foreach ($value as $key => $valueItem)
				{
					$oDivRow = Core_Html_Entity::factory('Div')
						->id('lib_property_' . $oLib_Property->id)
						->class('row form-group');

					$oDivInputs = Core_Html_Entity::factory('Div')
						->add(
							Core_Html_Entity::factory('Span')
								->class('caption')
								->value($acronym)
						)
						->class('col-xs-12');

					if ($oLib_Property->multivalue)
					{
						$oValue = clone $oValue;

						$oDivInputs
							->add($oDivOpen)
							->add($oValue->value($valueItem))
							->add($this->imgBox($oLib_Property))
							->add($oDivClose);
					}
					else
					{
						$oDivInputs->add(
							$oValue->value($valueItem)
						);
					}

					$oValue->name = "{$entityId}_{$key}";
					$oValue->id = "id_{$entityId}_{$key}";

					$oDivSection->add(
						$oDivRow->add($oDivInputs)
					);
				}
			break;
			case 6: // Множественные значения
				$oValue = Core_Html_Entity::factory('Input')
					->class('form-control');

				!is_array($value) && $value = array($value);

				foreach ($value as $key => $valueItem)
				{
					$oDivRow = Core_Html_Entity::factory('Div')
						->id('lib_property_' . $oLib_Property->id)
						->class('row form-group');

					$oDivInputs = Core_Html_Entity::factory('Div')
						->add(
							Core_Html_Entity::factory('Span')
								->class('caption')
								->value($acronym)
						)
						->class('col-xs-12');

					$oValue = clone $oValue;

					$oDivInputs
						->add($oDivOpen)
						->add($oValue->value($valueItem))
						->add($this->imgBox($oLib_Property))
						->add($oDivClose);

					$oValue->name = "{$entityId}_{$key}";
					$oValue->id = "id_{$entityId}_{$key}";

					$oDivSection->add(
						$oDivRow->add($oDivInputs)
					);
				}
			break;
			case 7: // TPL шаблон
				$oTpl_Controller_Edit = new Tpl_Controller_Edit($this->_Admin_Form_Action);
				$aTplDirs = $oTpl_Controller_Edit->fillTplDir(0);

				foreach ($value as $valueItem)
				{
					$oDivRow = Core_Html_Entity::factory('Div')
						->id('lib_property_' . $oLib_Property->id)
						->class('row form-group');

					$oDivInputs = Core_Html_Entity::factory('Div')
						->add(
							Core_Html_Entity::factory('Span')
								->class('caption')
								->value($acronym)
						)
						->class('col-xs-12');

					$oTpl = Core_Entity::factory('Tpl')->getByName($valueItem);

					if ($oTpl)
					{
						$tpl_id = $oTpl->id;
						$tpl_dir_id = $oTpl->tpl_dir_id;
					}
					else
					{
						$tpl_id = 0;
						$tpl_dir_id = 0;
					}

					$oDivInputs->add(
						Core_Html_Entity::factory('Div')
							->class('row')
							->add(
								Core_Html_Entity::factory('Div')
									->class('col-xs-12 col-sm-6')
									->add(
										Core_Html_Entity::factory('Select')
											->name("tpl_dir_id_{$oLib_Property->id}")
											->id("tpl_dir_id_{$oLib_Property->id}")
											->class('form-control')
											->options(
												array(' … ') + $aTplDirs
											)
											->value($tpl_dir_id)
											->onchange("$.ajaxRequest({path: hostcmsBackend + '/structure/index.php', context: 'id_{$entityId}', callBack: [$.loadSelectOptionsCallback, function(){var tpl_id = \$('#{$windowId} #id_{$entityId} [value=\'{$tpl_id}\']').get(0) ? {$tpl_id} : 0; \$('#{$windowId} #id_{$entityId}').val(tpl_id)}], action: 'loadTplList',additionalParams: 'tpl_dir_id=' + this.value + '&lib_property_id={$oLib_Property->id}',windowId: '{$windowId}'}); return false")
									)
							)
							->add(
								Core_Html_Entity::factory('Script')->value("$('#{$windowId} #tpl_dir_id_{$oLib_Property->id}').change();")
							)
							->add(
								Core_Html_Entity::factory('Div')
									->class('col-xs-12 col-sm-6')
									->add(
										Core_Html_Entity::factory('Div')
											->class('input-group')
											->add(
												Core_Html_Entity::factory('Select')
													->name("{$entityId}")
													->id("id_{$entityId}")
													->class('form-control')
													->value($tpl_dir_id)
											)
											->add(
												Core_Html_Entity::factory('A')
													->href(Admin_Form_Controller::correctBackendPath("/{admin}/tpl/index.php?tpl_dir_id={$tpl_dir_id}&hostcms[checked][1][{$tpl_id}]=1&hostcms[action]=edit"))
													->target('_blank')
													->class('input-group-addon blue')
													->value('<i class="fa fa-pencil"></i>')
											)
									)
							)
					);

					$oDivSection->add(
						$oDivRow->add($oDivInputs)
					);
				}
			break;
			case 8: // Файл
				$oFile = Admin_Form_Entity::factory('File')
					->controller($this->_Admin_Form_Controller)
					->type('file')
					->divAttr(array('class' => 'lib-property-file-row'))
					->largeImage(
						array('show_params' => FALSE)
					)
					->smallImage(
						array('show' => FALSE)
					);

				foreach ($value as $key => $valueItem)
				{
					$oDivRow = Core_Html_Entity::factory('Div')
						->id('lib_property_' . $oLib_Property->id)
						->class('row form-group');

					$oDivInputs = Core_Html_Entity::factory('Div')
						->add(
							Core_Html_Entity::factory('Span')
								->class('caption')
								->value($acronym)
						)
						->class('col-xs-12');

					$oLib_Property->multivalue
						&& $oDivInputs->add($oDivOpen);

					switch ($oObject->getModelName())
					{
						case 'structure':
						default:
							$path = '/{admin}/structure/index.php';
						break;
						case 'template_section_lib':
							$path = '/{admin}/template/section/lib/index.php';
						break;
					}

					$oFileClone = clone $oFile;

					$oDivInputs->add($oFileClone);

					if ($valueItem != '')
					{
						$path = Admin_Form_Controller::correctBackendPath($path);

						$oFileClone->largeImage(
							array(
								'id' => "id_{$entityId}_{$key}",
								'name' => "{$entityId}_{$key}",
								'path' => $valueItem,
								'show_params' => FALSE,
								'originalName' => basename($valueItem),
								'delete_onclick' => "$.adminLoad({path: '{$path}', additionalParams: 'hostcms[checked][0][{$this->_object->id}]=1&varible_name=" . Core_Str::escapeJavascriptVariable($oLib_Property->varible_name) . "&secret_csrf=" . Core_Security::getCsrfToken() . "', operation: '{$key}', action: 'deleteLibFile', windowId: '{$windowId}'}); return false",
								// 'delete_href' => '',
							)
						);
					}
					else
					{
						$oFileClone->largeImage(
							array(
								'id' => "id_{$entityId}_{$key}",
								'name' => "{$entityId}_{$key}",
								'show_params' => FALSE,
								'show_description' => FALSE
							)
						);
					}

					$oLib_Property->multivalue
						&& $oDivInputs
							->add($this->imgBox($oLib_Property))
							->add($oDivClose);

					$oDivSection->add(
						$oDivRow->add($oDivInputs)
					);
				}

				if (/*$oLib_Property->multivalue || */!count($value))
				{
					$oFile->name = $oLib_Property->multivalue
						? "{$entityId}[]"
						: "{$entityId}";

					$oFile->id = "id_{$entityId}_0";

					$oDivRow = Core_Html_Entity::factory('Div')
						->id('lib_property_' . $oLib_Property->id)
						->class('row form-group');

					$oDivInputs = Core_Html_Entity::factory('Div')
						->add(
							Core_Html_Entity::factory('Span')
								->class('caption')
								->value($acronym)
						)
						->class('col-xs-12');

					$oLib_Property->multivalue
						&& $oDivInputs->add($oDivOpen);

					$oDivInputs->add(
						$oFile->largeImage(
							array(
								'show_params' => FALSE,
								'show_description' => FALSE
							)
						)
					);

					$oLib_Property->multivalue
						&& $oDivInputs
							->add($this->imgBox($oLib_Property))
							->add($oDivClose);

					$oDivSection->add(
						$oDivRow->add($oDivInputs)
					);
				}
			break;
			case 9: // Визуальный редактор
				$oValue = Admin_Form_Entity::factory('Textarea')
					->class('form-control')
					->wysiwyg(Core::moduleIsActive('wysiwyg'))
					->rows(10)
					->template_id(0)
					->divAttr(array('class' => ''))
					->controller($this->_Admin_Form_Controller);

				foreach ($value as $key => $valueItem)
				{
					$oDivRow = Core_Html_Entity::factory('Div')
						->id('lib_property_' . $oLib_Property->id)
						->class('row form-group');

					$oDivInputs = Core_Html_Entity::factory('Div')
						->add(
							Core_Html_Entity::factory('Span')
								->class('caption')
								->value($acronym)
						)
						->class('col-xs-12');

					if ($oLib_Property->multivalue)
					{
						$oValue = clone $oValue;

						$oDivInputs
							->add($oDivOpen)
							->add($oValue->value($valueItem))
							->add($this->imgBox($oLib_Property))
							->add($oDivClose);
					}
					else
					{
						$oDivInputs->add(
							$oValue->value($valueItem)
						);
					}

					$oValue->name = "{$entityId}_{$key}";
					$oValue->id = "id_{$entityId}_{$key}";

					$oDivSection->add(
						$oDivRow->add($oDivInputs)
					);
				}
			break;
			// Иконка
			case 11:
				$oCrm_Icon = !is_null($value) && $value != ''
					? Core_Entity::factory('Crm_Icon')->getByValue($value)
					: NULL;

				if (is_null($oCrm_Icon))
				{
					$oCrm_Icon = Core_Entity::factory('Crm_Icon')->getRandom();
				}

				$oValue = Admin_Form_Entity::factory('Span')
					->class('crm-project-id ' . $entityId)
					->style('background-color: #aebec4')
					->add(
						Core_Html_Entity::factory('I')
							->class($oCrm_Icon->value)
					)
					->onclick("$.showCrmIcons(this, '{$entityId}')")
					->controller($this->_Admin_Form_Controller);

				$oInput = Admin_Form_Entity::factory('Input')
					->type('hidden')
					->class('input-' . $entityId)
					->divAttr(array('class' => ''))
					->value($oCrm_Icon->value);

				$oInput->name = "{$entityId}";
				$oInput->id = "id_{$entityId}_0";

				$oDivRow = Core_Html_Entity::factory('Div')
					->id('lib_property_' . $oLib_Property->id)
					->class('row form-group');

				$oDivInputs = Core_Html_Entity::factory('Div')
					->add(
						Core_Html_Entity::factory('Span')
							->class('caption')
							->value($acronym)
					)
					->class('col-xs-12');

				$oDivInputs
					->add($oValue)
					->add($oInput);

				$oDivSection->add(
					$oDivRow->add($oDivInputs)
				);
			break;
			case 12:
				$oValue = Admin_Form_Entity::factory('Input')
					->class('form-control')
					->colorpicker(TRUE)
					->controller($this->_Admin_Form_Controller)
					->divAttr(array('class' => ''));

				foreach ($value as $key => $valueItem)
				{
					$oDivRow = Core_Html_Entity::factory('Div')
						->id('lib_property_' . $oLib_Property->id)
						->class('row form-group');

					$oDivInputs = Core_Html_Entity::factory('Div')
						->add(
							Core_Html_Entity::factory('Span')
								->class('caption')
								->value($acronym)
						)
						->class('col-xs-12');

					if ($oLib_Property->multivalue)
					{
						$oValue = clone $oValue;

						$oDivInputs
							->add($oDivOpen)
							->add($oValue->value($valueItem))
							->add($this->imgBox($oLib_Property))
							->add($oDivClose);
					}
					else
					{
						$oDivInputs->add(
							$oValue->value($valueItem)
						);
					}

					$oValue->name = "{$entityId}_{$key}";
					$oValue->id = "id_{$entityId}_{$key}";

					$oDivSection->add(
						$oDivRow->add($oDivInputs)
					);
				}
			break;
			default:
				Core_Event::notify('Lib_Controller_Libproperties.onGetOptionsList', $this, array($oLib_Property, $oDivSection, $value));
		}

		// $oDivRow->execute();
		$oDivSection->execute();

		if ($oLib_Property->multivalue)
		{
			Core_Html_Entity::factory('Script')
				->value("$.applyLibPropertySortable('{$windowId}', '{$oLib_Property->id}');")
				->execute();
		}

		return $this;
	}

    /**
     * Get options list
     * @param $aLib_Properties
     * @param array $LA
     * @param Core_Entity $oObject
     * @throws Core_Exception
     * @hostcms-event Lib_Controller_Libproperties.onGetOptionsList
     */
	protected function _showLevelOptionsList($aLib_Properties, array $LA, Core_Entity $oObject)
	{
		if (is_array($LA))
		{
			// Булевы значения приводим к текстовым
			foreach ($LA as $key => $current_value)
			{
				if ($current_value === FALSE)
				{
					$LA[$key] = 'false';
				}
				elseif ($current_value === TRUE)
				{
					$LA[$key] = 'true';
				}
			}
		}

		foreach ($aLib_Properties as $oLib_Property)
		{
			$entityId = 'lib_property_' . $oLib_Property->id;

			// Получаем значение параметра
			$value = isset($LA[$oLib_Property->varible_name])
				? $LA[$oLib_Property->varible_name]
				: ($oLib_Property->type != 8
					? $oLib_Property->default_value
					: NULL
				);

			if ($oLib_Property->type != 10)
			{
				$this->_showLevelOptionItem($oLib_Property, $oObject, $value, $entityId);
			}
			else
			{
				$oLib = Core_Entity::factory('Lib', $this->_libId);
				$aSub_Lib_Property = $oLib->Lib_Properties->getAllByparent_id($oLib_Property->id, FALSE);

				!is_array($value) && $value = array($value);

				Core_Html_Entity::factory('Div')
					->class('row form-group')
					->add(
						Core_Html_Entity::factory('Div')
							->class('col-xs-12')
							->add(
								Core_Html_Entity::factory('Span')
									->class('caption')
									->value($oLib_Property->name)
							)
					)
					->execute();

				$oDivRow = Core_Html_Entity::factory('Div')
					->class('complex-lib-property section-' . $oLib_Property->id);

				// Количество блоков
				foreach (array_keys($value) as $blockKey => $blockId)
				{
					$oDivOpen = Core_Html_Entity::factory('Code')->value('<div id="lib_property_' . $oLib_Property->id . '" class="complex-block input-group margin-bottom-10 item_div clear' . ($oLib_Property->multivalue ? ' multiple_value' : '') . '">');
					$oDivClose = Core_Html_Entity::factory('Code')->value('</div>');

					$oDivRow
						->add($oDivOpen);

					foreach ($aSub_Lib_Property as $rowKey => $oSub_Lib_Property)
					{
						$subValue = Core_Array::get($value[$blockId], $oSub_Lib_Property->varible_name, '');

						ob_start();
						$this->_showLevelOptionItem($oSub_Lib_Property, $oObject, $subValue, "lib_property_{$blockKey}_{$oSub_Lib_Property->id}"  /*. '_' . $rowKey*/);
						$oDivRow->add(Core_Html_Entity::factory('Code')->value(ob_get_clean()));
					}

					if ($oLib_Property->multivalue)
					{
						$oDivRow
							->add($this->imgBox($oLib_Property));
					}

					$oDivRow
						->add($oDivClose);
				}

				$oDivRow->execute();

				/*if ($oLib_Property->multivalue)
				{
					$windowId = $this->_Admin_Form_Controller->getWindowId();

					Core_Html_Entity::factory('Script')
						->value("$.applyLibPropertySortable('{$windowId}', '{$oLib_Property->id}');")
						->execute();
				}*/
			}
		}
	}

	/**
	 * Create imageBox
	 * @param string $addFunction
	 * @param string $deleteOnclick
	 * @return self
	 */
	public function imgBox($oLib_Property, $addFunction = '$.cloneMultipleValue', $deleteOnclick = '$.deleteNewMultipleValue')
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		ob_start();
			Admin_Form_Entity::factory('Div')
				->class('input-group-addon no-padding1 add-remove-property margin-top-20')
				->add(
					Admin_Form_Entity::factory('Div')
					->class('no-padding-left col-lg-12')
					->add(
						Admin_Form_Entity::factory('Div')
							->class('btn btn-palegreen btn-clone inverted')
							->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-plus-circle close"></i>'))
							->onclick("{$addFunction}('{$windowId}', {$oLib_Property->id}, this); event.stopPropagation();")
					)
					->add(
						Admin_Form_Entity::factory('Div')
							->class('btn btn-darkorange btn-delete inverted')
							->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-minus-circle close"></i>'))
							->onclick("{$deleteOnclick}(this, {$oLib_Property->id}); event.stopPropagation();")
					)
				)
				->execute();

		return Admin_Form_Entity::factory('Code')->html(ob_get_clean());
	}
}