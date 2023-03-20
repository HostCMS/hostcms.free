<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @author Hostmake LLC
 * @copyright © 2005-2023 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Input extends Skin_Default_Admin_Form_Entity_Input
{
	/**
	 * Object has unlimited number of properties
	 * @var boolean
	 */
	protected $_unlimitedProperties = TRUE;

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
				$aDivAttr[] = "{$attrName}=\"" . htmlspecialchars((string) $attrValue) . "\"";
			}
		}

		?><div <?php echo implode(' ', $aDivAttr)?>><?php

		if ($this->caption != '')
		{
			?><span class="caption"><?php echo $this->caption?></span><?php
		}

		if (count($this->_children))
		{
			?><div class="input-group"><?php
		}

		?><input <?php echo implode(' ', $aAttr) ?>/><?php

		// Могут быть дочерние элементы элементы
		if (count($this->_children))
		{
			$this->executeChildren();
		}

		$this->_showFormat();

		if (count($this->_children))
		{
			?></div><?php
		}

		if ($this->colorpicker)
		{
			$windowId = $this->_Admin_Form_Controller->getWindowId();

			?><script>$('#<?php echo htmlspecialchars($windowId) . ' #' . $this->id?>').each(function () {
				$(this).minicolors({
					control: $(this).attr('data-control') || 'hue',
					defaultValue: $(this).attr('data-defaultValue') || '',
					inline: $(this).attr('data-inline') === 'true',
					letterCase: $(this).attr('data-letterCase') || 'lowercase',
					opacity: $(this).attr('data-opacity'),
					position: $(this).attr('data-position') || 'bottom left',
					change: function (hex, opacity) {
						if (!hex) return;
						if (opacity) hex += ', ' + opacity;
					},
					theme: 'bootstrap'
				});
			});</script><?php
		}

		?></div><?php
	}
}