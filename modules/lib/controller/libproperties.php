<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib.
 * Типовой контроллер загрузки свойст типовой дин. страницы для структуры
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2021 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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

	/**
	 * Get lib properties JSON
	 * @param Core_Entity $oObject object
	 * @return string
	 */
	static public function getJson(Core_Entity $oObject)
	{
		$LA = array();
		$aOptions = json_decode($oObject->options, TRUE);

		$oLib = $oObject->Lib;

		$aLib_Properties = $oLib->Lib_Properties->findAll();

		foreach ($aLib_Properties as $oLib_Property)
		{
			$propertyName = 'lib_property_' . $oLib_Property->id;

			if ($oLib_Property->type != 8)
			{
				$propertyValue = Core_Array::getPost($propertyName);
			}
			else
			{
				$aTmp = Core_Array::getFiles($propertyName);

				if (isset($aTmp['name']))
				{
					$propertyValue = array();

					foreach ($aTmp['name'] as $key => $sName)
					{
						$propertyValue[] = array(
							'name' => $sName,
							'tmp_name' => $aTmp['tmp_name'][$key],
							'size' => $aTmp['size'][$key]
						);
					}
				}
				else
				{
					$propertyValue = $aTmp;
				}
			}

			if ($oLib_Property->multivalue)
			{
				$aPropertyValues = is_array($propertyValue)
					? $propertyValue
					: array(NULL);
			}
			else
			{
				$aPropertyValues = is_array($propertyValue)
					? array() // Delete wrong value
					: array($propertyValue);
			}

			$aNewValues = $oLib_Property->type == 8 && isset($aOptions[$oLib_Property->varible_name]) && is_array($aOptions[$oLib_Property->varible_name])
				? $aOptions[$oLib_Property->varible_name]
				: array();

			foreach ($aPropertyValues as $key => $propertyValue)
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

							if (intval($aFile['size']) > 0)
							{
								if (Core_File::isValidExtension($aFile['name'], Core::$mainConfig['availableExtension']))
								{
									$param = array();

									$ext = Core_File::getExtension($aFile['name']);

									$imageName = $oLib_Property->change_filename
										? strtolower(Core_Guid::get()) . '.' . $ext
										: $aFile['name'];

									// Путь к файлу-источнику большого изображения;
									$param['large_image_source'] = $aFile['tmp_name'];
									// Оригинальное имя файла большого изображения
									$param['large_image_name'] = $aFile['name'];

									// Путь к создаваемому файлу большого изображения;
									$param['large_image_target'] = !empty($imageName)
										? $oObject->getLibFilePath() . $imageName
										: '';

									// Использовать большое изображение для создания малого
									$param['create_small_image_from_large'] = FALSE;

									// Значение максимальной ширины большого изображения
									// $param['large_image_max_width'] = Core_Array::getPost('large_max_width_image', 0);

									// Значение максимальной высоты большого изображения
									// $param['large_image_max_height'] = Core_Array::getPost('large_max_height_image', 0);

									// Сохранять пропорции изображения для большого изображения
									// $param['large_image_preserve_aspect_ratio'] = !is_null(Core_Array::getPost('large_preserve_aspect_ratio_image'));

									// $oObject->createDir();

									$result = Core_File::adminUpload($param);

									if ($result['large_image'])
									{
										$propertyValue = '/' . $oObject->getLibFileHref() . $imageName;
									}
								}
							}
						}
						else
						{
							$propertyValue = NULL;
						}
					break;
				}

				!is_null($propertyValue)
					&& $aNewValues[] = $propertyValue;
			}

			$LA[$oLib_Property->varible_name] = $oLib_Property->multivalue
				/*? ($oLib_Property->type == 8 && isset($aOptions[$oLib_Property->varible_name]) && is_array($aOptions[$oLib_Property->varible_name])
					? array_merge($aOptions[$oLib_Property->varible_name], $aPropertyValues)
					: $aPropertyValues
				)*/
				? $aNewValues
				: Core_Array::get($aNewValues, 0);
		}

		return json_encode($LA);
	}

	/**
	 * Get options list
	 * @param array $LA
	 * @return self
	 * @hostcms-event Lib_Controller_Libproperties.onGetOptionsList
	 */
	public function getOptionsList(array $LA, Core_Entity $oObject)
	{
		$oLib = Core_Entity::factory('Lib', $this->_libId);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$aLib_Properties = $oLib->Lib_Properties->findAll();

		$oXsl_Controller_Edit = new Xsl_Controller_Edit($this->_Admin_Form_Action);
		$aXslDirs = $oXsl_Controller_Edit->fillXslDir(0);

		$oTpl_Controller_Edit = new Tpl_Controller_Edit($this->_Admin_Form_Action);
		$aTplDirs = $oTpl_Controller_Edit->fillTplDir(0);

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

		$oDivOpen = Core::factory('Core_Html_Entity_Code')->value('<div class="input-group margin-bottom-10 multiple_value item_div clear">');
		$oDivClose = Core::factory('Core_Html_Entity_Code')->value('</div>');

		foreach ($aLib_Properties as $oLib_Property)
		{
			// Получаем значение параметра
			$value = isset($LA[$oLib_Property->varible_name])
				? $LA[$oLib_Property->varible_name]
				: $oLib_Property->default_value;

			$acronym = $oLib_Property->description == ''
				? htmlspecialchars($oLib_Property->name)
				: '<acronym title="' . htmlspecialchars($oLib_Property->description) . '">'
					. htmlspecialchars($oLib_Property->name) . '</acronym>';

			$oDivCaption = Core::factory('Core_Html_Entity_Div')
				->class('col-xs-6 col-sm-5 col-lg-4 no-padding-right')
				->add(
					Core::factory('Core_Html_Entity_Span')
						->class('caption')
						->value($acronym)
				);

			$oDivInputs = Core::factory('Core_Html_Entity_Div')
				->class('col-xs-6 col-sm-7 col-lg-8');

			$oDivRow = Core::factory('Core_Html_Entity_Div')
				->class('row form-group')
				->add($oDivCaption)
				->add($oDivInputs);

			$sFieldName = $oLib_Property->multivalue
				? "lib_property_{$oLib_Property->id}[]"
				: "lib_property_{$oLib_Property->id}";

			!is_array($value) && $value = array($value);

			count($value) > 1 && !$oLib_Property->multivalue
				&& $value = array_slice($value, 0, 1);

			switch ($oLib_Property->type)
			{
				case 0: /* Текстовое поле */
					$oValue = Core::factory('Core_Html_Entity_Input')
						->class('form-control')
						->name($sFieldName);

					foreach ($value as $valueItem)
					{
						if ($oLib_Property->multivalue)
						{
							$oValue = clone $oValue;

							$oDivInputs
								->add($oDivOpen)
								->add($oValue->value($valueItem))
								->add($this->imgBox())
								->add($oDivClose);
						}
						else
						{
							$oDivInputs->add(
								$oValue->value($valueItem)
							);
						}
					}
				break;
				case 1: /* Флажок */
					$oValue = Core::factory('Core_Html_Entity_Input')
						->name($sFieldName)
						->type('checkbox')
						->id("lib_property_id_{$oLib_Property->id}");

					foreach ($value as $valueItem)
					{
						if ($oLib_Property->multivalue)
						{
							$oValue = clone $oValue;
						}

						if (strtolower($valueItem) == 'true')
						{
							$oValue->checked('checked');
						}

						if ($oLib_Property->multivalue)
						{
							$oDivInputs
								->add($oDivOpen);
						}

						$oDivInputs->add(
							Core::factory('Core_Html_Entity_Td')
								->add(
									Core::factory('Core_Html_Entity_Label')
										->for("lib_property_id_{$oLib_Property->id}")
										->add($oValue)
										->add(
											Core::factory('Core_Html_Entity_Span')
												->class('text')
												->value('&nbsp;' . Core::_('Admin_Form.yes'))
										)
								)
						);

						if ($oLib_Property->multivalue)
						{
							$oDivInputs
								->add($this->imgBox())
								->add($oDivClose);
						}
					}
				break;
				case 2: // XSL шаблон
					foreach ($value as $valueItem)
					{
						$oXsl = Core_Entity::factory('Xsl')->getByName($valueItem);

						if ($oXsl)
						{
							$xsl_id = $oXsl->id;
							$xsl_dir_id = $oXsl->xsl_dir_id;
						}
						else
						{
							$xsl_id = 0;
							$xsl_dir_id = 0;
						}

						$oDivInputs->add(
							Core::factory('Core_Html_Entity_Div')
								->class('row')
								->add(
									Core::factory('Core_Html_Entity_Div')
										->class('col-xs-12 col-sm-6')
										->add(
											Core::factory('Core_Html_Entity_Select')
												->name("xsl_dir_id_{$oLib_Property->id}")
												->id("xsl_dir_id_{$oLib_Property->id}")
												->class('form-control')
												->options(
													array(' … ') + $aXslDirs
												)
												->value($xsl_dir_id)
												->onchange("$.ajaxRequest({path: '/admin/structure/index.php', context: 'lib_property_id_{$oLib_Property->id}', callBack: [$.loadSelectOptionsCallback, function(){var xsl_id = \$('#{$windowId} #lib_property_id_{$oLib_Property->id} [value=\'{$xsl_id}\']').get(0) ? {$xsl_id} : 0; \$('#{$windowId} #lib_property_id_{$oLib_Property->id}').val(xsl_id)}], action: 'loadXslList',additionalParams: 'xsl_dir_id=' + this.value + '&lib_property_id={$oLib_Property->id}',windowId: '{$windowId}'}); return false")
										)
								)
								->add(
									Core::factory('Core_Html_Entity_Script')->value("$('#{$windowId} #xsl_dir_id_{$oLib_Property->id}').change();")
								)
								->add(
									Core::factory('Core_Html_Entity_Div')
										->class('col-xs-12 col-sm-6')
										->add(
											Core::factory('Core_Html_Entity_Div')
												->class('input-group')
												->add(
													Core::factory('Core_Html_Entity_Select')
														->name($sFieldName)
														->id("lib_property_id_{$oLib_Property->id}")
														->class('form-control')
														->value($xsl_dir_id)
												)
												->add(
													Core::factory('Core_Html_Entity_A')
														->href("/admin/xsl/index.php?xsl_dir_id={$xsl_dir_id}&hostcms[checked][1][{$xsl_id}]=1&hostcms[action]=edit")
														->target('_blank')
														->class('input-group-addon bg-blue bordered-blue')
														->value('<i class="fa fa-pencil"></i>')
												)
										)
								)
						);
					}
				break;
				case 3: // Список
					$aLib_Property_List_Values = $oLib_Property->Lib_Property_List_Values->findAll();
					$aOptions = array();
					foreach ($aLib_Property_List_Values as $oLib_Property_List_Value)
					{
						$aOptions[$oLib_Property_List_Value->value] = $oLib_Property_List_Value->name;
					}

					$oValue = Core::factory('Core_Html_Entity_Select')
						->name($sFieldName)
						->id("lib_property_id_{$oLib_Property->id}")
						->class('form-control')
						->options($aOptions);

					foreach ($value as $valueItem)
					{
						if ($oLib_Property->multivalue)
						{
							$oValue = clone $oValue;

							$oDivInputs
								->add($oDivOpen)
								->add($oValue->value($valueItem))
								->add($this->imgBox())
								->add($oDivClose);
						}
						else
						{
							$oDivInputs->add(
								$oValue->value($valueItem)
							);
						}
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
						$oValue = Core::factory('Core_Html_Entity_Select')
							->name($sFieldName)
							->id("lib_property_id_{$oLib_Property->id}")
							->class('form-control')
							->options($aOptions);

						foreach ($value as $valueItem)
						{
							if ($oLib_Property->multivalue)
							{
								$oValue = clone $oValue;

								$oDivInputs
									->add($oDivOpen)
									->add($oValue->value($valueItem))
									->add($this->imgBox())
									->add($oDivClose);
							}
							else
							{
								$oDivInputs->add(
									$oValue->value($valueItem)
								);
							}
						}
					}
				break;
				case 5: // Текстовое поле
					$oValue = Core::factory('Core_Html_Entity_Textarea')
						->class('form-control')
						->name($sFieldName);

					foreach ($value as $valueItem)
					{
						if ($oLib_Property->multivalue)
						{
							$oValue = clone $oValue;

							$oDivInputs
								->add($oDivOpen)
								->add($oValue->value($valueItem))
								->add($this->imgBox())
								->add($oDivClose);
						}
						else
						{
							$oDivInputs->add(
								$oValue->value($valueItem)
							);
						}
					}
				break;
				case 6: // Множественные значения
					$oDivOpen = Core::factory('Core_Html_Entity_Code')->value('<div class="input-group margin-bottom-10 multiple_value item_div clear">');
					$oDivClose = Core::factory('Core_Html_Entity_Code')->value('</div>');

					$oValue = Core::factory('Core_Html_Entity_Input')
						->class('form-control')
						->name("lib_property_id_{$oLib_Property->id}[]");

					!is_array($value) && $value = array($value);

					foreach ($value as $valueItem)
					{
						$oValue = clone $oValue;

						$oDivInputs
							->add($oDivOpen)
							->add($oValue->value($valueItem))
							->add($this->imgBox())
							->add($oDivClose);
					}
				break;
				case 7: // TPL шаблон
					foreach ($value as $valueItem)
					{
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
							Core::factory('Core_Html_Entity_Div')
								->class('row')
								->add(
									Core::factory('Core_Html_Entity_Div')
										->class('col-xs-12 col-sm-6')
										->add(
											Core::factory('Core_Html_Entity_Select')
												->name("tpl_dir_id_{$oLib_Property->id}")
												->id("tpl_dir_id_{$oLib_Property->id}")
												->class('form-control')
												->options(
													array(' … ') + $aTplDirs
												)
												->value($tpl_dir_id)
												->onchange("$.ajaxRequest({path: '/admin/structure/index.php', context: 'lib_property_id_{$oLib_Property->id}', callBack: [$.loadSelectOptionsCallback, function(){var tpl_id = \$('#{$windowId} #lib_property_id_{$oLib_Property->id} [value=\'{$tpl_id}\']').get(0) ? {$tpl_id} : 0; \$('#{$windowId} #lib_property_id_{$oLib_Property->id}').val(tpl_id)}], action: 'loadTplList',additionalParams: 'tpl_dir_id=' + this.value + '&lib_property_id={$oLib_Property->id}',windowId: '{$windowId}'}); return false")
										)
								)
								->add(
									Core::factory('Core_Html_Entity_Script')->value("$('#{$windowId} #tpl_dir_id_{$oLib_Property->id}').change();")
								)
								->add(
									Core::factory('Core_Html_Entity_Div')
										->class('col-xs-12 col-sm-6')
										->add(
											Core::factory('Core_Html_Entity_Div')
												->class('input-group')
												->add(
													Core::factory('Core_Html_Entity_Select')
														->name($sFieldName)
														->id("lib_property_id_{$oLib_Property->id}")
														->class('form-control')
														->value($tpl_dir_id)
												)
												->add(
													Core::factory('Core_Html_Entity_A')
														->href("/admin/tpl/index.php?tpl_dir_id={$tpl_dir_id}&hostcms[checked][1][{$tpl_id}]=1&hostcms[action]=edit")
														->target('_blank')
														->class('input-group-addon bg-blue bordered-blue')
														->value('<i class="fa fa-pencil"></i>')
												)
										)
								)
						);
					}
				break;
				// Файл
				case 8:
					foreach ($value as $key => $valueItem)
					{
						switch ($oObject->getModelName())
						{
							case 'structure':
							default:
								$path = '/admin/structure/index.php';
							break;
							case 'template_section_lib':
								$path = '/admin/template/section/lib/index.php';
							break;
						}

						if ($valueItem != '')
						{
							$oDivInputs->add(
								Admin_Form_Entity::factory('File')
									->controller($this->_Admin_Form_Controller)
									->type('file')
									->name($sFieldName)
									->largeImage(
										array(
											// 'path' => '/' . $oObject->getLibFileHref() . $valueItem,
											'path' => $valueItem,
											'show_params' => FALSE,
											'originalName' => basename($valueItem),
											'delete_onclick' => "$.adminLoad({path: '{$path}', additionalParams: 'hostcms[checked][0][{$this->_object->id}]=1&varible_name=" . Core_Str::escapeJavascriptVariable($oLib_Property->varible_name) . "', operation: '{$key}', action: 'deleteLibFile', windowId: '{$windowId}'}); return false",
											// 'delete_href' => '',
											// 'show_description' => TRUE,
											// 'description' => $oDeal_Attachment->description
										)
									)
									->smallImage(
										array('show' => FALSE)
									)
									// ->divAttr(array('id' => "file_{$oDeal_Attachment->id}", 'class' => 'input-group col-xs-12'))
									// ->execute();
							);
						}
					}

					/*if ($oLib_Property->multivalue)
					{
						$oAdmin_Form_Entity_Code = Admin_Form_Entity::factory('Code');
						$oAdmin_Form_Entity_Code->html('<div class="input-group-addon no-padding add-remove-property"><div class="no-padding-left col-lg-12"><div class="btn btn-palegreen" onclick="$.cloneFile(\'' . $windowId .'\'); event.stopPropagation();"><i class="fa fa-plus-circle close"></i></div>
							<div class="btn btn-darkorange" onclick="$(this).parents(\'#file\').remove(); event.stopPropagation();"><i class="fa fa-minus-circle close"></i></div>
							</div>
							</div>');
					}*/

					$oLib_Property->multivalue && $oDivInputs->add($oDivOpen);

					$oDivInputs->add(
						Admin_Form_Entity::factory('File')
							->controller($this->_Admin_Form_Controller)
							->type('file')
							->name($sFieldName)
							->largeImage(
								array(
									'show_params' => FALSE,
									'show_description' => FALSE
								)
							)
							->smallImage(
								array('show' => FALSE)
							)
							// ->divAttr(array('id' => 'file', 'class' => 'row col-xs-12 add-deal-attachment'))
							// ->add($oAdmin_Form_Entity_Code)
							// ->execute();
					);

					if ($oLib_Property->multivalue)
					{
						$oDivInputs
							->add($this->imgBox())
							->add($oDivClose);
					}
				break;
				default:
					Core_Event::notify('Lib_Controller_Libproperties.onGetOptionsList', $this, array($oLib_Property, $oDivInputs, $value));
			}

			$oDivRow->execute();
		}
	}

	/**
	 * Create imageBox
	 * @param string $addFunction
	 * @param string $deleteOnclick
	 * @return self
	 */
	public function imgBox($addFunction = '$.cloneMultipleValue', $deleteOnclick = '$.deleteNewMultipleValue(this)')
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
							->class('btn btn-palegreen')
							->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-plus-circle close"></i>'))
							->onclick("{$addFunction}('{$windowId}', this);")
					)
					->add(
						Admin_Form_Entity::factory('Div')
							->class('btn btn-darkorange btn-delete')
							->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-minus-circle close"></i>'))
							->onclick($deleteOnclick)
					)
				)
				->execute();

		return Admin_Form_Entity::factory('Code')->html(ob_get_clean());
	}
}