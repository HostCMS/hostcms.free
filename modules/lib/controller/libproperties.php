<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Lib.
 * Типовой контроллер загрузки свойст типовой дин. страницы для структуры
 *
 * @package HostCMS
 * @subpackage Lib
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
	 * Constructor.
	 * @param Admin_Form_Action_Model $oAdmin_Form_Action action
	 */
	public function __construct(Admin_Form_Action_Model $oAdmin_Form_Action)
	{
		parent::__construct($oAdmin_Form_Action);
	}

	static public function getJson(Lib_Model $oLib)
	{
		$LA = array();

		$aLib_Properties = $oLib->Lib_Properties->findAll();

		foreach ($aLib_Properties as $oLib_Property)
		{
			$propertyName = 'lib_property_' . $oLib_Property->id;

			$propertyValue = Core_Array::getPost($propertyName);

			if ($oLib_Property->multivalue)
			{
				$aPropertyValues = is_array($propertyValue)
					? $propertyValue
					: array('');
			}
			else
			{
				$aPropertyValues = is_array($propertyValue)
					? array() // Delete wrong value
					: array($propertyValue);
			}

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
				}
				
				$aPropertyValues[$key] = $propertyValue;
			}

			$LA[$oLib_Property->varible_name] = $oLib_Property->multivalue
				? $aPropertyValues
				: $aPropertyValues[0];
		}

		return json_encode($LA);
	}

	/**
	 * Get options list
	 * @param array $LA
	 * @return self
	 */
	public function getOptionsList(array $LA)
	{
		$oLib = Core_Entity::factory('Lib', $this->_libId);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$aLib_Properties = $oLib->Lib_Properties->findAll();

		$oXsl_Controller_Edit = new Xsl_Controller_Edit($this->_Admin_Form_Action);
		$aXslDirs = $oXsl_Controller_Edit->fillXslDir(0);

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
									Core::factory('Core_Html_Entity_Script')
										->type("text/javascript")
										->value("$('#{$windowId} #xsl_dir_id_{$oLib_Property->id}').change();")
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
				->class('input-group-addon no-padding add-remove-property margin-top-20')
				->add(
					Admin_Form_Entity::factory('Div')
					->class('no-padding-right col-lg-12')
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