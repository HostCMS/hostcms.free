<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * select entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Core_Html_Entity_Select extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'value',
		'options',
		'disabled',
		'multiple',
		'name',
		'size',
		'form'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperties = array(
		'options', // array
		'value'
	);

	
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();
		
		echo PHP_EOL;
		
		?><select <?php echo implode(' ', $aAttr) ?>><?php

		if (is_array($this->options))
		{
			$this->_showOptions($this->options);
			/* foreach ($this->options as $key => $value)
			{
				?><option value="<?php echo htmlspecialchars($key)?>"<?php echo ($this->value == $key)
				? ' selected="selected"'
				: ''?>><?php
				?><?php echo htmlspecialchars((string) $value)?><?php
				?></option><?php
			} */
		}
		?></select><?php
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

				(!is_array($this->value) && $this->value == $key
					|| is_array($this->value) && in_array($key, $this->value))
				&& $attr['selected'] = 'selected';

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
				echo ' ', $attrKey, '=', '"', htmlspecialchars((string) $attrValue, ENT_COMPAT, 'UTF-8'), '"';
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
			echo ' ', $attrKey, '=', '"', htmlspecialchars((string) $attrValue, ENT_COMPAT, 'UTF-8'), '"';
		}
		?>><?php echo htmlspecialchars((string) $value, ENT_COMPAT, 'UTF-8')?></option><?php
	}
}