<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * select entity
 *
 * @package HostCMS
 * @subpackage Core\Html
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2016 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Core_Html_Entity_Select extends Core_Html_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'disabled',
		'multiple',
		'name',
		'size'
	);

	/**
	 * Skip properties
	 * @var array
	 */
	protected $_skipProperies = array(
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
			foreach ($this->options as $key => $value)
			{
				?><option value="<?php echo htmlspecialchars($key)?>"<?php echo ($this->value == $key)
				? ' selected="selected"'
				: ''?>><?php
				?><?php echo htmlspecialchars($value)?><?php
				?></option><?php
			}
		}
		?></select><?php
	}
}