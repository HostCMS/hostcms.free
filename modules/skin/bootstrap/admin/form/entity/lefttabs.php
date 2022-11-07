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
class Skin_Bootstrap_Admin_Form_Entity_Lefttabs extends Skin_Default_Admin_Form_Entity_Lefttabs
{
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// Tab-ы выводим только если их больше 1-го.
		if (count($this->_children))
		{
			?><div class="tabbable tabs-left"><?php
				?><ul class="nav nav-tabs"><?php
				$tab_id = 0;
				foreach ($this->_children as $oAdmin_Form_Tab_Entity)
				{
					// Hide inactive tabs
					if ($oAdmin_Form_Tab_Entity->active)
					{
						$class = $tab_id == 0 ? ' active' : '';

						$aAttr = $oAdmin_Form_Tab_Entity->getAttrsString();

						?><li <?php echo implode(' ', $aAttr) ?> class="tab-<?php echo htmlspecialchars((string) $oAdmin_Form_Tab_Entity->color)?><?php echo $class?>"><?php
							?><a href="#<?php echo htmlspecialchars((string) $windowId . '-tab-' . $tab_id)?>" data-toggle="tab"><?php echo htmlspecialchars((string) $oAdmin_Form_Tab_Entity->caption)?><i class="<?php echo htmlspecialchars((string) $oAdmin_Form_Tab_Entity->ico)?>"></i><?php echo $oAdmin_Form_Tab_Entity->captionHTML?></a><?php
						?></li><?php
						$tab_id++;
					}
				}
				$tab_id = 0;
				?></ul>
				<div class="tab-content"><?php
					foreach ($this->_children as $oAdmin_Form_Tab_Entity)
					{
						?><div class="tab-pane <?php echo $tab_id == 0 ? 'in active' : ''?>" id="<?php echo htmlspecialchars((string) $windowId . '-tab-' . $tab_id)?>"><?php $oAdmin_Form_Tab_Entity->execute()?></div><?php
						$oAdmin_Form_Tab_Entity->active && $tab_id++;
					}
			?></div><?php
		}
	}
}