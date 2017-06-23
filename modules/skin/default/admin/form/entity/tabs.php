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
class Skin_Default_Admin_Form_Entity_Tabs extends Admin_Form_Entity
{
	/**
	 * Allowed object properties
	 * @var array
	 */
	protected $_allowedProperties = array(
		'formId'
	);

	/**
	 * Executes the business logic.
	 */
	public function execute()
	{
		// Tab-ы выводим только если их больше 1-го.
		if (count($this->_children) > 1)
		{
			// Добавим отступ сверху и снизу
			?><div id="tab"><?php
				?><img src="/admin/images/tab_top_fon_4_form_end.gif" style="position: absolute; right: 0px; bottom: 0px"><?php
				?><ul><?php
				$tab_id = 0;
				foreach ($this->_children as $oAdmin_Form_Tab_Entity)
				{
					// Hide inactive tabs
					if ($oAdmin_Form_Tab_Entity->active)
					{
						$class = $tab_id == 0 ? ' current_li' : '';
						?><li class="li_tab<?php echo $class?>" id="li_tab_page_<?php echo $tab_id?>" onclick="$.showTab('<?php echo $this->_Admin_Form_Controller->getWindowId()?>', 'tab_page_<?php echo $tab_id?>')"><?php
							?><span><?php echo htmlspecialchars($oAdmin_Form_Tab_Entity->caption)?></span><?php
						?></li><?php
						$tab_id++;
					}
				}
				?></ul><div style="clear: both"></div><?php
			?></div><?php
		}

		$tab_id = 0;
		foreach ($this->_children as $oAdmin_Form_Tab_Entity)
		{
			?><div id="tab_page_<?php echo $oAdmin_Form_Tab_Entity->active ? $tab_id : ''?>" class="tab_page"><?php
			$oAdmin_Form_Tab_Entity->execute();
			?></div><?php
			$oAdmin_Form_Tab_Entity->active && $tab_id++;
		}
		?><div style="clear: both"> </div><?php
	}
}