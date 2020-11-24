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
class Skin_Bootstrap_Admin_Form_Entity_Tabs extends Skin_Default_Admin_Form_Entity_Tabs
{
	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		$windowId = $this->_Admin_Form_Controller->getWindowId();

		// Tab-ы выводим только если их больше 1-го.
		if (count($this->_children) > 1)
		{
			?><ul id="tab" class="nav nav-tabs <?php echo htmlspecialchars($this->class)?>"><?php
				$tab_id = 0;
				foreach ($this->_children as $oAdmin_Form_Tab_Entity)
				{
					// Hide inactive tabs
					if ($oAdmin_Form_Tab_Entity->active)
					{
						$class = $oAdmin_Form_Tab_Entity->class;
						$tab_id == $this->current && $class .= ' active';

						$tabId = strlen($oAdmin_Form_Tab_Entity->id)
							? $oAdmin_Form_Tab_Entity->id
							: $windowId . '-tab-' . $tab_id;

						?><li class="<?php echo htmlspecialchars($class)?>">
						<a href="#<?php echo htmlspecialchars($tabId)?>" data-toggle="tab"><?php
						
						echo htmlspecialchars($oAdmin_Form_Tab_Entity->caption);
						
						if ($oAdmin_Form_Tab_Entity->badge !== '')
						{
							$badgeColor = strlen($oAdmin_Form_Tab_Entity->badgeColor)
								? $oAdmin_Form_Tab_Entity->badgeColor
								: 'default';

							?><span class="badge badge-<?php echo htmlspecialchars($badgeColor)?> margin-left-5"><?php echo htmlspecialchars($oAdmin_Form_Tab_Entity->badge)?></span><?php
						}
						?></a>
						</li><?php
					}

					$tab_id++;
				}
			?></ul><?php
		}

		$tab_id = 0;
		?>
		<div class="tab-content <?php echo htmlspecialchars($this->class)?>">
		<?php
		foreach ($this->_children as $oAdmin_Form_Tab_Entity)
		{
			?><div class="tab-pane fade <?php echo $tab_id == $this->current ? 'in active' : ''?>" id="<?php echo htmlspecialchars(strlen($oAdmin_Form_Tab_Entity->id) ? $oAdmin_Form_Tab_Entity->id : $windowId . '-tab-' . $tab_id) ?>">
			<?php
			$oAdmin_Form_Tab_Entity->execute();
			?></div><?php
			$tab_id++;
		}
		?></div><?php
	}
}