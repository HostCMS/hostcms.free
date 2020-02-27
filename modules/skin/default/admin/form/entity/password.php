<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2020 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Default_Admin_Form_Entity_Password extends Admin_Form_Entity_Input
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'generatePassword'
	);

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$this
			->generatePassword(FALSE)
			->type('password')
			->size(30);
	}

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
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
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars($attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php
		?><span class="caption"><?php echo $this->caption?></span><?php

		if (count($this->_children))
		{
			?><div class="input-group"><?php
		}

		?>
		<input <?php echo implode(' ', $aAttr) ?>/>
		<?php
		if ($this->generatePassword)
		{
			?>
			<a class="generate-password" onclick="$.generatePassword();"><i class="fa fa-keyboard-o"></i></a>
			<?php
		}

		// Могут быть дочерние элементы элементы
		$this->executeChildren();

		$this->_showFormat();

		if (count($this->_children))
		{
			?></div><?php
		}

		?></div><?php
	}
}
