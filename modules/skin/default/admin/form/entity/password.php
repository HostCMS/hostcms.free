<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2025, https://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Password extends Admin_Form_Entity_Input
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'generatePassword',
		'generatePasswordLength'
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this
			->generatePassword(FALSE)
			->generatePasswordLength(8)
			->type('password')
			->size(30);
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Password.onBeforeExecute
	 * @hostcms-event Skin_Default_Admin_Form_Entity_Password.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		is_null($this->size) && is_null($this->style) && $this->style('width: 100%');

		$aAttr = $this->getAttrsString();

		$aDefaultDivAttr = array('class' => 'item_div');
		$this->divAttr = Core_Array::union($this->divAttr, $aDefaultDivAttr);

		$aDivAttr = array();
		// Установим атрибуты div'a.
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars((string) $attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php
		?><span class="caption"><?php echo $this->caption?></span><?php

		if (count($this->_children))
		{
			?><div class="input-group"><?php
		}
		?>
		<input autocomplete="new-password" <?php echo implode(' ', $aAttr) ?>/>
		<?php
		if ($this->generatePassword)
		{
			?><a class="generate-password" onclick="$.generatePassword(<?php echo htmlspecialchars((int) $this->generatePasswordLength)?>);"><i class="fa fa-keyboard-o"></i></a><?php
		}

		$this->executeChildren();

		$this->_showFormat();

		if (count($this->_children))
		{
			?></div><?php
		}

		?></div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}
