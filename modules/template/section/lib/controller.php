<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Template_Section_Lib_Controller
 *
 * @package HostCMS
 * @subpackage Template
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Template_Section_Lib_Controller
{
	/**
	 * Forbidden types of lib properties to show
	 * @var array
	 */
	static public $forbiddenToShow = array(2, 4, 7);

	/**
	 * Template_Section_Lib object
	 * @var Template_Section_Lib_Model
	 */
	protected $_oTemplate_Section_Lib = NULL;

	/**
	 * Constructor.
	 * @param Template_Section_Lib_Model $oTemplate_Section_Lib
	 */
	public function __construct(Template_Section_Lib_Model $oTemplate_Section_Lib)
	{
		$this->_oTemplate_Section_Lib = $oTemplate_Section_Lib;
	}

	/**
	 * Get types
	 * @return array
	 */
	protected function _getTypes()
	{
		return array(
			'solid',
			'gradient',
			'colorpicker',
			'range',
			'colorpicker-range',
			'separator',
			'select'
		);
	}

	/**
	 * Get backgrounds
	 * @param string $type
	 * @return array
	 */
	protected function _getBackgrounds($type)
	{
		switch ($type)
		{
			case 'solid':
				$aBackgrounds = array(
					'black',
					'lightblack',
					'green',
					'blue',
					'yellow',
					'orange',
					'lightgray',
					'white'
				);
			break;
			case 'gradient':
				$aBackgrounds = array(
					'pink-gradient',
					'blue-gradient',
					'violet-gradient',
					'green-gradient'
				);
			break;
			default:
				$aBackgrounds = array();
		}

		return $aBackgrounds;
	}

	/**
	 * Parse linear gradient
	 * @param string $linear_gradient
	 * @return array
	 */
	protected function _parseLinearGradient($linear_gradient)
	{
		$linear_gradient = trim($linear_gradient);

		if (substr($linear_gradient, 0, strlen('linear-gradient(')) != 'linear-gradient(')
		{
			return array();
		}

		$params = substr($linear_gradient, strlen('linear-gradient('), -1);

		$params = preg_replace('/\s+/', ' ', trim($params)); // normalize white-space

		// remove unneeded spaces
		$params = str_replace(array( ' ( ', ' (', '( ' ), '(', $params);
		$params = str_replace(array( ' , ', ' ,', ', ' ), ',', $params);
		$params = str_replace(' )', ')', $params);

		// swap commas to tilde inside rgb() syntax so we can explode
		$params = preg_replace('/\(([^,)]+),([^,)]+),([^,)]+),?/', '($1~$2~$3~', $params);
		$params = str_replace('~)', ')', $params);

		$aReturn = explode(',', $params);

		// unswap comma and tilde
		foreach ($aReturn as $i => $part)
		{
			$aReturn[$i] = str_replace('~', ',', trim($part));
		}

		return $aReturn;
	}

	/**
	 * Parse style from Template_Section_Lib
	 * @param string $type
	 * @return array
	 */
	public function parseStyles($type, $aEntity = array(), $style = NULL)
	{
		$aReturn = array();

		if (is_null($style))
		{
			$style = $this->_oTemplate_Section_Lib->style;
		}

		$aStyles = $style != ''
			? explode(';', $style)
			: array();

		$bIssetGradient = FALSE;

		foreach ($aStyles as $aStyle)
		{
			$aExplode = array_map('trim', explode(':', $aStyle));

			if (isset($aExplode[1]))
			{
				$property = $aExplode[0];
				$value = $aExplode[1];

				$bLinearGradient = strpos($value, 'linear-gradient') !== FALSE;

				if ($property == 'background' && $bLinearGradient)
				{
					$bIssetGradient = TRUE;

					if ($type == 'colorpicker-range')
					{
						if (isset($aEntity['name']) && isset($aEntity['contain']) && strpos($value, $aEntity['contain']) !== FALSE)
						{
							$aGradient = $this->_parseLinearGradient($value);

							// var_dump($aGradient);

							if (isset($aGradient[2]))
							{
								$aReturn[$aEntity['name'] . '_deg'] = $aGradient[0];
								$aReturn[$aEntity['name'] . '_from'] = $aGradient[1];
								$aReturn[$aEntity['name'] . '_to'] = $aGradient[2];
							}
						}
					}
				}

				$aReturn[$property] = $value;
			}
		}

		// echo "<pre>";
		// var_dump($aReturn);
		// var_dump($bIssetGradient);
		// echo "</pre>";

		if ($type == 'colorpicker'
			&& isset($aEntity['property'])  && $aEntity['property'] == 'background'
			&& isset($aReturn['background'])
			&& $bIssetGradient
		)
		{
			unset($aReturn['background']);
		}

		if ($type == 'colorpicker-range'
			&& isset($aEntity['property'])  && $aEntity['property'] == 'background'
			&& isset($aReturn['background'])
			&& !$bIssetGradient
		)
		{
			unset($aReturn['background']);
		}

		// echo "<pre>";
		// var_dump($aReturn);
		// echo "</pre>";

		return $aReturn;
	}

	/**
	 * Create style string
	 * @param string $type
	 * @param array $aStyles
	 * @return string
	 */
	public function createStyle($type, $aStyles)
	{
		$aTmp = array();

		foreach ($aStyles as $name => $value)
		{
			$aTmp[] = $name . ': ' . $value;
		}

		return implode('; ', $aTmp);
	}

	/**
	 * Parse class from Template_Section_Lib
	 * @param string $type
	 * @return array
	 */
	public function parseClasses($type, $field = '')
	{
		$class = $this->_oTemplate_Section_Lib->class;

		if ($field != '')
		{
			$aFieldClasses = $this->_oTemplate_Section_Lib->field_classes != ''
				? json_decode($this->_oTemplate_Section_Lib->field_classes, TRUE)
				: array();

			$class = isset($aFieldClasses[$field])
				? $aFieldClasses[$field]
				: '';
		}

		return $class != ''
			? array_map('trim', explode(' ', $class))
			: array();
	}

	/**
	 * Create class string
	 * @param string $type
	 * @param array $aClasses
	 * @return string
	 */
	public function createClass($type, $aClasses)
	{
		return implode(' ', array_filter($aClasses));
	}

	/**
	 * Get colorpicker html block
	 * @param string $name
	 * @param string $property
	 * @param array $aStyle
	 * @param array $aEntity
	 * @return self
	 */
	protected function _getColorpickerBlock($type, $name, $property, $aEntity, $field = '')
	{
		// $aStyles = $this->parseStyles($type, $aEntity);

		$aFieldStyles = $this->_oTemplate_Section_Lib->field_styles != ''
			? json_decode($this->_oTemplate_Section_Lib->field_styles, TRUE)
			: array();

		if ($field == '')
		{
			$aStyles = $this->parseStyles($type, $aEntity);
		}
		else
		{
			$aStyles = isset($aFieldStyles[$field])
				? $this->parseStyles($type, $aEntity, $aFieldStyles[$field])
				: array();
		}

		if ($name == '')
		{
			$name = $property;
		}

		$value = $property != '' && isset($aStyles[$property])
			? htmlspecialchars($aStyles[$property])
			: '';

		if ($type == 'colorpicker-range' && isset($aStyles[$name]))
		{
			$value = htmlspecialchars($aStyles[$name]);
		}

		$data_name = isset($aEntity['name']) && $aEntity['name'] != ''
			? ' data-name="' . htmlspecialchars($aEntity['name']) . '"'
			: '';

		$swatches = isset($aEntity['swatches']) && is_array($aEntity['swatches'])
			? ' data-swatches="' . implode('|', $aEntity['swatches']) . '"'
			: '';

		?><input type="text" name="<?php echo $name?>" class="colorpicker" data-id="<?php echo $this->_oTemplate_Section_Lib->id?>" data-type="<?php echo $type?>" data-property="<?php echo htmlspecialchars($property)?>" <?php echo $data_name?> value="<?php echo $value?>" <?php echo $swatches?>/><?php

		return $this;
	}

	/**
	 * Get range html block
	 * @param string $name
	 * @param string $property
	 * @param array $aEntity
	 * @return self
	 */
	protected function _getRangeBlock($type, $name, $property, $aEntity, $field = '')
	{
		// $aStyles = $this->parseStyles($type, $aEntity);

		$aFieldStyles = $this->_oTemplate_Section_Lib->field_styles != ''
			? json_decode($this->_oTemplate_Section_Lib->field_styles, TRUE)
			: array();

		if ($field == '')
		{
			$aStyles = $this->parseStyles($type, $aEntity);
		}
		else
		{
			$aStyles = isset($aFieldStyles[$field])
				? $this->parseStyles($type, $aEntity, $aFieldStyles[$field])
				: array();
		}

		$min = Core_Array::get($aEntity, 'from', 0, 'int');
		$max = Core_Array::get($aEntity, 'to', 0, 'int');
		$step = Core_Array::get($aEntity, 'step', 1, 'int');
		$measure = Core_Array::get($aEntity, 'measure', '', 'trim');

		$defaultValue = isset($aEntity['defaultValue']) && $aEntity['defaultValue'] != ''
			? $aEntity['defaultValue']
			: 1;

		$value = $property != '' && isset($aStyles[$property])
			? htmlspecialchars($aStyles[$property])
			: $defaultValue;

		if ($type == 'colorpicker-range' && isset($aStyles[$name]))
		{
			$value = htmlspecialchars($aStyles[$name]);
		}

		$data_name = isset($aEntity['name']) && $aEntity['name'] != ''
			? ' data-name="' . htmlspecialchars($aEntity['name']) . '"'
			: '';

		$data_unset = isset($aEntity['unset']) && $aEntity['unset']
			? ' data-unset="' . htmlspecialchars($aEntity['unset']) . '"'
			: '';

		// must be with 'pt', 'px', '%' etc.
		$value = intval(filter_var($value, FILTER_SANITIZE_NUMBER_INT));

		?><div class="range-wrapper">
			<div><input type="range" name="<?php echo $name?>" class="form-range" min="<?php echo $min?>" max="<?php echo $max?>" step="<?php echo $step?>" data-id="<?php echo $this->_oTemplate_Section_Lib->id?>" data-type="<?php echo $type?>" data-property="<?php echo htmlspecialchars($property)?>" <?php echo $data_name?> <?php echo $data_unset?> value="<?php echo $value?>" oninput="hQuery.changeRange(this, '<?php echo htmlspecialchars($measure)?>')"/></div>
			<div class="range-value"><?php echo $value?><?php echo htmlspecialchars($measure)?></div>
		</div><?php

		return $this;
	}

	/**
	 * Show background block
	 * @param string $type
	 * @return string
	 */
	protected function _showBlock($type, $aEntity, $field = '')
	{
		$property = Core_Array::get($aEntity, 'property', '', 'trim');
		$name = Core_Array::get($aEntity, 'name', '', 'trim');

		if ($field != '')
		{
			$aFieldStyles = $this->_oTemplate_Section_Lib->field_styles != ''
				? json_decode($this->_oTemplate_Section_Lib->field_styles, TRUE)
				: array();

			$aStyles = isset($aFieldStyles[$field])
				? $this->parseStyles($type, array(), $aFieldStyles[$field])
				: array();
		}
		else
		{
			$aStyles = $this->parseStyles($type, $aEntity);
		}

		switch ($type)
		{
			case 'solid':
			case 'gradient':
				$aBackgrounds = $this->_getBackgrounds($type);

				$aClasses = $this->parseClasses($type, $field);

				// var_dump($aClasses);

				foreach ($aBackgrounds as $background)
				{
					$active = in_array('preset-' . $background, $aClasses)
						? 'background-active'
						: ''

					?><div onclick="hQuery.changePreset(this, <?php echo $this->_oTemplate_Section_Lib->id?>)" class="background-item background-<?php echo $type?>-item <?php echo $active?>" data-id="<?php echo $this->_oTemplate_Section_Lib->id?>" data-type="<?php echo $type?>" data-background="preset-<?php echo $background?>">
						<i class="fa-solid fa-align-left"></i>
					</div><?php
				}
			break;
			case 'colorpicker':
				$this->_getColorpickerBlock($type, $name, $property, $aEntity, $field);
			break;
			case 'range':
				$this->_getRangeBlock($type, $name, $property, $aEntity, $field);
			break;
			case 'colorpicker-range':
				?><div class="colorpicker-range-wrapper">
					<div class="colorpicker-range-colors"><?php
						$this->_getColorpickerBlock($type, $name . '_from', $property, $aEntity, $field);
						$this->_getColorpickerBlock($type, $name . '_to', $property, $aEntity, $field);
					?></div>
					<div class="colorpicker-range"><?php
						$this->_getRangeBlock($type, $name . '_deg', $property, $aEntity, $field);
					?></div>
				</div><?php
			break;
			case 'separator':
				?><hr/><?php
			break;
			case 'select':
				if (isset($aEntity['options']) && is_array($aEntity['options']) && count($aEntity['options']))
				{
					$value = $property != '' && isset($aStyles[$property])
						? $aStyles[$property]
						: '';

					?><select data-id="<?php echo $this->_oTemplate_Section_Lib->id?>" data-type="<?php echo $type?>" data-property="<?php echo htmlspecialchars($property)?>" onchange="hQuery.refreshStyle(this, hQuery(this).val())">
						<?php
						foreach ($aEntity['options'] as $option_value => $option_name)
						{
							$selected = $option_value == $value
								? ' selected="selected"'
								: '';

							$style = $option_value != ''
								? ' style="font-family: ' . htmlspecialchars($option_value) . ' !important;"'
								: '';

							?><option <?php echo $style?> value="<?php echo htmlspecialchars($option_value)?>" <?php echo $selected?>><?php echo htmlspecialchars($option_name)?></option><?php
						}
						?>
					</select><?php
				}
			break;
		}

		return $this;
	}

	/**
	 * Show right panel with settings
	 * @return string
	 */
	public function showPanel($field = '', $aAttributes = array())
	{
		$aTypes = $this->_getTypes();
		$aEntities = $this->_getEntities();

		// echo "<pre>";
		// var_dump($aAttributes);
		// echo "</pre>";

		$attributes = count($aAttributes)
			? implode(' ', $aAttributes)
			: '';

		// echo "<pre>";
		// var_dump($attributes);
		// echo "</pre>";

		ob_start();

		?><div id="panel<?php echo $this->_oTemplate_Section_Lib->id?>" <?php echo htmlspecialchars($attributes)?> class="bootsrap-iso template-settings template-section-lib-settings">
			<div class="slidepanel">
				<div class="slidepanel-button-close"><i class="fa-solid fa-xmark"></i></div>
				<div class="background-wrapper">
					<div class="tab">
						<input checked id="tab-btn-1" name="tab-btn" type="radio" value="">
						<label for="tab-btn-1">Дизайн</label>

						<input id="tab-btn-2" name="tab-btn" type="radio" value="">
						<label for="tab-btn-2">Настройки</label>

						<div class="tab-content" id="main">
							<?php
							foreach ($aEntities as $aEntity)
							{
								if (isset($aEntity['type']) && in_array($aEntity['type'], $aTypes))
								{
									$type = $aEntity['type'];

									$class = Core_Array::get($aEntity, 'class', '', 'trim');
									$title = Core_Array::get($aEntity, 'title', NULL);

									?><div class="background-block <?php echo $class?>" data-type="<?php echo htmlspecialchars($type)?>">
										<?php if (!is_null($title))
										{
											?><div class="background-title">
												<div><?php echo $title?></div>
												<?php
												if (!in_array($type, array('solid', 'gradient')))
												{
													$defaultValue = isset($aEntity['defaultValue'])
														? htmlspecialchars($aEntity['defaultValue'])
														: '';

													$measure = isset($aEntity['measure'])
														? htmlspecialchars($aEntity['measure'])
														: '';

													?><i class="fa-solid fa-circle-xmark" title="Clear" onclick="hQuery.clearBlock(this, '<?php echo htmlspecialchars($type)?>', '<?php echo htmlspecialchars($defaultValue)?>', '<?php echo htmlspecialchars($measure)?>')"></i><?php
												}
												?>
											</div><?php
										}

										$this->_showBlock($type, $aEntity, $field);
									?></div><?php
								}
							}
							?>
						</div>
						<div id="settings" class="tab-content">
							<?php echo $this->showSettingsBlock()?>
						</div>
					</div>
				</div>
			</div>
			<script>
				hQuery('.template-section-lib-settings .colorpicker').each(function () {
					var data_swatches = $(this).attr('data-swatches') || '',
						swatches = data_swatches.split('|').filter(function(i){return i});

					hQuery(this).minicolors({
						control: $(this).attr('data-control') || 'hue',
						defaultValue: $(this).attr('data-defaultValue') || '',
						inline: $(this).attr('data-inline') === 'true',
						letterCase: $(this).attr('data-letterCase') || 'lowercase',
						opacity: $(this).attr('data-rgba'),
						position: $(this).attr('data-position') || 'bottom right',
						format: $(this).attr('data-format') || 'hex',
						change: function (hex, opacity) {
							if (!hex) return;
							if (opacity) hex += ', ' + opacity;
							try {
							} catch (e) { }
						},
						hide: hQuery.updateMinicolors,
						theme: 'bootstrap',
						swatches: swatches
					});
				});
			</script>
		</div><?php

		return ob_get_clean();
	}

	/**
	 * Show settings block
	 * @return string
	 */
	public function showSettingsBlock()
	{
		ob_start();

		?><form class="settings<?php echo $this->_oTemplate_Section_Lib->id?>" action="<?php echo Admin_Form_Controller::correctBackendPath('/{admin}/template/index.php')?>" method="POST" enctype="multipart/form-data">
			<div class="button-wrapper">
				<button class="button-1" onclick="hQuery.saveSettings(this, <?php echo intval($this->_oTemplate_Section_Lib->id)?>, <?php echo intval($this->_oTemplate_Section_Lib->Template_Section->id)?>)"><?php echo Core::_('Admin_Form.save')?></button>
			</div>
			<?php
				$this->_showSettings();
			?>
		</form><?php

		return ob_get_clean();
	}

	/**
	 * Show settings
	 * @return self
	 */
	protected function _showSettings()
	{
		$aLibOptions = !is_null($this->_oTemplate_Section_Lib->options)
			? json_decode($this->_oTemplate_Section_Lib->options, TRUE)
			: array();

		$oLib = $this->_oTemplate_Section_Lib->Lib;

		$oLib_Properties = $oLib->Lib_Properties;
		$oLib_Properties->queryBuilder()
			->where('lib_properties.parent_id', '=', 0)
			->where('lib_properties.type', 'NOT IN', self::$forbiddenToShow);

		$aLib_Properties = $oLib_Properties->findAll(FALSE);

		foreach ($aLib_Properties as $oLib_Property)
		{
			// Получаем значение параметра
			$value = isset($aLibOptions[$oLib_Property->varible_name])
				? $aLibOptions[$oLib_Property->varible_name]
				: ($oLib_Property->type != 8
					? $oLib_Property->default_value
					: NULL
				);

			// echo "<pre>";
			// var_dump($value);
			// echo "</pre>";

			if ($oLib_Property->type != 10)
			{
				$this->_showSettingRow($oLib_Property, $value);
			}
			else
			{
				$aTmp = $oLib->Lib_Properties->getAllByparent_id($oLib_Property->id, FALSE);

				!is_array($value) && $value = array($value);

				?><div class="details-wrapper details-sort<?php echo intval($oLib_Property->id)?>">
					<div class="details-title">
						<span><?php echo htmlspecialchars($oLib_Property->name)?></span>
						<i title="Add" class="fa-solid fa-plus" onclick="hQuery.addPoint(this, <?php echo intval($this->_oTemplate_Section_Lib->id)?>, '<?php echo intval($oLib_Property->id)?>', '<?php echo htmlspecialchars($oLib_Property->varible_name)?>');"></i>
					</div>
					<div class="details-item-wrapper" data-block-name="<?php echo htmlspecialchars($oLib_Property->varible_name)?>"><?php
						foreach (array_keys($value) as $key => $blockId)
						{
							$this->showDetailsItem($oLib_Property, $value, $aTmp, $key, $blockId);
						}
					?></div>
				</div>
				<script>
					var $object = hQuery(".details-sort<?php echo intval($oLib_Property->id)?>");
					$object.sortable({
						connectWith: ".details-sort<?php echo intval($oLib_Property->id)?>",
						items: "> .details-item",
						handle: ".fa-grip",
						scroll: false,
						placeholder: 'placeholder',
						tolerance: 'pointer',
						helper: "clone",
						/*stop: function (event, ui) {
							hQuery.updateSettings(".details-sort<?php echo $oLib_Property->id?> :input", hQuery(".details-sort<?php echo $oLib_Property->id?> .details-title"), <?php echo $this->_oTemplate_Section_Lib->id?>, <?php echo $this->_oTemplate_Section_Lib->Template_Section->id?>);
						}*/
					});
				</script>
				<?php
			}
		}

		return $this;
	}

	/**
	 * Show details item
	 * @param Lib_Property_Model $oLib_Property
	 * @param array $value
	 * @param array $aTmp
	 * @param int $key
	 * @param int $blockId
	 * @param bool $showCaption
	 * @param bool $bCopy
	 * @return self
	 */
	public function showDetailsItem(Lib_Property_Model $oLib_Property, $value, $aTmp, $key, $blockId, $showCaption = TRUE, $bCopy = TRUE)
	{
		if (count($aTmp))
		{
			$caption = $showCaption && isset($aTmp[0])
				? Core_Array::get($value[$blockId], $aTmp[0]->varible_name, '', 'trim')
				: 'New';

			?><div class="details-item" data-key="<?php echo intval($key)?>">
				<details><summary><i class="fa-solid fa-grip"></i><span class="summary-title"><?php echo htmlspecialchars(strip_tags($caption))?></span></summary><div class="settings-row-wrapper" data-block-name="<?php echo htmlspecialchars($oLib_Property->varible_name)?>"><?php
				foreach ($aTmp as $oSub_Lib_Property)
				{
					$subValue = Core_Array::get($value[$blockId], $oSub_Lib_Property->varible_name, '', 'trim');

					$this->_showSettingRow($oSub_Lib_Property, $subValue, $key, $oLib_Property->varible_name);
				}
				?></div></details>
					<div class="details-item-actions">
						<?php
						if ($bCopy)
						{
							?><i title="Copy" class="fa-solid fa-copy" onclick="hQuery.copyPoint(this, <?php echo intval($this->_oTemplate_Section_Lib->id)?>, '<?php echo intval($oLib_Property->id)?>', '<?php echo htmlspecialchars($oLib_Property->varible_name)?>', <?php echo intval($blockId)?>);"></i><?php
						}?>
						<i title="Delete" class="fa-solid fa-trash-can" onclick="hQuery.deletePoint(this, '<?php echo htmlspecialchars($oLib_Property->varible_name)?>');"></i>
					</div>
			</div><?php
		}

		return $this;
	}

	/**
	 * Show settings row
	 * @param Lib_Property_Model $oLib_Property
	 * @param string $value
	 * @param integer $position
	 * @param string $prefix
	 * @return self
	 */
	protected function _showSettingRow(Lib_Property_Model $oLib_Property, $value, $position = 0, $prefix = '')
	{
		if ($oLib_Property->type != 2)
		{
			/*$name = $prefix . $oLib_Property->varible_name . '_' . $oLib_Property->id;
			$name .= $position
				? '_' . $position
				: '[]';*/

			// card[0][647]

			$name = $prefix != ''
				? $prefix . '[' . $position . '][' . $oLib_Property->varible_name . ']'
				: $oLib_Property->varible_name;

			$old_position = $position
				? $oLib_Property->varible_name . '_' . $position
				: $oLib_Property->varible_name;

			?><div class="settings-row-title"><?php echo htmlspecialchars($oLib_Property->name)?></div><?php

			switch ($oLib_Property->type)
			{
				// Визуальный редактор
				case 9:
					?><textarea class="form-control mb-2" data-wysiwyg="1" name="<?php echo $name?>" data-old-position="<?php echo htmlspecialchars($old_position)?>"><?php echo $value?></textarea>
					<script>
						setTimeout(function(){
							var object = hQuery("textarea[name = '<?php echo $name?>']");
							// console.log(object);

							// object.addClass('editing');

							object.tinymce({
								language: backendLng,
								language_url: hostcmsBackend + '/wysiwyg/langs/' + backendLng + '.js',
								init_instance_callback: function (editor) {
									editor.on('init', function (e) {
										e.stopImmediatePropagation();
										editor.remove();
										object.css('visibility', '');
									});
								},
								script_url: hostcmsBackend + "/wysiwyg/tinymce.min.js",
								menubar: false,
								toolbar_mode: 'sliding',
								toolbar_items_size: 'small',
								promotion: false,
								statusbar: false,
								// inline: true,
								plugins: 'advlist autolink lists link image charmap preview anchor searchreplace visualblocks code fullscreen insertdatetime media table importcss',
								toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist | link unlink image media preview table | removeformat code',
								font_size_formats: "8pt 9pt 10pt 11pt 12pt 14pt 16pt 18pt 20pt 24pt 30pt 36pt 48pt 60pt 72pt 96pt"
							});
						}, 1000);
					</script><?php
				break;
				// Файл
				case 8:
					?><div class="settings-row-icon-wrapper mb-2">
						<input type="file" class="form-control" name="<?php echo $name?>" data-old-position="<?php echo htmlspecialchars($old_position)?>"/><?php
						if ($value != '' && Core_File::isFile(CMS_FOLDER . $value))
						{
							?><div class="input-settings-row-icon-wrapper"><input type="button" class="settings-row-icon" popovertarget="setting-icon-popover<?php echo $name?>"/>
								<i class="fa-solid fa-image"></i>
							</div>
							<div id="setting-icon-popover<?php echo $name?>" class="setting-icon-popover" popover="popover">
								<img src="<?php echo htmlspecialchars($value)?>"/>
							</div><?php
						}
					?></div><?php
				break;
				// Иконка
				case 11:
					?><div class="settings-row-icon-wrapper mb-2">
						<input type="text" class="form-control" placeholder="<?php echo htmlspecialchars($oLib_Property->name)?>" name="<?php echo $name?>" data-old-position="<?php echo htmlspecialchars($old_position)?>" value="<?php echo htmlspecialchars($value)?>"/>
						<span class="settings-row-icon" onclick="hQuery.showSettingsCrmIcons(this, '<?php echo $name?>')">
						<i class="<?php echo htmlspecialchars($value)?>"></i></span>
					</div><?php
				break;
				default:
					?><input type="text" class="form-control mb-2" placeholder="<?php echo htmlspecialchars($oLib_Property->name)?>" name="<?php echo $name?>" data-old-position="<?php echo htmlspecialchars($old_position)?>" value="<?php echo htmlspecialchars($value)?>"/><?php
			}
		}

		return $this;
	}

	/**
	 * Get entities
	 * @return array
	 */
	protected function _getEntities()
	{
		return array(
			array(
				'type' => 'solid',
				'title' => Core::_('Template_Section_Lib.type_solid')
			),
			array(
				'type' => 'gradient',
				'title' => Core::_('Template_Section_Lib.type_gradient')
			),
			array(
				'type' => 'colorpicker',
				'title' => Core::_('Template_Section_Lib.background'),
				'class' => ' col-6',
				'property' => 'background',
				'swatches' => array(
					'#ef9a9a',
					'#90caf9',
					'#a5d6a7',
					'#fff59d',
					'#ffcc80',
					'#bcaaa4',
					'#eeeeee',
					'#f44336',
					'#2196f3',
					'#4caf50',
					'#ffeb3b',
					'#ff9800',
					'#795548',
					'#9e9e9e'
				)
			),
			array(
				'type' => 'colorpicker',
				'title' => Core::_('Template_Section_Lib.color'),
				'class' => ' col-6',
				'property' => 'color'
			),
			array(
				'type' => 'range',
				'title' => Core::_('Template_Section_Lib.opacity'),
				'property' => 'opacity',
				'from' => 0,
				'to' => 100,
				'step' => 1,
				'measure' => '%',
				'defaultValue' => 100
			),
			array('type' => 'separator'),
			array(
				'type' => 'colorpicker-range',
				'title' => Core::_('Template_Section_Lib.gradient_background'),
				'property' => 'background',
				'contain' => 'linear-gradient',
				'name' => 'background_gradient',
				'from' => -360,
				'to' => 360,
				'step' => 1,
				'measure' => 'deg',
				'defaultValue' => 45
			),
			array('type' => 'separator'),
			array(
				'type' => 'select',
				'title' => Core::_('Template_Section_Lib.font_family'),
				'property' => 'font-family',
				'options' => array(
					'' => '...',
					"'Roboto', sans-serif" => 'Roboto',
					"'Ubuntu', sans-serif" => 'Ubuntu',
					"'Montserrat', sans-serif" => 'Montserrat'
				)
			),
			array(
				'type' => 'range',
				'title' => Core::_('Template_Section_Lib.font_size'),
				'property' => 'font-size',
				'from' => 0,
				'to' => 48,
				'measure' => 'pt',
				'defaultValue' => 14,
				'unset' => 1
			),
			array(
				'type' => 'range',
				'title' => Core::_('Template_Section_Lib.font_weight'),
				'property' => 'font-weight',
				'from' => 0,
				'to' => 900,
				'step' => 100,
				'defaultValue' => 400,
				'unset' => 1
			),
			array('type' => 'separator'),
			array(
				'type' => 'range',
				'title' => Core::_('Template_Section_Lib.padding_top'),
				'property' => 'padding-top',
				'from' => 0,
				'to' => 150,
				'step' => 5,
				'measure' => 'px',
				'defaultValue' => 0
			),
			array(
				'type' => 'range',
				'title' => Core::_('Template_Section_Lib.padding_bottom'),
				'property' => 'padding-bottom',
				'from' => 0,
				'to' => 150,
				'step' => 5,
				'measure' => 'px',
				'defaultValue' => 0
			),
			array(
				'type' => 'range',
				'title' => Core::_('Template_Section_Lib.padding_left'),
				'property' => 'padding-left',
				'from' => 0,
				'to' => 150,
				'step' => 5,
				'measure' => 'px',
				'defaultValue' => 0
			),
			array(
				'type' => 'range',
				'title' => Core::_('Template_Section_Lib.padding_right'),
				'property' => 'padding-right',
				'from' => 0,
				'to' => 150,
				'step' => 5,
				'measure' => 'px',
				'defaultValue' => 0
			),
			array(
				'type' => 'range',
				'title' => Core::_('Template_Section_Lib.margin_top'),
				'property' => 'margin-top',
				'from' => 0,
				'to' => 170,
				'step' => 1,
				'measure' => 'px',
				'defaultValue' => 0
			),
			array('type' => 'separator')
		);
	}
}