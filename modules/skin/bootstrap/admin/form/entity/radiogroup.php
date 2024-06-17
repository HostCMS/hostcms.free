<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2024, https://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Radiogroup extends Skin_Default_Admin_Form_Entity_Radiogroup
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		// Add label propery
		$this->_allowedProperties[] = 'colors';

		$this->_skipProperties[] = 'colors';

		parent::__construct();

		$this->colors(array('btn-palegreen', 'btn-warning', 'btn-danger', 'btn-sky', 'btn-maroon'));
	}

	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Radiogroup.onBeforeExecute
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Radiogroup.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$aAttr = $this->getAttrsString();

		$aDivAttr = array();
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars((string) $attrValue) . "\"";
			}
		}

		$aLabelAttr = array();
		if (is_array($this->labelAttr))
		{
			foreach ($this->labelAttr as $attrName => $attrValue)
			{
				$aLabelAttr[] = "{$attrName}=\"" . htmlspecialchars((string) $attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php
		?><span class="caption"><?php echo $this->caption?></span><?php
		?><div class="radio-group"><?php
		$aClassBtnColors = $this->colors;
		$i = 0;

		foreach ($this->radio as $key => $value)
		{
			$tmpAttr = $aAttr;

			if ($key == $this->value)
			{
				$tmpAttr[] = 'checked="checked"';
			}

			//$tmpAttr[] = 'id="' . htmlspecialchars($this->id) . $key . '"';
			$tmpAttr[] = 'value="' . htmlspecialchars($key) . '"';
			?>

			<label class="checkbox-inline">
			<input <?php echo implode(' ', $tmpAttr)?> />
			<span class="btn btn-labeled <?php echo htmlspecialchars($i < count($aClassBtnColors) ? $aClassBtnColors[$i++] : $aClassBtnColors[$i = 0])?>">
			<?php
			// ico к пункту
			if (isset($this->ico[$key]))
			{
				$ico = strpos($this->ico[$key], ' ') === FALSE
					? 'fa ' . $this->ico[$key]
					: $this->ico[$key];
				?><i class="btn-label <?php echo htmlspecialchars((string) $ico)?>"></i><?php
			}
			echo $value;
			?></span>
			</label>
			<?php
		}

		if (count($this->_children))
		{
			// Могут быть дочерние элементы элементы
			$this->executeChildren();
		}

		?></div></div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}