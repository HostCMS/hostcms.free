<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Admin forms.
 *
 * @package HostCMS
 * @subpackage Skin
 * @version 6.x
 * @author Hostmake LLC
 * @copyright © 2005-2018 ООО "Хостмэйк" (Hostmake LLC), http://www.hostcms.ru
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
			// Добавим отступ сверху и снизу
			?><ul id="tab" class="nav nav-tabs"><?php
				$tab_id = 0;
				foreach ($this->_children as $oAdmin_Form_Tab_Entity)
				{
					// Hide inactive tabs
					if ($oAdmin_Form_Tab_Entity->active)
					{
						echo '<li' . ($tab_id == 0 ? ' class="active"' : '') . '>'.
						'<a href="#' . $windowId . '-tab-' . $tab_id . '" data-toggle="tab">' . htmlspecialchars($oAdmin_Form_Tab_Entity->caption) . '</a></li>';
					}
					$tab_id++;
				}
			?></ul><?php
		}

		$tab_id = 0;
		?>
		<div class="tab-content">
		<?php
		foreach ($this->_children as $oAdmin_Form_Tab_Entity)
		{
			?><div class="tab-pane fade <?php echo $tab_id == 0 ? 'in active' : ''?>" id="<?php echo $windowId . '-tab-' . $tab_id ?>">
			<?php
			$oAdmin_Form_Tab_Entity->execute();
			?></div><?php
			$tab_id++;
		}
		?></div><?php
	}
}