<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2017 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Controller extends Admin_Form_Controller
{
	/**
	 * Show form content in administration center
	 * @return self
	 * @hostcms-event Admin_Form_Controller.onBeforeShowActions
	 * @hostcms-event Admin_Form_Controller.onBeforeShowContent
	 * @hostcms-event Admin_Form_Controller.onAfterShowContent
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

		Core_Event::notify('Admin_Form_Controller.onBeforeShowContent', $this);

		// div class="table-scrollable"
		// table-bordered
		?>
		<div>
			<table class="admin-table table table-hover table-striped">
				<thead>
				<tr>
					<?php
					// Ячейку над групповыми чекбоксами показываем только при наличии действий
					if ($this->_Admin_Form->show_operations && $this->_showOperations)
					{
						?><th width="40">&nbsp;</th><?php
					}

					foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
					{
						// There is at least one filter
						$oAdmin_Form_Field->allow_filter && $allow_filter = TRUE;

						// Перекрытие параметров для данного поля
						$oAdmin_Form_Field_Changed = $oAdmin_Form_Field;
						foreach ($this->_datasets as $datasetKey => $oAdmin_Form_Dataset)
						{
							$oAdmin_Form_Field_Changed = $this->_changeField($oAdmin_Form_Dataset, $oAdmin_Form_Field);
						}

						$width = htmlspecialchars($oAdmin_Form_Field_Changed->width);
						$class = htmlspecialchars($oAdmin_Form_Field_Changed->class);

						$Admin_Word_Value = $oAdmin_Form_Field->Admin_Word->getWordByLanguage($this->_Admin_Language->id);

						$fieldName = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
							? htmlspecialchars($Admin_Word_Value->name)
							: '&mdash;';

						// Change to Font Awesome
						if (strlen($oAdmin_Form_Field_Changed->ico))
						{
							$fieldName = '<i class="' . htmlspecialchars($oAdmin_Form_Field_Changed->ico) . '" title="' . $fieldName . '"></i>';
						}

						$oAdmin_Form_Field_Changed->allow_sorting
							&& is_object($this->_sortingAdmin_Form_Field)
							&& $oAdmin_Form_Field->id == $this->_sortingAdmin_Form_Field->id
							&& $class .= ' highlight';

						$sSortingClass = $sSortingOnClick = '';

						if ($oAdmin_Form_Field_Changed->allow_sorting)
						{
							//$hrefDown = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, NULL, $oAdmin_Form_Field->id, 1);
							$onclickDown = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, NULL, $oAdmin_Form_Field->id, 1);

							//$hrefUp = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, NULL, $oAdmin_Form_Field->id, 0);
							$onclickUp = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, NULL, $oAdmin_Form_Field->id, 0);

							if ($oAdmin_Form_Field->id == $this->_sortingFieldId)
							{
								$class .= $this->_sortingDirection == 1
									? ' sorting_desc'
									: ' sorting_asc';

								$sSortingOnClick = $this->_sortingDirection == 1
									? $onclickUp
									: $onclickDown;
							}
							else
							{
								$class .= ' sorting';
								$sSortingOnClick = $onclickUp;
							}
						}
						?><th class="<?php echo trim($class)?>" <?php echo !empty($width) ? "width=\"{$width}\"" : ''?> onclick="<?php echo $sSortingOnClick?>"><?php echo $fieldName?></th><?php
					}

					$oUser = Core_Entity::factory('User')->getCurrent();

					// Доступные действия для пользователя
					$aAllowed_Admin_Form_Actions = $this->_Admin_Form->Admin_Form_Actions->getAllowedActionsForUser($oUser);

					if ($this->_Admin_Form->show_operations && $this->_showOperations
					|| $allow_filter && $this->_showFilter)
					{
							$iSingleActionCount = 0;

							foreach ($aAllowed_Admin_Form_Actions as $o_Admin_Form_Action)
							{
								$o_Admin_Form_Action->single && $iSingleActionCount++;
							}

						?><th class="filter-action-<?php echo $iSingleActionCount?>">&nbsp;</th><?php
					}
					?>
				</tr><?php
			?><tr class="admin_table_filter"><?php
			// Чекбокс "Выбрать все" показываем только при наличии действий
			if ($this->_Admin_Form->show_operations && $this->_showOperations)
			{
				?><td align="center" width="40">
				<label><input type="checkbox" name="admin_forms_all_check" class="colored-black" id="id_admin_forms_all_check" onclick="$('#<?php echo $windowId?>').highlightAllRows(this.checked)" class="form-control"/><span class="text"></span></label></td><?php
			}

			// Filter
			foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
			{
				// Перекрытие параметров для данного поля
				foreach ($this->_datasets as $datasetKey => $oAdmin_Form_Dataset)
				{
					$oAdmin_Form_Field_Changed = $this->_changeField($oAdmin_Form_Dataset, $oAdmin_Form_Field);
				}

				$width = htmlspecialchars($oAdmin_Form_Field_Changed->width);
				$class = htmlspecialchars($oAdmin_Form_Field_Changed->class);

				// Подсвечивать
				$oAdmin_Form_Field_Changed->allow_sorting
					&& is_object($this->_sortingAdmin_Form_Field)
					&& $oAdmin_Form_Field->id == $this->_sortingAdmin_Form_Field->id
					&& $class .= ' highlight';

				?><td class="<?php echo trim($class)?>" <?php echo !empty($width) ? "width=\"{$width}\"" : ''?>><?php

				if ($oAdmin_Form_Field_Changed->allow_filter)
				{
					$value = trim(Core_Array::get($this->request, "admin_form_filter_{$oAdmin_Form_Field->id}"));

					// Функция обратного вызова для фильтра
					if (isset($this->_filters[$oAdmin_Form_Field_Changed->name]))
					{
						switch ($oAdmin_Form_Field_Changed->type)
						{
							case 1: // Строка
							case 2: // Поле ввода
							case 4: // Ссылка
							case 10: // Функция обратного вызова
							case 3: // Checkbox.
							case 8: // Выпадающий список
								echo call_user_func($this->_filters[$oAdmin_Form_Field_Changed->name], $value, $oAdmin_Form_Field);
							break;

							case 5: // Дата-время.
							case 6: // Дата.
								$date_from = Core_Array::get($this->request, "admin_form_filter_from_{$oAdmin_Form_Field->id}", NULL);
								$date_to = Core_Array::get($this->request, "admin_form_filter_to_{$oAdmin_Form_Field->id}", NULL);

								echo call_user_func($this->_filters[$oAdmin_Form_Field_Changed->name], $date_from, $date_to, $oAdmin_Form_Field);
							break;
						}
					}
					else
					{
						$style = /*!empty($width)
							? "width: {$width};"
							: */"width: 100%;";

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

								?><div class="input-group date">
									<input name="admin_form_filter_from_<?php echo $oAdmin_Form_Field->id?>" id="id_admin_form_filter_from_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo $date_from?>" class="form-control input-sm" type="text"/>
								</div>
								<div class="input-group date">
									<input name="admin_form_filter_to_<?php echo $oAdmin_Form_Field->id?>" id="id_admin_form_filter_to_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo $date_to?>" class="form-control input-sm" type="text"/>
								</div>
								<script type="text/javascript">
								(function($) {
									$('#id_admin_form_filter_from_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: 'ru', format: 'DD.MM.YYYY HH:mm:ss'});
									$('#id_admin_form_filter_to_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: 'ru', format: 'DD.MM.YYYY HH:mm:ss'});
								})(jQuery);
								</script><?php
							break;

							case 6: // Дата.
								$date_from = Core_Array::get($this->request, "admin_form_filter_from_{$oAdmin_Form_Field->id}", NULL);
								$date_from = htmlspecialchars($date_from);

								$date_to = Core_Array::get($this->request, "admin_form_filter_to_{$oAdmin_Form_Field->id}", NULL);
								$date_to = htmlspecialchars($date_to);

								?><div class="input-group date">
									<input type="text" name="admin_form_filter_from_<?php echo $oAdmin_Form_Field->id?>" id="id_admin_form_filter_from_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo $date_from?>" size="8" class="form-control input-sm" />
								</div>
								<div class="input-group date">
									<input type="text" name="admin_form_filter_to_<?php echo $oAdmin_Form_Field->id?>" id="id_admin_form_filter_to_<?php echo $oAdmin_Form_Field->id?>" value="<?php echo $date_to?>" size="8" class="form-control input-sm" />
								</div>
								<script type="text/javascript">
								(function($) {
									$('#id_admin_form_filter_from_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: 'ru', format: 'DD.MM.YYYY'});
									$('#id_admin_form_filter_to_<?php echo $oAdmin_Form_Field->id?>').datetimepicker({locale: 'ru', format: 'DD.MM.YYYY'});
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
							?><div style="color: #CEC3A3; text-align: center">&mdash;</div><?php
							break;
						}
					}
				}
				else
				{
					// Фильтр не разрешен.
					?><div style="color: #CEC3A3; text-align: center">&mdash;</div><?php
				}
				?></td><?php
			}

			// Фильтр показываем если есть события или хотя бы у одного есть фильтр
			if ($this->_Admin_Form->show_operations && $this->_showOperations
				|| $allow_filter && $this->_showFilter)
			{
				$onclick = $this->getAdminLoadAjax($this->getPath());

				?><td class="apply-button"><?php
					?>
					<div class="btn-group">
						<a class="btn btn-xs btn-palegreen" id="admin_forms_apply_button" title="<?php echo Core::_('Admin_Form.button_to_filter')?>" onclick="<?php echo $onclick?>"><i class="fa fa-search"></i></a>
						<a title="<?php echo Core::_('Admin_Form.button_to_clear')?>" class="btn btn-xs btn-magenta" onclick="$.clearFilter('<?php echo $windowId?>')"><i class="fa fa-times-circle"></i></a>
					</div><?php
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

				$quotedDatasetKey = htmlspecialchars($datasetKey);
				$escapedDatasetKey = Core_Str::escapeJavascriptVariable($this->jQueryEscape($datasetKey));

				$aDataFromDataset = $oAdmin_Form_Dataset->load();

				if (!empty($aDataFromDataset))
				{
					foreach ($aDataFromDataset as $oEntity)
					{
						try
						{
							$key_field_name = $this->_Admin_Form->key_field;
							$entityKey = $oEntity->$key_field_name;

							// Экранируем ' в имени индексного поля, т.к. дальше это значение пойдет в JS
							$quotedEntityKey = htmlspecialchars($entityKey);
							$escapedEntityKey = Core_Str::escapeJavascriptVariable($this->jQueryEscape(htmlspecialchars($entityKey)));

							/*$entityKey = str_replace(
								array("'", '%'),
								array("\'", '\\%'),
								$entityKey
							);*/
						}
						catch (Exception $e)
						{
							Core_Message::show('Caught exception: ' . $e->getMessage() . "\n", 'error');
							$entityKey = NULL;
						}

						?><tr id="row_<?php echo $quotedDatasetKey?>_<?php echo $quotedEntityKey?>">
						<?php
						// Чекбокс "Для элемента" показываем только при наличии действий
						if ($this->_Admin_Form->show_operations && $this->_showOperations)
						{
							?><td align="center" width="25">
								<label>
								<input type="checkbox" class="colored-black" id="check_<?php echo $quotedDatasetKey?>_<?php echo $quotedEntityKey?>" onclick="$('#<?php echo $windowId?>').setTopCheckbox(); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight()" /><span class="text"></span>
								</label><?php
							?></td><?php
						}

						foreach ($aAdmin_Form_Fields as $oAdmin_Form_Field)
						{
							// Перекрытие параметров для данного поля
							$oAdmin_Form_Field_Changed = $this->_changeField($oAdmin_Form_Dataset, $oAdmin_Form_Field);

							// Параметры поля.
							$width = htmlspecialchars(trim($oAdmin_Form_Field_Changed->width));
							$class = htmlspecialchars($oAdmin_Form_Field_Changed->class);

							$oAdmin_Form_Field->allow_sorting
								&& is_object($this->_sortingAdmin_Form_Field)
								&& $oAdmin_Form_Field->id == $this->_sortingAdmin_Form_Field->id
								&& $class .= ' highlight';

							?><td class="<?php echo trim($class)?>" <?php echo !empty($width) ? "width=\"{$width}\"" : ''?>><?php

							$fieldName = $this->getFieldName($oAdmin_Form_Field_Changed->name);

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

									$element_name = "apply_check_{$quotedDatasetKey}_{$quotedEntityKey}_fv_{$oAdmin_Form_Field_Changed->id}";

									$sCheckSelector = "check_{$escapedDatasetKey}_{$escapedEntityKey}";
									// Формат не экранируем, т.к. он может содержать теги
									$sFormat = $oAdmin_Form_Field_Changed->format;

									// Отображения элементов полей, в зависимости от их типа.
									switch ($oAdmin_Form_Field_Changed->type)
									{
										case 1: // Текст.
											if (!is_null($value))
											{
												?><span id="<?php echo $element_name?>"<?php echo 	$oAdmin_Form_Field_Changed->editable ? ' class="editable"' : ''?>><?php
												echo $this->applyFormat($value, $sFormat)?></span><?php
											}
										break;
										case 2: // Поле ввода.
											if (!is_null($value))
											{
											?><input type="text" name="<?php echo $element_name?>" id="<?php echo $element_name?>" value="<?php echo $value?>" onchange="$.setCheckbox('<?php echo $windowId?>', '<?php echo $sCheckSelector?>'); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight()" onkeydown="$.setCheckbox('<?php echo $windowId?>', '<?php echo $sCheckSelector?>'); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight()" class="form-control input-xs" /><?php
											}
										break;
										case 3: // Checkbox.
											?><label><input type="checkbox" name="<?php echo $element_name?>" id="<?php echo $element_name?>" <?php echo intval($value) ? 'checked="checked"' : ''?> onclick="$.setCheckbox('<?php echo $windowId?>', '<?php echo $sCheckSelector?>'); $('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight();" value="1" /><span class="text"></span></label><?php
										break;
										case 4: // Ссылка.
											$link = htmlspecialchars($oAdmin_Form_Field_Changed->link);
											$onclick = htmlspecialchars($oAdmin_Form_Field_Changed->onclick);

											//$link_text = trim($value);
											$link_text = $this->applyFormat($value, $sFormat);

											$link = $this->doReplaces($aAdmin_Form_Fields, $oEntity, $link);
											$onclick = $this->doReplaces($aAdmin_Form_Fields, $oEntity, $onclick, 'onclick');

											if (mb_strlen($link_text))
											{
												?><a href="<?php echo $link?>" <?php echo (!empty($onclick)) ? "onclick=\"{$onclick}\"" : ''?>><?php echo $link_text?></a><?php
											}
											else
											{
												?>&nbsp;<?php
											}
										break;
										case 5: // Дата-время.
											if (!is_null($value))
											{
												$value = $value == '0000-00-00 00:00:00' || $value == ''
													? ''
													: Core_Date::sql2datetime($value);
												echo $this->applyFormat($value, $sFormat);
											}
										break;
										case 6: // Дата.
											if (!is_null($value))
											{
												$value = $value == '0000-00-00 00:00:00' || $value == ''
													? ''
													: Core_Date::sql2date($value);
												echo $this->applyFormat($value, $sFormat);
											}
										break;
										case 7: // Картинка-ссылка.
											$link = $oAdmin_Form_Field_Changed->link;
											$onclick = $oAdmin_Form_Field_Changed->onclick;

											$link = $this->doReplaces($aAdmin_Form_Fields, $oEntity, $link);
											$onclick = $this->doReplaces($aAdmin_Form_Fields, $oEntity, $onclick, 'onclick');

											// ALT-ы к картинкам
											// TITLE-ы к картинкам
											$alt_array = $title_array = $value_array = $ico_array = array();

											/*
											Разделяем варианты значений на строки, т.к. они приходят к нам в виде:
											0 = /images/off.gif
											1 = /images/on.gif
											*/
											$str_array = explode("\n", $oAdmin_Form_Field_Changed->image);

											foreach ($str_array as $str_value)
											{
												// Каждую строку разделяем по равно
												$str_explode = explode('=', $str_value);

												if (count($str_explode) > 1)
												{
													$mIndex = trim($str_explode[0]);
													// сохраняем в массив варинаты значений и ссылки для них
													$value_array[$mIndex] = trim($str_explode[1]);

													// Если указано альтернативное значение для картинки - добавим его в alt и title
													if (isset($str_explode[2])
														&& trim($value) == $mIndex)
													{
														$alt_array[$mIndex] = $title_array[$mIndex] = trim($str_explode[2]);
													}

													isset($str_explode[3])
														&& $ico_array[$mIndex] = $str_explode[3];
												}
											}

											// Получаем заголовок столбца на случай, если для IMG не было указано alt-а или title
											$Admin_Word_Value = $oAdmin_Form_Field->Admin_Word->getWordByLanguage(
												$this->_Admin_Language->id
											);

											$fieldCaption = $Admin_Word_Value
												? htmlspecialchars($Admin_Word_Value->name)
												: "&mdash;";

											if (empty($alt_array[$value]))
											{
												$alt_array[$value] = $fieldCaption;
											}

											if (empty($title_array[$value]))
											{
												$title_array[$value] = $fieldCaption;
											}

											if (isset($value_array[$value]))
											{
												$src = $value_array[$value];
											}
											elseif(isset($value_array['']))
											{
												$src = $value_array[''];
											}
											else
											{
												$src = NULL;
											}

											if (isset($ico_array[$value]))
											{
												$ico = $ico_array[$value];
											}
											elseif(isset($ico_array['']))
											{
												$ico = $ico_array[''];
											}
											else
											{
												$ico = NULL;
											}

											// Отображаем картинку ссылкой
											if (!empty($link) && !is_null($src))
											{
												?><a href="<?php echo $link?>" onclick="$('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight();<?php echo $onclick?>"><?php
											}

											// Отображаем картинку без ссылки
											if (!is_null($ico))
											{
												?><i class="<?php echo htmlspecialchars($ico)?>" title="<?php echo htmlspecialchars(Core_Array::get($title_array, $value))?>"></i><?php
											}
											elseif (!is_null($src))
											{
												?><img src="<?php echo htmlspecialchars($src)?>" alt="<?php echo htmlspecialchars(Core_Array::get($alt_array, $value))?>" title="<?php echo htmlspecialchars(Core_Array::get($title_array, $value))?>" /><?php
											}
											/*elseif (!empty($link) && !isset($value_array[$value]))
											{
												// Картинки для такого значения не найдено, но есть ссылка
												?><a href="<?php echo $link?>" onclick="$('#' + $.getWindowId('<?php echo $windowId?>') + ' #row_<?php echo $escapedDatasetKey?>_<?php echo $escapedEntityKey?>').toggleHighlight();<?php echo $onclick?> ">&mdash;</a><?php
											}*/
											else
											{
												// Картинки для такого значения не найдено
												?>&mdash;<?php
											}

											if (!empty($link) && !is_null($src))
											{
												?></a><?php
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

											?><select name="<?php echo $element_name?>" id="<?php echo $element_name?>" onchange="$.setCheckbox('<?php echo $windowId?>', '<?php echo $sCheckSelector?>')"><?php

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
										default: // Тип не определен.
											?>&nbsp;<?php
										break;
									}
								}
								// Вычисляемое поле с помощью функции обратного вызова
								else
								{
									if (method_exists($oEntity, $fieldName)
										|| method_exists($oEntity, 'isCallable') && $oEntity->isCallable($fieldName)
									)
									{
										// Выполним функцию обратного вызова
										echo $oEntity->$fieldName($oAdmin_Form_Field_Changed, $this);
									}
									elseif (property_exists($oEntity, $fieldName))
									{
										// Выведим значение свойства
										echo $oEntity->$fieldName;
									}
								}
							}
							catch (Exception $e)
							{
								Core_Message::show('Caught exception: ' . $e->getMessage() . "\n", 'error');
							}

							// Badges
							$badgesMethodName = $fieldName . 'Badge';
							if (method_exists($oEntity, $badgesMethodName)
								|| method_exists($oEntity, 'isCallable') && $oEntity->isCallable($badgesMethodName)
							)
							{
								// Выполним функцию обратного вызова
								$oEntity->$badgesMethodName($oAdmin_Form_Field, $this);
							}
							?></td><?php
						}

						// Действия для строки в правом столбце
						if ($this->_Admin_Form->show_operations
						&& $this->_showOperations
						|| $allow_filter && $this->_showFilter)
						{
							// Определяем ширину столбца для действий.
							/*
							$width = isset($this->form_params['actions_width'])
								? strval($this->form_params['actions_width'])
								: '10px'; // Минимальная ширина
							*/
							$sContents = '';

							$oCore_Html_Entity_Ul = Core::factory('Core_Html_Entity_Ul')
								->class('dropdown-menu pull-right');

							?><td><?php

							$sActionsFullView = $sActionsShortView = '';

							$iActionsCount = 0;

							// Подмена массива действий через событие
							Core_Event::notify('Admin_Form_Controller.onBeforeShowActions', $this, array($datasetKey, $oEntity, $aAllowed_Admin_Form_Actions));
							$aActions = Core_Event::getLastReturn();

							!is_array($aActions)
								&& $aActions = $aAllowed_Admin_Form_Actions;

							foreach ($aActions as $key => $o_Admin_Form_Action)
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

								$iActionsCount++;

								$Admin_Word_Value = $o_Admin_Form_Action->Admin_Word->getWordByLanguage($this->_Admin_Language->id);

								$name = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
									? htmlspecialchars($Admin_Word_Value->name)
									: '';

								$href = $this->getAdminActionLoadHref($this->getPath(), $o_Admin_Form_Action->name, NULL, $escapedDatasetKey, $escapedEntityKey);

								$onclick = $this->getAdminActionLoadAjax($this->getPath(), $o_Admin_Form_Action->name, NULL, $datasetKey, $entityKey);

								// Добавляем установку метки для чекбокса и строки + добавлем уведомление, если необходимо
								if ($o_Admin_Form_Action->confirm)
								{
									$onclick = "res = confirm('" .
										htmlspecialchars(Core::_('Admin_Form.confirm_dialog', $name)) .
										"'); if (!res) { $('#{$windowId} #row_{$escapedDatasetKey}_{$escapedEntityKey}').toggleHighlight(); } else {{$onclick}} return res;";
								}

								// Раскрашиваем кнопки с верификацией
								/*
								$sBtnClass = $o_Admin_Form_Action->confirm
									?	$o_Admin_Form_Action->name == 'markDeleted'
										?	' btn-danger' // удаление
										:	' btn-warning' // верификация
									: ' btn-info'; // без верификации
									*/

								/*
								$actionClassName = '';

								switch ($o_Admin_Form_Action->name)
								{
									case 'edit':
										$actionClassName = 'pencil';
										break;
									case 'copy':
										$actionClassName = 'copy';
										break;
									case 'markDeleted':
										$actionClassName = 'times';
										break;
								}*/

								$sActionsFullView .= '<a class="btn btn-xs btn-' . htmlspecialchars($o_Admin_Form_Action->color) .' " title="' . $name . '" href="' . $href . '" onclick="' . $onclick .'"><i class="' . htmlspecialchars($o_Admin_Form_Action->icon) . '"></i></a>';

								$sActionsShortView .= '<li><a title="' . $name . '" href="' . htmlspecialchars($href) . '" onclick="' . $onclick .'"><i class="' . htmlspecialchars($o_Admin_Form_Action->icon) . ' btn-sm btn-' . htmlspecialchars($o_Admin_Form_Action->color) . '"></i>' . $name . '</a></li>';
							}

							if ($iActionsCount)
							{
							?><div class="btn-group <?php echo $iActionsCount > 1 ? 'visible-md visible-lg' : ''?>">
								<?php echo $sActionsFullView?>
							</div><?php

								if ($iActionsCount > 1)
								{
								?>
								<div class="visible-xs visible-sm">
									<div class="btn-group">
											<button class="btn btn-palegreen btn-xs dropdown-toggle" data-toggle="dropdown" >
											<i class="fa fa-bars"></i>
										</button>
										<ul class="dropdown-menu actions-dropdown-menu dropdown-menu-right" role="menu">
										<?php echo $sActionsShortView?>
										</ul>
									</div>
								</div><?php
								}
							}
							?></td><?php
						}
						?></tr><?php
					}
				}
			}
			?>
			</table>
		</div>
		<?php

		Core_Event::notify('Admin_Form_Controller.onAfterShowContent', $this);

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

			// Групповые операции
			if ($this->_Admin_Form->show_group_operations && !empty($aAllowed_Admin_Form_Actions))
			{
				?><div class="dataTables_actions"><?php
				$sActionsFullView = $sActionsShortView = '';

				$iGroupCount = 0;

				foreach ($aAllowed_Admin_Form_Actions as $o_Admin_Form_Action)
				{
					if ($o_Admin_Form_Action->group)
					{
						$iGroupCount++;

						$Admin_Word_Value = $o_Admin_Form_Action->Admin_Word->getWordByLanguage($this->_Admin_Language->id);

						$text = $Admin_Word_Value && strlen($Admin_Word_Value->name) > 0
							? $Admin_Word_Value->name
							: '';

						$href = $this->getAdminLoadHref($this->getPath(), $o_Admin_Form_Action->name);
						$onclick = $this->getAdminLoadAjax($this->getPath(), $o_Admin_Form_Action->name);

						// Нужно подтверждение для действия
						if ($o_Admin_Form_Action->confirm)
						{
							$onclick = "res = confirm('" . Core::_('Admin_Form.confirm_dialog', htmlspecialchars($text)) . "'); if (res) { {$onclick} } else {return false}";

							$link_class = 'admin_form_action_alert_link';
						}
						else
						{
							$link_class = 'admin_form_action_link';
						}

						// ниже по тексту alt-ы и title-ы не выводятся, т.к. они дублируются текстовыми
						// надписями и при отключении картинок текст дублируется
						/* alt="<?php echo htmlspecialchars($text)?>"*/

						$sActionsFullView .= '<li><a title="' . htmlspecialchars($text) . '" href="' . $href . '" onclick="' . $onclick .'"><i class="' . htmlspecialchars($o_Admin_Form_Action->icon) . ' btn-sm btn-' . htmlspecialchars($o_Admin_Form_Action->color) . '"></i>' . htmlspecialchars($text) . '</a></li>';

						$sActionsShortView .= '<a href="' . htmlspecialchars($href) . '" onclick="' . $onclick . '" class="btn-labeled btn btn-'. htmlspecialchars($o_Admin_Form_Action->color) . '" ><i class="btn-label ' . htmlspecialchars($o_Admin_Form_Action->icon) . '"></i>' . htmlspecialchars($text) . '</a>';
					}
				}

				if ($iGroupCount > 1)
				{
					?><div class="visible-sm visible-xs">
						<div class="btn-group dropup">
							<a class="btn btn-palegreen dropdown-toggle" data-toggle="dropdown">
								<i class="fa fa-bars icon-separator"></i>
								<?php echo Core::_('Admin_Form.actions')?>
							</a>
							<ul class="dropdown-menu">
							<?php echo $sActionsFullView?>
							</ul>
						</div>
					</div><?php
				}
				?><div <?php echo $iGroupCount > 1 ? 'class="hidden-sm hidden-xs"' : ''?>>
					<?php echo $sActionsShortView?>
				</div>
			</div>
			<?php
			}
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
			->name('admin_forms_on_page')
			->id('id_on_page')
			->class('form-control input-sm')
			->onchange("$.adminLoad({path: '{$path}', additionalParams: '{$additionalParams}', limit: this.options[this.selectedIndex].value, windowId : '{$windowId}'}); return false")
			->options($this->_onPage)
			->value($sCurrentValue)
			->execute();
	}

	/**
	 * Get form
	 * @return string
	 */
	protected function _getForm()
	{
		$oAdmin_View = Admin_View::create();
		$oAdmin_View
			->children($this->_children)
			->pageTitle($this->_pageTitle)
			->module($this->_module);

		ob_start();
		$this->_pageSelector();
		$oAdmin_View->pageSelector(ob_get_clean());

		// При показе формы могут быть добавлены сообщения в message, поэтому message показывается уже после отработки формы
		ob_start();
		$this->showContent();
		$this->showFooter();
		$content = ob_get_clean();

		ob_start();
		$oAdmin_View
			->content($content)
			->message($this->getMessage())
			->show();

		$this->_applyEditable();

		return ob_get_clean();
	}

	/**
	 * Show form footer
	 */
	public function showFooter()
	{
		$sShowNavigation = $this->getTotalCount() > $this->_limit;

		?><div class="DTTTFooter">
			<div class="row">
				<div class="col-xs-12 <?php echo $sShowNavigation ? 'col-sm-6 col-md-8' : ''?>">
					<?php $this->bottomActions()?>
				</div>
				<?php
				if ($sShowNavigation)
				{
					?><div class="col-xs-12 col-sm-6 col-md-4">
						<?php $this->pageNavigation()?>
					</div><?php
				}
				?>
			</div>
		</div><?php

		return $this;
	}

	protected $_pageNavigationDelta = 2;

	/**
	 * Показ строки ссылок
	 * @return self
	 */
	public function pageNavigation()
	{
		$total_count = $this->getTotalCount();
		$total_page = $total_count / $this->_limit;

		// Округляем в большую сторону
		if ($total_count % $this->_limit != 0)
		{
			$total_page = intval($total_page) + 1;
		}

		// Отображаем строку ссылок, если общее число страниц больше 1.
		if ($total_page > 1)
		{
			$this->_current > $total_page && $this->_current = $total_page;

			$oCore_Html_Entity_Div = Core::factory('Core_Html_Entity_Div')
				->class('dataTables_paginate paging_bootstrap');

			$oCore_Html_Entity_Ul = Core::factory('Core_Html_Entity_Ul')
				->class('pagination');

			$oCore_Html_Entity_Div->add($oCore_Html_Entity_Ul);

			// Ссылка на предыдущую страницу
			$page = $this->_current - 1 ? $this->_current - 1 : 1;

			$oCore_Html_Entity_Li = Core::factory('Core_Html_Entity_Li');
			$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);

			$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A');
			$oCore_Html_Entity_Li
				->class('prev' . ($this->_current == 1 ? ' disabled' : ''))
				->add(
					$oCore_Html_Entity_A
						->id('id_prev')
						->add(Admin_Form_Entity::factory('Code')
							->html('<i class="fa fa-angle-left"></i>')
						)
				);

			if ($this->_current != 1)
			{
				$oCore_Html_Entity_A
					->href($this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $page))
					->onclick($this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $page));
			}

			// Определяем номер ссылки, с которой начинается строка ссылок.
			$link_num_begin = ($this->_current - $this->_pageNavigationDelta < 1)
				? 1
				: $this->_current - $this->_pageNavigationDelta;

			// Определяем номер ссылки, которой заканчивается строка ссылок.
			$link_num_end = $this->_current + $this->_pageNavigationDelta;
			$link_num_end > $total_page && $link_num_end = $total_page;

			// Определяем число ссылок выводимых на страницу.
			$count_link = $link_num_end - $link_num_begin + 1;

			$oCore_Html_Entity_Li = Core::factory('Core_Html_Entity_Li');
			$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);

			$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A');
			$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);

			if ($this->_current == 1)
			{
				$oCore_Html_Entity_Li->class('active');

				$oCore_Html_Entity_A
					->class('current')
					->value($link_num_begin);
			}
			else
			{
				$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, 1);
				$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, 1);

				$oCore_Html_Entity_A
					->href($href)
					->onclick($onclick)
					//->class('page_link')
					->value(1);

				// Выведем … со ссылкой на 2-ю страницу, если показываем с 3-й
				if ($link_num_begin > 1)
				{
					$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, 2);
					$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, 2);

					$oCore_Html_Entity_A
						->href($href)
						->onclick($onclick)
						//->class('page_link')
						->value('…');
				}
			}

			// Страница не является первой и не является последней.
			for ($i = 1; $i < $count_link - 1; $i++)
			{
				$link_number = $link_num_begin + $i;

				$oCore_Html_Entity_Li = Core::factory('Core_Html_Entity_Li');
				$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);


				$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A')
					->value($link_number);
				$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);

				if ($link_number == $this->_current)
				{
					// Страница является текущей
					$oCore_Html_Entity_Li->class('active');
				}
				else
				{
					$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $link_number);
					$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $link_number);

					$oCore_Html_Entity_A
						->href($href)
						->onclick($onclick);
				}
			}

			$oCore_Html_Entity_Li = Core::factory('Core_Html_Entity_Li');
			$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);

			// Если последняя страница является текущей
			if ($this->_current == $total_page)
			{
				$oCore_Html_Entity_Li->class('active');

				$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A')
					->value($total_page);

				$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);
			}
			else
			{
				// Выведем … со ссылкой на предпоследнюю страницу
				if ($link_num_end < $total_page)
				{
					$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $total_page - 1);
					$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $total_page - 1);

					$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A')
						->href($href)
						->onclick($onclick)
						->value('…');

					$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);
				}

				$href = $this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $total_page);
				$onclick = $this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $total_page);

				$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A');

				// Последняя страница не является текущей
				$oCore_Html_Entity_A
					->href($href)
					->onclick($onclick)
					->value($total_page);

				$oCore_Html_Entity_Li = Core::factory('Core_Html_Entity_Li');
				$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);
				$oCore_Html_Entity_Li->add($oCore_Html_Entity_A);
			}

			// Формируем скрытые ссылки навигации для перехода по Ctrl + стрелка
			$oCore_Html_Entity_Li = Core::factory('Core_Html_Entity_Li');
			$oCore_Html_Entity_Ul->add($oCore_Html_Entity_Li);

			$oCore_Html_Entity_A = Core::factory('Core_Html_Entity_A');

			// Ссылка на следующую страницу
			$page = $this->_current + 1 ? $this->_current + 1 : 1;
			$oCore_Html_Entity_Li
				->class('next' . ($this->_current == $total_page ? ' disabled' : ''))
				->add(
					$oCore_Html_Entity_A
						->id('id_next')
						->add(Admin_Form_Entity::factory('Code')
								->html('<i class="fa fa-angle-right"></i>')
							)
				);

			if ($this->_current != $total_page)
			{
				$oCore_Html_Entity_A
					->href($this->getAdminLoadHref($this->getPath(), NULL, NULL, NULL, NULL, $page))
					->onclick($this->getAdminLoadAjax($this->getPath(), NULL, NULL, NULL, NULL, $page));
			}

			$oCore_Html_Entity_Div->execute();
		}

		return $this;
	}
}