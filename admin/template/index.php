<?php
/**
 * Templates.
 *
 * @package HostCMS
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
require_once('../../bootstrap.php');

Core_Auth::authorization($sModule = 'template');

// Код формы
$iAdmin_Form_Id = 6;
$sAdminFormAction = '/{admin}/template/index.php';

$oAdmin_Form = Core_Entity::factory('Admin_Form', $iAdmin_Form_Id);

$oParentTemplate = Core_Entity::factory('Template', Core_Array::getGet('template_id', 0, 'int'));

$sFormTitle = $oParentTemplate->id
	? $oParentTemplate->name
	: Core::_('Template.title');

// Контроллер формы
$oAdmin_Form_Controller = Admin_Form_Controller::create($oAdmin_Form);
$oAdmin_Form_Controller
	->module(Core_Module_Abstract::factory($sModule))
	->setUp()
	->path($sAdminFormAction)
	->title($sFormTitle)
	->pageTitle($sFormTitle);

if (!is_null(Core_Array::getPost('showDesignPanel')))
{
	$aJSON = array(
		'status' => 'error',
		'html' => ''
	);

	$template_section_lib_id = Core_Array::getPost('template_section_lib_id', 0, 'int');

	if ($template_section_lib_id)
	{
		$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib')->getById($template_section_lib_id);

		if (!is_null($oTemplate_Section_Lib))
		{
			$aAttributes = Core_Array::getPost('attributes', array(), 'array');
			$field = Core_Array::getPost('field', '', 'trim');

			$oTemplate_Section_Lib_Controller = new Template_Section_Lib_Controller($oTemplate_Section_Lib);

			$aJSON = array(
				'status' => 'success',
				'html' => $oTemplate_Section_Lib_Controller->showPanel($field, $aAttributes)
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('refreshStyle')))
{
	$aJSON = array(
		'status' => 'error'
	);

	$template_section_lib_id = Core_Array::getPost('template_section_lib_id', 0, 'int');
	$property = Core_Array::getPost('property', '', 'trim');
	$type = Core_Array::getPost('type', '', 'trim');

	if ($template_section_lib_id && $property != '' && $type != '')
	{
		$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib')->getById($template_section_lib_id);
		if (!is_null($oTemplate_Section_Lib))
		{
			$value = Core_Array::getPost('value', '', 'trim');
			$field = Core_Array::getPost('field', '', 'trim');

			if ($type == 'user_css')
			{
				$oTemplate_Section_Lib->user_css = $value;
				$oTemplate_Section_Lib->save();

				Core::showJson(array(
					'status' => 'success'
				));
			}

			$oTemplate_Section_Lib_Controller = new Template_Section_Lib_Controller($oTemplate_Section_Lib);

			$aFieldStyles = $oTemplate_Section_Lib->field_styles != ''
				? json_decode($oTemplate_Section_Lib->field_styles, TRUE)
				: array();

			if ($field == '')
			{
				$aStyles = $oTemplate_Section_Lib_Controller->parseStyles($type);
			}
			else
			{
				$aStyles = isset($aFieldStyles[$field])
					? $oTemplate_Section_Lib_Controller->parseStyles($type, array(), $aFieldStyles[$field])
					: array();
			}

			// echo "<pre>";
			// var_dump($aStyles);
			// echo "</pre>";

			if (isset($aStyles[$property]))
			{
				if ($value != '')
				{
					$aStyles[$property] = $value;
				}
				else
				{
					unset($aStyles[$property]);
				}
			}
			else
			{
				$aStyles[$property] = $value;
			}

			if ($field == '')
			{
				$oTemplate_Section_Lib->style = $oTemplate_Section_Lib_Controller->createStyle($type, $aStyles);
			}
			else
			{
				$aFieldStyles[$field] = $oTemplate_Section_Lib_Controller->createStyle($type, $aStyles);

				$oTemplate_Section_Lib->field_styles = json_encode($aFieldStyles, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
			}

			$oTemplate_Section_Lib->save();

			$aJSON = array(
				'status' => 'success'
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('changePreset')))
{
	$aJSON = array(
		'status' => 'error'
	);

	$template_section_lib_id = Core_Array::getPost('template_section_lib_id', 0, 'int');
	// $type = Core_Array::getPost('type', '', 'trim');

	if ($template_section_lib_id/* && $type != ''*/)
	{
		$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib')->getById($template_section_lib_id);

		if (!is_null($oTemplate_Section_Lib))
		{
			$class = Core_Array::getPost('class', '', 'trim');
			$field = Core_Array::getPost('field', '', 'trim');

			if ($field == '')
			{
				$oTemplate_Section_Lib->class = implode(' ', array_unique(array_map('trim', explode(' ', $class))));
			}
			else
			{
				$aFieldClasses = $oTemplate_Section_Lib->field_classes != ''
					? json_decode($oTemplate_Section_Lib->field_classes, TRUE)
					: array();

				$aFieldClasses[$field] = implode(' ', array_unique(array_map('trim', explode(' ', $class)))); // only unique classes

				$oTemplate_Section_Lib->field_classes = json_encode($aFieldClasses, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
			}

			$oTemplate_Section_Lib->save();

			$aJSON = array(
				'status' => 'success'
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('showFontsPanel')))
{
	$aJSON = array(
		'status' => 'error'
	);

	$template_section_lib_id = Core_Array::getPost('template_section_lib_id', 0, 'int');

	if ($template_section_lib_id)
	{
		$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib')->getById($template_section_lib_id);

		if (!is_null($oTemplate_Section_Lib))
		{
			$oTemplate_Section_Lib_Controller = new Template_Section_Lib_Controller($oTemplate_Section_Lib);

			$aJSON = array(
				'status' => 'success',
				'html' => $oTemplate_Section_Lib_Controller->showFontsPanel()
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('changeFont')))
{
	$aJSON = array(
		'status' => 'error'
	);

	$template_section_lib_id = Core_Array::getPost('template_section_lib_id', 0, 'int');

	if ($template_section_lib_id)
	{
		$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib')->getById($template_section_lib_id);

		if (!is_null($oTemplate_Section_Lib))
		{
			$class = Core_Array::getPost('class', '', 'trim');

			$oTemplate_Section_Lib->class = implode(' ', array_unique(array_map('trim', explode(' ', $class))));
			$oTemplate_Section_Lib->save();

			$aJSON = array(
				'status' => 'success'
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('showWidgetPanel')))
{
	$aJSON = array(
		'status' => 'error',
		'html' => ''
	);

	$template_section_id = Core_Array::getPost('template_section_id', 0, 'int');

	if ($template_section_id)
	{
		$oTemplate_Section = Core_Entity::factory('Template_Section')->getById($template_section_id);

		if (!is_null($oTemplate_Section))
		{
			$oTemplate_Section_Controller = new Template_Section_Controller($oTemplate_Section);

			$template_section_lib_id = Core_Array::getPost('template_section_lib_id', 0, 'int');
			$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib')->getById($template_section_lib_id);

			!is_null($oTemplate_Section_Lib)
				&& $oTemplate_Section_Controller->templateSectionLib($oTemplate_Section_Lib);

			$aJSON = array(
				'status' => 'success',
				'html' => $oTemplate_Section_Controller->showPanel()
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('showWidgets')))
{
	$aJSON = array(
		'status' => 'error',
		'html' => ''
	);

	$template_section_id = Core_Array::getPost('template_section_id', 0, 'int');
	$lib_dir_id = Core_Array::getPost('lib_dir_id', 0, 'int');

	if ($template_section_id && $lib_dir_id)
	{
		$oTemplate_Section = Core_Entity::factory('Template_Section')->getById($template_section_id);
		$oLib_Dir = Core_Entity::factory('Lib_Dir')->getById($lib_dir_id);

		if (!is_null($oLib_Dir) && !is_null($oTemplate_Section))
		{
			$oTemplate_Section_Controller = new Template_Section_Controller($oTemplate_Section);

			$template_section_lib_id = Core_Array::getPost('template_section_lib_id', 0, 'int');
			$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib')->getById($template_section_lib_id);

			!is_null($oTemplate_Section_Lib)
				&& $oTemplate_Section_Controller->templateSectionLib($oTemplate_Section_Lib);

			$aJSON = array(
				'status' => 'success',
				'html' => $oTemplate_Section_Controller->getWidgets($oLib_Dir, $template_section_id)
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('addWidget')))
{
	$aJSON = array(
		'status' => 'error',
		'html' => ''
	);

	$template_section_id = Core_Array::getPost('template_section_id', 0, 'int');
	$lib_id = Core_Array::getPost('lib_id', 0, 'int');

	if ($template_section_id && $lib_id)
	{
		$oTemplate_Section = Core_Entity::factory('Template_Section')->getById($template_section_id);
		$oLib = Core_Entity::factory('Lib')->getById($lib_id);

		if (!is_null($oLib) && !is_null($oTemplate_Section))
		{
			$template_section_lib_id = Core_Array::getPost('template_section_lib_id', 0, 'int');
			$oTemplate_Section_Lib_Prev = Core_Entity::factory('Template_Section_Lib')->getById($template_section_lib_id);
			$previous_sorting = !is_null($oTemplate_Section_Lib_Prev)
				? $oTemplate_Section_Lib_Prev->sorting
				: 0;

			$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib');
			$oTemplate_Section_Lib->template_section_id = $oTemplate_Section->id;
			$oTemplate_Section_Lib->lib_id = $oLib->id;
			$oTemplate_Section_Lib->class = $oLib->class;
			$oTemplate_Section_Lib->style = $oLib->style;
			$oTemplate_Section_Lib->sorting = $previous_sorting + 1;

			$aOptions = array();

			$aLib_Properties = $oLib->Lib_Properties->findAll();
			foreach ($aLib_Properties as $oLib_Property)
			{
				$aOptions[$oLib_Property->varible_name] = $oLib_Property->type != 10
					? $oLib_Property->default_value
					: json_decode($oLib_Property->default_value);
			}

			$oTemplate_Section_Lib->options = json_encode($aOptions, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);

			$oTemplate_Section_Lib->save();

			// Пересортировка
			$oTemplate_Section_Libs = $oTemplate_Section->Template_Section_Libs;
			$oTemplate_Section_Libs->queryBuilder()
				->where('template_section_libs.id', '!=', $oTemplate_Section_Lib->id)
				->where('template_section_libs.sorting', '>=', $oTemplate_Section_Lib->sorting)
				->clearOrderBy()
				->orderBy('template_section_libs.sorting');

			$aTemplate_Section_Libs	= $oTemplate_Section_Libs->findAll(FALSE);
			foreach ($aTemplate_Section_Libs as $oTemplate_Section_Lib_Tmp)
			{
				$oTemplate_Section_Lib_Tmp->sorting += 1;
				$oTemplate_Section_Lib_Tmp->save();
			}

			$aJSON = array(
				'status' => 'success'
			);
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('saveContent')))
{
	$aJSON = array(
		'status' => 'error'
	);

	$aData = Core_Array::getPost('data', array(), 'array');

	if (isset($aData['id']))
	{
		$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib')->getById($aData['id'], FALSE);
		if (!is_null($oTemplate_Section_Lib) && $oTemplate_Section_Lib->lib_id)
		{
			$oLib = $oTemplate_Section_Lib->Lib;

			$name = Core_Array::get($aData, 'name', '', 'trim');
			$value = Core_Array::get($aData, 'value', '', 'trim');
			$prefix = Core_Array::get($aData, 'prefix', '', 'trim');
			$position = Core_Array::get($aData, 'position', 0, 'int');

			$aOptions = !is_null($oTemplate_Section_Lib->options)
				? json_decode($oTemplate_Section_Lib->options, TRUE)
				: array();

			// echo "<pre>";
			// var_dump($aOptions);
			// echo "</pre>";

			if ($prefix == '' && isset($aOptions[$name]))
			{
				$oLib_Property = $oTemplate_Section_Lib->Lib->Lib_Properties->getByvarible_name($name);
				$oLib_Property && $oLib_Property->type != 9 && $value = strip_tags($value);

				$aOptions[$name] = $value;
			}
			elseif($prefix != '' && isset($aOptions[$prefix][$position - 1][$name]))
			{
				$oLib_Property = $oTemplate_Section_Lib->Lib->Lib_Properties->getByvarible_name($prefix);
				if ($oLib_Property)
				{
					$oLib_Property = $oLib_Property->Lib_Properties->getByvarible_name($name);
					$oLib_Property && $oLib_Property->type != 9 && $value = strip_tags($value);
				}

				$aOptions[$prefix][$position - 1][$name] = $value;
			}

			$oTemplate_Section_Lib->options = json_encode($aOptions, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
			$oTemplate_Section_Lib->save();

			$aJSON['status'] = 'success';
		}
	}

	Core::showJson($aJSON);
}

/*if (!is_null(Core_Array::getPost('saveSettings')))
{
	$aJSON = array(
		'status' => 'error',
		'html' => ''
	);

	$template_section_lib_id = Core_Array::getPost('template_section_lib_id', 0, 'int');

	$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib')->getById($template_section_lib_id);

	if (!is_null($oTemplate_Section_Lib) && $oTemplate_Section_Lib->lib_id)
	{
		$oLib = $oTemplate_Section_Lib->Lib;

		$aFieldClasses = !is_null($oTemplate_Section_Lib->field_classes)
			? json_decode($oTemplate_Section_Lib->field_classes, TRUE)
			: array();

		$aFieldStyles = !is_null($oTemplate_Section_Lib->field_styles)
			? json_decode($oTemplate_Section_Lib->field_styles, TRUE)
			: array();

		$aOldOptions = !is_null($oTemplate_Section_Lib->options)
			? json_decode($oTemplate_Section_Lib->options, TRUE)
			: array();

		$oTemplate_Section_Lib_Controller = new Template_Section_Lib_Controller($oTemplate_Section_Lib);

		$aOptions = $aSubOptions = array();
		$aNewFieldClasses = $aNewFieldStyles = array();

		$aLib_Properties = $oLib->Lib_Properties->getAllByparent_id(0, FALSE);
		foreach ($aLib_Properties as $oLib_Property)
		{

// echo "<pre>";
// var_dump($_POST[$oLib_Property->varible_name]);
// echo "</pre>";

			if (isset($_POST[$oLib_Property->varible_name]) || isset($_FILES[$oLib_Property->varible_name]))
			{
				if ($oLib_Property->type == 10)
				{
					$position = 0;

					foreach ($_POST[$oLib_Property->varible_name] as $old_position => $aElement)
					{
						$old_position++;
						$new_position = $position + 1;

						$aTmp = array();

						$aSub_Lib_Properties = $oLib_Property->Lib_Properties->findAll(FALSE);
						foreach ($aSub_Lib_Properties as $oSub_Lib_Property)
						{
							$aTmp[$oSub_Lib_Property->varible_name] = isset($aElement[$oSub_Lib_Property->varible_name])
								? $aElement[$oSub_Lib_Property->varible_name]
								: '';
						}

						foreach ($aTmp as $name => $value)
						{
							$oSub_Lib_Property = $oLib_Property->Lib_Properties->getByVarible_name($name, FALSE);

							if (!is_null($oSub_Lib_Property))
							{
								if ($oSub_Lib_Property->type == 8)
								{
									$aNewValues = $oTemplate_Section_Lib_Controller->uploadWidgetComplexFile($oSub_Lib_Property, $position);

									$value = $oSub_Lib_Property->multivalue
										? $aNewValues
										: Core_Array::get($aNewValues, 0);
								}
							}

							$aSubOptions[$position][$name] = $value;

							// Стили и классы составных элементов
							isset($aFieldClasses[$name . '_' . $old_position])
								&& $aNewFieldClasses[$name . '_' . $new_position] = $aFieldClasses[$name . '_' . $old_position];

							isset($aFieldStyles[$name . '_' . $old_position])
								&& $aNewFieldStyles[$name . '_' . $new_position] = $aFieldStyles[$name . '_' . $old_position];
						}

						$position++;
					}

					$aOptions[$oLib_Property->varible_name] = $aSubOptions;
				}
				else
				{
					if ($oLib_Property->type == 8)
					{

						$aNewValues = $oTemplate_Section_Lib_Controller->uploadWidgetFile($oLib_Property);

						$aOptions[$oLib_Property->varible_name] = $oLib_Property->multivalue
							? $aNewValues
							: Core_Array::get($aNewValues, 0);
					}
					else
					{
						$newValue = Core_Array::getPost($oLib_Property->varible_name, '');

						$newValue = is_array($newValue)
							? $newValue[0]
							: $newValue;

						switch ($oLib_Property->type)
						{
							case 1:
								$newValue = boolval($newValue);
							break;
						}

						if (is_array($newValue))
						{
							ksort($newValue);
							$newValue = array_values($newValue);
						}

						$aOptions[$oLib_Property->varible_name] = $newValue;
					}

					// Стили и классы не составных элементов
					isset($aFieldClasses[$oLib_Property->varible_name])
						&& $aNewFieldClasses[$oLib_Property->varible_name] = $aFieldClasses[$oLib_Property->varible_name];

					isset($aFieldStyles[$oLib_Property->varible_name])
						&& $aNewFieldStyles[$oLib_Property->varible_name] = $aFieldStyles[$oLib_Property->varible_name];
				}
			}
			elseif (in_array($oLib_Property->type, Template_Section_Lib_Controller::$forbiddenToShow))
			{
				if (isset($aOldOptions[$oLib_Property->varible_name]))
				{
					$aOptions[$oLib_Property->varible_name] = $aOldOptions[$oLib_Property->varible_name];
				}
			}
		}

		$oTemplate_Section_Lib->options = json_encode($aOptions, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
		$oTemplate_Section_Lib->field_classes = json_encode($aNewFieldClasses, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
		$oTemplate_Section_Lib->field_styles = json_encode($aNewFieldStyles, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
		$oTemplate_Section_Lib->save();

		$aJSON['status'] = 'success';
	}

	Core::showJson($aJSON);
}*/

if (!is_null(Core_Array::getPost('saveSettings')))
{
	$aJSON = array(
		'status' => 'error',
		'html' => ''
	);

	$template_section_lib_id = Core_Array::getPost('template_section_lib_id', 0, 'int');

	$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib')->getById($template_section_lib_id);

	if (!is_null($oTemplate_Section_Lib) && $oTemplate_Section_Lib->lib_id)
	{
		$oLib = $oTemplate_Section_Lib->Lib;

		$aFieldClasses = !is_null($oTemplate_Section_Lib->field_classes)
			? json_decode($oTemplate_Section_Lib->field_classes, TRUE)
			: array();

		$aFieldStyles = !is_null($oTemplate_Section_Lib->field_styles)
			? json_decode($oTemplate_Section_Lib->field_styles, TRUE)
			: array();

		$aOldOptions = !is_null($oTemplate_Section_Lib->options)
			? json_decode($oTemplate_Section_Lib->options, TRUE)
			: array();

		$oTemplate_Section_Lib_Controller = new Template_Section_Lib_Controller($oTemplate_Section_Lib);

		$aOptions = $aSubOptions = array();
		$aNewFieldClasses = $aNewFieldStyles = array();

		$aLib_Properties = $oLib->Lib_Properties->getAllByparent_id(0, FALSE);
		foreach ($aLib_Properties as $oLib_Property)
		{
			if (isset($_POST[$oLib_Property->varible_name]) || isset($_FILES[$oLib_Property->varible_name]))
			{
				if ($oLib_Property->type == 10)
				{
					$position = 0;

					// Получаем старые значения для этого свойства, если они есть
					$aOldSubOptions = isset($aOldOptions[$oLib_Property->varible_name])
						? $aOldOptions[$oLib_Property->varible_name]
						: array();

					foreach ($_POST[$oLib_Property->varible_name] as $old_position => $aElement)
					{
						$old_position++;
						$new_position = $position + 1;

						$aTmp = array();

						$aSub_Lib_Properties = $oLib_Property->Lib_Properties->findAll(FALSE);
						foreach ($aSub_Lib_Properties as $oSub_Lib_Property)
						{
							$aTmp[$oSub_Lib_Property->varible_name] = isset($aElement[$oSub_Lib_Property->varible_name])
								? $aElement[$oSub_Lib_Property->varible_name]
								: '';
						}

						foreach ($aTmp as $name => $value)
						{
							$oSub_Lib_Property = $oLib_Property->Lib_Properties->getByVarible_name($name, FALSE);

							if (!is_null($oSub_Lib_Property))
							{
								if ($oSub_Lib_Property->type == 8)
								{
									// Проверяем, загружен ли новый файл для текущей позиции
									$bFileUploaded = FALSE;

									if (isset($_FILES[$oLib_Property->varible_name]))
									{
										$aFiles = $_FILES[$oLib_Property->varible_name];

										// Проверяем, есть ли файл для текущей позиции и поля
										if (isset($aFiles['name'][$old_position - 1][$name]) &&
											isset($aFiles['error'][$old_position - 1][$name]) &&
											$aFiles['error'][$old_position - 1][$name] != 4)
										{
											// Загружаем новый файл
											$aNewValues = $oTemplate_Section_Lib_Controller->uploadWidgetComplexFile($oSub_Lib_Property, $old_position - 1);

											$value = $oSub_Lib_Property->multivalue
												? $aNewValues
												: Core_Array::get($aNewValues, 0);

											$bFileUploaded = TRUE;
										}
									}

									// Если новый файл не загружен, используем старое значение
									if (!$bFileUploaded)
									{
										$value = isset($aOldSubOptions[$old_position - 1][$name])
											? $aOldSubOptions[$old_position - 1][$name]
											: '';
									}
								}
							}

							$aSubOptions[$position][$name] = $value;

							// Стили и классы составных элементов
							isset($aFieldClasses[$name . '_' . $old_position])
								&& $aNewFieldClasses[$name . '_' . $new_position] = $aFieldClasses[$name . '_' . $old_position];

							isset($aFieldStyles[$name . '_' . $old_position])
								&& $aNewFieldStyles[$name . '_' . $new_position] = $aFieldStyles[$name . '_' . $old_position];
						}

						$position++;
					}

					$aOptions[$oLib_Property->varible_name] = $aSubOptions;
				}
				else
				{
					if ($oLib_Property->type == 8)
					{
						// Проверяем, загружен ли новый файл
						if (isset($_FILES[$oLib_Property->varible_name]) && $_FILES[$oLib_Property->varible_name]['error'][0] != 4)
						{
							$aNewValues = $oTemplate_Section_Lib_Controller->uploadWidgetFile($oLib_Property);
							$aOptions[$oLib_Property->varible_name] = $oLib_Property->multivalue
								? $aNewValues
								: Core_Array::get($aNewValues, 0);
						}
						else
						{
							// Используем старое значение файла
							if (isset($aOldOptions[$oLib_Property->varible_name]))
							{
								$aOptions[$oLib_Property->varible_name] = $aOldOptions[$oLib_Property->varible_name];
							}
							else
							{
								$aOptions[$oLib_Property->varible_name] = '';
							}
						}
					}
					else
					{
						$newValue = Core_Array::getPost($oLib_Property->varible_name, '');

						$newValue = is_array($newValue)
							? $newValue[0]
							: $newValue;

						switch ($oLib_Property->type)
						{
							case 1:
								$newValue = boolval($newValue);
							break;
						}

						if (is_array($newValue))
						{
							ksort($newValue);
							$newValue = array_values($newValue);
						}

						$aOptions[$oLib_Property->varible_name] = $newValue;
					}

					// Стили и классы не составных элементов
					isset($aFieldClasses[$oLib_Property->varible_name])
						&& $aNewFieldClasses[$oLib_Property->varible_name] = $aFieldClasses[$oLib_Property->varible_name];

					isset($aFieldStyles[$oLib_Property->varible_name])
						&& $aNewFieldStyles[$oLib_Property->varible_name] = $aFieldStyles[$oLib_Property->varible_name];
				}
			}
			elseif (in_array($oLib_Property->type, Template_Section_Lib_Controller::$forbiddenToShow))
			{
				if (isset($aOldOptions[$oLib_Property->varible_name]))
				{
					$aOptions[$oLib_Property->varible_name] = $aOldOptions[$oLib_Property->varible_name];
				}
			}
		}

		$oTemplate_Section_Lib->options = json_encode($aOptions, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
		$oTemplate_Section_Lib->field_classes = json_encode($aNewFieldClasses, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
		$oTemplate_Section_Lib->field_styles = json_encode($aNewFieldStyles, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
		$oTemplate_Section_Lib->save();

		$aJSON['status'] = 'success';
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('refreshSettingsBlock')))
{
	$aJSON = array(
		'status' => 'error',
		'html' => ''
	);

	$template_section_lib_id = Core_Array::getPost('template_section_lib_id', 0, 'int');

	$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib')->getById($template_section_lib_id);

	if (!is_null($oTemplate_Section_Lib) && $oTemplate_Section_Lib->lib_id)
	{
		$oTemplate_Section_Lib_Controller = new Template_Section_Lib_Controller($oTemplate_Section_Lib);

		$aJSON['html'] = $oTemplate_Section_Lib_Controller->showSettingsBlock();
		$aJSON['status'] = 'success';
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('showSettingsCrmIcons')))
{
	$aJSON = array(
		'status' => 'error',
		'html' => ''
	);

	$input_name = Core_Array::getPost('input_name', '', 'trim');

	if ($input_name != '')
	{
		$template_section_lib_id = Core_Array::getPost('template_section_lib_id', 0, 'int');

		ob_start();

		?><!--<div class="modal fade" id="settingsCrmIcons" tabindex="-1" aria-labelledby="settingsCrmIconsLabel" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h1 class="modal-title fs-5" id="settingsCrmIconsLabel">Modal title</h1>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">-->
						<input type="text" class="icon-filter margin-bottom-20 w-100 input-lg" class="form-control" placeholder="Введите название иконки, например, 'arrow'"/>
						<div class="crm-icon-wrapper">
							<div class="crm-icon-modal">
								<?php
									$aCrm_Icons = Core_Entity::factory('Crm_Icon')->findAll(FALSE);
									foreach ($aCrm_Icons as $oCrm_Icon)
									{
										$value = htmlspecialchars($oCrm_Icon->value);

										?><span onclick="hQuery.selectSettingsCrmIcon(this, '<?php echo $input_name?>', <?php echo $template_section_lib_id?>)" class="crm-project-id" data-id="<?php echo $oCrm_Icon->id?>" data-value="<?php echo $value?>"><i class="<?php echo $value?>"></i></span><?php
									}
								?>
							</div>
						</div>
					<!-- </div>
				</div>
			</div>
		</div>-->
		<script>
			$(function() {
				$(".icon-filter").on('keyup', function(){
					var selectIcon = $(this).val();

					if (selectIcon.length)
					{
						filter(selectIcon);
					}
					else
					{
						$('.crm-icon-modal .crm-project-id').show();
					}
				});

				function filter(e) {
					$('.crm-icon-modal .crm-project-id').hide()
						.filter(function() {
							// console.log($(this).data('value'));
							return $(this).data('value').toLowerCase().indexOf(e.toLowerCase()) > -1;
						})
						.show();
				}
			});
		</script><?php

		$aJSON = array(
			'status' => 'success',
			'html' => ob_get_clean()
		);
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('addPoint')) || !is_null(Core_Array::getPost('copyPoint')))
{
	$aJSON = array(
		'status' => 'error'
	);

	$template_section_lib_id = Core_Array::getPost('template_section_lib_id', 0, 'int');

	$oTemplate_Section_Lib = Core_Entity::factory('Template_Section_Lib')->getById($template_section_lib_id);

	if (!is_null($oTemplate_Section_Lib) && $oTemplate_Section_Lib->lib_id)
	{
		$lib_property_id = Core_Array::getPost('lib_property_id', 0, 'int');

		$oLib_Property = Core_Entity::factory('Lib_Property')->getById($lib_property_id, FALSE);

		if (!is_null($oLib_Property))
		{
			if ($oLib_Property->type == 10)
			{
				$aLibOptions = !is_null($oTemplate_Section_Lib->options)
					? json_decode($oTemplate_Section_Lib->options, TRUE)
					: array();

				$oLib = $oTemplate_Section_Lib->Lib;

				$aTmp = $oLib->Lib_Properties->getAllByparent_id($oLib_Property->id, FALSE);

				// Получаем значение параметра
				$value = isset($aLibOptions[$oLib_Property->varible_name])
					? $aLibOptions[$oLib_Property->varible_name]
					: ($oLib_Property->type != 8
						? $oLib_Property->default_value
						: NULL
					);

				!is_array($value) && $value = array($value);

				$count = Core_Array::getPost('count', 0, 'int');
				$blockId = Core_Array::getPost('block_id', 0, 'int');

				ob_start();

				$oTemplate_Section_Lib_Controller = new Template_Section_Lib_Controller($oTemplate_Section_Lib);
				$oTemplate_Section_Lib_Controller->showDetailsItem($oLib_Property, $value, $aTmp, $count + 1, $blockId, FALSE, TRUE);

				$aJSON = array(
					'status' => 'success',
					'html' => ob_get_clean()
				);
			}
		}
	}

	Core::showJson($aJSON);
}

if (!is_null(Core_Array::getPost('showLessPanel')))
{
	$aJSON = array(
		'status' => 'error',
		'html' => ''
	);

	$ids = Core_Array::getPost('ids', '', 'trim');

	if ($ids != '')
	{
		ob_start();

		$aTemplatIds = explode(',', $ids);
		foreach ($aTemplatIds as $template_id)
		{
			$oTemplate = Core_Entity::factory('Template')->getById($template_id, FALSE);
			if (!is_null($oTemplate))
			{
				$oTemplate->showManifest();
			}
		}

		$aJSON['html'] = ob_get_clean();
		$aJSON['status'] = 'success';
	}

	Core::showJson($aJSON);
}

$template_dir_id = Core_Array::getGet('template_dir_id', 0, 'int');
$template_id = Core_Array::getGet('template_id', 0, 'int');

// Меню формы
$oAdmin_Form_Entity_Menus = Admin_Form_Entity::factory('Menus');

// Элементы меню
$oAdmin_Form_Entity_Menus->add(
	Admin_Form_Entity::factory('Menu')
		->name(Core::_('Template.menu1'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, 0)
		)
);

if (!$template_id)
{
	$oAdmin_Form_Entity_Menus->add(

		Admin_Form_Entity::factory('Menu')
		->name(Core::_('Template_Dir.menu'))
		->icon('fa fa-plus')
		->href(
			$oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 0, 0)
		)
	);
}

// Добавляем все меню контроллеру
$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Menus);

if ($oParentTemplate->id)
{
	$href = $oAdmin_Form_Controller->getAdminActionLoadHref($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, $oParentTemplate->id);
	$onclick = $oAdmin_Form_Controller->getAdminActionLoadAjax($oAdmin_Form_Controller->getPath(), 'edit', NULL, 1, $oParentTemplate->id);

	$oAdmin_Form_Controller->addEntity(
		$oAdmin_Form_Controller->getTitleEditIcon($href, $onclick)
	);
}

// Глобальный поиск
$additionalParamsProperties = "template_dir_id={$template_dir_id}&template_id={$template_id}";

$sGlobalSearch = Core_Array::getGet('globalSearch', '', 'trim');

$oAdmin_Form_Controller->addEntity(
	Admin_Form_Entity::factory('Code')
		->html('
			<div class="row search-field margin-bottom-20">
				<div class="col-xs-12">
					<form action="' . $oAdmin_Form_Controller->getPath() . '" method="GET">
						<input type="text" name="globalSearch" class="form-control" placeholder="' . Core::_('Admin.placeholderGlobalSearch') . '" value="' . htmlspecialchars($sGlobalSearch) . '" />
						<i class="fa fa-times-circle no-margin" onclick="' . $oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), '', '', $additionalParamsProperties) . '"></i>
						<button type="submit" class="btn btn-default global-search-button" onclick="' . $oAdmin_Form_Controller->getAdminSendForm('', '', $additionalParamsProperties) . '"><i class="fa-solid fa-magnifying-glass fa-fw"></i></button>
					</form>
				</div>
			</div>
		')
);

$sGlobalSearch = str_replace(' ', '%', Core_DataBase::instance()->escapeLike($sGlobalSearch));

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs = Admin_Form_Entity::factory('Breadcrumbs');

// Элементы строки навигации
$oAdmin_Form_Entity_Breadcrumbs->add(
	Admin_Form_Entity::factory('Breadcrumb')
		->name(Core::_('Template_dir.root'))
		->href(
			$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
		)
		->onclick(
			$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, '')
	)
);

if ($template_dir_id)
{
	// Если передана родительская группа - строим хлебные крошки
	$oTemplateDir = Core_Entity::factory('Template_Dir')->find($template_dir_id);

	if (!is_null($oTemplateDir->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'template_dir_id=' . intval($oTemplateDir->id);

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name($oTemplateDir->name)
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while ($oTemplateDir = $oTemplateDir->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

if ($template_id)
{
	$oParentTemplate = Core_Entity::factory('Template')->find($template_id);

	if (!is_null($oParentTemplate->id))
	{
		$aBreadcrumbs = array();

		do
		{
			$additionalParams = 'template_dir_id=' . intval($oParentTemplate->template_dir_id)
				. '&template_id=' . $oParentTemplate->id;

			$aBreadcrumbs[] = Admin_Form_Entity::factory('Breadcrumb')
				->name(Core::_('Template.breadCrumb', $oParentTemplate->name, FALSE))
				->href(
					$oAdmin_Form_Controller->getAdminLoadHref($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				)
				->onclick(
					$oAdmin_Form_Controller->getAdminLoadAjax($oAdmin_Form_Controller->getPath(), NULL, NULL, $additionalParams)
				);
		} while ($oParentTemplate = $oParentTemplate->getParent());

		$aBreadcrumbs = array_reverse($aBreadcrumbs);

		foreach ($aBreadcrumbs as $oAdmin_Form_Entity_Breadcrumb)
		{
			$oAdmin_Form_Entity_Breadcrumbs->add(
				$oAdmin_Form_Entity_Breadcrumb
			);
		}
	}
}

if ($template_dir_id || $template_id)
{
	// Добавляем все хлебные крошки контроллеру
	$oAdmin_Form_Controller->addEntity($oAdmin_Form_Entity_Breadcrumbs);
}

// Действие редактирования
$oAdmin_Form_Action = $oAdmin_Form->Admin_Form_Actions->getByName('edit');

if ($oAdmin_Form_Action && $oAdmin_Form_Controller->getAction() == 'edit')
{
	$oTemplate_Controller_Edit = Admin_Form_Action_Controller::factory(
		'Template_Controller_Edit', $oAdmin_Form_Action
	);

	$oTemplate_Controller_Edit
		->addEntity($oAdmin_Form_Entity_Breadcrumbs);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTemplate_Controller_Edit);
}

// Действие "Применить"
$oAdminFormActionApply = $oAdmin_Form->Admin_Form_Actions->getByName('apply');

if ($oAdminFormActionApply && $oAdmin_Form_Controller->getAction() == 'apply')
{
	$oTemplateDirControllerApply = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Apply', $oAdminFormActionApply
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oTemplateDirControllerApply);
}

// Действие "Копировать"
$oAdminFormActionCopy = $oAdmin_Form->Admin_Form_Actions->getByName('copy');

if ($oAdminFormActionCopy && $oAdmin_Form_Controller->getAction() == 'copy')
{
	$oControllerCopy = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Copy', $oAdminFormActionCopy
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerCopy);
}

$oAdminFormActionRollback = $oAdmin_Form->Admin_Form_Actions->getByName('rollback');

if ($oAdminFormActionRollback && $oAdmin_Form_Controller->getAction() == 'rollback')
{
	$oControllerRollback = Admin_Form_Action_Controller::factory(
		'Admin_Form_Action_Controller_Type_Rollback', $oAdminFormActionRollback
	);

	// Добавляем типовой контроллер редактирования контроллеру формы
	$oAdmin_Form_Controller->addAction($oControllerRollback);
}

// Источник данных 0
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Template_Dir')
);

// Ограничение источника 0 по родительской группе
$oAdmin_Form_Dataset->addCondition(
	array('where' =>
		array('site_id', '=', CURRENT_SITE)
	)
)->addCondition(
	array('where' =>
		array('parent_id', '=', $template_id == 0 ? $template_dir_id : NULL)
	)
);

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Источник данных 1
$oAdmin_Form_Dataset = new Admin_Form_Dataset_Entity(
	Core_Entity::factory('Template')
);

// Доступ только к своим
$oUser = Core_Auth::getCurrentUser();
!$oUser->superuser && $oUser->only_access_my_own
	&& $oAdmin_Form_Dataset->addUserConditions();

// Ограничение источника 1 по родительской группе
$oAdmin_Form_Dataset
	->addCondition(array('where' => array('site_id', '=', CURRENT_SITE)))
	//->changeField('name', 'type', 1)
	->changeField('name', 'link', '/{admin}/template/index.php?template_dir_id={template_dir_id}&template_id={id}')
	->changeField('name', 'onclick', "$.adminLoad({path: '/{admin}/template/index.php',additionalParams: 'template_dir_id={template_dir_id}&template_id={id}', windowId: '{windowId}'}); return false");

if (strlen($sGlobalSearch))
{
	$oAdmin_Form_Dataset
		->addCondition(array('open' => array()));

	is_numeric($sGlobalSearch) && $oAdmin_Form_Dataset
			->addCondition(array('where' => array('templates.id', '=', intval($sGlobalSearch))))
			->addCondition(array('setOr' => array()));

	$oAdmin_Form_Dataset
			->addCondition(array('where' => array('templates.name', 'LIKE', '%' . $sGlobalSearch . '%')))
		->addCondition(array('close' => array()));
}
else
{
	if ($template_id == 0)
	{
		$oAdmin_Form_Dataset
			->addCondition(
				array('where' =>
					array('template_dir_id', '=', $template_dir_id)
				)
			)
			->addCondition(
				array('where' =>
					array('template_id', '=', 0)
				)
			);
	}
	else
	{
		$oAdmin_Form_Dataset
			->addCondition(
				array('where' =>
					array('template_id', '=', $template_id)
				)
			);
	}
}

// Добавляем источник данных контроллеру формы
$oAdmin_Form_Controller->addDataset(
	$oAdmin_Form_Dataset
);

// Показ формы
$oAdmin_Form_Controller->execute();
