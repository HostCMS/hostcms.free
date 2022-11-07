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
class Skin_Bootstrap_Admin_Form_Entity_Select extends Skin_Default_Admin_Form_Entity_Select
{
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
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

		if ($this->filter)
		{
			?><div class="row"><?php
				if ($this->caseSensitive)
				{
					?><div class="col-xs-12 col-sm-6"><?php
				}
				else
				{
					?><div class="col-xs-7 col-sm-8"><?php
				}
		}

		// Не показывать <span>, если пустой. Используется при сдвоенных селекторах
		if ($this->caption != '' && strlen($this->caption))
		{
			?><span class="caption"><?php echo $this->caption; $this->invertor && $this->_invertor();?></span><?php
		}

		if (count($this->_children))
		{
			?><div class="input-group"><?php
		}

		?><select <?php echo implode(' ', $aAttr) ?>><?php
		if (is_array($this->options))
		{
			$this->_showOptions($this->options);
		}
		?></select><?php

		$this->executeChildren();

		if (count($this->_children))
		{
			?></div><?php
		}

		if ($this->filter)
		{
			?></div><?php
			$this->_filter();
			?></div><?php
		}

		?></div><?php

		// Clear
		$this->_aAlreadySelected = array();
	}

	/**
	 * Show invertor
	 * @return self
	 */
	protected function _invertor()
	{
		?><label class="checkbox-inline"><?php
		$oCore_Html_Entity_Input = Core_Html_Entity::factory('Input')
			->type("checkbox")
			->id($this->invertor_id)
			->name($this->name . '_inverted')
			->value(1);

		$this->inverted && $oCore_Html_Entity_Input->checked(TRUE);

		$oCore_Html_Entity_Input->execute();

		Core_Html_Entity::factory('Span')
			->class('caption text')
			->style('display:inline')
			->value($this->invertorCaption . '&nbsp;')
			->execute();
		?></label><?php
		return $this;
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

		Admin_Form_Entity::factory('Div')
			->class($this->caseSensitive ? 'col-xs-10 col-sm-5' : 'col-xs-5 col-sm-4 no-padding-left')
			->add(
				Admin_Form_Entity::factory('Div')
					->class('input-group' . ($this->caption != '' ? ' margin-top-21' : ''))
					->add(
						Admin_Form_Entity::factory('Code')
							->html('<span class="input-group-addon"><i class="fa-solid fa-magnifying-glass"></i></span>
								<input class="form-control" type="text" id="filter_' . $this->id . '" onkeyup="clearTimeout(' . $filterName . '.timeout); ' . $filterName . '.timeout = setTimeout(function(){' . $filterName . ".Set($(event.target).val()); {$filterName}.Filter(); }, 500)". '" onkeypress="if (event.keyCode == 13) return false;" />' .
								'<span class="input-group-addon" onclick="' . " $(this).prev('input').val(''); {$filterName}.Set(''); {$filterName}.Filter();" . '"><i class="fa fa-times-circle no-margin"></i></span>'
							)
					)
			)
			->execute();

			if ($this->caseSensitive)
			{
				Admin_Form_Entity::factory('Div')
					->class('col-xs-2 col-sm-1 no-padding-left' . ($this->caption != '' ? ' margin-top-25' : ''))
					->add(
						Admin_Form_Entity::factory('Code')
							->html('<label class="checkbox-inline" title="' . Core::_('Admin_Form.case_sensitive') . '">' .
							'<input id="filter_ignorecase_' . $this->id . '" class="form-control colored-blue font" type="checkbox" value="1" checked="checked" onclick="' . $filterName . '.SetIgnoreCase(!this.checked); ' . $filterName . '.Filter()" />' .
							'<span class="text"></span></label>')
					)
					->execute();
			}

			Core_Html_Entity::factory('Script')
				->value("var {$filterName} = new cSelectFilter('{$windowId}', '{$this->id}');")
				->execute();

		return $this;
	}
}