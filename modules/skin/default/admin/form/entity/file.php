<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_File extends Admin_Form_Entity_Input
{
	/**
	 * Constructor.
	 * Соответствия старых и новых параметров см. в execute()
	 */
	public function __construct()
	{
		$this->_allowedProperties += array(
			'largeImage',
			'smallImage',
			'crop',
		);

		parent::__construct();

		if (is_null($this->largeImage))
		{
			$this->largeImage = array();
		}

		if (is_null($this->smallImage))
		{
			$this->smallImage = array();
		}

		$this->crop = FALSE;

		$this->divAttr = array('class' => 'col-xs-12') + $this->divAttr;
	}

	/**
	 * Escaping square brackets
	 * @param string $id source string
	 * @return string
	 */
	protected function _correctId($id)
	{
		return str_replace(array('[', ']'), array('\\\\[', '\\\\]'), $id);
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$oCore_Registry = Core_Registry::instance();
		$iAdmin_Form_Count = $oCore_Registry->get('Admin_Form_Count', 0);
		$oCore_Registry->set('Admin_Form_Count', $iAdmin_Form_Count + 1);

		$aTypicalLargeParams = array(
			// image_big_max_width - значение максимальной ширины большого изображения;
			'max_width' => defined('MAX_SIZE_LOAD_IMAGE_BIG') ? MAX_SIZE_LOAD_IMAGE_BIG : 0,

			// image_big_max_height - значение максимальной высоты большого изображения;
			'max_height' => defined('MAX_SIZE_LOAD_IMAGE_BIG') ? MAX_SIZE_LOAD_IMAGE_BIG : 0,

			// big_image_path - адрес большого загруженного изображения
			'path' => '',

			// show_big_image_params - параметр, определяющий отображать ли настройки большого изображения
			'show_params' => TRUE,

			// watermark_position_x - значение поля ввода с подписью "По оси X"
			'watermark_position_x' => '50%',

			// watermark_position_y - значение поля ввода с подписью "По оси Y"
			'watermark_position_y' => '100%',

			// used_watermark_big_image_show - отображать ли checkbox с подписью "Наложить водяной знак на большое изображение" (1 - отображать (по умолчанию), 0 - не отображать);
			'place_watermark_checkbox' => TRUE,

			// used_watermark_big_image_checked - вид ображения checkbox'а с подписью "Наложить водяной знак на большое изображение" (1 - отображать выбранным (по умолчанию), 0 - невыбранным);
			'place_watermark_checkbox_checked' => TRUE,

			// onclick_delete_big_image - значение onclick для удаления большой картинки
			'delete_onclick' => '',

			// href_delete_big_image - значение href для удаления большой картинки
			'delete_href' => NULL,

			'caption' => $this->caption,
			'name' => $this->name,
			'id' => $this->id,

			// used_big_image_preserve_aspect_ratio - параметр Отображать ли checkbox с подписью "Сохранять пропорции изображения" (1 - отображать (по умолчанию), 0 - не отображать);
			'preserve_aspect_ratio_checkbox' => TRUE,

			// used_big_image_preserve_aspect_ratio_checked - вид ображения checkbox'а с подписью "Сохранять пропорции изображения" (1 - отображать выбранным (по умолчанию), 0 - невыбранным);
			'preserve_aspect_ratio_checkbox_checked' => TRUE,

			// Показать поле описания файла
			'show_description' => FALSE,

			// Описания файла
			'description' => ''
		);

		// image_watermark_position_x_show - показывать поле задания положения "водяного" знака по оси X
		$aTypicalLargeParams['place_watermark_x_show'] = $aTypicalLargeParams['place_watermark_checkbox'];

		// image_watermark_position_y_show - показывать поле задания положения "водяного" знака по оси Y
		$aTypicalLargeParams['place_watermark_y_show'] = $aTypicalLargeParams['place_watermark_checkbox'];

		// Объединяем с типовыми параметрами
		$this->largeImage += $aTypicalLargeParams;

		// -------------------

		$aTypicalSmallParams = array(
			// load_small_image_show - отображать ли поле загрузки малого изображения (1 - отображать (по умолчанию), 0 - не отображать);
			'show' => TRUE,

			// image_small_max_width - значение максимальной ширины малого изображения;
			'max_width' => defined('MAX_SIZE_LOAD_IMAGE') ? MAX_SIZE_LOAD_IMAGE : 0,

			// image_small_max_height - значение максимальной высоты малого изображения;
			'max_height' => defined('MAX_SIZE_LOAD_IMAGE') ? MAX_SIZE_LOAD_IMAGE : 0,

			// small_image_path - адрес малого загруженного изображения
			'path' => '',

			// show_small_image_params - параметр, определяющий отображать ли настройки малого изображения
			'show_params' => TRUE,

			// make_small_image_from_big_show - отображать ли checkbox с подписью "Создать малое изображение из большого" (1 - отображать (по умолчанию), 0 - не отображать);
			'create_small_image_from_large' => TRUE,

			// make_small_image_from_big_checked - вид ображения checkbox'а с подписью "Создать малое изображение из большого" выбранным (1 - отображать выбранным (по умолчанию), 0 - невыбранным);
			'create_small_image_from_large_checked' => TRUE,

			// used_watermark_small_image_show - отображать ли checkbox с подписью "Наложить водяной знак на малое изображение" (1 - отображать (по умолчанию), 0 - не отображать);
			'place_watermark_checkbox' => TRUE,

			// used_watermark_small_image_checked - вид ображения checkbox'а с подписью "Наложить водяной знак на малое изображение" (1 - отображать выбранным (по умолчанию), 0 - невыбранным);
			'place_watermark_checkbox_checked' => TRUE,

			// onclick_delete_small_image - значение onclick для удаления малой картинки
			'delete_onclick' => '',

			// href_delete_small_image - значение href для удаления малой картинки
			'delete_href' => NULL,

			// load_small_image_caption - заголовок поля загрузки малого изображения
			'caption' => Core::_('Admin_Form.small_image'),

			'name' => 'small_' . $this->largeImage['name'],
			'id' => 'small_' . $this->largeImage['id'],

			// used_small_image_preserve_aspect_ratio - параметр Отображать ли checkbox с подписью "Сохранять пропорции изображения" (1 - отображать (по умолчанию), 0 - не отображать);
			'preserve_aspect_ratio_checkbox' => TRUE,

			// Не задан вид ображения checkbox'а с подписью "Наложить водяной знак на большое изображение" (1 - отображать выбранным (по умолчанию), 0 - невыбранным);
			'preserve_aspect_ratio_checkbox_checked' => TRUE,

			// Показать поле описания файла
			'show_description' => FALSE,

			// Описания файла
			'description' => ''
		);

		// Объединяем с типовыми параметрами
		$this->smallImage += $aTypicalSmallParams;

		// ----------

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		$oLarge_Core_Html_Entity_Div = new Core_Html_Entity_Div();

		// Установим атрибуты div'a.
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$oLarge_Core_Html_Entity_Div->$attrName($attrValue);
			}
		}

		$oLarge_Main_Block_Core_Html_Entity_Div = Core_Html_Entity::factory('Div')
			->class('row');

		$oLarge_Core_Html_Entity_Div
			->class(
				(count($this->_children) ? 'input-group' : '')
				. ($oLarge_Core_Html_Entity_Div->class != '' ? ' ' . $oLarge_Core_Html_Entity_Div->class : '')
			);

		if (count($this->_children))
		{
			$oLarge_Core_Html_Entity_Div
				->add(
					Core_Html_Entity::factory('Div')
						->class('col-xs-12')
						->add($oLarge_Main_Block_Core_Html_Entity_Div)
				);
		}
		else
		{
			$oLarge_Core_Html_Entity_Div->add($oLarge_Main_Block_Core_Html_Entity_Div);
		}

		$oLarge_Input_Div = Core_Html_Entity::factory('Div')
			->id('file_large_' . $iAdmin_Form_Count)
			->class('form-group col-xs-6 col-sm-7 col-md-5 col-lg-6')
			->add(
				Core_Html_Entity::factory('Span')
					->class('caption')
					->value($this->largeImage['caption'])
			)
			->add(
				$oLarge_Input_Group_Div = Core_Html_Entity::factory('Div')
					->class('input-group')
					->add(
						Core_Html_Entity::factory('Input')
							->class('form-control')
							->name($this->largeImage['name'])
							->id($this->largeImage['id'])
							->type('file')
					)
			);

		$oLarge_Main_Block_Core_Html_Entity_Div
			->add($oLarge_Input_Div);

		if ($this->largeImage['path'] != '' || $this->largeImage['show_params'])
		{
			if ($this->largeImage['path'] != '')
			{
					$oLarge_Input_Group_Div->add(
						Core_Html_Entity::factory('A')
							//->id('control_' . 'large_' . $this->largeImage['id'])
							->id('preview_' . 'large_' . $this->largeImage['id'])
							//->style("float: left")
							->class('input-group-addon control-item')
							->href($this->largeImage['path'])
							->target('_blank')
							->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-image"></i>'))
					)
					->add(
						Core_Html_Entity::factory('A')
							->id('delete_' . 'large_' . $this->largeImage['id'])
							->class('input-group-addon control-item')
							->onclick("res = confirm('" . Core::_('Admin_Form.msg_information_delete') . "'); if (res) { {$this->largeImage['delete_onclick']} } else {return false;}")
							->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-trash"></i>'))
					);
			}

			// Настройки большого изображения
			if ($this->largeImage['show_params'])
			{
				$oLarge_Input_Group_Div
					->add(
						Core_Html_Entity::factory('A')
							->id('file_large_settings_' . $iAdmin_Form_Count)
							->class('input-group-addon control-item')
							//->style('margin: 10px 0 0 15px')
							->add(
								Admin_Form_Entity::factory('Code')->html('<i class="fa fa-cog"></i>')
							)
							->onclick("$('#{$windowId}_watermark_" . $this->_correctId($this->largeImage['name']) . "').HostCMSWindow('open')")
					);
			}
		}

		// Настройки большого изображения
		if ($this->largeImage['show_params'])
		{
			$oLargeWatermark_Div = Core_Html_Entity::factory('Div')
				->id("{$windowId}_watermark_{$this->largeImage['name']}");

			$oLargeWatermark_Table = Core_Html_Entity::factory('Table')
				->border(0)
				->class('no_border')
				->add(
					Core_Html_Entity::factory('Tr')
						->add(
							Core_Html_Entity::factory('Td')
								->value(Core::_('Admin_Form.large_image_max_width'))
						)
						->add(
							Core_Html_Entity::factory('Td')
								->add(
									Core_Html_Entity::factory('Input')
										->type('text')
										->name("large_max_width_{$this->largeImage['name']}")
										->size(5)
										->value($this->largeImage['max_width'])
								)
						)
				)
				->add(
					Core_Html_Entity::factory('Tr')
						->add(
							Core_Html_Entity::factory('Td')
								->value(Core::_('Admin_Form.large_image_max_height'))
						)
						->add(
							Core_Html_Entity::factory('Td')
								->add(
									Core_Html_Entity::factory('Input')
										->type('text')
										->name("large_max_height_{$this->largeImage['name']}")
										->size(5)
										->value($this->largeImage['max_height'])
								)
						)
				)
				;


			// Отображать Сохранять пропорции изображения
			if ($this->largeImage['preserve_aspect_ratio_checkbox'])
			{
				$Core_Html_Entity_Checkbox = Core_Html_Entity::factory('Input')
						->type('checkbox')
						->name("large_preserve_aspect_ratio_{$this->largeImage['name']}")
						->id("large_preserve_aspect_ratio_{$this->largeImage['name']}")
						->value(1)
						->checked($this->largeImage['preserve_aspect_ratio_checkbox_checked']);

				$oLargeWatermark_Table
					->add(
						Core_Html_Entity::factory('Tr')
							->add(
								Core_Html_Entity::factory('Td')
									->colspan(2)
									->add(
										Core_Html_Entity::factory('Label')
											->value(Core::_('Admin_Form.image_preserve_aspect_ratio'))
											->add(
												$Core_Html_Entity_Checkbox
											)
									)
							)
					);
			}

			// Наложить водяной знак на изображение
			if ($this->largeImage['place_watermark_checkbox'] == 1)
			{
				$Core_Html_Entity_Checkbox = Core_Html_Entity::factory('Input')
						->type('checkbox')
						->name("large_place_watermark_checkbox_{$this->largeImage['name']}")
						->id("large_place_watermark_checkbox_{$this->largeImage['name']}")
						->value(1)
						->checked($this->largeImage['place_watermark_checkbox_checked']);

				$oLargeWatermark_Table
					->add(
						Core_Html_Entity::factory('Tr')
							->add(
								Core_Html_Entity::factory('Td')
									->colspan(2)
									->add(
										Core_Html_Entity::factory('Label')
											->value(Core::_('Admin_Form.place_watermark'))
											->add(
												$Core_Html_Entity_Checkbox
											)
									)
							)
					);
			}

			// Отображать поле положения "водяного" знака по оси X
			if ($this->largeImage['place_watermark_x_show'] == 1)
			{
				$oLargeWatermark_Table
					->add(
						Core_Html_Entity::factory('Tr')
							->add(
								Core_Html_Entity::factory('Td')
									->value(Core::_('Admin_Form.watermark_position_x'))
							)
							->add(
								Core_Html_Entity::factory('Td')
									->add(
										Core_Html_Entity::factory('Input')
											->type('text')
											->name("watermark_position_x_{$this->largeImage['name']}")
											->size(5)
											->value($this->largeImage['watermark_position_x'])
									)
							)
					);
			}

			// Отображать поле положения "водяного" знака по оси Y
			if ($this->largeImage['place_watermark_y_show'] == 1)
			{
				$oLargeWatermark_Table
					->add(
						Core_Html_Entity::factory('Tr')
							->add(
								Core_Html_Entity::factory('Td')
									->value(Core::_('Admin_Form.watermark_position_y'))
							)
							->add(
								Core_Html_Entity::factory('Td')
									->add(
										Core_Html_Entity::factory('Input')
											->type('text')
											->name("watermark_position_y_{$this->largeImage['name']}")
											->size(5)
											->value($this->largeImage['watermark_position_y'])
									)
							)
					);
			}

			// -----------------

			$oLargeWatermark_Div->add(
					$oLargeWatermark_Table
				);

			$oLarge_Core_Html_Entity_Div
				->add($oLargeWatermark_Div)
				->add(
					Core_Html_Entity::factory('Script')
						->value("$(function() {
							$('#{$windowId}_watermark_" . $this->_correctId($this->largeImage['name']) . "').HostCMSWindow({ autoOpen: false, destroyOnClose: false, title: '" . Core::_('Admin_Form.window_large_image') . "', AppendTo: '#{$windowId} form #file_large_{$iAdmin_Form_Count}', width: 360, height: 230, addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
				);
		}

		if ($this->largeImage['show_description'])
		{
			//$oLarge_Core_Html_Entity_Div
			$oLarge_Main_Block_Core_Html_Entity_Div
				->add(
					 Core_Html_Entity::factory('Div')
						->class('form-group col-xs-6 col-sm-5 col-md-7 col-lg-6')
						->add(
							Core_Html_Entity::factory('Span')
								->class('caption')
								->value(Core::_('Admin_Form.file_description'))
						)
						->add(
							Core_Html_Entity::factory('Input')
								->type('text')
								->name("description_{$this->largeImage['name']}")
								//->size(45)
								//->class('clear')
								->class('form-control description-large')
								->value($this->largeImage['description'])
						)
				);
		}

		// Отображать поле загрузки малого изображения
		if ($this->smallImage['show'] == 1)
		{
			$oSmall_Input_Div = Core_Html_Entity::factory('Div')
				->id('file_small_' . $iAdmin_Form_Count)
				->class('form-group col-xs-6 col-sm-7 col-md-5 col-lg-6')
				->add(
					Core_Html_Entity::factory('Span')
						->class('caption')
						->value($this->smallImage['caption'])
				)
				->add(
					$oSmall_Input_Group_Div = Core_Html_Entity::factory('Div')
						->class('input-group')
						->add(
							Core_Html_Entity::factory('Input')
								->class('form-control')
								->name($this->smallImage['name'])
								->id($this->smallImage['id'])
								->type('file')
						)
				);

			$oLarge_Main_Block_Core_Html_Entity_Div
				->add(
					Core_Html_Entity::factory('Div')
						->style('clear:both')
				)
				->add($oSmall_Input_Div);

			if ($this->smallImage['path'] != '' || $this->smallImage['show_params'])
			{
				if ($this->smallImage['path'] != '')
				{
					//$oSmallControl_Div->add(
					$oSmall_Input_Group_Div->add(
							Core_Html_Entity::factory('A')
								//->id('control_' . $this->smallImage['id'])
								->id('preview_' . 'small_' . $this->smallImage['id'])
								->class('input-group-addon control-item')
								->href($this->smallImage['path'])
								->target('_blank')
								->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-image"></i>'))
						)
						->add(
							Core_Html_Entity::factory('A')
								->id('delete_' . 'small_' . $this->smallImage['id'])
								->class('input-group-addon control-item')
								->onclick("res = confirm('" . Core::_('Admin_Form.msg_information_delete') . "'); if (res) { {$this->smallImage['delete_onclick']} } else {return false;}")
								->add(Admin_Form_Entity::factory('Code')->html('<i class="fa fa-trash"></i>'))
						);
				}

				// Настройки малого изображения
				if ($this->smallImage['show_params'])
				{
					$oSmall_Input_Group_Div
						->add(
							Core_Html_Entity::factory('Span')
								->id('file_small_settings_' . $iAdmin_Form_Count)
								->class('input-group-addon control-item')
								->add(
									Admin_Form_Entity::factory('Code')->html('<i class="fa fa-cog"></i>')
								)
								->onclick("$('#{$windowId}_watermark_" . $this->_correctId($this->smallImage['name']) . "').HostCMSWindow('open')")
						);
				}
			}

			// Настройки малого изображения
			if ($this->smallImage['show_params'])
			{
				$oSmallWatermark_Table = Core_Html_Entity::factory('Table')
					->border(0)
					->class('no_border')
					->add(
						Core_Html_Entity::factory('Tr')
							->add(
								Core_Html_Entity::factory('Td')
									->value(Core::_('Admin_Form.small_image_max_width'))
							)
							->add(
								Core_Html_Entity::factory('Td')
									->add(
										Core_Html_Entity::factory('Input')
											->type('text')
											->name("small_max_width_{$this->smallImage['name']}")
											->size(5)
											->value($this->smallImage['max_width'])
									)
							)
					)
					->add(
						Core_Html_Entity::factory('Tr')
							->add(
								Core_Html_Entity::factory('Td')
									->value(Core::_('Admin_Form.small_image_max_height'))
							)
							->add(
								Core_Html_Entity::factory('Td')
									->add(
										Core_Html_Entity::factory('Input')
											->type('text')
											->name("small_max_height_{$this->smallImage['name']}")
											->size(5)
											->value($this->smallImage['max_height'])
									)
							)
					);

				// Создать малое изображение из большого
				if ($this->smallImage['create_small_image_from_large'])
				{
					$Core_Html_Entity_Checkbox = Core_Html_Entity::factory('Input')
							->type('checkbox')
							->name("create_small_image_from_large_{$this->smallImage['name']}")
							->id("create_small_image_from_large_{$this->smallImage['name']}")
							->value(1)
							->checked($this->smallImage['create_small_image_from_large_checked']);

					$oSmallWatermark_Table
						->add(
							Core_Html_Entity::factory('Tr')
								->add(
									Core_Html_Entity::factory('Td')
										->colspan(2)
										->add(
											Core_Html_Entity::factory('Label')
												->value(Core::_('Admin_Form.create_thumbnail'))
												->add(
													$Core_Html_Entity_Checkbox
												)
										)
								)
						);
				}

				// Отображать Сохранять пропорции изображения
				if ($this->smallImage['preserve_aspect_ratio_checkbox'])
				{
					$Core_Html_Entity_Checkbox = Core_Html_Entity::factory('Input')
							->type('checkbox')
							->name("small_preserve_aspect_ratio_{$this->smallImage['name']}")
							->id("small_preserve_aspect_ratio_{$this->smallImage['name']}")
							->value(1)
							->checked($this->smallImage['preserve_aspect_ratio_checkbox_checked']);

					$oSmallWatermark_Table
						->add(
							Core_Html_Entity::factory('Tr')
								->add(
									Core_Html_Entity::factory('Td')
										->colspan(2)
										->add(
											Core_Html_Entity::factory('Label')
												->value(Core::_('Admin_Form.image_preserve_aspect_ratio'))
												->add(
													$Core_Html_Entity_Checkbox
												)
										)
								)
						);
				}

				// Наложить водяной знак на изображение
				if ($this->smallImage['place_watermark_checkbox'] == 1)
				{
					$Core_Html_Entity_Checkbox = Core_Html_Entity::factory('Input')
							->type('checkbox')
							->name("small_place_watermark_checkbox_{$this->smallImage['name']}")
							->id("small_place_watermark_checkbox_{$this->smallImage['name']}")
							->value(1)
							->checked($this->smallImage['place_watermark_checkbox_checked']);

					$oSmallWatermark_Table
						->add(
							Core_Html_Entity::factory('Tr')
								->add(
									Core_Html_Entity::factory('Td')
										->colspan(2)
										->add(
											Core_Html_Entity::factory('Label')
												->value(Core::_('Admin_Form.place_watermark'))
												->add(
													$Core_Html_Entity_Checkbox
												)
										)
								)
						);
				}

				if ($this->smallImage['show_description'])
				{
					$oLarge_Main_Block_Core_Html_Entity_Div
						->add(
							Core_Html_Entity::factory('Div')
							->class('form-group col-xs-6 col-sm-5 col-md-7 col-lg-6')
							->add(
								Core_Html_Entity::factory('Span')
									->class('caption')
									->value(Core::_('Admin_Form.file_description'))
							)
							->add(
								Core_Html_Entity::factory('Input')
									->type('text')
									->name("description_{$this->smallImage['name']}")
									->class('form-control description-small')
									->value($this->smallImage['description'])
							)
						);
				}

				// -----------------
				$oSmallWatermark_Div = Core_Html_Entity::factory('Div')
					->id("{$windowId}_watermark_{$this->smallImage['name']}")
					->add(
						$oSmallWatermark_Table
					);

				$oSmall_Input_Div
					->add($oSmallWatermark_Div)
					->add(
						Core_Html_Entity::factory('Script')
							//->value("CreateWindow('{$windowId}_watermark_{$this->smallImage['name']}', '" . Core::_('Admin_Form.window_small_image') . "', '', '')")
							->value("$(function() {
							$('#{$windowId}_watermark_" . $this->_correctId($this->smallImage['name']) . "').HostCMSWindow({ autoOpen: false, destroyOnClose: false, title: '" . Core::_('Admin_Form.window_small_image') . "', AppendTo: '#{$windowId} form #file_small_{$iAdmin_Form_Count}', width: 360, height: 180, addContentPadding: true, modal: false, Maximize: false, Minimize: false }); });")
					);
			}
		}

		foreach ($this->_children as $oCore_Html_Entity)
		{
			$oLarge_Core_Html_Entity_Div->add($oCore_Html_Entity);
		}

		$oLarge_Core_Html_Entity_Div->add(
				Core_Html_Entity::factory('Div')
					->style('clear: both')
			);

		$oLarge_Core_Html_Entity_Div->execute();
	}
}