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
class Skin_Bootstrap_Admin_Form_Entity_Radiogroup extends Skin_Default_Admin_Form_Entity_Radiogroup {

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$aAttr = $this->getAttrsString();

		$aDivAttr = array();
		if (is_array($this->divAttr))
		{
			foreach ($this->divAttr as $attrName => $attrValue)
			{
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars($attrValue) . "\"";
			}
		}

		$aLabelAttr = array();
		if (is_array($this->labelAttr))
		{
			foreach ($this->labelAttr as $attrName => $attrValue)
			{
				$aLabelAttr[] = "{$attrName}=\"" . htmlspecialchars($attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php
		?><span class="caption"><?php echo $this->caption?></span>
		<!--<div class="btn-group">-->
		<div class="radio-group">
		<?php

		$aClassBtnColors = array('btn-palegreen', 'btn-warning', 'btn-danger', 'btn-sky');
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
			<input <?php echo implode(' ', $tmpAttr) ?> />
			<span class="btn btn-labeled <?php echo $i < count($aClassBtnColors) ? $aClassBtnColors[$i++] : $aClassBtnColors[$i = 0]?>">
			<?php
			// ico к пункту
			if (isset($this->ico[$key]))
			{
				?><i class="btn-label fa <?php echo htmlspecialchars($this->ico[$key])?>"></i><?php
			}
			echo $value;
			?></span>
			</label>
			<?php
		}

		?></div></div><?php
	}
}