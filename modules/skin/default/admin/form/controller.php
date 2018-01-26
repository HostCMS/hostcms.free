<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Controller extends Admin_Form_Controller
{
	/**
	 * Show form content in administration center
	 * @return self
	 */
	public function showContent()
	{
		$aAdmin_Form_Fields = $this->_Admin_Form->Admin_Form_Fields->findAll();

		if (empty($aAdmin_Form_Fields))
		{
			throw new Core_Exception('Admin form does not have fields.');
		}

		$windowId = $this->getWindowId();

		$allow_filter = FALSE;

		?>
		<table width="100%" cellpadding="2" cellspacing="2" class="admin_table table">
		<thead>
		<tr class="admin_table_title"><?php

		// Ячейку над групповыми чекбоксами показываем только при наличии действий
		if ($this->_Admin_Form->show_operations && $this->_showOperations)
		{
			?><td width="25">&nbsp;</td><?php
		}

		foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
		{
			// There is at least one filter
			$oAdmin_Form_Field->allow_filter && $allow_filter = TRUE;

			$width = htmlspecialchars($oAdmin_Form_Field->width);
			$class = htmlspecialchars($oAdmin_Form_Field->class);

			$Admin_Word_Value = $oAdmin_Form_Field->Admin_Word->getWordByLanguage($this->_Admin_Language->id);

			// Слово найдено
			$fieldName = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
				? htmlspecialchars($Admin_Word_Value->name)
				: '—';

			// Change to Font Awesome
			if (strlen($oAdmin_Form_Field->ico))
			{
				$fieldName = '<i class="' . htmlspecialchars($oAdmin_Form_Field->ico) . '" title="' . $fieldName . '" />';
			}

			$oAdmin_Form_Field->allow_sorting
				&& $oAdmin_Form_Field->id == $this->_sortingAdmin_Form_Field->id
				&& $class .= ' highlight';

			?><td class="<?php echo trim($class)?>" <?php echo !empty($width) ? "width=\"{$width}\"" : ''?>><?php
				?><nobr><?php echo $fieldName?> <?php
				if ($oAdmin_Form_Field->allow_sorting)
				{
					$hrefDown = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, NULL, $oAdmin_Form_Field->id, 1);
					$onclickDown = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, NULL, $oAdmin_Form_Field->id, 1);

					$hrefUp = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, NULL, $oAdmin_Form_Field->id, 0);
					$onclickUp = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, NULL, $oAdmin_Form_Field->id, 0);

					if ($oAdmin_Form_Field->id == $this->_sortingFieldId)
					{
						if ($this->_sortingDirection == 0)
						{
							?><img src="/admin/images/arrow_up.gif" alt="&uarr" /> <?php
							?><a href="<?php echo $hrefDown?>" onclick="<?php echo $onclickDown?>"><img src="/admin/images/arrow_down_gray.gif" alt="&darr" /></a><?php
						}
						else
						{
							?><a href="<?php echo $hrefUp?>" onclick="<?php echo $onclickUp?>"><img src="/admin/images/arrow_up_gray.gif" alt="&uarr" /></a> <?php
							?><img src="/admin/images/arrow_down.gif" alt="&darr" /><?php
						}
					}
					else
					{
						?><a href="<?php echo $hrefUp?>" onclick="<?php echo $onclickUp?>"><img src="/admin/images/arrow_up_gray.gif" alt="&uarr" /></a> <?php
						?><a href="<?php echo $hrefDown?>" onclick="<?php echo $onclickDown?>"><img src="/admin/images/arrow_down_gray.gif" alt="&darr" /></a><?php
					}
				}
				?></nobr><?php
			?></td><?php
		}

		$oUser = Core_Entity::factory('User')->getCurrent();

		// Доступные действия для пользователя
		$aAllowed_Admin_Form_Actions = $this->_Admin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);

		if ($this->_Admin_Form->show_operations && $this->_showOperations
		|| $allow_filter && $this->_showFilter)
		{
				// min width action column
				$width = 10;

				foreach ($aAllowed_Admin_Form_Actions as $o_Admin_Form_Action)
				{
					$o_Admin_Form_Action->single && $width += 15;
				}

			?><td width="<?php echo $width?>">&nbsp;</td><?php
		}
		?></tr>
		<tr class="admin_table_filter"><?php
		// Чекбокс "Выбрать все" показываем только при наличии действий
		if ($this->_Admin_Form->show_operations && $this->_showOperations)
		{
			?><td align="center" width="25"><input type="checkbox" name="admin_forms_all_check" id="id_admin_forms_all_check" onclick="$('#<?php echo $windowId?>').highlightAllRows(this.checked)" /></td><?php
		}

		// Filter
		foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
		{
			// Перекрытие параметров для данного поля
			foreach ($this->_datasets as $datasetKey => $oTmpAdmin_Form_Dataset)
			{
				$oAdmin_Form_Field_Changed = $this->_changeField($oTmpAdmin_Form_Dataset, $oAdmin_Form_Field);
			}

			$width = htmlspecialchars($oAdmin_Form_Field->width);
			$class = htmlspecialchars($oAdmin_Form_Field->class);

			// Подсвечивать
			$oAdmin_Form_Field->allow_sorting
				&& $oAdmin_Form_Field->id == $this->_sortingAdmin_Form_Field->id
				&& $class .= ' highlight';

			?><td class="<?php echo trim($class)?>" <?php echo !empty($width) ? "width=\"{$width}\"" : ''?>><?php
			if ($oAdmin_Form_Field->allow_filter)
			{
				$value = trim(Core_Array::get($this->request, "admin_form_filter_{$oAdmin_Form_Field->id}"));

				// Функция обратного вызова для фильтра
				if (isset($this->_filters[$oAdmin_Form_Field->name]))
				{
					switch ($oAdmin_Form_Field->type)
					{
						case 1: // Строка
						case 2: // Поле ввода
						case 4: // Ссылка
						case 10: // Функция обратного вызова
						case 3: // Checkbox.
						case 8: // Выпадающий список
							echo call_user_func($this->_filters[$oAdmin_Form_Field->name], $value, $oAdmin_Form_Field);
						break;

						case 5: // Дата-время.
						case 6: // Дата.
							$date_from = Core_Array::get($this->request, "admin_form_filter_from_{$oAdmin_Form_Field->id}", NULL);
							$date_to = Core_Array::get($this->request, "admin_form_filter_to_{$oAdmin_Form_Field->id}", NULL);

							echo call_user_func($this->_filters[$oAdmin_Form_Field->name], $date_from, $date_to, $oAdmin_Form_Field);
						break;
					}
				}
				else
				{
					$style = /*!empty($width)
						? "width: {$width};"
						: */"width: 97%;";

					switch ($oAdmin_Form_Field->type)
					{
						case 1: // Строка
						case 2: // Поле ввода
						case 4: // Ссылка
						case 10: // Функция обратного вызова
							$value = htmlspecialchars($value);
							?><input type="text" name="admin_form_filter_<?php echo $oAdmin_Form_Field->id?>" id="id_admin_form_filter_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo $value?>" style="<?php echo $style?>" class="form-control input-sm" /><?php
						break;

						case 3: // Checkbox.
							?><select name="admin_form_filter_<?php echo $oAdmin_Form_Field->id?>" id="id_admin_form_filter_<?php echo $oAdmin_Form_Field->id?>" class="form-control">
								<option value="0" <?php echo $value == 0 ? "selected" : ''?>><?php echo htmlspecialchars(Core::_('Admin_Form.filter_selected_all'))?></option>
								<option value="1" <?php echo $value == 1 ? "selected" : ''?>><?php echo htmlspecialchars(Core::_('Admin_Form.filter_selected'))?></option>
								<option value="2" <?php echo $value == 2 ? "selected" : ''?>><?php echo htmlspecialchars(Core::_('Admin_Form.filter_not_selected'))?></option>
							</select><?php
						break;

						case 5: // Дата-время.
							$date_from = Core_Array::get($this->request, "admin_form_filter_from_{$oAdmin_Form_Field->id}", NULL);
							$date_from = htmlspecialchars($date_from);

							$date_to = Core_Array::get($this->request, "admin_form_filter_to_{$oAdmin_Form_Field->id}", NULL);
							$date_to = htmlspecialchars($date_to);

							?><input name="admin_form_filter_from_<?php echo $oAdmin_Form_Field->id?>" id="id_admin_form_filter_from_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo $date_from?>" size="17" class="form-control input-sm calendar_field" type="text" />
							<div><input name="admin_form_filter_to_<?php echo $oAdmin_Form_Field->id?>" id="id_admin_form_filter_to_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo $date_to?>" size="17" class="form-control input-sm calendar_field" type="text" /></div>
							<script type="text/javascript">
							(function($) {
								$("#id_admin_form_filter_from_<?php echo $oAdmin_Form_Field->id?>").datetimepicker({showOtherMonths: true, selectOtherMonths: true, changeMonth: true, changeYear: true, timeFormat: 'hh:mm:ss'});
								$("#id_admin_form_filter_to_<?php echo $oAdmin_Form_Field->id?>").datetimepicker({showOtherMonths: true, selectOtherMonths: true, changeMonth: true, changeYear: true, timeFormat: 'hh:mm:ss'});
							})(jQuery);
							</script><?php
						break;

						case 6: // Дата.
							$date_from = Core_Array::get($this->request, "admin_form_filter_from_{$oAdmin_Form_Field->id}", NULL);
							$date_from = htmlspecialchars($date_from);

							$date_to = Core_Array::get($this->request, "admin_form_filter_to_{$oAdmin_Form_Field->id}", NULL);
							$date_to = htmlspecialchars($date_to);

							?><input type="text" name="admin_form_filter_from_<?php echo $oAdmin_Form_Field->id?>" id="id_admin_form_filter_from_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo $date_from?>" size="8" class="form-control input-sm calendar_field" />
							<div><input type="text" name="admin_form_filter_to_<?php echo $oAdmin_Form_Field->id?>" id="id_admin_form_filter_to_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo $date_to?>" size="8" class="form-control input-sm calendar_field" /></div>
							<script type="text/javascript">
							(function($) {
								$("#id_admin_form_filter_from_<?php echo $oAdmin_Form_Field->id?>").datetimepicker({showOtherMonths: true, selectOtherMonths: true, changeMonth: true, changeYear: true});
								$("#id_admin_form_filter_to_<?php echo $oAdmin_Form_Field->id?>").datetimepicker({showOtherMonths: true, selectOtherMonths: true, changeMonth: true, changeYear: true});
							})(jQuery);
							</script>
							<?php
						break;

						case 8: // Выпадающий список.

						?><select name="admin_form_filter_<?php echo $oAdmin_Form_Field->id?>" id="id_admin_form_filter_<?php echo $oAdmin_Form_Field->id?>" style="<?php echo $style?>">
						<option value="HOST_CMS_ALL" <?php echo $value == 'HOST_CMS_ALL' ? "selected" : ''?>><?php echo htmlspecialchars(Core::_('Admin_Form.filter_selected_all'))?></option>
						<?php
						$str_array = explode("\n", $oAdmin_Form_Field_Changed->list);
						$value_array = array();

						foreach ($str_array as $str_value)
						{
							// Каждую строку разделяем по равно
							$str_explode = explode('=', $str_value);

							if ($str_explode[0] != 0 && count($str_explode) > 1)
							{
								// сохраняем в массив варинаты значений и ссылки для них
								$value_array[intval(trim($str_explode[0]))] = trim($str_explode[1]);

								?><option value="<?php echo htmlspecialchars($str_explode[0])?>" <?php echo $value == $str_explode[0] ? "selected" : ''?>><?php echo htmlspecialchars(trim($str_explode[1]))?></option><?php
							}
						}
						?>
						</select>
						<?php
						break;

						default:
						?><div style="color: #CEC3A3; text-align: center">—</div><?php
						break;
					}
				}
			}
			else
			{
				// Фильтр не разрешен.
				?><div style="color: #CEC3A3; text-align: center">—</div><?php
			}
			?></td><?php
		}

		// Фильтр показываем если есть события или хотя бы у одного есть фильтр
		if ($this->_Admin_Form->show_operations && $this->_showOperations
		|| $allow_filter && $this->_showFilter)
		{
			$onclick = $this->getAdminLoadAjax($this->getPath());
			?><td><?php
				?><input title="<?php echo Core::_('Admin_Form.button_to_filter')?>" type="image" src="/admin/images/filter.gif" id="admin_forms_apply_button" type="button" value="<?php echo Core::_('Admin_Form.button_to_filter')?>" onclick="<?php echo $onclick?>" /> <input title="<?php echo Core::_('Admin_Form.button_to_clear')?>" type="image" src="/admin/images/clear.png" type="button" value="<?php echo Core::_('Admin_Form.button_to_clear')?>" onclick="$.clearFilter('<?php echo $windowId?>')" /><?php
			?></td><?php
		}
		?></tr>
		</thead><?php

		$aEntities = array();

		// Устанавливаем ограничения на источники
		$this->setDatasetConditions();

		foreach ($this->_datasets as $datasetKey => $oAdmin_Form_Dataset)
		{
			// Добавляем внешнюю замену по датасету
			$this->AddExternalReplace('{dataset_key}', $datasetKey);

			$aDataFromDataset = $oAdmin_Form_Dataset->load();

			if (!empty($aDataFromDataset))
			{
				foreach ($aDataFromDataset as $oEntity)
				{
					try
					{
						$key_field_name = $this->_Admin_Form->key_field;
						$key_field_value = $oEntity->$key_field_name;

						// Экранируем ' в имени индексного поля, т.к. дальше это значение пойдет в JS
						$key_field_value = str_replace(
							array("'", '%'),
							array("\'", '\\%'),
							$key_field_value
						);
					}
					catch (Exception $e)
					{
						Core_Message::show('Caught exception: ' . $e->getMessage() . "\n", 'error');
						$key_field_value = NULL;
					}

					?><tr id="row_<?php echo htmlspecialchars($datasetKey)?>_<?php echo htmlspecialchars($key_field_value)?>">
						<?php
						// Чекбокс "Для элемента" показываем только при наличии действий
						if ($this->_Admin_Form->show_operations && $this->_showOperations)
						{
							?><td align="center" width="25">
								<input type="checkbox" id="check_<?php echo htmlspecialchars($datasetKey)?>_<?php echo htmlspecialchars($key_field_value)?>" onclick="$('#<?php echo $windowId?>').setTopCheckbox(); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $this->jQueryEscape(Core_Str::escapeJavascriptVariable($datasetKey))?>_<?php echo $this->jQueryEscape(Core_Str::escapeJavascriptVariable($key_field_value))?>').toggleHighlight()" /><?php
							?></td><?php
						}

						foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
						{
							// Перекрытие параметров для данного поля
							$oAdmin_Form_Field_Changed = $this->_changeField($oAdmin_Form_Dataset, $oAdmin_Form_Field);


							$width = htmlspecialchars(trim($oAdmin_Form_Field_Changed->width));
							$class = htmlspecialchars($oAdmin_Form_Field_Changed->class);

							$oAdmin_Form_Field->allow_sorting
								&& $oAdmin_Form_Field->id == $this->_sortingAdmin_Form_Field->id
								&& $class .= ' highlight';

							?><td class="<?php echo trim($class)?>" <?php echo !empty($width) ? "width=\"{$width}\"" : ''?>><?php

							$fieldName = $oAdmin_Form_Field_Changed->name;

							try
							{
								if ($oAdmin_Form_Field_Changed->type != 10)
								{
									if (isset($oEntity->$fieldName))
									{
										// Выведим значение свойства
										$value = htmlspecialchars($oEntity->$fieldName);
									}
									elseif (method_exists($oEntity, $fieldName))
									{
										// Выполним функцию обратного вызова
										$value = htmlspecialchars($oEntity->$fieldName());
									}
									else
									{
										$value = NULL;
									}
								}

								$element_name = "apply_check_{$datasetKey}_{$key_field_value}_fv_{$oAdmin_Form_Field_Changed->id}";

								// Функция, выполняемая перед отображением поля
								$methodName = 'prefix' . ucfirst($fieldName);
								if (method_exists($oEntity, $methodName))
								{
									// Выполним функцию обратного вызова
									echo $oEntity->$methodName();
								}

								// Отображения элементов полей, в зависимости от их типа.
								switch ($oAdmin_Form_Field_Changed->type)
								{
									case 1: // Текст.
										//trim($value) == '' && $value = '&nbsp;';

										$class = 'dl';

										$oAdmin_Form_Field_Changed->editable && $class .= ' editable';

										?><div id="<?php echo $element_name?>"><div <?php echo !empty($width_value) ? 'style="width: ' . $width_value . '"' : '' ?> class="<?php echo $class?>"><?php
										echo $this->applyFormat(nl2br($value), $oAdmin_Form_Field_Changed->format);
										?></div></div><?php
									break;
									case 2: // Поле ввода.
										?><input type="text" name="<?php echo $element_name?>" id="<?php echo $element_name?>" value="<?php echo $value?>" onchange="$.setCheckbox('<?php echo $windowId?>', 'check_<?php echo $datasetKey?>_<?php echo $key_field_value?>'); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $datasetKey?>_<?php echo $key_field_value?>').toggleHighlight()" onkeydown="$.setCheckbox('<?php echo $windowId?>', 'check_<?php echo $datasetKey?>_<?php echo $key_field_value?>'); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $datasetKey?>_<?php echo $key_field_value?>').toggleHighlight()" class="form-control input-xs" /><?php
									break;
									case 3: // Checkbox.
										?><input type="checkbox" name="<?php echo $element_name?>" id="<?php echo $element_name?>" <?php echo intval($value) ? 'checked="checked"' : ''?> onclick="$.setCheckbox('<?php echo $windowId?>', 'check_<?php echo $datasetKey?>_<?php echo $key_field_value?>'); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $datasetKey?>_<?php echo $key_field_value?>').toggleHighlight();" value="1" /><?php
									break;
									case 4: // Ссылка.
										$link = $oAdmin_Form_Field_Changed->link;
										$onclick = $oAdmin_Form_Field_Changed->onclick;

										//$link_text = trim($value);
										$link_text = $this->applyFormat($value, $oAdmin_Form_Field_Changed->format);

										$link = $this->doReplaces($aAdmin_Form_Fields, $oEntity, $link);
										$onclick = $this->doReplaces($aAdmin_Form_Fields, $oEntity, $onclick, 'onclick');

										// Нельзя применять, т.к. 0 является пустотой if (empty($link_text))
										if (mb_strlen($link_text) != 0)
										{
											?><a href="<?php echo $link?>" <?php echo (!empty($onclick)) ? "onclick=\"{$onclick}\"" : ''?>><?php echo $link_text?></a><?php
										}
										else
										{
											?>&nbsp;<?php
										}
									break;
									case 5: // Дата-время.
										$value = $value == '0000-00-00 00:00:00' || $value == ''
											? ''
											: Core_Date::sql2datetime($value);
										echo $this->applyFormat($value, $oAdmin_Form_Field_Changed->format);

									break;
									case 6: // Дата.
										$value = $value == '0000-00-00 00:00:00' || $value == ''
											? ''
											: Core_Date::sql2date($value);
										echo $this->applyFormat($value, $oAdmin_Form_Field_Changed->format);

									break;
									case 7: // Картинка-ссылка.
										$link = $oAdmin_Form_Field_Changed->link;
										$onclick = $oAdmin_Form_Field_Changed->onclick;

										$link = $this->doReplaces($aAdmin_Form_Fields, $oEntity, $link);
										$onclick = $this->doReplaces($aAdmin_Form_Fields, $oEntity, $onclick, 'onclick');

										// ALT-ы к картинкам
										$alt_array = array();

										// TITLE-ы к картинкам
										$title_array = array();

										$value_trim = trim($value);

										/*
										Разделяем варианты значений на строки, т.к. они приходят к нам в виде:
										0 = /images/off.gif
										1 = /images/on.gif
										*/
										$str_array = explode("\n", $oAdmin_Form_Field_Changed->image);
										$value_array = array();

										foreach ($str_array as $str_value)
										{
											// Каждую строку разделяем по равно
											$str_explode = explode('=', $str_value/*, 2*/);

											if (count($str_explode) > 1)
											{
												// сохраняем в массив варинаты значений и ссылки для них
												$value_array[trim($str_explode[0])] = trim($str_explode[1]);

												// Если указано альтернативное значение для картинки - добавим его в alt и title
												if (isset($str_explode[2]) && $value_trim == trim($str_explode[0]))
												{
													$alt_array[$value_trim] = trim($str_explode[2]);
													$title_array[$value_trim] = trim($str_explode[2]);
												}
											}
										}

										// Получаем заголовок столбца на случай, если для IMG не было указано alt-а или title
										$Admin_Word_Value = $oAdmin_Form_Field->Admin_Word->getWordByLanguage(
											$this->_Admin_Language->id
										);

										$fieldName = $Admin_Word_Value
											? htmlspecialchars($Admin_Word_Value->name)
											: "—";

										// Warning: 01-06-11 Создать отдельное поле в таблице в БД и в нем хранить alt-ы
										if (isset($field_value['admin_forms_field_alt']))
										{
											$str_array_alt = explode("\n", trim($field_value['admin_forms_field_alt']));

											foreach ($str_array_alt as $str_value)
											{
												// Каждую строку разделяем по равно
												$str_explode_alt = explode('=', $str_value, 2);

												// сохраняем в массив варинаты значений и ссылки для них
												if (count($str_explode_alt) > 1)
												{
													$alt_array[trim($str_explode_alt[0])] = trim($str_explode_alt[1]);
												}
											}
										}
										elseif (!isset($alt_array[$value]))
										{
											$alt_array[$value] = $fieldName;
										}

										// ToDo: Создать отдельное поле в таблице в БД и в нем хранить title-ы
										if (isset($field_value['admin_forms_field_title']))
										{
											$str_array_title = explode("\n", $field_value['admin_forms_field_title']);

											foreach ($str_array_title as $str_value)
											{
												// Каждую строку разделяем по равно
												$str_explode_title = explode('=', $str_value, 2);

												if (count($str_explode_title) > 1)
												{
													// сохраняем в массив варинаты значений и ссылки для них
													$title_array[trim($str_explode_title[0])] = trim($str_explode_title[1]);
												}
											}
										}
										elseif (!isset($title_array[$value]))
										{
											$title_array[$value] = $fieldName;
										}

										if (isset($value_array[$value]))
										{
											$src = $value_array[$value];
										}
										elseif (isset($value_array['']))
										{
											$src = $value_array[''];
										}
										else
										{
											$src = NULL;
										}

										// Отображаем картинку ссылкой
										if (!empty($link) && !is_null($src))
										{
											?><a href="<?php echo $link?>" onclick="$('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $datasetKey?>_<?php echo $key_field_value?>').toggleHighlight();<?php echo $onclick?>"><img src="<?php echo htmlspecialchars($src)?>" alt="<?php echo Core_Type_Conversion::toStr($alt_array[$value])?>" title="<?php echo Core_Type_Conversion::toStr($title_array[$value])?>"></a><?php
										}
										// Отображаем картинку без ссылки
										elseif (!is_null($src))
										{
											?><img src="<?php echo htmlspecialchars($src)?>" alt="<?php echo Core_Type_Conversion::toStr($alt_array[$value])?>" title="<?php echo Core_Type_Conversion::toStr($title_array[$value])?>"><?php
										}
										/*elseif (!empty($link) && !isset($value_array[$value]))
										{
											// Картинки для такого значения не найдено, но есть ссылка
											?><a href="<?php echo $link?>" onclick="$('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $datasetKey?>_<?php echo $key_field_value?>').toggleHighlight();<?php echo $onclick?> ">—</a><?php
										}*/
										else
										{
											// Картинки для такого значения не найдено
											?>—<?php
										}
									break;
									case 8: // Выпадающий список
										/*
										Разделяем варианты значений на строки, т.к. они приходят к нам в виде:
										0 = /images/off.gif
										1 = /images/on.gif
										*/

										$str_array = explode("\n", $oAdmin_Form_Field_Changed->list);

										$value_array = array();

										?><select name="<?php echo $element_name?>" id="<?php echo $element_name?>" onchange="$.setCheckbox('<?php echo $windowId?>', 'check_<?php echo $datasetKey?>_<?php echo $key_field_value?>');"><?php

										foreach ($str_array as $str_value)
										{
											// Каждую строку разделяем по равно
											$str_explode = explode('=', $str_value, 2);

											if (count($str_explode) > 1)
											{
												// сохраняем в массив варинаты значений и ссылки для них
												$value_array[intval(trim($str_explode[0]))] = trim($str_explode[1]);

												$selected = $value == $str_explode[0]
													? ' selected = "" '
													: '';

												?><option value="<?php echo htmlspecialchars($str_explode[0])?>" <?php echo $selected?>><?php echo htmlspecialchars(trim($str_explode[1]))?></option><?php
											}
										}
										?>
										</select>
										<?php

									break;
									case 9: // Текст "AS IS"
										if (mb_strlen($value) != 0)
										{
											echo html_entity_decode($value, ENT_COMPAT, 'UTF-8');
										}
										else
										{
											?>&nbsp;<?php
										}

									break;
									case 10: // Вычисляемое поле с помощью функции обратного вызова,
									// имя функции обратного вызова f($field_value, $value)
									// передается функции с именем, содержащимся в $field_value['callback_function']
										if (method_exists($oEntity, $fieldName)
											|| method_exists($oEntity, 'isCallable') && $oEntity->isCallable($fieldName)
										)
										{
											// Выполним функцию обратного вызова
											echo $oEntity->$fieldName($oAdmin_Form_Field, $this);
										}
										elseif (property_exists($oEntity, $fieldName))
										{
											// Выведим значение свойства
											echo $oEntity->$fieldName;
										}
									break;
									default: // Тип не определен.
										?>&nbsp;<?php
									break;
								}

								// Функция, выполняемая после отображением поля
								$methodName = 'suffix' . ucfirst($fieldName);
								if (method_exists($oEntity, $methodName))
								{
									// Выполним функцию обратного вызова
									echo $oEntity->$methodName();
								}
							}
							catch (Exception $e)
							{
								Core_Message::show('Caught exception: ' . $e->getMessage() . "\n", 'error');
							}
							?></td><?php
						}

						// Действия для строки в правом столбце
						if ($this->_Admin_Form->show_operations
						&& $this->_showOperations
						|| $allow_filter && $this->_showFilter)
						{
							// Определяем ширину столбца для действий.
							$width = isset($this->form_params['actions_width'])
								? strval($this->form_params['actions_width'])
								: '10px'; // Минимальная ширина

							// <nobr> из-за IE
							?><td class="admin_forms_action_td" style="width: <?php echo $width?>"><nobr><?php

							foreach ($aAllowed_Admin_Form_Actions as $o_Admin_Form_Action)
							{
								// Отображаем действие, только если разрешено.
								if (!$o_Admin_Form_Action->single)
								{
									continue;
								}

								// Проверяем, привязано ли действие к определенному dataset'у.
								if ($o_Admin_Form_Action->dataset != -1
								&& $o_Admin_Form_Action->dataset != $datasetKey)
								{
									continue;
								}

								$Admin_Word_Value = $o_Admin_Form_Action->Admin_Word->getWordByLanguage($this->_Admin_Language->id);

								if ($Admin_Word_Value && strlen($Admin_Word_Value->name) > 0)
								{
									$name = $Admin_Word_Value->name;
								}
								else
								{
									$name = '';
								}

								$href = $this->getAdminActionLoadHref($this->getPath(), $o_Admin_Form_Action->name, NULL, $datasetKey, $key_field_value);

								$onclick = $this->getAdminActionLoadAjax($this->getPath(), $o_Admin_Form_Action->name, NULL, $datasetKey, $key_field_value);

								// Добавляем установку метки для чекбокса и строки + добавлем уведомление, если необходимо
								if ($o_Admin_Form_Action->confirm)
								{
									$onclick = "res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars($name))."'); if (!res) { $('#{$windowId} #row_{$datasetKey}_{$key_field_value}').toggleHighlight(); } else {{$onclick}} return res;";
								}

								?><a href="<?php echo $href?>" onclick="<?php echo $onclick?>"><img src="<?php echo htmlspecialchars($o_Admin_Form_Action->picture)?>" alt="<?php echo $name?>" title="<?php echo $name?>"></a> <?php
							}
							?></nobr></td><?php
						}

						?></tr><?php
				}
			}
		}

		?></table><?php

		return $this;
	}

	/**
	 * Show action panel in administration center
	 * @return self
	 */
	public function bottomActions()
	{
		// Строка с действиями
		if ($this->_showBottomActions)
		{
			$windowId = $this->getWindowId();

			// Текущий пользователь
			$oUser = Core_Entity::factory('User')->getCurrent();

			// Доступные действия для пользователя
			$aAllowed_Admin_Form_Actions = $this->_Admin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);
		?>
		<table cellpadding="5" cellspacing="0" border="1" width="100%" style="margin-top: 8px;" class="light_table">
		<tr>
		<?php
		// Чекбокс "Выбрать все" показываем только при наличии действий
		if ($this->_Admin_Form->show_operations && $this->_showOperations)
		{
			?><td align="center" width="25">
				<input type="checkbox" name="admin_forms_all_check2" id="id_admin_forms_all_check2" onclick="$('#<?php echo $windowId?>').highlightAllRows(this.checked)" />
			</td><?php
		}

		?><td>
			<div class="admin_form_action"><?php

				if ($this->_Admin_Form->show_group_operations)
				{
					// Групповые операции
					if (!empty($aAllowed_Admin_Form_Actions))
					{
						foreach ($aAllowed_Admin_Form_Actions as $o_Admin_Form_Action)
						{
							if ($o_Admin_Form_Action->group)
							{
								$Admin_Word_Value = $o_Admin_Form_Action->Admin_Word->getWordByLanguage($this->_Admin_Language->id);

								if ($Admin_Word_Value && strlen($Admin_Word_Value->name) > 0)
								{
									$text = $Admin_Word_Value->name;
								}
								else
								{
									$text = '';
								}

								$href = $this->getAdminLoadHref($this->getPath(), $o_Admin_Form_Action->name);
								$onclick = $this->getAdminLoadAjax($this->getPath(), $o_Admin_Form_Action->name);

								// Нужно подтверждение для действия
								if ($o_Admin_Form_Action->confirm)
								{
									$onclick = "res = confirm('".Core::_('Admin_Form.confirm_dialog', htmlspecialchars($text))."'); if (res) { $onclick } else {return false}";

									$link_class = 'admin_form_action_alert_link';
								}
								else
								{
									$link_class = 'admin_form_action_link';
								}

								// ниже по тексту alt-ы и title-ы не выводятся, т.к. они дублируются текстовыми
								// надписями и при отключении картинок текст дублируется
								/* alt="<?php echo htmlspecialchars($text)?>"*/
								?><nobr><a href="<?php echo $href?>" onclick="<?php echo $onclick?>"><img src="<?php echo htmlspecialchars($o_Admin_Form_Action->picture)?>" title="<?php echo htmlspecialchars($text)?>"></a> <a href="<?php echo $href?>" onclick="<?php echo $onclick?>" class="<?php echo $link_class?>"><?php echo htmlspecialchars($text)?></a>
								</nobr><?php
							}
						}
					}
				}
				?>
			</div>
			</td>
			<td width="110" align="center">
				<div class="admin_form_action"></div>
			</td>
			<td width="60" align="center"><?php
				$this->_pageSelector()
			?></td>
		</tr>
		</table>
		<?php
		}

		return $this;
	}

	/**
	 * Show items count selector
	 */
	protected function _pageSelector()
	{
		$sCurrentValue = $this->_limit;
		$windowId = Core_Str::escapeJavascriptVariable($this->getWindowId());
		$additionalParams = Core_Str::escapeJavascriptVariable(
			str_replace(array('"'), array('&quot;'), $this->_additionalParams)
		);
 		$path = Core_Str::escapeJavascriptVariable($this->getPath());

		$oCore_Html_Entity_Select = Core::factory('Core_Html_Entity_Select')
			//->name('admin_forms_on_page')
			//->id('id_on_page')
			->onchange("$.adminLoad({path: '{$path}', additionalParams: '{$additionalParams}', limit: this.options[this.selectedIndex].value, windowId : '{$windowId}'}); return false")
			->options($this->_onPage)
			->value($sCurrentValue)
			->execute();
	}

	/**
	 * Показ строки ссылок
	 * @return self
	 */
	protected function _pageNavigation()
	{
		$total_count = $this->getTotalCount();
		$total_page = $total_count / $this->_limit;

		// Округляем в большую сторону
		if ($total_count % $this->_limit != 0)
		{
			$total_page = intval($total_page) + 1;
		}

		$this->_current > $total_page && $this->_current = $total_page;

		$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
			->style('float: left; text-align: center; margin-top: 10px');

		// Формируем скрытые ссылки навигации для перехода по Ctrl + стрелка
		if ($this->_current < $total_page)
		{
			// Ссылка на следующую страницу
			$page = $this->_current + 1 ? $this->_current + 1 : 1;
			$oCore_Html_Entity_Div->add(
				Core::factory('Core_Html_Entity_A')
					->onclick($this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $page))
					->id('id_next')
			);
		}

		if ($this->_current > 1)
		{
			// Ссылка на предыдущую страницу
			$page = $this->_current - 1 ? $this->_current - 1 : 1;
			$oCore_Html_Entity_Div->add(
				Core::factory('Core_Html_Entity_A')
					->onclick($this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $page))
					->id('id_prev')
			);
		}

		// Отображаем строку ссылок, если общее число страниц больше 1.
		if ($total_page > 1)
		{
			// Определяем номер ссылки, с которой начинается строка ссылок.
			$link_num_begin = ($this->_current - $this->_pageNavigationDelta < 1)
				? 1
				: $this->_current - $this->_pageNavigationDelta;

			// Определяем номер ссылки, которой заканчивается строка ссылок.
			$link_num_end = $this->_current + $this->_pageNavigationDelta;
			$link_num_end > $total_page && $link_num_end = $total_page;

			// Определяем число ссылок выводимых на страницу.
			$count_link = $link_num_end - $link_num_begin + 1;

			if ($this->_current == 1)
			{
				$oCore_Html_Entity_Div->add(
					Core::factory('Core_Html_Entity_Span')
						->class('current')
						->value($link_num_begin)
				);
			}
			else
			{
				$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, 1);
				$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, 1);

				$oCore_Html_Entity_Div->add(
					Core::factory('Core_Html_Entity_A')
						->href($href)
						->onclick($onclick)
						->class('page_link')
						->value(1)
				);

				// Выведем … со ссылкой на 2-ю страницу, если показываем с 3-й
				if ($link_num_begin > 1)
				{
					$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, 2);
					$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, 2);

					$oCore_Html_Entity_Div->add(
						Core::factory('Core_Html_Entity_A')
							->href($href)
							->onclick($onclick)
							->class('page_link')
							->value('…')
					);
				}
			}

			// Страница не является первой и не является последней.
			for ($i = 1; $i < $count_link - 1; $i++)
			{
				$link_number = $link_num_begin + $i;

				if ($link_number == $this->_current)
				{
					// Страница является текущей
					$oCore_Html_Entity_Div->add(
						Core::factory('Core_Html_Entity_Span')
							->class('current')
							->value($link_number)
					);
				}
				else
				{
					$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $link_number);
					$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $link_number);
					$oCore_Html_Entity_Div->add(
						Core::factory('Core_Html_Entity_A')
							->href($href)
							->onclick($onclick)
							->class('page_link')
							->value($link_number)
					);
				}
			}

			// Если последняя страница является текущей
			if ($this->_current == $total_page)
			{
				$oCore_Html_Entity_Div->add(
					Core::factory('Core_Html_Entity_Span')
							->class('current')
							->value($total_page)
				);
			}
			else
			{
				// Выведем … со ссылкой на предпоследнюю страницу
				if ($link_num_end < $total_page)
				{
					$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $total_page - 1);
					$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $total_page - 1);

					$oCore_Html_Entity_Div->add(
						Core::factory('Core_Html_Entity_A')
							->href($href)
							->onclick($onclick)
							->class('page_link')
							->value('…')
					);
				}

				$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $total_page);
				$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $total_page);

				// Последняя страница не является текущей
				$oCore_Html_Entity_Div->add(
					Core::factory('Core_Html_Entity_A')
						->href($href)
						->onclick($onclick)
						->class('page_link')
						->value($total_page)
				);
			}

			$oCore_Html_Entity_Div->execute();
			Core::factory('Core_Html_Entity_Div')
				->style('clear: both')
				->execute();
		}

		return $this;
	}
}