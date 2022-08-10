<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms. Select.
 *
 * - options(array()) Массив значений
 * - value(string|array()) Значение или массив значений
 *
 * <code>
 * $oOptgroup = new stdClass();
 * $oOptgroup->attributes = array('label' => 'Первая группа', 'class' => 'my-optgroup');
 * $oOptgroup->children = array(
 * 		17 => 'Подэлемент 1',
 * 		18 => 'Подэлемент 2',
 * );
 *
 * $oController->options(
 * 	array(
 * 		0 => 'default',
 * 		2 => 'Second',
 * 		'sub1' => $oOptgroup,
 *		3 => 'Third',
 * )
 * );
 * </code>
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2022 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Select extends Admin_Form_Entity
{
	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'divAttr', // array
		'options', // array
		'caption',
		'value', // идет в selected
		'format', // array, массив условий форматирования
		'filter',
		'filterName',
		'caseSensitive',
		'invertor',
		'invertor_id',
		'invertorCaption',
		'inverted'
	);

	/**
	 * Counter of Admin_Form_Entity_Select used in the form
	 * @var int
	 */
	static $iFilterCount = 0;

	protected $_aAlreadySelected = array();

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// Combine
		$this->_skipProperties = array_combine($this->_skipProperties, $this->_skipProperties);

		$oCore_Html_Entity_Select = new Core_Html_Entity_Select();
		$this->_allowedProperties += $oCore_Html_Entity_Select->getAllowedProperties();

		// Свойства, исключаемые для <select>, добавляем в список разрешенных объекта
		$this->_allowedProperties += $this->_skipProperties;

		parent::__construct();

		$oCore_Registry = Core_Registry::instance();
		$iAdmin_Form_Count = $oCore_Registry->get('Admin_Form_Count', 0);
		$oCore_Registry->set('Admin_Form_Count', $iAdmin_Form_Count + 2);

		$this->id = $this->name = 'field_id_' . $iAdmin_Form_Count;

		$iAdmin_Form_Count++;
		$this->invertor_id = 'field_id_' . $iAdmin_Form_Count;

		$this->class .= ' form-control';
		$this->divAttr = array('class' => 'form-group col-xs-12');

		$this->caseSensitive = TRUE;
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		is_null($this->size) && is_null($this->style) && $this->style('width: 100%');

		$aDefaultDivAttr = array('class' => 'item_div');
		$this->divAttr = Core_Array::union($this->divAttr, $aDefaultDivAttr);

		$aAttr = $this->getAttrsString();

		// Установим атрибуты div'a.
		$aDivAttr = array();
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars($attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php

		?><span class="caption"><?php echo $this->caption?></span><?php

		if (count($this->_children))
		{
			?><div class="input-group"><?php
		}

		$this->invertor && $this->_invertor();

		?><select <?php echo implode(' ', $aAttr) ?>><?php
		if (is_array($this->options))
		{
			$this->_showOptions($this->options);
		}
		?></select><?php

		$this->executeChildren();

		$this->filter && $this->_filter();

		if (count($this->_children))
		{
			?></div><?php
		}

		?></div><?php

		// Clear
		$this->_aAlreadySelected = array();
	}

	protected function _showOptions($aOptions)
	{
		foreach ($aOptions as $key => $aValue)
		{
			if (is_object($aValue))
			{
				$this->_showOptgroup($aValue);
			}
			else
			{
				if (is_array($aValue))
				{
					$value = Core_Array::get($aValue, 'value');
					$attr = Core_Array::get($aValue, 'attr', array());
				}
				else
				{
					$value = $aValue;
					$attr = array();
				}

				if ((!is_array($this->value) && strval($this->value) === strval($key)
						|| is_array($this->value) && in_array($key, $this->value)
					) && !in_array($key, $this->_aAlreadySelected)
				)
				{
					$this->_aAlreadySelected[] = $key;
					$attr['selected'] = 'selected';
				}

				$this->_showOption($key, $value, $attr);
			}
		}
	}

	/**
	 * Show optgroup.
	 */
	protected function _showOptgroup(stdClass $oOptgroup)
	{
		?><optgroup<?php
		if (isset($oOptgroup->attributes) && is_array($oOptgroup->attributes))
		{
			foreach ($oOptgroup->attributes as $attrKey => $attrValue)
			{
				echo ' ', $attrKey, '=', '"', htmlspecialchars($attrValue, ENT_COMPAT, 'UTF-8'), '"';
			}
		}
		?>><?php
		if (isset($oOptgroup->children) && is_array($oOptgroup->children))
		{
			$this->_showOptions($oOptgroup->children);
		}
		?></optgroup><?php
	}

	/**
	 * Show option
	 * @param string $key key
	 * @param string $value value
	 * @param array $aAttr attributes
	 */
	protected function _showOption($key, $value, array $aAttr = array())
	{
		?><option value="<?php echo htmlspecialchars($key)?>"<?php
		foreach ($aAttr as $attrKey => $attrValue)
		{
			echo ' ', $attrKey, '=', '"', htmlspecialchars($attrValue, ENT_COMPAT, 'UTF-8'), '"';
		}
		?>><?php echo htmlspecialchars($value, ENT_COMPAT, 'UTF-8')?></option><?php
	}

	/**
	 * Show filter
	 * @return self
	 */
	protected function _filter()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();
		
		$filterName = !is_null($this->filterName)
			? $this->filterName
			: "oSelectFilter" . (self::$iFilterCount++);

		Core_Html_Entity::factory('Div')
			->style("float: left; opacity: 0.7")
			->add(
				Core_Html_Entity::factory('Img')
					->src('/admin/images/filter.gif')
					->class('img_line')
					->style('margin-left: 10px')
			)
			->add(
				Core_Html_Entity::factory('Input')
					->size(15)
					->id("filter_{$this->id}")
					->onkeyup("clearTimeout({$filterName}.timeout); {$filterName}.timeout = setTimeout(function(){{$filterName}.Set(document.getElementById('filter_" . Core_Str::escapeJavascriptVariable($this->id) . "').value); {$filterName}.Filter();}, 500)")
					->onkeypress("if (event.keyCode == 13) return false;")
			)
			->add(
				Core_Html_Entity::factory('Input')
					->type("button")
					->onclick("this.form.filter_" . Core_Str::escapeJavascriptVariable($this->id) . ".value = '';{$filterName}.Set('');{$filterName}.Filter();")
					->value(Core::_('Admin_Form.clear'))
					->class('saveButton')
			)
			->add(
				Core_Html_Entity::factory('Input')
					->id("filter_ignorecase_{$this->id}")
					->type("checkbox")
					->onclick("{$filterName}.SetIgnoreCase(!this.checked);{$filterName}.Filter()")
			)
			->add(
				Core_Html_Entity::factory('Label')
					->for("filter_ignorecase_{$this->id}")
					->value(Core::_('Admin_Form.case_sensitive'))
			)
			->add(
				Core_Html_Entity::factory('Script')
					->value("var {$filterName} = new cSelectFilter('" . Core_Str::escapeJavascriptVariable($windowId) . "', '" . Core_Str::escapeJavascriptVariable($this->id) . "');")
			)
			->execute();

		Admin_Form_Entity::factory('Separator')
			->execute();

		return $this;
	}
}