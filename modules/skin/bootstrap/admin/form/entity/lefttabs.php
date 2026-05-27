<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Skin_Bootstrap_Admin_Form_Entity_Lefttabs extends Skin_Default_Admin_Form_Entity_Lefttabs
{
	/**
	 * Executes the business logic.
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Lefttabs.onBeforeExecute
	 * @hostcms-event Skin_Bootstrap_Admin_Form_Entity_Lefttabs.onAfterExecute
	 */
	public function execute()
	{
		Core_Event::notify(get_class($this) . '.onBeforeExecute', $this);

		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// Tab-ы выводим только если их больше 1-го.
		if (count($this->_children))
		{
			?><aside class="sidebar"><?php
				$tab_id = 0;
				foreach ($this->_children as $oAdmin_Form_Tab_Entity)
				{
					// Hide inactive tabs
					if ($oAdmin_Form_Tab_Entity->active)
					{
						$class = $tab_id == 0 ? ' active' : '';

						$aAttr = $oAdmin_Form_Tab_Entity->getAttrsString();

						?><div <?php echo implode(' ', $aAttr)?> class="tab-item tab-<?php echo htmlspecialchars((string) $oAdmin_Form_Tab_Entity->color)?><?php echo $class?>" data-tab="#<?php echo htmlspecialchars((string) $windowId . '-tab-' . $tab_id)?>">
							<div class="tab-title"><i class="<?php echo htmlspecialchars((string) $oAdmin_Form_Tab_Entity->ico)?>"></i><?php echo htmlspecialchars((string) $oAdmin_Form_Tab_Entity->caption)?></div>
							<div class="tab-value"><?php echo $oAdmin_Form_Tab_Entity->captionHTML?></div>
						</div><?php
						$tab_id++;
					}
				}
			?></aside><?php

			$tab_id = 0;
			?>
			<main class="content"><?php
				foreach ($this->_children as $oAdmin_Form_Tab_Entity)
				{
					?><div class="tab-item-content <?php echo $tab_id == 0 ? 'active' : ''?>" id="<?php echo htmlspecialchars((string) $windowId . '-tab-' . $tab_id)?>"><?php $oAdmin_Form_Tab_Entity->execute()?></div><?php
					$oAdmin_Form_Tab_Entity->active && $tab_id++;
				}
			?></main><?php
		}

		Core_Event::notify(get_class($this) . '.onAfterExecute', $this);
	}
}