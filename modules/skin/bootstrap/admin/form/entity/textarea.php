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
class Skin_Bootstrap_Admin_Form_Entity_Textarea extends Skin_Default_Admin_Form_Entity_Textarea
{
	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Textarea.onBeforeExecute
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Textarea.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		if ($this->wysiwyg || $this->syntaxHighlighter)
		{
			$this->id = $windowId . '_' . $this->id;

			// Skip check field
			$this->format = NULL;
		}

		$aAttr = $this->getAttrsString();

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

		/*$this->_init = is_null($this->wysiwygOptions)
			? Core_Config::instance()->get('core_wysiwyg')
			: $this->wysiwygOptions;*/

		$tagName = $this->wysiwygInline
			? 'div'
			: 'textarea';

		?><<?php echo $tagName?> <?php echo implode(' ', $aAttr) ?>><?php echo htmlspecialchars((string) $this->value)?></<?php echo $tagName?>><?php

		$this->_format();

		if (count($this->_children))
		{
			// Могут быть дочерние элементы элементы
			$this->executeChildren();
			?></div><?php
		}

		?></div><?php

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}